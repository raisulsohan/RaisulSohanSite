<?php
/**
 * A standalone page.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="rs-single" id="rs-content">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article class="rs-article">
			<h1 class="rs-article__title"><?php the_title(); ?></h1>
			<div class="rs-article__body">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>

<?php
get_footer();
