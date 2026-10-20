// Map graphics in the look of the channel's Falklands film: white pills, big see-through country names,
// glowing dashed lines with distance labels, red / yellow tags, stat lines with icons, coastline draw-on,
// country highlights (tint × land mask, bevel, white edge). All drawn from t alone.
(function () {
  const F = 'Montserrat, "Segoe UI", Arial, sans-serif';
  const UI = { F };
  const E = K.ease, cl = K.clamp;
  UI.fonts = () => K.fonts(['500 20px Montserrat', '600 20px Montserrat', '700 20px Montserrat', '800 20px Montserrat', '900 20px Montserrat',
    'italic 700 20px Montserrat', 'italic 800 20px Montserrat']);
  // appear / disappear envelope: 0→1 over [a, a+d], 1→0 over [b, b+d]
  UI.env = (t, a, b = 1e9, d = .35) => Math.min(cl((t - a) / d), 1 - cl((t - b) / d));
  UI.pop = (t, a, d = .45) => E.outBack(cl((t - a) / d));

  function rr(g, x, y, w, h, r) { g.beginPath(); g.moveTo(x + r, y); g.arcTo(x + w, y, x + w, y + h, r); g.arcTo(x + w, y + h, x, y + h, r); g.arcTo(x, y + h, x, y, r); g.arcTo(x, y, x + w, y, r); g.closePath(); }
  UI.rr = rr;


  // ── text with a hard, offset drop shadow (no blur): the letters stand on a solid darker copy, stacked
  // 1 px at a time down-right, so it reads as a small extrusion. Drawn on a scratch canvas first, so a
  // see-through label stays even (the stacked copies do not add up), then placed with the caller's transform.
  const SCR = { c: null, g: null };
  UI._scr = SCR;   // QA (tools/bleed.js)
  UI.text3d = (g, str, x, y, { size = 30, weight = 800, italic = false, fill = '#fff', shade = 'rgb(22,32,38)', depth, align = 'left', base = 'middle', spacing = 0, alpha = 1, dir = [1, 1] } = {}) => {
    if (alpha <= 0 || !str) return 0;
    if (!SCR.c) { SCR.c = K.canvas(2400, 400); SCR.g = SCR.c.getContext('2d'); }
    const sg = SCR.g, font = `${italic ? 'italic ' : ''}${weight} ${size}px ${F}`;
    sg.setTransform(1, 0, 0, 1, 0, 0); sg.font = font; sg.textBaseline = 'alphabetic'; sg.textAlign = 'left';
    if ('letterSpacing' in sg) sg.letterSpacing = spacing + 'px';
    const m = sg.measureText(str), tw = Math.ceil(m.width + spacing), d = depth ?? Math.max(2, Math.round(size * .06));
    const asc = Math.ceil(m.actualBoundingBoxAscent || size * .8), desc = Math.ceil(m.actualBoundingBoxDescent || size * .2);
    const pad = d + 4, w = Math.min(2400, tw + pad * 2 + Math.ceil(size * .2)), h = Math.min(400, asc + desc + pad * 2);
    // clear a margin too: the copy below is filtered, and its edge pixels would pick up what an earlier, larger label
    // left on the scratch canvas (it showed as a faint line under some labels)
    sg.clearRect(0, 0, Math.min(2400, w + 6), Math.min(400, h + 6));
    const ox = pad, oy = pad + asc;
    sg.fillStyle = shade;
    for (let k = d; k >= 1; k--) sg.fillText(str, ox + k * dir[0], oy + k * dir[1]);
    sg.fillStyle = fill; sg.fillText(str, ox, oy);
    const ax = align === 'center' ? tw / 2 : align === 'right' ? tw : 0;
    const ay = base === 'middle' ? (asc - desc) / 2 : base === 'top' ? -asc : 0;
    g.save(); g.globalAlpha *= cl(alpha);
    g.drawImage(SCR.c, 0, 0, w, h, x - ax - ox, y + ay - oy, w, h);
    g.restore();
    return tw;
  };

  // ── flat paths (Mercator) → screen ──
  UI.path = (g, api, rings, { close = true } = {}) => {
    g.beginPath();
    for (const r of rings) {
      let first = true;
      for (let i = 0; i < r.length; i += 2) {
        const p = api.Pm(r[i], r[i + 1]); if (!p) { first = true; continue; }
        if (first) { g.moveTo(p[0], p[1]); first = false; } else g.lineTo(p[0], p[1]);
      }
      if (close) g.closePath();
    }
  };
  UI.lonlatPath = (g, api, pts) => { g.beginPath(); pts.forEach((p, i) => { const s = api.P(p[0], p[1]); if (s) i ? g.lineTo(s[0], s[1]) : g.moveTo(s[0], s[1]); }); };

  // ── country highlight ──
  // color: tint multiplied into the land (keeps the terrain); a: 0..1; edge: white outline; bevel: soft inner light/shadow;
  // glow: outer glow; lift: dark halo on the sea under the land (reads as raised)
  UI.highlight = (api, rings, { color = '#c4161c', a = 1, edge = 0, edgeColor = '#ffffff', bevel = 0, glow = 0, glowColor = 'rgba(255,255,255,.9)', lift = 0, wash = 0, onLand = true, light = false, sea = false } = {}) => {
    if (a <= 0) return;
    const { tl, tm, tn, tw, ta, ov } = api.C2;
    // light: a pale wash over the land instead of a multiply · sea: a wash over the water only
    let g = sea ? tw.g : light ? tn.g : onLand ? tl.g : tm.g;
    g.save(); g.globalAlpha = a; g.fillStyle = color; UI.path(g, api, rings); g.fill('evenodd'); g.restore();
    if (wash > 0) {   // a light colour wash over everything inside the ring (land and sea) — for big soft areas
      g = ta.g; g.save(); g.globalAlpha = a * wash; g.fillStyle = color; UI.path(g, api, rings); g.fill('evenodd'); g.restore();
    }
    if (lift > 0) {
      g = ta.g; g.save(); g.globalAlpha = a * lift;
      g.shadowColor = 'rgba(0,10,14,.85)'; g.shadowBlur = 18; g.shadowOffsetY = 6; g.fillStyle = '#000';
      UI.path(g, api, rings); g.fill('evenodd'); g.restore();
      g.save(); g.globalCompositeOperation = 'destination-out'; UI.path(g, api, rings); g.fill('evenodd'); g.restore();
    }
    g = ov.g;
    if (bevel > 0) {
      // inner shadow (bottom-right) and inner light (top-left), clipped to the shape
      g.save(); UI.path(g, api, rings); g.clip('evenodd');
      // the stroke is drawn 4000 px off-screen; only its blurred shadow lands inside the shape
      for (const [col, ox, oy, al] of [['rgba(0,0,0,1)', 3, 4, .55], ['rgba(255,235,225,1)', -2, -2, .35]]) {
        g.save(); g.globalAlpha = a * bevel * al; g.shadowColor = col; g.shadowBlur = 8; g.shadowOffsetX = ox + 4000; g.shadowOffsetY = oy;
        g.lineWidth = 5; g.strokeStyle = col; g.translate(-4000, 0); UI.path(g, api, rings); g.stroke(); g.restore();
      }
      g.restore();
    }
    if (glow > 0) {
      g.save(); g.globalAlpha = a * glow; g.shadowColor = glowColor; g.shadowBlur = 16; g.strokeStyle = glowColor; g.lineWidth = 2;
      UI.path(g, api, rings); g.stroke(); g.restore();
    }
    if (edge > 0) {
      g.save(); g.globalAlpha = a * edge; g.strokeStyle = edgeColor; g.lineWidth = 1.6; g.lineJoin = 'round';
      UI.path(g, api, rings); g.stroke(); g.restore();
    }
  };

  // ── coastline draw-on (white glowing line that travels round each ring) ──
  // rings: flat Mercator arrays; f: 0..1 of the total length; precomputed lengths are cached on the rings object
  // bbox [lon0, lat0, lon1, lat1]: draw only the stretches of the rings inside this box. For a big country of which only
  // one coast is on screen, the line then draws on across the whole time instead of crossing the screen in a few frames.
  const BOXED = new WeakMap();
  const boxed = (rings, bb) => {
    const key = bb.join(','), m = BOXED.get(rings) || {};
    if (m[key]) return m[key];
    const x0 = GM.mx(bb[0]), x1 = GM.mx(bb[2]), y0 = GM.my(bb[3]), y1 = GM.my(bb[1]), out = [];
    for (const r of rings) {
      let run = [];
      for (let i = 0; i < r.length; i += 2) {
        if (r[i] >= x0 && r[i] <= x1 && r[i + 1] >= y0 && r[i + 1] <= y1) run.push(r[i], r[i + 1]);
        else { if (run.length >= 4) out.push(run); run = []; }
      }
      if (run.length >= 4) out.push(run);
    }
    out.open = true; m[key] = out; BOXED.set(rings, m);
    return out;
  };
  UI.outline = (api, g, rings, f, { color = '#fff', width = 2.2, glow = 14, alpha = 1, bbox } = {}) => {
    if (f <= 0 || alpha <= 0) return;
    if (bbox) rings = boxed(rings, bbox);
    if (!rings._len) {
      rings._len = rings.map(r => { let L = 0; for (let i = 2; i < r.length; i += 2) L += Math.hypot(r[i] - r[i - 2], r[i + 1] - r[i - 1]); return L; });
      rings._tot = rings._len.reduce((a, b) => a + b, 0);
    }
    g.save(); g.globalAlpha = alpha; g.strokeStyle = color; g.lineWidth = width; g.lineJoin = g.lineCap = 'round';
    g.shadowColor = color; g.shadowBlur = glow;
    // every ring draws at the same pace (fraction of its own length), so small islands finish with the big one
    rings.forEach((r, k) => {
      const L = rings._len[k] * cl(f); let acc = 0; g.beginPath(); let first = true;
      for (let i = 0; i < r.length; i += 2) {
        if (i) { const d = Math.hypot(r[i] - r[i - 2], r[i + 1] - r[i - 1]); if (acc + d > L) { const u = (L - acc) / d; const p = api.Pm(r[i - 2] + (r[i] - r[i - 2]) * u, r[i - 1] + (r[i + 1] - r[i - 1]) * u); if (p) g.lineTo(p[0], p[1]); break; } acc += d; }
        const p = api.Pm(r[i], r[i + 1]); if (!p) continue;
        if (first) { g.moveTo(p[0], p[1]); first = false; } else g.lineTo(p[0], p[1]);
      }
      if (f >= 1 && !rings.open) g.closePath();
      g.stroke();
    });
    g.restore();
  };

  // ── white pill label with a pin (as "Stanley", "Falkland Islands") ──
  UI.pill = (g, x, y, text, { a = 1, s = 1, pin = true, dot = true, lift = 34, size = 15, dark = false } = {}) => {
    if (a <= 0) return;
    g.save(); g.globalAlpha = cl(a);
    const k = .7 + .3 * E.outBack(cl(a));
    g.font = `700 ${size * s}px ${F}`;
    const tw = g.measureText(text).width, ph = 26 * s, pad = 10 * s, iw = pin ? 14 * s : 0;
    const w = tw + pad * 2 + iw, bx = x - w / 2, by = y - lift * s - ph;
    if (dot) {
      g.strokeStyle = 'rgba(255,255,255,.95)'; g.lineWidth = 1.5 * s; g.beginPath(); g.moveTo(x, y - 4 * s); g.lineTo(x, by + ph); g.stroke();
      g.fillStyle = '#e8262b'; g.beginPath(); g.arc(x, y, 4.2 * s, 0, 7); g.fill();
      g.strokeStyle = '#fff'; g.lineWidth = 1.6 * s; g.stroke();
    }
    g.translate(x, by + ph); g.scale(k, k); g.translate(-x, -(by + ph));
    g.shadowColor = 'rgba(0,0,0,.35)'; g.shadowBlur = 10 * s; g.shadowOffsetY = 3 * s;
    g.fillStyle = dark ? '#0b1622' : '#fff'; rr(g, bx, by, w, ph, 5 * s); g.fill();
    g.shadowColor = 'transparent';
    if (pin) UI.icon(g, 'pin', bx + pad + 5 * s, by + ph / 2, 13 * s, '#e8262b');
    g.fillStyle = dark ? '#fff' : '#111'; g.textBaseline = 'middle'; g.textAlign = 'left';
    g.fillText(text, bx + pad + iw, by + ph / 2 + .5 * s);
    g.restore();
  };

  // ── big see-through name on the map (country / sea), letter by letter ──
  // still: no letter-by-letter rise, the whole name only fades with a (a name that is on screen from the first frame)
  UI.bigName = (g, x, y, text, { a = 1, size = 54, angle = 0, spacing = .18, color = '#fff', alpha = .82, italic = false, weight = 800, stagger = .5, still = false } = {}) => {
    if (a <= 0) return;
    g.save(); g.translate(x, y); g.rotate(angle);
    g.font = `${italic ? 'italic ' : ''}${weight} ${size}px ${F}`;
    if ('letterSpacing' in g) g.letterSpacing = '0px';
    // colour strings with alpha ("rgba(255,255,255,.6)") set the label's see-through level
    const mm = /rgba\([^)]*,\s*([\d.]+)\)/.exec(color); const al = mm ? +mm[1] : alpha, fill = mm ? color.replace(/,\s*[\d.]+\)$/, ',1)') : color;
    const sp = size * spacing, ws = [...text].map(c => g.measureText(c).width), tot = ws.reduce((p, v) => p + v, 0) + sp * (text.length - 1);
    // one baseline for every letter: centred on the font's cap height, not on each letter's own box
    // (letters such as S, G, O dip below the line and were set a little higher or lower than I, R, A)
    const capH = g.measureText('H').actualBoundingBoxAscent || size * .7, base = capH / 2;
    let cx = -tot / 2;
    [...text].forEach((c, i) => {
      const u = still ? 1 : cl(a * (1 + stagger) - stagger * i / Math.max(1, text.length - 1));
      UI.text3d(g, c, cx, base + (1 - E.out(u)) * size * .35, { size, weight, italic, fill, alpha: (still ? cl(a) : E.out(u)) * al, base: 'alphabetic' });
      cx += ws[i] + sp;
    });
    g.restore();
  };

  // ── small white caps label with glow ("EAST FALKLAND") ──
  UI.caps = (g, x, y, text, { a = 1, size = 16, spacing = 1.5, align = 'center', color = '#fff' } = {}) => {
    if (a <= 0) return;
    UI.text3d(g, text, x, y + (1 - E.out(cl(a))) * 8, { size, weight: 800, fill: color, spacing, align, alpha: cl(a) });
  };

  // ── dashed line (great-circle-ish polyline in lon/lat), drawn on from start, with optional head ──
  UI.dash = (api, g, pts, f, { a = 1, width = 3.2, dash = [10, 8], color = '#fff', glow = 10, head = false, headSize = 14 } = {}) => {
    if (f <= 0 || a <= 0) return null;
    const sp = pts.map(p => api.P(p[0], p[1])).filter(Boolean);
    if (sp.length < 2) return null;
    const cum = [0]; for (let i = 1; i < sp.length; i++) cum.push(cum[i - 1] + Math.hypot(sp[i][0] - sp[i - 1][0], sp[i][1] - sp[i - 1][1]));
    const L = cum[cum.length - 1] * cl(f), out = [sp[0]];
    for (let i = 1; i < sp.length; i++) {
      if (cum[i] <= L) { out.push(sp[i]); continue; }
      const u = (L - cum[i - 1]) / (cum[i] - cum[i - 1]); out.push([sp[i - 1][0] + (sp[i][0] - sp[i - 1][0]) * u, sp[i - 1][1] + (sp[i][1] - sp[i - 1][1]) * u]); break;
    }
    g.save(); g.globalAlpha = cl(a); g.strokeStyle = color; g.lineWidth = width; g.lineCap = 'round'; g.setLineDash(dash);
    g.shadowColor = color; g.shadowBlur = glow;
    K.polyline(g, out); g.stroke(); g.setLineDash([]);
    const e = out[out.length - 1], p0 = out[Math.max(0, out.length - 2)];
    if (head && out.length > 1) {
      const ang = Math.atan2(e[1] - p0[1], e[0] - p0[0]);
      g.fillStyle = color; g.beginPath(); g.moveTo(e[0] + Math.cos(ang) * headSize * .6, e[1] + Math.sin(ang) * headSize * .6);
      g.lineTo(e[0] + Math.cos(ang + 2.5) * headSize, e[1] + Math.sin(ang + 2.5) * headSize);
      g.lineTo(e[0] + Math.cos(ang - 2.5) * headSize, e[1] + Math.sin(ang - 2.5) * headSize); g.closePath(); g.fill();
    }
    g.restore();
    return { end: e, mid: sp[Math.floor(sp.length / 2)], pts: sp };
  };

  // ── solid swept arrow (thick white, tapered tail, as the Falklands "1982" moves) ──
  UI.arrow = (api, g, pts, f, { a = 1, width = 7, color = '#fff', glow = 12, headSize = 26, tail = .0 } = {}) => {
    if (f <= 0 || a <= 0) return;
    const sp = pts.map(p => api.P(p[0], p[1])).filter(Boolean);
    if (sp.length < 2) return;
    const line = K.smoothLine(sp, 8), seg = K.partial(line, cl(f));
    if (seg.length < 2) return;
    const startF = cl(tail);
    g.save(); g.globalAlpha = cl(a); g.lineCap = 'round'; g.lineJoin = 'round';
    g.shadowColor = 'rgba(0,0,0,.45)'; g.shadowBlur = glow;
    const n = seg.length;
    for (let i = 1; i < n; i++) {   // width tapers from the tail
      const u = i / (n - 1); if (u < startF) continue;
      g.strokeStyle = color; g.lineWidth = width * (.35 + .65 * u);
      g.beginPath(); g.moveTo(seg[i - 1][0], seg[i - 1][1]); g.lineTo(seg[i][0], seg[i][1]); g.stroke();
    }
    const e = seg[n - 1], p0 = seg[Math.max(0, n - 4)], ang = Math.atan2(e[1] - p0[1], e[0] - p0[0]);
    g.fillStyle = color; g.beginPath();
    g.moveTo(e[0] + Math.cos(ang) * headSize * .55, e[1] + Math.sin(ang) * headSize * .55);
    g.lineTo(e[0] + Math.cos(ang + 2.45) * headSize, e[1] + Math.sin(ang + 2.45) * headSize);
    g.lineTo(e[0] + Math.cos(ang + Math.PI) * headSize * .25, e[1] + Math.sin(ang + Math.PI) * headSize * .25);
    g.lineTo(e[0] + Math.cos(ang - 2.45) * headSize, e[1] + Math.sin(ang - 2.45) * headSize);
    g.closePath(); g.fill();
    g.restore();
  };

  // ── dots travelling along a lon/lat polyline (ships on a route, oil in a pipe) ──
  // t0: the time the line starts to draw. The dots then leave the root of the line one after another (each grows out of
  // the root) and never run ahead of the drawn line (head: how much of the line is drawn, 0..1). Without t0 the dots are
  // spread along the whole line at once (only for lines that are on screen from the start).
  UI.flow = (api, g, pts, t, { a = 1, n = 6, speed = .12, size = 4.5, color = '#FFD23F', from = 0, to = 1, t0 = null, head = 1 } = {}) => {
    if (a <= 0 || (t0 != null && t <= t0)) return;
    const sp = pts.map(p => api.P(p[0], p[1])).filter(Boolean);
    if (sp.length < 2) return;
    const cum = [0]; for (let i = 1; i < sp.length; i++) cum.push(cum[i - 1] + Math.hypot(sp[i][0] - sp[i - 1][0], sp[i][1] - sp[i - 1][1]));
    const L = cum[cum.length - 1];
    g.save(); g.fillStyle = color; g.shadowColor = color; g.shadowBlur = 10;
    for (let k = 0; k < n; k++) {
      let ph;
      if (t0 != null) { ph = (t - t0) * speed - k / n; if (ph <= 0) continue; ph = Math.min(ph % 1, cl(head)); }
      else ph = (t * speed + k / n) % 1;
      const u = from + (to - from) * ph, d = u * L;
      let i = 1; while (i < sp.length - 1 && cum[i] < d) i++;
      const v = (d - cum[i - 1]) / Math.max(1e-6, cum[i] - cum[i - 1]);
      const x = sp[i - 1][0] + (sp[i][0] - sp[i - 1][0]) * v, y = sp[i - 1][1] + (sp[i][1] - sp[i - 1][1]) * v;
      g.globalAlpha = cl(a) * Math.min(1, Math.sin(Math.PI * ph) * 3);
      const r = size * (t0 != null ? .3 + .7 * E.out(cl(ph / .08)) : 1);   // grows out of the root
      g.beginPath(); g.arc(x, y, r, 0, 7); g.fill();
    }
    g.restore();
  };

  // ── distance / value label along a line ("500 KM") ──
  UI.distLabel = (g, x, y, text, { a = 1, size = 30, angle = 0, color = '#fff' } = {}) => {
    if (a <= 0) return;
    g.save(); g.translate(x, y); g.rotate(angle);
    UI.text3d(g, text, 0, -12 + (1 - E.out(cl(a))) * 10, { size, weight: 800, italic: true, fill: color, align: 'center', base: 'alphabetic', alpha: cl(a) });
    g.restore();
  };

  // ── tags: yellow year box ("1982"), red box ("NORTHERN IRELAND") ──
  UI.tag = (g, x, y, text, { a = 1, bg = '#FFD23F', fg = '#111', size = 22, align = 'left', pad = 9 } = {}) => {
    if (a <= 0) return;
    g.save(); g.globalAlpha = cl(a); g.font = `900 ${size}px ${F}`;
    const w = g.measureText(text).width + pad * 2, h = size * 1.38;
    const bx = align === 'center' ? x - w / 2 : align === 'right' ? x - w : x;
    const k = E.out(cl(a));
    g.fillStyle = bg; g.fillRect(bx, y - h / 2, w * k, h);
    g.save(); g.beginPath(); g.rect(bx, y - h / 2, w * k, h); g.clip();
    g.fillStyle = fg; g.textBaseline = 'middle'; g.fillText(text, bx + pad, y + size * .04);
    g.restore(); g.restore();
  };

  // ── stat line with an icon ("3,700 PEOPLE") ──
  UI.stat = (g, x, y, icon, text, { a = 1, size = 22, color = '#fff', align = 'center', iconColor } = {}) => {
    if (a <= 0) return;
    g.save(); g.font = `800 ${size}px ${F}`;
    if ('letterSpacing' in g) g.letterSpacing = '1px';
    const tw = g.measureText(text).width, iw = size * 1.5, w = tw + iw;
    const x0 = align === 'center' ? x - w / 2 : x;
    g.globalAlpha = cl(a);
    const d = Math.max(2, Math.round(size * .06));
    for (let k = d; k >= 1; k--) UI.icon(g, icon, x0 + size * .55 + k, y + k, size * 1.1, 'rgb(22,32,38)');
    UI.icon(g, icon, x0 + size * .55, y, size * 1.1, iconColor || color);
    g.restore();
    UI.text3d(g, text, x0 + iw, y + 1, { size, weight: 800, fill: color, spacing: 1, alpha: cl(a) });
  };

  // ── stat block in the channel style (editor-pack stills 11–13): icon above, big number, short caps label below,
  // optional small sub-label. The caller counts the number up; `final` (the last value) fixes the number's left edge,
  // so the digits do not slide sideways while they change.
  UI.statBlock = (g, x, y, icon, value, label, { a = 1, size = 96, color = '#FFD23F', iconColor = '#fff', labelSize = 30, sub, subA = 1, subSize = 22, align = 'center', final } = {}) => {
    if (a <= 0) return;
    const al = cl(a), dy = (1 - E.out(al)) * 18;
    g.save(); g.font = `900 ${size}px ${F}`; if ('letterSpacing' in g) g.letterSpacing = '0px';
    const wFinal = g.measureText(final || value).width; g.restore();
    const left = align === 'center' ? x - wFinal / 2 : x, mid = left + wFinal / 2, isz = size * .64;
    const ix = align === 'center' ? mid : left + isz * .5, iy = y - size * 1.02 + dy, d = Math.max(2, Math.round(isz * .06));
    g.save(); g.globalAlpha = al;
    for (let s = d; s >= 1; s--) UI.icon(g, icon, ix + s, iy + s, isz, 'rgb(22,32,38)');
    UI.icon(g, icon, ix, iy, isz, iconColor);
    g.restore();
    UI.text3d(g, value, left, y + dy, { size, weight: 900, fill: color, alpha: al, shade: color === '#fff' ? 'rgb(22,32,38)' : 'rgb(58,38,0)' });
    const lx = align === 'center' ? mid : left, la = align === 'center' ? 'center' : 'left';
    UI.text3d(g, label, lx, y + size * .74 + dy, { size: labelSize, weight: 800, fill: '#fff', spacing: labelSize * .12, align: la, alpha: al });
    if (sub) UI.text3d(g, sub, lx, y + size * .74 + labelSize * 1.3 + dy, { size: subSize, weight: 600, fill: '#C9D3DD', spacing: 1, align: la, alpha: al * cl(subA) });
  };

  // ── big centred kicker line ──
  UI.kicker = (g, W, y, text, { a = 1, size = 54, color = '#FFD23F', italic = false, sub, subSize = 22 } = {}) => {
    if (a <= 0) return;
    const dy = (1 - E.out(cl(a))) * 14;
    UI.text3d(g, text, W / 2, y + dy, { size, weight: 900, italic, fill: color, spacing: 1, align: 'center', alpha: cl(a), shade: color === '#fff' ? 'rgb(22,32,38)' : 'rgb(58,38,0)' });
    if (sub) UI.text3d(g, sub, W / 2, y + size * .78 + dy, { size: subSize, weight: 700, fill: '#fff', align: 'center', alpha: cl(a) });
  };

  // ── pulse rings at a point ──
  UI.pulse = (g, x, y, t, { a = 1, color = '#ff3b3b', r = 34, period = 1.4, n = 2, dot = 6 } = {}) => {
    if (a <= 0) return;
    g.save(); g.globalAlpha = cl(a);
    for (let k = 0; k < n; k++) {
      const u = ((t / period + k / n) % 1 + 1) % 1;
      g.strokeStyle = color; g.globalAlpha = cl(a) * (1 - u) * .9; g.lineWidth = 2.2;
      g.beginPath(); g.arc(x, y, dot + u * r, 0, 7); g.stroke();
    }
    g.globalAlpha = cl(a); g.fillStyle = color; g.shadowColor = color; g.shadowBlur = 12;
    g.beginPath(); g.arc(x, y, dot, 0, 7); g.fill();
    g.shadowBlur = 0; g.strokeStyle = '#fff'; g.lineWidth = 1.8; g.stroke();
    g.restore();
  };

  // ── icons (drawn, centred at x,y, size s) ──
  UI.icon = (g, name, x, y, s, color = '#fff') => {
    g.save(); g.translate(x, y); g.scale(s / 24, s / 24); g.fillStyle = color; g.strokeStyle = color;
    const P = d => { const p = new Path2D(d); g.fill(p); };
    switch (name) {
      case 'pin': P('M0-11c-4.6 0-8 3.5-8 7.8C-8 2.6 0 11 0 11S8 2.6 8-3.2C8-7.5 4.6-11 0-11zm0 10.7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z'); break;
      case 'people': P('M-5-1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm10 0a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM-5 1c-4 0-8 2-8 5v3h11V6c0-1.5.6-2.9 1.6-3.9C-2.2 1.4-3.6 1-5 1zm10 0c-4 0-8 2-8 5v3h16V6c0-3-4-5-8-5z'); break;
      case 'drop': P('M0-11C0-11-8-1.5-8 3.5a8 8 0 0 0 16 0C8-1.5 0-11 0-11z'); break;
      case 'nodrop': P('M0-11C0-11-8-1.5-8 3.5a8 8 0 0 0 16 0C8-1.5 0-11 0-11z'); g.globalCompositeOperation = 'destination-out'; g.lineWidth = 3; g.beginPath(); g.moveTo(-9, -9); g.lineTo(9, 11); g.stroke(); g.globalCompositeOperation = 'source-over'; g.lineWidth = 2.4; g.beginPath(); g.moveTo(-10, -11); g.lineTo(10, 11); g.stroke(); break;
      case 'plant': P('M-11 10V-2l6 3v-3l6 3v-3l6 3V-9h4v19z'); break;
      case 'refinery': P('M-11 10V-1h5v-7h3v7h3v-4h3v4h3v-9h3v20zM-6 3v3h3V3zm6 0v3h3V3z'); break;
      case 'derrick': P('M-1-11h2l7 21H5L3 4h-6l-2 6h-3zm-1 13h4l-2-6z'); break;
      case 'ship': P('M-11 4h22l-4 6H-7zm3-2V-3h5v-4h4v4h3l3 5z'); break;
      case 'anchor': P('M-1-11h2v3a3 3 0 1 1-2 0zM-1-4h2v11c4-.4 6.5-2.6 7-6l-2.5 1.2L8-2l3 4.6-2.4-1C7.8 6.3 4.2 10 0 10s-7.8-3.7-8.6-8.4L-11 2.6-8-2l2.5 4.2L-8 1c.5 3.4 3 5.6 7 6z'); break;
      case 'pearl': g.beginPath(); g.arc(0, 0, 9, 0, 7); g.fill(); g.globalAlpha *= .45; g.fillStyle = '#fff'; g.beginPath(); g.arc(-3, -3, 3.4, 0, 7); g.fill(); break;
      case 'water': P('M-11 3c2.5 0 2.5-2 5.5-2s3 2 5.5 2 3-2 5.5-2 3 2 5.5 2v3c-2.5 0-2.5-2-5.5-2s-3 2-5.5 2-3-2-5.5-2-3 2-5.5 2zm0-7c2.5 0 2.5-2 5.5-2s3 2 5.5 2 3-2 5.5-2 3 2 5.5 2v3c-2.5 0-2.5-2-5.5-2s-3 2-5.5 2-3-2-5.5-2-3 2-5.5 2z'); break;
      case 'calendar': P('M-10-7h20v17h-20zm2 6v9h16v-9zm2-10h3v5h-3zm9 0h3v5h-3z'); break;
      case 'bank': P('M0-11l11 6v2h-22v-2zm-9 9h3v9h-3zm5 0h3v9h-3zm5 0h3v9h-3zm5 0h3v9h-3zm-15 10h22v3h-22z'); break;
      default: g.beginPath(); g.arc(0, 0, 8, 0, 7); g.fill();
    }
    g.restore();
  };

  // soft dark pool behind text (drawn into the map layer, under the vignette) so labels read over bright desert
  // (the user does not want dark pools behind text: kept as a no-op so older calls do nothing)
  UI.shade = (api, x, y, rx, ry, a = 1, strength = .55) => {
    return;
    const g = api.C2.ta.g;
    g.save(); g.translate(x, y); g.scale(1, ry / rx);
    const gr = g.createRadialGradient(0, 0, 0, 0, 0, rx);
    gr.addColorStop(0, `rgba(0,12,16,${strength * a})`); gr.addColorStop(.55, `rgba(0,12,16,${strength * a * .6})`); gr.addColorStop(1, 'rgba(0,12,16,0)');
    g.fillStyle = gr; g.beginPath(); g.arc(0, 0, rx, 0, 7); g.fill(); g.restore();
  };

  // project a lon/lat and keep it if on screen (with margin)
  UI.on = (api, lon, lat, m = 60) => { const p = api.P(lon, lat); return p && p[0] > -m && p[0] < api.W + m && p[1] > -m && p[1] < api.H + m ? p : null; };

  // affine "paint on the map" transform at a lon/lat (text then follows the map's tilt and scale)
  UI.onMap = (api, g, lon, lat, kmUnit = 1) => {
    const p = api.P(lon, lat), dx = kmUnit / (111.32 * Math.cos(lat * Math.PI / 180)), dy = kmUnit / 110.57;
    const e = api.P(lon + dx, lat), n = api.P(lon, lat + dy);
    if (!p || !e || !n) return false;
    g.setTransform(e[0] - p[0], e[1] - p[1], -(n[0] - p[0]), -(n[1] - p[1]), p[0], p[1]);
    return true;
  };

  window.UI = UI;
})();
