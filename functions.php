<?php
/**
 * Raisul Sohan theme functions.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Bump this on every CSS or JS change: it is the cache buster in the
   ?ver= query string for style.css and app.js. */
define( 'RS_VERSION', '7.4.4' );

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

/* =========================================================================
 * 1. Theme setup
 * ====================================================================== */

/**
 * Register theme supports.
 */
function rs_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'rs_primary' => __( 'Primary menu (below the header)', 'raisul-sohan' ),
		)
	);

	/* Shows the Classic editor the same serif and justified paragraphs
	   the front end uses, so the writing view is not a surprise. */
	add_editor_style( 'assets/editor.css' );
}
add_action( 'after_setup_theme', 'rs_setup' );

/**
 * How long each list is.
 *
 * The front page, the archives and the search results all go through
 * rs_render_list(), whose page links are built from the main query. So the
 * main query is what has to be sized here — on every one of those views,
 * or a category would paginate at the WordPress default while the front
 * page paginated at its own setting.
 *
 * The front page gets its own number because it is the one people arrive
 * on and scroll; an archive is somewhere they have already narrowed down
 * to and a shorter page reads better there.
 *
 * @param WP_Query $query Main query.
 */
function rs_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_home() ) {
		$query->set( 'posts_per_page', (int) rs_option( 'rs_home_per_page' ) );
		$query->set( 'ignore_sticky_posts', true );
	} elseif ( $query->is_archive() || $query->is_search() ) {
		$query->set( 'posts_per_page', (int) rs_option( 'rs_archive_per_page' ) );
		$query->set( 'ignore_sticky_posts', true );
	}
}
add_action( 'pre_get_posts', 'rs_pre_get_posts' );

/**
 * Put the justify button back in the Classic editor toolbar.
 *
 * WordPress dropped it from the default set but TinyMCE still ships the
 * command, so it only needs re-listing. The name is "alignjustify":
 * "justifyfull" was the TinyMCE 3 spelling and is silently ignored by the
 * TinyMCE 4 that WordPress bundles, which is why it must match the
 * alignleft / aligncenter / alignright already in this array.
 *
 * @param array $buttons First row of toolbar buttons.
 * @return array
 */
function rs_mce_buttons( $buttons ) {
	if ( in_array( 'alignjustify', $buttons, true ) ) {
		return $buttons;
	}

	$pos = array_search( 'alignright', $buttons, true );

	if ( false === $pos ) {
		$buttons[] = 'alignjustify';

		return $buttons;
	}

	array_splice( $buttons, $pos + 1, 0, 'alignjustify' );

	return $buttons;
}
add_filter( 'mce_buttons', 'rs_mce_buttons' );

/**
 * Force justify alignment on pasted text in post editor.
 */
function rs_tinymce_paste_justify_script() {
	?>
	<script>
	(function($) {
		function applyJustify(editor) {
			if (!editor || editor._rsJustifyBound) return;
			editor._rsJustifyBound = true;

			// 1. Process pasted HTML nodes
			editor.on('PastePostProcess', function(e) {
				if (e.node) {
					var nodes = $(e.node).find('p, h1, h2, h3, h4, h5, h6, div, blockquote, li').addBack('p, h1, h2, h3, h4, h5, h6, div, blockquote, li');
					nodes.each(function() {
						$(this).css('text-align', 'justify');
					});
				}
			});

			// 2. Also run right after paste to trigger TinyMCE's native justify command
			editor.on('paste', function() {
				setTimeout(function() {
					try {
						editor.formatter.apply('alignjustify');
					} catch(err) {}

					try {
						var node = editor.selection.getNode();
						if (node) {
							$(node).closest('p, h1, h2, h3, h4, h5, h6, div, blockquote, li').css('text-align', 'justify');
						}
					} catch(err) {}
				}, 30);
			});
		}

		// Attach to future editors
		$(document).on('tinymce-editor-init', function(event, editor) {
			applyJustify(editor);
		});

		// Attach to already initialized editors
		$(document).ready(function() {
			if (typeof tinymce !== 'undefined' && tinymce.editors) {
				for (var i = 0; i < tinymce.editors.length; i++) {
					applyJustify(tinymce.editors[i]);
				}
			}
		});
	})(jQuery);
	</script>
	<?php
}
add_action( 'admin_footer', 'rs_tinymce_paste_justify_script', 99 );

/* =========================================================================
 * 1.1 Language detection & Switcher
 * ====================================================================== */

/**
 * Check if the current context is English (subsite or English locale).
 *
 * @return bool
 */
function rs_is_en() {
	if ( is_multisite() && ! is_main_site() ) {
		return true;
	}
	$locale = get_locale();
	if ( 0 === strpos( $locale, 'en' ) ) {
		return true;
	}
	return false;
}

/**
 * Language switcher data for header toggle.
 *
 * @return array
 */
function rs_lang_switcher_data() {
	if ( rs_is_en() ) {
		$main_id = function_exists( 'get_main_site_id' ) ? get_main_site_id() : 1;
		$url     = is_multisite() ? get_home_url( $main_id, '/' ) : home_url( '/' );
		$label   = 'BN';
		$title   = 'বাংলায় পড়ুন';
	} else {
		$url = home_url( '/en/' );
		if ( is_multisite() ) {
			$sites = get_sites( array( 'path' => '/en/', 'number' => 1 ) );
			if ( ! empty( $sites ) ) {
				$url = get_home_url( $sites[0]->blog_id, '/' );
			}
		}
		$label = 'EN';
		$title = 'Read in English';
	}

	return array(
		'url'   => esc_url( $url ),
		'label' => $label,
		'title' => $title,
	);
}

/**
 * Add language class to body.
 *
 * @param array $classes Body classes.
 * @return array
 */
function rs_body_classes( $classes ) {
	if ( rs_is_en() ) {
		$classes[] = 'rs-en';
	}
	return $classes;
}
add_filter( 'body_class', 'rs_body_classes' );

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

	wp_enqueue_style( 'rs-style', get_stylesheet_uri(), array( 'rs-fonts' ), RS_VERSION );

	wp_enqueue_script(
		'rs-app',
		get_template_directory_uri() . '/assets/app.js',
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

/**
 * Start the body font downloading before the stylesheet is parsed.
 *
 * Only the Bengali serif subset: it is what nearly every glyph on the page
 * needs, and preloading more would compete with it for bandwidth. Fonts are
 * fetched in CORS mode even from our own origin, hence the crossorigin
 * attribute — without it the browser downloads the file twice.
 */
function rs_preload_font() {
	if ( ! rs_is_en() ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_template_directory_uri() . '/assets/fonts/noto-serif-bengali-bengali.woff2' )
		);
	} else {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_template_directory_uri() . '/assets/fonts/noto-serif-bengali-latin.woff2' )
		);
	}
}
add_action( 'wp_head', 'rs_preload_font', 2 );

/* =========================================================================
 * 3. Bengali numbers and dates
 * ====================================================================== */

/**
 * Convert ASCII digits to Bengali digits.
 *
 * @param string|int $value Value to convert.
 * @return string
 */
function rs_bn_digits( $value ) {
	if ( rs_is_en() ) {
		return (string) $value;
	}

	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$bn = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );

	return str_replace( $en, $bn, (string) $value );
}

/**
 * Abbreviated Bengali month names, matching the original design.
 *
 * @return array
 */
function rs_bn_months() {
	return array(
		1  => 'জানু',
		2  => 'ফেব্রু',
		3  => 'মার্চ',
		4  => 'এপ্রি',
		5  => 'মে',
		6  => 'জুন',
		7  => 'জুলাই',
		8  => 'আগস্ট',
		9  => 'সেপ্টে',
		10 => 'অক্টো',
		11 => 'নভে',
		12 => 'ডিসে',
	);
}

/**
 * Month names in full, for the one place that has room for them.
 *
 * The abbreviations above earn their shortness in a list row, where the
 * date sits beside a title and must not compete with it. On the index the
 * month is a heading of its own with a line to itself, and an abbreviation
 * there reads as a saving nobody asked for.
 *
 * @return array
 */
function rs_bn_months_full() {
	return array(
		1  => 'জানুয়ারি',
		2  => 'ফেব্রুয়ারি',
		3  => 'মার্চ',
		4  => 'এপ্রিল',
		5  => 'মে',
		6  => 'জুন',
		7  => 'জুলাই',
		8  => 'আগস্ট',
		9  => 'সেপ্টেম্বর',
		10 => 'অক্টোবর',
		11 => 'নভেম্বর',
		12 => 'ডিসেম্বর',
	);
}

/**
 * Format a post date as "১৫ অক্টো ২০২৬" or "Oct 15, 2026".
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rs_bn_date( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	if ( rs_is_en() ) {
		return get_the_time( 'M j, Y', $post );
	}

	$months = rs_bn_months();
	$day    = (int) get_post_time( 'j', false, $post );
	$month  = (int) get_post_time( 'n', false, $post );
	$year   = (int) get_post_time( 'Y', false, $post );

	return rs_bn_digits( $day ) . ' ' . $months[ $month ] . ' ' . rs_bn_digits( $year );
}

/**
 * The post's first category term. Null when it has none.
 *
 * @param int|WP_Post|null $post Post.
 * @return WP_Term|null
 */
function rs_primary_category( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return null;
	}

	$terms = get_the_category( $post->ID );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return null;
	}

	return $terms[0];
}

/**
 * Name of the post's first category. Empty when it has none.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rs_category( $post = null ) {
	$term = rs_primary_category( $post );

	return $term ? $term->name : '';
}

/**
 * Archive URL for the post's first category. Empty when it has none.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rs_category_link( $post = null ) {
	$term = rs_primary_category( $post );

	if ( ! $term ) {
		return '';
	}

	$link = get_category_link( $term->term_id );

	return is_wp_error( $link ) ? '' : $link;
}

/* =========================================================================
 * 4. Text helpers
 * ====================================================================== */

/**
 * Plain text version of a post body, with blocks and shortcodes removed.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function rs_plain_text( $post ) {
	/*
	 * Memoised per request. Every list row asks for this twice — once via
	 * rs_summary() for the hover text, once via rs_reading_time() — and the
	 * five string passes below are not cheap over a whole post body.
	 */
	static $cache = array();

	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$raw = $post->post_content;

	if ( function_exists( 'excerpt_remove_blocks' ) ) {
		$raw = excerpt_remove_blocks( $raw );
	}

	$raw = strip_shortcodes( $raw );
	$raw = wp_strip_all_tags( $raw );
	$raw = html_entity_decode( $raw, ENT_QUOTES, 'UTF-8' );
	$raw = preg_replace( '/\s+/u', ' ', $raw );

	$cache[ $post->ID ] = trim( (string) $raw );

	return $cache[ $post->ID ];
}

/**
 * Rough reading time, in Bengali digits, e.g. "৭ মিনিট".
 *
 * 180 words a minute rather than the usual English 200-250: Bengali is
 * denser per word and its conjuncts slow the eye down.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rs_reading_time( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	/*
	 * Kept in post meta after the first time it is worked out. Counting
	 * words means walking the whole body, and a list page asks for this
	 * once per row: at ten rows it is unnoticeable, at six hundred posts
	 * and a longer page it would not be. rs_clear_cached_text() throws
	 * the answer away whenever the post is saved.
	 */
	$minutes = get_post_meta( $post->ID, RS_MINUTES_KEY, true );

	if ( '' === $minutes ) {
		$words   = preg_match_all( '/\S+/u', rs_plain_text( $post ) );
		$minutes = max( 1, (int) round( $words / 180 ) );

		update_post_meta( $post->ID, RS_MINUTES_KEY, $minutes );
	}

	if ( rs_is_en() ) {
		return (string) (int) $minutes . ' min read';
	}

	return rs_bn_digits( (int) $minutes ) . ' মিনিট';
}

/**
 * A post's title as plain text.
 *
 * get_the_title() returns HTML. wptexturize has been over it, so a
 * hyphen is now "&#8211;" and a quote is "&#8216;" — correct in a
 * template, where the browser turns them back into characters, and wrong
 * everywhere else. JSON is everywhere else: JavaScript puts these titles
 * into textContent and escapes them before writing markup, and in both
 * places "&#8216;" is eight characters rather than a quotation mark.
 *
 * Decoding here rather than in JavaScript keeps it in one place, and the
 * decoded text is still escaped at every point it reaches the page.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rs_plain_title( $post = null ) {
	return html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
}

/**
 * Cut a string to a character limit, with an ellipsis when it was cut.
 *
 * Characters rather than bytes, and characters rather than words: Bengali
 * conjuncts are several bytes each, and the meta tags this feeds have
 * character limits of their own.
 *
 * @param string $text   Text to shorten.
 * @param int    $length Character limit.
 * @return string
 */
function rs_shorten( $text, $length ) {
	$text = trim( (string) $text );

	if ( mb_strlen( $text, 'UTF-8' ) <= $length ) {
		return $text;
	}

	return rtrim( mb_substr( $text, 0, $length, 'UTF-8' ) ) . '…';
}

/**
 * Short plain text summary, used for the hover tooltip and meta description.
 *
 * @param int|WP_Post|null $post   Post.
 * @param int              $length Character limit.
 * @return string
 */
function rs_summary( $post = null, $length = 200 ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	/* Cached at its longest, then cut down to whatever this caller wants.
	   Every caller asks for 200 or fewer; anything longer would silently
	   get the 200 character version. */
	$text = get_post_meta( $post->ID, RS_SUMMARY_KEY, true );

	if ( '' === $text ) {
		/* Decoded on the excerpt branch for the same reason as the title:
		   this ends up in the hover summary, which is set as text. The
		   other branch comes back decoded already. */
		$text = has_excerpt( $post )
			? trim( html_entity_decode( wp_strip_all_tags( $post->post_excerpt ), ENT_QUOTES, 'UTF-8' ) )
			: rs_plain_text( $post );

		$text = rs_shorten( $text, 200 );

		update_post_meta( $post->ID, RS_SUMMARY_KEY, $text );
	}

	return rs_shorten( $text, $length );
}

/**
 * Forget the cached reading time and summary when a post changes.
 *
 * Thrown away rather than recalculated here: save_post fires for autosaves,
 * revisions and quick edits too, and the next reader will pay for it once.
 *
 * @param int $post_id Post ID.
 */
function rs_clear_cached_text( $post_id ) {
	delete_post_meta( $post_id, RS_MINUTES_KEY );
	delete_post_meta( $post_id, RS_SUMMARY_KEY );
}
add_action( 'save_post', 'rs_clear_cached_text' );

