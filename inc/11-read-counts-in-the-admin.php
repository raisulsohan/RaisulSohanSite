<?php
/**
 * Read counts in the admin.
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
