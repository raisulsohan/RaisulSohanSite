<?php
/**
 * Book list.
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
	if ( ! isset( $_POST['rs_book_nonce'] ) || ! is_string( $_POST['rs_book_nonce'] ) ||
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rs_book_nonce'] ) ), 'rs_book_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* One field, read the same way wherever it came from: unslashed before
	   it is trimmed, because WordPress adds the slashes on the way in and
	   trim() would otherwise be looking at a backslash rather than a name. */
	$field = function ( $key ) {
		if ( ! isset( $_POST[ $key ] ) || ! is_string( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by the caller above.
			return '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- As above.
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
	};

	/* Author: check the new input first, fall back to the dropdown. */
	$new_author    = trim( $field( 'rs_book_author_new' ) );
	$picked_author = trim( $field( 'rs_book_author_select' ) );
	$final_author  = '' !== $new_author ? $new_author : $picked_author;

	update_post_meta( $post_id, '_rs_book_author', $final_author );

	if ( isset( $_POST['rs_book_translator'] ) ) {
		update_post_meta( $post_id, '_rs_book_translator', $field( 'rs_book_translator' ) );
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