/**
 * Case insensitive position of a needle, in characters.
 *
 * WordPress ships compat shims for mb_substr and mb_strlen but not for
 * mb_stripos, so this falls back by hand on hosts without mbstring.
 *
 * @param string $haystack Text to search.
 * @param string $needle   Term to find.
 * @return int|false
 */
function rs_stripos( $haystack, $needle ) {
	if ( function_exists( 'mb_stripos' ) ) {
		return mb_stripos( $haystack, $needle, 0, 'UTF-8' );
	}

	$pos = stripos( $haystack, $needle );

	if ( false === $pos ) {
		return false;
	}

	return mb_strlen( substr( $haystack, 0, $pos ), 'UTF-8' );
}

/**
 * Text around the first match of a search term.
 *
 * @param WP_Post $post   Post object.
 * @param string  $term   Search term.
 * @param int     $before Characters kept before the match.
 * @param int     $after  Characters kept after the match.
 * @return string
 */
function rs_match_snippet( $post, $term, $before = 30, $after = 40 ) {
	$text = rs_plain_text( $post );
	$term = trim( $term );

	if ( '' === $term || '' === $text ) {
		return '';
	}

	$pos = rs_stripos( $text, $term );

	if ( false === $pos ) {
		return rs_summary( $post, $before + $after );
	}

	$start   = max( 0, $pos - $before );
	$length  = mb_strlen( $term, 'UTF-8' ) + $before + $after;
	$snippet = trim( mb_substr( $text, $start, $length, 'UTF-8' ) );

	$prefix = $start > 0 ? '…' : '';
	$suffix = ( $start + $length ) < mb_strlen( $text, 'UTF-8' ) ? '…' : '';

	return $prefix . $snippet . $suffix;
}

/**
 * Number of published posts.
 *
 * @return int
 */
function rs_published_count() {
	$counts = wp_count_posts( 'post' );

	return isset( $counts->publish ) ? (int) $counts->publish : 0;
}

/* =========================================================================
 * 5. Theme settings
 * ====================================================================== */

/**
 * Option defaults.
 *
 * @return array
 */
function rs_defaults() {
	return array(
		'rs_email'    => 'lettertosohan@gmail.com',
		'rs_facebook' => 'https://www.facebook.com/lettertosohan/',
		'rs_linkedin' => 'https://www.linkedin.com/in/raisulsohan/',
		'rs_phrases'  => 'অক্ষরের আশ্রয়, এখানে গল্প থাকে',
		'rs_brand'    => '',
		'rs_footer'   => '© {year} রাইসুল সোহানের গল্প · সর্বস্বত্ব সংরক্ষিত',
		'rs_about'    => 0,
		'rs_og_image' => 0,
		'rs_hero_image' => 0,
		'rs_hero_pos'   => '50% 50%',
		'rs_home_per_page'    => RS_PER_PAGE,
		'rs_archive_per_page' => RS_PER_PAGE,
		'rs_verify'   => 'UGbwgVSquWFpv2qZcQYRQzJSyEFaryG9PHAIpY2ZsYA',
		'rs_featured_block_offset' => 0,
		'rs_featured_bottom_gap'   => 0,
		'rs_featured_summary_length' => 250,
	);
}

/**
 * Read a theme option with its default.
 *
 * @param string $key Option key.
 * @return mixed
 */
function rs_option( $key ) {
	$defaults = rs_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	return get_theme_mod( $key, $default );
}

/**
 * Typewriter phrases as an array.
 *
 * @return array
 */
function rs_phrases() {
	$raw   = (string) rs_option( 'rs_phrases' );
	$parts = array_filter( array_map( 'trim', explode( ',', $raw ) ) );

	return array_values( $parts ? $parts : array( get_bloginfo( 'name' ) ) );
}

/**
 * The name in the middle of the header.
 *
 * Its own setting rather than the Site Title, because the two are read in
 * different places. The Site Title goes into the browser tab, the feed,
 * og:site_name and every mail WordPress sends; the header is a line of
 * type on the page. Empty here means the two agree, which is the sane
 * default and what happens until someone decides otherwise.
 *
 * @return string
 */
function rs_brand() {
	$brand = trim( (string) rs_option( 'rs_brand' ) );

	return '' !== $brand ? $brand : get_bloginfo( 'name' );
}

/**
 * The banner standing in for the heading text, or 0 for none.
 *
 * Checked rather than trusted: a setting can outlive the picture it names
 * if the file is deleted from the media library, and an empty <img> at
 * the top of every list is worse than the text it replaced.
 *
 * @return int
 */
function rs_hero_image() {
	$id = (int) rs_option( 'rs_hero_image' );

	if ( $id && wp_get_attachment_image_src( $id, 'large' ) ) {
		return $id;
	}

	if ( is_multisite() && ! is_main_site() ) {
		switch_to_blog( get_main_site_id() );
		$main_id = (int) rs_option( 'rs_hero_image' );
		$valid   = $main_id && wp_get_attachment_image_src( $main_id, 'large' );
		restore_current_blog();
		if ( $valid ) {
			return $main_id;
		}
	}

	return 0;
}

/**
 * Render hero image HTML, with automatic multisite fallback to main site if subsite has none set.
 *
 * @param string $alt Alt text.
 * @param string $sizes Sizes attribute.
 * @param string $class Class attribute.
 * @return string Image HTML or empty string.
 */
function rs_render_hero_image_html( $alt = '', $sizes = '(max-width: 48rem) 100vw, 720px', $class = 'rs-hero__image' ) {
	$id          = (int) rs_option( 'rs_hero_image' );
	$pos         = rs_option( 'rs_hero_pos' );
	$target_blog = 0;

	if ( $id && wp_get_attachment_image_src( $id, 'large' ) ) {
		$target_blog = get_current_blog_id();
	} elseif ( is_multisite() && ! is_main_site() ) {
		$main_site_id = get_main_site_id();
		switch_to_blog( $main_site_id );
		$main_id = (int) rs_option( 'rs_hero_image' );
		if ( $main_id && wp_get_attachment_image_src( $main_id, 'large' ) ) {
			$id          = $main_id;
			$pos         = rs_option( 'rs_hero_pos' );
			$target_blog = $main_site_id;
		}
		restore_current_blog();
	}

	if ( ! $target_blog || ! $id ) {
		return '';
	}

	$is_switched = false;
	if ( $target_blog !== get_current_blog_id() ) {
		switch_to_blog( $target_blog );
		$is_switched = true;
	}

	$html = wp_get_attachment_image(
		$id,
		'large',
		false,
		array(
			'class'         => $class,
			'alt'           => $alt,
			'sizes'         => $sizes,
			'fetchpriority' => 'high',
			'style'         => 'object-position: ' . esc_attr( $pos ? $pos : '50% 50%' ) . ';',
		)
	);

	if ( $is_switched ) {
		restore_current_blog();
	}

	return $html;
}

/**
 * Footer line with the year substituted in Bengali digits.
 *
 * @return string
 */
function rs_footer_text() {
	$text = (string) rs_option( 'rs_footer' );
	if ( '' === $text ) {
		$text = rs_is_en() ? '© {year} Raisul Sohan. All rights reserved.' : '© {year} রইসুল সোহান';
	}
	$year = rs_is_en() ? gmdate( 'Y' ) : rs_bn_digits( gmdate( 'Y' ) );
	return str_replace( '{year}', $year, $text );
}

/**
 * About modal content. Falls back to a built in bio when no page is chosen.
 *
 * @return array
 */
function rs_about() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$page_id = (int) rs_option( 'rs_about' );

	if ( $page_id ) {
		$page = get_post( $page_id );

		if ( $page && 'publish' === $page->post_status ) {
			$cached = array(
				'title'   => get_the_title( $page ),
				'content' => apply_filters( 'the_content', $page->post_content ),
			);

			return $cached;
		}
	}

	/* Auto-detect page by slug */
	if ( rs_is_en() ) {
		$page = get_page_by_path( 'myself' );
		if ( ! $page ) {
			$page = get_page_by_path( 'about' );
		}
		if ( ! $page ) {
			$page = get_page_by_path( 'about-me' );
		}
		if ( $page && 'publish' === $page->post_status ) {
			$cached = array(
				'title'   => get_the_title( $page ),
				'content' => apply_filters( 'the_content', $page->post_content ),
			);

			return $cached;
		}
	} else {
		$page = get_page_by_path( 'about' );
		if ( ! $page ) {
			$page = get_page_by_path( 'amar-shomporke' );
		}
		if ( $page && 'publish' === $page->post_status ) {
			$cached = array(
				'title'   => get_the_title( $page ),
				'content' => apply_filters( 'the_content', $page->post_content ),
			);

			return $cached;
		}
	}

	$cached = rs_is_en() ? array(
		'title'   => 'Myself',
		'content' => '<p>I am Raisul Sohan. My primary literary focus is storytelling and essays. My writings explore the quiet transformations of contemporary life, memory, and human connections. While I work in motion graphics and animation, both are ultimately different ways of telling stories.</p>',
	) : array(
		'title'   => 'আমি',
		'content' => '<p>আমি রাইসুল সোহান। আমার সাহিত্যচর্চার মূল মাধ্যম গল্প। সমকালীন মানুষের জীবন, সম্পর্ক, স্মৃতি ও শহরের নীরব রূপান্তর আমার লেখার আগ্রহের জায়গা। জীবিকার জন্য মোশন গ্রাফিক্স ও অ্যানিমেশন নিয়ে কাজ করলেও, আমার কাছে দুটি মাধ্যমই শেষ পর্যন্ত গল্প বলার ভিন্ন ভিন্ন উপায়।</p>',
	);

	return $cached;
}

/**
 * Where the index lives, if it has been made.
 *
 * Found by looking for the page that uses the Index template rather than
 * by asking for it in the settings. There is nothing to choose here — the
 * template can only sensibly be on one page, and a setting would be a
 * second place to keep the same fact in step with the first.
 *
 * Empty until such a page exists, which is what lets the count on the
 * front page stay ordinary text until there is somewhere for it to go.
 *
 * @return string Permalink, or '' when there is no index page.
 */
function rs_index_url() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$cached = '';

	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One row, once per request, and only on the front page.
	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'meta_key'         => '_wp_page_template',
			'meta_value'       => 'page-index.php',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => false,
		)
	);

	if ( $pages ) {
		$cached = get_permalink( $pages[0] );
	} elseif ( rs_is_en() ) {
		// On English subsite, ensure All Writings page exists with template page-index.php.
		$page = get_page_by_path( 'all-writings' );
		if ( ! $page ) {
			$page = get_page_by_path( 'all-write-up' );
		}

		if ( $page ) {
			update_post_meta( $page->ID, '_wp_page_template', 'page-index.php' );
			$cached = get_permalink( $page->ID );
		} else {
			$page_id = wp_insert_post(
				array(
					'post_title'   => 'All Writings',
					'post_name'    => 'all-writings',
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '',
				)
			);
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_wp_page_template', 'page-index.php' );
				$cached = get_permalink( $page_id );
			}
		}
	}

	return $cached;
}

/**
 * Keep a page length usable.
 *
 * An empty or zero field would otherwise mean a list with nothing in it,
 * and a very large one would put every summary and reading time on a
 * single request. Both ends are held rather than trusted.
 *
 * @param mixed $value Submitted value.
 * @return int
 */
function rs_sanitize_per_page( $value ) {
	$value = absint( $value );

	if ( $value < 1 ) {
		return RS_PER_PAGE;
	}

	return min( $value, 100 );
}

/**
 * The plain text settings, and how each one is handled.
 *
 * key => array( label, input type, sanitiser, note under the field )
 *
 * The page picker and the image picker are not in here: neither is a text
 * box, and both are written out by hand in rs_settings_page().
 *
 * These labels are English while the site itself is Bengali, on purpose.
 * They are read alongside WordPress's own labels, which follow whichever
 * language the user chose for the admin, and a screen that is half one
 * language and half the other is harder to read than either.
 *
 * @return array
 */
function rs_settings_fields() {
	return array(
		'rs_phrases'  => array(
			__( 'Heading text', 'raisul-sohan' ),
			'text',
			'sanitize_text_field',
			__( 'Separate several with commas and they take turns. A single one stays put.', 'raisul-sohan' ),
		),
		'rs_home_per_page'    => array(
			__( 'Posts per page, front page', 'raisul-sohan' ),
			'number',
			'rs_sanitize_per_page',
			__( 'How many rows the front page shows before the page links.', 'raisul-sohan' ),
		),
		'rs_archive_per_page' => array(
			__( 'Posts per page, archives', 'raisul-sohan' ),
			'number',
			'rs_sanitize_per_page',
			__( 'Categories, tags and search results.', 'raisul-sohan' ),
		),
		'rs_brand'    => array(
			__( 'Header title', 'raisul-sohan' ),
			'text',
			'sanitize_text_field',
			__( 'The name shown in the middle of the header. Empty uses the Site Title from Settings → General, which is also what the browser tab and shared links use.', 'raisul-sohan' ),
		),
		'rs_email'    => array(
			__( 'Email', 'raisul-sohan' ),
			'text',
			'sanitize_email',
			__( 'Copied when a reader clicks the mail icon in the header.', 'raisul-sohan' ),
		),
		'rs_facebook' => array(
			__( 'Facebook URL', 'raisul-sohan' ),
			'url',
			'esc_url_raw',
			__( 'Leave empty to hide the icon.', 'raisul-sohan' ),
		),
		'rs_linkedin' => array(
			__( 'LinkedIn URL', 'raisul-sohan' ),
			'url',
			'esc_url_raw',
			__( 'Leave empty to hide the icon.', 'raisul-sohan' ),
		),
		'rs_footer'   => array(
			__( 'Footer text', 'raisul-sohan' ),
			'text',
			'sanitize_text_field',
			__( 'Write {year} and the current year appears there, in Bengali digits.', 'raisul-sohan' ),
		),
		'rs_verify'   => array(
			__( 'Google verification code', 'raisul-sohan' ),
			'text',
			'sanitize_text_field',
			__( 'From Search Console: the content value of the HTML tag on its own. Empty means no tag.', 'raisul-sohan' ),
		),
		'rs_featured_block_offset' => array(
			__( 'Featured block vertical offset (px)', 'raisul-sohan' ),
			'number',
			'intval',
			__( 'Adjust the vertical position of the entire featured section. Can be negative, e.g., -5, 10.', 'raisul-sohan' ),
		),
		'rs_featured_bottom_gap' => array(
			__( 'Featured bottom gap (px)', 'raisul-sohan' ),
			'number',
			'intval',
			__( 'Adjust the gap below the featured post. Can be negative to bring the list closer.', 'raisul-sohan' ),
		),
		/*
		 * rs_featured_summary_length is deliberately absent. It lives in the
		 * Customizer, where a change can be seen against the actual post
		 * while it is being made, and a setting belongs on one screen only.
		 *
		 * Two screens over one theme_mod is not a duplicate label, it is a
		 * way to lose a value: rs_settings_save() writes every field in this
		 * array on every save, so pressing Save here would have overwritten
		 * whatever the Customizer had with whatever this form happened to be
		 * holding — without anyone touching that field.
		 */
	);
}

