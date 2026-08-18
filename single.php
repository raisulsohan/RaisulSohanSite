<?php
/**
 * A single post, shown as a full page.
 *
 * The modal on the front page uses the same markup, so a link shared from
 * the modal opens something that looks identical.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php
/* The modal has had one of these all along. A reader arriving from a
   shared link never saw it, which is most of them. */
?>
<div class="rs-progress" aria-hidden="true"><span id="rs-progress"></span></div>
<div id="rs-single-time-left" class="rs-time-left" aria-hidden="true"></div>

<main class="rs-single" id="rs-content">
	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<a class="rs-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo wp_kses( rs_icon( 'left', 14 ), rs_svg_tags() ); ?>
			সব লেখা
		</a>

		<article class="rs-article">
			<div class="rs-article__meta">
				<p class="rs-article__date"><?php echo esc_html( rs_bn_date() ); ?></p>
				<span class="rs-article__read"><?php echo esc_html( rs_reading_time() ); ?></span>
				<?php
				/* edit_post_link() checks the capability itself and prints
				   nothing for readers. */
				edit_post_link(
					__( 'সম্পাদনা', 'raisul-sohan' ),
					'',
					'',
					0,
					'rs-article__edit'
				);
				?>
				<div class="rs-fontctl" role="group" aria-label="লেখার আকার">
					<button type="button" data-rs-font="down" data-step="0" aria-label="ছোট করুন">A-</button>
					<span class="rs-fontctl__sep"></span>
					<button type="button" data-rs-font="reset" data-step="1" aria-label="স্বাভাবিক আকার">A</button>
					<span class="rs-fontctl__sep"></span>
					<button type="button" data-rs-font="up" data-step="2" aria-label="বড় করুন">A+</button>
				</div>
			</div>

			<div class="rs-article__head">
				<h1 class="rs-article__title"><?php the_title(); ?></h1><?php
				$rs_cat      = rs_category();
				$rs_cat_link = rs_category_link();
				if ( $rs_cat && $rs_cat_link ) :
					?><a class="rs-article__cat" href="<?php echo esc_url( $rs_cat_link ); ?>"><?php echo esc_html( $rs_cat ); ?></a><?php
				elseif ( $rs_cat ) :
					?><em class="rs-article__cat"><?php echo esc_html( $rs_cat ); ?></em><?php
				endif;
				?>
			</div>
			<p class="rs-article__author"><?php the_author(); ?></p>

			<div class="rs-article__body">
				<?php the_content(); ?>
			</div>

			<?php rs_share_row(); ?>
			<?php rs_related_row(); ?>
		</article>

		<nav class="rs-nextprev">
			<?php
			$rs_prev = get_previous_post();
			$rs_next = get_next_post();

			if ( $rs_prev ) {
				printf(
					'<a href="%s">← %s</a>',
					esc_url( get_permalink( $rs_prev ) ),
					esc_html( get_the_title( $rs_prev ) )
				);
			}

			if ( $rs_next ) {
				printf(
					'<a class="rs-nextprev__next" href="%s">%s →</a>',
					esc_url( get_permalink( $rs_next ) ),
					esc_html( get_the_title( $rs_next ) )
				);
			}
			?>
		</nav>

	<?php endwhile; ?>
</main>

<?php
get_footer();
