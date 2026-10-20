<?php
/**
 * The map animation demos page, served by rs_map_demo_render().
 *
 * English on both editions. Laid out for a client in the showreel's look
 * (since 9.2.0): a short pitch, two featured pieces (the 3D satellite news
 * map and the Bahrain client documentary) in one large player, every style
 * as a card that plays in a pop-up player, and a way to get in touch.
 *
 * Each demo is its own page in assets/demo/map-animation/ (copied there by
 * scripts/map-demo-sync.js). A player loads only when asked: the featured
 * player shows a poster until it is clicked. The players talk back over
 * postMessage (time, posterize, the demo and frame keys). Static output, so
 * the full page cache can keep it as it is.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rs_md_base  = RS_URI . '/assets/demo/map-animation/';
$rs_md_email = rs_option( 'rs_email' ) ? rs_option( 'rs_email' ) : 'lettertosohan@gmail.com';
$rs_md_wa    = 'https://wa.me/8801775860544';

/* The English edition is the canonical one; the Bengali edition shows the same page. */
$rs_md_pair      = rs_edition_pair();
$rs_md_canonical = ( $rs_md_pair && ! rs_is_en() ) ? rs_map_demo_url( $rs_md_pair['other'] ) : rs_map_demo_url();

$rs_md_title = 'Map Animation Demos';
$rs_md_desc  = 'Map animation with voice-over by Raisul Sohan: documentary, explainer, true-crime and city maps, a 3D satellite news map and a client documentary, each in 16:9 and 4:5.';

/* The kinds of map, for the filters. */
$rs_md_kinds = array(
	'doc'       => 'Documentary & news',
	'explainer' => 'Explainer',
	'crime'     => 'True crime',
	'city'      => 'City reveal',
);

/*
 * Every demo, in the order the styles grid shows them. Keys: file, its own
 * 4:5 file if it has one (otherwise the 4:5 frame is file?ar=45), the kind,
 * the tag, the title, one line for the card, the alt text of the thumbnail,
 * and a note (data credits, what is made up) shown with the player and in
 * the footer.
 */
$rs_md_demos = array(
	'13' => array( 'hormuz/hormuz-v2.html', 'hormuz/hormuz-v2-4x5.html', 'doc', '3D satellite news map', 'Strait of Hormuz', 'Breaking news in 3D over satellite relief: a destroyer on its route, missiles off the rail, a running clock.', 'Red-tinted 3D satellite map of the Strait of Hormuz with three missiles launching from the coast near Bandar Abbas', 'Imagery: Esri World Imagery. Terrain: AWS Terrain Tiles. Coastline © OpenStreetMap contributors. Borders: geoBoundaries.' ),
	'14' => array( 'bahrain/highlights.html', '', 'doc', 'Client documentary', 'Bahrain', 'Highlights of the map scenes made for a YouTube documentary on a desert island with no rivers.', 'Satellite map of Bahrain, the island filled red, with a counter reading 1,500,000 people', 'Made for a client’s YouTube documentary. Satellite imagery: NASA Blue Marble; contains modified Copernicus Sentinel data (2025); Landsat courtesy of the U.S. Geological Survey. Map data: Natural Earth. Trade routes and strike sites are illustrative.' ),
	'12' => array( '12-korean-war.html', '', 'doc', 'Animated war map', 'The Korean War', 'A front of waving flags moves with the date across shaded relief, with a live territory count.', 'Relief map of Korea, the north red and the south blue, with flags along the front and an Inchon Landing card', 'Front lines simplified from campaign maps. Terrain: AWS Terrain Tiles. Borders: Natural Earth.' ),
	'02' => array( '02-globe-dive.html', '', 'doc', 'Documentary globe dive', 'From Orbit to Dhaka', 'A dive from space into a paper map, with a red marker and a typewriter note.', 'Paper map with red marker circle around Dhaka and a typewriter note', '' ),
	'01' => array( '01-vox-delta.html', '', 'explainer', 'Vox-style explainer', 'The World’s Largest Delta', 'Cut paper, a country that lifts off the page and rivers that draw themselves.', 'Paper map of Bangladesh with rivers and halftone delta', '' ),
	'03' => array( '03-neon-routes.html', '', 'explainer', 'Dark data map', 'Remittance Routes', 'Glowing routes across a dot-matrix world, all landing on one hub.', 'Dark dot-matrix map with glowing routes into Dhaka', '' ),
	'04' => array( '04-3d-density.html', '', 'explainer', '3D data map', 'The Most Crowded Big Country', 'Countries rise by population density, lit and shadowed like a model.', '3D extruded map of South Asia with Bangladesh as the tallest tower', '' ),
	'11' => array( 'shifting-shores.html', '', 'explainer', 'Desk & TV explainer', 'Proof on Paper', 'Survey sheets on a desk: a coastline that moves and an island that keeps growing.', 'Two survey maps on a wooden desk, 1973 in blue and 2023 in yellow, with a NEW LAND stamp', 'The coast and islands are illustrative, not to scale.' ),
	'05' => array( '05-crime-evidence-board.html', '', 'crime', 'Evidence board', 'Case #0417', 'A city map on cork: pins, polaroids, red string and an UNSOLVED stamp.', 'City map pinned on a cork board with red string, photos and an UNSOLVED stamp', 'The city, case and people are fictional.' ),
	'06' => array( '06-crime-surveillance.html', '', 'crime', 'Surveillance', 'Signal Lost', 'A night map, a timed route, CCTV stills and cell-tower triangulation.', 'Dark night map with a red tracked route, tower rings and a last known area', 'The city, case and people are fictional.' ),
	'07' => array( '07-crime-dossier-route.html', '', 'crime', 'Case dossier', 'The Escape Route', 'A printed map of Europe, dated stops, a mugshot card and redaction bars.', 'Grey hatched map of Europe with a red dashed route and date stamps', 'The case and people are fictional.' ),
	'08' => array( '08-crime-search-grid.html', '', 'crime', 'Search operation', 'Outside the Search Zone', 'Topographic terrain, probability rings and a find outside the zone.', 'Green topographic map with a search grid and a red find marker', 'The case and people are fictional.' ),
	'09' => array( '09-manhattan-vox.html', '', 'city', 'Vox-style city reveal', 'Manhattan, New York', 'Rebuilt from a reference clip: Manhattan fills in block by block, north to south.', 'Paper map of New York with Manhattan filled in green and the title Manhattan, New York', 'Rebuilt from a reference clip (the Vox-style Manhattan reveal), matched by measuring its frames. Map data © OpenStreetMap contributors, tiles by OpenFreeMap.' ),
	'10' => array( '10-dhaka-night.html', '', 'city', 'City reveal at night', 'Dhaka, Bangladesh', 'Streets light up from the centre and neighbourhoods switch on in gold.', 'Night map of Dhaka with neighbourhoods lit in gold and the title Dhaka, Bangladesh', 'Map data © OpenStreetMap contributors, tiles by OpenFreeMap.' ),
);

