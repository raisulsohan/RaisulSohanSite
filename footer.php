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

<?php
/*
 * No link to the feed here on purpose. Browsers stopped rendering feeds
 * years ago, so following one shows a reader a screenful of XML and the
 * impression that something broke. The people who do use feeds add a
 * site by its address, and the alternate link wp_head prints is what
 * their reader follows — a visible link would only catch the readers it
 * cannot help.
 */
?>
<footer class="rs-footer">
	<div class="rs-wrap">
		<span><?php echo esc_html( rs_footer_text() ); ?></span>
	</div>
</footer>

<?php
/* Above the floating size control, and hidden on the same narrow screens.
   The whole palette is worked out from this one colour by app.js. */
?>
<div class="rs-floats" role="group" aria-label="<?php echo esc_attr( rs_is_en() ? 'Site controls' : 'সাইটের নিয়ন্ত্রণ' ); ?>">
	<button class="rs-floats__install" type="button" id="rs-install-btn" hidden aria-label="<?php echo esc_attr( rs_is_en() ? 'Install App' : 'অ্যাপ ইনস্টল করুন' ); ?>" title="<?php echo esc_attr( rs_is_en() ? 'Install App' : 'অ্যাপ ইনস্টল করুন' ); ?>">
		<?php echo wp_kses( rs_icon( 'install', 15 ), rs_svg_tags() ); ?>
	</button>
	<button class="rs-floats__anim" type="button" id="rs-anim-toggle" aria-label="<?php echo esc_attr( rs_is_en() ? 'Toggle animation' : 'অ্যানিমেশন অন/অফ করুন' ); ?>" title="<?php echo esc_attr( rs_is_en() ? 'Toggle animation' : 'অ্যানিমেশন অন/অফ করুন' ); ?>">
		<?php echo wp_kses( rs_icon( 'sparkles', 16 ), rs_svg_tags() ); ?>
	</button>
	<div class="rs-tint" role="group" aria-label="<?php echo esc_attr( rs_is_en() ? 'Site colour' : 'সাইটের রং' ); ?>">
		<input class="rs-tint__input" type="color" id="rs-tint" aria-label="<?php echo esc_attr( rs_is_en() ? 'Pick site colour' : 'সাইটের রং বেছে নিন' ); ?>">
		<button class="rs-tint__reset" type="button" id="rs-tint-reset" hidden aria-label="<?php echo esc_attr( rs_is_en() ? 'Reset colour' : 'আগের রঙে ফিরুন' ); ?>">
			<?php echo wp_kses( rs_icon( 'undo', 13 ), rs_svg_tags() ); ?>
		</button>
	</div>
</div>

<button class="rs-top" type="button" id="rs-top" aria-label="<?php echo esc_attr( rs_is_en() ? 'Back to top' : 'উপরে যান' ); ?>">
	<?php echo wp_kses( rs_icon( 'up' ), rs_svg_tags() ); ?>
</button>

<span class="rs-tooltip" id="rs-tooltip" role="tooltip" aria-hidden="true"></span>

<aside class="rs-install-bar" id="rs-install-bar" hidden aria-label="<?php echo esc_attr( rs_is_en() ? 'App installation' : 'অ্যাপ ইনস্টলেশন' ); ?>">
	<button class="rs-install-bar__btn" type="button" id="rs-install-bar-btn">
		<?php echo wp_kses( rs_icon( 'install', 14 ), rs_svg_tags() ); ?>
		<span><?php echo esc_html( rs_is_en() ? 'Install App' : 'অ্যাপ ইনস্টল করুন' ); ?></span>
	</button>
	<button class="rs-install-bar__close" type="button" id="rs-install-bar-close" aria-label="<?php echo esc_attr( rs_is_en() ? 'Close' : 'বন্ধ করুন' ); ?>" title="<?php echo esc_attr( rs_is_en() ? 'Close' : 'বন্ধ করুন' ); ?>">
		<?php echo wp_kses( rs_icon( 'close', 12 ), rs_svg_tags() ); ?>
	</button>
</aside>

<!-- Post modal -->
<div class="rs-overlay" id="rs-post-overlay" role="dialog" aria-modal="true" aria-labelledby="rs-post-title" hidden>
	<div class="rs-modal rs-modal--post">
		<div class="rs-modal__progress" aria-hidden="true"><span id="rs-modal-progress"></span></div>
		<div id="rs-time-left" class="rs-time-left" aria-hidden="true"></div>
		<button class="rs-modal__close" type="button" data-rs-close aria-label="<?php echo esc_attr( rs_is_en() ? 'Close' : 'বন্ধ করুন' ); ?>">
			<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
		</button>
		<div class="rs-modal__scroll">
			<div class="rs-article" id="rs-post-body"></div>
		</div>
		<button class="rs-modal__top" type="button" id="rs-modal-top" aria-label="<?php echo esc_attr( rs_is_en() ? 'Back to top' : 'উপরে যান' ); ?>">
			<?php echo wp_kses( rs_icon( 'up' ), rs_svg_tags() ); ?>
		</button>
	</div>
</div>

<!-- Search modal -->
<div class="rs-overlay rs-overlay--top" id="rs-search-overlay" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( rs_is_en() ? 'Search' : 'সার্চ' ); ?>" hidden>
	<div class="rs-modal rs-modal--search">
		<div class="rs-search__field">
			<?php echo wp_kses( rs_icon( 'search', 16 ), rs_svg_tags() ); ?>
			<input class="rs-search__input" id="rs-search-input" type="search" placeholder="<?php echo esc_attr( rs_is_en() ? 'Search...' : 'খুঁজুন...' ); ?>" autocomplete="off" aria-label="<?php echo esc_attr( rs_is_en() ? 'Search' : 'খুঁজুন' ); ?>">
			<button class="rs-icon" type="button" data-rs-close aria-label="<?php echo esc_attr( rs_is_en() ? 'Close' : 'বন্ধ করুন' ); ?>">
				<?php echo wp_kses( rs_icon( 'close', 16 ), rs_svg_tags() ); ?>
			</button>
		</div>
		<div class="rs-search__results" id="rs-search-results" aria-live="polite"></div>
		<div class="rs-search__count" id="rs-search-count" hidden></div>
	</div>
</div>

<!-- About modal -->
<?php
/* No heading inside: the button that opens this already carries the
   page's title, so repeating it here only pushed the writing down.
   aria-label does the naming instead, since there is no longer an
   element to point at. */
?>
<div class="rs-overlay" id="rs-about-overlay" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $rs_about['title'] ); ?>" hidden>
	<div class="rs-modal rs-modal--about">
		<button class="rs-modal__close" type="button" data-rs-close aria-label="<?php echo esc_attr( rs_is_en() ? 'Close' : 'বন্ধ করুন' ); ?>">
			<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
		</button>
		<div class="rs-modal__scroll rs-about__inner">
			<div class="rs-about__body"><?php echo wp_kses_post( $rs_about['content'] ); ?></div>
		</div>
	</div>
</div>

<div class="rs-toasts" id="rs-toasts" aria-live="polite" aria-atomic="true"></div>

<?php wp_footer(); ?>
</body>
</html>
