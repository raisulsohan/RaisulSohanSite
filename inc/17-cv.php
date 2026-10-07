<?php
/**
 * The CV.
 *
 * One page, /cv/, and the same in both editions: a résumé is written once,
 * in one language, so nothing in this file asks rs_is_en(). The content is a
 * PHP array rather than text in the page editor, for the reason the portfolio
 * ships its defaults in code: what is committed is what the site serves, and
 * the PDF in assets/cv/ is built from the same facts, so the two cannot drift
 * apart without a commit saying so.
 *
 * page-cv.php renders rs_cv_data(); the page itself is created once per site
 * by rs_seed_cv_page(), so a push is all it takes to put the CV online.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is the CV page.
 *
 * Only meaningful once the main query is set up, so it is asked from the
 * header, the template and wp_head — never from init.
 *
 * @return bool
 */
function rs_is_cv_page() {
	return is_page_template( 'page-cv.php' );
}

/**
 * The CV's address on this edition.
 *
 * @return string
 */
function rs_cv_url() {
	return home_url( '/cv/' );
}

/**
 * The PDF of the same CV, with the theme version as cache buster, or an
 * empty string when the file is not there (a half-deployed theme).
 *
 * @return string
 */
function rs_cv_pdf() {
	$file = 'assets/cv/Raisul_Sohan_CV.pdf';

	if ( ! file_exists( RS_DIR . '/' . $file ) ) {
		return '';
	}

	return add_query_arg( 'ver', RS_VERSION, RS_URI . '/' . $file );
}

/**
 * Bold and links inside a line of the CV.
 *
 * The text is escaped first; only then are **bold** and [label](url) turned
 * into markup, so nothing written in the array can carry HTML of its own.
 *
 * @param string $text A line that may use **bold** and [label](https://…).
 * @return string Escaped HTML.
 */
function rs_cv_rich( $text ) {
	$out = esc_html( $text );
	$out = (string) preg_replace( '~\*\*(.+?)\*\*~', '<strong>$1</strong>', $out );

	return (string) preg_replace_callback(
		'~\[([^\]]+)\]\(([^)\s]+)\)~',
		function ( $m ) {
			return '<a href="' . esc_url( html_entity_decode( $m[2], ENT_QUOTES, 'UTF-8' ) ) . '" target="_blank" rel="noopener noreferrer">' . $m[1] . '</a>';
		},
		$out
	);
}

/**
 * Everything the CV says, in the order the page says it.
 *
 * Lines may use **bold** and [label](url); see rs_cv_rich().
 *
 * @return array
 */
