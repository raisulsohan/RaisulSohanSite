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

/* The portfolio is a dark stage in both site themes. This class lets the
   header, the footer and the floating controls follow it. */
add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'rs-stage';
		return $classes;
	}
);

get_header();

$rs_is_en = rs_is_en();

/* ---- Portfolio Projects & Case Studies Data (Dynamic Dashboard CPT & Fallback) ---- */
$projects = function_exists( 'rs_get_portfolio_projects' ) ? rs_get_portfolio_projects() : array();
?>

<?php
/* Numbers for the hero, worked out from the projects themselves. */
$rs_count_cat  = array( 'web' => 0, 'video' => 0, 'tools' => 0 );
$rs_count_open = 0;
$rs_downloads  = 0;
$rs_gh         = array();
foreach ( $projects as $p ) {
	if ( isset( $rs_count_cat[ $p['category'] ] ) ) {
		$rs_count_cat[ $p['category'] ]++;
	}
	if ( ! empty( $p['github_url'] ) ) {
		$rs_count_open++;
		$rs_gh[ $p['id'] ] = function_exists( 'rs_github_stats' ) ? rs_github_stats( $p['github_url'] ) : null;
		if ( $rs_gh[ $p['id'] ] ) {
			$rs_downloads += (int) $rs_gh[ $p['id'] ]['downloads'];
		}
	}
}
$rs_num = function ( $n ) use ( $rs_is_en ) {
	return $rs_is_en ? (string) $n : rs_bn_digits( $n );
};
$rs_cats = array(
	'web'   => array( $rs_is_en ? 'Web' : 'ওয়েব', '#31a8ff' ),
	'video' => array( $rs_is_en ? 'Video & motion' : 'ভিডিও ও মোশন', '#ea77ff' ),
	'tools' => array( $rs_is_en ? 'Tools & extensions' : 'টুলস ও এক্সটেনশন', '#b9a8ff' ),
);
$rs_arrow_right = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
$rs_arrow_out   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>';
?>

