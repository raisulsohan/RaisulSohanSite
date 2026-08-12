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
define( 'RS_VERSION', '2.17.0' );

/** Rows per page before anyone changes it on the settings screen, and the
    value fallen back to if the field is ever emptied. */
define( 'RS_PER_PAGE', 10 );

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
			'rest'    => esc_url_raw( rest_url( 'rs/v1/' ) ),
			'home'    => esc_url_raw( home_url( '/' ) ),
			'total'   => rs_published_count(),
			'phrases' => rs_phrases(),
			'email'   => rs_option( 'rs_email' ),
			'siteName'=> get_bloginfo( 'name' ),
			/* Which post this page is, for the read count. Zero everywhere
			   else, and app.js counts nothing when it is zero. */
			'postId'  => is_singular( 'post' ) ? get_queried_object_id() : 0,
			/* Base URL for the modal's edit link, empty for readers. The
			   capability is checked here, in a normally authenticated page
			   request; the REST call carries no nonce, so current_user_can()
			   would see a logged out user inside the endpoint. WordPress
			   still gates the edit screen itself, so this only decides
			   whether the button is worth showing. */
			'editBase' => current_user_can( 'edit_posts' )
				? admin_url( 'post.php?action=edit&post=' )
				: '',
			'strings' => array(
				'copied'   => __( 'Mail copied!', 'raisul-sohan' ),
				'copyFail' => __( 'কপি হয়নি', 'raisul-sohan' ),
				'linkCopy' => __( 'লিঙ্ক কপি হয়েছে', 'raisul-sohan' ),
				'noResult' => __( 'কোনো ফলাফল পাওয়া যায়নি', 'raisul-sohan' ),
				'hint'     => __( 'শিরোনাম বা লেখার অংশ লিখুন', 'raisul-sohan' ),
				'results'  => __( 'টি ফলাফল', 'raisul-sohan' ),
				'loading'  => __( 'আসছে...', 'raisul-sohan' ),
				'error'    => __( 'লেখাটি আনা যায়নি', 'raisul-sohan' ),
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
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( get_template_directory_uri() . '/assets/fonts/noto-serif-bengali-bengali.woff2' )
	);
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
 * Format a post date as "১৫ অক্টো ২০২৬".
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rs_bn_date( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
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
		'rs_home_per_page'    => RS_PER_PAGE,
		'rs_archive_per_page' => RS_PER_PAGE,
		'rs_verify'   => 'UGbwgVSquWFpv2qZcQYRQzJSyEFaryG9PHAIpY2ZsYA',
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

	return $id && wp_get_attachment_image_src( $id, 'large' ) ? $id : 0;
}

/**
 * Footer line with the year substituted in Bengali digits.
 *
 * @return string
 */
