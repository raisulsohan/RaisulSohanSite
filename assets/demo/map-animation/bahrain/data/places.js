// Places used by the map parts: [lon, lat]. Checked against the Sentinel-2 image (Feb 2025) where the
// photo shows the place (causeway, refinery tanks, Muharraq old town), otherwise public references.
window.PLACES = {
  bahrain: [50.56, 26.03],
  manama: [50.586, 26.222],
  muharraq: [50.612, 26.255],
  riffa: [50.555, 26.13],
  sitra: [50.628, 26.152],
  hawar: [50.765, 25.66],
  jabalDukhan: [50.548, 26.034],      // highest point, 134 m (Mountain of Smoke)
  qalatBahrain: [50.520, 26.233],     // Bahrain Fort, Dilmun capital site
  refinery: [50.624, 26.147],         // Bapco refinery tank farm, Sitra (rows of round tanks in the photo)
  navalBase: [50.6097, 26.2097],      // Naval Support Activity Bahrain, Juffair (US Fifth Fleet HQ)
  khobar: [50.208, 26.285],
  dammam: [50.103, 26.427],
  doha: [51.531, 25.286],
  riyadh: [46.716, 24.713],
  kuwait: [47.978, 29.376],
  abuDhabi: [54.377, 24.453],
  dubai: [55.296, 25.276],
  muscat: [58.405, 23.588],
  tehran: [51.389, 35.689],
  bandarAbbas: [56.27, 27.18],
  hormuz: [56.35, 26.55],
  // Bronze Age trade (Dilmun)
  ur: [46.103, 30.963],
  mohenjoDaro: [68.138, 27.329],
  lothal: [72.249, 22.522],
  // pearl buyers
  paris: [2.35, 48.857],
  london: [-0.128, 51.507],
  newYork: [-74.006, 40.713],
  bombay: [72.877, 19.076],
  beirut: [35.502, 33.894],
};

// King Fahd Causeway, Saudi shore (Al Khobar) → Saudi island → border islands → Umm an Nasan → Bahrain (Al Jasra), traced on the photo
window.CAUSEWAY = [[50.214, 26.2218], [50.236, 26.2207], [50.255, 26.2150], [50.290, 26.2010], [50.316, 26.1890],
  [50.322, 26.1855], [50.335, 26.1835], [50.370, 26.1812], [50.392, 26.1790], [50.402, 26.1735], [50.418, 26.1712], [50.448, 26.1720]];

// Pearling Path, Muharraq: Bu Maher fort at the island's southern tip, north through the old town (illustrative line)
window.PEARL_PATH = [[50.6081, 26.2443], [50.6092, 26.2469], [50.6106, 26.2498], [50.6120, 26.2527], [50.6114, 26.2557], [50.6128, 26.2588]];

// a sea route of the Bronze Age trade: Ur → down the Gulf → Dilmun → Strait of Hormuz → Makran coast → Indus delta
window.DILMUN_ROUTE_W = [[46.10, 30.96], [47.6, 30.0], [48.9, 29.25], [49.8, 28.2], [50.3, 27.2], [50.52, 26.25]];
window.DILMUN_ROUTE_E = [[50.52, 26.25], [51.6, 26.55], [53.6, 26.3], [55.6, 26.45], [56.6, 26.1], [57.6, 25.2], [60.5, 25.05], [63.5, 25.1], [66.4, 24.75], [67.4, 24.4], [68.14, 27.33]];

// large desalination plants around the Gulf (approximate; shown as unnamed dots)
window.DESAL = [[48.38, 28.72], [47.83, 29.36], [49.27, 27.47], [49.65, 27.10], [50.21, 26.32], [50.665, 26.235], [50.63, 25.98],
  [51.56, 25.92], [51.62, 25.21], [51.60, 24.98], [52.44, 24.13], [54.70, 24.77], [54.51, 24.44], [55.06, 24.98], [56.20, 27.16],
  [50.85, 28.95], [53.98, 26.54]];