<main class="rs-pf" id="rs-content">
	<div class="rs-pf__glow rs-pf__glow--a" aria-hidden="true"></div>
	<div class="rs-pf__glow rs-pf__glow--b" aria-hidden="true"></div>

	<header class="rs-pf__hero rs-pf__wrap">
		<div class="rs-pf__intro">
			<p class="rs-pf__eyebrow rs-pf-rise"><?php echo esc_html( $rs_is_en ? 'Portfolio' : 'পোর্টফোলিও' ); ?></p>

			<ul class="rs-pf__chips rs-pf-rise">
				<li class="rs-pf-chip" style="--c: #b9a8ff"><?php echo esc_html( $rs_is_en ? 'Creative developer' : 'ক্রিয়েটিভ ডেভেলপার' ); ?></li>
				<li class="rs-pf-chip" style="--c: #ea77ff"><?php echo esc_html( $rs_is_en ? 'Motion tools maker' : 'মোশন টুলস নির্মাতা' ); ?></li>
				<li class="rs-pf-chip" style="--c: #31a8ff"><?php echo esc_html( $rs_is_en ? 'Automation geek' : 'অটোমেশন' ); ?></li>
			</ul>

			<h1 class="rs-pf__title rs-pf-rise rs-pf-rise--2">
				<?php if ( $rs_is_en ) : ?>
					Stories told in <em>frames</em>, problems solved in <em>code</em>
				<?php else : ?>
					গল্প বলি <em>ফ্রেমে</em>, সমস্যা মেটাই <em>কোডে</em>
				<?php endif; ?>
			</h1>

			<p class="rs-pf__bio rs-pf-rise rs-pf-rise--2">
				<?php if ( $rs_is_en ) : ?>
					I tell stories through video editing and motion animation, and I build free, open-source tools for motion designers, video editors and creators. Every cut and every line of code chases the same thing: work that is fast, clean and friction-free.
				<?php else : ?>
					ভিডিও এডিটিং আর মোশন অ্যানিমেশনে গল্প বলি, আর মোশন ডিজাইনার, ভিডিও এডিটর ও ক্রিয়েটরদের জন্য ফ্রি, ওপেন সোর্স টুল বানাই। প্রতিটি কাটে আর প্রতিটি লাইন কোডে একটাই লক্ষ্য: কাজ হোক দ্রুত, পরিষ্কার আর ঝামেলাহীন।
				<?php endif; ?>
			</p>

			<div class="rs-pf__actions rs-pf-rise rs-pf-rise--3">
				<a class="rs-pf-btn rs-pf-btn--primary" href="#rs-pf-work-section">
					<?php echo esc_html( $rs_is_en ? 'See the work' : 'কাজগুলো দেখুন' ); ?>
					<?php echo $rs_arrow_right; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</a>
				<a class="rs-pf-btn rs-pf-btn--ghost" href="#rs-pf-contact"><?php echo esc_html( $rs_is_en ? 'Get in touch' : 'যোগাযোগ' ); ?></a>
			</div>

			<dl class="rs-pf__stats rs-pf-rise rs-pf-rise--3">
				<div>
					<dt><?php echo esc_html( $rs_is_en ? 'Projects' : 'প্রজেক্ট' ); ?></dt>
					<dd><?php echo esc_html( $rs_num( count( $projects ) ) ); ?></dd>
				</div>
				<?php if ( $rs_count_open ) : ?>
					<div>
						<dt><?php echo esc_html( $rs_is_en ? 'Open source' : 'ওপেন সোর্স' ); ?></dt>
						<dd><?php echo esc_html( $rs_num( $rs_count_open ) ); ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( $rs_downloads ) : ?>
					<div>
						<dt><?php echo esc_html( $rs_is_en ? 'Downloads' : 'ডাউনলোড' ); ?></dt>
						<dd><?php echo esc_html( $rs_num( number_format_i18n( $rs_downloads ) ) ); ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( $rs_count_cat['video'] ) : ?>
					<div>
						<dt><?php echo esc_html( $rs_is_en ? 'Video & motion' : 'ভিডিও ও মোশন' ); ?></dt>
						<dd><?php echo esc_html( $rs_num( $rs_count_cat['video'] ) ); ?></dd>
					</div>
				<?php endif; ?>
			</dl>
		</div>

		<?php
		$rs_eye  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
		$rs_lock = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>';
		$rs_hand = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 13V5.5a1.5 1.5 0 0 1 3 0V12"/><path d="M11 11.5v-2a1.5 1.5 0 0 1 3 0V12"/><path d="M14 10.5a1.5 1.5 0 0 1 3 0V12"/><path d="M17 11.5a1.5 1.5 0 0 1 3 0V16a6 6 0 0 1-6 6h-2a6 6 0 0 1-5-2.7L4.3 15a1.5 1.5 0 0 1 2.4-1.8L8 15"/></svg>';
		?>
		<div class="rs-pf__graph rs-pf-rise rs-pf-rise--2" aria-hidden="true" data-rs-graph>
			<div class="rs-pf__graph-bar"><span>Speed Graph</span><span data-rs-graph-readout>Influence 43% · 43%</span></div>
			<div class="rs-pf__graph-plot">
				<svg viewBox="0 0 360 230" fill="none">
					<defs>
						<linearGradient id="rs-pf-curve-g" x1="40" y1="0" x2="320" y2="0" gradientUnits="userSpaceOnUse">
							<stop stop-color="#8f74ff"/>
							<stop offset="1" stop-color="#ea77ff"/>
						</linearGradient>
					</defs>
					<g stroke="#221d34">
						<path d="M20 40H340M20 90H340M20 140H340M20 190H340"/>
						<path d="M40 20V210M96 20V210M152 20V210M208 20V210M264 20V210M320 20V210"/>
					</g>
					<line data-arm="1" x1="40" y1="190" x2="160" y2="190" stroke="#625c7e" stroke-dasharray="4 5"/>
					<line data-arm="2" x1="320" y1="40" x2="200" y2="40" stroke="#625c7e" stroke-dasharray="4 5"/>
					<path class="rs-pf-curve" data-rs-curve d="M40 190C160 190 200 40 320 40" stroke="url(#rs-pf-curve-g)" stroke-width="3.5" stroke-linecap="round"/>
					<g fill="#ffd166">
						<rect x="33" y="183" width="14" height="14" rx="2" transform="rotate(45 40 190)"/>
						<rect x="313" y="33" width="14" height="14" rx="2" transform="rotate(45 320 40)"/>
					</g>
					<circle class="rs-pf-motion" data-rs-dot cx="40" cy="190" r="6" fill="#fff"/>
					<g class="rs-pf-g-handle" data-h="1" transform="translate(160 190)">
						<circle class="rs-pf-g-ring" r="11"/>
						<circle class="rs-pf-g-knob" r="5.5"/>
						<circle class="rs-pf-g-hit" r="18"/>
					</g>
					<g class="rs-pf-g-handle" data-h="2" transform="translate(200 40)">
						<circle class="rs-pf-g-ring" r="11"/>
						<circle class="rs-pf-g-knob" r="5.5"/>
						<circle class="rs-pf-g-hit" r="18"/>
					</g>
					<text x="44" y="224" fill="#625c7e" font-family="ui-monospace, Consolas, monospace" font-size="10">0f</text>
					<text x="300" y="224" fill="#625c7e" font-family="ui-monospace, Consolas, monospace" font-size="10">24f</text>
				</svg>
			</div>
			<div class="rs-pf__graph-presets">
				<button type="button" tabindex="-1" data-ease="0,0,1,1">Linear</button>
				<button type="button" tabindex="-1" data-ease="0.43,0,0.57,1" class="is-active">Easy Ease</button>
				<button type="button" tabindex="-1" data-ease="0,0,0.2,1">Ease Out</button>
				<button type="button" tabindex="-1" data-ease="0.8,0,0.2,1">Snappy</button>
				<button type="button" tabindex="-1" data-ease="0.34,1.25,0.64,1">Overshoot</button>
			</div>
			<div class="rs-pf__graph-foot">
				<code data-rs-graph-code>cubic-bezier(0.43, 0.00, 0.57, 1.00)</code>
				<button type="button" tabindex="-1" class="rs-pf__graph-copy" data-rs-graph-copy data-done="<?php echo esc_attr( $rs_is_en ? 'Copied' : 'কপি হয়েছে' ); ?>"><?php echo esc_html( $rs_is_en ? 'Copy' : 'কপি' ); ?></button>
				<span class="rs-pf__graph-track"><i data-rs-graph-ball></i></span>
			</div>
			<span class="rs-pf-hint" data-rs-hint="graph"><?php echo $rs_hand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?><?php echo esc_html( $rs_is_en ? 'Go on, drag me' : 'ধরে টানুন তো!' ); ?></span>
		</div>
	</header>

	<section class="rs-pf__wrap rs-pf__timeline rs-pf-rise rs-pf-rise--3" aria-hidden="true">
		<div class="rs-pf-tl" data-rs-tl>
			<div class="rs-pf-tl__head">
				<span class="rs-pf-tl__tc"><span data-rs-tc>00:00:02:07</span><span class="rs-pf-tl__speed" data-rs-speed></span></span>
				<ol class="rs-pf-tl__ruler"><li>0s</li><li>1s</li><li>2s</li><li>3s</li><li>4s</li><li>5s</li><li>6s</li></ol>
			</div>
			<div class="rs-pf-tl__row" style="--c: #ea77ff">
				<span class="rs-pf-tl__name">
					<button type="button" tabindex="-1" class="rs-pf-tl__switch" data-rs-eye><?php echo $rs_eye; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
					<button type="button" tabindex="-1" class="rs-pf-tl__switch" data-rs-lock><?php echo $rs_lock; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
					<span class="rs-pf-tl__label"><?php echo esc_html( $rs_is_en ? 'Motion design' : 'মোশন ডিজাইন' ); ?></span>
				</span>
				<span class="rs-pf-tl__track"><i class="rs-pf-tl__bar" style="--x: 3%; --w: 60%"></i><b style="--x: 3%"></b><b style="--x: 28%"></b><b style="--x: 63%"></b></span>
			</div>
			<div class="rs-pf-tl__row" style="--c: #31a8ff">
				<span class="rs-pf-tl__name">
					<button type="button" tabindex="-1" class="rs-pf-tl__switch" data-rs-eye><?php echo $rs_eye; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
					<button type="button" tabindex="-1" class="rs-pf-tl__switch" data-rs-lock><?php echo $rs_lock; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
					<span class="rs-pf-tl__label"><?php echo esc_html( $rs_is_en ? 'Automation' : 'অটোমেশন' ); ?></span>
				</span>
				<span class="rs-pf-tl__track"><i class="rs-pf-tl__bar" style="--x: 22%; --w: 72%"></i><b style="--x: 22%"></b><b style="--x: 47%"></b><b style="--x: 94%"></b></span>
			</div>
			<div class="rs-pf-tl__row" style="--c: #3ddc97">
				<span class="rs-pf-tl__name">
					<button type="button" tabindex="-1" class="rs-pf-tl__switch" data-rs-eye><?php echo $rs_eye; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
					<button type="button" tabindex="-1" class="rs-pf-tl__switch" data-rs-lock><?php echo $rs_lock; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
					<span class="rs-pf-tl__label"><?php echo esc_html( $rs_is_en ? 'Open source' : 'ওপেন সোর্স' ); ?></span>
				</span>
				<span class="rs-pf-tl__track"><i class="rs-pf-tl__bar" style="--x: 10%; --w: 86%"></i><b style="--x: 10%"></b><b style="--x: 54%"></b><b style="--x: 79%"></b><b style="--x: 96%"></b></span>
			</div>
			<span class="rs-pf-tl__scrub"><span class="rs-pf-tl__playhead"></span></span>
			<span class="rs-pf-hint rs-pf-hint--tl" data-rs-hint="tl"><?php echo $rs_hand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?><?php echo esc_html( $rs_is_en ? 'Grab me' : 'আমাকে ধরুন' ); ?></span>
		</div>
		<p class="rs-pf-tl__keys">
			<?php if ( $rs_is_en ) : ?>
				<kbd>Space</kbd> play or pause <span>·</span> <kbd>J</kbd><kbd>K</kbd><kbd>L</kbd> back, stop, forward <span>·</span> try typing <kbd>render</kbd>
			<?php else : ?>
				<kbd>Space</kbd> চালান বা থামান <span>·</span> <kbd>J</kbd><kbd>K</kbd><kbd>L</kbd> পেছনে, থামুন, সামনে <span>·</span> একবার <kbd>render</kbd> লিখে দেখুন
			<?php endif; ?>
		</p>
	</section>

	<section class="rs-pf__wrap rs-pf__section" aria-labelledby="rs-pf-toolbox">
		<h2 class="rs-pf-h" id="rs-pf-toolbox"><?php echo esc_html( $rs_is_en ? 'Toolbox' : 'টুলবক্স' ); ?></h2>
		<div class="rs-pf-toolbox">
			<div class="rs-pf-toolbox__group">
				<p class="rs-pf-toolbox__label"><?php echo esc_html( $rs_is_en ? 'Design & motion' : 'ডিজাইন ও মোশন' ); ?></p>
				<ul class="rs-pf-tiles">
					<li class="rs-pf-tile" style="--bg: #00005b; --fg: #9999ff"><b>Ae</b>After Effects</li>
					<li class="rs-pf-tile" style="--bg: #1e0633; --fg: #ea77ff"><b>Pr</b>Premiere Pro</li>
					<li class="rs-pf-tile" style="--bg: #001e36; --fg: #31a8ff"><b>Ps</b>Photoshop</li>
					<li class="rs-pf-tile" style="--bg: #330000; --fg: #ff9a00"><b>Ai</b>Illustrator</li>
					<li class="rs-pf-tile" style="--fg: #a259ff; --bd: #2c2642">
						<b><svg viewBox="0 0 2 3" aria-hidden="true"><path d="M.5 0H1V1H.5A.5.5 0 0 1 .5 0Z" fill="#f24e1e"/><path d="M1 0H1.5A.5.5 0 0 1 1.5 1H1Z" fill="#ff7262"/><path d="M.5 1H1V2H.5A.5.5 0 0 1 .5 1Z" fill="#a259ff"/><circle cx="1.5" cy="1.5" r=".5" fill="#1abcfe"/><path d="M1 2V2.5A.5.5 0 1 1 .5 2Z" fill="#0acf83"/></svg></b>Figma
					</li>
				</ul>
			</div>
			<div class="rs-pf-toolbox__group">
				<p class="rs-pf-toolbox__label"><?php echo esc_html( $rs_is_en ? 'Code & automation' : 'কোড ও অটোমেশন' ); ?></p>
				<ul class="rs-pf-tiles">
					<li class="rs-pf-tile" style="--fg: #f7df1e; --bd: #2c2642"><b>JS</b>JavaScript</li>
					<li class="rs-pf-tile" style="--fg: #6cc24a; --bd: #2c2642"><b>N</b>Node.js</li>
					<li class="rs-pf-tile" style="--fg: #4fa3ff; --bd: #2c2642"><b>{ }</b>CEP</li>
					<li class="rs-pf-tile" style="--fg: #b3b7f2; --bd: #2c2642"><b>php</b>PHP</li>
					<li class="rs-pf-tile" style="--fg: #4fa9da; --bd: #2c2642"><b>W</b>WordPress</li>
					<li class="rs-pf-tile" style="--fg: #f05032; --bd: #2c2642"><b>git</b>Git</li>
				</ul>
			</div>
		</div>
	</section>

	<section class="rs-pf__wrap rs-pf__section" id="rs-pf-work-section" aria-labelledby="rs-pf-work">
		<div class="rs-pf__work-head">
			<h2 class="rs-pf-h" id="rs-pf-work"><?php echo esc_html( $rs_is_en ? 'Selected work' : 'নির্বাচিত কাজ' ); ?></h2>

			<div class="rs-portfolio-filter rs-pf-filter" role="tablist" aria-label="<?php echo esc_attr( $rs_is_en ? 'Filter projects' : 'প্রজেক্ট ফিল্টার' ); ?>">
				<button type="button" class="rs-portfolio-filter__btn is-active" data-filter="all" role="tab" aria-selected="true">
					<?php echo esc_html( $rs_is_en ? 'All' : 'সব' ); ?>
					<span class="rs-pf-filter__n"><?php echo esc_html( $rs_num( count( $projects ) ) ); ?></span>
				</button>
				<?php foreach ( $rs_cats as $rs_cat_key => $rs_cat ) : ?>
					<?php if ( $rs_count_cat[ $rs_cat_key ] ) : ?>
						<button type="button" class="rs-portfolio-filter__btn" data-filter="<?php echo esc_attr( $rs_cat_key ); ?>" role="tab" aria-selected="false" style="--c: <?php echo esc_attr( $rs_cat[1] ); ?>">
							<?php echo esc_html( $rs_cat[0] ); ?>
							<span class="rs-pf-filter__n"><?php echo esc_html( $rs_num( $rs_count_cat[ $rs_cat_key ] ) ); ?></span>
						</button>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="rs-portfolio-grid rs-pf-grid" id="rs-portfolio-grid">
			<?php foreach ( $projects as $rs_i => $p ) : ?>
				<?php
				$pf_title   = $rs_is_en ? $p['title_en'] : $p['title_bn'];
				$pf_parts   = preg_split( '/\s+[—–]\s+/u', $pf_title, 2 );
				$pf_name    = $pf_parts[0];
				$pf_tagline = isset( $pf_parts[1] ) ? $pf_parts[1] : '';
				$pf_accent  = sanitize_hex_color( $p['accent'] );
				$pf_accent  = $pf_accent ? $pf_accent : '#6c4cff';
				$pf_cat     = isset( $rs_cats[ $p['category'] ] ) ? $rs_cats[ $p['category'] ][0] : '';
				$pf_feature = ( 0 === $rs_i );
				$pf_tags    = array_slice( $p['tags'], 0, $pf_feature ? 6 : 4 );
				$pf_more    = count( $p['tags'] ) - count( $pf_tags );
				$pf_domain  = ! empty( $p['direct_url'] ) ? preg_replace( '#^https?://(?:www\.)?([^/]+).*$#', '$1', $p['direct_url'] ) : '';
				$pf_label   = ( $rs_is_en ? 'Case study: ' : 'কেস স্টাডি: ' ) . $pf_name;
				$pf_gh      = isset( $rs_gh[ $p['id'] ] ) ? $rs_gh[ $p['id'] ] : null;
				$pf_yt      = ( 'video' === $p['category'] && preg_match( '~(?:youtu\.be/|youtube\.com/(?:watch\?v=|shorts/|embed/))([A-Za-z0-9_-]{11})~', (string) $p['direct_url'], $pf_ytm ) ) ? $pf_ytm[1] : '';
				?>
				<article class="rs-portfolio-card rs-pf-card rs-pf-card--<?php echo esc_attr( $p['category'] ); ?><?php echo $pf_feature ? ' is-featured' : ''; ?>" data-category="<?php echo esc_attr( $p['category'] ); ?>" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" id="project-<?php echo esc_attr( $p['id'] ); ?>" style="--a: <?php echo esc_attr( $pf_accent ); ?>">

					<div class="rs-pf-card__media rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( $pf_label ); ?>"<?php echo $pf_yt ? ' data-rs-scrub="' . esc_attr( $pf_yt ) . '"' : ''; ?>>
						<?php if ( $pf_yt ) : ?>
							<span class="rs-pf-card__scrub" aria-hidden="true"></span>
						<?php endif; ?>
						<?php if ( 'web' === $p['category'] ) : ?>
							<span class="rs-pf-card__bar" aria-hidden="true"><i></i><i></i><i></i><span><?php echo esc_html( $pf_domain ); ?></span></span>
						<?php endif; ?>

						<?php if ( ! empty( $p['image'] ) ) : ?>
							<img class="rs-pf-card__img<?php echo ( 'contain' === $p['image_fit'] ) ? ' is-contain' : ''; ?>" src="<?php echo esc_url( $p['image'] ); ?>" alt="" loading="<?php echo $pf_feature ? 'eager' : 'lazy'; ?>" decoding="async">
						<?php else : ?>
							<span class="rs-pf-card__mono" aria-hidden="true"><?php echo esc_html( mb_substr( $pf_name, 0, 1 ) ); ?></span>
						<?php endif; ?>

						<?php if ( 'video' === $p['category'] ) : ?>
							<span class="rs-pf-card__play" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l12-7.5z"/></svg></span>
						<?php endif; ?>

						<?php if ( $pf_cat ) : ?>
							<span class="rs-pf-card__cat"><?php echo esc_html( $pf_cat ); ?></span>
						<?php endif; ?>
					</div>

					<div class="rs-pf-card__body">
						<?php if ( $pf_feature ) : ?>
							<span class="rs-pf-card__flag"><?php echo esc_html( $rs_is_en ? 'Featured project' : 'ফিচার্ড প্রজেক্ট' ); ?></span>
						<?php endif; ?>

						<p class="rs-pf-card__type"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></p>

						<h3 class="rs-pf-card__title">
							<button type="button" class="rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>"><?php echo esc_html( $pf_name ); ?></button>
						</h3>

						<?php if ( $pf_tagline ) : ?>
							<p class="rs-pf-card__tagline"><?php echo esc_html( $pf_tagline ); ?></p>
						<?php endif; ?>

						<?php if ( $pf_feature ) : ?>
							<p class="rs-pf-card__summary"><?php echo esc_html( $rs_is_en ? $p['summary_en'] : $p['summary_bn'] ); ?></p>
						<?php endif; ?>

						<?php if ( $pf_tags ) : ?>
							<ul class="rs-pf-card__tags">
								<?php foreach ( $pf_tags as $tag ) : ?>
									<li class="rs-pf-tag"><?php echo esc_html( $tag ); ?></li>
								<?php endforeach; ?>
								<?php if ( $pf_more > 0 ) : ?>
									<li class="rs-pf-tag">+<?php echo esc_html( $rs_num( $pf_more ) ); ?></li>
								<?php endif; ?>
							</ul>
						<?php endif; ?>

						<?php if ( $pf_gh ) : ?>
							<ul class="rs-pf-card__stats">
								<li><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3.2l2.7 5.5 6 .9-4.35 4.25 1 6L12 17l-5.35 2.85 1-6L3.3 9.6l6-.9z"/></svg><span class="rs-pf-sr"><?php echo esc_html( $rs_is_en ? 'GitHub stars:' : 'GitHub স্টার:' ); ?></span> <?php echo esc_html( $rs_num( number_format_i18n( $pf_gh['stars'] ) ) ); ?></li>
								<?php if ( $pf_gh['downloads'] ) : ?>
									<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></svg><span class="rs-pf-sr"><?php echo esc_html( $rs_is_en ? 'Downloads:' : 'ডাউনলোড:' ); ?></span> <?php echo esc_html( $rs_num( number_format_i18n( $pf_gh['downloads'] ) ) ); ?></li>
								<?php endif; ?>
								<?php if ( $pf_gh['version'] ) : ?>
									<li class="rs-pf-card__ver">
										<?php
										echo esc_html( $pf_gh['version'] );
										if ( $pf_gh['published'] && strtotime( $pf_gh['published'] ) ) {
											$pf_ago = human_time_diff( strtotime( $pf_gh['published'] ), time() );
											echo esc_html( ' · ' . ( $rs_is_en ? $pf_ago . ' ago' : rs_bn_digits( $pf_ago ) . ' আগে' ) );
										}
										?>
									</li>
								<?php endif; ?>
							</ul>
						<?php endif; ?>

						<div class="rs-pf-card__foot">
							<button type="button" class="rs-pf-card__cta rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" aria-label="<?php echo esc_attr( $pf_label ); ?>">
								<?php echo esc_html( $rs_is_en ? 'Case study' : 'কেস স্টাডি' ); ?>
								<?php echo $rs_arrow_right; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
							</button>
							<?php if ( ! empty( $p['direct_url'] ) ) : ?>
								<a class="rs-pf-card__link" href="<?php echo esc_url( $p['direct_url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( $rs_is_en ? $p['action_en'] : $p['action_bn'] ); ?>
									<?php echo $rs_arrow_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<nav class="rs-portfolio-pagination" id="rs-portfolio-pagination" aria-label="<?php echo esc_attr( $rs_is_en ? 'Portfolio pages' : 'পোর্টফোলিওর পাতা' ); ?>" style="display: none;"></nav>

		<div class="rs-portfolio-empty rs-pf-empty" id="rs-portfolio-empty" style="display: none;">
			<p><?php echo esc_html( $rs_is_en ? 'No projects in this category yet.' : 'এই বিভাগে এখনো কোনো প্রজেক্ট নেই।' ); ?></p>
		</div>
	</section>

	<section class="rs-pf__wrap rs-pf__section" id="rs-pf-contact" aria-labelledby="rs-pf-contact-title">
		<div class="rs-pf-cta">
			<p class="rs-pf__eyebrow"><?php echo esc_html( $rs_is_en ? "Let's talk" : 'যোগাযোগ' ); ?></p>
			<h2 class="rs-pf-cta__title" id="rs-pf-contact-title">
				<?php echo esc_html( $rs_is_en ? "Have an idea in mind? Let's make it real." : 'মাথায় কোনো কাজের আইডিয়া ঘুরছে? চলুন বানিয়ে ফেলি।' ); ?>
			</h2>
			<p class="rs-pf-cta__text">
				<?php if ( $rs_is_en ) : ?>
					Cinematic video editing, motion animation, a fast custom website, or a tool that automates the boring part of your workflow: send a message and let's talk.
				<?php else : ?>
					সিনেমাটিক ভিডিও এডিটিং, মোশন অ্যানিমেশন, দ্রুতগতির কাস্টম ওয়েবসাইট, কিংবা কাজের একঘেয়ে অংশটা স্বয়ংক্রিয় করার কোনো টুল: একটা মেসেজ দিন, কথা হবে।
				<?php endif; ?>
			</p>
			<div class="rs-pf__actions">
				<?php $rs_email = rs_option( 'rs_email' ); ?>
				<?php if ( $rs_email ) : ?>
					<?php /* Copies the address, the same way the mail icon in the header does:
					   a mailto: link only helps readers with a mail app set up. */ ?>
					<button type="button" class="rs-pf-btn rs-pf-btn--primary" data-rs-copy="<?php echo esc_attr( $rs_email ); ?>" data-rs-copy-kind="mail" title="<?php echo esc_attr( $rs_email ); ?>" aria-label="<?php echo esc_attr( ( $rs_is_en ? 'Copy email address: ' : 'ইমেইল ঠিকানা কপি করুন: ' ) . $rs_email ); ?>">
						<?php echo wp_kses( rs_icon( 'mail', 17 ), rs_svg_tags() ); ?>
						<?php echo esc_html( $rs_is_en ? 'Send an email' : 'ইমেইল পাঠান' ); ?>
					</button>
				<?php endif; ?>
				<?php if ( rs_option( 'rs_linkedin' ) ) : ?>
					<a class="rs-pf-btn rs-pf-btn--ghost" href="<?php echo esc_url( rs_option( 'rs_linkedin' ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo wp_kses( rs_icon( 'linkedin', 17 ), rs_svg_tags() ); ?>
						LinkedIn
					</a>
				<?php endif; ?>
				<?php if ( rs_option( 'rs_facebook' ) ) : ?>
					<a class="rs-pf-btn rs-pf-btn--ghost" href="<?php echo esc_url( rs_option( 'rs_facebook' ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo wp_kses( rs_icon( 'facebook', 17 ), rs_svg_tags() ); ?>
						Facebook
					</a>
				<?php endif; ?>
				<a class="rs-pf-btn rs-pf-btn--ghost" href="https://github.com/raisulsohan" target="_blank" rel="noopener noreferrer">GitHub</a>
			</div>
		</div>
	</section>
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

					<!-- Before & after, when the project has both pictures -->
					<div class="rs-case-study-section" id="rs-modal-ba" hidden>
						<h4 class="rs-case-study-heading"><?php echo esc_html( $rs_is_en ? 'Before & after' : 'আগে ও পরে' ); ?></h4>
						<div class="rs-ba" style="--ba: 50%">
							<img class="rs-ba__img" data-rs-ba-after src="" alt="<?php echo esc_attr( $rs_is_en ? 'After' : 'পরে' ); ?>">
							<img class="rs-ba__img rs-ba__img--before" data-rs-ba-before src="" alt="<?php echo esc_attr( $rs_is_en ? 'Before' : 'আগে' ); ?>">
							<span class="rs-ba__line" aria-hidden="true"><span></span></span>
							<span class="rs-ba__tag rs-ba__tag--before" aria-hidden="true"><?php echo esc_html( $rs_is_en ? 'Before' : 'আগে' ); ?></span>
							<span class="rs-ba__tag rs-ba__tag--after" aria-hidden="true"><?php echo esc_html( $rs_is_en ? 'After' : 'পরে' ); ?></span>
							<input class="rs-ba__range" type="range" min="0" max="100" value="50" data-rs-ba-range aria-label="<?php echo esc_attr( $rs_is_en ? 'Compare before and after' : 'আগে ও পরে তুলনা করুন' ); ?>">
						</div>
					</div>

					<!-- The Challenge -->
					<div class="rs-case-study-section rs-case-study-box rs-case-study-box--challenge">
						<h4 class="rs-case-study-heading">
							<?php echo esc_html( $rs_is_en ? 'The Challenge & Context' : 'চ্যালেঞ্জ ও প্রেক্ষাপট' ); ?>
						</h4>
						<div class="rs-case-study-text" id="rs-modal-challenge"></div>
					</div>

					<!-- The Solution & Process -->
					<div class="rs-case-study-section rs-case-study-box rs-case-study-box--solution">
						<h4 class="rs-case-study-heading">
							<?php echo esc_html( $rs_is_en ? 'The Solution & Creative Process' : 'সমাধান ও কর্মপ্রক্রিয়া' ); ?>
						</h4>
						<div class="rs-case-study-text" id="rs-modal-solution"></div>
					</div>

					<!-- Key Highlights -->
					<div class="rs-case-study-section">
						<h4 class="rs-case-study-heading">
							<?php echo esc_html( $rs_is_en ? 'Key Highlights & Results' : 'মূল ফলাফল ও বিশেষ অর্জন' ); ?>
						</h4>
						<ul class="rs-case-study-list" id="rs-modal-highlights"></ul>
					</div>

					<!-- Tools & Technologies -->
					<div class="rs-case-study-section">
						<h4 class="rs-case-study-heading">
							<?php echo esc_html( $rs_is_en ? 'Tools & Technologies Used' : 'ব্যবহৃত সফটওয়্যার ও টুলস' ); ?>
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
		'before'      => ! empty( $p['before'] ) ? esc_url_raw( $p['before'] ) : '',
		'after'       => ! empty( $p['after'] ) ? esc_url_raw( $p['after'] ) : '',
	);
}
echo wp_json_encode( $client_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
?>
</script>

<script>
(function() {
	'use strict';

	var isEn = <?php echo $rs_is_en ? 'true' : 'false'; ?>;
	var bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
	function bn(n) {
		if (isEn) return '' + n;
		return ('' + n).replace(/\d/g, function(d) { return bnDigits[d]; });
	}

	/* 1. Category filter and in-page pagination */
	var buttons      = document.querySelectorAll('.rs-portfolio-filter__btn');
	var cards        = Array.prototype.slice.call(document.querySelectorAll('.rs-portfolio-card'));
	var empty        = document.getElementById('rs-portfolio-empty');
	var paginationEl = document.getElementById('rs-portfolio-pagination');
	var gridEl       = document.getElementById('rs-portfolio-grid');

	/* Four cards to a page, two rows of two. The featured card sits above
	   them on its own full row and is not one of the four. */
	var PER_PAGE  = 4;
	var curFilter = 'all';
	var curPage   = 1;

	var arrowPrev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>';
	var arrowNext = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>';

	if (gridEl) {
		gridEl.setAttribute('tabindex', '-1');
	}

	function getMatchingCards() {
		if (curFilter === 'all') {
			return cards;
		}
		return cards.filter(function(card) {
			return card.getAttribute('data-category') === curFilter;
		});
	}

	function paginate(list) {
		var pages = [];
		var i = 0;

		while (i < list.length) {
			var size = (0 === pages.length && list[i].classList.contains('is-featured')) ? PER_PAGE + 1 : PER_PAGE;
			pages.push(list.slice(i, i + size));
			i += size;
		}

		return pages;
	}

	function pageButton(page, label, content, extra) {
		return '<button type="button" class="rs-pf-page' + (extra || '') + '" data-page="' + page + '" aria-label="' + label + '">' + content + '</button>';
	}

	function renderPagination(totalPages) {
		if (!paginationEl) return;

		if (totalPages <= 1) {
			paginationEl.innerHTML = '';
			paginationEl.style.display = 'none';
			return;
		}

		paginationEl.style.display = 'flex';
		var html = '';

		html += curPage > 1
			? pageButton(curPage - 1, isEn ? 'Previous page' : 'আগের পাতা', arrowPrev, ' rs-pf-page--arrow')
			: '<span class="rs-pf-page rs-pf-page--arrow is-disabled" aria-hidden="true">' + arrowPrev + '</span>';

		for (var p = 1; p <= totalPages; p++) {
			if (p === curPage) {
				html += '<span class="rs-pf-page is-current" aria-current="page">' + bn(p) + '</span>';
			} else if (p === 1 || p === totalPages || Math.abs(p - curPage) <= 1) {
				html += pageButton(p, (isEn ? 'Page ' : 'পাতা ') + bn(p), bn(p));
			} else if (Math.abs(p - curPage) === 2) {
				html += '<span class="rs-pf-page__gap" aria-hidden="true">…</span>';
			}
		}

		html += curPage < totalPages
			? pageButton(curPage + 1, isEn ? 'Next page' : 'পরের পাতা', arrowNext, ' rs-pf-page--arrow')
			: '<span class="rs-pf-page rs-pf-page--arrow is-disabled" aria-hidden="true">' + arrowNext + '</span>';

		html += '<span class="rs-pf-sr" aria-live="polite">' + (isEn ? 'Page ' + curPage + ' of ' + totalPages : 'মোট ' + bn(totalPages) + ' পাতার ' + bn(curPage) + ' নম্বর পাতা') + '</span>';

		paginationEl.innerHTML = html;
	}

	/* mode: 'init' on load, 'filter' when a category is picked, 'page' when
	   a page button is pressed. Only a page change scrolls and moves focus. */
	function applyFilterAndPagination(mode) {
		var matching   = getMatchingCards();
		var pages      = paginate(matching);
		var totalPages = Math.max(1, pages.length);

		if (curPage > totalPages) {
			curPage = 1;
		}

		if (empty) {
			empty.style.display = matching.length === 0 ? 'block' : 'none';
		}

		var visible = pages[curPage - 1] || [];

		cards.forEach(function(card) {
			card.classList.remove('is-entering');
			card.style.display = visible.indexOf(card) !== -1 ? '' : 'none';
		});

		if ('init' !== mode) {
			visible.forEach(function(card, i) {
				card.style.setProperty('--i', i);
				void card.offsetWidth; /* restart the entrance */
				card.classList.add('is-entering');
			});
		}

		renderPagination(totalPages);

		if ('page' === mode && gridEl) {
			var head   = document.getElementById('rs-pf-work-section') || gridEl;
			var header = document.querySelector('.rs-header');
			var clear  = header ? header.offsetHeight : 0;
			var topPos = head.getBoundingClientRect().top + window.pageYOffset - clear - 16;

			window.scrollTo({ top: Math.max(0, topPos), behavior: 'smooth' });
			gridEl.focus({ preventScroll: true });
		}
	}

	if (buttons.length && cards.length) {
		buttons.forEach(function(btn) {
			btn.addEventListener('click', function() {
				var filter = this.getAttribute('data-filter');
				if (filter === curFilter) return;

				buttons.forEach(function(b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				this.classList.add('is-active');
				this.setAttribute('aria-selected', 'true');

				curFilter = filter;
				curPage = 1;
				applyFilterAndPagination('filter');
			});
		});
	}

	if (paginationEl) {
		paginationEl.addEventListener('click', function(e) {
			var link = e.target.closest('[data-page]');
			if (!link) return;
			e.preventDefault();

			var targetPage = parseInt(link.getAttribute('data-page'), 10);
			if (targetPage && targetPage !== curPage) {
				curPage = targetPage;
				applyFilterAndPagination('page');
			}
		});
	}

	// Initial render on page load
	applyFilterAndPagination('init');

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

		// Before & after slider
		var baWrap = document.getElementById('rs-modal-ba');
		if (baWrap) {
			var hasBa = Boolean(p.before && p.after);
			baWrap.hidden = !hasBa;
			if (hasBa) {
				baWrap.querySelector('[data-rs-ba-before]').src = p.before;
				baWrap.querySelector('[data-rs-ba-after]').src = p.after;
				baWrap.querySelector('[data-rs-ba-range]').value = 50;
				baWrap.querySelector('.rs-ba').style.setProperty('--ba', '50%');
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

	var baRangeEl = document.querySelector('[data-rs-ba-range]');
	if (baRangeEl) {
		baRangeEl.addEventListener('input', function() {
			this.parentNode.style.setProperty('--ba', this.value + '%');
		});
	}

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
<script>
/* A light that follows the pointer across each project card. */
(function () {
	if ( ! window.matchMedia || ! window.matchMedia( '(hover: hover)' ).matches ) {
		return;
	}
	document.querySelectorAll( '.rs-pf-card' ).forEach( function ( card ) {
		card.addEventListener( 'pointermove', function ( e ) {
			var r = card.getBoundingClientRect();
			card.style.setProperty( '--mx', ( e.clientX - r.left ) + 'px' );
			card.style.setProperty( '--my', ( e.clientY - r.top ) + 'px' );
		}, { passive: true } );
	} );
}());
</script>
<script>
/*
 * Toys on the portfolio stage, for the fun of it: the speed graph's handles
 * can be dragged, and so can the timeline's keyframes and its playhead.
 * A first visit gets a nudge to try them; once they have been touched the
 * nudge is never shown again.
 */
(function () {
	'use strict';

	var stage = document.querySelector('.rs-pf');
	if (!stage || !window.requestAnimationFrame || !window.PointerEvent) {
		return;
	}

	var reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
	var FLAG   = 'rs-pf-toy';
	var played = false;

	try {
		played = '1' === window.localStorage.getItem(FLAG);
	} catch (e) {}

	var hints = Array.prototype.slice.call(stage.querySelectorAll('[data-rs-hint]'));

	function capture(el, e) {
		try {
			el.setPointerCapture(e.pointerId);
		} catch (err) {}
	}

	function clamp(n, lo, hi) {
		return Math.max(lo, Math.min(hi, n));
	}

	function markPlayed() {
		if (played) {
			return;
		}
		played = true;

		hints.forEach(function (h) {
			h.classList.remove('is-on');
		});
		stage.classList.remove('rs-pf-toy-hint');
		wakeTimeline();

		try {
			window.localStorage.setItem(FLAG, '1');
		} catch (e) {}
	}

	/* The playhead waits beside its label during the nudge, then runs. */
	function wakeTimeline() {
		if (T && !T.drag && !reduce) {
			T.playing = true;
		}
	}

	/* Keeps a hint inside its panel, offset from whatever it points at. */
	function placeHint(hint, anchor, panel, dx, dy) {
		var a = anchor.getBoundingClientRect();
		var box = panel.getBoundingClientRect();
		var x = clamp(a.left - box.left + dx, 8, box.width - hint.offsetWidth - 8);
		var y = clamp(a.top - box.top + dy, 6, box.height - hint.offsetHeight - 6);

		hint.style.setProperty('--hx', Math.round(x) + 'px');
		hint.style.setProperty('--hy', Math.round(y) + 'px');
	}

	/* ------------------------------------------------------------------
	 * Speed graph
	 * ---------------------------------------------------------------- */

	var X0 = 40, W = 280, Y0 = 190, H = 150;
	var DEFAULT = [[120 / 280, 0], [160 / 280, 1]];
	var graph = stage.querySelector('[data-rs-graph]');
	var G = null;

	if (graph) {
		G = {
			svg: graph.querySelector('svg'),
			curve: graph.querySelector('[data-rs-curve]'),
			dot: graph.querySelector('[data-rs-dot]'),
			arms: [graph.querySelector('[data-arm="1"]'), graph.querySelector('[data-arm="2"]')],
			handles: [graph.querySelector('[data-h="1"]'), graph.querySelector('[data-h="2"]')],
			readout: graph.querySelector('[data-rs-graph-readout]'),
			code: graph.querySelector('[data-rs-graph-code]'),
			ball: graph.querySelector('[data-rs-graph-ball]'),
			hint: graph.querySelector('[data-rs-hint="graph"]'),
			p: [DEFAULT[0].slice(), DEFAULT[1].slice()],
			visible: true,
			active: -1,
			demo: 0
		};
	}

	function sx(u) {
		return X0 + W * u;
	}

	function sy(v) {
		return Y0 - H * v;
	}

	function bez(a, b, t) {
		var m = 1 - t;
		return 3 * m * m * t * a + 3 * m * t * t * b + t * t * t;
	}

	/* The curve parameter at which the graph reaches a given time. */
	function solveT(u) {
		var lo = 0, hi = 1, t = u;

		for (var i = 0; i < 22; i++) {
			t = (lo + hi) / 2;
			if (bez(G.p[0][0], G.p[1][0], t) < u) {
				lo = t;
			} else {
				hi = t;
			}
		}

		return t;
	}

	function fixed(n) {
		return (Math.round(n * 100) / 100).toFixed(2);
	}

	function drawGraph() {
		var a = G.p[0], b = G.p[1];
		var h1x = sx(a[0]).toFixed(1), h1y = sy(a[1]).toFixed(1);
		var h2x = sx(b[0]).toFixed(1), h2y = sy(b[1]).toFixed(1);

		G.curve.setAttribute('d', 'M40 190C' + h1x + ' ' + h1y + ' ' + h2x + ' ' + h2y + ' 320 40');
		G.arms[0].setAttribute('x2', h1x);
		G.arms[0].setAttribute('y2', h1y);
		G.arms[1].setAttribute('x2', h2x);
		G.arms[1].setAttribute('y2', h2y);
		G.handles[0].setAttribute('transform', 'translate(' + h1x + ' ' + h1y + ')');
		G.handles[1].setAttribute('transform', 'translate(' + h2x + ' ' + h2y + ')');

		if (G.readout) {
			G.readout.textContent = 'Influence ' + Math.round(a[0] * 100) + '% · ' + Math.round((1 - b[0]) * 100) + '%';
		}

		if (G.code) {
			G.code.textContent = 'cubic-bezier(' + fixed(a[0]) + ', ' + fixed(a[1]) + ', ' + fixed(b[0]) + ', ' + fixed(b[1]) + ')';
		}
	}

	function placeMotion(u) {
		var t = solveT(u);
		var v = bez(G.p[0][1], G.p[1][1], t);

		if (G.dot) {
			G.dot.setAttribute('cx', sx(u).toFixed(1));
			G.dot.setAttribute('cy', sy(v).toFixed(1));
		}

		if (G.ball) {
			G.ball.style.setProperty('--p', clamp(v, -0.15, 1.15).toFixed(4));
		}
	}

	function stopDemo() {
		if (G && G.demo) {
			window.cancelAnimationFrame(G.demo);
			G.demo = 0;
		}
	}

	/* The nudge: the lower handle pulls itself out and back once. */
	function demo() {
		var from = G.p[0][0], started = 0;

		function step(ts) {
			if (!started) {
				started = ts;
			}

			var k = Math.min(1, (ts - started) / 1700);

			G.p[0][0] = from + (0.84 - from) * (0.5 - 0.5 * Math.cos(k * Math.PI * 2));
			drawGraph();
			G.demo = k < 1 ? window.requestAnimationFrame(step) : 0;
		}

		G.demo = window.requestAnimationFrame(step);
	}

	if (G) {
		if (reduce) {
			G.curve.style.strokeDasharray = 'none';
		} else {
			G.curve.addEventListener('animationend', function () {
				G.curve.style.strokeDasharray = 'none';
			});
		}

		G.handles.forEach(function (handle, i) {
			handle.addEventListener('pointerdown', function (e) {
				e.preventDefault();
				capture(handle, e);
				G.active = i;
				markPreset(null);
				handle.classList.add('is-dragging');
				G.curve.style.strokeDasharray = 'none';
				stopDemo();
				markPlayed();
			});

			handle.addEventListener('pointermove', function (e) {
				if (G.active !== i) {
					return;
				}

				var m = G.svg.getScreenCTM();
				if (!m) {
					return;
				}

				var pt = G.svg.createSVGPoint();
				pt.x = e.clientX;
				pt.y = e.clientY;
				pt = pt.matrixTransform(m.inverse());

				G.p[i] = [clamp((pt.x - X0) / W, 0, 1), clamp((Y0 - pt.y) / H, -0.25, 1.25)];
				drawGraph();
			});

			function release() {
				G.active = -1;
				handle.classList.remove('is-dragging');
			}

			handle.addEventListener('pointerup', release);
			handle.addEventListener('pointercancel', release);
		});

		/* Double click puts the classic ease back. */
		G.svg.addEventListener('dblclick', function () {
			stopDemo();
			G.p = [DEFAULT[0].slice(), DEFAULT[1].slice()];
			markPreset(presetBtns[1]);
			drawGraph();
		});

		/* Presets glide the handles to a named ease. */
		var presetBtns = Array.prototype.slice.call(graph.querySelectorAll('[data-ease]'));
		var glide = 0;

		function markPreset(btn) {
			presetBtns.forEach(function (b) {
				b.classList.toggle('is-active', b === btn);
			});
		}

		presetBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var to = btn.getAttribute('data-ease').split(',').map(parseFloat);
				var from = [G.p[0][0], G.p[0][1], G.p[1][0], G.p[1][1]];
				var started = 0;

				stopDemo();
				markPlayed();
				markPreset(btn);
				G.curve.style.strokeDasharray = 'none';
				window.cancelAnimationFrame(glide);

				function step(ts) {
					if (!started) {
						started = ts;
					}

					var k = reduce ? 1 : Math.min(1, (ts - started) / 450);
					var e = 1 - Math.pow(1 - k, 3);
					var v = from.map(function (f, n) {
						return f + (to[n] - f) * e;
					});

					G.p = [[v[0], v[1]], [v[2], v[3]]];
					drawGraph();
					glide = k < 1 ? window.requestAnimationFrame(step) : 0;
				}

				glide = window.requestAnimationFrame(step);
			});
		});

		/* Copy the curve as CSS. */
		var copyBtn = graph.querySelector('[data-rs-graph-copy]');

		if (copyBtn) {
			var copyLabel = copyBtn.textContent;

			copyBtn.addEventListener('click', function () {
				var text = G.code ? G.code.textContent : '';

				function done() {
					copyBtn.textContent = copyBtn.getAttribute('data-done') + ' ✓';
					copyBtn.classList.add('is-done');
					window.setTimeout(function () {
						copyBtn.textContent = copyLabel;
						copyBtn.classList.remove('is-done');
					}, 1600);
				}

				/* The old way, for browsers that refuse the clipboard API. */
				function legacyCopy() {
					var ta = document.createElement('textarea');
					ta.value = text;
					ta.setAttribute('readonly', '');
					ta.style.position = 'fixed';
					ta.style.opacity = '0';
					document.body.appendChild(ta);
					ta.select();
					try {
						if (document.execCommand('copy')) {
							done();
						}
					} catch (err) {}
					document.body.removeChild(ta);
				}

				markPlayed();

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(done, legacyCopy);
					return;
				}

				legacyCopy();
			});
		}

		drawGraph();
		placeMotion(reduce ? 0.5 : 0);
	}

	/* ------------------------------------------------------------------
	 * Timeline
	 * ---------------------------------------------------------------- */

	var SPAN = 6, FPS = 24;
	var tl = stage.querySelector('[data-rs-tl]');
	var T = null;

	if (tl) {
		T = {
			scrub: tl.querySelector('.rs-pf-tl__scrub'),
			head: tl.querySelector('.rs-pf-tl__playhead'),
			tc: tl.querySelector('[data-rs-tc]'),
			hint: tl.querySelector('[data-rs-hint="tl"]'),
			keys: Array.prototype.slice.call(tl.querySelectorAll('.rs-pf-tl__track b')),
			t: 0.38,
			playing: !reduce && played,
			speed: 1,
			resume: 0,
			last: 0,
			visible: true,
			drag: null
		};

		T.keys.forEach(function (key) {
			key.rsX = (parseFloat(key.style.getPropertyValue('--x')) || 0) / 100;
		});
	}

	function pad(n) {
		return (n < 10 ? '0' : '') + n;
	}

	function drawPlayhead() {
		var frames = Math.round(T.t * SPAN * FPS);

		T.head.style.left = (T.t * 100).toFixed(3) + '%';

		if (T.tc) {
			T.tc.textContent = '00:00:' + pad(Math.floor(frames / FPS)) + ':' + pad(frames % FPS);
		}
	}

	function drawRow(track) {
		var xs = Array.prototype.map.call(track.querySelectorAll('b'), function (key) {
			return key.rsX;
		});
		var min = Math.min.apply(null, xs), max = Math.max.apply(null, xs);
		var bar = track.querySelector('.rs-pf-tl__bar');

		bar.style.setProperty('--x', (min * 100).toFixed(2) + '%');
		bar.style.setProperty('--w', ((max - min) * 100).toFixed(2) + '%');
	}

	/* A keyframe the playhead runs over flashes, as in a real timeline. */
	function flashCrossed(prev, cur) {
		if (Math.abs(cur - prev) > 0.5) {
			return;
		}

		var lo = Math.min(prev, cur), hi = Math.max(prev, cur);

		T.keys.forEach(function (key) {
			if (key.rsX > lo && key.rsX <= hi && !key.classList.contains('is-dragging') && !key.closest('.is-hidden')) {
				key.classList.remove('is-hit');
				void key.offsetWidth;
				key.classList.add('is-hit');
			}
		});
	}

	function pct(e, el) {
		var r = el.getBoundingClientRect();
		return clamp((e.clientX - r.left) / r.width, 0, 1);
	}

	function placeTlHint() {
		placeHint(T.hint, T.head, tl, 10, -2);
	}

	if (T) {
		tl.addEventListener('pointerdown', function (e) {
			var key = e.target.closest ? e.target.closest('.rs-pf-tl__track b') : null;

			if (e.target.closest && e.target.closest('.rs-pf-tl__switch')) {
				return;
			}

			/* A locked layer's keyframes stay put; the press scrubs instead. */
			if (key && key.closest('.is-locked')) {
				key = null;
			}

			if (key) {
				e.preventDefault();
				T.drag = { key: key, track: key.parentNode };
				key.classList.add('is-dragging');
			} else {
				if (e.clientX < T.scrub.getBoundingClientRect().left - 12) {
					return;
				}

				var prev = T.t;

				T.drag = { scrub: true };
				T.playing = false;
				window.clearTimeout(T.resume);
				tl.classList.add('is-scrubbing');
				T.t = pct(e, T.scrub);
				flashCrossed(prev, T.t);
				drawPlayhead();
			}

			markPlayed();
			capture(tl, e);
		});

		tl.addEventListener('pointermove', function (e) {
			if (!T.drag) {
				return;
			}

			if (T.drag.key) {
				T.drag.key.rsX = pct(e, T.drag.track);
				T.drag.key.style.setProperty('--x', (T.drag.key.rsX * 100).toFixed(2) + '%');
				drawRow(T.drag.track);
			} else {
				var prev = T.t;

				T.t = pct(e, T.scrub);
				flashCrossed(prev, T.t);
				drawPlayhead();
			}
		});

		function release() {
			if (!T.drag) {
				return;
			}

			if (T.drag.key) {
				T.drag.key.classList.remove('is-dragging');
			} else {
				tl.classList.remove('is-scrubbing');

				if (!reduce) {
					T.resume = window.setTimeout(function () {
						T.playing = true;
					}, 2500);
				}
			}

			T.drag = null;
		}

		tl.addEventListener('pointerup', release);
		tl.addEventListener('pointercancel', release);

		/* The eye hides a layer; the lock keeps its keyframes where they are. */
		Array.prototype.forEach.call(tl.querySelectorAll('[data-rs-eye], [data-rs-lock]'), function (btn) {
			btn.addEventListener('click', function () {
				var row = btn.closest('.rs-pf-tl__row');
				var cls = btn.hasAttribute('data-rs-eye') ? 'is-hidden' : 'is-locked';

				markPlayed();
				row.classList.toggle(cls);
				btn.classList.toggle('is-on', row.classList.contains(cls));
			});
		});

		/*
		 * Transport keys, as in an editor: Space plays and pauses, J runs
		 * backwards, K stops, L runs forwards, and J or L again go faster.
		 * They only listen while the pointer is over the hero or the
		 * timeline, so Space keeps scrolling the page everywhere else.
		 */
		var speedEl = tl.querySelector('[data-rs-speed]');
		var armed = false;

		[stage.querySelector('.rs-pf__hero'), tl.parentNode].forEach(function (zone) {
			if (!zone) {
				return;
			}
			zone.addEventListener('pointerenter', function () {
				armed = true;
			});
			zone.addEventListener('pointerleave', function () {
				armed = false;
			});
		});

		function showSpeed() {
			if (!speedEl) {
				return;
			}
			speedEl.textContent = ! T.playing ? '❚❚' : (T.speed < 0 ? '◀ ' : '▶ ') + Math.abs(T.speed) + '×';
			speedEl.classList.add('is-on');
			window.clearTimeout(speedEl.rsTimer);
			speedEl.rsTimer = window.setTimeout(function () {
				speedEl.classList.remove('is-on');
			}, 1400);
		}

		document.addEventListener('keydown', function (e) {
			if (!armed || e.ctrlKey || e.metaKey || e.altKey || !e.key) {
				return;
			}

			var tag = e.target && e.target.tagName;
			if ('INPUT' === tag || 'TEXTAREA' === tag || 'SELECT' === tag || (e.target && e.target.isContentEditable)) {
				return;
			}

			var overlay = document.getElementById('rs-case-study-overlay');
			if (overlay && !overlay.hidden) {
				return;
			}

			var key = e.key.toLowerCase();
			if (' ' !== key && 'j' !== key && 'k' !== key && 'l' !== key) {
				return;
			}

			e.preventDefault();
			markPlayed();
			window.clearTimeout(T.resume);

			if (' ' === key) {
				T.playing = !T.playing;
				T.speed = 1;
			} else if ('k' === key) {
				T.playing = false;
			} else if ('l' === key) {
				T.speed = T.playing && T.speed > 0 ? Math.min(8, T.speed * 2) : 1;
				T.playing = true;
			} else {
				T.speed = T.playing && T.speed < 0 ? Math.max(-8, T.speed * 2) : -1;
				T.playing = true;
			}

			showSpeed();
		});

		drawPlayhead();
	}

	/* ------------------------------------------------------------------
	 * One clock for both, idle while they are off screen
	 * ---------------------------------------------------------------- */

	if ('IntersectionObserver' in window) {
		var watch = new window.IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (G && entry.target === graph) {
					G.visible = entry.isIntersecting;
				}
				if (T && entry.target === tl) {
					T.visible = entry.isIntersecting;
				}
			});
		});

		if (graph) {
			watch.observe(graph);
		}
		if (tl) {
			watch.observe(tl);
		}
	}

	var LOOP = 2400, MOVE = 1700, clockStart = 0;

	function tick(ts) {
		if (G && G.visible && !reduce) {
			if (!clockStart) {
				clockStart = ts;
			}
			placeMotion(Math.min(1, ((ts - clockStart) % LOOP) / MOVE));
		}

		if (T) {
			var dt = T.last ? Math.min(64, ts - T.last) : 16;

			T.last = ts;

			if (T.visible && T.playing && !T.drag) {
				var prev = T.t;

				T.t += (dt * T.speed) / (SPAN * 1000);
				if (T.t > 1) {
					T.t = 0;
				} else if (T.t < 0) {
					T.t = 1;
				}
				flashCrossed(prev, T.t);
				drawPlayhead();
			}

			if (T.hint && T.hint.classList.contains('is-on')) {
				placeTlHint();
			}
		}

		window.requestAnimationFrame(tick);
	}

	window.requestAnimationFrame(tick);

	/* ------------------------------------------------------------------
	 * Hover-scrub video cards, like a clip in an editor's bin
	 * ---------------------------------------------------------------- */

	if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
		Array.prototype.forEach.call(stage.querySelectorAll('[data-rs-scrub]'), function (media) {
			var img = media.querySelector('.rs-pf-card__img');
			var line = media.querySelector('.rs-pf-card__scrub');

			if (!img) {
				return;
			}

			var id = media.getAttribute('data-rs-scrub');
			var original = img.getAttribute('src');
			var frames = null;
			var current = -1;

			media.addEventListener('pointerenter', function () {
				if (frames) {
					return;
				}
				/* YouTube keeps three stills from each video; they are only
				   asked for once someone actually hovers the card. */
				frames = [original].concat(['hq1', 'hq2', 'hq3'].map(function (name) {
					var url = 'https://i.ytimg.com/vi/' + id + '/' + name + '.jpg';
					(new Image()).src = url;
					return url;
				}));
			});

			media.addEventListener('pointermove', function (e) {
				if (!frames) {
					return;
				}

				var r = media.getBoundingClientRect();
				var k = clamp((e.clientX - r.left) / r.width, 0, 0.999);
				var i = Math.floor(k * frames.length);

				media.classList.add('is-scrubbing');
				if (line) {
					line.style.left = (k * 100).toFixed(2) + '%';
				}
				if (i !== current) {
					current = i;
					img.setAttribute('src', frames[i]);
				}
			});

			media.addEventListener('pointerleave', function () {
				media.classList.remove('is-scrubbing');
				current = -1;
				img.setAttribute('src', original);
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Type "render" anywhere on the page
	 * ---------------------------------------------------------------- */

	var typed = '';
	var rendering = false;

	document.addEventListener('keydown', function (e) {
		if (e.ctrlKey || e.metaKey || e.altKey || !e.key || 1 !== e.key.length) {
			return;
		}

		var tag = e.target && e.target.tagName;
		if ('INPUT' === tag || 'TEXTAREA' === tag || 'SELECT' === tag || (e.target && e.target.isContentEditable)) {
			return;
		}

		typed = (typed + e.key.toLowerCase()).slice(-6);

		if ('render' === typed && !rendering) {
			typed = '';
			renderQueue();
		}
	});

	function renderQueue() {
		var en = 0 === (document.documentElement.lang || '').indexOf('en');
		var box = document.createElement('div');

		rendering = true;
		box.className = 'rs-pf-rq';
		box.setAttribute('role', 'status');
		box.innerHTML =
			'<div class="rs-pf-rq__head"><span>Render Queue</span><span class="rs-pf-rq__time">0:00:00</span></div>' +
			'<div class="rs-pf-rq__row"><span class="rs-pf-rq__name">Portfolio_FINAL_v7_final2.mp4</span><span class="rs-pf-rq__status">' + (en ? 'Rendering…' : 'রেন্ডার হচ্ছে…') + '</span></div>' +
			'<div class="rs-pf-rq__bar"><i></i></div>' +
			'<div class="rs-pf-rq__meta"><span class="rs-pf-rq__frames">0 / 240</span><span>H.264 · 1920×1080 · 24 fps</span></div>';
		document.body.appendChild(box);

		var bar = box.querySelector('.rs-pf-rq__bar i');
		var frames = box.querySelector('.rs-pf-rq__frames');
		var time = box.querySelector('.rs-pf-rq__time');
		var status = box.querySelector('.rs-pf-rq__status');
		var DUR = reduce ? 700 : 3200;
		var started = 0;

		window.requestAnimationFrame(function () {
			box.classList.add('is-on');
		});

		function step(ts) {
			if (!started) {
				started = ts;
			}

			var k = Math.min(1, (ts - started) / DUR);
			/* Quick to ninety percent, then the last frames drag, as they do. */
			var done = k < 0.7 ? (k / 0.7) * 0.9 : 0.9 + ((k - 0.7) / 0.3) * 0.1;

			bar.style.width = (done * 100).toFixed(1) + '%';
			frames.textContent = Math.round(done * 240) + ' / 240';
			time.textContent = '0:00:0' + Math.floor((ts - started) / 1000);

			if (k < 1) {
				window.requestAnimationFrame(step);
				return;
			}

			box.classList.add('is-done');
			status.textContent = en ? '✓ Render complete' : '✓ রেন্ডার শেষ';

			if (!reduce) {
				confetti();
			}

			window.setTimeout(function () {
				box.classList.remove('is-on');
				window.setTimeout(function () {
					box.parentNode.removeChild(box);
					rendering = false;
				}, 450);
			}, 3200);
		}

		window.requestAnimationFrame(step);
	}

	function confetti() {
		var layer = document.createElement('div');
		var colors = ['#6c4cff', '#ea77ff', '#31a8ff', '#3ddc97', '#ffd166', '#ff9a00'];

		layer.className = 'rs-pf-confetti';
		layer.setAttribute('aria-hidden', 'true');

		for (var i = 0; i < 90; i++) {
			var bit = document.createElement('i');

			bit.style.left = (Math.random() * 100).toFixed(1) + '%';
			bit.style.background = colors[i % colors.length];
			bit.style.setProperty('--dx', ((Math.random() - 0.5) * 40).toFixed(1) + 'vw');
			bit.style.setProperty('--r', Math.round(Math.random() * 900 - 450) + 'deg');
			bit.style.setProperty('--d', (1.8 + Math.random() * 1.6).toFixed(2) + 's');
			bit.style.setProperty('--delay', (Math.random() * 0.35).toFixed(2) + 's');
			layer.appendChild(bit);
		}

		document.body.appendChild(layer);
		window.setTimeout(function () {
			layer.parentNode.removeChild(layer);
		}, 4000);
	}

	/* ------------------------------------------------------------------
	 * The first-visit nudge
	 * ---------------------------------------------------------------- */

	if (!played) {
		stage.classList.add('rs-pf-toy-hint');

		window.setTimeout(function () {
			if (played) {
				return;
			}

			if (G && G.hint) {
				placeHint(G.hint, G.handles[0].querySelector('.rs-pf-g-knob'), graph, 16, -46);
				G.hint.classList.add('is-on');
			}

			if (T && T.hint) {
				placeTlHint();
				T.hint.classList.add('is-on');
			}

			if (G && !reduce) {
				demo();
			}
		}, 1900);

		/* The labels step back after a while; the pulsing rings stay until
		   something is touched. */
		window.setTimeout(function () {
			hints.forEach(function (h) {
				h.classList.remove('is-on');
			});
			wakeTimeline();
		}, 15000);
	}
}());
</script>


<?php
get_footer();
