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
\ = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vT5wLOw3k0BZaXvxdLgjmVISNEHVzIRKWinmATyx3Dnwa1--CbGvyUVoMLtZ7cQY1IDqsRqbwOiO65o/pub?gid=1051931047&single=true&output=csv';

// Cache for 1 hour to prevent slow loading
\ = 'rs_book_list_data';
\ = get_transient( \ );

if ( false === \ ) {
	\ = wp_remote_get( \, array( 'timeout' => 15 ) );
	if ( ! is_wp_error( \ ) && wp_remote_retrieve_response_code( \ ) === 200 ) {
		\ = wp_remote_retrieve_body( \ );
		set_transient( \, \, HOUR_IN_SECONDS );
	}
}

\ = array();
\ = array();
\ = array();

if ( \ ) {
	\ = explode( \"\n\", \ );
	// Skip header row
	\ = array_shift( \ );
	
	foreach ( \ as \ ) {
		if ( empty( trim( \ ) ) ) continue;
		
		// Parse CSV line handling quotes properly using str_getcsv
		\ = str_getcsv( \ );
		if ( count( \ ) >= 4 ) {
			\ = trim( \[0] );
			\ = trim( \[1] );
			\ = isset(\[2]) ? trim( \[2] ) : '';
			\ = trim( \[3] );
			\ = isset(\[4]) ? trim( \[4] ) : '';
			
			\[] = array(
				'title' => \,
				'author' => \,
				'translator' => \,
				'genre' => \,
				'read_status' => \
			);
			
			if ( ! empty( \ ) && ! in_array( \, \ ) ) {
				\[] = \;
			}
			if ( ! empty( \ ) && ! in_array( \, \ ) ) {
				\[] = \;
			}
		}
	}
	
	sort( \ );
	sort( \ );
}
?>

<div class="rs-hero">
	<h1 class="rs-hero__title">
		<span id="rs-type">আমার বইয়ের তালিকা</span>
	</h1>
</div>

<div class="rs-post-count">
	মোট <span id="rs-book-count" class="rs-bn"><?php echo count( \ ); ?></span>টি বই
</div>

<main class="rs-wrap rs-main" id="rs-content">
	<div class="rs-list-wrap">
		
		<div style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; align-items: center;">
			<select id="rs-filter-genre" class="rs-row__cat" style="padding: 0.5rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit;">
				<option value="">সব ধরন</option>
				<?php foreach ( \ as \ ) : ?>
					<option value="<?php echo esc_attr( \ ); ?>"><?php echo esc_html( \ ); ?></option>
				<?php endwhile; // Oops meant endforeach ?>
				<?php endforeach; ?>
			</select>
			
			<select id="rs-filter-author" class="rs-row__cat" style="padding: 0.5rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit;">
				<option value="">সব লেখক</option>
				<?php foreach ( \ as \ ) : ?>
					<option value="<?php echo esc_attr( \ ); ?>"><?php echo esc_html( \ ); ?></option>
				<?php endforeach; ?>
			</select>
            
            <button id="rs-filter-reset" class="rs-row__cat" style="padding: 0.5rem 1rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-surface); color: var(--rs-fg); cursor: pointer; font-family: inherit; display: none;">রিসেট</button>
		</div>

		<div class="rs-list" id="rs-book-list">
			<?php foreach ( \ as \ ) : 
				\ = ! empty( \['read_status'] );
			?>
				<article class="rs-row book-item" data-genre="<?php echo esc_attr( \['genre'] ); ?>" data-author="<?php echo esc_attr( \['author'] ); ?>">
					<div class="rs-row__link" style="cursor: default;">
						<span class="rs-row__head">
							<span class="rs-row__title"><?php echo esc_html( \['title'] ); ?></span>
							<?php if ( \['genre'] ) : ?>
								<em class="rs-row__cat"><?php echo esc_html( \['genre'] ); ?></em>
							<?php endif; ?>
						</span>
						<span class="rs-row__aside">
							<span class="rs-row__read" style="opacity: 0.8;">
                                <?php echo esc_html( \['author'] ); ?>
                                <?php if ( \['translator'] ) : ?>
                                    <span style="font-size: 0.85em; opacity: 0.7;">(<?php echo esc_html( \['translator'] ); ?>)</span>
                                <?php endif; ?>
                            </span>
							<span class="rs-row__date">
								<?php if ( \ ) : ?>
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
