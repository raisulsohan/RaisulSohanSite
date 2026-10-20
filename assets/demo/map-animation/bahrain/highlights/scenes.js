// Built by tools/build_live_scenes.js from the part pages (do not edit here): the scene of every part that
// highlights/edl.js cuts from. Load it after defining window.Part (see highlights-live.html).

// ── 01-hook.html
(function () {
// 0:00 "What if I told you that around one and a half million people live on a desert archipelago
//       with no permanent rivers, no lakes, and barely any rain?"
// Hook, version 2 (approved 20 Oct; the first version is kept in 01-hook-v1.html; same narration, length and start time).
// The first three seconds are a satellite lock-on in three punch-ins, one on each beat of the question:
//   "What if I told you" — a fast dive from a continent-wide view; the HUD locks on a red dot, the latitude reads out
//   "that around"        — punch-in on the Gulf, the HUD locks on again
//   "one and a half million people" — punch-in on the island (frame 50, as v1's stat); a counter rolls 0 → 1,500,000
//   and lands on "people": the island turns red and 1,500 lights switch on in a wave from Manama.
// Then each fact is shown by taking it away: a river draws on and dries into steam, a lake grows and shrinks to a salt
// flat, rain falls and dries in the air before it reaches the ground (a few drops land). The name stays back (part 02).
(function () {
  const FPS = 30000 / 1001;
  const CUT1 = 30.5 / FPS, CUT2 = 49.5 / FPS;       // first frames of shot 2 (31, "that") and shot 3 (50, the stat)
  const TGT = [50.55, 26.06];                        // lock-on point: the middle of the main island
  const RIVER = [[50.505, 26.205], [50.53, 26.17], [50.515, 26.13], [50.545, 26.09], [50.525, 26.05], [50.555, 26.01],
    [50.535, 25.97], [50.56, 25.93], [50.57, 25.88]];  // an imagined river, north to south, inside the main island
  const LAKE = [50.535, 26.0];
  const DROPS = [[50.588, 26.17], [50.505, 26.1], [50.575, 25.985], [50.53, 26.135]];   // the few drops that land
  const dropT = w => [w('barely') + .3, w('any') + .22, w('rain') + .28, w('rain') + .78];

  Part('01', {
    title: 'Hook',
    layers: ['world', 'region', 's2_gulf', 's2_bahrain'],
    cues: ({ w }) => {
      const tp = w('people');
      return [
        [.02, 'whoosh', .95, .34],                       // the dive starts on frame 0
        [.1, 'type', 9, .42, .1],                        // the readout scrambles
        [.43, 'blip', 1.2, .12],                         // lock
        [CUT1 - .1, 'whoosh', .45, .3], [CUT1, 'hit', .45], [CUT1 + .36, 'blip', 1.35, .11],
        [CUT2 - .1, 'whoosh', .5, .32], [CUT2, 'hit', .6],
        [CUT2 + .04, 'type', 16, tp - CUT2 - .1, .08],   // digits roll
        [CUT2 + .1, 'riser', tp - CUT2 - .12, .22],
        [tp - .01, 'hit', 1], [tp, 'shimmer', .16],      // the number lands, the lights switch on
        [w('archipelago') - .05, 'tick'],                // the coastline starts to draw
        [w('with') + .02, 'tick'],                       // the river starts to draw
        [w('rivers') - .15, 'pop', 1, .18], [w('rivers') - .05, 'whoosh', .9, .1],
        [w('no', 2) - .02, 'blip', .9, .08], [w('lakes') - .1, 'pop', 1.1, .18], [w('lakes') + .12, 'whoosh', .8, .09],
        [w('and', 2) - .05, 'whoosh', 1.3, .12], [w('barely') - .05, 'pop', 1.2, .18],
        ...dropT(w).map(d => [d, 'tick', .12]),
      ];
    },
    async scene({ w, P, info }) {
      const E = K.ease, cl = K.clamp, VERT = info.VERT, hs = FX.hs;
      const BH = GEO.bahrain.now, URB = GEO.bahrain.urban;
      const T = {
        people: w('people'), arch: w('archipelago'), with: w('with'), rivers: w('rivers'), no2: w('no', 2),
        lakes: w('lakes'), and2: w('and', 2), barely: w('barely'),
      };
      // layout: the stat column on the left in 16:9, under the island in 4:5
      const L = VERT
        ? { sx: 0, sy: -250, dz: -.45, x: 70, y: 900, size: 92, lab: 34, fy: 1050, fg: 56, fs: 32 }
        : { sx: 250, sy: 10, dz: 0, x: 110, y: 410, size: 116, lab: 40, fy: 610, fg: 68, fs: 38 };

      // ── cameras: three shots, hard cuts on the beats; each shot starts fast and settles (a punch-in)
      const C = (lon, lat, zoom, pitch, bearing, o) => ({ lon, lat, zoom, pitch, bearing, sx: 0, sy: 0, ...o });
      const off = { sx: L.sx, sy: L.sy };
      const shots = [
        GM.track([[0, C(...TGT, 4.85, 36, 16), 'go'], [.42, C(...TGT, 5.8, 40, 10)], [CUT1, C(...TGT, 6.12, 41, 8), 'go']]),
        GM.track([[CUT1, C(...TGT, 7.7, 44, 3), 'go'], [CUT1 + .3, C(...TGT, 8.15, 45, 1)], [CUT2, C(...TGT, 8.38, 45, 0), 'go']]),
        // close and tilted on the dense north while the number rolls; on "people" it stops, then pulls back fast and
        // shows the whole island as the lights spread; a slow orbit to the end
        GM.track([
          [CUT2, C(50.585, 26.2, 12.25 + L.dz, 56, -18, off), 'go'],
          [CUT2 + .35, C(50.58, 26.19, 12.5 + L.dz, 55, -14, off)],
          [T.people - .05, C(50.575, 26.18, 12.6 + L.dz, 54, -11, off)],
          [T.people + .75, C(50.55, 26.05, 11.85 + L.dz, 47, -5, off)],
          [T.with, C(50.545, 26.04, 11.7 + L.dz, 44, 0, off)],
          [P.dur, C(50.545, 26.035, 11.62 + L.dz, 42, 6, off), 'go'],
        ]),
      ];
      const shotOf = t => t < CUT1 ? 0 : t < CUT2 ? 1 : 2;
      const base = t => shots[shotOf(t)](t);
      const cam = FX.shaken(base, [[T.people, 1]], 6);

      // ── the people: 1,500 lights (one per ~1,000 people), most in the built-up north, switched on in a wave from Manama
      const inside = (rings, x, y) => {
        let c = false;
        for (const r of rings) for (let i = 0, j = r.length - 2; i < r.length; j = i, i += 2) {
          const yi = r[i + 1], yj = r[j + 1];
          if ((yi > y) !== (yj > y) && x < (r[j] - r[i]) * (y - yi) / (yj - yi) + r[i]) c = !c;
        }
        return c;
      };
      const bbox = rings => { const b = [1, 1, 0, 0]; for (const r of rings) for (let i = 0; i < r.length; i += 2) { b[0] = Math.min(b[0], r[i]); b[1] = Math.min(b[1], r[i + 1]); b[2] = Math.max(b[2], r[i]); b[3] = Math.max(b[3], r[i + 1]); } return b; };
      const R = K.rng(11), DOTS = [], MX = GM.mx(PLACES.manama[0]), MY = GM.my(PLACES.manama[1]);
      // the main island and the islands next to it (Muharraq, Sitra, reclaimed land); not the far southern Hawar group
      const ub = bbox(URB), bb = bbox(BH), ySouth = GM.my(25.78), xEast = GM.mx(50.73);
      bb[3] = Math.min(bb[3], ySouth); bb[2] = Math.min(bb[2], xEast);
      for (let k = 0, n = 0; n < 1120 && k < 400000; k++) {
        const x = ub[0] + R() * (ub[2] - ub[0]), y = ub[1] + R() * (ub[3] - ub[1]);
        if (inside(URB, x, y) && inside(BH, x, y)) { DOTS.push({ x, y }); n++; }
      }
      for (let k = 0, n = 0; n < 380 && k < 400000; k++) {
        const x = bb[0] + R() * (bb[2] - bb[0]), y = bb[1] + R() * (bb[3] - bb[1]);
        if (inside(BH, x, y) && !inside(URB, x, y)) { DOTS.push({ x, y }); n++; }
      }
      const DMAX = Math.max(...DOTS.map(d => Math.hypot(d.x - MX, d.y - MY)));
      const WAVE = .85, tW = T.people - .03;
      const COLS = ['255,231,176', '255,231,176', '255,231,176', '255,210,63', '255,255,255'];
      DOTS.forEach((d, i) => {
        d.tb = tW + WAVE * Math.pow(Math.hypot(d.x - MX, d.y - MY) / DMAX, .8) + .06 * R();
        d.r = 1.3 + 1.3 * R() * R(); d.ph = R() * 6.28; d.f = 2 + 3 * R(); d.col = COLS[i % COLS.length];
      });
      const sprite = (size, stops) => { const c = K.canvas(size, size), g = c.getContext('2d'), gr = g.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2); stops.forEach(([o, s]) => gr.addColorStop(o, s)); g.fillStyle = gr; g.fillRect(0, 0, size, size); return c; };
      const GLOW = sprite(64, [[0, 'rgba(255,214,140,.9)'], [.35, 'rgba(255,170,70,.35)'], [1, 'rgba(255,120,30,0)']]);
      const CORE = sprite(32, [[0, 'rgba(255,255,250,1)'], [.45, 'rgba(255,240,200,.9)'], [1, 'rgba(255,220,150,0)']]);

      const RV = K.smoothLine(RIVER.map(([lo, la]) => [GM.mx(lo), GM.my(la)]), 12);
      const mixRGB = (a, b, u) => a.map((v, i) => Math.round(v + (b[i] - v) * u));

      // the readout: scrambles, then settles on the coordinates (a nod to the channel's name)
      const readout = (t, t0, f) => {
        const real = ['26.06° N', '50.55° E'];
        if (t - t0 >= .42) return real;
        const RR = K.rng(f * 31 + 7);
        return real.map(s => s.replace(/\d/g, () => String(Math.floor(RR() * 10))));
      };
      const brackets = (g, x, y, h, a, col) => {
        if (a <= 0) return;
        const arm = Math.max(12, h * .3);
        g.save(); g.globalAlpha = cl(a); g.lineWidth = 3; g.lineCap = 'square';
        for (const [o, c] of [[2, 'rgb(22,32,38)'], [0, col]]) {
          g.strokeStyle = c;
          for (const [sx, sy] of [[-1, -1], [1, -1], [1, 1], [-1, 1]]) {
            const cx = x + sx * h + o, cy = y + sy * h + o;
            g.beginPath(); g.moveTo(cx - sx * arm, cy); g.lineTo(cx, cy); g.lineTo(cx, cy - sy * arm); g.stroke();
          }
        }
        g.restore();
      };

      return {
        cam, cuts: [CUT1, CUT2],
        pre(api, c, t) {
          const shot = shotOf(t);
          api.look.vig = shot < 2 ? [.74, .3, 1.0] : [.62, .38, 1.02];
          api.look.landSat = 1; api.look.landGain = .95;   // a little more colour than the other parts: the first impression
          // a two-frame white flash on each cut
          const since = shot === 1 ? t - CUT1 : shot === 2 ? t - CUT2 : 99;
          api.look.fade = [1, 1, 1, since < 1 ? .3 * Math.max(0, 1 - since / (2.5 / FPS)) : 0];
        },
        draw(api, c, t) {
          const g = api.C2.ov.g, ad = api.C2.ad.g, W = api.W, H = api.H, shot = shotOf(t), f = Math.round(t * FPS);
          const tp = api.P(...TGT);

          // ── shot 1: orientation names, on screen from frame 0 (no entry animation), until the cut
          if (shot === 0) {
            for (const [lon, lat, txt] of [[49.3, 28.8, 'PERSIAN GULF'], [54.2, 31.4, 'IRAN'], [45.2, 23.4, 'SAUDI ARABIA']]) {
              const p = UI.on(api, lon, lat, 300); if (!p) continue;
              UI.bigName(g, p[0], p[1], txt, { a: 1, size: 34, color: 'rgba(255,255,255,.55)', still: true });
            }
          }

          // ── the lock-on HUD (shots 1 and 2): crosshair, brackets that close in on a red dot, the readout
          if (shot < 2 && tp) {
            const t0 = shot === 0 ? 0 : CUT1, u = cl((t - t0) / .45);
            const h = shot === 0 ? K.lerp(330, 44, E.outExpo(u)) : K.lerp(300, 92, E.outExpo(u));
            const lock = UI.env(t, t0 + .42, t0 + .58, .05), col = lock > .5 ? '#FFD23F' : '#ffffff';
            g.save(); g.strokeStyle = 'rgba(255,255,255,.34)'; g.lineWidth = 1.2;
            const gap = h + 12;
            g.beginPath();
            g.moveTo(0, tp[1]); g.lineTo(tp[0] - gap, tp[1]); g.moveTo(tp[0] + gap, tp[1]); g.lineTo(W, tp[1]);
            g.moveTo(tp[0], 0); g.lineTo(tp[0], tp[1] - gap); g.moveTo(tp[0], tp[1] + gap); g.lineTo(tp[0], H);
            g.stroke(); g.restore();
            UI.pulse(g, tp[0], tp[1], t, { r: 30, dot: 5.5, period: 1.1 });
            brackets(g, tp[0], tp[1], h * (1 + .06 * lock), 1, col);
            const [l1, l2] = readout(t, t0, f);
            UI.text3d(g, l1, tp[0] + h + 18, tp[1] - h + 12, { size: 22, weight: 800, spacing: 2, fill: lock > .5 ? '#FFD23F' : '#fff' });
            UI.text3d(g, l2, tp[0] + h + 18, tp[1] - h + 40, { size: 22, weight: 800, spacing: 2, fill: lock > .5 ? '#FFD23F' : '#fff' });
          }
          // shot 3 opens through the reticle: the brackets fly out of the frame
          if (shot === 2 && tp && t < CUT2 + .4) {
            const u = cl((t - CUT2) / .36);
            brackets(g, tp[0], tp[1], K.lerp(150, 1300, E.in(u)), 1 - u, '#ffffff');
          }

          if (shot < 2) return;

          // ── the island turns red as the number lands (traced from the photo, raised, white edge)
          const hi = E.inOut(cl((t - (T.people - .08)) / .4));
          UI.highlight(api, BH, { color: '#b3141b', a: hi * .95, onLand: false, lift: .75, bevel: 1, edge: .3 });
          // the coastline draws on in white on "archipelago"
          const fo = E.inOut(cl((t - (T.arch - .05)) / 1.0));
          UI.outline(api, g, BH, fo, { width: 2, glow: 12, alpha: .95 * (1 - .55 * cl((t - T.arch - 1.2) / .8)) });

          // ── the lights: a gold wave front runs out from Manama and switches them on
          const age = t - tW;
          if (age > 0) {
            const zs = cl(Math.pow(2, (c.zoom - 11.7 - L.dz) * .85), .55, 1.6);
            ad.save(); ad.globalCompositeOperation = 'lighter';
            for (const d of DOTS) {
              const a = t - d.tb; if (a <= 0) continue;
              const p = api.Pm(d.x, d.y); if (!p || p[0] < -20 || p[0] > W + 20 || p[1] < -20 || p[1] > H + 20) continue;
              const gr = E.outBack(cl(a / .22)), tw = .8 + .2 * Math.sin(t * d.f + d.ph), r = d.r * zs * gr;
              ad.globalAlpha = .18 * tw; ad.drawImage(GLOW, p[0] - r * 3.4, p[1] - r * 3.4, r * 6.8, r * 6.8);
              ad.globalAlpha = .95 * tw; ad.drawImage(CORE, p[0] - r * 1.3, p[1] - r * 1.3, r * 2.6, r * 2.6);
            }
            // the wave front, only over the land it lights up
            if (age < WAVE + .35) {
              const rr = DMAX * Math.pow(cl(age / WAVE), 1 / .8), wa = .95 * (1 - cl((age - WAVE * .6) / (WAVE * .4 + .35)));
              ad.save(); UI.path(ad, api, BH); ad.clip('evenodd');
              ad.globalAlpha = wa; ad.strokeStyle = 'rgb(255,214,130)'; ad.lineWidth = 4; ad.shadowColor = 'rgb(255,190,90)'; ad.shadowBlur = 16;
              ad.beginPath();
              for (let i = 0; i <= 96; i++) { const th = i / 96 * 6.2832, p = api.Pm(MX + rr * Math.cos(th), MY + rr * Math.sin(th)); if (p) i ? ad.lineTo(p[0], p[1]) : ad.moveTo(p[0], p[1]); }
              ad.stroke(); ad.restore();
            }
            ad.restore();
          }

          // ── the counter: on screen from frame 50, rolls up to 1,500,000 and lands on "people" with a punch
          const sa = UI.env(t, CUT2, 99, .2);
          if (sa > 0) {
            const tL = T.people - .04, u = cl((t - CUT2) / (tL - CUT2));
            const n = 1.5e6 * (1 - Math.pow(1 - u, 3.2));
            const val = '≈' + K.fmt(t >= tL ? 1.5e6 : n);
            const punch = t >= tL ? .14 * (1 - E.out(cl((t - tL) / .35))) : 0, k = 1 + punch;
            g.save(); g.translate(L.x, L.y); g.scale(k, k); g.translate(-L.x, -L.y);
            UI.statBlock(g, L.x, L.y, 'people', val, '', { a: sa, size: L.size, align: 'left', final: '≈1,500,000' });
            g.restore();
            const la = UI.env(t, tL, 99, .25);
            UI.text3d(g, 'PEOPLE', L.x, L.y + L.size * .74 + (1 - E.out(la)) * 14, { size: L.lab, weight: 800, fill: '#fff', spacing: L.lab * .12, alpha: la });
          }

          // ── the facts, one line per phrase, under the counter
          [[T.rivers - .15, 'nodrop', '0 PERMANENT RIVERS'], [T.lakes - .1, 'nodrop', '0 LAKES'], [T.barely - .05, 'drop', 'BARELY ANY RAIN']].forEach(([ts, icon, txt], i) => {
            const a = UI.env(t, ts, 99, .35);
            if (a <= 0) return;
            g.save(); g.translate((1 - E.out(a)) * -40, 0);
            UI.stat(g, L.x, L.fy + i * L.fg, icon, txt, { a, size: L.fs, align: 'left', iconColor: '#7fd3ff' });
            g.restore();
          });

          const zs = cl(Math.pow(2, (c.zoom - 11.7 - L.dz) * .9), .6, 1.4);
          // ── "with no permanent rivers": a river draws on in the pause after "with", then dries into steam
          // dries to a pale bed on "rivers", breaks up, and is gone before the lake comes
          const fr = E.inOut(cl((t - (T.with + .02)) / .6)), dry = E.inOut(cl((t - (T.rivers - .1)) / .45));
          const brk = E.inOut(cl((t - (T.rivers + .12)) / .45)), gone = cl((t - (T.no2 - .05)) / .35);
          if (fr > 0 && gone < 1) {
            const pts = K.partial(RV, fr).map(p => api.Pm(p[0], p[1])).filter(Boolean);
            const col = mixRGB([64, 186, 255], [246, 232, 204], dry), wd = (6 - 2 * dry) * zs;
            g.save(); g.lineJoin = g.lineCap = 'round'; g.globalAlpha = 1 - E.in(gone);
            if (brk > 0) g.setLineDash([Math.max(3, 60 * (1 - brk)), 3 + 20 * brk]);
            g.shadowColor = `rgba(${col},.9)`; g.shadowBlur = 16 * (1 - dry); g.strokeStyle = `rgb(${col})`; g.lineWidth = wd;
            K.polyline(g, pts); g.stroke();
            g.shadowBlur = 0; g.strokeStyle = `rgba(225,246,255,${.85 * (1 - dry)})`; g.lineWidth = wd * .34; g.stroke();
            g.restore();
            for (const q of [.12, .3, .48, .66, .84]) {
              const i = Math.round(q * (RV.pts.length - 1)), p = api.Pm(RV.pts[i][0], RV.pts[i][1]); if (!p) continue;
              FX.smoke(api, p[0], p[1], t, { from: T.rivers - .02 + q * .25, to: T.rivers + .6, rate: 10, life: 1.6, rise: 42, wind: [10, -3], r0: 6, grow: 16, a: .55, dark: false, seed: 40 + i, k: zs, spread: 8 });
            }
          }

          // ── "no lakes": a lake grows on "no", shrinks on "lakes" to a pale salt flat and steam
          const lin = E.outBack(cl((t - (T.no2 - .02)) / .4)), lout = E.inOut(cl((t - (T.lakes + .12)) / .6)), ls = lin * (1 - lout);
          const salt = cl((t - (T.lakes + .3)) / .3) * (1 - cl((t - (T.and2 + .4)) / .6));
          const lp = api.P(...LAKE), ppk = api.pxPerKm(...LAKE);
          if ((ls > .001 || salt > 0) && lp && ppk) {
            const blob = (gg, Rk) => {
              gg.beginPath();
              for (let i = 0; i <= 64; i++) { const th = i / 64 * 6.2832, r = Rk * (1 + .14 * Math.sin(3 * th + 1.3) + .08 * Math.sin(5 * th + .4) + .05 * Math.sin(2 * th + 2)); i ? gg.lineTo(r * Math.cos(th), r * Math.sin(th)) : gg.moveTo(r * Math.cos(th), r * Math.sin(th)); }
              gg.closePath();
            };
            const tg = api.C2.ta.g;
            tg.save();
            if (UI.onMap(api, tg, ...LAKE, 1)) {
              if (salt > 0) { blob(tg, 3.0); tg.fillStyle = `rgba(240,231,210,${.55 * salt})`; tg.fill(); }
              if (ls > .001) {
                const gr = tg.createRadialGradient(-.8, -.8, 0, 0, 0, 3.6 * ls);
                gr.addColorStop(0, '#7fd8ff'); gr.addColorStop(.6, '#2f9be0'); gr.addColorStop(1, '#1d6fb8');
                blob(tg, 3.3 * ls); tg.fillStyle = gr; tg.globalAlpha = .95; tg.fill();
                tg.globalAlpha = .85; tg.strokeStyle = '#eaf8ff'; tg.lineWidth = 2.2 / ppk; tg.stroke();
              }
            }
            tg.restore();
            FX.smoke(api, lp[0], lp[1], t, { from: T.lakes + .14, to: T.lakes + .7, rate: 12, life: 1.5, rise: 34, wind: [10, -3], r0: 5, grow: 13, a: .4, dark: false, seed: 77, k: zs, spread: 18 });
          }

          // ── "and barely any rain?": a shower falls and dries in the air before it lands; only a few drops reach the island
          const r0 = T.and2 - .05;
          if (t > r0) {
            g.save(); g.lineCap = 'round'; g.shadowColor = 'rgba(160,214,255,.7)'; g.shadowBlur = 6;
            for (let i = 0; i < 80; i++) {
              const s0 = r0 + .5 * hs(i, 1), u = (t - s0) / .55; if (u < 0 || u > 1) continue;
              const yEnd = H * (.3 + .45 * hs(i, 3)), y = -60 + (yEnd + 60) * u, x = W * (-.05 + 1.25 * hs(i, 2)) - .22 * (y + 60);
              const len = 80 + 60 * hs(i, 4), a = Math.sin(Math.PI * u) * .75 * (1 - .5 * u);
              g.strokeStyle = `rgba(210,235,255,${a})`; g.lineWidth = 2.2;
              g.beginPath(); g.moveTo(x + .22 * len, y - len); g.lineTo(x, y); g.stroke();
            }
            g.restore();
            const sq = Math.max(.35, Math.cos(c.pitch * Math.PI / 180));
            dropT(w).forEach((td, i) => {
              const p = api.P(...DROPS[i]); if (!p) return;
              const u = (t - (td - .38)) / .38;
              if (u >= 0 && u < 1) {   // the drop falls to its spot
                const y = -60 + (p[1] + 60) * E.in(u), x = p[0] + .22 * (p[1] - y);
                g.save(); g.lineCap = 'round'; g.strokeStyle = 'rgba(215,238,255,.9)'; g.lineWidth = 2.2; g.shadowColor = 'rgba(160,214,255,.8)'; g.shadowBlur = 8;
                g.beginPath(); g.moveTo(x + .22 * 80, y - 80); g.lineTo(x, y); g.stroke(); g.restore();
              }
              for (const [dl, rmax] of [[0, 72], [.14, 44]]) {   // ripples where it lands
                const v = (t - td - dl) / .8; if (v < 0 || v > 1) continue;
                g.save(); g.globalAlpha = .95 * (1 - v); g.strokeStyle = 'rgb(214,240,255)'; g.lineWidth = 2.6; g.shadowColor = 'rgba(150,210,255,.8)'; g.shadowBlur = 8;
                g.beginPath(); g.ellipse(p[0], p[1], rmax * E.out(v) * zs, rmax * E.out(v) * zs * sq, 0, 0, 7); g.stroke(); g.restore();
              }
            });
          }
        },
      };
    },
  });
})();
})();

