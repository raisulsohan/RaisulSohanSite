<?php
/**
 * The other language.
 *
 * The two editions are two sites in one network, and a story written in both
 * exists twice: once here, once there, with its own id on each. This file is
 * how the two copies find each other — for the reader, who is offered the
 * translation beside the date, and for a search engine, which is told about
 * it in an hreflang pair.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The two editions, from where this request is standing.
 *
 * Null when there is no pair to speak of: a single site, a network without
 * an /en/ site, or some third site that is not an edition of anything.
 *
 * @return array|null { here, other, lang } — lang is the other edition's.
 */
function rs_edition_pair() {
	static $cache = array();

	if ( ! is_multisite() ) {
		return null;
	}

	$here = (int) get_current_blog_id();

	/* Keyed by site, because a switch_to_blog() moves what "here" means and
	   this static outlives the switch. */
	if ( array_key_exists( $here, $cache ) ) {
		return $cache[ $here ];
	}

	$cache[ $here ] = null;

	$en_sites = get_sites(
		array(
			'path'   => '/en/',
			'number' => 1,
		)
	);

	if ( empty( $en_sites ) ) {
		return null;
	}

	$en_id   = (int) $en_sites[0]->blog_id;
	$main_id = (int) get_main_site_id();

	if ( $here !== $en_id && $here !== $main_id ) {
		return null;
	}

	$other = ( $here === $en_id ) ? $main_id : $en_id;

	$cache[ $here ] = array(
		'here'  => $here,
		'other' => $other,
		'lang'  => ( $other === $en_id ) ? 'en' : 'bn',
	);

	return $cache[ $here ];
}

/**
 * The same story in the other edition, or null.
 *
 * Three ways of finding it, in order of how much they can be trusted:
 *
 * 1. The link saved on the post. Somebody said these two are the same piece,
 *    so nothing below gets a say.
 * 2. The same slug. The back catalogue was cloned into the English site, so
 *    most of it still carries the Bengali slug on both sides.
 * 3. The same id *and* the same publish time, to the second. Also the cloned
 *    set — the ones whose English slug has since been rewritten for search.
 *    Two unrelated stories sharing both an id and a timestamp is not a thing
 *    that happens, and the timestamp is what makes this safe: after the
 *    clone both sites kept numbering on their own, so ids alone would pair
 *    whatever happened to be next in each queue.
 *
 * Anything published separately after the clone matches none of these and
 * has to be linked by hand, which is what the box on the post editor is for.
 *
 * @param int $post_id Post, or 0 for the current one.
 * @return array|null { id, url, rest, lang, title, how }
 */
function rs_post_twin( $post_id = 0 ) {
	static $cache = array();

	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		return null;
	}

	$pair = rs_edition_pair();

	if ( ! $pair ) {
		return null;
	}

	$key = $pair['here'] . ':' . $post_id;

	if ( array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}

	$cache[ $key ] = null;

	/* Read on this side, before the switch moves what get_post() means. */
	$linked = (int) get_post_meta( $post_id, '_rs_twin', true );
	$here   = get_post( $post_id );
	$slug   = $here ? (string) $here->post_name : '';
	$when   = $here ? (string) $here->post_date_gmt : '';

	switch_to_blog( $pair['other'] );

	$twin = null;
	$how  = '';

	if ( $linked ) {
		$found = get_post( $linked );

		if ( $found && 'post' === $found->post_type ) {
			$twin = $found;
			$how  = 'linked';
		}
	}

	if ( ! $twin && '' !== $slug ) {
		$found = get_page_by_path( $slug, OBJECT, 'post' );

		if ( $found ) {
			$twin = $found;
			$how  = 'slug';
		}
	}

	if ( ! $twin && '' !== $when ) {
		$found = get_post( $post_id );

		if ( $found && 'post' === $found->post_type && $found->post_date_gmt === $when ) {
			$twin = $found;
			$how  = 'clone';
		}
	}

	$answer = null;

	/* Published and unlocked, or there is nothing to offer: a draft
	   translation behind a link is worse than no link. */
	if ( $twin && 'publish' === $twin->post_status && '' === (string) $twin->post_password ) {
		$answer = array(
			'id'    => (int) $twin->ID,
			'url'   => get_permalink( $twin ),
			'rest'  => esc_url_raw( rest_url( 'rs/v1/' ) ),
			'lang'  => $pair['lang'],
			'title' => rs_plain_title( $twin ),
			'how'   => $how,
		);
	}

	restore_current_blog();

	$cache[ $key ] = $answer;

	return $answer;
}

