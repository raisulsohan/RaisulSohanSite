<?php
/**
 * Nothing found: "Missing Footage".
 *
 * After Effects shows colour bars where a file it cannot find should be.
 * This page does the same for a story that is not there, names the missing
 * address as the lost file, and offers a way back.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$rs_en = rs_is_en();

/* The address the reader typed or followed is itself a description of what
   they wanted, so it gets asked first. */
$rs_guesses = rs_missing_matches( 3 );

/* The last part of the address, shown as the file After Effects lost. */
$rs_path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only displayed, through esc_html().
$rs_file = sanitize_file_name( rawurldecode( basename( untrailingslashit( $rs_path ) ) ) );
$rs_file = $rs_file ? mb_substr( $rs_file, 0, 42 ) . '.mov' : 'untitled.mov';
?>

<main id="rs-content" class="rs-missing">
	<div class="rs-wrap rs-missing__inner">
		<figure class="rs-missing__frame" aria-hidden="true">
			<div class="rs-missing__bars"><i></i><i></i><i></i></div>
			<div class="rs-missing__badge">
				<span class="rs-missing__warn">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 2 20h20L12 3z"/><path d="M12 10v4M12 17.5v.01"/></svg>
					Missing Footage
				</span>
				<code><?php echo esc_html( $rs_file ); ?></code>
			</div>
			<span class="rs-missing__tc">00:00:04:04</span>
		</figure>

		<h1 class="rs-missing__title"><?php echo esc_html( $rs_en ? 'This footage can’t be found' : 'এই ফুটেজটা খুঁজে পাওয়া যাচ্ছে না' ); ?></h1>
		<p class="rs-missing__text"><?php echo esc_html( $rs_en ? 'The link may have changed, or the file was taken out of the project.' : 'লিংকটা হয়তো বদলে গেছে, অথবা ফাইলটা প্রজেক্ট থেকে সরিয়ে ফেলা হয়েছে।' ); ?></p>

		<div class="rs-missing__actions">
			<a class="rs-missing__btn rs-missing__btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.1.4l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.1-.4l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7"/></svg>
				<?php echo esc_html( $rs_en ? 'Relink: go home' : 'Relink: প্রথম পাতায় ফিরুন' ); ?>
			</a>
			<button class="rs-missing__btn" type="button" data-rs-open="search">
				<?php echo wp_kses( rs_icon( 'search', 16 ), rs_svg_tags() ); ?>
				<?php echo esc_html( $rs_en ? 'Search the project' : 'খুঁজে দেখুন' ); ?>
			</button>
		</div>
	</div>

	<?php
	/* A shared link that has gone stale drops the reader here with nothing
	   to do. When the address still resembles something that exists, saying
	   so is worth more than three stories drawn at random — and when it
	   does not, three stories are at least an offer. */
	?>
	<div class="rs-wrap rs-notice__more">
		<?php if ( $rs_guesses ) : ?>
			<?php rs_suggestions( $rs_guesses, $rs_en ? 'Were you looking for this?' : 'এটা খুঁজছিলেন?' ); ?>
		<?php else : ?>
			<?php rs_suggestions( rs_random_posts( 3 ), $rs_en ? 'You might also like' : 'এদিকেও দেখতে পারেন' ); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
