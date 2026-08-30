<?php
/**
 * Template Name: Book List
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fetch CSV from Google Sheets
$csv_url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vT5wLOw3k0BZaXvxdLgjmVISNEHVzIRKWinmATyx3Dnwa1--CbGvyUVoMLtZ7cQY1IDqsRqbwOiO65o/pub?gid=1051931047&single=true&output=csv';

// Cache for 1 hour to prevent slow loading
$cache_key = 'rs_book_list_data';
$csv_data = get_transient( $cache_key );

if ( false === $csv_data ) {
	$response = wp_remote_get( $csv_url, array( 'timeout' => 15 ) );
	if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
		$csv_data = wp_remote_retrieve_body( $response );
		set_transient( $cache_key, $csv_data, HOUR_IN_SECONDS );
	}
}

$books = array();
$genres = array();
$authors = array();

if ( $csv_data ) {
	$lines = explode( "\n", $csv_data );
	// Skip header row
	$header = array_shift( $lines );
	
	foreach ( $lines as $line ) {
		if ( empty( trim( $line ) ) ) continue;
		
		// Parse CSV line handling quotes properly using str_getcsv
		$cols = str_getcsv( $line );
		if ( count( $cols ) >= 4 ) {
			$title = trim( $cols[0] );
			$author = trim( $cols[1] );
			$translator = isset($cols[2]) ? trim( $cols[2] ) : '';
			$genre = trim( $cols[3] );
			$read_status = isset($cols[4]) ? trim( $cols[4] ) : '';
			
			$books[] = array(
				'title' => $title,
				'author' => $author,
				'translator' => $translator,
				'genre' => $genre,
				'read_status' => $read_status
			);
			
			if ( ! empty( $genre ) && ! in_array( $genre, $genres ) ) {
				$genres[] = $genre;
			}
			if ( ! empty( $author ) && ! in_array( $author, $authors ) ) {
				$authors[] = $author;
			}
		}
	}
	
	sort( $genres );
	sort( $authors );
}
?>

<div class="rs-hero">
	<h1 class="rs-hero__title">
		<span id="rs-type">আমার বইয়ের তালিকা</span>
	</h1>
</div>

<div class="rs-post-count">
	মোট <span id="rs-book-count" class="rs-bn"><?php echo count( $books ); ?></span>টি বই
</div>

<main class="rs-wrap rs-main" id="rs-content">
	<div class="rs-list-wrap">
		
		<div style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; align-items: center;">
			<select id="rs-filter-genre" class="rs-row__cat" style="padding: 0.5rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit;">
				<option value="">সব ধরন</option>
				<?php foreach ( $genres as $genre ) : ?>
					<option value="<?php echo esc_attr( $genre ); ?>"><?php echo esc_html( $genre ); ?></option>
				<?php endforeach; ?>
			</select>
			
			<select id="rs-filter-author" class="rs-row__cat" style="padding: 0.5rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit;">
				<option value="">সব লেখক</option>
				<?php foreach ( $authors as $author ) : ?>
					<option value="<?php echo esc_attr( $author ); ?>"><?php echo esc_html( $author ); ?></option>
				<?php endforeach; ?>
			</select>
            
            <button id="rs-filter-reset" class="rs-row__cat" style="padding: 0.5rem 1rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-surface); color: var(--rs-fg); cursor: pointer; font-family: inherit; display: none;">রিসেট</button>
		</div>

		<div class="rs-list" id="rs-book-list">
			<?php foreach ( $books as $book ) : 
				$is_read = ! empty( $book['read_status'] );
			?>
				<article class="rs-row book-item" data-genre="<?php echo esc_attr( $book['genre'] ); ?>" data-author="<?php echo esc_attr( $book['author'] ); ?>">
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
								<?php if ( $is_read ) : ?>
									<span style="color: var(--rs-cat);">পড়া হয়েছে ✓</span>
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
	const filterGenre = document.getElementById('rs-filter-genre');
	const filterAuthor = document.getElementById('rs-filter-author');
	const resetBtn = document.getElementById('rs-filter-reset');
	const bookItems = document.querySelectorAll('.book-item');
	const countDisplay = document.getElementById('rs-book-count');
    
    // Function to convert English numbers to Bengali
    function bnDigits(str) {
        const bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return String(str).replace(/[0-9]/g, d => bn[d]);
    }

	function applyFilters() {
		const selectedGenre = filterGenre.value;
		const selectedAuthor = filterAuthor.value;
		let visibleCount = 0;

		bookItems.forEach(item => {
			const itemGenre = item.getAttribute('data-genre');
			const itemAuthor = item.getAttribute('data-author');
			
			const genreMatch = selectedGenre === '' || itemGenre === selectedGenre;
			const authorMatch = selectedAuthor === '' || itemAuthor === selectedAuthor;

			if (genreMatch && authorMatch) {
				item.style.display = '';
				visibleCount++;
			} else {
				item.style.display = 'none';
			}
		});

		countDisplay.textContent = bnDigits(visibleCount);
        
        if (selectedGenre !== '' || selectedAuthor !== '') {
            resetBtn.style.display = 'inline-block';
        } else {
            resetBtn.style.display = 'none';
        }
	}

	filterGenre.addEventListener('change', applyFilters);
	filterAuthor.addEventListener('change', applyFilters);
    
    resetBtn.addEventListener('click', function() {
        filterGenre.value = '';
        filterAuthor.value = '';
        applyFilters();
    });
    
    // Initialize count format
    countDisplay.textContent = bnDigits(bookItems.length);
});
</script>

<?php
get_footer();
