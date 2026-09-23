<?php
/**
 * Raisul Sohan theme functions.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Bump this on every CSS or JS change (after `npm run build`): it is the
   cache buster in the ?ver= query string for style.min.css and app.min.js,
   and it must match the Version in style.css and package.json. */
define( 'RS_VERSION', '7.11.0' );

/** Rows per page before anyone changes it on the settings screen, and the
    value fallen back to if the field is ever emptied. */
define( 'RS_PER_PAGE', 10 );

/** The shape the heading banner is cropped to. Read by the front end and
    by the box on the settings screen that sets the crop, so the two
    cannot drift apart. */
define( 'RS_HERO_RATIO', '1600 / 300' );

/** Post meta holding the counts: every opening, and first time readers.
    Leading underscores keep them out of the Custom Fields box, where they
    would only invite editing. */
define( 'RS_VIEWS_KEY', '_rs_views' );
define( 'RS_READERS_KEY', '_rs_readers' );

/** Post meta holding what a list row needs but a post body is expensive to
    work out: the reading time and the summary. Both are filled the first
    time they are asked for and thrown away when the post is saved. */
define( 'RS_MINUTES_KEY', '_rs_minutes' );
define( 'RS_SUMMARY_KEY', '_rs_summary' );

/* Define constants early */
define( 'RS_DIR', get_template_directory() );
define( 'RS_URI', get_template_directory_uri() );

/* =========================================================================
 * GitHub Auto-Updater
 * ====================================================================== */

require_once get_template_directory() . '/inc/github-updater.php';
require_once get_template_directory() . '/inc/portfolio-cpt.php';

/*
 * The theme code itself, one file per area. The order matters only in
 * that it is the order the sections sat in when this was a single file.
 */
foreach ( array(
	'inc/01-theme-setup.php',
	'inc/02-assets.php',
	'inc/03-bengali-numbers-and-dates.php',
	'inc/04-text-helpers.php',
	'inc/05-theme-settings.php',
	'inc/06-icons.php',
	'inc/07-share-links.php',
	'inc/08-the-post-list.php',
	'inc/09-rest-endpoints.php',
	'inc/10-seo-meta.php',
	'inc/11-read-counts-in-the-admin.php',
	'inc/12-housekeeping.php',
	'inc/13-book-list.php',
	'inc/14-progressive-web-app.php',
) as $rs_module ) {
	require_once get_template_directory() . '/' . $rs_module;
}
unset( $rs_module );
