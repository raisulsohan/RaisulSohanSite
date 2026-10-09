/*
 * LazyEditMirror interactive demo.
 *
 * A small Premiere Pro sequence with the LazyEditMirror panel docked beside it. The front
 * camera (V1) is a raw recording with its pauses and retakes still in: cut them, pick a
 * side-camera file, Analyze (the audio engine lines the two recordings up by their sound),
 * Sync, and the side camera lands on V2 with the same cuts. Press play and both monitors say
 * the same line at the same moment, because each draws the presenter from the moment of the
 * performance its clip shows: the lips only match when the side clip's in-point really is
 * the front in-point plus the offset.
 *
 * The panel is the real one's markup, colours and wording (index.html, styles.css, main.js
 * of LazyEditMirror 1.1.2); the plan is its rule: one side file per pass, a front clip is
 * placed only when it fits inside the side file whole, clips already on the Target track are
 * never touched, side in-points snap to the frame grid, every placement is its own undo step.
 *
 * Two stages, scaled to fit: a wide one (1280 x 760) and a tall one for phones and narrow
 * columns (440 x 1120). It is a simulation: no file is read and nothing leaves the page.
 *
 * In the theme: a standalone page bundle (assets/lazyeditmirror-demo.min.js), enqueued only
 * on the project whose kit names it (rs_project_demo_kit()); page-portfolio.php prints
 * <div class="rs-demo-wrap lem-wrap"><div class="lem rs-demo-mount" data-lazyeditmirror-demo …>,
 * and the portfolio's pop-up player mounts the same bundle through window.LazyEditMirrorDemo.
 * It starts when it scrolls near, and its box is sized by CSS alone, so a cached page never
 * shifts.
 */
