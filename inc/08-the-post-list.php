<?php
/**
 * The post list.
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
 * 8. The post list
 * ====================================================================== */

/**
 * URL of a page of the list currently being rendered.
 *
 * get_pagenum_link() builds on the request URI, so during a fragment
 * request every link it returns would carry rs_ajax=1 along and the reader
 * would end up with that in their address bar.
 *
 * @param int $page Page number.
 * @return string
 */
function rs_page_url( $page ) {
	return remove_query_arg( 'rs_ajax', get_pagenum_link( (int) $page ) );
}

/**
 * Which page numbers a pagination bar shows.
 *
 * The ends, the current page and its neighbours; a 0 marks where a run was
 * left out. Six hundred posts is sixty pages, and sixty numbers in a row is
 * not a thing anyone reads.
 *
 * @param int $current Current page.
 * @param int $total   Total pages.
 * @param int $edge    How many pages to always keep at each end.
 * @param int $around  How many pages to keep either side of the current one.
 * @return array
 */
function rs_page_slots( $current, $total, $edge = 1, $around = 1 ) {
	$slots = array();
	$last  = 0;

	for ( $page = 1; $page <= $total; $page++ ) {
		$keep = $page <= $edge
			|| $page > $total - $edge
			|| abs( $page - $current ) <= $around;

		if ( ! $keep ) {
			continue;
		}

		if ( $last && $page - $last > 1 ) {
			$slots[] = 0;
		}

		$slots[] = $page;
		$last    = $page;
	}

	return $slots;
}

/**
 * Render one arrow of the pagination bar.
 *
 * The disabled arrow stays in the flow as a span rather than disappearing,
 * so the numbers do not shift sideways between the first page and the rest.
 *
 * @param int    $page  Page to link to.
 * @param bool   $on    Whether that page exists.
 * @param string $icon  Icon name.
 * @param string $rel   Link relation.
 * @param string $label Accessible label.
 */
