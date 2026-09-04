<?php
/**
 * Template Name: Portfolio
 *
 * Multidisciplinary portfolio showcase: Video Editing, Motion Animation,
 * Web Development, and Browser Extensions / Plugins / Scripts.
 *
 * Features interactive Case Study Pop-up Modals, screenshot support,
 * and direct preview links. Fully localized for Bengali and English.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$rs_is_en = rs_is_en();
$rs_hero  = rs_hero_image();

/* ---- Portfolio Projects & Case Studies Data (Dynamic Dashboard CPT & Fallback) ---- */
$projects = function_exists( 'rs_get_portfolio_projects' ) ? rs_get_portfolio_projects() : array();
?>

<div class="rs-hero<?php echo $rs_hero ? ' rs-hero--image' : ''; ?>">
	<h1 class="rs-hero__title">
		<?php if ( $rs_hero ) : ?>
			<?php echo rs_render_hero_image_html( $rs_is_en ? 'Portfolio' : 'পোর্টফোলিও' ); ?>
		<?php else : ?>
			<span><?php echo esc_html( $rs_is_en ? 'Portfolio' : 'পোর্টফোলিও' ); ?></span>
		<?php endif; ?>
	</h1>
</div>

<main class="rs-wrap rs-portfolio-page" id="rs-content">

	<!-- Introduction & Core Bio -->
	<header class="rs-portfolio-hero">
		<div class="rs-portfolio-hero__badge">
			<?php echo esc_html( $rs_is_en ? 'Multidisciplinary Creative & Engineer' : 'ভিজ্যুয়াল ক্রিয়েটর ও সফটওয়্যার ডেভেলপার' ); ?>
		</div>
		<h2 class="rs-portfolio-hero__headline">
			<?php if ( $rs_is_en ) : ?>
				Visual Storytelling Meets <span class="rs-portfolio-gradient">Clean Code</span>
			<?php else : ?>
				ভিজ্যুয়াল স্টোরিটেলিং ও <span class="rs-portfolio-gradient">ক্লিন কোডের সমন্বয়</span>
			<?php endif; ?>
		</h2>
		<p class="rs-portfolio-hero__bio">
			<?php if ( $rs_is_en ) : ?>
				I craft engaging visual narratives through video editing and 2D motion animation, while engineering lightweight, responsive websites and productivity tools (browser extensions, WordPress plugins, and automation scripts). Passionate about seamless storytelling, clean code, and zero-bloat digital experiences.
			<?php else : ?>
				আমি ভিডিও এডিটিং ও ২ডি মোশন অ্যানিমেশনের মাধ্যমে ভিজ্যুয়াল গল্প বলি, আবার একই সাথে আধুনিক ওয়েব প্রযুক্তি, ব্রাউজার এক্সটেনশন ও অটোমেশন স্ক্রিপ্ট তৈরি করে ডিজিটাল সমস্যার সমাধান করি। প্রতিটি কাট ও প্রতিটি লাইন কোডে পারফেকশন, স্পিড এবং মিনিমালিজম বজায় রাখাই আমার লক্ষ্য।
			<?php endif; ?>
		</p>

		<!-- Skill Highlights Strip -->
		<div class="rs-portfolio-skills">
			<div class="rs-portfolio-skill-pill">
				<span class="rs-portfolio-skill-pill__icon">💻</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Web Development' : 'ওয়েব ডেভেলপমেন্ট' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">WordPress • PHP • Vanilla JS • CSS3</span>
			</div>
			<div class="rs-portfolio-skill-pill">
				<span class="rs-portfolio-skill-pill__icon">🎬</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Video & Animation' : 'ভিডিও ও মোশন' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">Premiere Pro • After Effects • DaVinci</span>
			</div>
			<div class="rs-portfolio-skill-pill">
				<span class="rs-portfolio-skill-pill__icon">⚡</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Extensions & Tools' : 'এক্সটেনশন ও টুলস' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">Chrome MV3 • Plugins • Python Scripts</span>
			</div>
		</div>
	</header>

	<!-- Filter Controls -->
	<section class="rs-portfolio-filter-section" aria-label="<?php echo esc_attr( $rs_is_en ? 'Project Filter' : 'প্রজেক্ট ফিল্টার' ); ?>">
		<div class="rs-portfolio-filter" role="tablist">
			<button type="button" class="rs-portfolio-filter__btn is-active" data-filter="all" role="tab" aria-selected="true">
				<?php echo esc_html( $rs_is_en ? 'All Work' : 'সব কাজ' ); ?>
				<span class="rs-portfolio-filter__count"><?php echo count( $projects ); ?></span>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="web" role="tab" aria-selected="false">
				💻 <?php echo esc_html( $rs_is_en ? 'Web Development' : 'ওয়েব ডেভেলপমেন্ট' ); ?>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="video" role="tab" aria-selected="false">
				🎬 <?php echo esc_html( $rs_is_en ? 'Video & Animation' : 'ভিডিও ও অ্যানিমেশন' ); ?>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="tools" role="tab" aria-selected="false">
				⚡ <?php echo esc_html( $rs_is_en ? 'Extensions & Scripts' : 'এক্সটেনশন ও স্ক্রিপ্ট' ); ?>
			</button>
		</div>
	</section>

	<!-- Projects Grid -->
	<section class="rs-portfolio-grid" id="rs-portfolio-grid">
		<?php foreach ( $projects as $p ) : ?>
			<?php
			$card_domain = ! empty( $p['direct_url'] ) ? preg_replace( '#^https?://([^/]+).*$#', '$1', $p['direct_url'] ) : 'demo';
			?>
			<article class="rs-portfolio-card" data-category="<?php echo esc_attr( $p['category'] ); ?>" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" id="project-<?php echo esc_attr( $p['id'] ); ?>">
				
				<!-- Card Visual Mockup / Real Screenshot -->
				<div class="rs-portfolio-card__visual rs-portfolio-card__visual--<?php echo esc_attr( $p['category'] ); ?> rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" title="<?php echo esc_attr( $rs_is_en ? 'Open Case Study' : 'কেস স্টাডি দেখুন' ); ?>" role="button" tabindex="0">
					
					<?php if ( ! empty( $p['image'] ) ) : ?>
						<?php if ( 'web' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__browser-bar">
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-card__url"><?php echo esc_html( $card_domain ); ?></span>
							</div>
							<div class="rs-portfolio-card__img-wrap <?php echo ( ! empty( $p['image_fit'] ) && 'contain' === $p['image_fit'] ) ? 'is-contain' : ''; ?>">
								<img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $rs_is_en ? $p['title_en'] : $p['title_bn'] ); ?>" class="rs-portfolio-card__img" loading="lazy">
							</div>
						<?php elseif ( 'video' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__img-wrap" style="background-image: linear-gradient(rgba(0,0,0,0.25), rgba(0,0,0,0.45)), url('<?php echo esc_url( $p['image'] ); ?>'); background-size: cover; background-position: center;">
								<div class="rs-portfolio-card__play-btn">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg>
								</div>
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php else : ?>
							<div class="rs-portfolio-card__img-wrap <?php echo ( ! empty( $p['image_fit'] ) && 'contain' === $p['image_fit'] ) ? 'is-contain' : ''; ?>">
								<img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $rs_is_en ? $p['title_en'] : $p['title_bn'] ); ?>" class="rs-portfolio-card__img" loading="lazy">
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<!-- Fallback CSS Mockup -->
						<?php if ( 'video' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__media-placeholder">
								<div class="rs-portfolio-card__play-btn">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg>
								</div>
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php elseif ( 'web' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__browser-bar">
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-card__url"><?php echo esc_html( $card_domain ); ?></span>
							</div>
							<div class="rs-portfolio-card__web-preview">
								<div class="rs-portfolio-card__wire-block"></div>
								<div class="rs-portfolio-card__wire-line"></div>
								<div class="rs-portfolio-card__wire-line short"></div>
							</div>
						<?php else : ?>
							<div class="rs-portfolio-card__tool-banner">
								<div class="rs-portfolio-card__tool-icon">
									<?php if ( 'extension' === $p['icon'] ) : ?>
										🧩
									<?php elseif ( 'terminal' === $p['icon'] ) : ?>
										⌨️
									<?php else : ?>
										⚙️
									<?php endif; ?>
								</div>
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<div class="rs-portfolio-card__visual-hover">
						<span>📋 <?php echo esc_html( $rs_is_en ? 'Read Case Study' : 'কেস স্টাডি পড়ুন' ); ?></span>
					</div>
				</div>

				<!-- Card Content -->
				<div class="rs-portfolio-card__content">
					<div class="rs-portfolio-card__meta">
						<span class="rs-portfolio-card__badge"><?php echo esc_html( $rs_is_en ? $p['badge_en'] : $p['badge_bn'] ); ?></span>
						<span class="rs-portfolio-card__type"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
					</div>

					<h3 class="rs-portfolio-card__title rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" role="button" tabindex="0">
						<?php echo esc_html( $rs_is_en ? $p['title_en'] : $p['title_bn'] ); ?>
					</h3>

					<p class="rs-portfolio-card__desc">
						<?php echo esc_html( $rs_is_en ? $p['summary_en'] : $p['summary_bn'] ); ?>
					</p>

					<!-- Tags -->
					<div class="rs-portfolio-card__tags">
						<?php foreach ( $p['tags'] as $tag ) : ?>
							<span class="rs-portfolio-tag"><?php echo esc_html( $tag ); ?></span>
						<?php endforeach; ?>
					</div>

					<!-- Dual Action Footer: Case Study Trigger + Direct Outbound Preview -->
					<div class="rs-portfolio-card__footer">
						<button type="button" class="rs-portfolio-btn rs-portfolio-btn--details rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" aria-label="<?php echo esc_attr( ( $rs_is_en ? 'Case study for ' : 'কেস স্টাডি: ' ) . ( $rs_is_en ? $p['title_en'] : $p['title_bn'] ) ); ?>">
							<span>📋 <?php echo esc_html( $rs_is_en ? 'Case Study' : 'কেস স্টাডি' ); ?></span>
						</button>

						<a href="<?php echo esc_url( $p['direct_url'] ); ?>" class="rs-portfolio-btn rs-portfolio-btn--direct" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $rs_is_en ? 'Open original project destination' : 'মূল প্রজেক্টের ঠিকানায় যান' ); ?>">
							<span><?php echo esc_html( $rs_is_en ? $p['action_en'] : $p['action_bn'] ); ?></span>
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
								<line x1="7" y1="17" x2="17" y2="7"></line>
								<polyline points="7 7 17 7 17 17"></polyline>
							</svg>
						</a>
					</div>
				</div>

			</article>
		<?php endforeach; ?>
	</section>

	<!-- Empty state if no projects match filter -->
	<div class="rs-portfolio-empty" id="rs-portfolio-empty" style="display: none;">
		<p><?php echo esc_html( $rs_is_en ? 'No projects found in this category.' : 'এই ক্যাটাগরিতে কোনো প্রজেক্ট পাওয়া যায়নি।' ); ?></p>
	</div>

	<!-- Contact / Collaboration Section -->
	<footer class="rs-portfolio-cta">
		<h3 class="rs-portfolio-cta__title">
			<?php echo esc_html( $rs_is_en ? 'Have an interesting project in mind?' : 'কোনো নতুন কাজের পরিকল্পনা আছে?' ); ?>
		</h3>
		<p class="rs-portfolio-cta__desc">
			<?php if ( $rs_is_en ) : ?>
				Whether you need cinematic video editing, animated graphics, a custom website, or custom automation tools — feel free to connect!
			<?php else : ?>
				সিনেমাটিক ভিডিও এডিটিং, মোশন অ্যানিমেশন, দ্রুতগতির কাস্টম ওয়েবসাইট বা স্পেশাল কোনো এক্সটেনশন/স্ক্রিপ্ট বানাতে চাইলে যোগাযোগ করতে পারেন।
			<?php endif; ?>
		</p>
		<div class="rs-portfolio-cta__actions">
			<?php $rs_email = rs_option( 'rs_email' ); ?>
			<?php if ( $rs_email ) : ?>
				<a href="mailto:<?php echo esc_attr( $rs_email ); ?>" class="rs-portfolio-btn rs-portfolio-btn--primary">
					✉️ <?php echo esc_html( $rs_is_en ? 'Send an Email' : 'ইমেইল পাঠান' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( rs_option( 'rs_linkedin' ) ) : ?>
				<a href="<?php echo esc_url( rs_option( 'rs_linkedin' ) ); ?>" target="_blank" rel="noopener noreferrer" class="rs-portfolio-btn rs-portfolio-btn--secondary">
					💼 LinkedIn
				</a>
			<?php endif; ?>
			<?php if ( rs_option( 'rs_facebook' ) ) : ?>
				<a href="<?php echo esc_url( rs_option( 'rs_facebook' ) ); ?>" target="_blank" rel="noopener noreferrer" class="rs-portfolio-btn rs-portfolio-btn--secondary">
					📘 Facebook
				</a>
			<?php endif; ?>
		</div>
	</footer>

</main>

<!-- =========================================================================
     Case Study Pop-up Modal (Native .rs-overlay Architecture)
     ====================================================================== -->
<div class="rs-overlay" id="rs-case-study-overlay" role="dialog" aria-modal="true" aria-labelledby="rs-modal-project-title" hidden>
	<div class="rs-modal rs-modal--case-study">
		<button class="rs-modal__close" type="button" id="rs-case-study-close" aria-label="<?php echo esc_attr( $rs_is_en ? 'Close' : 'বন্ধ করুন' ); ?>">
			<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
		</button>
		
		<div class="rs-modal__scroll" id="rs-case-study-scroll">
			<article class="rs-case-study-article">
				
				<!-- Modal Header -->
				<header class="rs-case-study-header">
					<div class="rs-case-study-meta">
						<span class="rs-case-study-badge" id="rs-modal-badge"></span>
						<span class="rs-case-study-type" id="rs-modal-type"></span>
					</div>
					<h2 class="rs-case-study-title" id="rs-modal-project-title"></h2>
					<div class="rs-case-study-subline" id="rs-modal-subline">
						<span class="rs-case-study-role" id="rs-modal-role"></span>
						<span class="rs-case-study-sep">•</span>
						<span class="rs-case-study-context" id="rs-modal-context"></span>
					</div>
				</header>

				<!-- Visual Preview Banner inside Modal -->
				<div class="rs-case-study-visual" id="rs-modal-visual">
					<div class="rs-case-study-visual__inner" id="rs-modal-visual-content"></div>
				</div>

				<!-- Case Study Sections -->
				<div class="rs-case-study-body">
					
					<!-- Overview / Summary -->
					<div class="rs-case-study-section">
						<p class="rs-case-study-lead" id="rs-modal-summary"></p>
					</div>

					<!-- The Challenge -->
					<div class="rs-case-study-section rs-case-study-box rs-case-study-box--challenge">
						<h4 class="rs-case-study-heading">
							🎯 <?php echo esc_html( $rs_is_en ? 'The Challenge & Context' : 'চ্যালেঞ্জ ও প্রেক্ষাপট' ); ?>
						</h4>
						<div class="rs-case-study-text" id="rs-modal-challenge"></div>
					</div>

					<!-- The Solution & Process -->
					<div class="rs-case-study-section rs-case-study-box rs-case-study-box--solution">
						<h4 class="rs-case-study-heading">
							💡 <?php echo esc_html( $rs_is_en ? 'The Solution & Creative Process' : 'সমাধান ও কর্মপ্রক্রিয়া' ); ?>
						</h4>
						<div class="rs-case-study-text" id="rs-modal-solution"></div>
					</div>

					<!-- Key Highlights -->
					<div class="rs-case-study-section">
						<h4 class="rs-case-study-heading">
							⭐ <?php echo esc_html( $rs_is_en ? 'Key Highlights & Results' : 'মূল ফলাফল ও বিশেষ অর্জন' ); ?>
						</h4>
						<ul class="rs-case-study-list" id="rs-modal-highlights"></ul>
					</div>

					<!-- Tools & Technologies -->
					<div class="rs-case-study-section">
						<h4 class="rs-case-study-heading">
							🛠 <?php echo esc_html( $rs_is_en ? 'Tools & Technologies Used' : 'ব্যবহৃত সফটওয়্যার ও টুলস' ); ?>
						</h4>
						<div class="rs-portfolio-card__tags" id="rs-modal-tags"></div>
					</div>

				</div>

				<!-- Modal Action Footer -->
				<footer class="rs-case-study-footer">
					<a href="#" class="rs-portfolio-btn rs-portfolio-btn--primary" id="rs-modal-action-btn" target="_blank" rel="noopener noreferrer">
						<span id="rs-modal-action-label"></span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<line x1="7" y1="17" x2="17" y2="7"></line>
							<polyline points="7 7 17 7 17 17"></polyline>
						</svg>
					</a>
					<a href="#" class="rs-portfolio-btn rs-portfolio-btn--secondary" id="rs-modal-github-btn" target="_blank" rel="noopener noreferrer" style="display: none;">
						<span>GitHub</span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<line x1="7" y1="17" x2="17" y2="7"></line>
							<polyline points="7 7 17 7 17 17"></polyline>
						</svg>
					</a>
					<button type="button" class="rs-portfolio-btn rs-portfolio-btn--secondary" id="rs-modal-dismiss-btn">
						<?php echo esc_html( $rs_is_en ? 'Close Case Study' : 'বন্ধ করুন' ); ?>
					</button>
				</footer>

			</article>
		</div>

		<button class="rs-modal__top" type="button" id="rs-case-study-top" aria-label="<?php echo esc_attr( $rs_is_en ? 'Back to top' : 'উপরে যান' ); ?>" title="<?php echo esc_attr( $rs_is_en ? 'Back to top' : 'উপরে যান' ); ?>">
			<?php echo wp_kses( rs_icon( 'up' ), rs_svg_tags() ); ?>
		</button>
	</div>
</div>

<!-- =========================================================================
     Full-Resolution Image Lightbox Overlay
     ====================================================================== -->
<div class="rs-overlay rs-overlay--lightbox" id="rs-image-lightbox" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $rs_is_en ? 'Full Image Preview' : 'ছবির পূর্ণাঙ্গ প্রিভিউ' ); ?>" hidden>
	<button class="rs-lightbox__close" type="button" id="rs-lightbox-close" aria-label="<?php echo esc_attr( $rs_is_en ? 'Close preview' : 'প্রিভিউ বন্ধ করুন' ); ?>" title="<?php echo esc_attr( $rs_is_en ? 'Close (Esc)' : 'বন্ধ করুন (Esc)' ); ?>">
		<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
	</button>
	<div class="rs-lightbox__wrap" id="rs-lightbox-wrap">
		<img src="" alt="" class="rs-lightbox__img" id="rs-lightbox-img">
		<div class="rs-lightbox__footer">
			<span class="rs-lightbox__caption" id="rs-lightbox-caption"></span>
			<span class="rs-lightbox__tip"><?php echo esc_html( $rs_is_en ? 'Click anywhere or press Esc to close' : 'যেকোনো স্থানে ক্লিক করে বা Esc চেপে বন্ধ করুন' ); ?></span>
		</div>
	</div>
</div>

<!-- Structured Projects Data for Client-Side Modal -->
<script id="rs-portfolio-data" type="application/json">
<?php
$client_data = array();
foreach ( $projects as $p ) {
	$client_data[ $p['id'] ] = array(
		'id'          => $p['id'],
		'category'    => $p['category'],
		'type'        => $rs_is_en ? $p['type_en'] : $p['type_bn'],
		'badge'       => $rs_is_en ? $p['badge_en'] : $p['badge_bn'],
		'title'       => $rs_is_en ? $p['title_en'] : $p['title_bn'],
		'summary'     => $rs_is_en ? $p['summary_en'] : $p['summary_bn'],
		'role'        => ( $rs_is_en ? 'Role: ' : 'ভূমিকা: ' ) . ( $rs_is_en ? $p['role_en'] : $p['role_bn'] ),
		'context'     => $rs_is_en ? $p['context_en'] : $p['context_bn'],
		'challenge'   => $rs_is_en ? $p['challenge_en'] : $p['challenge_bn'],
		'solution'    => $rs_is_en ? $p['solution_en'] : $p['solution_bn'],
		'highlights'  => $rs_is_en ? $p['highlights_en'] : $p['highlights_bn'],
		'tags'        => $p['tags'],
		'accent'      => $p['accent'],
		'icon'        => $p['icon'],
		'image'       => ! empty( $p['image'] ) ? $p['image'] : '',
		'image_fit'   => ! empty( $p['image_fit'] ) ? $p['image_fit'] : 'cover',
		'action_type' => $p['action_type'],
		'action_label'=> $rs_is_en ? $p['action_en'] : $p['action_bn'],
		'direct_url'  => $p['direct_url'],
		'github_url'  => ! empty( $p['github_url'] ) ? $p['github_url'] : '',
	);
}
echo wp_json_encode( $client_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
?>
</script>

<script>
(function() {
	'use strict';

	/* 1. Category Filter Logic */
	var buttons = document.querySelectorAll('.rs-portfolio-filter__btn');
	var cards   = document.querySelectorAll('.rs-portfolio-card');
	var empty   = document.getElementById('rs-portfolio-empty');

	if (buttons.length && cards.length) {
		buttons.forEach(function(btn) {
			btn.addEventListener('click', function() {
				var filter = this.getAttribute('data-filter');

				buttons.forEach(function(b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				this.classList.add('is-active');
				this.setAttribute('aria-selected', 'true');

				var visibleCount = 0;
				cards.forEach(function(card) {
					var cat = card.getAttribute('data-category');
					if (filter === 'all' || filter === cat) {
						card.style.display = '';
						visibleCount++;
					} else {
						card.style.display = 'none';
					}
				});

				if (empty) {
					empty.style.display = visibleCount === 0 ? 'block' : 'none';
				}
			});
		});
	}

	/* 2. Case Study Pop-up Modal Logic */
	var rawData = document.getElementById('rs-portfolio-data');
	if (!rawData) return;

	var projectsMap = {};
	try {
		projectsMap = JSON.parse(rawData.textContent || '{}');
	} catch (e) {
		console.error('Failed to parse portfolio data', e);
	}

	var overlay      = document.getElementById('rs-case-study-overlay');
	var scrollArea   = document.getElementById('rs-case-study-scroll');
	var closeBtn     = document.getElementById('rs-case-study-close');
	var dismissBtn   = document.getElementById('rs-modal-dismiss-btn');
	var titleEl      = document.getElementById('rs-modal-project-title');
	var badgeEl      = document.getElementById('rs-modal-badge');
	var typeEl       = document.getElementById('rs-modal-type');
	var roleEl       = document.getElementById('rs-modal-role');
	var contextEl    = document.getElementById('rs-modal-context');
	var summaryEl    = document.getElementById('rs-modal-summary');
	var challengeEl  = document.getElementById('rs-modal-challenge');
	var solutionEl   = document.getElementById('rs-modal-solution');
	var highlightsEl = document.getElementById('rs-modal-highlights');
	var tagsEl       = document.getElementById('rs-modal-tags');
	var visualContainer = document.getElementById('rs-modal-visual');
	var visualEl        = document.getElementById('rs-modal-visual-content');
	var actionBtn       = document.getElementById('rs-modal-action-btn');
	var actionLabel     = document.getElementById('rs-modal-action-label');
	var githubBtn       = document.getElementById('rs-modal-github-btn');
	var topBtn          = document.getElementById('rs-case-study-top');
	var lightboxOverlay = document.getElementById('rs-image-lightbox');
	var lightboxClose   = document.getElementById('rs-lightbox-close');
	var lightboxImg     = document.getElementById('rs-lightbox-img');
	var lightboxCaption = document.getElementById('rs-lightbox-caption');

	function renderRichText(container, text) {
		container.innerHTML = '';
		if (!text) return;
		var paras = text.split(/\n\n+/);
		paras.forEach(function(para) {
			var p = document.createElement('p');
			p.innerHTML = para.replace(/\n/g, '<br>');
			container.appendChild(p);
		});
	}

	function openLightbox(src, title) {
		if (!lightboxOverlay || !lightboxImg) return;
		lightboxImg.src = src;
		lightboxImg.alt = title || '';
		if (lightboxCaption) lightboxCaption.textContent = title || '';
		lightboxOverlay.hidden = false;
	}

	function closeLightbox() {
		if (!lightboxOverlay || lightboxOverlay.hidden) return;
		lightboxOverlay.hidden = true;
		if (lightboxImg) lightboxImg.src = '';
	}

	function openCaseStudy(projectId) {
		var p = projectsMap[projectId];
		if (!p || !overlay) return;

		var isEn = document.documentElement.lang.indexOf('en') === 0;

		titleEl.textContent    = p.title;
		badgeEl.textContent    = p.badge;
		typeEl.textContent     = p.type;
		roleEl.textContent     = p.role;
		contextEl.textContent  = p.context;
		summaryEl.textContent  = p.summary;
		renderRichText(challengeEl, p.challenge);
		renderRichText(solutionEl, p.solution);

		// Render Highlights List
		highlightsEl.innerHTML = '';
		if (Array.isArray(p.highlights)) {
			p.highlights.forEach(function(h) {
				var li = document.createElement('li');
				li.textContent = h;
				highlightsEl.appendChild(li);
			});
		}

		// Render Tags
		tagsEl.innerHTML = '';
		if (Array.isArray(p.tags)) {
			p.tags.forEach(function(tag) {
				var sp = document.createElement('span');
				sp.className = 'rs-portfolio-tag';
				sp.textContent = tag;
				tagsEl.appendChild(sp);
			});
		}

		// Action Buttons
		actionBtn.href = p.direct_url;
		actionLabel.textContent = p.action_label;

		// Only show secondary GitHub button if GitHub URL is distinct from the primary action link
		if (p.github_url && githubBtn && p.github_url !== p.direct_url) {
			githubBtn.href = p.github_url;
			githubBtn.style.display = 'inline-flex';
			githubBtn.querySelector('span').textContent = isEn ? 'View on GitHub' : 'গিটহাবে কোড দেখুন';
		} else if (githubBtn) {
			githubBtn.style.display = 'none';
		}

		// Dynamic Visual Banner (Real Screenshot or CSS Mockup)
		var displayDomain = p.direct_url.replace(/^https?:\/\//, '').replace(/\/.*$/, '');
		var isZoomable = Boolean(p.image && p.image_fit !== 'contain');

		if (visualContainer) {
			visualContainer.classList.toggle('is-full-view', isZoomable);
		}

		if (p.image) {
			var zoomHint = isZoomable
				? '<div class="rs-case-study-zoom-badge">' +
				  '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>' +
				  '<span>' + (isEn ? 'Click to zoom' : 'বড় করে দেখুন') + '</span>' +
				  '</div>'
				: '';
			var zoomClass = isZoomable ? ' is-zoomable' : '';
			var fitClass = p.image_fit === 'contain' ? ' is-contain' : '';
			var zoomAttrs = isZoomable
				? ' role="button" tabindex="0" title="' + (isEn ? 'Click to view full image' : 'সম্পূর্ণ ছবি দেখতে ক্লিক করুন') + '"'
				: '';

			if (p.category === 'web') {
				visualEl.innerHTML = '<div class="rs-case-study-web-mockup"><div class="rs-portfolio-card__browser-bar"><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-card__url">' + displayDomain + '</span></div><div class="rs-case-study-img-wrap' + fitClass + zoomClass + '"' + zoomAttrs + '><img src="' + p.image + '" alt="' + p.title + '" class="rs-case-study-img">' + zoomHint + '</div></div>';
			} else if (p.category === 'video') {
				var videoZoomBtn = isZoomable
					? '<button type="button" class="rs-case-study-zoom-badge is-clickable" aria-label="' + (isEn ? 'Click to view full image' : 'সম্পূর্ণ ছবি দেখতে ক্লিক করুন') + '" title="' + (isEn ? 'Click to zoom artwork' : 'আর্টওয়ার্ক বড় করে দেখুন') + '">' +
					  '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>' +
					  '<span>' + (isEn ? 'Click to zoom' : 'বড় করে দেখুন') + '</span>' +
					  '</button>'
					: '';
				visualEl.innerHTML = '<div class="rs-case-study-video-wrap">' +
					'<a href="' + p.direct_url + '" target="_blank" rel="noopener noreferrer" class="rs-case-study-video-mockup" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url(' + p.image + '); background-size: cover; background-position: center;" title="' + (isEn ? 'Watch on YouTube (Opens in new tab)' : 'ইউটিউবে দেখুন (নতুন ট্যাবে খুলবে)') + '">' +
					'<div class="rs-portfolio-card__play-btn"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg></div>' +
					'<span class="rs-case-study-video-label">' + (isEn ? 'HD Video Preview • Click to Watch' : 'এইচডি ভিডিও প্রিভিউ • দেখতে ক্লিক করুন') + '</span>' +
					'</a>' +
					videoZoomBtn +
					'</div>';
			} else {
				visualEl.innerHTML = '<div class="rs-case-study-img-wrap' + fitClass + zoomClass + '"' + zoomAttrs + '><img src="' + p.image + '" alt="' + p.title + '" class="rs-case-study-img">' + zoomHint + '</div>';
			}

			if (isZoomable) {
				var zoomTrigger = visualEl.querySelector('.is-zoomable, .rs-case-study-zoom-badge.is-clickable');
				if (zoomTrigger) {
					zoomTrigger.addEventListener('click', function(e) {
						e.stopPropagation();
						e.preventDefault();
						openLightbox(p.image, p.title);
					});
					zoomTrigger.addEventListener('keydown', function(e) {
						if (e.key === 'Enter' || e.key === ' ') {
							e.stopPropagation();
							e.preventDefault();
							openLightbox(p.image, p.title);
						}
					});
				}
			}
		} else {
			if (p.category === 'video') {
				visualEl.innerHTML = '<div class="rs-case-study-video-wrap">' +
					'<a href="' + p.direct_url + '" target="_blank" rel="noopener noreferrer" class="rs-case-study-video-mockup" title="' + (isEn ? 'Watch on YouTube (Opens in new tab)' : 'ইউটিউবে দেখুন (নতুন ট্যাবে খুলবে)') + '">' +
					'<div class="rs-portfolio-card__play-btn"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg></div>' +
					'<span class="rs-case-study-video-label">' + (isEn ? 'HD Video Preview • Click to Watch' : 'এইচডি ভিডিও প্রিভিউ • দেখতে ক্লিক করুন') + '</span>' +
					'</a>' +
					'</div>';
			} else if (p.category === 'web') {
				visualEl.innerHTML = '<div class="rs-case-study-web-mockup"><div class="rs-portfolio-card__browser-bar"><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-card__url">' + displayDomain + '</span></div><div class="rs-case-study-web-body"><span>⚡ ' + (isEn ? 'Fast Responsive Zero-Plugin Web Platform' : 'দ্রুতগতির জিরো-প্লাগিন রেসপনসিভ ওয়েবসাইট') + '</span></div></div>';
			} else {
				visualEl.innerHTML = '<div class="rs-case-study-tool-mockup"><div class="rs-case-study-tool-badge">' + (p.icon === 'extension' ? '🧩' : (p.icon === 'terminal' ? '⌨️' : '⚙️')) + '</div><span>' + p.type + '</span></div>';
			}
		}

		// Show Modal
		overlay.hidden = false;
		document.body.style.overflow = 'hidden';
		if (scrollArea) scrollArea.scrollTop = 0;
		if (topBtn) topBtn.classList.remove('is-visible');
	}

	function closeCaseStudy() {
		closeLightbox();
		if (!overlay || overlay.hidden) return;
		overlay.hidden = true;
		document.body.style.overflow = '';
		if (topBtn) topBtn.classList.remove('is-visible');
	}

	// Back to top inside modal scroller
	if (scrollArea && topBtn) {
		scrollArea.addEventListener('scroll', function() {
			topBtn.classList.toggle('is-visible', scrollArea.scrollTop > 220);
		}, { passive: true });

		topBtn.addEventListener('click', function() {
			scrollArea.scrollTo({
				top: 0,
				behavior: 'smooth'
			});
		});
	}

	// Attach click handlers to triggers
	document.querySelectorAll('.rs-open-case-study').forEach(function(trigger) {
		trigger.addEventListener('click', function(e) {
			e.preventDefault();
			var pid = this.getAttribute('data-project-id');
			if (pid) {
				openCaseStudy(pid);
			}
		});
		trigger.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				var pid = this.getAttribute('data-project-id');
				if (pid) openCaseStudy(pid);
			}
		});
	});

	// Close buttons
	if (closeBtn) closeBtn.addEventListener('click', closeCaseStudy);
	if (dismissBtn) dismissBtn.addEventListener('click', closeCaseStudy);

	// Lightbox close button
	if (lightboxClose) {
		lightboxClose.addEventListener('click', closeLightbox);
	}

	// Lightbox backdrop click closes
	if (lightboxOverlay) {
		lightboxOverlay.addEventListener('click', function(e) {
			if (e.target === lightboxOverlay || e.target.id === 'rs-lightbox-wrap' || e.target === lightboxImg) {
				closeLightbox();
			}
		});
	}

	// Backdrop click closes
	if (overlay) {
		overlay.addEventListener('mousedown', function(e) {
			if (e.target === overlay) {
				closeCaseStudy();
			}
		});
	}

	// ESC key closes (closes Lightbox first if open, else Case Study modal)
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' || e.key === 'Esc') {
			if (lightboxOverlay && !lightboxOverlay.hidden) {
				e.preventDefault();
				closeLightbox();
			} else if (overlay && !overlay.hidden) {
				e.preventDefault();
				closeCaseStudy();
			}
		}
	});

})();
</script>

<?php
get_footer();