/**
 * The button that takes a reader to this same story in the other language.
 *
 * Prints nothing when there is no twin: a button that lands the reader on a
 * front page rather than the piece they were reading is worse than no button
 * at all.
 *
 * The attributes are what app.js needs to fetch the translation from the
 * other edition's REST route and swap it into the reading modal. Without
 * JavaScript it is an ordinary link to an ordinary page, which is what it
 * stays on a story's own page.
 *
 * @param int $post_id Post, or 0 for the current one.
 */
function rs_lang_pill( $post_id = 0 ) {
	$twin = rs_post_twin( $post_id );

	if ( ! $twin ) {
		return;
	}

	$to_en = 'en' === $twin['lang'];
	$label = $to_en ? 'English' : 'বাংলা';
	$title = $to_en ? 'এই লেখাটি ইংরেজিতে পড়ুন' : 'Read this story in Bengali';

	printf(
		'<a class="rs-lang-pill" href="%1$s" hreflang="%2$s" lang="%2$s" rel="alternate"'
			. ' data-rs-lang="%3$d" data-rs-lang-rest="%4$s" data-rs-lang-code="%2$s"'
			. ' title="%5$s" aria-label="%5$s">%6$s<span>%7$s</span></a>',
		esc_url( $twin['url'] ),
		esc_attr( $twin['lang'] ),
		(int) $twin['id'],
		esc_url( $twin['rest'] ),
		esc_attr( $title ),
		wp_kses( rs_icon( 'globe', 13 ), rs_svg_tags() ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped on the line itself.
		esc_html( $label )
	);
}

/* =========================================================================
 * Linking the two by hand
 * ====================================================================== */

/**
 * Every story in the other edition, newest first, as id => label.
 *
 * Read while switched, because a title belongs to the site that holds it.
 * Fifty-odd stories is a list a person can look down, so it is a plain
 * select rather than a search box; at ten times this it would want one.
 *
 * @return array
 */
function rs_other_edition_choices() {
	$pair = rs_edition_pair();

	if ( ! $pair ) {
		return array();
	}

	switch_to_blog( $pair['other'] );

	$posts   = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 300,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$choices = array();

	foreach ( $posts as $item ) {
		$choices[ (int) $item->ID ] = array(
			'title' => rs_plain_title( $item ),
			'when'  => (string) $item->post_date_gmt,
		);
	}

	restore_current_blog();

	return $choices;
}

/**
 * Put the box on the post editor.
 */
function rs_twin_meta_box() {
	if ( ! rs_edition_pair() ) {
		return;
	}

	add_meta_box(
		'rs_twin',
		__( 'The other language', 'raisul-sohan' ),
		'rs_twin_meta_box_html',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'rs_twin_meta_box' );

/**
 * The box itself.
 *
 * @param WP_Post $post Post being edited.
 */
function rs_twin_meta_box_html( $post ) {
	$pair = rs_edition_pair();

	if ( ! $pair ) {
		return;
	}

	wp_nonce_field( 'rs_twin_save', 'rs_twin_nonce' );

	$linked  = (int) get_post_meta( $post->ID, '_rs_twin', true );
	$found   = rs_post_twin( $post->ID );
	$choices = rs_other_edition_choices();
	$other   = 'en' === $pair['lang'] ? __( 'English', 'raisul-sohan' ) : __( 'Bengali', 'raisul-sohan' );

	/*
	 * When nothing is linked and nothing was matched, the story published
	 * nearest in time is the one worth offering: a translation is written
	 * after the piece it translates, usually the same day.
	 */
	$suggest = 0;

	if ( ! $linked && ! $found && $post->post_date_gmt ) {
		$mine = strtotime( $post->post_date_gmt . ' UTC' );
		$best = null;

		foreach ( $choices as $id => $choice ) {
			$gap = abs( strtotime( $choice['when'] . ' UTC' ) - $mine );

			if ( null === $best || $gap < $best ) {
				$best    = $gap;
				$suggest = $id;
			}
		}
	}
	?>
	<p style="margin-top:0;">
		<?php
		if ( $found ) {
			$how = 'linked' === $found['how']
				? __( 'linked by hand', 'raisul-sohan' )
				: ( 'slug' === $found['how']
					? __( 'matched on the address', 'raisul-sohan' )
					: __( 'matched on id and publish time', 'raisul-sohan' ) );

			printf(
				'<strong>%1$s</strong><br><a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a><br><span class="description">%4$s</span>',
				esc_html( $other ),
				esc_url( $found['url'] ),
				esc_html( $found['title'] ),
				esc_html( $how )
			);
		} else {
			printf(
				'<span class="description">%s</span>',
				esc_html__( 'No version in the other language yet.', 'raisul-sohan' )
			);
		}
		?>
	</p>

	<p>
		<label for="rs_twin_id"><?php esc_html_e( 'Link to:', 'raisul-sohan' ); ?></label>
		<select name="rs_twin_id" id="rs_twin_id" style="width:100%;">
			<option value="0"><?php esc_html_e( '— work it out automatically —', 'raisul-sohan' ); ?></option>
			<?php foreach ( $choices as $id => $choice ) : ?>
				<option value="<?php echo (int) $id; ?>" <?php selected( $linked, $id ); ?>>
					<?php
					echo esc_html( rs_shorten( $choice['title'], 60 ) );

					if ( $id === $suggest ) {
						echo ' ' . esc_html__( '(closest in time)', 'raisul-sohan' );
					}
					?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>

	<p class="description">
		<?php
		esc_html_e(
			'Only needed when the two were published separately and share neither an address nor a publish time. Saving writes the link on both sides.',
			'raisul-sohan'
		);
		?>
	</p>
	<?php
}

/**
 * Save the link, on both sides.
 *
 * A pairing that only one of the two knows about is worse than none: the
 * reader would be offered the translation going one way and not coming back.
 *
 * @param int $post_id Post being saved.
 */
function rs_twin_save( $post_id ) {
	if ( ! isset( $_POST['rs_twin_nonce'] ) || ! is_string( $_POST['rs_twin_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rs_twin_nonce'] ) ), 'rs_twin_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$pair = rs_edition_pair();

	if ( ! $pair ) {
		return;
	}

	$was = (int) get_post_meta( $post_id, '_rs_twin', true );
	$now = isset( $_POST['rs_twin_id'] ) ? absint( $_POST['rs_twin_id'] ) : 0;

	if ( $was === $now ) {
		return;
	}

	if ( $now ) {
		update_post_meta( $post_id, '_rs_twin', $now );
	} else {
		delete_post_meta( $post_id, '_rs_twin' );
	}

	switch_to_blog( $pair['other'] );

	/* The story this one used to point at should stop pointing back. */
	if ( $was && (int) get_post_meta( $was, '_rs_twin', true ) === (int) $post_id ) {
		delete_post_meta( $was, '_rs_twin' );
	}

	if ( $now && get_post( $now ) ) {
		update_post_meta( $now, '_rs_twin', (int) $post_id );
	}

	restore_current_blog();
}
add_action( 'save_post_post', 'rs_twin_save' );
