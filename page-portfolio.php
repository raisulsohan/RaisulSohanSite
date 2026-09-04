<?php
/**
 * Template Name: Portfolio
 *
 * Multidisciplinary portfolio showcase: Video Editing, Motion Animation,
 * Web Development, and Browser Extensions / Plugins / Scripts.
 *
 * Fully localized for Bengali and English with identical responsive layout.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$rs_is_en = rs_is_en();
$rs_hero  = rs_hero_image();

/* ---- Portfolio Projects Data ---- */
$projects = array(
	// Video & Animation
	array(
		'id'          => 'docu-story',
		'category'    => 'video',
		'type_bn'     => 'ভিডিও এডিটিং',
		'type_en'     => 'Video Editing',
		'title_bn'    => 'ডকুমেন্টারি স্টোরিটেলিং ও সিনেমাটিক এডিটিং',
		'title_en'    => 'Documentary Narrative & Cinematic Edit',
		'desc_bn'     => 'ভিজ্যুয়াল পেসিং, গভীর সাউন্ড ডিজাইন ও সিনেমাটিক কালার গ্রেডিংয়ের সমন্বয়ে নির্মিত দীর্ঘ ডকু-ফিচার এডিটিং। প্রতিটি কাটে আবেগের ধারাবাহিকতা বজায় রাখা হয়েছে।',
		'desc_en'     => 'A long-form documentary edit crafted with dynamic visual pacing, immersive atmospheric sound design, and cinematic color grading with emotional continuity.',
		'tags'        => array( 'Premiere Pro', 'DaVinci Resolve', 'Sound Design' ),
		'accent'      => '#e17055',
		'icon'        => 'video',
		'badge_bn'    => 'ফিচার্ড প্রজেক্ট',
		'badge_en'    => 'Featured Project',
		'action_bn'   => 'ভিডিও প্রিভিউ',
		'action_en'   => 'Watch Preview',
		'link'        => '#video-preview',
	),
	array(
		'id'          => 'motion-explainer',
		'category'    => 'video',
		'type_bn'     => 'মোশন অ্যানিমেশন',
		'type_en'     => 'Motion Animation',
		'title_bn'    => '২ডি ক্যারেক্টার ও এক্সপ্লেইনার মোশন গ্রাফিক্স',
		'title_en'    => '2D Character & Explainer Motion Graphics',
		'desc_bn'     => 'জটিল কনসেপ্টকে সহজ ও সাবলীলভাবে বোঝাতে কাস্টম ভেক্টর ইলাস্ট্রেশন ও রিদমিক কিফ্রেম অ্যানিমেশন। সাউন্ড ইফেক্টের সাথে ভিজ্যুয়াল ট্রানজিশনের নিখুঁত টাইমিং।',
		'desc_en'     => 'Custom vector illustrations brought to life through fluid keyframe animation, translating complex concepts into engaging, digestible visual stories.',
		'tags'        => array( 'After Effects', 'Illustrator', '2D Motion' ),
		'accent'      => '#6c5ce7',
		'icon'        => 'animation',
		'badge_bn'    => 'অ্যানিমেশন',
		'badge_en'    => 'Animation',
		'action_bn'   => 'ভিডিও প্রিভিউ',
		'action_en'   => 'Watch Preview',
		'link'        => '#video-preview',
	),
	array(
		'id'          => 'brand-promo',
		'category'    => 'video',
		'type_bn'     => 'কমার্শিয়াল রিল',
		'type_en'     => 'Commercial Reel',
		'title_bn'    => 'হাই-এনার্জি কমার্শিয়াল ও সোশ্যাল মিডিয়া প্রোমো',
		'title_en'    => 'High-Energy Commercial & Promo Reel',
		'desc_bn'     => 'সোশ্যাল মিডিয়া ও ব্রান্ড ক্যাম্পেইনের উপযোগী স্ন্যাপি ট্রানজিশন, কাইনেটিক টাইপোগ্রাফি ও মিউজিক বিট-সিঙ্ক এডিটিং যা প্রথম ৩ সেকেন্ডেই দর্শককে ধরে রাখে।',
		'desc_en'     => 'Fast-paced rhythmic editing, kinetic typography, and snappy visual transitions tailored for social campaigns to maximize audience retention.',
		'tags'        => array( 'After Effects', 'Kinetic Typography', 'Color Grading' ),
		'accent'      => '#d63031',
		'icon'        => 'video',
		'badge_bn'    => 'কমার্শিয়াল',
		'badge_en'    => 'Commercial',
		'action_bn'   => 'রিল দেখুন',
		'action_en'   => 'View Reel',
		'link'        => '#video-preview',
	),

	// Web Development
	array(
		'id'          => 'theme-dev',
		'category'    => 'web',
		'type_bn'     => 'ওয়েব ডেভেলপমেন্ট',
		'type_en'     => 'Web Development',
		'title_bn'    => 'জিরো-প্লাগিন বেস্পোক ওয়ার্ডপ্রেস থিম আর্কিটেকচার',
		'title_en'    => 'Zero-Plugin Bespoke WordPress Theme Architecture',
		'desc_bn'     => 'কোনো থার্ড-পার্টি প্লাগিন ছাড়াই নির্মিত সুপারফাস্ট ব্যক্তিগত ওয়েবসাইট থিম। এতে রয়েছে এজেক্স নেভিগেশন, ডার্ক মোড ও ডায়নামিক কালার প্যালেট জেনারেশন।',
		'desc_en'     => 'An ultra-fast, zero-plugin custom WordPress theme featuring seamless AJAX pagination, distraction-free reading modal, and WCAG-compliant dynamic theming.',
		'tags'        => array( 'WordPress', 'PHP 8', 'Vanilla JS', 'Semantic CSS' ),
		'accent'      => '#0984e3',
		'icon'        => 'code',
		'badge_bn'    => 'ওপেন সোর্স',
		'badge_en'    => 'Open Source',
		'action_bn'   => 'লাইভ ডেমো',
		'action_en'   => 'Live Demo',
		'link'        => home_url( '/' ),
	),
	array(
		'id'          => 'digital-shelf',
		'category'    => 'web',
		'type_bn'     => 'ওয়েব অ্যাপ্লিকেশন',
		'type_en'     => 'Web Application',
		'title_bn'    => 'ইন্টারেক্টিভ ডিজিটাল বুকশেল্ফ ও রিডিং পোর্টাল',
		'title_en'    => 'Interactive Digital Bookshelf & Reading Hub',
		'desc_bn'     => 'কোনো ভারী ইমেজ ছাড়াই পিওর সিএসএস স্পাইন জেনারেশন, লাইভ ইনস্ট্যান্ট ফিল্টারিং ও সার্চ সহ রেসপনসিভ বুকশেল্ফ ইন্টারফেস।',
		'desc_en'     => 'Procedural CSS book spine rendering without heavy images, instant author & genre filtering, and client-side search with near-zero latency.',
		'tags'        => array( 'JavaScript', 'CSS Grid', 'REST API' ),
		'accent'      => '#00b894',
		'icon'        => 'layout',
		'badge_bn'    => 'ইন্টারেক্টিভ',
		'badge_en'    => 'Interactive',
		'action_bn'   => 'বুকশেল্ফ দেখুন',
		'action_en'   => 'Visit Shelf',
		'link'        => home_url( '/book-list/' ),
	),

	// Extensions, Plugins & Scripts
	array(
		'id'          => 'chrome-ext',
		'category'    => 'tools',
		'type_bn'     => 'ক্রোম এক্সটেনশন',
		'type_en'     => 'Chrome Extension',
		'title_bn'    => 'স্মার্ট ওয়ার্কফ্লো ও ট্যাব ম্যানেজার (Manifest V3)',
		'title_en'    => 'Smart Workflow & Tab Manager (Manifest V3)',
		'desc_bn'     => 'ব্রাউজিং প্রোডাক্টিভিটি বাড়াতে তৈরি আধুনিক Manifest V3 এক্সটেনশন। কিবোর্ড শর্টকাট, কুইক গ্রুপ ও সেশন মেমোরি সংরক্ষণ ফিচার যুক্ত।',
		'desc_en'     => 'A high-performance Chrome extension built on Manifest V3 for organizing active workflows, quick tab grouping, and instant session recall.',
		'tags'        => array( 'Chrome API', 'Manifest V3', 'JavaScript', 'Storage API' ),
		'accent'      => '#fdcb6e',
		'icon'        => 'extension',
		'badge_bn'    => 'এক্সটেনশন',
		'badge_en'    => 'Extension',
		'action_bn'   => 'সোর্স কোড',
		'action_en'   => 'Source Code',
		'link'        => 'https://github.com/raisulsohan',
	),
	array(
		'id'          => 'wp-optimizer',
		'category'    => 'tools',
		'type_bn'     => 'ওয়ার্ডপ্রেস টুলকিট',
		'type_en'     => 'WordPress Toolkit',
		'title_bn'    => 'ব্লট-রিমুভার ও অ্যাসেট পারফরম্যান্স মডিউল',
		'title_en'    => 'Zero-Bloat Asset & Script Performance Module',
		'desc_bn'     => 'অপ্রয়োজনীয় ডিফল্ট স্ক্রিপ্ট ছাঁটাই, ফন্ট প্রি-লোডিং এবং রেন্ডার-ব্লকিং অ্যাসেট অপ্টিমাইজ করার কাস্টম মডিউল যা পেজস্পিড ১০০/১০০ করে।',
		'desc_en'     => 'Custom utility module engineered to prune unused core assets, configure font preloading, and eliminate render-blocking resources for 100/100 Core Web Vitals.',
		'tags'        => array( 'PHP', 'WordPress Hooks', 'Web Performance' ),
		'accent'      => '#6c5ce7',
		'icon'        => 'plugin',
		'badge_bn'    => 'পারফরম্যান্স',
		'badge_en'    => 'Performance',
		'action_bn'   => 'GitHub রিপো',
		'action_en'   => 'GitHub Repo',
		'link'        => 'https://github.com/raisulsohan',
	),
	array(
		'id'          => 'media-script',
		'category'    => 'tools',
		'type_bn'     => 'অটোমেশন স্ক্রিপ্ট',
		'type_en'     => 'Automation Script',
		'title_bn'    => 'মিডিয়া ট্রান্সকোডিং ও ব্যাকআপ অটোমেশন টুল',
		'title_en'    => 'Batch Media Transcoding & Asset Sync Script',
		'desc_bn'     => 'FFmpeg এবং পাইথনের সাহায্যে স্বয়ংক্রিয়ভাবে হাই-রেজোলিউশন ভিডিও কম্প্রেস করা, থাম্বনেইল তৈরি এবং ক্লাউড স্টোরেজে সিঙ্ক করার অটোমেশন স্ক্রিপ্ট।',
		'desc_en'     => 'Python & Shell automation script leveraging FFmpeg for batch lossless video compression, multi-ratio thumbnail generation, and cloud sync.',
		'tags'        => array( 'Python', 'FFmpeg', 'Bash Script', 'CLI Tool' ),
		'accent'      => '#00cec9',
		'icon'        => 'terminal',
		'badge_bn'    => 'অটোমেশন',
		'badge_en'    => 'CLI / Script',
		'action_bn'   => 'স্ক্রিপ্ট দেখুন',
		'action_en'   => 'View Script',
		'link'        => 'https://github.com/raisulsohan',
	),
);
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
				<span class="rs-portfolio-skill-pill__icon">🎬</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Video & Animation' : 'ভিডিও ও মোশন' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">Premiere Pro • After Effects • DaVinci</span>
			</div>
			<div class="rs-portfolio-skill-pill">
				<span class="rs-portfolio-skill-pill__icon">💻</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Web Development' : 'ওয়েব ডেভেলপমেন্ট' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">WordPress • PHP • Vanilla JS • CSS3</span>
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
			<button type="button" class="rs-portfolio-filter__btn" data-filter="video" role="tab" aria-selected="false">
				🎬 <?php echo esc_html( $rs_is_en ? 'Video & Animation' : 'ভিডিও ও অ্যানিমেশন' ); ?>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="web" role="tab" aria-selected="false">
				💻 <?php echo esc_html( $rs_is_en ? 'Web Development' : 'ওয়েব ডেভেলপমেন্ট' ); ?>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="tools" role="tab" aria-selected="false">
				⚡ <?php echo esc_html( $rs_is_en ? 'Extensions & Scripts' : 'এক্সটেনশন ও স্ক্রিপ্ট' ); ?>
			</button>
		</div>
	</section>

	<!-- Projects Grid -->
	<section class="rs-portfolio-grid" id="rs-portfolio-grid">
		<?php foreach ( $projects as $p ) : ?>
			<article class="rs-portfolio-card" data-category="<?php echo esc_attr( $p['category'] ); ?>" id="project-<?php echo esc_attr( $p['id'] ); ?>">
				
				<!-- Card Visual Mockup -->
				<div class="rs-portfolio-card__visual rs-portfolio-card__visual--<?php echo esc_attr( $p['category'] ); ?>">
					<?php if ( 'video' === $p['category'] ) : ?>
						<div class="rs-portfolio-card__media-placeholder">
							<div class="rs-portfolio-card__play-btn" title="<?php echo esc_attr( $rs_is_en ? 'Play Video' : 'ভিডিও চালান' ); ?>">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg>
							</div>
							<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
						</div>
					<?php elseif ( 'web' === $p['category'] ) : ?>
						<div class="rs-portfolio-card__browser-bar">
							<span class="rs-portfolio-dot"></span>
							<span class="rs-portfolio-dot"></span>
							<span class="rs-portfolio-dot"></span>
							<span class="rs-portfolio-card__url">raisulsohan.com</span>
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
				</div>

				<!-- Card Content -->
				<div class="rs-portfolio-card__content">
					<div class="rs-portfolio-card__meta">
						<span class="rs-portfolio-card__badge"><?php echo esc_html( $rs_is_en ? $p['badge_en'] : $p['badge_bn'] ); ?></span>
						<span class="rs-portfolio-card__type"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
					</div>

					<h3 class="rs-portfolio-card__title">
						<?php echo esc_html( $rs_is_en ? $p['title_en'] : $p['title_bn'] ); ?>
					</h3>

					<p class="rs-portfolio-card__desc">
						<?php echo esc_html( $rs_is_en ? $p['desc_en'] : $p['desc_bn'] ); ?>
					</p>

					<!-- Tags -->
					<div class="rs-portfolio-card__tags">
						<?php foreach ( $p['tags'] as $tag ) : ?>
							<span class="rs-portfolio-tag"><?php echo esc_html( $tag ); ?></span>
						<?php endforeach; ?>
					</div>

					<!-- Action Footer -->
					<div class="rs-portfolio-card__footer">
						<a href="<?php echo esc_url( $p['link'] ); ?>" class="rs-portfolio-btn" <?php echo strpos( $p['link'], 'http' ) === 0 ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<span><?php echo esc_html( $rs_is_en ? $p['action_en'] : $p['action_bn'] ); ?></span>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

<script>
(function() {
	'use strict';
	var buttons = document.querySelectorAll('.rs-portfolio-filter__btn');
	var cards   = document.querySelectorAll('.rs-portfolio-card');
	var empty   = document.getElementById('rs-portfolio-empty');

	if (!buttons.length || !cards.length) return;

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
})();
</script>

<?php
get_footer();