/**
 * Keep an object-position to the pair of percentages it should be.
 *
 * Only ever written by dragging the box on the settings screen, but it
 * arrives as a string in a form post like anything else, and it is put
 * straight into a style attribute at the other end.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function rs_sanitize_position( $value ) {
	if ( ! preg_match( '/^(\d{1,3}(?:\.\d)?)% (\d{1,3}(?:\.\d)?)%$/', trim( (string) $value ), $found ) ) {
		return '50% 50%';
	}

	$x = min( 100, max( 0, (float) $found[1] ) );
	$y = min( 100, max( 0, (float) $found[2] ) );

	return $x . '% ' . $y . '%';
}

/**
 * The settings that hold a picture, and what each one is for.
 *
 * key => array( label, note under the field )
 *
 * Separate from rs_settings_fields() because these are not text boxes:
 * they store an attachment ID and are worked with through the media
 * library rather than typed.
 *
 * @return array
 */
function rs_settings_images() {
	return array(
		'rs_hero_image' => array(
			'label'  => __( 'Heading image', 'raisul-sohan' ),
			'note'   => __( 'Stands in place of the heading text at the top of the list. Any picture will do — it is cropped to a 1600 by 300 band, so a tall or square one loses its edges rather than stretching. Drag it inside the box to choose which part of it survives.', 'raisul-sohan' ),
			/* A ratio turns the preview into a box of that shape that can
			   be dragged, and names the setting the drag writes to. */
			'ratio'  => RS_HERO_RATIO,
			'anchor' => 'rs_hero_pos',
		),
		'rs_og_image'   => array(
			'label' => __( 'Share image', 'raisul-sohan' ),
			'note'  => __( 'Used on the share card of posts that have no picture of their own. Square, 600x600 or larger — the card crops a wide image to its middle. A picture inside a post wins over this one.', 'raisul-sohan' ),
		),
	);
}

/**
 * Put the settings on their own screen, under Appearance.
 */
function rs_settings_menu() {
	add_theme_page(
		__( 'Theme Settings', 'raisul-sohan' ),
		__( 'Theme Settings', 'raisul-sohan' ),
		'edit_theme_options',
		'rs-settings',
		'rs_settings_page'
	);
}
add_action( 'admin_menu', 'rs_settings_menu' );

/**
 * The settings screen.
 *
 * A full width form rather than the customizer's sidebar. Nothing here
 * benefits from a live preview — every one of these settings redrew the
 * whole page anyway — and several of them are long enough that a column
 * two hundred pixels wide was the wrong shape for reading them.
 */
function rs_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Theme Settings', 'raisul-sohan' ); ?></h1>

		<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading a flag off our own redirect, acting on nothing. ?>
		<?php if ( isset( $_GET['updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved.', 'raisul-sohan' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="rs_save_settings">
			<?php wp_nonce_field( 'rs_save_settings' ); ?>

			<table class="form-table" role="presentation">
				<?php foreach ( rs_settings_fields() as $rs_key => $rs_field ) : ?>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $rs_key ); ?>"><?php echo esc_html( $rs_field[0] ); ?></label>
						</th>
						<td>
							<?php $rs_number = 'number' === $rs_field[1]; ?>
							<input type="<?php echo esc_attr( $rs_field[1] ); ?>"
								id="<?php echo esc_attr( $rs_key ); ?>"
								name="<?php echo esc_attr( $rs_key ); ?>"
								value="<?php echo esc_attr( rs_option( $rs_key ) ); ?>"
								class="<?php echo $rs_number ? 'small-text' : 'regular-text'; ?>"
								<?php
								/* The same range rs_sanitize_per_page() enforces,
								   so the browser objects before the save does. */
								if ( $rs_number ) :
									?>
									min="1" max="100" step="1"
								<?php endif; ?>>
							<p class="description"><?php echo esc_html( $rs_field[3] ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>

				<tr>
					<th scope="row">
						<label for="rs_about"><?php esc_html_e( 'About page', 'raisul-sohan' ); ?></label>
					</th>
					<td>
						<?php
						wp_dropdown_pages(
							array(
								'name'              => 'rs_about',
								'id'                => 'rs_about',
								'selected'          => (int) rs_option( 'rs_about' ),
								'show_option_none'  => __( '— Use the built in bio —', 'raisul-sohan' ),
								'option_none_value' => 0,
							)
						);
						?>
						<p class="description">
							<?php esc_html_e( 'That page\'s content is what the About modal shows.', 'raisul-sohan' ); ?>
						</p>
					</td>
				</tr>

				<?php foreach ( rs_settings_images() as $rs_key => $rs_image ) : ?>
					<?php
					$rs_id     = (int) rs_option( $rs_key );
					$rs_ratio  = isset( $rs_image['ratio'] ) ? $rs_image['ratio'] : '';
					$rs_anchor = isset( $rs_image['anchor'] ) ? $rs_image['anchor'] : '';
					/* A crop box wants a picture big enough to move around
					   inside it; a plain thumbnail does not. */
					$rs_src = $rs_id ? wp_get_attachment_image_src( $rs_id, $rs_ratio ? 'large' : 'medium' ) : false;
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $rs_image['label'] ); ?></th>
						<td>
							<?php if ( $rs_ratio ) : ?>
								<div class="rs-crop"
									id="<?php echo esc_attr( $rs_key ); ?>-preview"
									data-rs-anchor="<?php echo esc_attr( $rs_anchor ); ?>"
									style="aspect-ratio: <?php echo esc_attr( $rs_ratio ); ?>;">
									<?php if ( $rs_src ) : ?>
										<img src="<?php echo esc_url( $rs_src[0] ); ?>" alt=""
											style="object-position: <?php echo esc_attr( rs_option( $rs_anchor ) ); ?>;">
									<?php endif; ?>
								</div>
								<p class="description rs-crop__hint">
									<?php esc_html_e( 'Drag the picture to choose what shows.', 'raisul-sohan' ); ?>
								</p>
								<input type="hidden"
									name="<?php echo esc_attr( $rs_anchor ); ?>"
									id="<?php echo esc_attr( $rs_anchor ); ?>"
									value="<?php echo esc_attr( rs_option( $rs_anchor ) ); ?>">
							<?php else : ?>
								<div class="rs-image-preview" id="<?php echo esc_attr( $rs_key ); ?>-preview" style="margin-bottom:.75rem;">
									<?php if ( $rs_src ) : ?>
										<img src="<?php echo esc_url( $rs_src[0] ); ?>" alt="" style="max-width:200px;height:auto;">
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php /* The attachment ID rather than a URL, which would go stale the day the media library moves. */ ?>
							<input type="hidden"
								name="<?php echo esc_attr( $rs_key ); ?>"
								id="<?php echo esc_attr( $rs_key ); ?>"
								value="<?php echo esc_attr( $rs_id ); ?>">

							<button type="button" class="button" data-rs-pick="<?php echo esc_attr( $rs_key ); ?>">
								<?php esc_html_e( 'Choose image', 'raisul-sohan' ); ?>
							</button>
							<button type="button" class="button" data-rs-clear="<?php echo esc_attr( $rs_key ); ?>">
								<?php esc_html_e( 'Remove', 'raisul-sohan' ); ?>
							</button>

							<p class="description"><?php echo esc_html( $rs_image['note'] ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Save the settings.
 *
 * They stay theme mods rather than becoming options of their own, so
 * rs_option() and everything that reads through it is untouched by the
 * move off the customizer.
 */
function rs_settings_save() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to change these settings.', 'raisul-sohan' ) );
	}

	check_admin_referer( 'rs_save_settings' );

	foreach ( rs_settings_fields() as $key => $field ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised by the field's own callback on the next line.
		$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

		set_theme_mod( $key, call_user_func( $field[2], $value ) );
	}

	set_theme_mod( 'rs_about', isset( $_POST['rs_about'] ) ? absint( $_POST['rs_about'] ) : 0 );

	foreach ( rs_settings_images() as $key => $image ) {
		set_theme_mod( $key, isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : 0 );

		if ( empty( $image['anchor'] ) ) {
			continue;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised on the next line.
		$anchor = isset( $_POST[ $image['anchor'] ] ) ? wp_unslash( $_POST[ $image['anchor'] ] ) : '';

		set_theme_mod( $image['anchor'], rs_sanitize_position( $anchor ) );
	}

	wp_safe_redirect( admin_url( 'themes.php?page=rs-settings&updated=1' ) );
	exit;
}
add_action( 'admin_post_rs_save_settings', 'rs_settings_save' );

/**
 * The media library picker, loaded on this screen and nowhere else.
 *
 * @param string $hook Current admin page.
 */
function rs_settings_assets( $hook ) {
	if ( 'appearance_page_rs-settings' !== $hook ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'jquery' );

	wp_add_inline_style(
		'common',
		'.rs-crop {
	position: relative;
	width: 100%;
	max-width: 560px;
	overflow: hidden;
	background: #f0f0f1;
	border: 1px solid #c3c4c7;
	border-radius: 2px;
}

.rs-crop img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
	cursor: grab;
	user-select: none;
	-webkit-user-drag: none;
	touch-action: none;
}

.rs-crop img:active {
	cursor: grabbing;
}

.rs-crop__hint {
	margin-top: .35rem;
	margin-bottom: .75rem;
}'
	);

	/* One handler for however many picture settings there are: the button
	   names the field it belongs to, and the field's own id is what the
	   hidden input and the preview are built from. */
	wp_add_inline_script(
		'jquery-core',
		"jQuery( function ( $ ) {
	var frames = {};

	$( document ).on( 'click', '[data-rs-pick]', function () {
		var key = $( this ).attr( 'data-rs-pick' );

		if ( ! frames[ key ] ) {
			frames[ key ] = wp.media( {
				title: " . wp_json_encode( __( 'Choose image', 'raisul-sohan' ) ) . ",
				library: { type: 'image' },
				multiple: false
			} );

			frames[ key ].on( 'select', function () {
				var img = frames[ key ].state().get( 'selection' ).first().toJSON();
				var box = $( '#' + key + '-preview' );

				$( '#' + key ).val( img.id );

				if ( box.hasClass( 'rs-crop' ) ) {
					/* A crop box wants room to move the picture about, so
					   it takes the large size rather than the thumbnail. */
					var big = img.sizes && img.sizes.large ? img.sizes.large.url : img.url;

					box.html( $( '<img>' ).attr( 'src', big ).css( { objectPosition: '50% 50%' } ) );
					$( '#' + box.attr( 'data-rs-anchor' ) ).val( '50% 50%' );

					return;
				}

				var src = img.sizes && img.sizes.medium ? img.sizes.medium.url : img.url;

				box.html( $( '<img>' ).attr( 'src', src ).css( { maxWidth: '200px', height: 'auto' } ) );
			} );
		}

		frames[ key ].open();
	} );

	$( document ).on( 'click', '[data-rs-clear]', function () {
		var key = $( this ).attr( 'data-rs-clear' );

		$( '#' + key ).val( '' );
		$( '#' + key + '-preview' ).empty();
	} );

	/*
	 * Dragging inside a crop box moves the picture behind it.
	 *
	 * object-position's percentages run across whatever the crop hides
	 * rather than across the picture, so a pixel of drag is only worth a
	 * pixel on screen once it is divided by that hidden amount — which is
	 * why the overflow is worked out here rather than guessed at.
	 */
	$( document ).on( 'pointerdown', '.rs-crop img', function ( event ) {
		var img = this;
		var box = img.parentNode;
		var field = $( box ).attr( 'data-rs-anchor' );

		if ( ! field || ! img.naturalWidth ) {
			return;
		}

		var rect = box.getBoundingClientRect();
		var scale = Math.max( rect.width / img.naturalWidth, rect.height / img.naturalHeight );
		var roomX = img.naturalWidth * scale - rect.width;
		var roomY = img.naturalHeight * scale - rect.height;
		var start = $( '#' + field ).val().split( ' ' );
		var fromX = parseFloat( start[ 0 ] );
		var fromY = parseFloat( start[ 1 ] );
		var atX = event.clientX;
		var atY = event.clientY;

		if ( isNaN( fromX ) ) {
			fromX = 50;
		}

		if ( isNaN( fromY ) ) {
			fromY = 50;
		}

		event.preventDefault();
		img.setPointerCapture( event.pointerId );

		function hold( value ) {
			return Math.min( 100, Math.max( 0, value ) ).toFixed( 1 );
		}

		function move( e ) {
			/* Minus, so the picture follows the pointer: dragging down
			   should bring what is above into view. */
			var x = roomX > 0 ? hold( fromX - ( e.clientX - atX ) / roomX * 100 ) : hold( fromX );
			var y = roomY > 0 ? hold( fromY - ( e.clientY - atY ) / roomY * 100 ) : hold( fromY );
			var to = x + '% ' + y + '%';

			img.style.objectPosition = to;
			$( '#' + field ).val( to );
		}

		function drop() {
			img.removeEventListener( 'pointermove', move );
			img.removeEventListener( 'pointerup', drop );
			img.removeEventListener( 'pointercancel', drop );
		}

		img.addEventListener( 'pointermove', move );
		img.addEventListener( 'pointerup', drop );
		img.addEventListener( 'pointercancel', drop );
	} );
} );"
	);
}
add_action( 'admin_enqueue_scripts', 'rs_settings_assets' );

/* =========================================================================
 * 6. Icons
 * ====================================================================== */

/**
 * Inline SVG icon.
 *
 * @param string $name Icon name.
 * @param int    $size Pixel size.
 * @return string
 */
