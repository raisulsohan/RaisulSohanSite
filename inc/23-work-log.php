<?php
/**
 * The work log at /work/ (and /en/work/): every piece of work by the day it
 * went up, newest first.
 *
 * A page of its own, outside the site's header and stylesheet, like the map
 * animation demos and the showreel: a calendar strip of the days work went
 * up, a vertical timeline (list view) and an editor-style timeline with one
 * track per kind of work (editor view), both playing each piece in one
 * pop-up player. Every piece is an HTML page in the theme (or the showreel
 * page), so the player is an iframe. The page itself is
 * parts/work-log.php.
 *
 * The pieces are listed in rs_work_log_items(), below: add one there and it
 * appears in both views, in the calendar and in the player's order.
 *
 * No WordPress page is needed: the rule below answers the address on both
 * editions, and rs_flush_rewrite_on_update() adds it on the version bump.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The address, relative to each edition's root. */
define( 'RS_WORK_LOG_SLUG', 'work' );

function rs_work_log_rewrites() {
	add_rewrite_rule( '^' . RS_WORK_LOG_SLUG . '/?$', 'index.php?rs_work_log=1', 'top' );
}
add_action( 'init', 'rs_work_log_rewrites' );

/**
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function rs_work_log_query_vars( $vars ) {
	$vars[] = 'rs_work_log';
	return $vars;
}
add_filter( 'query_vars', 'rs_work_log_query_vars' );

/**
 * The page's address on the current edition, or on another site of the
 * network when a blog id is given.
 *
 * @param int|null $blog_id Site id, or null for the current site.
 * @return string
 */
function rs_work_log_url( $blog_id = null ) {
	return null === $blog_id
		? home_url( '/' . RS_WORK_LOG_SLUG . '/' )
		: get_home_url( $blog_id, '/' . RS_WORK_LOG_SLUG . '/' );
}

