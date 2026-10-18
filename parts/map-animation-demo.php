<?php
/**
 * The map animation demos page, served by rs_map_demo_render().
 *
 * English on both editions. One iframe plays any demo from
 * assets/demo/map-animation/; the players talk back over postMessage
 * (time, posterize, the demo and frame keys). Static output, so the full
 * page cache can keep it as it is.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rs_md_base  = RS_URI . '/assets/demo/map-animation/';
$rs_md_email = rs_option( 'rs_email' ) ? rs_option( 'rs_email' ) : 'lettertosohan@gmail.com';
$rs_md_phone = '01775860544';
$rs_md_wa    = 'https://wa.me/8801775860544';

/* The English edition is the canonical one; the Bengali edition shows the same page. */
$rs_md_pair      = rs_edition_pair();
$rs_md_canonical = ( $rs_md_pair && ! rs_is_en() ) ? rs_map_demo_url( $rs_md_pair['other'] ) : rs_map_demo_url();

$rs_md_title = 'Map Animation Demos';
$rs_md_desc  = 'Thirteen map animations with voice-over, drawn in code by Raisul Sohan: explainer and data maps, crime documentary maps, a Vox-style reference recreation, a desk-and-TV explainer, an animated war front map and a 3D satellite news map.';

/* Sections with the same row number sit side by side.
   Each item: id, file, tag, title, description, alt text, and optionally
   the file of its own 4:5 cut (otherwise the 4:5 frame is file?ar=45). */
