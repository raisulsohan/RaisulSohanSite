/*
 * LazyMotionToolkit interactive demo.
 *
 * The panel docked in a mock After Effects with a small comp to work on: select layers in the
 * timeline and run the real tools. The anchor pad moves the anchor and compensates the position
 * with the layer's scale and rotation; Head to Line puts an auto-orienting head on a Bézier path
 * and rides the trim; Auto Box measures the text as it types on and grows the box, caret and all;
 * Fade applies the seven easing curves; Stagger, Null + Parent, Grid Maker, Precomp, Swatch and
 * LazyPreview Render do what the panel does, in one undo step each. LazyStrike FX draws its own
 * lightning (After Effects' Advanced Lightning is not in a browser) and the preview render is
 * simulated. Nothing is written anywhere.
 *
 * Two stages, scaled to fit: a wide one (1280 x 760) and a tall one for phones and narrow
 * columns (440 x 1120).
 *
 * In the theme: a standalone page bundle (assets/lazymotion-demo.min.js), enqueued only on a
 * project whose kit names it (rs_project_demo_kit()); page-portfolio.php prints
 * <div class="rs-demo-wrap lmt-wrap"><div class="lmt rs-demo-mount" data-lazymotion-demo …>, and
 * the portfolio's pop-up player mounts the same bundle through window.LazyMotionDemo.
 * It starts when it scrolls near, and its box is sized by CSS alone, so a cached page never
 * shifts.
 */
