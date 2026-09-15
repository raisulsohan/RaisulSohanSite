<?php
/**
 * Housekeeping.
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
 * 12. Housekeeping
 * ====================================================================== */

/*
 * Keep the block editor switched off.
 *
 * This is the whole of what the Classic Editor plugin was doing here. The
 * theme is built around the classic editor already: it hands that editor
 * its own stylesheet, puts the justify button back in its toolbar, and
 * drops the block library's CSS from the front end. use_block_editor_for_post
 * consults this filter too, so the one covers both.
 */
add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );

/**
 * Whether the toolbar wants a different language from the page under it.
 *
 * WordPress keeps two languages: the site's, for everything a reader
 * sees, and the user's own, for the admin. The toolbar on a front end
 * page belongs to the page as far as WordPress is concerned, so it comes
 * out in the site's language — Bengali — while the same toolbar inside
 * wp-admin comes out in whatever the user chose.
 *
 * Worked out once and remembered, because the answer has to survive the
 * switch: after switch_to_locale() runs, get_locale() returns the new
 * locale and the comparison below would say no switch ever happened.
 *
 * @return bool
 */
function rs_toolbar_needs_locale() {
	static $needed = null;

	if ( null === $needed ) {
		$needed = ! is_admin() && get_user_locale() !== get_locale();
	}

	return $needed;
}

/**
 * Put the toolbar in the language its owner reads.
 *
 * Hooked ahead of every core callback that builds a toolbar item, because
 * the items are where the translated words are made. wp_before_admin_bar_render
 * would look like the right place and is already too late.
 */
function rs_toolbar_locale() {
	if ( rs_toolbar_needs_locale() ) {
		switch_to_locale( get_user_locale() );
	}
}
add_action( 'admin_bar_menu', 'rs_toolbar_locale', -9999 );

/**
 * Hand the page back its own language.
 *
 * The toolbar renders at the very end of the footer, so this closes a
 * window with almost nothing left in it — but leaving a switch open is
 * the kind of thing that surprises whatever runs next.
 */
function rs_toolbar_locale_restore() {
	if ( rs_toolbar_needs_locale() ) {
		restore_previous_locale();
	}
}
add_action( 'wp_after_admin_bar_render', 'rs_toolbar_locale_restore' );

/**
 * Keep the emoji script out of the page. Bengali text does not need it.
 */
function rs_trim_head() {
	/* Only the site's own feed is worth announcing. feed_links_extra adds
	   one for comments, which this theme does not have a template for and
	   which will therefore never hold anything. */
	remove_action( 'wp_head', 'feed_links_extra', 3 );

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'rs_trim_head' );

/* The theme has no comment template, so the comment feed WordPress still
   advertises would never hold anything. Close the doors it belongs to. */
add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

/**
 * Security headers for the front end.
 *
 * Every value is static, so a full page cache stores them with the page
 * and hands them to every reader. HSTS is only sent over HTTPS, where a
 * browser is allowed to remember it.
 */
function rs_security_headers() {
	if ( is_admin() || headers_sent() ) {
		return;
	}

	header_remove( 'X-Powered-By' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()' );

	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
}
add_action( 'send_headers', 'rs_security_headers' );

/**
 * Keep the author's account name out of public reach.
 *
 * The users endpoint and the author archive both hand out the login-like
 * slug. This is a single author site with an About page, so neither is
 * needed by anyone who is not signed in.
 *
 * @param array $endpoints Registered REST routes.
 * @return array
 */
function rs_hide_users_endpoint( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)', '/wp/v2/users/me' ) as $route ) {
		unset( $endpoints[ $route ] );
	}

	return $endpoints;
}
add_filter( 'rest_endpoints', 'rs_hide_users_endpoint' );

/**
 * The author archive repeats the front page under the account name.
 */
