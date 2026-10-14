<?php
/**
 * The map animation demos at /map-animation-demo/ (and /en/map-animation-demo/).
 *
 * A page of its own, outside the site's header and stylesheet: one player
 * that plays any of the demos in assets/demo/map-animation/ (copied there by
 * scripts/map-demo-sync.js), the cards to pick one, and a floating "Work
 * with me" button. The page itself is parts/map-animation-demo.php.
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
define( 'RS_MAP_DEMO_SLUG', 'map-animation-demo' );

function rs_map_demo_rewrites() {
	add_rewrite_rule( '^' . RS_MAP_DEMO_SLUG . '/?$', 'index.php?rs_map_demo=1', 'top' );
}
add_action( 'init', 'rs_map_demo_rewrites' );

/**
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function rs_map_demo_query_vars( $vars ) {
	$vars[] = 'rs_map_demo';
	return $vars;
}
add_filter( 'query_vars', 'rs_map_demo_query_vars' );

/**
 * The page's address on the current edition, or on another site of the
 * network when a blog id is given.
 *
 * @param int|null $blog_id Site id, or null for the current site.
 * @return string
 */
function rs_map_demo_url( $blog_id = null ) {
	return null === $blog_id
		? home_url( '/' . RS_MAP_DEMO_SLUG . '/' )
		: get_home_url( $blog_id, '/' . RS_MAP_DEMO_SLUG . '/' );
}

/** Serve the page and stop; nothing else in the theme runs for it. */
function rs_map_demo_render() {
	if ( ! get_query_var( 'rs_map_demo' ) ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	require RS_DIR . '/parts/map-animation-demo.php';
	exit;
}
add_action( 'template_redirect', 'rs_map_demo_render', 1 );