(() => {
	"use strict";

	const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
	const NBSP = String.fromCharCode(160);
	const clamp = (x, a, b) => Math.min(b, Math.max(a, x));
	const lerp = (a, b, u) => a + (b - a) * u;
	const r2 = (x) => Math.round(x * 100) / 100;
	const easeInOut = (u) => (u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2);
	const easeOut = (u) => 1 - Math.pow(1 - u, 3);

	const COMP = { w: 1920, h: 1080, fps: 30, dur: 8, wa: [1, 6], name: "Promo_v3", file: "CityStories_Promo.aep" };

	/* The panel's own easing curves (Fade). */
	const FADE_EASES = {
		"Linear": (u) => u,
		"Ease In (Expo)": (u) => (u === 0 ? 0 : Math.pow(2, 10 * u - 10)),
		"Ease Out (Sine)": (u) => Math.sin((u * Math.PI) / 2),
		"Ease InOut (Quad)": (u) => (u < 0.5 ? 2 * u * u : 1 - Math.pow(-2 * u + 2, 2) / 2),
		"Ease InOut (Cubic)": easeInOut,
		"Bounce": (u) => {
			const n1 = 7.5625, d1 = 2.75;
			if (u < 1 / d1) return n1 * u * u;
			if (u < 2 / d1) return n1 * (u -= 1.5 / d1) * u + 0.75;
			if (u < 2.5 / d1) return n1 * (u -= 2.25 / d1) * u + 0.9375;
			return n1 * (u -= 2.625 / d1) * u + 0.984375;
		},
		"Elastic": (u) => (u === 0 ? 0 : u === 1 ? 1 : Math.pow(2, -10 * u) * Math.sin((u * 10 - 0.75) * ((2 * Math.PI) / 3)) + 1),
	};
	const HEAD_TYPES = ["Triangle", "Circle", "Star", "Rectangle", "Pentagon", "Hexagon", "Heptagon", "Octagon"];
	const REVEAL_STYLES = ["Typewriter (smooth)", "Typewriter (hard)", "Word by word", "Line by line", "Box only (I animate Reveal)", "Static box (no reveal)"];
	const SETTLES = ["Blur + rise", "Rise", "Drop", "Scale pop", "Fade only"];
	const REVEAL_EASES = ["Linear", "Ease out (types fast, settles)", "Smooth both ends"];
	const GRID_OUTPUTS = ["Shape Tiles (Fill)", "Outline Strokes", "Guide Nulls"];
	const STRIKE_STYLES = ["Direct Bolt + Flash", "Sky Flash", "Both Combined", "Audio-Driven"];

	const STR = {
		en: {
			label: "Interactive demo",
			note: "a small comp, nothing is written",
			reset: "Reset",
			undo: "Undo",
			region: "LazyMotionToolkit interactive demo",
			hints: {
				select: "Click a layer bar in the timeline to select it (several if you like), then pick a tool. Every tool works on the selection, in one undo step.",
				anchor: "Try the anchor pad: pick a corner. The anchor moves and the layer stays put, even on the rotated Badge.",
				head: "Select Arrow Path, pick a head type and press Head it!. Tick Anim and press ▶ to watch the head ride the line.",
				autobox: "Select Tagline and press Auto Box: the box measures the text as it types on. Press ▶ to watch.",
				fade: "Select a few layers and press ⚡ Apply under Fade, then ▶. Card is skipped: it has its own opacity expression.",
				stagger: "Select Title, Tagline and Badge and press Stagger: 4 frames apart, top to bottom. Keys only moves the keyframes instead.",
				strike: "Press ⚡ LazyStrike FX and generate lightning, then ▶. Audio-Driven follows the peaks of Music.wav.",
				more: "Left to try: Precomp, Null + Parent, Grid Maker, LazyPreview Render and the Swatch. Undo takes any tool back.",
				done: "That is LazyMotionToolkit. Press Reset to start over.",
			},
			need: { select: "Select a layer first." },
		},
		bn: {
			label: "ইন্টারঅ্যাক্টিভ ডেমো",
			note: "ছোট একটা কম্প, কোথাও কিছু লেখা হয় না",
			reset: "রিসেট",
			undo: "আনডু",
			region: "LazyMotionToolkit ইন্টারঅ্যাক্টিভ ডেমো",
			hints: {
				select: "টাইমলাইনে একটা লেয়ার বারে ক্লিক করে সিলেক্ট করুন (চাইলে কয়েকটা), তারপর একটা টুল বেছে নিন। প্রতিটি টুল সিলেকশনের ওপর কাজ করে, এক ধাপে আনডু হয়।",
				anchor: "অ্যাঙ্কর প্যাড দেখুন: একটা কোণা বেছে নিন। অ্যাঙ্কর সরে, লেয়ার নড়ে না, ঘোরানো Badge-এও।",
				head: "Arrow Path সিলেক্ট করে একটা মাথা বেছে Head it! চাপুন। Anim টিক দিয়ে ▶ চাপলে মাথা লাইন ধরে দৌড়ায়।",
				autobox: "Tagline সিলেক্ট করে Auto Box চাপুন: টেক্সট টাইপ হতে হতে বাক্স মেপে বড় হয়। ▶ চেপে দেখুন।",
				fade: "কয়েকটা লেয়ার সিলেক্ট করে Fade-এর ⚡ Apply চাপুন, তারপর ▶। Card বাদ পড়ে: ওর নিজের অপাসিটি এক্সপ্রেশন আছে।",
				stagger: "Title, Tagline আর Badge সিলেক্ট করে Stagger চাপুন: ওপর থেকে নিচে ৪ ফ্রেম করে ফাঁক। Keys only দিলে শুধু কিফ্রেম সরে।",
				strike: "⚡ LazyStrike FX চেপে বজ্রপাত বানান, তারপর ▶। Audio-Driven চললে Music.wav-এর পিক ধরে।",
				more: "বাকি আছে: Precomp, Null + Parent, Grid Maker, LazyPreview Render আর Swatch। Undo যেকোনো টুল ফিরিয়ে নেয়।",
				done: "এই হলো LazyMotionToolkit। আবার শুরু করতে রিসেট চাপুন।",
			},
			need: { select: "আগে একটা লেয়ার সিলেক্ট করুন।" },
		},
	};

	/* ------------------------------------------------------------------ 2D affine matrices [a b c d e f] */
	const I = [1, 0, 0, 1, 0, 0];
	const mul = (A, B) => [
		A[0] * B[0] + A[2] * B[1], A[1] * B[0] + A[3] * B[1],
		A[0] * B[2] + A[2] * B[3], A[1] * B[2] + A[3] * B[3],
		A[0] * B[4] + A[2] * B[5] + A[4], A[1] * B[4] + A[3] * B[5] + A[5],
	];
	const inv = (M) => {
		const det = M[0] * M[3] - M[1] * M[2] || 1e-9;
		return [M[3] / det, -M[1] / det, -M[2] / det, M[0] / det, (M[2] * M[5] - M[3] * M[4]) / det, (M[1] * M[4] - M[0] * M[5]) / det];
	};
	const pt = (M, x, y) => [M[0] * x + M[2] * y + M[4], M[1] * x + M[3] * y + M[5]];
	const T = (x, y) => [1, 0, 0, 1, x, y];
	const R = (deg) => { const a = (deg * Math.PI) / 180; return [Math.cos(a), Math.sin(a), -Math.sin(a), Math.cos(a), 0, 0]; };
	const Sc = (s) => [s, 0, 0, s, 0, 0];
	const mstr = (M) => `matrix(${M.map((v) => r2(v)).join(" ")})`;

	/* ------------------------------------------------------------------ the comp */
	const SWATCHES = ["#5865f2", "#ff6b6b", "#ffd166", "#06d6a0", "#ffffff"];

	function initialLayers() {
		return [
			{ id: "title", name: "Title", kind: "text", in: 0.3, out: 8, x: 960, y: 400, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100,
				text: { str: "LAUNCH DAY", size: 120, weight: 800, fill: "#ffffff", stroke: null, strokeW: 0, letter: 6 },
				anim: { pos: { t0: 0.3, t1: 1.0, from: [960, 470], to: [960, 400] } } },
			{ id: "tagline", name: "Tagline", kind: "text", in: 0.8, out: 8, x: 0, y: 130, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, parent: "card",
				text: { str: "Coffee took over the world, one cup at a time.", size: 44, weight: 500, fill: "#cfd6ff", stroke: null, strokeW: 0, letter: 0 } },
			{ id: "arrow", name: "Arrow Path", kind: "shape", in: 1.0, out: 8, x: 960, y: 800, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100,
				shape: { type: "path", pts: [[-460, 80], [-220, -170], [180, 190], [440, -60]], stroke: "#ffd166", strokeW: 12, fill: null } },
			{ id: "badge", name: "Badge", kind: "shape", in: 0, out: 8, x: 1560, y: 300, ax: 0, ay: 0, scale: 100, rot: 18, opacity: 100,
				shape: { type: "rect", w: 260, h: 150, rx: 22, fill: "#ff6b6b", stroke: "#ffffff", strokeW: 0, label: "NEW" } },
			{ id: "card", name: "Card", kind: "shape", in: 0, out: 8, x: 960, y: 440, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, ownOpacityExpr: true,
				shape: { type: "rect", w: 1000, h: 330, rx: 28, fill: "#171a2e", stroke: "#5865f2", strokeW: 3 } },
			{ id: "logo", name: "Logo", kind: "shape", in: 0, out: 8, x: 300, y: 300, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100,
				shape: { type: "diamond", s: 80, fill: "#5865f2", stroke: null, strokeW: 0 }, anim: { rot: { t0: 0, t1: 8, from: 0, to: 360 } } },
			{ id: "music", name: "Music.wav", kind: "audio", in: 0, out: 8 },
			{ id: "bg", name: "BG", kind: "solid", in: 0, out: 8, x: 960, y: 540, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, locked: true,
				solid: { w: 1920, h: 1080, fill: "#0e1022" } },
		];
	}

	const keysOf = (l) => {
		const k = [];
		if (l.anim && l.anim.pos) k.push(l.anim.pos.t0, l.anim.pos.t1);
		if (l.anim && l.anim.rot) k.push(l.anim.rot.t0, l.anim.rot.t1);
		return k;
	};

	/* Music.wav: a beat every half second with a swell, as a loudness envelope every 10 ms. */
	const MUSIC = (() => {
		const step = 0.01, values = [];
		const pattern = [1, 0.45, 0.8, 0.55, 0.95, 0.4, 0.7, 0.6];
		for (let i = 0; i < COMP.dur / step; i++) {
			const t = i * step;
			const beat = Math.floor(t / 0.5);
			const phase = (t % 0.5) / 0.5;
			const amp = pattern[beat % pattern.length] * Math.exp(-phase * 5) + 0.12 + 0.08 * Math.sin(t * 1.7);
			values.push(amp * 0.7);
		}
		return { step, values };
	})();

	const musicAt = (t) => MUSIC.values[clamp(Math.floor(t / MUSIC.step), 0, MUSIC.values.length - 1)] || 0;

	function wavePath(bars, h) {
		const per = Math.max(1, Math.floor(MUSIC.values.length / bars));
		let d = "";
		for (let b = 0; b < bars; b++) {
			let peak = 0;
			for (let i = b * per; i < (b + 1) * per && i < MUSIC.values.length; i++) peak = Math.max(peak, MUSIC.values[i]);
			const y = Math.max(1, Math.min(1, peak / 0.8) * h);
			d += `M${b} ${(h - y) / 2}v${y}`;
		}
		return d;
	}

	/* ------------------------------------------------------------------ Bézier helpers (Head to Line) */
	const bez = (p, u) => {
		const m = 1 - u;
		return [
			m * m * m * p[0][0] + 3 * m * m * u * p[1][0] + 3 * m * u * u * p[2][0] + u * u * u * p[3][0],
			m * m * m * p[0][1] + 3 * m * m * u * p[1][1] + 3 * m * u * u * p[2][1] + u * u * u * p[3][1],
		];
	};
	const bezTan = (p, u) => {
		const m = 1 - u;
		return [
			3 * m * m * (p[1][0] - p[0][0]) + 6 * m * u * (p[2][0] - p[1][0]) + 3 * u * u * (p[3][0] - p[2][0]),
			3 * m * m * (p[1][1] - p[0][1]) + 6 * m * u * (p[2][1] - p[1][1]) + 3 * u * u * (p[3][1] - p[2][1]),
		];
	};
	/* The first part of the curve up to u (de Casteljau). */
	const bezHead = (p, u) => {
		const L = (a, b) => [lerp(a[0], b[0], u), lerp(a[1], b[1], u)];
		const q0 = L(p[0], p[1]), q1 = L(p[1], p[2]), q2 = L(p[2], p[3]);
		const r0 = L(q0, q1), r1 = L(q1, q2);
		return [p[0], q0, r0, L(r0, r1)];
	};
	const pathD = (p) => `M${r2(p[0][0])} ${r2(p[0][1])} C ${r2(p[1][0])} ${r2(p[1][1])}, ${r2(p[2][0])} ${r2(p[2][1])}, ${r2(p[3][0])} ${r2(p[3][1])}`;

	function headPoints(type, s) {
		const ngon = (n) => Array.from({ length: n }, (_, i) => {
			const a = (i / n) * Math.PI * 2;
			return [r2(Math.cos(a) * s * 0.5), r2(Math.sin(a) * s * 0.5)];
		});
		if (type === "Triangle") return [[-s * 0.45, -s * 0.5], [s * 0.6, 0], [-s * 0.45, s * 0.5]];
		if (type === "Rectangle") return [[-s * 0.45, -s * 0.3], [s * 0.45, -s * 0.3], [s * 0.45, s * 0.3], [-s * 0.45, s * 0.3]];
		if (type === "Star") return Array.from({ length: 10 }, (_, i) => {
			const a = (i / 10) * Math.PI * 2;
			const r = i % 2 ? s * 0.24 : s * 0.58;
			return [r2(Math.cos(a) * r), r2(Math.sin(a) * r)];
		});
		return ngon({ Pentagon: 5, Hexagon: 6, Heptagon: 7, Octagon: 8 }[type] || 6);
	}

	/* ------------------------------------------------------------------ lightning (LazyStrike FX) */
	function seeded(seed) {
		let s = seed >>> 0;
		return () => {
			s = (s * 1664525 + 1013904223) >>> 0;
			return s / 4294967296;
		};
	}

	function boltPath(seed, from, to, random) {
		const rnd = seeded(seed);
		const jitter = 40 + random * 1.6;
		const n = 16;
		const pts = [from];
		for (let i = 1; i < n; i++) {
			const u = i / n;
			const x = lerp(from[0], to[0], u) + (rnd() - 0.5) * jitter * 2 * Math.sin(u * Math.PI);
			const y = lerp(from[1], to[1], u) + (rnd() - 0.5) * jitter * 0.6;
			pts.push([x, y]);
		}
		pts.push(to);
		let d = `M${r2(pts[0][0])} ${r2(pts[0][1])}` + pts.slice(1).map((p) => `L${r2(p[0])} ${r2(p[1])}`).join("");
		const branches = [];
		for (let b = 0; b < 2 + Math.floor(random / 40); b++) {
			const at = 3 + Math.floor(rnd() * (n - 6));
			const p = pts[at];
			const len = 120 + rnd() * 220;
			const dir = rnd() < 0.5 ? -1 : 1;
			let bx = p[0], by = p[1];
			let bd = `M${r2(bx)} ${r2(by)}`;
			for (let k = 0; k < 6; k++) {
				bx += dir * (len / 6) * (0.6 + rnd() * 0.8);
				by += (len / 6) * (0.5 + rnd());
				bd += `L${r2(bx)} ${r2(by)}`;
			}
			branches.push(bd);
		}
		return { d, branches };
	}

	/* ------------------------------------------------------------------ state */
	const start = () => ({
		layers: initialLayers(),
		sel: [],
		t: 1.5,
		playing: false,
		swatches: SWATCHES.slice(),
		swTot: 5,
		swCol: 5,
		head: { type: "Triangle", round: false, double: false, rev: false, anim: true, frames: 30 },
		fade: { dur: 20, spd: 1, ease: "Linear", in: true, out: true, markers: true },
		stagger: { frames: 4, rev: false, keys: false },
		anchorPos: 4,
		preview: null,
		autoBoxLast: null,
		gridLast: null,
		strikeLast: null,
		history: [],
		msg: ["Ready", "quiet"],
		did: { select: false, anchor: false, head: false, autobox: false, fade: false, play: false, stagger: false, strike: false, other: 0 },
	});

	const badge = (kind, s) => `<svg viewBox="0 0 ${s} ${s}" aria-hidden="true"><rect width="${s}" height="${s}" rx="${s * 0.22}" fill="#00005b"/>` +
		`<rect x="${s * 0.08}" y="${s * 0.08}" width="${s * 0.84}" height="${s * 0.84}" rx="${s * 0.15}" fill="none" stroke="#9999ff" stroke-width="${s * 0.05}"/>` +
		`<text x="${s / 2}" y="${s * 0.66}" text-anchor="middle" fill="#9999ff" font-size="${s * 0.44}" font-weight="700">${kind}</text></svg>`;

	const ICONS = {
		spark: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.2 6.3L20.5 10l-6.3 2.2L12 18.5 9.8 12.2 3.5 10l6.3-1.7z"/></svg>`,
		reset: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>`,
		undo: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 0 12h-3"/></svg>`,
		play: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4.5v15l12-7.5z"/></svg>`,
		pause: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>`,
		cat: `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 22 12 12 22 2 12z" fill="#5865f2"/><path d="M8.5 13.2c.4-1.6 1.6-2.6 3.5-2.6s3.1 1 3.5 2.6c.2.9-.4 1.6-1.3 1.6h-4.4c-.9 0-1.5-.7-1.3-1.6z" fill="#fff"/><path d="M9.6 10.9 9 9.4l1.6.7M14.4 10.9l.6-1.5-1.6.7" stroke="#fff" stroke-width=".9" fill="none" stroke-linecap="round"/></svg>`,
	};

	/* ------------------------------------------------------------------ markup */
	function markup(L, tall) {
		const sel = (opts, cur) => opts.map((o) => `<option${o === cur ? " selected" : ""}>${o}</option>`).join("");
		return `<div class="lmt-stage ${tall ? "lmt-tall" : "lmt-wide"}">
<div class="lmt-head">
	<span class="lmt-label">${ICONS.spark}${L.label}<small>· ${L.note}</small></span>
	<p class="lmt-hint"></p>
	<button type="button" class="lmt-headbtn" data-act="undo" title="Ctrl+Z">${ICONS.undo}${L.undo}</button>
	<button type="button" class="lmt-headbtn" data-act="reset">${ICONS.reset}${L.reset}</button>
</div>

<section class="lmt-app" aria-label="After Effects">
	<div class="lmt-titlebar">
		<span class="lmt-apptab">${badge("Ae", 20)}After Effects</span>
		<span class="lmt-file">${COMP.file} — Adobe After Effects</span>
		<span class="lmt-dots" aria-hidden="true"><i style="background:#f5a623"></i><i style="background:#27c93f"></i><i style="background:#ff5f56"></i></span>
	</div>

	<div class="lmt-body">
		<div class="lmt-viewer">
			<div class="lmt-viewer__bar"><b>${COMP.name}</b><span>1920 × 1080 · ${COMP.fps} fps</span><span class="lmt-selinfo"></span><span>Fit</span></div>
			<div class="lmt-canvas"><div class="lmt-frame"><svg viewBox="0 0 ${COMP.w} ${COMP.h}" aria-hidden="true"></svg><svg class="lmt-measure" aria-hidden="true"><text></text></svg><div class="lmt-previewbadge" hidden>PREVIEW · solo</div></div></div>
		</div>

		<div class="lmt-timeline">
			<div class="lmt-tlbar">
				<button type="button" class="lmt-playbtn" data-act="play" aria-label="Play">${ICONS.play}</button>
				<b class="lmt-tc">0:00:00:00</b>
				<span class="lmt-tlname">${COMP.name}</span>
				<span class="lmt-tlhint">click a bar to select · the ruler to jump</span>
			</div>
			<div class="lmt-tlbody"><div class="lmt-tlgrid" data-seek></div></div>
		</div>

		<div class="lmt-panel" aria-label="LazyMotionToolkit">
			<div class="lmt-ph"><span class="lmt-brand">${ICONS.cat}LazyMotionToolkit</span><span class="lmt-made">Made by <b>Raisul Sohan</b></span><span class="lmt-ver">v1.12</span><span class="lmt-menu">⋯</span></div>
			<div class="lmt-scroll">
				<h4 class="lmt-sec"><span>Motion Tools</span></h4>
				<div class="lmt-grid2">
					<button type="button" class="lmt-btn" data-act="precomp1">⊞ Precomp (1:1)</button>
					<button type="button" class="lmt-btn" data-act="precompg">▣ Precomp (Group)</button>
					<button type="button" class="lmt-btn" data-act="autobox">⊡ Auto Box</button>
					<button type="button" class="lmt-btn" data-act="grid">⊞ Grid Maker</button>
					<button type="button" class="lmt-btn" data-act="stagger">⇥ Stagger</button>
					<button type="button" class="lmt-btn" data-act="nullparent">◎ Null + Parent</button>
				</div>
				<div class="lmt-row lmt-row--stagger">
					<span>Stagger by</span><input type="number" class="lmt-num" data-field="staggerFrames" value="4" step="1"><span>frames</span>
					<label class="lmt-radio"><input type="checkbox" data-field="staggerRev"><i></i>Rev</label>
					<label class="lmt-radio"><input type="checkbox" data-field="staggerKeys"><i></i>Keys only</label>
				</div>
				<button type="button" class="lmt-btn lmt-btn--accent lmt-btn--wide" data-act="strike">⚡ LazyStrike FX</button>

				<h4 class="lmt-sec"><span>🎬 LazyPreview Render</span></h4>
				<div class="lmt-grid3">
					<button type="button" class="lmt-btn" data-act="render">▶ Render In→Out</button>
					<button type="button" class="lmt-btn" data-act="ptoggle">Toggle</button>
					<button type="button" class="lmt-btn lmt-btn--danger" data-act="premove">Remove</button>
				</div>
				<p class="lmt-status lmt-pstatus">Set the work area (B / N), then render.</p>

				<div class="lmt-cols">
					<div>
						<h4 class="lmt-sec"><span>Head to Line</span></h4>
						<div class="lmt-row"><span>Type:</span><select class="lmt-select" data-field="headType">${sel(HEAD_TYPES, "Triangle")}</select></div>
						<div class="lmt-row lmt-row--wrap">
							<label class="lmt-radio"><input type="checkbox" data-field="headRound"><i></i>Round</label>
							<label class="lmt-radio"><input type="checkbox" data-field="headDouble"><i></i>Double</label>
							<label class="lmt-radio"><input type="checkbox" data-field="headRev"><i></i>Rev</label>
							<label class="lmt-check"><input type="checkbox" data-field="headAnim" checked><i></i>Anim:</label><input type="number" class="lmt-num" data-field="headFrames" value="30" min="1">
						</div>
						<button type="button" class="lmt-btn lmt-btn--wide" data-act="head">⚙ Head it!</button>
					</div>
					<div>
						<h4 class="lmt-sec"><span>Anchor</span></h4>
						<div class="lmt-pad" role="group" aria-label="Anchor point">${["↖", "↑", "↗", "←", "●", "→", "↙", "↓", "↘"].map((g, i) => `<button type="button" class="lmt-padbtn" data-anchor="${i}" aria-pressed="${i === 4}">${g}</button>`).join("")}</div>
						<button type="button" class="lmt-btn lmt-btn--wide" data-act="center">Center Comp</button>
					</div>
				</div>

				<div class="lmt-cols">
					<div>
						<h4 class="lmt-sec"><span>Fade</span></h4>
						<div class="lmt-row"><span>Dur:</span><input type="number" class="lmt-num" data-field="fadeDur" value="20" min="1"><span>Spd:</span><input type="number" class="lmt-num" data-field="fadeSpd" value="1" min="0.1" step="0.5"></div>
						<div class="lmt-row"><span>Ease:</span><select class="lmt-select" data-field="fadeEase">${sel(Object.keys(FADE_EASES), "Linear")}</select></div>
						<div class="lmt-row lmt-row--wrap">
							<label class="lmt-check"><input type="checkbox" data-field="fadeIn" checked><i></i>In</label>
							<label class="lmt-check"><input type="checkbox" data-field="fadeOut" checked><i></i>Out</label>
							<label class="lmt-check"><input type="checkbox" data-field="fadeMarkers" checked><i></i>Markers</label>
						</div>
						<div class="lmt-grid2"><button type="button" class="lmt-btn lmt-btn--accent" data-act="fade">⚡ Apply</button><button type="button" class="lmt-btn" data-act="fadeclear">✕ Clear</button></div>
					</div>
					<div>
						<h4 class="lmt-sec"><span>Swatch</span></h4>
						<div class="lmt-row"><span>Tot:</span><select class="lmt-select lmt-select--s" data-field="swTot">${sel(["1", "2", "3", "4", "5", "6", "7", "8", "9", "10"], "5")}</select><span>Col:</span><select class="lmt-select lmt-select--s" data-field="swCol">${sel(["1", "2", "3", "4", "5", "6"], "5")}</select></div>
						<div class="lmt-swatches"></div>
					</div>
				</div>

				<p class="lmt-status lmt-log" title="">Ready</p>
			</div>
		</div>
	</div>

	<div class="lmt-dialog" hidden></div>
	<div class="lmt-toast" aria-hidden="true"></div>
</section>
</div>`;
	}

	/* ------------------------------------------------------------------ one demo */
	let seq = 0;
	const reduceMQ = window.matchMedia ? window.matchMedia("(prefers-reduced-motion: reduce)") : null;
	const reduced = () => Boolean(reduceMQ && reduceMQ.matches);

	function mount(root) {
		if (root.lmtMounted) return;
		root.lmtMounted = true;

		const u = `lmt${++seq}`;
		const lang = (root.getAttribute("data-lang") || document.documentElement.lang || "en").toLowerCase();
		const L = STR[lang.indexOf("bn") === 0 ? "bn" : "en"];

		let S = start();
		let tall = null, stage = null, W = 0, k = 1, playRaf = 0, toastTimer = 0, msgTimer = 0, renderTimer = 0;
		let idSeq = 0;
		const measured = new Map();

		const q = (sel) => stage.querySelector(sel);
		const qa = (sel) => Array.from(stage.querySelectorAll(sel));
		const byId = (id) => S.layers.find((l) => l.id === id) || null;
		const selected = () => S.layers.filter((l) => S.sel.indexOf(l.id) !== -1);
		const newId = (p) => `${p}${++idSeq}`;
		const fr = (n) => n / COMP.fps;

		root.setAttribute("role", "region");
		root.setAttribute("aria-label", L.region);

		function build() {
			const nextTall = (root.parentElement || root).clientWidth < 980;
			if (stage && nextTall === tall) {
				fit();
				return;
			}
			tall = nextTall;
			W = tall ? 440 : 1280;
			root.innerHTML = markup(L, tall);
			stage = root.firstElementChild;
			root.classList.add("lmt-ready");
			measured.clear();
			fit();
			paintAll();
		}

		function fit() {
			k = root.clientWidth / W || 1;
			stage.style.transform = `scale(${k})`;
		}

		/* ---- measuring text with the canvas's own font ---- */
		function measure(str, size, weight, letter) {
			const key = `${size}|${weight}|${letter}|${str}`;
			if (measured.has(key)) return measured.get(key);
			const el = q(".lmt-measure text");
			el.setAttribute("font-size", size);
			el.setAttribute("font-weight", weight);
			el.setAttribute("letter-spacing", letter || 0);
			/* SVG drops a trailing space, so every space is measured as a no-break one. */
			el.textContent = str.split(" ").join(NBSP);
			let w = 0;
			try { w = el.getComputedTextLength(); } catch (e) { w = str.length * size * 0.55; }
			if (!w && str) w = str.length * size * 0.55;
			measured.set(key, w);
			return w;
		}

		/* ---- animated properties ---- */
		const posAt = (l, t) => {
			if (l.anim && l.anim.pos) {
				const a = l.anim.pos;
				const p = easeOut(clamp((t - a.t0) / Math.max(0.001, a.t1 - a.t0), 0, 1));
				return [lerp(a.from[0], a.to[0], p), lerp(a.from[1], a.to[1], p)];
			}
			return [l.x, l.y];
		};
		const rotAt = (l, t) => (l.anim && l.anim.rot ? lerp(l.anim.rot.from, l.anim.rot.to, clamp((t - l.anim.rot.t0) / Math.max(0.001, l.anim.rot.t1 - l.anim.rot.t0), 0, 1)) : l.rot || 0);

		function contentBox(l) {
			if (l.kind === "text") {
				const w = measure(l.text.str, l.text.size, l.text.weight, l.text.letter);
				const h = l.text.size * 1.16;
				return { x0: -w / 2, y0: -h / 2, w, h };
			}
			if (l.kind === "shape") {
				const s = l.shape;
				if (s.type === "rect") return { x0: -s.w / 2, y0: -s.h / 2, w: s.w, h: s.h };
				if (s.type === "circle") return { x0: -s.r, y0: -s.r, w: s.r * 2, h: s.r * 2 };
				if (s.type === "diamond") return { x0: -s.s, y0: -s.s, w: s.s * 2, h: s.s * 2 };
				if (s.type === "path") {
					let x0 = Infinity, y0 = Infinity, x1 = -Infinity, y1 = -Infinity;
					for (let i = 0; i <= 40; i++) {
						const p = bez(s.pts, i / 40);
						x0 = Math.min(x0, p[0]); y0 = Math.min(y0, p[1]); x1 = Math.max(x1, p[0]); y1 = Math.max(y1, p[1]);
					}
					const pad = s.strokeW / 2;
					return { x0: x0 - pad, y0: y0 - pad, w: x1 - x0 + 2 * pad, h: y1 - y0 + 2 * pad };
				}
			}
			if (l.kind === "solid") return { x0: -l.solid.w / 2, y0: -l.solid.h / 2, w: l.solid.w, h: l.solid.h };
			if (l.kind === "null") return { x0: -50, y0: -50, w: 100, h: 100 };
			if (l.kind === "grid") return { x0: -l.cell.w / 2, y0: -l.cell.h / 2, w: l.cell.w, h: l.cell.h };
			if (l.kind === "head") return { x0: -l.size / 2, y0: -l.size / 2, w: l.size, h: l.size };
			if (l.kind === "precomp") {
				if (l.attrsOutside) return contentBox(l.inner[0]);
				return { x0: 0, y0: 0, w: COMP.w, h: COMP.h };
			}
			return { x0: -COMP.w / 2, y0: -COMP.h / 2, w: COMP.w, h: COMP.h };
		}

		const ownMatrix = (l, t) => {
			const p = posAt(l, t);
			return mul(mul(mul(T(p[0], p[1]), R(rotAt(l, t))), Sc((l.scale === undefined ? 100 : l.scale) / 100)), T(-(l.ax || 0), -(l.ay || 0)));
		};
		const findIn = (pool, id) => (pool || S.layers).find((l) => l.id === id) || null;
		const worldMatrix = (l, t, pool, depth = 0) => {
			const own = ownMatrix(l, t);
			const parent = l.parent ? findIn(pool, l.parent) : null;
			return parent && depth < 8 ? mul(worldMatrix(parent, t, pool, depth + 1), own) : own;
		};

		/* Hang a layer off another (or off nothing) and keep it exactly where it is on screen. */
		function reparent(l, newParent) {
			const t = S.t;
			const Wm = worldMatrix(l, t);
			const Pm = newParent ? worldMatrix(newParent, t) : I;
			const RS = mul(R(rotAt(l, t)), Sc((l.scale === undefined ? 100 : l.scale) / 100));
			const M = mul(mul(mul(inv(Pm), Wm), T(l.ax || 0, l.ay || 0)), inv(RS));
			const p = posAt(l, t);
			const dx = M[4] - p[0], dy = M[5] - p[1];
			if (l.anim && l.anim.pos) {
				l.anim.pos.from = [l.anim.pos.from[0] + dx, l.anim.pos.from[1] + dy];
				l.anim.pos.to = [l.anim.pos.to[0] + dx, l.anim.pos.to[1] + dy];
			}
			l.x += dx;
			l.y += dy;
			l.parent = newParent ? newParent.id : null;
		}

		function fadeFactor(l, t) {
			if (!l.fade) return 1;
			const f = l.fade;
			const d = Math.max(1, f.dur) / Math.max(0.1, f.spd) / COMP.fps;
			const e = FADE_EASES[f.ease] || FADE_EASES.Linear;
			let v = 1;
			if (f.in) v *= e(clamp((t - l.in) / d, 0, 1));
			if (f.out) v *= e(clamp((l.out - t) / d, 0, 1));
			return v;
		}
		const opacityAt = (l, t) => {
			/* A head follows its line, as its expression does. */
			if (l.kind === "head" && byId(l.ref)) return opacityAt(byId(l.ref), t);
			return ((l.opacity === undefined ? 100 : l.opacity) / 100) * fadeFactor(l, t) * (l.ownOpacityExpr ? 0.96 + 0.04 * Math.sin(t * 9) : 1);
		};

		/* ---- Auto Box: what is revealed at time t ---- */
		function reveal(l, t) {
			const ab = l.autoBox;
			const str = l.text.str;
			const size = l.text.size;
			const chars = Array.from(str);
			const words = str.split(" ");
			const units = ab.style === "Word by word" ? words.length : ab.style === "Line by line" ? 1 : chars.length;
			const D = ab.timing === "unit" ? Math.min(120, units * ab.fpu) : ab.total;
			const p = ab.style.indexOf("Static") === 0 || ab.style.indexOf("Box only") === 0 ? 1 : clamp(((t - ab.t0) * COMP.fps) / Math.max(1, D), 0, 1);
			const ease = ab.ease === "Linear" ? p : ab.ease.indexOf("Ease out") === 0 ? easeOut(p) : easeInOut(p);
			const u = ease * units;
			const hard = ab.style === "Typewriter (hard)";
			const arrived = (i) => {
				if (ab.style === "Word by word") {
					let w = 0, count = 0;
					for (let c = 0; c <= i; c++) if (chars[c] === " ") w++;
					count = w;
					return clamp(u - count, 0, ab.band) / ab.band;
				}
				if (ab.style === "Line by line") return clamp(u * 3, 0, 1);
				if (ab.style.indexOf("Static") === 0 || ab.style.indexOf("Box only") === 0) return 1;
				const v = hard ? Math.floor(u) - i : u - i;
				return clamp(v, 0, ab.band) / ab.band;
			};
			const full = measure(str, size, l.text.weight, l.text.letter);
			const x0 = -full / 2;
			let visibleTo = 0;
			const out = chars.map((ch, i) => {
				const s = arrived(i);
				const prefix = measure(str.slice(0, i), size, l.text.weight, l.text.letter);
				if (s > 0) visibleTo = measure(str.slice(0, i + 1), size, l.text.weight, l.text.letter);
				const settle = ab.settle;
				return {
					ch, x: x0 + prefix, s,
					opacity: s,
					dy: settle === "Blur + rise" || settle === "Rise" ? (1 - s) * size * 0.3 : settle === "Drop" ? -(1 - s) * size * 0.35 : 0,
					sc: settle === "Scale pop" ? 1 + (1 - s) * 0.4 : 1,
					blur: settle === "Blur + rise" ? (1 - s) * 6 : 0,
				};
			});
			return { chars: out, x0, w: visibleTo, full, done: p >= 1, p };
		}

		function boxWidthAt(l, t) {
			const ab = l.autoBox;
			let sum = 0;
			for (let i = 0; i < ab.smooth; i++) sum += reveal(l, t + fr(ab.lead - (ab.smooth - 1) / 2 + i)).w;
			return sum / ab.smooth;
		}

		/* ---- drawing one layer at time t (the comp's own coordinates) ---- */
		function drawContent(l, t, M) {
			const o = opacityAt(l, t);
			const box = contentBox(l);
			let inner = "";

			if (l.kind === "text" && l.autoBox) {
				const rv = reveal(l, t);
				const ab = l.autoBox;
				const bw = boxWidthAt(l, t);
				const bh = box.h + 2 * ab.padY;
				const bx = rv.x0 - ab.padX;
				const boxOp = ab.style.indexOf("Static") === 0 ? 1 : clamp(((t - ab.t0) * COMP.fps) / Math.max(1, ab.fade), 0, 1);
				const rx = Math.min(ab.round, bh / 2);
				inner += `<rect x="${r2(bx)}" y="${r2(-bh / 2)}" width="${r2(bw + 2 * ab.padX)}" height="${r2(bh)}" rx="${rx}" fill="${ab.boxColor}" opacity="${r2(boxOp)}"${ab.stroke ? ` stroke="${ab.caretColor}" stroke-width="${ab.strokeW}"` : ""}/>`;
				const base = l.text.size * 0.36;
				const filt = `${u}-blur`;
				inner += rv.chars.map((c) => c.s <= 0 ? "" :
					`<text x="${r2(c.x)}" y="${r2(base + c.dy)}" fill="${l.text.fill}" font-size="${l.text.size}" font-weight="${l.text.weight}" letter-spacing="${l.text.letter || 0}" opacity="${r2(c.opacity)}"${c.sc !== 1 ? ` transform="translate(${r2(c.x)} ${r2(base)}) scale(${r2(c.sc)}) translate(${r2(-c.x)} ${r2(-base)})"` : ""}${c.blur > 0.3 ? ` filter="url(#${filt})"` : ""}${l.text.stroke ? ` stroke="${l.text.stroke}" stroke-width="${l.text.strokeW}" paint-order="stroke"` : ""}>${esc(c.ch === " " ? NBSP : c.ch)}</text>`).join("");
				if (ab.caret && ab.style.indexOf("Static") !== 0 && ab.style.indexOf("Box only") !== 0) {
					const on = ab.blink > 0 ? Math.floor(t * ab.blink * 2) % 2 === 0 : true;
					if (on) inner += `<rect x="${r2(rv.x0 + rv.w + 6)}" y="${r2(-box.h / 2 + 4)}" width="${ab.caretW}" height="${r2(box.h - 8)}" rx="2" fill="${ab.caretColor}"/>`;
				}
			} else if (l.kind === "text") {
				inner += `<text x="0" y="${r2(l.text.size * 0.36)}" text-anchor="middle" fill="${l.text.fill}" font-size="${l.text.size}" font-weight="${l.text.weight}" letter-spacing="${l.text.letter || 0}"${l.text.stroke ? ` stroke="${l.text.stroke}" stroke-width="${l.text.strokeW}" paint-order="stroke"` : ""}>${esc(l.text.str)}</text>`;
			} else if (l.kind === "shape") {
				const s = l.shape;
				const st = s.stroke && s.strokeW ? ` stroke="${s.stroke}" stroke-width="${s.strokeW}"` : "";
				if (s.type === "rect") {
					inner += `<rect x="${-s.w / 2}" y="${-s.h / 2}" width="${s.w}" height="${s.h}" rx="${s.rx}" fill="${s.fill || "none"}"${st}/>`;
					if (s.label) inner += `<text x="0" y="16" text-anchor="middle" fill="#ffffff" font-size="54" font-weight="800" letter-spacing="4">${esc(s.label)}</text>`;
				} else if (s.type === "circle") {
					inner += `<circle r="${s.r}" fill="${s.fill || "none"}"${st}/>`;
				} else if (s.type === "diamond") {
					inner += `<path d="M0 ${-s.s} L${s.s} 0 L0 ${s.s} L${-s.s} 0 Z" fill="${s.fill || "none"}"${st}/>`;
				} else if (s.type === "path") {
					let pts = s.pts;
					if (l.head && l.head.anim) {
						const p = easeInOut(clamp(((t - l.head.t0) * COMP.fps) / Math.max(1, l.head.frames), 0, 1));
						pts = bezHead(s.pts, Math.max(0.001, p));
					}
					inner += `<path d="${pathD(pts)}" fill="none" stroke="${s.stroke}" stroke-width="${s.strokeW}" stroke-linecap="round"/>`;
				}
			} else if (l.kind === "solid") {
				inner += `<rect x="${-l.solid.w / 2}" y="${-l.solid.h / 2}" width="${l.solid.w}" height="${l.solid.h}" fill="${l.solid.fill}"/>` +
					(l.id === "bg" ? `<rect x="${-l.solid.w / 2}" y="${-l.solid.h / 2}" width="${l.solid.w}" height="${l.solid.h}" fill="url(#${u}-bg)"/>` : "");
			} else if (l.kind === "null") {
				inner += `<rect x="-50" y="-50" width="100" height="100" fill="none" stroke="#ff2e74" stroke-width="3" stroke-dasharray="10 8"/><path d="M-70 0H70M0 -70V70" stroke="#ff2e74" stroke-width="3"/>`;
			} else if (l.kind === "grid") {
				const c = l.cell;
				if (c.type === "Shape Tiles (Fill)") inner += `<rect x="${-c.w / 2}" y="${-c.h / 2}" width="${c.w}" height="${c.h}" rx="6" fill="#5865f2" fill-opacity="0.28" stroke="#8b95f8" stroke-width="2"/>`;
				else if (c.type === "Outline Strokes") inner += `<rect x="${-c.w / 2}" y="${-c.h / 2}" width="${c.w}" height="${c.h}" fill="none" stroke="#00e5ff" stroke-width="3"/>`;
				else inner += `<rect x="-40" y="-40" width="80" height="80" fill="none" stroke="#ff2e74" stroke-width="2" stroke-dasharray="6 5"/><path d="M-52 0H52M0 -52V52" stroke="#ff2e74" stroke-width="2"/>`;
			} else if (l.kind === "head") {
				const line = byId(l.ref);
				if (line && line.shape) {
					const h = line.head;
					let uu = l.atStart ? 0 : 1;
					if (h && h.anim && !l.atStart) uu = Math.max(0.001, easeInOut(clamp(((t - h.t0) * COMP.fps) / Math.max(1, h.frames), 0, 1)));
					const p = bez(line.shape.pts, uu);
					const tg = bezTan(line.shape.pts, uu);
					let ang = (Math.atan2(tg[1], tg[0]) * 180) / Math.PI + (l.atStart ? 180 : 0) + (h && h.rev ? 180 : 0);
					const pts = headPoints(l.headType, l.size);
					const shape = l.headType === "Circle" ? `<circle r="${l.size * 0.5}" fill="${line.shape.stroke}"/>` :
						`<polygon points="${pts.map((pp) => pp.join(",")).join(" ")}" fill="${line.shape.stroke}"${l.round ? ` stroke="${line.shape.stroke}" stroke-width="${r2(l.size * 0.22)}" stroke-linejoin="round"` : ""}/>`;
					inner += `<g transform="translate(${r2(p[0])} ${r2(p[1])}) rotate(${r2(ang)})">${shape}</g>`;
				}
			} else if (l.kind === "strike") {
				inner += drawStrike(l, t);
			} else if (l.kind === "precomp") {
				for (let i = l.inner.length - 1; i >= 0; i--) inner += drawLayer(l.inner[i], t, l.inner);
			}

			const bo = l.kind === "strike" ? 1 : o;
			return `<g transform="${mstr(M)}" opacity="${r2(bo)}"${l.kind === "strike" ? ` style="mix-blend-mode:screen"` : ""}>${inner}</g>`;
		}

		function drawLayer(l, t, pool) {
			if (t < l.in - 0.0001 || t >= l.out) return "";
			if (l.kind === "audio" || l.guide || l.kind === "preview" || l.kind === "box") return "";
			return drawContent(l, t, worldMatrix(l, t, pool));
		}

		function drawStrike(l, t) {
			const st = l.strike;
			let out = "";
			st.strikes.forEach((s, i) => {
				const q = (t - s.at) / fr(st.dur);
				if (q < 0 || q > 1.15) return;
				const rnd = seeded(s.seed + Math.floor(t * COMP.fps) * 7);
				if (st.bolt) {
					const flick = 0.55 + 0.45 * rnd();
					const bp = boltPath(s.seed, [s.x0, -20], [s.x1, s.y1], st.random);
					const w = 3 + (st.boltInt / 100) * 7;
					const op = clamp(1 - q, 0, 1) * flick;
					out += `<g opacity="${r2(op)}"><path d="${bp.d}" fill="none" stroke="${st.boltColor}" stroke-width="${r2(w * 3)}" stroke-linejoin="round" opacity="0.35" filter="url(#${u}-glow)"/>` +
						`<path d="${bp.d}" fill="none" stroke="#ffffff" stroke-width="${r2(w)}" stroke-linejoin="round"/>` +
						bp.branches.map((b) => `<path d="${b}" fill="none" stroke="${st.boltColor}" stroke-width="${r2(w * 0.5)}" opacity="0.8"/>`).join("") + `</g>`;
				}
				if (st.flash) {
					const op = (st.flashInt / 100) * Math.pow(clamp(1 - q, 0, 1), 2) * (0.7 + 0.3 * rnd());
					out += `<rect x="-960" y="-540" width="1920" height="1080" fill="${st.flashColor}" opacity="${r2(op * 0.55)}"/>`;
				}
				if (st.sky) {
					const per = 1 / Math.max(1, st.flickers);
					const phase = (q % per) / per;
					const op = (st.flashInt / 100) * (phase < 0.35 ? 1 - phase / 0.35 : 0) * (1 - q * 0.5) * 0.6;
					if (op > 0.01) out += `<rect x="-960" y="-540" width="1920" height="1080" fill="url(#${u}-sky)" opacity="${r2(op)}"/>`;
				}
				if (st.audio) {
					const op = s.gain * (q * fr(st.dur) * COMP.fps < st.decay ? 1 : Math.max(0, 1 - (q * fr(st.dur) * COMP.fps - st.decay) / 3));
					out += `<rect x="-960" y="-540" width="1920" height="1080" fill="${st.flashColor}" opacity="${r2(op * 0.5)}"/>`;
				}
			});
			return out;
		}

		/* ---- the canvas ---- */
		function paintCanvas() {
			const svg = q(".lmt-frame svg:not(.lmt-measure)");
			const t = S.t;
			const previewOn = S.preview && S.preview.status === "done" && byId(S.preview.id) && byId(S.preview.id).solo;
			let body = "";
			for (let i = S.layers.length - 1; i >= 0; i--) body += drawLayer(S.layers[i], t, S.layers);

			let selOut = "";
			selected().forEach((l) => {
				if (l.kind === "audio") return;
				const M = worldMatrix(l, t);
				const b = contentBox(l);
				const c = [[b.x0, b.y0], [b.x0 + b.w, b.y0], [b.x0 + b.w, b.y0 + b.h], [b.x0, b.y0 + b.h]].map((p) => pt(M, p[0], p[1]));
				const a = pt(M, l.ax || 0, l.ay || 0);
				selOut += `<polygon points="${c.map((p) => `${r2(p[0])},${r2(p[1])}`).join(" ")}" fill="none" stroke="#9cb4ff" stroke-width="2" vector-effect="non-scaling-stroke"/>` +
					c.map((p) => `<rect x="${r2(p[0] - 6)}" y="${r2(p[1] - 6)}" width="12" height="12" fill="#9cb4ff"/>`).join("") +
					`<g transform="translate(${r2(a[0])} ${r2(a[1])})"><circle r="10" fill="none" stroke="#ff2e74" stroke-width="3"/><path d="M-18 0H18M0 -18V18" stroke="#ff2e74" stroke-width="3"/></g>`;
			});

			svg.innerHTML = `<defs><linearGradient id="${u}-bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#101335"/><stop offset="1" stop-color="#090a16"/></linearGradient>` +
				`<linearGradient id="${u}-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#cfe1ff"/><stop offset="1" stop-color="#cfe1ff" stop-opacity="0.2"/></linearGradient>` +
				`<filter id="${u}-glow" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur stdDeviation="14"/></filter>` +
				`<filter id="${u}-blur" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur stdDeviation="5"/></filter></defs>` +
				`<rect width="${COMP.w}" height="${COMP.h}" fill="#05070b"/>${body}${selOut}`;
			q(".lmt-previewbadge").hidden = !previewOn;
			q(".lmt-selinfo").textContent = S.sel.length ? `${S.sel.length} selected` : "";
		}

		/* ---- the timeline ---- */
		function paintTimeline() {
			const grid = q(".lmt-tlgrid");
			const D = COMP.dur;
			const pct = (s) => `${((clamp(s, 0, D) / D) * 100).toFixed(3)}%`;
			const ticks = [];
			for (let s = 0; s <= D; s += 1) ticks.push(`<i style="left:${pct(s)}"><b>${s}s</b></i>`);
			const wa = `<em class="lmt-wa" style="left:${pct(COMP.wa[0])};width:${pct(COMP.wa[1] - COMP.wa[0])}"></em>`;
			const colors = { text: "#ff2e74", shape: "#5865f2", solid: "#8d93a8", null: "#ff2e74", audio: "#27c93f", precomp: "#e8b04c", grid: "#00c2ff", head: "#5865f2", box: "#ff7262", strike: "#a4c4ff", preview: "#ff5f56" };
			const rows = S.layers.map((l, i) => {
				const isSel = S.sel.indexOf(l.id) !== -1;
				const col = colors[l.kind] || "#8d93a8";
				const keys = keysOf(l).map((kt) => `<u style="left:${pct(kt)}" title="keyframe ${LazyTC(kt)}"></u>`).join("");
				const marks = l.fade && l.fade.markers ? (l.fade.in ? `<s style="left:${pct(l.in)}" title="fade in"></s>` : "") + (l.fade.out ? `<s style="left:${pct(l.out - Math.max(1, l.fade.dur) / Math.max(0.1, l.fade.spd) / COMP.fps)}" title="fade out"></s>` : "") : "";
				const wave = l.kind === "audio" ? `<svg class="lmt-wave" viewBox="0 0 ${Math.round(D * 10)} 20" preserveAspectRatio="none" style="width:${pct(D)}"><path d="${wavePath(Math.round(D * 10), 20)}"/></svg>` : "";
				const tags = (l.locked ? `<i title="Locked">🔒</i>` : "") + (l.solo ? `<i class="lmt-solo" title="Solo">S</i>` : "") + (l.guide ? `<i class="lmt-guide" title="Guide layer">GUIDE</i>` : "") +
					(l.ownOpacityExpr ? `<i title="Has its own opacity expression">ƒx</i>` : "") + (l.parent && byId(l.parent) ? `<small>↳ ${esc(byId(l.parent).name)}</small>` : "") + (l.autoBox ? `<small>LazyType</small>` : "") + (l.fade ? `<small>fade</small>` : "");
				return `<div class="lmt-rname${isSel ? " lmt-sel" : ""}${l.fresh ? " lmt-fresh" : ""}" data-layer="${l.id}"><em>${i + 1}</em><b style="background:${col}"></b><span title="${esc(l.name)}">${esc(l.name)}</span>${tags}</div>` +
					`<div class="lmt-lane${isSel ? " lmt-sel" : ""}"><span class="lmt-bar lmt-bar--${l.kind}" data-layer="${l.id}" style="left:${pct(l.in)};width:${pct(Math.min(D, l.out) - clamp(l.in, 0, D))};background:${col}"><i>${esc(l.name)}</i>${keys}${marks}</span>${wave}</div>`;
			}).join("");
			grid.innerHTML = `<div class="lmt-tlcorner"></div><div class="lmt-ruler">${wa}${ticks.join("")}</div>${rows}<div class="lmt-playhead"><b></b></div>`;
			S.layers.forEach((l) => { l.fresh = false; });
			paintPlayhead();
		}

		const LazyTC = (t) => {
			const f = Math.round(t * COMP.fps);
			const s = Math.floor(f / COMP.fps);
			return `0:${String(Math.floor(s / 60)).padStart(2, "0")}:${String(s % 60).padStart(2, "0")}:${String(f % COMP.fps).padStart(2, "0")}`;
		};

		function paintPlayhead() {
			const grid = q(".lmt-tlgrid");
			const head = q(".lmt-playhead");
			if (!head) return;
			const lane = grid.querySelector(".lmt-lane");
			const left = lane ? lane.offsetLeft : 150;
			const width = lane ? lane.clientWidth : grid.clientWidth - 150;
			head.style.left = `${(left + (S.t / COMP.dur) * width).toFixed(1)}px`;
			q(".lmt-tc").textContent = LazyTC(S.t);
			q(".lmt-playbtn").innerHTML = S.playing ? ICONS.pause : ICONS.play;
			q(".lmt-playbtn").setAttribute("aria-label", S.playing ? "Pause" : "Play");
		}

		/* ---- the panel's live parts ---- */
		function paintPanel() {
			qa("[data-anchor]").forEach((b) => b.setAttribute("aria-pressed", String(+b.getAttribute("data-anchor") === S.anchorPos)));
			const sw = q(".lmt-swatches");
			sw.style.gridTemplateColumns = `repeat(${Math.min(S.swCol, S.swTot, tall ? 5 : 4)}, 1fr)`;
			sw.innerHTML = S.swatches.slice(0, S.swTot).map((c, i) => `<div class="lmt-sw"><label class="lmt-swtile" style="background:${c}" title="Click to change"><input type="color" value="${c}" data-swatch="${i}"></label><button type="button" class="lmt-pill" data-act="fill" data-sw="${i}">F</button><button type="button" class="lmt-pill" data-act="stroke" data-sw="${i}">S</button></div>`).join("");
			const pv = S.preview;
			const ps = q(".lmt-pstatus");
			ps.textContent = !pv ? "Set the work area (B / N), then render." :
				pv.status === "rendering" ? `Rendering the work area… ${pv.seconds} s` :
				pv.status === "done" ? `Preview ready: ${pv.file} (${byId(pv.id) && byId(pv.id).solo ? "showing" : "hidden"})` : "";
			q("[data-act='render']").textContent = pv && pv.status === "rendering" ? "✕ Cancel" : "▶ Render In→Out";
			q("[data-act='undo']").disabled = !S.history.length;
		}

		function paintLog() {
			const el = q(".lmt-log");
			el.textContent = S.msg[0];
			el.title = S.msg[0];
			el.className = `lmt-status lmt-log${S.msg[1] ? ` lmt-${S.msg[1]}` : ""}`;
		}

		function paintHint() {
			const d = S.did;
			const key = !d.select ? "select" : !d.anchor ? "anchor" : !d.head ? "head" : !d.autobox ? "autobox" : !d.fade ? "fade" : !d.stagger ? "stagger" : !d.strike ? "strike" : d.other < 2 ? "more" : "done";
			q(".lmt-hint").textContent = L.hints[key];
		}

		function paintAll() {
			paintCanvas();
			paintTimeline();
			paintPanel();
			paintLog();
			paintHint();
		}

		function log(text, kind, ms) {
			clearTimeout(msgTimer);
			S.msg = [text, kind || ""];
			paintLog();
			if (ms) msgTimer = setTimeout(() => { if (stage) { S.msg = ["Ready", "quiet"]; paintLog(); } }, ms);
		}

		function toast(text) {
			const el = q(".lmt-toast");
			el.textContent = text;
			el.classList.add("lmt-on");
			clearTimeout(toastTimer);
			toastTimer = setTimeout(() => el.classList.remove("lmt-on"), 2400);
		}

		function nudge(text) {
			const h = q(".lmt-hint");
			h.classList.remove("lmt-nudging");
			void h.offsetWidth;
			h.classList.add("lmt-nudging");
			log(text, "", 3000);
		}

		/* One undo step per tool, as the panel wraps its work. */
		function snapshot() {
			S.history.push(JSON.stringify({ layers: S.layers, sel: S.sel, preview: S.preview }));
			if (S.history.length > 24) S.history.shift();
		}

		function undo() {
			const last = S.history.pop();
			if (!last) return;
			const st = JSON.parse(last);
			S.layers = st.layers;
			S.sel = st.sel;
			S.preview = st.preview;
			clearTimeout(renderTimer);
			paintAll();
			log("Undo", "", 1200);
		}

		/* Results, the way the panel reports them: what was done and what was skipped, with the reason. */
		function report(done, skipped, kind) {
			const parts = [];
			if (done) parts.push(done);
			if (skipped.length) parts.push(`skipped ${skipped.join("; ")}`);
			log(parts.join(" · "), skipped.length && !done ? "warn" : kind || "ok", 7000);
		}

		const needSel = () => {
			if (S.sel.length) return true;
			nudge(L.need.select);
			return false;
		};

		function insertAbove(ref, layer) {
			const i = S.layers.indexOf(ref);
			S.layers.splice(i < 0 ? 0 : i, 0, layer);
		}

		/* ---- the tools ---- */
		function anchorTo(i) {
			if (!needSel()) return;
			snapshot();
			const skipped = [];
			let n = 0;
			selected().forEach((l) => {
				if (l.kind === "audio") { skipped.push(`${l.name} (no transform)`); return; }
				if (l.anim && l.anim.rot) { skipped.push(`${l.name} (animated rotation)`); return; }
				const b = contentBox(l);
				const ax = b.x0 + (i % 3) * (b.w / 2), ay = b.y0 + Math.floor(i / 3) * (b.h / 2);
				const RS = mul(R(rotAt(l, S.t)), Sc((l.scale === undefined ? 100 : l.scale) / 100));
				const d = pt(RS, ax - (l.ax || 0), ay - (l.ay || 0));
				if (l.anim && l.anim.pos) {
					l.anim.pos.from = [l.anim.pos.from[0] + d[0], l.anim.pos.from[1] + d[1]];
					l.anim.pos.to = [l.anim.pos.to[0] + d[0], l.anim.pos.to[1] + d[1]];
				}
				l.x += d[0]; l.y += d[1];
				l.ax = ax; l.ay = ay;
				n++;
			});
			S.anchorPos = i;
			S.did.anchor = true;
			paintAll();
			report(n ? `Anchor moved on ${n} layer${n === 1 ? "" : "s"}, nothing moved on screen` : "", skipped);
		}

		function centerComp() {
			if (!needSel()) return;
			snapshot();
			const skipped = [];
			let n = 0;
			selected().forEach((l) => {
				if (l.kind === "audio") { skipped.push(`${l.name} (no transform)`); return; }
				if (l.parent) { skipped.push(`${l.name} (parented: its position is not in comp space)`); return; }
				const RS = mul(R(rotAt(l, S.t)), Sc((l.scale === undefined ? 100 : l.scale) / 100));
				const off = pt(RS, l.ax || 0, l.ay || 0);
				const nx = COMP.w / 2 + off[0], ny = COMP.h / 2 + off[1];
				if (l.anim && l.anim.pos) {
					const dx = nx - l.anim.pos.to[0], dy = ny - l.anim.pos.to[1];
					l.anim.pos.from = [l.anim.pos.from[0] + dx, l.anim.pos.from[1] + dy];
					l.anim.pos.to = [nx, ny];
				}
				l.x = nx; l.y = ny;
				n++;
			});
			S.did.other++;
			paintAll();
			report(n ? `Centred the content of ${n} layer${n === 1 ? "" : "s"} in the comp` : "", skipped);
		}

		function headIt() {
			if (!needSel()) return;
			const lines = selected().filter((l) => l.kind === "shape" && l.shape.type === "path");
			const skipped = selected().filter((l) => !(l.kind === "shape" && l.shape.type === "path")).map((l) => `${l.name} (not a shape layer with a path)`);
			if (!lines.length) { report("", skipped); return; }
			snapshot();
			const h = S.head;
			lines.forEach((line) => {
				S.layers = S.layers.filter((l) => !(l.kind === "head" && l.ref === line.id));
				line.head = { type: h.type, round: h.round, double: h.double, rev: h.rev, anim: h.anim, frames: Math.max(1, h.frames), t0: Math.max(S.t, line.in) };
				const size = line.shape.strokeW * 3;
				const mk = (atStart) => ({ id: newId("head"), name: `${line.name} - Head${atStart ? " Start" : ""}`, kind: "head", ref: line.id, atStart, headType: h.type, round: h.round, size,
					in: line.in, out: line.out, x: 0, y: 0, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, parent: line.id, fresh: true });
				insertAbove(line, mk(false));
				if (h.double) insertAbove(line, mk(true));
			});
			S.did.head = true;
			paintAll();
			report(`${h.type} head on ${lines.map((l) => l.name).join(", ")}${h.anim ? `, trim keyed 0 → 100 over ${h.frames} frames` : ""} · Head Size ${lines[0].shape.strokeW * 3}, Offset Angle 0`, skipped);
			if (h.anim && !S.playing) seek(Math.max(0, S.t));
		}

		function fadeApply() {
			if (!needSel()) return;
			snapshot();
			const f = S.fade;
			const skipped = [];
			let n = 0;
			selected().forEach((l) => {
				if (l.kind === "audio") { skipped.push(`${l.name} (no opacity)`); return; }
				if (l.ownOpacityExpr) { skipped.push(`${l.name} (it has its own opacity expression)`); return; }
				l.fade = { dur: f.dur, spd: f.spd, ease: f.ease, in: f.in, out: f.out, markers: f.markers };
				n++;
			});
			S.did.fade = true;
			paintAll();
			report(n ? `Fade ${f.in && f.out ? "in and out" : f.in ? "in" : "out"} (${f.ease}, ${f.dur} frames at speed ${f.spd}) on ${n} layer${n === 1 ? "" : "s"}` : "", skipped);
		}

		function fadeClear() {
			if (!needSel()) return;
			snapshot();
			let n = 0;
			selected().forEach((l) => { if (l.fade) { delete l.fade; n++; } });
			paintAll();
			report(`Cleared the LazyMotion fade and markers from ${n} layer${n === 1 ? "" : "s"}; other expressions and markers left alone`, []);
		}

		function swatch(kind, i) {
			if (!needSel()) return;
			snapshot();
			const c = S.swatches[i];
			const skipped = [];
			let n = 0;
			selected().forEach((l) => {
				if (l.kind === "shape") { if (kind === "fill") l.shape.fill = c; else { l.shape.stroke = c; if (!l.shape.strokeW) l.shape.strokeW = 6; } n++; }
				else if (l.kind === "text") { if (kind === "fill") l.text.fill = c; else { l.text.stroke = c; if (!l.text.strokeW) l.text.strokeW = Math.max(2, Math.round(l.text.size / 18)); } n++; }
				else if (l.kind === "solid" && kind === "fill") { l.solid.fill = c; n++; }
				else if (l.kind === "solid") skipped.push(`${l.name} (a solid takes a fill only)`);
				else skipped.push(`${l.name} (not a shape, text or solid)`);
			});
			S.did.other++;
			paintAll();
			report(n ? `${kind === "fill" ? "Fill" : "Stroke"} ${c} on ${n} layer${n === 1 ? "" : "s"}` : "", skipped);
		}

		function stagger() {
			if (!needSel()) return;
			const st = S.stagger;
			let list = selected().filter((l) => l.kind !== "audio");
			const skipped = selected().filter((l) => l.locked).map((l) => `${l.name} (locked)`);
			list = list.filter((l) => !l.locked);
			if (st.keys) {
				list.forEach((l) => { if (!keysOf(l).length) skipped.push(`${l.name} (no keyframes to move)`); });
				list = list.filter((l) => keysOf(l).length);
			}
			if (!list.length) { report("", skipped.length ? skipped : ["nothing to stagger"]); return; }
			snapshot();
			if (st.rev) list.reverse();
			list.forEach((l, i) => {
				const d = fr(st.frames * i);
				if (st.keys) {
					if (l.anim && l.anim.pos) { l.anim.pos.t0 += d; l.anim.pos.t1 += d; }
					if (l.anim && l.anim.rot) { l.anim.rot.t0 += d; l.anim.rot.t1 += d; }
				} else {
					l.in += d; l.out += d;
					if (l.anim && l.anim.pos) { l.anim.pos.t0 += d; l.anim.pos.t1 += d; }
					if (l.anim && l.anim.rot) { l.anim.rot.t0 += d; l.anim.rot.t1 += d; }
					if (l.autoBox) l.autoBox.t0 += d;
					S.layers.filter((c) => c.parent === l.id && (c.kind === "head" || c.kind === "box")).forEach((c) => { c.in += d; c.out += d; });
				}
			});
			S.did.stagger = true;
			paintAll();
			report(`Staggered ${list.length} layer${list.length === 1 ? "" : "s"} by ${st.frames} frame${Math.abs(st.frames) === 1 ? "" : "s"} each, ${st.rev ? "bottom to top" : "top to bottom"}${st.keys ? ", keyframes only" : ""}`, skipped);
		}

		function nullParent() {
			if (!needSel()) return;
			const list = selected().filter((l) => l.kind !== "audio");
			if (!list.length) { report("", ["Music.wav (an audio layer cannot be parented)"]); return; }
			snapshot();
			let x0 = Infinity, y0 = Infinity, x1 = -Infinity, y1 = -Infinity;
			list.forEach((l) => {
				const M = worldMatrix(l, S.t);
				const b = contentBox(l);
				[[b.x0, b.y0], [b.x0 + b.w, b.y0], [b.x0 + b.w, b.y0 + b.h], [b.x0, b.y0 + b.h]].forEach((p) => {
					const w = pt(M, p[0], p[1]);
					x0 = Math.min(x0, w[0]); y0 = Math.min(y0, w[1]); x1 = Math.max(x1, w[0]); y1 = Math.max(y1, w[1]);
				});
			});
			const n = S.layers.filter((l) => l.kind === "null").length;
			const nul = { id: newId("null"), name: n ? `Control ${n + 1}` : "Control", kind: "null", in: 0, out: COMP.dur, x: (x0 + x1) / 2, y: (y0 + y1) / 2, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, fresh: true };
			const kept = [];
			list.forEach((l) => {
				if (l.parent && S.sel.indexOf(l.parent) !== -1) { kept.push(`${l.name} keeps its parent ${byId(l.parent).name}`); return; }
				reparent(l, nul);
			});
			S.layers.unshift(nul);
			S.sel = [nul.id];
			S.did.other++;
			paintAll();
			report(`${nul.name} on top, centred on the selection, ${list.length} layer${list.length === 1 ? "" : "s"} parented; nothing moved`, kept);
		}

		function precomp1() {
			if (!needSel()) return;
			snapshot();
			const skipped = [];
			const made = [];
			selected().forEach((l) => {
				if (l.kind === "null") { skipped.push(`${l.name} (nulls are skipped)`); return; }
				if (l.kind === "audio") { skipped.push(`${l.name} (audio)`); return; }
				if ((l.kind === "shape" || l.kind === "text") && l.parent) { skipped.push(`${l.name} (a parented shape or text layer: use Precomp (Group))`); return; }
				const outside = l.kind === "solid" || l.kind === "precomp";
				const i = S.layers.indexOf(l);
				const inner = Object.assign({}, l, { parent: null, fresh: false, fade: null });
				/* Attributes left outside: the comp layer takes the transform, the inner sits at its origin.
				   Attributes inside: the comp layer is the comp itself, at the comp's own centre. */
				const pc = outside
					? { id: newId("pc"), name: `${l.name} Comp 1`, kind: "precomp", attrsOutside: true, inner: [inner], in: l.in, out: l.out, fresh: true, x: l.x, y: l.y, ax: l.ax, ay: l.ay, scale: l.scale, rot: l.rot, opacity: l.opacity, anim: l.anim, fade: l.fade, parent: l.parent, ownOpacityExpr: l.ownOpacityExpr }
					: { id: newId("pc"), name: `${l.name} Comp 1`, kind: "precomp", attrsOutside: false, inner: [inner], in: l.in, out: l.out, fresh: true, x: COMP.w / 2, y: COMP.h / 2, ax: COMP.w / 2, ay: COMP.h / 2, scale: 100, rot: 0, opacity: 100, anim: null, fade: null, parent: null };
				if (outside) { inner.anim = null; inner.rot = 0; inner.scale = 100; inner.ax = 0; inner.ay = 0; inner.x = 0; inner.y = 0; inner.ownOpacityExpr = false; inner.opacity = 100; }
				S.layers.splice(i, 1, pc);
				S.layers.filter((c) => c.parent === l.id).forEach((c) => reparent(c, pc));
				S.sel[S.sel.indexOf(l.id)] = pc.id;
				made.push(`${l.name} → ${pc.name}${outside ? " (attributes left outside)" : ""}`);
			});
			S.did.other++;
			paintAll();
			report(made.length ? `Precomposed: ${made.join(", ")}` : "", skipped);
		}

		function precompGroup() {
			if (!needSel()) return;
			const list = selected().filter((l) => l.kind !== "audio");
			const stranded = list.filter((l) => l.parent && S.sel.indexOf(l.parent) === -1);
			if (stranded.length) {
				report("", stranded.map((l) => `refused: ${l.name} is parented to ${byId(l.parent).name}, which is not selected`));
				return;
			}
			if (!list.length) return;
			snapshot();
			const top = Math.min.apply(null, list.map((l) => S.layers.indexOf(l)));
			const n = S.layers.filter((l) => l.kind === "precomp" && /^Pre-comp/.test(l.name)).length;
			const pc = { id: newId("pcg"), name: `Pre-comp ${n + 1}`, kind: "precomp", attrsOutside: false, inner: list.map((l) => Object.assign({}, l, { fresh: false })), fresh: true,
				in: Math.min.apply(null, list.map((l) => l.in)), out: Math.max.apply(null, list.map((l) => l.out)), x: COMP.w / 2, y: COMP.h / 2, ax: COMP.w / 2, ay: COMP.h / 2, scale: 100, rot: 0, opacity: 100 };
			/* A layer left outside whose parent went in now hangs off the precomp, where it was. */
			const orphans = S.layers.filter((l) => list.indexOf(l) === -1 && l.parent && list.some((p) => p.id === l.parent));
			orphans.forEach((l) => reparent(l, null));
			S.layers = S.layers.filter((l) => list.indexOf(l) === -1);
			S.layers.splice(Math.min(top, S.layers.length), 0, pc);
			orphans.forEach((l) => reparent(l, pc));
			S.sel = [pc.id];
			S.did.other++;
			paintAll();
			report(`${pc.name}: ${list.length} layers in one precomp spanning ${LazyTC(pc.in)} to ${LazyTC(pc.out)}`, []);
		}

		/* ---- dialogs: a form the panel's own way ---- */
		function dialog(opts) {
			const box = q(".lmt-dialog");
			return new Promise((resolve) => {
				box.innerHTML = `<div class="lmt-dialog__box${opts.wide ? " lmt-dialog__box--wide" : ""}">
					<div class="lmt-dialog__h"><h3>${esc(opts.title)}</h3><button type="button" class="lmt-close" data-dialog="cancel" title="Close">×</button></div>
					<div class="lmt-dialog__b">${opts.html}</div>
					<div class="lmt-dialog__f">${opts.left || ""}<span class="lmt-grow"></span><button type="button" class="lmt-btn" data-dialog="cancel">Cancel</button><button type="button" class="lmt-btn lmt-btn--accent" data-dialog="ok">${esc(opts.ok || "Apply")}</button></div>
				</div>`;
				box.hidden = false;
				box.onclick = (e) => {
					const b = e.target instanceof Element ? e.target.closest("[data-dialog]") : null;
					if (!b) return;
					const which = b.getAttribute("data-dialog");
					if (which === "ok" && opts.validate && !opts.validate(box)) return;
					box.hidden = true;
					box.onclick = null;
					resolve(which);
				};
				if (opts.after) opts.after(box);
			});
		}

		const field = (f) => {
			if (f.type === "select") return `<label class="lmt-f"><span>${f.label}</span><select class="lmt-select" data-f="${f.k}">${f.opts.map((o) => `<option${o === f.value ? " selected" : ""}>${o}</option>`).join("")}</select></label>`;
			if (f.type === "check") return `<label class="lmt-f lmt-f--check"><input type="checkbox" data-f="${f.k}"${f.value ? " checked" : ""}><i></i><span>${f.label}</span></label>`;
			if (f.type === "color") return `<label class="lmt-f"><span>${f.label}</span><span class="lmt-colorwrap" style="background:${f.value}"><input type="color" data-f="${f.k}" value="${f.value}"></span></label>`;
			if (f.type === "range") return `<label class="lmt-f lmt-f--range"><span>${f.label}</span><input type="range" data-f="${f.k}" value="${f.value}" min="${f.min}" max="${f.max}"><output>${f.value}</output></label>`;
			return `<label class="lmt-f"><span>${f.label}</span><input type="number" class="lmt-num" data-f="${f.k}" value="${f.value}"${f.min !== undefined ? ` min="${f.min}"` : ""}${f.step ? ` step="${f.step}"` : ""}></label>`;
		};
		const panelHtml = (title, fields) => `<fieldset class="lmt-fs"><legend>${title}</legend>${fields.map(field).join("")}</fieldset>`;
		const readForm = (box, defs) => {
			const out = {};
			defs.forEach((f) => {
				const el = box.querySelector(`[data-f="${f.k}"]`);
				if (!el) return;
				out[f.k] = f.type === "check" ? el.checked : f.type === "select" || f.type === "color" ? el.value : Number(el.value);
			});
			return out;
		};

		function autoBoxOpen() {
			if (!needSel()) return;
			const texts = selected().filter((l) => l.kind === "text");
			if (!texts.length) { report("", selected().map((l) => `${l.name} (not a text layer)`)); return; }
			const d = S.autoBoxLast || { style: "Typewriter (smooth)", settle: "Blur + rise", band: 2, timing: "unit", fpu: 1.5, total: 40, ease: "Ease out (types fast, settles)", atPlayhead: true,
				padX: 44, padY: 26, round: 14, lead: 1, smooth: 3, fade: 4, boxColor: "#1f2233", caret: true, caretW: 6, blink: 2, caretColor: "#ffffff", stroke: false, strokeW: 3 };
			const F1 = [
				{ k: "style", label: "Style", type: "select", opts: REVEAL_STYLES, value: d.style },
				{ k: "settle", label: "Settle", type: "select", opts: SETTLES, value: d.settle },
				{ k: "band", label: "Settle band:", type: "num", value: d.band, min: 1 },
				{ k: "fpu", label: "Frames / unit:", type: "num", value: d.fpu, min: 0.1, step: 0.5 },
				{ k: "total", label: "Total frames:", type: "num", value: d.total, min: 1 },
				{ k: "ease", label: "Ease:", type: "select", opts: REVEAL_EASES, value: d.ease },
				{ k: "atPlayhead", label: "Start at playhead", type: "check", value: d.atPlayhead },
			];
			const F2 = [
				{ k: "padX", label: "Padding X:", type: "num", value: d.padX, min: 0 }, { k: "padY", label: "Padding Y:", type: "num", value: d.padY, min: 0 },
				{ k: "round", label: "Roundness:", type: "num", value: d.round, min: 0 }, { k: "lead", label: "Box Lead:", type: "num", value: d.lead, min: 0 },
				{ k: "smooth", label: "Box Smooth:", type: "num", value: d.smooth, min: 1 }, { k: "fade", label: "Box Fade:", type: "num", value: d.fade, min: 0 },
				{ k: "boxColor", label: "Box colour:", type: "color", value: d.boxColor },
			];
			const F3 = [
				{ k: "caret", label: "Show Caret", type: "check", value: d.caret }, { k: "caretW", label: "Caret Width:", type: "num", value: d.caretW, min: 1 },
				{ k: "blink", label: "Caret Blink:", type: "num", value: d.blink, min: 0 }, { k: "caretColor", label: "Caret colour:", type: "color", value: d.caretColor },
				{ k: "stroke", label: "Add Stroke", type: "check", value: d.stroke }, { k: "strokeW", label: "Stroke Width:", type: "num", value: d.strokeW, min: 1 },
			];
			const timing = `<div class="lmt-f lmt-f--radios"><label><input type="radio" name="${u}-timing" value="unit"${d.timing === "unit" ? " checked" : ""}><i></i>Time per unit</label><label><input type="radio" name="${u}-timing" value="total"${d.timing === "total" ? " checked" : ""}><i></i>Total time</label></div>`;
			const html = `<div class="lmt-panels">${panelHtml("Text reveal", F1.slice(0, 3)) .replace("</fieldset>", timing + F1.slice(3).map(field).join("") + "</fieldset>")}${panelHtml("Box and Padding", F2)}${panelHtml("Caret and Stroke", F3)}</div>`;
			dialog({ title: "LazyMotion — Auto Box & Text Reveal", html, ok: "Apply", wide: true, left: `<button type="button" class="lmt-btn" data-dialog="remove">Remove box</button>` }).then((which) => {
				const box = q(".lmt-dialog");
				if (which === "remove") { autoBoxRemove(texts); return; }
				if (which !== "ok") return;
				const v = Object.assign(readForm(box, F1.concat(F2, F3)), { timing: box.querySelector(`input[name="${u}-timing"]:checked`).value });
				S.autoBoxLast = v;
				snapshot();
				texts.forEach((l) => {
					S.layers = S.layers.filter((c) => !(c.kind === "box" && c.ref === l.id));
					l.autoBox = Object.assign({}, v, { t0: v.atPlayhead ? S.t : l.in });
					const i = S.layers.indexOf(l);
					S.layers.splice(i + 1, 0,
						{ id: newId("box"), name: `${l.name} - Box`, kind: "box", ref: l.id, parent: l.id, in: l.in, out: l.out, fresh: true },
						{ id: newId("boxm"), name: `${l.name} - Box Measure`, kind: "box", ref: l.id, parent: l.id, guide: true, in: l.in, out: l.out, fresh: true });
				});
				S.did.autobox = true;
				paintAll();
				report(`Auto Box on ${texts.map((l) => l.name).join(", ")}: ${v.style}, ${v.settle}, box ${v.padX}/${v.padY} px padding, roundness ${v.round}`, []);
			});
		}

		function autoBoxRemove(texts) {
			snapshot();
			let n = 0;
			texts.forEach((l) => { if (l.autoBox) { delete l.autoBox; S.layers = S.layers.filter((c) => !(c.kind === "box" && c.ref === l.id)); n++; } });
			paintAll();
			report(n ? `Removed the box, the measure layer, the LazyType animators and the Reveal controls from ${n} layer${n === 1 ? "" : "s"}` : "No Auto Box on the selected text", []);
		}

		function gridOpen() {
			const d = S.gridLast || { cols: 3, rows: 3, gx: 20, gy: 20, mx: 40, my: 40, out: "Shape Tiles (Fill)" };
			const F = [
				{ k: "cols", label: "Columns:", type: "num", value: d.cols, min: 1 }, { k: "rows", label: "Rows:", type: "num", value: d.rows, min: 1 },
				{ k: "gx", label: "Gutter X (px):", type: "num", value: d.gx, min: 0 }, { k: "gy", label: "Gutter Y (px):", type: "num", value: d.gy, min: 0 },
				{ k: "mx", label: "Margin X (px):", type: "num", value: d.mx, min: 0 }, { k: "my", label: "Margin Y (px):", type: "num", value: d.my, min: 0 },
				{ k: "out", label: "Output:", type: "select", opts: GRID_OUTPUTS, value: d.out },
			];
			const presets = `<fieldset class="lmt-fs"><legend>⚡ Quick Presets</legend><div class="lmt-presets">${[["2 x 2", 2, 2], ["3 x 3 (Thirds)", 3, 3], ["3 Columns", 3, 1], ["12 Columns", 12, 1]].map((p) => `<button type="button" class="lmt-btn" data-preset="${p[1]},${p[2]}">${p[0]}</button>`).join("")}</div></fieldset>`;
			const html = presets + panelHtml("Grid Configuration", F) + `<p class="lmt-dialog__err" hidden></p>`;
			dialog({ title: "LazyMotion — Grid Designer", html, ok: "Generate Grid",
				after: (box) => box.querySelectorAll("[data-preset]").forEach((b) => b.addEventListener("click", () => {
					const [c, r] = b.getAttribute("data-preset").split(",");
					box.querySelector("[data-f='cols']").value = c;
					box.querySelector("[data-f='rows']").value = r;
				})),
				validate: (box) => {
					const v = readForm(box, F);
					const err = box.querySelector(".lmt-dialog__err");
					const cells = v.cols * v.rows;
					const tw = (COMP.w - 2 * v.mx - (v.cols - 1) * v.gx) / v.cols;
					const th = (COMP.h - 2 * v.my - (v.rows - 1) * v.gy) / v.rows;
					if (cells > 400) { err.textContent = `${cells} cells: more than 400 is refused.`; err.hidden = false; return false; }
					if (tw <= 0 || th <= 0) { err.textContent = `No room for the tiles: the comp is ${COMP.w} × ${COMP.h} px, and these margins and gutters leave ${Math.round(tw)} × ${Math.round(th)} px per cell.`; err.hidden = false; return false; }
					return true;
				},
			}).then((which) => {
				if (which !== "ok") return;
				const v = readForm(q(".lmt-dialog"), F);
				S.gridLast = v;
				snapshot();
				const tw = (COMP.w - 2 * v.mx - (v.cols - 1) * v.gx) / v.cols;
				const th = (COMP.h - 2 * v.my - (v.rows - 1) * v.gy) / v.rows;
				S.layers = S.layers.filter((l) => l.kind !== "grid");
				const made = [];
				for (let r = 0; r < v.rows; r++) for (let c = 0; c < v.cols; c++) {
					made.push({ id: newId("grid"), name: `${v.out === "Guide Nulls" ? "Grid Null" : v.out === "Outline Strokes" ? "Grid Line" : "Grid Tile"} ${r + 1}-${c + 1}`, kind: "grid", cell: { type: v.out, w: tw, h: th },
						in: 0, out: COMP.dur, x: v.mx + c * (tw + v.gx) + tw / 2, y: v.my + r * (th + v.gy) + th / 2, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, guide: v.out === "Guide Nulls", fresh: true });
				}
				S.layers = made.concat(S.layers);
				S.sel = [];
				S.did.other++;
				paintAll();
				report(`${v.out}: ${v.cols} × ${v.rows}, ${made.length} layers, ${Math.round(tw)} × ${Math.round(th)} px each, gutters ${v.gx}/${v.gy}, margins ${v.mx}/${v.my}`, []);
			});
		}

		function strikeOpen() {
			const d = S.strikeLast || { style: "Direct Bolt + Flash", boltColor: "#9fd8ff", flashColor: "#ffffff", dur: 10, count: 3, gap: 12, flickers: 3, boltInt: 75, flashInt: 80, random: 50, threshold: 10, gain: 12, decay: 0, minGap: 5, fill: true, cti: false, precomp: false };
			const F = [
				{ k: "style", label: "Style", type: "select", opts: STRIKE_STYLES, value: d.style },
				{ k: "boltColor", label: "Bolt Color:", type: "color", value: d.boltColor }, { k: "flashColor", label: "Flash Color:", type: "color", value: d.flashColor },
				{ k: "dur", label: "Strike Dur. (fr):", type: "num", value: d.dur, min: 1 }, { k: "count", label: "Manual strikes:", type: "num", value: d.count, min: 1 },
				{ k: "gap", label: "Gap (frames):", type: "num", value: d.gap, min: 0 }, { k: "flickers", label: "Flickers/Sky:", type: "num", value: d.flickers, min: 1 },
				{ k: "boltInt", label: "Bolt Int:", type: "range", value: d.boltInt, min: 0, max: 100 }, { k: "flashInt", label: "Flash Int:", type: "range", value: d.flashInt, min: 0, max: 100 },
				{ k: "random", label: "Random:", type: "range", value: d.random, min: 0, max: 100 },
				{ k: "threshold", label: "Threshold:", type: "range", value: d.threshold, min: 0, max: 100 }, { k: "gain", label: "Gain:", type: "range", value: d.gain, min: 0, max: 30 },
				{ k: "decay", label: "Decay (fr):", type: "range", value: d.decay, min: 0, max: 30 }, { k: "minGap", label: "Min Gap:", type: "range", value: d.minGap, min: 0, max: 30 },
				{ k: "fill", label: "Fill Work Area", type: "check", value: d.fill }, { k: "cti", label: "Start at CTI", type: "check", value: d.cti }, { k: "precomp", label: "Pre-compose", type: "check", value: d.precomp },
			];
			const by = (keys) => F.filter((f) => keys.indexOf(f.k) !== -1);
			const html = `<div class="lmt-panels lmt-panels--2"><div>${panelHtml("Style", by(["style"]))}${panelHtml("Colors", by(["boltColor", "flashColor"]))}${panelHtml("Timing", by(["dur", "count", "gap", "flickers"]))}</div>` +
				`<div>${panelHtml("Intensity & Randomness", by(["boltInt", "flashInt", "random"]))}${panelHtml("Audio Sync", by(["threshold", "gain", "decay", "minGap"]))}${panelHtml("Options", by(["fill", "cti", "precomp"]))}</div></div>`;
			dialog({ title: "LazyStrike FX", html, ok: "⚡ Generate Lightning", wide: true,
				after: (box) => box.querySelectorAll("input[type=range]").forEach((r) => r.addEventListener("input", () => { r.nextElementSibling.textContent = r.value; })),
			}).then((which) => {
				if (which !== "ok") return;
				const v = readForm(q(".lmt-dialog"), F);
				S.strikeLast = v;
				const startT = v.cti ? S.t : v.fill ? COMP.wa[0] : 0;
				const endT = v.fill ? COMP.wa[1] : COMP.dur;
				const period = fr(v.dur + v.gap);
				const rnd = seeded(20260927 + Math.round(S.t * 1000));
				const strikes = [];
				const st = { style: v.style, bolt: v.style === "Direct Bolt + Flash" || v.style === "Both Combined", flash: v.style === "Direct Bolt + Flash" || v.style === "Both Combined", sky: v.style === "Sky Flash" || v.style === "Both Combined", audio: v.style === "Audio-Driven",
					boltColor: v.boltColor, flashColor: v.flashColor, dur: v.dur, flickers: v.flickers, boltInt: v.boltInt, flashInt: v.flashInt, random: v.random, decay: v.decay, strikes };
				if (st.audio) {
					const music = byId("music");
					let last = -Infinity;
					for (let i = 1; i < MUSIC.values.length - 1; i++) {
						const t = i * MUSIC.step;
						if (t < startT || t > endT) continue;
						const a = MUSIC.values[i] * 100;
						if (a > MUSIC.values[i - 1] * 100 && a >= MUSIC.values[i + 1] * 100 && a > v.threshold && t - last >= fr(v.minGap)) {
							strikes.push({ at: t, seed: i, gain: clamp((a * v.gain) / 1000, 0, 1), x0: 0, x1: 0, y1: 0 });
							last = t;
						}
					}
					st.dur = Math.max(2, v.decay + 3);
					if (!music) { report("", ["no audio layer for Audio-Driven"]); return; }
				} else {
					const n = v.fill ? Math.floor((endT - startT) / period) : v.count;
					for (let i = 0; i < n; i++) {
						const jitter = (rnd() - 0.5) * period * (v.random / 100) * 0.8;
						const at = startT + i * period + (i ? jitter : 0);
						if (at + fr(v.dur) > endT + 0.001) break;
						strikes.push({ at, seed: 1000 + i * 7919, x0: -700 + rnd() * 1400, x1: -500 + rnd() * 1000, y1: 250 + rnd() * 250 });
					}
				}
				if (!strikes.length) {
					report("", [st.audio ? "no peak above the threshold in the work area" : "nothing fits: the playhead is past the work area"]);
					return;
				}
				snapshot();
				S.layers = S.layers.filter((l) => l.kind !== "strike" && !(l.kind === "precomp" && l.strikeComp));
				const first = Math.min.apply(null, strikes.map((s) => s.at)), lastT = Math.max.apply(null, strikes.map((s) => s.at)) + fr(st.dur);
				const layer = { id: newId("strike"), name: st.audio ? "LazyStrike Audio Flash" : st.sky && !st.bolt ? "LazyStrike Sky Flash" : "LazyStrike Bolt + Flash", kind: "strike", strike: st, in: first, out: Math.min(COMP.dur, lastT + 0.2), x: COMP.w / 2, y: COMP.h / 2, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, fresh: true };
				if (v.precomp) {
					S.layers.unshift({ id: newId("pcs"), name: "LazyStrike Comp", kind: "precomp", strikeComp: true, attrsOutside: false, inner: [layer], in: layer.in, out: layer.out, x: COMP.w / 2, y: COMP.h / 2, ax: 0, ay: 0, scale: 100, rot: 0, opacity: 100, fresh: true });
				} else {
					S.layers.unshift(layer);
				}
				S.sel = [];
				S.did.strike = true;
				paintAll();
				report(`${v.style}: ${strikes.length} strike${strikes.length === 1 ? "" : "s"} from ${LazyTC(first)}, ${st.dur} frames each, in Add mode${v.precomp ? ", pre-composed" : ""}`, []);
				if (!S.playing) seek(first + fr(2));
			});
		}

		/* ---- LazyPreview Render (simulated: the rendered file is the comp itself) ---- */
		function renderPreview() {
			if (S.preview && S.preview.status === "rendering") {
				clearTimeout(renderTimer);
				S.preview = null;
				paintPanel();
				log("Cancelled this render (only this one)", "", 2500);
				return;
			}
			snapshot();
			const stamp = new Date();
			const pad = (n) => String(n).padStart(2, "0");
			S.preview = { status: "rendering", seconds: 0, file: `preview_${stamp.getFullYear()}${pad(stamp.getMonth() + 1)}${pad(stamp.getDate())}_${pad(stamp.getHours())}${pad(stamp.getMinutes())}${pad(stamp.getSeconds())}_1.mp4`, id: null };
			log("Project saved · aerender is rendering the work area in the background (H.264, 15 Mbps)", "");
			paintPanel();
			const tick = () => {
				if (!stage || !S.preview || S.preview.status !== "rendering") return;
				S.preview.seconds++;
				paintPanel();
				if (S.preview.seconds >= (reduced() ? 1 : 3)) {
					S.layers = S.layers.filter((l) => l.kind !== "preview");
					const id = newId("pv");
					S.layers.unshift({ id, name: "[PREVIEW] preview", kind: "preview", solo: true, in: COMP.wa[0], out: COMP.wa[1], fresh: true });
					S.preview.status = "done";
					S.preview.id = id;
					S.did.other++;
					paintAll();
					report(`Rendered ${S.preview.file} into AE_Previews beside the project; it plays as a solo'd [PREVIEW] layer, footage in the Lazy Preview Files bin`, []);
					toast("🎬 Preview ready");
					return;
				}
				renderTimer = setTimeout(tick, reduced() ? 100 : 1000);
			};
			renderTimer = setTimeout(tick, reduced() ? 100 : 1000);
		}

		function previewToggle() {
			const l = S.preview && S.preview.status === "done" ? byId(S.preview.id) : null;
			if (!l) { log("No preview to toggle: render one first", "", 2500); return; }
			l.solo = !l.solo;
			paintAll();
			log(l.solo ? "Showing the preview (solo)" : "Showing the live comp", "", 2000);
		}

		function previewRemove() {
			const l = S.preview && S.preview.status === "done" ? byId(S.preview.id) : null;
			if (!l) { log("No preview to remove", "", 2500); return; }
			snapshot();
			S.layers = S.layers.filter((x) => x !== l);
			const file = S.preview.file;
			S.preview = null;
			paintAll();
			report(`Removed the preview layer, its footage and ${file}`, []);
		}

		/* ---- playback ---- */
		function seek(t) {
			S.t = clamp(t, 0, COMP.dur);
			paintPlayhead();
			paintCanvas();
		}

		function stopPlay() {
			S.playing = false;
			if (playRaf) { cancelAnimationFrame(playRaf); playRaf = 0; }
			paintPlayhead();
		}

		function togglePlay() {
			if (S.playing) { stopPlay(); return; }
			S.playing = true;
			S.did.play = true;
			paintPlayhead();
			const t0 = S.t >= COMP.dur - 0.05 ? 0 : S.t;
			const at0 = performance.now();
			const step = (now) => {
				playRaf = 0;
				if (!stage || !S.playing) return;
				S.t = (t0 + (now - at0) / 1000) % COMP.dur;
				paintPlayhead();
				paintCanvas();
				playRaf = requestAnimationFrame(step);
			};
			playRaf = requestAnimationFrame(step);
		}

		function reset() {
			stopPlay();
			clearTimeout(renderTimer);
			S = start();
			q(".lmt-dialog").hidden = true;
			qa("[data-field]").forEach((el) => {
				const f = el.getAttribute("data-field");
				const defaults = { staggerFrames: 4, staggerRev: false, staggerKeys: false, headType: "Triangle", headRound: false, headDouble: false, headRev: false, headAnim: true, headFrames: 30, fadeDur: 20, fadeSpd: 1, fadeEase: "Linear", fadeIn: true, fadeOut: true, fadeMarkers: true, swTot: "5", swCol: "5" };
				if (el.type === "checkbox") el.checked = defaults[f]; else el.value = defaults[f];
			});
			paintAll();
		}

		/* ---- input ---- */
		root.addEventListener("click", (e) => {
			if (!stage || !(e.target instanceof Element)) return;
			const t = e.target;
			let el;
			if (t.closest(".lmt-dialog")) return;

			if ((el = t.closest("[data-layer]"))) {
				const id = el.getAttribute("data-layer");
				const i = S.sel.indexOf(id);
				if (i === -1) S.sel.push(id); else S.sel.splice(i, 1);
				S.did.select = S.did.select || S.sel.length > 0;
				paintCanvas();
				paintTimeline();
				paintHint();
				return;
			}
			if ((el = t.closest("[data-seek]")) && !t.closest(".lmt-rname")) {
				const lane = el.querySelector(".lmt-lane");
				const r = (lane || el).getBoundingClientRect();
				seek(((e.clientX - r.left) / r.width) * COMP.dur);
				return;
			}
			if ((el = t.closest("[data-anchor]"))) { anchorTo(+el.getAttribute("data-anchor")); return; }
			if ((el = t.closest("[data-act]"))) {
				const act = el.getAttribute("data-act");
				readPanel();
				if (act === "reset") reset();
				else if (act === "undo") undo();
				else if (act === "play") togglePlay();
				else if (act === "precomp1") precomp1();
				else if (act === "precompg") precompGroup();
				else if (act === "autobox") autoBoxOpen();
				else if (act === "grid") gridOpen();
				else if (act === "stagger") stagger();
				else if (act === "nullparent") nullParent();
				else if (act === "strike") strikeOpen();
				else if (act === "render") renderPreview();
				else if (act === "ptoggle") previewToggle();
				else if (act === "premove") previewRemove();
				else if (act === "head") headIt();
				else if (act === "center") centerComp();
				else if (act === "fade") fadeApply();
				else if (act === "fadeclear") fadeClear();
				else if (act === "fill" || act === "stroke") swatch(act, +el.getAttribute("data-sw"));
			}
		});

		root.addEventListener("input", (e) => {
			const t = e.target;
			if (!(t instanceof Element)) return;
			if (t.hasAttribute("data-swatch")) {
				S.swatches[+t.getAttribute("data-swatch")] = t.value;
				t.parentElement.style.background = t.value;
			} else if (t.hasAttribute("data-f") && t.type === "color") {
				t.parentElement.style.background = t.value;
			}
		});

		root.addEventListener("change", (e) => {
			const t = e.target;
			if (!(t instanceof Element) || !t.hasAttribute("data-field")) return;
			readPanel();
			if (t.getAttribute("data-field") === "swTot" || t.getAttribute("data-field") === "swCol") paintPanel();
		});

		function readPanel() {
			const g = (f) => q(`[data-field="${f}"]`);
			S.stagger = { frames: Math.round(Number(g("staggerFrames").value) || 0), rev: g("staggerRev").checked, keys: g("staggerKeys").checked };
			S.head = { type: g("headType").value, round: g("headRound").checked, double: g("headDouble").checked, rev: g("headRev").checked, anim: g("headAnim").checked, frames: Math.round(Number(g("headFrames").value) || 30) };
			S.fade = { dur: Number(g("fadeDur").value) || 20, spd: Number(g("fadeSpd").value) || 1, ease: g("fadeEase").value, in: g("fadeIn").checked, out: g("fadeOut").checked, markers: g("fadeMarkers").checked };
			S.swTot = Number(g("swTot").value) || 5;
			S.swCol = Number(g("swCol").value) || 5;
			while (S.swatches.length < S.swTot) S.swatches.push(["#8b95f8", "#ff7262", "#27c93f", "#00c2ff", "#e8b04c"][S.swatches.length % 5]);
		}

		root.addEventListener("keydown", (e) => {
			if (!stage || !(e.target instanceof Element)) return;
			if ((e.ctrlKey || e.metaKey) && (e.key === "z" || e.key === "Z") && !e.target.closest("input, select")) { e.preventDefault(); undo(); return; }
			if (e.key === " " && !e.target.closest("button, input, select, .lmt-dialog") && e.target.closest(".lmt-timeline, .lmt-viewer")) { e.preventDefault(); togglePlay(); }
		});

		build();
		if ("ResizeObserver" in window) new ResizeObserver(() => build()).observe(root.parentElement || root);
		else window.addEventListener("resize", build);
	}

	/* ------------------------------------------------------------------ start when near */
	function auto() {
		const els = Array.from(document.querySelectorAll("[data-lazymotion-demo]"));
		if (!els.length) return;
		if (!("IntersectionObserver" in window)) { els.forEach(mount); return; }
		const io = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) { io.unobserve(entry.target); mount(entry.target); }
			});
		}, { rootMargin: "400px 0px" });
		els.forEach((el) => io.observe(el));
	}

	window.LazyMotionDemo = { mount };

	if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", auto);
	else auto();
})();
