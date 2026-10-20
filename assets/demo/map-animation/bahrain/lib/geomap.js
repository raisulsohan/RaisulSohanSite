// GeoMap: satellite map in WebGL2 (Web Mercator) seen by a 3D camera.
// Imagery layers (NASA Blue Marble, Sentinel-2, Landsat) carry a land mask, so country fills drawn on a 2D canvas
// are multiplied by the real coastline of the photo. Three 2D canvases are composited on top in one pass:
//   tl  – "tint over land": colour × land mask, multiplied into the map (country highlights keep the terrain)
//   tm  – tint multiplied without the land mask (for shapes already traced from the photo, e.g. Bahrain)
//   tn  – light wash over land (normal blend × land mask): pale highlights as Argentina in the Falklands film
//   ta  – "tint anywhere": normal blend under the vignette (seas, shadows, glows that sit in the map)
//   ad  – light added over the map, above the vignette (fire, flashes, missile heads: lib/fx.js)
//   ov  – overlay: labels, lines, pins, titles (above the vignette, crisp)
// Every frame is a pure function of the camera, so seek / frame-step / posterize stay exact.
(function () {
  const D2R = Math.PI / 180, S = 1024;               // world units across the whole Mercator square
  const GM = {};
  GM.mx = lon => (lon + 180) / 360;
  GM.my = lat => { const s = Math.sin(K.clamp(lat, -85, 85) * D2R); return .5 - Math.log((1 + s) / (1 - s)) / (4 * Math.PI); };
  GM.lon = x => x * 360 - 180;
  GM.lat = y => Math.atan(Math.sinh(Math.PI * (1 - 2 * y))) / D2R;
  let MX0 = GM.mx(50.55), MY0 = GM.my(26.0);         // local origin (Bahrain) keeps float precision high

  // ───────── matrices (column-major) ─────────
  const M4 = {
    mul(a, b) { const o = new Float32Array(16); for (let c = 0; c < 4; c++) for (let r = 0; r < 4; r++) { let s = 0; for (let k = 0; k < 4; k++) s += a[k * 4 + r] * b[c * 4 + k]; o[c * 4 + r] = s; } return o; },
    persp(fov, asp, n, f) { const t = 1 / Math.tan(fov / 2), o = new Float32Array(16); o[0] = t / asp; o[5] = t; o[10] = (f + n) / (n - f); o[11] = -1; o[14] = 2 * f * n / (n - f); return o; },
    look(e, c, u) {
      let z = [e[0] - c[0], e[1] - c[1], e[2] - c[2]]; let l = Math.hypot(...z); z = z.map(v => v / l);
      let x = [u[1] * z[2] - u[2] * z[1], u[2] * z[0] - u[0] * z[2], u[0] * z[1] - u[1] * z[0]]; l = Math.hypot(...x); x = x.map(v => v / l);
      const y = [z[1] * x[2] - z[2] * x[1], z[2] * x[0] - z[0] * x[2], z[0] * x[1] - z[1] * x[0]];
      return new Float32Array([x[0], y[0], z[0], 0, x[1], y[1], z[1], 0, x[2], y[2], z[2], 0,
        -(x[0] * e[0] + x[1] * e[1] + x[2] * e[2]), -(y[0] * e[0] + y[1] * e[1] + y[2] * e[2]), -(z[0] * e[0] + z[1] * e[1] + z[2] * e[2]), 1]);
    },
  };

  // camera: { lon, lat, zoom (web-map zoom: 256·2^z px per world), pitch°, bearing° (heading the camera faces), sx, sy (screen offset of the target, px) }
  function camMatrices(c, W, H, FOV) {
    const T = [(GM.mx(c.lon) - MX0) * S, (MY0 - GM.my(c.lat)) * S, 0];
    const ppu = 256 * Math.pow(2, c.zoom) / S, hW = H / ppu, fov = FOV * D2R;
    const d = hW / 2 / Math.tan(fov / 2), p = (c.pitch || 0) * D2R, b = (c.bearing || 0) * D2R;
    const fw = [Math.sin(b), Math.cos(b)];
    const eye = [T[0] - fw[0] * d * Math.sin(p), T[1] - fw[1] * d * Math.sin(p), d * Math.cos(p)];
    const up = [fw[0] * Math.cos(p), fw[1] * Math.cos(p), Math.sin(p)];
    let P = M4.persp(fov, W / H, d * .02, d * 40);
    if (c.sx || c.sy) { const o = new Float32Array(16); o.set([1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 2 * (c.sx || 0) / W, -2 * (c.sy || 0) / H, 0, 1]); P = M4.mul(o, P); }
    return { vp: M4.mul(P, M4.look(eye, T, up)), ppu, d };
  }

  // ───────── shaders ─────────
  const VS_LAYER = `#version 300 es
  in vec2 aPos; in vec2 aUv; uniform mat4 uVP; out vec2 vUv;
  void main(){ vUv = aUv; gl_Position = uVP * vec4(aPos, 0., 1.); }`;
  const FS_LAYER = `#version 300 es
  precision highp float;
  in vec2 vUv; uniform sampler2D uTex, uLand;
  uniform float uOpacity; uniform vec4 uAdj; uniform vec3 uGain; uniform vec4 uFeather;
  uniform vec3 uWLo, uWMid, uWHi; uniform vec4 uGrade;   // water amount, water detail, land saturation, land gain
  uniform vec2 uLandLvl;                                  // land-mask remap (lo, hi)
  uniform vec2 uWGain;                                    // sea brightness gain, offset (match sources)
  layout(location=0) out vec4 oCol; layout(location=1) out vec4 oLand;
  void main(){
    vec4 t = texture(uTex, vUv);
    float land = smoothstep(uLandLvl.x, uLandLvl.y, texture(uLand, vUv).r);
    vec3 c = pow(max(t.rgb, 0.), vec3(uAdj.y)) * uAdj.x * uGain;
    float l = dot(c, vec3(.299, .587, .114));
    c = mix(vec3(l), c, uAdj.z);
    c = (c - .5) * uAdj.w + .5;
    // global look: teal sea with its own detail, muted land
    float wl = dot(c, vec3(.25, .5, .25)) * uWGain.x + uWGain.y;
    vec3 teal = wl < .22 ? mix(uWLo, uWMid, smoothstep(.0, .22, wl)) : mix(uWMid, uWHi, smoothstep(.22, .6, wl));
    vec3 cw = teal + (c - vec3(wl)) * uGrade.y;
    float l2 = dot(c, vec3(.299, .587, .114));
    vec3 cl = mix(vec3(l2), c, uGrade.z) * uGrade.w;
    c = mix(mix(c, cw, uGrade.x), cl, land);
    vec2 e = min(vUv, 1. - vUv);
    float f = smoothstep(0., uFeather.x, e.x) * smoothstep(0., uFeather.y, e.y);
    float a = clamp(t.a * f * uOpacity, 0., 1.);
    oCol = vec4(clamp(c, 0., 1.), a); oLand = vec4(land, 0., 0., a);
  }`;
  const VS_QUAD = `#version 300 es
  in vec2 aPos; out vec2 vUv; void main(){ vUv = aPos * .5 + .5; gl_Position = vec4(aPos, 0., 1.); }`;
  const FS_ACC = `#version 300 es
  precision highp float; in vec2 vUv; uniform sampler2D uA, uB; uniform float uW;
  layout(location=0) out vec4 o0; layout(location=1) out vec4 o1;
  void main(){ o0 = texture(uA, vUv) * uW; o1 = texture(uB, vUv) * uW; }`;
  const FS_COMP = `#version 300 es
  precision highp float; in vec2 vUv;
  uniform sampler2D uBase, uLandS, uTL, uTM, uTN, uTW, uTA, uAD, uOV; uniform vec4 uVig; uniform float uGrain, uSeed; uniform vec2 uRes;
  uniform vec4 uFade; // rgb colour, amount (fade the whole map, e.g. to black)
  out vec4 o;
  float h(vec2 p){ return fract(sin(dot(p, vec2(12.9898, 78.233)) + uSeed) * 43758.5453); }
  void main(){
    vec2 uv = vec2(vUv.x, 1. - vUv.y);                 // canvases are top-down
    vec3 c = texture(uBase, vUv).rgb;
    float land = texture(uLandS, vUv).r;
    vec4 tl = texture(uTL, uv);                         // premultiplied
    vec3 tcol = tl.a > 0. ? tl.rgb / tl.a : vec3(1.);
    c *= mix(vec3(1.), tcol, tl.a * land);
    vec4 tm = texture(uTM, uv);                         // multiply, no land mask (shapes traced from the photo)
    c *= mix(vec3(1.), tm.a > 0. ? tm.rgb / tm.a : vec3(1.), tm.a);
    vec4 tn = texture(uTN, uv);                         // light wash over land only (pale country highlight)
    c = mix(c, tn.a > 0. ? tn.rgb / tn.a : c, tn.a * land);
    vec4 tw = texture(uTW, uv);                         // wash over the sea only (sea highlights)
    c = mix(c, tw.a > 0. ? tw.rgb / tw.a : c, tw.a * (1. - land));
    vec4 ta = texture(uTA, uv);
    c = c * (1. - ta.a) + ta.rgb;
    vec2 q = (vUv - .5) * vec2(uRes.x / uRes.y, 1.);
    float r = length(q);
    c *= 1. - uVig.x * smoothstep(uVig.y, uVig.z, r);
    c += texture(uAD, uv).rgb;                          // premultiplied colour = added light
    c = mix(c, uFade.rgb, uFade.a);
    vec4 ov = texture(uOV, uv);
    c = c * (1. - ov.a) + ov.rgb;
    c += (h(gl_FragCoord.xy) - .5) * uGrain;
    o = vec4(c, 1.);
  }`;

  function compile(gl, vs, fs) {
    const p = gl.createProgram();
    for (const [type, src] of [[gl.VERTEX_SHADER, vs], [gl.FRAGMENT_SHADER, fs]]) {
      const s = gl.createShader(type); gl.shaderSource(s, src); gl.compileShader(s);
      if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) throw new Error(gl.getShaderInfoLog(s));
      gl.attachShader(p, s);
    }
    gl.linkProgram(p);
    if (!gl.getProgramParameter(p, gl.LINK_STATUS)) throw new Error(gl.getProgramInfoLog(p));
    const u = {}, n = gl.getProgramParameter(p, gl.ACTIVE_UNIFORMS);
    for (let i = 0; i < n; i++) { const a = gl.getActiveUniform(p, i); u[a.name] = gl.getUniformLocation(p, a.name); }
    return { p, u };
  }

  // a texture from data/texjs/*.js (data URL, used when the page is opened from disk) or the image file itself
  async function bitmap(url) {
    const src = (window.GM_TEX && window.GM_TEX[url]) || url;
    const r = await fetch(src); if (!r.ok) throw new Error(url + ' ' + r.status);
    return createImageBitmap(await r.blob(), { imageOrientation: 'none', premultiplyAlpha: 'none', colorSpaceConversion: 'none' });
  }

  // ───────── the map ─────────
  GM.create = async function (stage, o) {
    const W = o.W, H = o.H, FOV = o.fov || 30;
    const cv = K.canvas(W, H); stage.appendChild(cv);
    const gl = cv.getContext('webgl2', { antialias: false, preserveDrawingBuffer: true, premultipliedAlpha: false, alpha: false });
    if (!gl) throw new Error('WebGL2 is needed');
    const half = gl.getExtension('EXT_color_buffer_float') || gl.getExtension('EXT_color_buffer_half_float');
    const aniso = gl.getExtension('EXT_texture_filter_anisotropic');
    const MAXT = gl.getParameter(gl.MAX_TEXTURE_SIZE);
    const progL = compile(gl, VS_LAYER, FS_LAYER), progA = compile(gl, VS_QUAD, FS_ACC), progC = compile(gl, VS_QUAD, FS_COMP);

    function tex(src, { mip = true, single = false } = {}) {
      const t = gl.createTexture(); gl.bindTexture(gl.TEXTURE_2D, t);
      gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false); gl.pixelStorei(gl.UNPACK_PREMULTIPLY_ALPHA_WEBGL, false);
      let img = src;
      if (src.width > MAXT || src.height > MAXT) {   // GPU limit: shrink on a canvas
        const k = MAXT / Math.max(src.width, src.height), c = K.canvas(Math.floor(src.width * k), Math.floor(src.height * k));
        c.getContext('2d').drawImage(src, 0, 0, c.width, c.height); img = c;
      }
      gl.texImage2D(gl.TEXTURE_2D, 0, single ? gl.R8 : gl.RGBA8, single ? gl.RED : gl.RGBA, gl.UNSIGNED_BYTE, img);
      if (mip) gl.generateMipmap(gl.TEXTURE_2D);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, mip ? gl.LINEAR_MIPMAP_LINEAR : gl.LINEAR);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
      if (mip && aniso) gl.texParameterf(gl.TEXTURE_2D, aniso.TEXTURE_MAX_ANISOTROPY_EXT, Math.min(8, gl.getParameter(aniso.MAX_TEXTURE_MAX_ANISOTROPY_EXT)));
      return t;
    }

    // layers: ids from window.GM_LAYERS, drawn coarse → fine
    const defs = (o.layers || []).map(id => ({ id, ...GM_LAYERS[id] }));
    let done = 0; const total = defs.length * 2, tick = () => { done++; o.progress && o.progress(done, total); };
    const layers = await Promise.all(defs.map(async d => {
      const [im, lm] = await Promise.all([bitmap(d.src).then(b => (tick(), b)), bitmap(d.land).then(b => (tick(), b))]);
      const [x0, y0, x1, y1] = d.b;
      const X0 = (x0 - MX0) * S, X1 = (x1 - MX0) * S, Y0 = (MY0 - y0) * S, Y1 = (MY0 - y1) * S;
      const buf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buf);
      gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([X0, Y0, 0, 0, X1, Y0, 1, 0, X0, Y1, 0, 1, X1, Y1, 1, 1]), gl.STATIC_DRAW);
      const L = { ...d, buf, t: tex(im), lt: tex(lm, { single: true }), opacity: 1 };
      im.close && im.close(); lm.close && lm.close();
      return L;
    }));
    const byId = Object.fromEntries(layers.map(l => [l.id, l]));

    // render targets
    function target(w, h, float) {
      const fb = gl.createFramebuffer(); gl.bindFramebuffer(gl.FRAMEBUFFER, fb);
      const ts = [0, 1].map(i => {
        const t = gl.createTexture(); gl.bindTexture(gl.TEXTURE_2D, t);
        gl.texImage2D(gl.TEXTURE_2D, 0, float ? gl.RGBA16F : gl.RGBA8, w, h, 0, gl.RGBA, float ? gl.HALF_FLOAT : gl.UNSIGNED_BYTE, null);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
        gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0 + i, gl.TEXTURE_2D, t, 0);
        return t;
      });
      gl.drawBuffers([gl.COLOR_ATTACHMENT0, gl.COLOR_ATTACHMENT1]);
      return { fb, ts };
    }
    const SS = o.supersample || 1;              // optional supersampling of the imagery pass
    const RW = W * SS, RH = H * SS;
    const rtS = target(RW, RH, false), rtA = target(RW, RH, !!half);
    const quad = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, quad); gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
    const vao = gl.createVertexArray();

    // 2D canvases
    // each 2D canvas notes whether anything was drawn this frame; empty ones are not uploaded again (a 1920×1080
    // upload costs a few ms, and most frames use only two or three of the six canvases)
    const mk = () => {
      const c = K.canvas(W, H), g = c.getContext('2d'), e = { c, g, used: false, wasUsed: true };
      for (const fn of ['fill', 'stroke', 'fillText', 'strokeText', 'drawImage', 'fillRect', 'strokeRect', 'putImageData']) {
        const orig = g[fn].bind(g); g[fn] = (...a) => { e.used = true; return orig(...a); };
      }
      e.t = gl.createTexture(); gl.bindTexture(gl.TEXTURE_2D, e.t);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
      gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA8, 1, 1, 0, gl.RGBA, gl.UNSIGNED_BYTE, new Uint8Array(4));
      return e;
    };
    const C2 = { tl: mk(), tm: mk(), tn: mk(), tw: mk(), ta: mk(), ad: mk(), ov: mk() };

    // look (shared by all layers); a page may change any of these
    const look = Object.assign({
      // dark navy ocean, as the channel style guide (editor-pack): sampled from the style-pack stills
      wLo: [0.008, 0.068, 0.19], wMid: [0.05, 0.2, 0.41], wHi: [0.21, 0.4, 0.6],
      water: .9, detail: .12, landSat: .82, landGain: .92,
      vig: [.62, .38, 1.02], grain: .018, fade: [0, 0, 0, 0],
    }, o.look || {});

    function drawLayers(c, fb) {
      const m = camMatrices(c, W, H, FOV);
      gl.bindFramebuffer(gl.FRAMEBUFFER, fb.fb); gl.viewport(0, 0, RW, RH);
      gl.clearColor(look.wLo[0], look.wLo[1], look.wLo[2], 1); gl.clear(gl.COLOR_BUFFER_BIT);
      gl.useProgram(progL.p); const u = progL.u;
      gl.uniformMatrix4fv(u.uVP, false, m.vp);
      gl.uniform3fv(u.uWLo, look.wLo); gl.uniform3fv(u.uWMid, look.wMid); gl.uniform3fv(u.uWHi, look.wHi);
      gl.uniform4f(u.uGrade, look.water, look.detail, look.landSat, look.landGain);
      gl.enable(gl.BLEND); gl.blendFuncSeparate(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA, gl.ONE, gl.ONE_MINUS_SRC_ALPHA);
      gl.bindVertexArray(vao);
      const aPos = gl.getAttribLocation(progL.p, 'aPos'), aUv = gl.getAttribLocation(progL.p, 'aUv');
      for (const L of layers) {
        let op = L.opacity;
        if (L.zfade) op *= K.clamp((c.zoom - L.zfade[0]) / (L.zfade[1] - L.zfade[0]));
        if (op <= 0.001) continue;
        gl.bindBuffer(gl.ARRAY_BUFFER, L.buf);
        gl.enableVertexAttribArray(aPos); gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 16, 0);
        gl.enableVertexAttribArray(aUv); gl.vertexAttribPointer(aUv, 2, gl.FLOAT, false, 16, 8);
        gl.activeTexture(gl.TEXTURE0); gl.bindTexture(gl.TEXTURE_2D, L.t); gl.uniform1i(u.uTex, 0);
        gl.activeTexture(gl.TEXTURE1); gl.bindTexture(gl.TEXTURE_2D, L.lt); gl.uniform1i(u.uLand, 1);
        gl.uniform1f(u.uOpacity, op);
        const a = L.adj || [1, 1, 1, 1]; gl.uniform4f(u.uAdj, a[0], a[1], a[2], a[3]);
        gl.uniform3fv(u.uGain, L.gain || [1, 1, 1]);
        gl.uniform4f(u.uFeather, L.feather ? L.feather[0] : 0.0001, L.feather ? L.feather[1] : 0.0001, 0, 0);
        gl.uniform2fv(u.uLandLvl, L.landLvl || [.35, .65]);
        gl.uniform2fv(u.uWGain, L.wgain || [1, 0]);
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
      }
      gl.disable(gl.BLEND);
      return m;
    }

    function upload(t, c) {
      gl.bindTexture(gl.TEXTURE_2D, t);
      gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false); gl.pixelStorei(gl.UNPACK_PREMULTIPLY_ALPHA_WEBGL, true);
      gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA8, gl.RGBA, gl.UNSIGNED_BYTE, c);
    }

    let cur = null;   // matrices of the current (sharp) camera, for projecting overlay points
    const api = {
      W, H, gl, layers: byId, look, C2,
      setOrigin(lon, lat) { MX0 = GM.mx(lon); MY0 = GM.my(lat); },
      // world → screen for the current camera: [x, y] in px (null when behind the camera)
      P(lon, lat) { return api.Pm(GM.mx(lon), GM.my(lat)); },
      Pm(x, y) {
        const m = cur.vp, X = (x - MX0) * S, Y = (MY0 - y) * S;
        const cx = m[0] * X + m[4] * Y + m[12], cy = m[1] * X + m[5] * Y + m[13], cw = m[3] * X + m[7] * Y + m[15];
        if (cw <= 1e-6) return null;
        return [(cx / cw + 1) / 2 * W, (1 - cy / cw) / 2 * H];
      },
      // px per km on the ground at a point (for sizes that stay on the map)
      pxPerKm(lon, lat) { const a = api.P(lon, lat), b = api.P(lon + 1 / (111.32 * Math.cos(lat * D2R)), lat); return a && b ? Math.hypot(b[0] - a[0], b[1] - a[1]) : 0; },
      cam: null,
      // render one frame. camAt(t) → camera; overlay(api) draws the 2D canvases; blur: shutter in seconds (0 = off)
      frame(t, camAt, overlay, { blur = 1 / 60, samples = 8, frameDur = 1 / 30, pre } = {}) {
        const c = camAt(t);
        api.cam = c;
        pre && pre(api, c);   // per-frame layer settings (opacity, look) before anything is drawn
        // motion blur: how far does the centre move across the shutter? (px)
        let n = 1;
        if (blur > 0) {
          const ca = camAt(t - blur / 2), cb = camAt(t + blur / 2);
          cur = camMatrices(ca, W, H, FOV); const pa = api.P(c.lon, c.lat), qa = api.P(GM.lon((GM.mx(c.lon) + .0)), c.lat);
          cur = camMatrices(cb, W, H, FOV); const pb = api.P(c.lon, c.lat);
          const za = Math.abs(cb.zoom - ca.zoom) * H * .7, rot = Math.abs((cb.bearing || 0) - (ca.bearing || 0)) * D2R * W * .5;
          const mv = (pa && pb ? Math.hypot(pb[0] - pa[0], pb[1] - pa[1]) : 0) + za + rot;
          n = mv < 1.2 ? 1 : Math.min(samples, Math.max(3, Math.ceil(mv / 2.5)));
        }
        if (n === 1) {
          drawLayers(c, rtA);
        } else {
          gl.bindFramebuffer(gl.FRAMEBUFFER, rtA.fb); gl.viewport(0, 0, RW, RH); gl.clearColor(0, 0, 0, 0); gl.clear(gl.COLOR_BUFFER_BIT);
          for (let i = 0; i < n; i++) {
            const ts = t - blur / 2 + blur * (i + .5) / n;
            drawLayers(camAt(ts), rtS);
            gl.bindFramebuffer(gl.FRAMEBUFFER, rtA.fb); gl.viewport(0, 0, RW, RH);
            gl.useProgram(progA.p); gl.enable(gl.BLEND); gl.blendFunc(gl.ONE, gl.ONE);
            gl.activeTexture(gl.TEXTURE0); gl.bindTexture(gl.TEXTURE_2D, rtS.ts[0]); gl.uniform1i(progA.u.uA, 0);
            gl.activeTexture(gl.TEXTURE1); gl.bindTexture(gl.TEXTURE_2D, rtS.ts[1]); gl.uniform1i(progA.u.uB, 1);
            gl.uniform1f(progA.u.uW, 1 / n);
            api._quad(progA);
            gl.disable(gl.BLEND);
          }
        }
        cur = camMatrices(c, W, H, FOV);
        for (const k in C2) { const e = C2[k], g = e.g; g.setTransform(1, 0, 0, 1, 0, 0); g.globalAlpha = 1; g.globalCompositeOperation = 'source-over'; if (e.wasUsed) g.clearRect(0, 0, W, H); e.used = false; }
        overlay && overlay(api, c);
        for (const k in C2) { const e = C2[k]; if (e.used || e.wasUsed) upload(e.t, e.c); e.wasUsed = e.used; }
        gl.bindFramebuffer(gl.FRAMEBUFFER, null); gl.viewport(0, 0, W, H);
        gl.useProgram(progC.p); const u = progC.u;
        const bind = (i, t, name) => { gl.activeTexture(gl.TEXTURE0 + i); gl.bindTexture(gl.TEXTURE_2D, t); gl.uniform1i(u[name], i); };
        bind(0, rtA.ts[0], 'uBase'); bind(1, rtA.ts[1], 'uLandS'); bind(2, C2.tl.t, 'uTL'); bind(3, C2.ta.t, 'uTA'); bind(4, C2.ov.t, 'uOV'); bind(5, C2.tm.t, 'uTM'); bind(6, C2.tn.t, 'uTN'); bind(7, C2.tw.t, 'uTW'); bind(8, C2.ad.t, 'uAD');
        gl.uniform4f(u.uVig, look.vig[0], look.vig[1], look.vig[2], 0);
        gl.uniform4f(u.uFade, ...look.fade);
        gl.uniform1f(u.uGrain, look.grain); gl.uniform1f(u.uSeed, (Math.round(t * 30) % 97) * 1.37); gl.uniform2f(u.uRes, W, H);
        api._quad(progC);
        return n;
      },
      _quad(prog) {
        gl.bindVertexArray(vao); gl.bindBuffer(gl.ARRAY_BUFFER, quad);
        const a = gl.getAttribLocation(prog.p, 'aPos'); gl.enableVertexAttribArray(a); gl.vertexAttribPointer(a, 2, gl.FLOAT, false, 0, 0);
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
      },
      info: { MAXT, half: !!half, aniso: !!aniso },
    };
    return api;
  };

  // ───────── camera tracks ─────────
  // keys: [[t, {lon, lat, zoom, pitch, bearing, sx, sy}], ...] → C1-smooth path (monotone cubic per channel),
  // so a move that passes through a key never jumps in speed. Missing channels hold their last value.
  GM.track = function (keys, ease) {
    const ch = ['lon', 'lat', 'zoom', 'pitch', 'bearing', 'sx', 'sy'], ts = keys.map(k => k[0]);
    const full = []; let last = { lon: 0, lat: 0, zoom: 3, pitch: 0, bearing: 0, sx: 0, sy: 0 };
    for (const [, v] of keys) { last = { ...last, ...v }; full.push(last); }
    // pan in Mercator space (straight lines on the map), zoom linear in z
    const val = (k, c) => c === 'lon' ? GM.mx(full[k].lon) : c === 'lat' ? GM.my(full[k].lat) : full[k][c];
    const splines = {};
    for (const c of ch) {
      const y = full.map((_, k) => val(k, c)), n = y.length, d = [], m = [];
      for (let i = 0; i < n - 1; i++) d.push((y[i + 1] - y[i]) / (ts[i + 1] - ts[i]));
      // keys marked {hold:true} or the ends: zero speed there (ease in / out)
      // a key marked 'go' at either end keeps moving (the clip is cut out of a longer move); 'stop' rests mid-way
      for (let i = 0; i < n; i++) {
        if (i === 0) m[i] = keys[i][2] === 'go' ? d[0] : 0;
        else if (i === n - 1) m[i] = keys[i][2] === 'go' ? d[n - 2] : 0;
        else if (keys[i][2] === 'stop') m[i] = 0;
        else m[i] = d[i - 1] * d[i] <= 0 ? 0 : (d[i - 1] + d[i]) / 2;
      }
      for (let i = 0; i < n - 1; i++) {
        if (!d[i]) { m[i] = m[i + 1] = 0; continue; }
        const a = m[i] / d[i], b = m[i + 1] / d[i], h = a * a + b * b;
        if (h > 9) { const k = 3 / Math.sqrt(h); m[i] = k * a * d[i]; m[i + 1] = k * b * d[i]; }
      }
      splines[c] = { y, m };
    }
    return t => {
      const o = {};
      let i = 0; if (t <= ts[0]) i = -1; else if (t >= ts[ts.length - 1]) i = -2; else while (t > ts[i + 1]) i++;
      for (const c of ch) {
        const { y, m } = splines[c]; let v;
        if (i === -1) v = y[0]; else if (i === -2) v = y[y.length - 1];
        else {
          const h = ts[i + 1] - ts[i], u = (t - ts[i]) / h, u2 = u * u, u3 = u2 * u;
          v = (2 * u3 - 3 * u2 + 1) * y[i] + (u3 - 2 * u2 + u) * h * m[i] + (3 * u2 - 2 * u3) * y[i + 1] + (u3 - u2) * h * m[i + 1];
        }
        o[c] = c === 'lon' ? GM.lon(v) : c === 'lat' ? GM.lat(v) : v;
      }
      return o;
    };
  };

  window.GM = GM;
})();