/** Serve the page and stop; nothing else in the theme runs for it. */
function rs_work_log_render() {
	if ( ! get_query_var( 'rs_work_log' ) ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	require RS_DIR . '/parts/work-log.php';
	exit;
}
add_action( 'template_redirect', 'rs_work_log_render', 1 );

/**
 * The main edition's address in the sitemap, beside the other standalone
 * pages.
 *
 * @param string[] $urls Addresses of the standalone pages.
 * @return string[]
 */
function rs_work_log_sitemap_url( $urls ) {
	$urls[] = rs_work_log_url( is_multisite() ? get_main_site_id() : null );
	return $urls;
}
add_filter( 'rs_standalone_sitemap_urls', 'rs_work_log_sitemap_url' );

/**
 * The kinds of work, top track first in the editor view.
 *
 * @return array[] key => { bn, en }
 */
function rs_work_log_kinds() {
	return array(
		'reel' => array( 'bn' => 'শোরিল', 'en' => 'Showreels' ),
		'map'  => array( 'bn' => 'ম্যাপ ও ডকুমেন্টারি', 'en' => 'Maps & documentary' ),
		'tool' => array( 'bn' => 'টুল প্রোমো', 'en' => 'Tool promos' ),
	);
}

/**
 * Every piece of work, newest first.
 *
 * The date is the day the piece went up on this site (the day its file
 * first came into this repository); change it if a piece was made earlier.
 *
 * Keys:
 * - id       Short, unique; it is the address of the piece (/work/#id).
 * - date     Y-m-d.
 * - kind     A key of rs_work_log_kinds().
 * - title    The same on both editions.
 * - type_bn, type_en  The line above the title.
 * - desc_bn, desc_en  One sentence for the card.
 * - img      The thumbnail, relative to the theme (16:9).
 * - src      The page that plays, relative to the theme, or a full URL.
 * - src45    Its own 4:5 cut, if it has one.
 * - map      true for a map animation player: it takes ?embed, and its
 *            4:5 frame is ?ar=45 when there is no src45.
 * - preview  false to skip the silent preview on hover.
 * - clips    For a project: the pieces inside it, each { title, src, img }.
 *
 * @return array[]
 */
function rs_work_log_items() {
	$map = 'assets/demo/map-animation/';
	$th  = $map . 'thumbs/';

	return array(
		array(
			'id'      => 'lazyreply',
			'date'    => '2026-11-03',
			'kind'    => 'tool',
			'title'   => 'LazyReply',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'বাংলায় লিখুন, ইংরেজি পাঠান: যেকোনো চ্যাট বক্সে।',
			'desc_en' => 'Write in Bengali, send in English, in any chat box.',
			'img'     => 'assets/img/lazyreply.webp',
			'src'     => 'assets/demo/lazyreply-demo.html',
		),
		array(
			'id'      => 'bahrain',
			'date'    => '2026-10-20',
			'kind'    => 'map',
			'title'   => 'Bahrain',
			'type_bn' => 'ক্লায়েন্ট ডকুমেন্টারি',
			'type_en' => 'Client documentary',
			'desc_bn' => 'নদীহীন এক মরু দ্বীপ নিয়ে ইউটিউব ডকুমেন্টারির ম্যাপ সিনের হাইলাইট।',
			'desc_en' => 'Highlights of the map scenes made for a YouTube documentary on a desert island with no rivers.',
			'img'     => $th . '14.jpg',
			'src'     => $map . 'bahrain/highlights.html',
			'map'     => true,
		),
		array(
			'id'      => 'showreel-2026',
			'date'    => '2026-10-19',
			'kind'    => 'reel',
			'title'   => 'Showreel 2026',
			'type_bn' => 'শোরিল',
			'type_en' => 'Showreel',
			'desc_bn' => '৬০ সেকেন্ডে ২৪টি কাজ, প্রতিটি বিটে একটি কাট।',
			'desc_en' => '24 pieces in 60 seconds, a cut on every beat.',
			'img'     => 'assets/img/showreel-2026.webp',
			'src'     => rs_showreel_url(),
			'preview' => false,
		),
		array(
			'id'      => 'hormuz',
			'date'    => '2026-10-14',
			'kind'    => 'map',
			'title'   => 'Strait of Hormuz',
			'type_bn' => '৩D স্যাটেলাইট নিউজ ম্যাপ',
			'type_en' => '3D satellite news map',
			'desc_bn' => 'স্যাটেলাইট রিলিফের উপর ৩D-তে ব্রেকিং নিউজ: রুটে একটা ডেস্ট্রয়ার, রেল থেকে ছোটা মিসাইল।',
			'desc_en' => 'Breaking news in 3D over satellite relief: a destroyer on its route, missiles off the rail.',
			'img'     => $th . '13.jpg',
			'src'     => $map . 'hormuz/hormuz-v2.html',
			'src45'   => $map . 'hormuz/hormuz-v2-4x5.html',
			'map'     => true,
		),
		array(
			'id'      => 'map-showreel',
			'date'    => '2026-10-14',
			'kind'    => 'reel',
			'title'   => 'Map Animation Showreel',
			'type_bn' => 'ম্যাপ অ্যানিমেশন প্রজেক্ট',
			'type_en' => 'Map animation project',
			'desc_bn' => '১৩টি ম্যাপ অ্যানিমেশন স্টাইল, কোডে বানানো। মূল রিলের সাথে প্রতিটা স্টাইল আলাদা ক্লিপ হিসেবেও আছে।',
			'desc_en' => '13 map animation styles made in code. Besides the reel, every style is a clip of its own.',
			'img'     => 'assets/img/map-animation-showreel.webp',
			'src'     => $map . 'showreel.html',
			'src45'   => $map . 'showreel-4x5.html',
			'map'     => true,
			'clips'   => array(
				array( 'title' => 'The World’s Largest Delta', 'src' => $map . '01-vox-delta.html', 'img' => $th . '01.jpg' ),
				array( 'title' => 'From Orbit to Dhaka', 'src' => $map . '02-globe-dive.html', 'img' => $th . '02.jpg' ),
				array( 'title' => 'Remittance Routes', 'src' => $map . '03-neon-routes.html', 'img' => $th . '03.jpg' ),
				array( 'title' => 'The Most Crowded Big Country', 'src' => $map . '04-3d-density.html', 'img' => $th . '04.jpg' ),
				array( 'title' => 'Case #0417', 'src' => $map . '05-crime-evidence-board.html', 'img' => $th . '05.jpg' ),
				array( 'title' => 'Signal Lost', 'src' => $map . '06-crime-surveillance.html', 'img' => $th . '06.jpg' ),
				array( 'title' => 'The Escape Route', 'src' => $map . '07-crime-dossier-route.html', 'img' => $th . '07.jpg' ),
				array( 'title' => 'Outside the Search Zone', 'src' => $map . '08-crime-search-grid.html', 'img' => $th . '08.jpg' ),
				array( 'title' => 'Manhattan, New York', 'src' => $map . '09-manhattan-vox.html', 'img' => $th . '09.jpg' ),
				array( 'title' => 'Dhaka, Bangladesh', 'src' => $map . '10-dhaka-night.html', 'img' => $th . '10.jpg' ),
				array( 'title' => 'Proof on Paper', 'src' => $map . 'shifting-shores.html', 'img' => $th . '11.jpg' ),
				array( 'title' => 'The Korean War', 'src' => $map . '12-korean-war.html', 'img' => $th . '12.jpg' ),
			),
		),
		array(
			'id'      => 'lazyfinds',
			'date'    => '2026-10-13',
			'kind'    => 'tool',
			'title'   => 'lazyFinds',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'ফাইলের নাম নয়, ভেতরে কী আছে তা লিখে খুঁজুন।',
			'desc_en' => 'Search files by what is inside them, not by their names.',
			'img'     => 'assets/img/lazyfinds.webp',
			'src'     => 'assets/demo/lazyfinds-demo.html',
		),
		array(
			'id'      => 'lazytyper',
			'date'    => '2026-10-10',
			'kind'    => 'tool',
			'title'   => 'LazyTyper',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'After Effects-এ এক ক্লিকে মানুষের মতো স্বাভাবিক টাইপিং অ্যানিমেশন।',
			'desc_en' => 'Human-like typing animation in After Effects, in one click.',
			'img'     => 'assets/img/lazytyper.webp',
			'src'     => 'assets/demo/lazytyper-demo.html',
		),
		array(
			'id'      => 'lazycat',
			'date'    => '2026-10-10',
			'kind'    => 'tool',
			'title'   => 'Lazy Cat Companion',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'ডেস্কটপের কোণে ঘুমায়, আদরে চোখ বোজে, ক্লিক করলে লাফিয়ে ওঠে।',
			'desc_en' => 'Sleeps in a corner of the desktop, purrs when petted, jumps when clicked.',
			'img'     => 'assets/img/lazycatcompanion.webp',
			'src'     => 'assets/demo/lazycat-demo.html',
		),
		array(
			'id'      => 'lazymaplayers',
			'date'    => '2026-10-09',
			'kind'    => 'tool',
			'title'   => 'LazyMapLayers',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'After Effects-এর জন্য ফ্রেম-নিখুঁত ম্যাপ ডিজাইন ও অ্যানিমেশন।',
			'desc_en' => 'Frame-exact map design and animation for After Effects.',
			'img'     => 'assets/img/lazymaplayers.svg',
			'src'     => 'assets/demo/lazymaplayers-demo.html',
		),
		array(
			'id'      => 'lazyeditmirror',
			'date'    => '2026-10-09',
			'kind'    => 'tool',
			'title'   => 'LazyEditMirror',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'ফ্রন্ট ক্যামেরার এডিট অডিও মিলিয়ে সাইড ক্যামেরায় কপি।',
			'desc_en' => 'Copies a front-camera edit onto the side camera, synced by audio.',
			'img'     => 'assets/img/lazyeditmirror.svg',
			'src'     => 'assets/demo/lazyeditmirror-demo.html',
		),
		array(
			'id'      => 'lazypin',
			'date'    => '2026-10-08',
			'kind'    => 'tool',
			'title'   => 'LazyPin',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'যেকোনো উইন্ডো এক ক্লিকে সবার উপরে পিন করে রাখুন।',
			'desc_en' => 'Pin any window on top of the others in one click.',
			'img'     => 'assets/img/lazypin.webp',
			'src'     => 'assets/demo/lazypin-demo.html',
			'src45'   => 'assets/demo/lazypin-demo-4x5.html',
		),
		array(
			'id'      => 'lazykick',
			'date'    => '2026-09-27',
			'kind'    => 'tool',
			'title'   => 'LazyKick',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'ক্লিপবোর্ড থেকে সরাসরি After Effects আর Premiere Pro-র টাইমলাইনে।',
			'desc_en' => 'From the clipboard straight to the After Effects and Premiere Pro timeline.',
			'img'     => 'assets/img/lazykick.svg',
			'src'     => 'assets/demo/lazykick-demo.html',
			'src45'   => 'assets/demo/lazykick-demo-vertical.html',
		),
		array(
			'id'      => 'lazy-image',
			'date'    => '2026-09-23',
			'kind'    => 'tool',
			'title'   => 'Lazy-Image',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'After Effects আর Premiere Pro-র ভেতরেই AI ইমেজ জেনারেটর।',
			'desc_en' => 'An AI image generator inside After Effects and Premiere Pro.',
			'img'     => 'assets/img/lazyimage.png',
			'src'     => 'assets/demo/lazy-image-demo.html',
			'src45'   => 'assets/demo/lazy-image-demo-vertical.html',
		),
		array(
			'id'      => 'lazymotiontoolkit',
			'date'    => '2026-09-22',
			'kind'    => 'tool',
			'title'   => 'LazyMotionToolkit',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'After Effects-এর জন্য নয়টি মোশন টুলের এক প্যানেল।',
			'desc_en' => 'Nine motion design tools in one After Effects panel.',
			'img'     => 'assets/img/lazymotiontoolkit.svg',
			'src'     => 'assets/demo/lazymotiontoolkit-demo.html',
			'src45'   => 'assets/demo/lazymotiontoolkit-demo-vertical.html',
		),
		array(
			'id'      => 'lazylord',
			'date'    => '2026-09-17',
			'kind'    => 'tool',
			'title'   => 'LazyLord',
			'type_bn' => 'টুল প্রোমো ভিডিও',
			'type_en' => 'Tool promo',
			'desc_bn' => 'Figma, Photoshop, Illustrator আর After Effects-এর মধ্যে আসল আর্টওয়ার্ক আদান-প্রদান।',
			'desc_en' => 'Moves real artwork between Figma, Photoshop, Illustrator and After Effects.',
			'img'     => 'assets/img/lazylord.svg',
			'src'     => 'assets/demo/lazylord-demo.html',
			'src45'   => 'assets/demo/lazylord-demo-vertical.html',
		),
	);
}
