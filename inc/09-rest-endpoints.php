<?php
/**
 * REST endpoints.
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
				'cat'     => array(
					'sanitize_callback' => 'absint',
				),
				'exclude' => array(
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
			'author'       => rs_author_name( $item->post_author ),
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

	/*
	 * Every spelling of the term at once. WordPress's own search takes a
	 * single string, so when the term has nukta or joiner variants the
	 * WHERE clause is replaced with one that ORs all of them.
	 */
	$variants = rs_bn_variants( $term );
	$widen    = function ( $search, $wp_query ) use ( $variants ) {
		global $wpdb;

		if ( count( $variants ) < 2 || ! $wp_query->is_search() ) {
			return $search;
		}

		$parts = array();

		foreach ( $variants as $variant ) {
			$like    = '%' . $wpdb->esc_like( $variant ) . '%';
			$parts[] = $wpdb->prepare( "({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_excerpt LIKE %s OR {$wpdb->posts}.post_content LIKE %s)", $like, $like, $like );
		}

		return ' AND (' . implode( ' OR ', $parts ) . ') ';
	};

	add_filter( 'posts_search', $widen, 10, 2 );

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

	remove_filter( 'posts_search', $widen, 10 );

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
	$cat_id     = (int) $request->get_param( 'cat' );
	$exclude_id = (int) $request->get_param( 'exclude' );
	
	ob_start();
	$post_id = rs_render_featured_post( $cat_id, $exclude_id );
	$html    = ob_get_clean();
	
	return rest_ensure_response(
		array(
			'id'   => $post_id,
			'html' => $html,
		)
	);
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