function rs_pagination_step( $page, $on, $icon, $rel, $label ) {
	$svg = wp_kses( rs_icon( $icon, 14 ), rs_svg_tags() );

	if ( ! $on ) {
		echo '<span class="rs-pagination__step is-off" aria-hidden="true">' . $svg . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	echo '<a class="rs-pagination__step" href="' . esc_url( rs_page_url( $page ) ) . '"'
		. ' rel="' . esc_attr( $rel ) . '" aria-label="' . esc_attr( $label ) . '">'
		. $svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		. '</a>';
}

/**
 * Numbered page links for the main query.
 *
 * Built from the main query rather than from a count of every post, so a
 * category, a tag or a search paginates over its own results. Ordinary
 * anchors, so they work with JavaScript off; app.js intercepts the click
 * and swaps the list in place when it is on.
 */
function rs_pagination() {
	$total = (int) $GLOBALS['wp_query']->max_num_pages;

	if ( $total < 2 ) {
		return;
	}

	$current = max( 1, (int) get_query_var( 'paged' ) );
	$nav_label  = rs_is_en() ? 'Pagination' : 'পাতা';
	$prev_label = rs_is_en() ? 'Previous page' : 'আগের পাতা';
	$next_label = rs_is_en() ? 'Next page' : 'পরের পাতা';
	?>
	<nav class="rs-pagination" aria-label="<?php echo esc_attr( $nav_label ); ?>">
		<?php
		rs_pagination_step( $current - 1, $current > 1, 'left', 'prev', $prev_label );

		foreach ( rs_page_slots( $current, $total ) as $slot ) {
			if ( 0 === $slot ) {
				echo '<span class="rs-pagination__gap" aria-hidden="true">…</span>';
				continue;
			}

			$digits = rs_bn_digits( $slot );

			if ( $slot === $current ) {
				printf(
					'<span class="rs-pagination__num is-current" aria-current="page">%s</span>',
					esc_html( $digits )
				);
				continue;
			}

			$page_label = rs_is_en()
				? sprintf( 'Page %s', $digits )
				: sprintf( '%s নম্বর পাতা', $digits );

			printf(
				'<a class="rs-pagination__num" href="%s" aria-label="%s">%s</a>',
				esc_url( rs_page_url( $slot ) ),
				esc_attr( $page_label ),
				esc_html( $digits )
			);
		}

		rs_pagination_step( $current + 1, $current < $total, 'right', 'next', $next_label );
		?>
	</nav>
	<?php
}

/**
 * The count that sits above the list.
 *
 * The front page counts every published post. Inside an archive that
 * number would be a claim about posts the reader cannot see from there, so
 * the archive's own total is used instead — which is also the only number
 * that answers the question they just asked by narrowing the list.
 *
 * It lives outside the swapped fragment on purpose: it describes the whole
 * archive, not the page of it currently on screen, so turning a page must
 * not change it.
 */
function rs_render_count() {
	$count = ( is_home() || is_front_page() )
		? rs_published_count()
		: (int) $GLOBALS['wp_query']->found_posts;

	if ( ! $count ) {
		/* An empty list says so in its own words, further down. */
		return;
	}

	$term = ( is_category() || is_tag() ) ? get_queried_object() : null;

	if ( rs_is_en() ) {
		$plural = 1 === (int) $count ? 'writing' : 'writings';
		if ( $term instanceof WP_Term && is_category() ) {
			$before = '';
			$after  = ' ' . $plural . ' in ' . $term->name;
		} elseif ( $term instanceof WP_Term ) {
			$before = '';
			$after  = ' ' . $plural . ' tagged with ' . $term->name;
		} elseif ( is_search() ) {
			$before = '';
			$after  = ' ' . ( 1 === (int) $count ? 'result' : 'results' ) . ' found';
		} else {
			$before = '';
			$after  = ' ' . $plural . ' published';
		}
		$random_label = 'Random writing';
	} else {
		if ( $term instanceof WP_Term && is_category() ) {
			/* The name rather than "this category": it is the only line on an
			   archive that says which one the reader is standing in, since the
			   heading above keeps the site's own phrase. */
			$before = $term->name . ' ক্যাটাগরিতে ';
			$after  = 'টি লেখা প্রকাশিত';
		} elseif ( $term instanceof WP_Term ) {
			$before = $term->name . ' ট্যাগে ';
			$after  = 'টি লেখা প্রকাশিত';
		} elseif ( is_search() ) {
			$before = '';
			$after  = 'টি লেখা পাওয়া গেছে';
		} else {
			$before = '';
			$after  = 'টি লেখা প্রকাশিত';
		}
		$random_label = 'যেকোনো একটা লেখা';
	}
	/*
	 * On the front page the sentence is already a description of the index,
	 * so it becomes the way in rather than growing a button beside itself.
	 * Only there: on an archive it counts that archive's posts, and sending
	 * a reader from "গল্প ক্যাটাগরিতে ১৫টি" to a list of all forty seven
	 * would answer a question they did not ask.
	 */
	$index = ( is_home() || is_front_page() ) ? rs_index_url() : '';
	?>
	<div class="rs-wrap">
		<p class="rs-post-count">
			<?php if ( $index ) : ?>
				<a class="rs-post-count__all" href="<?php echo esc_url( $index ); ?>"><?php
					echo esc_html( $before );
					?><span class="rs-post-count__number"><?php echo esc_html( rs_bn_digits( $count ) ); ?></span><?php
					echo esc_html( $after );
				?></a>
			<?php else : ?>
				<?php
				/* No line breaks around the number: টি is a suffix, and any
				   whitespace here would render as a space inside the word. */
				echo esc_html( $before );
				?><span class="rs-post-count__number"><?php echo esc_html( rs_bn_digits( $count ) ); ?></span><?php
				echo esc_html( $after );
				?>
			<?php endif; ?>
			<a class="rs-post-count__any"
				href="<?php echo esc_url( rs_random_url() ); ?>"
				data-rs-random="<?php echo (int) rs_random_cat(); ?>"><?php echo esc_html( $random_label ); ?></a>
		</p>
	</div>
	<?php
}

/**
 * The query behind the featured block: the handful of candidates the page
 * carries, of which an inline script shows one.
 */
function rs_featured_query_args( $cat_id = 0, $count = 1 ) {
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $count,
		'orderby'        => 'rand',
		'no_found_rows'  => true,
	);

	if ( $cat_id ) {
		$args['cat'] = (int) $cat_id;
	} elseif ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$args['cat'] = $term->term_id;
		}
	}

	return $args;
}

/**
 * How many posts the featured block carries.
 *
 * A page cache hands every visitor the same HTML, so a server that picks
 * the post picks it once and for everybody — which is the bug this
 * replaces. Sending a handful and letting the browser choose costs a few
 * hundred bytes and no request at all.
 */
