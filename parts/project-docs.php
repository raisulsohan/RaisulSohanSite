<?php
/**
 * A page of a project's documentation.
 *
 * Included by page-portfolio.php, whose $rs_is_en and $rs_arrow_out it
 * uses. The writing itself is the project's own docs, read from its
 * repository (inc/16-project-documentation.php); this is the page around
 * it: where you are, the other pages, and the way on.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pd       = rs_current_doc();
$pd_proj  = $pd['project'];
$pd_name  = rs_project_name( $pd_proj );
$pd_index = $pd['index'];
$pd_html  = rs_current_doc_html();
$pd_acc   = sanitize_hex_color( $pd_proj['accent'] );
$pd_acc   = $pd_acc ? $pd_acc : '#6c4cff';
$pd_order = array_values( array_filter( (array) $pd_index['order'], 'is_string' ) );
$pd_at    = array_search( $pd['key'], $pd_order, true );
$pd_prev  = ( false !== $pd_at && $pd_at > 0 ) ? $pd_order[ $pd_at - 1 ] : null;
$pd_next  = ( false !== $pd_at && $pd_at < count( $pd_order ) - 1 ) ? $pd_order[ $pd_at + 1 ] : null;
$pd_toc   = $pd_html ? rs_docs_sections( $pd_html ) : array();
$pd_arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
?>
<main class="rs-pf rs-pf--docs" id="rs-content">
	<div class="rs-pf__glow rs-pf__glow--a" aria-hidden="true"></div>
	<div class="rs-pf__glow rs-pf__glow--b" aria-hidden="true"></div>

	<div class="rs-pf__wrap rs-doc" style="--a: <?php echo esc_attr( $pd_acc ); ?>">
		<nav class="rs-doc__crumbs" aria-label="<?php echo esc_attr( $rs_is_en ? 'Breadcrumb' : 'অবস্থান' ); ?>">
			<a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>"><?php echo esc_html( $rs_is_en ? 'All work' : 'সব কাজ' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( rs_project_url( $pd_proj['id'] ) ); ?>"><?php echo esc_html( $pd_name[0] ); ?></a>
			<span aria-hidden="true">/</span>
			<?php if ( '' === $pd['key'] ) : ?>
				<span aria-current="page"><?php echo esc_html( $rs_is_en ? 'Documentation' : 'ডকুমেন্টেশন' ); ?></span>
			<?php else : ?>
				<a href="<?php echo esc_url( rs_project_docs_url( $pd_proj['id'] ) ); ?>"><?php echo esc_html( $rs_is_en ? 'Documentation' : 'ডকুমেন্টেশন' ); ?></a>
			<?php endif; ?>
		</nav>

		<div class="rs-doc__layout">
			<aside class="rs-doc__side">
				<p class="rs-doc__label"><?php echo esc_html( $pd_name[0] . ( $rs_is_en ? ' documentation' : ' ডকুমেন্টেশন' ) ); ?></p>
				<nav aria-label="<?php echo esc_attr( $rs_is_en ? 'Documentation pages' : 'ডকুমেন্টেশনের পাতা' ); ?>">
					<ol class="rs-doc__pages">
						<?php foreach ( $pd_order as $pd_key ) : ?>
							<?php
							if ( ! isset( $pd_index['pages'][ $pd_key ] ) ) {
								continue;
							}
							$pd_here  = $pd_key === $pd['key'];
							$pd_item  = $pd_index['pages'][ $pd_key ];
							$pd_title = '' === $pd_key ? ( $rs_is_en ? 'Overview' : 'শুরু' ) : ( ! empty( $pd_item['label'] ) ? $pd_item['label'] : $pd_item['title'] );
							?>
							<li<?php echo $pd_here ? ' class="is-here"' : ''; ?>>
								<a href="<?php echo esc_url( rs_project_docs_url( $pd_proj['id'], $pd_key ) ); ?>"<?php echo $pd_here ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $pd_title ); ?></a>
								<?php if ( $pd_here && count( $pd_toc ) > 1 ) : ?>
									<ol class="rs-doc__toc">
										<?php foreach ( $pd_toc as $pd_section ) : ?>
											<li><a href="#<?php echo esc_attr( $pd_section['id'] ); ?>"><?php echo esc_html( $pd_section['text'] ); ?></a></li>
										<?php endforeach; ?>
									</ol>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</nav>
				<a class="rs-doc__source" href="<?php echo esc_url( $pd['page']['html'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php if ( ! empty( $pd['page']['made'] ) ) : ?>
						<?php echo esc_html( $rs_is_en ? 'These docs on GitHub' : 'ডকুমেন্টেশন GitHub-এ' ); ?>
					<?php else : ?>
						<?php echo esc_html( $rs_is_en ? 'This page on GitHub' : 'এই পাতা GitHub-এ' ); ?>
					<?php endif; ?>
					<?php echo $rs_arrow_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</a>
			</aside>

			<article class="rs-doc__body" lang="<?php echo esc_attr( ! empty( $pd['page']['lang'] ) ? $pd['page']['lang'] : 'en' ); ?>">
				<?php if ( $pd_html ) : ?>
					<?php echo $pd_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Passed through wp_kses_post() in rs_docs_prepare(). ?>
				<?php else : ?>
					<h1><?php echo esc_html( $pd['page']['title'] ); ?></h1>
					<p lang="<?php echo $rs_is_en ? 'en' : 'bn'; ?>">
						<?php echo esc_html( $rs_is_en ? 'This page could not be fetched from GitHub just now. It is there to read in the meantime:' : 'এই মুহূর্তে পাতাটা GitHub থেকে আনা গেল না। ততক্ষণ সেখানেই পড়া যাবে:' ); ?>
						<a href="<?php echo esc_url( $pd['page']['html'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $pd['page']['path'] ); ?></a>
					</p>
				<?php endif; ?>

				<?php if ( null !== $pd_prev || null !== $pd_next ) : ?>
					<nav class="rs-doc__steps" aria-label="<?php echo esc_attr( $rs_is_en ? 'Previous and next page' : 'আগের ও পরের পাতা' ); ?>">
						<?php foreach ( array( array( $pd_prev, $rs_is_en ? 'Previous' : 'আগের পাতা', 'is-prev' ), array( $pd_next, $rs_is_en ? 'Next' : 'পরের পাতা', 'is-next' ) ) as $pd_step ) : ?>
							<?php if ( null !== $pd_step[0] && isset( $pd_index['pages'][ $pd_step[0] ] ) ) : ?>
								<a class="rs-doc__step <?php echo esc_attr( $pd_step[2] ); ?>" href="<?php echo esc_url( rs_project_docs_url( $pd_proj['id'], $pd_step[0] ) ); ?>">
									<span lang="<?php echo $rs_is_en ? 'en' : 'bn'; ?>"><?php echo esc_html( $pd_step[1] ); ?></span>
									<strong><?php echo esc_html( '' === $pd_step[0] ? ( $rs_is_en ? 'Overview' : 'শুরু' ) : $pd_index['pages'][ $pd_step[0] ]['title'] ); ?></strong>
									<?php echo $pd_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
								</a>
							<?php else : ?>
								<span class="rs-doc__step is-empty" aria-hidden="true"></span>
							<?php endif; ?>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>
			</article>
		</div>
	</div>
</main>
<script>
/* On a phone the row of pages starts at the page you are on; beside the
   writing, the list of sections follows the one being read. */
(function () {
	'use strict';

	var row  = document.querySelector('.rs-doc__pages');
	var here = row && row.querySelector('.is-here');

	if (row && here && row.scrollWidth > row.clientWidth) {
		row.scrollLeft = here.getBoundingClientRect().left - row.getBoundingClientRect().left - 16;
	}

	var links = document.querySelectorAll('.rs-doc__toc a');

	if (!links.length || !('IntersectionObserver' in window)) {
		return;
	}

	var byId = {};
	var on   = null;

	Array.prototype.forEach.call(links, function (a) {
		byId[a.getAttribute('href').slice(1)] = a;
	});

	var watch = new window.IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (entry.isIntersecting && byId[entry.target.id]) {
				if (on) {
					on.classList.remove('is-on');
				}
				on = byId[entry.target.id];
				on.classList.add('is-on');
			}
		});
	}, { rootMargin: '-15% 0px -70% 0px' });

	Object.keys(byId).forEach(function (id) {
		var heading = document.getElementById(id);

		if (heading) {
			watch.observe(heading);
		}
	});
}());
</script>