(() => {
	"use strict";

	const FPS = 25;
	const TL_SECONDS = 72;
	const VERSION = "1.1.2";

	/* ------------------------------------------------------------------ the footage */
	const FILES = {
		C0002: { name: "C0002.MP4", kind: "front", dur: 40 },
		C0003: { name: "C0003.MP4", kind: "front", dur: 30 },
		C0056: { name: "C0056.MP4", kind: "side", of: "C0002", offset: 0.635, score: 0.84, dur: 42 },
		C0057: { name: "C0057.MP4", kind: "side", of: "C0003", offset: 1.21, score: 0.79, dur: 33 },
		BROLL: { name: "City_Broll.MP4", kind: "broll", dur: 24 },
	};
	const FOOTAGE = ["C0056", "C0057", "BROLL", "C0002", "C0003"];

	/* What the presenter does, by the time in the front file: [from, to, kind, en, bn]. */
	const SCRIPT = {
		C0002: [
			[0, 3, "say", "Hi! Today we're filming with two cameras.", "হ্যালো! আজ দুটো ক্যামেরায় শুট করছি।"],
			[3, 6, "say", "The front camera gets cut first.", "আগে ফ্রন্ট ক্যামেরা কাটা হয়।"],
			[6, 9, "pause", "(long pause)", "(লম্বা বিরতি)"],
			[9, 12, "say", "Every pause and every mistake goes.", "প্রতিটি বিরতি আর প্রতিটি ভুল বাদ যায়।"],
			[12, 15, "say", "Then the side camera has to match.", "তারপর সাইড ক্যামেরাকে মেলাতে হয়।"],
			[15, 19, "retake", "“Usually that means— sorry, again.”", "“সাধারণত এর মানে— সরি, আবার।”"],
			[19, 22, "say", "Usually that means cutting it all twice.", "সাধারণত এর মানে সব দুবার কাটা।"],
			[22, 25, "say", "Same cuts, same timing, by hand.", "একই কাট, একই টাইমিং, হাতে হাতে।"],
			[25, 26.5, "cough", "(cough)", "(কাশি)"],
			[26.5, 29.5, "say", "It takes as long as the first edit.", "প্রথম এডিটের সমান সময় লেগে যায়।"],
			[29.5, 32, "say", "And one slip puts the lips out of sync.", "আর একটু ভুলেই ঠোঁট আর কথা মেলে না।"],
			[32, 34.5, "notes", "(checks notes)", "(নোট দেখছে)"],
			[34.5, 37.5, "say", "So let the side camera follow instead.", "তাই সাইড ক্যামেরাকে পিছু পিছু আসতে দিন।"],
			[37.5, 40, "say", "Cut once. Mirror the rest.", "একবার কাটুন। বাকিটা মিরর হবে।"],
		],
		C0003: [
			[0, 3.5, "say", "The trick is in the sound.", "রহস্যটা শব্দে।"],
			[3.5, 7, "say", "Both cameras heard the same voice.", "দুটো ক্যামেরাই একই কণ্ঠ শুনেছে।"],
			[7, 10, "pause", "(long pause)", "(লম্বা বিরতি)"],
			[10, 13.5, "say", "Line the two recordings up by ear…", "দুটো রেকর্ডিং কান দিয়ে মিলিয়ে নিন…"],
			[13.5, 17, "say", "…and you know where every cut lands.", "…তাহলেই জানা যায় প্রতিটি কাট কোথায় পড়বে।"],
			[17, 21, "retake", "“To the frame, well— let me redo that.”", "“ফ্রেম ধরে, মানে— আবার বলি।”"],
			[21, 25, "say", "To the frame, on every clip.", "ফ্রেম ধরে, প্রতিটি ক্লিপে।"],
			[25, 30, "say", "That's it. Thanks for watching!", "এই তো। দেখার জন্য ধন্যবাদ!"],
		],
	};

	/* The parts of the front recording an editor cuts away. */
	const JUNK = [];
	Object.keys(SCRIPT).forEach((file) => {
		SCRIPT[file].forEach((s, i) => {
			if (s[2] !== "say") {
				JUNK.push({ id: `${file}-${i}`, file, a: s[0], b: s[1], kind: s[2] });
			}
		});
	});

	const MODE_HINTS = {
		audio: "The audio engine listens to the side file and to every front file on the Master track and finds the offsets itself.",
		selection: "Put the side clip on a free video track, select it together with the Master clip it is in sync with (select both, right-click > Synchronize > Audio), keep them selected, then Analyze. Covers that front file; the side clip is consumed.",
		timecode: "Every file must carry matching (jam-synced) source timecode.",
		offset: "Select one Master clip of the front file this offset belongs to, and enter how many seconds later (+) or earlier (-) the same moment is in the side file.",
	};

	/* ------------------------------------------------------------------ words */
	const STR = {
		en: {
			region: "LazyEditMirror interactive demo: a Premiere Pro sequence with the LazyEditMirror panel",
			undo: "Undo",
			reset: "Reset",
			sequence: "Sequence 01",
			cutAll: (n) => `Cut all pauses (${n})`,
			cutNone: "Front edit cut",
			front: "FRONT CAM · V1",
			side: "SIDE CAM · V2",
			master: "Master",
			target: "Target",
			emptyFront: "No clip on V1 here",
			emptySide: "V2 is empty here",
			emptySideSub: "LazyEditMirror fills it",
			inSync: "in sync ✓",
			off: (s) => `off by ${s}`,
			wrong: "wrong footage",
			play: "Play",
			pause: "Pause",
			home: "Go to start",
			keys: "Space play · ← → frame · Ctrl+Z undo",
			junk: { pause: "pause", retake: "retake", cough: "cough", notes: "notes" },
			cutTip: "Cut this and close the gap",
			locked: "The side camera is already on V2. Undo its passes to change the front edit.",
			rippled: "Ripple delete: the gap closes, V1 stays gapless.",
			refreshed: "Tracks and project re-read.",
			folder: "In Premiere this opens %LOCALAPPDATA%\\LazyEditMirror\\engine.",
			saved: "In Premiere this saves the log to a file.",
			copied: "Log copied.",
			undone: (label) => `Undo: ${label}`,
			noUndo: "Nothing to undo.",
			uCut: "Ripple delete",
			uCutAll: "Ripple delete pauses",
			uPlace: "Place side clip",
			scanTitle: "Audio engine · cross-correlating loudness envelopes",
			scanFront: "Front audio",
			scanSide: "Side audio",
			scanCorr: "cross-correlation",
			listening: "listening…",
			noPeak: "no clear peak · no match",
			g: {
				cut: "Cut the front camera first: click the striped pauses on V1, or Cut all.",
				analyze: "Click Analyze: the engine finds the side file's place by its sound.",
				sync: (n) => `Click Sync ${n} clips, then click it again to confirm.`,
				confirm: "Click the red button again to place the clips.",
				next: (name) => `Pick ${name} in step 2 and click Analyze again.`,
				nothing: "This file adds nothing. Pick another side file in step 2.",
				done: "Done. Press play: both cameras say the same line at the same moment.",
				busy: "Working…",
			},
		},
		bn: {
			region: "LazyEditMirror ইন্টারঅ্যাক্টিভ ডেমো: LazyEditMirror প্যানেলসহ একটি Premiere Pro সিকোয়েন্স",
			undo: "আনডু",
			reset: "রিসেট",
			sequence: "Sequence 01",
			cutAll: (n) => `সব বিরতি কাটুন (${toBn(n)})`,
			cutNone: "ফ্রন্ট এডিট কাটা শেষ",
			front: "ফ্রন্ট ক্যাম · V1",
			side: "সাইড ক্যাম · V2",
			master: "Master",
			target: "Target",
			emptyFront: "এখানে V1-এ ক্লিপ নেই",
			emptySide: "এখানে V2 ফাঁকা",
			emptySideSub: "LazyEditMirror এটা ভরাবে",
			inSync: "সিঙ্কে আছে ✓",
			off: (s) => `${s} সরে আছে`,
			wrong: "ভুল ফুটেজ",
			play: "চালান",
			pause: "থামান",
			home: "শুরুতে যান",
			keys: "Space চালান · ← → ফ্রেম · Ctrl+Z আনডু",
			junk: { pause: "বিরতি", retake: "রিটেক", cough: "কাশি", notes: "নোট" },
			cutTip: "এটুকু কেটে ফাঁক বন্ধ করুন",
			locked: "সাইড ক্যামেরা আগেই V2-এ বসেছে। ফ্রন্ট এডিট বদলাতে আগে সেই পাসগুলো আনডু করুন।",
			rippled: "রিপল ডিলিট: ফাঁক বন্ধ হয়, V1-এ কোনো গ্যাপ থাকে না।",
			refreshed: "ট্র্যাক আর প্রজেক্ট আবার পড়া হয়েছে।",
			folder: "Premiere-এ এটা %LOCALAPPDATA%\\LazyEditMirror\\engine ফোল্ডার খোলে।",
			saved: "Premiere-এ এটা লগ একটি ফাইলে সেভ করে।",
			copied: "লগ কপি হয়েছে।",
			undone: (label) => `আনডু: ${label}`,
			noUndo: "আনডু করার কিছু নেই।",
			uCut: "রিপল ডিলিট",
			uCutAll: "সব বিরতি রিপল ডিলিট",
			uPlace: "সাইড ক্লিপ বসানো",
			scanTitle: "অডিও ইঞ্জিন · দুই রেকর্ডিংয়ের শব্দের ছাপ মেলানো হচ্ছে",
			scanFront: "ফ্রন্ট অডিও",
			scanSide: "সাইড অডিও",
			scanCorr: "ক্রস-কোরিলেশন",
			listening: "শুনছে…",
			noPeak: "স্পষ্ট মিল নেই",
			g: {
				cut: "আগে ফ্রন্ট ক্যামেরা কাটুন: V1-এর ডোরাকাটা বিরতিগুলোতে ক্লিক করুন, অথবা সব একসাথে।",
				analyze: "Analyze চাপুন: ইঞ্জিন শব্দ শুনে সাইড ফাইলের জায়গা খুঁজে নেবে।",
				sync: (n) => `Sync ${toBn(n)} clips চাপুন, তারপর নিশ্চিত করতে আবার চাপুন।`,
				confirm: "ক্লিপগুলো বসাতে লাল বাটনটা আবার চাপুন।",
				next: (name) => `ধাপ ২-এ ${name} বেছে আবার Analyze চাপুন।`,
				nothing: "এই ফাইল থেকে নতুন কিছু বসবে না। ধাপ ২-এ অন্য সাইড ফাইল বাছুন।",
				done: "হয়ে গেছে! চালিয়ে দেখুন: দুই ক্যামেরা একই মুহূর্তে একই কথা বলছে।",
				busy: "কাজ চলছে…",
			},
		},
	};

	function toBn(n) {
		return String(n).replace(/\d/g, (d) => "০১২৩৪৫৬৭৮৯"[d]);
	}

	/* ------------------------------------------------------------------ small helpers */
	const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
	const clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
	const ease = (u) => (u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2);
	const hash = (n) => {
		const x = Math.sin(n * 127.1 + 311.7) * 43758.5453;
		return x - Math.floor(x);
	};
	const snap = (s) => Math.round(s * FPS) / FPS;

	/* The next frame, or a timer when the tab is hidden and frames stop. */
	const nextFrame = (fn) => {
		let ran = false;
		const go = (t) => {
			if (!ran) {
				ran = true;
				fn(t);
			}
		};
		requestAnimationFrame(go);
		setTimeout(() => go(performance.now()), 80);
	};
	const sign = (s) => (s >= 0 ? "+" : "-") + Math.abs(s).toFixed(3) + " s";
	const plural = (n, word) => `${n} ${word}${n === 1 ? "" : "s"}`;

	function tc(t) {
		const f = Math.max(0, Math.round(t * FPS));
		const p = (n) => String(n).padStart(2, "0");
		return `${p(Math.floor(f / (FPS * 3600)))}:${p(Math.floor(f / (FPS * 60)) % 60)}:${p(Math.floor(f / FPS) % 60)}:${p(f % FPS)}`;
	}

	function segAt(file, f) {
		const list = SCRIPT[file];

		if (!list) {
			return null;
		}

		for (let i = 0; i < list.length; i++) {
			if (f >= list[i][0] - 1e-6 && f < list[i][1]) {
				return list[i];
			}
		}

		return f < 0 ? list[0] : list[list.length - 1];
	}

	/* The loudness of a front recording at a moment: speech, silence, a cough. */
	function loud(file, f) {
		const s = segAt(file, f);
		const kind = s ? s[2] : "pause";
		const seed = file === "C0002" ? 0.3 : 1.7;

		if (kind === "pause" || kind === "notes") {
			return 0.04 + 0.04 * hash(Math.floor(f * 30) + seed);
		}

		if (kind === "cough") {
			const mid = (s[0] + s[1]) / 2;
			return 0.15 + 0.8 * Math.exp(-Math.pow((f - mid) * 3.2, 2));
		}

		const env = Math.abs(Math.sin(f * 3.1 + seed) * Math.sin(f * 1.3 + 0.4 + seed));
		return 0.18 + 0.72 * env * (0.45 + 0.55 * hash(Math.floor(f * 12) + seed));
	}

	const ICON = {
		undo: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 7H5V3M5.4 7A8 8 0 1 1 4 13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		reset: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.3-5.6M20 4v5h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		play: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>',
		pause: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h4v14H6zm8 0h4v14h-4z" fill="currentColor"/></svg>',
		home: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h2v14H6zM19 5v14L9 12z" fill="currentColor"/></svg>',
		cut: '<svg viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="6" cy="7" r="3"/><circle cx="6" cy="17" r="3"/><path d="M8.5 8.5 20 18M8.5 15.5 20 6"/></g></svg>',
	};

	/* ------------------------------------------------------------------ the camera pictures */
	function room(u, side) {
		const shelfX = side ? 214 : 22;
		const plantX = side ? 274 : 40;
		const posterX = side ? 30 : 236;

		return `
<defs>
	<linearGradient id="${u}-wall" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2b2444"/><stop offset="1" stop-color="#18142a"/></linearGradient>
	<radialGradient id="${u}-glow" cx="${side ? 0.42 : 0.5}" cy="0.38" r="0.62"><stop offset="0" stop-color="#a78bfa" stop-opacity="0.32"/><stop offset="1" stop-color="#a78bfa" stop-opacity="0"/></radialGradient>
</defs>
<rect width="320" height="180" fill="url(#${u}-wall)"/>
<rect width="320" height="180" fill="url(#${u}-glow)"/>
<rect x="${posterX}" y="26" width="54" height="38" rx="3" fill="#221d38" stroke="#3a3260"/>
<path d="M${posterX + 8} 56 l12 -14 l9 9 l7 -6 l10 11 z" fill="#3b3266"/>
<rect x="${shelfX}" y="72" width="84" height="5" rx="2" fill="#352e55"/>
<rect x="${shelfX + 8}" y="56" width="9" height="16" rx="1.5" fill="#5b46c4"/>
<rect x="${shelfX + 19}" y="60" width="8" height="12" rx="1.5" fill="#ec4899" opacity="0.8"/>
<rect x="${shelfX + 29}" y="58" width="7" height="14" rx="1.5" fill="#22d3ee" opacity="0.7"/>
<path d="M${plantX - 7} 72 h14 l-2 -9 h-10 z" fill="#4a3b2a"/>
<circle cx="${plantX - 5}" cy="58" r="7" fill="#1f6b4f"/><circle cx="${plantX + 4}" cy="55" r="8" fill="#23805d"/><circle cx="${plantX}" cy="49" r="6" fill="#1f6b4f"/>
<rect y="146" width="320" height="34" fill="#1b1730"/>
<rect y="146" width="320" height="1.5" fill="#2c2548"/>`;
	}

	function person(side) {
		const shirt = side ? "#4c3fa0" : "#5b46c4";
		const skin = "#e9c7a8";
		const hair = "#2a1d18";

		if (!side) {
			return `
<g class="lem-who">
	<path d="M112 186 V152 Q112 128 140 126 H180 Q208 128 208 152 V186 Z" fill="${shirt}"/>
	<path d="M150 126 L160 138 L170 126" fill="none" stroke="#3a2f86" stroke-width="3" stroke-linejoin="round"/>
	<rect x="152" y="110" width="16" height="20" fill="${skin}"/>
	<g class="lem-head">
		<circle cx="137.5" cy="97" r="4" fill="${skin}"/><circle cx="182.5" cy="97" r="4" fill="${skin}"/>
		<circle cx="160" cy="95" r="22" fill="${skin}"/>
		<path d="M137.5 93 Q138 70 160 70 Q182 70 182.5 93 Q176 80 160 80 Q146 80 137.5 93 Z" fill="${hair}"/>
		<path d="M148 88 h8 M164 88 h8" stroke="${hair}" stroke-width="2" stroke-linecap="round"/>
		<g class="lem-eyes"><circle cx="152" cy="95" r="2.3" fill="${hair}"/><circle cx="168" cy="95" r="2.3" fill="${hair}"/></g>
		<rect class="lem-mouth" x="154.5" y="105" width="11" height="2" rx="1.5" fill="#6b2b2b"/>
	</g>
	<rect class="lem-card-p" x="166" y="136" width="28" height="19" rx="2" fill="#f1effa" transform="rotate(-8 180 145)" opacity="0"/>
	<path class="lem-arm" d="M196 140 L212 162 L216 144" fill="none" stroke="${shirt}" stroke-width="13" stroke-linecap="round" stroke-linejoin="round"/>
	<circle class="lem-hand" cx="216" cy="144" r="7" fill="${skin}"/>
</g>`;
		}

		return `
<g class="lem-who">
	<path d="M126 186 V154 Q126 128 152 126 H166 Q190 128 190 154 V186 Z" fill="${shirt}"/>
	<rect x="150" y="110" width="15" height="20" fill="${skin}"/>
	<g class="lem-head">
		<circle cx="152" cy="96" r="4" fill="${skin}"/>
		<circle cx="160" cy="95" r="22" fill="${skin}"/>
		<path d="M181 98 l7 8 l-7 2 z" fill="${skin}"/>
		<path d="M138 102 Q134 72 160 70 Q178 70 182 84 Q170 78 160 82 Q152 86 152 100 Z" fill="${hair}"/>
		<path d="M170 88 h8" stroke="${hair}" stroke-width="2" stroke-linecap="round"/>
		<g class="lem-eyes"><circle cx="174" cy="95" r="2.2" fill="${hair}"/></g>
		<rect class="lem-mouth" x="171" y="105" width="9" height="2" rx="1.3" fill="#6b2b2b"/>
	</g>
	<rect class="lem-card-p" x="178" y="134" width="26" height="18" rx="2" fill="#f1effa" transform="rotate(-14 190 143)" opacity="0"/>
	<path class="lem-arm" d="M176 140 L188 160 L198 146" fill="none" stroke="${shirt}" stroke-width="13" stroke-linecap="round" stroke-linejoin="round"/>
	<circle class="lem-hand" cx="198" cy="146" r="7" fill="${skin}"/>
</g>`;
	}

	/* A city for the B-roll file: what you get when the wrong footage is placed. */
	function city(u) {
		const towers = [[10, 70, 40], [54, 50, 30], [88, 90, 36], [128, 60, 28], [160, 100, 42], [206, 66, 30], [240, 84, 38], [282, 58, 34]]
			.map(([x, h, w]) => `<rect x="${x}" y="${146 - h}" width="${w}" height="${h}" fill="#1c1838"/>` +
				Array.from({ length: Math.floor(h / 14) }, (_, i) => `<rect x="${x + 6}" y="${146 - h + 6 + i * 14}" width="${w - 12}" height="4" fill="#fbbf24" opacity="${(0.25 + 0.5 * hash(x + i)).toFixed(2)}"/>`).join(""))
			.join("");

		return `
<defs><linearGradient id="${u}-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0f1a3d"/><stop offset="0.7" stop-color="#5a2a6e"/><stop offset="1" stop-color="#ec4899"/></linearGradient></defs>
<rect width="320" height="180" fill="url(#${u}-sky)"/>
<circle cx="250" cy="44" r="14" fill="#fde68a" opacity="0.85"/>
${towers}
<rect y="146" width="320" height="34" fill="#0d0b1c"/>
<rect class="lem-car" x="20" y="152" width="34" height="9" rx="3" fill="#22d3ee"/>`;
	}

	/* ------------------------------------------------------------------ markup */
	function monitor(u, which, L) {
		const side = which === "side";

		return `
<figure class="lem-mon" data-mon="${which}">
	<figcaption><b>${side ? L.side : L.front}</b><span class="lem-mon__src"></span></figcaption>
	<div class="lem-screen">
		<svg viewBox="0 0 320 180" aria-hidden="true" preserveAspectRatio="xMidYMid slice">
			<g class="lem-scene">${room(`${u}-${which}`, side)}${person(side)}</g>
			<g class="lem-city" style="display:none">${city(`${u}-${which}`)}</g>
		</svg>
		<span class="lem-rec"><i></i>REC</span>
		${side ? '<span class="lem-tag"></span>' : ""}
		<span class="lem-cc"></span>
		<span class="lem-empty"><b></b><small></small></span>
	</div>
</figure>`;
	}

	function scanMarkup(L) {
		return `
<svg viewBox="0 0 640 200" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
	<text x="16" y="24" class="lem-scan__title">${esc(L.scanTitle)}</text>
	<g class="lem-scan__chip" opacity="0"><rect x="380" y="8" width="244" height="24" rx="12"/><text x="502" y="24" text-anchor="middle"></text></g>
	<text x="16" y="66" class="lem-scan__lab" fill="#a78bfa">${esc(L.scanFront)}</text>
	<text x="16" y="82" class="lem-scan__sub lem-scan__ff"></text>
	<path class="lem-scan__front" fill="#a78bfa"/>
	<text x="16" y="124" class="lem-scan__lab" fill="#22d3ee">${esc(L.scanSide)}</text>
	<text x="16" y="140" class="lem-scan__sub lem-scan__sf"></text>
	<svg x="150" y="96" width="476" height="60" viewBox="150 96 476 60" overflow="hidden"><path class="lem-scan__side" fill="#22d3ee"/></svg>
	<text x="16" y="184" class="lem-scan__sub">${esc(L.scanCorr)}</text>
	<path d="M150 188 H626" stroke="#312c48" stroke-width="1"/>
	<path class="lem-scan__curve" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linejoin="round"/>
	<circle class="lem-scan__dot" r="5" fill="#34d399" opacity="0"/>
	<text x="626" y="66" text-anchor="end" class="lem-scan__sub lem-scan__state"></text>
</svg>`;
	}

	function panelMarkup() {
		const opts = (list) => list.map(([v, label, sel]) => `<option value="${v}"${sel ? " selected" : ""}>${esc(label)}</option>`).join("");

		return `
<div class="lem-p">
	<div class="lem-p__top">
		<div class="lem-brand">
			<div class="lem-mark" aria-hidden="true"><i class="lem-mark__s"></i><i class="lem-mark__a"></i><i class="lem-mark__o"></i></div>
			<div>
				<div class="lem-bname">LazyEdit<span>Mirror</span></div>
				<div class="lem-bsub">Cut the front camera once. The side camera follows.</div>
			</div>
		</div>
		<div class="lem-pill" data-engine="idle" title="The audio engine finds the sync between the cameras"><span class="lem-pill__dot"></span><span class="lem-pill__t">Audio engine</span></div>
	</div>
	<div class="lem-stripe" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>

	<div class="lem-card lem-card--violet">
		<div class="lem-ctitle"><span class="lem-step">1</span>Tracks</div>
		<label class="lem-field"><span>Front camera edit (Master)</span><select data-f="master">${opts([["V1", "V1", true], ["V2", "V2"]])}</select></label>
		<label class="lem-field"><span>Side camera track (Target)</span><select data-f="target">${opts([["V1", "V1"], ["V2", "V2", true]])}</select></label>
	</div>

	<div class="lem-card lem-card--cyan">
		<div class="lem-ctitle lem-ctitle--between"><span><span class="lem-step">2</span>Side camera footage</span><button type="button" class="lem-btn lem-btn--small" data-act="refresh" title="Re-read the tracks and the project">Refresh</button></div>
		<div class="lem-field">
			<select data-f="footage" aria-label="Side camera footage">${opts(FOOTAGE.map((id) => [id, FILES[id].name + (FILES[id].kind === "front" ? " (front, on V1)" : ""), id === "C0056"]))}</select>
			<p class="lem-hint">One pass per side-camera file. Front files are listed last.</p>
		</div>
		<button type="button" class="lem-link" data-act="advanced" aria-expanded="false">Show advanced options</button>
		<div class="lem-adv" hidden>
			<label class="lem-field"><span>Sync reference</span><select data-f="mode">${opts([
				["audio", "Audio match (automatic)", true],
				["selection", "Selected clips are in sync (1 Master clip + a side clip)"],
				["timecode", "Match by source timecode (jam-synced cameras)"],
				["offset", "Offset only (enter it below)"],
			])}</select></label>
			<p class="lem-hint lem-modehint"></p>
			<label class="lem-field"><span>Extra offset in seconds (+ = later in the side file)</span><input data-f="extra" type="text" value="0" inputmode="decimal" autocomplete="off" spellcheck="false"></label>
			<div class="lem-row">
				<button type="button" class="lem-btn lem-btn--small" data-act="engine">Start audio engine</button>
				<button type="button" class="lem-btn lem-btn--small" data-act="folder">Open engine folder</button>
			</div>
		</div>
	</div>

	<div class="lem-actions">
		<button type="button" class="lem-btn lem-btn--primary" data-act="analyze">Analyze</button>
		<button type="button" class="lem-btn lem-btn--accent" data-act="sync" disabled>Sync</button>
	</div>
	<div class="lem-progress" hidden><i></i></div>
	<p class="lem-status" aria-live="polite"></p>

	<div class="lem-card lem-result" hidden>
		<div class="lem-ctitle lem-rtitle">Result</div>
		<div class="lem-rfiles"></div>
		<p class="lem-rtotals"></p>
		<p class="lem-hint lem-rnotes"></p>
	</div>

	<div class="lem-card lem-card--amber lem-logcard">
		<div class="lem-ctitle lem-ctitle--between">
			<span>Log</span>
			<span class="lem-row">
				<button type="button" class="lem-btn lem-btn--small" data-act="copylog">Copy</button>
				<button type="button" class="lem-btn lem-btn--small" data-act="savelog">Save...</button>
				<button type="button" class="lem-btn lem-btn--small" data-act="clearlog">Clear</button>
				<button type="button" class="lem-btn lem-btn--small" data-act="log" aria-expanded="false">Show</button>
			</span>
		</div>
		<div class="lem-logbody" hidden>
			<textarea class="lem-log" readonly aria-label="Log"></textarea>
			<p class="lem-hint">Also written to LazyEditMirror.log in the plugin's data folder.</p>
		</div>
	</div>

	<p class="lem-foot">LazyEditMirror ${VERSION} by <a href="https://raisulsohan.com" target="_blank" rel="noopener noreferrer">Raisul Sohan</a>. Free and open source (MIT). Every step is a separate Undo entry.</p>
</div>`;
	}

	function markup(u, L, tall) {
		const W = tall ? 440 : 1280;
		const H = tall ? 1120 : 760;
		const guide = '<div class="lem-guide" aria-live="polite"><b class="lem-guide__n"></b><span class="lem-guide__t"></span></div>';

		return `
<div class="lem-stage lem-stage--${tall ? "tall" : "wide"}" style="width:${W}px;height:${H}px">
	<header class="lem-top">
		<span class="lem-app" aria-hidden="true">Pr</span>
		<span class="lem-proj">Interview_Edit.prproj</span>
		${tall ? '<span class="lem-spacer"></span>' : guide}
		<button type="button" class="lem-tbtn" data-act="undo" disabled>${ICON.undo}<span>${L.undo}</span></button>
		<button type="button" class="lem-tbtn" data-act="reset">${ICON.reset}<span>${L.reset}</span></button>
	</header>
	${tall ? guide : ""}
	<div class="lem-work">
		<div class="lem-mons">
			${monitor(u, "front", L)}
			${monitor(u, "side", L)}
			<div class="lem-scan" hidden>${scanMarkup(L)}</div>
		</div>
		<div class="lem-transport">
			<button type="button" class="lem-tp" data-act="home" aria-label="${esc(L.home)}" title="${esc(L.home)}">${ICON.home}</button>
			<button type="button" class="lem-tp lem-tp--play" data-act="play" aria-label="${esc(L.play)}" title="${esc(L.play)}">${ICON.play}</button>
			<span class="lem-tc"><b></b> / <span></span></span>
			<span class="lem-keys">${esc(L.keys)}</span>
		</div>
		<div class="lem-tl">
			<div class="lem-tl__head">
				<b>${L.sequence}</b>
				<span class="lem-tl__dur"></span>
				<button type="button" class="lem-cutall" data-act="cutall">${ICON.cut}<span></span></button>
			</div>
			<div class="lem-tl__body">
				<div class="lem-heads">
					<span class="lem-rh"></span>
					<span class="lem-th" data-tr="V2">V2<small></small></span>
					<span class="lem-th" data-tr="V1">V1<small></small></span>
					<span class="lem-th lem-th--a">A1</span>
					<span class="lem-th lem-th--a">A2</span>
				</div>
				<div class="lem-lanes">
					<div class="lem-ruler"></div>
					<div class="lem-lane" data-lane="V2"></div>
					<div class="lem-lane" data-lane="V1"></div>
					<div class="lem-lane lem-lane--a" data-lane="A1"></div>
					<div class="lem-lane lem-lane--a" data-lane="A2"></div>
					<div class="lem-ph" aria-hidden="true"><i></i></div>
				</div>
			</div>
		</div>
	</div>
	<aside class="lem-panel" aria-label="LazyEditMirror">${panelMarkup()}</aside>
	<div class="lem-toast" role="status" aria-live="polite"></div>
</div>`;
	}

	/* ------------------------------------------------------------------ one demo */
	let seq = 0;
	const reduceMQ = window.matchMedia ? window.matchMedia("(prefers-reduced-motion: reduce)") : null;
	const reduced = () => Boolean(reduceMQ && reduceMQ.matches);

	function fresh() {
		return {
			v1: [
				{ id: "c1", file: "C0002", in: 0, out: 40 },
				{ id: "c2", file: "C0003", in: 0, out: 30 },
			],
			v2: [],
			cut: {},
			undo: [],
			t: 0,
			playing: false,
			master: "V1",
			target: "V2",
			footage: "C0056",
			mode: "audio",
			extra: 0,
			advanced: false,
			analysis: null,
			done: null,
			error: "",
			confirm: false,
			status: ["Open your sequence, pick the side footage and click Analyze.", ""],
			progress: -1,
			nid: 3,
		};
	}

	function mount(root) {
		if (root.lemMounted) {
			return;
		}
		root.lemMounted = true;

		const u = `lem${++seq}`;
		const lang = (root.getAttribute("data-lang") || document.documentElement.lang || "en").toLowerCase();
		const isBn = lang.indexOf("bn") === 0;
		const L = STR[isBn ? "bn" : "en"];

		let S = fresh();
		let engine = "idle";
		let cache = {};
		let logLines = [];
		let logOpen = false;
		let busy = false;
		let epoch = 0;
		let tall = null, stage = null, W = 0, k = 1, pps = 10;
		let raf = 0, last = 0, toastTimer = 0, confirmTimer = 0;
		const els = { V1: new Map(), A1: new Map(), V2: new Map(), junk: new Map() };

		const q = (sel) => stage.querySelector(sel);
		const qa = (sel) => Array.from(stage.querySelectorAll(sel));
		const sleep = (ms) => new Promise((r) => setTimeout(r, reduced() ? Math.min(ms, 60) : ms));

		root.setAttribute("role", "region");
		root.setAttribute("aria-label", L.region);

		/* ---- the sequence ---- */
		function layout() {
			let at = 0;
			return S.v1.map((c) => {
				const o = Object.assign({}, c, { start: at, end: at + (c.out - c.in) });
				at = o.end;
				return o;
			});
		}

		const seqDur = () => S.v1.reduce((n, c) => n + (c.out - c.in), 0);
		const at = (list, t) => list.filter((c) => t >= c.start - 1e-6 && t < c.end - 1e-6)[0] || null;
		const coveredIds = () => new Set(S.v2.map((c) => c.front));
		const v2Layout = () => S.v2.map((c) => Object.assign({}, c, { end: c.start + (c.out - c.in) }));

		function posIn(lay, file, src) {
			for (const c of lay) {
				if (c.file === file && src >= c.in - 1e-6 && src <= c.out + 1e-6) {
					return c.start + (src - c.in);
				}
			}
			return null;
		}

		const snapshot = () => JSON.parse(JSON.stringify({ v1: S.v1, v2: S.v2, cut: S.cut }));

		/* ---- the log ---- */
		function log(line) {
			const d = new Date();
			const p = (n) => String(n).padStart(2, "0");
			logLines.push(`[${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}] ${line}`);
			if (logLines.length > 200) {
				logLines = logLines.slice(-200);
			}
			paintLog();
		}

		function paintLog() {
			if (!stage) {
				return;
			}
			const ta = q(".lem-log");
			ta.value = logLines.join("\n");
			ta.scrollTop = ta.scrollHeight;
		}

		function toast(msg) {
			const el = q(".lem-toast");
			el.textContent = msg;
			el.classList.add("is-on");
			clearTimeout(toastTimer);
			toastTimer = setTimeout(() => el.classList.remove("is-on"), 2600);
		}

		function setStatus(text, kind) {
			S.status = [text, kind || ""];
			paintPanel();
		}

		/* ---- build and fit ---- */
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
			root.classList.add("lem-ready");
			els.V1.clear();
			els.A1.clear();
			els.V2.clear();
			els.junk.clear();
			fit();
			pps = q('[data-lane="V1"]').clientWidth / TL_SECONDS;
			paintRuler();
			paintAll();
			paintLog();
		}

		function fit() {
			k = root.clientWidth / W || 1;
			stage.style.transform = `scale(${k})`;
		}

		function paintAll(prev) {
			paintTimeline(prev);
			paintMonitors();
			paintPanel();
		}

		/* ---- the timeline ---- */
		function paintRuler() {
			const ruler = q(".lem-ruler");
			let html = "";

			for (let s = 0; s <= TL_SECONDS; s += 2) {
				const major = s % 10 === 0;
				html += `<i class="${major ? "is-major" : ""}" style="left:${(s * pps).toFixed(1)}px"></i>`;
				if (major && s < TL_SECONDS - 4) {
					html += `<span style="left:${(s * pps).toFixed(1)}px">${tall ? `0:${String(s).padStart(2, "0")}` : tc(s).slice(0, 8)}</span>`;
				}
			}

			ruler.innerHTML = html;
		}

		function wave(file, a, b, wPx) {
			const n = Math.max(1, Math.floor(wPx / 3));
			let d = "";

			for (let i = 0; i < n; i++) {
				const f = a + ((i + 0.5) / n) * (b - a);
				const h = Math.max(1, 34 * loud(file, f));
				d += `M${(i * 3).toFixed(1)} ${(20 - h / 2).toFixed(1)}h1.8v${h.toFixed(1)}h-1.8z`;
			}

			return `<svg class="lem-wave" viewBox="0 0 ${Math.max(1, n * 3)} 40" preserveAspectRatio="none" aria-hidden="true"><path d="${d}"/></svg>`;
		}

		/* Keyed: a clip keeps its element, so a ripple slides it instead of redrawing it. */
		function upsert(map, lane, key, cls, x, w, from, inner, sig) {
			let el = map.get(key);
			let created = false;

			if (!el) {
				el = document.createElement("div");
				el.className = cls;
				el.setAttribute("data-k", key);
				lane.appendChild(el);
				map.set(key, el);
				created = true;
				if (from != null) {
					el.style.left = `${(from * pps).toFixed(2)}px`;
					el.style.width = `${(w * pps).toFixed(2)}px`;
					void el.offsetWidth;
				}
			}

			if (el.getAttribute("data-sig") !== sig) {
				el.innerHTML = inner;
				el.setAttribute("data-sig", sig);
			}

			el.style.left = `${(x * pps).toFixed(2)}px`;
			el.style.width = `${(w * pps).toFixed(2)}px`;
			return { el, created };
		}

		function prune(map, keep) {
			map.forEach((el, key) => {
				if (!keep.has(key)) {
					el.remove();
					map.delete(key);
				}
			});
		}

		function paintTimeline(prev) {
			const lay = layout();
			const v2 = v2Layout();
			const laneV1 = q('[data-lane="V1"]');
			const laneA1 = q('[data-lane="A1"]');
			const laneV2 = q('[data-lane="V2"]');
			const keepV1 = new Set();
			const keepV2 = new Set();
			const keepJ = new Set();
			const locked = S.v2.length > 0;
			const selected = S.mode === "offset" ? at(lay, S.t) : null;

			lay.forEach((c) => {
				const from = prev ? posIn(prev, c.file, c.in) : null;
				const w = c.out - c.in;
				const label = `<b>${FILES[c.file].name}</b>`;
				const v = upsert(els.V1, laneV1, c.id, "lem-clip lem-clip--v1", c.start, w, from, label, `${c.file}${c.in}${c.out}`);
				v.el.classList.toggle("is-selected", Boolean(selected && selected.id === c.id));
				upsert(els.A1, laneA1, c.id, "lem-clip lem-clip--a1", c.start, w, from, wave(c.file, c.in, c.out, w * pps), `${c.file}${c.in}${c.out}${pps.toFixed(2)}`);
				keepV1.add(c.id);
			});
			prune(els.V1, keepV1);
			prune(els.A1, keepV1);

			JUNK.forEach((j) => {
				if (S.cut[j.id]) {
					return;
				}
				const x = posIn(lay, j.file, j.a);
				if (x == null) {
					return;
				}
				const r = upsert(els.junk, laneV1, j.id, "lem-junk", x, j.b - j.a, prev ? posIn(prev, j.file, j.a) : null, `<span>${esc(L.junk[j.kind])}</span>`, j.kind);
				if (r.created) {
					r.el.setAttribute("role", "button");
					r.el.setAttribute("tabindex", "0");
					r.el.setAttribute("data-junk", j.id);
				}
				r.el.title = locked ? L.locked : L.cutTip;
				r.el.setAttribute("aria-label", `${L.cutTip}: ${L.junk[j.kind]} (${FILES[j.file].name} ${j.a}–${j.b} s)`);
				r.el.classList.toggle("is-locked", locked);
				keepJ.add(j.id);
			});
			prune(els.junk, keepJ);

			v2.forEach((c) => {
				const r = upsert(els.V2, laneV2, c.id, "lem-clip lem-clip--v2", c.start, c.out - c.in, null, `<b>${FILES[c.file].name}</b>`, c.file);
				if (r.created && !reduced()) {
					r.el.classList.add("lem-land");
				}
				keepV2.add(c.id);
			});
			prune(els.V2, keepV2);

			/* Track roles and the cut-all button. */
			qa("[data-tr]").forEach((th) => {
				const tr = th.getAttribute("data-tr");
				th.querySelector("small").textContent = tr === S.master ? L.master : tr === S.target ? L.target : "";
			});

			const left = JUNK.filter((j) => !S.cut[j.id]).length;
			const cutBtn = q(".lem-cutall");
			cutBtn.querySelector("span").textContent = left ? L.cutAll(left) : L.cutNone;
			cutBtn.disabled = !left || locked || busy;

			q(".lem-tl__dur").textContent = tc(seqDur());
			paintHead();
			paintTop();
		}

		function paintHead() {
			q(".lem-ph").style.left = `${(S.t * pps).toFixed(2)}px`;
			const tcEl = q(".lem-tc");
			tcEl.firstElementChild.textContent = tc(S.t);
			tcEl.lastElementChild.textContent = tc(seqDur());
		}

		function ghost(lane, x, w, cls) {
			const g = document.createElement("div");
			g.className = cls;
			g.style.left = `${(x * pps).toFixed(2)}px`;
			g.style.width = `${(w * pps).toFixed(2)}px`;
			q(`[data-lane="${lane}"]`).appendChild(g);
			setTimeout(() => g.remove(), reduced() ? 0 : 520);
		}

		/* ---- the monitors ---- */
		function pose(file, f) {
			const s = segAt(file, f);
			const kind = s ? s[2] : "pause";
			const talk = kind === "say" || kind === "retake";

			return {
				kind,
				line: s ? s[isBn ? 4 : 3] : "",
				mouth: talk ? Math.abs(Math.sin(f * 9.1)) * (0.35 + 0.65 * Math.abs(Math.sin(f * 2.7))) : 0,
				g: kind === "say" ? 0.5 + 0.5 * Math.sin(f * 2.3) * Math.sin(f * 0.9 + 0.6) : kind === "retake" ? 0.65 + 0.3 * Math.sin(f * 7) : 0.08,
				blink: (f * 1.3) % 3.7 < 0.12,
				shake: kind === "retake" ? Math.sin(f * 14) * 4 : 0,
			};
		}

		function drawPerson(mon, side, p) {
			const svg = mon.querySelector(".lem-who");
			const head = svg.querySelector(".lem-head");
			const mouth = svg.querySelector(".lem-mouth");
			const arm = svg.querySelector(".lem-arm");
			const hand = svg.querySelector(".lem-hand");
			const card = svg.querySelector(".lem-card-p");
			const eyes = svg.querySelector(".lem-eyes");
			const sx = side ? 176 : 196;
			let e, h;

			if (p.kind === "cough") {
				e = side ? [190, 148] : [204, 150];
				h = side ? [178, 108] : [166, 108];
			} else if (p.kind === "notes") {
				e = side ? [188, 166] : [208, 166];
				h = side ? [192, 150] : [186, 150];
			} else if (side) {
				e = [188 + 6 * p.g, 160 - 8 * p.g];
				h = [198 + 16 * p.g, 146 - 30 * p.g];
			} else {
				e = [212 + 6 * p.g, 162 - 10 * p.g];
				h = [216 + 20 * p.g, 144 - 34 * p.g];
			}

			arm.setAttribute("d", `M${sx} 140 L${e[0].toFixed(1)} ${e[1].toFixed(1)} L${h[0].toFixed(1)} ${h[1].toFixed(1)}`);
			hand.setAttribute("cx", h[0].toFixed(1));
			hand.setAttribute("cy", h[1].toFixed(1));
			card.setAttribute("opacity", p.kind === "notes" ? "1" : "0");
			head.setAttribute("transform", p.kind === "notes" ? `translate(0 4) rotate(${side ? 10 : 0} 160 110)` : `rotate(${p.shake.toFixed(2)} 160 115)`);
			mouth.setAttribute("height", (2 + p.mouth * 7).toFixed(2));
			mouth.setAttribute("y", (105 - p.mouth * 1.5).toFixed(2));
			mouth.style.opacity = p.kind === "cough" ? "0" : "1";
			eyes.setAttribute("transform", p.blink || p.kind === "notes" ? "translate(0 95) scale(1 0.15) translate(0 -95)" : "");
		}

		function paintMonitors() {
			const lay = layout();
			const front = at(lay, S.t);
			const sideClip = at(v2Layout(), S.t);
			const mf = q('[data-mon="front"]');
			const ms = q('[data-mon="side"]');
			let fNow = null;

			/* Front: the V1 clip under the playhead, at its source time. */
			if (front) {
				fNow = front.in + (S.t - front.start);
				const p = pose(front.file, fNow);
				mf.classList.remove("is-empty");
				drawPerson(mf, false, p);
				mf.querySelector(".lem-cc").textContent = p.line;
				mf.querySelector(".lem-mon__src").textContent = `${FILES[front.file].name} · ${tc(fNow)}`;
			} else {
				mf.classList.add("is-empty");
				mf.querySelector(".lem-empty b").textContent = L.emptyFront;
				mf.querySelector(".lem-mon__src").textContent = "";
			}

			/* Side: the V2 clip, drawn from the moment of the performance it shows. */
			const tag = ms.querySelector(".lem-tag");
			ms.classList.remove("is-city");
			ms.querySelector(".lem-scene").style.display = "";
			ms.querySelector(".lem-city").style.display = "none";

			if (sideClip) {
				const s = sideClip.in + (S.t - sideClip.start);
				const file = FILES[sideClip.file];
				ms.classList.remove("is-empty");
				ms.querySelector(".lem-mon__src").textContent = `${file.name} · ${tc(s)}`;

				if (file.kind !== "side") {
					ms.classList.add("is-city");
					ms.querySelector(".lem-scene").style.display = "none";
					ms.querySelector(".lem-city").style.display = "";
					const car = ms.querySelector(".lem-car");
					car.setAttribute("x", (((s * 40) % 360) - 40).toFixed(1));
					ms.querySelector(".lem-cc").textContent = "";
					tag.textContent = L.wrong;
					tag.className = "lem-tag is-bad";
				} else {
					const g = s - file.offset;
					const p = pose(file.of, g);
					drawPerson(ms, true, p);
					ms.querySelector(".lem-cc").textContent = p.line;
					const same = front && front.file === file.of;
					const d = same ? g - fNow : null;

					if (same && Math.abs(d) <= 0.03) {
						tag.textContent = L.inSync;
						tag.className = "lem-tag is-ok";
					} else {
						tag.textContent = same ? L.off(sign(d)) : L.wrong;
						tag.className = "lem-tag is-warn";
					}
				}
			} else {
				ms.classList.add("is-empty");
				ms.querySelector(".lem-empty b").textContent = L.emptySide;
				ms.querySelector(".lem-empty small").textContent = L.emptySideSub;
				ms.querySelector(".lem-mon__src").textContent = "";
				tag.textContent = "";
				tag.className = "lem-tag";
			}

			const play = q('[data-act="play"]');
			play.innerHTML = S.playing ? ICON.pause : ICON.play;
			play.setAttribute("aria-label", S.playing ? L.pause : L.play);
			play.title = S.playing ? L.pause : L.play;
			stage.classList.toggle("is-playing", S.playing);
		}

		/* ---- playback ---- */
		function tick(now) {
			raf = 0;

			if (!root.isConnected) {
				S.playing = false;
				return;
			}

			if (!S.playing) {
				return;
			}

			const dt = Math.min(0.1, (now - last) / 1000);
			last = now;
			S.t += dt;

			if (S.t >= seqDur()) {
				S.t = seqDur();
				S.playing = false;
			}

			paintHead();
			paintMonitors();

			if (S.playing) {
				raf = requestAnimationFrame(tick);
			}
		}

		function play() {
			if (S.playing) {
				S.playing = false;
				paintMonitors();
				return;
			}

			if (S.t >= seqDur() - 1 / FPS) {
				S.t = 0;
			}

			S.playing = true;
			last = performance.now();
			if (!raf) {
				raf = requestAnimationFrame(tick);
			}
			paintMonitors();
		}

		function seek(t) {
			S.t = clamp(t, 0, seqDur());
			paintHead();
			paintMonitors();
			if (S.mode === "offset") {
				paintTimeline();
			}
		}

		/* ---- editing the front camera ---- */
		function cutOne(j) {
			const lay = layout();
			const i = lay.findIndex((c) => c.file === j.file && c.in <= j.a + 1e-6 && c.out >= j.b - 1e-6);

			if (i < 0) {
				return false;
			}

			const c = lay[i];
			const parts = [];

			if (j.a - c.in > 1e-6) {
				parts.push({ id: c.id, file: c.file, in: c.in, out: j.a });
			}

			if (c.out - j.b > 1e-6) {
				parts.push({ id: parts.length ? `c${S.nid++}` : c.id, file: c.file, in: j.b, out: c.out });
			}

			S.v1.splice(i, 1, ...parts);
			S.cut[j.id] = true;

			/* Premiere keeps the playhead where it was; after the gap it moves with the edit. */
			const cutAt = c.start + (j.a - c.in);
			if (S.t > cutAt) {
				S.t = Math.max(cutAt, S.t - (j.b - j.a));
			}

			return true;
		}

		function cut(ids, label) {
			if (busy) {
				return;
			}

			if (S.v2.length) {
				toast(L.locked);
				return;
			}

			const prev = layout();
			const snap0 = snapshot();
			const list = JUNK.filter((j) => ids.indexOf(j.id) >= 0 && !S.cut[j.id]);
			let n = 0;

			list.forEach((j) => {
				const x = posIn(prev, j.file, j.a);
				if (cutOne(j)) {
					n++;
					if (x != null) {
						ghost("V1", x, j.b - j.a, "lem-ghost");
						ghost("A1", x, j.b - j.a, "lem-ghost lem-ghost--a");
					}
					log(`Ripple delete: ${FILES[j.file].name} ${j.a.toFixed(1)}–${j.b.toFixed(1)} s (${j.kind}).`);
				}
			});

			if (!n) {
				return;
			}

			S.undo.push({ label, snap: snap0 });
			invalidate();
			paintAll(prev);
			if (n > 1 || !S.undo.slice(0, -1).some((x) => x.label === L.uCut || x.label === L.uCutAll)) {
				toast(L.rippled);
			}
		}

		function undo() {
			if (busy) {
				return;
			}

			const last = S.undo.pop();

			if (!last) {
				toast(L.noUndo);
				return;
			}

			const prev = layout();
			S.v1 = last.snap.v1;
			S.v2 = last.snap.v2;
			S.cut = last.snap.cut;
			S.t = clamp(S.t, 0, seqDur());
			S.analysis = null;
			S.done = null;
			S.error = "";
			resetConfirm();
			log(`Edit > Undo: ${last.label}.`);
			S.status = [`Edit > Undo: ${last.label}. Click Analyze when you are ready.`, ""];
			paintAll(prev);
			toast(L.undone(last.label));
		}

		function reset() {
			epoch++;
			busy = false;
			clearTimeout(confirmTimer);
			S = fresh();
			logLines = [];
			stage.querySelector(".lem-scan").hidden = true;
			qa(".lem-ghost, .lem-park").forEach((g) => g.remove());
			syncInputs();
			paintAll();
			paintLog();
		}

		/* Anything that changes what Analyze would find drops a plan already made. */
		function invalidate(msg) {
			resetConfirm();

			if (S.analysis || S.error) {
				S.analysis = null;
				S.error = "";
				S.status = [msg || "The timeline changed since the analysis. Click Analyze again.", "warn"];
			}
		}

		function resetConfirm() {
			clearTimeout(confirmTimer);
			S.confirm = false;
		}

		/* ---- the plan: one side file against every front file on the Master track ---- */
		function plan() {
			const side = FILES[S.footage];
			const lay = layout();
			const done = coveredIds();
			const fronts = [];
			const placements = [];
			const files = [];
			const under = S.mode === "offset" ? at(lay, S.t) : null;

			lay.forEach((c) => {
				if (fronts.indexOf(c.file) < 0) {
					fronts.push(c.file);
				}
			});

			fronts.forEach((fid) => {
				const clips = lay.filter((c) => c.file === fid);
				const row = { name: FILES[fid].name, total: clips.length, already: clips.filter((c) => done.has(c.id)).length, placed: 0, outOfRange: 0, remaining: 0, status: "none", offset: null, score: null, note: "" };
				let off = null;

				if (S.mode === "audio" && side.kind === "side" && side.of === fid) {
					off = side.offset;
					row.score = side.score;
				} else if (S.mode === "offset" && under && under.file === fid) {
					off = 0;
					row.note = "offset entered by hand";
				} else if (S.mode === "timecode") {
					row.note = "no matching source timecode (the cameras were not jam-synced)";
				}

				if (off != null) {
					row.status = "match";
					row.offset = off + S.extra;

					clips.forEach((c) => {
						if (done.has(c.id)) {
							return;
						}
						const sIn = snap(c.in + off + S.extra);
						const sOut = sIn + (c.out - c.in);
						if (sIn < -1e-6 || sOut > side.dur + 1e-6) {
							row.outOfRange++;
						} else {
							placements.push({ front: c.id, file: S.footage, in: sIn, out: sOut, start: c.start });
							row.placed++;
						}
					});

					row.remaining = row.outOfRange;
					if (!row.placed && !row.outOfRange) {
						row.status = "done";
					}
				} else {
					row.remaining = row.total - row.already;
					if (!row.remaining) {
						row.status = "done";
					}
				}

				files.push(row);
			});

			const totals = {
				placed: placements.length,
				pictureSeconds: placements.reduce((n, p) => n + (p.out - p.in), 0),
				already: files.reduce((n, f) => n + f.already, 0),
				remaining: files.reduce((n, f) => n + f.remaining, 0),
				clips: lay.length,
				files: fronts.length,
				cuts: Math.max(0, lay.length - 1),
			};

			return { side: S.footage, files, placements, totals, extra: S.extra };
		}

		async function ensureEngine(ep, loud) {
			if (engine === "ok") {
				return true;
			}

			engine = "starting";
			log("Starting the audio engine (lazyeditmirror://start).");
			if (loud) {
				S.status = ["Starting the audio engine…", ""];
			}
			paintPanel();
			await sleep(900);

			if (ep !== epoch) {
				return false;
			}

			engine = "ok";
			log("Audio engine ready on 127.0.0.1.");
			paintPanel();
			return true;
		}

		async function analyze() {
			if (busy) {
				return;
			}

			resetConfirm();
			S.analysis = null;
			S.done = null;
			S.error = "";
			const ep = epoch;
			const side = FILES[S.footage];

			const fail = (msg) => {
				S.error = msg;
				log(msg);
				S.status = [msg, "error"];
				paintAll();
			};

			if (S.master === S.target) {
				fail("Master and Target are the same track. Pick two different tracks.");
				return;
			}

			if (S.master !== "V1") {
				fail(`${S.master} holds no front-camera edit to copy. Pick V1 as the Master track.`);
				return;
			}

			if (side.kind === "front") {
				fail(`${side.name} is a front file on V1. Pick a side-camera file.`);
				return;
			}

			if (S.mode === "selection") {
				fail("No side clip is selected together with a Master clip. Use Audio match in this demo, or see the manual for this mode.");
				return;
			}

			busy = true;
			log(`Analyze: side ${side.name}, Master ${S.master}, Target ${S.target}, sync reference: ${S.mode}.`);
			paintAll();

			const lay = layout();
			const fronts = [];
			lay.forEach((c) => {
				if (fronts.indexOf(c.file) < 0) {
					fronts.push(c.file);
				}
			});

			if (S.mode === "audio") {
				if (!(await ensureEngine(ep, true))) {
					return;
				}

				const reads = [S.footage].concat(fronts);
				for (let i = 0; i < reads.length; i++) {
					const name = FILES[reads[i]].name;
					const hit = cache[name];
					S.progress = i / (reads.length + 1);
					setStatus(`Reading audio: ${name}${hit ? " (cached)" : "…"}`);
					log(`Engine: ${name} ${hit ? "from the cache" : "decoded to 8 kHz mono, 200 Hz loudness envelope"}.`);
					await sleep(hit ? 140 : 560);
					if (ep !== epoch) {
						return;
					}
					cache[name] = true;
				}

				S.progress = reads.length / (reads.length + 1);
				setStatus("Matching…");
				const matched = side.kind === "side" && fronts.indexOf(side.of) >= 0 ? side.of : null;
				await scan(matched || fronts[0], matched ? side : null, ep);
				if (ep !== epoch) {
					return;
				}

				fronts.forEach((fid) => {
					log(side.kind === "side" && side.of === fid
						? `${FILES[fid].name}: side = front ${sign(side.offset)} (correlation ${side.score.toFixed(2)}).`
						: `${FILES[fid].name}: no match in ${side.name} (best peak ${(0.12 + 0.1 * hash(fid.length + side.dur)).toFixed(2)}, below the bar).`);
				});
			} else {
				S.progress = 0.5;
				setStatus("Reading the sequence…");
				await sleep(380);
				if (ep !== epoch) {
					return;
				}
			}

			const an = plan();
			S.analysis = an;
			S.progress = -1;
			busy = false;
			log(`Plan: ${an.totals.placed} to place, ${an.totals.already} done, ${an.totals.remaining} waiting.`);

			if (an.placements.length) {
				S.status = [`Ready: ${an.placements.length} clips to place on ${S.target}. Click Sync.`, "ok"];
			} else {
				S.status = [`Nothing to place from ${side.name}.`, "warn"];
			}

			paintAll();
		}

		/* The engine at work, drawn over the monitors: the side lane slides into place. */
		function scan(fid, side, ep) {
			const box = q(".lem-scan");
			const svg = box.querySelector("svg");
			const X0 = 150, X1 = 626;
			const D = 120;
			const peakX = X0 + (X1 - X0) * 0.62;
			const bars = (shift, noisy, end) => {
				let d = "";
				for (let x = X0; x < end; x += 4) {
					const f = 4 + (x - X0 - shift) / 40;
					const h = 4 + 40 * (noisy ? 0.15 + 0.6 * hash(Math.floor(x / 4) * 3.3) * Math.abs(Math.sin(x * 0.05)) : loud(fid, f));
					d += `M${x} ${(-h / 2).toFixed(1)}h2.4v${h.toFixed(1)}h-2.4z`;
				}
				return d;
			};
			const corr = (x) => side
				? 0.84 * Math.exp(-Math.pow(x - peakX, 2) / (2 * 11 * 11)) + 0.14 * Math.abs(Math.sin(x * 0.09) * Math.sin(x * 0.031 + 2)) * hash(Math.floor(x / 6))
				: 0.1 + 0.16 * Math.abs(Math.sin(x * 0.07) * Math.sin(x * 0.023 + 1)) * hash(Math.floor(x / 5));

			svg.querySelector(".lem-scan__ff").textContent = FILES[fid].name;
			svg.querySelector(".lem-scan__sf").textContent = FILES[S.footage].name;
			const front = svg.querySelector(".lem-scan__front");
			const sideP = svg.querySelector(".lem-scan__side");
			front.setAttribute("d", bars(0, false, X1));
			front.setAttribute("transform", "translate(0 64)");
			sideP.setAttribute("d", bars(0, !side, X1 + D + 4));
			const curve = svg.querySelector(".lem-scan__curve");
			const dot = svg.querySelector(".lem-scan__dot");
			const chip = svg.querySelector(".lem-scan__chip");
			const state = svg.querySelector(".lem-scan__state");
			dot.setAttribute("opacity", "0");
			chip.setAttribute("opacity", "0");
			chip.classList.toggle("is-bad", !side);
			chip.querySelector("text").textContent = side ? `side = front ${sign(side.offset)} · correlation ${side.score.toFixed(2)}` : L.noPeak;
			box.classList.remove("is-out");
			box.hidden = false;

			const dur = reduced() ? 1 : 1500;
			const t0 = performance.now();

			return new Promise((resolve) => {
				const frame = (now) => {
					if (ep !== epoch || !root.isConnected) {
						box.hidden = true;
						resolve();
						return;
					}

					const p = clamp((now - t0) / dur);
					const off = side ? D * (1 - ease(p)) + 18 * Math.sin(p * 30) * (1 - p) * 0.4 : 40 * Math.sin(p * 9) + 20 * Math.sin(p * 23);
					sideP.setAttribute("transform", `translate(${(-D + off).toFixed(1)} 126)`);
					if (!side) {
						sideP.setAttribute("transform", `translate(${(-D / 2 + off).toFixed(1)} 126)`);
					}

					let d = "";
					const xe = X0 + (X1 - X0) * p;
					for (let x = X0; x <= xe; x += 2) {
						d += `${x === X0 ? "M" : "L"}${x} ${(188 - 34 * corr(x)).toFixed(1)}`;
					}
					curve.setAttribute("d", d);
					state.textContent = p < 1 ? L.listening : "";

					if (p < 1) {
						nextFrame(frame);
						return;
					}

					if (side) {
						dot.setAttribute("cx", peakX.toFixed(1));
						dot.setAttribute("cy", (188 - 34 * corr(peakX)).toFixed(1));
						dot.setAttribute("opacity", "1");
					}
					chip.setAttribute("opacity", "1");

					setTimeout(() => {
						if (ep !== epoch) {
							resolve();
							return;
						}
						box.classList.add("is-out");
						setTimeout(() => {
							box.hidden = true;
							box.classList.remove("is-out");
						}, reduced() ? 0 : 320);
						resolve();
					}, reduced() ? 500 : 1000);
				};
				nextFrame(frame);
			});
		}

		async function sync() {
			const an = S.analysis;

			if (busy || !an || !an.placements.length) {
				return;
			}

			/* Two clicks: the first arms the button, the second (within 8 s) runs. */
			if (!S.confirm) {
				S.confirm = true;
				S.status = [`Click again to place the clips on ${S.target}. Every step is undoable.`, "warn"];
				clearTimeout(confirmTimer);
				confirmTimer = setTimeout(() => {
					S.confirm = false;
					if (S.analysis) {
						S.status = [`Ready: ${S.analysis.placements.length} clips to place on ${S.target}. Click Sync.`, "ok"];
					}
					paintPanel();
				}, 8000);
				paintPanel();
				return;
			}

			resetConfirm();
			busy = true;
			const ep = epoch;
			const n = an.placements.length;
			log(`Sync started: ${n} clips -> ${S.target}.`);
			S.progress = 0;
			setStatus("Re-reading the sequence…");
			paintTimeline();
			await sleep(300);
			if (ep !== epoch) {
				return;
			}

			for (let i = 0; i < n; i++) {
				const p = an.placements[i];
				S.progress = i / n;
				setStatus(`Placing clips… ${i + 1} / ${n}`);
				S.undo.push({ label: L.uPlace, snap: snapshot() });
				S.v2.push({ id: `s${S.nid++}`, file: p.file, in: p.in, out: p.out, start: p.start, front: p.front });
				S.v2.sort((a, b) => a.start - b.start);
				ghost("A2", p.start, p.out - p.in, "lem-park");
				log(`Placed ${FILES[p.file].name} [${p.in.toFixed(2)}–${p.out.toFixed(2)} s] at ${tc(p.start)}; side audio parked on A2 and removed.`);
				paintTimeline();
				paintMonitors();
				await sleep(280);
				if (ep !== epoch) {
					return;
				}
			}

			const left = layout().length - coveredIds().size;
			const byFile = {};
			const done = coveredIds();
			layout().forEach((c) => {
				if (!done.has(c.id)) {
					byFile[FILES[c.file].name] = (byFile[FILES[c.file].name] || 0) + 1;
				}
			});

			log(`Read back ${S.target}: ${n} / ${n} clips verified.`);
			S.done = { an, placed: n, remaining: left, byFile };
			S.analysis = null;
			S.progress = -1;
			busy = false;
			S.status = [`Placed ${n} clips on ${S.target} (${plural(n, "undo step")}).` + (left ? ` ${left} front clips still need a side file.` : " Every front clip is covered."), "ok"];
			paintAll();
		}

		/* ---- the panel ---- */
		function fileRow(f, synced) {
			let kind = "wait", sub = "", count = "";

			if (f.status === "done" || (synced && f.placed && f.remaining === 0)) {
				kind = "done";
				sub = synced && f.placed ? "placed in this pass" : "already on the side track";
				count = `${f.total} done`;
			} else if (f.status === "match") {
				kind = "ok";
				sub = f.note ? `${f.note}: side = front ${sign(f.offset)}` : `side = front ${sign(f.offset)}` + (f.score != null ? ` (correlation ${f.score.toFixed(2)})` : "");
				count = synced ? `${f.placed} placed` : `${f.placed} to place`;
				if (f.outOfRange) {
					count += `, ${f.outOfRange} outside`;
				}
				if (f.already) {
					count += `, ${f.already} done`;
				}
			} else {
				sub = f.note || "no match in this side file";
				count = `${f.remaining} waiting`;
			}

			return `<div class="lem-frow lem-frow--${kind}"><span class="lem-fdot"></span><div class="lem-fmain"><div class="lem-fname">${esc(f.name)}</div><div class="lem-fsub">${esc(sub)}</div></div><span class="lem-fcount">${esc(count)}</span></div>`;
		}

		function paintResult() {
			const card = q(".lem-result");
			const title = q(".lem-rtitle");
			const files = q(".lem-rfiles");
			const totals = q(".lem-rtotals");
			const notes = q(".lem-rnotes");

			card.className = "lem-card lem-result";
			totals.className = "lem-rtotals";

			if (S.error) {
				title.textContent = "Could not analyze";
				files.innerHTML = "";
				totals.textContent = S.error;
				totals.className = "lem-rtotals is-error";
				notes.textContent = "";
				card.classList.add("lem-result--error");
				card.hidden = false;
				return;
			}

			const an = S.done ? S.done.an : S.analysis;

			if (!an) {
				card.hidden = true;
				return;
			}

			const synced = Boolean(S.done);
			const sideName = FILES[an.side].name;
			title.textContent = synced ? `Done: ${sideName}` : `Plan for ${sideName}`;
			files.innerHTML = an.files.map((f) => fileRow(f, synced)).join("");

			if (synced) {
				const r = S.done;
				totals.textContent = `Placed ${plural(r.placed, "clip")} (verified). ` + (r.remaining
					? `${plural(r.remaining, "front clip")} still waiting: ${Object.keys(r.byFile).map((k) => `${k} ${r.byFile[k]}`).join(", ")}. Pick the next side file.`
					: "Every front clip is covered.");
				card.classList.add("lem-result--ok");
			} else {
				const t = an.totals;
				const parts = [`${plural(t.placed, "clip")} to place (${t.pictureSeconds.toFixed(1)} s)`];
				if (t.already) {
					parts.push(`${t.already} done`);
				}
				if (t.remaining) {
					parts.push(`${t.remaining} waiting`);
				}
				totals.textContent = `${parts.join(" · ")}. Master: ${t.clips} clips from ${plural(t.files, "file")}, ${t.cuts} cuts, 0 gaps.`;
			}

			const n = [];
			if (!synced && an.placements.length) {
				n.push("Side audio is parked on A2 for a moment and removed.");
			}
			if (an.extra) {
				n.push(`Extra offset ${sign(an.extra)} applied.`);
			}
			notes.textContent = n.join(" ");
			card.hidden = false;
		}

		function paintPanel() {
			if (!stage) {
				return;
			}

			const pill = q(".lem-pill");
			pill.setAttribute("data-engine", engine);
			q(".lem-pill__t").textContent = engine === "ok" ? "Audio engine ready" : engine === "starting" ? "Starting audio engine" : "Audio engine";

			const an = S.analysis;
			const syncBtn = q('[data-act="sync"]');
			const n = an ? an.placements.length : 0;
			syncBtn.textContent = S.confirm ? `Confirm: place ${n} clips` : an ? `Sync ${n} clips` : "Sync";
			syncBtn.disabled = busy || !n;
			syncBtn.classList.toggle("lem-btn--danger", S.confirm);
			q('[data-act="analyze"]').disabled = busy;

			const prog = q(".lem-progress");
			prog.hidden = S.progress < 0;
			prog.firstElementChild.style.width = `${Math.round(clamp(S.progress) * 100)}%`;

			const st = q(".lem-status");
			st.textContent = S.status[0];
			st.className = "lem-status" + (S.status[1] ? ` lem-status--${S.status[1]}` : "");

			q(".lem-adv").hidden = !S.advanced;
			const advBtn = q('[data-act="advanced"]');
			advBtn.textContent = S.advanced ? "Hide advanced options" : "Show advanced options";
			advBtn.setAttribute("aria-expanded", String(S.advanced));
			q(".lem-modehint").textContent = MODE_HINTS[S.mode];

			q(".lem-logbody").hidden = !logOpen;
			const logBtn = q('[data-act="log"]');
			logBtn.textContent = logOpen ? "Hide" : "Show";
			logBtn.setAttribute("aria-expanded", String(logOpen));

			paintResult();
			paintTop();
		}

		function nextSide() {
			const done = coveredIds();
			const need = {};
			layout().forEach((c) => {
				if (!done.has(c.id)) {
					need[c.file] = true;
				}
			});
			const id = FOOTAGE.filter((f) => FILES[f].kind === "side" && need[FILES[f].of])[0];
			return id ? FILES[id].name : "";
		}

		function paintTop() {
			if (!stage) {
				return;
			}

			const undoBtn = q('[data-act="undo"]');
			const last = S.undo[S.undo.length - 1];
			undoBtn.disabled = busy || !last;
			undoBtn.title = last ? `${L.undo}: ${last.label}` : L.undo;
			q('[data-act="reset"]').disabled = false;

			const lay = layout();
			const done = coveredIds();
			const all = lay.length && lay.every((c) => done.has(c.id));
			const left = JUNK.filter((j) => !S.cut[j.id]).length;
			let n = "2", text = L.g.analyze;

			if (busy) {
				n = "…";
				text = L.g.busy;
			} else if (all) {
				n = "✓";
				text = L.g.done;
			} else if (S.analysis && S.analysis.placements.length) {
				n = "3";
				text = S.confirm ? L.g.confirm : L.g.sync(S.analysis.placements.length);
			} else if (S.analysis) {
				text = L.g.nothing;
			} else if (!S.v2.length && left) {
				n = "1";
				text = L.g.cut;
			} else if (S.v2.length && nextSide()) {
				text = L.g.next(nextSide());
			}

			q(".lem-guide__n").textContent = isBn && /\d/.test(n) ? toBn(n) : n;
			q(".lem-guide__t").textContent = text;
			q(".lem-guide").classList.toggle("is-done", n === "✓");
		}

		function syncInputs() {
			const set = (f, v) => {
				const el = q(`[data-f="${f}"]`);
				if (el) {
					el.value = v;
				}
			};
			set("master", S.master);
			set("target", S.target);
			set("footage", S.footage);
			set("mode", S.mode);
			set("extra", String(S.extra));
		}

		/* ---- wiring ---- */
		root.addEventListener("click", (e) => {
			const t = e.target instanceof Element ? e.target : null;

			if (!t || !stage) {
				return;
			}

			const junk = t.closest("[data-junk]");
			if (junk) {
				const j = JUNK.filter((x) => x.id === junk.getAttribute("data-junk"))[0];
				if (j) {
					cut([j.id], L.uCut);
				}
				return;
			}

			const btn = t.closest("[data-act]");
			if (!btn || btn.disabled) {
				return;
			}

			switch (btn.getAttribute("data-act")) {
				case "play":
					play();
					break;
				case "home":
					seek(0);
					break;
				case "cutall":
					cut(JUNK.map((j) => j.id), L.uCutAll);
					break;
				case "undo":
					undo();
					break;
				case "reset":
					reset();
					break;
				case "analyze":
					analyze();
					break;
				case "sync":
					sync();
					break;
				case "advanced":
					S.advanced = !S.advanced;
					paintPanel();
					break;
				case "refresh":
					invalidate("Tracks and project re-read. Click Analyze again.");
					if (!S.analysis && S.status[1] !== "warn") {
						S.status = ["Tracks and project re-read.", ""];
					}
					log("Refresh: 2 video tracks, 2 audio tracks, 5 media files in the project.");
					paintAll();
					toast(L.refreshed);
					break;
				case "engine":
					if (engine === "ok") {
						toast("Audio engine ready");
					} else if (!busy) {
						ensureEngine(epoch, false);
					}
					break;
				case "folder":
					toast(L.folder);
					break;
				case "log":
					logOpen = !logOpen;
					paintPanel();
					break;
				case "copylog":
					try {
						navigator.clipboard.writeText(logLines.join("\n")).then(() => toast(L.copied), () => toast(L.copied));
					} catch (err) {
						toast(L.copied);
					}
					S.status = ["Log copied to the clipboard.", "ok"];
					paintPanel();
					break;
				case "savelog":
					toast(L.saved);
					break;
				case "clearlog":
					logLines = [];
					paintLog();
					break;
			}
		});

		root.addEventListener("change", (e) => {
			const t = e.target instanceof Element ? e.target : null;
			const f = t ? t.getAttribute("data-f") : null;

			if (!f) {
				return;
			}

			if (f === "extra") {
				const v = parseFloat(String(t.value).replace(",", "."));
				S.extra = Number.isFinite(v) ? clamp(Math.round(v * 1000) / 1000, -10, 10) : 0;
				t.value = String(S.extra);
			} else {
				S[f] = t.value;
			}

			invalidate("Settings changed. Click Analyze again.");
			if (S.done && f !== "extra" && f !== "mode") {
				S.done = null;
			}
			paintAll();
		});

		root.addEventListener("keydown", (e) => {
			const t = e.target instanceof Element ? e.target : null;

			if (!t) {
				return;
			}

			const junk = t.closest("[data-junk]");
			if (junk && (e.key === "Enter" || e.key === " ")) {
				e.preventDefault();
				junk.click();
				return;
			}

			if (t.closest("input, select, textarea")) {
				return;
			}

			if ((e.ctrlKey || e.metaKey) && (e.key === "z" || e.key === "Z")) {
				e.preventDefault();
				undo();
				return;
			}

			if (e.ctrlKey || e.metaKey || e.altKey || t.closest("button, a")) {
				return;
			}

			if (e.key === " " || e.key === "k" || e.key === "K") {
				e.preventDefault();
				play();
			} else if (e.key === "ArrowLeft" || e.key === "ArrowRight") {
				e.preventDefault();
				S.playing = false;
				seek(S.t + (e.key === "ArrowLeft" ? -1 : 1) * (e.shiftKey ? 1 : 1 / FPS));
			} else if (e.key === "Home") {
				e.preventDefault();
				seek(0);
			} else if (e.key === "End") {
				e.preventDefault();
				S.playing = false;
				seek(seqDur());
			}
		});

		/* Click or drag on the ruler or an empty part of a lane moves the playhead. */
		let dragging = false;
		const toTime = (clientX) => {
			const lanes = q(".lem-lanes").getBoundingClientRect();
			return (clientX - lanes.left) / k / pps;
		};

		root.addEventListener("pointerdown", (e) => {
			const t = e.target instanceof Element ? e.target : null;

			if (!t || !stage || e.button !== 0 || t.closest("[data-junk]") || !t.closest(".lem-lanes")) {
				return;
			}

			dragging = true;
			stage.classList.add("is-scrubbing");
			try {
				t.setPointerCapture(e.pointerId);
			} catch (err) {
				/* not every element can capture */
			}
			seek(toTime(e.clientX));
		});

		root.addEventListener("pointermove", (e) => {
			if (dragging) {
				seek(toTime(e.clientX));
			}
		});

		const endDrag = () => {
			dragging = false;
			if (stage) {
				stage.classList.remove("is-scrubbing");
			}
		};
		root.addEventListener("pointerup", endDrag);
		root.addEventListener("pointercancel", endDrag);

		build();
		log(`LazyEditMirror ${VERSION} ready. Sequence 01: V1 ${layout().length} clips, V2 empty.`);

		if ("ResizeObserver" in window) {
			new ResizeObserver(() => build()).observe(root.parentElement || root);
		} else {
			window.addEventListener("resize", build);
		}
	}

	/* ------------------------------------------------------------------ start when near */
	function auto() {
		const roots = Array.from(document.querySelectorAll("[data-lazyeditmirror-demo]"));

		if (!roots.length) {
			return;
		}

		if (!("IntersectionObserver" in window)) {
			roots.forEach(mount);
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

		roots.forEach((el) => io.observe(el));
	}

	window.LazyEditMirrorDemo = { mount };

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", auto);
	} else {
		auto();
	}
})();