$rs_md_sections = array(
	array(
		'row'   => 1,
		'title' => 'Explainer & Data Maps',
		'sub'   => 'Bangladesh and South Asia in four looks: cut paper, a dive from orbit, neon data routes and 3D towers.',
		'items' => array(
			array( '01', '01-vox-delta.html', '01 · Vox explainer', "The World's Largest Delta", 'Paper texture, a country that lifts off the page, rivers that draw themselves, halftone fill, locator globe, callout card.', 'Paper map of Bangladesh with rivers and halftone delta' ),
			array( '02', '02-globe-dive.html', '02 · Documentary globe dive', 'From Orbit to Dhaka', 'Satellite globe with orbit HUD, a dive from space, a light-leak change into a paper map, red marker and a taped typewriter note.', 'Paper map with red marker circle around Dhaka and a typewriter note' ),
			array( '03', '03-neon-routes.html', '03 · Dark data map', 'Remittance Routes', 'Dot-matrix world, scanner sweep, glowing great-circle routes that land on one hub, scramble titles and a data panel.', 'Dark dot-matrix map with glowing routes into Dhaka' ),
			array( '04', '04-3d-density.html', '04 · 3D data map', 'The Most Crowded Big Country', 'Countries extruded by population density with real lighting and shadows, a staggered rise and a camera dolly onto the tallest tower.', '3D extruded map of South Asia with Bangladesh as the tallest tower' ),
		),
	),
	array(
		'row'   => 2,
		'title' => 'Crime Documentary Maps',
		'sub'   => 'True-crime styles. The cities, cases and people are fictional.',
		'items' => array(
			array( '05', '05-crime-evidence-board.html', '05 · Evidence board', 'Case #0417', 'City map pinned to cork, polaroids, pins that drop, red string, sticky note, newspaper clipping and an UNSOLVED stamp with camera shake.', 'City map pinned on a cork board with red string, photos and an UNSOLVED stamp' ),
			array( '06', '06-crime-surveillance.html', '06 · Surveillance', 'Signal Lost', 'Night map, timestamped route with a running clock, CCTV stills, a movements timeline, cell-tower triangulation and a SIGNAL LOST glitch.', 'Dark night map with a red tracked route, tower rings and a last known area' ),
			array( '07', '07-crime-dossier-route.html', '07 · Case dossier', 'The Escape Route', 'Printed grey map of Europe, red dashed route with dated stops, mugshot card, CONFIDENTIAL stamp and redaction bars.', 'Grey hatched map of Europe with a red dashed route and date stamps' ),
			array( '08', '08-crime-search-grid.html', '08 · Search operation', 'Outside the Search Zone', 'Topographic terrain, last known location, probability rings, sectors cleared by a helicopter, then a find 2.4 km away.', 'Green topographic map with a search grid and a red find marker' ),
		),
	),
	array(
		'row'   => 3,
		'title' => 'Reference Recreation',
		'sub'   => '09 is rebuilt from a reference clip (the Vox-style Manhattan reveal), matched by measuring its frames; 10 takes the same idea to Dhaka in a new style. Real map data © OpenStreetMap contributors, tiles by OpenFreeMap.',
		'items' => array(
			array( '09', '09-manhattan-vox.html', '09 · Vox-style Manhattan', 'Manhattan, New York', 'Paper texture, a soft street wipe, a typed serif title, Manhattan filling block by block from north to south, mint parks and a slow ease-out push.', 'Paper map of New York with Manhattan filled in green and the title Manhattan, New York' ),
			array( '10', '10-dhaka-night.html', '10 · Dhaka, night gold', 'Dhaka, Bangladesh', 'The same reveal in a new style: a navy night map, streets lighting up from the centre, neighbourhoods switching on in gold from Old Dhaka northward, teal parks, a rising title.', 'Night map of Dhaka with neighbourhoods lit in gold and the title Dhaka, Bangladesh' ),
		),
	),
	array(
		'row'   => 3,
		'title' => 'Desk & TV Explainer',
		'sub'   => 'A longer piece (26 s with the voice-over) with its own score. The coast and islands are illustrative, not to scale.',
		'items' => array(
			array( '11', 'shifting-shores.html', '11 · Shifting Shores', 'Proof on Paper', 'A retro TV switches on, then 1973 and 2023 survey sheets on a desk: a highlighted coastline, an island that keeps growing, a NEW LAND stamp.', 'Two survey maps on a wooden desk, 1973 in blue and 2023 in yellow, with a NEW LAND stamp' ),
		),
	),
	array(
		'row'   => 3,
		'title' => 'War Front Map',
		'sub'   => 'The "every day" history-map style. Front lines simplified from campaign maps; terrain from AWS Terrain Tiles, borders from Natural Earth.',
		'items' => array(
			array( '12', '12-korean-war.html', '12 · Animated war map', 'The Korean War', 'Shaded relief, red and blue sides, a front of waving flags that moves with a running date, explosions, event cards and a live territory counter.', 'Relief map of Korea, the north red and the south blue, with flags along the front and an Inchon Landing card' ),
		),
	),
	array(
		'row'   => 4,
		'title' => '3D Satellite News Map',
		'sub'   => 'A breaking-news piece (25 s with the voice-over) in 3D over real satellite relief, with its own 4:5 cut. Imagery from Esri World Imagery, terrain from AWS Terrain Tiles, coastline © OpenStreetMap contributors, borders from geoBoundaries.',
		'items' => array(
			array( '13', 'hormuz/hormuz-v2.html', '13 · 3D satellite map', 'Strait of Hormuz', 'Countries tinted over satellite relief, glowing type laid on the ground, a running clock, a destroyer on a dashed route, three missiles off the rail and an E-2D Hawkeye.', 'Red-tinted 3D satellite map of the Strait of Hormuz with three missiles launching from the coast near Bandar Abbas', 'hormuz/hormuz-v2-4x5.html' ),
		),
	),
);

