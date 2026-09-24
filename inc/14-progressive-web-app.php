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
			$theme_uri . '/assets/style.min.css?ver=' . RS_VERSION,
			$theme_uri . '/assets/app.min.js?ver=' . RS_VERSION,
			$theme_uri . '/assets/fonts/noto-serif-bengali-bengali.woff2',
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

	/*
	 * Only the cuts that exist, at the size they actually are.
	 *
	 * get_site_icon_url() answers with the full picture when the cut asked
	 * for was never made, and this went on to label whatever came back
	 * 512x512. On this site that meant the manifest advertised a 512 icon
	 * and pointed at the original upload — three hundred kilobytes of PNG,
	 * fetched by any browser that took the offer seriously.
	 *
	 * "maskable" is gone with it. A maskable icon has to keep its subject
	 * inside a safe circle, because Android crops it to whatever shape the
	 * launcher uses; claiming it of an ordinary square icon does not make
	 * it one, it just gets the edges cut off.
	 */
	$icon_id = (int) get_option( 'site_icon' );
	$seen    = array();

	foreach ( array( 192, 512 ) as $px ) {
		$cut = $icon_id ? wp_get_attachment_image_src( $icon_id, array( $px, $px ) ) : false;

		if ( ! $cut || empty( $cut[0] ) || isset( $seen[ $cut[0] ] ) ) {
			continue;
		}

		$seen[ $cut[0] ] = true;

		$manifest['icons'][] = array(
			'src'     => $cut[0],
			/* What the file is, not what was asked for. */
			'sizes'   => (int) $cut[1] . 'x' . (int) $cut[2],
			'type'    => 'image/png',
			'purpose' => 'any',
		);
	}

	/*
	 * No site icon, no icons. screenshot.png used to stand in here, which
	 * was 2560 by 1998 and half a megabyte — not an icon by shape, size or
	 * intention. A manifest without icons simply means the browser does not
	 * offer to install the site, which is the right answer for a site whose
	 * owner has not given it a picture to be installed as.
	 */

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

	/*
	 * No apple-touch-icon here. WordPress prints one itself from the Site
	 * Icon, through wp_site_icon() on wp_head, and this printed a second
	 * copy of the same address on every page — the same duplication the
	 * font preload was quietly making before it was moved to header.php.
	 */
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
	$here = rs_portfolio_path();

	/* The portfolio itself, not a project under it: /portfolio/<slug>/ is
	   routed to the portfolio page by a rewrite rule and needs nothing here. */
	if ( ! $here || '' !== $here['slug'] ) {
		return $template;
	}

	$portfolio_file = locate_template( array( 'page-portfolio.php' ) );

	if ( ! $portfolio_file ) {
		return $template;
	}

	global $wp_query;

	if ( $wp_query && $wp_query->is_404 ) {
		$wp_query->is_404 = false;
		status_header( 200 );
	}

	return $portfolio_file;
} );

/**
 * Filter document title for portfolio page if loaded virtually.
 */
add_filter( 'pre_get_document_title', function( $title ) {
	$here = rs_portfolio_path();

	/* A project's own title is set by rs_portfolio_document_title(). */
	if ( ! $here || '' !== $here['slug'] ) {
		return $title;
	}

	$brand = function_exists( 'rs_brand' ) ? rs_brand() : get_bloginfo( 'name' );

	return ( rs_is_en() ? 'Portfolio' : 'পোর্টফোলিও' ) . ' — ' . $brand;
} );