function rs_featured_pool_size() {
	return max( 1, min( 20, (int) apply_filters( 'rs_featured_pool_size', 8 ) ) );
}

/**
 * The featured block: several candidates, one of them on show.
 *
 * All but the first arrive with `hidden`, so a browser without JavaScript
 * — and every browser up to the moment the script below runs — has
 * exactly one post to lay out, and there is no layout shift either way.
 * The picker is inline and blocking on purpose: it moves the attribute
 * while the parser is still here, before this region has been painted, so
 * nobody sees one post replaced by another.
 */
function rs_render_featured_pool( $cat_id = 0 ) {
	if ( is_search() ) {
		return;
	}

	$q = new WP_Query( rs_featured_query_args( $cat_id, rs_featured_pool_size() ) );

	if ( ! $q->have_posts() ) {
		return;
	}

	$count = 0;
	?>
	<div class="rs-wrap" id="rs-featured-wrap">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			rs_featured_post_markup( get_post(), $count > 0 );
			$count++;
		endwhile;
		wp_reset_postdata();
		?>
	</div>
	<?php

	if ( $count < 2 ) {
		return;
	}
	?>
	<script>(function(){try{var c=document.querySelectorAll('#rs-featured-wrap > .rs-featured__pick'),n=c.length;if(n<2){return;}var r=Math.floor(Math.random()*n);if(!r){return;}c[0].hidden=true;c[r].hidden=false;}catch(e){}})();</script>
	<?php
}

/**
 * One featured post, drawn as it appears on the page.
 *
 * $hidden is what rs_render_featured_pool() sets on every candidate but
 * the first, so the block lays out as a single post until the picker
 * chooses.
 */
