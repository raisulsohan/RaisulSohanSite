/*
 * Lazy-Image interactive demo.
 *
 * The same story as the Lazy-Image demo animation, with real buttons: log in with your own
 * browser, write a prompt in any language, choose a ratio, press Generate, and the picture is
 * downloaded and dropped straight onto the timeline — in After Effects or in Premiere Pro.
 * The pictures, the footage and the comp are the animation's own drawing code, injected at build.
 *
 * Two stages, scaled to fit: a wide one (1280 x 760) and a tall one for phones and narrow
 * columns (440 x 1120). It is a simulation: nothing is generated and nothing is downloaded.
 *
 * In the theme: a standalone page bundle (assets/lazyimage-demo.min.js), enqueued only on a
 * project whose kit names it (rs_project_demo_kit()); page-portfolio.php prints
 * <div class="rs-demo-wrap lzi-wrap"><div class="lzi rs-demo-mount" data-lazyimage-demo …>, and
 * the portfolio's pop-up player mounts the same bundle through window.LazyImageDemo.
 * It starts when it scrolls near, and its box is sized by CSS alone, so a cached page never
 * shifts.
 */
(() => {
	"use strict";

	/* The text builder the animation's comp is drawn with. */
	const T = (x, y, s, fill, size, weight = 400, anchor = "start", extra = "") =>
		`<text x="${x}" y="${y}" fill="${fill}" font-size="${size}" font-weight="${weight}" text-anchor="${anchor}" ${extra}>${esc(s)}</text>`;

	/* The pictures, the coast footage and the comp, from the demo animation. */
	function imgApple(p) {
  return `
<defs>
  <linearGradient id="${p}-wall" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f7f7f9"/><stop offset="1" stop-color="#e2e2e7"/></linearGradient>
  <linearGradient id="${p}-table" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fcfcfd"/><stop offset="1" stop-color="#d9d9df"/></linearGradient>
  <radialGradient id="${p}-body" cx="0.36" cy="0.30" r="0.78"><stop offset="0" stop-color="#ff7364"/><stop offset="0.42" stop-color="#e51f30"/><stop offset="1" stop-color="#7f0a1c"/></radialGradient>
  <linearGradient id="${p}-leaf" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#8fe36a"/><stop offset="1" stop-color="#2f8f3c"/></linearGradient>
  <filter id="${p}-blur" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="9"/></filter>
</defs>
<rect width="400" height="400" fill="url(#${p}-wall)"/>
<rect y="262" width="400" height="138" fill="url(#${p}-table)"/>
<ellipse cx="206" cy="324" rx="120" ry="20" fill="#000" opacity="0.22" filter="url(#${p}-blur)"/>
<path d="M200 118 C 150 96, 80 118, 78 200 C 76 270, 130 330, 178 328 C 190 327, 196 322, 200 322 C 204 322, 210 327, 222 328 C 270 330, 324 270, 322 200 C 320 118, 250 96, 200 118 Z" fill="url(#${p}-body)"/>
<ellipse cx="200" cy="123" rx="34" ry="12" fill="#7a0a1a" opacity="0.45"/>
<path d="M200 124 C 198 108, 202 92, 214 78" fill="none" stroke="#5a3a1a" stroke-width="7" stroke-linecap="round"/>
<path d="M212 92 C 232 70, 268 72, 282 86 C 262 104, 230 106, 212 92 Z" fill="url(#${p}-leaf)"/>
<path d="M214 91 C 236 86, 258 84, 278 87" fill="none" stroke="#1f6b2c" stroke-width="1.5" opacity="0.6"/>
<ellipse cx="150" cy="180" rx="26" ry="44" fill="#fff" opacity="0.32" transform="rotate(-18 150 180)"/>
<ellipse cx="128" cy="240" rx="10" ry="22" fill="#fff" opacity="0.14" transform="rotate(-10 128 240)"/>
`;
}

function imgBoat(p) {
  const glints = Array.from({ length: 10 }, (_, i) =>
    `<rect x="${180 - (40 + i * 9)}" y="${420 + i * 18}" width="${(40 + i * 9) * 2}" height="4" rx="2" fill="#ffe49a" opacity="${(0.55 - i * 0.045).toFixed(2)}"/>`).join("");
  const ripples = Array.from({ length: 6 }, (_, i) =>
    `<path d="M${20 + i * 60} ${470 + i * 26} q 20 -6 40 0" fill="none" stroke="#ffd0a0" stroke-width="1.5" opacity="0.35"/>`).join("");
  const trees = [30, 55, 75, 215, 240, 300, 330].map(x => `<path d="M${x - 8} 400 L${x} 372 L${x + 8} 400 Z" fill="#1c0f31"/>`).join("");
  const birds = [[60, 200], [92, 186], [120, 214], [280, 160], [300, 176]].map(([x, y]) =>
    `<path d="M${x - 10} ${y} q 5 -7 10 0 q 5 -7 10 0" fill="none" stroke="#2a1640" stroke-width="2.2" stroke-linecap="round"/>`).join("");
  return `
<defs>
  <linearGradient id="${p}-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1c1240"/><stop offset="0.35" stop-color="#6a2f7e"/><stop offset="0.62" stop-color="#ff6e5a"/><stop offset="0.82" stop-color="#ffb347"/><stop offset="1" stop-color="#ffd98a"/></linearGradient>
  <linearGradient id="${p}-water" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffae5e"/><stop offset="0.3" stop-color="#d9634f"/><stop offset="1" stop-color="#2a1b4e"/></linearGradient>
  <radialGradient id="${p}-sunglow" cx="0.5" cy="0.5" r="0.5"><stop offset="0" stop-color="#fff2b0" stop-opacity="0.9"/><stop offset="1" stop-color="#ffb347" stop-opacity="0"/></radialGradient>
</defs>
<rect width="360" height="640" fill="url(#${p}-sky)"/>
<circle cx="180" cy="392" r="150" fill="url(#${p}-sunglow)"/>
<circle cx="180" cy="392" r="62" fill="#ffe89a"/>
<path d="M0 404 L40 398 L70 386 L90 396 L130 392 L160 400 L200 396 L230 386 L250 392 L290 388 L320 396 L360 392 L360 412 L0 412 Z" fill="#26143f"/>
${trees}
<rect y="410" width="360" height="230" fill="url(#${p}-water)"/>
${glints}
${ripples}
<path d="M96 520 Q180 542 264 520 L246 548 Q180 560 114 548 Z" fill="#120b22"/>
<path d="M96 520 Q180 532 264 520" fill="none" stroke="#2a1a3d" stroke-width="3"/>
<path d="M176 522 L176 438" stroke="#120b22" stroke-width="4" stroke-linecap="round"/>
<path d="M178 440 L178 512 L232 512 Z" fill="#1a1030"/>
<path d="M150 522 C150 506, 168 506, 168 522 Z" fill="#120b22"/>
<ellipse cx="180" cy="566" rx="90" ry="10" fill="#120b22" opacity="0.35"/>
${birds}
`;
}

function imgCity(p) {
  let seed = 7;
  const rnd = () => { seed = (seed * 16807) % 2147483647; return seed / 2147483647; };
  // Far skyline first (dim), then the near buildings with lit windows.
  let b = "", x = 0;
  while (x < 640) {
    const w = 40 + Math.floor(rnd() * 60), h = 60 + Math.floor(rnd() * 90);
    b += `<rect x="${x}" y="${300 - h - 40}" width="${w}" height="${h + 40}" fill="#182452"/>`;
    x += w + 6;
  }
  x = 0;
  while (x < 640) {
    const w = 30 + Math.floor(rnd() * 48), h = 100 + Math.floor(rnd() * 150), top = 300 - h;
    b += `<rect x="${x}" y="${top}" width="${w}" height="${h}" fill="#0a0f2c"/>`;
    b += `<rect x="${x}" y="${top}" width="${w}" height="2" fill="#2b3d7a"/>`;
    for (let wy = top + 8; wy < 290; wy += 10) {
      for (let wx = x + 4; wx < x + w - 5; wx += 8) {
        const r = rnd();
        if (r < 0.62) b += `<rect x="${wx}" y="${wy}" width="4" height="6" fill="${r < 0.1 ? "#ff4fd8" : r < 0.22 ? "#3cf0ff" : "#ffd36a"}" opacity="${(0.55 + rnd() * 0.45).toFixed(2)}"/>`;
      }
    }
    x += w + 3;
  }
  const stars = Array.from({ length: 40 }, () =>
    `<circle cx="${(rnd() * 640).toFixed(0)}" cy="${(rnd() * 140).toFixed(0)}" r="${(0.6 + rnd()).toFixed(1)}" fill="#fff" opacity="${(0.3 + rnd() * 0.6).toFixed(2)}"/>`).join("");
  const rain = Array.from({ length: 30 }, () => {
    const rx = (rnd() * 640).toFixed(0), ry = (rnd() * 320).toFixed(0);
    return `<line x1="${rx}" y1="${ry}" x2="${rx - 5}" y2="${+ry + 22}" stroke="#9fb6ff" stroke-width="1" opacity="0.28"/>`;
  }).join("");
  return `
<defs>
  <linearGradient id="${p}-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#050818"/><stop offset="0.55" stop-color="#1a2a5e"/><stop offset="1" stop-color="#4a2f7e"/></linearGradient>
  <linearGradient id="${p}-street" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0c0f24"/><stop offset="1" stop-color="#05060f"/></linearGradient>
  <filter id="${p}-glow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="6"/></filter>
</defs>
<rect width="640" height="360" fill="url(#${p}-sky)"/>
${stars}
<ellipse cx="320" cy="300" rx="360" ry="90" fill="#ff3fd0" opacity="0.14" filter="url(#${p}-glow)"/>
${b}
<rect x="250" y="150" width="110" height="26" rx="6" fill="none" stroke="#ff4fd8" stroke-width="3" filter="url(#${p}-glow)"/>
<rect x="250" y="150" width="110" height="26" rx="6" fill="none" stroke="#ff8fe8" stroke-width="1.5"/>
<rect x="470" y="120" width="60" height="18" rx="5" fill="none" stroke="#3cf0ff" stroke-width="2.5" filter="url(#${p}-glow)"/>
<rect x="470" y="120" width="60" height="18" rx="5" fill="none" stroke="#a8f8ff" stroke-width="1.2"/>
<rect y="300" width="640" height="60" fill="url(#${p}-street)"/>
<rect x="200" y="304" width="210" height="40" fill="#ff4fd8" opacity="0.18" filter="url(#${p}-glow)"/>
<rect x="440" y="304" width="120" height="40" fill="#3cf0ff" opacity="0.16" filter="url(#${p}-glow)"/>
${rain}
`;
}

function footageCoast(p) {
  const waves = Array.from({ length: 7 }, (_, i) =>
    `<path d="M0 ${270 + i * 34} q 60 -10 120 0 t 120 0 t 120 0 t 120 0 t 120 0 t 120 0 t 120 0 t 120 0" fill="none" stroke="#ffffff" stroke-width="2" opacity="${(0.12 + i * 0.04).toFixed(2)}"/>`).join("");
  return `
<defs>
  <linearGradient id="${p}-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5aa7e6"/><stop offset="1" stop-color="#dff2ff"/></linearGradient>
  <linearGradient id="${p}-sea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2a86c2"/><stop offset="1" stop-color="#0b3d6b"/></linearGradient>
  <linearGradient id="${p}-cliff" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8a7351"/><stop offset="1" stop-color="#3d3020"/></linearGradient>
</defs>
<rect width="900" height="506" fill="url(#${p}-sky)"/>
<circle cx="700" cy="110" r="40" fill="#fff6d0" opacity="0.9"/>
<rect y="230" width="900" height="276" fill="url(#${p}-sea)"/>
${waves}
<path d="M560 506 L600 300 L660 260 L720 280 L780 220 L860 250 L900 240 L900 506 Z" fill="url(#${p}-cliff)"/>
<path d="M660 260 L720 280 L780 220 L860 250 L900 240 L900 262 L840 270 L780 246 L720 300 L660 284 Z" fill="#6f9a52" opacity="0.7"/>
<path d="M430 506 C 520 468, 620 474, 720 506 Z" fill="#e9dcc3"/>
<path d="M430 506 C 520 476, 620 482, 720 506" fill="none" stroke="#ffffff" stroke-width="3" opacity="0.6"/>
`;
}

function compDesign(p) {
  return `
<defs><linearGradient id="${p}-bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1c2233"/><stop offset="1" stop-color="#0b0e16"/></linearGradient></defs>
<rect width="900" height="506" fill="url(#${p}-bg)"/>
<circle cx="760" cy="120" r="180" fill="#1a73e8" opacity="0.10"/>
<circle cx="140" cy="440" r="140" fill="#00bcd4" opacity="0.08"/>
${T(44, 78, "FRESH PICKS", "#ffffff", 40, 800, "start", 'letter-spacing="3"')}
${T(46, 106, "Organic · Local · Daily", "#9aa1bc", 15, 500)}
<rect x="44" y="124" width="60" height="4" rx="2" fill="url(#${p}-accent)"/>
`;
}

	const ART = { imgApple, imgBoat, imgCity, footageCoast, compDesign };

	const STR = {
		en: {
			label: "Interactive demo",
			note: "nothing is really generated",
			reset: "Reset",
			region: "Lazy-Image interactive demo",
			hints: {
				login: "Lazy-Image works through your own browser. Press Login to start.",
				prompt: "Pick a prompt. Any language works, Bengali included.",
				generate: "Choose a ratio if you like, then press Generate Image.",
				wait: "It is generating in the browser, then downloading the file.",
				landed: "It landed on the timeline by itself. Try another prompt, or Premiere Pro.",
				done: "That is Lazy-Image. Press Reset to start over.",
			},
			need: { login: "Log in first.", prompt: "Pick a prompt first." },
		},
		bn: {
			label: "ইন্টারঅ্যাক্টিভ ডেমো",
			note: "আসলে কিছু তৈরি হয় না",
			reset: "রিসেট",
			region: "Lazy-Image ইন্টারঅ্যাক্টিভ ডেমো",
			hints: {
				login: "Lazy-Image চলে আপনার নিজের ব্রাউজার দিয়ে। শুরু করতে Login চাপুন।",
				prompt: "একটা প্রম্পট বেছে নিন। যেকোনো ভাষায় চলে, বাংলাতেও।",
				generate: "চাইলে মাপ বেছে নিন, তারপর Generate Image চাপুন।",
				wait: "ব্রাউজারে ছবি তৈরি হচ্ছে, তারপর ফাইলটা নামবে।",
				landed: "ছবিটা নিজে থেকেই টাইমলাইনে বসে গেছে। অন্য প্রম্পট বা Premiere Pro দেখুন।",
				done: "এই হলো Lazy-Image। আবার শুরু করতে রিসেট চাপুন।",
			},
			need: { login: "আগে লগইন করুন।", prompt: "আগে একটা প্রম্পট বেছে নিন।" },
		},
	};

	const clamp = (x, a, b) => Math.min(b, Math.max(a, x));
	const lerp = (a, b, u) => a + (b - a) * u;
	const easeInOut = (u) => (u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2);

	/* ------------------------------------------------------------------ what can be generated */
	const PROMPTS = [
		{ id: "apple", art: "imgApple", nat: [400, 400], ratio: "1:1", file: "lazy_image_chatgpt_1758585123456.png", text: "A single red apple on a white table, studio lighting" },
		{ id: "boat", art: "imgBoat", nat: [360, 640], ratio: "9:16", file: "lazy_image_chatgpt_1758585140912.png", text: "সূর্যাস্তে নদীর ধারে একটি ছোট নৌকা, সোনালি আলো" },
		{ id: "city", art: "imgCity", nat: [640, 360], ratio: "16:9", file: "lazy_image_chatgpt_1758585161377.png", text: "Neon city skyline at night, rain, cinematic" },
	];
	const RATIOS = [["1:1", 1, 1], ["16:9", 16, 9], ["9:16", 9, 16], ["4:5", 4, 5]];
	const APPS = {
		ae: { badge: "Ae", name: "After Effects", file: "FreshPicks_Promo.aep — Adobe After Effects", view: "Main Comp", base: "compDesign", baseRow: "FreshPicks_Design", host: "AFTER EFFECTS" },
		pr: { badge: "Pr", name: "Premiere Pro", file: "FreshPicks_Cut.prproj — Adobe Premiere Pro", view: "Program", base: "footageCoast", baseRow: "Coast_4K.mp4", host: "PREMIERE PRO" },
	};
	const VB = [900, 506];

	/* Where a placed picture sits in the composition, in viewBox units. */
	/* x, y, width and the tallest it may be, so a 9:16 picture still fits. */
	const SLOTS = [[470, 46, 380, 414], [64, 232, 300, 250], [520, 300, 330, 196]];

	const graphemes = (s) => (window.Intl && Intl.Segmenter
		? Array.from(new Intl.Segmenter("bn", { granularity: "grapheme" }).segment(s), (x) => x.segment)
		: Array.from(s));

	const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

	/* Scale a picture to cover a w × h box, centred, the way a placed still is. */
	function cover(nat, w, h) {
		const s = Math.max(w / nat[0], h / nat[1]);
		return `translate(${((w - nat[0] * s) / 2).toFixed(2)} ${((h - nat[1] * s) / 2).toFixed(2)}) scale(${s.toFixed(5)})`;
	}

	/* The size a picture takes in a slot: its own ratio, capped by the slot. */
	function boxFor(ratio, slotW, slotH) {
		const r = RATIOS.filter((x) => x[0] === ratio)[0] || RATIOS[0];
		let w = slotW;
		let h = (w * r[2]) / r[1];

		if (slotH && h > slotH) {
			h = slotH;
			w = (h * r[1]) / r[2];
		}

		return [Math.round(w), Math.round(h)];
	}

	function picture(id, promptId, ratio, w, h) {
		const p = PROMPTS.filter((x) => x.id === promptId)[0];
		const box = h || boxFor(ratio, w)[1];

		return {
			w: w,
			h: box,
			svg: `<clipPath id="${id}-clip"><rect width="${w}" height="${box}" rx="6"/></clipPath>` +
				`<g clip-path="url(#${id}-clip)"><g transform="${cover(p.nat, w, box)}">${ART[p.art](id)}</g></g>` +
				`<rect width="${w}" height="${box}" rx="6" fill="none" stroke="#00e5ff" stroke-opacity=".55"/>`,
		};
	}

	const ICONS = {
		spark: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.2 6.3L20.5 10l-6.3 2.2L12 18.5 9.8 12.2 3.5 10l6.3-1.7z"/></svg>`,
		reset: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>`,
	};

	const badge = (kind, s) => {
		const spec = kind === "Pr" ? { bg: "#2a0a3f", fg: "#e97fff" } : { bg: "#00005b", fg: "#9999ff" };
		return `<svg viewBox="0 0 ${s} ${s}" aria-hidden="true"><rect width="${s}" height="${s}" rx="${s * 0.22}" fill="${spec.bg}"/>` +
			`<rect x="${s * 0.08}" y="${s * 0.08}" width="${s * 0.84}" height="${s * 0.84}" rx="${s * 0.15}" fill="none" stroke="${spec.fg}" stroke-width="${s * 0.05}"/>` +
			`<text x="${s / 2}" y="${s * 0.66}" text-anchor="middle" fill="${spec.fg}" font-size="${s * 0.44}" font-weight="700">${kind}</text></svg>`;
	};

	const start = () => ({
		app: "ae",
		logged: false,
		prompt: "",
		ratio: "1:1",
		typing: null,          /* { chars, at, t0 } */
		gen: null,             /* { t0, step } */
		result: null,          /* { promptId, ratio, file } */
		browser: false,
		placed: { ae: [], pr: [] },
		fresh: "",
		status: ["Not logged in — Lazy-Image uses your own browser, no API key.", "quiet"],
		did: { login: false, generate: false, land: false, app: false },
	});

	/* ------------------------------------------------------------------ markup */
	function markup(u, L, tall) {
		const chip = (name, value, label, checked) =>
			`<button type="button" class="lzi-chip" role="radio" data-${name}="${value}" aria-checked="${checked}">${label}</button>`;

		return `<div class="lzi-stage ${tall ? "lzi-tall" : "lzi-wide"}">
<div class="lzi-head">
	<span class="lzi-label">${ICONS.spark}${L.label}<small>· ${L.note}</small></span>
	<p class="lzi-hint"></p>
	<button type="button" class="lzi-reset" data-act="reset">${ICONS.reset}${L.reset}</button>
</div>

<section class="lzi-app" aria-label="Adobe">
	<div class="lzi-titlebar">
		<button type="button" class="lzi-tab" role="tab" data-app="ae" aria-selected="true">${badge("Ae", 20)}After Effects</button>
		<button type="button" class="lzi-tab" role="tab" data-app="pr" aria-selected="false">${badge("Pr", 20)}Premiere Pro</button>
		<span class="lzi-file"></span>
		<span class="lzi-dots" aria-hidden="true"><i style="background:#f5a623"></i><i style="background:#27c93f"></i><i style="background:#ff5f56"></i></span>
	</div>

	<div class="lzi-body">
		<div class="lzi-viewer">
			<div class="lzi-viewer__bar"><b class="lzi-view-name">Main Comp</b><span>1920 × 1080 · 30 fps</span><span>Full</span></div>
			<div class="lzi-canvas"><svg viewBox="0 0 ${VB[0]} ${VB[1]}" aria-hidden="true"></svg></div>
			<div class="lzi-browser" aria-hidden="true">
				<div class="lzi-browser__bar"><i></i><i></i><i></i><span class="lzi-browser__url">chatgpt.com</span></div>
				<p class="lzi-browser__line">Signing in with your own browser…</p>
				<p class="lzi-browser__note">No API key needed</p>
			</div>
		</div>

		<div class="lzi-timeline">
			<div class="lzi-timeline__bar"><span class="lzi-tl-name">Timeline</span></div>
			<div class="lzi-rows"></div>
		</div>

		<div class="lzi-panel" aria-label="Lazy-Image">
			<div class="lzi-phead"><b>Lazy-Image</b><span class="lzi-host">AFTER EFFECTS</span><span class="lzi-ver">v2.6</span></div>

			<div class="lzi-loginrow">
				<button type="button" class="lzi-btn" data-act="login">🌐 Login to ChatGPT</button>
				<span class="lzi-badge">Login required</span>
			</div>

			<span class="lzi-cap">Image prompt</span>
			<div class="lzi-chips" role="radiogroup" aria-label="Prompt">${PROMPTS.map((p, i) =>
				chip("prompt", p.id, ["🍎 Apple", "🛶 নৌকা (বাংলা)", "🌃 Neon city"][i], false)).join("")}</div>
			<div class="lzi-prompt lzi-empty" data-placeholder="Describe the image you want (any language)…"></div>

			<span class="lzi-cap">Aspect ratio</span>
			<div class="lzi-chips" role="radiogroup" aria-label="Aspect ratio">${RATIOS.map((r) =>
				chip("ratio", r[0], r[0], r[0] === "1:1")).join("")}</div>

			<button type="button" class="lzi-btn lzi-btn--go" data-act="generate">✨ Generate Image</button>
			<div class="lzi-meter"><i></i></div>
			<p class="lzi-status"></p>

			<span class="lzi-cap">Generated image</span>
			<div class="lzi-preview"></div>

			<div class="lzi-chips">
				<button type="button" class="lzi-chip" data-act="copy">📋 Copy image</button>
				<button type="button" class="lzi-chip" data-act="folder">📂 Open folder</button>
			</div>

			<div class="lzi-foot"><span>🔗 Auto-timeline import active</span><span class="lzi-meta"></span></div>
		</div>
	</div>

	<div class="lzi-toast" aria-hidden="true"></div>
</section>
</div>`;
	}

	/* ------------------------------------------------------------------ one demo */
	let seq = 0;
	const reduceMQ = window.matchMedia ? window.matchMedia("(prefers-reduced-motion: reduce)") : null;
	const reduced = () => Boolean(reduceMQ && reduceMQ.matches);

	function mount(root) {
		if (root.lziMounted) {
			return;
		}
		root.lziMounted = true;

		const u = `lzi${++seq}`;
		const lang = (root.getAttribute("data-lang") || document.documentElement.lang || "en").toLowerCase();
		const L = STR[lang.indexOf("bn") === 0 ? "bn" : "en"];

		let S = start();
		let tall = null, stage = null, W = 0, k = 1, raf = 0, toastTimer = 0, flyId = 0;

		const q = (sel) => stage.querySelector(sel);
		const qa = (sel) => Array.from(stage.querySelectorAll(sel));

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
			root.innerHTML = markup(u, L, tall);
			stage = root.firstElementChild;
			root.classList.add("lzi-ready");
			fit();
			paintAll();
		}

		function fit() {
			k = root.clientWidth / W || 1;
			stage.style.transform = `scale(${k})`;
		}

		/* ---- the composition ---- */
		function paintCanvas() {
			const app = APPS[S.app];
			const svg = q(".lzi-canvas svg");
			const placed = S.placed[S.app];
			const base = `${u}-${S.app}-base`;

			svg.innerHTML = `<defs><linearGradient id="${base}-accent" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1a73e8"/><stop offset="1" stop-color="#00bcd4"/></linearGradient></defs>` +
				`<g>${ART[app.base](base)}</g>` + placed.map((item, i) => {
				const slot = SLOTS[i % SLOTS.length];
				const size = boxFor(item.ratio, slot[2], slot[3]);
				const pic = picture(`${u}-p${S.app}${i}`, item.promptId, item.ratio, size[0], size[1]);
				return `<g transform="translate(${slot[0] + (slot[2] - size[0]) / 2} ${slot[1]})">${pic.svg}</g>`;
			}).join("");
		}

		function paintRows() {
			const app = APPS[S.app];
			const placed = S.placed[S.app];
			const rows = placed.map((item, i) => {
				const p = PROMPTS.filter((x) => x.id === item.promptId)[0];
				const pic = picture(`${u}-r${S.app}${i}`, item.promptId, item.ratio, 18, 18);
				const isNew = S.fresh === `${S.app}${i}`;

				return `<div class="lzi-row${isNew ? " lzi-new" : ""}">
					<span class="lzi-row__n">${placed.length - i}</span>
					<span class="lzi-row__chip"><svg viewBox="0 0 18 18" aria-hidden="true">${pic.svg}</svg></span>
					<span class="lzi-row__name">${esc(item.file)}</span>
					<span class="lzi-row__bar"></span>
				</div>`;
			}).reverse().join("");

			q(".lzi-rows").innerHTML = rows + `<div class="lzi-row lzi-row--footage">
				<span class="lzi-row__n">${placed.length + 1}</span>
				<span class="lzi-row__chip"></span>
				<span class="lzi-row__name">${app.baseRow}</span>
				<span class="lzi-row__bar"></span>
			</div>`;

			S.fresh = "";
			q(".lzi-tl-name").textContent = S.app === "ae" ? "Main Comp · timeline" : "Sequence 01 · V1–V2";
		}

		function paintPanel() {
			const app = APPS[S.app];

			qa("[data-app]").forEach((b) => b.setAttribute("aria-selected", String(b.getAttribute("data-app") === S.app)));
			qa("[data-prompt]").forEach((b) => b.setAttribute("aria-checked", String(b.getAttribute("data-prompt") === S.prompt)));
			qa("[data-ratio]").forEach((b) => b.setAttribute("aria-checked", String(b.getAttribute("data-ratio") === S.ratio)));

			q(".lzi-file").textContent = app.file;
			q(".lzi-view-name").textContent = app.view;
			q(".lzi-host").textContent = app.host;

			const badgeEl = q(".lzi-badge");
			badgeEl.textContent = S.logged ? "Logged in" : "Login required";
			badgeEl.classList.toggle("lzi-ok", S.logged);

			const login = q("[data-act='login']");
			login.textContent = S.logged ? "🌐 Logged in" : "🌐 Login to ChatGPT";
			login.setAttribute("aria-disabled", String(S.logged));

			const gen = q("[data-act='generate']");
			gen.textContent = S.gen ? "✕ Cancel" : "✨ Generate Image";
			gen.setAttribute("aria-disabled", String(!S.gen && (!S.logged || !S.prompt)));

			q(".lzi-meta").textContent = S.result ? `${S.result.ratio} · PNG` : "";
			paintPreview();
			paintStatus();
		}

		function paintPreview() {
			const box = q(".lzi-preview");

			if (S.gen) {
				box.innerHTML = `<span class="lzi-spin" aria-hidden="true"></span>`;
				return;
			}

			if (!S.result) {
				box.innerHTML = `📷<br>Generated image will appear here`;
				return;
			}

			const r = RATIOS.filter((x) => x[0] === S.result.ratio)[0];
			const w = 280;
			const h = Math.round((w * r[2]) / r[1]);
			const pic = picture(`${u}-prev`, S.result.promptId, S.result.ratio, w, h);

			box.innerHTML = `<svg viewBox="0 0 ${w} ${h}" width="${w}" height="${h}" aria-hidden="true">${pic.svg}</svg>`;
		}

		function paintPrompt() {
			const box = q(".lzi-prompt");
			const p = PROMPTS.filter((x) => x.id === S.prompt)[0];

			if (!p) {
				box.textContent = "";
				box.classList.add("lzi-empty");
				return;
			}

			const shown = S.typing ? S.typing.chars.slice(0, S.typing.at).join("") : p.text;
			box.classList.remove("lzi-empty");
			box.innerHTML = esc(shown) + (S.typing ? `<i></i>` : "");
		}

		function paintStatus() {
			const el = q(".lzi-status");
			el.textContent = S.status[0];
			el.className = `lzi-status${S.status[1] ? ` lzi-${S.status[1]}` : ""}`;
		}

		function paintHint() {
			const key = !S.did.login ? "login"
				: !S.prompt ? "prompt"
				: S.gen ? "wait"
				: !S.did.generate ? "generate"
				: !S.did.app ? "landed"
				: "done";
			q(".lzi-hint").textContent = L.hints[key];
		}

		function paintAll() {
			paintCanvas();
			paintRows();
			paintPrompt();
			paintPanel();
			paintHint();
		}

		function status(text, kind) {
			S.status = [text, kind || ""];
			paintStatus();
		}

		function toast(text) {
			const el = q(".lzi-toast");
			el.textContent = text;
			el.classList.add("lzi-on");
			clearTimeout(toastTimer);
			toastTimer = setTimeout(() => el.classList.remove("lzi-on"), 2000);
		}

		function nudge(text) {
			const h = q(".lzi-hint");
			h.classList.remove("lzi-nudging");
			void h.offsetWidth;
			h.classList.add("lzi-nudging");
			status(text, "");
		}

		/* ---- the clock: typing, generating ---- */
		function run() {
			if (!raf) {
				raf = requestAnimationFrame(tick);
			}
		}

		function tick(now) {
			raf = 0;

			if (S.typing) {
				const want = Math.floor((now - S.typing.t0) / 22);

				if (want !== S.typing.at) {
					S.typing.at = Math.min(want, S.typing.chars.length);
					paintPrompt();
				}

				if (S.typing.at >= S.typing.chars.length) {
					S.typing = null;
					paintPrompt();
					paintPanel();
				}
			}

			if (S.gen) {
				const at = (now - S.gen.t0) / 1000;
				const steps = [
					[0.0, "Opening your browser…"],
					[0.7, "Sending the prompt to ChatGPT…"],
					[1.5, "Generating…"],
					[2.4, "Downloading the PNG…"],
				];
				let label = steps[0][1];

				steps.forEach(([at0, text]) => {
					if (at >= at0) {
						label = text;
					}
				});

				if (label !== S.gen.step) {
					S.gen.step = label;
					status(label, "");
				}

				q(".lzi-meter i").style.width = `${Math.min(100, (at / 3) * 100).toFixed(0)}%`;
				q(".lzi-browser").classList.toggle("lzi-on", at < 1.2);

				if (at >= 3) {
					finishGenerate();
				}
			}

			if (S.typing || S.gen) {
				run();
			}
		}

		/* ---- actions ---- */
		function login() {
			if (S.logged) {
				return;
			}

			S.logged = true;
			S.did.login = true;
			q(".lzi-browser").classList.add("lzi-on");
			status("Signing in with your own browser…", "");
			paintPanel();
			paintHint();

			setTimeout(() => {
				if (!stage) {
					return;
				}
				q(".lzi-browser").classList.remove("lzi-on");
				status("Logged in — no API key, it drives your own Chrome.", "ok");
				toast("✓ Logged in");
				paintPanel();
			}, reduced() ? 60 : 1300);
		}

		function pickPrompt(id) {
			const p = PROMPTS.filter((x) => x.id === id)[0];

			S.prompt = id;
			S.ratio = p.ratio;
			S.typing = reduced() ? null : { chars: graphemes(p.text), at: 0, t0: performance.now() };
			paintPrompt();
			paintPanel();
			paintHint();
			run();
		}

		function generate() {
			if (S.gen) {
				S.gen = null;
				q(".lzi-browser").classList.remove("lzi-on");
				q(".lzi-meter i").style.width = "0%";
				status("Cancelled", "");
				paintPanel();
				return;
			}

			if (!S.logged) {
				nudge(L.need.login);
				return;
			}

			if (!S.prompt) {
				nudge(L.need.prompt);
				return;
			}

			S.gen = { t0: performance.now(), step: "" };
			S.result = null;
			S.did.generate = true;
			paintPanel();
			paintHint();

			if (reduced()) {
				finishGenerate();
				return;
			}

			run();
		}

		function finishGenerate() {
			const p = PROMPTS.filter((x) => x.id === S.prompt)[0];

			S.gen = null;
			S.result = { promptId: S.prompt, ratio: S.ratio, file: p.file };
			q(".lzi-meter i").style.width = "100%";
			q(".lzi-browser").classList.remove("lzi-on");
			status(`Saved ${p.file}`, "ok");
			paintPanel();
			land();
		}

		/* The picture flies from the panel's preview into the composition,
		   which is what auto-import does on its own. */
		function land() {
			const item = { promptId: S.result.promptId, ratio: S.result.ratio, file: S.result.file };
			const done = () => {
				S.placed[S.app] = S.placed[S.app].concat([item]);
				S.fresh = `${S.app}${S.placed[S.app].length - 1}`;
				S.did.land = true;
				paintCanvas();
				paintRows();
				paintHint();
				status(`Imported to the ${S.app === "ae" ? "composition" : "sequence"} at the playhead`, "ok");
				toast("🔗 Imported to the timeline");
			};

			const from = q(".lzi-preview svg");
			const svg = q(".lzi-canvas svg");

			if (reduced() || !from || !svg) {
				done();
				return;
			}

			const stageBox = stage.getBoundingClientRect();
			const a = from.getBoundingClientRect();
			const canvas = svg.getBoundingClientRect();
			const slot = SLOTS[S.placed[S.app].length % SLOTS.length];
			const scale = canvas.width / VB[0];
			const size = boxFor(item.ratio, slot[2], slot[3]);
			const bw = size[0] * scale;
			const bh = size[1] * scale;

			const fly = document.createElement("div");
			const pic = picture(`${u}-f${++flyId}`, item.promptId, item.ratio, Math.round(a.width / k), Math.round(a.height / k));

			fly.className = "lzi-fly";
			fly.innerHTML = `<svg viewBox="0 0 ${Math.round(a.width / k)} ${Math.round(a.height / k)}" aria-hidden="true">${pic.svg}</svg>`;
			stage.appendChild(fly);

			const box = (rect) => ({
				x: (rect.left - stageBox.left) / k,
				y: (rect.top - stageBox.top) / k,
				w: rect.width / k,
				h: rect.height / k,
			});
			const src = box(a);
			const dst = {
				x: (canvas.left - stageBox.left) / k + ((slot[0] + (slot[2] - size[0]) / 2) * scale) / k,
				y: (canvas.top - stageBox.top) / k + (slot[1] * scale) / k,
				w: bw / k,
				h: bh / k,
			};
			const t0 = performance.now();

			const step = (now) => {
				const e = easeInOut(clamp((now - t0) / 700, 0, 1));

				fly.style.left = `${lerp(src.x, dst.x, e).toFixed(1)}px`;
				fly.style.top = `${lerp(src.y, dst.y, e).toFixed(1)}px`;
				fly.style.width = `${lerp(src.w, dst.w, e).toFixed(1)}px`;
				fly.style.height = `${lerp(src.h, dst.h, e).toFixed(1)}px`;

				if (e < 1) {
					requestAnimationFrame(step);
				} else {
					fly.remove();
					done();
				}
			};

			requestAnimationFrame(step);
		}

		function setApp(app) {
			if (S.app === app) {
				return;
			}

			S.app = app;
			S.did.app = true;
			paintAll();
		}

		function reset() {
			S = start();
			q(".lzi-meter i").style.width = "0%";
			q(".lzi-browser").classList.remove("lzi-on");
			paintAll();
		}

		/* ---- input ---- */
		root.addEventListener("click", (e) => {
			if (!stage || !(e.target instanceof Element)) {
				return;
			}

			const t = e.target;
			let el;

			if ((el = t.closest("[data-app]"))) {
				setApp(el.getAttribute("data-app"));
			} else if ((el = t.closest("[data-prompt]"))) {
				pickPrompt(el.getAttribute("data-prompt"));
			} else if ((el = t.closest("[data-ratio]"))) {
				S.ratio = el.getAttribute("data-ratio");
				paintPanel();
			} else if ((el = t.closest("[data-act]"))) {
				const act = el.getAttribute("data-act");

				if (act === "login") {
					login();
				} else if (act === "generate") {
					generate();
				} else if (act === "copy") {
					toast(S.result ? "📋 Copied to the clipboard" : "Nothing to copy yet");
				} else if (act === "folder") {
					toast(S.result ? "📂 Opened the download folder" : "Nothing saved yet");
				} else if (act === "reset") {
					reset();
				}
			}
		});

		root.addEventListener("keydown", (e) => {
			const dir = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
			const el = dir && e.target instanceof Element ? e.target.closest('[role="radio"], [role="tab"]') : null;

			if (!el) {
				return;
			}

			e.preventDefault();
			const group = Array.from(el.parentElement.querySelectorAll(`[role="${el.getAttribute("role")}"]`));
			const next = group[(group.indexOf(el) + dir + group.length) % group.length];
			next.click();
			next.focus();
		});

		build();

		if ("ResizeObserver" in window) {
			new ResizeObserver(() => build()).observe(root.parentElement || root);
		} else {
			window.addEventListener("resize", build);
		}
	}

	/* ------------------------------------------------------------------ start when near */
	function auto() {
		const els = Array.from(document.querySelectorAll("[data-lazyimage-demo]"));

		if (!els.length) {
			return;
		}

		if (!("IntersectionObserver" in window)) {
			els.forEach(mount);
			return;
		}

		const io = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					io.unobserve(entry.target);
					mount(entry.target);
				}
			});
		}, { rootMargin: "400px 0px" });

		els.forEach((el) => io.observe(el));
	}

	window.LazyImageDemo = { mount };

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", auto);
	} else {
		auto();
	}
})();