function rs_icon( $name, $size = 15 ) {
	$stroke = array(
		'mail'     => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'linkedin' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
		'search'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'close'    => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'up'       => '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
		'copy'     => '<rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
		/* Lines drawn as paths: rs_svg_tags() allows path and circle, and
		   two more tags for one icon is not worth widening it. */
		'share'    => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/>',
		'left'     => '<path d="m15 18-6-6 6-6"/>',
		'right'    => '<path d="m9 18 6-6-6-6"/>',
		'undo'     => '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>',
		'edit'     => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
		/* One outline for both states. Saved fills it in from CSS, which is
		   a colour change rather than a different shape — so the mark does
		   not appear to move when the reader taps it. */
		'bookmark' => '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
		'sparkles' => '<path d=\"m12 3-1.9 5.8a2 2 0 0 1-1.2 1.2L3 12l5.8 1.9a2 2 0 0 1 1.2 1.2L12 21l1.9-5.8a2 2 0 0 1 1.2-1.2L21 12l-5.8-1.9a2 2 0 0 1-1.2-1.2Z\"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
		'moon'     => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
		'install'  => '<rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/><path d="m9 10 3 3 3-3"/><path d="M12 6v7"/>',
	);

	$fill = array(
		'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
	);

	if ( isset( $stroke[ $name ] ) ) {
		return sprintf(
			'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
			(int) $size,
			$stroke[ $name ]
		);
	}

	if ( isset( $fill[ $name ] ) ) {
		return sprintf(
			'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%2$s</svg>',
			(int) $size,
			$fill[ $name ]
		);
	}

	return '';
}

/**
 * Allow the inline SVG markup through wp_kses.
 *
 * @return array
 */
function rs_svg_tags() {
	$attrs = array(
		'width'            => true,
		'height'           => true,
		'viewbox'          => true,
		'fill'             => true,
		'stroke'           => true,
		'stroke-width'     => true,
		'stroke-linecap'   => true,
		'stroke-linejoin'  => true,
		'aria-hidden'      => true,
		'focusable'        => true,
		'class'            => true,
	);

	return array(
		'svg'    => $attrs,
		'path'   => array_merge( $attrs, array( 'd' => true ) ),
		'rect'   => array_merge( $attrs, array( 'x' => true, 'y' => true, 'rx' => true ) ),
		'circle' => array_merge( $attrs, array( 'cx' => true, 'cy' => true, 'r' => true ) ),
	);
}

/* =========================================================================
 * 7. Share links
 * ====================================================================== */

/**
 * The two ways in to editing a post, for whoever may.
 *
 * Two plain links rather than a menu: there are only two of them, and a
 * menu would be a thing to open before you could choose. Prints nothing
 * for a reader, so the capability check is the whole gate.
 *
 * @param int|WP_Post|null $post Post.
 */
function rs_edit_links( $post = null ) {
	$post = get_post( $post );

	if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}
	?>
	<button class="rs-article__edit" type="button" data-rs-edit>
		<?php echo wp_kses( rs_icon( 'edit', 13 ), rs_svg_tags() ); ?>
		<?php echo esc_html( rs_is_en() ? 'Edit' : 'সম্পাদনা' ); ?>
	</button>
	<a class="rs-article__edit" href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>">
		<?php echo esc_html( rs_is_en() ? 'Dashboard' : 'ড্যাশবোর্ডে' ); ?>
	</a>
	<?php
}

/**
 * Render the share row for a post.
 *
 * @param int|WP_Post|null $post Post.
 */
function rs_share_row( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return;
	}

	$url = get_permalink( $post );
	?>
	<div class="rs-share">
		<p class="rs-share__label"><?php echo esc_html( rs_is_en() ? 'Share with others' : 'অন্যদেরও পড়তে দিন' ); ?></p>
		<div class="rs-share__row">
			<?php
			/* Hidden by CSS until app.js finds navigator.share and marks
			   the page. On a phone this opens whatever the reader already
			   sends things with, which is where these links actually go. */
			?>
			<button class="rs-share__btn" type="button"
				data-rs-share="<?php echo esc_attr( $url ); ?>"
				data-rs-share-title="<?php echo esc_attr( rs_plain_title( $post ) ); ?>">
				<?php echo wp_kses( rs_icon( 'share', 14 ), rs_svg_tags() ); ?>
				<?php echo esc_html( rs_is_en() ? 'Share' : 'শেয়ার করুন' ); ?>
			</button>

			<button class="rs-share__btn" type="button" data-rs-copy="<?php echo esc_attr( $url ); ?>">
				<?php echo wp_kses( rs_icon( 'copy', 14 ), rs_svg_tags() ); ?>
				<?php echo esc_html( rs_is_en() ? 'Copy link' : 'লিঙ্ক কপি' ); ?>
			</button>

			<?php
			/* Kept in step with shareHtml() in app.js, which draws this same
			   row inside the modal. Unlike a list row this one has nothing
			   around it to read the title from, so it is told outright. */
			?>
			<button class="rs-share__btn rs-share__btn--save" type="button"
				data-rs-later="<?php echo (int) $post->ID; ?>"
				data-rs-later-url="<?php echo esc_attr( $url ); ?>"
				data-rs-later-title="<?php echo esc_attr( rs_plain_title( $post ) ); ?>"
				data-rs-later-time="<?php echo esc_attr( rs_reading_time( $post ) ); ?>"
				aria-pressed="false">
				<?php echo wp_kses( rs_icon( 'bookmark', 14 ), rs_svg_tags() ); ?>
				<span data-rs-later-text><?php echo esc_html( rs_is_en() ? 'Read later' : 'পরে পড়ব' ); ?></span>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Break an address fragment into the words it is made of.
 *
 * Slugs on this site are romanised — "rituparno-ghosh-cinema" — while the
 * writing they name is Bengali, so searching the posts for those words finds
 * nothing. The slug is the only Latin thing about a post, which makes slug
 * against slug the comparison that can actually work. Percent-encoded
 * Bengali slugs decode back into Bengali words and compare the same way.
 *
 * Anything under three characters is dropped: "er" and "o" sit in half the
 * slugs on the site and would make every post look like a match.
 *
 * @param string $text A slug, or the tail of a requested path.
 * @return string[] Lowercased words, each one unique.
 */
