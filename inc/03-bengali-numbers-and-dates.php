<?php
/**
 * Bengali numbers and dates.
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
