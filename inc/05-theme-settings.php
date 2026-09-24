<?php
/**
 * Theme settings.
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
 * 5. Theme settings
 * ====================================================================== */

/**
 * Option defaults.
 *
 * @return array
 */
function rs_defaults() {
	$en = rs_is_en();

	return array(
		'rs_email'    => 'lettertosohan@gmail.com',
		'rs_facebook' => 'https://www.facebook.com/lettertosohan/',
		'rs_linkedin' => 'https://www.linkedin.com/in/raisulsohan/',
		/* In the language of the site being looked at. A sub site that has
		   never had these set was serving the Bengali wording to English
		   readers, because a default is what rs_option() falls back to. */
		'rs_phrases'  => $en ? 'Where letters take shelter' : 'অক্ষরের আশ্রয়, এখানে গল্প থাকে',
		'rs_brand'    => '',
		'rs_footer'   => $en ? '© {year} Raisul Sohan. All rights reserved.' : '© {year} রাইসুল সোহানের গল্প · সর্বস্বত্ব সংরক্ষিত',
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
/**
 * Which cut of the heading banner to send, and how wide it will be shown.
 *
 * "rs-hero" is the 1600 by 300 crop registered in rs_setup(); a banner
 * uploaded before that size existed has no such file, and "large" is what
 * it has always been served as.
 *
 * The widths matter as much as the cut. The banner sits inside .rs-hero,
 * which is at most --rs-wrap (48rem) wide and carries a gutter of
 * --rs-gutter on each side, so it is never wider than 720px on a desktop
 * and is the viewport less 3rem on a phone. The old value said 100vw
 * there, three rem more than the truth, which on a dense screen was enough
 * to tip the browser into fetching the next cut up.
 *
 * @param int $id Attachment.
 * @return array { size, sizes }
 */
function rs_hero_image_cut( $id ) {
	$cropped = wp_get_attachment_image_src( $id, 'rs-hero' );
	$size    = ( $cropped && isset( $cropped[3] ) && $cropped[3] ) ? 'rs-hero' : 'large';

	return array(
		'size'  => $size,
		'sizes' => '(max-width: 48rem) calc(100vw - 3rem), 720px',
	);
}

function rs_render_hero_image_html( $alt = '', $sizes = '', $class = 'rs-hero__image' ) {
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

	$cut = rs_hero_image_cut( $id );

	$html = wp_get_attachment_image(
		$id,
		$cut['size'],
		false,
		array(
			'class'         => $class,
			'alt'           => $alt,
			'sizes'         => $sizes ? $sizes : $cut['sizes'],
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
	$text = trim( (string) rs_option( 'rs_footer' ) );

	if ( '' === $text ) {
		/* The field was emptied on purpose or by accident; either way the
		   default is the one line already written for this language. */
		$defaults = rs_defaults();
		$text     = $defaults['rs_footer'];
	}

	/* wp_date rather than gmdate: for the six hours after midnight in Dhaka
	   the year in UTC is still the old one, and a copyright line that says
	   last year on the first morning of January is the one day anybody looks. */
	return str_replace( '{year}', rs_bn_digits( wp_date( 'Y' ) ), $text );
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
				'content' => rs_defer_images( apply_filters( 'the_content', $page->post_content ) ),
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
				'content' => rs_defer_images( apply_filters( 'the_content', $page->post_content ) ),
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
				'content' => rs_defer_images( apply_filters( 'the_content', $page->post_content ) ),
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

/* =========================================================================
 * Cache headers for the uploads folder
 * ====================================================================== */

/**
 * Where the rules would go.
 *
 * @return string Absolute path, or '' when the uploads folder is unreadable.
 */
function rs_uploads_htaccess() {
	$dir = wp_get_upload_dir();

	if ( empty( $dir['basedir'] ) || ! empty( $dir['error'] ) ) {
		return '';
	}

	return trailingslashit( $dir['basedir'] ) . '.htaccess';
}

/**
 * The rules themselves.
 *
 * The same shape as the theme's own .htaccess, and for the same reason: the
 * server sends an ETag for an uploaded picture and nothing else, so a
 * returning reader asks about every image on every page and is told each
 * time that nothing changed. A year is safe because WordPress gives every
 * upload a name of its own and never writes over one — editing a picture in
 * the media library produces a new file, not a new version of the old one.
 *
 * Both blocks are wrapped in IfModule, so a server without mod_headers or
 * mod_expires skips them rather than refusing the request.
 *
 * @return string[] Lines, for insert_with_markers().
 */
function rs_uploads_htaccess_rules() {
	return array(
		'<IfModule mod_headers.c>',
		"\t" . '<FilesMatch "\.(jpe?g|png|gif|webp|avif|svg|ico|woff2?|ttf|mp4|webm)$">',
		"\t\t" . 'Header set Cache-Control "public, max-age=31536000"',
		"\t" . '</FilesMatch>',
		'</IfModule>',
		'',
		'<IfModule mod_expires.c>',
		"\t" . 'ExpiresActive On',
		"\t" . '<FilesMatch "\.(jpe?g|png|gif|webp|avif|svg|ico|woff2?|ttf|mp4|webm)$">',
		"\t\t" . 'ExpiresDefault "access plus 1 year"',
		"\t" . '</FilesMatch>',
		'</IfModule>',
	);
}

/**
 * Whether our block is already in that file.
 *
 * @return bool
 */
function rs_uploads_cache_ready() {
	$file = rs_uploads_htaccess();

	if ( ! $file || ! file_exists( $file ) ) {
		return false;
	}

	$body = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- One small local file, on one admin screen.

	return is_string( $body ) && false !== strpos( $body, '# BEGIN Raisul Sohan cache' );
}

/**
 * Write the block, on request.
 *
 * Never on its own: these rules live outside the theme, and a host that
 * does not allow Header or ExpiresDefault in a .htaccess answers 500 for
 * everything in the folder. That is a thing to do while somebody is
 * watching, so it is a button.
 */
function rs_uploads_cache_write() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'raisul-sohan' ) );
	}

	check_admin_referer( 'rs_uploads_cache' );

	$file = rs_uploads_htaccess();
	$done = 'failed';

	if ( $file ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';

		if ( ! file_exists( $file ) ) {
			/* insert_with_markers() will not create a file it cannot read. */
			@file_put_contents( $file, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions, Generic.PHP.NoSilencedErrors -- Failure is reported back below.
		}

		if ( is_writable( $file ) && insert_with_markers( $file, 'Raisul Sohan cache', rs_uploads_htaccess_rules() ) ) {
			$done = 'written';
		}
	}

	wp_safe_redirect( admin_url( 'themes.php?page=rs-settings&rs_uploads=' . $done ) );
	exit;
}
add_action( 'admin_post_rs_write_uploads_cache', 'rs_uploads_cache_write' );

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

		<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading a flag off our own redirect, acting on nothing.
		$rs_uploads_flag = isset( $_GET['rs_uploads'] ) ? sanitize_key( wp_unslash( $_GET['rs_uploads'] ) ) : '';

		if ( 'written' === $rs_uploads_flag ) :
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Cache rules written. Open a picture from the media library and check that it now answers with a Cache-Control header; if images have stopped loading, delete the .htaccess in wp-content/uploads and tell your host that Header and ExpiresDefault are not permitted there.', 'raisul-sohan' ); ?></p>
			</div>
		<?php elseif ( 'failed' === $rs_uploads_flag ) : ?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Could not write to the uploads folder. Add the rules by hand instead.', 'raisul-sohan' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( ! rs_uploads_cache_ready() ) : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Uploaded pictures are sent without a cache header.', 'raisul-sohan' ); ?></strong>
					<?php esc_html_e( 'A returning reader re-checks every image on every page. The theme\'s own files already carry a year; the uploads folder is outside the theme and needs its own rule.', 'raisul-sohan' ); ?>
				</p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rs_write_uploads_cache' ), 'rs_uploads_cache' ) ); ?>">
						<?php esc_html_e( 'Add the rules for me', 'raisul-sohan' ); ?>
					</a>
					<span class="description"><?php echo esc_html( rs_uploads_htaccess() ); ?></span>
				</p>
				<details>
					<summary><?php esc_html_e( 'Or paste this in yourself', 'raisul-sohan' ); ?></summary>
					<textarea readonly rows="12" style="width:100%;font-family:monospace;"><?php echo esc_textarea( implode( "\n", rs_uploads_htaccess_rules() ) ); ?></textarea>
				</details>
			</div>
		<?php else : ?>
			<div class="notice notice-success">
				<p><?php esc_html_e( 'Uploaded pictures are served with a year-long cache header.', 'raisul-sohan' ); ?></p>
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
