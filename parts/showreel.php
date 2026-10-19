<?php
/**
 * The showreel page, served by rs_showreel_render().
 *
 * English on both editions, with the main edition's address as canonical.
 * The reel plays from YouTube through the IFrame API, which loads only
 * when the visitor reaches for play; until then a still from the reel
 * stands in its place. The cut is picked in the browser before the first
 * paint (16:9 on a computer, 4:5 on a phone or a tablet; the switch under
 * the picture changes it), so the full page cache keeps one copy for all.
 * The control bar is the page's own and sits under the picture: nothing
 * is drawn over YouTube's player.
 *
 * The stills in assets/img/showreel/ are single frames of the two cuts;
 * the chapters and the cut times below are the reel's own timeline
 * (D:\GitHub\Showreel\reel\timeline.js).
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rs_sr_img   = RS_URI . '/assets/img/showreel/';
$rs_sr_ver   = '?ver=' . RS_VERSION;
$rs_sr_url   = rs_showreel_url();
$rs_sr_home  = home_url( '/' );
$rs_sr_email = rs_option( 'rs_email' ) ? rs_option( 'rs_email' ) : 'lettertosohan@gmail.com';
$rs_sr_phone = '+880 1775 860544';
$rs_sr_wa    = 'https://wa.me/8801775860544';

$rs_sr_title = 'Showreel 2026';
$rs_sr_desc  = 'The 2026 motion design and animation showreel of Raisul Sohan: 24 works in 60 seconds. Documentary and explainer animation, maps, 3D, character and UI motion, cut on every beat to an original score.';

/* The two cuts on YouTube: the same 60 seconds on the same beat. */
$rs_sr_cuts = array(
	'169' => array(
		'id'    => 'sY1wSRvi5CM',
		'watch' => 'https://www.youtube.com/watch?v=sY1wSRvi5CM',
		'name'  => 'I Make Stories MOVE. | Motion Design & Animation Showreel 2026',
		'date'  => '2026-10-16T07:39:56-07:00',
		'w'     => 1920,
		'h'     => 1080,
	),
	'45'  => array(
		'id'    => '5_J27wUs5qI',
		'watch' => 'https://www.youtube.com/shorts/5_J27wUs5qI',
		'name'  => '24 animations in 60 seconds. Wait for the CHOMP',
		'date'  => '2026-10-16T07:41:26-07:00',
		'w'     => 1080,
		'h'     => 1350,
	),
);

/* The chapters. img: the still's name in assets/img/showreel/ (ch-NN-169 / ch-NN-45). */
$rs_sr_chapters = array(
	array( 't' => 0, 'img' => '00', 'short' => 'Intro', 'name' => 'Intro', 'text' => '“I make stories move.” The big letters fill with the work, and the orange dot drops in as the full stop.' ),
	array( 't' => 4, 'img' => '04', 'short' => 'Documentary', 'name' => 'Documentary & explainer', 'text' => 'A fish breaks the sea on the first hit. Bombs fall on Pearl Harbor. 8,000 terracotta soldiers. An immune cell swallows a bacterium.' ),
	array( 't' => 16, 'img' => '16', 'short' => 'Maps', 'name' => 'Maps & data', 'text' => 'Globe dives, the Strait of Hormuz in 3D, a crime board stamped UNSOLVED, the Korean War front and Dhaka at night, a cut on every kick.' ),
	array( 't' => 30, 'img' => '30', 'short' => '3D & character', 'name' => '3D, character, UI & logo', 'text' => 'A 3D cube bursts in slow motion. A sniper, a flying drumstick and one loud CHOMP. A walk cycle, UI loops and a logo.' ),
	array( 't' => 44, 'img' => '44', 'short' => 'All of it', 'name' => 'All of it', 'text' => 'The logo pulls back into a wall of nine works, then 22 moments in five and a half seconds, one word on every beat.' ),
	array( 't' => 52, 'img' => '52', 'short' => 'Contact', 'name' => "Let's work together", 'text' => 'The end card: name, email and WhatsApp, as the score comes home.' ),
);

/* Every cut, in seconds: the shots of the three acts, the wall, the rush (a cut every half beat), the gap and the end card. */
$rs_sr_cut_times = array_merge(
	array( 4, 5, 5.5, 6, 6.5, 7, 7.5, 8, 9, 9.5, 10, 10.5, 11, 12, 13, 13.5, 14, 15 ),
	array( 16, 16.5, 17, 17.5, 18, 18.5, 19, 19.5, 20, 20.5, 21, 21.5, 22, 22.5, 23, 23.5, 24, 26, 27, 28 ),
	array( 30, 31, 31.5, 32, 32.5, 33, 33.5, 34, 34.5, 35, 35.5, 36, 36.5, 37, 37.5, 38, 39.5, 40.5, 41.5, 42, 42.5, 43 ),
	array( 44 ),
	range( 46, 51.25, 0.25 ),
	array( 51.5, 52 )
);
/* The two rests: half a beat of silence before the drop and before the end card. */
$rs_sr_rests = array( array( 29.5, 30 ), array( 51.5, 52 ) );

/* The beat ruler, drawn once here: 20 units a second, 120 beats, a bar line every four. */
$rs_sr_beats = '';
$rs_sr_bars  = '';
for ( $rs_sr_i = 0; $rs_sr_i <= 120; $rs_sr_i++ ) {
	if ( $rs_sr_i % 4 ) {
		$rs_sr_beats .= 'M' . ( $rs_sr_i * 10 ) . ' 36V64';
	} else {
		$rs_sr_bars .= 'M' . ( $rs_sr_i * 10 ) . ' 28V72';
	}
}
$rs_sr_cut_path = '';
foreach ( $rs_sr_cut_times as $rs_sr_t ) {
	$rs_sr_cut_path .= 'M' . round( $rs_sr_t * 20, 2 ) . ' 12V88';
}

$rs_sr_fmt = function ( $t ) {
	return floor( $t / 60 ) . ':' . str_pad( (string) floor( fmod( $t, 60 ) ), 2, '0', STR_PAD_LEFT );
};

/* Structured data: the page, the two videos (the 16:9 one with its chapters as clips) and the author. */
$rs_sr_person = array(
	'@type'  => 'Person',
	'@id'    => $rs_sr_home . '#person',
	'name'   => 'Raisul Sohan',
	'url'    => $rs_sr_home,
	'sameAs' => array( 'https://www.youtube.com/@lettertosohan' ),
);
$rs_sr_graph = array(
	array(
		'@type'              => 'WebPage',
		'@id'                => $rs_sr_url . '#page',
		'url'                => $rs_sr_url,
		'name'               => $rs_sr_title . ' · Raisul Sohan',
		'description'        => $rs_sr_desc,
		'inLanguage'         => 'en',
		'primaryImageOfPage' => $rs_sr_img . 'og.jpg' . $rs_sr_ver,
		'author'             => array( '@id' => $rs_sr_person['@id'] ),
	),
	$rs_sr_person,
);
foreach ( $rs_sr_cuts as $rs_sr_key => $rs_sr_cut ) {
	$rs_sr_video = array(
		'@type'        => 'VideoObject',
		'name'         => $rs_sr_cut['name'],
		'description'  => $rs_sr_desc,
		'thumbnailUrl' => array( $rs_sr_img . ( '169' === $rs_sr_key ? 'og.jpg' : 'poster-45.webp' ) . $rs_sr_ver ),
		'uploadDate'   => $rs_sr_cut['date'],
		'duration'     => 'PT1M',
		'embedUrl'     => 'https://www.youtube.com/embed/' . $rs_sr_cut['id'],
		'url'          => $rs_sr_cut['watch'],
		'width'        => $rs_sr_cut['w'],
		'height'       => $rs_sr_cut['h'],
		'author'       => array( '@id' => $rs_sr_person['@id'] ),
	);
	if ( '169' === $rs_sr_key ) {
		$rs_sr_video['hasPart'] = array();
		foreach ( $rs_sr_chapters as $rs_sr_n => $rs_sr_c ) {
			$rs_sr_video['hasPart'][] = array(
				'@type'       => 'Clip',
				'name'        => $rs_sr_c['name'],
				'startOffset' => $rs_sr_c['t'],
				'endOffset'   => isset( $rs_sr_chapters[ $rs_sr_n + 1 ] ) ? $rs_sr_chapters[ $rs_sr_n + 1 ]['t'] : 60,
				'url'         => $rs_sr_cut['watch'] . '&t=' . $rs_sr_c['t'] . 's',
			);
		}
	}
	$rs_sr_graph[] = $rs_sr_video;
}

