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

<main id="rs-content">
	<div class="rs-notice">
		<h1>লেখাটি খুঁজে পাওয়া যায়নি</h1>
		<p>ঠিকানাটা বদলে গেছে, অথবা লেখাটি সরিয়ে ফেলা হয়েছে।</p>
		<a class="rs-share__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">সব লেখা দেখুন</a>
	</div>

	<?php
	/* A shared link that has gone stale drops the reader here with nothing
	   to do. Three stories is at least an offer. */
	?>
	<div class="rs-wrap rs-notice__more">
		<?php rs_suggestions( rs_random_posts( 3 ), 'এদিকেও দেখতে পারেন' ); ?>
	</div>
</main>

<?php
get_footer();