function rs_slug_words( $text ) {
	$text  = strtolower( urldecode( (string) $text ) );
	$parts = preg_split( '/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY );

	if ( ! $parts ) {
		return array();
	}

	$words = array();

	foreach ( $parts as $part ) {
		if ( mb_strlen( $part, 'UTF-8' ) >= 3 ) {
			$words[] = $part;
		}
	}

	return array_values( array_unique( $words ) );
}

/**
 * Posts whose address resembles the one the reader asked for.
 *
 * A link that has gone stale is the commonest way to land on the missing
 * page, and the address itself says what was wanted. WordPress already
 * redirects when the request is the beginning of exactly one slug; this
 * covers what that cannot — a word changed in the middle, a word dropped
 * from the end, two posts it could equally have been. Those are offered
 * rather than redirected to, because a near miss guessed wrongly is worse
 * than a choice.
 *
 * Every published slug is read in one go and the comparison happens here.
 * At this size that is a single small query against two columns; a site of
 * several thousand posts would want to push the work into SQL instead.
 *
 * @param int $limit How many to offer.
 * @return WP_Post[] Best matches first, empty when nothing resembles it.
 */
function rs_missing_matches( $limit = 3 ) {
	global $wp, $wpdb;

	$request = isset( $wp->request ) ? $wp->request : '';

	if ( ! $request ) {
		return array();
	}

	/* The last segment names the thing; the ones before it are the shelf it
	   was expected to be on. A trailing "page" and a number are WordPress's
	   own punctuation and belong to no title, so they are walked back past
	   rather than mistaken for the name. */
	$parts = array_values( array_filter( explode( '/', $request ) ) );

	while ( count( $parts ) > 1 ) {
		$last = $parts[ count( $parts ) - 1 ];

		if ( ! is_numeric( $last ) && 'page' !== $last ) {
			break;
		}

		array_pop( $parts );
	}

	$words = rs_slug_words( end( $parts ) );

	if ( ! $words ) {
		return array();
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Two columns, on a page nobody meant to reach.
	$rows = $wpdb->get_results(
		"SELECT ID, post_name FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'"
	);

	if ( ! $rows ) {
		return array();
	}

	/*
	 * How ordinary each word is, counted from this site rather than from a
	 * list of English stopwords — because the slugs here are romanised
	 * Bengali as often as they are English, and no ready-made list knows
	 * what is unremarkable in either. "the" turns up in nearly a fifth of
	 * these slugs and so tells us nothing; "ghosh" turns up once and tells
	 * us everything.
	 *
	 * The floor keeps the rule from eating a small site alive: with a dozen
	 * posts a share of them is a very small number.
	 */
	$slugs  = array();
	$common = array();

	foreach ( $rows as $row ) {
		$slugs[ (int) $row->ID ] = rs_slug_words( $row->post_name );

		foreach ( $slugs[ (int) $row->ID ] as $word ) {
			$common[ $word ] = isset( $common[ $word ] ) ? $common[ $word ] + 1 : 1;
		}
	}

	$ceiling = max( 4, (int) ceil( count( $rows ) * 0.15 ) );
	$scored  = array();

	foreach ( $slugs as $id => $slug_words ) {
		$shared = array_intersect( $words, $slug_words );
		$strong = array();

		foreach ( $shared as $word ) {
			if ( $common[ $word ] < $ceiling ) {
				$strong[] = $word;
			}
		}

		/* Sharing nothing but a word half the site shares is not a match. */
		if ( ! $strong ) {
			continue;
		}

		$longest = 0;

		foreach ( $strong as $word ) {
			$longest = max( $longest, mb_strlen( $word, 'UTF-8' ) );
		}

		$scored[] = array(
			'id'      => $id,
			'shared'  => count( $strong ),
			'longest' => $longest,
		);
	}

	if ( ! $scored ) {
		return array();
	}

	/* Most words in common first. Where two share the same number, the one
	   sharing the longer word wins: length is the only measure of weight
	   available once the ordinary words are gone. */
	usort(
		$scored,
		function ( $a, $b ) {
			if ( $a['shared'] !== $b['shared'] ) {
				return $b['shared'] - $a['shared'];
			}

			return $b['longest'] - $a['longest'];
		}
	);

	/*
	 * Only the joint best are offered. Padding the answer out to three with
	 * whatever else brushed against a word turns a confident "this one" into
	 * a shrug — and the heading over these says the reader was looking for
	 * one of them.
	 */
	$best  = $scored[0]['shared'];
	$items = array();

	foreach ( $scored as $hit ) {
		if ( $hit['shared'] < $best || count( $items ) >= max( 1, (int) $limit ) ) {
			break;
		}

		$item = get_post( $hit['id'] );

		if ( $item ) {
			$items[] = $item;
		}
	}

	return $items;
}

/**
 * A handful of posts, shuffled.
 *
 * Shuffled rather than newest first, so the same few do not end up under
 * every story in a category and on every missing page. The filters keep
 * the set small enough that the sort costs nothing worth measuring.
 *
 * @param int $limit   How many.
 * @param int $exclude Post to leave out.
 * @param int $cat     Category to stay inside, 0 for anywhere.
 * @return WP_Post[]
 */
function rs_random_posts( $limit = 3, $exclude = 0, $cat = 0 ) {
	$args = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => (int) $limit,
		'orderby'                => 'rand',
		'has_password'           => false,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	if ( $exclude ) {
		$args['post__not_in'] = array( (int) $exclude );
	}

	if ( $cat ) {
		$args['cat'] = (int) $cat;
	}

	$query = new WP_Query( $args );

	return $query->posts;
}

/**
 * A few other posts from the same category.
 *
 * The next and previous links below an article are neighbours by date,
 * which is rarely what a reader who just finished a story wants next.
 * These are neighbours by subject instead.
 *
 * @param int|WP_Post|null $post  Post to find company for.
 * @param int              $limit How many.
 * @return WP_Post[]
 */
function rs_related( $post = null, $limit = 3 ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return array();
	}

	$term = rs_primary_category( $post );

	if ( ! $term ) {
		return array();
	}

	return rs_random_posts( $limit, $post->ID, $term->term_id );
}

/**
 * The category "any one of them" should stay inside, or 0 for anywhere.
 *
 * A reader who has narrowed the list down has said something about what
 * they want.
 *
 * @return int
 */
function rs_random_cat() {
	if ( ! is_category() ) {
		return 0;
	}

	$term = get_queried_object();

	return $term instanceof WP_Term ? (int) $term->term_id : 0;
}

/**
 * Address of the "any one of them" link.
 *
 * app.js catches the click and opens the story in the modal instead,
 * which is how every other story on the list opens. This address is what
 * happens without that: a redirect to the post's own page.
 *
 * @return string
 */
function rs_random_url() {
	$url = add_query_arg( 'rs_random', '1', home_url( '/' ) );
	$cat = rs_random_cat();

	return $cat ? add_query_arg( 'rs_cat', $cat, $url ) : $url;
}

/**
 * Send ?rs_random=1 off to a post picked at random.
 *
 * A plain link and a redirect rather than anything cleverer, so it works
 * with JavaScript off. nocache_headers() matters more than usual here:
 * a full page cache that kept the redirect would hand every reader the
 * same "random" post until it expired.
 */
function rs_random_redirect() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only, and public.
	if ( ! isset( $_GET['rs_random'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only, and public.
	$cat   = isset( $_GET['rs_cat'] ) ? absint( $_GET['rs_cat'] ) : 0;
	$items = rs_random_posts( 1, 0, $cat );

	nocache_headers();

	wp_safe_redirect( $items ? get_permalink( $items[0] ) : home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'rs_random_redirect' );

/**
 * Render a short list of posts being offered to the reader.
 *
 * Used under an article and on the missing page, which want the same
 * shape and differ only in what they call it.
 *
 * @param WP_Post[] $items Posts.
 * @param string    $label Heading above them.
 */
function rs_suggestions( $items, $label ) {
	if ( ! $items ) {
		return;
	}
	?>
	<nav class="rs-related" aria-label="<?php echo esc_attr( $label ); ?>">
		<p class="rs-related__label"><?php echo esc_html( $label ); ?></p>
		<ul class="rs-related__list">
			<?php foreach ( $items as $item ) : ?>
				<li>
					<?php /* data-rs-post lets the modal's own click handler catch these. */ ?>
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" data-rs-post="<?php echo (int) $item->ID; ?>">
						<span class="rs-related__title"><?php echo esc_html( get_the_title( $item ) ); ?></span>
						<span class="rs-related__meta"><?php echo esc_html( rs_reading_time( $item ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/**
 * Render the related posts block under an article.
 *
 * @param int|WP_Post|null $post Post.
 */
function rs_related_row( $post = null ) {
	$term  = rs_primary_category( $post );
	$label = rs_is_en()
		? ( 'More in ' . ( $term ? $term->name : 'writings' ) )
		: ( 'আরও ' . ( $term ? $term->name : 'লেখা' ) );

	rs_suggestions( rs_related( $post ), $label );
}

/**
 * Related posts trimmed down to what the modal needs.
 *
 * @param int|WP_Post|null $post Post.
 * @return array
 */
function rs_related_payload( $post = null ) {
	$items = array();

	foreach ( rs_related( $post ) as $item ) {
		$items[] = array(
			'id'          => $item->ID,
			'title'       => rs_plain_title( $item ),
			'link'        => get_permalink( $item ),
			'readingTime' => rs_reading_time( $item ),
		);
	}

	return $items;
}

/* =========================================================================
 * 8. The post list
 * ====================================================================== */

/**
 * URL of a page of the list currently being rendered.
 *
 * get_pagenum_link() builds on the request URI, so during a fragment
 * request every link it returns would carry rs_ajax=1 along and the reader
 * would end up with that in their address bar.
 *
 * @param int $page Page number.
 * @return string
 */
function rs_page_url( $page ) {
	return remove_query_arg( 'rs_ajax', get_pagenum_link( (int) $page ) );
}

/**
 * Which page numbers a pagination bar shows.
 *
 * The ends, the current page and its neighbours; a 0 marks where a run was
 * left out. Six hundred posts is sixty pages, and sixty numbers in a row is
 * not a thing anyone reads.
 *
 * @param int $current Current page.
 * @param int $total   Total pages.
 * @param int $edge    How many pages to always keep at each end.
 * @param int $around  How many pages to keep either side of the current one.
 * @return array
 */
function rs_page_slots( $current, $total, $edge = 1, $around = 1 ) {
	$slots = array();
	$last  = 0;

	for ( $page = 1; $page <= $total; $page++ ) {
		$keep = $page <= $edge
			|| $page > $total - $edge
			|| abs( $page - $current ) <= $around;

		if ( ! $keep ) {
			continue;
		}

		if ( $last && $page - $last > 1 ) {
			$slots[] = 0;
		}

		$slots[] = $page;
		$last    = $page;
	}

	return $slots;
}

/**
 * Render one arrow of the pagination bar.
 *
 * The disabled arrow stays in the flow as a span rather than disappearing,
 * so the numbers do not shift sideways between the first page and the rest.
 *
 * @param int    $page  Page to link to.
 * @param bool   $on    Whether that page exists.
 * @param string $icon  Icon name.
 * @param string $rel   Link relation.
 * @param string $label Accessible label.
 */
function rs_pagination_step( $page, $on, $icon, $rel, $label ) {
	$svg = wp_kses( rs_icon( $icon, 14 ), rs_svg_tags() );

	if ( ! $on ) {
		echo '<span class="rs-pagination__step is-off" aria-hidden="true">' . $svg . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	echo '<a class="rs-pagination__step" href="' . esc_url( rs_page_url( $page ) ) . '"'
		. ' rel="' . esc_attr( $rel ) . '" aria-label="' . esc_attr( $label ) . '">'
		. $svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		. '</a>';
}

/**
 * Numbered page links for the main query.
 *
 * Built from the main query rather than from a count of every post, so a
 * category, a tag or a search paginates over its own results. Ordinary
 * anchors, so they work with JavaScript off; app.js intercepts the click
 * and swaps the list in place when it is on.
 */
function rs_pagination() {
	$total = (int) $GLOBALS['wp_query']->max_num_pages;

	if ( $total < 2 ) {
		return;
	}

	$current = max( 1, (int) get_query_var( 'paged' ) );
	$nav_label  = rs_is_en() ? 'Pagination' : 'পাতা';
	$prev_label = rs_is_en() ? 'Previous page' : 'আগের পাতা';
	$next_label = rs_is_en() ? 'Next page' : 'পরের পাতা';
	?>
	<nav class="rs-pagination" aria-label="<?php echo esc_attr( $nav_label ); ?>">
		<?php
		rs_pagination_step( $current - 1, $current > 1, 'left', 'prev', $prev_label );

		foreach ( rs_page_slots( $current, $total ) as $slot ) {
			if ( 0 === $slot ) {
				echo '<span class="rs-pagination__gap" aria-hidden="true">…</span>';
				continue;
			}

			$digits = rs_bn_digits( $slot );

			if ( $slot === $current ) {
				printf(
					'<span class="rs-pagination__num is-current" aria-current="page">%s</span>',
					esc_html( $digits )
				);
				continue;
			}

			$page_label = rs_is_en()
				? sprintf( 'Page %s', $digits )
				: sprintf( '%s নম্বর পাতা', $digits );

			printf(
				'<a class="rs-pagination__num" href="%s" aria-label="%s">%s</a>',
				esc_url( rs_page_url( $slot ) ),
				esc_attr( $page_label ),
				esc_html( $digits )
			);
		}

		rs_pagination_step( $current + 1, $current < $total, 'right', 'next', $next_label );
		?>
	</nav>
	<?php
}

/**
 * The count that sits above the list.
 *
 * The front page counts every published post. Inside an archive that
 * number would be a claim about posts the reader cannot see from there, so
 * the archive's own total is used instead — which is also the only number
 * that answers the question they just asked by narrowing the list.
 *
 * It lives outside the swapped fragment on purpose: it describes the whole
 * archive, not the page of it currently on screen, so turning a page must
 * not change it.
 */
function rs_render_count() {
	$count = ( is_home() || is_front_page() )
		? rs_published_count()
		: (int) $GLOBALS['wp_query']->found_posts;

	if ( ! $count ) {
		/* An empty list says so in its own words, further down. */
		return;
	}

	$term = ( is_category() || is_tag() ) ? get_queried_object() : null;

	if ( rs_is_en() ) {
		$plural = 1 === (int) $count ? 'writing' : 'writings';
		if ( $term instanceof WP_Term && is_category() ) {
			$before = '';
			$after  = ' ' . $plural . ' in ' . $term->name;
		} elseif ( $term instanceof WP_Term ) {
			$before = '';
			$after  = ' ' . $plural . ' tagged with ' . $term->name;
		} elseif ( is_search() ) {
			$before = '';
			$after  = ' ' . ( 1 === (int) $count ? 'result' : 'results' ) . ' found';
		} else {
			$before = '';
			$after  = ' ' . $plural . ' published';
		}
		$random_label = 'Random writing';
	} else {
		if ( $term instanceof WP_Term && is_category() ) {
			/* The name rather than "this category": it is the only line on an
			   archive that says which one the reader is standing in, since the
			   heading above keeps the site's own phrase. */
			$before = $term->name . ' ক্যাটাগরিতে ';
			$after  = 'টি লেখা প্রকাশিত';
		} elseif ( $term instanceof WP_Term ) {
			$before = $term->name . ' ট্যাগে ';
			$after  = 'টি লেখা প্রকাশিত';
		} elseif ( is_search() ) {
			$before = '';
			$after  = 'টি লেখা পাওয়া গেছে';
		} else {
			$before = '';
			$after  = 'টি লেখা প্রকাশিত';
		}
		$random_label = 'যেকোনো একটা লেখা';
	}
	/*
	 * On the front page the sentence is already a description of the index,
	 * so it becomes the way in rather than growing a button beside itself.
	 * Only there: on an archive it counts that archive's posts, and sending
	 * a reader from "গল্প ক্যাটাগরিতে ১৫টি" to a list of all forty seven
	 * would answer a question they did not ask.
	 */
	$index = ( is_home() || is_front_page() ) ? rs_index_url() : '';
	?>
	<div class="rs-wrap">
		<p class="rs-post-count">
			<?php if ( $index ) : ?>
				<a class="rs-post-count__all" href="<?php echo esc_url( $index ); ?>"><?php
					echo esc_html( $before );
					?><span class="rs-post-count__number"><?php echo esc_html( rs_bn_digits( $count ) ); ?></span><?php
					echo esc_html( $after );
				?></a>
			<?php else : ?>
				<?php
				/* No line breaks around the number: টি is a suffix, and any
				   whitespace here would render as a space inside the word. */
				echo esc_html( $before );
				?><span class="rs-post-count__number"><?php echo esc_html( rs_bn_digits( $count ) ); ?></span><?php
				echo esc_html( $after );
				?>
			<?php endif; ?>
			<a class="rs-post-count__any"
				href="<?php echo esc_url( rs_random_url() ); ?>"
				data-rs-random="<?php echo (int) rs_random_cat(); ?>"><?php echo esc_html( $random_label ); ?></a>
		</p>
	</div>
	<?php
}

/**
 * Featured post block
 */
function rs_render_featured_post( $cat_id = 0 ) {
	if ( is_search() ) {
		return;
	}

	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'rand',
		'no_found_rows'  => true,
	);

	if ( $cat_id ) {
		$args['cat'] = $cat_id;
	} elseif ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$args['cat'] = $term->term_id;
		}
	}

	$q = new WP_Query( $args );

	if ( ! $q->have_posts() ) {
		return;
	}

	$q->the_post();
	global $post;

	$length = (int) rs_option( 'rs_featured_summary_length' );
	if ( ! $length ) $length = 250;
	
	if ( $length > 200 ) {
		$text = has_excerpt( $post )
			? trim( html_entity_decode( wp_strip_all_tags( $post->post_excerpt ), ENT_QUOTES, 'UTF-8' ) )
			: rs_plain_text( $post );
		$summary = rs_shorten( $text, $length );
	} else {
		$summary = rs_summary( $post, $length );
	}

	// Match first letter and its combining marks/virama-linked letters
	$pattern = '/^([\x{0980}-\x{09FF}](?:\x{09CD}[\x{0980}-\x{09FF}])*[\x{09BE}-\x{09CC}\x{09D7}\x{09E2}\x{09E3}]?[\x{0981}-\x{0983}]?)/u';
	if ( preg_match( $pattern, $summary, $matches ) ) {
		$dropcap = $matches[0];
	} else {
		// Fallback for non-Bengali or basic chars
		preg_match('/^\X/u', $summary, $matches);
		$dropcap = $matches[0] ?? '';
	}
	$rest = mb_substr( $summary, mb_strlen( $dropcap, 'UTF-8' ), null, 'UTF-8' );

	?>
	<div>
		<div class="rs-featured">
			<div class="rs-featured__label">
				<span class="rs-featured__line"></span><?php echo esc_html( rs_is_en() ? 'Featured' : 'ফিচার্ড' ); ?>
			</div>
			<h2 class="rs-featured__title">
				<a href="<?php the_permalink(); ?>" data-rs-post="<?php the_ID(); ?>"><?php the_title(); ?></a>
			</h2>
			<div class="rs-featured__date">
				<?php echo esc_html( rs_bn_date( $post ) ); ?>
				<span style="margin: 0 0.5rem; opacity: 0.5;">&bull;</span>
				<?php echo esc_html( rs_reading_time( $post ) ); ?>
				<?php if ( $cat = rs_category( $post ) ) : ?>
					<span style="margin: 0 0.5rem; opacity: 0.5;">&bull;</span>
					<a href="<?php echo esc_url( rs_category_link( $post ) ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( $cat ); ?></a>
				<?php endif; ?>
			</div>
			<div class="rs-featured__summary">
				<span class="rs-featured__dropcap"><?php echo esc_html( $dropcap ); ?></span><?php echo esc_html( $rest ); ?>
			</div>
			<div class="rs-featured__action">
				<a href="<?php the_permalink(); ?>" class="rs-featured__btn" data-rs-post="<?php the_ID(); ?>"><?php echo esc_html( rs_is_en() ? 'Read full article →' : 'সম্পূর্ণ লেখা পড়ুন →' ); ?></a>
			</div>
		</div>
	</div>
	<?php
	wp_reset_postdata();
}

/**
 * The list of posts, with its page links.
 *
 * Both the full page and the fragment that app.js swaps in go through
 * here, so the page reached by clicking "২" is the same markup as the page
 * reached by opening /page/2/ directly.
 */
function rs_render_list() {
	/* Inside a category archive the tag would just repeat the heading, so
	   it only earns its place in a mixed list. */
	$show_cat = ! is_category();
	?>
	<div class="rs-list-wrap" id="rs-list-wrap" tabindex="-1" data-rs-title="<?php echo esc_attr( wp_get_document_title() ); ?>">
		<?php if ( have_posts() ) : ?>

			<div class="rs-list" id="rs-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="rs-row">
						<a class="rs-row__link"
							href="<?php the_permalink(); ?>"
							data-rs-post="<?php the_ID(); ?>"
							data-rs-summary="<?php echo esc_attr( rs_summary() ); ?>">
							<span class="rs-row__head">
								<span class="rs-row__title"><?php the_title(); ?></span><?php
								$rs_cat = $show_cat ? rs_category() : '';
								if ( $rs_cat ) :
									?><em class="rs-row__cat"><?php echo esc_html( $rs_cat ); ?></em><?php
								endif;
								?>
							</span>
							<span class="rs-row__aside">
								<span class="rs-row__read"><?php echo esc_html( rs_reading_time() ); ?></span>
								<span class="rs-row__date"><?php echo esc_html( rs_bn_date() ); ?></span>
							</span>
						</a>
						<?php
						/* Outside the link rather than inside it. A button
						   nested in an anchor is invalid markup, and every
						   press of it would also open the post — which is
						   the one thing this button exists to postpone.

						   It carries only the id: app.js reads the title,
						   the address and the reading time off the row it
						   sits in rather than having them repeated into
						   attributes on every row of every page. */
						?>
						<button class="rs-row__save" type="button"
							data-rs-later="<?php the_ID(); ?>"
							aria-pressed="false"
							aria-label="<?php echo esc_attr( rs_is_en() ? 'Read later' : 'পরে পড়ব' ); ?>">
							<?php echo wp_kses( rs_icon( 'bookmark', 14 ), rs_svg_tags() ); ?>
						</button>
					</article>
				<?php endwhile; ?>
			</div>

			<?php rs_pagination(); ?>

		<?php else : ?>

			<div class="rs-notice">
				<?php if ( is_search() ) : ?>
					<h2><?php echo esc_html( rs_is_en() ? 'No results found' : 'কিছু পাওয়া যায়নি' ); ?></h2>
					<p><?php echo esc_html( rs_is_en() ? 'Try searching with different keywords.' : 'অন্য শব্দ দিয়ে খুঁজে দেখুন।' ); ?></p>
				<?php else : ?>
					<h2><?php echo esc_html( rs_is_en() ? 'No writings yet' : 'এখনো কোনো লেখা নেই' ); ?></h2>
					<p><?php echo esc_html( rs_is_en() ? 'Published articles will appear here.' : 'প্রথম লেখাটা প্রকাশ করলে এখানে দেখা যাবে।' ); ?></p>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	</div>
	<?php
}

/**
 * Answer ?rs_ajax=1 with the list on its own.
 *
 * A page link handled by app.js asks for the page it was going to load
 * anyway, just without the header, hero and footer wrapped around it. The
 * main query has already run by the time template_redirect fires, so this
 * is the same list the full page would have shown — which is what keeps a
 * category, a tag or a search paginating over its own posts without the
 * endpoint having to know anything about them.
 */
function rs_list_fragment() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only, and public.
	if ( ! isset( $_GET['rs_ajax'] ) ) {
		return;
	}

	if ( ! is_home() && ! is_archive() && ! is_search() ) {
		return;
	}

	nocache_headers();
	header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
	/* A fragment has no header, no footer and no canonical tag. If a
	   crawler ever finds one of these URLs, it should not keep it. */
	header( 'X-Robots-Tag: noindex' );

	rs_render_list();
	exit;
}
add_action( 'template_redirect', 'rs_list_fragment' );

/* =========================================================================
 * 9. REST endpoints
 * ====================================================================== */

/**
 * Register the theme's REST routes.
 */
function rs_rest_routes() {
	register_rest_route(
		'rs/v1',
		'/post/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rs_rest_post',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/search',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rs_rest_search',
			'permission_callback' => '__return_true',
			'args'                => array(
				'q' => array(
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/featured',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rs_rest_featured',
			'permission_callback' => '__return_true',
			'args'                => array(
				'cat' => array(
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/random',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rs_rest_random',
			'permission_callback' => '__return_true',
			'args'                => array(
				'cat' => array(
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/edit/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rs_rest_edit',
			'permission_callback' => 'rs_can_edit',
			'args'                => array(
				'id' => array(
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/view/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'rs_rest_view',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id'    => array(
					'sanitize_callback' => 'absint',
				),
				/* Set by the browser the first time it opens this post. */
				'first' => array(
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'rs_rest_routes' );

/**
 * One post, rendered for the modal.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function rs_rest_post( $request ) {
	$item = get_post( (int) $request['id'] );

	if ( ! $item || 'publish' !== $item->post_status || 'post' !== $item->post_type ) {
		return new WP_Error( 'rs_not_found', 'পোস্ট পাওয়া যায়নি', array( 'status' => 404 ) );
	}

	if ( ! empty( $item->post_password ) ) {
		return new WP_Error( 'rs_protected', 'পোস্টটি সুরক্ষিত', array( 'status' => 403 ) );
	}

	// Blocks and shortcodes read the global post, so point it at this one
	// while the_content runs, then put it back.
	global $post;
	$restore = $post;
	$post    = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

	setup_postdata( $post );
	$content = apply_filters( 'the_content', $item->post_content );

	// get_previous_post() and get_next_post() read the global too, so they
	// have to run before it is put back.
	$prev = get_previous_post();
	$next = get_next_post();

	wp_reset_postdata();

	$post = $restore; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

	return rest_ensure_response(
		array(
			'id'           => $item->ID,
			'title'        => rs_plain_title( $item ),
			'date'         => rs_bn_date( $item ),
			'author'       => get_the_author_meta( 'display_name', $item->post_author ),
			'content'      => $content,
			'link'         => get_permalink( $item ),
			'category'     => rs_category( $item ),
			'categoryLink' => rs_category_link( $item ),
			'categoryId'   => rs_primary_category( $item ) ? rs_primary_category( $item )->term_id : 0,
			'readingTime'  => rs_reading_time( $item ),
			'related'      => rs_related_payload( $item ),
			'prev'         => rs_adjacent_payload( $prev ),
			'next'         => rs_adjacent_payload( $next ),
		)
	);
}

/**
 * Trim an adjacent post down to what the modal's footer nav needs.
 *
 * @param WP_Post|string|null $item Adjacent post, or '' when there is none.
 * @return array|null
 */
function rs_adjacent_payload( $item ) {
	if ( ! $item instanceof WP_Post ) {
		return null;
	}

	return array(
		'id'    => $item->ID,
		'title' => rs_plain_title( $item ),
		'link'  => get_permalink( $item ),
	);
}

/**
 * Server side search with a snippet around the match.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function rs_rest_search( $request ) {
	/* Cut before it is looked at. Nobody searches with eighty characters,
	   and a LIKE across every post body deserves an upper bound that does
	   not depend on the caller being reasonable. Plain mb_substr rather
	   than rs_shorten(), which would hang an ellipsis on the end of the
	   thing being searched for. */
	$term = mb_substr( trim( (string) $request->get_param( 'q' ) ), 0, 80, 'UTF-8' );

	/*
	 * Two, not one. A single Bengali letter matches most of the archive
	 * and tells the reader nothing, and app.js holds its hint on screen
	 * until there are two, so this is the same rule kept on both sides.
	 */
	if ( mb_strlen( $term, 'UTF-8' ) < 2 ) {
		return rest_ensure_response(
			array(
				'items' => array(),
				'total' => 0,
			)
		);
	}

	$query = new WP_Query(
		array(
			's'                      => $term,
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 25,
			'has_password'           => false,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$items = array();

	foreach ( $query->posts as $post ) {
		$items[] = array(
			'id'      => $post->ID,
			'title'   => rs_plain_title( $post ),
			'date'    => rs_bn_date( $post ),
			'link'    => get_permalink( $post ),
			'snippet' => rs_match_snippet( $post, $term ),
		);
	}

	return rest_ensure_response(
		array(
			'items' => $items,
			'total' => (int) $query->found_posts,
		)
	);
}

/**
 * Which post "any one of them" landed on.
 *
 * Only the ID and the address, because app.js hands them straight to the
 * same code a click on a list row uses, and that fetches the rest.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function rs_rest_featured( $request ) {
	nocache_headers();
	$cat_id = (int) $request->get_param( 'cat' );
	
	ob_start();
	rs_render_featured_post( $cat_id );
	$html = ob_get_clean();
	
	return rest_ensure_response( array( 'html' => $html ) );
}

function rs_rest_random( $request ) {
	$items = rs_random_posts( 1, 0, (int) $request->get_param( 'cat' ) );

	if ( ! $items ) {
		return new WP_Error( 'rs_empty', 'কোনো লেখা নেই', array( 'status' => 404 ) );
	}

	/* Nothing between here and the reader should hold on to this: a
	   different answer every time is the entire feature. */
	nocache_headers();

	return rest_ensure_response(
		array(
			'id'   => $items[0]->ID,
			'link' => get_permalink( $items[0] ),
		)
	);
}

/**
 * Whether the caller may edit the post they are asking about.
 *
 * Unlike the counting endpoint next door, this one is a normal
 * authenticated REST call: app.js sends the X-WP-Nonce it was given in the
 * page, WordPress recognises the cookie because of it, and
 * current_user_can() means what it usually means in here.
 *
 * @param WP_REST_Request $request Request.
 * @return bool|WP_Error
 */
function rs_can_edit( $request ) {
	$id   = (int) $request['id'];
	$item = get_post( $id );

	if ( ! $item || 'post' !== $item->post_type ) {
		return new WP_Error( 'rs_not_found', 'পোস্ট পাওয়া যায়নি', array( 'status' => 404 ) );
	}

	return current_user_can( 'edit_post', $id );
}

/**
 * Save a title or a body edited in place.
 *
 * Nothing is filtered here on the way past. wp_update_post() runs the
 * content through the same hooks the editor screen does — kses for anyone
 * without unfiltered_html, and nothing extra for anyone with it — so a
 * post edited from the front of the site ends up in exactly the state the
 * dashboard would have left it in. Adding a second pass of our own would
 * only mean the two paths could disagree, and the one that strips
 * something the author was allowed to keep is the front end.
 *
 * It also writes a revision, which is the real safety net under all of
 * this: anything a stray keystroke does here is one click away from being
 * undone under Posts → Revisions.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function rs_rest_edit( $request ) {
	$id     = (int) $request['id'];
	$title  = $request->get_param( 'title' );
	$body   = $request->get_param( 'content' );
	$update = array( 'ID' => $id );

	if ( is_string( $title ) && '' !== trim( $title ) ) {
		/* Sent from a field that only ever holds text, and read out of it
		   as text; tags here would be an accident either way. */
		$update['post_title'] = wp_strip_all_tags( $title );
	}

	if ( is_string( $body ) ) {
		$update['post_content'] = $body;
	}

	if ( count( $update ) < 2 ) {
		return new WP_Error( 'rs_nothing', 'বদলানোর মতো কিছু আসেনি', array( 'status' => 400 ) );
	}

	$saved = wp_update_post( $update, true );

	if ( is_wp_error( $saved ) ) {
		return $saved;
	}

	$item = get_post( $id );

	/* Handed back rather than assumed, because what was saved is not
	   always what was sent: kses may have trimmed it, and the reading
	   time is worked out again from whatever survived. */
	return rest_ensure_response(
		array(
			'id'          => $item->ID,
			'title'       => rs_plain_title( $item ),
			'content'     => apply_filters( 'the_content', $item->post_content ),
			'readingTime' => rs_reading_time( $item ),
		)
	);
}

/**
 * Whether whatever is asking looks like a machine.
 *
 * Counting from the browser already keeps out everything that does not
 * run JavaScript, which is most crawlers. What is left is the handful
 * that do, plus the previewers, the uptime checkers and anything run from
 * a script — none of them readers.
 *
 * A guard on the user agent rather than a rate limit keyed on the address.
 * The limit would want a transient, and a transient is a row written to
 * the options table: it would add a write to every genuine reading in
 * order to save writes during an attack nobody has made on a site with
 * thirty four stories on it.
 *
 * @return bool
 */
function rs_looks_automated() {
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] )
		? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) )
		: '';

	/* A browser always sends one. */
	if ( '' === $agent ) {
		return true;
	}

	$marks = array(
		'bot',
		'crawl',
		'spider',
		'slurp',
		'facebookexternalhit',
		'headless',
		'preview',
		'python',
		'curl',
		'wget',
		'http-client',
		'monitor',
		'lighthouse',
		'pingdom',
	);

	foreach ( $marks as $mark ) {
		if ( false !== strpos( $agent, $mark ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Add one to a post's read count.
 *
 * Called from the browser rather than while the page renders, because the
 * pages sit behind a full page cache: a reader served a cached copy runs
 * no PHP at all, so anything counted during a render would miss most of
 * them.
 *
 * There is no nonce, and that is deliberate. A nonce belongs to a session
 * and would be baked into the cached HTML, so every reader would send the
 * same stale one. The endpoint is written to be safe without it: it takes
 * nothing but the ID of a published post and adds one to a number.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function rs_rest_view( $request ) {
	$id   = (int) $request['id'];
	$item = get_post( $id );

	if ( ! $item || 'publish' !== $item->post_status || 'post' !== $item->post_type ) {
		return new WP_Error( 'rs_not_found', 'পোস্ট পাওয়া যায়নি', array( 'status' => 404 ) );
	}

	/*
	 * The author reading their own work is not a reader, and the cookie is
	 * read here by hand rather than through is_user_logged_in(). WordPress
	 * only trusts a cookie on a REST request that also carries a nonce, and
	 * a nonce cannot be used on these pages: it belongs to one session, and
	 * the page it would have to travel in is cached and handed to everyone.
	 * wp_validate_auth_cookie() checks the same cookie without that rule.
	 */
	$viewer = wp_validate_auth_cookie( '', 'logged_in' );

	if ( ( $viewer && user_can( $viewer, 'edit_posts' ) ) || rs_looks_automated() ) {
		return rest_ensure_response( array( 'counted' => false ) );
	}

	rs_bump( $id, RS_VIEWS_KEY );

	/*
	 * Whether this browser has opened this post before is the browser's
	 * own memory, and it is the only party that can answer. Which makes
	 * this a count of browsers rather than of people: the same reader on
	 * a phone and a laptop is two, and clearing site data starts them
	 * over. Nothing closer is possible without keeping something about
	 * each reader on the server, which is not worth doing for a number.
	 */
	if ( $request->get_param( 'first' ) ) {
		rs_bump( $id, RS_READERS_KEY );
	}

	return rest_ensure_response( array( 'counted' => true ) );
}

/**
 * Add one to a counter held in post meta.
 *
 * The addition happens in the database rather than by reading, adding and
 * writing back: two readers arriving together would otherwise both store
 * the same number, and one of them would be lost.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 */
function rs_bump( $post_id, $key ) {
	global $wpdb;

	/* There has to be a row before there is anything to add to. */
	add_post_meta( $post_id, $key, 0, true );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No API for an atomic increment.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
			$post_id,
			$key
		)
	);

	/* The row was changed behind the object cache's back. */
	wp_cache_delete( $post_id, 'post_meta' );
}

/* =========================================================================
 * 10. SEO meta
 * ====================================================================== */

/**
 * Whether an SEO plugin is doing this job instead.
 *
 * Yoast and Rank Math write the same tags. Two descriptions and two
 * canonicals are worse than either one alone, so everything in this
 * section stands down when one of them is active.
 *
 * @return bool
 */
function rs_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' );
}

/**
 * Google Search Console's ownership tag.
 *
 * Google only reads this on the address the property was registered under,
 * but it costs a single line to put it on every page, and doing so means a
 * property added later for a subsection verifies without another edit.
 *
 * Not gated on rs_seo_plugin_active(): this proves who owns the site
 * rather than describing it, so there is nothing here to duplicate.
 */
function rs_verify_tag() {
	$code = trim( (string) rs_option( 'rs_verify' ) );

	if ( '' === $code ) {
		return;
	}

	printf( "<meta name=\"google-site-verification\" content=\"%s\">\n", esc_attr( $code ) );
}
add_action( 'wp_head', 'rs_verify_tag', 1 );

/**
 * The first image in a post's body.
 *
 * WordPress stamps every image inserted from the media library with a
 * wp-image-<id> class. That is worth more than the src beside it: an ID
 * gives the right size and its dimensions, and it survives the site being
 * moved to another domain. An image pasted in as a bare URL carries no
 * such class and is not found, which is the correct outcome — it may not
 * be ours to put on a share card.
 *
 * @param WP_Post $post Post object.
 * @return int Attachment ID, or 0.
 */
function rs_content_image_id( $post ) {
	if ( ! preg_match( '/wp-image-(\d+)/', $post->post_content, $found ) ) {
		return 0;
	}

	return (int) $found[1];
}

/**
 * The picture to attach to a shared link.
 *
 * Three places to look, in order of how deliberately the picture was
 * chosen: the featured image, then the first image in the body, then the
 * one set in the customizer.
 *
 * The middle one is there because this theme never displays a featured
 * image. Setting one would be invisible work done purely for Facebook's
 * benefit, and a post that already opens with a picture has said which
 * picture it is about.
 *
 * @return array|null
 */
function rs_share_image() {
	$id = 0;

	if ( is_singular() ) {
		$post = get_post( get_queried_object_id() );

		if ( $post && has_post_thumbnail( $post ) ) {
			$id = get_post_thumbnail_id( $post );
		} elseif ( $post ) {
			$id = rs_content_image_id( $post );
		}
	}

	if ( ! $id ) {
		$id = (int) rs_option( 'rs_og_image' );
	}

	if ( ! $id ) {
		return null;
	}

	/*
	 * The medium size deliberately, and the smallness is the point.
	 *
	 * Facebook chooses the shape of the card from the picture it is
	 * handed. At 600x315 or above it builds the tall card: a wide image,
	 * the headline under it, and nothing else. Below that it builds the
	 * compact one — a small picture on the left, and on the right the
	 * headline followed by the opening lines of the writing.
	 *
	 * What is being shared here is prose, so the card that shows a few
	 * lines of it is worth more than the one that fills the feed with a
	 * picture. Sending a large image would silently throw those lines
	 * away, which is exactly what it did until this was changed.
	 */
	$src = wp_get_attachment_image_src( $id, 'medium' );

	if ( ! $src ) {
		return null;
	}

	return array(
		'url'    => $src[0],
		'width'  => (int) $src[1],
		'height' => (int) $src[2],
	);
}

/**
 * What the view on screen should say about itself.
 *
 * Null for the views that have nothing worth saying — a date or author
 * archive repeats the front page, and rs_robots() below keeps those out of
 * the index rather than describing them.
 *
 * @return array|null
 */
function rs_seo_context() {
	$paged = max( 1, (int) get_query_var( 'paged' ) );

	if ( is_singular() ) {
		return array(
			'title'       => get_the_title( get_queried_object_id() ),
			'description' => rs_summary( get_queried_object_id(), 160 ),
			'url'         => get_permalink( get_queried_object_id() ),
			'type'        => is_singular( 'post' ) ? 'article' : 'website',
		);
	}

	if ( is_category() || is_tag() ) {
		$term = get_queried_object();

		if ( ! $term instanceof WP_Term ) {
			return null;
		}

		$base = get_term_link( $term );

		if ( is_wp_error( $base ) ) {
			return null;
		}

		$about = trim( wp_strip_all_tags( $term->description ) );

		if ( '' === $about ) {
			$about = sprintf(
				is_category() ? '%1$s বিভাগের সব লেখা — %2$s' : '%1$s বিষয়ের সব লেখা — %2$s',
				$term->name,
				get_bloginfo( 'name' )
			);
		}

		return array(
			'title'       => $term->name,
			'description' => rs_shorten( $about, 160 ),
			/* Page one keeps the clean term URL. Running it through
			   get_pagenum_link() would drag any utm_ tags the reader
			   arrived with into the canonical. */
			'url'         => $paged > 1 ? rs_page_url( $paged ) : $base,
			'type'        => 'website',
		);
	}

	if ( is_home() || is_front_page() ) {
		$site_name = get_bloginfo( 'name' );
		$tagline   = get_bloginfo( 'description' );
		return array(
			'title'       => $site_name . ' - ' . $tagline,
			'description' => $site_name . ' - ' . $tagline . '। রাইসুল সোহানের ব্যক্তিগত ওয়েবসাইট ও ব্লগ (Personal Website & Blog)।',
			'url'         => $paged > 1 ? rs_page_url( $paged ) : home_url( '/' ),
			'type'        => 'website',
		);
	}

	return null;
}

/**
 * Description, canonical, Open Graph and Twitter tags.
 */
function rs_seo_meta() {
	if ( rs_seo_plugin_active() ) {
		return;
	}

	$view = rs_seo_context();

	if ( ! $view ) {
		return;
	}

	$image = rs_share_image();

	printf( "\n<meta name=\"description\" content=\"%s\">\n", esc_attr( $view['description'] ) );

	/* Core writes a canonical on single posts and pages and nowhere else,
	   which was enough while those were the only addresses the theme had.
	   Now that the list paginates, page two has to name itself or it reads
	   as a second copy of the front page. */
	if ( ! is_singular() ) {
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $view['url'] ) );
	}

	printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $view['type'] ) );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $view['title'] ) );
	printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $view['description'] ) );
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $view['url'] ) );
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( get_locale() ) );

	if ( is_singular( 'post' ) ) {
		$id     = get_queried_object_id();
		$author = (int) get_post_field( 'post_author', $id );

		printf( "<meta property=\"article:published_time\" content=\"%s\">\n", esc_attr( get_the_date( DATE_W3C, $id ) ) );
		printf( "<meta property=\"article:modified_time\" content=\"%s\">\n", esc_attr( get_the_modified_date( DATE_W3C, $id ) ) );
		printf( "<meta property=\"article:author\" content=\"%s\">\n", esc_attr( get_the_author_meta( 'display_name', $author ) ) );

		$section = rs_category( $id );

		if ( $section ) {
			printf( "<meta property=\"article:section\" content=\"%s\">\n", esc_attr( $section ) );
		}
	}

	if ( $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image['url'] ) );
		printf( "<meta property=\"og:image:width\" content=\"%d\">\n", (int) $image['width'] );
		printf( "<meta property=\"og:image:height\" content=\"%d\">\n", (int) $image['height'] );
	}

	/* The small card here too, to match what rs_share_image() asks
	   Facebook for. summary_large_image would stretch a medium sized
	   picture across a wide frame and drop the description underneath it,
	   which is the trade this theme has already declined once. */
	printf( "<meta name=\"twitter:card\" content=\"summary\">\n" );
	printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $view['title'] ) );
	printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $view['description'] ) );
	printf( "<meta name=\"twitter:site\" content=\"@raisulsohan\">\n" );
	printf( "<meta name=\"twitter:creator\" content=\"@raisulsohan\">\n" );
	if ( $image ) {
		printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $image['url'] ) );
	}
}
add_action( 'wp_head', 'rs_seo_meta', 1 );