// ── 04-1980s.html
(function () {
// 1:12 "In the early 1980s, its land area was roughly six hundred and sixty-five square kilometres."
// Today's photo turns into the real Landsat 5 photo of 1985 (the earliest clear one), the coastline of that photo is
// traced, and the area counts up. The on-screen year says "1980s", as the narration does.
Part('04', {
  title: 'Early 1980s',
  extraCues: ({ w }) => [[w('square') - .1, 'pop', 1.1, .14]],   // style-guide pops / ticks this part was missing
  layers: ['region', 's2_gulf', 's2_bahrain', 'ls_1985'],
  layerSet: { ls_1985: { zfade: null, opacity: 0 } },
  cues: ({ w }) => [[w('early') - .2, 'whoosh', 1.2, .22], [w('early') + .2, 'pop', .9, .2], [w('land'), 'tick'], [w('six') - .05, 'pop', 1.2, .22]],
  async scene({ w, P }) {
    const OLD = GEO.bahrain.y1985;
    const T = { early: w('early'), land: w('land'), six: w('six'), sq: w('square'), km: w('kilometres') };
    const cam = GM.track([
      [0, { lon: 50.56, lat: 26.06, zoom: 10.82, pitch: 10, bearing: 0 }, 'go'],
      [P.dur, { lon: 50.555, lat: 26.07, zoom: 11.0, pitch: 8, bearing: 0 }, 'go'],
    ]);
    return {
      cam,
      pre(api, c, t) {
        // 1985 photo dissolves in on "early 1980s"
        api.layers.ls_1985.opacity = K.ease.inOut(K.clamp((t - T.early + .25) / 1.1));
      },
      draw(api, c, t) {
        const g = api.C2.ov.g, E = K.ease, W = api.W, H = api.H;
        // year tag, top left (as the "1982" tag in the Falklands film)
        UI.tag(g, 90, 92, '1980s', { a: UI.env(t, T.early + .1, 99, .35), size: 34 });
        // 1985 coastline traced, then a soft red fill inside it
        UI.outline(api, g, OLD, E.inOut(K.clamp((t - T.land + .1) / 1.5)), { width: 2.4, glow: 14 });
        UI.highlight(api, OLD, { color: '#b3141b', a: .6 * E.inOut(K.clamp((t - T.six) / .8)), onLand: false, bevel: .7 });
        // the area counts up to 665 while the number is said
        const k = E.out(K.clamp((t - T.six + .1) / (T.sq - T.six + .3)));
        const a = UI.env(t, T.six - .15, 99, .3);
        // to the right of the island, over the sea
        const X = 1450, Y = 500;
        UI.kicker(g, 2 * X, Y, `≈${Math.round(665 * k)} KM²`, { a, size: 96 });
        UI.caps(g, X, Y + 74, 'LAND AREA', { a: UI.env(t, T.sq - .1, 99, .35), size: 24, spacing: 5 });
      },
    };
  },
});
})();

