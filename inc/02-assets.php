<?php
/**
 * Assets.
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
 * 2. Assets
 * ====================================================================== */

/**
 * Enqueue fonts, stylesheet and script.
 */
function rs_assets() {
	wp_enqueue_style(
		'rs-fonts',
		get_template_directory_uri() . '/assets/fonts.css',
		array(),
		RS_VERSION
	);

	/* Built from src/css by `npm run build`; style.css only carries the
	   theme header now. */
	wp_enqueue_style( 'rs-style', get_template_directory_uri() . '/assets/style.min.css', array( 'rs-fonts' ), RS_VERSION );

	wp_enqueue_script(
		'rs-app',
		get_template_directory_uri() . '/assets/app.min.js',
		array(),
		RS_VERSION,
		true
	);

	wp_localize_script(
		'rs-app',
		'RS',
		array(
			'rest'       => esc_url_raw( rest_url( 'rs/v1/' ) ),
			'home'       => esc_url_raw( home_url( '/' ) ),
			'total'      => rs_published_count(),
			'phrases'    => rs_phrases(),
			'email'      => rs_option( 'rs_email' ),
			'siteName'   => get_bloginfo( 'name' ),
			'postId'     => is_singular( 'post' ) ? get_queried_object_id() : 0,
			'catId'      => is_category() ? get_queried_object_id() : 0,
			'animations' => get_theme_mod( 'rs_enable_animations', true ) ? 1 : 0,
			'isEn'       => rs_is_en(),
			'editBase'   => current_user_can( 'edit_posts' )
				? admin_url( 'post.php?action=edit&post=' )
				: '',
			'editNonce'  => current_user_can( 'edit_posts' )
				? wp_create_nonce( 'wp_rest' )
				: '',
			'strings'    => rs_is_en() ? array(
				'copied'       => 'Mail copied!',
				'copyFail'     => 'Failed to copy',
				'linkCopy'     => 'Link copied to clipboard',
				'noResult'     => 'No results found',
				'hint'         => 'Type title or content keyword',
				'results'      => ' results',
				'loading'      => 'Loading...',
				'error'        => 'Could not load writing',
				'fontSize'     => 'Text size',
				'fontDown'     => 'Decrease font size',
				'fontReset'    => 'Reset font size',
				'fontUp'       => 'Increase font size',
				'edit'         => 'Edit',
				'dashboard'    => 'Dashboard',
				'shareLabel'   => 'Share with others',
				'shareBtn'     => 'Share',
				'copyBtn'      => 'Copy link',
				'readLater'    => 'Read later',
				'inLater'      => 'Saved to list',
				'removeLater'  => 'Remove from list',
				'laterAdded'   => 'Added to reading list',
				'laterRemoved' => 'Removed from reading list',
				'resumeLabel'  => 'You were reading',
				'dismiss'      => 'Dismiss',
				'timeLeft'     => 'min left',
				'cardTitle'    => 'Quote card',
				'download'     => 'Download',
				'cardSaved'    => 'Card downloaded',
				'saveBtn'      => 'Save',
				'cancelBtn'    => 'Cancel',
				'saving'       => 'Saving...',
				'saved'        => 'Saved successfully',
				'saveFail'     => 'Failed to save',
				'confirmExit'  => 'Changes not saved. Leave anyway?',
				'installing'   => 'Installing app...',
				'installed'    => 'App installed successfully!',
				'iosInstall'   => 'Tap the share icon in Safari and select "Add to Home Screen"',
			) : array(
				'copied'       => __( 'Mail copied!', 'raisul-sohan' ),
				'copyFail'     => __( 'কপি হয়নি', 'raisul-sohan' ),
				'linkCopy'     => __( 'লিঙ্ক কপি হয়েছে', 'raisul-sohan' ),
				'noResult'     => __( 'কোনো ফলাফল পাওয়া যায়নি', 'raisul-sohan' ),
				'hint'         => __( 'শিরোনাম বা লেখার অংশ লিখুন', 'raisul-sohan' ),
				'results'      => __( 'টি ফলাফল', 'raisul-sohan' ),
				'loading'      => __( 'আসছে...', 'raisul-sohan' ),
				'error'        => __( 'লেখাটি আনা যায়নি', 'raisul-sohan' ),
				'fontSize'     => 'লেখার আকার',
				'fontDown'     => 'ছোট করুন',
				'fontReset'    => 'স্বাভাবিক আকার',
				'fontUp'       => 'বড় করুন',
				'edit'         => 'সম্পাদনা',
				'dashboard'    => 'ড্যাশবোর্ডে',
				'shareLabel'   => 'অন্যদেরও পড়তে দিন',
				'shareBtn'     => 'শেয়ার করুন',
				'copyBtn'      => 'লিঙ্ক কপি',
				'readLater'    => 'পরে পড়ব',
				'inLater'      => 'তালিকায় আছে',
				'removeLater'  => 'তালিকা থেকে সরান',
				'laterAdded'   => 'পরে পড়ার তালিকায় রাখা হলো',
				'laterRemoved' => 'তালিকা থেকে সরানো হলো',
				'resumeLabel'  => 'আপনি পড়ছিলেন',
				'dismiss'      => 'সরিয়ে দিন',
				'timeLeft'     => 'মিনিট বাকি',
				'cardTitle'    => 'উদ্ধৃতি কার্ড',
				'download'     => 'ডাউনলোড',
				'cardSaved'    => 'কার্ড ডাউনলোড হয়েছে',
				'saveBtn'      => 'সেভ করুন',
				'cancelBtn'    => 'বাতিল',
				'saving'       => 'সেভ হচ্ছে...',
				'saved'        => 'সেভ হয়েছে',
				'saveFail'     => 'সেভ হয়নি',
				'confirmExit'  => 'সম্পাদনা সেভ করা হয়নি। বাতিল করে বেরিয়ে যাবেন?',
				'installing'   => 'অ্যাপ ইনস্টল হচ্ছে...',
				'installed'    => 'অ্যাপ সফলভাবে ইনস্টল হয়েছে!',
				'iosInstall'   => 'সাফারির নিচে শেয়ার আইকনে ট্যাপ করে "Add to Home Screen" বেছে নিন',
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'rs_assets' );

/**
 * Set the colour scheme on <html> before the page paints.
 *
 * This has to be inline and early. app.js loads in the footer, so leaving
 * it to run there would show a light page first and then snap to dark.
 */
function rs_theme_boot() {
	?>
<script>
( function () {
	try {
		var stored = window.localStorage.getItem( 'rs-theme' );
		var dark = stored
			? 'dark' === stored
			: window.matchMedia( '(prefers-color-scheme: dark)' ).matches;

		document.documentElement.setAttribute( 'data-theme', dark ? 'dark' : 'light' );
	} catch ( e ) {
		/* Private mode or no matchMedia: light is the sane fallback. */
		document.documentElement.setAttribute( 'data-theme', 'light' );
	}

	/*
	 * A colour the reader chose, if there is one. app.js works the whole
	 * palette out and stores the finished custom properties, so this only
	 * has to write them back — no colour arithmetic before first paint,
	 * and none of it duplicated here.
	 *
	 * Its own try: a value that will not parse should cost the reader
	 * their tint, not the light or dark choice made just above.
	 */
	try {
		var tint = window.localStorage.getItem( 'rs-tint' );
		var saved = tint ? JSON.parse( tint ) : null;

		if ( saved && saved.vars ) {
			for ( var key in saved.vars ) {
				document.documentElement.style.setProperty( key, saved.vars[ key ] );
			}

			document.documentElement.setAttribute( 'data-theme', saved.light ? 'light' : 'dark' );
		}
	} catch ( e ) {}
}() );
</script>
	<?php
}
add_action( 'wp_head', 'rs_theme_boot', 1 );

/*
 * The body font is preloaded in header.php, on the line above wp_head(),
 * where the browser's preload scanner reaches it before anything this file
 * could print. The function that used to live here did the same job from
 * wp_head and was unhooked when that turned out to print the tag twice; it
 * then sat unhooked and unused for several releases, so it is gone. Fonts
 * are fetched in CORS mode even from our own origin, which is why the tag
 * in header.php carries crossorigin — without it the file downloads twice.
 */
