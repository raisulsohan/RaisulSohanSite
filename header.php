<?php
/**
 * Header template.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="rs-sr" href="#rs-content">মূল লেখায় যান</a>

<header class="rs-header">
	<div class="rs-wrap rs-header__inner">
		<nav class="rs-header__nav">
			<button class="rs-header__link" type="button" data-rs-open="about">
				<?php echo esc_html( rs_about()['title'] ); ?>
			</button>
		</nav>

		<a class="rs-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php bloginfo( 'name' ); ?>
		</a>

		<nav class="rs-header__nav rs-header__nav--right">
			<button class="rs-icon rs-theme" type="button" data-rs-theme aria-label="<?php esc_attr_e( 'ডার্ক ও লাইট মোড বদলান', 'raisul-sohan' ); ?>">
				<span class="rs-theme__moon"><?php echo wp_kses( rs_icon( 'moon' ), rs_svg_tags() ); ?></span>
				<span class="rs-theme__sun"><?php echo wp_kses( rs_icon( 'sun' ), rs_svg_tags() ); ?></span>
			</button>

			<?php $rs_email = rs_option( 'rs_email' ); ?>
			<?php if ( $rs_email ) : ?>
				<button class="rs-icon" type="button" data-rs-copy="<?php echo esc_attr( $rs_email ); ?>" data-rs-copy-kind="mail" title="<?php echo esc_attr( $rs_email ); ?>" aria-label="মেইল কপি করুন">
					<?php echo wp_kses( rs_icon( 'mail' ), rs_svg_tags() ); ?>
				</button>
			<?php endif; ?>

			<?php if ( rs_option( 'rs_facebook' ) ) : ?>
				<a class="rs-icon" href="<?php echo esc_url( rs_option( 'rs_facebook' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="ফেসবুক">
					<?php echo wp_kses( rs_icon( 'facebook' ), rs_svg_tags() ); ?>
				</a>
			<?php endif; ?>

			<?php if ( rs_option( 'rs_linkedin' ) ) : ?>
				<a class="rs-icon" href="<?php echo esc_url( rs_option( 'rs_linkedin' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="লিঙ্কডইন">
					<?php echo wp_kses( rs_icon( 'linkedin' ), rs_svg_tags() ); ?>
				</a>
			<?php endif; ?>

			<button class="rs-icon" type="button" data-rs-open="search" aria-label="সার্চ">
				<?php echo wp_kses( rs_icon( 'search' ), rs_svg_tags() ); ?>
			</button>
		</nav>
	</div>

	<?php if ( has_nav_menu( 'rs_primary' ) ) : ?>
		<nav class="rs-navbar" aria-label="<?php esc_attr_e( 'প্রধান মেনু', 'raisul-sohan' ); ?>">
			<div class="rs-wrap rs-navbar__inner">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'rs_primary',
						'container'      => false,
						'menu_class'     => 'rs-navbar__list',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</div>
		</nav>
	<?php endif; ?>
</header>