/* Small inline icons (24 × 24, drawn with the text colour). */
$rs_sr_icons = array(
	'play'    => '<path d="M8 5.6v12.8a1 1 0 0 0 1.53.85l10.2-6.4a1 1 0 0 0 0-1.7L9.53 4.75A1 1 0 0 0 8 5.6Z" fill="currentColor" stroke="none"/>',
	'pause'   => '<rect x="6" y="5" width="4.2" height="14" rx="1.3" fill="currentColor" stroke="none"/><rect x="13.8" y="5" width="4.2" height="14" rx="1.3" fill="currentColor" stroke="none"/>',
	'restart' => '<path d="M4.5 12a7.5 7.5 0 1 0 2.2-5.3"/><path d="M4.5 4.2v4.6h4.6"/>',
	'back'    => '<path d="M6 6v12"/><path d="M18.5 6.9v10.2a.8.8 0 0 1-1.25.66l-7.3-5.1a.8.8 0 0 1 0-1.32l7.3-5.1a.8.8 0 0 1 1.25.66Z" fill="currentColor" stroke="none"/>',
	'fwd'     => '<path d="M18 6v12"/><path d="M5.5 6.9v10.2a.8.8 0 0 0 1.25.66l7.3-5.1a.8.8 0 0 0 0-1.32l-7.3-5.1a.8.8 0 0 0-1.25.66Z" fill="currentColor" stroke="none"/>',
	'loop'    => '<path d="m17 2.8 3.4 3.4L17 9.6"/><path d="M3.6 11.2V9.7a3.5 3.5 0 0 1 3.5-3.5h13.3"/><path d="m7 21.2-3.4-3.4L7 14.4"/><path d="M20.4 12.8v1.5a3.5 3.5 0 0 1-3.5 3.5H3.6"/>',
	'sound'   => '<path d="M11 5.2 6.4 9H3.6v6h2.8l4.6 3.8Z" fill="currentColor"/><path d="M15.3 8.8a4.6 4.6 0 0 1 0 6.4"/><path d="M18.3 6a8.6 8.6 0 0 1 0 12"/>',
	'muted'   => '<path d="M11 5.2 6.4 9H3.6v6h2.8l4.6 3.8Z" fill="currentColor"/><path d="m15.6 9.6 4.8 4.8M20.4 9.6l-4.8 4.8"/>',
	'full'    => '<path d="M4 9.5V4h5.5M20 9.5V4h-5.5M4 14.5V20h5.5M20 14.5V20h-5.5"/>',
	'exit'    => '<path d="M9.5 4v5.5H4M14.5 4v5.5H20M9.5 20v-5.5H4M14.5 20v-5.5H20"/>',
	'left'    => '<path d="m14.5 18-6-6 6-6"/>',
	'right'   => '<path d="m9.5 18 6-6-6-6"/>',
	'up'      => '<path d="M12 19V5"/><path d="m5.5 11.5 6.5-6.5 6.5 6.5"/>',
	'out'     => '<path d="M7 17 17 7"/><path d="M8.5 7H17v8.5"/>',
);
$rs_sr_icon = function ( $name, $class = '' ) use ( $rs_sr_icons ) {
	return '<svg class="ic' . ( $class ? ' ' . $class : '' ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $rs_sr_icons[ $name ] . '</svg>';
};
?><!doctype html>
<html lang="en" data-cut="169">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<script>
/* Before the first paint: the cut for this device (4:5 on a phone or a tablet, 16:9 on a computer), or the one picked in this visit. */
(function (d) {
	var c = '169', s = null;
	try {
		if (matchMedia('(hover: none) and (pointer: coarse)').matches || /Android|iPhone|iPad|iPod|Mobile|Silk|Kindle/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) c = '45';
		s = sessionStorage.getItem('rs-showreel-cut');
	} catch (e) {}
	if (s === '45' || s === '169') c = s;
	d.setAttribute('data-cut', c);
	d.setAttribute('data-picked', s ? 'visitor' : 'device');
	d.className += ' js';
})(document.documentElement);
</script>
<title><?php echo esc_html( $rs_sr_title . ' · Raisul Sohan' ); ?></title>
<meta name="description" content="<?php echo esc_attr( $rs_sr_desc ); ?>">
<link rel="canonical" href="<?php echo esc_url( $rs_sr_url ); ?>">
<meta property="og:type" content="video.other">
<meta property="og:site_name" content="Raisul Sohan">
<meta property="og:title" content="<?php echo esc_attr( 'I make stories move. Showreel 2026 · Raisul Sohan' ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $rs_sr_desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( $rs_sr_url ); ?>">
<meta property="og:image" content="<?php echo esc_url( $rs_sr_img . 'og.jpg' . $rs_sr_ver ); ?>">
<meta property="og:image:width" content="1280">
<meta property="og:image:height" content="720">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#000000">
<?php wp_site_icon(); ?>
<link rel="preload" as="image" href="<?php echo esc_url( $rs_sr_img . 'fill.webp' . $rs_sr_ver ); ?>" fetchpriority="high">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400..800&amp;display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400..800&amp;display=swap"></noscript>
<script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $rs_sr_graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<style>
	:root {
		--bg: #000; --tile: #161617; --tile2: #1d1d1f; --line: rgba(255, 255, 255, .1);
		--fg: #f5f5f7; --fg2: #a1a1a6; --fg3: #86868b;
		--acc: #ff4a1c; --ink: #0b0a0d;
		--sans: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", system-ui, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
		--ease: cubic-bezier(.25, .1, .25, 1);
		--out: cubic-bezier(.22, 1, .36, 1);
		--nav: 52px; --vh: 100vh;
		--col: min(980px, calc(100% - 32px));
		--edge: max(16px, calc((100% - 980px) / 2));
		color-scheme: dark;
	}
	@supports (height: 100svh) { :root { --vh: 100svh; } }
	* { box-sizing: border-box; }
	html { background: var(--bg); -webkit-text-size-adjust: 100%; scroll-padding-top: calc(var(--nav) + 16px); }
	body {
		margin: 0; background: var(--bg); color: var(--fg);
		font: 17px/1.47 var(--sans); letter-spacing: -.016em; font-optical-sizing: auto;
		-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility;
		overflow-x: clip;
	}
	a { color: inherit; }
	img { max-width: 100%; }
	button, select, input { font: inherit; letter-spacing: inherit; color: inherit; }
	button { -webkit-tap-highlight-color: transparent; }
	:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; border-radius: 6px; }
	.sr { position: absolute !important; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
	.col { width: var(--col); margin-inline: auto; }
	.ic { width: 24px; height: 24px; display: block; flex: none; }

	/* ── The local nav: a translucent layer the page scrolls under ── */
	.nav {
		position: sticky; top: 0; z-index: 50; height: var(--nav);
		background: rgba(22, 22, 23, .72); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px);
		border-bottom: 1px solid rgba(255, 255, 255, .08);
	}
	.nav .in { display: flex; align-items: center; gap: 18px; height: 100%; width: min(1240px, calc(100% - 32px)); margin: 0 auto; }
	.brand { display: inline-flex; align-items: baseline; font-weight: 600; font-size: 19px; letter-spacing: -.025em; text-decoration: none; white-space: nowrap; }
	.brand i { display: inline-block; width: .3em; height: .3em; margin-left: .08em; border-radius: 50%; background: var(--acc); }
	.ntitle { color: var(--fg2); font-size: 14px; padding-left: 16px; border-left: 1px solid rgba(255, 255, 255, .16); white-space: nowrap; }
	.nlinks { display: flex; gap: 26px; margin-left: auto; font-size: 13px; }
	.nlinks a { color: var(--fg2); text-decoration: none; transition: color .2s; }
	.nlinks a:hover { color: var(--fg); }
	.pill {
		display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 0; cursor: pointer;
		border-radius: 980px; background: var(--acc); color: var(--ink); font-weight: 600; text-decoration: none; white-space: nowrap;
		transition: background-color .2s, transform .12s var(--ease);
	}
	.pill:hover { background: #ff6238; }
	.pill:active { transform: scale(.97); }
	.pill:focus-visible { outline-color: #fff; }
	.nav .pill { font-size: 13px; padding: 5px 15px; min-height: 30px; }
	@media (max-width: 760px) { .nlinks { display: none; } .nav .pill { margin-left: auto; } }
	@media (max-width: 360px) { .ntitle { display: none; } }

	/* ── Hero ── */
	/* wider than the text column, so "stories move." stays on one line on a computer (about 5.6em wide) */
	.hero { width: min(1240px, calc(100% - 32px)); text-align: center; padding: clamp(64px, 9vw, 116px) 0 clamp(40px, 5.5vw, 72px); }
	.eyebrow { margin: 0 0 14px; color: var(--acc); font-weight: 600; font-size: clamp(17px, 1.7vw, 21px); letter-spacing: -.012em; }
	.hero h1 { margin: 0; font-weight: 700; }
	.h1a { display: block; font-size: clamp(30px, 4.2vw, 56px); line-height: 1.08; letter-spacing: -.03em; }
	.h1b { display: block; font-size: clamp(60px, 13.2vw, 196px); line-height: .94; letter-spacing: -.045em; padding: .02em 0 .07em; text-wrap: balance; }
	.fill { color: var(--fg); }
	@supports ((-webkit-background-clip: text) or (background-clip: text)) {
		/* The tight tracking ends the box inside the last letter, and the picture only paints inside the box:
		   room on both sides keeps every edge filled, and the negative margin keeps the letters where they were. */
		.fill {
			padding: 0 .12em; margin: 0 -.12em;
			color: transparent; -webkit-text-fill-color: transparent;
			background: #3a3a3c url("<?php echo esc_url( $rs_sr_img . 'fill.webp' . $rs_sr_ver ); ?>") 0 52% / auto 1.08em repeat-x;
			-webkit-background-clip: text; background-clip: text;
			-webkit-box-decoration-break: clone; box-decoration-break: clone;
			animation: pan 54s linear infinite;
		}
	}
	/* the strip is 19.2 frames wide for its height: 1.08em × 19.2 */
	@keyframes pan { to { background-position-x: -20.736em; } }
	.dot { display: inline-block; width: .17em; height: .17em; margin-left: .06em; border-radius: 50%; background: var(--acc); transform-origin: 50% 100%; }
	.lede { margin: clamp(18px, 2.4vw, 28px) auto 0; max-width: 30em; color: var(--fg2); font-size: clamp(19px, 2vw, 25px); line-height: 1.32; font-weight: 600; letter-spacing: -.02em; text-wrap: balance; }
	.lede b { color: var(--fg); font-weight: 600; }
	.cta { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 14px 28px; margin-top: clamp(26px, 3.4vw, 38px); }
	.cta .pill { font-size: 17px; padding: 12px 24px 12px 20px; min-height: 48px; }
	.cta .pill .ic { width: 18px; height: 18px; }
	.more { display: inline-flex; align-items: center; gap: 4px; color: var(--acc); font-size: 17px; text-decoration: none; }
	.more:hover { text-decoration: underline; text-underline-offset: 3px; }
	.more .ic { width: 15px; height: 15px; }

	.js .rise { animation: rise 1s var(--out) both; }
	.js .rise.d1 { animation-delay: .08s; } .js .rise.d2 { animation-delay: .18s; } .js .rise.d3 { animation-delay: .3s; } .js .rise.d4 { animation-delay: .42s; }
	@keyframes rise { from { opacity: 0; transform: translateY(26px); } }
	.js .dot { animation: drop 1.15s var(--ease) .75s both; }
	@keyframes drop {
		0% { transform: translateY(-2.4em) scale(.7); opacity: 0; animation-timing-function: cubic-bezier(.55, 0, 1, .45); }
		38% { transform: translateY(0) scale(1.18, .8); opacity: 1; animation-timing-function: cubic-bezier(0, .55, .45, 1); }
		56% { transform: translateY(-.34em) scale(.95, 1.06); animation-timing-function: cubic-bezier(.55, 0, 1, .45); }
		70% { transform: translateY(0) scale(1.08, .92); animation-timing-function: cubic-bezier(0, .55, .45, 1); }
		82% { transform: translateY(-.08em); }
		92%, 100% { transform: none; opacity: 1; }
	}

	/* ── The player ── */
	.stage { padding-bottom: clamp(72px, 10vw, 128px); }
	.frame { width: min(calc(100% - 32px), max(320px, min(1240px, calc((var(--vh) - var(--nav) - 190px) * 16 / 9)))); margin: 0 auto; }
	[data-cut="45"] .frame { width: min(calc(100% - 32px), max(300px, min(600px, calc((var(--vh) - var(--nav) - 210px) * 4 / 5)))); }
	.player {
		position: relative; border-radius: 28px; overflow: hidden; background: var(--tile); isolation: isolate;
		box-shadow: 0 0 0 1px rgba(255, 255, 255, .07), 0 40px 90px -30px rgba(0, 0, 0, .9);
	}
	.player:focus { outline: none; }
	.screen { position: relative; aspect-ratio: 16 / 9; background: #000; overflow: hidden; }
	[data-cut="45"] .screen { aspect-ratio: 4 / 5; }
	.facade { position: absolute; inset: 0; display: block; z-index: 1; color: #fff; text-decoration: none; cursor: pointer; }
	.facade img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 1.4s var(--out); }
	.facade::after { content: ""; position: absolute; inset: 0; background: radial-gradient(120% 90% at 50% 50%, rgba(0, 0, 0, .08) 30%, rgba(0, 0, 0, .5)); }
	.facade:hover img { transform: scale(1.025); }
	[data-cut="169"] .v45, [data-cut="45"] .v169 { display: none !important; }
	/* at the end the still is the reel's own end card */
	.facade .vend, .is-ended .facade img:not(.vend) { display: none; }
	.is-ended .facade .vend { display: block; }
	.bigplay {
		position: absolute; left: 50%; top: 50%; z-index: 1; translate: -50% -50%;
		display: flex; align-items: center; gap: 14px; padding: 9px 26px 9px 9px; border-radius: 980px; white-space: nowrap;
		background: rgba(28, 28, 30, .5); -webkit-backdrop-filter: blur(24px) saturate(160%); backdrop-filter: blur(24px) saturate(160%);
		box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .2), 0 10px 40px rgba(0, 0, 0, .35);
		transition: scale .35s var(--out), background-color .25s;
	}
	.facade:hover .bigplay { scale: 1.04; background-color: rgba(28, 28, 30, .62); }
	.facade:active .bigplay { scale: .97; transition-duration: .1s; }
	/* on the end card the button moves down, off its words */
	.is-ended .bigplay { top: auto; bottom: 7%; translate: -50% 0; }
	.bigplay .disc { position: relative; width: 58px; height: 58px; border-radius: 50%; background: #fff; color: #000; display: grid; place-items: center; }
	.bigplay .disc .ic { width: 26px; height: 26px; margin-left: 3px; }
	.bigplay b { display: block; font-size: 19px; font-weight: 600; letter-spacing: -.02em; }
	.bigplay small { display: block; font-size: 13px; color: rgba(255, 255, 255, .78); font-weight: 500; letter-spacing: 0; }
	.is-busy .bigplay .disc .ic { opacity: 0; }
	.is-busy .bigplay .disc::after { content: ""; position: absolute; inset: 17px; border-radius: 50%; border: 2.5px solid rgba(0, 0, 0, .18); border-top-color: #000; animation: spin .8s linear infinite; }
	@keyframes spin { to { transform: rotate(1turn); } }
	@media (max-width: 480px) { .bigplay { padding: 7px 20px 7px 7px; gap: 12px; } .bigplay .disc { width: 50px; height: 50px; } .bigplay b { font-size: 17px; } }
	.screen iframe { position: absolute; inset: 0; z-index: 2; width: 100%; height: 100%; border: 0; opacity: 0; transition: opacity .5s var(--ease); }
	.is-live .screen iframe { opacity: 1; }
	.is-live .facade { visibility: hidden; transition: visibility 0s .5s; }
	.oops { position: absolute; left: 50%; bottom: 18px; z-index: 1; transform: translateX(-50%); margin: 0; padding: 6px 14px; border-radius: 980px; background: rgba(28, 28, 30, .8); font-size: 13px; white-space: nowrap; }

	/* the control bar: under the picture, never over it */
	.bar { --barbg: var(--tile); container: bar / inline-size; padding: 8px clamp(10px, 1.6vw, 18px) 12px; background: var(--barbg); user-select: none; -webkit-user-select: none; }
	.seek { position: relative; height: 26px; display: flex; align-items: center; }
	input[type=range] { -webkit-appearance: none; appearance: none; margin: 0; background: transparent; cursor: pointer; touch-action: none; }
	#seek { width: 100%; height: 26px; --p: 0%; --b: 0%; }
	#seek::-webkit-slider-runnable-track { height: 5px; border-radius: 5px; background: linear-gradient(to right, var(--acc) 0 var(--p), rgba(255, 255, 255, .4) var(--p) max(var(--p), var(--b)), rgba(255, 255, 255, .16) 0); }
	#seek::-moz-range-track { height: 5px; border-radius: 5px; background: linear-gradient(to right, var(--acc) 0 var(--p), rgba(255, 255, 255, .4) var(--p) max(var(--p), var(--b)), rgba(255, 255, 255, .16) 0); }
	#seek::-webkit-slider-thumb { -webkit-appearance: none; width: 15px; height: 15px; margin-top: -5px; border-radius: 50%; background: #fff; box-shadow: 0 1px 5px rgba(0, 0, 0, .45); transform: scale(.8); transition: transform .15s var(--ease); }
	#seek::-moz-range-thumb { width: 15px; height: 15px; border: 0; border-radius: 50%; background: #fff; box-shadow: 0 1px 5px rgba(0, 0, 0, .45); transform: scale(.8); transition: transform .15s var(--ease); }
	.seek:hover #seek::-webkit-slider-thumb, #seek:active::-webkit-slider-thumb, #seek:focus-visible::-webkit-slider-thumb { transform: scale(1.05); }
	.seek:hover #seek::-moz-range-thumb, #seek:active::-moz-range-thumb { transform: scale(1.05); }
	#seek:focus-visible { outline-offset: 1px; }
	.ticks i { position: absolute; top: 50%; width: 3px; height: 9px; margin-left: -1.5px; transform: translateY(-50%); background: var(--barbg); pointer-events: none; }
	.tip {
		position: absolute; bottom: 30px; left: 0; z-index: 3; padding: 5px 9px; border-radius: 8px; white-space: nowrap; pointer-events: none;
		background: rgba(44, 44, 46, .92); -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);
		font-size: 12px; font-weight: 500; letter-spacing: 0; font-variant-numeric: tabular-nums;
		opacity: 0; transform: translate(-50%, 4px); transition: opacity .15s, transform .15s var(--ease);
	}
	.tip.on { opacity: 1; transform: translate(-50%, 0); }
	.tip b { color: var(--acc); font-weight: 600; margin-right: 6px; }
	.ctl { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 14px; margin-top: 2px; }
	.grp { display: flex; align-items: center; gap: 2px; }
	.ib {
		width: 40px; height: 40px; padding: 0; border: 0; border-radius: 50%; background: transparent; color: var(--fg);
		display: grid; place-items: center; cursor: pointer; transition: background-color .2s, transform .12s var(--ease), color .2s;
	}
	.ib:hover { background: rgba(255, 255, 255, .09); }
	.ib:active { transform: scale(.9); }
	.ib .ic { width: 22px; height: 22px; }
	.ib.main { width: 46px; height: 46px; background: var(--fg); color: #000; margin: 0 4px; }
	.ib.main:hover { background: #fff; }
	.ib.main .ic { width: 22px; height: 22px; }
	.ib[aria-pressed="true"] { color: var(--acc); }
	.i-pause, .is-playing .i-play, .i-muted, .is-muted .i-sound, .i-exit, .is-full .i-full { display: none; }
	.is-playing .i-pause, .is-muted .i-muted, .is-full .i-exit { display: block; }
	.read { display: flex; flex-wrap: wrap; gap: 2px 14px; font-size: 13px; line-height: 1.25; color: var(--fg2); letter-spacing: 0; font-variant-numeric: tabular-nums; white-space: nowrap; }
	.read b { color: var(--fg); font-weight: 600; }
	.spacer { flex: 1; }
	.speed { appearance: none; -webkit-appearance: none; height: 32px; padding: 0 12px; border: 0; border-radius: 9px; background: rgba(255, 255, 255, .09); font-size: 13px; font-weight: 600; letter-spacing: 0; cursor: pointer; text-align: center; }
	.speed:hover { background: rgba(255, 255, 255, .14); }
	.speed option { background: #2c2c2e; color: var(--fg); }
	#vol { width: 84px; height: 26px; margin: 0 6px 0 2px; --v: 100%; }
	#vol::-webkit-slider-runnable-track { height: 4px; border-radius: 4px; background: linear-gradient(to right, var(--fg) 0 var(--v), rgba(255, 255, 255, .2) var(--v)); }
	#vol::-moz-range-track { height: 4px; border-radius: 4px; background: linear-gradient(to right, var(--fg) 0 var(--v), rgba(255, 255, 255, .2) var(--v)); }
	#vol::-webkit-slider-thumb { -webkit-appearance: none; width: 13px; height: 13px; margin-top: -4.5px; border-radius: 50%; background: #fff; box-shadow: 0 1px 4px rgba(0, 0, 0, .4); }
	#vol::-moz-range-thumb { width: 13px; height: 13px; border: 0; border-radius: 50%; background: #fff; }
	@media (pointer: coarse) { .ib { width: 44px; height: 44px; } .ib.main { width: 48px; height: 48px; } #vol { display: none; } }
	.spd { display: flex; align-items: center; }
	/* a narrow player (a phone, or the 4:5 cut): the buttons on one row, the readouts and the speed under them */
	@container bar (max-width: 600px) {
		.ctl { gap: 2px 8px; }
		.spacer { display: none; }
		.t-grp { order: 1; } .o-grp { order: 2; margin-left: auto; }
		.read { order: 3; flex: 1; padding-left: 6px; } .spd { order: 4; }
	}

	/* full screen: the frame fills the screen, the bar stays under the picture */
	.player:fullscreen { width: 100%; height: 100%; border-radius: 0; display: flex; flex-direction: column; background: #000; box-shadow: none; }
	.player:-webkit-full-screen { width: 100%; height: 100%; border-radius: 0; display: flex; flex-direction: column; background: #000; box-shadow: none; }
	.player.is-pseudo { position: fixed; inset: 0; z-index: 100; border-radius: 0; display: flex; flex-direction: column; background: #000; padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left); }
	.player:fullscreen .screen, .player.is-pseudo .screen { flex: 1; min-height: 0; aspect-ratio: auto; }
	.player:-webkit-full-screen .screen { flex: 1; min-height: 0; aspect-ratio: auto; }
	.player:fullscreen .facade img, .player.is-pseudo .facade img { object-fit: contain; }
	.player:fullscreen .bar, .player.is-pseudo .bar { --barbg: #000; }
	.player:-webkit-full-screen .bar { --barbg: #000; }

	.under { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px 24px; margin-top: 22px; }
	.under p { margin: 0; color: var(--fg2); font-size: 14px; line-height: 1.45; max-width: 34em; }
	.under p b { color: var(--fg); font-weight: 600; }
	@media (hover: none) and (pointer: coarse) { .under .keys { display: none; } }
	.segwrap { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
	.seg { position: relative; display: inline-grid; grid-auto-flow: column; grid-auto-columns: 1fr; padding: 3px; border-radius: 980px; background: rgba(118, 118, 128, .24); }
	.seg button { position: relative; z-index: 1; padding: 7px 16px; min-height: 34px; border: 0; border-radius: 980px; background: transparent; font-size: 14px; font-weight: 600; white-space: nowrap; cursor: pointer; letter-spacing: -.01em; }
	.seg button span { color: var(--fg2); font-weight: 500; margin-left: 5px; }
	.seg .knob { position: absolute; z-index: 0; top: 3px; bottom: 3px; left: 3px; width: calc(50% - 3px); border-radius: 980px; background: #636366; box-shadow: 0 3px 8px rgba(0, 0, 0, .25); transition: transform .45s cubic-bezier(.3, 1.25, .5, 1); }
	[data-cut="45"] .seg .knob { transform: translateX(100%); }
	.seg button[aria-checked="true"] span { color: rgba(245, 245, 247, .8); }
	.hint { font-size: 12px; color: var(--fg3); }
	[data-picked="visitor"] .hint { visibility: hidden; }
	@media (max-width: 640px) { .under { flex-direction: column-reverse; align-items: stretch; } .segwrap { align-items: center; } .seg { width: 100%; } .under p { text-align: center; margin: 0 auto; } }
	@media (pointer: coarse) { .seg button { min-height: 40px; } }

	@supports (animation-timeline: view()) {
		@media (prefers-reduced-motion: no-preference) {
			.player { animation: grow linear both; animation-timeline: view(); animation-range: entry 0% cover 32%; }
			@keyframes grow { from { transform: scale(.9); } }
			.player:fullscreen, .player.is-pseudo { animation: none; transform: none; }
		}
	}

	/* ── Sections ── */
	.sect { padding: clamp(88px, 13vw, 168px) 0; }
	.sect + .sect { padding-top: 0; }
	.kicker { margin: 0 0 12px; color: var(--acc); font-size: clamp(17px, 1.6vw, 19px); font-weight: 600; letter-spacing: -.01em; }
	.h2 { margin: 0; font-size: clamp(40px, 6.6vw, 80px); line-height: 1.04; letter-spacing: -.04em; font-weight: 700; text-wrap: balance; }
	.intro { margin: clamp(18px, 2.2vw, 26px) 0 0; max-width: 32em; color: var(--fg2); font-size: clamp(19px, 1.9vw, 24px); line-height: 1.36; font-weight: 600; letter-spacing: -.02em; text-wrap: pretty; }
	.intro b { color: var(--fg); font-weight: 600; }
	.js .rv { opacity: 0; transform: translateY(32px); transition: opacity 1s var(--out), transform 1s var(--out); }
	.js .rv.in { opacity: 1; transform: none; }

	/* chapters: a gallery that bleeds off the right edge */
	.gallery {
		display: flex; gap: 20px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-padding-inline: var(--edge);
		padding: clamp(36px, 5vw, 56px) var(--edge) 28px; scrollbar-width: none; overscroll-behavior-x: contain;
	}
	.gallery::-webkit-scrollbar { display: none; }
	.card { flex: 0 0 auto; width: clamp(272px, 40vw, 500px); scroll-snap-align: start; color: inherit; text-decoration: none; }
	[data-cut="45"] .card { width: clamp(236px, 66vw, 330px); }
	.card .media { position: relative; display: block; aspect-ratio: 16 / 9; border-radius: 22px; overflow: hidden; background: var(--tile); transform: translateZ(0); }
	[data-cut="45"] .card .media { aspect-ratio: 4 / 5; }
	.card img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 1s var(--out); }
	.card:hover img { transform: scale(1.04); }
	.card .mini {
		position: absolute; right: 12px; bottom: 12px; width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; color: #fff;
		background: rgba(28, 28, 30, .5); -webkit-backdrop-filter: blur(16px) saturate(160%); backdrop-filter: blur(16px) saturate(160%);
		box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18); transition: transform .3s var(--out);
	}
	.card:hover .mini { transform: scale(1.08); }
	.card .mini .ic { width: 18px; height: 18px; margin-left: 2px; }
	.card .tc { display: block; margin-top: 18px; color: var(--acc); font-size: 14px; font-weight: 600; letter-spacing: 0; font-variant-numeric: tabular-nums; }
	.card .tc em { font-style: normal; color: var(--fg3); font-weight: 500; margin-left: 8px; opacity: 0; transition: opacity .3s; }
	.card.is-now .tc em { opacity: 1; }
	.card h3 { margin: 4px 0 6px; font-size: 21px; line-height: 1.2; font-weight: 600; letter-spacing: -.022em; }
	.card p { margin: 0; color: var(--fg2); font-size: 15px; line-height: 1.47; letter-spacing: -.01em; }
	.card.is-now .media { box-shadow: 0 0 0 2px var(--acc); }
	.paddles { display: flex; justify-content: flex-end; gap: 14px; }
	.paddle { width: 38px; height: 38px; border: 0; border-radius: 50%; background: rgba(210, 210, 215, .16); color: var(--fg); display: grid; place-items: center; cursor: pointer; transition: background-color .2s, opacity .2s, transform .12s; }
	.paddle:hover { background: rgba(210, 210, 215, .24); }
	.paddle:active { transform: scale(.92); }
	.paddle:disabled { opacity: .36; cursor: default; }
	.paddle .ic { width: 20px; height: 20px; }

	/* rhythm: the beat grid with every cut on it */
	.ruler { position: relative; margin-top: clamp(44px, 6vw, 72px); }
	.grid { position: relative; height: clamp(76px, 9vw, 104px); cursor: pointer; touch-action: pan-y; }
	.grid svg { position: absolute; inset: 0; width: 100%; height: 100%; overflow: visible; }
	.grid .beat { stroke: rgba(255, 255, 255, .26); }
	.grid .bar { stroke: rgba(255, 255, 255, .5); }
	.grid .cut { stroke: var(--acc); stroke-width: 2; }
	@media (max-width: 640px) { .grid .cut { stroke-width: 1; } }
	.grid .rest { fill: rgba(255, 255, 255, .07); }
	.grid .base { stroke: rgba(255, 255, 255, .16); }
	.head { position: absolute; inset: -6px 0; pointer-events: none; transform: translateX(var(--p, 0%)); transition: opacity .3s; opacity: 0; }
	.head::before { content: ""; position: absolute; left: -1px; top: 0; bottom: 0; width: 2px; border-radius: 2px; background: #fff; box-shadow: 0 0 12px rgba(255, 255, 255, .6); }
	.head::after { content: ""; position: absolute; left: -5px; top: -5px; width: 10px; height: 10px; border-radius: 50%; background: #fff; }
	.ruler.on .head { opacity: 1; }
	.hover { position: absolute; top: 0; bottom: 0; width: 1px; background: rgba(255, 255, 255, .55); pointer-events: none; opacity: 0; }
	.hover span { position: absolute; bottom: calc(100% + 6px); left: 0; transform: translateX(-50%); padding: 3px 7px; border-radius: 7px; background: rgba(44, 44, 46, .92); font-size: 12px; white-space: nowrap; font-variant-numeric: tabular-nums; }
	.grid:hover .hover { opacity: 1; }
	.chaps { position: relative; height: 52px; margin-top: 10px; }
	/* the upper labels sit on the page colour and above the lower ones, whose lines reach up past them to the ruler */
	.chaps button {
		position: absolute; top: 0; z-index: 2; padding: 4px 6px 0 9px; border: 0; background: var(--bg); text-align: left; cursor: pointer; white-space: nowrap;
		font-size: 13px; line-height: 1.25; color: var(--fg2); letter-spacing: -.005em;
	}
	.chaps button::before { content: ""; position: absolute; left: 0; top: 0; bottom: 4px; width: 1px; background: rgba(255, 255, 255, .3); }
	.chaps button:nth-child(even) { top: 24px; z-index: 1; }
	.chaps button:nth-child(even)::before { top: -24px; }
	.chaps button b { color: var(--fg); font-weight: 600; font-variant-numeric: tabular-nums; margin-right: 6px; }
	.chaps button:hover { color: var(--fg); }
	.chaps button:last-child { padding: 4px 9px 0 6px; transform: translateX(-100%); text-align: right; }
	.chaps button:last-child::before { left: auto; right: 0; }
	@media (max-width: 760px) { .chaps button span { display: none; } .chaps button b { margin: 0; } }
	.legend { display: flex; flex-wrap: wrap; gap: 10px 26px; margin: clamp(26px, 3vw, 36px) 0 0; padding: 0; list-style: none; color: var(--fg2); font-size: 14px; }
	.legend li { display: flex; align-items: center; gap: 9px; }
	.legend i { display: inline-block; width: 2px; height: 16px; border-radius: 1px; background: rgba(255, 255, 255, .5); }
	.legend .l-cut { background: var(--acc); width: 3px; }
	.legend .l-rest { width: 12px; height: 16px; background: rgba(255, 255, 255, .12); }
	.facts { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin: clamp(48px, 6vw, 80px) 0 0; padding: clamp(28px, 3vw, 36px) 0 0; border-top: 1px solid var(--line); }
	.facts div { min-width: 0; }
	.facts dt { color: var(--fg2); font-size: 14px; font-weight: 500; margin-top: 4px; letter-spacing: -.005em; }
	.facts dd { margin: 0; order: -1; font-size: clamp(28px, 3.6vw, 44px); font-weight: 700; letter-spacing: -.035em; line-height: 1.1; font-variant-numeric: tabular-nums; }
	.facts div { display: flex; flex-direction: column; }
	@media (max-width: 640px) { .facts { grid-template-columns: repeat(2, 1fr); row-gap: 28px; } }

	/* formats: the two cuts side by side, at the same height */
	.cuts { display: grid; grid-template-columns: 2.2222fr 1fr; gap: clamp(12px, 2.6vw, 28px); align-items: start; margin-top: clamp(40px, 6vw, 72px); }
	.cutfig { margin: 0; min-width: 0; }
	.cutfig button { position: relative; display: block; width: 100%; padding: 0; border: 0; border-radius: clamp(14px, 2.2vw, 24px); overflow: hidden; background: var(--tile); cursor: pointer; aspect-ratio: 16 / 9; transition: box-shadow .3s; }
	.cutfig.c45 button { aspect-ratio: 4 / 5; }
	.cutfig img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 1s var(--out); }
	.cutfig button:hover img { transform: scale(1.035); }
	.cutfig .now {
		position: absolute; left: 10px; top: 10px; padding: 4px 10px; border-radius: 980px; font-size: 12px; font-weight: 600; letter-spacing: 0; color: #fff;
		background: rgba(28, 28, 30, .55); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px); opacity: 0; transition: opacity .3s;
	}
	[data-cut="169"] .c169 .now, [data-cut="45"] .c45 .now { opacity: 1; }
	[data-cut="169"] .c169 button, [data-cut="45"] .c45 button { box-shadow: 0 0 0 2px var(--acc); }
	.cutfig figcaption { margin-top: 16px; }
	.cutfig figcaption b { display: block; font-size: clamp(17px, 1.9vw, 21px); font-weight: 600; letter-spacing: -.02em; }
	.cutfig figcaption span { display: block; color: var(--fg2); font-size: clamp(13px, 1.4vw, 15px); line-height: 1.4; }
	@media (max-width: 520px) { .cutfig .now { left: 6px; top: 6px; padding: 3px 7px; font-size: 11px; } }

	/* contact: the reel's own end card colour */
	.contact { background: var(--acc); color: var(--ink); text-align: center; padding: clamp(96px, 14vw, 176px) 0; }
	.contact .kicker { color: var(--ink); opacity: .78; }
	.contact .h2 { max-width: 13em; margin: 0 auto; font-size: clamp(44px, 7.4vw, 96px); }
	.contact .intro { margin: clamp(18px, 2.2vw, 26px) auto 0; color: rgba(11, 10, 13, .8); }
	.contact .cta .pill { background: var(--ink); color: #fff; }
	.contact .cta .pill:hover { background: #24222a; }
	.contact .cta .pill:focus-visible, .contact :focus-visible { outline-color: var(--ink); }
	.ghost { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 12px 24px; border-radius: 980px; color: var(--ink); text-decoration: none; font-size: 17px; font-weight: 600; box-shadow: inset 0 0 0 1.5px var(--ink); transition: background-color .2s, transform .12s var(--ease); }
	.ghost:hover { background: rgba(11, 10, 13, .08); }
	.ghost:active { transform: scale(.97); }
	.lines { margin: 28px 0 0; font-size: 15px; font-weight: 500; letter-spacing: -.005em; color: rgba(11, 10, 13, .8); }
	.lines a { color: var(--ink); font-weight: 600; text-decoration: none; border-bottom: 1px solid rgba(11, 10, 13, .35); }
	.lines span { margin: 0 10px; opacity: .5; }

	/* footer */
	.foot { padding: 34px 0 calc(40px + env(safe-area-inset-bottom)); color: var(--fg3); font-size: 12px; line-height: 1.5; letter-spacing: -.005em; }
	.foot nav { display: flex; flex-wrap: wrap; gap: 8px 22px; padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid var(--line); font-size: 13px; }
	.foot nav span { color: var(--fg2); font-weight: 600; }
	.foot nav a { color: var(--fg2); text-decoration: none; }
	.foot nav a:hover { color: var(--fg); text-decoration: underline; text-underline-offset: 3px; }
	.foot p { margin: 0 0 8px; max-width: 64em; }
	.foot p a { color: var(--fg2); }

	.top-btn {
		position: fixed; right: max(18px, env(safe-area-inset-right)); bottom: calc(18px + env(safe-area-inset-bottom)); z-index: 40;
		width: 44px; height: 44px; border: 0; border-radius: 50%; display: grid; place-items: center; cursor: pointer; color: var(--fg);
		background: rgba(44, 44, 46, .72); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px);
		box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .14), 0 8px 24px rgba(0, 0, 0, .35);
		opacity: 0; visibility: hidden; transform: translateY(8px) scale(.9); transition: opacity .3s, transform .4s var(--out), visibility 0s .3s;
	}
	.top-btn.on { opacity: 1; visibility: visible; transform: none; transition: opacity .3s, transform .4s var(--out); }
	.top-btn .ic { width: 18px; height: 18px; }

	@media (prefers-reduced-motion: reduce) {
		.fill, .js .dot, .js .rise { animation: none; }
		.js .rv { transform: none; transition: opacity .4s; }
		.facade img, .card img, .cutfig img, .bigplay { transition: none; }
		.facade:hover img, .card:hover img, .cutfig button:hover img { transform: none; }
	}
	@media (prefers-reduced-transparency: reduce) {
		.nav { background: #161617; -webkit-backdrop-filter: none; backdrop-filter: none; }
		.bigplay, .card .mini, .cutfig .now, .top-btn, .tip { background: #2c2c2e; -webkit-backdrop-filter: none; backdrop-filter: none; }
	}
	@media (prefers-contrast: more) {
		:root { --fg2: #d2d2d7; --fg3: #a1a1a6; }
		.nav { background: #000; border-bottom-color: rgba(255, 255, 255, .4); }
		.bigplay { background: #000; box-shadow: inset 0 0 0 2px #fff; }
	}
</style>
</head>
<body>
<header class="nav">
	<div class="in">
		<a class="brand" href="<?php echo esc_url( $rs_sr_home ); ?>">Raisul Sohan<i aria-hidden="true"></i></a>
		<span class="ntitle">Showreel 2026</span>
		<nav class="nlinks" aria-label="On this page">
			<a href="#chapters">Chapters</a>
			<a href="#rhythm">Rhythm</a>
			<a href="#formats">Formats</a>
			<a href="#contact">Contact</a>
		</nav>
		<a class="pill" href="#watch" data-play>Watch</a>
	</div>
</header>

<main>
	<section class="hero col" aria-labelledby="title">
		<p class="eyebrow rise">Showreel 2026</p>
		<h1 id="title">
			<span class="h1a rise d1">I make</span>
			<span class="h1b rise d2"><span class="fill">stories move</span><span class="dot" aria-hidden="true"></span><span class="sr">.</span></span>
		</h1>
		<p class="lede rise d3"><b>24 works. 60 seconds.</b> A cut on every beat.</p>
		<div class="cta rise d4">
			<a class="pill" href="#watch" data-play><?php echo $rs_sr_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>Watch the reel</a>
			<a class="more" id="ytLink" href="<?php echo esc_url( $rs_sr_cuts['169']['watch'] ); ?>" target="_blank" rel="noopener">Watch on YouTube<?php echo $rs_sr_icon( 'out' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></a>
		</div>
	</section>

	<section class="stage" id="watch" aria-label="The reel">
		<div class="frame">
			<div class="player" id="player" tabindex="-1">
				<div class="screen" id="screen">
					<a class="facade" id="facade" href="<?php echo esc_url( $rs_sr_cuts['169']['watch'] ); ?>" aria-label="Play the reel, 1 minute">
						<img class="v169" src="<?php echo esc_url( $rs_sr_img . 'poster-169.webp' . $rs_sr_ver ); ?>" srcset="<?php echo esc_url( $rs_sr_img . 'poster-169-960.webp' . $rs_sr_ver ); ?> 960w, <?php echo esc_url( $rs_sr_img . 'poster-169.webp' . $rs_sr_ver ); ?> 1920w" sizes="(max-width: 1272px) calc(100vw - 32px), 1240px" width="1920" height="1080" alt="A giant cartoon fish rising out of the sea at sunset, the first shot of the reel" fetchpriority="high">
						<img class="v45" src="<?php echo esc_url( $rs_sr_img . 'poster-45.webp' . $rs_sr_ver ); ?>" srcset="<?php echo esc_url( $rs_sr_img . 'poster-45-720.webp' . $rs_sr_ver ); ?> 720w, <?php echo esc_url( $rs_sr_img . 'poster-45.webp' . $rs_sr_ver ); ?> 1080w" sizes="(max-width: 632px) calc(100vw - 32px), 600px" width="1080" height="1350" alt="A giant cartoon fish rising out of the sea at sunset, the first shot of the reel">
						<img class="v169 vend" src="<?php echo esc_url( $rs_sr_img . 'ch-52-169.webp' . $rs_sr_ver ); ?>" width="960" height="540" alt="The reel's end card: Raisul Sohan, motion designer and animator, connect with me" loading="lazy">
						<img class="v45 vend" src="<?php echo esc_url( $rs_sr_img . 'ch-52-45.webp' . $rs_sr_ver ); ?>" width="720" height="900" alt="The reel's end card: Raisul Sohan, motion designer and animator, connect with me" loading="lazy">
						<span class="bigplay"><span class="disc"><?php echo $rs_sr_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span><span><b id="fLabel">Play the reel</b><small>1:00 · sound on</small></span></span>
					</a>
				</div>
				<div class="bar" role="group" aria-label="Player controls">
					<div class="seek" id="seekWrap">
						<input id="seek" type="range" min="0" max="60" step="any" value="0" aria-label="Seek" aria-valuetext="0:00 of 1:00">
						<div class="ticks" aria-hidden="true">
							<?php foreach ( array_slice( $rs_sr_chapters, 1 ) as $rs_sr_c ) : ?>
								<i style="left:<?php echo esc_attr( round( $rs_sr_c['t'] / 60 * 100, 4 ) ); ?>%"></i>
							<?php endforeach; ?>
						</div>
						<div class="tip" id="tip" aria-hidden="true"></div>
					</div>
					<div class="ctl">
						<div class="grp t-grp">
							<button type="button" class="ib" id="bRestart" aria-label="Restart" title="Restart (Home)"><?php echo $rs_sr_icon( 'restart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
							<button type="button" class="ib" id="bBack" aria-label="Previous frame" title="Previous frame (← or ,)"><?php echo $rs_sr_icon( 'back' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
							<button type="button" class="ib main" id="bPlay" aria-label="Play" title="Play (Space or K)"><?php echo $rs_sr_icon( 'play', 'i-play' ) . $rs_sr_icon( 'pause', 'i-pause' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
							<button type="button" class="ib" id="bFwd" aria-label="Next frame" title="Next frame (→ or .)"><?php echo $rs_sr_icon( 'fwd' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
						</div>
						<div class="read">
							<span><b id="tNow">0:00</b> / 1:00</span>
							<span>Frame <b id="fNow">1</b> / 3600</span>
						</div>
						<span class="spacer"></span>
						<div class="spd">
							<label class="sr" for="speed">Playback speed</label>
							<select class="speed" id="speed" title="Playback speed">
								<option value="0.25">0.25×</option>
								<option value="0.5">0.5×</option>
								<option value="0.75">0.75×</option>
								<option value="1" selected>1×</option>
								<option value="1.25">1.25×</option>
								<option value="1.5">1.5×</option>
								<option value="2">2×</option>
							</select>
						</div>
						<div class="grp o-grp">
							<button type="button" class="ib" id="bLoop" aria-pressed="false" aria-label="Loop" title="Loop (L)"><?php echo $rs_sr_icon( 'loop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
							<button type="button" class="ib" id="bMute" aria-pressed="false" aria-label="Mute" title="Mute (M)"><?php echo $rs_sr_icon( 'sound', 'i-sound' ) . $rs_sr_icon( 'muted', 'i-muted' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
							<input id="vol" type="range" min="0" max="100" step="1" value="100" aria-label="Volume" title="Volume">
							<button type="button" class="ib" id="bFull" aria-label="Full screen" title="Full screen (F)"><?php echo $rs_sr_icon( 'full', 'i-full' ) . $rs_sr_icon( 'exit', 'i-exit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
						</div>
					</div>
				</div>
			</div>
			<div class="under">
				<p><b>Documentary, maps, 3D, character and UI motion</b>, cut to an original score. <span class="keys">Space plays, the arrow keys step one frame.</span></p>
				<div class="segwrap">
					<div class="seg" role="radiogroup" aria-label="Cut">
						<span class="knob" aria-hidden="true"></span>
						<button type="button" role="radio" aria-checked="true" data-cut="169">Widescreen<span>16:9</span></button>
						<button type="button" role="radio" aria-checked="false" data-cut="45">Vertical<span>4:5</span></button>
					</div>
					<span class="hint">Picked for your screen</span>
				</div>
			</div>
		</div>
	</section>

	<section class="sect" id="chapters" aria-labelledby="chTitle">
		<div class="col">
			<p class="kicker rv">Inside the reel</p>
			<h2 class="h2 rv" id="chTitle">Six chapters.<br>One minute.</h2>
			<p class="intro rv">Pick a chapter and <b>the reel plays from there.</b></p>
		</div>
		<div class="gallery rv" id="gallery">
			<?php foreach ( $rs_sr_chapters as $rs_sr_n => $rs_sr_c ) : ?>
				<a class="card" href="<?php echo esc_url( $rs_sr_cuts['169']['watch'] . '&t=' . $rs_sr_c['t'] . 's' ); ?>" data-t="<?php echo esc_attr( $rs_sr_c['t'] ); ?>" aria-label="<?php echo esc_attr( 'Play from ' . $rs_sr_fmt( $rs_sr_c['t'] ) . ', ' . $rs_sr_c['name'] ); ?>">
					<span class="media">
						<img class="v169" src="<?php echo esc_url( $rs_sr_img . 'ch-' . $rs_sr_c['img'] . '-169.webp' . $rs_sr_ver ); ?>" width="960" height="540" alt="" loading="lazy" decoding="async">
						<img class="v45" src="<?php echo esc_url( $rs_sr_img . 'ch-' . $rs_sr_c['img'] . '-45.webp' . $rs_sr_ver ); ?>" width="720" height="900" alt="" loading="lazy" decoding="async">
						<span class="mini"><?php echo $rs_sr_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
					</span>
					<span class="tc"><?php echo esc_html( $rs_sr_fmt( $rs_sr_c['t'] ) ); ?><em>Now</em></span>
					<h3><?php echo esc_html( $rs_sr_c['name'] ); ?></h3>
					<p><?php echo esc_html( $rs_sr_c['text'] ); ?></p>
				</a>
			<?php endforeach; ?>
		</div>
		<div class="col paddles">
			<button type="button" class="paddle" id="galPrev" aria-label="Previous chapters" disabled><?php echo $rs_sr_icon( 'left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
			<button type="button" class="paddle" id="galNext" aria-label="More chapters"><?php echo $rs_sr_icon( 'right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
		</div>
	</section>

	<section class="sect" id="rhythm" aria-labelledby="rhTitle">
		<div class="col">
			<p class="kicker rv">Edit and score</p>
			<h2 class="h2 rv" id="rhTitle">A cut on<br>every beat.</h2>
			<p class="intro rv">The score was composed for this reel at 120 beats a minute. <b>Each grey line is a beat, each orange line a cut.</b> Big moments hold for two beats, the rush cuts on every half beat, and two rests of half a beat let it breathe.</p>
			<div class="ruler rv" id="ruler">
				<div class="grid" id="grid">
					<svg viewBox="0 0 1200 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
						<path class="base" d="M0 50H1200" vector-effect="non-scaling-stroke"/>
						<?php foreach ( $rs_sr_rests as $rs_sr_r ) : ?>
							<rect class="rest" x="<?php echo esc_attr( $rs_sr_r[0] * 20 ); ?>" y="6" width="<?php echo esc_attr( ( $rs_sr_r[1] - $rs_sr_r[0] ) * 20 ); ?>" height="88"/>
						<?php endforeach; ?>
						<path class="beat" d="<?php echo esc_attr( $rs_sr_beats ); ?>" vector-effect="non-scaling-stroke"/>
						<path class="bar" d="<?php echo esc_attr( $rs_sr_bars ); ?>" vector-effect="non-scaling-stroke"/>
						<path class="cut" d="<?php echo esc_attr( $rs_sr_cut_path ); ?>" vector-effect="non-scaling-stroke"/>
					</svg>
					<div class="head" id="head"></div>
					<div class="hover" id="hover"><span></span></div>
				</div>
				<div class="chaps">
					<?php foreach ( $rs_sr_chapters as $rs_sr_c ) : ?>
						<button type="button" data-t="<?php echo esc_attr( $rs_sr_c['t'] ); ?>" style="left:<?php echo esc_attr( round( $rs_sr_c['t'] / 60 * 100, 4 ) ); ?>%" aria-label="<?php echo esc_attr( 'Play from ' . $rs_sr_fmt( $rs_sr_c['t'] ) . ', ' . $rs_sr_c['name'] ); ?>"><b><?php echo esc_html( $rs_sr_fmt( $rs_sr_c['t'] ) ); ?></b><span><?php echo esc_html( $rs_sr_c['short'] ); ?></span></button>
					<?php endforeach; ?>
				</div>
				<p class="sr"><?php echo esc_html( sprintf( 'A timeline of the reel: 120 beats in 60 seconds, %d cuts and two rests of half a beat, at 29.5 and 51.5 seconds.', count( $rs_sr_cut_times ) ) ); ?></p>
			</div>
			<ul class="legend rv" aria-hidden="true">
				<li><i class="l-cut"></i>Cut</li>
				<li><i></i>Beat</li>
				<li><i class="l-rest"></i>Rest, half a beat of silence</li>
			</ul>
			<dl class="facts rv">
				<div><dt>beats a minute</dt><dd>120</dd></div>
				<div><dt>cuts in 60 seconds</dt><dd><?php echo esc_html( count( $rs_sr_cut_times ) ); ?></dd></div>
				<div><dt>works</dt><dd>24</dd></div>
				<div><dt>frames a second</dt><dd>60</dd></div>
			</dl>
		</div>
	</section>

	<section class="sect" id="formats" aria-labelledby="fmTitle">
		<div class="col">
			<p class="kicker rv">Delivery</p>
			<h2 class="h2 rv" id="fmTitle">One reel.<br>Two cuts.</h2>
			<p class="intro rv"><b>Widescreen for YouTube, desktop and TV. Vertical for the feed.</b> Both run 60 seconds at 60 frames a second, on the same beat.</p>
			<div class="cuts rv">
				<figure class="cutfig c169">
					<button type="button" data-cut-play="169" aria-label="Play the 16:9 widescreen cut">
						<img src="<?php echo esc_url( $rs_sr_img . 'cut-169.webp' . $rs_sr_ver ); ?>" width="1280" height="720" alt="" loading="lazy" decoding="async">
						<span class="now">Selected</span>
					</button>
					<figcaption><b>16:9 Widescreen</b><span>1920 × 1080 · YouTube, desktop, TV</span></figcaption>
				</figure>
				<figure class="cutfig c45">
					<button type="button" data-cut-play="45" aria-label="Play the 4:5 vertical cut">
						<img src="<?php echo esc_url( $rs_sr_img . 'cut-45.webp' . $rs_sr_ver ); ?>" width="720" height="900" alt="" loading="lazy" decoding="async">
						<span class="now">Selected</span>
					</button>
					<figcaption><b>4:5 Vertical</b><span>1080 × 1350 · Phone feeds</span></figcaption>
				</figure>
			</div>
		</div>
	</section>

	<section class="contact" id="contact" aria-labelledby="ctTitle">
		<div class="col">
			<p class="kicker rv">Let's work together</p>
			<h2 class="h2 rv" id="ctTitle">Want your story to move like this?</h2>
			<p class="intro rv">Documentary and explainer animation, maps, 3D, character and UI motion, with sound. Delivered in 16:9 and 4:5.</p>
			<div class="cta rv">
				<a class="pill" href="<?php echo esc_url( 'mailto:' . $rs_sr_email . '?subject=' . rawurlencode( 'A project, after your showreel' ) ); ?>">Email me</a>
				<a class="ghost" href="<?php echo esc_url( $rs_sr_wa ); ?>" target="_blank" rel="noopener">WhatsApp</a>
			</div>
			<p class="lines rv"><a href="<?php echo esc_url( 'mailto:' . $rs_sr_email ); ?>"><?php echo esc_html( $rs_sr_email ); ?></a><span aria-hidden="true">·</span><a href="<?php echo esc_url( $rs_sr_wa ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $rs_sr_phone ); ?></a></p>
		</div>
	</section>
</main>

<footer class="foot">
	<div class="col">
		<nav aria-label="More work">
			<span>More work</span>
			<a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">Portfolio</a>
			<a href="<?php echo esc_url( rs_map_demo_url() ); ?>">Map animation demos</a>
			<a href="<?php echo esc_url( rs_3d_info_link() ); ?>">3D asset map</a>
			<a href="<?php echo esc_url( home_url( '/cv/' ) ); ?>">CV</a>
			<a href="https://www.youtube.com/@lettertosohan" target="_blank" rel="noopener">YouTube</a>
		</nav>
		<p>The reel plays from YouTube, and nothing from YouTube loads until you go to play it. The true-crime cases in the reel are fictional. Map data © OpenStreetMap contributors, Natural Earth, NASA Blue Marble and GEBCO; satellite imagery by Esri, Maxar, Earthstar Geographics and the GIS User Community.</p>
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Raisul Sohan · <a href="<?php echo esc_url( $rs_sr_home ); ?>">raisulsohan.com</a></p>
	</div>
</footer>

<button type="button" class="top-btn" id="toTop" aria-label="Back to top" title="Back to top"><?php echo $rs_sr_icon( 'up' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>

<script>
(() => {
'use strict';
// The two cuts on YouTube, the reel's chapters and its cuts (seconds).
const CUTS = <?php echo wp_json_encode( array_map( function ( $c ) { return array( 'id' => $c['id'], 'watch' => $c['watch'] ); }, $rs_sr_cuts ) ); ?>;
const CH = <?php echo wp_json_encode( array_map( function ( $c ) { return array( 't' => $c['t'], 'name' => $c['name'] ); }, $rs_sr_chapters ) ); ?>;
const CUT_T = <?php echo wp_json_encode( array_values( $rs_sr_cut_times ) ); ?>;
const DUR = 60, FPS = 60, FRAMES = DUR * FPS;

const root = document.documentElement;
const $ = id => document.getElementById(id);
const $$ = (sel, el = document) => Array.from(el.querySelectorAll(sel));
const reduce = matchMedia('(prefers-reduced-motion: reduce)');
const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
const fmt = t => { t = Math.max(0, t); const s = Math.floor(t + 1e-6); return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); };
const frameAt = t => Math.min(FRAMES, Math.floor(t * FPS + 1e-6) + 1);
const chapterAt = t => { let i = 0; CH.forEach((c, k) => { if (t + 1e-6 >= c.t) i = k; }); return i; };

const player = $('player'), screenEl = $('screen'), facade = $('facade'), seekEl = $('seek'), volEl = $('vol'), speedEl = $('speed');
const cards = $$('.card'), ruler = $('ruler'), grid = $('grid'), head = $('head');

// S.t is the playhead in seconds; YT is the YouTube player once it exists.
const S = { cut: root.dataset.cut === '45' ? '45' : '169', t: 0, playing: false, wantPlay: false, holdPause: false, holdT: 0, ready: false, pending: false,
	rate: 1, loop: false, muted: false, vol: 100, dragging: false, inView: false, pseudo: false, lastCt: 0, lastAt: 0, ovr: null, ci: -1, failed: false, cancelled: false, loadedCut: null };
let YT = null, revealT = 0, raf = 0;

/* ── YouTube's IFrame API: fetched the first time someone reaches for play ── */
let apiP = null;
function api() {
	if (apiP) return apiP;
	apiP = new Promise((res, rej) => {
		if (window.YT && window.YT.Player) { res(window.YT); return; }
		const prev = window.onYouTubeIframeAPIReady;
		window.onYouTubeIframeAPIReady = () => { if (typeof prev === 'function') prev(); res(window.YT); };
		const s = document.createElement('script');
		s.src = 'https://www.youtube.com/iframe_api';
		s.async = true;
		s.onerror = () => { apiP = null; rej(new Error('blocked')); };
		document.head.appendChild(s);
		setTimeout(() => rej(new Error('timeout')), 20000);
	});
	return apiP;
}
let warmed = false;
function warm() {
	if (warmed) return;
	warmed = true;
	['https://www.youtube-nocookie.com', 'https://i.ytimg.com', 'https://www.google.com'].forEach(href => {
		const l = document.createElement('link'); l.rel = 'preconnect'; l.href = href; l.crossOrigin = ''; document.head.appendChild(l);
	});
	api().catch(() => {});
}

function create(t, play) {
	S.t = clamp(t, 0, DUR); S.wantPlay = play;
	if (S.pending || YT) return;
	S.pending = true; S.failed = false; S.cancelled = false; S.loadedCut = S.cut;
	busy(true);
	api().then(Y => {
		const host = document.createElement('div');
		screenEl.appendChild(host);
		YT = new Y.Player(host, {
			host: 'https://www.youtube-nocookie.com',
			videoId: CUTS[S.cut].id,
			width: '100%', height: '100%',
			playerVars: { autoplay: S.wantPlay ? 1 : 0, controls: 0, disablekb: 1, fs: 0, iv_load_policy: 3, playsinline: 1, rel: 0, modestbranding: 1, start: Math.floor(S.t), origin: location.origin },
			events: { onReady: ready, onStateChange: changed, onPlaybackRateChange: e => { S.rate = e.data; speedEl.value = String(S.rate); }, onError: fail },
		});
	}, fail);
}
function ready() {
	S.ready = true; S.pending = false;
	const f = YT.getIframe();
	f.title = 'Raisul Sohan, Showreel 2026 (YouTube)';
	f.tabIndex = -1;   // the bar below is the keyboard way in
	keepFocus();
	YT.setVolume(S.vol);
	if (S.muted) YT.mute(); else YT.unMute();
	if (S.rate !== 1) YT.setPlaybackRate(S.rate);
	if (S.loadedCut !== S.cut) { S.loadedCut = S.cut; YT.cueVideoById({ videoId: CUTS[S.cut].id, startSeconds: S.t }); }
	else if (S.t > 0.01 || S.holdPause) YT.seekTo(S.t, true);
	if (S.wantPlay) YT.playVideo();
	stamp(S.t);
	clearTimeout(revealT);
	// If the browser holds back autoplay (iOS can), show YouTube's own frame so a tap on it starts the reel.
	revealT = setTimeout(() => { if (YT) { live(true); busy(false); } }, S.wantPlay || S.holdPause ? 1600 : 0);
}
function changed(e) {
	const st = e.data;
	if (st === 1) {
		if (S.holdPause || S.cancelled) {
			// started only to show a frame (or paused before it was ready): stop, and land on the exact frame asked for
			YT.pauseVideo();
			if (S.holdPause) seek(S.holdT, true);
			S.holdPause = false; S.cancelled = false;
		}
		else S.wantPlay = true;   // also when the start came from a tap on YouTube's own picture
		S.playing = true; live(true); busy(false);
	} else if (st === 2) {
		S.playing = false; S.wantPlay = false; busy(false);
	} else if (st === 0) {
		if (S.loop) { YT.seekTo(0, true); YT.playVideo(); return; }
		S.playing = false; S.wantPlay = false; S.t = DUR;
		end();
	}
	stamp(YT ? YT.getCurrentTime() : S.t);
	keepFocus();
	ui(); tick();
}
function fail() {
	S.pending = false; S.failed = true;
	destroy();
	busy(false);
	$('fLabel').textContent = 'Watch on YouTube';
	if (!$('oops')) {
		const p = document.createElement('p');
		p.id = 'oops'; p.className = 'oops'; p.textContent = 'The player could not load here. The link opens the reel on YouTube.';
		screenEl.appendChild(p);
	}
}
function destroy() {
	if (YT) { try { YT.destroy(); } catch (err) { /* already gone */ } }
	YT = null; S.ready = false; S.pending = false; S.playing = false;
	$$('iframe', screenEl).forEach(f => f.remove());
	live(false); ui();
}
function end() {
	destroy();
	sync(DUR);
	ui();
}
const live = on => player.classList.toggle('is-live', on);
const busy = on => player.classList.toggle('is-busy', on);

/* ── The clock: YouTube reports a few times a second; between reports the playhead runs on ── */
function stamp(t) { S.lastCt = t; S.lastAt = performance.now(); }
function cur() {
	if (!YT || !S.ready) return S.t;
	const now = performance.now();
	const ct = YT.getCurrentTime() || 0;
	if (S.ovr) {
		if (ct === S.ovr.from && now - S.ovr.at < 1500) return (S.t = clamp(S.ovr.t + (S.playing ? (now - S.ovr.at) / 1000 * S.rate : 0), 0, DUR));
		S.ovr = null; stamp(ct);
	}
	if (ct !== S.lastCt) stamp(ct);
	const run = S.playing ? Math.min((now - S.lastAt) / 1000 * S.rate, 0.4 * S.rate) : 0;
	return (S.t = clamp(ct + run, 0, DUR));
}
function tick() {
	if (raf) return;
	const frame = () => {
		raf = 0;
		if (!S.playing) { sync(cur()); return; }
		if (!S.dragging) sync(cur());
		raf = requestAnimationFrame(frame);
	};
	raf = requestAnimationFrame(frame);
}

/* ── What the controls do ── */
function play() {
	if (S.t >= DUR - 0.02) S.t = 0;
	if (S.failed) { location.href = CUTS[S.cut].watch; return; }
	if (!YT) { create(S.t, true); return; }
	S.wantPlay = true;
	if (S.ready) YT.playVideo();
}
function pause() {
	if (S.pending && S.wantPlay) S.cancelled = true;   // paused before the player was ready: hold it when it starts
	S.wantPlay = false;
	if (YT && S.ready) YT.pauseVideo();
	ui();
}
const toggle = () => (S.playing || (S.pending && S.wantPlay) ? pause() : play());
function seek(t, exact = true) {
	t = clamp(t, 0, DUR);
	S.t = t;
	if (YT && S.ready) {
		S.ovr = { t, from: YT.getCurrentTime() || 0, at: performance.now() };
		YT.seekTo(t, exact);
	}
	sync(t);
	if (!YT) ui();   // the still's label: Watch again only at the end
}
function playAt(t) {
	t = clamp(t, 0, DUR - 0.05);
	if (!YT) { create(t, true); sync(t); return; }
	S.wantPlay = true;
	if (!S.ready) { S.t = t; return; }
	seek(t);
	YT.playVideo();
}
function step(n) {
	const base = S.ovr ? S.ovr.t : cur();
	const t = clamp(Math.round(base * FPS) + n, 0, FRAMES - 1) / FPS;
	if (!YT) { S.holdPause = true; S.holdT = t; create(t, false); sync(t); return; }
	if (S.playing) pause();
	seek(t);
}
function jump(d) { seek(clamp((S.ovr ? S.ovr.t : cur()) + d, 0, DUR - 1 / FPS)); }
function restart() { if (YT) { seek(0); if (!S.playing) play(); } else playAt(0); }
function toEnd() { if (S.playing) pause(); seek((FRAMES - 1) / FPS); }
function setMute(m) {
	S.muted = m;
	if (!m && S.vol === 0) { S.vol = 60; volEl.value = 60; }
	if (YT && S.ready) { m ? YT.mute() : YT.unMute(); if (!m) YT.setVolume(S.vol); }
	ui();
}
function setLoop(l) { S.loop = l; ui(); }
function setRate(r) { S.rate = r; if (YT && S.ready) YT.setPlaybackRate(r); }

const isFull = () => !!(document.fullscreenElement || document.webkitFullscreenElement) || S.pseudo;
function pseudo(on) {
	S.pseudo = on;
	player.classList.toggle('is-pseudo', on);
	document.body.style.overflow = on ? 'hidden' : '';
	ui();
}
function toggleFull() {
	if (S.pseudo) { pseudo(false); return; }
	if (document.fullscreenElement || document.webkitFullscreenElement) {
		(document.exitFullscreen || document.webkitExitFullscreen).call(document);
		return;
	}
	if (player.requestFullscreen) player.requestFullscreen().catch(() => pseudo(true));
	else if (player.webkitRequestFullscreen) player.webkitRequestFullscreen();
	else pseudo(true);   // iPhone Safari: no element full screen, so the player fills the window instead
}
document.addEventListener('fullscreenchange', ui);
document.addEventListener('webkitfullscreenchange', ui);

/* ── Cuts: 16:9 or 4:5, the same timeline, so the playhead carries over ── */
function setCut(c, by) {
	if (c !== '45' && c !== '169') return;
	if (by === 'visitor') { root.dataset.picked = 'visitor'; try { sessionStorage.setItem('rs-showreel-cut', c); } catch (err) { /* private mode */ } }
	if (c === S.cut) return;
	S.cut = c;
	root.dataset.cut = c;
	$$('.seg button').forEach(b => b.setAttribute('aria-checked', String(b.dataset.cut === c)));
	$('ytLink').href = CUTS[c].watch;
	facade.href = CUTS[c].watch;
	if (YT && S.ready) {
		const t = cur();
		if (S.playing || S.wantPlay) { S.ovr = null; S.loadedCut = c; YT.loadVideoById({ videoId: CUTS[c].id, startSeconds: t }); S.t = t; stamp(t); }
		else { destroy(); S.t = t; sync(t); }
	}
	galUpdate();
}

/* ── The screen: readouts, the seek bar, the ruler's playhead, the chapter now playing ── */
function sync(t) {
	S.t = t;
	const p = (t / DUR * 100).toFixed(3) + '%';
	if (!S.dragging) seekEl.value = String(t);
	seekEl.style.setProperty('--p', p);
	if (YT && S.ready && YT.getVideoLoadedFraction) seekEl.style.setProperty('--b', (YT.getVideoLoadedFraction() * 100).toFixed(2) + '%');
	const ci = chapterAt(t);
	seekEl.setAttribute('aria-valuetext', fmt(t) + ' of 1:00, ' + CH[ci].name);
	$('tNow').textContent = fmt(t);
	$('fNow').textContent = frameAt(t);
	head.style.setProperty('--p', p);
	const on = !!YT || t > 0;
	ruler.classList.toggle('on', on);
	const now = YT ? ci : -1;
	if (now !== S.ci) { S.ci = now; cards.forEach((c, i) => c.classList.toggle('is-now', i === now)); }
}
function ui() {
	const playing = S.playing || (S.pending && S.wantPlay);
	player.classList.toggle('is-playing', playing);
	$('bPlay').setAttribute('aria-label', playing ? 'Pause' : 'Play');
	$('bPlay').title = playing ? 'Pause (Space or K)' : 'Play (Space or K)';
	player.classList.toggle('is-muted', S.muted || S.vol === 0);
	$('bMute').setAttribute('aria-pressed', String(S.muted));
	$('bMute').setAttribute('aria-label', S.muted ? 'Unmute' : 'Mute');
	$('bLoop').setAttribute('aria-pressed', String(S.loop));
	volEl.style.setProperty('--v', (S.muted ? 0 : S.vol) + '%');
	const full = isFull();
	player.classList.toggle('is-full', full);
	$('bFull').setAttribute('aria-label', full ? 'Exit full screen' : 'Full screen');
	const ended = !YT && S.t >= DUR - 0.05;
	player.classList.toggle('is-ended', ended);
	if (!S.failed) $('fLabel').textContent = ended ? 'Watch again' : 'Play the reel';
}

/* ── Wiring ── */
$$('[data-play]').forEach(a => a.addEventListener('click', e => {
	e.preventDefault();
	toPlayer();
	if (!S.playing) play();
}));
['pointerenter', 'focusin', 'touchstart'].forEach(ev => {
	player.addEventListener(ev, warm, { passive: true, once: true });
	$$('[data-play]').forEach(a => a.addEventListener(ev, warm, { passive: true, once: true }));
});
facade.addEventListener('click', e => {
	if (S.failed) return;   // the link itself opens YouTube
	e.preventDefault();
	play();
});
$('bPlay').onclick = toggle;
$('bRestart').onclick = restart;
$('bBack').onclick = () => step(-1);
$('bFwd').onclick = () => step(1);
$('bLoop').onclick = () => setLoop(!S.loop);
$('bMute').onclick = () => setMute(!S.muted);
$('bFull').onclick = toggleFull;
speedEl.onchange = () => setRate(parseFloat(speedEl.value));
volEl.addEventListener('input', () => {
	S.vol = parseInt(volEl.value, 10);
	if (YT && S.ready) YT.setVolume(S.vol);
	setMute(S.vol === 0);
});

// The seek bar: drag to scrub, the frame follows; a tip shows the time and the chapter.
const tip = $('tip'), seekWrap = $('seekWrap');
seekEl.addEventListener('pointerdown', () => { S.dragging = true; });
seekEl.addEventListener('input', () => {
	const t = Math.round(parseFloat(seekEl.value) * FPS) / FPS;
	S.t = t;
	if (YT && S.ready) { S.ovr = { t, from: YT.getCurrentTime() || 0, at: performance.now() }; YT.seekTo(t, false); }
	sync(t);
});
const release = () => {
	S.dragging = false;
	seek(Math.round(parseFloat(seekEl.value) * FPS) / FPS, true);
};
seekEl.addEventListener('change', release);
seekEl.addEventListener('pointerup', release);
seekWrap.addEventListener('pointermove', e => {
	const r = seekWrap.getBoundingClientRect();
	const x = clamp(e.clientX - r.left, 0, r.width), t = x / r.width * DUR;
	tip.innerHTML = '<b>' + fmt(t) + '</b>' + CH[chapterAt(t)].name;
	tip.style.left = clamp(x, tip.offsetWidth / 2, r.width - tip.offsetWidth / 2) + 'px';
	tip.classList.add('on');
});
seekWrap.addEventListener('pointerleave', () => tip.classList.remove('on'));

// Keys: Space/K play, ←/→ a frame (hold to keep stepping), Shift+←/→ a second, , and . a frame, Home/End, M, L, F.
document.addEventListener('keydown', e => {
	if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.altKey) return;
	const tg = e.target instanceof Element ? e.target : document.body;
	if (tg.closest('select, textarea, input:not(#seek), [contenteditable]')) return;
	if (!(S.inView || isFull() || tg.closest('.player'))) return;
	const k = e.key, onControl = !!tg.closest('a, button');
	if (k === ' ' || k === 'k' || k === 'K') {
		if (k === ' ' && onControl) return;   // a focused button answers Space itself
		e.preventDefault();
		if (!e.repeat) toggle();
	} else if (k === 'ArrowLeft' || k === 'ArrowRight') {
		e.preventDefault();
		const d = k === 'ArrowLeft' ? -1 : 1;
		if (e.shiftKey) jump(d); else step(d);
	} else if (k === ',' || k === '<') { e.preventDefault(); step(-1); }
	else if (k === '.' || k === '>') { e.preventDefault(); step(1); }
	else if (k === 'Home') { e.preventDefault(); seek(0); }
	else if (k === 'End') { e.preventDefault(); toEnd(); }
	else if (k === 'm' || k === 'M') { e.preventDefault(); setMute(!S.muted); }
	else if (k === 'l' || k === 'L') { e.preventDefault(); setLoop(!S.loop); }
	else if (k === 'f' || k === 'F') { e.preventDefault(); toggleFull(); }
	else if (k === 'Escape' && S.pseudo) { e.preventDefault(); pseudo(false); }
});
// YouTube's frame can take the focus (on load, or on a click on its picture); bring it back so the keys keep working.
function keepFocus() {
	setTimeout(() => {
		const f = YT && S.ready && YT.getIframe();
		if (f && document.activeElement === f) player.focus({ preventScroll: true });
	}, 0);
}
window.addEventListener('blur', keepFocus);

if ('IntersectionObserver' in window) {
	new IntersectionObserver(es => { S.inView = es[0].isIntersecting && es[0].intersectionRatio >= 0.3; }, { threshold: [0, 0.3, 0.6] }).observe(player);
} else S.inView = true;

function toPlayer() {
	const r = player.getBoundingClientRect();
	const top = scrollY + r.top - Math.max(64, (innerHeight - r.height) / 2);
	scrollTo({ top: Math.max(0, top), behavior: reduce.matches ? 'auto' : 'smooth' });
}

// The switch under the picture, and the two cuts further down.
$$('.seg button').forEach(b => b.addEventListener('click', () => setCut(b.dataset.cut, 'visitor')));
$$('[data-cut-play]').forEach(b => b.addEventListener('click', () => {
	setCut(b.dataset.cutPlay, 'visitor');
	toPlayer();
	if (!S.playing) play();
}));

// Chapters: play from there.
cards.forEach(a => a.addEventListener('click', e => { e.preventDefault(); toPlayer(); playAt(parseFloat(a.dataset.t)); }));
$$('.chaps button').forEach(b => b.addEventListener('click', () => playAt(parseFloat(b.dataset.t))));
$$('.card, .chaps button').forEach(el => el.addEventListener('pointerenter', warm, { once: true, passive: true }));

// The gallery's paddles.
const gal = $('gallery'), galPrev = $('galPrev'), galNext = $('galNext');
function galUpdate() {
	const max = gal.scrollWidth - gal.clientWidth - 2;
	galPrev.disabled = gal.scrollLeft <= 2;
	galNext.disabled = gal.scrollLeft >= max;
}
function galGo(d) {
	const w = cards[0].getBoundingClientRect().width + (parseFloat(getComputedStyle(gal).columnGap) || 20);
	gal.scrollBy({ left: d * w, behavior: reduce.matches ? 'auto' : 'smooth' });
}
galPrev.onclick = () => galGo(-1);
galNext.onclick = () => galGo(1);
gal.addEventListener('scroll', galUpdate, { passive: true });
addEventListener('resize', galUpdate);

// The beat ruler: hover shows the time, a click plays from the cut at or before it.
const hover = $('hover');
grid.addEventListener('pointermove', e => {
	const r = grid.getBoundingClientRect(), x = clamp(e.clientX - r.left, 0, r.width), t = x / r.width * DUR;
	hover.style.left = x + 'px';
	hover.firstElementChild.textContent = fmt(t) + ' · ' + CH[chapterAt(t)].name;
});
grid.addEventListener('click', e => {
	warm();
	const r = grid.getBoundingClientRect(), t = clamp(e.clientX - r.left, 0, r.width) / r.width * DUR;
	let at = 0;
	CUT_T.forEach(c => { if (c <= t + 0.12) at = c; });
	playAt(at);
});

// Sections rise into place as they come into view.
const rv = $$('.rv');
if ('IntersectionObserver' in window) {
	const io = new IntersectionObserver(es => es.forEach(en => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } }), { rootMargin: '0px 0px -8% 0px', threshold: 0.1 });
	rv.forEach(el => io.observe(el));
} else rv.forEach(el => el.classList.add('in'));

// Back to top: shows after 300px, like the button on the rest of the site.
const toTop = $('toTop');
const topBtn = () => toTop.classList.toggle('on', scrollY > 300);
addEventListener('scroll', topBtn, { passive: true });
toTop.onclick = () => {
	scrollTo({ top: 0, behavior: reduce.matches ? 'auto' : 'smooth' });
	document.querySelector('.brand').focus({ preventScroll: true });
};

// Start: the cut the head script picked, the readouts at zero.
$$('.seg button').forEach(b => b.setAttribute('aria-checked', String(b.dataset.cut === S.cut)));
$('ytLink').href = CUTS[S.cut].watch;
facade.href = CUTS[S.cut].watch;
topBtn(); galUpdate(); sync(0); ui();
})();
</script>
<?php if ( function_exists( 'rs_stats_beacon' ) ) { rs_stats_beacon(); } ?>
</body>
</html>
