<?php
/**
 * Template Name: CV
 *
 * The résumé as a spec sheet on the portfolio's dark stage, with a printable
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
$rs_icon_view     = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
$rs_icon_out      = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>';

/* One glyph per kind of contact entry; rs_cv_contact_items() names them. */
$rs_contact_svg   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>';
$rs_contact_icons = array(
	'pin'       => sprintf( $rs_contact_svg, '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>' ),
	'phone'     => sprintf( $rs_contact_svg, '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>' ),
	'whatsapp'  => sprintf( $rs_contact_svg, '<path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/>' ),
	'mail'      => sprintf( $rs_contact_svg, '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>' ),
	'globe'     => sprintf( $rs_contact_svg, '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>' ),
	'linkedin'  => sprintf( $rs_contact_svg, '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>' ),
	'github'    => sprintf( $rs_contact_svg, '<path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.4 5.4 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/>' ),
	'youtube'   => sprintf( $rs_contact_svg, '<path d="M2.5 17a24.1 24.1 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.6 49.6 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.1 24.1 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.6 49.6 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>' ),
	'instagram' => sprintf( $rs_contact_svg, '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><path d="M17.5 6.5h.01"/>' ),
	'play'      => sprintf( $rs_contact_svg, '<circle cx="12" cy="12" r="10"/><path d="m10 8 6 4-6 4Z"/>' ),
);
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
			<?php /* The same PDF on Google Drive: read it in the browser, on a
			   phone too, without saving a file. */ ?>
			<?php if ( $rs_cv['pdf_view_url'] ) : ?>
				<a class="rs-pf-btn rs-pf-btn--ghost" href="<?php echo esc_url( $rs_cv['pdf_view_url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo $rs_icon_view; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					<?php echo esc_html( $rs_cv['labels']['view_pdf'] ); ?>
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
				<a class="rs-cv__project" href="<?php echo esc_url( rs_cv_resolve_url( $rs_film['url'] ) ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="rs-cv__project-top"><span><?php echo esc_html( $rs_film['meta'] ); ?></span><span class="rs-cv__project-index"><?php echo esc_html( sprintf( '%02d', $rs_index + 1 ) ); ?></span></span>
					<h3 class="rs-cv__project-title"><?php echo esc_html( $rs_film['title'] ); ?></h3>
					<span class="rs-cv__project-text"><?php echo rs_cv_rich( $rs_film['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in rs_cv_rich(). ?></span>
					<span class="rs-cv__project-link"><?php echo esc_html( $rs_cv['labels']['view_project'] ); ?> <?php echo $rs_icon_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="rs-pf__wrap rs-cv__section" aria-labelledby="rs-cv-sheet-title">
		<div class="rs-cv__sheet-head">
			<h2 class="rs-cv__sheet-title" id="rs-cv-sheet-title"><?php echo esc_html( $rs_cv['labels']['sheet_title'] ); ?></h2>
			<p class="rs-cv__updated"><?php echo esc_html( $rs_cv['labels']['updated_prefix'] . ' ' . $rs_cv['updated'] ); ?></p>
		</div>

		<?php /* Laid out like Apple's spec sheets: each part of the CV is one
		   row, its name on the left and its content on the right, with a
		   hairline between rows and no box inside a box. Print turns the
		   same rows into the paper CV, with the name and roles on top. */ ?>
		<article class="rs-cv__sheet" id="rs-cv-sheet" aria-label="<?php echo esc_attr( $rs_cv['labels']['sheet_aria_label'] ); ?>">
			<header class="rs-cv__head">
				<p class="rs-cv__name"><?php echo esc_html( $rs_cv['name'] ); ?></p>
				<p class="rs-cv__tag"><?php echo esc_html( implode( '  ·  ', $rs_cv['roles'] ) ); ?></p>
			</header>

			<section class="rs-cv__row rs-cv__row--contact" aria-labelledby="rs-cv-contact-title">
				<h3 class="rs-cv__h" id="rs-cv-contact-title"><?php echo esc_html( $rs_cv['labels']['contact'] ); ?></h3>
				<ul class="rs-cv__contact">
					<?php foreach ( rs_cv_contact_items( $rs_cv ) as $rs_item ) : ?>
						<?php
						$rs_inner = '<span class="rs-cv__contact-icon" aria-hidden="true">' . ( isset( $rs_contact_icons[ $rs_item['icon'] ] ) ? $rs_contact_icons[ $rs_item['icon'] ] : '' ) . '</span>'
							. '<span class="rs-cv__contact-key">' . esc_html( $rs_item['key'] ) . '</span>'
							. '<span class="rs-cv__contact-value">' . esc_html( $rs_item['value'] ) . '</span>';
						?>
						<li>
							<?php if ( $rs_item['url'] ) : ?>
								<a class="rs-cv__contact-item" href="<?php echo esc_url( $rs_item['url'] ); ?>"<?php echo $rs_item['external'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo $rs_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG and escaped text. ?></a>
							<?php else : ?>
								<span class="rs-cv__contact-item"><?php echo $rs_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG and escaped text. ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="rs-cv__row" aria-labelledby="rs-cv-profile-title">
				<h3 class="rs-cv__h" id="rs-cv-profile-title"><?php echo esc_html( $rs_cv['labels']['profile'] ); ?></h3>
				<p class="rs-cv__lede"><?php echo esc_html( $rs_cv['profile'] ); ?></p>
			</section>

			<section class="rs-cv__row" aria-labelledby="rs-cv-highlights-title">
				<h3 class="rs-cv__h" id="rs-cv-highlights-title"><?php echo esc_html( $rs_cv['labels']['highlights'] ); ?></h3>
				<ul class="rs-cv__hl">
					<?php foreach ( $rs_cv['highlights'] as $rs_hl ) : ?>
						<?php $rs_hl_parts = rs_cv_highlight_parts( $rs_hl['lead'] ); ?>
						<li>
							<?php if ( $rs_hl_parts['figure'] ) : ?><span class="rs-cv__hl-figure"><?php echo esc_html( $rs_hl_parts['figure'] ); ?></span><?php endif; ?>
							<strong class="rs-cv__hl-lead"><?php echo esc_html( $rs_hl_parts['words'] ); ?></strong>
							<span class="rs-cv__hl-text"><?php echo esc_html( $rs_hl['text'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="rs-cv__row" aria-labelledby="rs-cv-experience-title">
				<h3 class="rs-cv__h" id="rs-cv-experience-title"><?php echo esc_html( $rs_cv['labels']['experience'] ); ?></h3>
				<div class="rs-cv__jobs">
					<?php foreach ( $rs_cv['experience'] as $rs_job ) : ?>
						<section class="rs-cv__job">
							<div class="rs-cv__job-head">
								<h4 class="rs-cv__job-title"><?php echo esc_html( $rs_job['title'] ); ?></h4>
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

			<section class="rs-cv__row" aria-labelledby="rs-cv-tools-title">
				<h3 class="rs-cv__h" id="rs-cv-tools-title"><?php echo esc_html( $rs_cv['labels']['open_source_tools'] ); ?> <small><?php echo esc_html( $rs_cv['labels']['tools_note'] ); ?></small></h3>
				<div class="rs-cv__tools">
					<?php foreach ( $rs_cv['tools'] as $rs_tool ) : ?>
						<?php
						/* The tool's own pages are its names; a GitHub link is
						   set apart as a small "GitHub" pill after them. */
						$rs_names = array();
						$rs_repos = array();
						foreach ( ! empty( $rs_tool['links'] ) ? $rs_tool['links'] : array() as $rs_tool_link ) {
							$rs_label = ! empty( $rs_tool_link['label'] ) ? $rs_tool_link['label'] : $rs_tool['name'];
							$rs_url   = ! empty( $rs_tool_link['url'] ) ? rs_cv_resolve_url( $rs_tool_link['url'] ) : '';
							if ( $rs_url && false !== stripos( (string) wp_parse_url( $rs_url, PHP_URL_HOST ), 'github.com' ) ) {
								$rs_repos[] = array( $rs_label, $rs_url );
							} else {
								$rs_names[] = array( $rs_label, $rs_url );
							}
						}
						if ( ! $rs_names ) {
							array_unshift( $rs_names, array( $rs_tool['name'], '' ) );
						}
						?>
						<div class="rs-cv__tool">
							<div class="rs-cv__tool-names">
								<?php foreach ( $rs_names as $rs_name_index => $rs_name ) : ?>
									<?php if ( $rs_name_index ) : ?><span class="rs-cv__tool-name-separator" aria-hidden="true">·</span><?php endif; ?>
									<?php if ( $rs_name[1] ) : ?><a class="rs-cv__tool-name" href="<?php echo esc_url( $rs_name[1] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $rs_name[0] ); ?></a><?php else : ?><span class="rs-cv__tool-name"><?php echo esc_html( $rs_name[0] ); ?></span><?php endif; ?>
								<?php endforeach; ?>
								<?php foreach ( $rs_repos as $rs_repo ) : ?>
									<a class="rs-cv__tool-repo" href="<?php echo esc_url( $rs_repo[1] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $rs_repo[0] ); ?><?php echo $rs_icon_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></a>
								<?php endforeach; ?>
							</div>
							<span class="rs-cv__tool-kind"><?php echo esc_html( $rs_tool['kind'] ); ?></span>
							<p class="rs-cv__tool-text"><?php echo esc_html( $rs_tool['text'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="rs-cv__row" aria-labelledby="rs-cv-skills-title">
				<h3 class="rs-cv__h" id="rs-cv-skills-title"><?php echo esc_html( $rs_cv['labels']['skills'] ); ?></h3>
				<div class="rs-cv__skills">
					<?php foreach ( $rs_cv['skills'] as $rs_group ) : ?>
						<section class="rs-cv__skill">
							<h4 class="rs-cv__skill-group"><?php echo esc_html( $rs_group['group'] ); ?></h4>
							<ul class="rs-cv__plain">
								<?php foreach ( $rs_group['items'] as $rs_item ) : ?>
									<li><?php echo esc_html( $rs_item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="rs-cv__row" aria-labelledby="rs-cv-education-title">
				<h3 class="rs-cv__h" id="rs-cv-education-title"><?php echo esc_html( $rs_cv['labels']['education'] ); ?></h3>
				<div class="rs-cv__education">
					<?php foreach ( $rs_cv['education'] as $rs_edu ) : ?>
						<p class="rs-cv__edu"><strong><?php echo esc_html( $rs_edu['lead'] ); ?></strong><span><?php echo esc_html( $rs_edu['text'] ); ?></span></p>
					<?php endforeach; ?>
				</div>
			</section>

			<?php if ( $rs_cv['languages'] ) : ?>
			<section class="rs-cv__row" aria-labelledby="rs-cv-languages-title">
				<h3 class="rs-cv__h" id="rs-cv-languages-title"><?php echo esc_html( $rs_cv['labels']['languages'] ); ?></h3>
				<p class="rs-cv__languages"><?php echo esc_html( $rs_cv['languages'] ); ?></p>
			</section>
			<?php endif; ?>
		</article>

		<?php if ( $rs_pdf || $rs_cv['work_url'] ) : ?>
		<p class="rs-cv__note">
			<?php if ( $rs_pdf ) : ?>
				<a href="<?php echo esc_url( $rs_pdf ); ?>" download="<?php echo esc_attr( $rs_cv['pdf_filename'] ? $rs_cv['pdf_filename'] : 'Raisul_Sohan_CV.pdf' ); ?>"><?php echo esc_html( $rs_cv['labels']['also_pdf'] ); ?></a>
			<?php endif; ?>
			<?php if ( $rs_pdf && $rs_cv['pdf_view_url'] ) : ?>&nbsp;·&nbsp;<?php endif; ?>
			<?php if ( $rs_cv['pdf_view_url'] ) : ?><a href="<?php echo esc_url( $rs_cv['pdf_view_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $rs_cv['labels']['view_pdf'] ); ?><?php echo $rs_icon_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></a><?php endif; ?>
			<?php if ( ( $rs_pdf || $rs_cv['pdf_view_url'] ) && $rs_cv['work_url'] ) : ?>&nbsp;·&nbsp;<?php endif; ?>
			<?php if ( $rs_cv['work_url'] ) : ?><a href="<?php echo esc_url( rs_cv_resolve_url( $rs_cv['work_url'] ) ); ?>"><?php echo esc_html( $rs_cv['labels']['see_work'] ); ?><?php echo $rs_icon_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></a><?php endif; ?>
		</p>
		<?php endif; ?>
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
