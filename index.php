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
			<?php echo rs_render_hero_image_html( rs_phrases()[0] ); ?>
		<?php else : ?>
			<?php /* app.js finds this by id; with a picture there is nothing
			         to type out and the typewriter stays asleep. */ ?>
			<span id="rs-type"><?php echo esc_html( rs_phrases()[0] ); ?></span>
		<?php endif; ?>
	</h1>
</div>

<?php rs_render_count(); ?>

<?php
/* The featured post — server-rendered, so there is ZERO layout shift (CLS).
   Several candidates go out with it and a blocking inline script picks one
   before this region is painted, which is what keeps the post changing per
   visit even when a page cache is serving the same HTML to everybody.
   See rs_render_featured_pool(). */
if ( ! is_paged() ) {
	rs_render_featured_pool();
}
?>

<?php
/* Filled by app.js when this browser left a story unfinished. Empty in
   the markup, because only the browser knows whether there is one. */
?>
<div class="rs-wrap" id="rs-resume"></div>

<?php
/* The shelf the reader fills on purpose, drawn by app.js from this
   browser's own storage. Only on the front page's first screen: it is a
   place to come back to, and repeating it down every archive and every
   page of results would turn it into furniture. */
if ( ( is_home() || is_front_page() ) && ! is_paged() ) : ?>
<div class="rs-wrap" id="rs-later"></div>
<?php endif; ?>

<div class="rs-fontctl rs-fontctl--float" role="group" aria-label="<?php echo esc_attr( rs_is_en() ? 'Text size' : 'লেখার আকার' ); ?>">
	<button type="button" data-rs-font="down" data-step="0" aria-label="<?php echo esc_attr( rs_is_en() ? 'Decrease font size' : 'ছোট করুন' ); ?>">A-</button>
	<span class="rs-fontctl__sep"></span>
	<button type="button" data-rs-font="reset" data-step="1" aria-label="<?php echo esc_attr( rs_is_en() ? 'Reset font size' : 'স্বাভাবিক আকার' ); ?>">A</button>
	<span class="rs-fontctl__sep"></span>
	<button type="button" data-rs-font="up" data-step="2" aria-label="<?php echo esc_attr( rs_is_en() ? 'Increase font size' : 'বড় করুন' ); ?>">A+</button>
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
