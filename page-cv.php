<?php
/**
 * Template Name: CV
 *
 * The résumé as a bento sheet on the portfolio's dark stage, with a printable
 * version and the downloadable PDF a click away. The content comes from
 * rs_cv_data() in inc/17-cv.php and is the same in both editions; only the
 * header and footer around it change language.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* The same dark stage as the portfolio, so the header, the footer and the
   floating controls follow it. */
add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'rs-stage';
		$classes[] = 'rs-stage--cv';
		return $classes;
	}
);

get_header();

$rs_cv  = rs_cv_data();
$rs_pdf = rs_cv_pdf();

$rs_icon_download = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M4 19h16"/></svg>';
$rs_icon_print    = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7" rx="1"/></svg>';
$rs_icon_out      = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>';
?>

<main class="rs-pf rs-cv" id="rs-content">
	<div class="rs-pf__glow rs-pf__glow--a" aria-hidden="true"></div>
	<div class="rs-pf__glow rs-pf__glow--b" aria-hidden="true"></div>

	<header class="rs-pf__wrap rs-cv__hero">
		<p class="rs-pf__eyebrow"><?php echo esc_html( $rs_cv['labels']['hero_eyebrow'] ); ?></p>
		<h1 class="rs-pf__title rs-cv__title"><?php echo esc_html( $rs_cv['name'] ); ?></h1>
		<ul class="rs-cv__roles">
			<?php foreach ( $rs_cv['roles'] as $rs_role ) : ?>
				<li><?php echo esc_html( $rs_role ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p class="rs-pf__bio"><?php echo esc_html( $rs_cv['summary'] ); ?></p>

		<div class="rs-pf__actions">
			<?php if ( $rs_pdf ) : ?>
				<a class="rs-pf-btn rs-pf-btn--primary" href="<?php echo esc_url( $rs_pdf ); ?>" download="<?php echo esc_attr( $rs_cv['pdf_filename'] ? $rs_cv['pdf_filename'] : 'Raisul_Sohan_CV.pdf' ); ?>">
					<?php echo $rs_icon_download; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					<?php echo esc_html( $rs_cv['labels']['download_pdf'] ); ?>
				</a>
			<?php endif; ?>
			<button type="button" class="rs-pf-btn rs-pf-btn--ghost" data-rs-cv-print>
				<?php echo $rs_icon_print; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				<?php echo esc_html( $rs_cv['labels']['print'] ); ?>
			</button>
			<?php /* Copies the address, the same way the mail icon in the header does:
			   a mailto: link only helps readers with a mail app set up. */ ?>
			<?php if ( $rs_cv['email'] ) : ?>
			<button type="button" class="rs-pf-btn rs-pf-btn--ghost" data-rs-copy="<?php echo esc_attr( $rs_cv['email'] ); ?>" data-rs-copy-kind="mail" title="<?php echo esc_attr( $rs_cv['email'] ); ?>" aria-label="<?php echo esc_attr( $rs_cv['labels']['copy_email'] . ': ' . $rs_cv['email'] ); ?>">
				<?php echo wp_kses( rs_icon( 'mail', 17 ), rs_svg_tags() ); ?>
				<?php echo esc_html( $rs_cv['labels']['email'] ); ?>
			</button>
			<?php endif; ?>
			<?php if ( $rs_cv['whatsapp'] ) : ?>
			<a class="rs-pf-btn rs-pf-btn--ghost" href="<?php echo esc_url( $rs_cv['whatsapp'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php echo esc_html( $rs_cv['labels']['whatsapp'] ); ?>
			</a>
			<?php endif; ?>
			<?php if ( $rs_cv['linkedin_url'] ) : ?>
			<a class="rs-pf-btn rs-pf-btn--ghost" href="<?php echo esc_url( $rs_cv['linkedin_url'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php echo wp_kses( rs_icon( 'linkedin', 17 ), rs_svg_tags() ); ?>
				<?php echo esc_html( $rs_cv['labels']['linkedin'] ); ?>
			</a>
			<?php endif; ?>
		</div>

		<dl class="rs-pf__stats">
			<?php foreach ( $rs_cv['stats'] as $rs_stat ) : ?>
				<div>
					<dt><?php echo esc_html( $rs_stat['label'] ); ?></dt>
					<dd><?php echo esc_html( $rs_stat['value'] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</header>

	<section class="rs-pf__wrap rs-cv__showcase" aria-labelledby="rs-cv-showcase-title">
		<div class="rs-cv__showcase-head">
			<div>
				<p class="rs-pf__eyebrow"><?php echo esc_html( $rs_cv['labels']['selected_work'] ); ?></p>
				<h2 class="rs-cv__showcase-title" id="rs-cv-showcase-title"><?php echo esc_html( $rs_cv['labels']['showcase_title'] ); ?></h2>
			</div>
			<p class="rs-cv__showcase-intro"><?php echo esc_html( $rs_cv['labels']['showcase_intro'] ); ?></p>
		</div>
		<div class="rs-cv__projects">
			<?php foreach ( $rs_cv['films'] as $rs_index => $rs_film ) : ?>
				<?php if ( empty( $rs_film['url'] ) ) { continue; } ?>
				<a class="rs-cv__project" href="<?php echo esc_url( $rs_film['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="rs-cv__project-top"><span><?php echo esc_html( $rs_film['meta'] ); ?></span><span class="rs-cv__project-index"><?php echo esc_html( sprintf( '%02d', $rs_index + 1 ) ); ?></span></span>
					<h3 class="rs-cv__project-title"><?php echo esc_html( $rs_film['title'] ); ?></h3>
					<span class="rs-cv__project-text"><?php echo rs_cv_rich( $rs_film['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in rs_cv_rich(). ?></span>
					<span class="rs-cv__project-link"><?php echo esc_html( $rs_cv['labels']['view_project'] ); ?> <?php echo $rs_icon_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="rs-pf__wrap rs-cv__section" aria-label="<?php echo esc_attr( $rs_cv['labels']['sheet_aria_label'] ); ?>">
		<article class="rs-cv__sheet" id="rs-cv-sheet">
			<header class="rs-cv__head">
				<div>
					<p class="rs-cv__name"><?php echo esc_html( $rs_cv['name'] ); ?></p>
					<p class="rs-cv__tag"><?php echo esc_html( implode( '  ·  ', $rs_cv['roles'] ) ); ?></p>
				</div>
				<ul class="rs-cv__contact">
					<?php if ( $rs_cv['location'] ) : ?><li><?php echo esc_html( $rs_cv['location'] ); ?></li><?php endif; ?>
					<?php if ( $rs_cv['phone'] ) : ?><li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $rs_cv['phone'] ) ); ?>"><?php echo esc_html( $rs_cv['phone'] ); ?></a></li><?php endif; ?>
					<?php if ( $rs_cv['email'] ) : ?><li><a href="mailto:<?php echo esc_attr( $rs_cv['email'] ); ?>"><?php echo esc_html( $rs_cv['email'] ); ?></a></li><?php endif; ?>
					<?php foreach ( $rs_cv['links'] as $rs_link ) : ?>
						<?php if ( ! empty( $rs_link['label'] ) && ! empty( $rs_link['url'] ) ) : ?><li><a href="<?php echo esc_url( $rs_link['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $rs_link['label'] ); ?></a></li><?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</header>

			<?php /* Each section is its own bento tile. The experience list spans
			   the full row so a taller reference column cannot leave a blank void
			   beside the last job. */ ?>
			<div class="rs-cv__grid">
				<section class="rs-cv__card rs-cv__card--profile" aria-labelledby="rs-cv-profile-title">
					<h2 class="rs-cv__h" id="rs-cv-profile-title"><?php echo esc_html( $rs_cv['labels']['profile'] ); ?></h2>
					<p class="rs-cv__lede"><?php echo esc_html( $rs_cv['profile'] ); ?></p>
				</section>

				<section class="rs-cv__card rs-cv__card--highlights" aria-labelledby="rs-cv-highlights-title">
					<h2 class="rs-cv__h" id="rs-cv-highlights-title"><?php echo esc_html( $rs_cv['labels']['highlights'] ); ?></h2>
					<ul class="rs-cv__hl">
						<?php foreach ( $rs_cv['highlights'] as $rs_hl ) : ?>
							<li><strong><?php echo esc_html( $rs_hl['lead'] ); ?></strong> <?php echo esc_html( $rs_hl['text'] ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>

				<section class="rs-cv__card rs-cv__card--experience" aria-labelledby="rs-cv-experience-title">
					<h2 class="rs-cv__h" id="rs-cv-experience-title"><?php echo esc_html( $rs_cv['labels']['experience'] ); ?></h2>
					<div class="rs-cv__jobs">
						<?php foreach ( $rs_cv['experience'] as $rs_job ) : ?>
							<section class="rs-cv__job">
								<div class="rs-cv__job-head">
									<h3 class="rs-cv__job-title"><?php echo esc_html( $rs_job['title'] ); ?></h3>
									<span class="rs-cv__job-dates"><?php echo esc_html( $rs_job['dates'] ); ?></span>
								</div>
								<p class="rs-cv__job-org"><?php echo esc_html( $rs_job['org'] . ' · ' . $rs_job['place'] ); ?></p>
								<ul class="rs-cv__list">
									<?php foreach ( $rs_job['bullets'] as $rs_line ) : ?>
										<li><?php echo rs_cv_rich( $rs_line ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in rs_cv_rich(). ?></li>
									<?php endforeach; ?>
								</ul>
							</section>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="rs-cv__card rs-cv__card--tools" aria-labelledby="rs-cv-tools-title">
					<h2 class="rs-cv__h" id="rs-cv-tools-title"><?php echo esc_html( $rs_cv['labels']['open_source_tools'] ); ?> <small><?php echo esc_html( $rs_cv['labels']['tools_note'] ); ?></small></h2>
					<div class="rs-cv__tools">
						<?php foreach ( $rs_cv['tools'] as $rs_tool ) : ?>
							<div class="rs-cv__tool">
						<?php if ( ! empty( $rs_tool['url'] ) ) : ?><a class="rs-cv__tool-name" href="<?php echo esc_url( $rs_tool['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $rs_tool['name'] ); ?></a><?php else : ?><span class="rs-cv__tool-name"><?php echo esc_html( $rs_tool['name'] ); ?></span><?php endif; ?>
								<span class="rs-cv__tool-kind"><?php echo esc_html( $rs_tool['kind'] ); ?></span>
								<span><?php echo esc_html( $rs_tool['text'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="rs-cv__card rs-cv__card--skills" aria-labelledby="rs-cv-skills-title">
					<h2 class="rs-cv__h" id="rs-cv-skills-title"><?php echo esc_html( $rs_cv['labels']['skills'] ); ?></h2>
					<div class="rs-cv__skills">
						<?php foreach ( $rs_cv['skills'] as $rs_group ) : ?>
							<section class="rs-cv__skill">
								<h3 class="rs-cv__skill-group"><?php echo esc_html( $rs_group['group'] ); ?></h3>
								<ul class="rs-cv__list rs-cv__list--tight">
									<?php foreach ( $rs_group['items'] as $rs_item ) : ?>
										<li><?php echo esc_html( $rs_item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</section>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="rs-cv__card rs-cv__card--learning" aria-labelledby="rs-cv-education-title">
					<h2 class="rs-cv__h" id="rs-cv-education-title"><?php echo esc_html( $rs_cv['labels']['education'] ); ?></h2>
					<div class="rs-cv__education">
						<?php foreach ( $rs_cv['education'] as $rs_edu ) : ?>
							<p class="rs-cv__edu"><strong><?php echo esc_html( $rs_edu['lead'] ); ?></strong><span><?php echo esc_html( $rs_edu['text'] ); ?></span></p>
						<?php endforeach; ?>
					</div>
					<div class="rs-cv__languages">
						<h3 class="rs-cv__skill-group"><?php echo esc_html( $rs_cv['labels']['languages'] ); ?></h3>
						<p><?php echo esc_html( $rs_cv['languages'] ); ?></p>
					</div>
				</section>
			</div>
		</article>

		<p class="rs-cv__note">
			<?php echo esc_html( $rs_cv['labels']['updated_prefix'] . ' ' . $rs_cv['updated'] ); ?>
			<?php if ( $rs_pdf ) : ?>
				&nbsp;·&nbsp; <a href="<?php echo esc_url( $rs_pdf ); ?>" download="<?php echo esc_attr( $rs_cv['pdf_filename'] ? $rs_cv['pdf_filename'] : 'Raisul_Sohan_CV.pdf' ); ?>"><?php echo esc_html( $rs_cv['labels']['also_pdf'] ); ?></a>
			<?php endif; ?>
			<?php if ( $rs_cv['work_url'] ) : ?>&nbsp;·&nbsp; <a href="<?php echo esc_url( $rs_cv['work_url'] ); ?>"><?php echo esc_html( $rs_cv['labels']['see_work'] ); ?><?php echo $rs_icon_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></a><?php endif; ?>
		</p>
	</section>
</main>

<script>
/* The Print button: the stylesheet's print rules turn the sheet into the
   document, so this is all the button has to do. */
(function () {
	var button = document.querySelector('[data-rs-cv-print]');
	if (button) {
		button.addEventListener('click', function () {
			window.print();
		});
	}
}());
</script>

<?php
get_footer();