$rs_md_count = 0;
foreach ( $rs_md_sections as $rs_md_s ) {
	$rs_md_count += count( $rs_md_s['items'] );
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $rs_md_title . ' · ' . get_bloginfo( 'name' ) ); ?></title>
<meta name="description" content="<?php echo esc_attr( $rs_md_desc ); ?>">
<link rel="canonical" href="<?php echo esc_url( $rs_md_canonical ); ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo esc_attr( $rs_md_title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $rs_md_desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( $rs_md_canonical ); ?>">
<meta property="og:image" content="<?php echo esc_url( $rs_md_base . 'thumbs/10.jpg' ); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0c0a13">
<?php wp_site_icon(); ?>
<?php rs_inline_fonts(); ?>
<style>
	:root {
		--bg: #0c0a13; --card: #14111f; --card2: #1b1728; --line: #2c2642; --fg: #f5f3fb; --mut: #9a93b5;
		--acc: #8f74ff; --brand: #6c4cff; --pink: #ea77ff; --key: #ffd166; --ok: #3ddc97;
		--serif: "Noto Serif Bengali", Georgia, Cambria, "Times New Roman", serif;
		color-scheme: dark;
	}
	* { box-sizing: border-box; }
	html { scroll-behavior: smooth; }
	body {
		margin: 0; color: var(--fg); font: 16px/1.6 var(--serif);
		background: radial-gradient(1200px 600px at 10% -10%, rgba(108, 76, 255, .18), transparent 60%), var(--bg);
		min-height: 100vh;
	}
	a { color: inherit; }
	main { max-width: 1240px; margin: 0 auto; padding: 22px 16px 120px; }
	.top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 28px; font-size: 14px; }
	.top a { text-decoration: none; color: var(--mut); }
	.top a:hover { color: var(--fg); }
	.eyebrow { display: flex; align-items: center; gap: 10px; color: var(--acc); font-size: 12px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 8px; }
	.eyebrow::before { content: ""; width: 34px; height: 1px; background: currentColor; }
	h1 { font-size: clamp(30px, 5vw, 46px); line-height: 1.15; font-weight: 700; margin: 0 0 10px; letter-spacing: -.5px; }
	h1 em { font-style: normal; background: linear-gradient(135deg, var(--acc), var(--pink)); -webkit-background-clip: text; background-clip: text; color: transparent; }
	p.lead { color: #c4bdd8; margin: 0 0 28px; max-width: 86ch; font-size: 15px; }
	kbd { font: 600 12px/1 ui-monospace, Consolas, monospace; padding: 3px 6px; border: 1px solid var(--line); border-bottom-width: 2px; border-radius: 5px; color: var(--fg); background: var(--card); }

	/* the player: one iframe, any demo, 16:9 or 4:5 */
	#playerBox { background: #050506; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; scroll-margin-top: 16px; box-shadow: 0 30px 80px -40px rgba(108, 76, 255, .55); }
	#screen { display: block; width: 100%; height: 600px; border: 0; background: #050506; }
	#ctl { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 12px 14px; border-top: 1px solid var(--line); background: var(--card); }
	#ctl .now { flex: 1; min-width: 200px; }
	#ctl .now b { display: block; font-size: 18px; line-height: 1.3; }
	.btn { all: unset; display: inline-grid; place-items: center; min-width: 38px; height: 36px; padding: 0 12px; border: 1px solid var(--line); border-radius: 10px; cursor: pointer; font: 600 13px/1 var(--serif); color: var(--fg); background: var(--card2); }
	.btn:hover { border-color: var(--acc); }
	.btn:focus-visible { outline: 2px solid var(--key); outline-offset: 2px; }
	.seg { display: inline-flex; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
	.seg button { all: unset; padding: 0 14px; height: 34px; display: grid; place-items: center; cursor: pointer; font: 600 13px/1 var(--serif); color: var(--mut); }
	.seg button.on { background: linear-gradient(135deg, var(--brand), var(--acc)); color: #fff; }
	.seg button:focus-visible { outline: 2px solid var(--key); outline-offset: -2px; }
	#ctl a.btn { text-decoration: none; }

	/* demo cards: sections in one row share the columns; subgrid keeps headers and cards on the same lines */
	.row { display: grid; grid-template-columns: repeat(var(--n), minmax(0, 1fr)); grid-template-rows: auto auto; column-gap: 18px; margin-top: 48px; }
	.block { display: grid; grid-column: span var(--k); grid-row: span 2; grid-template-columns: subgrid; grid-template-rows: subgrid; }
	.block > header { grid-column: 1 / -1; padding-bottom: 18px; }
	h2.sec { display: flex; align-items: center; gap: 10px; font-size: 23px; font-weight: 700; margin: 0 0 4px; }
	h2.sec .n { font-size: 12px; font-weight: 600; color: var(--mut); border: 1px solid var(--line); border-radius: 20px; padding: 2px 9px; }
	p.sub { color: var(--mut); margin: 0; font-size: 14px; max-width: 70ch; }
	a.card { display: flex; flex-direction: column; background: var(--card); border: 1px solid var(--line); border-radius: 14px; overflow: hidden; color: inherit; text-decoration: none; transition: transform .15s, border-color .15s, box-shadow .15s; }
	a.card:hover { transform: translateY(-3px); border-color: var(--acc); box-shadow: 0 14px 34px -14px rgba(143, 116, 255, .55); }
	a.card:focus-visible { outline: 2px solid var(--key); outline-offset: 3px; }
	a.card.on { border-color: var(--acc); box-shadow: 0 0 0 1px var(--acc); }
	a.card img { display: block; width: 100%; aspect-ratio: 16/9; object-fit: cover; background: #000; }
	.body { padding: 14px 16px 16px; }
	.tag { font-size: 12px; font-weight: 600; color: var(--acc); letter-spacing: 1.5px; text-transform: uppercase; }
	.body h3 { font-size: 18px; margin: 4px 0 6px; line-height: 1.35; }
	.body p { margin: 0; color: var(--mut); font-size: 14px; }
	.playing { display: none; font-size: 11px; font-weight: 600; color: #fff; background: linear-gradient(135deg, var(--brand), var(--pink)); padding: 2px 8px; border-radius: 6px; margin-left: 6px; letter-spacing: 0; text-transform: none; }
	a.card.on .playing { display: inline-block; }
	/* a row with one demo: the card lies on its side, picture left */
	@media (min-width: 1001px) {
		.row.solo .block.one > a.card { flex-direction: row; }
		.row.solo .block.one > a.card img { width: 50%; height: auto; }
		.row.solo .block.one .body { align-self: center; padding: 22px 28px; }
	}
	@media (max-width: 1000px) {
		.row { display: block; margin-top: 0; }
		.block { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: none; grid-row: auto; gap: 18px; margin-top: 40px; }
		.block > header { padding-bottom: 0; }
		.block.one > a.card { grid-column: 1 / -1; flex-direction: row; }
		.block.one > a.card img { width: 50%; height: auto; }
	}
	@media (max-width: 600px) {
		.block { grid-template-columns: minmax(0, 1fr); }
		.block.one > a.card { flex-direction: column; }
		.block.one > a.card img { width: 100%; }
		.keys { display: none; }
	}
	footer.foot { margin-top: 56px; padding-top: 20px; border-top: 1px solid var(--line); color: var(--mut); font-size: 13px; }

	/* Work with me: floats in the corner on every scroll position */
	.wwm { position: fixed; right: 22px; bottom: 22px; z-index: 50; display: flex; flex-direction: column; align-items: flex-end; gap: 12px; }
	.wwm-btn {
		all: unset; display: inline-flex; align-items: center; gap: 10px; cursor: pointer;
		padding: 13px 22px 13px 18px; border-radius: 999px; font: 700 15px/1 var(--serif); color: #fff;
		background: linear-gradient(135deg, var(--brand), var(--pink));
		box-shadow: 0 16px 36px -12px rgba(108, 76, 255, .85), 0 0 0 1px rgba(255, 255, 255, .12) inset;
		animation: wwm-float 3.2s ease-in-out infinite;
	}
	.wwm-btn:hover { animation-play-state: paused; box-shadow: 0 20px 44px -12px rgba(234, 119, 255, .8), 0 0 0 1px rgba(255, 255, 255, .2) inset; }
	.wwm-btn:focus-visible { outline: 2px solid var(--key); outline-offset: 4px; }
	.wwm-btn .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--ok); box-shadow: 0 0 0 0 rgba(61, 220, 151, .7); animation: wwm-pulse 2s ease-out infinite; }
	@keyframes wwm-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
	@keyframes wwm-pulse { 0% { box-shadow: 0 0 0 0 rgba(61, 220, 151, .6); } 100% { box-shadow: 0 0 0 9px rgba(61, 220, 151, 0); } }
	.wwm-card {
		width: min(300px, calc(100vw - 32px)); padding: 16px; border-radius: 16px; background: rgba(20, 17, 31, .97);
		border: 1px solid var(--line); box-shadow: 0 24px 60px -20px rgba(0, 0, 0, .8); font-size: 14px;
		transform-origin: bottom right; animation: wwm-in .18s ease-out;
	}
	.wwm-card[hidden] { display: none; }
	@keyframes wwm-in { from { opacity: 0; transform: translateY(8px) scale(.97); } }
	.wwm-card .hd { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 700; }
	.wwm-card .x { all: unset; cursor: pointer; color: var(--mut); font-size: 20px; line-height: 1; padding: 2px 6px; border-radius: 6px; }
	.wwm-card .x:hover { color: var(--fg); }
	.wwm-card .x:focus-visible { outline: 2px solid var(--key); }
	.wwm-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 12px; background: var(--card2); }
	.wwm-row + .wwm-row { margin-top: 8px; }
	.wwm-row small { display: block; color: var(--mut); font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
	.wwm-row b { font-size: 14px; overflow-wrap: anywhere; }
	.wwm-row .ok { margin-left: 6px; color: var(--ok); font-weight: 700; letter-spacing: 0; text-transform: none; }
	.wwm-row a.btn { height: 32px; font-size: 12px; text-decoration: none; }
	@media (max-width: 600px) { .wwm { right: 16px; bottom: 16px; } }
	/* The stack's own box is taller than its buttons (the hidden back-to-top keeps its place); clicks on the empty part reach the page. */
	.wwm { pointer-events: none; }
	.wwm > * { pointer-events: auto; }

	/* Back to top: the site's button (44px circle, 18px arrow) in the portfolio's dark glass, above Work with me */
	.to-top {
		all: unset; box-sizing: border-box; display: grid; place-items: center; width: 44px; height: 44px; cursor: pointer;
		border: 1px solid var(--line); border-radius: 50%; color: #fff; background: rgba(22, 18, 42, .86);
		-webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); box-shadow: 0 14px 34px -14px rgba(108, 76, 255, .8);
		opacity: 0; visibility: hidden; transform: translateY(8px); transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
	}
	.to-top.is-visible { opacity: 1; visibility: visible; transform: none; }
	.to-top:hover { border-color: transparent; background: linear-gradient(135deg, var(--brand), var(--pink)); }
	.to-top:focus-visible { outline: 2px solid var(--key); outline-offset: 3px; }
	.to-top svg { display: block; }
	@media (prefers-reduced-motion: reduce) { .wwm-btn, .wwm-btn .dot, .wwm-card { animation: none; } .to-top { transition: none; } html { scroll-behavior: auto; } }
</style>
</head>
<body>
<main>
	<nav class="top" aria-label="Site">
		<a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">← Portfolio</a>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
	</nav>

	<div class="eyebrow">Map animation</div>
	<h1>Every map, <em>a story in motion</em></h1>
	<p class="lead"><?php echo esc_html( $rs_md_count ); ?> map styles with voice-over in one place. Pick a demo and it plays here, in 16:9 (1920×1080) or 4:5 (1080×1350), at 30 fps.
		Turn on <b>Posterize</b> in the player bar (or press <kbd>P</kbd>) for an After Effects–style stepped look at 12, 8 or 6 fps.
		<span class="keys">Keys: <kbd>Space</kbd> play/pause, <kbd>←</kbd>/<kbd>→</kbd> step frames, <kbd>Shift</kbd>+<kbd>←</kbd>/<kbd>→</kbd> ±1 s,
		<kbd>M</kbd> mute, <kbd>L</kbd> loop, <kbd>F</kbd> fullscreen, <kbd>V</kbd> 16:9 ⇄ 4:5, <kbd>[</kbd>/<kbd>]</kbd> previous/next demo.</span></p>

	<section id="playerBox" aria-label="Player">
		<iframe id="screen" title="Map animation player" allow="autoplay; fullscreen" allowfullscreen></iframe>
		<div id="ctl">
			<button class="btn" id="bPrevDemo" title="Previous demo ([)" aria-label="Previous demo">◀</button>
			<div class="now"><span class="tag" id="nowTag"></span><b id="nowTitle"></b></div>
			<div class="seg" role="group" aria-label="Frame">
				<button id="ar169" title="16:9 · 1920×1080 (V)">16:9</button><button id="ar45" title="4:5 · 1080×1350 (V)">4:5</button>
			</div>
			<a class="btn" id="openTab" target="_blank" rel="noopener" title="Open this demo on its own page">Open ↗</a>
			<button class="btn" id="bNextDemo" title="Next demo (])" aria-label="Next demo">▶</button>
		</div>
	</section>

	<div id="list">
		<?php
		$rs_md_rows = array();
		foreach ( $rs_md_sections as $rs_md_s ) {
			$rs_md_rows[ $rs_md_s['row'] ][] = $rs_md_s;
		}
		foreach ( $rs_md_rows as $rs_md_secs ) :
			$rs_md_n = 0;
			foreach ( $rs_md_secs as $rs_md_s ) {
				$rs_md_n += count( $rs_md_s['items'] );
			}
			?>
			<div class="row<?php echo 1 === $rs_md_n ? ' solo' : ''; ?>" style="--n:<?php echo (int) $rs_md_n; ?>">
				<?php foreach ( $rs_md_secs as $rs_md_s ) : $rs_md_k = count( $rs_md_s['items'] ); ?>
					<section class="block<?php echo 1 === $rs_md_k ? ' one' : ''; ?>" style="--k:<?php echo (int) $rs_md_k; ?>">
						<header>
							<h2 class="sec"><?php echo esc_html( $rs_md_s['title'] ); ?><span class="n"><?php echo esc_html( 1 === $rs_md_k ? '1 demo' : $rs_md_k . ' demos' ); ?></span></h2>
							<p class="sub"><?php echo esc_html( $rs_md_s['sub'] ); ?></p>
						</header>
						<?php foreach ( $rs_md_s['items'] as $rs_md_i ) : ?>
							<a class="card" href="#<?php echo esc_attr( $rs_md_i[0] ); ?>" data-id="<?php echo esc_attr( $rs_md_i[0] ); ?>">
								<img src="<?php echo esc_url( $rs_md_base . 'thumbs/' . $rs_md_i[0] . '.jpg?ver=' . RS_VERSION ); ?>" alt="<?php echo esc_attr( $rs_md_i[5] ); ?>" width="640" height="360" loading="lazy" decoding="async">
								<div class="body"><div class="tag"><?php echo esc_html( $rs_md_i[2] ); ?><span class="playing">Playing</span></div><h3><?php echo esc_html( $rs_md_i[3] ); ?></h3><p><?php echo esc_html( $rs_md_i[4] ); ?></p></div>
							</a>
						<?php endforeach; ?>
					</section>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<footer class="foot">Made by <a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">Raisul Sohan</a>. Every frame is drawn in code in the browser; nothing here is a video file.</footer>
</main>

<div class="wwm" id="wwm">
	<button type="button" class="to-top" id="toTop" aria-label="Back to top" title="Back to top"><?php echo wp_kses( rs_icon( 'up', 18 ), rs_svg_tags() ); ?></button>
	<div class="wwm-card" id="wwmCard" role="dialog" aria-label="Work with me" hidden>
		<div class="hd"><span>Let's work together</span><button type="button" class="x" id="wwmClose" aria-label="Close">×</button></div>
		<div class="wwm-row">
			<div><small>Email <span class="ok" id="wwmCopied" aria-live="polite">✓ Copied</span></small><b><?php echo esc_html( $rs_md_email ); ?></b></div>
		</div>
		<div class="wwm-row">
			<div><small>WhatsApp</small><b><?php echo esc_html( $rs_md_phone ); ?></b></div>
			<a class="btn" href="<?php echo esc_url( $rs_md_wa ); ?>" target="_blank" rel="noopener noreferrer">Chat ↗</a>
		</div>
	</div>
	<button type="button" class="wwm-btn" id="wwmBtn" aria-expanded="false" aria-controls="wwmCard"><span class="dot" aria-hidden="true"></span>Work with me</button>
</div>

<script>
// Every demo is its own page (lib/player.js, or GSAP for Shifting Shores); this page plays them in one iframe.
// Shared between the pages: the 16:9 / 4:5 frame, the posterize setting and the playhead (kept on a frame switch).
const BASE = <?php echo wp_json_encode( $rs_md_base ); ?>, VER = <?php echo wp_json_encode( RS_VERSION ); ?>;
const DEMOS = <?php
$rs_md_js = array();
foreach ( $rs_md_sections as $rs_md_s ) {
	foreach ( $rs_md_s['items'] as $rs_md_i ) {
		$rs_md_js[] = array( 'id' => $rs_md_i[0], 'src' => $rs_md_i[1], 'src45' => isset( $rs_md_i[6] ) ? $rs_md_i[6] : '', 'tag' => $rs_md_i[2], 'title' => $rs_md_i[3] );
	}
}
echo wp_json_encode( $rs_md_js );
?>;
const $ = id => document.getElementById(id), screenEl = $('screen');

// state: #05 or #05-45 in the address bar; posterize and time come back from the player
const S = { i: 0, vert: false, post: null, t: 0, playing: true };
const store = { get(k) { try { return localStorage.getItem(k); } catch { return null; } }, set(k, v) { try { localStorage.setItem(k, v); } catch {} } };
try { const p = JSON.parse(store.get('mapdemo.posterize') || 'null'); if (p) S.post = p; } catch {}
function readHash() {
  const m = /^#?(\d\d)(-45)?$/.exec(location.hash);
  if (m) { const i = DEMOS.findIndex(d => d.id === m[1]); if (i >= 0) S.i = i; S.vert = !!m[2]; }
  else S.vert = store.get('mapdemo.vert') === '1';
}
const file = d => (S.vert && d.src45) || d.src;   // a demo with its own 4:5 cut plays that file in the 4:5 frame
function url(d, keepTime) {
  const q = ['embed', 'ver=' + VER];
  if (S.vert) q.push('ar=45');
  if (S.post) q.push('post=' + (S.post.on ? S.post.fps : 0));
  if (keepTime && S.t > 0) { q.push('t=' + S.t.toFixed(3)); if (!S.playing) q.push('paused'); }
  return BASE + file(d) + '?' + q.join('&');
}
function load(keepTime) {
  const d = DEMOS[S.i];
  screenEl.src = url(d, keepTime);
  $('openTab').href = BASE + file(d) + '?ver=' + VER + (S.vert ? '&ar=45' : '');
  $('nowTag').textContent = d.tag; $('nowTitle').textContent = d.title;
  $('ar169').classList.toggle('on', !S.vert); $('ar45').classList.toggle('on', S.vert);
  $('ar169').setAttribute('aria-pressed', !S.vert); $('ar45').setAttribute('aria-pressed', S.vert);
  document.querySelectorAll('a.card').forEach(a => a.classList.toggle('on', a.dataset.id === d.id));
  const h = '#' + d.id + (S.vert ? '-45' : ''); if (location.hash !== h) history.replaceState(null, '', h);
  store.set('mapdemo.vert', S.vert ? '1' : '0');
  if (!keepTime) S.t = 0;
  size();
}
const go = i => { S.i = (i + DEMOS.length) % DEMOS.length; S.t = 0; S.playing = true; load(false); };
const setVert = v => { if (v === S.vert) return; S.vert = v; load(true); };

// size: 16:9 fills the width; 4:5 is as tall as the window allows (the player letterboxes inside)
function size() {
  const w = screenEl.clientWidth, bar = 60, room = innerHeight - 150;
  screenEl.style.height = Math.round(S.vert ? Math.max(420, Math.min(room, w * 5 / 4 + bar)) : Math.min(w * 9 / 16 + bar, Math.max(360, room + 80))) + 'px';
}
addEventListener('resize', size);

$('bPrevDemo').onclick = () => go(S.i - 1);
$('bNextDemo').onclick = () => go(S.i + 1);
$('ar169').onclick = () => setVert(false);
$('ar45').onclick = () => setVert(true);
document.addEventListener('click', e => {
  const a = e.target.closest('a.card'); if (!a) return;
  e.preventDefault(); go(DEMOS.findIndex(d => d.id === a.dataset.id));
  $('playerBox').scrollIntoView({ behavior: 'smooth', block: 'start' });
});
screenEl.addEventListener('load', () => { try { screenEl.focus(); } catch {} });
addEventListener('hashchange', () => { readHash(); load(false); });

// the player reports back
addEventListener('message', e => {
  if (e.source !== screenEl.contentWindow) return;
  const d = e.data; if (!d || typeof d !== 'object') return;
  if (d.mapdemo === 'time') { S.t = d.t; S.playing = d.playing; }
  else if (d.mapdemo === 'hubKey') hubKey(d.key);
  else if (d.mapdemo === 'post' || d.mapdemo === 'ready') { S.post = { on: d.mapdemo === 'post' ? d.on : d.post, fps: d.fps }; store.set('mapdemo.posterize', JSON.stringify(S.post)); }
});

function hubKey(k) {
  if (k === '[' || k === 'PageUp') go(S.i - 1);
  else if (k === ']' || k === 'PageDown') go(S.i + 1);
  else if (k === 'v' || k === 'V') setVert(!S.vert);
  else return false;
  return true;
}
// keys pressed on this page (outside the player) go to the player; [ ] switch demos, V switches the frame
const FWD = new Set([' ', 'k', 'K', 'ArrowLeft', 'ArrowRight', ',', '.', '<', '>', 'Home', 'End', 'm', 'M', 'l', 'L', 'p', 'P']);
addEventListener('keydown', e => {
  if (e.ctrlKey || e.metaKey || e.altKey || /INPUT|SELECT|TEXTAREA/.test(e.target.tagName)) return;
  const k = e.key;
  if (k === 'Escape') { wwm(false); return; }
  if (hubKey(k)) e.preventDefault();
  else if (k === 'f' || k === 'F') { e.preventDefault(); screenEl.requestFullscreen?.(); }
  else if (FWD.has(k)) { if (k === ' ' && e.target.closest('a, button')) return; e.preventDefault(); screenEl.contentWindow.postMessage({ mapdemo: 'key', key: k, shiftKey: e.shiftKey }, '*'); }
});

// Work with me: a click copies the email address and shows it with the WhatsApp number
const EMAIL = <?php echo wp_json_encode( $rs_md_email ); ?>;
function copyText(text) {
  if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text);
  return new Promise((ok, no) => {
    const ta = document.createElement('textarea');
    ta.value = text; ta.setAttribute('readonly', ''); ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy') ? ok() : no(); } catch (err) { no(err); } finally { ta.remove(); }
  });
}
function wwm(open) {
  $('wwmCard').hidden = !open;
  $('wwmBtn').setAttribute('aria-expanded', open);
}
$('wwmBtn').onclick = () => {
  const opening = $('wwmCard').hidden;
  wwm(opening);
  if (!opening) return;
  const note = $('wwmCopied');
  copyText(EMAIL).then(() => { note.textContent = '✓ Copied'; }, () => { note.textContent = 'Copy it above'; });
};
$('wwmClose').onclick = () => { wwm(false); $('wwmBtn').focus(); };
document.addEventListener('pointerdown', e => { if (!$('wwmCard').hidden && !e.target.closest('#wwm')) wwm(false); });

// Back to top: shows after 300px, like the button on the rest of the site
const toTop = $('toTop');
const topBtn = () => toTop.classList.toggle('is-visible', scrollY > 300);
addEventListener('scroll', topBtn, { passive: true });
toTop.onclick = () => {
  scrollTo({ top: 0, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  document.querySelector('.top a').focus({ preventScroll: true });   // the button hides at the top, so keyboard focus moves to the first link
};
topBtn();

readHash(); load(false);
</script>
</body>
</html>
