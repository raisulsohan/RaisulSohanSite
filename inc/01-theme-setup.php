<?php
/**
 * Theme setup.
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
 * 1. Theme setup
 * ====================================================================== */

/**
 * Register theme supports.
 */
function rs_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );

	/* The size social cards are built from; see rs_share_image(). */
	add_image_size( 'rs-share', 1200, 630, true );

	/*
	 * The heading banner, cut to the band it is actually shown in.
	 *
	 * CSS gives the banner aspect-ratio 1600/300 and object-fit: cover, so
	 * whatever is handed to it gets cropped to that shape on screen — but
	 * the file still arrives whole. The "large" cut was 1024 by 585 and a
	 * hundred and twenty kilobytes, of which the reader saw a 1024 by 192
	 * strip. Cropping on the server instead means the bytes and the picture
	 * are the same shape. See rs_render_hero_image_html(), which falls back
	 * to "large" for a banner uploaded before this size existed.
	 */
	add_image_size( 'rs-hero', 1600, 300, true );

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
 * The path of the request, relative to this site's own root.
 *
 * The site's base is taken off the front, so a sub site at /en/ and an
 * install in a sub directory both answer "portfolio" for their portfolio
 * page rather than "en/portfolio" or "blog/portfolio". Everything that
 * matches a path against a fixed one reads it through here, which is what
 * lets those patterns be anchored at both ends.
 *
 * @return string Path without the slashes at either end.
 */
function rs_request_path() {
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only ever matched against fixed patterns below.
	$path = trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' );
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

	if ( '' !== $base && 0 === stripos( $path . '/', $base . '/' ) ) {
		$path = ltrim( substr( $path, strlen( $base ) ), '/' );
	}

	return $path;
}

/**
 * Whether a path is the portfolio, and which project it names.
 *
 * Anchored at both ends on purpose. The pattern this replaces was
 * "(?:^|/)portfolio/?$", which matched the tail of *any* address —
 * /category/portfolio, /2019/07/portfolio — and the template filter that
 * read it then cancelled the 404 and answered 200, so the portfolio page
 * existed at endlessly many addresses, none of them carrying a canonical
 * tag. rs_request_path() has already taken the site's own base off the
 * front, so nothing but the portfolio's own path can match here.
 *
 * @param string|null $req Path to test; the current request when null.
 * @return array|null { slug } — slug is '' for the portfolio itself.
 */
function rs_portfolio_path( $req = null ) {
	$req = null === $req ? rs_request_path() : trim( (string) $req, '/' );

	if ( ! preg_match( '~^portfolio(?:/([^/]+))?$~i', $req, $found ) ) {
		return null;
	}

	return array( 'slug' => isset( $found[1] ) ? $found[1] : '' );
}

/* The two editions and how a story finds its twin in the other one now live
   in inc/15-the-other-language.php, which also holds the box on the post
   editor that links a pair the site cannot work out for itself. */

/**
 * Language switcher data for header toggle.
 *
 * @return array
 */
