// Shared by highlights.html and tools/track_email.js: source time of a shot (with speed ramps) and the email labels.
// speed ramps (s.rates): the rate eases linearly over `ramp` s around each knot; the source time is its integral,
// tabulated per millisecond (the same maths as tools/build_highlights_mix.py, so the voice stays on the picture)
(function () {
  const cl = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
  HL.shots.forEach(s => {
    if (!s.rates) return;
    const w = s.ramp ?? .3, n = Math.ceil(s.d * 1000) + 2, tab = new Float64Array(n);
    s.rateAt = u => { let r = s.rates[0][1]; for (let j = 1; j < s.rates.length; j++) r += (s.rates[j][1] - s.rates[j - 1][1]) * cl((u - (s.rates[j][0] - w / 2)) / w); return r; };
    for (let k = 1; k < n; k++) tab[k] = tab[k - 1] + s.rateAt((k - .5) / 1000) / 1000;
    s.tab = tab;
  });
  HL.srcOf = (s, u) => {
    if (!s.tab) return s.in + u * s.rate;
    const x = cl(u * 1000, 0, s.tab.length - 1.001), k = Math.floor(x);
    return s.in + s.tab[k] + (s.tab[k + 1] - s.tab[k]) * (x - k);
  };
  HL.rateOf = (s, u) => s.rateAt ? s.rateAt(u) : s.rate;
  HL.VFPS = 30000 / 1001;   // the rendered parts: frame k shows page time k / 29.97
  // email label grid: GX × GY cells over the label (u, v in -0.5 … 0.5), height = width × AR
  HL.GX = 8; HL.GY = 2; HL.AR = .16;
  // label opacity at a source time (window [a, b] with fades)
  HL.labelA = (lb, src) => {
    const [a, b] = lb.win || [-1e9, 1e9], [fi, fo] = lb.fade || [0, 0];
    if (src < a || src > b) return 0;
    return Math.min(fi > 0 ? cl((src - a) / fi) : 1, fo > 0 ? cl((b - src) / fo) : 1);
  };
})();