function rs_cv_data() {
	return array(
		'name'       => 'Raisul Islam Sohan',
		'roles'      => array( 'Motion Designer', '2D Animator', 'Creative Developer' ),
		'location'   => 'Dhaka, Bangladesh',
		'email'      => 'lettertosohan@gmail.com',
		'phone'      => '+880 1775 860544',
		'whatsapp'   => 'https://wa.me/8801775860544',
		'updated'    => 'October 2026',

		'links'      => array(
			array( 'label' => 'raisulsohan.com', 'url' => 'https://raisulsohan.com/en/portfolio/' ),
			array( 'label' => 'linkedin.com/in/raisulsohan', 'url' => 'https://www.linkedin.com/in/raisulsohan/' ),
			array( 'label' => 'github.com/raisulsohan', 'url' => 'https://github.com/raisulsohan' ),
			array( 'label' => 'youtube.com/@nomolosfiles', 'url' => 'https://www.youtube.com/@nomolosfiles' ),
			array( 'label' => 'Showreel on YouTube', 'url' => 'https://youtu.be/Hdq8STf5beQ' ),
		),

		/* One sentence for the hero and the search result. */
		'summary'    => 'Motion designer and 2D animator with 10+ years turning complex briefs into clear visual stories. I work from concept and storyboard through animation and sound, and build free tools that help creative teams move faster.',

		'profile'    => 'Motion designer and 2D animator with 10+ years of experience on story-led work for international development, technology and editorial teams. Selected clients include UNDP, UNICEF, GIZ, USAID, the World Bank and WHO. Alongside client work, I build free tools for Adobe, Figma and Chrome, and direct animated documentaries from script to final sound. I pair hands-on craft with production leadership, carrying projects from the first storyboard to delivery and improving the workflow for the next one.',

		'stats'      => array(
			array( 'label' => 'Years in motion', 'value' => '10+' ),
			array( 'label' => 'Open-source tools', 'value' => '7' ),
			array( 'label' => 'Documentary films', 'value' => '3' ),
			array( 'label' => 'Organisations served', 'value' => '12' ),
		),

		'highlights' => array(
			array( 'lead' => '100% on-time delivery', 'text' => 'across Vidiosa client campaigns; a new storyboard-and-feedback workflow cut turnaround by 15%.' ),
			array( 'lead' => '200K+ organic views', 'text' => 'on campaigns where I directed the creative, with measurable audience engagement.' ),
			array( 'lead' => '7 free open-source tools', 'text' => 'for Adobe CC, Figma and Chrome, alongside 1,100+ GitHub contributions in the past year.' ),
			array( 'lead' => '3 animated documentaries', 'text' => 'written, illustrated, animated and sound-designed for Nomolos and Bichitro Biggan.' ),
		),

		'experience' => array(
			array(
				'title'   => 'Independent Motion Designer & Creative Developer',
				'org'     => 'Self-employed',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Apr 2026 – Present',
				'bullets' => array(
					'Created **Nomolos** ([youtube.com/@nomolosfiles](https://www.youtube.com/@nomolosfiles)), an animated documentary channel about decisions that backfired. Wrote, illustrated, animated and sound-designed two episodes solo: **Prohibition** (9:40) and **Cobra Effect** (8:49, 23 scenes).',
					'Write and animate science stories for **Bichitro Biggan**; created **Consciousness** (4:08) with nine hand-drawn scenes, multiplane 3D parallax and volumetric lighting.',
					'Build and maintain seven free tools for editors and motion designers: Adobe CEP / ExtendScript panels, a Figma plugin, and Chrome and Edge extensions, with regular releases and documentation.',
					'Built two bilingual WordPress platforms from scratch with PHP and vanilla JavaScript: **bichitrobiggan.com** and **raisulsohan.com**.',
				),
			),
			array(
				'title'   => 'Project Manager',
				'org'     => 'Vidiosa',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Aug 2024 – Mar 2026',
				'bullets' => array(
					'Led animation and motion graphics production for global SaaS and technology clients, from brief and storyboard through delivery.',
					'Managed cross-functional teams, budgets, schedules and client reviews, delivering every campaign on time.',
					'Introduced a storyboard-and-feedback workflow that cut turnaround time by 15%; directed campaigns with 200K+ organic views.',
				),
			),
			array(
				'title'   => 'Senior Motion & Graphics Designer',
				'org'     => 'JMI Group',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Jan 2022 – Jul 2024',
				'bullets' => array(
					'Maintained a large industrial group\'s visual identity across social, web, email, brochures, advertising and print-ready artwork.',
					'Produced and edited campaign video; presented 2D and 3D concepts to marketing and product teams.',
					'Coordinated concurrent projects with photographers, illustrators and copywriters while keeping brand standards consistent.',
				),
			),
			array(
				'title'   => 'Senior Motion Graphics Designer & 2D Animator',
				'org'     => 'Big Blue Communications',
				'place'   => 'Bangladesh & UK',
				'dates'   => 'Jan 2018 – Dec 2021',
				'bullets' => array(
					'Created explainers, infographic films, e-learning and social animation for international development clients, from pitch and storyboard through rigging and final animation.',
					'**UNICEF:** monthly story-based animation for the Oky period tracker. **USAID:** twelve infographic animations. **GIZ:** a climate-change awareness film for Bangladesh.',
					'**ITF Seafarers\' Trust:** app explainers in English, Spanish and Portuguese. **Landell Mills:** a 3D map film about the Mekong river.',
					'Also created work for Awaj Foundation, the Dutch Embassy, HelpAge International and DAI, including Lottie animation and an interactive Adobe Animate site.',
				),
			),
			array(
				'title'   => 'Video Editor & Animator',
				'org'     => 'Think School (ThinkBangla)',
				'place'   => 'Bangladesh & UK',
				'dates'   => '2015 – 2017',
				'bullets' => array(
					'Edited and animated 20+ educational videos on Bangladesh\'s science, history and culture; also handled storyboarding and YouTube SEO.',
				),
			),
			array(
				'title'   => 'Earlier career',
				'org'     => 'Old Bay Media · BDSPORTSNEWS.COM',
				'place'   => 'Dhaka',
				'dates'   => '2010 – 2015',
				'bullets' => array(
					'**Program Manager & Video Editor, Old Bay Media (2013 – 2015):** built and ran Bangla Boi, an online bookshop, from its catalogue and publisher deals to customer service and delivery.',
					'**Program Manager & Sports Journalist, BDSPORTSNEWS.COM (2010 – 2012):** reported from the Sher-e-Bangla stadium press box and interviewed national cricketers and footballers.',
				),
			),
		),

		'tools'      => array(
			array(
				'name' => 'LazyLord',
				'url'  => 'https://github.com/raisulsohan/LazyLord',
				'kind' => 'Adobe CEP panel + Figma plugin · TypeScript',
				'text' => 'Moves vector artwork between Figma, Photoshop, Illustrator and After Effects as a free Overlord alternative.',
			),
			array(
				'name' => 'LazyMotionToolkit',
				'url'  => 'https://github.com/raisulsohan/LazyMotionToolkit',
				'kind' => 'After Effects ScriptUI panel · ExtendScript',
				'text' => 'Nine dockable motion tools, including smart precomp, auto text boxes, eased fades, animated arrows, anchor pad and grids.',
			),
			array(
				'name' => 'LazyKick',
				'url'  => 'https://github.com/raisulsohan/LazyKick',
				'kind' => 'CEP panel · After Effects & Premiere Pro',
				'text' => 'Paste clipboard images to the timeline, add time-coded project notes and auto-import media folders.',
			),
			array(
				'name' => 'Lazy-Image',
				'url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
				'kind' => 'CEP extension · CDP browser automation',
				'text' => 'Generate images from inside After Effects and Premiere Pro without an API key or browser switching.',
			),
			array(
				'name' => 'LazyScroll · LazySnap · LazyRuler',
				'url'  => 'https://raisulsohan.com/en/portfolio/',
				'kind' => 'Chrome & Edge extensions · Manifest V3',
				'text' => 'Browser tools for per-site media volume, article and commentary text, plus Photoshop-style rulers and guides.',
			),
		),

		'films'      => array(
			array(
				'title' => 'Nomolos',
				'url'   => 'https://www.youtube.com/@nomolosfiles',
				'meta'  => 'Animated documentary channel',
				'text'  => 'Two films about history\'s backfires: **Prohibition** (9:40) and **Cobra Effect** (8:49). Script, illustration, animation and sound design, all created solo.',
			),
			array(
				'title' => 'Consciousness',
				'url'   => 'https://raisulsohan.github.io/Consciousness-animation/',
				'meta'  => 'Animated science documentary for Bichitro Biggan, 4:08',
				'text'  => 'A 4:08 science film with nine illustrated scenes, multiplane 3D parallax, volumetric lighting and original sound design.',
			),
			array(
				'title' => 'Showreel',
				'url'   => 'https://youtu.be/Hdq8STf5beQ',
				'meta'  => 'Motion design · 2D animation · SaaS product video',
				'text'  => 'A short reel of selected motion design, 2D animation and SaaS product work.',
			),
		),

		'skills'     => array(
			array(
				'group' => 'Motion & design',
				'items' => array(
					'After Effects (expert): Duik Angela and Joystick \'n Sliders rigging, expressions, 3D parallax',
					'Premiere Pro, Cinema 4D, Audition',
					'Illustrator, Photoshop, InDesign, Figma, Animate',
					'Storyboarding, illustration, sound design, YouTube SEO',
				),
			),
			array(
				'group' => 'Code & automation',
				'items' => array(
					'JavaScript (ES6+), TypeScript, Node.js',
					'Adobe CEP, ExtendScript, ScriptUI; Figma Plugin API; Chrome extensions (Manifest V3)',
					'PHP, WordPress theme architecture, HTML, CSS, Lottie',
					'Git and GitHub, documentation, release management',
				),
			),
			array(
				'group' => 'Production & leadership',
				'items' => array(
					'Project management: ClickUp, Trello, Asana',
					'Creative direction, scriptwriting, storyboard review',
					'Client and stakeholder communication, budgeting, scheduling',
					'AI-assisted research and scripting: Claude, ChatGPT, Gemini, Perplexity',
				),
			),
		),

		'education'  => array(
			array( 'lead' => 'B.S.S. (Hons.) in Social Science', 'text' => 'Government Bangla College, Dhaka · 2014' ),
			array( 'lead' => 'HSC, Science (2007) · SSC, Science', 'text' => 'Motijheel Model High School & College (2005)' ),
			array( 'lead' => 'Continuing education', 'text' => 'Motion Design School, LinkedIn Learning, Skillshare (motion graphics, video editing, graphic design)' ),
		),

		'languages'  => 'Bengali (native) · English (professional)',
	);
}

