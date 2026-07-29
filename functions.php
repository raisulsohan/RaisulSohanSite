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
define( 'RS_VERSION', '2.5.0' );

/** How many posts render server side, and how many each lazy batch adds. */
define( 'RS_BATCH', 200 );

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
			'rs_primary' => __( 'প্রধান মেনু (হেডারের নিচে)', 'raisul-sohan' ),
		)
	);

	/* Shows the Classic editor the same serif and justified paragraphs
	   the front end uses, so the writing view is not a surprise. */
	add_editor_style( 'assets/editor.css' );
}
add_action( 'after_setup_theme', 'rs_setup' );

/**
 * Show every post on the front page and on category archives, in batches.
 *
 * Category archives need this as much as the front page does: on the
 * default ten per page the "আরও লেখা" button appears, and it pulls from
 * /rs/v1/list, which is not filtered by category and would append posts
 * from everywhere. Rendering the whole archive server side keeps the
 * button hidden and the list honest.
 *
 * @param WP_Query $query Main query.
 */
function rs_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_home() || $query->is_category() ) {
		$query->set( 'posts_per_page', RS_BATCH );
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
			'batch'   => RS_BATCH,
			'total'   => rs_published_count(),
			'phrases' => rs_phrases(),
			'email'   => rs_option( 'rs_email' ),
			'siteName'=> get_bloginfo( 'name' ),
			/* Lazy loaded rows follow the server rendered ones: no category
			   tag on a category archive, where it would repeat the heading.
			   wp_localize_script stringifies scalars, so this reaches JS as
			   "1" or "" — both behave correctly as a truthiness check. */
			'showCat' => ! is_category(),
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
				'more'     => __( 'আরও লেখা', 'raisul-sohan' ),
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

	$words   = preg_match_all( '/\S+/u', rs_plain_text( $post ) );
	$minutes = max( 1, (int) round( $words / 180 ) );

	return rs_bn_digits( $minutes ) . ' মিনিট';
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

	if ( has_excerpt( $post ) ) {
		$text = trim( wp_strip_all_tags( $post->post_excerpt ) );
	} else {
		$text = rs_plain_text( $post );
	}

	if ( mb_strlen( $text, 'UTF-8' ) > $length ) {
		$text = rtrim( mb_substr( $text, 0, $length, 'UTF-8' ) ) . '…';
	}

	return $text;
}

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
 * 5. Customizer options
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
		'rs_footer'   => '© {year} রাইসুল সোহানের গল্প · সর্বস্বত্ব সংরক্ষিত',
		'rs_about'    => 0,
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
 * Register customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function rs_customize( $wp_customize ) {
	$defaults = rs_defaults();

	$wp_customize->add_section(
		'rs_site',
		array(
			'title'    => __( 'সাইটের সেটিংস', 'raisul-sohan' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'rs_phrases'  => array( __( 'হেডিংয়ের লেখা (কমা দিয়ে আলাদা)', 'raisul-sohan' ), 'text', 'sanitize_text_field' ),
		'rs_email'    => array( __( 'ইমেইল', 'raisul-sohan' ), 'text', 'sanitize_email' ),
		'rs_facebook' => array( __( 'ফেসবুক লিংক', 'raisul-sohan' ), 'url', 'esc_url_raw' ),
		'rs_linkedin' => array( __( 'লিঙ্কডইন লিংক', 'raisul-sohan' ), 'url', 'esc_url_raw' ),
		'rs_footer'   => array( __( 'ফুটারের লেখা ({year} বসালে সাল আসবে)', 'raisul-sohan' ), 'text', 'sanitize_text_field' ),
	);

	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $field[2],
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field[0],
				'section' => 'rs_site',
				'type'    => $field[1],
			)
		);
	}

	$wp_customize->add_setting(
		'rs_about',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		'rs_about',
		array(
			'label'       => __( '"আমার সম্পর্কে" পেজ', 'raisul-sohan' ),
			'description' => __( 'একটা পেজ বেছে দিন, তার লেখাই মডালে দেখাবে।', 'raisul-sohan' ),
			'section'     => 'rs_site',
			'type'        => 'dropdown-pages',
		)
	);
}
add_action( 'customize_register', 'rs_customize' );

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

/* =========================================================================
 * 8. REST endpoints
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
		'/list',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rs_rest_list',
			'permission_callback' => '__return_true',
			'args'                => array(
				'page' => array(
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
			'title'        => get_the_title( $item ),
			'date'         => rs_bn_date( $item ),
			'author'       => get_the_author_meta( 'display_name', $item->post_author ),
			'content'      => $content,
			'link'         => get_permalink( $item ),
			'category'     => rs_category( $item ),
			'categoryLink' => rs_category_link( $item ),
			'readingTime'  => rs_reading_time( $item ),
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
		'title' => get_the_title( $item ),
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
			'title'   => get_the_title( $post ),
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
 * A batch of list rows, for lazy loading beyond the first RS_BATCH posts.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function rs_rest_list( $request ) {
	$page = max( 1, (int) $request->get_param( 'page' ) );

	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => RS_BATCH,
			'paged'                  => $page,
			'has_password'           => false,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			/* Primed in one query so rs_category() below does not hit the
			   database once per post. */
			'update_post_term_cache' => true,
		)
	);

	$items = array();

	foreach ( $query->posts as $post ) {
		$items[] = array(
			'id'       => $post->ID,
			'title'    => get_the_title( $post ),
			'date'     => rs_bn_date( $post ),
			'link'     => get_permalink( $post ),
			'summary'  => rs_summary( $post ),
			'category' => rs_category( $post ),
			'readingTime' => rs_reading_time( $post ),
		);
	}

	return rest_ensure_response(
		array(
			'items'    => $items,
			'page'     => $page,
			'maxPages' => (int) $query->max_num_pages,
		)
	);
}

/* =========================================================================
 * 9. SEO meta
 * ====================================================================== */

/**
 * Basic description and Open Graph tags.
 *
 * Skip this if an SEO plugin such as Yoast or Rank Math is active.
 */
function rs_meta_tags() {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	if ( is_singular( 'post' ) ) {
		$description = rs_summary( get_queried_object_id(), 160 );
		$title       = get_the_title();
		$url         = get_permalink();
		$type        = 'article';
	} elseif ( is_home() || is_front_page() ) {
		$description = get_bloginfo( 'description' );
		$title       = get_bloginfo( 'name' );
		$url         = home_url( '/' );
		$type        = 'website';
	} else {
		return;
	}

	printf(
		"\n<meta name=\"description\" content=\"%s\">\n",
		esc_attr( $description )
	);
	printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $type ) );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
	printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $description ) );
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( get_locale() ) );
	printf( "<meta name=\"twitter:card\" content=\"summary\">\n" );

	if ( is_singular( 'post' ) && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( null, 'large' );
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image ) );
	}
}
add_action( 'wp_head', 'rs_meta_tags', 1 );

/**
 * Add a helpful body class.
 *
 * @param array $classes Body classes.
 * @return array
 */
function rs_body_class( $classes ) {
	$classes[] = 'rs';

	if ( is_home() || is_front_page() ) {
		$classes[] = 'rs-is-list';
	}

	return $classes;
}
add_filter( 'body_class', 'rs_body_class' );

/* =========================================================================
 * 10. Housekeeping
 * ====================================================================== */

/**
 * Keep the emoji script out of the page. Bengali text does not need it.
 */
function rs_trim_head() {
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
