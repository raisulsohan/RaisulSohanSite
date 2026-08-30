<?php
/**
 * Template Name: Book List
 *
 * Displays all books from the rs_book custom post type, with
 * dropdown filters for genre and author, live search, and
 * client-side pagination that matches the site's AJAX style.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/* ---- Hero: same banner as the homepage ---- */
$rs_hero = rs_hero_image();

/* ---- Fetch every published book ---- */
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

/* ---- Shelf Helpers ---- */
function rs_get_spine_color( $genre ) {
	if ( ! $genre ) return 'hsl(0, 0%, 40%)';
	$hash = crc32( $genre );
	$hue  = $hash % 360;
	/* Saturation 40-60%, Lightness 30-45% for a classy book cover look */
	$sat  = 40 + ( $hash % 20 );
	$lit  = 30 + ( $hash % 15 );
	return "hsl({$hue}, {$sat}%, {$lit}%)";
}

function rs_get_spine_height( $title ) {
	$len = mb_strlen( $title, 'UTF-8' );
	/* Base padding + approx 12px per character */
	$height = 60 + ( $len * 12 );
	return max( 180, min( 400, $height ) );
}
?>

<div class="rs-hero<?php echo $rs_hero ? ' rs-hero--image' : ''; ?>">
	<h1 class="rs-hero__title">
		<?php if ( $rs_hero ) : ?>
			<?php
			echo wp_get_attachment_image(
				$rs_hero,
				'large',
				false,
				array(
					'class' => 'rs-hero__image',
					'alt'   => 'আমার বইয়ের তালিকা',
					'sizes' => '(max-width: 48rem) 100vw, 720px',
					'style' => 'object-position: ' . esc_attr( rs_option( 'rs_hero_pos' ) ) . ';',
				)
			);
			?>
		<?php else : ?>
			<span>আমার বইয়ের তালিকা</span>
		<?php endif; ?>
	</h1>
</div>

<div class="rs-post-count" style="margin-bottom: 1.5rem;">
	মোট <span id="rs-book-count" class="rs-bn"><?php echo count( $books ); ?></span>টি বই
</div>

