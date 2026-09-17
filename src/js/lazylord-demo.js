/*
 * LazyLord interactive demo.
 *
 * Built on the drawing code of the LazyLord demo animation: the hero card's five layers, the app
 * badges, the photo and the sparkle, the logo, the flight curve and its easing. The windows around
 * them are real buttons instead of pictures of buttons, so every control can be clicked, tapped and
 * reached from the keyboard at a readable size. Two stages, like the animation's LAY: a wide one
 * (1280 x 760) and a tall one for phones and narrow columns (440 x 1084), scaled to fit.
 *
 * It is a simulation. Nothing leaves the page.
 *
 * In the theme: a standalone page bundle (assets/lazylord-demo.min.js), enqueued only on a
 * project page that has an interactive demo (rs_project_has_demo()); page-portfolio.php prints
 * <div class="lld-wrap"><div class="lld" data-lazylord-demo data-lang="bn|en" data-label="…">.
 * It starts when it scrolls near, and its box is sized by CSS alone, so a cached page never
 * shifts.
 */
(() => {
	"use strict";

	/* Brand geometry from the LazyLord logo system (symbol, stem, foot, bridge, word). */
	const BRAND = { symbol: "M3.6 3 H5.4 Q6 3 6 3.6 V18 C6.8 19.25 7.6 19.25 8.4 18 H17.4 Q18 18 18 18.6 V20.4 Q18 21 17.4 21 H8.4 C7.6 19.75 6.8 19.75 6 21 H3.6 Q3 21 3 20.4 V3.6 Q3 3 3.6 3 Z", stem: "M3.6 3 H5.4 Q6 3 6 3.6 V21 H3.6 Q3 21 3 20.4 V3.6 Q3 3 3.6 3 Z", foot: "M8.4 18 H17.4 Q18 18 18 18.6 V20.4 Q18 21 17.4 21 H8.4 Z", bridge: "M5.9 18 C6.8 19.25 7.6 19.25 8.5 18 V21 C7.6 19.75 6.8 19.75 5.9 21 Z", word: "M24.070 21.272L24.070 21.272Q22.787 21.272 21.763 20.811Q20.739 20.350 20.146 19.444Q19.553 18.539 19.553 17.212L19.553 17.212Q19.553 16.069 19.975 15.322Q20.397 14.575 21.126 14.127Q21.856 13.679 22.774 13.446Q23.693 13.213 24.677 13.107L24.677 13.107Q25.863 12.984 26.602 12.883Q27.340 12.782 27.678 12.567Q28.017 12.352 28.017 11.895L28.017 11.895L28.017 11.842Q28.017 10.849 27.428 10.304Q26.839 9.759 25.732 9.759L25.732 9.759Q24.563 9.759 23.881 10.269Q23.200 10.778 22.963 11.473L22.963 11.473L19.992 11.051Q20.344 9.820 21.152 8.990Q21.961 8.159 23.130 7.742Q24.299 7.324 25.714 7.324L25.714 7.324Q26.690 7.324 27.656 7.553Q28.623 7.781 29.423 8.304Q30.223 8.827 30.711 9.724Q31.198 10.620 31.198 11.965L31.198 11.965L31.198 21L28.140 21L28.140 19.146L28.034 19.146Q27.744 19.708 27.221 20.196Q26.698 20.684 25.912 20.978Q25.125 21.272 24.070 21.272ZM24.897 18.935L24.897 18.935Q25.855 18.935 26.558 18.552Q27.261 18.170 27.643 17.537Q28.026 16.904 28.026 16.157L28.026 16.157L28.026 14.566Q27.876 14.689 27.520 14.795Q27.164 14.900 26.725 14.979Q26.285 15.059 25.855 15.120Q25.424 15.182 25.108 15.226L25.108 15.226Q24.396 15.322 23.833 15.542Q23.271 15.762 22.945 16.153Q22.620 16.544 22.620 17.159L22.620 17.159Q22.620 18.038 23.262 18.486Q23.903 18.935 24.897 18.935ZM44.819 21L33.736 21L33.736 18.979L40.697 10.251L40.697 10.137L33.964 10.137L33.964 7.500L44.590 7.500L44.590 9.671L37.963 18.249L37.963 18.363L44.819 18.363L44.819 21ZM49.298 26.063L49.298 26.063Q48.648 26.063 48.098 25.961Q47.549 25.860 47.224 25.729L47.224 25.729L47.962 23.250Q48.657 23.452 49.202 23.443Q49.746 23.435 50.164 23.105Q50.581 22.775 50.871 22.011L50.871 22.011L51.144 21.281L46.248 7.500L49.623 7.500L52.735 17.695L52.875 17.695L55.995 7.500L59.379 7.500L53.974 22.635Q53.596 23.707 52.972 24.476Q52.348 25.245 51.447 25.654Q50.546 26.063 49.298 26.063ZM72.411 21L61.222 21L61.222 3L64.483 3L64.483 18.267L72.411 18.267L72.411 21ZM80.485 21.264L80.485 21.264Q78.507 21.264 77.057 20.394Q75.607 19.523 74.812 17.959Q74.016 16.395 74.016 14.303L74.016 14.303Q74.016 12.211 74.812 10.638Q75.607 9.064 77.057 8.194Q78.507 7.324 80.485 7.324L80.485 7.324Q82.463 7.324 83.913 8.194Q85.363 9.064 86.158 10.638Q86.954 12.211 86.954 14.303L86.954 14.303Q86.954 16.395 86.158 17.959Q85.363 19.523 83.913 20.394Q82.463 21.264 80.485 21.264ZM80.503 18.715L80.503 18.715Q81.575 18.715 82.296 18.122Q83.016 17.528 83.372 16.526Q83.728 15.524 83.728 14.294L83.728 14.294Q83.728 13.055 83.372 12.048Q83.016 11.042 82.296 10.444Q81.575 9.847 80.503 9.847L80.503 9.847Q79.404 9.847 78.679 10.444Q77.954 11.042 77.598 12.048Q77.242 13.055 77.242 14.294L77.242 14.294Q77.242 15.524 77.598 16.526Q77.954 17.528 78.679 18.122Q79.404 18.715 80.503 18.715ZM92.330 21L89.148 21L89.148 7.500L92.233 7.500L92.233 9.750L92.374 9.750Q92.743 8.581 93.644 7.944Q94.545 7.307 95.705 7.307L95.705 7.307Q95.968 7.307 96.298 7.329Q96.628 7.351 96.847 7.395L96.847 7.395L96.847 10.321Q96.645 10.251 96.210 10.194Q95.775 10.137 95.371 10.137L95.371 10.137Q94.501 10.137 93.811 10.510Q93.121 10.884 92.725 11.543Q92.330 12.202 92.330 13.063L92.330 13.063L92.330 21ZM103.480 21.237L103.480 21.237Q101.889 21.237 100.633 20.420Q99.376 19.603 98.646 18.047Q97.917 16.491 97.917 14.268L97.917 14.268Q97.917 12.018 98.660 10.466Q99.402 8.915 100.668 8.120Q101.933 7.324 103.489 7.324L103.489 7.324Q104.676 7.324 105.440 7.724Q106.205 8.124 106.653 8.682Q107.101 9.240 107.347 9.732L107.347 9.732L107.479 9.732L107.479 3L110.670 3L110.670 21L107.541 21L107.541 18.873L107.347 18.873Q107.101 19.365 106.636 19.915Q106.170 20.464 105.405 20.851Q104.640 21.237 103.480 21.237ZM104.368 18.627L104.368 18.627Q105.379 18.627 106.091 18.078Q106.803 17.528 107.172 16.544Q107.541 15.560 107.541 14.250L107.541 14.250Q107.541 12.940 107.176 11.974Q106.811 11.007 106.104 10.471Q105.396 9.935 104.368 9.935L104.368 9.935Q103.305 9.935 102.593 10.488Q101.881 11.042 101.520 12.018Q101.160 12.993 101.160 14.250L101.160 14.250Q101.160 15.516 101.525 16.504Q101.889 17.493 102.606 18.060Q103.322 18.627 104.368 18.627Z" };

	const STR = {
		en: {
			label: "Interactive demo",
			note: "nothing is really sent",
			reset: "Reset",
			region: "LazyLord interactive demo",
			hints: {
				pick: "Click a layer in Figma, or Hero card to take all five.",
				send: "Choose where it goes in the plugin, then press Send.",
				edit: "Now change a colour or the title in Figma and send again: the same layer updates, no copy.",
				live: "Turn on Live, then change something: it reaches the apps as you make it.",
				back: "It works the other way too: press Send to Figma in Photoshop or Illustrator.",
				place: "It waits in Figma until you press Place on canvas.",
				done: "That is LazyLord. Press Reset to play again.",
			},
		},
		bn: {
			label: "ইন্টারঅ্যাক্টিভ ডেমো",
			note: "আসলে কিছু পাঠানো হয় না",
			reset: "রিসেট",
			region: "LazyLord ইন্টারঅ্যাক্টিভ ডেমো",
			hints: {
				pick: "Figma-য় একটা লেয়ারে ক্লিক করুন, পাঁচটাই নিতে Hero card।",
				send: "প্লাগিনে গন্তব্য বেছে নিয়ে Send চাপুন।",
				edit: "এবার Figma-য় রঙ বা শিরোনাম বদলে আবার পাঠান: নতুন কপি হবে না, আগের লেয়ারটাই বদলাবে।",
				live: "Live চালু করে কিছু বদলান: বদলানোর সাথে সাথেই অ্যাপে পৌঁছে যাবে।",
				back: "উল্টো দিকেও চলে: Photoshop বা Illustrator-এ Send to Figma চাপুন।",
				place: "Place on canvas না চাপা পর্যন্ত ওটা Figma-য় অপেক্ষা করবে।",
				done: "এই হলো LazyLord। আবার খেলতে রিসেট চাপুন।",
			},
		},
	};

	/* ------------------------------------------------------------------ timing, from the animation */
	const lerp = (a, b, u) => a + (b - a) * u;
	const E = {
		inOut: (u) => (u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2),
	};

	/* ------------------------------------------------------------------ the design that travels */
	/* Bottom to top. `ae` is the After Effects label colour; `opts` are what Figma can change. */
	const LAYERS = [
		{ name: "Background", icon: "rect", ae: "#9a93b5", kind: "fill", opts: [["#2d2656", "#1b1730"], ["#173f5f", "#0b2236"], ["#43213f", "#211021"], ["#1d4433", "#0d2419"]] },
		{ name: "Sun", icon: "ellipse", ae: "#ff7a59", kind: "fill", opts: [["#ff7a59"], ["#ffc857"], ["#ff5fa2"], ["#6fe3c1"]] },
		{ name: "Mountains", icon: "vector", ae: "#8f89ff", kind: "fill", opts: [["#8f89ff", "#5b52f0"], ["#4fb3ff", "#2a6fd6"], ["#57d49a", "#1f8f63"], ["#ffa36b", "#e0564f"]] },
		{ name: "Title", icon: "text", ae: "#4fb3ff", kind: "text", opts: ["PEAK", "SUMMIT", "RIDGE"] },
		{ name: "Button", icon: "rect", ae: "#e8b04c", kind: "text", opts: ["Explore", "Start", "Book"] },
	];
	const TOP_DOWN = [4, 3, 2, 1, 0];
	const ALL = [0, 1, 2, 3, 4];

	function layerMarkup(i, p, v) {
		const o = LAYERS[i].opts[v[i]];
		switch (i) {
			case 0: return `<rect width="520" height="340" rx="24" fill="url(#${p}-sky)"/>`;
			case 1: return `<circle cx="392" cy="104" r="42" fill="${o[0]}"/>`;
			case 2: return `<path d="M0 290 L130 190 L220 250 L340 90 L520 270 L520 316 Q520 340 496 340 L24 340 Q0 340 0 316 Z" fill="url(#${p}-mtn)"/>` +
				`<path d="M340 90 L378 144 L356 136 L340 156 L324 134 L302 146 Z" fill="#fff" fill-opacity=".92"/>`;
			case 3: return `<text x="40" y="84" fill="#fff" font-size="46" font-weight="800" letter-spacing="8">${o}</text>` +
				`<text x="42" y="112" fill="#fff" fill-opacity=".72" font-size="15" font-weight="500">Trails above the clouds</text>`;
			case 4: return `<rect x="40" y="134" width="132" height="42" rx="21" fill="#fff"/>` +
				`<text x="106" y="161" text-anchor="middle" fill="#5b52f0" font-size="16" font-weight="700">${o}</text>`;
		}
		return "";
	}

	function designDefs(p, v) {
		const sky = LAYERS[0].opts[v[0]], mtn = LAYERS[2].opts[v[2]];
		return `<defs><linearGradient id="${p}-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${sky[0]}"/><stop offset="1" stop-color="${sky[1]}"/></linearGradient>` +
			`<linearGradient id="${p}-mtn" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${mtn[0]}"/><stop offset="1" stop-color="${mtn[1]}"/></linearGradient></defs>`;
	}

	/* ------------------------------------------------------------------ small pieces, from the animation */
	function badge(kind, x, y, s) {
		const spec = { F: { bg: "#1e1e1e" }, Ai: { bg: "#330000", fg: "#ff9a00" }, Ps: { bg: "#001e36", fg: "#31a8ff" }, Ae: { bg: "#00005b", fg: "#9999ff" } }[kind];
		let out = `<rect x="${x}" y="${y}" width="${s}" height="${s}" rx="${s * 0.22}" fill="${spec.bg}"/>`;
		if (kind === "F") {
			out += `<text x="${x + s / 2}" y="${y + s * 0.6}" text-anchor="middle" fill="#fff" font-size="${s * 0.5}" font-weight="700">F</text>`;
			["#f24e1e", "#ff7262", "#a259ff", "#1abcfe", "#0acf83"].forEach((c, i) => {
				out += `<rect x="${x + s * 0.2 + i * s * 0.12}" y="${y + s * 0.74}" width="${s * 0.1}" height="${s * 0.07}" rx="${s * 0.03}" fill="${c}"/>`;
			});
		} else {
			out += `<rect x="${x + s * 0.08}" y="${y + s * 0.08}" width="${s * 0.84}" height="${s * 0.84}" rx="${s * 0.15}" fill="none" stroke="${spec.fg}" stroke-width="${s * 0.05}"/>` +
				`<text x="${x + s / 2}" y="${y + s * 0.66}" text-anchor="middle" fill="${spec.fg}" font-size="${s * 0.44}" font-weight="700">${kind}</text>`;
		}
		return out;
	}

	function icon(type, x, y, c) {
		switch (type) {
			case "frame": return `<text x="${x}" y="${y + 4.5}" text-anchor="middle" fill="${c}" font-size="13" font-weight="600">#</text>`;
			case "text": return `<text x="${x}" y="${y + 4.5}" text-anchor="middle" fill="${c}" font-size="13" font-weight="700">T</text>`;
			case "rect": return `<rect x="${x - 5}" y="${y - 5}" width="10" height="10" rx="2" fill="none" stroke="${c}" stroke-width="1.4"/>`;
			case "ellipse": return `<circle cx="${x}" cy="${y}" r="5.2" fill="none" stroke="${c}" stroke-width="1.4"/>`;
			case "vector": return `<path d="M${x - 6} ${y + 5} C${x - 3} ${y - 8} ${x + 3} ${y + 8} ${x + 6} ${y - 5}" fill="none" stroke="${c}" stroke-width="1.4" stroke-linecap="round"/>`;
			case "group": return `<path d="M${x - 6} ${y - 4} h4 l2 2 h6 v7 h-12 z" fill="none" stroke="${c}" stroke-width="1.3" stroke-linejoin="round"/>`;
			case "image": return `<rect x="${x - 6}" y="${y - 5}" width="12" height="10" rx="1.5" fill="none" stroke="${c}" stroke-width="1.3"/><path d="M${x - 5} ${y + 4} l3 -3 l2 2 l2 -2 l3 3" fill="none" stroke="${c}" stroke-width="1.1"/>`;
		}
		return "";
	}

	const ico = (type, c) => `<svg viewBox="-8 -8 16 16" aria-hidden="true">${icon(type, 0, 0, c)}</svg>`;

	const brandGradient = (id) =>
		`<linearGradient id="${id}" x1="3" y1="3" x2="18" y2="21" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#00c2ff"/><stop offset="1" stop-color="#6c63ff"/></linearGradient>`;

	/* A small LazyLord lockup: gradient symbol, word in `wordFill`, capitals `h` px tall, ink starting at (x, y). */
	function lockup(id, x, y, h, wordFill) {
		const k = h / 18;
		return `<g transform="translate(${x - 3 * k} ${y - 3 * k}) scale(${k})"><path d="${BRAND.symbol}" fill="url(#${id})"/><path d="${BRAND.word}" fill="${wordFill}"/></g>`;
	}

	const symbol = (id) => `<svg viewBox="1 2 20 20" aria-hidden="true"><defs>${brandGradient(id)}</defs><path d="${BRAND.symbol}" fill="url(#${id})"/></svg>`;

	/* What travels the other way: a photo from Photoshop, a sparkle drawn in Illustrator. */
	function photo(p) {
		return `<defs><linearGradient id="${p}-ph" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffb199"/><stop offset=".55" stop-color="#ff7a59"/><stop offset="1" stop-color="#6c3fa0"/></linearGradient>` +
			`<clipPath id="${p}-phc"><rect x="-60" y="-40" width="120" height="80" rx="8"/></clipPath></defs>` +
			`<g clip-path="url(#${p}-phc)"><rect x="-60" y="-40" width="120" height="80" fill="url(#${p}-ph)"/>` +
			`<circle cx="18" cy="-4" r="14" fill="#ffe3b0" opacity=".9"/><path d="M-60 40 L-60 18 L-30 0 L-8 14 L20 -6 L60 22 L60 40 Z" fill="#2b1f45"/></g>` +
			`<rect x="-60" y="-40" width="120" height="80" rx="8" fill="none" stroke="#fff" stroke-opacity=".3"/>`;
	}
	function sparkle(p) {
		return `<defs><linearGradient id="${p}-sg" x1="0" y1="-26" x2="0" y2="26" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#00c2ff"/></linearGradient></defs>` +
			`<path d="M0 -26 C4 -6 6 -4 26 0 C6 4 4 6 0 26 C-4 6 -6 4 -26 0 C-6 -4 -4 -6 0 -26 Z" fill="url(#${p}-sg)"/>`;
	}
	const item = (kind, p) => (kind === "photo" ? photo(p) : sparkle(p));

	/* ------------------------------------------------------------------ the apps */
	const APPS = ["ps", "ai", "ae"];
	const APP = {
		ps: { badge: "Ps", name: "Photoshop", doc: "Hero card.psd", vb: [700, 380], card: [20, 20], own: { kind: "photo", name: "Photo", at: [625, 190, 1] } },
		ai: { badge: "Ai", name: "Illustrator", doc: "Hero card.ai", vb: [700, 380], card: [20, 20], own: { kind: "sparkle", name: "Sparkle", at: [620, 190, 1.3] } },
		ae: { badge: "Ae", name: "After Effects", doc: "Hero card.aep", vb: [540, 360], card: [10, 10] },
	};
	const DESTS = [["all", "All apps"], ["ps", "Photoshop"], ["ai", "Illustrator"], ["ae", "After Effects"]];

	/* Figma's canvas: the card, and where things from the other apps are placed. */
	const FIG_CARD = [10, 0];
	const PLACE_AT = { photo: [100, 440, 1.2], sparkle: [262, 440, 1.4] };

	const EYE = `<svg class="lld-eye" viewBox="-6 -6 12 12" aria-hidden="true"><path d="M-5 0 q5 -5.5 10 0 q-5 5.5 -10 0z" fill="none" stroke="#b5b5b5" stroke-width="1.1"/></svg>`;
	const RESET_ICON = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>`;
	const NS = "http://www.w3.org/2000/svg";

	const plural = (n, w) => `${n} ${w}${n === 1 ? "" : "s"}`;
	const listNames = (names) => (names.length < 3 ? names.join(" and ") : `${names.slice(0, -1).join(", ")} and ${names[names.length - 1]}`);

	const reduceMQ = window.matchMedia ? window.matchMedia("(prefers-reduced-motion: reduce)") : null;
	const reduced = () => Boolean(reduceMQ && reduceMQ.matches);

	function fresh() {
		return {
			sel: new Set(),
			dest: "all",
			live: false,
			v: [0, 0, 0, 0, 0],               // Figma: chosen option per layer
			ver: [0, 0, 0, 0, 0],             // Figma: edits per layer
			apps: { ps: {}, ai: {}, ae: {} }, // each app's copies: layer -> { v, ver }
			flags: { ps: {}, ai: {}, ae: {} },// one-shot: layer -> "new" | "updated"
			unseen: { ps: 0, ai: 0, ae: 0 },  // tall stage: arrivals on a hidden tab
			placed: [],                       // things placed on Figma's canvas
			queue: [],                        // waiting in Figma: { kind, from }
			sentBack: { ps: false, ai: false },
			did: { send: false, update: false, live: false, back: false, place: false },
			status: ["", ""],
			tab: "ps",
			justPlaced: "",
		};
	}

	/* ------------------------------------------------------------------ markup */
	function markup(u, L, tall) {
		const W = tall ? 440 : 1280, H = tall ? 1084 : 760;
		const appWin = (a) => {
			const m = APP[a];
			return `<section class="lld-win lld-app lld-app--${a}" data-app="${a}" id="${u}-${a}" aria-label="${m.name}"${tall ? ` role="tabpanel" aria-labelledby="${u}-tab-${a}"` : ""}>
	<div class="lld-bar lld-bar--app"><svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true">${badge(m.badge, 0, 0, 20)}</svg><span class="lld-app-name">${m.name}</span><span class="lld-doc">${m.doc}</span><span class="lld-toast" aria-hidden="true"></span>${m.own ? `<button type="button" class="lld-back" data-back="${a}">${symbol(`${u}-b${a}`)}<span>Send to Figma</span></button>` : ""}</div>
	<div class="lld-appbody">
		<div class="lld-appcanvas"><svg viewBox="0 0 ${m.vb[0]} ${m.vb[1]}" aria-hidden="true"></svg></div>
		<div class="lld-applayers${a === "ae" ? " lld-tl" : ""}"><h5>${a === "ae" ? "Hero card <span>0:00:05:00</span>" : "Layers"}</h5><ul class="lld-list"></ul></div>
	</div>
</section>`;
		};

		return `<div class="lld-stage ${tall ? "lld-tall" : "lld-wide"}">
<div class="lld-head">
	<span class="lld-label">${symbol(`${u}-hs`)}${L.label}<small>· ${L.note}</small></span>
	<p class="lld-hint"></p>
	<button type="button" class="lld-reset" data-act="reset">${RESET_ICON}${L.reset}</button>
</div>

<section class="lld-win lld-figma" aria-label="Figma">
	<div class="lld-bar lld-bar--figma">
		<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">${badge("F", 0, 0, 24)}</svg>
		<svg class="lld-tools" viewBox="40 6 150 32" width="150" height="32" aria-hidden="true"><rect x="46" y="8" width="28" height="28" rx="5" fill="#0d99ff"/><path d="M56 15 L66 22 L61 23 L59 28 Z" fill="#fff"/><text x="95" y="27" fill="#e6e6e6" font-size="14" font-weight="600" text-anchor="middle">#</text><rect x="115" y="16" width="12" height="12" rx="1.5" fill="none" stroke="#e6e6e6" stroke-width="1.4"/><path d="M143 28 C146 14 152 30 156 16" fill="none" stroke="#e6e6e6" stroke-width="1.4" stroke-linecap="round"/><text x="178" y="28" fill="#e6e6e6" font-size="14" font-weight="700" text-anchor="middle">T</text></svg>
		<span class="lld-file">Hero — Landing page</span>
		<svg class="lld-avatar" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#ff7262"/><text x="12" y="16" text-anchor="middle" fill="#fff" font-size="11" font-weight="700">R</text></svg>
		<span class="lld-share" aria-hidden="true">Share</span>
	</div>
	<div class="lld-figbody">
		<div class="lld-layers" tabindex="-1" role="group" aria-label="Layers">
			<div class="lld-tabs">Layers <span>Assets</span></div>
			<div class="lld-placed"></div>
			<button type="button" class="lld-row lld-row--frame" data-pick="all" aria-pressed="false">${ico("frame", "#555")}Hero card</button>
			${TOP_DOWN.map((i) => `<button type="button" class="lld-row lld-row--layer" data-pick="${i}" aria-pressed="false">${ico(LAYERS[i].icon, "#555")}${LAYERS[i].name}</button>`).join("")}
		</div>
		<div class="lld-canvas">
			<span class="lld-canvas-label">Hero card</span>
			<svg class="lld-design--fig" viewBox="0 0 540 520" aria-hidden="true"></svg>
			<div class="lld-incoming">
				<svg class="lld-inthumb" viewBox="-70 -50 140 100" aria-hidden="true"></svg>
				<div class="lld-intext"><small>Incoming</small><span></span></div>
				<button type="button" class="lld-place" data-act="place">Place on canvas</button>
			</div>
		</div>
		<div class="lld-props" role="group" aria-label="Design"></div>
	</div>
</section>

<section class="lld-win lld-plugin" aria-label="LazyLord plugin">
	<div class="lld-bar lld-bar--plugin">LazyLord<span class="lld-x" aria-hidden="true">×</span></div>
	<div class="lld-plugbody">
		<div class="lld-conn"><svg viewBox="0 0 92 20" width="80" height="17" aria-hidden="true"><defs>${brandGradient(`${u}-pl`)}</defs>${lockup(`${u}-pl`, 2, 3, 14, "#17171d")}</svg><i></i>Connected</div>
		<div class="lld-to">
			<span class="lld-cap" id="${u}-to">SEND TO</span>
			<div class="lld-chips" role="radiogroup" aria-labelledby="${u}-to">${DESTS.map(([d, label]) => `<button type="button" class="lld-chip" role="radio" data-dest="${d}" aria-checked="false">${label}</button>`).join("")}</div>
		</div>
		<div class="lld-live"><span><b id="${u}-live">Live</b><small>Send changes as you make them</small></span><button type="button" class="lld-switch" role="switch" data-act="live" aria-checked="false" aria-labelledby="${u}-live"></button></div>
		<button type="button" class="lld-send" data-act="send">Send to all apps</button>
		<p class="lld-status" role="status"></p>
	</div>
</section>

<div class="lld-apps">
	${tall ? `<div class="lld-apptabs" role="tablist" aria-label="Adobe apps">${APPS.map((a) => `<button type="button" class="lld-apptab" role="tab" id="${u}-tab-${a}" data-tab="${a}" aria-controls="${u}-${a}" aria-selected="false"><svg viewBox="0 0 20 20" aria-hidden="true">${badge(APP[a].badge, 0, 0, 20)}</svg>${APP[a].name}</button>`).join("")}</div>` : ""}
	${APPS.map(appWin).join("")}
</div>

<svg class="lld-fx" viewBox="0 0 ${W} ${H}" aria-hidden="true"></svg>
</div>`;
	}

	/* ------------------------------------------------------------------ one demo */
	let seq = 0;

	function mount(root) {
		if (root.lldMounted) {
			return;
		}
		root.lldMounted = true;

		const u = `lld${++seq}`;
		const lang = (root.getAttribute("data-lang") || document.documentElement.lang || "en").toLowerCase();
		const L = STR[lang.indexOf("bn") === 0 ? "bn" : "en"];

		let S = fresh();
		let tall = null, stage = null, W = 0, k = 1;
		let busy = 0, placing = false, propsKey = null, flightSeq = 0, raf = 0;
		const flights = new Set();
		const toastTimers = {};

		const q = (sel) => stage.querySelector(sel);
		const qa = (sel) => Array.from(stage.querySelectorAll(sel));
		const figSvg = () => q(".lld-design--fig");
		const appSvg = (a) => q(`[data-app="${a}"] .lld-appcanvas svg`);

		root.setAttribute("role", "region");
		root.setAttribute("aria-label", L.region);

		/* ---- stage: pick the wide or the tall one, and scale it to the box ---- */
		function build() {
			const nextTall = (root.parentElement || root).clientWidth < 980;

			if (stage && nextTall === tall) {
				fit();
				return;
			}

			finishFlights();
			tall = nextTall;
			W = tall ? 440 : 1280;
			root.innerHTML = markup(u, L, tall);
			stage = root.firstElementChild;
			propsKey = null;
			root.classList.add("lld-ready");
			fit();
			paintAll();
		}

		function fit() {
			k = root.clientWidth / W || 1;
			stage.style.transform = `scale(${k})`;
		}

		/** Where point (ox, oy) of an SVG's viewBox is, in stage units, and how big one unit is. */
		function frameIn(el, ox, oy) {
			const r = el.getBoundingClientRect(), s = stage.getBoundingClientRect(), vb = el.viewBox.baseVal;
			const px = r.width / vb.width;
			return { x: (r.left - s.left + (ox - vb.x) * px) / k, y: (r.top - s.top + (oy - vb.y) * px) / k, s: px / k };
		}

		/** Where a layer lands in an app: its card, or on a phone, the app's tab when another is showing. */
		function appFrame(a) {
			if (tall && S.tab !== a) {
				const r = q(`[data-tab="${a}"]`).getBoundingClientRect(), s = stage.getBoundingClientRect();
				return { x: (r.left - s.left + r.width / 2) / k, y: (r.top - s.top + r.height / 2) / k, s: 0.04, hidden: true };
			}

			const m = APP[a];
			return frameIn(appSvg(a), m.card[0], m.card[1]);
		}

		/* ---- flights: the animation's quadratic arc, glow and easing, started on demand ---- */
		function fx(inner) {
			const g = document.createElementNS(NS, "g");
			g.innerHTML = inner;
			g.setAttribute("opacity", "0");
			q(".lld-fx").appendChild(g);
			return g;
		}

		function launch(el, src, dst, delay, dur, arc, done) {
			flights.add({ el, src, dst, arc, done, t0: performance.now() + delay * 1000, dur: dur * 1000 });

			if (!raf) {
				raf = requestAnimationFrame(step);
			}
		}

		function step(now) {
			raf = 0;

			flights.forEach((f) => {
				const t = (now - f.t0) / f.dur;

				if (t < 0) {
					return;
				}

				if (t >= 1) {
					flights.delete(f);
					f.el.remove();
					if (f.done) {
						f.done();
					}
					return;
				}

				const e = E.inOut(t), a = f.src, b = f.dst;
				let cx = (a.x + b.x) / 2, cy = (a.y + b.y) / 2;

				if (f.arc === "up") {
					cy = Math.max(24, Math.min(a.y, b.y) - 110);
				} else if (f.arc === "right") {
					cx = Math.min(W - 30, cx + 150);
				} else if (f.arc === "left") {
					cx = Math.max(24, Math.min(a.x, b.x) - 90);
				}

				const x = (1 - e) * (1 - e) * a.x + 2 * (1 - e) * e * cx + e * e * b.x;
				const y = (1 - e) * (1 - e) * a.y + 2 * (1 - e) * e * cy + e * e * b.y;
				const glow = Math.sin(Math.PI * t);

				f.el.setAttribute("transform", `translate(${x.toFixed(2)} ${y.toFixed(2)}) scale(${lerp(a.s, b.s, e).toFixed(5)})`);
				f.el.setAttribute("opacity", "1");
				f.el.style.filter = `drop-shadow(0 0 ${(12 * glow).toFixed(1)}px rgba(0,194,255,${(0.7 * glow).toFixed(3)}))`;
			});

			if (flights.size) {
				raf = requestAnimationFrame(step);
			}
		}

		/** Land everything in the air at once (reset, or the stage changing shape). */
		function finishFlights() {
			if (raf) {
				cancelAnimationFrame(raf);
				raf = 0;
			}

			const list = Array.from(flights);
			flights.clear();
			list.forEach((f) => {
				f.el.remove();
				if (f.done) {
					f.done();
				}
			});
		}

		function launchLayer(i, a, delay, land) {
			const p = `${u}-f${++flightSeq}`;
			const el = fx(designDefs(p, S.v) + layerMarkup(i, p, S.v));
			launch(el, frameIn(figSvg(), FIG_CARD[0], FIG_CARD[1]), appFrame(a), delay, 0.9, tall ? "right" : "up", land);
		}

		function launchPacket(a, delay, land) {
			const el = fx(`<circle r="10" fill="#00c2ff" fill-opacity=".45"/><circle r="5" fill="#fff"/>`);
			const src = frameIn(figSvg(), FIG_CARD[0] + 260, FIG_CARD[1] + 170);
			const d = appFrame(a);
			const dst = d.hidden ? { x: d.x, y: d.y, s: 0.6 } : { x: d.x + 260 * d.s, y: d.y + 170 * d.s, s: 0.8 };
			src.s = 1;
			launch(el, src, dst, delay, 0.6, tall ? "right" : "up", land);
		}

		/* ---- actions ---- */
		function pick(i, add) {
			if (add) {
				if (S.sel.has(i)) {
					S.sel.delete(i);
				} else {
					S.sel.add(i);
				}
			} else {
				S.sel = new Set([i]);
			}
			afterSelect();
		}

		function afterSelect() {
			S.status = ["", ""];
			paintFigma();
			paintProps();
			paintPlugin();
			paintStatus();
			paintHint();
		}

		function setDest(d) {
			S.dest = d;
			if (tall && d !== "all") {
				showTab(d);
			}
			paintPlugin();
		}

		function setOpt(i, n) {
			if (S.v[i] === n) {
				return;
			}

			S.v[i] = n;
			S.ver[i]++;
			paintFigmaCanvas();
			paintPropsChecked();

			const holders = APPS.filter((a) => S.apps[a][i]);
			holders.forEach(paintApp);

			if (S.live && holders.length) {
				syncLive(holders.map((a) => [a, i]));
			} else if (S.live) {
				status(`Live is on. Send ${LAYERS[i].name} once and its changes follow.`, "");
			} else if (holders.length) {
				status(`${LAYERS[i].name} changed. Send again to update it in place.`, "");
			}
			paintHint();
		}

		function setLive(on) {
			S.live = on;
			paintPlugin();

			if (!on) {
				status("Live is off", "");
				return;
			}

			const stale = [];
			APPS.forEach((a) => ALL.forEach((i) => {
				if (S.apps[a][i] && S.apps[a][i].ver < S.ver[i]) {
					stale.push([a, i]);
				}
			}));

			if (stale.length) {
				syncLive(stale);
			} else {
				status("Live is on: changes go out as you make them", "ok");
			}
		}

		function syncLive(pairs) {
			const apps = [];

			pairs.forEach(([a, i], n) => {
				const snap = { v: S.v[i], ver: S.ver[i] };
				const land = () => {
					if (!S.apps[a][i] || S.apps[a][i].ver <= snap.ver) {
						S.apps[a][i] = snap;
					}
					S.flags[a][i] = "updated";
					if (tall && S.tab !== a) {
						bump(a);
					}
					paintApp(a);
					toast(a, "Updated live");
				};

				if (apps.indexOf(a) < 0) {
					apps.push(a);
				}

				if (reduced()) {
					land();
				} else {
					launchPacket(a, n * 0.08, land);
				}
			});

			S.did.live = true;
			S.did.update = true;

			const names = pairs.map(([, i]) => LAYERS[i].name).filter((name, n, all) => all.indexOf(name) === n);
			status(`Live: ${listNames(names)} updated in ${apps.length === 3 ? "all apps" : listNames(apps.map((a) => APP[a].name))}`, "ok");
			paintHint();
		}

		function send() {
			if (busy) {
				return;
			}

			if (!S.sel.size) {
				nudge();
				return;
			}

			const targets = S.dest === "all" ? APPS.slice() : [S.dest];
			const layers = Array.from(S.sel).sort((x, y) => x - y);
			const where = S.dest === "all" ? "all apps" : APP[S.dest].name;
			const count = { added: 0, updated: 0, same: 0 };
			const per = {};
			let pending = targets.length * layers.length;

			busy++;
			status(`Sending ${plural(layers.length, "layer")} to ${where}…`, "busy");
			paintPlugin();

			const done = () => {
				busy--;
				S.did.send = true;
				if (count.updated) {
					S.did.update = true;
				}

				targets.forEach((a) => {
					const c = per[a];
					toast(a, c.updated ? `${plural(c.updated, "layer")} updated${c.added ? `, ${c.added} new` : ""}` : c.added ? `${plural(c.added + c.same, "layer")} from Figma` : "Already up to date");
				});

				if (!count.updated && count.added) {
					status(`Sent ${plural(layers.length, "layer")} to ${where}`, "ok");
				} else if (count.updated && !count.added) {
					status(`Updated in place in ${where}: no new copies`, "ok");
				} else if (count.updated) {
					status(`Updated what was there and added the rest in ${where}`, "ok");
				} else {
					status(`Already up to date in ${where}`, "ok");
				}

				paintPlugin();
				paintHint();
			};

			targets.forEach((a, j) => {
				per[a] = { added: 0, updated: 0, same: 0 };

				layers.forEach((i, n) => {
					const snap = { v: S.v[i], ver: S.ver[i] };
					const land = () => {
						const cur = S.apps[a][i];
						const kind = !cur ? "added" : cur.ver < snap.ver ? "updated" : "same";

						per[a][kind]++;
						count[kind]++;
						if (!cur || cur.ver <= snap.ver) {
							S.apps[a][i] = snap;
						}
						S.flags[a][i] = kind === "added" ? "new" : "updated";
						if (tall && S.tab !== a) {
							bump(a);
						}
						paintApp(a);

						if (--pending === 0) {
							done();
						}
					};

					if (reduced()) {
						land();
					} else {
						launchLayer(i, a, n * 0.12 + j * 0.1, land);
					}
				});
			});
		}

		function sendBack(a) {
			if (S.sentBack[a]) {
				return;
			}

			const own = APP[a].own;
			S.sentBack[a] = true;
			paintApp(a);
			status(`Sending ${own.name} from ${APP[a].name} to Figma…`, "busy");

			const land = () => {
				S.queue.push({ kind: own.kind, from: a });
				S.did.back = true;
				paintIncoming();
				status(`${own.name} from ${APP[a].name} is waiting in Figma`, "ok");
				paintHint();
			};

			if (reduced()) {
				land();
				return;
			}

			const src = frameIn(appSvg(a), own.at[0], own.at[1]);
			const dst = frameIn(q(".lld-inthumb"), 0, 0);
			src.s *= own.at[2];
			dst.s *= own.kind === "photo" ? 1 : 1.6;
			launch(fx(item(own.kind, `${u}-f${++flightSeq}`)), src, dst, 0, 0.9, tall ? "left" : "up", land);
		}

		function place() {
			const it = S.queue[0];

			if (!it || placing) {
				return;
			}

			placing = true;

			const src = frameIn(q(".lld-inthumb"), 0, 0);
			const at = PLACE_AT[it.kind];
			const dst = frameIn(figSvg(), at[0], at[1]);
			src.s *= it.kind === "photo" ? 1 : 1.6;
			dst.s *= at[2];

			const land = () => {
				placing = false;
				S.queue.shift();
				S.placed.push(it.kind);
				S.justPlaced = it.kind;
				S.did.place = true;
				paintFigma();
				paintIncoming();
				status(`Placed ${APP[it.from].own.name} from ${APP[it.from].name}`, "ok");
				paintHint();

				const next = q(".lld-incoming.lld-on .lld-place");
				(next || q(".lld-layers")).focus({ preventScroll: true });
			};

			q(".lld-incoming").classList.remove("lld-on");

			if (reduced()) {
				land();
			} else {
				launch(fx(item(it.kind, `${u}-f${++flightSeq}`)), src, dst, 0, 0.7, "up", land);
			}
		}

		function reset() {
			finishFlights();
			Object.keys(toastTimers).forEach((a) => clearTimeout(toastTimers[a]));
			S = fresh();
			busy = 0;
			placing = false;
			propsKey = null;
			qa(".lld-toast").forEach((t) => t.classList.remove("lld-on"));
			paintAll();
		}

		function showTab(a) {
			S.tab = a;
			S.unseen[a] = 0;
			paintTabs();
		}

		function bump(a) {
			S.unseen[a]++;
			paintTabs();
		}

		function status(text, kind) {
			S.status = [text, kind];
			paintStatus();
		}

		function nudge() {
			const h = q(".lld-hint");
			h.classList.remove("lld-nudging");
			void h.offsetWidth;
			h.classList.add("lld-nudging");
			status("Select at least one layer first", "");
		}

		function toast(a, text) {
			const el = q(`[data-app="${a}"] .lld-toast`);
			el.textContent = text;
			el.classList.add("lld-on");
			clearTimeout(toastTimers[a]);
			toastTimers[a] = setTimeout(() => el.classList.remove("lld-on"), 2600);
		}

		/* ---- painting ---- */
		function paintAll() {
			paintFigma();
			paintProps();
			paintPlugin();
			paintIncoming();
			APPS.forEach(paintApp);
			paintTabs();
			paintStatus();
			paintHint();
		}

		function paintFigma() {
			qa("[data-pick]").forEach((b) => {
				const v = b.getAttribute("data-pick");
				b.setAttribute("aria-pressed", String(v === "all" ? S.sel.size === 5 : S.sel.has(+v)));
			});

			q(".lld-placed").innerHTML = S.placed.slice().reverse().map((kind) =>
				`<div class="lld-row lld-row--placed${S.justPlaced === kind ? "" : " lld-old"}">${ico(kind === "photo" ? "image" : "vector", "#555")}${kind === "photo" ? "Photo" : "Sparkle"}<em>${kind === "photo" ? "from Ps" : "from Ai"}</em></div>`).join("");
			S.justPlaced = "";

			paintFigmaCanvas();
		}

		function paintFigmaCanvas() {
			const svg = figSvg(), p = `${u}-fg`;
			const width = svg.getBoundingClientRect().width / k || 400;
			const h = (7 * 540) / width; /* 7px handles, whatever the scale */
			const handle = (x, y) => `<rect x="${x - h / 2}" y="${y - h / 2}" width="${h}" height="${h}" fill="#fff" stroke="#0d99ff" stroke-width="1.5" vector-effect="non-scaling-stroke"/>`;
			const box = (b) => `<rect x="${b.x}" y="${b.y}" width="${b.width}" height="${b.height}" fill="none" stroke="#0d99ff" stroke-width="2" vector-effect="non-scaling-stroke"/>` +
				handle(b.x, b.y) + handle(b.x + b.width, b.y) + handle(b.x, b.y + b.height) + handle(b.x + b.width, b.y + b.height);

			svg.innerHTML = designDefs(p, S.v) +
				`<g transform="translate(${FIG_CARD[0]} ${FIG_CARD[1]})">` +
				ALL.map((i) => `<g data-layer="${i}">${layerMarkup(i, p, S.v)}</g>`).join("") +
				ALL.map((i) => `<rect class="lld-hover" data-hover="${i}" fill="none" stroke="#0d99ff" stroke-width="1.5" vector-effect="non-scaling-stroke"/>`).join("") +
				`<g class="lld-selbox"></g></g>` +
				S.placed.map((kind) => `<g transform="translate(${PLACE_AT[kind][0]} ${PLACE_AT[kind][1]}) scale(${PLACE_AT[kind][2]})">${item(kind, `${p}-${kind}`)}</g>`).join("");

			const boxes = ALL.map((i) => {
				try {
					return svg.querySelector(`[data-layer="${i}"]`).getBBox();
				} catch (err) {
					return { x: 0, y: 0, width: 520, height: 340 };
				}
			});

			boxes.forEach((b, i) => {
				const r = svg.querySelector(`[data-hover="${i}"]`);
				r.setAttribute("x", b.x - 2);
				r.setAttribute("y", b.y - 2);
				r.setAttribute("width", b.width + 4);
				r.setAttribute("height", b.height + 4);
			});

			svg.querySelector(".lld-selbox").innerHTML = S.sel.size === 5
				? box({ x: -1, y: -1, width: 522, height: 342 }) +
					`<rect x="215" y="352" width="90" height="24" rx="4" fill="#0d99ff"/><text x="260" y="369" text-anchor="middle" fill="#fff" font-size="13" font-weight="600">520 × 340</text>`
				: Array.from(S.sel).map((i) => box({ x: boxes[i].x - 2, y: boxes[i].y - 2, width: boxes[i].width + 4, height: boxes[i].height + 4 })).join("");
		}

		function paintProps() {
			const key = Array.from(S.sel).sort().join(",");
			const panel = q(".lld-props");

			if (key !== propsKey) {
				propsKey = key;

				if (S.sel.size === 1) {
					const i = S.sel.values().next().value, layer = LAYERS[i];
					const opts = layer.opts.map((o, n) => (layer.kind === "fill"
						? `<button type="button" class="lld-swatch" role="radio" data-for="${i}" data-opt="${n}" aria-label="${o[0]}" style="background:${o[0]}"></button>`
						: `<button type="button" class="lld-word" role="radio" data-for="${i}" data-opt="${n}">${o}</button>`)).join("");

					panel.innerHTML = `<h4>Design <span>${layer.name}</span></h4><span class="lld-prop-name" id="${u}-prop">${layer.kind === "fill" ? "Fill" : "Text"}</span><div class="lld-opts" role="radiogroup" aria-labelledby="${u}-prop">${opts}</div>`;
				} else if (S.sel.size > 1) {
					panel.innerHTML = `<h4>Design <span>${S.sel.size} layers</span></h4><p>Pick one layer to change its colour or text.</p>`;
				} else {
					panel.innerHTML = `<h4>Design</h4><p>Select a layer to see what you can change.</p>`;
				}
			}

			paintPropsChecked();
		}

		function paintPropsChecked() {
			qa(".lld-props [data-opt]").forEach((b) => {
				const on = S.v[+b.getAttribute("data-for")] === +b.getAttribute("data-opt");
				b.setAttribute("aria-checked", String(on));
				b.tabIndex = on ? 0 : -1;
			});
		}

		function paintPlugin() {
			qa("[data-dest]").forEach((b) => {
				const on = b.getAttribute("data-dest") === S.dest;
				b.setAttribute("aria-checked", String(on));
				b.tabIndex = on ? 0 : -1;
			});

			q(".lld-switch").setAttribute("aria-checked", String(S.live));

			const sendBtn = q(".lld-send");
			sendBtn.textContent = S.dest === "all" ? "Send to all apps" : `Send to ${APP[S.dest].name}`;
			sendBtn.setAttribute("aria-disabled", String(!S.sel.size || busy > 0));
		}

		function paintStatus() {
			const el = q(".lld-status");
			const text = S.status[0] || (S.sel.size ? `${plural(S.sel.size, "layer")} selected` : "3 Adobe apps connected");

			if (el.textContent !== text) {
				el.textContent = text;
			}
			el.className = `lld-status${S.status[1] ? ` lld-${S.status[1]}` : ""}`;
		}

		function paintHint() {
			const key = S.queue.length ? "place"
				: !S.did.send ? (S.sel.size ? "send" : "pick")
				: !S.did.update ? "edit"
				: !S.did.live ? "live"
				: !S.did.place ? "back"
				: "done";
			q(".lld-hint").textContent = L.hints[key];
		}

		function paintIncoming() {
			const card = q(".lld-incoming");
			const it = S.queue[0];

			if (!it || placing) {
				card.classList.remove("lld-on");
				return;
			}

			q(".lld-inthumb").innerHTML = it.kind === "photo" ? item("photo", `${u}-in`) : `<g transform="scale(1.6)">${item("sparkle", `${u}-in`)}</g>`;
			card.querySelector(".lld-intext span").textContent = `${APP[it.from].own.name} from ${APP[it.from].name}${S.queue.length > 1 ? ` (+${S.queue.length - 1})` : ""}`;
			card.classList.add("lld-on");
		}

		function paintApp(a) {
			const m = APP[a], have = S.apps[a], flags = S.flags[a];
			const win = q(`[data-app="${a}"]`);
			const p = `${u}-${a}`;
			const vals = ALL.map((i) => (have[i] ? have[i].v : 0));
			const present = ALL.filter((i) => have[i]);
			let art = "";

			if (a === "ai") {
				art += `<rect x="${m.card[0] - 10}" y="${m.card[1] - 10}" width="540" height="360" fill="#fff"/>`;
			}

			if (!present.length) {
				art += `<rect x="${m.card[0]}" y="${m.card[1]}" width="520" height="340" rx="24" fill="none" stroke="${a === "ai" ? "#000" : "#fff"}" stroke-opacity=".16" stroke-width="3" stroke-dasharray="12 10"/>`;
			}

			/* Each copy keeps the values it was sent with, so an out-of-date copy still looks old. */
			art += designDefs(p, vals) + `<g transform="translate(${m.card[0]} ${m.card[1]})">${present.map((i) => layerMarkup(i, p, vals)).join("")}</g>`;

			if (m.own) {
				art += `<g transform="translate(${m.own.at[0]} ${m.own.at[1]}) scale(${m.own.at[2]})">${item(m.own.kind, `${p}-own`)}</g>`;
			}

			win.querySelector(".lld-appcanvas svg").innerHTML = art;

			const state = (i) => `${flags[i] === "new" ? " lld-new" : flags[i] === "updated" ? " lld-updated" : ""}${have[i].ver < S.ver[i] ? " lld-stale" : ""}`;
			const staleText = (i) => (have[i].ver < S.ver[i] ? `<span class="lld-sr"> (out of date)</span>` : "");
			const topDown = TOP_DOWN.filter((i) => have[i]);
			const list = win.querySelector(".lld-list");

			if (a === "ae") {
				list.innerHTML = topDown.map((i, n) =>
					`<li class="${state(i).trim()}"><span class="lld-tl-n">${n + 1}</span><i style="background:${LAYERS[i].ae}"></i><span class="lld-tl-name">${LAYERS[i].name}</span><span class="lld-tl-bar"><b style="background:${LAYERS[i].ae}"></b></span>${staleText(i)}</li>`).join("");
			} else {
				const thumb = (i) => {
					if (a === "ai") {
						return ico(LAYERS[i].icon, "#c8c8c8");
					}
					if (LAYERS[i].kind === "text") {
						return `<span class="lld-thumb" style="display:grid;place-items:center;font-size:9px;font-weight:700;color:#ddd">T</span>`;
					}
					return `<span class="lld-thumb" style="background:${LAYERS[i].opts[have[i].v][0]}"></span>`;
				};

				list.innerHTML = (topDown.length ? `<li>${EYE}${ico("group", "#c8c8c8")}Hero card</li>` : "") +
					topDown.map((i) => `<li class="lld-in${state(i)}">${EYE}${thumb(i)}${LAYERS[i].name}${staleText(i)}</li>`).join("") +
					`<li class="lld-own">${EYE}${a === "ps" ? `<span class="lld-thumb" style="background:linear-gradient(#ffb199,#ff7a59 55%,#6c3fa0)"></span>` : ico("vector", "#c8c8c8")}${m.own.name}</li>`;

				const back = win.querySelector(".lld-back");
				back.setAttribute("aria-disabled", String(S.sentBack[a]));
				back.lastChild.textContent = S.sentBack[a] ? "Sent to Figma" : "Send to Figma";
			}

			S.flags[a] = {};
		}

		function paintTabs() {
			if (!tall) {
				return;
			}

			qa("[data-tab]").forEach((b) => {
				const a = b.getAttribute("data-tab"), on = a === S.tab;
				let dot = b.querySelector("b");

				b.setAttribute("aria-selected", String(on));
				b.tabIndex = on ? 0 : -1;

				if (S.unseen[a] && !on) {
					if (!dot) {
						dot = document.createElement("b");
						b.appendChild(dot);
					}
					dot.textContent = `+${S.unseen[a]}`;
				} else if (dot) {
					dot.remove();
				}
			});

			APPS.forEach((a) => {
				q(`[data-app="${a}"]`).hidden = a !== S.tab;
			});
		}

		/* ---- input ---- */
		root.addEventListener("click", (e) => {
			if (!stage || !(e.target instanceof Element)) {
				return;
			}

			const t = e.target;
			const add = e.shiftKey || e.metaKey || e.ctrlKey;
			const hit = (sel) => t.closest(sel);
			let el;

			if ((el = hit("[data-pick]"))) {
				const v = el.getAttribute("data-pick");
				if (v === "all") {
					S.sel = new Set(ALL);
					afterSelect();
				} else {
					pick(+v, add);
				}
			} else if ((el = hit(".lld-design--fig [data-layer]"))) {
				pick(+el.getAttribute("data-layer"), add);
			} else if (hit(".lld-design--fig")) {
				if (!add && S.sel.size) {
					S.sel.clear();
					afterSelect();
				}
			} else if ((el = hit("[data-dest]"))) {
				setDest(el.getAttribute("data-dest"));
			} else if ((el = hit("[data-opt]"))) {
				setOpt(+el.getAttribute("data-for"), +el.getAttribute("data-opt"));
			} else if ((el = hit("[data-back]"))) {
				sendBack(el.getAttribute("data-back"));
			} else if ((el = hit("[data-tab]"))) {
				showTab(el.getAttribute("data-tab"));
			} else if ((el = hit("[data-act]"))) {
				const act = el.getAttribute("data-act");
				if (act === "send") {
					send();
				} else if (act === "live") {
					setLive(!S.live);
				} else if (act === "place") {
					place();
				} else if (act === "reset") {
					reset();
				}
			}
		});

		/* Arrow keys move through radio groups and tabs, as they do in native controls. */
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

		/* A hover outline on the canvas, as Figma draws one. */
		root.addEventListener("pointerover", (e) => {
			if (!stage || e.pointerType === "touch" || !(e.target instanceof Element)) {
				return;
			}
			const g = e.target.closest(".lld-design--fig [data-layer]");
			const i = g ? g.getAttribute("data-layer") : null;
			qa(".lld-hover").forEach((r) => r.classList.toggle("lld-hot", r.getAttribute("data-hover") === i));
		});

		root.addEventListener("pointerleave", () => {
			if (stage) {
				qa(".lld-hover").forEach((r) => r.classList.remove("lld-hot"));
			}
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
		const els = Array.from(document.querySelectorAll("[data-lazylord-demo]"));

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

	window.LazyLordDemo = { mount };

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", auto);
	} else {
		auto();
	}
})();
