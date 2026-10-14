// Map demo player: fixed 1920×1080 (or 4:5, 1080×1350) stage, full control bar, posterize time, code-synthesized score + optional voice-over.
// Usage: MapPlayer({ title, setup: async (stage, info) => drawFn(t), score: {...}, vo: ['audio/01-vo.wav', ...] })
(function () {
  const CSS = `
  :root { --bar: 60px; --fg: #e8e8ee; --mut: #8a8a96; --acc: #2F7CFF; --panel: #0f0f12; --line: #1e1e24; }
  * { box-sizing: border-box; }
  html, body { margin: 0; height: 100%; background: #050506; color: var(--fg); overflow: hidden;
    font: 13px "Segoe UI", -apple-system, system-ui, Roboto, sans-serif; }
  #wrap { position: absolute; left: 0; right: 0; top: 0; bottom: var(--bar); overflow: hidden; }
  #stage { position: absolute; left: 0; top: 0; transform-origin: 0 0; overflow: hidden; background: #0a0a0c; cursor: pointer; }
  #stage > canvas { position: absolute; left: 0; top: 0; display: block; }
  #loader { position: absolute; inset: 0; display: grid; place-items: center; color: #9aa; font-size: 34px; letter-spacing: 2px; }
  body.nobar #wrap { bottom: 0; } body.nobar #bar { display: none; }
  #bar { position: absolute; left: 0; right: 0; bottom: 0; height: var(--bar); display: flex; align-items: center; gap: 6px;
    padding: 0 12px; background: var(--panel); border-top: 1px solid var(--line); user-select: none; }
  #bar button, #bar a { all: unset; display: grid; place-items: center; min-width: 34px; height: 34px; padding: 0 6px; border-radius: 8px;
    color: var(--fg); cursor: pointer; font-weight: 600; font-size: 12px; white-space: nowrap; }
  #bar button:hover, #bar a:hover { background: #1c1c22; }
  #bar button:focus-visible { outline: 2px solid var(--acc); outline-offset: 1px; }
  #bar button.on { color: var(--acc); }
  #bar svg { width: 18px; height: 18px; fill: currentColor; }
  #bar .num { font-variant-numeric: tabular-nums; color: var(--mut); white-space: nowrap; min-width: 92px; text-align: center; }
  #bar .num b { color: var(--fg); font-weight: 600; }
  #bar .ttl { color: var(--mut); font-weight: 600; padding: 0 4px; white-space: nowrap; }
  #bar .vo { font-size: 10px; padding: 2px 6px; border-radius: 6px; background: #1b2a1b; color: #7fd27f; display: none; }
  #seek { flex: 1; min-width: 80px; }
  #vol { width: 80px; }
  input[type=range] { accent-color: var(--acc); height: 4px; cursor: pointer; }
  select { background: #17171c; color: var(--fg); border: 1px solid var(--line); border-radius: 7px; height: 30px; padding: 0 6px; font: inherit; cursor: pointer; }
  .sep { width: 1px; height: 24px; background: var(--line); margin: 0 4px; }
  #bar button.txt { display: flex; gap: 6px; padding: 0 9px; letter-spacing: .5px; border: 1px solid var(--line); }
  #bar button.txt.on { background: #14233f; border-color: var(--acc); }
  body.embed .hide-embed { display: none !important; }
  @media (max-width: 900px) { #vol, .hide-s, #bPost span { display: none; } #bar .num { min-width: 70px; } }
  @media (max-width: 560px) { #frame, #postFps { display: none; } }`;

  const BAR = (title, frames, dur, vert) => `
  <a href="index.html" class="hide-s hide-embed" title="All demos">☰</a>
  <span class="ttl hide-s hide-embed">${title}</span>
  <button id="bRestart" title="Restart (Home)"><svg viewBox="0 0 24 24"><path d="M6 5h2v14H6zM20 5v14L9 12z"/></svg></button>
  <button id="bPrev" title="Previous frame (← or ,)"><svg viewBox="0 0 24 24"><path d="M7 5h2v14H7zM19 5v14l-9-7z"/></svg></button>
  <button id="bPlay" title="Play / pause (Space or K)"><svg viewBox="0 0 24 24"><path id="playIcon" d="M7 4v16l13-8z"/></svg></button>
  <button id="bNext" title="Next frame (→ or .)"><svg viewBox="0 0 24 24"><path d="M15 5h2v14h-2zM5 5v14l9-7z"/></svg></button>
  <span class="num" id="time"><b>0.00</b> / ${dur.toFixed(2)}</span>
  <input id="seek" type="range" min="0" max="${frames - 1}" step="1" value="0" title="Seek">
  <span class="num" id="frame"><b>0</b> / ${frames - 1}</span>
  <select id="speed" title="Playback speed">
    <option value="0.25">0.25×</option><option value="0.5">0.5×</option><option value="0.75">0.75×</option>
    <option value="1" selected>1×</option><option value="1.5">1.5×</option><option value="2">2×</option>
  </select>
  <button id="bLoop" class="on" title="Loop (L)"><svg viewBox="0 0 24 24"><path d="M7 7h10v3l4-4-4-4v3H5v6h2zm10 10H7v-3l-4 4 4 4v-3h12v-6h-2z"/></svg></button>
  <div class="sep hide-s"></div>
  <button id="bPost" class="txt" title="Posterize time: picture steps at a lower frame rate, like After Effects (P)"><svg viewBox="0 0 24 24"><path d="M3 20v-4h5v-4h5V8h5V4h3v3h-3v4h-5v4H8v4H3z"/></svg><span>POSTERIZE</span></button>
  <select id="postFps" title="Posterize frame rate"><option value="12">12 fps</option><option value="8">8 fps</option><option value="6">6 fps</option></select>
  <button id="bAr" class="txt hide-embed" title="Switch frame: 16:9 / 4:5">${vert ? '4:5' : '16:9'}</button>
  <div class="sep hide-s"></div>
  <span class="vo" id="voTag" title="Voice-over file loaded">VO</span>
  <button id="bMute" title="Mute (M)"><svg viewBox="0 0 24 24"><path id="muteIcon" d="M3 9v6h4l5 5V4L7 9zm13.5 3A4.5 4.5 0 0 0 14 8v8a4.5 4.5 0 0 0 2.5-4z"/></svg></button>
  <input id="vol" type="range" min="0" max="1" step="0.01" value="1" title="Volume">
  <div class="sep hide-s"></div>
  <button id="bFull" title="Fullscreen (F)"><svg viewBox="0 0 24 24"><path d="M4 4h6v2H6v4H4zm10 0h6v6h-2V6h-4zM4 14h2v4h4v2H4zm14 0h2v6h-6v-2h4z"/></svg></button>`;

  // ───────────────────────── voice timing ─────────────────────────
  // clock time → animation time through anchor points [clock, anim], as a monotone cubic (no sudden speed jumps).
  // inv() maps an animation time back to the clock (used to keep the music cues on their picture beats).
  function makeWarp(pts) {
    const n = pts.length, x = pts.map(p => p[0]), y = pts.map(p => p[1]), d = [], m = [];
    for (let i = 0; i < n - 1; i++) d.push((y[i + 1] - y[i]) / (x[i + 1] - x[i]));
    m[0] = d[0]; m[n - 1] = d[n - 2];
    for (let i = 1; i < n - 1; i++) m[i] = d[i - 1] * d[i] <= 0 ? 0 : (d[i - 1] + d[i]) / 2;
    for (let i = 0; i < n - 1; i++) {   // Fritsch–Carlson: keep it monotone
      if (!d[i]) { m[i] = m[i + 1] = 0; continue; }
      const a = m[i] / d[i], b = m[i + 1] / d[i], h = a * a + b * b;
      if (h > 9) { const k = 3 / Math.sqrt(h); m[i] = k * a * d[i]; m[i + 1] = k * b * d[i]; }
    }
    const f = t => {
      if (t <= x[0]) return y[0]; if (t >= x[n - 1]) return y[n - 1];
      let i = 0; while (t > x[i + 1]) i++;
      const h = x[i + 1] - x[i], u = (t - x[i]) / h, u2 = u * u, u3 = u2 * u;
      return (2 * u3 - 3 * u2 + 1) * y[i] + (u3 - 2 * u2 + u) * h * m[i] + (3 * u2 - 2 * u3) * y[i + 1] + (u3 - u2) * h * m[i + 1];
    };
    const inv = v => { let a = x[0], b = x[n - 1]; for (let k = 0; k < 40; k++) { const c = (a + b) / 2; if (f(c) < v) a = c; else b = c; } return (a + b) / 2; };
    return { f, inv };
  }

  // ───────────────────────── score synth (OfflineAudioContext → WAV blob) ─────────────────────────
  // warp: cue times are written in animation time and land on the clock through warp.inv
  // vd: voice data from audio/vo-env.js; the music ducks under the voice (the curve is baked into the WAV, so seek stays exact)
  async function synthScore(DUR, S, warp, vd) {
    const sr = 44100, ac = new OfflineAudioContext(2, Math.ceil(sr * DUR), sr), R = K.rng(S.seed || 7);
    const noise = ac.createBuffer(1, sr * 2, sr); { const d = noise.getChannelData(0); for (let i = 0; i < d.length; i++) d[i] = R() * 2 - 1; }
    const master = ac.createGain(), comp = ac.createDynamicsCompressor();
    comp.threshold.value = -16; comp.ratio.value = 4; comp.attack.value = .004; comp.release.value = .25;
    const duck = ac.createGain();
    master.connect(comp).connect(duck).connect(ac.destination);
    if (vd) {
      const M = window.VO_META || {}, gap = M.duckGap ?? .75, sp = M.duckSpeech ?? .28, hz = M.envHz || 25;
      const c = Float32Array.from(vd.env, v => gap + (sp - gap) * v / 100);
      duck.gain.setValueCurveAtTime(c, 0, Math.min(DUR, (c.length - 1) / hz));
    }
    const at = t => warp ? warp.inv(t) : t;
    const m = S.master || .85, fadeIn = S.fadeIn ?? .15;   // fadeIn 0: a hit on 0.0 keeps its attack
    master.gain.setValueAtTime(fadeIn > 0 ? 0 : m, 0); if (fadeIn > 0) master.gain.linearRampToValueAtTime(m, fadeIn);
    master.gain.setValueAtTime(m, DUR - .5); master.gain.linearRampToValueAtTime(0, DUR);
    const irLen = sr * 2.8, ir = ac.createBuffer(2, irLen, sr);
    for (let c = 0; c < 2; c++) { const d = ir.getChannelData(c); for (let i = 0; i < irLen; i++) d[i] = (R() * 2 - 1) * Math.pow(1 - i / irLen, 3.4); }
    const verb = ac.createConvolver(); verb.buffer = ir; const wet = ac.createGain(); wet.gain.value = S.wet ?? .4; verb.connect(wet).connect(master);
    const out = (node, send = .3, pan = 0) => {
      let n = node; if (pan) { const p = ac.createStereoPanner(); p.pan.value = pan; node.connect(p); n = p; }
      n.connect(master); if (send) { const s = ac.createGain(); s.gain.value = send; n.connect(s).connect(verb); }
    };
    const hz = n => 440 * Math.pow(2, (n - 69) / 12);
    const env = (g, t, a, peak, d) => { g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(peak, t + a); g.gain.exponentialRampToValueAtTime(1e-4, t + a + d); };
    const osc = (type, f, t, dur) => { const o = ac.createOscillator(); o.type = type; o.frequency.setValueAtTime(f, t); o.start(t); o.stop(t + dur); return o; };
    const nsrc = (t, dur) => { const s = ac.createBufferSource(); s.buffer = noise; s.loop = true; s.start(t, R() * 1.5); s.stop(t + dur + .05); return s; };
    const filt = (type, f, q = .7) => { const b = ac.createBiquadFilter(); b.type = type; b.frequency.value = f; b.Q.value = q; return b; };

    const fx = {
      whoosh(t, d = .9, v = .45) {
        const s = nsrc(t, d), f = filt('bandpass', 250, 1.1), g = ac.createGain(), p = ac.createStereoPanner();
        f.frequency.setValueAtTime(250, t); f.frequency.exponentialRampToValueAtTime(2600, t + d * .6); f.frequency.exponentialRampToValueAtTime(450, t + d);
        g.gain.setValueAtTime(1e-4, t); g.gain.exponentialRampToValueAtTime(v, t + d * .6); g.gain.exponentialRampToValueAtTime(1e-4, t + d);
        p.pan.setValueAtTime(-.7, t); p.pan.linearRampToValueAtTime(.7, t + d);
        s.connect(f).connect(g).connect(p); out(p, .4);
      },
      hit(t, v = .9) {
        const o = osc('sine', 120, t, 1.2), g = ac.createGain(); o.frequency.exponentialRampToValueAtTime(38, t + .5); env(g, t, .003, v, .9); o.connect(g); out(g, .25);
        const s = nsrc(t, .2), f = filt('lowpass', 2000), g2 = ac.createGain(); env(g2, t, .002, v * .35, .16); s.connect(f).connect(g2); out(g2, .35);
      },
      boom(t, v = 1) {
        fx.hit(t, v * .8);
        const o = osc('sine', 58, t, 2.4), g = ac.createGain(); o.frequency.exponentialRampToValueAtTime(27, t + 1.8); env(g, t, .01, v * .8, 2.1); o.connect(g); out(g, .1);
        const s = nsrc(t, 2), f = filt('lowpass', 500), g2 = ac.createGain(); env(g2, t, .01, v * .3, 1.8); s.connect(f).connect(g2); out(g2, .6);
      },
      pop(t, p = 1, v = .28) {
        const o = osc('triangle', 520 * p, t, .25), g = ac.createGain(); o.frequency.exponentialRampToValueAtTime(980 * p, t + .06); env(g, t, .003, v, .16); o.connect(g); out(g, .25);
      },
      tick(t, v = .14) { const s = nsrc(t, .05), f = filt('highpass', 4500), g = ac.createGain(); env(g, t, .001, v, .03); s.connect(f).connect(g); out(g, .1); },
      type(t, n = 10, dur = 1, v = .22) {
        for (let i = 0; i < n; i++) {
          const tt = t + (i + .5) * dur / n + (R() - .5) * dur / n * .35;
          const s = nsrc(tt, .06), f = filt('bandpass', 2000 + R() * 900, 2.5), g = ac.createGain(); env(g, tt, .001, v, .04); s.connect(f).connect(g); out(g, .12, (R() - .5) * .3);
          const o = osc('sine', 170 + R() * 30, tt, .08), g2 = ac.createGain(); env(g2, tt, .001, v * .6, .05); o.connect(g2); out(g2, .05);
        }
      },
      scribble(t, d = .8, v = .2) {
        const s = nsrc(t, d), f = filt('bandpass', 2300, 3.5), g = ac.createGain(), N = 96, c = new Float32Array(N);
        for (let i = 0; i < N; i++) c[i] = v * (i === 0 || i === N - 1 ? 0 : .25 + .75 * Math.abs(Math.sin(i * .55 + R() * .8)));
        g.gain.setValueAtTime(0, t); g.gain.setValueCurveAtTime(c, t, d);
        s.connect(f).connect(g); out(g, .15, .15);
      },
      riser(t, d = 2, v = .32) {
        const s = nsrc(t, d), f = filt('highpass', 400), g = ac.createGain();
        f.frequency.setValueAtTime(400, t); f.frequency.exponentialRampToValueAtTime(7000, t + d);
        g.gain.setValueAtTime(1e-4, t); g.gain.exponentialRampToValueAtTime(v, t + d); g.gain.linearRampToValueAtTime(0, t + d + .04);
        s.connect(f).connect(g); out(g, .4);
        const o = osc('sawtooth', 110, t, d + .05), f2 = filt('lowpass', 300), g2 = ac.createGain();
        o.frequency.exponentialRampToValueAtTime(440, t + d); f2.frequency.setValueAtTime(300, t); f2.frequency.exponentialRampToValueAtTime(3200, t + d);
        g2.gain.setValueAtTime(1e-4, t); g2.gain.exponentialRampToValueAtTime(v * .35, t + d); g2.gain.linearRampToValueAtTime(0, t + d + .04);
        o.connect(f2).connect(g2); out(g2, .3);
      },
      shimmer(t, v = .16) {
        [24, 31, 36, 40, 43].forEach((n, i) => { const tt = t + i * .07, o = osc('sine', hz((S.root || 60) + n), tt, 2.6), g = ac.createGain(); env(g, tt, .01, v, 2.4); o.connect(g); out(g, .9, (i - 2) * .25); });
      },
      // soft piano-ish note (midi number)
      note(t, n, v = .14, d = 2.2) {
        [['sine', 1, 1], ['triangle', 2, .25], ['sine', 3, .08]].forEach(([type, mul, a]) => {
          const o = osc(type, hz(n) * mul, t, d + .1), g = ac.createGain(); env(g, t, .006, v * a, d); o.connect(g); out(g, .55);
        });
      },
      glitch(t, d = .45, v = .3) {
        const N = 48, c = new Float32Array(N); for (let i = 0; i < N; i++) c[i] = R() < .55 ? v * (.4 + .6 * R()) : 0; c[0] = c[N - 1] = 0;
        const s = nsrc(t, d), f = filt('bandpass', 1500 + R() * 2500, .8), g = ac.createGain(); g.gain.setValueAtTime(0, t); g.gain.setValueCurveAtTime(c, t, d);
        s.connect(f).connect(g); out(g, .1);
        const o = osc('square', 55, t, d + .05), f2 = filt('lowpass', 900), g2 = ac.createGain(); g2.gain.setValueAtTime(0, t); g2.gain.setValueCurveAtTime(c.map(x => x * .5), t, d);
        o.connect(f2).connect(g2); out(g2, 0);
      },
      rotor(t, d = 3, v = .25, rate = 11) {
        const N = Math.max(8, Math.floor(d * rate * 4)), c = new Float32Array(N);
        for (let i = 0; i < N; i++) { const u = i / (N - 1), envl = Math.min(1, u * 5, (1 - u) * 5); c[i] = v * envl * Math.pow(Math.sin(Math.PI * i / 4), 2); }
        const s = nsrc(t, d), f = filt('lowpass', 260, 1.2), g = ac.createGain(); g.gain.setValueAtTime(0, t); g.gain.setValueCurveAtTime(c, t, d);
        s.connect(f).connect(g); out(g, .15);
      },
      blip(t, p = 1, v = .16) {
        [0, .07].forEach((dt, i) => { const o = osc('square', 880 * p * (i ? 1.5 : 1), t + dt, .14), f = filt('lowpass', 3200), g = ac.createGain(); env(g, t + dt, .002, v, .08); o.connect(f).connect(g); out(g, .3, i ? .3 : -.3); });
      },
    };

    // music bed
    const beat = 60 / (S.bpm || 100), chords = S.chords || [[0, 4, 7]], seg = DUR / chords.length, root = S.root || 60;
    const chordAt = t => chords[Math.min(chords.length - 1, Math.floor(t / seg))];
    if (S.pad) chords.forEach((ch, i) => {
      const t0 = i * seg;
      ch.forEach(n => [-8, 8].forEach(det => {
        const o = ac.createOscillator(); o.type = 'sawtooth'; o.frequency.value = hz(root + n); o.detune.value = det;
        const f = filt('lowpass', S.padCut || 900, .4), g = ac.createGain();
        g.gain.setValueAtTime(0, t0); g.gain.linearRampToValueAtTime(S.pad, t0 + .5); g.gain.setValueAtTime(S.pad, t0 + seg - .2); g.gain.linearRampToValueAtTime(0, t0 + seg + .5);
        o.connect(f).connect(g); out(g, .6); o.start(t0); o.stop(t0 + seg + .6);
      }));
    });
    if (S.drone) { const o = osc('sine', hz(root - 24), 0, DUR), o2 = osc('triangle', hz(root - 12), 0, DUR), g = ac.createGain(); g.gain.value = S.drone; const f = filt('lowpass', 400); o.connect(g); o2.connect(f).connect(g); out(g, .2); }
    const steps = Math.floor(DUR / (beat / 2));
    for (let b = 0; b < steps; b++) {
      const t = b * beat / 2, ch = chordAt(t);
      if (t >= (S.bedTo ?? Infinity)) break;   // optional: the rhythm parts stop here (pad and drone go on)
      if (S.pluck && t >= at(S.pluckFrom || 0)) {
        const seq = [0, 1, 2, 3, 2, 1, 3, 2], n = root + 12 + ch[seq[b % 8] % ch.length] + (b % 16 >= 8 ? 12 : 0);
        const o = osc('triangle', hz(n), t, .5), f = filt('lowpass', 2600), g = ac.createGain(); env(g, t, .003, S.pluck, .38); o.connect(f).connect(g); out(g, .35, b % 2 ? .25 : -.25);
      }
      if (S.bass && b % 2 === 0 && t >= at(S.bassFrom || 0)) { const o = osc('sine', hz(root - 12 + ch[0]), t, beat), g = ac.createGain(); env(g, t, .006, S.bass, beat * .85); o.connect(g); out(g, 0); }
      if (S.pulse && t >= at(S.pulseFrom || 0)) { const o = osc('sawtooth', hz(root - 12 + ch[0]), t, beat / 2), f = filt('lowpass', 420, 2), g = ac.createGain(); env(g, t, .004, S.pulse, beat * .4); o.connect(f).connect(g); out(g, .1); }
      if (S.hats && b % 2 === 1 && t >= at(S.hatsFrom || 0)) { const s = nsrc(t, .06), f = filt('highpass', 8000), g = ac.createGain(); env(g, t, .001, S.hats, .035); s.connect(f).connect(g); out(g, .1, .2); }
      if (S.kick && b % 2 === 0 && t >= at(S.kickFrom || 0)) { const o = osc('sine', 150, t, .4), g = ac.createGain(); o.frequency.exponentialRampToValueAtTime(42, t + .12); env(g, t, .002, S.kick, .3); o.connect(g); out(g, 0); }
    }
    for (const [t, name, ...a] of S.cues || []) fx[name](at(t), ...a);
    // optional: a page writes its own instruments on the same bus (used by the showreel)
    if (S.compose) S.compose({ ac, out, osc, nsrc, filt, env, hz, R, fx, DUR, master });

    const buf = await ac.startRendering();
    // with a voice: level the music so it sits a fixed distance under the words (VO_META.voiceDb + bedDb), never clipping
    if (vd && window.VO_META && window.VO_META.voiceDb != null) {
      const hz = window.VO_META.envHz || 25, chs = [0, 1].map(c => buf.getChannelData(c));
      let s = 0, n = 0, peak = 0;
      vd.env.forEach((v, k) => { if (v <= 70) return; for (let i = Math.floor(k / hz * sr), e = Math.min(buf.length, Math.floor((k + 1) / hz * sr)); i < e; i++) { s += chs[0][i] ** 2 + chs[1][i] ** 2; n += 2; } });
      for (const d of chs) for (let i = 0; i < d.length; i++) peak = Math.max(peak, Math.abs(d[i]));
      if (n && peak) {
        const k = Math.min(10 ** ((window.VO_META.voiceDb + window.VO_META.bedDb - 10 * Math.log10(s / n)) / 20), .98 / peak);
        for (const d of chs) for (let i = 0; i < d.length; i++) d[i] *= k;
      }
    }
    // optional: scale the whole mix so its loudest sample sits at S.peak (e.g. .89 = -1 dBFS), never clipping
    if (S.peak) {
      const chs = [0, 1].map(c => buf.getChannelData(c)); let p = 0;
      for (const d of chs) for (let i = 0; i < d.length; i++) p = Math.max(p, Math.abs(d[i]));
      if (p) { const k = S.peak / p; for (const d of chs) for (let i = 0; i < d.length; i++) d[i] *= k; }
    }
    return URL.createObjectURL(wav(buf));
  }
  function wav(buf) {
    const ch = buf.numberOfChannels, n = buf.length, dv = new DataView(new ArrayBuffer(44 + n * ch * 2));
    const w = (o, s) => { for (let i = 0; i < s.length; i++) dv.setUint8(o + i, s.charCodeAt(i)); };
    w(0, 'RIFF'); dv.setUint32(4, 36 + n * ch * 2, true); w(8, 'WAVE'); w(12, 'fmt '); dv.setUint32(16, 16, true); dv.setUint16(20, 1, true);
    dv.setUint16(22, ch, true); dv.setUint32(24, buf.sampleRate, true); dv.setUint32(28, buf.sampleRate * ch * 2, true); dv.setUint16(32, ch * 2, true);
    dv.setUint16(34, 16, true); w(36, 'data'); dv.setUint32(40, n * ch * 2, true);
    const data = []; for (let c = 0; c < ch; c++) data.push(buf.getChannelData(c));
    let o = 44; for (let i = 0; i < n; i++) for (let c = 0; c < ch; c++) { const s = Math.max(-1, Math.min(1, data[c][i])); dv.setInt16(o, s < 0 ? s * 0x8000 : s * 0x7FFF, true); o += 2; }
    return new Blob([dv], { type: 'audio/wav' });
  }

  // ───────────────────────── player ─────────────────────────
  // URL options: ?ar=45 (4:5 frame, 1080×1350) · ?post=12|8|6|0 (posterize time) · ?t=3.2 · ?paused · ?embed (inside index.html) · ?nobar
  window.MapPlayer = async function (o) {
    const QS = new URLSearchParams(location.search), clamp = K.clamp;
    const VERT = /^(45|4x5|4:5|v|vertical)$/i.test(QS.get('ar') || '');
    // a voice in audio/vo-env.js sets the length; the picture is retimed to it through o.voWarp ([clock s, animation s] pairs)
    const VID = ((o.vo && /(\d\d)-vo/.exec(o.vo[0])) || [])[1], VD = (window.VO_DATA && VID && window.VO_DATA[VID]) || null;
    const DUR0 = o.DUR || 10, DUR = VD ? VD.total : DUR0;
    const warp = VD ? makeWarp([[0, 0], ...(o.voWarp || []).filter(p => p[0] > 0 && p[0] < DUR), [DUR, DUR0]]) : null;
    const W = o.W || (VERT ? 1080 : 1920), H = o.H || (VERT ? 1350 : 1080), FPS = o.FPS || 30, FRAMES = Math.round(DUR * FPS);
    const END = (FRAMES - 1) / FPS, EMBED = QS.has('embed') && parent !== window;
    const st = document.createElement('style'); st.textContent = CSS; document.head.appendChild(st);
    document.body.insertAdjacentHTML('afterbegin', `<div id="wrap"><div id="stage"><div id="loader">Loading map…</div></div></div><div id="bar">${BAR(o.title || '', FRAMES, DUR, VERT)}</div>`);
    const $ = id => document.getElementById(id), stage = $('stage');
    stage.style.width = W + 'px'; stage.style.height = H + 'px';
    if (EMBED) document.body.classList.add('embed');
    if ((navigator.webdriver && !QS.has('player')) || QS.has('nobar')) document.body.classList.add('nobar');
    const fit = () => { const wr = $('wrap'), k = Math.min(wr.clientWidth / W, wr.clientHeight / H); stage.style.transform = `translate(${(wr.clientWidth - W * k) / 2}px, ${(wr.clientHeight - H * k) / 2}px) scale(${k})`; };
    fit(); addEventListener('resize', fit); document.addEventListener('fullscreenchange', () => setTimeout(fit, 50));

    // posterize time (After Effects style): the clock and the sound run smoothly, the picture holds each pose for 1/fps s
    const POST_KEY = 'mapdemo.posterize';
    let post = { on: false, fps: 12 };
    try { const s = JSON.parse(localStorage.getItem(POST_KEY) || 'null'); if (s) post = { on: !!s.on, fps: +s.fps || 12 }; } catch {}
    if (QS.has('post')) { const v = +QS.get('post'); post = v > 0 ? { on: true, fps: v } : { ...post, on: false }; }
    const shownT = t => post.on ? Math.min(END, Math.floor(t * post.fps + 1e-6) / post.fps) : t;

    let draw;
    try { draw = await o.setup(stage, { W, H, FPS, DUR: DUR0, VERT }); }
    catch (e) { $('loader').textContent = 'Could not load (internet is needed for map data): ' + e.message; throw e; }
    $('loader').remove();

    // audio tracks: synthesized score + optional voice-over file
    const tracks = [];
    const P = { t: 0, playing: false, speed: 1, loop: true, wall: 0, t0: 0, muted: false, vol: 1, drawn: -1, uiT: -1, unlocked: EMBED, blocked: false };
    if (o.score) {
      const music = new Audio(); music.preload = 'auto'; music._g = 1; music.volume = P.vol; music.src = await synthScore(DUR, o.score, warp, VD); tracks.push(music);
      if (o.vo) {
        // a blob URL is always seekable (simple servers without Range support are not); file:// falls back to plain src
        let list = VD ? ['audio/' + VD.file] : o.vo;
        for (const u of o.vo) { try { const r = await fetch(u); if (r.ok) { list = [URL.createObjectURL(await r.blob())]; break; } } catch { break; } }
        const vo = new Audio(); vo.preload = 'auto'; vo._g = VD ? VD.gain : 1; vo._off = VD ? VD.delay : 0; let i = 0;
        const next = () => { if (i < list.length) vo.src = list[i++]; };
        vo.addEventListener('error', next);
        // with vo-env.js the music is already ducked under the words; without it, a flat 40 %
        vo.addEventListener('loadedmetadata', () => { tracks.push(vo); if (!VD) music._g = .4; $('voTag').style.display = 'inline-block'; audioSync(true); });
        next();
      }
    }
    const frameOf = t => Math.min(FRAMES - 1, Math.max(0, Math.floor(t * FPS + 1e-6)));
    function ui() {
      const f = frameOf(P.t);
      $('time').innerHTML = `<b>${P.t.toFixed(2)}</b> / ${DUR.toFixed(2)}`;
      $('frame').innerHTML = `<b>${f}</b> / ${FRAMES - 1}`;
      if (!P.scrub) $('seek').value = f;
      $('playIcon').setAttribute('d', P.playing ? 'M6 4h4v16H6zm8 0h4v16h-4z' : 'M7 4v16l13-8z');
      $('bLoop').classList.toggle('on', P.loop);
      $('bPost').classList.toggle('on', post.on); $('postFps').value = String(post.fps);
      $('muteIcon').setAttribute('d', P.muted || P.vol === 0
        ? 'M3 9v6h4l5 5V4L7 9zm13.6 3 2.7-2.7-1.4-1.4-2.7 2.7-2.7-2.7-1.4 1.4 2.7 2.7-2.7 2.7 1.4 1.4 2.7-2.7 2.7 2.7 1.4-1.4z'
        : 'M3 9v6h4l5 5V4L7 9zm13.5 3A4.5 4.5 0 0 0 14 8v8a4.5 4.5 0 0 0 2.5-4z');
    }
    function render(force) {
      const tq = shownT(P.t);
      if (force || tq !== P.drawn) { draw(warp ? warp.f(tq) : tq, frameOf(tq)); P.drawn = tq; }
      if (force || P.t !== P.uiT) { ui(); P.uiT = P.t; }
    }
    // the sound follows the clock: seek, pause, speed, mute, volume
    function audioSync(hard) {
      for (const a of tracks) {
        a.muted = P.muted; a.volume = clamp(P.vol * a._g); a.playbackRate = P.speed; a.preservesPitch = true;
        const at = P.t - (a._off || 0), past = a.duration && at >= a.duration - .02;   // the voice may start a little after 0
        if (!P.playing || !P.unlocked || past || at < 0) {
          if (!a.paused) a.pause();
          if (a.readyState && !past && (hard || (at < 0 && a.currentTime > .02))) a.currentTime = Math.max(0, at);
          continue;
        }
        if (hard || Math.abs(a.currentTime - at) > .15) a.currentTime = at;
        if (a.paused && !P.blocked) a.play().catch(() => { P.blocked = true; });
      }
    }
    function setTime(t) { P.t = clamp(t, 0, END); P.t0 = P.t; P.wall = performance.now(); audioSync(true); render(); }
    function play() { if (P.playing) return; if (P.t >= END && !P.loop) P.t = 0; P.playing = true; P.t0 = P.t; P.wall = performance.now(); audioSync(true); ui(); }
    function pause() { if (!P.playing) return; P.playing = false; audioSync(true); ui(); }
    const toggle = () => P.playing ? pause() : play();
    const step = n => { pause(); setTime((frameOf(P.t) + n + FRAMES) % FRAMES / FPS); };
    function setPost(on, fps) {
      post = { on, fps: fps || post.fps };
      try { localStorage.setItem(POST_KEY, JSON.stringify(post)); } catch {}
      if (EMBED) parent.postMessage({ mapdemo: 'post', on: post.on, fps: post.fps }, '*');
      render(true);
    }
    function advance(now) {
      if (P.playing) {
        let t = P.t0 + (now - P.wall) / 1000 * P.speed;
        if (t >= DUR) {
          if (P.loop) { t %= DUR; P.t0 = t; P.wall = now; P.t = t; audioSync(true); }
          else { P.t = END; P.playing = false; audioSync(true); render(true); return; }
        }
        P.t = t; audioSync(false);
      }
      render();
    }
    // rAF drives the clock; some embedded views (app panes, OBS sources) stop rAF, so a timer takes over when it stalls
    let lastRaf = 0;
    function tick(now) { lastRaf = performance.now(); advance(now); requestAnimationFrame(tick); }
    setInterval(() => { const now = performance.now(); if (now - lastRaf > 120) advance(now); }, 33);
    // browsers start sound only after a gesture: the first click / key switches it on, in sync
    const unlock = e => { const first = !P.unlocked; P.unlocked = true; P.blocked = false; if (first) P.skipClick = stage.contains(e.target); audioSync(true); };
    addEventListener('pointerdown', unlock, true); addEventListener('keydown', unlock, true);
    document.querySelectorAll('#bar button').forEach(b => b.addEventListener('pointerup', () => setTimeout(() => b.blur(), 0)));
    $('bPlay').onclick = toggle;
    stage.onclick = () => { if (P.skipClick) { P.skipClick = false; return; } toggle(); };
    $('bRestart').onclick = () => setTime(0);
    $('bPrev').onclick = () => step(-1); $('bNext').onclick = () => step(1);
    $('seek').oninput = e => { P.scrub = true; setTime(+e.target.value / FPS); };
    $('seek').onchange = () => { P.scrub = false; };
    $('speed').onchange = e => { P.t0 = P.t; P.wall = performance.now(); P.speed = +e.target.value; audioSync(true); e.target.blur(); };
    $('bLoop').onclick = () => { P.loop = !P.loop; ui(); };
    $('bPost').onclick = () => setPost(!post.on);
    $('postFps').onchange = e => { setPost(true, +e.target.value); e.target.blur(); };
    $('bAr').onclick = () => { const q = new URLSearchParams(location.search); if (VERT) q.delete('ar'); else q.set('ar', '45'); q.set('t', P.t.toFixed(3)); if (!P.playing) q.set('paused', ''); else q.delete('paused'); location.search = q.toString(); };
    $('bMute').onclick = () => { P.muted = !P.muted; audioSync(false); ui(); };
    $('vol').oninput = e => { P.vol = +e.target.value; if (P.vol > 0) P.muted = false; audioSync(false); ui(); };
    $('bFull').onclick = () => document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
    function onKey(k, sh, e) {
      const stop = () => e && e.preventDefault();
      if (k === ' ' || k === 'k' || k === 'K') { stop(); if (!(e && e.repeat)) toggle(); }
      else if (k === 'ArrowLeft') { stop(); sh ? (pause(), setTime(P.t - 1)) : step(-1); }
      else if (k === 'ArrowRight') { stop(); sh ? (pause(), setTime(P.t + 1)) : step(1); }
      else if (k === ',' || k === '<') { stop(); step(-1); }
      else if (k === '.' || k === '>') { stop(); step(1); }
      else if (k === 'Home') { stop(); setTime(0); }
      else if (k === 'End') { stop(); pause(); setTime(END); }
      else if (k === 'm' || k === 'M') { P.muted = !P.muted; audioSync(false); ui(); }
      else if (k === 'l' || k === 'L') { P.loop = !P.loop; ui(); }
      else if (k === 'p' || k === 'P') setPost(!post.on);
      else if (k === 'f' || k === 'F') $('bFull').onclick();
      else return false;
      return true;
    }
    addEventListener('keydown', e => {
      if (e.target.tagName === 'SELECT' || e.ctrlKey || e.metaKey || e.altKey) return;
      if (EMBED && /^(\[|\]|v|V|PageUp|PageDown)$/.test(e.key)) { e.preventDefault(); parent.postMessage({ mapdemo: 'hubKey', key: e.key }, '*'); return; }   // demo / frame switch keys belong to index.html
      if (onKey(e.key, e.shiftKey, e) && e.target.type === 'range') e.target.blur();
    });
    // index.html talks to an embedded player: forwarded keys, posterize, and it hears the time back (to keep it on a 16:9 ⇄ 4:5 switch)
    addEventListener('message', e => {
      const d = e.data; if (!d || typeof d !== 'object') return;
      if (d.mapdemo === 'key') { P.unlocked = true; P.blocked = false; onKey(d.key, d.shiftKey); }
      else if (d.mapdemo === 'setPost') setPost(!!d.on, d.fps);
    });
    if (EMBED) {
      setInterval(() => parent.postMessage({ mapdemo: 'time', t: P.t, playing: P.playing }, '*'), 200);
      parent.postMessage({ mapdemo: 'ready', post: post.on, fps: post.fps, vert: VERT }, '*');
    }
    const t0 = parseFloat(QS.get('t')); P.t = isFinite(t0) ? clamp(t0, 0, END) : 0;
    render(true);
    if (!QS.has('paused')) play();
    requestAnimationFrame(tick);
    window.__player = { P, setTime, play, pause, tracks, render, setPost, get post() { return post; } };
  };
})();