// ── 10-refinery.html
(function () {
// 7:46 "Within a few years, a refinery was operating on the island, and a new kind of wealth began to flow."
// Push in to the refinery at Sitra (its tank farm is visible in the photo); gold flows out of it, along the coast and
// out to sea.
Part('10', {
  title: 'Refinery',
  extraCues: ({ w }) => [[w('operating') - .1, 'pop', .95, .16], [w('wealth') - .3, 'tick']],   // style-guide pops / ticks this part was missing
  layers: ['region', 's2_gulf', 's2_bahrain', 's2_north'],
  cues: ({ w }) => [[w('years') - .2, 'whoosh', 1.4, .25], [w('refinery') - .05, 'pop', 1, .2], [w('island') - .1, 'tick'], [w('wealth') - .15, 'shimmer', .1]],
  async scene({ w, P }) {
    const BH = GEO.bahrain.now;
    const T = { within: w('within'), years: w('years'), ref: w('refinery'), op: w('operating'), isl: w('island'), wealth: w('wealth'), flow: w('flow') };
    const R = PLACES.refinery;
    const cam = GM.track([
      [0, { lon: 50.57, lat: 26.08, zoom: 10.6, pitch: 14, bearing: 0 }, 'go'],
      [T.ref + .5, { lon: 50.627, lat: 26.143, zoom: 13.7, pitch: 26, bearing: -8 }],
      [T.wealth, { lon: 50.645, lat: 26.147, zoom: 13.1, pitch: 28, bearing: -10 }],
      [P.dur, { lon: 50.67, lat: 26.153, zoom: 12.65, pitch: 28, bearing: -12 }, 'go'],
    ]);
    // gold leaves the refinery: down the jetty, along the coast, out into the Gulf
    const out1 = [R, [50.631, 26.150], [50.645, 26.155], [50.662, 26.161], [50.70, 26.175], [50.78, 26.20], [50.88, 26.23]];
    const out2 = [R, [50.632, 26.143], [50.644, 26.137], [50.66, 26.128], [50.72, 26.10], [50.82, 26.06]];
    return {
      cam,
      draw(api, c, t) {
        const g = api.C2.ov.g, E = K.ease, W = api.W, H = api.H;
        const p = api.P(...R);
        if (p) {
          UI.pulse(g, p[0], p[1], t, { a: UI.env(t, T.ref - .1, 99, .35), r: 60, dot: 8, color: '#FFD23F', period: 1.6 });
          // label box with the refinery icon
          const a = UI.env(t, T.ref - .1, 99, .35);
          if (a > 0) {
            g.save(); g.globalAlpha = a; const k = .7 + .3 * E.outBack(a);
            g.translate(p[0], p[1] - 70); g.scale(k, k);
            g.fillStyle = '#fff'; g.shadowColor = 'rgba(0,0,0,.4)'; g.shadowBlur = 12; UI.rr(g, -118, -26, 236, 52, 7); g.fill(); g.shadowBlur = 0;
            UI.icon(g, 'refinery', -88, 0, 28, '#c4161c');
            g.fillStyle = '#111'; g.font = `800 24px ${UI.F}`; g.textBaseline = 'middle'; g.fillText('REFINERY', -66, 1);
            g.restore();
            g.save(); g.globalAlpha = a; g.strokeStyle = '#fff'; g.lineWidth = 2; g.beginPath(); g.moveTo(p[0], p[1] - 8); g.lineTo(p[0], p[1] - 44); g.stroke(); g.restore();
          }
        }
        // gold lines leave the refinery; the dots start at the refinery with the line and never run ahead of it
        const fw = E.inOut(K.clamp((t - T.wealth + .3) / 1.6));
        for (const r of [out1, out2]) {
          UI.dash(api, g, r, fw, { color: '#FFD23F', width: 4, dash: [3, 10], glow: 14 });
          UI.flow(api, g, r, t, { t0: T.wealth - .3, head: fw, n: 7, speed: .35, color: '#FFD23F', size: 5 });
        }
        UI.caps(g, W / 2, 92, 'OPERATING ON THE ISLAND', { a: UI.env(t, T.op - .1, T.wealth - .3, .35), size: 24, spacing: 5 });
        UI.kicker(g, W, H - 140, 'A NEW KIND OF WEALTH', { a: UI.env(t, T.wealth - .15, 99, .35), size: 56 });
      },
    };
  },
});
})();