/* =========================================================================
 * The page's own stylesheet, search result and structured data
 * ====================================================================== */

/**
 * The CV's page bundle, only on the CV: the sheet is a few kilobytes of
 * rules nothing else on the site uses.
 */
function rs_cv_assets() {
	if ( ! rs_is_cv_page() ) {
		return;
	}

	wp_enqueue_style( 'rs-cv', RS_URI . '/assets/cv.min.css', array( 'rs-style' ), RS_VERSION );
}
add_action( 'wp_enqueue_scripts', 'rs_cv_assets', 20 );

/**
 * What a search result and a shared link say about the CV.
 *
 * rs_seo_context() asks here, because the page has no body text of its own
 * for rs_summary() to shorten.
 *
 * @return array { title, description, url, type }
 */
function rs_cv_seo() {
	$cv = rs_cv_data();

	return array(
		'title'       => $cv['name'] . ' — CV',
		'description' => rs_shorten( $cv['summary'], 160 ),
		'url'         => rs_cv_url(),
		'type'        => 'profile',
	);
}

/**
 * The person the page is about, for search engines.
 */
function rs_cv_schema() {
	if ( ! rs_is_cv_page() ) {
		return;
	}

	$cv = rs_cv_data();

	$data = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'ProfilePage',
		'url'        => rs_cv_url(),
		'mainEntity' => array(
			'@type'    => 'Person',
			'name'     => $cv['name'],
			'jobTitle' => implode( ', ', $cv['roles'] ),
			'email'    => 'mailto:' . $cv['email'],
			'url'      => home_url( '/' ),
			'address'  => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Dhaka',
				'addressCountry'  => 'BD',
			),
			'sameAs'   => array_values( wp_list_pluck( $cv['links'], 'url' ) ),
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON from wp_json_encode().
}
add_action( 'wp_head', 'rs_cv_schema', 3 );

