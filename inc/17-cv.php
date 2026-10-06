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
		'updated'    => 'October 2026',

		'links'      => array(
			array( 'label' => 'raisulsohan.com', 'url' => 'https://raisulsohan.com/en/portfolio/' ),
			array( 'label' => 'linkedin.com/in/raisulsohan', 'url' => 'https://www.linkedin.com/in/raisulsohan/' ),
			array( 'label' => 'github.com/raisulsohan', 'url' => 'https://github.com/raisulsohan' ),
			array( 'label' => 'youtube.com/@nomolosfiles', 'url' => 'https://www.youtube.com/@nomolosfiles' ),
			array( 'label' => 'Showreel on YouTube', 'url' => 'https://youtu.be/Hdq8STf5beQ' ),
		),

		/* One sentence for the hero and the search result. */
		'summary'    => 'Motion designer and 2D animator with ten years of stories told in frames, and a self-taught developer who builds the tools behind the work: free open-source panels for After Effects, Premiere Pro and Figma, and Nomolos, an animated documentary channel.',

		'profile'    => 'Motion designer and 2D animator with 10+ years of turning scripts into stories for the screen, and a self-taught developer who builds the tools behind the work. Produced story-driven animation for UNDP, UNICEF, GIZ, USAID, the World Bank and WHO; led production for a SaaS animation studio as project manager; author of seven free open-source tools for After Effects, Premiere Pro, Figma and Chrome; and creator of Nomolos, an animated documentary channel on history\'s greatest backfires. Equally at home rigging a character in After Effects and shipping a TypeScript panel that saves the team an afternoon.',

		'stats'      => array(
			array( 'label' => 'Years in motion', 'value' => '10+' ),
			array( 'label' => 'Open-source tools', 'value' => '7' ),
			array( 'label' => 'Documentary films', 'value' => '3' ),
			array( 'label' => 'Organisations served', 'value' => '12' ),
		),

		'highlights' => array(
			array( 'lead' => '100% on-time delivery', 'text' => 'across every client campaign at Vidiosa, while a new storyboard-and-feedback workflow cut turnaround time by 15%.' ),
			array( 'lead' => '200K+ organic views', 'text' => 'on campaigns where I directed the creative, with measurable uplift in audience engagement.' ),
			array( 'lead' => '7 open-source tools', 'text' => 'for Adobe CC, Figma and Chrome, released free; 1,100+ GitHub contributions in the last twelve months.' ),
			array( 'lead' => '3 animated documentaries', 'text' => 'written, illustrated, animated and sound-designed solo in After Effects, for the Nomolos channel and Bichitro Biggan.' ),
		),

		'experience' => array(
			array(
				'title'   => 'Independent Motion Designer & Creative Developer',
				'org'     => 'Self-employed',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Apr 2026 – Present',
				'bullets' => array(
					'Created and run **Nomolos** ([youtube.com/@nomolosfiles](https://www.youtube.com/@nomolosfiles)), an animated documentary channel on history\'s greatest backfires: decisions that achieved the opposite of what they intended. Each episode is written, illustrated, animated and sound-designed solo in After Effects; two episodes live (Prohibition, 9:40; Cobra Effect, 8:49, 23 scenes).',
					'Write, illustrate and animate science documentaries for **Bichitro Biggan**, a Bengali science magazine; latest film **Consciousness** (4:08, nine hand-drawn scenes, multiplane 3D parallax, volumetric lighting).',
					'Build and maintain seven free open-source tools for editors and motion designers: Adobe CEP / ExtendScript panels, a Figma plugin and Chrome and Edge extensions, released with documentation on a weekly cadence.',
					'Architected two zero-plugin WordPress platforms from scratch in PHP and vanilla JavaScript: **bichitrobiggan.com** (bilingual Bengali and English magazine) and **raisulsohan.com**.',
				),
			),
			array(
				'title'   => 'Project Manager',
				'org'     => 'Vidiosa',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Aug 2024 – Mar 2026',
				'bullets' => array(
					'Led end-to-end animation and motion graphics production for global SaaS and tech clients, from brief to final delivery.',
					'Coordinated cross-functional creative teams and managed budgets, schedules and client communication, holding a 100% on-time delivery record.',
					'Introduced a streamlined storyboard-and-feedback workflow that reduced project turnaround time by 15%.',
					'Directed creative execution on key client campaigns, achieving 200K+ organic views and measurable uplift in engagement.',
				),
			),
			array(
				'title'   => 'Senior Motion & Graphics Designer',
				'org'     => 'JMI Group',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Jan 2022 – Jul 2024',
				'bullets' => array(
					'Owned the visual identity of a large industrial group across digital and print: social media, web banners, email newsletters, brochures, advertisements and print-ready artwork.',
					'Produced and edited video content for marketing campaigns; presented 2D and 3D concepts and mock-ups to marketing and product teams for approval.',
					'Ran multiple concurrent projects with photographers, illustrators and copywriters, keeping brand standards consistent.',
				),
			),
			array(
				'title'   => 'Senior Motion Graphics Designer & 2D Animator',
				'org'     => 'Big Blue Communications',
				'place'   => 'Bangladesh & UK',
				'dates'   => 'Jan 2018 – Dec 2021',
				'bullets' => array(
					'Delivered animated films, explainers, infographic animations, e-learning content and social visuals for international development clients: pitches, storyboards, animatics, Illustrator vector assets, character rigging and animation.',
					'**UNICEF:** story-based monthly comic animation series for the Oky period tracker\'s period-positive campaign. **USAID:** twelve story-based infographic animations. **GIZ:** climate-change awareness film for Bangladesh.',
					'**ITF Seafarers\' Trust (London):** app explainer animations in English, Spanish and Portuguese. **Landell Mills:** 3D map animation on the Mekong river and its environment.',
					'**Also for** Awaj Foundation, the Dutch Embassy (film and painting festivals), HelpAge International and DAI (Lottie animations and an interactive Adobe Animate site).',
				),
			),
			array(
				'title'   => 'Video Editor & Animator',
				'org'     => 'Think School (ThinkBangla)',
				'place'   => 'Bangladesh & UK',
				'dates'   => '2015 – 2017',
				'bullets' => array(
					'Edited and animated 20+ educational YouTube videos on the science, history and culture of Bangladesh in Premiere Pro, After Effects, Cinema 4D and Audition; handled storyboarding and YouTube SEO, growing views and subscribers.',
				),
			),
			array(
				'title'   => 'Earlier career',
				'org'     => 'Old Bay Media · BDSPORTSNEWS.COM',
				'place'   => 'Dhaka',
				'dates'   => '2010 – 2015',
				'bullets' => array(
					'**Program Manager & Video Editor, Old Bay Media (2013 – 2015):** built and ran Bangla Boi, an online bookshop, end to end: catalogue database, publisher discount deals, customer service and delivery.',
					'**Program Manager & Sports Journalist, BDSPORTSNEWS.COM (2010 – 2012):** match reports from the Sher-e-Bangla stadium press box and interviews with national cricketers and footballers.',
				),
			),
		),

		'tools'      => array(
			array(
				'name' => 'LazyLord',
				'url'  => 'https://github.com/raisulsohan/LazyLord',
				'kind' => 'Adobe CEP panel + Figma plugin · TypeScript',
				'text' => 'Moves real vector artwork between Figma, Photoshop, Illustrator and After Effects; a free alternative to Overlord.',
			),
			array(
				'name' => 'LazyMotionToolkit',
				'url'  => 'https://github.com/raisulsohan/LazyMotionToolkit',
				'kind' => 'After Effects ScriptUI panel · ExtendScript',
				'text' => 'Nine motion tools in one dockable panel: smart precomp, auto text boxes, eased fades, animated arrows, anchor pad, grids, background preview renders.',
			),
			array(
				'name' => 'LazyKick',
				'url'  => 'https://github.com/raisulsohan/LazyKick',
				'kind' => 'CEP panel · After Effects & Premiere Pro',
				'text' => 'Clipboard images straight onto the timeline, time-coded project notes and auto-import of media folders.',
			),
			array(
				'name' => 'Lazy-Image',
				'url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
				'kind' => 'CEP extension · CDP browser automation',
				'text' => 'AI image generation inside After Effects and Premiere Pro with no API key and no browser switching.',
			),
			array(
				'name' => 'LazyScroll · LazySnap · LazyRuler',
				'url'  => 'https://raisulsohan.com/en/portfolio/',
				'kind' => 'Chrome & Edge extensions · Manifest V3',
				'text' => 'Per-site media volume control, article and match-commentary text extraction, Photoshop-style rulers and guides on any web page.',
			),
		),

		'films'      => array(
			array(
				'title' => 'Nomolos',
				'url'   => 'https://www.youtube.com/@nomolosfiles',
				'meta'  => 'Animated documentary channel',
				'text'  => 'Episode 01 **Prohibition** (9:40) and episode 02 **Cobra Effect** (8:49, 23 hand-illustrated scenes): screenplay, direction, vector illustration, multiplane 3D parallax animation and sound design, all solo. Browser editions of each film are drawn live in JavaScript with no video or image files.',
			),
			array(
				'title' => 'Consciousness',
				'url'   => 'https://raisulsohan.github.io/Consciousness-animation/',
				'meta'  => 'Animated science documentary for Bichitro Biggan, 4:08',
				'text'  => 'Concept, nine illustrated scenes, multiplane 3D parallax, atmospheric glow and volumetric lighting, original sound design from open archives.',
			),
			array(
				'title' => 'Showreel',
				'url'   => 'https://youtu.be/Hdq8STf5beQ',
				'meta'  => 'Motion design, 2D animation and SaaS product video',
				'text'  => '',
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
