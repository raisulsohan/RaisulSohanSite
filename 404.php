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

/* The address the reader typed or followed is itself a description of what
   they wanted, so it gets asked first. */
$rs_guesses = rs_missing_matches( 3 );
?>

<main id="rs-content">
	<div class="rs-notice">
		<h1><?php echo esc_html( rs_is_en() ? 'Writing not found' : 'লেখাটি খুঁজে পাওয়া যায়নি' ); ?></h1>
		<p><?php echo esc_html( rs_is_en() ? 'The address might have changed, or the article was removed.' : 'ঠিকানাটা বদলে গেছে, অথবা লেখাটি সরিয়ে ফেলা হয়েছে।' ); ?></p>
		<a class="rs-share__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( rs_is_en() ? 'View all writings' : 'সব লেখা দেখুন' ); ?></a>
	</div>

	<?php
	/* A shared link that has gone stale drops the reader here with nothing
	   to do. When the address still resembles something that exists, saying
	   so is worth more than three stories drawn at random — and when it
	   does not, three stories are at least an offer. */
	?>
	<div class="rs-wrap rs-notice__more">
		<?php if ( $rs_guesses ) : ?>
			<?php rs_suggestions( $rs_guesses, rs_is_en() ? 'Were you looking for this?' : 'এটা খুঁজছিলেন?' ); ?>
		<?php else : ?>
			<?php rs_suggestions( rs_random_posts( 3 ), rs_is_en() ? 'You might also like' : 'এদিকেও দেখতে পারেন' ); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
