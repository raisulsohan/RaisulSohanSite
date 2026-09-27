/*
 * LazyKick interactive demo.
 *
 * The LazyKick panel docked in a mock Premiere Pro or After Effects, with real buttons: paste a
 * script into the note, press Time to Audio and every line is stamped with the moment it is
 * spoken, make subtitles, play the timeline and watch the note follow the voice word by word,
 * paste an image onto the timeline, and link a folder to a watch bin. The timing is LazyKick's
 * own code (client/align.js, injected at build) run over a voiceover drawn for the demo, so the
 * timecodes are worked out, not typed in. The screenshot and the frame are the demo animation's
 * drawing code, also injected at build.
 *
 * Two stages, scaled to fit: a wide one (1280 x 760) and a tall one for phones and narrow
 * columns (440 x 1120). It is a simulation: no file is read, saved or imported.
 *
 * In the theme: a standalone page bundle (assets/lazykick-demo.min.js), enqueued only on a
 * project whose kit names it (rs_project_demo_kit()); page-portfolio.php prints
 * <div class="rs-demo-wrap lkd-wrap"><div class="lkd rs-demo-mount" data-lazykick-demo …>, and
 * the portfolio's pop-up player mounts the same bundle through window.LazyKickDemo.
 * It starts when it scrolls near, and its box is sized by CSS alone, so a cached page never
 * shifts.
 */