/* =========================================================================
 * The page itself
 * ====================================================================== */

/**
 * Create the CV page once per site.
 *
 * The same idea as the portfolio seeding: a page that exists only in the
 * dashboard would have to be made by hand on every edition after every fresh
 * install, so the theme makes it — a published page at /cv/ on this
 * template — and sets a flag so it is never made twice, even if it is later
 * deleted on purpose. A page already at /cv/ is kept; it is only pointed at
 * this template when it has none of its own.
 */
function rs_seed_cv_page() {
	if ( get_option( 'rs_cv_page_seeded_v1' ) ) {
		return;
	}

	$page = get_page_by_path( 'cv', OBJECT, 'page' );

	if ( ! $page ) {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'orderby' => 'ID',
				'order'   => 'ASC',
				'fields'  => 'ID',
			)
		);

		$id = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_title'     => 'CV',
				'post_name'      => 'cv',
				'post_status'    => 'publish',
				'post_author'    => $admins ? (int) $admins[0] : 0,
				'post_content'   => '',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'meta_input'     => array( '_wp_page_template' => 'page-cv.php' ),
			),
			true
		);

		/* Nothing written, nothing flagged: the next request tries again. */
		if ( is_wp_error( $id ) || ! $id ) {
			return;
		}
	} elseif ( ! get_page_template_slug( $page ) ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-cv.php' );
	}

	update_option( 'rs_cv_page_seeded_v1', 1 );

	/* The address may have been cached as a 404 before the page existed. */
	if ( function_exists( 'rs_purge_host_cache_soon' ) ) {
		rs_purge_host_cache_soon();
	}
}
add_action( 'init', 'rs_seed_cv_page', 25 );
