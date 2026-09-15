<?php
/**
 * Text helpers.
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

	// Self-healing: if on English site but the cached summary contains Bengali script (from multisite import/cloner), invalidate it!
	if ( rs_is_en() && preg_match( '/[\x{0980}-\x{09FF}]/u', (string) $text ) ) {
		$text = '';
	}

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
 * One-time cleanup: flush cached summaries on English subsite to purge stale imported Bengali summaries.
 */
function rs_flush_subsite_summaries() {
	if ( ! rs_is_en() ) {
		return;
	}
	$version_key = 'rs_summary_flush_ver';
	if ( get_option( $version_key ) === RS_VERSION ) {
		return;
	}
	delete_post_meta_by_key( RS_SUMMARY_KEY );
	update_option( $version_key, RS_VERSION );
}
add_action( 'init', 'rs_flush_subsite_summaries' );

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
	foreach ( rs_bn_variants( $needle ) as $variant ) {
		$pos = rs_stripos_one( $haystack, $variant );

		if ( false !== $pos ) {
			return $pos;
		}
	}

	return false;
}

/**
 * The spellings a Bengali search term is likely to have been typed in.
 *
 * য়, ড় and ঢ় exist twice in Unicode: as one precomposed letter and as
 * the base letter plus a nukta, and keyboards disagree about which one
 * they produce. Many writers also drop the nukta altogether, so the
 * bare form is tried too. Joiners (ZWJ / ZWNJ) are invisible and get
 * stripped. The first entry is always the term as typed.
 *
 * @param string $term Search term.
 * @return string[] Unique spellings, the original first.
 */
function rs_bn_variants( $term ) {
	$pre  = array( "\u{09DF}", "\u{09DC}", "\u{09DD}" );
	$dec  = array( "\u{09AF}\u{09BC}", "\u{09A1}\u{09BC}", "\u{09A2}\u{09BC}" );
	$flat = str_replace( array( "\u{200C}", "\u{200D}" ), '', $term );

	$decomposed  = str_replace( $pre, $dec, $flat );
	$precomposed = str_replace( $dec, $pre, $decomposed );
	$bare        = str_replace( "\u{09BC}", '', $decomposed );

	return array_values( array_unique( array( $term, $flat, $decomposed, $precomposed, $bare ) ) );
}

/**
 * One spelling, one position. The multibyte branch and the byte-offset
 * fallback for hosts without mbstring.
 *
 * @param string $haystack Text to search.
 * @param string $needle   Term to find.
 * @return int|false
 */
function rs_stripos_one( $haystack, $needle ) {
	if ( '' === $needle ) {
		return false;
	}

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
