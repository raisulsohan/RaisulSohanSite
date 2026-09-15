<?php
/**
 * Share links.
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
 * 7. Share links
 * ====================================================================== */

/**
 * The two ways in to editing a post, for whoever may.
 *
 * Two plain links rather than a menu: there are only two of them, and a
 * menu would be a thing to open before you could choose. Prints nothing
 * for a reader, so the capability check is the whole gate.
 *
 * @param int|WP_Post|null $post Post.
 */
function rs_edit_links( $post = null ) {
	$post = get_post( $post );

	if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}
	?>
	<button class="rs-article__edit" type="button" data-rs-edit>
		<?php echo wp_kses( rs_icon( 'edit', 13 ), rs_svg_tags() ); ?>
		<?php echo esc_html( rs_is_en() ? 'Edit' : 'সম্পাদনা' ); ?>
	</button>
	<a class="rs-article__edit" href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>">
		<?php echo esc_html( rs_is_en() ? 'Dashboard' : 'ড্যাশবোর্ডে' ); ?>
	</a>
	<?php
}

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
		<p class="rs-share__label"><?php echo esc_html( rs_is_en() ? 'Share with others' : 'অন্যদেরও পড়তে দিন' ); ?></p>
		<div class="rs-share__row">
			<?php
			/* Hidden by CSS until app.js finds navigator.share and marks
			   the page. On a phone this opens whatever the reader already
			   sends things with, which is where these links actually go. */
			?>
			<button class="rs-share__btn" type="button"
				data-rs-share="<?php echo esc_attr( $url ); ?>"
				data-rs-share-title="<?php echo esc_attr( rs_plain_title( $post ) ); ?>">
				<?php echo wp_kses( rs_icon( 'share', 14 ), rs_svg_tags() ); ?>
				<?php echo esc_html( rs_is_en() ? 'Share' : 'শেয়ার করুন' ); ?>
			</button>

			<button class="rs-share__btn" type="button" data-rs-copy="<?php echo esc_attr( $url ); ?>">
				<?php echo wp_kses( rs_icon( 'copy', 14 ), rs_svg_tags() ); ?>
				<?php echo esc_html( rs_is_en() ? 'Copy link' : 'লিঙ্ক কপি' ); ?>
			</button>

			<?php
			/* Kept in step with shareHtml() in app.js, which draws this same
			   row inside the modal. Unlike a list row this one has nothing
			   around it to read the title from, so it is told outright. */
			?>
			<button class="rs-share__btn rs-share__btn--save" type="button"
				data-rs-later="<?php echo (int) $post->ID; ?>"
				data-rs-later-url="<?php echo esc_attr( $url ); ?>"
				data-rs-later-title="<?php echo esc_attr( rs_plain_title( $post ) ); ?>"
				data-rs-later-time="<?php echo esc_attr( rs_reading_time( $post ) ); ?>"
				aria-pressed="false">
				<?php echo wp_kses( rs_icon( 'bookmark', 14 ), rs_svg_tags() ); ?>
				<span data-rs-later-text><?php echo esc_html( rs_is_en() ? 'Read later' : 'পরে পড়ব' ); ?></span>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Break an address fragment into the words it is made of.
 *
 * Slugs on this site are romanised — "rituparno-ghosh-cinema" — while the
 * writing they name is Bengali, so searching the posts for those words finds
 * nothing. The slug is the only Latin thing about a post, which makes slug
 * against slug the comparison that can actually work. Percent-encoded
 * Bengali slugs decode back into Bengali words and compare the same way.
 *
 * Anything under three characters is dropped: "er" and "o" sit in half the
 * slugs on the site and would make every post look like a match.
 *
 * @param string $text A slug, or the tail of a requested path.
 * @return string[] Lowercased words, each one unique.
 */
