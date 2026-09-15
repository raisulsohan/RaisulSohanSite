<?php
/**
 * Progressive Web App.
 *
 * Split out of functions.php; functions.php loads every inc/NN-*.php
 * file in numeric order, which is the order the code always ran in.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * 14. Progressive Web App
 * ====================================================================== */

/**
 * Serve the service worker with the correct scope header.
 *
 * The file lives inside the theme directory, but the worker has to control
 * every page on the site.  Service-Worker-Allowed: / lets the browser
 * accept a scope wider than the file's own directory.
 *
 * A JSON configuration block is prepended so the worker knows the theme
 * URI, the REST base, and which assets to pre-cache — all values that
 * only PHP can resolve.
 */
function rs_serve_sw() {
	if ( ! isset( $_GET['rs-sw'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$theme_uri = get_template_directory_uri();

	header( 'Content-Type: application/javascript; charset=UTF-8' );
	header( 'Service-Worker-Allowed: /' );
	header( 'Cache-Control: no-cache, no-store, must-revalidate' );

	$config = array(
		'version'    => RS_VERSION,
		'themeUri'   => $theme_uri,
		'restUrl'    => esc_url_raw( rest_url( 'rs/v1/' ) ),
		'offlineUrl' => $theme_uri . '/offline.html',
		'shell'      => array(
			$theme_uri . '/offline.html',
			$theme_uri . '/assets/fonts.css?ver=' . RS_VERSION,
			$theme_uri . '/assets/style.min.css?ver=' . RS_VERSION,
			$theme_uri . '/assets/app.min.js?ver=' . RS_VERSION,
			$theme_uri . '/assets/fonts/noto-serif-bengali-bengali.woff2',
			$theme_uri . '/assets/fonts/noto-sans-bengali-bengali.woff2',
		),
	);

	echo 'var RS_SW_CONFIG = ' . wp_json_encode( $config, JSON_UNESCAPED_SLASHES ) . ";\n\n";

	readfile( get_template_directory() . '/sw.js' );
	exit;
}
add_action( 'template_redirect', 'rs_serve_sw', 0 );

/**
 * Serve a web app manifest built from the site's own settings.
 */
function rs_serve_manifest() {
	if ( ! isset( $_GET['rs-manifest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	header( 'Content-Type: application/manifest+json; charset=UTF-8' );

	$manifest = array(
		'name'             => get_bloginfo( 'name' ),
		'short_name'       => get_bloginfo( 'name' ),
		'description'      => get_bloginfo( 'description' ),
		'start_url'        => home_url( '/' ),
		'display'          => 'standalone',
		'background_color' => '#eaecf1',
		'theme_color'      => '#eaecf1',
		'lang'             => get_bloginfo( 'language' ),
		'icons'            => array(),
	);

	$sizes = array( 192, 512 );

	foreach ( $sizes as $px ) {
		$url = get_site_icon_url( $px );

		if ( $url ) {
			$manifest['icons'][] = array(
				'src'     => $url,
				'sizes'   => $px . 'x' . $px,
				'type'    => 'image/png',
				'purpose' => 'any maskable',
			);
		}
	}

	if ( empty( $manifest['icons'] ) ) {
		$manifest['icons'][] = array(
			'src'     => get_template_directory_uri() . '/screenshot.png',
			'sizes'   => '512x512',
			'type'    => 'image/png',
			'purpose' => 'any maskable',
		);
	}

	echo wp_json_encode( $manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	exit;
}
add_action( 'template_redirect', 'rs_serve_manifest', 0 );

/**
 * PWA meta tags: manifest link and theme colour.
 */
function rs_pwa_head() {
	echo '<link rel="manifest" href="' . esc_url( home_url( '/?rs-manifest' ) ) . "\">\n";
	echo "<meta name=\"theme-color\" content=\"#eaecf1\">\n";
	echo "<meta name=\"mobile-web-app-capable\" content=\"yes\">\n";
	echo "<meta name=\"apple-mobile-web-app-capable\" content=\"yes\">\n";
	echo "<meta name=\"apple-mobile-web-app-status-bar-style\" content=\"default\">\n";
	$icon = get_site_icon_url( 180 );
	if ( $icon ) {
		echo '<link rel="apple-touch-icon" href="' . esc_url( $icon ) . "\">\n";
	}
}
add_action( 'wp_head', 'rs_pwa_head', 0 );

/* =========================================================================
 * Portfolio Virtual Template & Title Handler
 * ====================================================================== */

/**
 * Automatically load page-portfolio.php for /portfolio/ or /en/portfolio/ requests,
 * even before a static page is created in wp-admin.
 */
add_filter( 'template_include', function( $template ) {
	$req = isset( $_SERVER['REQUEST_URI'] ) ? trim( (string) parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ) : '';
	if ( 'portfolio' === $req || 'en/portfolio' === $req || preg_match( '~(?:^|/)portfolio/?$~i', $req ) ) {
		$portfolio_file = locate_template( array( 'page-portfolio.php' ) );
		if ( $portfolio_file ) {
			global $wp_query;
			if ( $wp_query && $wp_query->is_404 ) {
				$wp_query->is_404 = false;
				status_header( 200 );
			}
			return $portfolio_file;
		}
	}
	return $template;
} );

/**
 * Filter document title for portfolio page if loaded virtually.
 */
add_filter( 'pre_get_document_title', function( $title ) {
	$req = isset( $_SERVER['REQUEST_URI'] ) ? trim( (string) parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ) : '';
	if ( 'portfolio' === $req || 'en/portfolio' === $req || preg_match( '~(?:^|/)portfolio/?$~i', $req ) ) {
		$brand = function_exists( 'rs_brand' ) ? rs_brand() : get_bloginfo( 'name' );
		return ( function_exists( 'rs_is_en' ) && rs_is_en() ? 'Portfolio' : 'পোর্টফোলিও' ) . ' — ' . $brand;
	}
	return $title;
} );