function rs_featured_post_markup( $post, $hidden = false ) {
	$length = (int) rs_option( 'rs_featured_summary_length' );
	if ( ! $length ) $length = 250;
	
	if ( $length > 200 ) {
		$text = has_excerpt( $post )
			? trim( html_entity_decode( wp_strip_all_tags( $post->post_excerpt ), ENT_QUOTES, 'UTF-8' ) )
			: rs_plain_text( $post );
		$summary = rs_shorten( $text, $length );
	} else {
		$summary = rs_summary( $post, $length );
	}

	// Match first letter and its combining marks/virama-linked letters
	$pattern = '/^([\x{0980}-\x{09FF}](?:\x{09CD}[\x{0980}-\x{09FF}])*[\x{09BE}-\x{09CC}\x{09D7}\x{09E2}\x{09E3}]?[\x{0981}-\x{0983}]?)/u';
	if ( preg_match( $pattern, $summary, $matches ) ) {
		$dropcap = $matches[0];
	} else {
		// Fallback for non-Bengali or basic chars
		preg_match('/^\X/u', $summary, $matches);
		$dropcap = $matches[0] ?? '';
	}
	$rest = mb_substr( $summary, mb_strlen( $dropcap, 'UTF-8' ), null, 'UTF-8' );

	?>
	<div class="rs-featured__pick"<?php echo $hidden ? ' hidden' : ''; ?>>
		<div class="rs-featured">
			<div class="rs-featured__label">
				<span class="rs-featured__line"></span><?php echo esc_html( rs_is_en() ? 'Featured' : 'ফিচার্ড' ); ?>
			</div>
			<h2 class="rs-featured__title">
				<a href="<?php the_permalink(); ?>" data-rs-post="<?php the_ID(); ?>"><?php the_title(); ?></a>
			</h2>
			<div class="rs-featured__date">
				<?php echo esc_html( rs_bn_date( $post ) ); ?>
				<span style="margin: 0 0.5rem; opacity: 0.5;">&bull;</span>
				<?php echo esc_html( rs_reading_time( $post ) ); ?>
				<?php if ( $cat = rs_category( $post ) ) : ?>
					<span style="margin: 0 0.5rem; opacity: 0.5;">&bull;</span>
					<a href="<?php echo esc_url( rs_category_link( $post ) ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( $cat ); ?></a>
				<?php endif; ?>
			</div>
			<div class="rs-featured__summary">
				<span class="rs-featured__dropcap"><?php echo esc_html( $dropcap ); ?></span><?php echo esc_html( $rest ); ?>
			</div>
			<div class="rs-featured__action">
				<a href="<?php the_permalink(); ?>" class="rs-featured__btn" data-rs-post="<?php the_ID(); ?>"><?php echo esc_html( rs_is_en() ? 'Read full article →' : 'সম্পূর্ণ লেখা পড়ুন →' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}

/**
 * The list of posts, with its page links.
 *
 * Both the full page and the fragment that app.js swaps in go through
 * here, so the page reached by clicking "২" is the same markup as the page
 * reached by opening /page/2/ directly.
 */
function rs_render_list() {
	/* Inside a category archive the tag would just repeat the heading, so
	   it only earns its place in a mixed list. */
	$show_cat = ! is_category();
	?>
	<div class="rs-list-wrap" id="rs-list-wrap" tabindex="-1" data-rs-title="<?php echo esc_attr( wp_get_document_title() ); ?>">
		<?php if ( have_posts() ) : ?>

			<div class="rs-list" id="rs-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="rs-row">
						<a class="rs-row__link"
							href="<?php the_permalink(); ?>"
							data-rs-post="<?php the_ID(); ?>"
							data-rs-summary="<?php echo esc_attr( rs_summary() ); ?>">
							<span class="rs-row__head">
								<span class="rs-row__title"><?php the_title(); ?></span><?php
								$rs_cat = $show_cat ? rs_category() : '';
								if ( $rs_cat ) :
									?><em class="rs-row__cat"><?php echo esc_html( $rs_cat ); ?></em><?php
								endif;
								?>
							</span>
							<span class="rs-row__aside">
								<span class="rs-row__read"><?php echo esc_html( rs_reading_time() ); ?></span>
								<span class="rs-row__date"><?php echo esc_html( rs_bn_date() ); ?></span>
							</span>
						</a>
						<?php
						/* Outside the link rather than inside it. A button
						   nested in an anchor is invalid markup, and every
						   press of it would also open the post — which is
						   the one thing this button exists to postpone.

						   It carries only the id: app.js reads the title,
						   the address and the reading time off the row it
						   sits in rather than having them repeated into
						   attributes on every row of every page. */
						?>
						<button class="rs-row__save" type="button"
							data-rs-later="<?php the_ID(); ?>"
							aria-pressed="false"
							aria-label="<?php echo esc_attr( rs_is_en() ? 'Read later' : 'পরে পড়ব' ); ?>">
							<?php echo wp_kses( rs_icon( 'bookmark', 14 ), rs_svg_tags() ); ?>
						</button>
					</article>
				<?php endwhile; ?>
			</div>

			<?php rs_pagination(); ?>

		<?php else : ?>

			<div class="rs-notice">
				<?php if ( is_search() ) : ?>
					<h2><?php echo esc_html( rs_is_en() ? 'No results found' : 'কিছু পাওয়া যায়নি' ); ?></h2>
					<p><?php echo esc_html( rs_is_en() ? 'Try searching with different keywords.' : 'অন্য শব্দ দিয়ে খুঁজে দেখুন।' ); ?></p>
				<?php else : ?>
					<h2><?php echo esc_html( rs_is_en() ? 'No writings yet' : 'এখনো কোনো লেখা নেই' ); ?></h2>
					<p><?php echo esc_html( rs_is_en() ? 'Published articles will appear here.' : 'প্রথম লেখাটা প্রকাশ করলে এখানে দেখা যাবে।' ); ?></p>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	</div>
	<?php
}

/**
 * Answer ?rs_ajax=1 with the list on its own.
 *
 * A page link handled by app.js asks for the page it was going to load
 * anyway, just without the header, hero and footer wrapped around it. The
 * main query has already run by the time template_redirect fires, so this
 * is the same list the full page would have shown — which is what keeps a
 * category, a tag or a search paginating over its own posts without the
 * endpoint having to know anything about them.
 */
function rs_list_fragment() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only, and public.
	if ( ! isset( $_GET['rs_ajax'] ) ) {
		return;
	}

	if ( ! is_home() && ! is_archive() && ! is_search() ) {
		return;
	}

	/* Public HTML, so the CDN may hold it for a few minutes instead of
	   sending every pagination click through PHP. A signed in editor
	   gets edit links in the rows, so their copy is never stored. */
	if ( is_user_logged_in() ) {
		nocache_headers();
	} else {
		header( 'Cache-Control: public, max-age=0, s-maxage=600' );
	}
	header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
	/* A fragment has no header, no footer and no canonical tag. If a
	   crawler ever finds one of these URLs, it should not keep it. */
	header( 'X-Robots-Tag: noindex' );

	rs_render_list();
	exit;
}
add_action( 'template_redirect', 'rs_list_fragment' );