function rs_lang_switcher_data() {
	$here         = rs_portfolio_path();
	$is_portfolio = null !== $here;
	/* A project page switches to the same project in the other language. */
	$portfolio    = '/portfolio/' . ( $is_portfolio && '' !== $here['slug'] ? $here['slug'] . '/' : '' );

	if ( rs_is_en() ) {
		$main_id = function_exists( 'get_main_site_id' ) ? get_main_site_id() : 1;
		$path    = $is_portfolio ? $portfolio : '/';
		$url     = is_multisite() ? get_home_url( $main_id, $path ) : home_url( $path );
		$label   = 'BN';
		$title   = 'বাংলায় পড়ুন';
	} else {
		$path = $is_portfolio ? '/en' . $portfolio : '/en/';
		$url  = home_url( $path );
		if ( is_multisite() ) {
			$sites = get_sites( array( 'path' => '/en/', 'number' => 1 ) );
			if ( ! empty( $sites ) ) {
				$url = get_home_url( $sites[0]->blog_id, $is_portfolio ? $portfolio : '/' );
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
 * Fix Multisite Main Site Permalinks (Remove unwanted /blog prefix)
 * ====================================================================== */

/**
 * Remove /blog from category_base, tag_base and permalink_structure on main site.
 */
if ( is_multisite() ) {
	foreach ( array( 'permalink_structure', 'category_base', 'tag_base' ) as $option ) {
		add_filter( "option_{$option}", function( $value ) {
			if ( is_main_site() && is_string( $value ) ) {
				return preg_replace( '|^/?blog|', '', $value );
			}
			return $value;
		} );
	}
}

/**
 * Ensure category links on main site never include /blog/.
 */
add_filter( 'term_link', function( $termlink, $term, $taxonomy ) {
	if ( is_multisite() && is_main_site() && 'category' === $taxonomy ) {
		return str_replace( '/blog/category/', '/category/', $termlink );
	}
	return $termlink;
}, 10, 3 );

/**
 * Redirect /blog/* to /* on main site to eliminate 404s and keep URLs clean.
 */
add_action( 'template_redirect', function() {
	if ( ! is_multisite() || ! is_main_site() ) {
		return;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	if ( 0 === strpos( $uri, '/blog/' ) ) {
		$target = home_url( substr( $uri, 5 ) );
		wp_safe_redirect( $target, 301 );
		exit;
	}
} );

/**
 * Rebuild the rewrite rules once per theme version.
 *
 * The option filter above changes the permalink structure at runtime, but
 * the rules WordPress matches requests against live in the rewrite_rules
 * option and are only rebuilt on a flush. Without this, links generated
 * from the filtered structure can point at URLs no stored rule matches,
 * and every post answers 404 while the dashboard looks perfectly healthy.
 */
function rs_flush_rewrite_on_update() {
	if ( get_option( 'rs_rewrite_version' ) === RS_VERSION ) {
		return;
	}

	rs_ensure_pages();
	flush_rewrite_rules( false );
	update_option( 'rs_rewrite_version', RS_VERSION, false );
}
add_action( 'init', 'rs_flush_rewrite_on_update', 99 );

/**
 * Create the pages the theme links to, once, on a site that lacks them.
 *
 * The portfolio used to be served from a virtual URL, which left it
 * without a canonical tag, share tags or structured data. A real page
 * with the same slug gets all of that from the ordinary page code and
 * picks page-portfolio.php up through the template hierarchy. The
 * privacy page is what the footer link points at.
 */
function rs_ensure_pages() {
	$en    = rs_is_en();
	$pages = array(
		'portfolio' => array(
			'title'    => $en ? 'Portfolio' : 'পোর্টফোলিও',
			'content'  => $en
				? 'Video editing, motion design, web development and browser extensions by Raisul Sohan.'
				: 'রাইসুল সোহানের ভিডিও এডিটিং, মোশন ডিজাইন, ওয়েব ডেভেলপমেন্ট ও ব্রাউজার এক্সটেনশনের কাজ।',
			'template' => 'page-portfolio.php',
		),
		'timeline'  => array(
			'title'    => $en ? 'Story timeline' : 'লেখার টাইমলাইন',
			'content'  => $en
				? 'Every story laid out on a timeline: each category a layer, each story a clip.'
				: 'সব লেখা একটা টাইমলাইনে সাজানো: প্রতিটি বিভাগ একটা লেয়ার, প্রতিটি লেখা একটা ক্লিপ।',
			'template' => 'page-timeline.php',
		),
		'privacy'   => array(
			'title'    => $en ? 'Privacy' : 'গোপনীয়তা',
			'content'  => rs_privacy_page_content( $en ),
			'template' => '',
		),
	);

	foreach ( $pages as $slug => $page ) {
		if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
			continue;
		}

		$args = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_name'      => $slug,
			'post_title'     => $page['title'],
			'post_content'   => $page['content'],
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		);

		if ( $page['template'] ) {
			$args['meta_input'] = array( '_wp_page_template' => $page['template'] );
		}

		$id = wp_insert_post( $args );

		if ( 'privacy' === $slug && $id && ! is_wp_error( $id ) && ! get_option( 'wp_page_for_privacy_policy' ) ) {
			update_option( 'wp_page_for_privacy_policy', $id );
		}
	}
}

/**
 * The privacy page, written once here so both sites get the same facts.
 *
 * @param bool $en English site.
 * @return string HTML.
 */
function rs_privacy_page_content( $en ) {
	if ( $en ) {
		return '<p>This site keeps no account of who you are. There is no sign-up, no comment form, no advertising and no third-party analytics. The fonts are served from this site, so no font provider sees your visit.</p>'
			. '<p><strong>What stays in your browser.</strong> Your reading preferences (text size, colour, dark mode, animation), the stories you have opened, where you stopped reading, and your "read later" shelf are stored in your own browser&#8217;s local storage. They never leave your device and you can clear them at any time from your browser&#8217;s site settings.</p>'
			. '<p><strong>What the site counts.</strong> Each story shows how many times it has been read. When you open a story, your browser sends a single request that adds one to that number. No IP address, name or identifier is stored with it.</p>'
			. '<p><strong>Copying text.</strong> If you copy more than a sentence from a story, the author&#8217;s name and a link back are added to the clipboard so the source travels with the words.</p>'
			. '<p><strong>Links to other sites.</strong> Portfolio items may link to YouTube, GitHub or other services, which have their own privacy policies.</p>'
			. '<p>Questions: use the email icon in the header.</p>';
	}

	return '<p>এই সাইট আপনার পরিচয়ের কোনো হিসাব রাখে না। এখানে সাইন-আপ নেই, মন্তব্যের ফর্ম নেই, বিজ্ঞাপন নেই, বাইরের কোনো অ্যানালিটিক্স নেই। ফন্টগুলো এই সাইট থেকেই আসে, তাই কোনো ফন্ট-সেবাদাতা আপনার আসা টের পায় না।</p>'
		. '<p><strong>যা আপনার ব্রাউজারে থাকে।</strong> পড়ার পছন্দ (লেখার আকার, রং, ডার্ক মোড, অ্যানিমেশন), কোন লেখাগুলো খুলেছেন, কোথায় থেমেছিলেন, আর &#8220;পরে পড়ব&#8221; তালিকা, এসব আপনার ব্রাউজারের local storage-এ থাকে। এগুলো কখনো আপনার ডিভাইস ছেড়ে যায় না; ব্রাউজারের সাইট সেটিংস থেকে যেকোনো সময় মুছে ফেলতে পারেন।</p>'
		. '<p><strong>সাইট যা গোনে।</strong> প্রতিটি লেখায় দেখানো হয় কতবার পড়া হয়েছে। লেখা খুললে আপনার ব্রাউজার একটা অনুরোধ পাঠায়, যা ওই সংখ্যায় এক যোগ করে। এর সাথে কোনো IP ঠিকানা, নাম বা পরিচয়সূচক কিছু রাখা হয় না।</p>'
		. '<p><strong>লেখা কপি করা।</strong> কোনো লেখা থেকে এক বাক্যের বেশি কপি করলে লেখকের নাম আর লিংক ক্লিপবোর্ডে যুক্ত হয়, যাতে উৎসটা কথার সাথে যায়।</p>'
		. '<p><strong>অন্য সাইটের লিংক।</strong> পোর্টফোলিওর কিছু লিংক YouTube, GitHub বা অন্য সেবায় নিয়ে যায়; তাদের নিজস্ব গোপনীয়তা নীতি আছে।</p>'
		. '<p>প্রশ্ন থাকলে হেডারের মেইল আইকনে ক্লিক করুন।</p>';
}

/**
 * Send the old date based post URLs to the post's current permalink.
 *
 * Those URLs sat in the sitemap, the feed and shared links for a long
 * while. WordPress parses them as a page path rather than a post name,
 * so it cannot guess the post on its own and serves a 404 instead.
 */
function rs_redirect_legacy_post_urls() {
	if ( ! is_404() ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only matched against a pattern below.

	if ( ! $path || ! preg_match( '#/(\d{4})/(\d{1,2})/(\d{1,2})/([^/]+)/?$#', $path, $m ) ) {
		return;
	}

	$found = get_page_by_path( rawurldecode( $m[4] ), OBJECT, 'post' );

	if ( ! $found || 'publish' !== $found->post_status ) {
		return;
	}

	$target = get_permalink( $found );

	if ( $target && untrailingslashit( (string) wp_parse_url( $target, PHP_URL_PATH ) ) !== untrailingslashit( $path ) ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'rs_redirect_legacy_post_urls' );
