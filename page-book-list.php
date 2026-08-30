<?php
/**
 * Template Name: Book List
 *
 * Displays all books from the rs_book custom post type, with
 * dropdown filters for genre and author.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/* Fetch every published book in one query. The post type is not public
   so there is no main query for it; we run our own. Ordering by title
   keeps the list alphabetical, matching the spreadsheet the data came from. */
$book_query = new WP_Query( array(
	'post_type'      => 'rs_book',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'title',
	'order'          => 'ASC',
) );

$books   = array();
$genres  = array();
$authors = array();

if ( $book_query->have_posts() ) {
	while ( $book_query->have_posts() ) {
		$book_query->the_post();

		$author     = get_post_meta( get_the_ID(), '_rs_book_author', true );
		$translator = get_post_meta( get_the_ID(), '_rs_book_translator', true );
		$is_read    = get_post_meta( get_the_ID(), '_rs_book_read', true );

		$genre_terms = wp_get_object_terms( get_the_ID(), 'rs_book_genre' );
		$genre       = ! empty( $genre_terms ) && ! is_wp_error( $genre_terms )
			? $genre_terms[0]->name
			: '';

		$books[] = array(
			'title'      => get_the_title(),
			'author'     => $author,
			'translator' => $translator,
			'genre'      => $genre,
			'is_read'    => $is_read,
		);

		if ( $genre && ! in_array( $genre, $genres, true ) ) {
			$genres[] = $genre;
		}
		if ( $author && ! in_array( $author, $authors, true ) ) {
			$authors[] = $author;
		}
	}
	wp_reset_postdata();
}

sort( $genres );
sort( $authors );
?>

<div class="rs-hero">
	<h1 class="rs-hero__title">
		<span id="rs-type">আমার বইয়ের তালিকা</span>
	</h1>
</div>

<div class="rs-post-count">
	মোট <span id="rs-book-count" class="rs-bn"><?php echo count( $books ); ?></span>টি বই
</div>

<main class="rs-wrap rs-main" id="rs-content">
	<div class="rs-list-wrap">

		<div style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; align-items: center;">
			<select id="rs-filter-genre" style="padding: 0.5rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit;">
				<option value="">সব ধরন</option>
				<?php foreach ( $genres as $g ) : ?>
					<option value="<?php echo esc_attr( $g ); ?>"><?php echo esc_html( $g ); ?></option>
				<?php endforeach; ?>
			</select>

			<select id="rs-filter-author" style="padding: 0.5rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit;">
				<option value="">সব লেখক</option>
				<?php foreach ( $authors as $a ) : ?>
					<option value="<?php echo esc_attr( $a ); ?>"><?php echo esc_html( $a ); ?></option>
				<?php endforeach; ?>
			</select>

			<button id="rs-filter-reset" style="padding: 0.5rem 1rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-surface); color: var(--rs-fg); cursor: pointer; font-family: inherit; display: none;">রিসেট</button>
		</div>

		<div class="rs-list" id="rs-book-list">
			<?php foreach ( $books as $book ) : ?>
				<article class="rs-row book-item"
				         data-genre="<?php echo esc_attr( $book['genre'] ); ?>"
				         data-author="<?php echo esc_attr( $book['author'] ); ?>">
					<div class="rs-row__link" style="cursor: default;">
						<span class="rs-row__head">
							<span class="rs-row__title"><?php echo esc_html( $book['title'] ); ?></span>
							<?php if ( $book['genre'] ) : ?>
								<em class="rs-row__cat"><?php echo esc_html( $book['genre'] ); ?></em>
							<?php endif; ?>
						</span>
						<span class="rs-row__aside">
							<span class="rs-row__read" style="opacity: 0.8;">
								<?php echo esc_html( $book['author'] ); ?>
								<?php if ( $book['translator'] ) : ?>
									<span style="font-size: 0.85em; opacity: 0.7;">(<?php echo esc_html( $book['translator'] ); ?>)</span>
								<?php endif; ?>
							</span>
							<span class="rs-row__date">
								<?php if ( $book['is_read'] ) : ?>
									<span style="color: var(--rs-cat);">পড়া হয়েছে ✓</span>
								<?php else : ?>
									<span style="opacity: 0.5;">বাকি আছে</span>
								<?php endif; ?>
							</span>
						</span>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var filterGenre  = document.getElementById('rs-filter-genre');
	var filterAuthor = document.getElementById('rs-filter-author');
	var resetBtn     = document.getElementById('rs-filter-reset');
	var bookItems    = document.querySelectorAll('.book-item');
	var countDisplay = document.getElementById('rs-book-count');

	function bnDigits(str) {
		var bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
		return String(str).replace(/[0-9]/g, function(d) { return bn[d]; });
	}

	function applyFilters() {
		var selectedGenre  = filterGenre.value;
		var selectedAuthor = filterAuthor.value;
		var visibleCount   = 0;

		bookItems.forEach(function(item) {
			var genreMatch  = selectedGenre  === '' || item.getAttribute('data-genre')  === selectedGenre;
			var authorMatch = selectedAuthor === '' || item.getAttribute('data-author') === selectedAuthor;

			if (genreMatch && authorMatch) {
				item.style.display = '';
				visibleCount++;
			} else {
				item.style.display = 'none';
			}
		});

		countDisplay.textContent = bnDigits(visibleCount);
		resetBtn.style.display = (selectedGenre || selectedAuthor) ? 'inline-block' : 'none';
	}

	filterGenre.addEventListener('change', applyFilters);
	filterAuthor.addEventListener('change', applyFilters);

	resetBtn.addEventListener('click', function() {
		filterGenre.value  = '';
		filterAuthor.value = '';
		applyFilters();
	});

	countDisplay.textContent = bnDigits(bookItems.length);
});
</script>

<?php
get_footer();
