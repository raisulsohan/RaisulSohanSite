// Shared helpers for the map demos: easing, seeded randomness, map data, textures, polylines.
(function () {
  const K = {};
  K.clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
  K.lerp = (a, b, t) => a + (b - a) * t;
  K.prog = (t, a, b) => K.clamp((t - a) / (b - a));
  K.lerpLog = (a, b, t) => Math.exp(K.lerp(Math.log(a), Math.log(b), t));
  K.ease = {
    lin: t => t,
    out: t => 1 - Math.pow(1 - t, 3),
    in: t => t * t * t,
    inOut: t => t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2,
    inOutQuint: t => t < .5 ? 16 * t ** 5 : 1 - Math.pow(-2 * t + 2, 5) / 2,
    inOutSine: t => -(Math.cos(Math.PI * t) - 1) / 2,
    outSine: t => Math.sin(t * Math.PI / 2),
    outExpo: t => t >= 1 ? 1 : 1 - Math.pow(2, -10 * t),
    outBack: t => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2); },
    outElastic: t => t <= 0 ? 0 : t >= 1 ? 1 : Math.pow(2, -10 * t) * Math.sin((t * 10 - .75) * (2 * Math.PI / 3)) + 1,
  };
  // mulberry32: the same seed gives the same frame every time (scrub-safe)
  K.rng = seed => () => {
    seed |= 0; seed = seed + 0x6D2B79F5 | 0;
    let t = Math.imul(seed ^ seed >>> 15, 1 | seed);
    t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
    return ((t ^ t >>> 14) >>> 0) / 4294967296;
  };
  K.hash = (n) => { const r = K.rng(n * 9301 + 49297); r(); return r(); };

  // keyframe track: [[t, value, ease?], ...] → value at t (numbers only)
  K.track = (keys, t) => {
    if (t <= keys[0][0]) return keys[0][1];
    for (let i = 1; i < keys.length; i++) {
      const [t1, v1, e] = keys[i], [t0, v0] = keys[i - 1];
      if (t <= t1) return K.lerp(v0, v1, (K.ease[e || 'inOut'])((t - t0) / (t1 - t0)));
    }
    return keys[keys.length - 1][1];
  };

  K.fonts = list => Promise.all(list.map(f => document.fonts.load(f))).catch(() => {});

  K.loadWorld = async (res = '50m') => {
    const url = `https://cdn.jsdelivr.net/npm/world-atlas@2.0.2/countries-${res}.json`;
    const r = await fetch(url);
    if (!r.ok) throw new Error('map data ' + r.status);
    const topo = await r.json();
    return {
      topo,
      land: topojson.feature(topo, topo.objects.land),
      countries: topojson.feature(topo, topo.objects.countries).features,
      borders: topojson.mesh(topo, topo.objects.countries, (a, b) => a !== b),
    };
  };
  K.byId = (countries, id) => countries.find(f => f.id === id);
  // keep only countries whose bounds touch bbox [lon0, lat0, lon1, lat1]: projecting the whole world every frame costs ~50 ms
  K.region = (world, [x0, y0, x1, y1]) => {
    const geoms = world.topo.objects.countries.geometries, keep = [], feats = [];
    world.countries.forEach((f, i) => {
      const [[a, b], [c, d]] = d3.geoBounds(f);
      if (a <= c && c >= x0 && a <= x1 && d >= y0 && b <= y1) { keep.push(geoms[i]); feats.push(f); }
    });
    const set = new Set(keep);
    return {
      ...world, countries: feats,
      land: topojson.merge(world.topo, keep),
      borders: topojson.mesh(world.topo, world.topo.objects.countries, (a, b) => a !== b && set.has(a) && set.has(b)),
    };
  };

  K.canvas = (w, h) => { const c = document.createElement('canvas'); c.width = w; c.height = h; return c; };

  // paper: grey around 128 so 'overlay' / 'soft-light' leave the image tone alone
  K.paper = (W, H, seed = 1, strength = 1) => {
    const R = K.rng(seed), c = K.canvas(W, H), g = c.getContext('2d');
    g.fillStyle = '#808080'; g.fillRect(0, 0, W, H);
    for (const [cw, ch, amp] of [[24, 14, 34], [96, 54, 18]]) {
      const s = K.canvas(cw, ch), sg = s.getContext('2d'), id = sg.createImageData(cw, ch);
      for (let i = 0; i < cw * ch; i++) { const v = 128 + (R() - .5) * amp * 2 * strength; id.data.set([v, v, v, 255], i * 4); }
      sg.putImageData(id, 0, 0);
      g.globalAlpha = .55; g.imageSmoothingQuality = 'high'; g.drawImage(s, 0, 0, W, H);
    }
    g.globalAlpha = 1;
    const id = g.getImageData(0, 0, W, H), d = id.data;
    for (let i = 0; i < d.length; i += 4) { const n = (R() - .5) * 26 * strength; d[i] += n; d[i + 1] += n; d[i + 2] += n; }
    g.putImageData(id, 0, 0);
    // fibres
    g.lineCap = 'round';
    for (let i = 0; i < 900; i++) {
      const x = R() * W, y = R() * H, a = R() * Math.PI * 2, l = 6 + R() * 26;
      g.strokeStyle = R() < .5 ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)'; g.lineWidth = .6 + R();
      g.beginPath(); g.moveTo(x, y); g.quadraticCurveTo(x + Math.cos(a + .6) * l * .5, y + Math.sin(a + .6) * l * .5, x + Math.cos(a) * l, y + Math.sin(a) * l); g.stroke();
    }
    return c;
  };

  // film grain tile; drawn with a frame-seeded offset
  K.grainTile = (seed = 3, size = 256, amp = 60) => {
    const R = K.rng(seed), c = K.canvas(size, size), g = c.getContext('2d'), id = g.createImageData(size, size);
    for (let i = 0; i < size * size; i++) { const v = 128 + (R() - .5) * amp * 2; id.data.set([v, v, v, 255], i * 4); }
    g.putImageData(id, 0, 0); return c;
  };
  K.drawGrain = (g, tile, W, H, frame, alpha = .08, op = 'overlay') => {
    const R = K.rng(frame * 7 + 1), ox = -Math.floor(R() * tile.width), oy = -Math.floor(R() * tile.height);
    g.save(); g.globalAlpha = alpha; g.globalCompositeOperation = op;
    for (let y = oy; y < H; y += tile.height) for (let x = ox; x < W; x += tile.width) g.drawImage(tile, x, y);
    g.restore();
  };
  K.vignette = (g, W, H, inner = .55, color = 'rgba(0,0,0,.55)') => {
    const gr = g.createRadialGradient(W / 2, H / 2, Math.min(W, H) * inner * .6, W / 2, H / 2, Math.hypot(W, H) * .55);
    gr.addColorStop(0, 'rgba(0,0,0,0)'); gr.addColorStop(1, color);
    g.fillStyle = gr; g.fillRect(0, 0, W, H);
  };

  // lon/lat polyline → Catmull-Rom smooth, with cumulative length for partial drawing
  K.smoothLine = (pts, per = 10) => {
    const out = [];
    for (let i = 0; i < pts.length - 1; i++) {
      const p0 = pts[Math.max(0, i - 1)], p1 = pts[i], p2 = pts[i + 1], p3 = pts[Math.min(pts.length - 1, i + 2)];
      for (let s = 0; s < per; s++) {
        const t = s / per, t2 = t * t, t3 = t2 * t;
        out.push([0, 1].map(k => .5 * ((2 * p1[k]) + (-p0[k] + p2[k]) * t + (2 * p0[k] - 5 * p1[k] + 4 * p2[k] - p3[k]) * t2 + (-p0[k] + 3 * p1[k] - 3 * p2[k] + p3[k]) * t3)));
      }
    }
    out.push(pts[pts.length - 1]);
    const cum = [0];
    for (let i = 1; i < out.length; i++) cum.push(cum[i - 1] + Math.hypot(out[i][0] - out[i - 1][0], out[i][1] - out[i - 1][1]));
    return { pts: out, cum, total: cum[cum.length - 1] };
  };
  K.partial = (line, f) => {
    if (f <= 0) return [];
    const L = line.total * Math.min(1, f), out = [line.pts[0]];
    for (let i = 1; i < line.pts.length; i++) {
      if (line.cum[i] <= L) { out.push(line.pts[i]); continue; }
      const a = line.pts[i - 1], b = line.pts[i], u = (L - line.cum[i - 1]) / (line.cum[i] - line.cum[i - 1]);
      out.push([K.lerp(a[0], b[0], u), K.lerp(a[1], b[1], u)]); break;
    }
    return out;
  };
  K.polyline = (g, pts) => { g.beginPath(); pts.forEach((p, i) => i ? g.lineTo(p[0], p[1]) : g.moveTo(p[0], p[1])); };

  // letter-spaced text (canvas letterSpacing where supported)
  K.text = (g, str, x, y, { font, color = '#000', align = 'left', base = 'alphabetic', spacing = 0, alpha = 1, shadow, stroke, strokeW = 0 } = {}) => {
    g.save(); g.globalAlpha *= alpha; g.font = font; g.textAlign = align; g.textBaseline = base;
    if ('letterSpacing' in g) g.letterSpacing = spacing + 'px';
    if (shadow) { g.shadowColor = shadow[0]; g.shadowBlur = shadow[1]; g.shadowOffsetX = shadow[2] || 0; g.shadowOffsetY = shadow[3] || 0; }
    if (stroke) { g.lineJoin = 'round'; g.strokeStyle = stroke; g.lineWidth = strokeW; g.strokeText(str, x, y); g.shadowColor = 'transparent'; }
    g.fillStyle = color; g.fillText(str, x, y);
    g.restore();
  };
  K.fmt = n => Math.round(n).toLocaleString('en-US');

  window.K = K;
})();