// ── 07-dilmun.html
(function () {
// 3:31 "In the Bronze Age, Bahrain was the heart of a civilisation called Dilmun. Its merchants sat in the middle of a
// trade network that ran between Mesopotamia, in what is now Iraq, and the Indus Valley, in what is now Pakistan."
// Bahrain glows as Dilmun, the camera rises, and the sea route draws west to Mesopotamia and east to the Indus Valley.
Part('07', {
  title: 'Dilmun trade',
  extraCues: ({ w }) => [[w('middle') - .2, 'pop', 1.15, .16]],   // style-guide pops / ticks this part was missing
  layers: ['world', 'region', 's2_gulf'],
  cues: ({ w }) => [[w('bronze') - .1, 'pop', .9, .18], [w('bahrain') - .1, 'pop', 1, .16], [w('dilmun') - .1, 'shimmer', .12], [w('merchants') - .3, 'whoosh', 1.6, .3],
    [w('mesopotamia') - .5, 'tick'], [w('mesopotamia') - .05, 'pop', 1.1, .18], [w('iraq') - .05, 'pop', 1.2, .14], [w('indus') - .6, 'tick'], [w('indus') - .05, 'pop', 1.1, .18], [w('pakistan') - .05, 'pop', 1.2, .14]],
  async scene({ w, P }) {
    const BH = GEO.bahrain.now, IRQ = GEO.countries.IRQ.rings, PAK = GEO.countries.PAK.rings;
    const T = { bronze: w('bronze'), bah: w('bahrain'), heart: w('heart'), dil: w('dilmun'), merch: w('merchants'), middle: w('middle'), trade: w('trade'),
      meso: w('mesopotamia'), iraq: w('iraq'), indus: w('indus'), valley: w('valley'), pak: w('pakistan') };
    const cam = GM.track([
      [0, { lon: 51.15, lat: 26.35, zoom: 7.0, pitch: 26, bearing: 0 }, 'go'],
      [T.dil + .8, { lon: 51.0, lat: 26.3, zoom: 7.35, pitch: 24, bearing: 0 }],
      // closer than before (user, 20 Oct): the trade area from Mesopotamia to the Indus fills the frame
      [T.meso - .2, { lon: 56.9, lat: 27.6, zoom: 6.12, pitch: 24, bearing: 0 }],
      [P.dur, { lon: 57.4, lat: 27.6, zoom: 6.08, pitch: 22, bearing: 0 }, 'go'],
    ]);
    const GOLD = '#FFD23F';
    return {
      cam,
      draw(api, c, t) {
        const g = api.C2.ov.g, E = K.ease, W = api.W, H = api.H;
        UI.tag(g, 90, 92, 'BRONZE AGE', { a: UI.env(t, T.bronze, 99, .35), size: 34 });
        // Bahrain = Dilmun
        UI.highlight(api, BH, { color: '#b3141b', a: .95 * E.inOut(K.clamp((t - T.bah + .2) / .6)), onLand: false, lift: .6, bevel: .8, edge: .3 });
        const pb = api.P(50.56, 26.05);
        if (pb) {
          UI.pulse(g, pb[0], pb[1], t, { a: UI.env(t, T.heart - .2, 99, .5), r: 70, dot: 7, color: GOLD, period: 1.8 });
          UI.bigName(g, pb[0] + 10, pb[1] + 78, 'DILMUN', { a: K.clamp((t - T.dil + .1) / .9), size: 56, spacing: .3, color: GOLD, alpha: .95 });
        }
        // the routes: west to Ur (Mesopotamia), east to the Indus mouth
        const fw = E.inOut(K.clamp((t - T.meso + .7) / 1.4)), fe = E.inOut(K.clamp((t - T.indus + .9) / 1.9));
        const west = [...DILMUN_ROUTE_W].reverse();
        UI.dash(api, g, west, fw, { a: 1, color: GOLD, width: 3.6, dash: [12, 9], glow: 12, head: true, headSize: 16 });
        UI.dash(api, g, DILMUN_ROUTE_E, fe, { a: 1, color: GOLD, width: 3.6, dash: [12, 9], glow: 12, head: true, headSize: 16 });
        // the dots leave the root of each route as it starts to draw, and never run ahead of the line
        UI.flow(api, g, west, t, { t0: T.meso - .7, head: fw, n: 4, speed: .2, color: '#fff', size: 4 });
        UI.flow(api, g, DILMUN_ROUTE_E, t, { t0: T.indus - .9, head: fe, n: 6, speed: .15, color: '#fff', size: 4 });
        // Mesopotamia / Iraq
        UI.highlight(api, IRQ, { light: true, color: '#ffe2a8', a: .32 * UI.env(t, T.iraq - .2, 99, .6) });
        UI.outline(api, g, IRQ, E.inOut(K.clamp((t - T.iraq + .2) / 1.3)), { width: 1.8, glow: 10, alpha: .8 });   // glowing border draws on
        const pm = UI.on(api, 45.2, 32.6, 600);
        if (pm) {
          UI.bigName(g, pm[0], pm[1], 'MESOPOTAMIA', { a: K.clamp((t - T.meso + .1) / .8), size: 40, spacing: .14 });
          UI.caps(g, pm[0], pm[1] + 44, 'TODAY: IRAQ', { a: UI.env(t, T.iraq - .15, 99, .35), size: 20, spacing: 3, color: '#ffe2a8' });
        }
        const pu = api.P(...PLACES.ur); if (pu) UI.pulse(g, pu[0], pu[1], t, { a: UI.env(t, T.meso, 99, .4), r: 26, dot: 5, color: GOLD });
        // Indus Valley / Pakistan
        UI.highlight(api, PAK, { light: true, color: '#c9f0d0', a: .3 * UI.env(t, T.pak - .2, 99, .6) });
        UI.outline(api, g, PAK, E.inOut(K.clamp((t - T.pak + .2) / 1.3)), { width: 1.8, glow: 10, alpha: .8 });
        const pi = UI.on(api, 69.4, 29.3, 600);
        if (pi) {
          UI.bigName(g, pi[0], pi[1], 'INDUS VALLEY', { a: K.clamp((t - T.indus + .1) / .8), size: 40, spacing: .14 });
          UI.caps(g, pi[0], pi[1] + 44, 'TODAY: PAKISTAN', { a: UI.env(t, T.pak - .15, 99, .35), size: 20, spacing: 3, color: '#c9f0d0' });
        }
        const pk = api.P(...PLACES.mohenjoDaro); if (pk) UI.pulse(g, pk[0], pk[1], t, { a: UI.env(t, T.valley, 99, .4), r: 26, dot: 5, color: GOLD });
        UI.kicker(g, W, H - 125, 'IN THE MIDDLE OF A TRADE NETWORK', { a: UI.env(t, T.middle - .2, T.meso - .3, .35), size: 40, color: '#fff' });
      },
    };
  },
});
})();

