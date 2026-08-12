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

<?php rs_render_count(); ?>

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