function rs_slug_words( $text ) {
	$text  = strtolower( urldecode( (string) $text ) );
	$parts = preg_split( '/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY );

	if ( ! $parts ) {
		return array();
	}

	$words = array();

	foreach ( $parts as $part ) {
		if ( mb_strlen( $part, 'UTF-8' ) >= 3 ) {
			$words[] = $part;
		}
	}

	return array_values( array_unique( $words ) );
}

/**
 * Posts whose address resembles the one the reader asked for.
 *
 * A link that has gone stale is the commonest way to land on the missing
 * page, and the address itself says what was wanted. WordPress already
 * redirects when the request is the beginning of exactly one slug; this
 * covers what that cannot — a word changed in the middle, a word dropped
 * from the end, two posts it could equally have been. Those are offered
 * rather than redirected to, because a near miss guessed wrongly is worse
 * than a choice.
 *
 * Every published slug is read in one go and the comparison happens here.
 * At this size that is a single small query against two columns; a site of
 * several thousand posts would want to push the work into SQL instead.
 *
 * @param int $limit How many to offer.
 * @return WP_Post[] Best matches first, empty when nothing resembles it.
 */
function rs_missing_matches( $limit = 3 ) {
	global $wp, $wpdb;

	$request = isset( $wp->request ) ? $wp->request : '';

	if ( ! $request ) {
		return array();
	}

	/* The last segment names the thing; the ones before it are the shelf it
	   was expected to be on. A trailing "page" and a number are WordPress's
	   own punctuation and belong to no title, so they are walked back past
	   rather than mistaken for the name. */
	$parts = array_values( array_filter( explode( '/', $request ) ) );

	while ( count( $parts ) > 1 ) {
		$last = $parts[ count( $parts ) - 1 ];

		if ( ! is_numeric( $last ) && 'page' !== $last ) {
			break;
		}

		array_pop( $parts );
	}

	$words = rs_slug_words( end( $parts ) );

	if ( ! $words ) {
		return array();
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Two columns, on a page nobody meant to reach.
	$rows = $wpdb->get_results(
		"SELECT ID, post_name FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'"
	);

	if ( ! $rows ) {
		return array();
	}

	/*
	 * How ordinary each word is, counted from this site rather than from a
	 * list of English stopwords — because the slugs here are romanised
	 * Bengali as often as they are English, and no ready-made list knows
	 * what is unremarkable in either. "the" turns up in nearly a fifth of
	 * these slugs and so tells us nothing; "ghosh" turns up once and tells
	 * us everything.
	 *
	 * The floor keeps the rule from eating a small site alive: with a dozen
	 * posts a share of them is a very small number.
	 */
	$slugs  = array();
	$common = array();

	foreach ( $rows as $row ) {
		$slugs[ (int) $row->ID ] = rs_slug_words( $row->post_name );

		foreach ( $slugs[ (int) $row->ID ] as $word ) {
			$common[ $word ] = isset( $common[ $word ] ) ? $common[ $word ] + 1 : 1;
		}
	}

	$ceiling = max( 4, (int) ceil( count( $rows ) * 0.15 ) );
	$scored  = array();

	foreach ( $slugs as $id => $slug_words ) {
		$shared = array_intersect( $words, $slug_words );
		$strong = array();

		foreach ( $shared as $word ) {
			if ( $common[ $word ] < $ceiling ) {
				$strong[] = $word;
			}
		}

		/* Sharing nothing but a word half the site shares is not a match. */
		if ( ! $strong ) {
			continue;
		}

		$longest = 0;

		foreach ( $strong as $word ) {
			$longest = max( $longest, mb_strlen( $word, 'UTF-8' ) );
		}

		$scored[] = array(
			'id'      => $id,
			'shared'  => count( $strong ),
			'longest' => $longest,
		);
	}

	if ( ! $scored ) {
		return array();
	}

	/* Most words in common first. Where two share the same number, the one
	   sharing the longer word wins: length is the only measure of weight
	   available once the ordinary words are gone. */
	usort(
		$scored,
		function ( $a, $b ) {
			if ( $a['shared'] !== $b['shared'] ) {
				return $b['shared'] - $a['shared'];
			}

			return $b['longest'] - $a['longest'];
		}
	);

	/*
	 * Only the joint best are offered. Padding the answer out to three with
	 * whatever else brushed against a word turns a confident "this one" into
	 * a shrug — and the heading over these says the reader was looking for
	 * one of them.
	 */
	$best  = $scored[0]['shared'];
	$items = array();

	foreach ( $scored as $hit ) {
		if ( $hit['shared'] < $best || count( $items ) >= max( 1, (int) $limit ) ) {
			break;
		}

		$item = get_post( $hit['id'] );

		if ( $item ) {
			$items[] = $item;
		}
	}

	return $items;
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
	$term  = rs_primary_category( $post );
	$label = rs_is_en()
		? ( 'More in ' . ( $term ? $term->name : 'writings' ) )
		: ( 'আরও ' . ( $term ? $term->name : 'লেখা' ) );

	rs_suggestions( rs_related( $post ), $label );
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

/**
 * Bilingual author display name.
 *
 * In WordPress Multisite the user profile table is network-global, so the
 * author display_name is the same across all subsites. On the Bengali site,
 * the author name is rendered in Bengali ("রাইসুল সোহান"), while on the
 * English subsite it remains in English ("Raisul Sohan").
 *
 * @param int|WP_Post|null $post_or_author Post ID, author ID, or null for current author.
 * @return string
 */
function rs_author_name( $post_or_author = null ) {
	$author_id = 0;
	if ( $post_or_author instanceof WP_Post ) {
		$author_id = (int) $post_or_author->post_author;
	} elseif ( is_numeric( $post_or_author ) && (int) $post_or_author > 0 ) {
		$author_id = (int) $post_or_author;
	} else {
		$author_id = (int) get_the_author_meta( 'ID' );
		if ( ! $author_id ) {
			$post = get_post();
			if ( $post ) {
				$author_id = (int) $post->post_author;
			}
		}
	}

	$display_name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';

	if ( ! rs_is_en() ) {
		if ( empty( $display_name ) || 'Raisul Sohan' === $display_name || 'raisulsohan' === strtolower( $display_name ) ) {
			return 'রাইসুল সোহান';
		}
	} else {
		if ( empty( $display_name ) || 'রাইসুল সোহান' === $display_name ) {
			return 'Raisul Sohan';
		}
	}

	return $display_name ? $display_name : ( rs_is_en() ? 'Raisul Sohan' : 'রাইসুল সোহান' );
}

add_filter( 'the_author', function ( $display_name ) {
	if ( ! rs_is_en() ) {
		if ( empty( $display_name ) || 'Raisul Sohan' === $display_name || 'raisulsohan' === strtolower( $display_name ) ) {
			return 'রাইসুল সোহান';
		}
	} else {
		if ( 'রাইসুল সোহান' === $display_name ) {
			return 'Raisul Sohan';
		}
	}
	return $display_name;
} );

add_filter( 'get_the_author_display_name', function ( $display_name, $user_id ) {
	if ( ! rs_is_en() ) {
		if ( empty( $display_name ) || 'Raisul Sohan' === $display_name || 'raisulsohan' === strtolower( $display_name ) ) {
			return 'রাইসুল সোহান';
		}
	} else {
		if ( 'রাইসুল সোহান' === $display_name ) {
			return 'Raisul Sohan';
		}
	}
	return $display_name;
}, 10, 2 );

/**
 * Remove WordPress Multisite 1MB upload limit for subsites,
 * allowing them to use the full server PHP upload limit.
 */
add_filter( 'upload_size_limit', function ( $size, $u_bytes, $p_bytes ) {
	return min( $u_bytes, $p_bytes );
}, 99, 3 );