// ── 11-causeway.html
(function () {
// 9:04 "Then, in 1986, Bahrain stopped being entirely an island in practical terms."
// The causeway (traced on the photo) lights up from Bahrain across the border islands to the Saudi shore.
Part('11', {
  title: 'Causeway 1986',
  extraCues: ({ w }) => [[w('stopped') + .8, 'pop', 1.05, .16]],   // style-guide pops / ticks this part was missing
  layers: ['region', 's2_gulf', 's2_bahrain'],
  cues: ({ w }) => [[w('1986') - .05, 'pop', .9, .2], [w('bahrain') - .1, 'pop', 1, .16], [w('stopped') - .1, 'riser', 1.6, .18], [w('island') - .05, 'hit', .45]],
  async scene({ w, P }) {
    const BH = GEO.bahrain.now, SAU = GEO.countries.SAU.rings;
    const T = { then: w('then'), y: w('1986'), bah: w('bahrain'), stop: w('stopped'), ent: w('entirely'), isl: w('island'), prac: w('practical') };
    const cam = GM.track([
      [0, { lon: 50.42, lat: 26.14, zoom: 10.9, pitch: 20, bearing: -6 }, 'go'],
      [T.isl, { lon: 50.34, lat: 26.175, zoom: 11.35, pitch: 26, bearing: -10 }],
      [P.dur, { lon: 50.33, lat: 26.18, zoom: 11.45, pitch: 28, bearing: -12 }, 'go'],
    ]);
    const cw = [...CAUSEWAY].reverse();   // drawn from the Bahrain end
    return {
      cam,
      draw(api, c, t) {
        const g = api.C2.ov.g, E = K.ease, W = api.W, H = api.H;
        UI.tag(g, 90, 92, '1986', { a: UI.env(t, T.y - .05, 99, .3), size: 40 });
        UI.highlight(api, BH, { color: '#b3141b', a: .92 * E.inOut(K.clamp((t - T.bah + .2) / .6)), onLand: false, lift: .6, bevel: .9, edge: .3 });
        UI.highlight(api, SAU, { light: true, color: '#d8ecff', a: .3 * UI.env(t, T.ent - .2, 99, .6) });
        // glowing borders draw on: Bahrain as it is named, Saudi Arabia on "entirely"
        UI.outline(api, g, BH, E.inOut(K.clamp((t - T.bah + .2) / 1.2)), { width: 2.2, glow: 12, alpha: .9 });
        UI.outline(api, g, SAU, E.inOut(K.clamp((t - T.ent + .2) / 1.2)), { width: 2, glow: 12, alpha: .85, bbox: [49.5, 25.5, 51.3, 26.9] });
        // the causeway lights up, Bahrain → Saudi Arabia
        const f = E.inOut(K.clamp((t - T.stop + .1) / 1.8));
        if (f > 0) {
          const sp = cw.map(p => api.P(p[0], p[1])).filter(Boolean), part = K.partial(K.smoothLine(sp, 4), f);
          g.save(); g.lineCap = g.lineJoin = 'round';
          g.strokeStyle = 'rgba(255,210,63,.35)'; g.lineWidth = 16; g.shadowColor = '#FFD23F'; g.shadowBlur = 24; K.polyline(g, part); g.stroke();
          g.strokeStyle = '#fff'; g.lineWidth = 5; g.shadowBlur = 10; K.polyline(g, part); g.stroke();
          const e = part[part.length - 1]; g.fillStyle = '#fff'; g.shadowBlur = 18; g.beginPath(); g.arc(e[0], e[1], 7, 0, 7); g.fill();
          g.restore();
          UI.flow(api, g, cw, t, { t0: T.stop - .1, head: f, n: 8, speed: .22, color: '#FFD23F', size: 4 });   // from the Bahrain end, with the line
        }
        const pc = api.P(50.31, 26.21);
        if (pc) UI.caps(g, pc[0], pc[1] - 30, 'CAUSEWAY', { a: UI.env(t, T.stop + .8, 99, .4), size: 22, spacing: 5 });
        const ps = UI.on(api, 50.08, 26.05, 300), pb = UI.on(api, 50.6, 26.2, 300);
        if (ps) UI.bigName(g, ps[0], ps[1], 'SAUDI ARABIA', { a: K.clamp((t - T.ent + .1) / .8), size: 44, angle: -1.2 });
        if (pb) UI.bigName(g, pb[0] + 230, pb[1] - 30, 'BAHRAIN', { a: K.clamp((t - T.bah) / .8), size: 50, spacing: .2 });
        UI.kicker(g, W, H - 140, 'NO LONGER ENTIRELY AN ISLAND', { a: UI.env(t, T.isl - .1, 99, .35), size: 48 });
      },
    };
  },
});
})();

