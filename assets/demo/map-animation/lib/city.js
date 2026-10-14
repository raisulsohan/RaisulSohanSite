// Procedural (fictional) city map: jittered street grid, a diagonal avenue, a river with bridges, parks and building footprints.
// Everything is built once as Path2D in world units; a camera transform draws it each frame.
(function () {
  K.city = (seed = 1, Wd = 2600, Hd = 1700) => {
    const R = K.rng(seed);
    const xs = [], ys = [];
    for (let x = -120; x < Wd + 120; x += 72 + R() * 64) xs.push(x);
    for (let y = -120; y < Hd + 120; y += 64 + R() * 52) ys.push(y);
    const majX = new Set(xs.map((_, i) => i).filter(i => i % 4 === 2)), majY = new Set(ys.map((_, i) => i).filter(i => i % 4 === 1));
    const river = K.smoothLine([[-150, Hd * .66], [Wd * .2, Hd * .73 + R() * 40], [Wd * .42, Hd * .6], [Wd * .62, Hd * .67 + R() * 30], [Wd * .82, Hd * .55], [Wd + 150, Hd * .6]], 18);
    const RW = 74;
    const riverDist = (x, y) => { let d = 1e9; for (const p of river.pts) d = Math.min(d, Math.hypot(p[0] - x, p[1] - y)); return d; };
    const ave = [[-100, Hd * .05], [Wd + 100, Hd * .98]];            // diagonal avenue
    const aveDist = (x, y) => { const [[x1, y1], [x2, y2]] = ave, dx = x2 - x1, dy = y2 - y1; return Math.abs(dy * x - dx * y + x2 * y1 - y2 * x1) / Math.hypot(dx, dy); };

    const minor = new Path2D(), major = new Path2D(), bld = new Path2D(), parks = new Path2D(), lots = new Path2D();
    xs.forEach((x, i) => { const p = majX.has(i) ? major : minor; p.moveTo(x, -150); p.lineTo(x, Hd + 150); });
    ys.forEach((y, i) => { const p = majY.has(i) ? major : minor; p.moveTo(-150, y); p.lineTo(Wd + 150, y); });
    major.moveTo(...ave[0]); major.lineTo(...ave[1]);

    const blocks = [];
    for (let i = 0; i < xs.length - 1; i++) for (let j = 0; j < ys.length - 1; j++) {
      const x = xs[i] + 8, y = ys[j] + 8, w = xs[i + 1] - xs[i] - 16, h = ys[j + 1] - ys[j] - 16, cx = x + w / 2, cy = y + h / 2;
      if (riverDist(cx, cy) < RW / 2 + Math.max(w, h) * .55 || aveDist(cx, cy) < Math.max(w, h) * .5) continue;
      const r = R(), type = r < .07 ? 'park' : r < .12 ? 'lot' : 'bld';
      blocks.push({ x, y, w, h, type, i, j });
      if (type === 'park') { parks.rect(x, y, w, h); continue; }
      if (type === 'lot') { lots.rect(x, y, w, h); continue; }
      // split the block into building footprints along its long side
      const n = 1 + Math.floor(R() * 4), horiz = w > h;
      let a = 0;
      for (let k = 0; k < n; k++) {
        const len = (k === n - 1 ? 1 - a : (1 - a) * (.3 + R() * .5)), g = 4;
        if (horiz) bld.rect(x + a * w + g / 2, y + g / 2 + (R() < .3 ? h * .25 : 0), len * w - g, h * (R() < .3 ? .75 : 1) - g);
        else bld.rect(x + g / 2, y + a * h + g / 2, w - g, len * h - g);
        a += len;
      }
    }
    const water = new Path2D(); river.pts.forEach((p, i) => i ? water.lineTo(p[0], p[1]) : water.moveTo(p[0], p[1]));
    // bridges where major streets cross the river
    const bridges = new Path2D();
    xs.forEach((x, i) => { if (!majX.has(i)) return; const near = river.pts.reduce((b, p) => Math.abs(p[0] - x) < Math.abs(b[0] - x) ? p : b); bridges.moveTo(x, near[1] - RW * .75); bridges.lineTo(x, near[1] + RW * .75); });

    const NAMES_V = ['ELM ST', '5TH AVE', 'PARK AVE', 'MILL ST', 'OAK ST', 'GRANT AVE', 'BAY ST'];
    const NAMES_H = ['HARBOR RD', 'MAIN ST', 'KING ST', 'CANAL ST', 'NORTH RD', 'WATER ST'];
    const names = [];
    [...majX].forEach((i, k) => names.push({ v: true, x: xs[i], name: NAMES_V[k % NAMES_V.length] }));
    [...majY].forEach((j, k) => names.push({ v: false, y: ys[j], name: NAMES_H[k % NAMES_H.length] }));

    return { Wd, Hd, xs, ys, majX, majY, river, RW, minor, major, bld, parks, lots, water, bridges, blocks, names, ave };
  };

  // pal: { bg, bld, park, lot, water, waterEdge, minor, major, bridge, glow }
  K.drawCity = (g, c, pal, sc = 1) => {
    g.fillStyle = pal.bg; g.fillRect(-200, -200, c.Wd + 400, c.Hd + 400);
    if (pal.lot) { g.fillStyle = pal.lot; g.fill(c.lots); }
    g.fillStyle = pal.park; g.fill(c.parks);
    g.fillStyle = pal.bld; g.fill(c.bld);
    g.lineCap = 'butt'; g.lineJoin = 'round';
    g.strokeStyle = pal.minor; g.lineWidth = 9; g.stroke(c.minor);
    if (pal.glow) { g.strokeStyle = pal.glow; g.lineWidth = 30; g.stroke(c.major); }
    g.strokeStyle = pal.major; g.lineWidth = 17; g.stroke(c.major);
    if (pal.waterEdge) { g.strokeStyle = pal.waterEdge; g.lineWidth = c.RW + 10; g.stroke(c.water); }
    g.strokeStyle = pal.water; g.lineWidth = c.RW; g.lineCap = 'round'; g.stroke(c.water);
    g.lineCap = 'butt'; g.strokeStyle = pal.bridge || pal.major; g.lineWidth = 19; g.stroke(c.bridges);
  };
  // grid intersection → world point
  K.cityPt = (c, i, j) => [c.xs[i], c.ys[j]];
})();
