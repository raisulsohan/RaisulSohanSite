<?php
/**
 * The free 3D asset map at /3dinformation (with or without the slash).
 *
 * A finished page of its own, sent exactly as it was made: the file
 * parts/3dinformation.html carries its own styles, scripts, meta tags,
 * canonical link and EN/BN switch, so nothing of the site's header,
 * stylesheet or scripts is added to it. Static output, so the full page
 * cache can keep it as it is.
 *
 * No WordPress page is needed: the rule below answers the address, and
 * rs_flush_rewrite_on_update() adds it on the version bump.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The address, relative to each edition's root. */
define( 'RS_3D_INFO_SLUG', '3dinformation' );

function rs_3d_info_rewrites() {
	add_rewrite_rule( '^' . RS_3D_INFO_SLUG . '/?$', 'index.php?rs_3d_info=1', 'top' );
}
add_action( 'init', 'rs_3d_info_rewrites' );

/**
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function rs_3d_info_query_vars( $vars ) {
	$vars[] = 'rs_3d_info';
	return $vars;
}
add_filter( 'query_vars', 'rs_3d_info_query_vars' );

/**
 * The page's address, as its canonical link names it: no trailing slash.
 *
 * @return string
 */
function rs_3d_info_url() {
	return home_url( '/' . RS_3D_INFO_SLUG );
}

/**
 * The link the rest of the site gives the page (the portfolio's hero, the
 * footer, the command palette): always the canonical address on the main
 * edition, opened in the reader's language. The page reads #en or #bn on
 * load and switches to it.
 *
 * @return string
 */
function rs_3d_info_link() {
	$main = is_multisite() ? get_main_site_id() : null;

	return get_home_url( $main, '/' . RS_3D_INFO_SLUG ) . '#' . ( rs_is_en() ? 'en' : 'bn' );
}

/**
 * Serve the page and stop; nothing else in the theme runs for it.
 *
 * At priority 1, before redirect_canonical() (10), so the address without
 * the slash is answered as it is rather than redirected.
 */
function rs_3d_info_render() {
	if ( ! get_query_var( 'rs_3d_info' ) ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	readfile( RS_DIR . '/parts/3dinformation.html' );
	exit;
}
add_action( 'template_redirect', 'rs_3d_info_render', 1 );

/**
 * The page in the sitemap, on the main edition only: its canonical link
 * names raisulsohan.com/3dinformation, so that is the one address offered.
 */
function rs_3d_info_sitemap_provider() {
	if ( ! class_exists( 'WP_Sitemaps_Provider' ) || rs_seo_plugin_active() || ! is_main_site() ) {
		return;
	}

	if ( ! class_exists( 'RS_Standalone_Sitemap' ) ) {
		/**
		 * Lists the theme's standalone pages that ask to be indexed.
		 */
		class RS_Standalone_Sitemap extends WP_Sitemaps_Provider {
			public function __construct() {
				$this->name        = 'standalone';
				$this->object_type = 'standalone';
			}

			public function get_url_list( $page_num, $object_subtype = '' ) {
				return array(
					array( 'loc' => rs_3d_info_url() ),
				);
			}

			public function get_max_num_pages( $object_subtype = '' ) {
				return 1;
			}
		}
	}

	wp_register_sitemap_provider( 'standalone', new RS_Standalone_Sitemap() );
}
add_action( 'init', 'rs_3d_info_sitemap_provider' );