// ── 15-strikes.html
(function () {
// 14:46 "On 28 February 2026, after American and Israeli strikes on Iran, Iran launched missiles and drones at targets
// across the Gulf."
// Action on the map (user, 20 Oct; look of the Hormuz demo, 0:14–0:17): strikes fly in from outside the frame and hit
// Iran on "strikes on Iran"; then Iran launches — flashes and white smoke on the ground, missiles with glowing heads
// and smoke trails arc across the Gulf, slower drones follow — and the hits land on the western shore with a flash,
// fireball, shock ring, fire and smoke, and a short camera shake. Map only: no people; impact points are general,
// not named targets.
// one plan for picture and sound: all times from the word times
function plan(w) {
  const am = w('american'), launch = w('launched'), tDro = w('drones');
  // strikes on Iran: from the west (Iraq side) and from the sea to the south, all from outside the frame
  const strikes = [
    [[41.6, 31.9], [49.25, 30.55], 0, 1.25], [[41.8, 30.6], [51.5, 30.62], .3, 1.35], [[55.4, 21.6], [50.95, 29.05], .55, 1.4],
    [[58.6, 22.0], [56.15, 27.28], .75, 1.2], [[54.2, 21.4], [52.55, 29.6], .95, 1.45], [[57.2, 21.5], [54.3, 28.95], 1.15, 1.35],
  ].map(([A, B, d, dur], i) => ({ A, B, t0: am - .1 + d, dur, arc: .07, accel: .2, seed: 10 + i }));
  // Iran launches: missiles from the coast and the interior to the western shore of the Gulf
  const missiles = [
    [[51.1, 29.65], [47.95, 29.3]], [[52.05, 28.65], [50.12, 26.6]], [[50.0, 30.55], [48.4, 28.25]], [[53.3, 28.05], [50.61, 26.21]],
    [[54.6, 27.78], [51.32, 25.15]], [[55.8, 27.38], [54.55, 24.3]], [[56.55, 27.52], [55.08, 25.0]],
  ].map(([A, B], i) => ({ A, B, t0: launch - .05 + i * .2, dur: 1.75 + .12 * (i % 3), arc: .3, accel: .3, seed: 30 + i }));
  const drones = [[[54.9, 27.0], [53.9, 24.95]], [[52.7, 27.95], [51.55, 25.6]], [[55.6, 26.95], [55.4, 25.25]]]
    .map(([A, B], i) => ({ A, B, t0: tDro - .15 + i * .22, dur: 2.9 - i * .15, seed: 50 + i }));
  const hit = m => m.t0 + m.dur;
  return { strikes, missiles, drones, hit,
    shake: [...strikes.map(m => [hit(m), .55]), ...missiles.map(m => [hit(m), .5]), ...drones.map(m => [hit(m), .3])] };
}
Part('15', {
  title: '28 February 2026',
  extraCues: ({ w }) => [[w('28') - .1, 'pop', 1, .18], [w('american') - .1, 'pop', 1.05, .14], [w('iran') - .05, 'pop', 1.1, .16], [w('missiles') - .1, 'pop', 1.2, .14], [w('targets') - .15, 'pop', 1.3, .18]],   // style-guide pops / ticks this part was missing
  layers: ['world', 'region', 's2_gulf'],
  cues: ({ w }) => {
    const Q = plan(w), c = [[w('february') - .2, 'pop', .9, .2], [w('american') - .2, 'whoosh', 1.5, .22], [w('iran', 2) - .1, 'pop', .8, .16]];
    Q.strikes.forEach((m, i) => c.push([Q.hit(m), 'boom', .32 + .04 * (i % 2)]));
    Q.missiles.forEach((m, i) => { c.push([m.t0, 'launch', 1.3, .17]); c.push([Q.hit(m), 'boom', .36]); });
    Q.drones.forEach(m => { c.push([m.t0, 'whoosh', 1, .1]); c.push([Q.hit(m), 'hit', .4]); });
    return c;
  },
  async scene({ w, P }) {
    const IRN = GEO.countries.IRN.rings, BH = GEO.bahrain.now, Q = plan(w);
    const T = { on: w('on'), d28: w('28'), feb: w('february'), y: w('2026'), after: w('after'), am: w('american'), isr: w('israeli'), str: w('strikes'),
      iran1: w('iran', 1), iran2: w('iran', 2), launch: w('launched'), mis: w('missiles'), dro: w('drones'), tg: w('targets'), across: w('across'), gulf: w('gulf') };
    const base = GM.track([
      [0, { lon: 52.3, lat: 28.05, zoom: 7.02, pitch: 38, bearing: 0 }, 'go'],
      [T.iran2, { lon: 52.0, lat: 27.5, zoom: 7.27, pitch: 36, bearing: 0 }],
      [P.dur, { lon: 51.8, lat: 27.2, zoom: 7.42, pitch: 34, bearing: 0 }, 'go'],
    ]);
    const cam = FX.shaken(base, Q.shake, 7);
    const wind = [13, -3];
    return {
      cam,
      draw(api, c, t) {
        const g = api.C2.ov.g, E = K.ease, W = api.W, H = api.H;
        // date tag with a calendar icon
        const da = UI.env(t, T.d28 - .1, 99, .35);
        if (da > 0) {
          g.save(); g.globalAlpha = da; g.fillStyle = '#FFD23F'; g.fillRect(88, 66, 420 * E.out(da), 54);
          g.beginPath(); g.rect(88, 66, 420 * E.out(da), 54); g.clip();
          UI.icon(g, 'calendar', 116, 93, 28, '#111');
          g.fillStyle = '#111'; g.font = `900 30px ${UI.F}`; g.textBaseline = 'middle'; g.fillText('28 FEBRUARY 2026', 142, 94);
          g.restore();
        }
        UI.caps(g, 300, 152, 'AFTER U.S. AND ISRAELI STRIKES ON IRAN', { a: UI.env(t, T.am - .1, 99, .35), size: 18, spacing: 2.5, align: 'left' });
        // Iran: outlined and lightly washed (neutral grey-white)
        const ia = E.inOut(K.clamp((t - T.iran1 + .15) / .6));
        UI.highlight(api, IRN, { light: true, color: '#f2e6d8', a: .32 * ia });
        UI.outline(api, g, IRN, E.inOut(K.clamp((t - T.iran1 + .15) / 1.4)), { width: 2, glow: 12, alpha: .8, bbox: [40, 22, 64, 34.5] });   // glowing border draws on
        UI.highlight(api, BH, { color: '#b3141b', a: .9, onLand: false, edge: .3 });
        // 1 · strikes on Iran
        for (const m of Q.strikes) {
          FX.missile(api, m, t, { k: .85, trail: .22, smoke: 1.3 });
          FX.impact(api, m.B[0], m.B[1], t, Q.hit(m), { k: .85, seed: m.seed, wind });
        }
        // 2 · Iran launches missiles and drones across the Gulf
        for (const m of Q.missiles) {
          FX.launch(api, m.A[0], m.A[1], t, m.t0, { k: .9, seed: m.seed, wind });
          FX.missile(api, m, t, { k: 1 });
          FX.impact(api, m.B[0], m.B[1], t, Q.hit(m), { k: .95, seed: m.seed, wind });
        }
        for (const d of Q.drones) {
          const pl = api.P(...d.A); if (pl) FX.flash(api, pl[0], pl[1], t - d.t0, { r: 30, dur: .3 });
          FX.drone(api, d, t, { k: 1 });
          FX.impact(api, d.B[0], d.B[1], t, Q.hit(d), { k: .7, seed: d.seed, wind });
        }
        const pi = UI.on(api, 53.6, 30.0, 600);
        if (pi) UI.bigName(g, pi[0], pi[1], 'IRAN', { a: K.clamp((t - T.iran1) / .7), size: 60, spacing: .3 });
        if (pi) UI.caps(g, pi[0], pi[1] + 52, 'MISSILES AND DRONES', { a: UI.env(t, T.mis - .1, 99, .35), size: 22, spacing: 4, color: '#ffd2bd' });
        UI.kicker(g, W, H - 130, 'TARGETS ACROSS THE GULF', { a: UI.env(t, T.tg - .15, 99, .35), size: 50, color: '#fff' });
      },
    };
  },
});
})();