/**
 * The trail from the front page to whatever is being shown.
 *
 * This is the half of structured data a reader actually sees: given it,
 * Google prints "raisulsohan.com › গল্প › …" above a result instead of the
 * bare address. It belongs on the post as much as on the archive — the post
 * is the page that turns up in a search, so the post is the page whose
 * result the trail improves.
 *
 * The last step carries no "item" on purpose. It is the page already being
 * read, and schema.org treats a trail that ends without a link as ending
 * here rather than pointing somewhere else.
 *
 * @return array|null BreadcrumbList node, or null where there is no trail.
 */
function rs_breadcrumb_schema() {
	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => get_bloginfo( 'name' ),
			'item'     => home_url( '/' ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$term = rs_primary_category();

		if ( $term ) {
			$link = get_term_link( $term );

			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => $term->name,
				/* get_term_link() answers with an error object when a term
				   has lost its taxonomy; a step without an address is still
				   a usable step. */
				'item'     => is_wp_error( $link ) ? null : $link,
			);
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => count( $items ) + 1,
			/* Decoded for the same reason the headline above is: this is
			   JSON, and an "&#8217;" would reach Google as those characters
			   rather than as the apostrophe it stands for. */
			'name'     => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ),
		);
	} elseif ( is_category() || is_tag() ) {
		$term = get_queried_object();

		if ( ! $term || is_wp_error( $term ) || empty( $term->name ) ) {
			return null;
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => $term->name,
		);
	} else {
		return null;
	}

	/* A trail of one step is the front page pointing at itself. */
	if ( count( $items ) < 2 ) {
		return null;
	}

	foreach ( $items as $index => $item ) {
		if ( array_key_exists( 'item', $item ) && null === $item['item'] ) {
			unset( $items[ $index ]['item'] );
		}
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array_values( $items ),
	);
}

