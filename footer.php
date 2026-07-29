<?php
/**
 * Footer template, plus the shared modal shells.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rs_about = rs_about();
?>

<footer class="rs-footer">
	<div class="rs-wrap">
		<span><?php echo esc_html( rs_footer_text() ); ?></span>
	</div>
</footer>

<button class="rs-top" type="button" id="rs-top" aria-label="উপরে যান">
	<?php echo wp_kses( rs_icon( 'up' ), rs_svg_tags() ); ?>
</button>

<span class="rs-tooltip" id="rs-tooltip" role="tooltip" aria-hidden="true"></span>

<!-- Post modal -->
<div class="rs-overlay" id="rs-post-overlay" role="dialog" aria-modal="true" aria-labelledby="rs-post-title" hidden>
	<div class="rs-modal rs-modal--post">
		<div class="rs-modal__progress" aria-hidden="true"><span id="rs-modal-progress"></span></div>
		<button class="rs-modal__close" type="button" data-rs-close aria-label="বন্ধ করুন">
			<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
		</button>
		<div class="rs-modal__scroll">
			<div class="rs-article" id="rs-post-body"></div>
		</div>
		<button class="rs-modal__top" type="button" id="rs-modal-top" aria-label="উপরে যান">
			<?php echo wp_kses( rs_icon( 'up' ), rs_svg_tags() ); ?>
		</button>
	</div>
</div>

<!-- Search modal -->
<div class="rs-overlay rs-overlay--top" id="rs-search-overlay" role="dialog" aria-modal="true" aria-label="সার্চ" hidden>
	<div class="rs-modal rs-modal--search">
		<div class="rs-search__field">
			<?php echo wp_kses( rs_icon( 'search', 16 ), rs_svg_tags() ); ?>
			<input class="rs-search__input" id="rs-search-input" type="search" placeholder="খুঁজুন..." autocomplete="off" aria-label="খুঁজুন">
			<button class="rs-icon" type="button" data-rs-close aria-label="বন্ধ করুন">
				<?php echo wp_kses( rs_icon( 'close', 16 ), rs_svg_tags() ); ?>
			</button>
		</div>
		<div class="rs-search__results" id="rs-search-results" aria-live="polite"></div>
		<div class="rs-search__count" id="rs-search-count" hidden></div>
	</div>
</div>

<!-- About modal -->
<div class="rs-overlay" id="rs-about-overlay" role="dialog" aria-modal="true" aria-labelledby="rs-about-title" hidden>
	<div class="rs-modal rs-modal--about">
		<button class="rs-modal__close" type="button" data-rs-close aria-label="বন্ধ করুন">
			<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
		</button>
		<div class="rs-modal__scroll rs-about__inner">
			<h2 class="rs-about__title" id="rs-about-title"><?php echo esc_html( $rs_about['title'] ); ?></h2>
			<div class="rs-about__body"><?php echo wp_kses_post( $rs_about['content'] ); ?></div>
		</div>
	</div>
</div>

<div class="rs-toasts" id="rs-toasts" aria-live="polite" aria-atomic="true"></div>

<?php wp_footer(); ?>
</body>
</html>
