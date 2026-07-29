<?php
/**
 * Nothing found.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="rs-notice" id="rs-content">
	<h1>লেখাটি খুঁজে পাওয়া যায়নি</h1>
	<p>ঠিকানাটা বদলে গেছে, অথবা লেখাটি সরিয়ে ফেলা হয়েছে।</p>
	<a class="rs-share__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">সব লেখা দেখুন</a>
</main>

<?php
get_footer();
