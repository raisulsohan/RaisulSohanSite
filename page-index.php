<?php
/**
 * Template Name: Index
 *
 * Every published post on one page, newest first, under the year and the
 * month it belongs to.
 *
 * The front page answers "what is new" and the pagination answers "what is
 * next". Neither answers "what is there", which is the question a reader
 * asks once they have decided they like the writing — and the one a search
 * engine asks on every visit. Eight pages deep, the oldest work is further
 * from the front page than it deserves to be; here everything is one click
 * from it.
 *
 * The day sits in a column of its own rather than trailing the title as it
 * does in the list. On the front page the date is a footnote to the story;
 * here it is the thing the page is sorted by, and it earns the position.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$rs_hero = rs_hero_image();

/*
 * Every post at once, which is the whole point of the page. no_found_rows
 * because nothing here counts pages, and the term and meta caches are left
 * on: each row asks for a category and a reading time, and priming both in
 * one query is what keeps that from becoming ninety four.
 */
$rs_all = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);

/* Counted before the loop, because a year's heading is printed before its
   posts have been walked. */
$rs_per_year = array();

foreach ( $rs_all->posts as $rs_item ) {
	$rs_y                 = (int) get_post_time( 'Y', false, $rs_item );
	$rs_per_year[ $rs_y ] = isset( $rs_per_year[ $rs_y ] ) ? $rs_per_year[ $rs_y ] + 1 : 1;
}

$rs_months = rs_is_en()
	? array(
		1  => 'January',
		2  => 'February',
		3  => 'March',
		4  => 'April',
		5  => 'May',
		6  => 'June',
		7  => 'July',
		8  => 'August',
		9  => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December',
	)
	: rs_bn_months_full();
$rs_year   = 0;
$rs_month  = 0;
$rs_open   = false;
?>

<div class="rs-hero<?php echo $rs_hero ? ' rs-hero--image' : ''; ?>">
	<h1 class="rs-hero__title">
		<?php if ( $rs_hero ) : ?>
			<?php echo rs_render_hero_image_html( get_the_title() ); ?>
		<?php else : ?>
			<span><?php the_title(); ?></span>
		<?php endif; ?>
	</h1>
</div>

<main class="rs-wrap rs-index" id="rs-content">

	<?php
	/* The page's own title and whatever the author wrote on it. Both come
	   from the dashboard, so the wording here can change without the
	   template being touched. */
	?>
	<header class="rs-index__head">
		<h2 class="rs-index__title"><?php the_title(); ?></h2>

		<?php
		while ( have_posts() ) :
			the_post();

			if ( '' !== trim( get_the_content() ) ) :
				?>
				<div class="rs-index__intro"><?php the_content(); ?></div>
				<?php
			endif;
		endwhile;
		rewind_posts();
		?>
	</header>

	<?php if ( ! $rs_all->have_posts() ) : ?>

		<div class="rs-notice">
			<h2><?php echo esc_html( rs_is_en() ? 'No writings yet' : 'এখনো কোনো লেখা নেই' ); ?></h2>
			<p><?php echo esc_html( rs_is_en() ? 'Published writings will appear here.' : 'প্রথম লেখাটা প্রকাশ করলে এখানে দেখা যাবে।' ); ?></p>
		</div>

	<?php else : ?>

		<?php
		foreach ( $rs_all->posts as $rs_item ) :
			$rs_y = (int) get_post_time( 'Y', false, $rs_item );
			$rs_m = (int) get_post_time( 'n', false, $rs_item );

			if ( $rs_y !== $rs_year ) :
				if ( $rs_open ) :
					?>
					</ul></section>
					<?php
					$rs_open = false;
				endif;
				?>
				<div class="rs-index__year">
					<h3 class="rs-index__year-n"><?php echo esc_html( rs_is_en() ? $rs_y : rs_bn_digits( $rs_y ) ); ?></h3>
					<span class="rs-index__year-c"><?php
						if ( rs_is_en() ) {
							$cnt = (int) $rs_per_year[ $rs_y ];
							echo esc_html( $cnt . ' ' . ( 1 === $cnt ? 'writing' : 'writings' ) );
						} else {
							echo esc_html( rs_bn_digits( $rs_per_year[ $rs_y ] ) . 'টি' );
						}
					?></span>
				</div>
				<?php
				$rs_year  = $rs_y;
				$rs_month = 0;
			endif;

			if ( $rs_m !== $rs_month ) :
				if ( $rs_open ) :
					?>
					</ul></section>
					<?php
				endif;
				?>
				<section class="rs-index__month">
					<h4 class="rs-index__month-n"><?php echo esc_html( $rs_months[ $rs_m ] ); ?></h4>
					<ul class="rs-index__rows">
				<?php
				$rs_month = $rs_m;
				$rs_open  = true;
			endif;

			$rs_cat = rs_category( $rs_item );
			?>
			<li class="rs-index__row">
				<span class="rs-index__day"><?php echo esc_html( rs_is_en() ? (int) get_post_time( 'j', false, $rs_item ) : rs_bn_digits( (int) get_post_time( 'j', false, $rs_item ) ) ); ?></span>
				<?php /* data-rs-post is what makes these open in the reading modal, the same as a row on the front page. */ ?>
				<a class="rs-index__link"
					href="<?php echo esc_url( get_permalink( $rs_item ) ); ?>"
					data-rs-post="<?php echo (int) $rs_item->ID; ?>">
					<span class="rs-index__t"><?php echo esc_html( rs_plain_title( $rs_item ) ); ?></span>
					<?php if ( $rs_cat ) : ?>
						<em class="rs-index__c"><?php echo esc_html( $rs_cat ); ?></em>
					<?php endif; ?>
				</a>
				<span class="rs-index__r"><?php echo esc_html( rs_reading_time( $rs_item ) ); ?></span>
			</li>
			<?php
		endforeach;

		if ( $rs_open ) :
			?>
			</ul></section>
			<?php
		endif;

		wp_reset_postdata();
		?>

	<?php endif; ?>

</main>

<?php
get_footer();
