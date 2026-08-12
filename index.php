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

<?php $rs_hero = rs_hero_image(); ?>

<div class="rs-hero<?php echo $rs_hero ? ' rs-hero--image' : ''; ?>">
	<?php
	/* Still an h1 either way. A picture standing in for the heading is
	   still the heading, and its alt text is the words it replaced —
	   which is what a search engine and a screen reader both read. */
	?>
	<h1 class="rs-hero__title">
		<?php if ( $rs_hero ) : ?>
			<?php
			/* sizes spelled out because the default assumes a picture
			   inside prose; this one fills the column, which is the
			   48rem wrap less its gutters. */
			echo wp_get_attachment_image(
				$rs_hero,
				'large',
				false,
				array(
					'class' => 'rs-hero__image',
					'alt'   => rs_phrases()[0],
					'sizes' => '(max-width: 48rem) 100vw, 720px',
				)
			);
			?>
		<?php else : ?>
			<?php /* app.js finds this by id; with a picture there is nothing
			         to type out and the typewriter stays asleep. */ ?>
			<span id="rs-type"><?php echo esc_html( rs_phrases()[0] ); ?></span>
		<?php endif; ?>
	</h1>
</div>

<?php rs_render_count(); ?>

<?php
/* Filled by app.js when this browser left a story unfinished. Empty in
   the markup, because only the browser knows whether there is one. */
?>
<div class="rs-wrap" id="rs-resume"></div>

<div class="rs-fontctl rs-fontctl--float" role="group" aria-label="লেখার আকার">
	<button type="button" data-rs-font="down" data-step="0" aria-label="ছোট করুন">A-</button>
	<span class="rs-fontctl__sep"></span>
	<button type="button" data-rs-font="reset" data-step="1" aria-label="স্বাভাবিক আকার">A</button>
	<span class="rs-fontctl__sep"></span>
	<button type="button" data-rs-font="up" data-step="2" aria-label="বড় করুন">A+</button>
</div>

<main class="rs-wrap rs-main" id="rs-content">
	<?php
	/* The rows and their page links live in rs_render_list(), because
	   app.js asks the server for exactly this part when a page link is
	   clicked. See rs_list_fragment(). */
	rs_render_list();
	?>
</main>

<?php
get_footer();