function rs_footer_text() {
	return str_replace( '{year}', rs_bn_digits( gmdate( 'Y' ) ), (string) rs_option( 'rs_footer' ) );
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

	$cached = array(
		'title'   => 'আমার সম্পর্কে',
		'content' => '<p>আমি রাইসুল সোহান। আমার সাহিত্যচর্চার মূল মাধ্যম গল্প। সমকালীন মানুষের জীবন, সম্পর্ক, স্মৃতি ও শহরের নীরব রূপান্তর আমার লেখার আগ্রহের জায়গা। জীবিকার জন্য মোশন গ্রাফিক্স ও অ্যানিমেশন নিয়ে কাজ করলেও, আমার কাছে দুটি মাধ্যমই শেষ পর্যন্ত গল্প বলার ভিন্ন ভিন্ন উপায়।</p>',
	);

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
	);
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
			__( 'Heading image', 'raisul-sohan' ),
			__( 'Stands in place of the heading text at the top of the list. A transparent PNG sits on the page rather than in a box of its own — but remember the page has a dark mode and a colour readers can change, so a picture drawn for one background may disappear on another. Shown at up to 120 pixels tall.', 'raisul-sohan' ),
		),
		'rs_og_image'   => array(
			__( 'Share image', 'raisul-sohan' ),
			__( 'Used on the share card of posts that have no picture of their own. Square, 600x600 or larger — the card crops a wide image to its middle. A picture inside a post wins over this one.', 'raisul-sohan' ),
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
					$rs_id    = (int) rs_option( $rs_key );
					$rs_thumb = $rs_id ? wp_get_attachment_image_src( $rs_id, 'medium' ) : false;
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $rs_image[0] ); ?></th>
						<td>
							<div class="rs-image-preview" id="<?php echo esc_attr( $rs_key ); ?>-preview" style="margin-bottom:.75rem;">
								<?php if ( $rs_thumb ) : ?>
									<img src="<?php echo esc_url( $rs_thumb[0] ); ?>" alt="" style="max-width:200px;height:auto;">
								<?php endif; ?>
							</div>

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

							<p class="description"><?php echo esc_html( $rs_image[1] ); ?></p>
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

	foreach ( array_keys( rs_settings_images() ) as $key ) {
		set_theme_mod( $key, isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : 0 );
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
				var src = img.sizes && img.sizes.medium ? img.sizes.medium.url : img.url;

				$( '#' + key ).val( img.id );
				$( '#' + key + '-preview' ).html(
					$( '<img>' ).attr( 'src', src ).css( { maxWidth: '200px', height: 'auto' } )
				);
			} );
		}

		frames[ key ].open();
	} );

	$( document ).on( 'click', '[data-rs-clear]', function () {
		var key = $( this ).attr( 'data-rs-clear' );

		$( '#' + key ).val( '' );
		$( '#' + key + '-preview' ).empty();
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
		'left'     => '<path d="m15 18-6-6 6-6"/>',
		'right'    => '<path d="m9 18 6-6-6-6"/>',
		'undo'     => '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>',
		'edit'     => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
		'moon'     => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
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
		<p class="rs-share__label">শেয়ার করুন</p>
		<div class="rs-share__row">
			<button class="rs-share__btn" type="button" data-rs-copy="<?php echo esc_attr( $url ); ?>">
				<?php echo wp_kses( rs_icon( 'copy', 14 ), rs_svg_tags() ); ?>
				লিঙ্ক কপি
			</button>
		</div>
	</div>
	<?php
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
	$term = rs_primary_category( $post );

	rs_suggestions( rs_related( $post ), 'আরও ' . ( $term ? $term->name : 'লেখা' ) );
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
	?>
	<nav class="rs-pagination" aria-label="<?php esc_attr_e( 'পাতা', 'raisul-sohan' ); ?>">
		<?php
		rs_pagination_step( $current - 1, $current > 1, 'left', 'prev', __( 'আগের পাতা', 'raisul-sohan' ) );

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

			printf(
				'<a class="rs-pagination__num" href="%s" aria-label="%s">%s</a>',
				esc_url( rs_page_url( $slot ) ),
				/* translators: %s: page number in Bengali digits. */
				esc_attr( sprintf( __( '%s নম্বর পাতা', 'raisul-sohan' ), $digits ) ),
				esc_html( $digits )
			);
		}

		rs_pagination_step( $current + 1, $current < $total, 'right', 'next', __( 'পরের পাতা', 'raisul-sohan' ) );
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
	?>
	<div class="rs-wrap">
		<p class="rs-post-count">
			<?php
			/* No line breaks around the number: টি is a suffix, and any
			   whitespace here would render as a space inside the word. */
			echo esc_html( $before );
			?><span class="rs-post-count__number"><?php echo esc_html( rs_bn_digits( $count ) ); ?></span><?php
			echo esc_html( $after );
			?>
			<a class="rs-post-count__any"
				href="<?php echo esc_url( rs_random_url() ); ?>"
				data-rs-random="<?php echo (int) rs_random_cat(); ?>">যেকোনো একটা</a>
		</p>
	</div>
	<?php
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
					</article>
				<?php endwhile; ?>
			</div>

			<?php rs_pagination(); ?>

		<?php else : ?>

			<div class="rs-notice">
				<?php if ( is_search() ) : ?>
					<h2>কিছু পাওয়া যায়নি</h2>
					<p>অন্য শব্দ দিয়ে খুঁজে দেখুন।</p>
				<?php else : ?>
					<h2>এখনো কোনো লেখা নেই</h2>
					<p>প্রথম লেখাটা প্রকাশ করলে এখানে দেখা যাবে।</p>
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
	$term = trim( (string) $request->get_param( 'q' ) );

	if ( mb_strlen( $term, 'UTF-8' ) < 1 ) {
		return rest_ensure_response( array( 'items' => array(), 'total' => 0 ) );
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

	if ( $viewer && user_can( $viewer, 'edit_posts' ) ) {
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
		return array(
			'title'       => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
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
}
add_action( 'wp_head', 'rs_seo_meta', 1 );

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
			'@context'         => 'https://schema.org',
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
			'@context'    => 'https://schema.org',
			'@type'       => 'WebSite',
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'url'         => home_url( '/' ),
			'inLanguage'  => get_bloginfo( 'language' ),
		);
	}

	if ( ! $data ) {
		return;
	}

	/* Unicode is left alone so the Bengali stays readable in view source,
	   but slashes keep their escaping: that is what stops a "</script>" in
	   a title from closing this block early. */
	printf(
		"\n<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