<main class="rs-wrap rs-main" id="rs-content">
	<div class="rs-list-wrap" id="rs-book-wrap">

		<div style="margin-bottom: 2rem; display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center; align-items: center;">
			<select id="rs-filter-genre" style="padding: 0.5rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit;">
				<option value="">বইয়ের ধরন</option>
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

			<div style="position: relative;">
				<input type="text" id="rs-book-search" placeholder="বই বা লেখক খুঁজুন..."
				       autocomplete="off"
				       style="padding: 0.5rem 0.5rem 0.5rem 2rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); font-family: inherit; width: 200px;"
				/>
				<span style="position: absolute; left: 0.6rem; top: 50%; transform: translateY(-50%); opacity: 0.4; pointer-events: none; font-size: 0.85rem;">🔍</span>
				<div id="rs-search-suggest" style="display:none; position:absolute; top:100%; left:0; right:0; max-height:200px; overflow-y:auto; background:var(--rs-surface); border:1px solid var(--rs-border); border-radius:4px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:10; margin-top:2px;"></div>
			</div>

			<button id="rs-filter-reset" style="padding: 0.5rem 1rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-surface); color: var(--rs-fg); cursor: pointer; font-family: inherit; display: none;">রিসেট</button>
			
			<button id="rs-view-toggle" title="ভিউ পরিবর্তন করুন" style="padding: 0.5rem 0.75rem; border: 1px solid var(--rs-border); border-radius: 4px; background: var(--rs-bg); color: var(--rs-fg); cursor: pointer; font-family: inherit; font-size: 1.1rem; display: flex; align-items: center;">📚</button>
		</div>

		<style>
		/* ---- Bookshelf CSS ---- */
		.rs-list.is-shelf-view {
			display: grid;
			grid-template-columns: repeat(auto-fit, 44px);
			justify-content: center;
			column-gap: 4px;
			row-gap: 45px; /* Space between shelves */
			padding-bottom: 0;
			border-bottom: none;
			margin-top: 2rem;
		}

		@media (max-width: 480px) {
			.rs-list.is-shelf-view {
				grid-template-columns: repeat(5, 44px); /* Exactly 5 books per row on mobile */
			}
		}

		.rs-list.is-shelf-view .rs-row {
			width: 44px;
			height: var(--spine-h, 200px);
			background-color: var(--spine-bg, #666);
			border: none;
			border-radius: 3px 3px 0 0;
			margin: 0;
			padding: 0;
			box-shadow: inset -4px 0 10px rgba(0,0,0,0.2), inset 2px 0 4px rgba(255,255,255,0.15), 1px 0 3px rgba(0,0,0,0.3);
			transition: transform 0.2s, filter 0.2s;
			position: relative;
			overflow: visible; /* Visible so the shelf can extend */
			align-self: end;
		}

		/* Draw the shelf segment under each book */
		.rs-list.is-shelf-view .rs-row::after {
			content: '';
			position: absolute;
			bottom: -12px;
			left: -8px; 
			right: -12px; /* Bridges the 4px gap and overlaps next book */
			height: 12px;
			background: var(--rs-border);
			border-radius: 2px;
			z-index: -1;
			box-shadow: 0 4px 6px rgba(0,0,0,0.05);
		}

		.rs-list.is-shelf-view .rs-row:hover {
			transform: translateY(-10px) scale(1.02);
			filter: brightness(1.2);
			z-index: 2;
		}

		.rs-list.is-shelf-view .rs-row__link {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 100%;
			padding: 0;
			overflow: hidden; /* Hide overflow here instead */
			border-radius: 3px 3px 0 0;
		}

		.rs-list.is-shelf-view .rs-row__head {
			transform: rotate(90deg);
			white-space: nowrap;
			width: var(--spine-h, 200px);
			text-align: center;
			margin: 0;
			padding: 0 10px;
		}

		.rs-list.is-shelf-view .rs-row__title {
			font-size: 0.95rem;
			color: #fff;
			text-shadow: 1px 1px 2px rgba(0,0,0,0.6);
			font-weight: 500;
			display: inline-block;
			width: 100%;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.rs-list.is-shelf-view .rs-row__aside,
		.rs-list.is-shelf-view .rs-row__cat {
			display: none;
		}
		</style>

		<div class="rs-list" id="rs-book-list">
			<?php foreach ( $books as $idx => $book ) : ?>
				<?php 
					$spine_bg = rs_get_spine_color( $book['genre'] );
					$spine_h  = rs_get_spine_height( $book['title'] );
				?>
				<article class="rs-row book-item"
				         data-genre="<?php echo esc_attr( $book['genre'] ); ?>"
				         data-author="<?php echo esc_attr( $book['author'] ); ?>"
				         data-title="<?php echo esc_attr( $book['title'] ); ?>"
				         style="--spine-bg: <?php echo esc_attr( $spine_bg ); ?>; --spine-h: <?php echo esc_attr( $spine_h ); ?>px;">
					<div class="rs-row__link" style="cursor: default;" title="<?php echo esc_attr( $book['title'] . ( $book['author'] ? ' - ' . $book['author'] : '' ) ); ?>">
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

		<nav class="rs-pagination" id="rs-book-pagination" aria-label="পাতা"></nav>

	</div>
</main>

<script>
(function () {
	var PER_PAGE     = 15;
	var filterGenre  = document.getElementById('rs-filter-genre');
	var filterAuthor = document.getElementById('rs-filter-author');
	var searchInput  = document.getElementById('rs-book-search');
	var suggestBox   = document.getElementById('rs-search-suggest');
	var resetBtn     = document.getElementById('rs-filter-reset');
	var viewToggleBtn= document.getElementById('rs-view-toggle');
	var allItems     = [].slice.call(document.querySelectorAll('.book-item'));
	var countDisplay = document.getElementById('rs-book-count');
	var listEl       = document.getElementById('rs-book-list');
	var paginationEl = document.getElementById('rs-book-pagination');
	var wrap         = document.getElementById('rs-book-wrap');

	var filtered  = allItems.slice();
	var curPage   = 1;
	var isShelf   = false;

	/* ---- View Toggle (Shelf vs List) ---- */
	function setView(shelf) {
		isShelf = shelf;
		if (isShelf) {
			listEl.classList.add('is-shelf-view');
			viewToggleBtn.innerHTML = '📄';
			viewToggleBtn.title = 'লিস্ট ভিউ দেখুন';
		} else {
			listEl.classList.remove('is-shelf-view');
			viewToggleBtn.innerHTML = '📚';
			viewToggleBtn.title = 'বুকশেলফ ভিউ দেখুন';
		}
		try { localStorage.setItem('rs_book_view', isShelf ? 'shelf' : 'list'); } catch (e) {}
	}

	try {
		if (localStorage.getItem('rs_book_view') === 'shelf') {
			setView(true);
		}
	} catch (e) {}

	viewToggleBtn.addEventListener('click', function() {
		setView(!isShelf);
	});

	/* Bengali digits */
	function bn(str) {
		var d = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
		return String(str).replace(/[0-9]/g, function(c) { return d[c]; });
	}

	/* ---- Stagger animation (matches the site's existing style) ---- */
	function staggerVisible() {
		var visible = listEl.querySelectorAll('.book-item[style=""],.book-item:not([style])');
		for (var i = 0; i < visible.length; i++) {
			visible[i].style.setProperty('--i', i);
		}
	}

	/* ---- Filtering ---- */
	function filterAll() {
		var genre  = filterGenre.value;
		var author = filterAuthor.value;
		var query  = searchInput.value.trim().toLowerCase();

		filtered = allItems.filter(function(item) {
			if (genre && item.getAttribute('data-genre') !== genre) return false;
			if (author && item.getAttribute('data-author') !== author) return false;
			if (query) {
				var title = item.getAttribute('data-title').toLowerCase();
				var auth  = item.getAttribute('data-author').toLowerCase();
				if (title.indexOf(query) === -1 && auth.indexOf(query) === -1) return false;
			}
			return true;
		});

		curPage = 1;
		render();

		var active = genre || author || query;
		resetBtn.style.display = active ? 'inline-block' : 'none';
	}

	/* ---- Pagination rendering ---- */
	function render() {
		var total = Math.ceil(filtered.length / PER_PAGE) || 1;
		if (curPage > total) curPage = total;

		var start = (curPage - 1) * PER_PAGE;
		var end   = start + PER_PAGE;

		/* Fade out like the site's own pagination */
		wrap.classList.add('is-loading');

		setTimeout(function() {
			/* Hide everything, then show the right slice */
			allItems.forEach(function(item) { item.style.display = 'none'; });
			filtered.forEach(function(item, i) {
				item.style.display = (i >= start && i < end) ? '' : 'none';
			});

			countDisplay.textContent = bn(filtered.length);
			buildPagination(total);
			staggerVisible();

			wrap.classList.remove('is-loading');
		}, 200);
	}

	function buildPagination(total) {
		if (total < 2) {
			paginationEl.innerHTML = '';
			return;
		}

		var html = '';

		/* Prev arrow */
		if (curPage > 1) {
			html += '<a class="rs-pagination__num" href="#" data-page="' + (curPage - 1) + '" aria-label="আগের পাতা">‹</a>';
		} else {
			html += '<span class="rs-pagination__num" style="opacity:.3;pointer-events:none;">‹</span>';
		}

		/* Page numbers */
		for (var p = 1; p <= total; p++) {
			if (p === curPage) {
				html += '<span class="rs-pagination__num is-current" aria-current="page">' + bn(p) + '</span>';
			} else if (p === 1 || p === total || Math.abs(p - curPage) <= 1) {
				html += '<a class="rs-pagination__num" href="#" data-page="' + p + '">' + bn(p) + '</a>';
			} else if (Math.abs(p - curPage) === 2) {
				html += '<span class="rs-pagination__gap" aria-hidden="true">…</span>';
			}
		}

		/* Next arrow */
		if (curPage < total) {
			html += '<a class="rs-pagination__num" href="#" data-page="' + (curPage + 1) + '" aria-label="পরের পাতা">›</a>';
		} else {
			html += '<span class="rs-pagination__num" style="opacity:.3;pointer-events:none;">›</span>';
		}

		paginationEl.innerHTML = html;
	}

	/* ---- Pagination clicks ---- */
	paginationEl.addEventListener('click', function(e) {
		var link = e.target.closest('[data-page]');
		if (!link) return;
		e.preventDefault();

		curPage = parseInt(link.getAttribute('data-page'), 10);
		render();

		/* Scroll to list top like the site's existing pagination */
		var header = document.querySelector('.rs-header');
		var clear  = header ? header.offsetHeight : 0;
		var top    = wrap.getBoundingClientRect().top + window.pageYOffset - clear - 16;
		window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
	});

	/* ---- Live search with suggestions ---- */
	searchInput.addEventListener('input', function() {
		var q = this.value.trim().toLowerCase();

		if (q.length < 1) {
			suggestBox.style.display = 'none';
			filterAll();
			return;
		}

		/* Build suggestions from titles and authors */
		var seen = {};
		var suggestions = [];

		allItems.forEach(function(item) {
			var title  = item.getAttribute('data-title');
			var author = item.getAttribute('data-author');

			if (title.toLowerCase().indexOf(q) > -1 && !seen[title]) {
				seen[title] = true;
				suggestions.push({ text: title, type: 'বই' });
			}
			if (author && author.toLowerCase().indexOf(q) > -1 && !seen[author]) {
				seen[author] = true;
				suggestions.push({ text: author, type: 'লেখক' });
			}
		});

		if (suggestions.length > 0 && q.length > 0) {
			var html = '';
			suggestions.slice(0, 8).forEach(function(s) {
				html += '<div class="rs-suggest-item" data-value="' + s.text.replace(/"/g, '&quot;') + '"'
				     + ' style="padding:0.5rem 0.75rem;cursor:pointer;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--rs-border-40);font-size:0.85rem;">'
				     + '<span>' + s.text + '</span>'
				     + '<small style="opacity:0.5;font-size:0.75rem;">' + s.type + '</small>'
				     + '</div>';
			});
			suggestBox.innerHTML = html;
			suggestBox.style.display = 'block';
		} else {
			suggestBox.style.display = 'none';
		}

		filterAll();
	});

	/* Click a suggestion */
	suggestBox.addEventListener('click', function(e) {
		var item = e.target.closest('.rs-suggest-item');
		if (!item) return;
		searchInput.value = item.getAttribute('data-value');
		suggestBox.style.display = 'none';
		filterAll();
	});

	/* Close suggestions on outside click */
	document.addEventListener('click', function(e) {
		if (!suggestBox.contains(e.target) && e.target !== searchInput) {
			suggestBox.style.display = 'none';
		}
	});

	/* Hover highlight on suggestions */
	suggestBox.addEventListener('mouseover', function(e) {
		var item = e.target.closest('.rs-suggest-item');
		if (item) item.style.background = 'var(--rs-muted)';
	});
	suggestBox.addEventListener('mouseout', function(e) {
		var item = e.target.closest('.rs-suggest-item');
		if (item) item.style.background = '';
	});

	/* ---- Filter change handlers ---- */
	filterGenre.addEventListener('change', function() { searchInput.value = ''; filterAll(); });
	filterAuthor.addEventListener('change', function() { searchInput.value = ''; filterAll(); });

	resetBtn.addEventListener('click', function() {
		filterGenre.value  = '';
		filterAuthor.value = '';
		searchInput.value  = '';
		suggestBox.style.display = 'none';
		filterAll();
	});

	/* ---- Initial render ---- */
	render();
})();
</script>

<?php
get_footer();
