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
	<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() . ( rs_is_en() ? '/assets/fonts/noto-serif-bengali-latin.woff2' : '/assets/fonts/noto-serif-bengali-bengali.woff2' ) ); ?>" as="font" type="font/woff2" crossorigin>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<script>try{if(window.history&&window.history.replaceState&&window.location.search){var p=new URLSearchParams(window.location.search),d=false,t=['fbclid','gclid','utm_source','utm_medium','utm_campaign','utm_term','utm_content'];for(var i=0;i<t.length;i++){if(p.has(t[i])){p.delete(t[i]);d=true;}}if(d){var n=window.location.pathname+(p.toString()?'?'+p.toString():'')+window.location.hash;window.history.replaceState(null,'',n);}}var a=window.localStorage.getItem('rs-anim');if(a==='false')document.body.classList.remove('rs-animated');else if(a==='true')document.body.classList.add('rs-animated');}catch(e){}</script>

<a class="rs-sr" href="#rs-content"><?php echo esc_html( rs_is_en() ? 'Skip to content' : 'মূল লেখায় যান' ); ?></a>

<header class="rs-header">
	<div class="rs-wrap rs-header__inner">
		<nav class="rs-header__nav">
			<?php $rs_switcher = rs_lang_switcher_data(); ?>
			<a class="rs-lang-switcher" href="<?php echo esc_url( $rs_switcher['url'] ); ?>" title="<?php echo esc_attr( $rs_switcher['title'] ); ?>" aria-label="<?php echo esc_attr( $rs_switcher['title'] ); ?>">
				<span><?php echo esc_html( $rs_switcher['label'] ); ?></span>
			</a>
			<button class="rs-header__link" type="button" data-rs-open="about">
				<?php echo esc_html( rs_about()['title'] ); ?>
			</button>
			<?php
			$rs_portfolio_url = home_url( '/portfolio/' );
			$rs_req_path      = isset( $_SERVER['REQUEST_URI'] ) ? trim( (string) parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ) : '';
			$rs_is_portfolio  = ( 'portfolio' === $rs_req_path || 'en/portfolio' === $rs_req_path || preg_match( '~(?:^|/)portfolio/?$~i', $rs_req_path ) );
			?>
			<a class="rs-header__link<?php echo $rs_is_portfolio ? ' is-active' : ''; ?>" href="<?php echo esc_url( $rs_portfolio_url ); ?>"<?php echo $rs_is_portfolio ? ' aria-current="page"' : ''; ?>>
				<?php echo esc_html( rs_is_en() ? 'Portfolio' : 'পোর্টফোলিও' ); ?>
			</a>
		</nav>

		<a class="rs-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo esc_html( rs_brand() ); ?>
		</a>

		<nav class="rs-header__nav rs-header__nav--right">
			<button class="rs-icon rs-theme" type="button" data-rs-theme aria-label="<?php echo esc_attr( rs_is_en() ? 'Toggle theme' : 'ডার্ক ও লাইট মোড বদলান' ); ?>">
				<span class="rs-theme__moon"><?php echo wp_kses( rs_icon( 'moon' ), rs_svg_tags() ); ?></span>
				<span class="rs-theme__sun"><?php echo wp_kses( rs_icon( 'sun' ), rs_svg_tags() ); ?></span>
			</button>

			<?php $rs_email = rs_option( 'rs_email' ); ?>
			<?php if ( $rs_email ) : ?>
				<button class="rs-icon" type="button" data-rs-copy="<?php echo esc_attr( $rs_email ); ?>" data-rs-copy-kind="mail" title="<?php echo esc_attr( $rs_email ); ?>" aria-label="<?php echo esc_attr( rs_is_en() ? 'Copy email' : 'মেইল কপি করুন' ); ?>">
					<?php echo wp_kses( rs_icon( 'mail' ), rs_svg_tags() ); ?>
				</button>
			<?php endif; ?>

			<?php if ( rs_option( 'rs_facebook' ) ) : ?>
				<a class="rs-icon" href="<?php echo esc_url( rs_option( 'rs_facebook' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( rs_is_en() ? 'Facebook' : 'ফেসবুক' ); ?>">
					<?php echo wp_kses( rs_icon( 'facebook' ), rs_svg_tags() ); ?>
				</a>
			<?php endif; ?>

			<?php if ( rs_option( 'rs_linkedin' ) ) : ?>
				<a class="rs-icon" href="<?php echo esc_url( rs_option( 'rs_linkedin' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( rs_is_en() ? 'LinkedIn' : 'লিঙ্কডইন' ); ?>">
					<?php echo wp_kses( rs_icon( 'linkedin' ), rs_svg_tags() ); ?>
				</a>
			<?php endif; ?>

			<button class="rs-icon" type="button" data-rs-open="search" aria-label="<?php echo esc_attr( rs_is_en() ? 'Search' : 'সার্চ' ); ?>">
				<?php echo wp_kses( rs_icon( 'search' ), rs_svg_tags() ); ?>
			</button>
		</nav>
	</div>

	<?php if ( has_nav_menu( 'rs_primary' ) ) : ?>
		<nav class="rs-navbar" aria-label="<?php echo esc_attr( rs_is_en() ? 'Main menu' : 'প্রধান মেনু' ); ?>">
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