// ── 16-summer.html
(function () {
// 15:49 "…but attacks on Bahrain continued into the summer."
// Bahrain close, red, tilted. Missiles and drones keep coming in from the north-east (from outside the frame) and hit
// the kinds of sites the narration names just before (naval base, port, refinery, Manama, air base): flash, fireball,
// shock ring and a short camera shake; the fires keep burning and the smoke drifts while a month bar at the bottom
// fills from February into the summer (user, 20 Oct: "Bahrain burning, explosions"). Map only, no people.
function plan() {
  // [site lon/lat, hit time, kind]: m = missile, d = drone
  const hits = [
    [PLACES.navalBase, .85, 'm'], [PLACES.refinery, 1.32, 'm'], [PLACES.manama, 1.78, 'd'], [[50.613, 26.198], 2.25, 'm'],
    [[50.59, 25.92], 2.7, 'm'], [[50.636, 26.135], 3.15, 'd'], [[50.602, 26.214], 3.6, 'm'], [[50.575, 26.228], 4.1, 'm'],
  ];
  const from = [[51.85, 26.85], [51.95, 26.55], [51.75, 27.0], [52.0, 26.3]];
  const strikes = hits.map(([B, th, kind], i) => {
    const A = from[i % from.length], dur = kind === 'd' ? 2.3 : 1.0 + .08 * (i % 3);
    return { A, B, t0: th - dur, dur, kind, arc: kind === 'd' ? 0 : .2, accel: .25, seed: 70 + i };
  });
  return { strikes, hit: m => m.t0 + m.dur, shake: strikes.map(m => [m.t0 + m.dur, m.kind === 'd' ? .35 : .5]) };
}
Part('16', {
  title: 'Into the summer',
  extraCues: ({ w }) => [[w('attacks') - .3, 'pop', .9, .16], [w('attacks') - .1, 'pop', 1.1, .14], [w('continued') - .1, 'tick']],   // style-guide pops / ticks this part was missing
  layers: ['region', 's2_gulf', 's2_bahrain'],
  cues: ({ w }) => {
    const Q = plan(), c = [[w('continued') - .1, 'riser', 1.2, .12], [w('summer') - .1, 'pop', 1.2, .16]];
    Q.strikes.forEach((m, i) => { if (m.t0 > 0) c.push([m.t0, 'whoosh', Math.min(1.2, m.dur), .1]); c.push([Q.hit(m), m.kind === 'd' ? 'hit' : 'boom', m.kind === 'd' ? .45 : .34]); });
    return c;
  },
  async scene({ w, P }) {
    const BH = GEO.bahrain.now, Q = plan();
    const T = { att: w('attacks'), bah: w('bahrain'), cont: w('continued'), sum: w('summer') };
    const base = GM.track([
      [0, { lon: 50.585, lat: 26.075, zoom: 11.1, pitch: 40, bearing: -4 }, 'go'],
      [P.dur, { lon: 50.59, lat: 26.09, zoom: 11.32, pitch: 44, bearing: -8 }, 'go'],
    ]);
    const cam = FX.shaken(base, Q.shake, 8);
    const months = ['FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG'];
    const wind = [26, 4], k = 1;   // smoke drifts downwind (to the south-east on screen)
    return {
      cam,
      draw(api, c, t) {
        const g = api.C2.ov.g, E = K.ease, W = api.W, H = api.H;
        UI.highlight(api, BH, { color: '#b3141b', a: .9, onLand: false, lift: .5, bevel: .7, edge: .3 });
        for (const m of Q.strikes) {
          if (m.kind === 'd') FX.drone(api, m, t, { k: 1.1 }); else FX.missile(api, m, t, { k: .9, trail: .4 });
          FX.impact(api, m.B[0], m.B[1], t, Q.hit(m), { k, seed: m.seed, wind, big: m.B === PLACES.refinery ? 1.35 : m.kind === 'd' ? .8 : 1 });   // the refinery burns biggest
        }
        // month bar
        const ba = UI.env(t, T.att - .3, 99, .4);
        if (ba > 0) {
          const x0 = W / 2 - 420, x1 = W / 2 + 420, y = H - 120, step = (x1 - x0) / (months.length - 1);
          const fill = E.inOut(K.clamp((t - T.cont + .1) / (T.sum - T.cont + .6)));
          g.save(); g.globalAlpha = ba; g.lineCap = 'round';
          g.strokeStyle = 'rgba(255,255,255,.35)'; g.lineWidth = 8; g.beginPath(); g.moveTo(x0, y); g.lineTo(x1, y); g.stroke();
          g.strokeStyle = '#ff4d4d'; g.beginPath(); g.moveTo(x0, y); g.lineTo(x0 + (x1 - x0) * fill, y); g.stroke();
          // summer band (June – August)
          g.fillStyle = 'rgba(255,210,63,.25)'; UI.rr(g, x0 + step * 4 - 18, y - 22, step * 2 + 36, 44, 10); g.fill();
          g.restore();
          months.forEach((m, i) => UI.caps(g, x0 + step * i, y + 34, m, { a: ba, size: 16, spacing: 2, color: i >= 4 ? '#FFD23F' : '#fff' }));
          UI.caps(g, x0 + step * 5, y - 40, 'SUMMER', { a: UI.env(t, T.sum - .1, 99, .3), size: 22, spacing: 6, color: '#FFD23F' });
        }
        UI.caps(g, W / 2, 92, 'ATTACKS ON BAHRAIN CONTINUED', { a: UI.env(t, T.att - .1, 99, .35), size: 28, spacing: 5 });
      },
    };
  },
});
})();