/* The featured pieces, in the order of their tabs. */
$rs_md_featured = array(
	'13' => array(
		'tab'    => 'Strait of Hormuz',
		'kicker' => '3D satellite news map',
		'title'  => 'Strait of Hormuz',
		'text'   => 'A breaking-news piece in 3D over real satellite relief: tinted countries, type laid on the ground, a running clock, a destroyer on its route and missiles off the rail. With voice-over, in 16:9 and its own 4:5 cut.',
	),
	'14' => array(
		'tab'    => 'Bahrain',
		'kicker' => 'Client work · YouTube documentary',
		'title'  => 'Bahrain: a desert island with no rivers',
		'text'   => 'The best moments of the map scenes made for a documentary on how 1.5 million people live without rivers: a satellite lock-on, a coastline that grows, trade routes and strikes across the Gulf, cut to music in 23 seconds. In 16:9 and 4:5.',
	),
);
$rs_md_first = array_key_first( $rs_md_featured );

$rs_md_icon_play = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.6-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5Z"/></svg>';
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
<meta property="og:image" content="<?php echo esc_url( $rs_md_base . 'thumbs/13-poster.jpg' ); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#000000">
<?php wp_site_icon(); ?>
<?php rs_inline_fonts(); ?>
<style>
	/* The showreel's look: black, neutral tiles, one orange, bold Inter. */
	:root {
		--bg: #000; --tile: #161617; --tile2: #1d1d1f; --line: #2c2c2e; --fg: #f5f5f7; --fg2: #d2d2d7; --mut: #a1a1a6; --dim: #6e6e73;
		--acc: #ff4a1c; --acc2: #ff6238; --ink: #0b0a0d; --ok: #3ddc97;
		--sans: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
		--out: cubic-bezier(.22, 1, .36, 1);
		--gut: clamp(16px, 4vw, 32px);
		color-scheme: dark;
	}
	* { box-sizing: border-box; }
	html { scroll-behavior: smooth; scroll-padding-top: 72px; }
	body {
		margin: 0; color: var(--fg); background: var(--bg); font: 17px/1.47 var(--sans); letter-spacing: -.016em;
		-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; overflow-x: clip;
	}
	body.locked { overflow: hidden; }
	a { color: inherit; }
	img { max-width: 100%; }
	.wrap { max-width: 1180px; margin-inline: auto; padding-inline: var(--gut); }
	.dot { display: inline-block; width: .17em; height: .17em; margin-left: .08em; border-radius: 50%; background: var(--acc); }
	:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; }

	/* pills: the showreel's buttons */
	.pill {
		all: unset; box-sizing: border-box; display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
		min-height: 44px; padding: 0 1.3rem; border-radius: 980px; cursor: pointer; text-decoration: none;
		font: 600 16px/1.2 var(--sans); letter-spacing: -.01em; color: var(--fg); background: rgba(255, 255, 255, .09);
		transition: background-color .2s, transform .12s;
	}
	.pill:hover { background: rgba(255, 255, 255, .15); }
	.pill:active { transform: scale(.97); }
	.pill:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; }
	.pill--acc { color: var(--ink); background: var(--acc); }
	.pill--acc:hover { background: var(--acc2); }
	.pill--acc:focus-visible { outline-color: #fff; }
	.pill svg { width: 15px; height: 15px; fill: currentColor; }
	.pill--sm { min-height: 34px; padding: 0 .95rem; font-size: 13px; }

	/* segmented control */
	.seg { display: inline-flex; padding: 3px; border-radius: 980px; background: rgba(118, 118, 128, .24); }
	.seg button {
		all: unset; box-sizing: border-box; display: grid; place-items: center; min-height: 32px; padding: 0 1rem; border-radius: 980px;
		cursor: pointer; font: 600 14px/1 var(--sans); color: var(--mut); white-space: nowrap; transition: color .2s, background-color .2s;
	}
	.seg button:hover { color: var(--fg); }
	.seg button[aria-pressed="true"], .seg button[aria-selected="true"] { color: var(--ink); background: var(--fg); }
	.seg button:focus-visible { outline: 2px solid var(--acc); outline-offset: 1px; }
	.seg--acc button[aria-selected="true"] { background: var(--acc); }

	/* the bar: the showreel's glass */
	.nav { position: sticky; top: 0; z-index: 40; background: rgba(22, 22, 23, .72); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); border-bottom: 1px solid rgba(255, 255, 255, .08); }
	.nav-in { display: flex; align-items: center; gap: 1.5rem; height: 56px; }
	.brand { font-weight: 700; font-size: 17px; letter-spacing: -.02em; text-decoration: none; }
	.links { display: flex; gap: 1.5rem; margin-right: auto; font-size: 14px; }
	.links a { color: var(--mut); text-decoration: none; transition: color .2s; }
	.links a:hover { color: var(--fg); }
	.nav .pill { min-height: 32px; padding: 0 1rem; font-size: 14px; }
	@media (max-width: 640px) { .links { display: none; } .brand { margin-right: auto; } }

	/* hero */
	.hero { padding-block: clamp(3.5rem, 9vw, 7rem) clamp(2.5rem, 6vw, 4.5rem); }
	.eyebrow { margin: 0 0 .9rem; color: var(--acc); font-size: clamp(17px, 1.6vw, 20px); font-weight: 600; letter-spacing: -.01em; }
	h1 { margin: 0; font-size: clamp(44px, 8.2vw, 96px); line-height: 1.02; font-weight: 700; letter-spacing: -.045em; text-wrap: balance; }
	.lead { max-width: 40rem; margin: 1.5rem 0 0; color: var(--mut); font-size: clamp(19px, 2vw, 23px); line-height: 1.42; font-weight: 500; letter-spacing: -.02em; text-wrap: pretty; }
	.lead b { color: var(--fg); font-weight: 600; }
	.cta { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
	.facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin: clamp(3rem, 7vw, 4.5rem) 0 0; padding: 0; list-style: none; }
	.facts li { padding: 1.4rem 1.5rem 1.5rem; border-radius: 22px; background: var(--tile); }
	.facts svg { display: block; width: 28px; height: 28px; margin-bottom: 1rem; fill: none; stroke: var(--acc); stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
	.facts b { display: block; font-size: 19px; letter-spacing: -.02em; }
	.facts span { display: block; margin-top: .3rem; color: var(--mut); font-size: 15px; line-height: 1.45; }
	@media (max-width: 760px) {
		.facts { grid-template-columns: 1fr; gap: .75rem; }
		.facts li { display: grid; grid-template-columns: 28px minmax(0, 1fr); column-gap: 1rem; padding: 1.1rem 1.2rem; }
		.facts svg { grid-row: span 2; margin: 2px 0 0; }
	}

	/* sections */
	.sec { padding-block: clamp(3.5rem, 8vw, 6rem) 0; }
	.sec-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1rem 2rem; margin-bottom: clamp(1.25rem, 2.5vw, 1.75rem); }
	.h2 { margin: 0; font-size: clamp(34px, 5vw, 56px); line-height: 1.06; font-weight: 700; letter-spacing: -.04em; }
	.sub { max-width: 34rem; margin: .7rem 0 0; color: var(--mut); font-size: 17px; line-height: 1.5; }

	/* the featured player: a poster until it is clicked, then the demo itself */
	.stage { position: relative; height: 600px; border-radius: 28px; overflow: hidden; background: #050506; box-shadow: 0 0 0 1px rgba(255, 255, 255, .07), 0 40px 90px -30px rgba(0, 0, 0, .9); }
	.stage iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: #050506; }
	/* loaded in the background, paused under its poster */
	.stage iframe.pre { z-index: 0; }
	.stage .poster { z-index: 1; }
	.poster { all: unset; position: absolute; inset: 0; cursor: pointer; display: block; }
	.poster[hidden] { display: none; }
	.poster img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .8s var(--out), filter .4s; }
	.poster::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0, 0, 0, 0) 45%, rgba(0, 0, 0, .55)); }
	.poster:hover img { transform: scale(1.02); filter: brightness(1.06); }
	.play { position: absolute; left: 50%; top: 50%; z-index: 1; display: grid; place-items: center; width: 92px; height: 92px; margin: -46px 0 0 -46px; border-radius: 50%; background: rgba(245, 245, 247, .92); box-shadow: 0 12px 40px rgba(0, 0, 0, .45); transition: transform .3s var(--out); }
	.play svg { width: 38px; height: 38px; margin-left: 5px; fill: #000; }
	.poster:hover .play { transform: scale(1.06); }
	.poster:focus-visible .play { outline: 3px solid var(--acc); outline-offset: 4px; }
	.ptag { position: absolute; left: 24px; bottom: 22px; z-index: 1; font-size: 15px; font-weight: 600; color: #fff; text-shadow: 0 1px 10px rgba(0, 0, 0, .6); }
	.fcap { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 1rem 2.5rem; align-items: start; margin-top: 1.5rem; }
	.kicker { margin: 0; color: var(--acc); font-size: 15px; font-weight: 600; }
	.fcap h3 { margin: .2rem 0 0; font-size: clamp(24px, 3vw, 32px); line-height: 1.15; letter-spacing: -.03em; }
	.fcap p.text { max-width: 44rem; margin: .6rem 0 0; color: var(--mut); font-size: 17px; line-height: 1.5; }
	.credit { margin: .7rem 0 0; color: var(--dim); font-size: 13px; line-height: 1.5; max-width: 44rem; }
	.fctl { display: flex; flex-wrap: wrap; justify-content: end; gap: .6rem; }
	@media (max-width: 760px) { .fcap { grid-template-columns: 1fr; } .fctl { justify-content: start; } }

	/* the styles: filters and cards */
	.chips { display: flex; flex-wrap: wrap; gap: .5rem; }
	.chip { all: unset; box-sizing: border-box; display: inline-flex; align-items: center; min-height: 36px; padding: 0 1rem; border-radius: 980px; cursor: pointer; font: 600 14px/1 var(--sans); color: var(--fg2); background: rgba(255, 255, 255, .07); transition: background-color .2s, color .2s; }
	.chip:hover { background: rgba(255, 255, 255, .13); }
	.chip[aria-pressed="true"] { color: var(--ink); background: var(--fg); }
	.chip:focus-visible { outline: 2px solid var(--acc); outline-offset: 2px; }
	.grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
	.card { display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; background: var(--tile); text-decoration: none; transition: transform .45s var(--out), background-color .2s; }
	.card:hover { transform: translateY(-4px); background: var(--tile2); }
	.card:active { transform: scale(.985); }
	.card[hidden] { display: none; }
	.thumb { position: relative; display: block; aspect-ratio: 16 / 9; background: #000; overflow: hidden; }
	.thumb img { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .8s var(--out); }
	.card:hover .thumb img { transform: scale(1.04); }
	.thumb .play-s { position: absolute; right: 14px; bottom: 14px; display: grid; place-items: center; width: 40px; height: 40px; border-radius: 50%; background: rgba(245, 245, 247, .9); opacity: 0; transform: scale(.85); transition: opacity .25s, transform .35s var(--out); }
	.thumb .play-s svg { width: 17px; height: 17px; margin-left: 2px; fill: #000; }
	.card:hover .play-s, .card:focus-visible .play-s { opacity: 1; transform: none; }
	.cbody { padding: 1.1rem 1.25rem 1.3rem; }
	.ctag { display: block; color: var(--mut); font-size: 13px; font-weight: 600; }
	.ctitle { display: block; margin-top: .25rem; font-size: 21px; font-weight: 700; line-height: 1.2; letter-spacing: -.025em; }
	.cdesc { display: block; margin-top: .4rem; color: var(--mut); font-size: 15px; line-height: 1.45; }
	.badge { display: inline-block; margin-left: .4rem; padding: 2px 8px; border-radius: 980px; font-size: 11px; font-weight: 700; color: var(--ink); background: var(--acc); vertical-align: 2px; letter-spacing: 0; }
	@media (max-width: 960px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
	@media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }

	/* the contact band */
	.band { padding: clamp(2rem, 5vw, 3.5rem); border-radius: 28px; background: var(--tile); text-align: center; }
	.band .h2 { font-size: clamp(32px, 4.6vw, 52px); text-wrap: balance; }
	.band p { max-width: 36rem; margin: 1rem auto 0; color: var(--mut); font-size: 18px; line-height: 1.5; }
	.band .cta { justify-content: center; }
	.copied { color: var(--ok); }

	/* footer */
	/* the hairline spans the content, not the padding */
	footer.foot { margin-top: clamp(3.5rem, 8vw, 5rem); padding-block: 1.5rem 7rem; background: linear-gradient(var(--line), var(--line)) no-repeat top center / calc(100% - 2 * var(--gut)) 1px; color: var(--dim); font-size: 13px; line-height: 1.55; }
	footer.foot a { color: var(--mut); }
	footer.foot details { margin-top: .9rem; }
	footer.foot summary { cursor: pointer; color: var(--mut); font-weight: 600; }
	footer.foot ul { margin: .7rem 0 0; padding-left: 1.1rem; }
	footer.foot li + li { margin-top: .35rem; }
	kbd { font: 600 11px/1 ui-monospace, Consolas, monospace; padding: 2px 5px; border: 1px solid var(--line); border-bottom-width: 2px; border-radius: 5px; color: var(--fg2); background: var(--tile); }

	/* the pop-up player */
	.modal { position: fixed; inset: 0; z-index: 60; display: grid; place-items: center; padding: 16px; background: rgba(0, 0, 0, .82); -webkit-backdrop-filter: blur(14px) saturate(140%); backdrop-filter: blur(14px) saturate(140%); animation: m-in .25s var(--out); }
	.modal[hidden] { display: none; }
	@keyframes m-in { from { opacity: 0; } }
	.m-box { width: 100%; max-width: 1240px; animation: m-up .4s var(--out); }
	@keyframes m-up { from { transform: translateY(14px) scale(.985); } }
	.m-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .6rem 1rem; margin-bottom: .75rem; }
	.m-top .kicker { font-size: 13px; }
	.m-top b { display: block; font-size: 20px; line-height: 1.2; letter-spacing: -.02em; }
	.m-ctl { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
	.icon-btn { all: unset; box-sizing: border-box; display: grid; place-items: center; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; color: var(--fg); background: rgba(255, 255, 255, .1); font: 600 15px/1 var(--sans); transition: background-color .2s; }
	.icon-btn:hover { background: rgba(255, 255, 255, .18); }
	.icon-btn:focus-visible { outline: 2px solid var(--acc); outline-offset: 2px; }
	.m-frame { display: block; width: 100%; border: 0; border-radius: 20px; background: #050506; box-shadow: 0 0 0 1px rgba(255, 255, 255, .08); }
	.m-note { margin: .7rem 0 0; color: var(--dim); font-size: 13px; line-height: 1.5; min-height: 1.5em; }

	/* Back to top floats in the corner, as on the rest of the site. */
	.corner { position: fixed; right: 22px; bottom: 22px; z-index: 50; }
	body.locked .corner { display: none; }
	@media (max-width: 600px) { .corner { right: 16px; bottom: 16px; } }
	/* Back to top: the site's button in the showreel's dark glass, above Work with me. */
	.to-top {
		all: unset; box-sizing: border-box; display: grid; place-items: center; width: 44px; height: 44px; cursor: pointer;
		border-radius: 50%; color: #fff; background: rgba(44, 44, 46, .72);
		-webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .14), 0 8px 24px rgba(0, 0, 0, .35);
		opacity: 0; visibility: hidden; transform: translateY(8px); transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
	}
	.to-top.is-visible { opacity: 1; visibility: visible; transform: none; }
	.to-top:hover { color: #000; background: #f5f5f7; }
	.to-top:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; }
	@media (prefers-reduced-motion: reduce) {
		html { scroll-behavior: auto; }
		.modal, .m-box { animation: none; }
		.card, .thumb img, .poster img, .to-top { transition: none; }
	}
</style>
</head>
<body>
<header class="nav">
	<div class="wrap nav-in">
		<a class="brand" href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>"><?php echo esc_html( 'Raisul Sohan' ); ?><span class="dot" aria-hidden="true"></span></a>
		<nav class="links" aria-label="On this page">
			<a href="#featured">Featured</a>
			<a href="#styles">Styles</a>
			<a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">Portfolio</a>
		</nav>
		<a class="pill pill--acc" href="#contact">Work with me</a>
	</div>
</header>

<main id="top">
	<section class="hero wrap">
		<p class="eyebrow">Map animation</p>
		<h1>Every map, a story in motion<span class="dot" aria-hidden="true"></span></h1>
		<p class="lead">Documentary and explainer maps for your video: <b>timed to your voice-over</b>, built on <b>real map data</b> and delivered for <b>YouTube and Reels</b>.</p>
		<div class="cta">
			<a class="pill pill--acc" href="#featured" id="watch"><?php echo $rs_md_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>Watch the work</a>
			<a class="pill" href="#styles">See every style</a>
		</div>
		<ul class="facts">
			<li>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12h2M7 8v8M11 5v14M15 9v6M19 11v2"/></svg>
				<b>Timed to your voice</b><span>Labels, lines and camera moves land on the words of your narration.</span>
			</li>
			<li>
				<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="6" width="12" height="9" rx="1.5"/><rect x="16" y="4" width="5.5" height="16" rx="1.5"/></svg>
				<b>16:9 and 4:5</b><span>One map, cut for YouTube and for Reels, Shorts and feeds.</span>
			</li>
			<li>
				<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.8 2.6 15.2 0 18M12 3c-2.6 2.8-2.6 15.2 0 18"/></svg>
				<b>Real map data</b><span>Satellite imagery, terrain, borders and streets from open sources.</span>
			</li>
		</ul>
	</section>

	<section class="wrap sec" id="featured" aria-labelledby="f-head">
		<div class="sec-head">
			<h2 class="h2" id="f-head">Featured<span class="dot" aria-hidden="true"></span></h2>
			<div class="seg seg--acc" role="tablist" aria-label="Featured work">
				<?php foreach ( $rs_md_featured as $rs_md_id => $rs_md_f ) : ?>
					<button type="button" role="tab" id="ftab-<?php echo esc_attr( $rs_md_id ); ?>" aria-controls="fStage" aria-selected="<?php echo $rs_md_id === $rs_md_first ? 'true' : 'false'; ?>" data-f="<?php echo esc_attr( $rs_md_id ); ?>"><?php echo esc_html( $rs_md_f['tab'] ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="stage" id="fStage" role="tabpanel" aria-labelledby="ftab-<?php echo esc_attr( $rs_md_first ); ?>">
			<button type="button" class="poster" id="fPoster" aria-label="<?php echo esc_attr( 'Play ' . $rs_md_featured[ $rs_md_first ]['title'] ); ?>">
				<img id="fImg" src="<?php echo esc_url( $rs_md_base . 'thumbs/' . $rs_md_first . '-poster.jpg?ver=' . RS_VERSION ); ?>" alt="<?php echo esc_attr( $rs_md_demos[ $rs_md_first ][6] ); ?>" width="1600" height="900" fetchpriority="high">
				<span class="play"><?php echo $rs_md_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
				<span class="ptag" id="fPtag"><?php echo esc_html( $rs_md_featured[ $rs_md_first ]['kicker'] ); ?></span>
			</button>
			<iframe id="fFrame" title="Featured map animation" allow="autoplay; fullscreen" allowfullscreen hidden></iframe>
		</div>
		<div class="fcap">
			<div>
				<p class="kicker" id="fKicker"><?php echo esc_html( $rs_md_featured[ $rs_md_first ]['kicker'] ); ?></p>
				<h3 id="fTitle"><?php echo esc_html( $rs_md_featured[ $rs_md_first ]['title'] ); ?></h3>
				<p class="text" id="fText"><?php echo esc_html( $rs_md_featured[ $rs_md_first ]['text'] ); ?></p>
				<p class="credit" id="fCredit"><?php echo esc_html( $rs_md_demos[ $rs_md_first ][7] ); ?></p>
			</div>
			<div class="fctl">
				<div class="seg" role="group" aria-label="Frame">
					<button type="button" data-ar="169" aria-pressed="true" title="16:9 · 1920×1080 (V)">16:9</button><button type="button" data-ar="45" aria-pressed="false" title="4:5 · 1080×1350 (V)">4:5</button>
				</div>
				<a class="pill pill--sm" id="fOpen" target="_blank" rel="noopener" href="<?php echo esc_url( $rs_md_base . $rs_md_demos[ $rs_md_first ][0] . '?ver=' . RS_VERSION ); ?>">Open ↗</a>
			</div>
		</div>
	</section>

	<section class="wrap sec" id="styles" aria-labelledby="s-head">
		<div class="sec-head">
			<div>
				<h2 class="h2" id="s-head">Pick a style<span class="dot" aria-hidden="true"></span></h2>
				<p class="sub">Click any map to play it with its voice-over. Each one is a starting point: your story, your data, your voice.</p>
			</div>
			<div class="chips" role="toolbar" aria-label="Filter the styles">
				<button type="button" class="chip" data-k="all" aria-pressed="true">All</button>
				<?php foreach ( $rs_md_kinds as $rs_md_k => $rs_md_label ) : ?>
					<button type="button" class="chip" data-k="<?php echo esc_attr( $rs_md_k ); ?>" aria-pressed="false"><?php echo esc_html( $rs_md_label ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="grid" id="grid">
			<?php foreach ( $rs_md_demos as $rs_md_id => $rs_md_d ) : ?>
				<a class="card" href="#<?php echo esc_attr( $rs_md_id ); ?>" data-id="<?php echo esc_attr( $rs_md_id ); ?>" data-k="<?php echo esc_attr( $rs_md_d[2] ); ?>">
					<span class="thumb">
						<img src="<?php echo esc_url( $rs_md_base . 'thumbs/' . $rs_md_id . '.jpg?ver=' . RS_VERSION ); ?>" alt="<?php echo esc_attr( $rs_md_d[6] ); ?>" width="640" height="360" loading="lazy" decoding="async">
						<span class="play-s" aria-hidden="true"><?php echo $rs_md_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
					</span>
					<span class="cbody">
						<span class="ctag"><?php echo esc_html( $rs_md_d[3] ); ?><?php if ( isset( $rs_md_featured[ $rs_md_id ] ) ) : ?><span class="badge">Featured</span><?php endif; ?></span>
						<span class="ctitle"><?php echo esc_html( $rs_md_d[4] ); ?></span>
						<span class="cdesc"><?php echo esc_html( $rs_md_d[5] ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="wrap sec" id="contact" aria-labelledby="c-head">
		<div class="band">
			<h2 class="h2" id="c-head">Need a map for your video<span class="dot" aria-hidden="true"></span></h2>
			<p>Send your script or voice-over. You get a map animation in the look you pick, timed to your narration and cut for 16:9 and 4:5.</p>
			<div class="cta">
				<button type="button" class="pill pill--acc" id="copyMail" data-email="<?php echo esc_attr( $rs_md_email ); ?>"><span id="copyMailText"><?php echo esc_html( 'Copy email · ' . $rs_md_email ); ?></span></button>
				<a class="pill" href="<?php echo esc_url( $rs_md_wa ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>
				<a class="pill" href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">See the portfolio</a>
			</div>
		</div>
	</section>
</main>

<footer class="wrap foot">
	<p>Made by <a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">Raisul Sohan</a>. Every frame is drawn in code in the browser; nothing here is a video file.</p>
	<details>
		<summary>Map data, credits and keys</summary>
		<ul>
			<?php foreach ( $rs_md_demos as $rs_md_id => $rs_md_d ) : ?>
				<?php if ( $rs_md_d[7] ) : ?><li><b><?php echo esc_html( $rs_md_d[4] ); ?>:</b> <?php echo esc_html( $rs_md_d[7] ); ?></li><?php endif; ?>
			<?php endforeach; ?>
			<li>In a player: <kbd>Space</kbd> play or pause, <kbd>←</kbd> <kbd>→</kbd> one frame, <kbd>Shift</kbd>+<kbd>←</kbd> <kbd>→</kbd> one second, <kbd>M</kbd> mute, <kbd>L</kbd> loop, <kbd>F</kbd> full screen, <kbd>P</kbd> posterize (a stepped look at 12, 8 or 6 fps), <kbd>V</kbd> 16:9 or 4:5, <kbd>[</kbd> <kbd>]</kbd> previous or next map, <kbd>Esc</kbd> close.</li>
		</ul>
	</details>
</footer>

<div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="mTitle" hidden>
	<div class="m-box" id="mBox">
		<div class="m-top">
			<div><p class="kicker" id="mTag"></p><b id="mTitle"></b></div>
			<div class="m-ctl">
				<button type="button" class="icon-btn" id="mPrev" aria-label="Previous map" title="Previous map ([)">◀</button>
				<div class="seg" role="group" aria-label="Frame">
					<button type="button" data-ar="169" aria-pressed="true" title="16:9 · 1920×1080 (V)">16:9</button><button type="button" data-ar="45" aria-pressed="false" title="4:5 · 1080×1350 (V)">4:5</button>
				</div>
				<a class="pill pill--sm" id="mOpen" target="_blank" rel="noopener">Open ↗</a>
				<button type="button" class="icon-btn" id="mNext" aria-label="Next map" title="Next map (])">▶</button>
				<button type="button" class="icon-btn" id="mClose" aria-label="Close" title="Close (Esc)">✕</button>
			</div>
		</div>
		<iframe class="m-frame" id="mFrame" title="Map animation player" allow="autoplay; fullscreen" allowfullscreen></iframe>
		<p class="m-note" id="mNote"></p>
	</div>
</div>

<div class="corner">
	<button type="button" class="to-top" id="toTop" aria-label="Back to top" title="Back to top"><?php echo wp_kses( rs_icon( 'up', 18 ), rs_svg_tags() ); ?></button>
</div>

<script>
// Every demo is its own page (a map player, or GSAP for Proof on Paper); this page plays them in an iframe: the
// featured player (a poster until clicked) or the pop-up player. Shared between them: the 16:9 / 4:5 frame, the
// posterize setting and, on a frame switch, the playhead.
const BASE = <?php echo wp_json_encode( $rs_md_base ); ?>, VER = <?php echo wp_json_encode( RS_VERSION ); ?>;
const DEMOS = <?php
$rs_md_js = array();
foreach ( $rs_md_demos as $rs_md_id => $rs_md_d ) {
	$rs_md_js[] = array( 'id' => (string) $rs_md_id, 'src' => $rs_md_d[0], 'src45' => $rs_md_d[1], 'tag' => $rs_md_d[3], 'title' => $rs_md_d[4], 'alt' => $rs_md_d[6], 'note' => $rs_md_d[7] );
}
echo wp_json_encode( $rs_md_js );
?>;
const FEATURED = <?php
$rs_md_fjs = array();
foreach ( $rs_md_featured as $rs_md_id => $rs_md_f ) {
	$rs_md_fjs[ (string) $rs_md_id ] = $rs_md_f;
}
echo wp_json_encode( $rs_md_fjs );
?>;
const $ = id => document.getElementById(id), byId = id => DEMOS.find(d => d.id === id);
const store = { get(k) { try { return localStorage.getItem(k); } catch { return null; } }, set(k, v) { try { localStorage.setItem(k, v); } catch {} } };

// state: the frame, posterize (from the players), the playhead of the playing demo
const S = { vert: store.get('mapdemo.vert') === '1', post: null, t: 0, playing: true, f: <?php echo wp_json_encode( (string) $rs_md_first ); ?>, fOn: false, m: null };
try { const p = JSON.parse(store.get('mapdemo.posterize') || 'null'); if (p) S.post = p; } catch {}
const file = d => (S.vert && d.src45) || d.src;   // a demo with its own 4:5 cut plays that file in the 4:5 frame
function url(d, keepTime) {
  const q = ['embed', 'ver=' + VER];
  if (S.vert) q.push('ar=45');
  if (S.post) q.push('post=' + (S.post.on ? S.post.fps : 0));
  if (keepTime && S.t > 0) { q.push('t=' + S.t.toFixed(3)); if (!S.playing) q.push('paused'); }
  return BASE + file(d) + '?' + q.join('&');
}
const openUrl = d => BASE + file(d) + '?ver=' + VER + (S.vert ? '&ar=45' : '');
const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

// ── the featured player
const fStage = $('fStage'), fFrame = $('fFrame'), fPoster = $('fPoster');
function fitFeatured() {
  const w = fStage.clientWidth, room = innerHeight - 140;
  fStage.style.height = Math.round(S.vert ? Math.max(420, Math.min(room, w * 5 / 4 + 60)) : Math.min(w * 9 / 16 + 60, Math.max(360, room + 80))) + 'px';
}
function showFeatured(id) {
  const d = byId(id), F = FEATURED[id];
  S.f = id;
  document.querySelectorAll('[data-f]').forEach(b => b.setAttribute('aria-selected', b.dataset.f === id ? 'true' : 'false'));
  fStage.setAttribute('aria-labelledby', 'ftab-' + id);
  $('fImg').src = BASE + 'thumbs/' + id + '-poster.jpg?ver=' + VER; $('fImg').alt = d.alt;
  fPoster.setAttribute('aria-label', 'Play ' + F.title);
  $('fPtag').textContent = F.kicker; $('fKicker').textContent = F.kicker; $('fTitle').textContent = F.title; $('fText').textContent = F.text; $('fCredit').textContent = d.note;
  $('fOpen').href = openUrl(d);
}
// The featured demo loads in the background once the page is idle, paused under its poster, so a click starts it
// at once (as the first demo of the old page did). Not with Data Saver on or on a 2G or 3G connection.
const conn = navigator.connection;
const lightNet = () => !!(conn && (conn.saveData || /^(slow-)?2g$|^3g$/.test(conn.effectiveType || '')));
const preKey = () => S.f + (S.vert ? '-45' : '');
const idle = fn => ('requestIdleCallback' in window ? requestIdleCallback(fn, { timeout: 2500 }) : setTimeout(fn, 1200));
const start = fr => fr.contentWindow.postMessage({ mapdemo: 'key', key: ' ' }, '*');   // the player's own play / pause key
function preloadFeatured() {
  if (S.fOn || !modal.hidden || lightNet() || S.pre === preKey()) return;
  S.pre = preKey(); S.fReady = false; S.playOnReady = false;
  fFrame.classList.add('pre'); fFrame.tabIndex = -1; fFrame.setAttribute('aria-hidden', 'true'); fFrame.hidden = false;
  fFrame.src = url(byId(S.f), false) + '&paused';
}
function playFeatured(keepTime) {
  closeModal(true);
  const d = byId(S.f), ready = !keepTime && S.pre === preKey();
  if (!keepTime) S.t = 0;
  S.fOn = true; S.pre = null; fPoster.hidden = true; fFrame.hidden = false;
  fFrame.classList.remove('pre'); fFrame.removeAttribute('aria-hidden'); fFrame.removeAttribute('tabindex');
  if (ready) {
    // already loaded under the poster: start it now, or the moment it is ready
    if (S.fReady) start(fFrame); else S.playOnReady = true;
  } else {
    fFrame.src = url(d, keepTime);
  }
  try { fFrame.focus(); } catch {}
}
function stopFeatured() {
  if (!S.fOn && !S.pre) return;
  S.fOn = false; S.pre = null; S.playOnReady = false; fFrame.src = 'about:blank'; fFrame.hidden = true; fPoster.hidden = false;
}
fPoster.addEventListener('click', () => playFeatured(false));
// "Watch the work" goes to the featured player and starts it (the click lets it play with sound)
$('watch').addEventListener('click', e => {
  e.preventDefault();
  $('featured').scrollIntoView({ behavior: reduced() ? 'auto' : 'smooth', block: 'start' });
  if (!S.fOn) playFeatured(false);
});
document.querySelectorAll('[data-f]').forEach(b => b.addEventListener('click', () => {
  if (b.dataset.f === S.f) return;
  const was = S.fOn; showFeatured(b.dataset.f);
  if (was) playFeatured(false); else preloadFeatured();
}));
// arrow keys move between the two tabs, as in a tab list
document.querySelector('[role="tablist"]').addEventListener('keydown', e => {
  if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
  e.preventDefault(); e.stopPropagation();
  const tabs = [...document.querySelectorAll('[data-f]')], i = tabs.findIndex(b => b.dataset.f === S.f), n = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
  n.focus(); n.click();
});

// ── the pop-up player
const modal = $('modal'), mFrame = $('mFrame');
let lastFocus = null;
function fitModal() {
  if (modal.hidden) return;
  const top = $('mBox').querySelector('.m-top').offsetHeight + 12, note = 34, room = innerHeight - 32 - top - note;
  let w = Math.min(1240, innerWidth - 32), h;
  if (S.vert) { h = Math.max(380, room); w = Math.min(w, Math.max(Math.min(640, w), (h - 60) * 4 / 5)); }
  else { h = w * 9 / 16 + 60; if (h > room) { h = Math.max(300, room); w = Math.min(w, (h - 60) * 16 / 9); } }
  $('mBox').style.maxWidth = Math.round(w) + 'px'; mFrame.style.height = Math.round(h) + 'px';
}
function loadModal(keepTime) {
  const d = byId(S.m);
  $('mTag').textContent = d.tag; $('mTitle').textContent = d.title; $('mNote').textContent = d.note;
  $('mOpen').href = openUrl(d);
  if (!keepTime) S.t = 0;
  mFrame.src = url(d, keepTime);
  const h = '#' + d.id + (S.vert ? '-45' : ''); if (location.hash !== h) history.replaceState(null, '', h);
  fitModal();
}
function openModal(id) {
  stopFeatured();
  const first = modal.hidden;
  S.m = id; S.playing = true;
  if (first) { lastFocus = document.activeElement; modal.hidden = false; document.body.classList.add('locked'); }
  loadModal(false);
  if (first) $('mClose').focus();
}
function closeModal(quiet) {
  if (modal.hidden) return;
  modal.hidden = true; document.body.classList.remove('locked');
  mFrame.src = 'about:blank'; S.m = null;
  history.replaceState(null, '', location.pathname + location.search);
  if (!quiet && lastFocus) lastFocus.focus({ preventScroll: true });
  idle(preloadFeatured);
}
const step = k => { const i = DEMOS.findIndex(d => d.id === S.m); openModal(DEMOS[(i + k + DEMOS.length) % DEMOS.length].id); };
$('mPrev').onclick = () => step(-1);
$('mNext').onclick = () => step(1);
$('mClose').onclick = () => closeModal();
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
// keyboard focus stays inside the pop-up while it is open
modal.addEventListener('keydown', e => {
  if (e.key !== 'Tab') return;
  const f = [...modal.querySelectorAll('button, a[href], iframe')].filter(el => !el.hidden && el.offsetParent !== null);
  if (!f.length) return;
  const a = f[0], z = f[f.length - 1];
  if (e.shiftKey && document.activeElement === a) { e.preventDefault(); z.focus(); }
  else if (!e.shiftKey && document.activeElement === z) { e.preventDefault(); a.focus(); }
});
document.getElementById('grid').addEventListener('click', e => {
  const a = e.target.closest('a.card'); if (!a) return;
  e.preventDefault(); openModal(a.dataset.id);
});

// ── 16:9 / 4:5, shared by both players
function setVert(v) {
  if (v === S.vert) return;
  S.vert = v; store.set('mapdemo.vert', v ? '1' : '0');
  document.querySelectorAll('[data-ar]').forEach(b => b.setAttribute('aria-pressed', (b.dataset.ar === '45') === v ? 'true' : 'false'));
  fitFeatured();
  if (!modal.hidden) loadModal(true);
  else if (S.fOn) playFeatured(true);
  else if (S.pre) preloadFeatured();
  $('fOpen').href = openUrl(byId(S.f));
}
document.querySelectorAll('[data-ar]').forEach(b => {
  b.setAttribute('aria-pressed', (b.dataset.ar === '45') === S.vert ? 'true' : 'false');
  b.addEventListener('click', () => setVert(b.dataset.ar === '45'));
});

// ── the filters
document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => {
  document.querySelectorAll('.chip').forEach(o => o.setAttribute('aria-pressed', o === c ? 'true' : 'false'));
  document.querySelectorAll('a.card').forEach(a => { a.hidden = c.dataset.k !== 'all' && a.dataset.k !== c.dataset.k; });
}));

// ── the players report back (only the one that is playing counts)
const active = () => (!modal.hidden ? mFrame : S.fOn ? fFrame : null);
addEventListener('message', e => {
  // the featured demo loading under its poster says when it is ready to start
  if (e.source === fFrame.contentWindow && e.data && e.data.mapdemo === 'ready' && !S.fReady) {
    S.fReady = true;
    if (S.playOnReady) { S.playOnReady = false; start(fFrame); }
  }
  const fr = active(); if (!fr || e.source !== fr.contentWindow) return;
  const d = e.data; if (!d || typeof d !== 'object') return;
  if (d.mapdemo === 'time') { S.t = d.t; S.playing = d.playing; }
  else if (d.mapdemo === 'hubKey') hubKey(d.key);
  else if (d.mapdemo === 'post' || d.mapdemo === 'ready') { S.post = { on: d.mapdemo === 'post' ? d.on : d.post, fps: d.fps }; store.set('mapdemo.posterize', JSON.stringify(S.post)); }
});
function hubKey(k) {
  if (k === 'v' || k === 'V') { setVert(!S.vert); return true; }
  if (!modal.hidden && (k === '[' || k === 'PageUp')) { step(-1); return true; }
  if (!modal.hidden && (k === ']' || k === 'PageDown')) { step(1); return true; }
  return false;
}
// keys pressed on this page go to the player that is playing; [ ] switch maps in the pop-up, V switches the frame
const FWD = new Set([' ', 'k', 'K', 'ArrowLeft', 'ArrowRight', ',', '.', '<', '>', 'Home', 'End', 'm', 'M', 'l', 'L', 'p', 'P']);
addEventListener('keydown', e => {
  if (e.ctrlKey || e.metaKey || e.altKey || /INPUT|SELECT|TEXTAREA/.test(e.target.tagName)) return;
  const k = e.key, fr = active();
  if (k === 'Escape') { if (!modal.hidden) closeModal(); return; }
  if (!fr) return;
  if (hubKey(k)) e.preventDefault();
  else if (k === 'f' || k === 'F') { e.preventDefault(); fr.requestFullscreen?.(); }
  else if (FWD.has(k)) {
    if ((k === ' ' || k === 'ArrowLeft' || k === 'ArrowRight') && e.target.closest('a, button, [role="tab"]')) return;
    e.preventDefault(); fr.contentWindow.postMessage({ mapdemo: 'key', key: k, shiftKey: e.shiftKey }, '*');
  }
});
addEventListener('resize', () => { fitFeatured(); fitModal(); });

// ── a link to one map (#13, #05-45) opens it in the pop-up
function readHash() {
  const m = /^#?(\d\d)(-45)?$/.exec(location.hash);
  if (!m || !byId(m[1])) return;
  if (!!m[2] !== S.vert) { S.vert = !!m[2]; document.querySelectorAll('[data-ar]').forEach(b => b.setAttribute('aria-pressed', (b.dataset.ar === '45') === S.vert ? 'true' : 'false')); fitFeatured(); }
  openModal(m[1]);
}
addEventListener('hashchange', readHash);

// ── copy the email (the contact band)
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
$('copyMail').onclick = () => {
  const t = $('copyMailText');
  copyText(EMAIL).then(() => { t.textContent = '✓ Copied · ' + EMAIL; }, () => { t.textContent = EMAIL; });
  setTimeout(() => { t.textContent = 'Copy email · ' + EMAIL; }, 2600);
};
// ── back to top: shows after 300px, like the button on the rest of the site
const toTop = $('toTop');
const topBtn = () => toTop.classList.toggle('is-visible', scrollY > 300);
addEventListener('scroll', topBtn, { passive: true });
toTop.onclick = () => {
  scrollTo({ top: 0, behavior: reduced() ? 'auto' : 'smooth' });
  document.querySelector('.brand').focus({ preventScroll: true });   // the button hides at the top, so keyboard focus moves to the first link
};
topBtn();

showFeatured(S.f);
fitFeatured();
readHash();
if (document.readyState === 'complete') idle(preloadFeatured); else addEventListener('load', () => idle(preloadFeatured));
</script>
<?php if ( function_exists( 'rs_stats_beacon' ) ) { rs_stats_beacon(); } ?>
</body>
</html>