(() => {
	"use strict";

	/* The screenshot on the clipboard and the frame in the viewer, from the demo animation. */
	function artShot(p) {
  return `
<defs>
  <linearGradient id="${p}-bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f6efe2"/><stop offset="1" stop-color="#e3d6c0"/></linearGradient>
  <linearGradient id="${p}-board" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#8d6544"/><stop offset="1" stop-color="#5d3f28"/></linearGradient>
  <radialGradient id="${p}-apple" cx="0.35" cy="0.3" r="0.8"><stop offset="0" stop-color="#ff8574"/><stop offset="0.45" stop-color="#e3402f"/><stop offset="1" stop-color="#8d1414"/></radialGradient>
  <radialGradient id="${p}-lime" cx="0.35" cy="0.3" r="0.8"><stop offset="0" stop-color="#c6ef7a"/><stop offset="1" stop-color="#4e8f2a"/></radialGradient>
  <radialGradient id="${p}-orange" cx="0.35" cy="0.3" r="0.8"><stop offset="0" stop-color="#ffc46b"/><stop offset="1" stop-color="#d1742a"/></radialGradient>
</defs>
<rect width="400" height="250" fill="url(#${p}-bg)"/>
<ellipse cx="200" cy="232" rx="185" ry="26" fill="#000000" opacity="0.08"/>
<rect x="46" y="150" width="308" height="74" rx="12" fill="url(#${p}-board)"/>
<circle cx="132" cy="146" r="44" fill="url(#${p}-apple)"/>
<path d="M132 104 q10 -16 26 -14 q-8 16 -24 18 Z" fill="#4f9a38"/>
<circle cx="208" cy="158" r="31" fill="url(#${p}-lime)"/>
<circle cx="264" cy="150" r="37" fill="url(#${p}-orange)"/>
<rect x="30" y="24" width="150" height="16" rx="8" fill="#c9bba2"/>
<rect x="30" y="50" width="96" height="11" rx="5.5" fill="#d6cab5"/>
<g stroke="#e0342a" stroke-width="4.5" fill="none" stroke-linecap="round">
  <ellipse cx="132" cy="146" rx="62" ry="58" transform="rotate(-8 132 146)"/>
  <path d="M196 96 L262 62"/>
  <path d="M262 62 l-18 3 M262 62 l-4 17"/>
</g>
<text x="270" y="56" fill="#e0342a" font-size="21" font-weight="700" font-style="italic">bigger!</text>`;
}

function artFrame(p) {
  return `
<defs>
  <linearGradient id="${p}-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#123227"/><stop offset="1" stop-color="#07130f"/></linearGradient>
  <linearGradient id="${p}-strip" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#7bd67a"/><stop offset="1" stop-color="#2f8f5b"/></linearGradient>
</defs>
<rect width="900" height="506" fill="url(#${p}-sky)"/>
<circle cx="700" cy="150" r="190" fill="#1d5c40" opacity="0.5"/>
<circle cx="760" cy="330" r="120" fill="#1a6b46" opacity="0.35"/>
<circle cx="672" cy="232" r="86" fill="#e3402f"/>
<path d="M672 146 q18 -30 50 -26 q-14 30 -46 32 Z" fill="#4f9a38"/>
<circle cx="774" cy="296" r="54" fill="#f0a838"/>
<circle cx="596" cy="312" r="44" fill="#8cc63f"/>
<rect x="70" y="150" width="14" height="118" rx="7" fill="url(#${p}-strip)"/>
<text x="112" y="212" fill="#f4f7ef" font-size="62" font-weight="800" letter-spacing="-1">FRESH PICKS</text>
<text x="114" y="252" fill="#9fd9a8" font-size="21" font-weight="600" letter-spacing="5">SPRING CAMPAIGN 2026</text>
<rect x="112" y="300" width="196" height="46" rx="23" fill="#7bd67a"/>
<text x="210" y="330" fill="#08281c" font-size="18" font-weight="700" text-anchor="middle">Order today</text>
<rect x="0" y="466" width="900" height="40" fill="#08130e" opacity="0.8"/>
<text x="36" y="492" fill="#7f9c8c" font-size="15" font-weight="500">freshpicks.co</text>`;
}

	/* LazyKick's script timing, exactly as the panel ships it. */
	const ALIGN_HOST = {};
/*
========================================================================
  LazyKick — Script-to-Audio Alignment (align.js)
  Developed By: RaisulSohan
  Website: https://raisulsohan.com
  Description: Times the lines of a note to a voiceover, and turns timed
               lines into subtitles.
  Copyright (c) 2026 Raisul Sohan. Free and open source under the MIT License.
========================================================================

  No speech recognition and no downloads: the voiceover's pauses are found
  from its loudness, and the lines are laid over the speech between them in
  proportion to how long each line takes to say. That works for any language
  (Bengali included) as long as the audio follows the script, which is what a
  read voiceover does. Every function is pure, so tools/test-align.mjs runs
  them in plain Node; the panel loads this file before main.js.

  Same rules as main.js: plain ES5 for CEP 9's Chromium 61 / Node 8.
*/

(function (root, factory) {
    var api = factory();
    // Always the page global: CEP's mixed Node context defines module and
    // exports on the page as well, so they cannot tell a panel from Node.
    if (root) root.LazyAlign = api;
    if (typeof module === "object" && module && module.exports) module.exports = api;
})(ALIGN_HOST, function () {
    "use strict";

    // ============================================================
    // WAV loudness envelope (streamed, so a long mix never sits in memory)
    // ============================================================

    /**
     * Feed a WAV file chunk by chunk (Node Buffers or Uint8Arrays), then call
     * finish() for { step, values, duration }: the RMS loudness of each
     * `windowSeconds` slice, all channels mixed. PCM 8/16/24/32-bit and
     * 32/64-bit float, including WAVE_FORMAT_EXTENSIBLE, as Premiere writes.
     */
    function WavEnvelope(windowSeconds) {
        this.windowSeconds = windowSeconds || 0.01;
        this.head = null;       // bytes kept until the header is complete
        this.format = null;
        this.carry = null;      // part of a sample frame split across chunks
        this.remaining = 0;     // data bytes still expected (Infinity if unknown)
        this.values = [];
        this.sum = 0;
        this.count = 0;
        this.error = "";
    }

    function concatBytes(a, b) {
        if (!a || !a.length) return b;
        var out = new Uint8Array(a.length + b.length);
        out.set(a, 0);
        out.set(b, a.length);
        return out;
    }

    function ascii(bytes, at, n) {
        var s = "";
        for (var i = 0; i < n; i++) s += String.fromCharCode(bytes[at + i]);
        return s;
    }

    function u16(bytes, at) { return bytes[at] | (bytes[at + 1] << 8); }
    function u32(bytes, at) { return (bytes[at] | (bytes[at + 1] << 8) | (bytes[at + 2] << 16)) + bytes[at + 3] * 16777216; }

    /** Reads the header once the "data" chunk starts; returns the offset of the first sample, or -1 for "need more". */
    WavEnvelope.prototype.readHeader = function (bytes) {
        if (bytes.length < 12) return -1;
        var riff = ascii(bytes, 0, 4);
        if ((riff !== "RIFF" && riff !== "RF64") || ascii(bytes, 8, 4) !== "WAVE") {
            this.error = "Not a WAV file";
            return -2;
        }
        var at = 12;
        while (at + 8 <= bytes.length) {
            var id = ascii(bytes, at, 4);
            var size = u32(bytes, at + 4);
            if (id === "data") {
                if (!this.format) {
                    this.error = "WAV data before its format";
                    return -2;
                }
                this.remaining = (size === 0 || size === 0xFFFFFFFF || riff === "RF64") ? Infinity : size;
                return at + 8;
            }
            if (at + 8 + size > bytes.length) return -1;
            if (id === "fmt ") {
                var tag = u16(bytes, at + 8);
                var bits = u16(bytes, at + 22);
                if (tag === 0xFFFE && size >= 26) tag = u16(bytes, at + 32); // extensible: sub-format
                this.format = {
                    tag: tag,
                    channels: u16(bytes, at + 10),
                    rate: u32(bytes, at + 12),
                    bits: bits,
                    frameBytes: u16(bytes, at + 20)
                };
                if (!this.format.channels || !this.format.rate || (tag !== 1 && tag !== 3)) {
                    this.error = "Unsupported WAV format";
                    return -2;
                }
                if (!this.format.frameBytes) this.format.frameBytes = this.format.channels * (bits >> 3);
                this.windowFrames = Math.max(1, Math.round(this.format.rate * this.windowSeconds));
            }
            at += 8 + size + (size & 1);
        }
        return -1;
    };

    WavEnvelope.prototype.push = function (chunk) {
        if (this.error) return;
        var bytes = chunk instanceof Uint8Array ? chunk : new Uint8Array(chunk);
        if (!this.format || this.head) {
            this.head = concatBytes(this.head, bytes);
            var start = this.readHeader(this.head);
            if (start === -1) return;
            if (start < 0) {
                this.head = null;
                return;
            }
            bytes = this.head.subarray(start);
            this.head = null;
        }
        // Count only new bytes against the data size, then join the part
        // frame left over from the last chunk.
        if (this.remaining !== Infinity) {
            if (this.remaining <= 0) return;
            if (bytes.length > this.remaining) bytes = bytes.subarray(0, this.remaining);
            this.remaining -= bytes.length;
        }
        if (this.carry) {
            bytes = concatBytes(this.carry, bytes);
            this.carry = null;
        }

        var f = this.format;
        var frame = f.frameBytes;
        var usable = bytes.length - (bytes.length % frame);
        if (usable < bytes.length) this.carry = bytes.slice(usable);
        var view = new DataView(bytes.buffer, bytes.byteOffset, usable);
        var width = f.bits >> 3;
        var channels = f.channels;
        var perWindow = this.windowFrames;
        for (var off = 0; off < usable; off += frame) {
            var mix = 0;
            for (var c = 0; c < channels; c++) {
                var p = off + c * width;
                var v;
                if (f.tag === 3) {
                    v = width === 8 ? view.getFloat64(p, true) : view.getFloat32(p, true);
                } else if (width === 2) {
                    v = view.getInt16(p, true) / 32768;
                } else if (width === 3) {
                    var n = bytes[p] | (bytes[p + 1] << 8) | (bytes[p + 2] << 16);
                    v = (n & 0x800000 ? n - 0x1000000 : n) / 8388608;
                } else if (width === 4) {
                    v = view.getInt32(p, true) / 2147483648;
                } else {
                    v = (bytes[p] - 128) / 128;
                }
                mix += v;
            }
            mix /= channels;
            this.sum += mix * mix;
            if (++this.count === perWindow) {
                this.values.push(Math.sqrt(this.sum / this.count));
                this.sum = 0;
                this.count = 0;
            }
        }
    };

    WavEnvelope.prototype.finish = function () {
        if (this.count > 0) {
            this.values.push(Math.sqrt(this.sum / this.count));
            this.sum = 0;
            this.count = 0;
        }
        var step = this.format ? this.windowFrames / this.format.rate : this.windowSeconds;
        return { step: step, values: this.values, duration: this.values.length * step, error: this.error || (this.format ? "" : "No audio in the file") };
    };

    // ============================================================
    // Speech and pauses
    // ============================================================

    function percentile(sorted, q) {
        if (!sorted.length) return 0;
        var i = Math.min(sorted.length - 1, Math.max(0, Math.round(q * (sorted.length - 1))));
        return sorted[i];
    }

    function toDb(values, step, smoothSeconds) {
        var db = new Array(values.length);
        for (var i = 0; i < values.length; i++) db[i] = 20 * Math.log(Math.max(values[i], 1e-7)) / Math.LN10;
        var half = Math.max(0, Math.round((smoothSeconds / step - 1) / 2));
        if (!half) return db;
        var out = new Array(db.length);
        var sum = 0;
        var lo = 0;
        var hi = -1;
        for (var k = 0; k < db.length; k++) {
            while (hi < Math.min(db.length - 1, k + half)) sum += db[++hi];
            while (lo < k - half) sum -= db[lo++];
            out[k] = sum / (hi - lo + 1);
        }
        return out;
    }

    /**
     * Where the voice is. envelope: { step, values, start? } (values are
     * linear loudness, any scale). Returns { segments: [{ s, e }],
     * dips: [seconds] } in seconds from the envelope's start. Dips are the
     * quietest moments inside long stretches of speech: breaths too short to
     * count as pauses, used only when there are more lines than pauses.
     */
    function findSpeech(envelope, opts) {
        opts = opts || {};
        var step = envelope.step;
        var t0 = envelope.start || 0;
        var minPause = opts.minPause || 0.12;
        var minSpeech = opts.minSpeech || 0.08;
        var db = toDb(envelope.values || [], step, opts.smooth || 0.05);
        var result = { segments: [], dips: [], floor: 0, peak: 0 };
        if (!db.length) return result;

        var sorted = db.slice().sort(function (a, b) { return a - b; });
        var floor = percentile(sorted, 0.1);
        var peak = percentile(sorted, 0.95);
        result.floor = floor;
        result.peak = peak;
        if (peak - floor < 6) {
            // No real dynamics: one stretch over everything above the floor.
            var first = -1;
            var last = -1;
            for (var q = 0; q < db.length; q++) {
                if (db[q] > floor + 1) {
                    if (first < 0) first = q;
                    last = q;
                }
            }
            if (first >= 0) result.segments.push({ s: t0 + first * step, e: t0 + (last + 1) * step });
            return result;
        }

        var on = floor + 0.38 * (peak - floor);
        var off = floor + 0.28 * (peak - floor);
        var raw = [];
        var inSpeech = false;
        var begin = 0;
        for (var i = 0; i < db.length; i++) {
            if (!inSpeech && db[i] >= on) {
                inSpeech = true;
                begin = i;
            } else if (inSpeech && db[i] < off) {
                inSpeech = false;
                raw.push([begin, i]);
            }
        }
        if (inSpeech) raw.push([begin, db.length]);

        // Close gaps too short to be a pause, then drop clicks.
        var merged = [];
        for (var r = 0; r < raw.length; r++) {
            var prev = merged[merged.length - 1];
            if (prev && (raw[r][0] - prev[1]) * step < minPause) prev[1] = raw[r][1];
            else merged.push([raw[r][0], raw[r][1]]);
        }
        for (var m = 0; m < merged.length; m++) {
            var a = merged[m][0];
            var b = merged[m][1];
            if ((b - a) * step < minSpeech) continue;
            result.segments.push({ s: t0 + a * step, e: t0 + b * step });

            // Breaths inside long speech: local minima well below its level.
            if ((b - a) * step < 1.0) continue;
            var inside = db.slice(a, b).sort(function (x, y) { return x - y; });
            var level = percentile(inside, 0.5);
            var edge = Math.round(0.25 / step);
            var gap = Math.round(0.35 / step);
            var lastDip = -Infinity;
            for (var k = a + edge; k < b - edge; k++) {
                if (db[k] > level - 6 || db[k] > db[k - 1] || db[k] > db[k + 1]) continue;
                if (k - lastDip < gap) continue;
                result.dips.push(t0 + k * step);
                lastDip = k;
            }
        }
        return result;
    }

    // ============================================================
    // How long a line takes to say
    // ============================================================

    function inRange(code, ranges) {
        for (var i = 0; i < ranges.length; i += 2) {
            if (code >= ranges[i] && code <= ranges[i + 1]) return true;
        }
        return false;
    }

    // Indic scripts: vowel signs and marks ride on a letter; a virama joins
    // two consonants into one sound. Bengali and Devanagari are listed.
    var INDIC_MARKS = [0x0900, 0x0903, 0x093A, 0x094F, 0x0951, 0x0957, 0x0962, 0x0963,
                       0x0981, 0x0983, 0x09BC, 0x09BC, 0x09BE, 0x09CC, 0x09D7, 0x09D7, 0x09E2, 0x09E3];
    var INDIC_VIRAMA = [0x094D, 0x094D, 0x09CD, 0x09CD];
    var INDIC_LETTERS = [0x0904, 0x0939, 0x0958, 0x0961, 0x0972, 0x097F,
                         0x0985, 0x09B9, 0x09CE, 0x09CE, 0x09DC, 0x09DF, 0x09F0, 0x09F1];
    var INDIC_DIGITS = [0x0966, 0x096F, 0x09E6, 0x09EF];
    var CJK = [0x3040, 0x30FF, 0x3400, 0x9FFF, 0xAC00, 0xD7AF];

    /**
     * A rough spoken length, in "Latin letters": English runs about 14
     * letters a second. An Indic syllable (a letter with its vowel sign) or a
     * CJK character is one syllable, about 2.8 letters; a digit is said as a
     * word. Punctuation counts nothing: pauses are measured, not guessed.
     */
    function lineWeight(text) {
        var s = String(text || "");
        var w = 0;
        for (var i = 0; i < s.length; i++) {
            var code = s.charCodeAt(i);
            if (inRange(code, INDIC_MARKS)) continue;
            if (inRange(code, INDIC_VIRAMA)) {
                w -= 2.8; // the next consonant joins this one
                continue;
            }
            if (inRange(code, INDIC_LETTERS) || inRange(code, CJK)) w += 2.8;
            else if ((code >= 48 && code <= 57) || inRange(code, INDIC_DIGITS)) w += 4;
            else if (/[A-Za-z\u00C0-\u024F\u0370-\u03FF\u0400-\u04FF\u0600-\u06FF]/.test(s.charAt(i))) w += 1;
        }
        return Math.max(1, w);
    }

    /** Whether a line has anything to say (not just "---" or a lone symbol). */
    function isSpoken(text) {
        return String(text || "").replace(/[\s\u00A0\u2000-\u206F\u2E00-\u2E7F\u3000-\u303F\u0964\u0965!-\/:-@\[-\x60{-~]/g, "").length > 0;
    }

    // ============================================================
    // Lines over speech
    // ============================================================

    /**
     * Split the speech into pieces at every pause and dip. A piece boundary is
     * a place a line may end: { gap: pause length (0 for a dip), end: where
     * the speech before it stops, start: where the speech after it starts }.
     */
    function speechPieces(speech) {
        var segs = speech.segments;
        var dips = speech.dips.slice().sort(function (a, b) { return a - b; });
        var pieces = [];
        var d = 0;
        for (var k = 0; k < segs.length; k++) {
            var from = segs[k].s;
            while (d < dips.length && dips[d] <= from) d++;
            for (; d < dips.length && dips[d] < segs[k].e; d++) {
                pieces.push({ s: from, e: dips[d], cutAfter: { gap: 0, dip: true } });
                from = dips[d];
            }
            var next = segs[k + 1];
            pieces.push({ s: from, e: segs[k].e, cutAfter: next ? { gap: next.s - segs[k].e, dip: false } : null });
        }
        return pieces;
    }

    /** Evenly by weight over the whole span, for when there is too little to go on. */
    function proportional(weights, start, end) {
        var total = 0;
        for (var i = 0; i < weights.length; i++) total += weights[i];
        var out = [];
        var t = start;
        for (var j = 0; j < weights.length; j++) {
            var len = (end - start) * weights[j] / total;
            out.push({ start: t, end: t + len });
            t += len;
        }
        return out;
    }

    /**
     * Times for each line: [{ start, end }] in seconds from the envelope's
     * start. weights: lineWeight() of each line, in reading order.
     *
     * Dynamic programming over the speech pieces picks where each line ends so
     * that every line's share of the speech matches its expected share (the
     * log of the ratio, squared), with a reward for ending on a long pause and
     * a cost for ending on a breath. Lines stay in order and cover the speech
     * exactly once.
     */
    function alignLines(weights, envelope, opts) {
        opts = opts || {};
        var n = weights.length;
        if (!n) return [];
        var speech = envelope.segments ? envelope : findSpeech(envelope, opts);
        var segs = speech.segments;
        if (!segs.length) return null;
        var pieces = speechPieces(speech);
        var K = pieces.length;
        var first = segs[0].s;
        var lastEnd = segs[segs.length - 1].e;
        if (K < n) return proportional(weights, first, lastEnd);

        var cum = [0];
        for (var p = 0; p < K; p++) cum.push(cum[p] + (pieces[p].e - pieces[p].s));
        var totalSpeech = cum[K];
        var totalWeight = 0;
        for (var w = 0; w < n; w++) totalWeight += weights[w];

        // Weights picked from the middle of the range that timed every
        // voiceover in tools/fixtures to within a few frames (see test-align).
        var FIT = opts.fit !== undefined ? opts.fit : 4;          // how much a wrong share costs
        var PAUSE = opts.pause !== undefined ? opts.pause : 0.6;  // how much ending on a pause is worth
        var BREATH = opts.breath !== undefined ? opts.breath : 1.5; // how much ending on a breath costs
        // How much drifting from the script's pace costs: a line boundary
        // should fall about where that much of the script has been read.
        var DRIFT = opts.drift !== undefined ? opts.drift : 2;
        var driftScale = Math.max(1, 0.05 * totalSpeech);
        var readBefore = [0];
        for (var rb = 0; rb < n; rb++) readBefore.push(readBefore[rb] + weights[rb] / totalWeight * totalSpeech);
        function cutCost(piece) {
            var cut = piece.cutAfter;
            if (!cut) return 0;
            if (cut.dip) return BREATH;
            return -PAUSE * Math.log(1 + cut.gap / 0.25);
        }

        // best[i][k]: lowest cost for lines 0..i-1 over pieces 0..k-1.
        var INF = Infinity;
        var best = [];
        var from = [];
        for (var i = 0; i <= n; i++) {
            best.push(new Array(K + 1));
            from.push(new Array(K + 1));
            for (var z = 0; z <= K; z++) best[i][z] = INF;
        }
        best[0][0] = 0;
        for (var line = 1; line <= n; line++) {
            var expected = totalSpeech * weights[line - 1] / totalWeight;
            var maxK = K - (n - line);
            for (var k = line; k <= maxK; k++) {
                var tail = 0;
                if (line < n) {
                    var off = (cum[k] - readBefore[line]) / driftScale;
                    tail = cutCost(pieces[k - 1]) + DRIFT * off * off;
                }
                for (var j = k - 1; j >= line - 1; j--) {
                    var d = cum[k] - cum[j];
                    if (d > expected * 6 && j < k - 1) break; // far too long already
                    if (best[line - 1][j] === INF) continue;
                    var r = Math.log(Math.max(d, 0.02) / Math.max(expected, 0.02));
                    var cost = best[line - 1][j] + FIT * r * r + tail;
                    if (cost < best[line][k]) {
                        best[line][k] = cost;
                        from[line][k] = j;
                    }
                }
            }
        }
        if (best[n][K] === INF) return proportional(weights, first, lastEnd);

        var spans = [];
        var at = K;
        for (var back = n; back >= 1; back--) {
            var j0 = from[back][at];
            spans.unshift({ start: pieces[j0].s, end: pieces[at - 1].e });
            at = j0;
        }
        return spans;
    }

    // ============================================================
    // Timecodes and subtitles
    // ============================================================

    function pad(n, size) {
        var s = String(n);
        while (s.length < size) s = "0" + s;
        return s;
    }

    /** HH:MM:SS:FF, counting whole frames of the nominal rate (as non-drop timecode does). */
    function formatTimecode(seconds, fps) {
        var nominal = Math.round(fps) || 30;
        var frames = Math.max(0, Math.round(seconds * (fps || 30)));
        var ff = frames % nominal;
        var total = Math.floor(frames / nominal);
        return pad(Math.floor(total / 3600), 2) + ":" + pad(Math.floor(total / 60) % 60, 2) + ":" + pad(total % 60, 2) + ":" + pad(ff, 2);
    }

    /**
     * Seconds from a timecode as people type or LazyKick writes them:
     * HH:MM:SS:FF (also ;FF), HH:MM:SS.mmm, HH:MM:SS, MM:SS, with or without
     * brackets. null when it is not one.
     */
    function parseTimecode(text, fps) {
        var s = String(text || "").replace(/[\[\]\s]/g, "");
        var m = /^(\d{1,2})[:;](\d{1,2})[:;](\d{1,2})[:;](\d{1,3})$/.exec(s); // ";" too: drop-frame display
        var rate = fps || 30;
        // The inverse of formatTimecode: frames of the nominal rate, played at the real one.
        if (m) return (((+m[1] * 60 + +m[2]) * 60 + +m[3]) * (Math.round(rate) || 30) + +m[4]) / rate;
        m = /^(?:(\d{1,2}):)?(\d{1,2}):(\d{1,2})(?:[.,](\d{1,3}))?$/.exec(s);
        if (m) return ((+(m[1] || 0) * 60 + +m[2]) * 60 + +m[3]) + (m[4] ? +("0." + m[4]) : 0);
        return null;
    }

    /** About how long a line is on screen when nothing else says: reading speed, at least 1.2 s. */
    function readingTime(text) {
        return Math.max(1.2, lineWeight(text) / 15 + 0.3);
    }

    /** One line, or two at the space nearest the middle when it is longer than `max`. */
    function wrapSubtitle(text, max) {
        var s = String(text || "").replace(/\s+/g, " ").replace(/^ | $/g, "");
        max = max || 42;
        if (s.length <= max) return s;
        var mid = Math.floor(s.length / 2);
        var bestAt = -1;
        for (var i = 0; i < s.length; i++) {
            if (s.charAt(i) === " " && (bestAt < 0 || Math.abs(i - mid) < Math.abs(bestAt - mid))) bestAt = i;
        }
        return bestAt < 0 ? s : s.substr(0, bestAt) + "\n" + s.substr(bestAt + 1);
    }

    // ============================================================
    // Words inside a line
    // ============================================================

    /** The words of a line, split the same way everywhere (timing, highlighting, subtitles). */
    function splitWords(text) {
        return String(text || "").split(/\s+/).filter(function (w) { return w.length > 0; });
    }

    function wordWeights(words) {
        return words.map(function (w) { return lineWeight(w); });
    }

    /** Word start times spread over [start, end] by how long each word takes to say. */
    function proportionalWordTimes(words, start, end) {
        var weights = wordWeights(words);
        var total = 0;
        for (var i = 0; i < weights.length; i++) total += weights[i];
        var out = [];
        var acc = 0;
        for (var k = 0; k < weights.length; k++) {
            out.push(start + (end - start) * acc / (total || 1));
            acc += weights[k];
        }
        return out;
    }

    /**
     * When each word of a line starts, as offsets in seconds from the line's
     * start. The words share the line's speech by how long each takes to
     * say, and the clock stops during the pauses inside the line, so a word
     * after a breath starts after the breath.
     */
    function wordTimes(words, span, speech) {
        // Sentences and clauses inside the line are timed like lines are (the
        // pauses between them are what the timing is good at), then the words
        // are spread inside each of them.
        var phrases = [];
        var from = 0;
        for (var w0 = 0; w0 < words.length; w0++) {
            if (w0 === words.length - 1 || SENTENCE_END.test(words[w0]) || PHRASE_END.test(words[w0])) {
                phrases.push({ from: from, to: w0 });
                from = w0 + 1;
            }
        }
        if (phrases.length > 1 && speech && speech.segments) {
            var inside = { segments: [], dips: [] };
            for (var si = 0; si < speech.segments.length; si++) {
                var sa = Math.max(span.start, speech.segments[si].s);
                var sb = Math.min(span.end, speech.segments[si].e);
                if (sb - sa > 0.005) inside.segments.push({ s: sa, e: sb });
            }
            for (var di = 0; di < (speech.dips || []).length; di++) {
                if (speech.dips[di] > span.start && speech.dips[di] < span.end) inside.dips.push(speech.dips[di]);
            }
            var phraseSpans = inside.segments.length ? alignLines(phrases.map(function (ph) {
                return lineWeight(words.slice(ph.from, ph.to + 1).join(" "));
            }), inside) : null;
            if (phraseSpans && phraseSpans.length === phrases.length) {
                var all = [];
                for (var pi = 0; pi < phrases.length; pi++) {
                    var ps = phraseSpans[pi];
                    var sub = spreadWords(words.slice(phrases[pi].from, phrases[pi].to + 1), ps, inside);
                    for (var sw = 0; sw < sub.length; sw++) all.push(Math.round((ps.start + sub[sw] - span.start) * 1000) / 1000);
                }
                return all;
            }
        }
        return spreadWords(words, span, speech);
    }

    /**
     * Times a whole script: for each line { start, end, words } (words: when
     * each word starts, in seconds from the line's start), or null when there
     * is no speech. texts: the spoken lines in reading order.
     *
     * Every sentence is laid over the speech as a unit of its own, not only
     * every line. A paragraph's sentences and the pauses between them pin it
     * down far better than its total length, and a reader's pause between
     * paragraphs is often no longer than the one between two sentences: timed
     * by line alone, a paragraph could end a sentence early and hand its last
     * sentence to the next one. With more sentences than places to cut (a
     * rushed read), whole lines are the units, as before.
     */
    function alignScript(texts, speech, opts) {
        if (!texts.length) return [];
        if (!speech || !speech.segments || !speech.segments.length) return null;
        var sentences = [];
        var lines = [];
        for (var i = 0; i < texts.length; i++) {
            var words = splitWords(texts[i]);
            lines.push({ line: i, words: words, text: texts[i] });
            var from = 0;
            for (var w = 0; w < words.length; w++) {
                if (w === words.length - 1 || SENTENCE_END.test(words[w])) {
                    sentences.push({ line: i, words: words.slice(from, w + 1), text: words.slice(from, w + 1).join(" ") });
                    from = w + 1;
                }
            }
            if (!words.length) sentences.push(lines[i]);
        }
        var units = sentences.length > speechPieces(speech).length ? lines : sentences;
        var spans = alignLines(units.map(function (u) { return lineWeight(u.text); }), speech, opts);
        if (!spans) return null;
        var out = [];
        for (var u = 0; u < units.length; u++) {
            var span = spans[u];
            var line = out[units[u].line];
            if (!line) line = out[units[u].line] = { start: span.start, end: span.end, words: [] };
            line.end = span.end;
            var offsets = wordTimes(units[u].words, span, speech);
            for (var k = 0; k < offsets.length; k++) line.words.push(Math.round((span.start + offsets[k] - line.start) * 1000) / 1000);
        }
        return out;
    }

    /** Offsets of each word from span.start, sharing the span's speech by spoken length; pauses stop the clock. */
    function spreadWords(words, span, speech) {
        var pieces = [];
        var segs = (speech && speech.segments) || [];
        for (var s = 0; s < segs.length; s++) {
            var a = Math.max(span.start, segs[s].s);
            var b = Math.min(span.end, segs[s].e);
            if (b - a > 0.005) pieces.push([a, b]);
        }
        if (!pieces.length) pieces.push([span.start, span.end]);
        var speechTime = 0;
        for (var p = 0; p < pieces.length; p++) speechTime += pieces[p][1] - pieces[p][0];
        function clockAt(x) {
            for (var q = 0; q < pieces.length; q++) {
                var len = pieces[q][1] - pieces[q][0];
                if (x <= len || q === pieces.length - 1) return pieces[q][0] + Math.min(x, len);
                x -= len;
            }
            return span.end;
        }
        var weights = wordWeights(words);
        var total = 0;
        for (var w = 0; w < weights.length; w++) total += weights[w];
        var out = [];
        var acc = 0;
        for (var k = 0; k < weights.length; k++) {
            out.push(Math.round((clockAt(speechTime * acc / (total || 1)) - span.start) * 1000) / 1000);
            acc += weights[k];
        }
        return out;
    }

    /**
     * Where the playhead is in the script: { line, word } indexes into
     * `lines` ([{ start, end, words: [absolute start times] }], by start),
     * or -1. Between two lines the earlier one stays current but no word is.
     */
    function followAt(lines, t) {
        var line = -1;
        for (var i = 0; i < lines.length; i++) {
            if (lines[i].start <= t + 0.02) line = i;
            else break;
        }
        if (line < 0) return { line: -1, word: -1 };
        var l = lines[line];
        if (t > l.end + 0.05) return { line: line, word: -1 };
        var word = -1;
        var words = l.words || [];
        for (var k = 0; k < words.length; k++) {
            if (words[k] <= t + 0.02) word = k;
            else break;
        }
        return { line: line, word: word };
    }

    // ============================================================
    // Subtitle cues
    // ============================================================

    var CUE_CHARS = 84;      // two subtitle lines of 42
    var CUE_SECONDS = 6.5;   // longer than this is hard to read along

    var SENTENCE_END = /[.!?\u0964\u2026]["'\u201D\u2019)\]]*$/;
    var PHRASE_END = /[,;:\u2013\u2014]["'\u201D\u2019)\]]*$/;

    /**
     * Cuts a line's words into subtitle-sized pieces [{ from, to }]. A piece
     * that must end early ends at a sentence end when one leaves it at least
     * a third full, else at a comma, else at the last word that fits.
     */
    function chunkWords(words, times, maxChars, maxSeconds) {
        var chunks = [];
        var from = 0;
        while (from < words.length) {
            var len = 0;
            var to = from;
            var sentence = -1;
            var phrase = -1;
            var lenAt = {};
            for (var i = from; i < words.length; i++) {
                var add = (i > from ? 1 : 0) + words[i].length;
                if (i > from && (len + add > maxChars || (times && times[i] - times[from] > maxSeconds))) break;
                len += add;
                lenAt[i] = len;
                to = i;
                if (SENTENCE_END.test(words[i])) sentence = i;
                else if (PHRASE_END.test(words[i])) phrase = i;
            }
            var end = to;
            if (to < words.length - 1) {
                if (sentence >= from && sentence < to && lenAt[sentence] >= maxChars / 3) end = sentence;
                else if (phrase >= from && phrase < to && lenAt[phrase] >= maxChars / 3) end = phrase;
            }
            chunks.push({ from: from, to: end });
            from = end + 1;
        }
        return chunks;
    }

    /**
     * Subtitle cues from timed lines [{ start, end?, text, words? }]
     * (seconds; `words`: absolute start time of each word of the text). Each
     * line ends where it was measured to end but never runs into the next,
     * and one without a measured end stays up for its reading time. A line
     * too long for one subtitle (a pasted paragraph) is cut into pieces that
     * start when their first word is spoken.
     */
    function makeCues(lines) {
        var sorted = lines.filter(function (l) { return l && typeof l.start === "number" && isFinite(l.start) && isSpoken(l.text); })
            .sort(function (a, b) { return a.start - b.start; });
        var cues = [];
        for (var i = 0; i < sorted.length; i++) {
            var l = sorted[i];
            var next = sorted[i + 1];
            var end = (typeof l.end === "number" && l.end > l.start + 0.1) ? l.end : l.start + readingTime(l.text);
            if (next) end = Math.min(end, next.start - 0.04);
            if (next && end - l.start < 0.5) end = Math.max(end, Math.min(l.start + 0.5, next.start));
            if (end <= l.start) continue;
            var start = Math.max(0, l.start);
            var words = splitWords(l.text);
            if (l.text.replace(/\s+/g, " ").length <= CUE_CHARS && end - start <= CUE_SECONDS) {
                cues.push({ start: start, end: end, text: wrapSubtitle(l.text) });
                continue;
            }
            var times = (l.words && l.words.length === words.length) ? l.words : proportionalWordTimes(words, start, end);
            var chunks = chunkWords(words, times, CUE_CHARS, CUE_SECONDS);
            for (var c = 0; c < chunks.length; c++) {
                var cs = Math.max(start, Math.min(times[chunks[c].from], end - 0.2));
                var ce = c < chunks.length - 1 ? Math.max(cs + 0.3, times[chunks[c + 1].from] - 0.04) : end;
                cues.push({ start: cs, end: Math.min(ce, end), text: wrapSubtitle(words.slice(chunks[c].from, chunks[c].to + 1).join(" ")) });
            }
        }
        return cues;
    }

    function srtTime(seconds) {
        var ms = Math.max(0, Math.round(seconds * 1000));
        return pad(Math.floor(ms / 3600000), 2) + ":" + pad(Math.floor(ms / 60000) % 60, 2) + ":" +
               pad(Math.floor(ms / 1000) % 60, 2) + "," + pad(ms % 1000, 3);
    }

    function buildSrt(cues) {
        var out = [];
        for (var i = 0; i < cues.length; i++) {
            out.push(String(i + 1), srtTime(cues[i].start) + " --> " + srtTime(cues[i].end), cues[i].text.replace(/\r?\n/g, "\r\n"), "");
        }
        return out.join("\r\n");
    }

    return {
        WavEnvelope: WavEnvelope,
        findSpeech: findSpeech,
        lineWeight: lineWeight,
        isSpoken: isSpoken,
        alignLines: alignLines,
        alignScript: alignScript,
        formatTimecode: formatTimecode,
        parseTimecode: parseTimecode,
        readingTime: readingTime,
        wrapSubtitle: wrapSubtitle,
        splitWords: splitWords,
        wordTimes: wordTimes,
        proportionalWordTimes: proportionalWordTimes,
        followAt: followAt,
        chunkWords: chunkWords,
        makeCues: makeCues,
        srtTime: srtTime,
        buildSrt: buildSrt
    };
});

const LazyAlign = ALIGN_HOST.LazyAlign;

	const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
	const clamp = (x, a, b) => Math.min(b, Math.max(a, x));
	const lerp = (a, b, u) => a + (b - a) * u;
	const easeInOut = (u) => (u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2);

	const STR = {
		en: {
			label: "Interactive demo",
			note: "nothing is really imported",
			reset: "Reset",
			region: "LazyKick interactive demo",
			pasteEn: "📄 Paste the English script",
			pasteBn: "📄 Paste the Bengali script",
			empty: "Write project notes, client feedback, or paste a script from Google Docs…",
			drop: "📥 A client drops a new file into the folder",
			dropNote: "Demo only: the folder is imagined. In LazyKick it is a real folder on your disk.",
			hints: {
				start: "Paste a script into the note, with one of the two buttons in it. The voiceover is already on the timeline.",
				time: "Now press 🎙️ Time to Audio: LazyKick listens to the voiceover and starts every line with the moment it is spoken.",
				wait: "Listening: finding the pauses in the voice, matching every sentence by how long it takes to say.",
				subs: "Press ▶ to play the timeline and watch the note follow the voice word by word, or 💬 Subtitles to put the lines on the timeline.",
				play: "Press ▶ on the timeline: the subtitles show in the monitor and the note follows along. Click the ruler to jump.",
				more: "Try 📋 Paste Image, link a folder on the Watch Bins tab, or switch to After Effects.",
				done: "That is LazyKick. Press Reset to start over.",
			},
			need: {
				script: "Paste the script first.",
				time: "Press Time to Audio first.",
			},
		},
		bn: {
			label: "ইন্টারঅ্যাক্টিভ ডেমো",
			note: "আসলে কিছু ইমপোর্ট হয় না",
			reset: "রিসেট",
			region: "LazyKick ইন্টারঅ্যাক্টিভ ডেমো",
			pasteEn: "📄 ইংরেজি স্ক্রিপ্ট পেস্ট করুন",
			pasteBn: "📄 বাংলা স্ক্রিপ্ট পেস্ট করুন",
			empty: "Write project notes, client feedback, or paste a script from Google Docs…",
			drop: "📥 ক্লায়েন্ট ফোল্ডারে একটা নতুন ফাইল রাখল",
			dropNote: "শুধু ডেমোর জন্য: ফোল্ডারটা কাল্পনিক। LazyKick-এ এটা আপনার ডিস্কের আসল ফোল্ডার।",
			hints: {
				start: "নোটের ভেতরের দুটো বোতামের একটা দিয়ে স্ক্রিপ্ট পেস্ট করুন। ভয়েসওভার টাইমলাইনে আগে থেকেই আছে।",
				time: "এবার 🎙️ Time to Audio চাপুন: LazyKick ভয়েসওভার শোনে আর প্রতিটি লাইনের শুরুতে বসিয়ে দেয় কখন সেটা বলা হয়েছে।",
				wait: "শুনছে: কণ্ঠের বিরতিগুলো খুঁজছে, প্রতিটি বাক্য বলতে কতক্ষণ লাগে সেই হিসাবে মেলাচ্ছে।",
				subs: "▶ চেপে টাইমলাইন চালান, দেখুন নোট শব্দে শব্দে কণ্ঠের সাথে চলছে; বা 💬 Subtitles চেপে লাইনগুলো টাইমলাইনে বসান।",
				play: "টাইমলাইনের ▶ চাপুন: মনিটরে সাবটাইটেল দেখাবে আর নোট সাথে সাথে চলবে। রুলারে ক্লিক করে যেকোনো জায়গায় যান।",
				more: "📋 Paste Image চেপে দেখুন, Watch Bins ট্যাবে একটা ফোল্ডার যুক্ত করুন, বা After Effects-এ যান।",
				done: "এই হলো LazyKick। আবার শুরু করতে রিসেট চাপুন।",
			},
			need: {
				script: "আগে স্ক্রিপ্টটা পেস্ট করুন।",
				time: "আগে Time to Audio চাপুন।",
			},
		},
	};

	/* ------------------------------------------------------------------ the two apps */
	const FPS = 25;
	const APPS = {
		pr: {
			badge: "Pr", name: "Premiere Pro", host: "PPRO",
			file: "City_Stories_Ep01.prproj", title: "City_Stories_Ep01.prproj — Adobe Premiere Pro",
			view: "Program: Ep01 Rough Cut", seq: "Ep01 Rough Cut", where: "sequence",
			video: "Ep01_Interview.mp4", vo: "Ep01_VO.wav", heard: "the selected audio",
		},
		ae: {
			badge: "Ae", name: "After Effects", host: "AEFT",
			file: "City_Stories_Ep01.aep", title: "City_Stories_Ep01.aep — Adobe After Effects",
			view: "Main Comp", seq: "Main Comp", where: "composition",
			video: "Ep01_Interview.mp4", vo: "Ep01_VO.wav", heard: "the timeline audio",
		},
	};
	const VB = [900, 506];
	const PASTE_FILE = "pasted_20260927_142233.png";
	const PASTE_DIR = "D:\\Projects\\City Stories\\Pasted Images\\";
	const STILL_SECONDS = 5;

	/* The scripts, as a paste from Google Docs lands in the note (client/paste.js): a title,
	   a grey meta line, section headings, paragraphs. Demo text, not a real programme. */
	const SCRIPTS = {
		en: [
			{ k: "h1", t: "Episode 1 | How Coffee Took Over the World" },
			{ k: "muted", t: "Series: City Stories | Target: 6 min | Tone: warm, curious" },
			{ k: "h3", t: "HOOK" },
			{ k: "p", t: "Every morning, two billion cups of coffee are poured around the world. Not tea. Not juice. Coffee. It wakes up offices, fuels night shifts and keeps whole cities moving." },
			{ k: "p", t: "Yet five hundred years ago, almost nobody outside a few mountain villages had ever tasted it." },
			{ k: "h3", t: "THE ORIGIN" },
			{ k: "p", t: "The story usually starts with a goat herder in Ethiopia, who noticed his goats dancing after eating bright red berries. It is probably a legend, but the plant is real, and it still grows wild in those hills." },
			{ k: "p", t: "From there, the beans crossed the sea to Yemen, where monks brewed them to stay awake through long nights of prayer." },
		],
		bn: [
			{ k: "h1", t: "পর্ব ১ | কফি যেভাবে দুনিয়া জয় করল" },
			{ k: "muted", t: "সিরিজ: City Stories | লক্ষ্য: ৬ মিনিট | টোন: উষ্ণ, কৌতূহলী" },
			{ k: "h3", t: "হুক" },
			{ k: "p", t: "প্রতিদিন সকালে সারা দুনিয়ায় দুইশো কোটি কাপ কফি ঢালা হয়। চা নয়। জুস নয়। কফি। এটা অফিস জাগিয়ে তোলে, রাতের শিফট চালিয়ে রাখে, আর আস্ত শহরকে সচল রাখে।" },
			{ k: "p", t: "অথচ পাঁচশো বছর আগে কয়েকটা পাহাড়ি গ্রামের বাইরে প্রায় কেউই এর স্বাদ পায়নি।" },
			{ k: "h3", t: "শুরুর গল্প" },
			{ k: "p", t: "গল্পটা সাধারণত শুরু হয় ইথিওপিয়ার এক ছাগল-রাখালকে দিয়ে, যে দেখেছিল লাল টুকটুকে বেরি খেয়ে তার ছাগলগুলো নাচছে। সম্ভবত এটা কিংবদন্তি, কিন্তু গাছটা সত্যি, আর সেই পাহাড়ে এখনো তা বুনো হয়ে জন্মায়।" },
			{ k: "p", t: "সেখান থেকে বীজগুলো সাগর পেরিয়ে ইয়েমেনে যায়, যেখানে সন্ন্যাসীরা লম্বা রাতের প্রার্থনায় জেগে থাকতে তা ফুটিয়ে খেতেন।" },
		],
	};

	const GLOBAL_NOTE = [
		{ k: "muted", t: "Global scratchpad — every project sees this one" },
		{ k: "p", t: "Render preset: H.264 · 20 Mbps · 1080p" },
		{ k: "p", t: "Client FTP: ftp.citystories.tv / uploads" },
		{ k: "task", t: "Ask for the new logo files", done: false },
		{ k: "task", t: "Send the round 2 cut", done: true },
	];

	/* Folders a watch bin can be linked to (the real panel browses your disk). */
	const FOLDERS = [
		{ path: "D:\\Projects\\City Stories\\Footage", name: "01. Footage", count: 148, kinds: { video: true, audio: false, image: true } },
		{ path: "D:\\Projects\\City Stories\\Audio\\SFX", name: "03. Audio/SFX", count: 62, kinds: { video: false, audio: true, image: false } },
		{ path: "D:\\Music\\Beds", name: "Music Beds", count: 24, kinds: { video: false, audio: true, image: false } },
	];
	const UPLOADS = { path: "C:\\Users\\Editor\\Downloads", name: "Client Uploads" };
	const UPLOAD_FILES = [
		{ name: "interview_take3.mp4", kind: "🎬", size: "482 MB" },
		{ name: "room_tone.wav", kind: "🎵", size: "38 MB" },
		{ name: "logo_pack.png", kind: "🖼️", size: "4 MB" },
	];
	const NEW_FILES = [
		{ name: "drone_pass.mp4", kind: "🎬", size: "1.2 GB" },
		{ name: "b-roll_cafe.mov", kind: "🎬", size: "640 MB" },
		{ name: "vo_retake_p3.wav", kind: "🎵", size: "21 MB" },
		{ name: "poster_v2.png", kind: "🖼️", size: "9 MB" },
	];

	/* ------------------------------------------------------------------ the voiceover */
	const SENTENCE_END = /[.!?।…]["'”’)\]]*$/;

	function seeded(seed) {
		let s = seed >>> 0;
		return () => {
			s = (s * 1664525 + 1013904223) >>> 0;
			return s / 4294967296;
		};
	}

	function sentencesOf(text) {
		const words = LazyAlign.splitWords(text);
		const out = [];
		let from = 0;

		words.forEach((w, i) => {
			if (i === words.length - 1 || SENTENCE_END.test(w)) {
				out.push(words.slice(from, i + 1).join(" "));
				from = i + 1;
			}
		});

		return out;
	}

	/* A loudness envelope (RMS every 10 ms, what LazyKick measures from a WAV) of someone reading
	   the script's paragraphs: each sentence takes as long as its spoken length says, with a
	   short pause after it, a longer one after a paragraph, and a breath at the commas of a long
	   sentence. Nothing is timed from this directly: it is only what Time to Audio listens to. */
	function makeVoice(blocks) {
		const rnd = seeded(20260927);
		const step = 0.01;
		const values = [];
		const lines = blocks.filter((b) => b.k === "p").map((b) => b.t);
		const truth = [];
		let t = 0;

		const push = (secs, level) => {
			const n = Math.round(secs / step);
			for (let i = 0; i < n; i++) {
				values.push(level(i * step));
			}
			t += n * step;
		};
		const silence = (secs) => push(secs, () => 0.004 + 0.003 * rnd());
		const speak = (text) => {
			const dur = (LazyAlign.lineWeight(text) / 14) * (0.92 + 0.16 * rnd());
			const phase = rnd() * 6.28;
			const breaths = [];

			if (dur > 2.4) {
				const words = LazyAlign.splitWords(text);
				let acc = 0;
				const total = words.reduce((sum, w) => sum + LazyAlign.lineWeight(w), 0);
				words.forEach((w, i) => {
					acc += LazyAlign.lineWeight(w);
					if (i < words.length - 1 && /[,;:]$/.test(w)) {
						breaths.push(acc / total);
					}
				});
			}

			push(dur, (x) => {
				const u = x / dur;
				let lv = 0.26 * (0.72 + 0.28 * Math.abs(Math.sin(2 * Math.PI * 3.8 * x + phase))) * (0.9 + 0.2 * rnd());
				for (const b of breaths) {
					if (u > b && u < b + 0.11 / dur) {
						lv *= 0.18;
					}
				}
				return lv;
			});
		};

		silence(1.2);
		lines.forEach((line, li) => {
			const sentences = sentencesOf(line);
			const start = t;
			sentences.forEach((s, si) => {
				speak(s);
				if (si < sentences.length - 1) {
					silence(0.3 + 0.25 * rnd());
				}
			});
			truth.push({ start, end: t });
			silence(li < lines.length - 1 ? 0.55 + 0.3 * rnd() : 1.0);
		});

		return { step, values, duration: Math.round(values.length * step * 100) / 100, truth };
	}

	const VOICES = {};
	const voiceFor = (lang) => (VOICES[lang] = VOICES[lang] || makeVoice(SCRIPTS[lang]));

	/* The waveform Premiere and After Effects draw on the clip, from the same envelope. */
	function wavePath(voice, bars, h) {
		const per = Math.max(1, Math.floor(voice.values.length / bars));
		let d = "";
		for (let b = 0; b < bars; b++) {
			let peak = 0;
			for (let i = b * per; i < (b + 1) * per && i < voice.values.length; i++) {
				peak = Math.max(peak, voice.values[i]);
			}
			const y = Math.max(1, Math.min(1, peak / 0.32) * h);
			d += `M${b} ${(h - y) / 2}v${y}`;
		}
		return d;
	}

	/* ------------------------------------------------------------------ state */
	const badge = (kind, s) => {
		const spec = kind === "Pr" ? { bg: "#2a0a3f", fg: "#e97fff" } : { bg: "#00005b", fg: "#9999ff" };
		return `<svg viewBox="0 0 ${s} ${s}" aria-hidden="true"><rect width="${s}" height="${s}" rx="${s * 0.22}" fill="${spec.bg}"/>` +
			`<rect x="${s * 0.08}" y="${s * 0.08}" width="${s * 0.84}" height="${s * 0.84}" rx="${s * 0.15}" fill="none" stroke="${spec.fg}" stroke-width="${s * 0.05}"/>` +
			`<text x="${s / 2}" y="${s * 0.66}" text-anchor="middle" fill="${spec.fg}" font-size="${s * 0.44}" font-weight="700">${kind}</text></svg>`;
	};

	const ICONS = {
		spark: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.2 6.3L20.5 10l-6.3 2.2L12 18.5 9.8 12.2 3.5 10l6.3-1.7z"/></svg>`,
		reset: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>`,
		play: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4.5v15l12-7.5z"/></svg>`,
		pause: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>`,
	};

	const newProject = () => ({
		t: 0,
		playing: false,
		note: { active: "0", tabs: [{ name: "Script", blocks: [] }] },
		lang: "",             /* which script the note holds */
		cues: null,           /* subtitles on the timeline */
		stills: [],           /* pasted images: { at, file } */
		bins: [{ path: UPLOADS.path, name: UPLOADS.name, kinds: { video: true, audio: true, image: true }, sub: false, count: 9, pending: null }],
		autoSync: false,
	});

	const start = () => ({
		app: "pr",
		tab: "notes",
		follow: true,
		guide: true,
		fit: false,
		fontSize: 14,
		global: GLOBAL_NOTE.map((b) => Object.assign({}, b)),
		recent: [],
		p: { pr: newProject(), ae: newProject() },
		busy: { time: false, subs: false, paste: false, sync: false },
		status: ["Ready", "quiet"],
		did: { script: false, time: false, subs: false, play: false, paste: false, bins: false, app: false },
	});

	/* ------------------------------------------------------------------ markup */
	function markup(L, tall) {
		return `<div class="lkd-stage ${tall ? "lkd-tall" : "lkd-wide"}">
<div class="lkd-head">
	<span class="lkd-label">${ICONS.spark}${L.label}<small>· ${L.note}</small></span>
	<p class="lkd-hint"></p>
	<button type="button" class="lkd-reset" data-act="reset">${ICONS.reset}${L.reset}</button>
</div>

<section class="lkd-app" aria-label="Adobe">
	<div class="lkd-titlebar">
		<button type="button" class="lkd-apptab" role="tab" data-app="pr" aria-selected="true">${badge("Pr", 20)}Premiere Pro</button>
		<button type="button" class="lkd-apptab" role="tab" data-app="ae" aria-selected="false">${badge("Ae", 20)}After Effects</button>
		<span class="lkd-file"></span>
		<span class="lkd-dots" aria-hidden="true"><i style="background:#f5a623"></i><i style="background:#27c93f"></i><i style="background:#ff5f56"></i></span>
	</div>

	<div class="lkd-body">
		<div class="lkd-viewer">
			<div class="lkd-viewer__bar"><b class="lkd-view-name">Program: Ep01 Rough Cut</b><span>1920 × 1080 · 25 fps</span><span>Fit</span></div>
			<div class="lkd-canvas">
				<div class="lkd-frame">
					<svg viewBox="0 0 ${VB[0]} ${VB[1]}" aria-hidden="true"></svg>
					<div class="lkd-sub" aria-live="polite"></div>
				</div>
			</div>
			<div class="lkd-folderwin" aria-hidden="true"></div>
			<div class="lkd-projwin" aria-hidden="true"></div>
		</div>

		<div class="lkd-timeline">
			<div class="lkd-tlbar">
				<button type="button" class="lkd-playbtn" data-act="play" aria-label="Play">${ICONS.play}</button>
				<b class="lkd-tc">00:00:00:00</b>
				<span class="lkd-tlname">Ep01 Rough Cut</span>
				<span class="lkd-tlhint">click the ruler to jump · Space plays</span>
			</div>
			<div class="lkd-tlbody"><div class="lkd-tlgrid" data-seek></div></div>
		</div>

		<div class="lkd-panel" aria-label="LazyKick">
			<div class="lkd-ph">
				<span class="lkd-brand"><span class="lkd-bolt">⚡</span>LazyKick</span>
				<span class="lkd-hostbadge">PPRO</span>
				<span class="lkd-proj">City_Stories_Ep01.prproj</span>
				<button type="button" class="lkd-pastebtn" data-act="paste"><span class="lkd-pastebtn__icon">📋</span><span class="lkd-pastebtn__text">Paste Image</span></button>
				<button type="button" class="lkd-iconbtn" data-act="refresh" title="Refresh project status">↻</button>
			</div>
			<div class="lkd-tabs" role="tablist">
				<button type="button" class="lkd-tab" role="tab" data-tab="notes" aria-selected="true"><span>📝</span>Notes &amp; Tasks</button>
				<button type="button" class="lkd-tab" role="tab" data-tab="bins" aria-selected="false"><span>📂</span>Watch Bins <i class="lkd-count">1</i></button>
				<button type="button" class="lkd-tab" role="tab" data-tab="tools" aria-selected="false"><span>🛠️</span>Paste &amp; Tools</button>
			</div>

			<section class="lkd-pane" data-pane="notes">
				<div class="lkd-ntbar">
					<div class="lkd-subtabs"></div>
					<div class="lkd-ntacts">
						<button type="button" class="lkd-tool" data-act="addtab" title="Add new note tab">+</button>
						<button type="button" class="lkd-tool lkd-tool--accent" data-act="timecode" title="Insert current timeline timecode">⏱️ Timecode</button>
						<button type="button" class="lkd-tool" data-act="task" title="Insert To-Do Checkbox">☑️ Task</button>
						<button type="button" class="lkd-tool" data-act="copy" title="Copy entire note">📋</button>
						<button type="button" class="lkd-tool" data-act="export" title="Export note as .txt file">📥</button>
						<button type="button" class="lkd-tool lkd-tool--danger" data-act="deltab" title="Delete current tab">🗑️</button>
					</div>
				</div>
				<div class="lkd-scriptbar">
					<button type="button" class="lkd-tool lkd-tool--accent" data-act="time">🎙️ Time to Audio</button>
					<button type="button" class="lkd-tool" data-act="subs">💬 Subtitles</button>
					<button type="button" class="lkd-tool lkd-tool--follow" data-act="follow" aria-pressed="true">👁 Follow</button>
					<span class="lkd-scripthint">Lines or whole paragraphs · select the voiceover to use only it</span>
					<button type="button" class="lkd-tool lkd-tool--size" data-act="smaller" title="Smaller note text">A−</button>
					<button type="button" class="lkd-tool lkd-tool--size" data-act="bigger" title="Bigger note text">A+</button>
				</div>
				<div class="lkd-editorwrap"><div class="lkd-editor" tabindex="0" aria-label="Note"></div></div>
			</section>

			<section class="lkd-pane" data-pane="bins" hidden>
				<div class="lkd-binbar">
					<button type="button" class="lkd-primary" data-act="addbin">+ Add Watch Bin</button>
					<button type="button" class="lkd-secondary" data-act="syncall">⚡ Sync All</button>
					<label class="lkd-toggle" title="Auto-sync media files in background"><input type="checkbox" data-act="autosync"><span class="lkd-slider"></span></label>
					<span class="lkd-togglelabel">Auto-Sync</span>
					<span class="lkd-dot"></span>
				</div>
				<div class="lkd-binlist"></div>
			</section>

			<section class="lkd-pane" data-pane="tools" hidden>
				<div class="lkd-tools">
					<div class="lkd-card">
						<div class="lkd-card__h"><h3>📋 LazyPaste Options</h3></div>
						<div class="lkd-card__b">
							<label class="lkd-setting"><span>Paste as Guide Layer <small>(After Effects only — won't render)</small></span><input type="checkbox" data-act="guide" checked></label>
							<label class="lkd-setting"><span>Shrink large images to fit the comp <small>(After Effects only — never enlarges)</small></span><input type="checkbox" data-act="fit"></label>
							<div class="lkd-setting-input">
								<label>Save Folder / Bin Name:</label>
								<div class="lkd-inputrow"><input type="text" value="Pasted Images" readonly><button type="button" class="lkd-secondary" data-act="openfolder" title="Open this project's paste folder">📂</button></div>
								<small>Used for the folder next to the project file and for the project bin. Slashes (/) make nested folders.</small>
							</div>
						</div>
					</div>
					<div class="lkd-card">
						<div class="lkd-card__h"><h3>🖼️ Recent Pastes</h3><button type="button" class="lkd-link" data-act="clearrecent">Clear</button></div>
						<div class="lkd-card__b"><div class="lkd-recent"></div></div>
					</div>
					<div class="lkd-card lkd-card--tip">
						<span class="lkd-tipicon">⌨️</span>
						<div><b>Ctrl / Cmd + V anywhere outside the notes</b><small>Screenshot in, layer out — the panel never leaves your hands.</small></div>
					</div>
				</div>
			</section>

			<div class="lkd-foot"><span class="lkd-status" title="">Ready</span><span class="lkd-credit"><b>LazyKick v1.5.2</b> • Developed By RaisulSohan</span></div>

			<div class="lkd-dialog" hidden></div>
		</div>
	</div>

	<div class="lkd-toast" aria-hidden="true"></div>
</section>
</div>`;
	}

	/* ------------------------------------------------------------------ one demo */
	let seq = 0;
	const reduceMQ = window.matchMedia ? window.matchMedia("(prefers-reduced-motion: reduce)") : null;
	const reduced = () => Boolean(reduceMQ && reduceMQ.matches);

	function mount(root) {
		if (root.lkdMounted) {
			return;
		}
		root.lkdMounted = true;

		const u = `lkd${++seq}`;
		const lang = (root.getAttribute("data-lang") || document.documentElement.lang || "en").toLowerCase();
		const L = STR[lang.indexOf("bn") === 0 ? "bn" : "en"];

		let S = start();
		let tall = null, stage = null, W = 0, k = 1, raf = 0, playRaf = 0, toastTimer = 0, statusTimer = 0, flyId = 0;
		let userAt = 0;
		const timers = [];

		const q = (sel) => stage.querySelector(sel);
		const qa = (sel) => Array.from(stage.querySelectorAll(sel));
		const P = () => S.p[S.app];
		const app = () => APPS[S.app];
		const later = (ms, fn) => {
			const id = setTimeout(() => {
				timers.splice(timers.indexOf(id), 1);
				if (stage) {
					fn();
				}
			}, reduced() ? Math.min(ms, 40) : ms);
			timers.push(id);
			return id;
		};

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
			root.classList.add("lkd-ready");
			fit();
			paintAll();
		}

		function fit() {
			k = root.clientWidth / W || 1;
			stage.style.transform = `scale(${k})`;
		}

		/* ---- the voiceover on the timeline ---- */
		function voice() {
			return voiceFor(P().lang || "en");
		}

		function duration() {
			return voice().duration + 1.5;
		}

		/* ---- the note ---- */
		function noteTab() {
			const n = P().note;
			return n.active === "global" ? null : n.tabs[parseInt(n.active, 10)] || n.tabs[0];
		}

		function noteBlocks() {
			const tab = noteTab();
			return tab ? tab.blocks : S.global;
		}

		function spokenBlocks() {
			return noteBlocks().filter((b) => b.k === "p" && LazyAlign.isSpoken(b.t));
		}

		function timedLines() {
			return spokenBlocks().filter((b) => b.tag).map((b) => ({
				block: b,
				start: b.tag.start,
				end: b.tag.end,
				words: b.tag.words.map((w) => b.tag.start + w),
				text: b.t,
			})).sort((a, b) => a.start - b.start);
		}

		/* ---- painting ---- */
		function paintTitle() {
			const a = app();
			qa("[data-app]").forEach((b) => b.setAttribute("aria-selected", String(b.getAttribute("data-app") === S.app)));
			q(".lkd-file").textContent = a.title;
			q(".lkd-view-name").textContent = a.view;
			q(".lkd-tlname").textContent = a.seq;
			q(".lkd-hostbadge").textContent = a.host;
			q(".lkd-proj").textContent = a.file;
		}

		function paintCanvas() {
			const svg = q(".lkd-frame svg");
			const p = P();
			const t = p.t;
			const base = `${u}-${S.app}-frame`;
			let over = "";

			p.stills.forEach((s, i) => {
				const shows = S.app === "ae" ? t >= s.at - 0.001 : (t >= s.at - 0.001 && t < s.at + STILL_SECONDS);
				if (!shows) {
					return;
				}
				const id = `${u}-${S.app}-shot${i}`;
				over += `<g transform="translate(238 133)">` +
					`<clipPath id="${id}-c"><rect width="424" height="265"/></clipPath>` +
					`<g clip-path="url(#${id}-c)"><g transform="scale(1.06)">${artShot(id)}</g></g>` +
					(S.app === "ae" && s.guide
						? `<rect width="424" height="265" fill="none" stroke="#8ec7ff" stroke-width="2" stroke-dasharray="7 5"/>` +
							`<rect x="0" y="-26" width="128" height="20" rx="10" fill="#0f2233" stroke="#2c517c"/>` +
							`<text x="64" y="-12" fill="#8ec7ff" font-size="9.5" font-weight="600" text-anchor="middle">GUIDE LAYER</text>`
						: `<rect width="424" height="265" fill="none" stroke="#ffffff" stroke-opacity="0.35"/>`) +
					`</g>`;
			});

			svg.innerHTML = `<g>${artFrame(base)}</g>${over}`;
			paintSubtitle();
		}

		function paintSubtitle() {
			const el = q(".lkd-sub");
			const p = P();
			const cue = p.cues ? p.cues.find((c) => p.t >= c.start && p.t < c.end) : null;
			el.textContent = cue ? cue.text : "";
			el.classList.toggle("lkd-on", Boolean(cue));
		}

		function paintTimecode() {
			q(".lkd-tc").textContent = LazyAlign.formatTimecode(P().t, FPS);
			q(".lkd-playbtn").innerHTML = P().playing ? ICONS.pause : ICONS.play;
			q(".lkd-playbtn").setAttribute("aria-label", P().playing ? "Pause" : "Play");
		}

		function timelineRows() {
			const a = app();
			const p = P();
			const D = duration();
			const rows = [];
			const pct = (s) => `${((s / D) * 100).toFixed(3)}%`;
			const clip = (from, to, label, cls) =>
				`<span class="lkd-clip ${cls}" style="left:${pct(from)};width:${pct(Math.min(D, to) - from)}"><i>${esc(label)}</i></span>`;
			const cueClips = () => (p.cues || []).map((c) => clip(c.start, c.end, c.text.replace(/\n/g, " "), "lkd-clip--sub")).join("");

			if (S.app === "pr") {
				if (p.cues) {
					rows.push({ name: "C1", sub: "Subtitles", cls: "lkd-row--new", lane: cueClips() });
				}
				rows.push({ name: "V2", lane: p.stills.map((s, i) => clip(s.at, s.at + STILL_SECONDS, s.file, `lkd-clip--still${s.fresh ? " lkd-clip--new" : ""}`)).join("") });
				rows.push({ name: "V1", lane: clip(0, D - 1.5, a.video, "lkd-clip--video") });
				rows.push({ name: "A1", lane: clip(0, D - 1.5, a.vo, "lkd-clip--audio lkd-clip--selected") + `<svg class="lkd-wave" viewBox="0 0 ${Math.round((D - 1.5) * 10)} 20" preserveAspectRatio="none" style="width:${pct(D - 1.5)}"><path d="${wavePath(voice(), Math.round((D - 1.5) * 10), 20)}"/></svg>` });
			} else {
				if (p.cues) {
					rows.push({ name: "T", sub: `Sub 01–${String(p.cues.length).padStart(2, "0")} · ${p.cues.length} text layers`, cls: "lkd-row--new", lane: cueClips() });
				}
				p.stills.slice().reverse().forEach((s) => {
					rows.push({ name: s.file, guide: s.guide, lane: clip(s.at, D - 1.5, s.file, `lkd-clip--still${s.guide ? " lkd-clip--guide" : ""}${s.fresh ? " lkd-clip--new" : ""}`) });
				});
				rows.push({ name: a.video, lane: clip(0, D - 1.5, a.video, "lkd-clip--video") });
				rows.push({ name: "Lower third", lane: clip(2, 12, "Lower third", "lkd-clip--text") });
				rows.push({ name: "Background", lane: clip(0, D - 1.5, "Background", "lkd-clip--bg") });
				rows.push({ name: a.vo, lane: clip(0, D - 1.5, a.vo, "lkd-clip--audio") + `<svg class="lkd-wave" viewBox="0 0 ${Math.round((D - 1.5) * 10)} 20" preserveAspectRatio="none" style="width:${pct(D - 1.5)}"><path d="${wavePath(voice(), Math.round((D - 1.5) * 10), 20)}"/></svg>` });
			}

			return rows;
		}

		function paintTimeline() {
			const grid = q(".lkd-tlgrid");
			const D = duration();
			const rows = timelineRows();
			const ticks = [];
			for (let s = 0; s <= D; s += 5) {
				ticks.push(`<i style="left:${((s / D) * 100).toFixed(3)}%"><b>${s}s</b></i>`);
			}

			grid.innerHTML = `<div class="lkd-tlcorner"></div><div class="lkd-ruler">${ticks.join("")}</div>` +
				rows.map((r, i) => `<div class="lkd-rname ${r.cls || ""}"><em>${i + 1}</em><span>${esc(r.name)}${r.sub ? `<small>${esc(r.sub)}</small>` : ""}</span>${r.guide ? `<u>GUIDE</u>` : ""}</div><div class="lkd-lane ${r.cls || ""}">${r.lane}</div>`).join("") +
				`<div class="lkd-playhead"><b></b></div>`;

			P().stills.forEach((s) => { s.fresh = false; });
			paintPlayhead();
		}

		function paintPlayhead() {
			const grid = q(".lkd-tlgrid");
			const head = q(".lkd-playhead");
			if (!head) {
				return;
			}
			const lane = grid.querySelector(".lkd-lane");
			const left = lane ? lane.offsetLeft : 118;
			const width = lane ? lane.clientWidth : grid.clientWidth - 118;
			head.style.left = `${(left + (P().t / duration()) * width).toFixed(1)}px`;
			paintTimecode();
		}

		function paintPanelTabs() {
			qa("[data-tab]").forEach((b) => b.setAttribute("aria-selected", String(b.getAttribute("data-tab") === S.tab)));
			qa("[data-pane]").forEach((s) => { s.hidden = s.getAttribute("data-pane") !== S.tab; });
			q(".lkd-count").textContent = String(P().bins.length);
		}

		function paintSubtabs() {
			const n = P().note;
			const box = q(".lkd-subtabs");
			box.innerHTML = `<button type="button" class="lkd-subtab" data-note="global" aria-selected="${n.active === "global"}" title="Scratchpad shared by every project">🌐 Global</button>` +
				n.tabs.map((t, i) => `<button type="button" class="lkd-subtab" data-note="${i}" aria-selected="${n.active === String(i)}">${esc(t.name)}</button>`).join("");
		}

		function blockHtml(b, i, follow) {
			const t = esc(b.t);
			if (b.k === "h1" || b.k === "h3") {
				return `<${b.k}>${t}</${b.k}>`;
			}
			if (b.k === "muted") {
				return `<p class="lkd-muted">${t}</p>`;
			}
			if (b.k === "task") {
				return `<div class="lkd-task${b.done ? " lkd-done" : ""}"><input type="checkbox" data-task="${i}" ${b.done ? "checked" : ""}><span>${t}</span></div>`;
			}
			if (b.k === "tcnote") {
				return `<p><span class="lkd-tag">[${LazyAlign.formatTimecode(b.at, FPS)}]</span>&nbsp;<span class="lkd-placeholder">${t}</span></p>`;
			}
			if (b.tag) {
				const now = follow && follow.block === b;
				const words = LazyAlign.splitWords(b.t).map((w, wi) => `<span class="lkd-w${now && follow.word === wi ? " lkd-w--now" : ""}">${esc(w)}</span>`).join(" ");
				return `<p class="lkd-timed${now ? " lkd-now" : ""}${b.fresh ? " lkd-fresh" : ""}"><span class="lkd-tag">[${LazyAlign.formatTimecode(b.tag.start, FPS)}]</span>&nbsp;${words}</p>`;
			}
			return `<p>${t}</p>`;
		}

		let shownFollow = { block: null, word: -1 };

		function paintEditor() {
			const ed = q(".lkd-editor");
			const blocks = noteBlocks();
			ed.style.fontSize = `${S.fontSize}px`;

			if (!blocks.length) {
				ed.innerHTML = `<div class="lkd-empty"><p>${esc(L.empty)}</p><div class="lkd-pastechips"><button type="button" class="lkd-chip" data-act="script" data-lang="en">${L.pasteEn}</button><button type="button" class="lkd-chip" data-act="script" data-lang="bn">${L.pasteBn}</button></div><small>Ctrl+V pastes from Google Docs or Word and keeps headings, bold and lists.</small></div>`;
				return;
			}

			ed.innerHTML = blocks.map((b, i) => blockHtml(b, i, shownFollow)).join("");
			blocks.forEach((b) => { b.fresh = false; });
		}

		function paintBins() {
			const p = P();
			const list = q(".lkd-binlist");
			const cards = p.bins.map((b, i) => `<div class="lkd-bincard${b.fresh ? " lkd-fresh" : ""}">
				<div class="lkd-bincard__top"><span class="lkd-bincard__title">📁 ${esc(b.name)}</span>
					<span class="lkd-bincard__acts">
						<button type="button" class="lkd-tool" data-bin="${i}" data-act="sync" title="Sync this folder now">⚡ Sync</button>
						<button type="button" class="lkd-tool" data-bin="${i}" data-act="open" title="Open in Explorer/Finder">📂</button>
						<button type="button" class="lkd-tool" data-bin="${i}" data-act="edit" title="Edit: change the folder, bin name or filters">✎</button>
						<button type="button" class="lkd-tool" data-bin="${i}" data-act="resetbin" title="Reset: forget what was synced and sync again">↺</button>
						<button type="button" class="lkd-tool lkd-tool--danger" data-bin="${i}" data-act="unlink" title="Unlink bin">✕</button>
					</span></div>
				<div class="lkd-bincard__path">${esc(b.path)}</div>
				<div class="lkd-bincard__bottom"><span class="lkd-chips">${b.kinds.video ? "<i>Video</i>" : ""}${b.kinds.audio ? "<i>Audio</i>" : ""}${b.kinds.image ? "<i>Image</i>" : ""}${b.sub ? "<i>Subfolders</i>" : ""}</span><span class="lkd-bincard__n">${b.count} items synced${b.pending && b.pending.done && !b.pending.imported ? ` <em>· 1 new</em>` : ""}</span></div>
			</div>`).join("");

			list.innerHTML = (p.bins.length ? cards : `<div class="lkd-emptybins"><span>📁</span><p>No watch bins linked to this project yet.</p><small>Click <b>+ Add Watch Bin</b> to link folders (e.g. SFX, Footage, Music).</small></div>`) +
				`<div class="lkd-card lkd-card--tip lkd-card--drop"><span class="lkd-tipicon">📥</span><div><button type="button" class="lkd-chip" data-act="drop" ${p.bins.some((b) => b.path === UPLOADS.path) ? "" : "disabled"}>${L.drop}</button><small>${esc(L.dropNote)}</small></div></div>`;

			p.bins.forEach((b) => { b.fresh = false; });
			q("[data-act='autosync']").checked = p.autoSync;
			q(".lkd-dot").className = `lkd-dot${S.busy.sync ? " lkd-dot--busy" : p.autoSync ? " lkd-dot--on" : ""}`;
			q(".lkd-count").textContent = String(p.bins.length);
		}

		function paintTools() {
			q("[data-act='guide']").checked = S.guide;
			q("[data-act='fit']").checked = S.fit;
			const box = q(".lkd-recent");
			box.innerHTML = S.recent.length
				? S.recent.map((f, i) => `<button type="button" class="lkd-thumb" data-act="replace" data-recent="${i}" title="${esc(f)}\nClick to place it again"><svg viewBox="0 0 400 250" aria-hidden="true">${artShot(`${u}-th${i}`)}</svg></button>`).join("")
				: `<div class="lkd-nopastes">No recent clipboard pastes yet.</div>`;
		}

		function paintStatus() {
			const el = q(".lkd-status");
			el.textContent = S.status[0];
			el.title = S.status[0];
			el.className = `lkd-status${S.status[1] ? ` lkd-${S.status[1]}` : ""}`;
		}

		function paintHint() {
			const d = S.did;
			const key = !d.script ? "start"
				: S.busy.time ? "wait"
				: !d.time ? "time"
				: !d.subs && !d.play ? "subs"
				: !d.play ? "play"
				: !(d.paste || d.bins || d.app) ? "more"
				: "done";
			q(".lkd-hint").textContent = L.hints[key];
		}

		function paintFollowBtn() {
			q("[data-act='follow']").setAttribute("aria-pressed", String(S.follow));
		}

		function paintAll() {
			paintTitle();
			paintCanvas();
			paintTimeline();
			paintPanelTabs();
			paintSubtabs();
			paintEditor();
			paintBins();
			paintTools();
			paintFollowBtn();
			paintStatus();
			paintHint();
		}

		function status(text, kind, ms) {
			clearTimeout(statusTimer);
			S.status = [text, kind || ""];
			paintStatus();
			if (ms) {
				statusTimer = setTimeout(() => {
					if (stage) {
						S.status = ["Ready", "quiet"];
						paintStatus();
					}
				}, ms);
			}
		}

		function toast(text) {
			const el = q(".lkd-toast");
			el.textContent = text;
			el.classList.add("lkd-on");
			clearTimeout(toastTimer);
			toastTimer = setTimeout(() => el.classList.remove("lkd-on"), 2200);
		}

		function nudge(text) {
			const h = q(".lkd-hint");
			h.classList.remove("lkd-nudging");
			void h.offsetWidth;
			h.classList.add("lkd-nudging");
			status(text, "", 3000);
		}

		/* ---- the playhead ---- */
		function seek(t, fromUser) {
			P().t = clamp(t, 0, duration());
			paintPlayhead();
			paintCanvas();
			showFollow();
			if (fromUser) {
				S.did.play = S.did.play || S.did.time;
				paintHint();
			}
		}

		function stopPlay() {
			const p = P();
			p.playing = false;
			if (playRaf) {
				cancelAnimationFrame(playRaf);
				playRaf = 0;
			}
			paintTimecode();
		}

		function togglePlay() {
			const p = P();
			if (p.playing) {
				stopPlay();
				return;
			}
			if (p.t >= duration() - 0.05) {
				p.t = 0;
			}
			p.playing = true;
			S.did.play = S.did.play || S.did.time;
			paintTimecode();
			paintHint();
			const t0 = p.t;
			const at0 = performance.now();
			const app0 = S.app;
			const step = (now) => {
				playRaf = 0;
				if (!stage || S.app !== app0 || !p.playing) {
					return;
				}
				p.t = t0 + (now - at0) / 1000;
				if (p.t >= duration()) {
					p.t = duration();
					paintPlayhead();
					paintCanvas();
					showFollow();
					stopPlay();
					return;
				}
				paintPlayhead();
				paintCanvas();
				showFollow();
				playRaf = requestAnimationFrame(step);
			};
			playRaf = requestAnimationFrame(step);
		}

		/* ---- following the voice in the note ---- */
		function showFollow() {
			const ed = q(".lkd-editor");
			if (!S.follow || S.tab !== "notes" || P().note.active === "global") {
				clearFollow();
				return;
			}
			const lines = timedLines();
			if (!lines.length) {
				clearFollow();
				return;
			}
			const pos = LazyAlign.followAt(lines, P().t);
			const block = pos.line >= 0 ? lines[pos.line].block : null;
			if (block === shownFollow.block && pos.word === shownFollow.word) {
				return;
			}
			shownFollow = { block, word: pos.word };
			const old = ed.querySelector(".lkd-now");
			if (old && (!block || old !== blockEl(block))) {
				old.classList.remove("lkd-now");
			}
			ed.querySelectorAll(".lkd-w--now").forEach((w) => w.classList.remove("lkd-w--now"));
			if (!block) {
				return;
			}
			const el = blockEl(block);
			if (!el) {
				return;
			}
			el.classList.add("lkd-now");
			if (pos.word >= 0) {
				const w = el.querySelectorAll(".lkd-w")[pos.word];
				if (w) {
					w.classList.add("lkd-w--now");
				}
			}
			if (old !== el && Date.now() - userAt > 2500) {
				const box = ed.getBoundingClientRect();
				const r = el.getBoundingClientRect();
				if (r.top < box.top + 8 * k || r.bottom > box.bottom - 8 * k) {
					const top = Math.max(0, ed.scrollTop + (r.top - box.top) / k - ed.clientHeight * 0.3);
					try { ed.scrollTo({ top, behavior: reduced() ? "auto" : "smooth" }); } catch (e) { ed.scrollTop = top; }
				}
			}
		}

		function blockEl(block) {
			const blocks = noteBlocks();
			const i = blocks.indexOf(block);
			return i < 0 ? null : q(".lkd-editor").children[i] || null;
		}

		function clearFollow() {
			if (!shownFollow.block && shownFollow.word < 0) {
				return;
			}
			shownFollow = { block: null, word: -1 };
			const ed = q(".lkd-editor");
			ed.querySelectorAll(".lkd-now").forEach((el) => el.classList.remove("lkd-now"));
			ed.querySelectorAll(".lkd-w--now").forEach((w) => w.classList.remove("lkd-w--now"));
		}

		/* ---- the script ---- */
		function pasteScript(which) {
			const tab = noteTab();
			if (!tab) {
				return;
			}
			const p = P();
			tab.blocks = SCRIPTS[which].map((b) => Object.assign({ fresh: true }, b));
			p.lang = which;
			p.cues = null;
			stopPlay();
			p.t = 0;
			S.did.script = true;
			paintEditor();
			paintTimeline();
			paintCanvas();
			paintHint();
			status("Notes saved", "", 1200);
		}

		function timeToAudio() {
			if (S.busy.time || S.busy.subs) {
				return;
			}
			const lines = spokenBlocks();
			if (!lines.length) {
				nudge(L.need.script);
				status("Write or paste the script first: lines or whole paragraphs, in the order they are spoken", "", 5000);
				return;
			}
			const a = app();
			S.busy.time = true;
			qa("[data-act='time'],[data-act='subs']").forEach((b) => { b.disabled = true; });
			status("Listening to the timeline audio...", "");
			q(".lkd-timeline").classList.add("lkd-listening");
			paintHint();

			later(1500, () => {
				const v = voice();
				const speech = LazyAlign.findSpeech({ step: v.step, values: v.values });
				const spans = LazyAlign.alignScript(lines.map((b) => b.t), speech);
				const release = () => {
					S.busy.time = false;
					q(".lkd-timeline").classList.remove("lkd-listening");
					qa("[data-act='time'],[data-act='subs']").forEach((b) => { b.disabled = false; });
				};

				if (!spans) {
					release();
					status(`No speech found in ${a.heard} of '${a.seq}'`, "", 5000);
					paintHint();
					return;
				}

				/* The tags land one line after another, as the panel writes them; the
				   buttons stay off until the last one is in. */
				const stampAt = (i) => {
					if (i >= lines.length) {
						release();
						S.did.time = true;
						status(`Timed ${lines.length} line${lines.length === 1 ? "" : "s"} to ${a.heard} of '${a.seq}'`, "ok", 4000);
						paintHint();
						showFollow();
						return;
					}
					lines[i].tag = { start: spans[i].start, end: spans[i].end, words: spans[i].words };
					lines[i].fresh = true;
					paintEditor();
					later(110, () => stampAt(i + 1));
				};
				P().cues = null;
				paintTimeline();
				paintCanvas();
				stampAt(0);
			});
		}

		function makeSubtitles() {
			if (S.busy.time || S.busy.subs) {
				return;
			}
			const timed = timedLines();
			if (!timed.length) {
				nudge(L.need.time);
				status("No timed lines yet: click 🎙️ Time to Audio first (or start lines with a timecode)", "", 5000);
				return;
			}
			const a = app();
			S.busy.subs = true;
			qa("[data-act='time'],[data-act='subs']").forEach((b) => { b.disabled = true; });
			status("Making subtitles...", "");

			later(900, () => {
				const cues = LazyAlign.makeCues(timed.map((l) => ({ start: l.start, end: l.end, text: l.text, words: l.words })));
				S.busy.subs = false;
				qa("[data-act='time'],[data-act='subs']").forEach((b) => { b.disabled = false; });
				P().cues = cues;
				S.did.subs = true;
				paintTimeline();
				paintCanvas();
				paintHint();
				const srt = `${a.seq}.srt`;
				status(S.app === "pr"
					? `Subtitles placed on a new caption track in '${a.seq}' · SRT: ${srt}`
					: `${cues.length} subtitle layer${cues.length === 1 ? "" : "s"} added to '${a.seq}' · SRT: ${srt}`, "ok", 6000);
				toast(`💬 ${cues.length} subtitles on the timeline`);
			});
		}

		/* ---- notes: tabs, tasks, timecodes ---- */
		function switchNote(id) {
			P().note.active = id;
			clearFollow();
			paintSubtabs();
			paintEditor();
			showFollow();
		}

		function addTask() {
			const blocks = noteBlocks();
			blocks.push({ k: "task", t: "New Task", done: false, fresh: true });
			paintEditor();
			scrollEditorToEnd();
			status("Notes saved", "", 1000);
		}

		function insertTimecode() {
			const blocks = noteBlocks();
			const tc = LazyAlign.formatTimecode(P().t, FPS);
			blocks.push({ k: "tcnote", at: P().t, t: "your note goes here", fresh: true });
			paintEditor();
			scrollEditorToEnd();
			status(`Inserted timecode: ${tc}`, "ok", 2000);
		}

		function scrollEditorToEnd() {
			const ed = q(".lkd-editor");
			try { ed.scrollTo({ top: ed.scrollHeight, behavior: reduced() ? "auto" : "smooth" }); } catch (e) { ed.scrollTop = ed.scrollHeight; }
		}

		function addNoteTab() {
			const n = P().note;
			n.tabs.push({ name: `Note ${n.tabs.length + 1}`, blocks: [] });
			switchNote(String(n.tabs.length - 1));
		}

		function deleteNoteTab() {
			const n = P().note;
			if (n.active === "global") {
				dialog({ title: "Notes", message: "The Global Scratchpad cannot be deleted.", cancel: false });
				return;
			}
			if (n.tabs.length <= 1) {
				dialog({ title: "Notes", message: "You must keep at least one project note tab.", cancel: false });
				return;
			}
			const idx = parseInt(n.active, 10);
			const name = n.tabs[idx].name;
			dialog({ title: "Delete note tab", message: `Delete "${name}"? Its notes cannot be brought back.`, ok: "Delete", danger: true }).then((yes) => {
				if (!yes) {
					return;
				}
				n.tabs.splice(idx, 1);
				n.active = String(Math.max(0, idx - 1));
				paintSubtabs();
				paintEditor();
				status("Note tab deleted", "", 1500);
			});
		}

		/* ---- the in-panel dialog (the panel's own, never the browser's white box) ---- */
		function dialog(opts) {
			const box = q(".lkd-dialog");
			return new Promise((resolve) => {
				box.innerHTML = `<div class="lkd-dialog__box">
					<div class="lkd-dialog__h"><h3>${esc(opts.title || "LazyKick")}</h3><button type="button" class="lkd-close" data-dialog="cancel" title="Close">×</button></div>
					<div class="lkd-dialog__b">${opts.html || `<p class="lkd-dialog__msg">${esc(opts.message || "")}</p>`}</div>
					<div class="lkd-dialog__f">${opts.cancel === false ? "" : `<button type="button" class="lkd-secondary" data-dialog="cancel">${esc(opts.cancelLabel || "Cancel")}</button>`}<button type="button" class="${opts.danger ? "lkd-danger" : "lkd-primary"}" data-dialog="ok">${esc(opts.ok || "OK")}</button></div>
				</div>`;
				box.hidden = false;
				box.onclick = (e) => {
					const b = e.target instanceof Element ? e.target.closest("[data-dialog]") : null;
					if (!b) {
						return;
					}
					const answer = b.getAttribute("data-dialog") === "ok";
					if (answer && opts.validate && !opts.validate(box)) {
						return;
					}
					box.hidden = true;
					box.onclick = null;
					resolve(answer);
				};
				const focus = box.querySelector(opts.danger ? "[data-dialog='cancel']" : "[data-dialog='ok']");
				if (focus) {
					focus.focus();
				}
				if (opts.after) {
					opts.after(box);
				}
			});
		}

		/* ---- pasting an image ---- */
		function pasteImage(again) {
			if (S.busy.paste) {
				return;
			}
			const p = P();
			const a = app();
			const btn = q(".lkd-pastebtn");
			const label = q(".lkd-pastebtn__text");
			const known = S.recent.indexOf(PASTE_FILE) !== -1;
			S.busy.paste = true;
			btn.className = "lkd-pastebtn lkd-processing";
			label.textContent = "Reading Clipboard...";
			const at = p.t;

			later(again ? 200 : 700, () => {
				status(known || again ? "Same picture — the saved file and the bin item are reused" : `Saved: ${PASTE_DIR}${PASTE_FILE}`, "", 4000);
				label.textContent = "Placing on timeline...";

				later(600, () => {
					const busy = S.app === "pr" && p.stills.some((s) => at > s.at - STILL_SECONDS + 0.01 && at < s.at + STILL_SECONDS - 0.01);
					S.busy.paste = false;
					if (busy) {
						btn.className = "lkd-pastebtn lkd-success";
						label.textContent = "Added to Project";
						status(`No free video track at the playhead: image is in the 'Pasted Images' bin (${PASTE_FILE})`, "", 5000);
					} else {
						p.stills.push({ at, file: PASTE_FILE, guide: S.guide, fresh: true });
						btn.className = "lkd-pastebtn lkd-success";
						label.textContent = "Placed on Timeline!";
						status(`${S.app === "pr" ? "Clip placed on V2" : `Layer added to '${a.seq}'`} (${PASTE_FILE})`, "ok", 4000);
						fly(() => {
							paintTimeline();
							paintCanvas();
						});
					}
					if (S.recent.indexOf(PASTE_FILE) === -1) {
						S.recent.unshift(PASTE_FILE);
					}
					S.did.paste = true;
					paintTools();
					paintHint();
					later(2500, () => {
						btn.className = "lkd-pastebtn";
						label.textContent = "Paste Image";
					});
				});
			});
		}

		/* The screenshot flies from the Paste button into the viewer, which is where the
		   clip lands on its own. */
		function fly(done) {
			const from = q(".lkd-pastebtn");
			const svg = q(".lkd-frame svg");
			if (reduced() || !from || !svg) {
				done();
				return;
			}
			const stageBox = stage.getBoundingClientRect();
			const a = from.getBoundingClientRect();
			const c = svg.getBoundingClientRect();
			const scale = c.width / VB[0];
			const el = document.createElement("div");
			el.className = "lkd-fly";
			el.innerHTML = `<svg viewBox="0 0 400 250" aria-hidden="true">${artShot(`${u}-fly${++flyId}`)}</svg>`;
			stage.appendChild(el);
			const box = (r) => ({ x: (r.left - stageBox.left) / k, y: (r.top - stageBox.top) / k, w: r.width / k, h: r.height / k });
			const src = box(a);
			src.h = src.w * 0.625;
			const dst = {
				x: (c.left - stageBox.left) / k + (238 * scale) / k,
				y: (c.top - stageBox.top) / k + (133 * scale) / k,
				w: (424 * scale) / k,
				h: (265 * scale) / k,
			};
			const t0 = performance.now();
			const step = (now) => {
				const e = easeInOut(clamp((now - t0) / 650, 0, 1));
				el.style.left = `${lerp(src.x, dst.x, e).toFixed(1)}px`;
				el.style.top = `${lerp(src.y, dst.y, e).toFixed(1)}px`;
				el.style.width = `${lerp(src.w, dst.w, e).toFixed(1)}px`;
				el.style.height = `${lerp(src.h, dst.h, e).toFixed(1)}px`;
				if (e < 1) {
					requestAnimationFrame(step);
				} else {
					el.remove();
					done();
				}
			};
			requestAnimationFrame(step);
		}

		/* ---- watch bins ---- */
		function binDialog(bin) {
			const chosen = { folder: bin ? FOLDERS.find((f) => f.path === bin.path) || null : null };
			const html = `<div class="lkd-form">
				<label>Source Folder (Computer):</label>
				<div class="lkd-folderpick">${FOLDERS.map((f, i) => `<button type="button" class="lkd-folder" data-folder="${i}" aria-pressed="${chosen.folder === f}">📁 ${esc(f.path)}<small>${f.count} files</small></button>`).join("")}${bin && bin.path === UPLOADS.path ? `<button type="button" class="lkd-folder" data-folder="uploads" aria-pressed="true">📁 ${esc(UPLOADS.path)}</button>` : ""}</div>
				<label>Target Bin Name (Project):</label>
				<input type="text" class="lkd-input" data-field="name" value="${esc(bin ? bin.name : "")}" placeholder="e.g. SFX or Footage/Interviews" spellcheck="false">
				<small>Use slashes (/) to create nested bins.</small>
				<label>Media Filters:</label>
				<div class="lkd-filters"><label><input type="checkbox" data-field="video" ${!bin || bin.kinds.video ? "checked" : ""}> 🎬 Video</label><label><input type="checkbox" data-field="audio" ${!bin || bin.kinds.audio ? "checked" : ""}> 🎵 Audio</label><label><input type="checkbox" data-field="image" ${!bin || bin.kinds.image ? "checked" : ""}> 🖼️ Image</label></div>
				<label class="lkd-check"><input type="checkbox" data-field="sub" ${!bin || bin.sub ? "checked" : ""}> Include subfolders (each one becomes a bin inside this bin)</label>
			</div>`;

			return dialog({
				title: bin ? "Edit Watch Bin" : "Link Folder to Bin",
				ok: bin ? "Save Changes" : "Save Watch Bin",
				html,
				after: (box) => {
					box.querySelectorAll("[data-folder]").forEach((b) => b.addEventListener("click", () => {
						const key = b.getAttribute("data-folder");
						chosen.folder = key === "uploads" ? { path: UPLOADS.path, name: UPLOADS.name, count: bin.count, kinds: bin.kinds } : FOLDERS[+key];
						box.querySelectorAll("[data-folder]").forEach((x) => x.setAttribute("aria-pressed", String(x === b)));
						const name = box.querySelector("[data-field='name']");
						if (!name.value.trim() || (bin && name.value === bin.name)) {
							name.value = chosen.folder.name;
						}
					}));
				},
				validate: (box) => {
					if (!chosen.folder) {
						status("Please choose a folder that exists on your computer.", "", 3000);
						return false;
					}
					if (!["video", "audio", "image"].some((f) => box.querySelector(`[data-field='${f}']`).checked)) {
						status("Turn on at least one media filter (Video, Audio or Image).", "", 3000);
						return false;
					}
					chosen.name = box.querySelector("[data-field='name']").value.trim() || chosen.folder.name;
					chosen.kinds = { video: box.querySelector("[data-field='video']").checked, audio: box.querySelector("[data-field='audio']").checked, image: box.querySelector("[data-field='image']").checked };
					chosen.sub = box.querySelector("[data-field='sub']").checked;
					return true;
				},
			}).then((ok) => (ok ? chosen : null));
		}

		function addBin() {
			binDialog(null).then((c) => {
				if (!c) {
					return;
				}
				const p = P();
				if (p.bins.some((b) => b.path === c.folder.path)) {
					dialog({ title: "Watch Bins", message: `This folder is already linked to the '${c.name}' bin.`, cancel: false });
					return;
				}
				const bin = { path: c.folder.path, name: c.name, kinds: c.kinds, sub: c.sub, count: 0, pending: null, fresh: true, files: c.folder.count };
				p.bins.push(bin);
				S.did.bins = true;
				paintBins();
				paintHint();
				syncBin(bin);
			});
		}

		function editBin(bin) {
			binDialog(bin).then((c) => {
				if (!c) {
					return;
				}
				bin.name = c.name;
				bin.kinds = c.kinds;
				bin.sub = c.sub;
				paintBins();
				status(`${bin.name}: no new files`, "", 2000);
			});
		}

		/* What a sync finds in the folder: the files it has not imported yet. */
		function syncBin(bin, quiet) {
			if (S.busy.sync) {
				return;
			}
			const p = P();
			const a = app();
			const fresh = bin.files ? bin.files - bin.count : 0;
			const pending = bin.pending && bin.pending.done && !bin.pending.imported ? 1 : 0;
			const n = fresh + pending;
			if (!n) {
				if (!quiet) {
					status(`${bin.name}: no new files`, "", 2000);
				}
				return;
			}
			S.busy.sync = true;
			paintBins();
			status(`Importing ${n} new item${n === 1 ? "" : "s"} into ${bin.name}...`, "");

			later(pending ? 900 : 1300, () => {
				S.busy.sync = false;
				bin.count += n;
				if (pending) {
					bin.pending.imported = true;
					showProjectWindow(bin.pending.file);
				}
				paintBins();
				status(`Synced ${bin.name}: ${n} imported${bin.sub && fresh ? `, ${Math.round(fresh * 0.6)} sorted into subfolder bins` : ""}`, "ok", 4000);
				toast(`⚡ ${n} file${n === 1 ? "" : "s"} imported into '${a.where === "sequence" ? "bin " : "folder "}${bin.name}'`);
			});
		}

		function syncAll() {
			const p = P();
			if (!p.bins.length) {
				status("No watch bins to sync yet", "", 2000);
				return;
			}
			const todo = p.bins.filter((b) => (b.files ? b.files - b.count : 0) + (b.pending && b.pending.done && !b.pending.imported ? 1 : 0) > 0);
			if (!todo.length) {
				status("Sync All complete: 0 imported", "ok", 3000);
				return;
			}
			syncBin(todo[0]);
		}

		function resetBin(bin) {
			dialog({
				title: "Reset watch bin",
				message: `Reset "${bin.name}"?\n\nLazyKick forgets which files it synced from this folder and syncs it again. Files that are no longer in the project come back; files still in it are not imported twice.`,
				ok: "Reset",
			}).then((yes) => {
				if (!yes) {
					return;
				}
				S.busy.sync = true;
				paintBins();
				status(`Sorting ${bin.name}...`, "");
				later(900, () => {
					S.busy.sync = false;
					paintBins();
					status(`Synced ${bin.name}: 0 imported, ${bin.count} already in the project`, "ok", 4000);
				});
			});
		}

		function unlinkBin(bin) {
			dialog({
				title: "Unlink watch bin",
				message: `Stop watching "${bin.name}"?\n\nFiles already imported stay in the project.`,
				ok: "Unlink",
				danger: true,
			}).then((yes) => {
				if (!yes) {
					return;
				}
				const p = P();
				p.bins.splice(p.bins.indexOf(bin), 1);
				hideFolderWindow();
				paintBins();
				paintPanelTabs();
			});
		}

		/* A file lands in the watched folder: it is imported once it has stopped growing,
		   by Auto-Sync when that is on, else by the next Sync. */
		let dropN = 0;

		function dropFile() {
			const p = P();
			const bin = p.bins.find((b) => b.path === UPLOADS.path);
			if (!bin || (bin.pending && !bin.pending.done)) {
				return;
			}
			const file = NEW_FILES[dropN++ % NEW_FILES.length];
			bin.pending = { file, done: false, imported: false, progress: 0 };
			S.did.bins = true;
			paintHint();
			showFolderWindow(bin);
			const t0 = performance.now();
			const dur = reduced() ? 60 : 2600;
			const step = (now) => {
				if (!stage || !bin.pending || bin.pending.imported) {
					return;
				}
				bin.pending.progress = clamp((now - t0) / dur, 0, 1);
				paintFolderWindow(bin);
				if (bin.pending.progress < 1) {
					requestAnimationFrame(step);
					return;
				}
				bin.pending.done = true;
				paintFolderWindow(bin);
				paintBins();
				if (p.autoSync) {
					status(`${file.name} is still copying — waiting until it is whole`, "", 1200);
					later(1100, () => {
						if (bin.pending && !bin.pending.imported) {
							syncBin(bin, true);
						}
					});
				} else {
					status(`${file.name} is new in ${bin.name} — press ⚡ Sync, or turn on Auto-Sync`, "", 6000);
				}
			};
			requestAnimationFrame(step);
		}

		function showFolderWindow(bin) {
			const w = q(".lkd-folderwin");
			w.classList.add("lkd-on");
			paintFolderWindow(bin);
		}

		function hideFolderWindow() {
			q(".lkd-folderwin").classList.remove("lkd-on");
			q(".lkd-projwin").classList.remove("lkd-on");
		}

		function paintFolderWindow(bin) {
			const w = q(".lkd-folderwin");
			const pend = bin.pending;
			const rows = UPLOAD_FILES.map((f, i) => `<div class="lkd-frow"><span>${f.kind}</span><b>${esc(f.name)}<small>${f.size}</small></b><em class="${i < 2 ? "lkd-ok" : "lkd-dim"}">${i < 2 ? "imported" : "already in project"}</em></div>`);
			if (pend) {
				const pct = Math.round(pend.progress * 100);
				rows.push(`<div class="lkd-frow lkd-frow--new"><span>${pend.file.kind}</span><b>${esc(pend.file.name)}<small>${pend.done ? pend.file.size : `copying… ${pct}%`}</small>${pend.done ? "" : `<i class="lkd-prog"><i style="width:${pct}%"></i></i>`}</b><em class="${pend.imported ? "lkd-ok" : pend.done ? "lkd-wait" : "lkd-dim"}">${pend.imported ? "imported" : pend.done ? (P().autoSync ? "waiting" : "new") : ""}</em></div>`);
			}
			w.innerHTML = `<div class="lkd-win__bar"><i></i><i></i><i></i><span>📁 ${esc(bin.path)}</span><button type="button" class="lkd-close" data-act="closewin">×</button></div>${rows.join("")}`;
		}

		function showProjectWindow(file) {
			const w = q(".lkd-projwin");
			const a = app();
			w.innerHTML = `<div class="lkd-win__bar"><span>Project: ${esc(a.file)}</span></div>
				<div class="lkd-prow lkd-prow--bin">📁 Client Uploads</div>
				<div class="lkd-prow">🎬 interview_take3.mp4</div>
				<div class="lkd-prow">🎵 room_tone.wav</div>
				<div class="lkd-prow lkd-prow--new">${file.kind} ${esc(file.name)}</div>
				<div class="lkd-prow lkd-prow--bin lkd-dim">📁 Pasted Images</div>`;
			w.classList.add("lkd-on");
			later(3200, () => hideFolderWindow());
		}

		/* ---- apps, reset ---- */
		function setApp(next) {
			if (S.app === next) {
				return;
			}
			stopPlay();
			hideFolderWindow();
			S.app = next;
			S.did.app = true;
			clearFollow();
			shownFollow = { block: null, word: -1 };
			paintAll();
			status(`Loaded project: ${app().file}`, "", 2000);
		}

		function reset() {
			stopPlay();
			timers.splice(0).forEach(clearTimeout);
			S = start();
			shownFollow = { block: null, word: -1 };
			hideFolderWindow();
			q(".lkd-dialog").hidden = true;
			q(".lkd-timeline").classList.remove("lkd-listening");
			qa("[data-act='time'],[data-act='subs']").forEach((b) => { b.disabled = false; });
			const btn = q(".lkd-pastebtn");
			btn.className = "lkd-pastebtn";
			q(".lkd-pastebtn__text").textContent = "Paste Image";
			paintAll();
		}

		/* ---- input ---- */
		root.addEventListener("click", (e) => {
			if (!stage || !(e.target instanceof Element)) {
				return;
			}
			const t = e.target;
			let el;

			if (t.closest(".lkd-dialog")) {
				return;
			}

			if ((el = t.closest("[data-app]"))) {
				setApp(el.getAttribute("data-app"));
			} else if ((el = t.closest("[data-tab]"))) {
				S.tab = el.getAttribute("data-tab");
				paintPanelTabs();
				showFollow();
			} else if ((el = t.closest("[data-note]"))) {
				switchNote(el.getAttribute("data-note"));
			} else if ((el = t.closest("[data-task]"))) {
				const b = noteBlocks()[+el.getAttribute("data-task")];
				if (b) {
					b.done = el.checked;
					el.closest(".lkd-task").classList.toggle("lkd-done", b.done);
					status("Notes saved", "", 1000);
				}
			} else if ((el = t.closest("[data-seek]")) && !t.closest(".lkd-rname")) {
				const lane = el.querySelector(".lkd-lane");
				const r = (lane || el).getBoundingClientRect();
				seek(((e.clientX - r.left) / r.width) * duration(), true);
			} else if ((el = t.closest("[data-act]"))) {
				const act = el.getAttribute("data-act");
				const bin = el.hasAttribute("data-bin") ? P().bins[+el.getAttribute("data-bin")] : null;

				if (act === "reset") {
					reset();
				} else if (act === "play") {
					togglePlay();
				} else if (act === "script") {
					pasteScript(el.getAttribute("data-lang"));
				} else if (act === "time") {
					timeToAudio();
				} else if (act === "subs") {
					makeSubtitles();
				} else if (act === "follow") {
					S.follow = !S.follow;
					paintFollowBtn();
					status(S.follow ? "Following the playhead" : "Not following the playhead", "", 1500);
					showFollow();
				} else if (act === "smaller" || act === "bigger") {
					const size = clamp(S.fontSize + (act === "bigger" ? 1 : -1), 10, 24);
					if (size !== S.fontSize) {
						S.fontSize = size;
						q(".lkd-editor").style.fontSize = `${size}px`;
						status(`Notes text size: ${size} px`, "", 1500);
					}
				} else if (act === "task") {
					addTask();
				} else if (act === "timecode") {
					insertTimecode();
				} else if (act === "addtab") {
					addNoteTab();
				} else if (act === "deltab") {
					deleteNoteTab();
				} else if (act === "copy") {
					status("Note copied to clipboard!", "ok", 2000);
				} else if (act === "export") {
					const tab = noteTab();
					const name = `Note_${app().file.replace(/\.[^.]+$/, "")}_${tab ? tab.name : "Global"}.txt`.replace(/[^\w\-.]+/g, "_");
					status(`Exported note to: ${name}`, "ok", 3000);
					dialog({ title: "Note exported", message: `Saved to:\nD:\\Projects\\City Stories\\${name}`, cancel: false });
				} else if (act === "paste") {
					pasteImage(false);
				} else if (act === "replace") {
					S.tab = "tools";
					pasteImage(true);
				} else if (act === "refresh") {
					status("Refreshed project info", "", 1500);
				} else if (act === "guide") {
					S.guide = el.checked;
				} else if (act === "fit") {
					S.fit = el.checked;
				} else if (act === "openfolder") {
					toast(S.recent.length ? "📂 Opened the Pasted Images folder" : "Nothing pasted into Pasted Images yet");
				} else if (act === "clearrecent") {
					S.recent = [];
					paintTools();
				} else if (act === "addbin") {
					addBin();
				} else if (act === "syncall") {
					syncAll();
				} else if (act === "autosync") {
					P().autoSync = el.checked;
					paintBins();
					const bin = P().bins.find((b) => b.pending && b.pending.done && !b.pending.imported);
					if (el.checked && bin) {
						later(900, () => syncBin(bin, true));
					}
				} else if (act === "drop") {
					dropFile();
				} else if (act === "sync" && bin) {
					syncBin(bin);
				} else if (act === "open" && bin) {
					toast(`📂 Opened ${bin.path}`);
				} else if (act === "edit" && bin) {
					editBin(bin);
				} else if (act === "resetbin" && bin) {
					resetBin(bin);
				} else if (act === "unlink" && bin) {
					unlinkBin(bin);
				} else if (act === "closewin") {
					hideFolderWindow();
				}
			}
		});

		root.addEventListener("keydown", (e) => {
			if (!stage || !(e.target instanceof Element)) {
				return;
			}
			if (e.key === " " && !e.target.closest("button, input, .lkd-dialog") && e.target.closest(".lkd-timeline, .lkd-editor, .lkd-viewer")) {
				e.preventDefault();
				togglePlay();
				return;
			}
			const dir = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
			const el = dir ? e.target.closest('[role="tab"]') : null;
			if (!el) {
				return;
			}
			e.preventDefault();
			const group = Array.from(el.parentElement.querySelectorAll('[role="tab"]'));
			const next = group[(group.indexOf(el) + dir + group.length) % group.length];
			next.click();
			next.focus();
		});

		["wheel", "mousedown", "touchstart"].forEach((type) => {
			root.addEventListener(type, (e) => {
				if (e.target instanceof Element && e.target.closest(".lkd-editor")) {
					userAt = Date.now();
				}
			}, { passive: true });
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
		const els = Array.from(document.querySelectorAll("[data-lazykick-demo]"));

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

	window.LazyKickDemo = { mount };

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", auto);
	} else {
		auto();
	}
})();
