// 23 s highlights cut from the rendered parts (render/NN-name.mp4). One source of truth for the page and tools/build_highlights_mix.py.
// t0 = start in the highlight (s), d = length (s), in = start in the source clip (s), rate = source seconds per highlight second.
// rates (optional) = speed ramp inside a shot: [[local s, rate from here], ...]; each change is a linear ramp `ramp` s wide,
//   centred on the knot, so the source time after a ramp is exactly the plain step sum (the voice stays in sync).
// fx: "punch" = the shot settles from a small zoom; "flash" = a short light flash on the cut. Cuts sit on the 120 BPM beat grid.
window.HL = {
"bpm": 120, "dur": 23.0, "fps": 30,
"shots": [
  {"clip": "01-hook",        "t0": 0.0,  "d": 9.0, "in": 0.0,  "rate": 1.0, "rates": [[0, 1.0], [3.4, 2.15], [4.4, 1.0]], "ramp": 0.3, "fx": "",
   "note": "lock-on, counter to 1,500,000; ramp through 'live on a desert archipelago'; then the river dries, the lake shrinks, the rain dries"},
  {"clip": "04-1980s",       "t0": 9.0,  "d": 1.5, "in": 3.5,  "rate": 1.2, "fx": "flash", "note": "1980s coast fills, area counts to 665 km2"},
  {"clip": "10-refinery",    "t0": 10.5, "d": 1.5, "in": 5.9,  "rate": 1.4, "fx": "punch", "note": "oil routes run out from the refinery"},
  {"clip": "07-dilmun",      "t0": 12.0, "d": 3.0, "in": 10.6, "rate": 1.6, "fx": "punch", "note": "Bronze Age trade route, to the end: the Indus Valley (Pakistan) fills"},
  {"clip": "11-causeway",    "t0": 15.0, "d": 1.5, "in": 2.4,  "rate": 1.3, "fx": "punch", "note": "causeway draws to Saudi Arabia"},
  {"clip": "15-strikes",     "t0": 16.5, "d": 3.5, "in": 5.1,  "rate": 1.8, "fx": "flash", "note": "missiles cross the Gulf, to the end: the hits on the western shore"},
  {"clip": "16-summer",      "t0": 20.0, "d": 3.0, "in": 0.4,  "rate": 1.0, "fx": "flash", "note": "strikes on Bahrain, end title"}
],
"voice": {"clip": "01-hook", "parts": [[0.0, 3.40], [5.50, 9.95]]},
"hits": [[3.4, 0.4]],
"email": {"text": "lettertosohan@gmail.com",
  "note": "the email sits in the map: on the sand (terrain) or under the sea (water). Each label is placed on screen at a reference source time (ref, x, y = centre in px, w = width px, ang = deg) and then follows the map; tracks in highlights/email-track.js (tools/track_email.js)",
  "labels": [
    {"shot": 0, "ref": 0.69, "x": 929,  "y": 822, "w": 460, "ang": -25, "style": "terrain", "win": [0, 1.03],    "fade": [0, 0]},
    {"shot": 0, "ref": 2.61, "x": 971,  "y": 170, "w": 620, "ang": -4,  "style": "water",   "win": [1.67, 3.7],  "fade": [0, 0.35]},
    {"shot": 0, "ref": 5.29, "x": 790,  "y": 660, "w": 340, "ang": 0,   "style": "water",   "win": [3.85, 10.5], "fade": [0.4, 0]},
    {"shot": 1, "ref": 4.4,  "x": 1560, "y": 230, "w": 440, "ang": 0,   "style": "water"},
    {"shot": 2, "ref": 6.9,  "x": 1660, "y": 670, "w": 420, "ang": -10, "style": "water"},
    {"shot": 3, "ref": 13.0, "x": 1480, "y": 930, "w": 520, "ang": -6,  "style": "water"},
    {"shot": 4, "ref": 3.4,  "x": 1480, "y": 230, "w": 480, "ang": 0,   "style": "water"},
    {"shot": 5, "ref": 8.2,  "x": 450,  "y": 930, "w": 440, "ang": -20, "style": "terrain"},
    {"shot": 6, "ref": 1.0,  "x": 1330, "y": 770, "w": 340, "ang": 0,   "style": "water"}
  ]},
"frame45": {"kicker": "MAP ANIMATION · BAHRAIN", "head": ["1.5 MILLION PEOPLE.", "NO RIVERS."],
  "foot": ["WANT A MAP ANIMATION FOR YOUR VIDEO?", "lettertosohan@gmail.com"]},
"title": {"t": 20.8, "kicker": "MAP ANIMATION", "main": "BAHRAIN",
  "cta": ["WANT AN EXCITING MAP ANIMATION FOR YOUR VIDEO?", "LET'S WORK TOGETHER."],
  "contacts": [["EMAIL", "lettertosohan@gmail.com"], ["WA", "+880 1775 860544"], ["WEB", "raisulsohan.com/en/portfolio"]]}
};
