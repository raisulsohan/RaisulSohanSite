<?php
/**
 * The post list.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="rs-hero">
	<h1 class="rs-hero__title">
		<span id="rs-type"><?php echo esc_html( rs_phrases()[0] ); ?></span>
	</h1>
</div>

<div class="rs-fontctl rs-fontctl--float" role="group" aria-label="লেখার আকার">
	<button type="button" data-rs-font="down" data-step="0" aria-label="ছোট করুন">A-</button>
	<span class="rs-fontctl__sep"></span>
	<button type="button" data-rs-font="reset" data-step="1" aria-label="স্বাভাবিক আকার">A</button>
	<span class="rs-fontctl__sep"></span>
	<button type="button" data-rs-font="up" data-step="2" aria-label="বড় করুন">A+</button>
</div>

<main class="rs-wrap rs-main" id="rs-content">
	<?php if ( have_posts() ) : ?>

		<div class="rs-list" id="rs-list">
			<?php
			/* Inside a category archive the tag would just repeat the
			   heading, so it only earns its place in a mixed list. */
			$rs_show_cat = ! is_category();

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
							$rs_cat = $rs_show_cat ? rs_category() : '';
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
				</article>
			<?php endwhile; ?>
		</div>

		<?php if ( $GLOBALS['wp_query']->max_num_pages > 1 ) : ?>
			<button class="rs-more" type="button" id="rs-more" data-next="2" data-max="<?php echo (int) $GLOBALS['wp_query']->max_num_pages; ?>">
				আরও লেখা
			</button>
		<?php endif; ?>

	<?php else : ?>

		<div class="rs-notice">
			<h1>এখনো কোনো লেখা নেই</h1>
			<p>প্রথম লেখাটা প্রকাশ করলে এখানে দেখা যাবে।</p>
		</div>

	<?php endif; ?>
</main>

<?php
get_footer();