/**
 * JSON-LD, so a search engine can tell a story from a page about stories.
 *
 * Person rather than Organization as the publisher: this is one writer's
 * site, and saying otherwise would invite a logo that does not exist.
 */
function rs_schema() {
	if ( rs_seo_plugin_active() ) {
		return;
	}

	$data = null;

	if ( is_singular( 'post' ) ) {
		$id     = get_queried_object_id();
		$author = (int) get_post_field( 'post_author', $id );
		$image  = rs_share_image();

		$data = array(
			'@type'            => 'BlogPosting',
			/*
			 * schema.org asks for 110 characters or fewer here.
			 *
			 * Decoded first, because this is JSON and not HTML: wptexturize
			 * turns a hyphen in a title into "&#8211;", which an attribute
			 * would decode on the way out but a JSON string would hand to
			 * Google as those eight literal characters. rs_summary() below
			 * is already plain text, so it needs none of this.
			 */
			'headline'         => rs_shorten( html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ), 110 ),
			'description'      => rs_summary( $id, 160 ),
			'datePublished'    => get_the_date( DATE_W3C, $id ),
			'dateModified'     => get_the_modified_date( DATE_W3C, $id ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $id ),
			),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $author ),
				'url'   => home_url( '/' ),
			),
			'publisher'        => array(
				'@type' => 'Person',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		$section = rs_category( $id );

		if ( $section ) {
			$data['articleSection'] = $section;
		}

		if ( $image ) {
			$data['image'] = $image['url'];
		}
	} elseif ( is_home() || is_front_page() ) {
		$data = array(
			'@type'       => 'WebSite',
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'url'         => home_url( '/' ),
			'inLanguage'  => get_bloginfo( 'language' ),
			/*
			 * The site's own search, described so a machine can use it.
			 *
			 * Google stopped drawing a search box under a result for this
			 * in late 2023, so it is not the reason to keep it. It stays
			 * because it is true, it costs nine lines, and it is still read
			 * by everything else that reads structured data.
			 */
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/* Built for the same pages either way, and appended rather than folded
	   in, because a trail is its own thing and not a property of the post. */
	$graph = array();

	if ( $data ) {
		$graph[] = $data;
	}

	$crumbs = rs_breadcrumb_schema();

	if ( $crumbs ) {
		$graph[] = $crumbs;
	}

	if ( ! $graph ) {
		return;
	}

	/* One object stays one object; more than one goes in a @graph. Either
	   way @context is stated once, at the top. */
	$payload = array( '@context' => 'https://schema.org' );

	if ( 1 === count( $graph ) ) {
		$payload = array_merge( $payload, $graph[0] );
	} else {
		$payload['@graph'] = $graph;
	}

	/* Unicode is left alone so the Bengali stays readable in view source,
	   but slashes keep their escaping: that is what stops a "</script>" in
	   a title from closing this block early. */
	printf(
		"\n<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
add_action( 'wp_head', 'rs_schema', 2 );

/**
 * Keep the archives that repeat the front page out of the index.
 *
 * A date or an author archive on a one writer site is the same posts in
 * the same order with nothing added, and every one of them competes with
 * the pages that matter. Search results core already handles on its own.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function rs_robots( $robots ) {
	if ( rs_seo_plugin_active() ) {
		return $robots;
	}

	if ( is_date() || is_author() ) {
		$robots['noindex'] = true;
		/* Still worth crawling through: the links out of here are the
		   posts themselves. */
		$robots['follow'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'rs_robots' );

/**
 * Keep the author archive out of the sitemap.
 *
 * A sitemap is a list of pages worth indexing, and rs_robots() above has
 * just told Google not to index this one. Offering it anyway is a
 * contradiction, and Search Console reports it back as an excluded URL.
 *
 * @param WP_Sitemaps_Provider $provider Sitemap provider.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function rs_sitemap_providers( $provider, $name ) {
	/* The noindex is conditional on this same check, so the omission has
	   to be: with a plugin in charge the archive may well be indexable. */
	if ( rs_seo_plugin_active() ) {
		return $provider;
	}

	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'rs_sitemap_providers', 10, 2 );

/**
 * Add a helpful body class.
 *
 * @param array $classes Body classes.
 * @return array
 */
function rs_body_class( $classes ) {
	$classes[] = 'rs';

	/* app.js takes over the history on any view that renders a list: it is
	   what tells the popstate handler that a back press means "close the
	   modal" or "go to the previous page of rows", and not "leave". */
	if ( is_home() || is_front_page() || is_archive() || is_search() ) {
		$classes[] = 'rs-is-list';
	}

	if ( get_theme_mod( 'rs_enable_animations', true ) ) {
		$classes[] = 'rs-animated';
	}

	return $classes;
}
add_filter( 'body_class', 'rs_body_class' );

/* =========================================================================
 * 11. Read counts in the admin
 * ====================================================================== */

/*
 * Two numbers, and they answer different questions. Readers counts the
 * browsers that opened a post for the first time; Reads counts every
 * opening. A story people come back to shows the gap.
 */

/**
 * The two counts a post carries, and the meta each one lives in.
 *
 * Keyed by the admin column name, which is also what the sort links pass
 * back, so this one array drives the columns, their values and their
 * ordering.
 *
 * @return array
 */
function rs_count_columns() {
	return array(
		'rs_readers' => array( __( 'Readers', 'raisul-sohan' ), RS_READERS_KEY ),
		'rs_views'   => array( __( 'Reads', 'raisul-sohan' ), RS_VIEWS_KEY ),
	);
}

/**
 * How many first time readers a post has had.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function rs_readers( $post_id ) {
	return (int) get_post_meta( $post_id, RS_READERS_KEY, true );
}

/**
 * How many times a post has been opened, returning readers included.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function rs_views( $post_id ) {
	return (int) get_post_meta( $post_id, RS_VIEWS_KEY, true );
}

/**
 * Add the count columns to the posts screen.
 *
 * @param array $columns Columns.
 * @return array
 */
function rs_count_column( $columns ) {
	foreach ( rs_count_columns() as $name => $column ) {
		$columns[ $name ] = $column[0];
	}

	return $columns;
}
add_filter( 'manage_post_posts_columns', 'rs_count_column' );

/**
 * Fill those columns.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function rs_count_column_value( $column, $post_id ) {
	$columns = rs_count_columns();

	if ( isset( $columns[ $column ] ) ) {
		echo esc_html( rs_bn_digits( (int) get_post_meta( $post_id, $columns[ $column ][1], true ) ) );
	}
}
add_action( 'manage_post_posts_custom_column', 'rs_count_column_value', 10, 2 );

/**
 * Let either column be clicked to sort by it.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function rs_count_sortable( $columns ) {
	foreach ( array_keys( rs_count_columns() ) as $name ) {
		$columns[ $name ] = $name;
	}

	return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'rs_count_sortable' );

/**
 * Order the posts screen by one of the counts.
 *
 * The OR against NOT EXISTS is what keeps the unread posts on the list.
 * Ordering by a meta key alone quietly drops every post that has no row
 * for it, which here would be exactly the ones worth noticing.
 *
 * @param WP_Query $query Query.
 */
function rs_count_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$columns = rs_count_columns();
	$orderby = $query->get( 'orderby' );

	if ( ! is_string( $orderby ) || ! isset( $columns[ $orderby ] ) ) {
		return;
	}

	$key = $columns[ $orderby ][1];

	$query->set(
		'meta_query',
		array(
			'relation' => 'OR',
			array(
				'key'     => $key,
				'compare' => 'EXISTS',
			),
			array(
				'key'     => $key,
				'compare' => 'NOT EXISTS',
			),
		)
	);
	$query->set( 'orderby', 'meta_value_num' );
}
add_action( 'pre_get_posts', 'rs_count_orderby' );

/**
 * Show both counts in the editor's Publish box, where one used to sit.
 */
function rs_count_submitbox() {
	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->post_type ) {
		return;
	}

	printf(
		'<div class="misc-pub-section">%1$s <b>%2$s</b> &middot; %3$s <b>%4$s</b></div>',
		esc_html__( 'Readers:', 'raisul-sohan' ),
		esc_html( rs_bn_digits( rs_readers( get_the_ID() ) ) ),
		esc_html__( 'Reads:', 'raisul-sohan' ),
		esc_html( rs_bn_digits( rs_views( get_the_ID() ) ) )
	);
}
add_action( 'post_submitbox_misc_actions', 'rs_count_submitbox' );

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

