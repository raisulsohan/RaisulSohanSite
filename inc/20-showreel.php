<?php
/**
 * The showreel at /showreel (with or without the slash).
 *
 * A page of its own, outside the site's header and stylesheet: the 2026
 * reel on a film page in Apple's manner, played from YouTube in the cut
 * that suits the device (16:9 on a computer, the 4:5 cut on a phone or a
 * tablet; the visitor can switch) under a full control bar of its own.
 * The page itself is parts/showreel.php. Static output, so the full page
 * cache can keep it as it is: the cut is picked in the browser.
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
define( 'RS_SHOWREEL_SLUG', 'showreel' );

function rs_showreel_rewrites() {
	add_rewrite_rule( '^' . RS_SHOWREEL_SLUG . '/?$', 'index.php?rs_showreel=1', 'top' );
}
add_action( 'init', 'rs_showreel_rewrites' );

/**
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function rs_showreel_query_vars( $vars ) {
	$vars[] = 'rs_showreel';
	return $vars;
}
add_filter( 'query_vars', 'rs_showreel_query_vars' );

/**
 * The page's one address, the canonical one and the one the rest of the
 * site links to: raisulsohan.com/showreel on the main edition, no slash.
 * The page is in English on both editions.
 *
 * @return string
 */
function rs_showreel_url() {
	$main = is_multisite() ? get_main_site_id() : null;

	return get_home_url( $main, '/' . RS_SHOWREEL_SLUG );
}

/**
 * Serve the page and stop; nothing else in the theme runs for it.
 *
 * At priority 1, before redirect_canonical() (10), so the address without
 * the slash is answered as it is rather than redirected.
 */
function rs_showreel_render() {
	if ( ! get_query_var( 'rs_showreel' ) ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	require RS_DIR . '/parts/showreel.php';
	exit;
}
add_action( 'template_redirect', 'rs_showreel_render', 1 );

/**
 * The page in the main edition's sitemap, beside the 3D asset map.
 *
 * @param string[] $urls Addresses of the standalone pages.
 * @return string[]
 */
function rs_showreel_sitemap_url( $urls ) {
	$urls[] = rs_showreel_url();
	return $urls;
}
add_filter( 'rs_standalone_sitemap_urls', 'rs_showreel_sitemap_url' );
