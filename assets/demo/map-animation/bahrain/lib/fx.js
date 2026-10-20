// Strike effects on the map, all drawn from t alone (seek / frame-step / posterize stay exact): missiles with glowing
// heads and smoke trails, drones, launch flashes, impacts (flash, fireball, shock ring), fires that keep burning,
// smoke that rises and drifts, and a short camera shake on each hit.
// Light (flashes, fire, missile heads) goes to the additive canvas api.C2.ad; smoke and drones to api.C2.ta (in the map,
// under the labels). Sizes are in screen px times opts.k, so one page sets the scale for its zoom.
(function () {
  const FX = {}, cl = K.clamp, E = K.ease;
  const hs = (a, b = 0) => { const s = Math.sin(a * 127.1 + b * 311.7) * 43758.5453; return s - Math.floor(s); };   // stable noise 0..1
  FX.hs = hs;
  const flick = (t, s) => .62 + .2 * Math.sin(t * 23.0 + s * 7.1) + .18 * Math.sin(t * 37.3 + s * 3.3) * Math.sin(t * 9.1 + s);

  // ── soft sprites, made once ──
  let SP = null;
  const radial = (size, stops) => {
    const c = K.canvas(size, size), g = c.getContext('2d'), r = size / 2, gr = g.createRadialGradient(r, r, 0, r, r, r);
    stops.forEach(([o, col]) => gr.addColorStop(o, col)); g.fillStyle = gr; g.fillRect(0, 0, size, size); return c;
  };
  // an irregular smoke puff: soft blobs inside one circle
  const puff = (seed, tone, size = 128, al = .4) => {
    const c = K.canvas(size, size), g = c.getContext('2d'), r = size / 2;
    for (let i = 0; i < 10; i++) {
      const a = hs(seed, i) * 6.283, d = hs(seed, i + 20) * r * .36, rr = r * (.36 + hs(seed, i + 40) * .26);
      const x = r + Math.cos(a) * d, y = r + Math.sin(a) * d, gr = g.createRadialGradient(x, y, 0, x, y, rr);
      gr.addColorStop(0, `rgba(${tone},${al})`); gr.addColorStop(.55, `rgba(${tone},${al * .42})`); gr.addColorStop(1, `rgba(${tone},0)`);
      g.fillStyle = gr; g.beginPath(); g.arc(x, y, rr, 0, 7); g.fill();
    }
    return c;
  };
  const sprites = () => SP || (SP = {
    hot: radial(128, [[0, 'rgba(255,255,244,1)'], [.1, 'rgba(255,244,200,.95)'], [.28, 'rgba(255,178,80,.6)'], [.55, 'rgba(255,98,24,.2)'], [1, 'rgba(255,60,0,0)']]),
    glow: radial(128, [[0, 'rgba(255,160,70,.85)'], [.35, 'rgba(255,104,34,.38)'], [1, 'rgba(255,60,10,0)']]),
    core: radial(64, [[0, 'rgba(255,255,255,1)'], [.45, 'rgba(255,250,232,.75)'], [1, 'rgba(255,240,200,0)']]),
    white: [1, 2, 3, 4].map(s => puff(s, '236,234,230')),
    dark: [11, 12, 13, 14].map(s => puff(s, '140,134,128', 128, .56)),   // thick mid grey: darker than sand, lighter than the sea
  });
  const spr = (g, img, x, y, r, a) => { if (a <= .004 || r <= .2) return; g.globalAlpha = Math.min(1, a); g.drawImage(img, x - r, y - r, r * 2, r * 2); };

  // ground ellipse squash for the camera tilt (a ring on the ground reads as lying flat)
  const squash = api => Math.max(.35, Math.cos(((api.cam && api.cam.pitch) || 0) * Math.PI / 180));

  // ── smoke that rises from a point and drifts ──
  // from / to: emission window (s); rate: puffs per s; life: s; rise: px/s at the start; wind: [px/s, px/s]; r0, grow: px, px/s
  FX.smoke = (api, x, y, t, { from, to = 1e9, rate = 9, life = 3.2, rise = 40, wind = [14, -4], r0 = 7, grow = 13, a = .55, dark = true, seed = 1, k = 1, spread = 5 } = {}) => {
    if (t <= from) return;
    const g = api.C2.ta.g, S = sprites(), set = dark ? S.dark : S.white;
    const i0 = Math.max(0, Math.floor((t - life - from) * rate)), i1 = Math.floor((Math.min(t, to) - from) * rate);
    g.save();
    for (let i = i0; i <= i1; i++) {
      const b = from + i / rate, age = t - b; if (age < 0 || age > life) continue;
      const u = age / life, h1 = hs(seed * 13 + i, 1), h2 = hs(seed * 13 + i, 2);
      const up = rise * k * (1 - Math.exp(-age * 1.1)) / 1.1;            // rises fast, then slows
      const px = x + (h1 - .5) * spread * 2 * k + wind[0] * k * age + Math.sin(age * 1.7 + h2 * 6) * 3 * k;
      const py = y - up + wind[1] * k * age;
      spr(g, set[i % 4], px, py, (r0 + grow * age) * k * (.8 + .4 * h2), a * Math.pow(1 - u, 1.4) * Math.min(1, age * 4));
    }
    g.restore();
  };

  // ── a bright flash that blooms and dies (launch, hit) ──
  FX.flash = (api, x, y, age, { r = 70, dur = .45, k = 1, a = 1 } = {}) => {
    if (age < 0 || age > dur) return;
    const g = api.C2.ad.g, S = sprites(), u = age / dur;
    g.save(); g.globalCompositeOperation = 'lighter';
    spr(g, S.hot, x, y, r * k * (.45 + .9 * E.out(u)), a * Math.pow(1 - u, 1.6));
    spr(g, S.core, x, y, r * k * .4 * (1 - u * .5), a * Math.pow(1 - u, 2.2));
    g.restore();
  };

  // ── shock ring on the ground ──
  FX.ring = (api, x, y, age, { r = 70, dur = .75, k = 1, a = .9 } = {}) => {
    if (age < 0 || age > dur) return;
    const g = api.C2.ad.g, u = age / dur, rr = r * k * E.out(u), sq = squash(api);
    g.save(); g.globalCompositeOperation = 'lighter'; g.globalAlpha = a * Math.pow(1 - u, 1.3);
    g.strokeStyle = 'rgb(255,214,160)'; g.lineWidth = (1 + 3.5 * (1 - u)) * k;
    g.beginPath(); g.ellipse(x, y, rr, rr * sq, 0, 0, 7); g.stroke();
    g.restore();
  };

  // ── fireball that swells, rises a little and burns down ──
  FX.fireball = (api, x, y, age, { r = 26, dur = 1.6, k = 1, seed = 1 } = {}) => {
    if (age < 0 || age > dur) return;
    const g = api.C2.ad.g, S = sprites(), u = age / dur;
    g.save(); g.globalCompositeOperation = 'lighter';
    for (let i = 0; i < 6; i++) {
      const ang = hs(seed, i) * 6.283, d = r * k * .55 * hs(seed, i + 9) * E.out(cl(age / .4));
      const bx = x + Math.cos(ang) * d, by = y + Math.sin(ang) * d * .6 - r * k * .5 * age;
      spr(g, i < 2 ? S.hot : S.glow, bx, by, r * k * (.55 + .6 * E.out(cl(age / .35))) * (.8 + .4 * hs(seed, i + 3)), Math.pow(1 - u, 1.3) * flick(age * 2, seed + i));
    }
    g.restore();
  };

  // ── a fire that keeps burning on the ground (flickering glow) ──
  FX.fire = (api, x, y, t, { from, r = 13, k = 1, seed = 1, a = 1 } = {}) => {
    if (t <= from) return;
    const g = api.C2.ad.g, S = sprites(), on = E.out(cl((t - from) / .5)) * a;
    g.save(); g.globalCompositeOperation = 'lighter';
    spr(g, S.glow, x, y, r * k * 6, .22 * on * (.85 + .15 * flick(t * .5, seed)));      // the ground around it lit orange
    spr(g, S.glow, x, y - 2 * k, r * k * 2.6 * (.9 + .1 * flick(t, seed)), .45 * on);
    for (let i = 0; i < 4; i++) {
      const f = flick(t * (1 + i * .13), seed * 3 + i), ox = (hs(seed, i + 30) - .5) * r * k * 1.4;
      spr(g, S.hot, x + ox, y - (2 + 5 * hs(seed, i + 50) + 4 * f) * k, r * k * (.55 + .5 * f), .7 * on * f);
    }
    g.restore();
  };

  // ── impact: flash + shock ring + fireball, then (optional) a fire and a smoke plume ──
  FX.impact = (api, lon, lat, t, th, { k = 1, seed = 1, fire = true, smoke = true, wind, big = 1, smokeTo = 1e9 } = {}) => {
    if (t < th) return;
    const p = api.P(lon, lat); if (!p) return;
    const age = t - th, kk = k * big;
    if (smoke) FX.smoke(api, p[0], p[1], t, { from: th + .1, to: smokeTo, k: kk, seed, wind, rate: 14, life: 4.4, rise: 70, r0: 10, grow: 15, a: 1 });
    if (fire) {
      FX.fire(api, p[0], p[1], t, { from: th + .25, k: kk, seed, r: 17 });
      for (let i = 0; i < 2; i++) FX.fire(api, p[0] + (hs(seed, i + 60) - .5) * 40 * kk, p[1] + (hs(seed, i + 70) - .5) * 22 * kk, t, { from: th + .6 + i * .35, k: kk * .7, seed: seed + i + 1, r: 15 });
    }
    FX.fireball(api, p[0], p[1], age, { k: kk, seed, r: 40 });
    FX.ring(api, p[0], p[1], age, { k: kk, r: 100 });
    FX.flash(api, p[0], p[1], age, { k: kk, r: 125 });
  };

  // ── launch: a short flash and a cloud of white smoke that lingers ──
  FX.launch = (api, lon, lat, t, t0, { k = 1, seed = 1, wind = [10, -3] } = {}) => {
    if (t < t0) return;
    const p = api.P(lon, lat); if (!p) return;
    FX.smoke(api, p[0], p[1], t, { from: t0, to: t0 + .6, rate: 28, life: 3, rise: 22, wind, r0: 9, grow: 20, a: .55, dark: false, seed, k, spread: 12 });
    FX.flash(api, p[0], p[1], t - t0, { k, r: 75, dur: .45 });
  };

  // ── missile: A → B (lon/lat), launched at t0, dur s in the air, arcing up on screen ──
  // arc: peak height as a share of the screen distance; accel: share of speed gained along the way
  FX.missile = (api, m, t, { k = 1, trail = .34, smoke = 1.9 } = {}) => {
    const u = (t - m.t0) / m.dur;
    if (u <= 0) return;
    const pa = api.P(...m.A), pb = api.P(...m.B); if (!pa || !pb) return;
    const L = Math.hypot(pb[0] - pa[0], pb[1] - pa[1]), arc = (m.arc ?? .22) * L, acc = m.accel ?? .35;
    const vOf = q => q * (1 - acc) + q * q * acc;
    const pos = q => { const v = vOf(q), p = api.P(m.A[0] + (m.B[0] - m.A[0]) * v, m.A[1] + (m.B[1] - m.A[1]) * v); return p && [p[0], p[1] - arc * 4 * v * (1 - v)]; };
    const N = 40, uh = Math.min(1, u);
    // smoke trail on the map: each point fades `smoke` s after the missile passed it, and widens as it fades
    if (t - m.t0 - m.dur < smoke) {
      const g = api.C2.ta.g; g.save(); g.lineCap = 'round'; g.strokeStyle = 'rgb(232,228,222)';
      let prev = pos(0);
      for (let i = 1; i <= N; i++) {
        const q = uh * i / N, p = pos(q); if (!p || !prev) { prev = p; continue; }
        const age = t - (m.t0 + q * m.dur), f = 1 - age / smoke;
        if (f > 0) { g.globalAlpha = .46 * f * Math.min(1, age * 6 + .2); g.lineWidth = (2.4 + age * 7) * k; g.beginPath(); g.moveTo(prev[0], prev[1]); g.lineTo(p[0], p[1]); g.stroke(); }
        prev = p;
      }
      g.restore();
    }
    if (u >= 1) return;
    // glowing streak behind the head, then the head itself
    const g = api.C2.ad.g, S = sprites();
    g.save(); g.globalCompositeOperation = 'lighter'; g.lineCap = 'round';
    const q0 = Math.max(0, u - trail);
    let prev = pos(q0);
    for (let i = 1; i <= 16; i++) {
      const q = q0 + (u - q0) * i / 16, p = pos(q); if (!p || !prev) { prev = p; continue; }
      const w = i / 16;
      g.globalAlpha = .9 * w * w; g.strokeStyle = `rgb(255,${Math.round(150 + 90 * w)},${Math.round(70 + 120 * w)})`; g.lineWidth = (2 + 5 * w) * k;
      g.beginPath(); g.moveTo(prev[0], prev[1]); g.lineTo(p[0], p[1]); g.stroke(); prev = p;
    }
    const h = pos(u);
    if (h) { spr(g, S.hot, h[0], h[1], 34 * k * (.9 + .1 * Math.sin(t * 40 + m.t0 * 7)), 1); spr(g, S.core, h[0], h[1], 9 * k, 1); }
    g.restore();
  };

  // ── drone: a small delta wing flying straight A → B, with a faint wake and a blinking light ──
  FX.drone = (api, d, t, { k = 1 } = {}) => {
    const u = (t - d.t0) / d.dur;
    if (u <= 0 || u >= 1) return;
    const pos = q => api.P(d.A[0] + (d.B[0] - d.A[0]) * q, d.A[1] + (d.B[1] - d.A[1]) * q);
    const p = pos(u), q = pos(Math.max(0, u - .02)); if (!p || !q) return;
    const ang = Math.atan2(p[1] - q[1], p[0] - q[0]), sway = Math.sin(t * 2.3 + d.t0) * 1.5 * k;
    const x = p[0] - Math.sin(ang) * sway, y = p[1] + Math.cos(ang) * sway;
    const g = api.C2.ta.g;
    // wake
    const w0 = pos(Math.max(0, u - .22));
    if (w0) { const gr = g.createLinearGradient(w0[0], w0[1], x, y); gr.addColorStop(0, 'rgba(240,240,240,0)'); gr.addColorStop(1, 'rgba(240,240,240,.45)');
      g.save(); g.strokeStyle = gr; g.lineWidth = 1.4 * k; g.beginPath(); g.moveTo(w0[0], w0[1]); g.lineTo(x, y); g.stroke(); g.restore(); }
    // body (delta wing, nose forward): dark with a light edge and a soft shadow on the ground
    const s = 14 * k;
    const wing = (gg, ox, oy) => { gg.save(); gg.translate(x + ox, y + oy); gg.rotate(ang); gg.beginPath(); gg.moveTo(s, 0); gg.lineTo(-s * .75, s * .78); gg.lineTo(-s * .45, 0); gg.lineTo(-s * .75, -s * .78); gg.closePath(); return gg; };
    g.save(); g.globalAlpha = .28; g.fillStyle = '#000'; wing(g, 3 * k, 6 * k).fill(); g.restore(); g.restore();
    const w = wing(g, 0, 0); w.fillStyle = 'rgb(38,40,44)'; w.fill(); w.strokeStyle = 'rgba(255,255,255,.85)'; w.lineWidth = 1.1 * k; w.stroke(); w.restore();
    const ad = api.C2.ad.g; ad.save(); ad.globalCompositeOperation = 'lighter';
    spr(ad, sprites().core, x - Math.cos(ang) * s * .5, y - Math.sin(ang) * s * .5, 4 * k, (Math.sin(t * 14 + d.t0 * 5) > .3 ? .9 : .2));
    ad.restore();
  };

  // ── camera shake after hits: [[t, strength], ...] → [sx, sy] px (smooth noise, dies in ~0.6 s) ──
  FX.shake = (t, hits, amp = 7) => {
    let x = 0, y = 0;
    hits.forEach(([th, s = 1], i) => {
      const a = t - th; if (a < 0 || a > 1.2) return;
      const d = amp * s * Math.exp(-a * 6) * Math.min(1, a * 40);
      x += d * (Math.sin(a * 71 + i * 1.3) * .6 + Math.sin(a * 113 + i * 2.1) * .4);
      y += d * (Math.sin(a * 83 + i * .7) * .6 + Math.sin(a * 127 + i * 1.7) * .4);
    });
    return [x, y];
  };
  // wraps a camera track: the same moves plus the shake
  FX.shaken = (cam, hits, amp) => t => { const c = cam(t), s = FX.shake(t, hits, amp); c.sx = (c.sx || 0) + s[0]; c.sy = (c.sy || 0) + s[1]; return c; };

  window.FX = FX;
})();