/* =========================================================================
 * 13. Book list
 * ====================================================================== */

/**
 * Register the rs_book post type.
 *
 * Not public: books have no individual pages, no feed entries, no sitemap
 * lines. They only appear on the page that uses the Book List template.
 * The admin menu says "Books" and sits below Pages.
 */
function rs_register_book_cpt() {
	register_post_type( 'rs_book', array(
		'labels' => array(
			'name'               => 'Books',
			'singular_name'      => 'Book',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Book',
			'edit_item'          => 'Edit Book',
			'new_item'           => 'New Book',
			'view_item'          => 'View Book',
			'search_items'       => 'Search Books',
			'not_found'          => 'No books found.',
			'not_found_in_trash' => 'No books found in Trash.',
			'all_items'          => 'All Books',
			'menu_name'          => 'Books',
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 21,
		'menu_icon'           => 'dashicons-book-alt',
		'supports'            => array( 'title' ),
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => false,
	) );
}
add_action( 'init', 'rs_register_book_cpt' );

/**
 * Register the rs_book_genre taxonomy.
 *
 * Hierarchical like categories but private: no archive pages, no URLs.
 * Only attached to rs_book, never to regular posts.
 */
function rs_register_book_genre_taxonomy() {
	register_taxonomy( 'rs_book_genre', 'rs_book', array(
		'labels' => array(
			'name'              => 'Genres',
			'singular_name'     => 'Genre',
			'search_items'      => 'Search Genres',
			'all_items'         => 'All Genres',
			'edit_item'         => 'Edit Genre',
			'update_item'       => 'Update Genre',
			'add_new_item'      => 'Add New Genre',
			'new_item_name'     => 'New Genre Name',
			'menu_name'         => 'Genres',
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => false,
		'rewrite'           => false,
	) );
}
add_action( 'init', 'rs_register_book_genre_taxonomy' );

/**
 * Translate book genres to English if in English mode.
 *
 * @param string $genre
 * @return string
 */
function rs_translate_book_genre( $genre ) {
	static $map = array(
		'ইতিহাস'           => 'History',
		'উপন্যাস'          => 'Novel',
		'উপন্যাস সমগ্র'    => 'Novel Collection',
		'কবিতা'            => 'Poetry',
		'গল্পসমগ্র'        => 'Collected Stories',
		'ছোটগল্প'          => 'Short Stories',
		'জীবনী'            => 'Biography',
		'থ্রিলার'          => 'Thriller',
		'দর্শন'            => 'Philosophy',
		'প্রবন্ধ'          => 'Essays',
		'বিজ্ঞান'          => 'Science',
		'ভ্রমণ'            => 'Travel',
		'ম্যাগাজিন'        => 'Magazine',
		'রচনাবলী'          => 'Works',
		'রচনাসমগ্র'        => 'Collected Works',
		'রাজনীতি'          => 'Politics',
		'শিশুতোষ'          => 'Children\'s Literature',
		'সমগ্র'            => 'Omnibus',
		'সাক্ষাৎকার সমগ্র' => 'Collected Interviews',
		'সায়েন্স ফিকশন'    => 'Science Fiction',
		'স্মৃতিকথা'        => 'Memoirs',
	);

	if ( rs_is_en() && isset( $map[ $genre ] ) ) {
		return $map[ $genre ];
	}

	return $genre;
}

/**
 * Auto-sync book genres from main site to English subsite.
 */
function rs_sync_book_genres_from_main_site() {
	if ( ! is_multisite() || is_main_site() ) {
		return;
	}

	if ( get_option( 'rs_book_genres_synced_v2' ) ) {
		return;
	}

	$genre_map = array(
		'ইতিহাস'           => 'History',
		'উপন্যাস'          => 'Novel',
		'উপন্যাস সমগ্র'    => 'Novel Collection',
		'কবিতা'            => 'Poetry',
		'গল্পসমগ্র'        => 'Collected Stories',
		'ছোটগল্প'          => 'Short Stories',
		'জীবনী'            => 'Biography',
		'থ্রিলার'          => 'Thriller',
		'দর্শন'            => 'Philosophy',
		'প্রবন্ধ'          => 'Essays',
		'বিজ্ঞান'          => 'Science',
		'ভ্রমণ'            => 'Travel',
		'ম্যাগাজিন'        => 'Magazine',
		'রচনাবলী'          => 'Works',
		'রচনাসমগ্র'        => 'Collected Works',
		'রাজনীতি'          => 'Politics',
		'শিশুতোষ'          => 'Children\'s Literature',
		'সমগ্র'            => 'Omnibus',
		'সাক্ষাৎকার সমগ্র' => 'Collected Interviews',
		'সায়েন্স ফিকশন'    => 'Science Fiction',
		'স্মৃতিকথা'        => 'Memoirs',
	);

	switch_to_blog( get_main_site_id() );
	$main_books = get_posts(
		array(
			'post_type'      => 'rs_book',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	$slug_to_genres = array();
	foreach ( $main_books as $mb_id ) {
		$slug  = get_post_field( 'post_name', $mb_id );
		$terms = get_the_terms( $mb_id, 'rs_book_genre' );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$slug_to_genres[ $slug ] = wp_list_pluck( $terms, 'name' );
		}
	}
	restore_current_blog();

	$sub_books = get_posts(
		array(
			'post_type'      => 'rs_book',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		)
	);

	foreach ( $sub_books as $sb ) {
		if ( ! empty( $slug_to_genres[ $sb->post_name ] ) ) {
			$en_genres = array();
			foreach ( $slug_to_genres[ $sb->post_name ] as $bg ) {
				$en_genres[] = isset( $genre_map[ $bg ] ) ? $genre_map[ $bg ] : $bg;
			}
			wp_set_object_terms( $sb->ID, $en_genres, 'rs_book_genre' );
		}
	}

	update_option( 'rs_book_genres_synced_v2', 1 );
}
add_action( 'init', 'rs_sync_book_genres_from_main_site', 20 );

/**
 * Add meta boxes for book details: author, translator, read status.
 */
function rs_book_meta_boxes() {
	add_meta_box(
		'rs_book_details',
		'Book Details',
		'rs_book_details_html',
		'rs_book',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'rs_book_meta_boxes' );

/**
 * Render the Book Details meta box.
 *
 * @param WP_Post $post Current post.
 */
function rs_book_details_html( $post ) {
	wp_nonce_field( 'rs_book_save', 'rs_book_nonce' );

	$author     = get_post_meta( $post->ID, '_rs_book_author', true );
	$translator = get_post_meta( $post->ID, '_rs_book_translator', true );
	$is_read    = get_post_meta( $post->ID, '_rs_book_read', true );

	/* Fetch all existing unique authors to build the dropdown */
	global $wpdb;
	$existing_authors = $wpdb->get_col( "
		SELECT DISTINCT meta_value 
		FROM {$wpdb->postmeta} 
		WHERE meta_key = '_rs_book_author' AND meta_value != '' 
		ORDER BY meta_value ASC
	" );
	?>
	<table class="form-table">
		<tr>
			<th><label for="rs_book_author">Author</label></th>
			<td>
				<div id="rs_author_select_wrap">
					<select id="rs_book_author_select" name="rs_book_author_select" class="regular-text">
						<option value="">-- Select Author --</option>
						<?php foreach ( $existing_authors as $a ) : ?>
							<option value="<?php echo esc_attr( $a ); ?>" <?php selected( $author, $a ); ?>>
								<?php echo esc_html( $a ); ?>
							</option>
						<?php endforeach; ?>
						<?php 
						/* If the current author is somehow not in the DB list yet, add it so it's selected */
						if ( $author && ! in_array( $author, $existing_authors, true ) ) : ?>
							<option value="<?php echo esc_attr( $author ); ?>" selected>
								<?php echo esc_html( $author ); ?>
							</option>
						<?php endif; ?>
					</select>
					<a href="#" id="rs_add_author_btn" style="margin-left: 10px; text-decoration: none;">+ Add new Author</a>
				</div>
				
				<div id="rs_author_new_wrap" style="display: none;">
					<input type="text" id="rs_book_author_new" name="rs_book_author_new" value="" class="regular-text" placeholder="Type new author name" />
					<a href="#" id="rs_cancel_author_btn" style="margin-left: 10px; color: #d63638; text-decoration: none;">Cancel</a>
				</div>

				<script>
				document.addEventListener('DOMContentLoaded', function() {
					var selectWrap = document.getElementById('rs_author_select_wrap');
					var newWrap    = document.getElementById('rs_author_new_wrap');
					var select     = document.getElementById('rs_book_author_select');
					var input      = document.getElementById('rs_book_author_new');

					document.getElementById('rs_add_author_btn').addEventListener('click', function(e) {
						e.preventDefault();
						selectWrap.style.display = 'none';
						newWrap.style.display = 'block';
						select.value = ''; /* Clear dropdown selection so new input takes precedence */
						input.focus();
					});

					document.getElementById('rs_cancel_author_btn').addEventListener('click', function(e) {
						e.preventDefault();
						newWrap.style.display = 'none';
						selectWrap.style.display = 'block';
						input.value = '';
					});
				});
				</script>
			</td>
		</tr>
		<tr>
			<th><label for="rs_book_translator">Translator / Editor</label></th>
			<td><input type="text" id="rs_book_translator" name="rs_book_translator"
			           value="<?php echo esc_attr( $translator ); ?>"
			           class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="rs_book_read">Read</label></th>
			<td><label>
				<input type="checkbox" id="rs_book_read" name="rs_book_read"
				       value="1" <?php checked( $is_read, '1' ); ?> />
				Finished reading this book
			</label></td>
		</tr>
	</table>
	<?php
}

/**
 * Save book meta on post save.
 *
 * @param int $post_id Post ID.
 */
function rs_book_save_meta( $post_id ) {
	if ( ! isset( $_POST['rs_book_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['rs_book_nonce'], 'rs_book_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* Author: Check the new input first, fallback to dropdown */
	$final_author = '';
	if ( ! empty( trim( $_POST['rs_book_author_new'] ?? '' ) ) ) {
		$final_author = sanitize_text_field( $_POST['rs_book_author_new'] );
	} elseif ( ! empty( trim( $_POST['rs_book_author_select'] ?? '' ) ) ) {
		$final_author = sanitize_text_field( $_POST['rs_book_author_select'] );
	}
	update_post_meta( $post_id, '_rs_book_author', $final_author );

	if ( isset( $_POST['rs_book_translator'] ) ) {
		update_post_meta( $post_id, '_rs_book_translator',
			sanitize_text_field( $_POST['rs_book_translator'] ) );
	}

	update_post_meta( $post_id, '_rs_book_read',
		! empty( $_POST['rs_book_read'] ) ? '1' : '' );
}
add_action( 'save_post_rs_book', 'rs_book_save_meta' );

/**
 * Add custom columns to the Books admin list table.
 *
 * @param array $columns Default columns.
 * @return array
 */
function rs_book_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['rs_author'] = 'Author';
			/* Genre column is auto-added by show_admin_column on the taxonomy. */
			$new['rs_read']   = 'Read';
		}
	}
	return $new;
}
add_filter( 'manage_rs_book_posts_columns', 'rs_book_admin_columns' );

/**
 * Fill the custom columns.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function rs_book_admin_column_data( $column, $post_id ) {
	if ( 'rs_author' === $column ) {
		$author = get_post_meta( $post_id, '_rs_book_author', true );
		$translator = get_post_meta( $post_id, '_rs_book_translator', true );
		echo esc_html( $author );
		if ( $translator ) {
			echo ' <small style="opacity:.6;">(' . esc_html( $translator ) . ')</small>';
		}
	} elseif ( 'rs_read' === $column ) {
		echo get_post_meta( $post_id, '_rs_book_read', true ) ? '✓' : '—';
	}
}
add_action( 'manage_rs_book_posts_custom_column', 'rs_book_admin_column_data', 10, 2 );

/**
 * Make the Author column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function rs_book_sortable_columns( $columns ) {
	$columns['rs_author'] = 'rs_author';
	return $columns;
}
add_filter( 'manage_edit-rs_book_sortable_columns', 'rs_book_sortable_columns' );

/**
 * Handle sorting by the author meta key.
 *
 * @param WP_Query $query The query.
 */
function rs_book_sort_by_author( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'rs_author' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_rs_book_author' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'rs_book_sort_by_author' );

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
			$theme_uri . '/style.css?ver=' . RS_VERSION,
			$theme_uri . '/assets/app.js?ver=' . RS_VERSION,
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