function rs_redirect_author_archive() {
	if ( is_author() && ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'rs_redirect_author_archive' );

/**
 * Tell search engines where the same page lives in the other language.
 *
 * Posts and categories are paired by slug, which the two sites share;
 * the front page and the portfolio are paired by path. A page with no
 * twin says nothing, which is better than pointing at a 404.
 */
function rs_hreflang() {
	if ( ! is_multisite() || is_404() || is_search() ) {
		return;
	}

	if ( function_exists( 'rs_seo_plugin_active' ) && rs_seo_plugin_active() ) {
		return;
	}

	$en_sites = get_sites( array( 'path' => '/en/', 'number' => 1 ) );

	if ( empty( $en_sites ) ) {
		return;
	}

	$en_id   = (int) $en_sites[0]->blog_id;
	$main_id = (int) get_main_site_id();
	$here    = (int) get_current_blog_id();
	$other   = ( $here === $en_id ) ? $main_id : $en_id;
	$pair    = array();
	$req     = isset( $_SERVER['REQUEST_URI'] ) ? trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only compared against fixed paths.

	if ( is_singular( 'post' ) ) {
		$id            = get_queried_object_id();
		$pair[ $here ] = get_permalink( $id );

		switch_to_blog( $other );
		$twin = get_page_by_path( get_post_field( 'post_name', $id ), OBJECT, 'post' );
		if ( $twin && 'publish' === $twin->post_status ) {
			$pair[ $other ] = get_permalink( $twin );
		}
		restore_current_blog();
	} elseif ( is_front_page() || is_home() ) {
		$pair[ $here ]  = get_home_url( $here, '/' );
		$pair[ $other ] = get_home_url( $other, '/' );
	} elseif ( is_category() ) {
		$term          = get_queried_object();
		$pair[ $here ] = get_category_link( $term );

		switch_to_blog( $other );
		$twin = get_category_by_slug( $term->slug );
		if ( $twin ) {
			$pair[ $other ] = get_category_link( $twin );
		}
		restore_current_blog();
	} elseif ( preg_match( '~(?:^|/)portfolio$~i', $req ) ) {
		$pair[ $here ]  = get_home_url( $here, '/portfolio/' );
		$pair[ $other ] = get_home_url( $other, '/portfolio/' );
	} elseif ( is_page() ) {
		$id            = get_queried_object_id();
		$pair[ $here ] = get_permalink( $id );

		switch_to_blog( $other );
		$twin = get_page_by_path( get_post_field( 'post_name', $id ), OBJECT, 'page' );
		if ( $twin && 'publish' === $twin->post_status ) {
			$pair[ $other ] = get_permalink( $twin );
		}
		restore_current_blog();
	}

	if ( count( $pair ) < 2 || empty( $pair[ $main_id ] ) ) {
		return;
	}

	$langs = array(
		$main_id => 'bn-BD',
		$en_id   => 'en',
	);

	foreach ( $pair as $blog => $url ) {
		printf( '<link rel="alternate" hreflang="%s" href="%s">' . "\n", esc_attr( $langs[ $blog ] ), esc_url( $url ) );
	}

	printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( $pair[ $main_id ] ) );
}
add_action( 'wp_head', 'rs_hreflang', 3 );

/**
 * Drop the default block library CSS. This theme styles everything itself.
 */
function rs_dequeue_block_css() {
	if ( ! is_admin() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'rs_dequeue_block_css', 100 );

/**
 * Add Featured Post settings to the WordPress Customizer for live editing.
 */
function rs_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'rs_featured_section', array(
		'title'    => __( 'Featured Post Settings', 'raisul-sohan' ),
		'priority' => 30,
	) );

	/* ---- Animation master toggle ---- */
	$wp_customize->add_setting( 'rs_enable_animations', array(
		'default'   => true,
		'type'      => 'theme_mod',
		'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'rs_enable_animations', array(
		'label'       => __( 'Enable smooth animations', 'raisul-sohan' ),
		'description' => __( 'Fade-up transitions on featured post, resume bar, list rows and pagination.', 'raisul-sohan' ),
		'section'     => 'rs_featured_section',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'rs_featured_summary_length', array(
		'default'   => 250,
		'type'      => 'theme_mod',
		'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'rs_featured_summary_length', array(
		'label'       => __( 'Featured summary length (chars)', 'raisul-sohan' ),
		'description' => __( 'How many characters to show in the featured post sneak peek.', 'raisul-sohan' ),
		'section'     => 'rs_featured_section',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 50,
			'max'  => 1000,
			'step' => 10,
		),
	) );
}
add_action( 'customize_register', 'rs_customize_register' );

/**
 * Auto-convert uploaded images to WebP and resize them to save space.
 */
function rs_optimize_image_upload( $upload ) {
	if ( $upload['type'] === 'image/jpeg' || $upload['type'] === 'image/png' ) {
		$file_path = $upload['file'];
		
		if ( ! file_exists( $file_path ) ) {
			return $upload;
		}

		$image_editor = wp_get_image_editor( $file_path );
		
		if ( ! is_wp_error( $image_editor ) && $image_editor->supports_mime_type( 'image/webp' ) ) {
			// Resize if it's too large
			$max_width = 1600;
			$size = $image_editor->get_size();
			if ( ! is_wp_error( $size ) && ( $size['width'] > $max_width || $size['height'] > $max_width ) ) {
				$image_editor->resize( $max_width, $max_width, false );
			}
			
			$image_editor->set_quality( 80 );
			
			$path_parts    = pathinfo( $file_path );
			$webp_filename = $path_parts['filename'] . '.webp';
			$webp_path     = $path_parts['dirname'] . '/' . $webp_filename;
			
			$saved = $image_editor->save( $webp_path, 'image/webp' );
			
			if ( ! is_wp_error( $saved ) && file_exists( $saved['path'] ) ) {
				@unlink( $file_path );
				
				$upload['file'] = $saved['path'];
				$url_parts      = pathinfo( $upload['url'] );
				$upload['url']  = $url_parts['dirname'] . '/' . $webp_filename;
				$upload['type'] = 'image/webp';
			}
		}
	}
	return $upload;
}
add_filter( 'wp_handle_upload', 'rs_optimize_image_upload' );

/**
 * Set standard thumbnail generation quality to 80.
 */
function rs_image_quality( $quality ) {
	return 80;
}
add_filter( 'wp_editor_set_quality', 'rs_image_quality' );
