<?php
/**
 * The CV.
 *
 * One page, /cv/, and the same in both editions: a résumé is written once,
 * in one language, so nothing in this file asks rs_is_en(). This file ships
 * the defaults; the CV page's native editor can override them in _rs_cv_data
 * page meta. The bundled PDF in assets/cv/ is the default download and can be
 * replaced from the Media Library on that page.
 *
 * page-cv.php renders rs_cv_data(); the page itself is created once per site
 * by rs_seed_cv_page(), so a new site starts with the same editable CV.
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
	$cv        = rs_cv_data();
	$media_id = isset( $cv['pdf_attachment_id'] ) ? absint( $cv['pdf_attachment_id'] ) : 0;

	if ( $media_id && 'attachment' === get_post_type( $media_id ) && 'application/pdf' === get_post_mime_type( $media_id ) ) {
		$url = wp_get_attachment_url( $media_id );

		if ( $url ) {
			$modified = get_post_modified_time( 'U', true, $media_id );
			return add_query_arg( 'ver', RS_VERSION . '-' . ( $modified ? $modified : '1' ), $url );
		}
	}

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
function rs_cv_data( $post_id = 0 ) {
	$defaults = array(
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
					'Created **Nomolos** ([youtube.com/@nomolosfiles](https://www.youtube.com/@nomolosfiles)); wrote, illustrated, animated and sound-designed **Prohibition** (9:40) and **Cobra Effect** (8:49, 23 scenes).',
					'Write and animate science stories for **Bichitro Biggan**; created **Consciousness** (4:08) with nine hand-drawn scenes, 3D parallax and volumetric lighting.',
					'Build and maintain seven free creative tools; built two bilingual PHP / JavaScript WordPress platforms: **bichitrobiggan.com** and **raisulsohan.com**.',
				),
			),
			array(
				'title'   => 'Project Manager',
				'org'     => 'Vidiosa',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Aug 2024 – Mar 2026',
				'bullets' => array(
					'Led animation and motion graphics for global SaaS and technology clients, from brief and storyboard through delivery.',
					'Managed cross-functional teams, budgets, schedules and client reviews; delivered every campaign on time.',
					'Introduced a storyboard-and-feedback workflow that cut turnaround by 15%; directed campaigns with 200K+ organic views.',
				),
			),
			array(
				'title'   => 'Senior Motion & Graphics Designer',
				'org'     => 'JMI Group',
				'place'   => 'Dhaka, Bangladesh',
				'dates'   => 'Jan 2022 – Jul 2024',
				'bullets' => array(
					'Maintained a large industrial group\'s visual identity across social, web, email, brochures, advertising and print.',
					'Produced campaign videos and presented 2D and 3D concepts to marketing and product teams.',
					'Coordinated photographers, illustrators and copywriters across concurrent projects while keeping brand standards consistent.',
				),
			),
			array(
				'title'   => 'Senior Motion Graphics Designer & 2D Animator',
				'org'     => 'Big Blue Communications',
				'place'   => 'Bangladesh & UK',
				'dates'   => 'Jan 2018 – Dec 2021',
				'bullets' => array(
					'Created explainers, infographic films, e-learning and social animation for development clients, from pitch and storyboard through final animation.',
					'Delivered UNICEF Oky stories, twelve USAID infographic animations, a GIZ climate film and a 3D Mekong map film for Landell Mills.',
					'Made multilingual app explainers for **ITF Seafarers\' Trust** and animation or interactive work for Awaj Foundation, the Dutch Embassy, HelpAge International and DAI.',
				),
			),
			array(
				'title'   => 'Video Editor & Animator',
				'org'     => 'Think School (ThinkBangla)',
				'place'   => 'Bangladesh & UK',
				'dates'   => '2015 – 2017',
				'bullets' => array(
					'Edited and animated 20+ educational videos on Bangladesh\'s science, history and culture.',
					'Also handled storyboarding and YouTube SEO for the series.',
				),
			),
			array(
				'title'   => 'Program Manager, Video Editor & Sports Journalist',
				'org'     => 'Old Bay Media · BDSPORTSNEWS.COM',
				'place'   => 'Dhaka',
				'dates'   => '2010 – 2015',
				'bullets' => array(
					'**Old Bay Media (2013 – 2015):** built and ran Bangla Boi, managing its catalogue, publisher deals, service and delivery.',
					'**BDSPORTSNEWS.COM (2010 – 2012):** reported from Sher-e-Bangla and interviewed national cricketers and footballers.',
				),
			),
		),

		'tools'      => array(
			array(
				'name' => 'LazyLord',
				'url'  => 'https://github.com/raisulsohan/LazyLord',
				'kind' => 'Adobe CEP panel + Figma plugin · TypeScript',
				'text' => 'Transfers vector artwork between Figma, Photoshop, Illustrator and After Effects.',
			),
			array(
				'name' => 'LazyMotionToolkit',
				'url'  => 'https://github.com/raisulsohan/LazyMotionToolkit',
				'kind' => 'After Effects ScriptUI panel · ExtendScript',
				'text' => 'Nine dockable tools for precomps, text boxes, fades, arrows, anchors and grids.',
			),
			array(
				'name' => 'LazyKick',
				'url'  => 'https://github.com/raisulsohan/LazyKick',
				'kind' => 'CEP panel · After Effects & Premiere Pro',
				'text' => 'Paste images, add time-coded notes and import media folders in After Effects or Premiere.',
			),
			array(
				'name' => 'Lazy-Image',
				'url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
				'kind' => 'CEP extension · CDP browser automation',
				'text' => 'Generate images inside After Effects or Premiere Pro without an API key.',
			),
			array(
				'name' => 'LazyScroll · LazySnap',
				'url'  => 'https://raisulsohan.com/en/portfolio/',
				'kind' => 'Chrome & Edge extensions · Manifest V3',
				'text' => 'Per-site media volume controls, article text and commentary text tools.',
			),
			array(
				'name' => 'LazyRuler',
				'url'  => 'https://raisulsohan.com/en/portfolio/',
				'kind' => 'Chrome & Edge extension · Manifest V3',
				'text' => 'Photoshop-style rulers and guides for arranging browser-page elements.',
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
		'linkedin_url' => 'https://www.linkedin.com/in/raisulsohan/',
		'work_url'     => home_url( '/portfolio/' ),
		'pdf_filename' => 'Raisul_Sohan_CV.pdf',
		'pdf_attachment_id' => 0,

		'labels'       => array(
			'hero_eyebrow'       => 'Curriculum vitae',
			'download_pdf'       => 'Download PDF',
			'print'              => 'Print',
			'email'              => 'Email',
			'whatsapp'           => 'WhatsApp',
			'linkedin'           => 'LinkedIn',
			'copy_email'         => 'Copy email address',
			'selected_work'      => 'Selected work',
			'showcase_title'     => 'Stories, motion & tools',
			'showcase_intro'     => 'A few projects that show how I combine visual craft, storytelling and creative technology.',
			'view_project'       => 'View project',
			'sheet_aria_label'   => 'Curriculum vitae',
			'profile'            => 'Profile',
			'highlights'         => 'Highlights',
			'experience'         => 'Experience',
			'open_source_tools'  => 'Open-source tools',
			'tools_note'         => 'all free',
			'skills'             => 'Skills',
			'education'          => 'Education',
			'languages'          => 'Languages',
			'updated_prefix'     => 'Last updated',
			'also_pdf'           => 'Also as a PDF',
			'see_work'           => 'See the work',
		),
	);

	$post_id = $post_id ? absint( $post_id ) : absint( get_queried_object_id() );
	$saved   = $post_id ? get_post_meta( $post_id, '_rs_cv_data', true ) : array();

	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	$data = $defaults;
	foreach ( $defaults as $key => $default ) {
		if ( 'labels' === $key && isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ) {
			$data[ $key ] = array_merge( $default, $saved[ $key ] );
		} elseif ( array_key_exists( $key, $saved ) ) {
			$data[ $key ] = $saved[ $key ];
		}
	}

	return $data;
}

/* =========================================================================
 * Native editor for the seeded CV page
 * ====================================================================== */

/**
 * Add the CV editor to the page that uses the CV template.
 *
 * @param WP_Post $post The page being edited.
 */
function rs_cv_add_editor_box( $post ) {
	if ( ! $post instanceof WP_Post || 'page-cv.php' !== get_page_template_slug( $post ) ) {
		return;
	}

	add_meta_box( 'rs-cv-content', 'CV content', 'rs_cv_render_editor_box', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes_page', 'rs_cv_add_editor_box' );

/**
 * Escape and print one field in the CV editor.
 *
 * @param string $name  Input name, without the rs_cv_data prefix.
 * @param string $label Field label.
 * @param mixed  $value Current value.
 * @param string $type  text, url, email or textarea.
 * @param bool   $wide  Whether the field spans the editor grid.
 */
function rs_cv_editor_field( $name, $label, $value, $type = 'text', $wide = false ) {
	$wide_class = $wide ? ' rs-cv-editor__field--wide' : '';
	$value      = is_scalar( $value ) ? (string) $value : '';

	echo '<label class="rs-cv-editor__field' . esc_attr( $wide_class ) . '"><span>' . esc_html( $label ) . '</span>';
	if ( 'textarea' === $type ) {
		echo '<textarea name="rs_cv_data[' . esc_attr( $name ) . ']' . '" rows="4">' . esc_textarea( $value ) . '</textarea>';
	} else {
		$input_type = in_array( $type, array( 'url', 'email' ), true ) ? $type : 'text';
		echo '<input type="' . esc_attr( $input_type ) . '" name="rs_cv_data[' . esc_attr( $name ) . ']' . '" value="' . esc_attr( $value ) . '">';
	}
	echo '</label>';
}

/**
 * Print one repeatable row, used by current rows and the JS template.
 *
 * @param string $base   Repeater meta key.
 * @param string $index  Row index or the JS placeholder.
 * @param string $title  Row title in the editor.
 * @param array  $fields Field descriptors.
 * @param array  $values Values for this row.
 */
function rs_cv_editor_repeater_item( $base, $index, $title, $fields, $values = array() ) {
	echo '<div class="rs-cv-editor__item" data-cv-item><div class="rs-cv-editor__item-head"><strong>' . esc_html( $title ) . '</strong><button type="button" class="button-link-delete" data-cv-remove>Remove</button></div><div class="rs-cv-editor__fields">';
	foreach ( $fields as $key => $field ) {
		$value = isset( $values[ $key ] ) ? $values[ $key ] : '';
		if ( isset( $field['lines'] ) && $field['lines'] && is_array( $value ) ) {
			$value = implode( "\n", $value );
		}
		rs_cv_editor_field( $base . '][' . $index . '][' . $key, $field['label'], $value, isset( $field['type'] ) ? $field['type'] : 'text', ! empty( $field['wide'] ) );
	}
	echo '</div></div>';
}

/**
 * Print an editable repeater with Add and Remove controls.
 *
 * @param string $base   Repeater meta key.
 * @param string $title  Visible editor heading.
 * @param array  $items  Current rows.
 * @param array  $fields Field descriptors.
 */
function rs_cv_editor_repeater( $base, $title, $items, $fields ) {
	$items = is_array( $items ) ? array_values( $items ) : array();
	echo '<div class="rs-cv-editor__repeater" data-cv-repeater data-next-index="' . esc_attr( count( $items ) ) . '"><div class="rs-cv-editor__repeater-head"><h4>' . esc_html( $title ) . '</h4><button type="button" class="button" data-cv-add>Add item</button></div><input type="hidden" name="rs_cv_data[' . esc_attr( $base ) . '][_present]" value="1"><div class="rs-cv-editor__items" data-cv-items>\n';
	foreach ( $items as $index => $item ) {
		rs_cv_editor_repeater_item( $base, (string) $index, $title . ' ' . ( $index + 1 ), $fields, $item );
	}
	echo '</div><template data-cv-template>';
	rs_cv_editor_repeater_item( $base, '__INDEX__', $title, $fields );
	echo '</template></div>';
}

/**
 * Print the CV page's native content editor.
 *
 * @param WP_Post $post The CV page.
 */
function rs_cv_render_editor_box( $post ) {
	$cv = rs_cv_data( $post->ID );
	wp_nonce_field( 'rs_cv_save_content', 'rs_cv_nonce' );
	echo '<div class="rs-cv-editor"><p class="description">Edit the content and labels shown on the CV page. Experience bullets and selected-work descriptions support **bold** and [link text](https://example.com). Use one line per role, skill or bullet. Changes take effect after you update this page.</p>';

	echo '<details class="rs-cv-editor__section" open><summary>Identity, contact and PDF</summary><div class="rs-cv-editor__fields">';
	rs_cv_editor_field( 'name', 'Name', $cv['name'] );
	rs_cv_editor_field( 'roles', 'Roles (one per line)', implode( "\n", $cv['roles'] ), 'textarea' );
	rs_cv_editor_field( 'location', 'Location', $cv['location'] );
	rs_cv_editor_field( 'email', 'Email address', $cv['email'], 'email' );
	rs_cv_editor_field( 'phone', 'Phone', $cv['phone'] );
	rs_cv_editor_field( 'whatsapp', 'WhatsApp URL', $cv['whatsapp'], 'url' );
	rs_cv_editor_field( 'linkedin_url', 'LinkedIn button URL', $cv['linkedin_url'], 'url' );
	rs_cv_editor_field( 'work_url', 'Portfolio URL', $cv['work_url'], 'url' );
	rs_cv_editor_field( 'updated', 'Last updated text', $cv['updated'] );
	rs_cv_editor_field( 'pdf_filename', 'PDF download filename', $cv['pdf_filename'] );
	rs_cv_editor_field( 'summary', 'Short summary', $cv['summary'], 'textarea', true );
	rs_cv_editor_field( 'profile', 'Profile', $cv['profile'], 'textarea', true );
	echo '</div><div class="rs-cv-editor__pdf"><input type="hidden" name="rs_cv_data[pdf_attachment_id]" value="' . esc_attr( absint( $cv['pdf_attachment_id'] ) ) . '" data-cv-pdf-id><span data-cv-pdf-status>';
	if ( ! empty( $cv['pdf_attachment_id'] ) && wp_get_attachment_url( absint( $cv['pdf_attachment_id'] ) ) ) {
		echo 'Selected PDF: ' . esc_html( get_the_title( absint( $cv['pdf_attachment_id'] ) ) );
	} else {
		echo 'Using the PDF bundled with the theme. Choose a PDF from the Media Library to replace it.';
	}
	echo '</span><button type="button" class="button" data-cv-pdf-select data-title="Choose a CV PDF" data-button="Use this PDF">Choose PDF</button><button type="button" class="button-link-delete" data-cv-pdf-remove>Use bundled PDF</button></div></details>';

	echo '<details class="rs-cv-editor__section"><summary>Page labels and buttons</summary><div class="rs-cv-editor__fields">';
	$label_fields = array(
		'hero_eyebrow' => 'Top label', 'download_pdf' => 'Download PDF button', 'print' => 'Print button',
		'email' => 'Email button', 'whatsapp' => 'WhatsApp button', 'linkedin' => 'LinkedIn button', 'copy_email' => 'Email copy screen-reader text',
		'selected_work' => 'Selected work label', 'showcase_title' => 'Selected work title',
		'showcase_intro' => 'Selected work introduction', 'view_project' => 'Project link label',
		'sheet_aria_label' => 'CV sheet screen-reader label', 'profile' => 'Profile heading',
		'highlights' => 'Highlights heading', 'experience' => 'Experience heading',
		'open_source_tools' => 'Tools heading', 'tools_note' => 'Tools note', 'skills' => 'Skills heading',
		'education' => 'Education heading', 'languages' => 'Languages heading',
		'updated_prefix' => 'Last updated prefix', 'also_pdf' => 'PDF link label', 'see_work' => 'Portfolio link label',
	);
	foreach ( $label_fields as $key => $label ) {
		rs_cv_editor_field( 'labels][' . $key, $label, $cv['labels'][ $key ] );
	}
	echo '</div></details>';

	echo '<details class="rs-cv-editor__section"><summary>Contact links and highlights</summary>';
	rs_cv_editor_repeater( 'links', 'Contact link', $cv['links'], array( 'label' => array( 'label' => 'Link text' ), 'url' => array( 'label' => 'URL', 'type' => 'url' ) ) );
	rs_cv_editor_repeater( 'stats', 'Statistic', $cv['stats'], array( 'value' => array( 'label' => 'Value' ), 'label' => array( 'label' => 'Caption' ) ) );
	rs_cv_editor_repeater( 'highlights', 'Highlight', $cv['highlights'], array( 'lead' => array( 'label' => 'Bold lead' ), 'text' => array( 'label' => 'Description', 'type' => 'textarea', 'wide' => true ) ) );
	echo '</details>';

	echo '<details class="rs-cv-editor__section"><summary>Experience</summary>';
	rs_cv_editor_repeater( 'experience', 'Position', $cv['experience'], array(
		'title' => array( 'label' => 'Role title' ), 'org' => array( 'label' => 'Organisation' ),
		'place' => array( 'label' => 'Location' ), 'dates' => array( 'label' => 'Dates' ),
		'bullets' => array( 'label' => 'Description bullets (one per line)', 'type' => 'textarea', 'wide' => true, 'lines' => true ),
	) );
	echo '</details>';

	echo '<details class="rs-cv-editor__section"><summary>Selected work and open-source tools</summary>';
	rs_cv_editor_repeater( 'films', 'Project', $cv['films'], array(
		'title' => array( 'label' => 'Project title' ), 'url' => array( 'label' => 'URL', 'type' => 'url' ),
		'meta' => array( 'label' => 'Category / details', 'wide' => true ), 'text' => array( 'label' => 'Description', 'type' => 'textarea', 'wide' => true ),
	) );
	rs_cv_editor_repeater( 'tools', 'Tool', $cv['tools'], array(
		'name' => array( 'label' => 'Tool name' ), 'url' => array( 'label' => 'URL', 'type' => 'url' ),
		'kind' => array( 'label' => 'Platform / technology', 'wide' => true ), 'text' => array( 'label' => 'Description', 'type' => 'textarea', 'wide' => true ),
	) );
	echo '</details>';

	echo '<details class="rs-cv-editor__section"><summary>Skills, education and languages</summary>';
	rs_cv_editor_repeater( 'skills', 'Skill group', $cv['skills'], array(
		'group' => array( 'label' => 'Group title' ), 'items' => array( 'label' => 'Skills (one per line)', 'type' => 'textarea', 'wide' => true, 'lines' => true ),
	) );
	rs_cv_editor_repeater( 'education', 'Education item', $cv['education'], array( 'lead' => array( 'label' => 'Qualification' ), 'text' => array( 'label' => 'Institution / details', 'type' => 'textarea', 'wide' => true ) ) );
	rs_cv_editor_field( 'languages', 'Languages', $cv['languages'], 'textarea', true );
	echo '</details></div>';
}

/**
 * Convert a textarea into clean, non-empty lines.
 *
 * @param mixed $value Raw value.
 * @return array
 */
function rs_cv_editor_sanitize_lines( $value ) {
	$value = sanitize_textarea_field( is_scalar( $value ) ? (string) $value : '' );
	$lines = preg_split( '/\r\n|\r|\n/', $value );
	$lines = array_map( 'trim', $lines );
	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * Sanitize a list submitted by the CV editor.
 *
 * @param mixed $rows   Submitted rows.
 * @param array $fields Allowed fields and sanitizers.
 * @return array
 */
function rs_cv_editor_sanitize_rows( $rows, $fields ) {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$clean = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$item = array();
		$has_value = false;
		foreach ( $fields as $key => $type ) {
			$value = isset( $row[ $key ] ) ? $row[ $key ] : '';
			if ( 'lines' === $type ) {
				$value = rs_cv_editor_sanitize_lines( $value );
				$has_value = $has_value || ! empty( $value );
			} else {
				$value = is_scalar( $value ) ? (string) $value : '';
				if ( 'url' === $type ) {
					$value = esc_url_raw( $value );
				} elseif ( 'textarea' === $type ) {
					$value = sanitize_textarea_field( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
				$has_value = $has_value || '' !== $value;
			}
			$item[ $key ] = $value;
		}
		if ( $has_value ) {
			$clean[] = $item;
		}
	}

	return $clean;
}

/**
 * Save the structured CV fields on the page, with the usual WordPress checks.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Page being saved.
 */
function rs_cv_save_editor_data( $post_id, $post ) {
	if ( ! $post instanceof WP_Post || 'page-cv.php' !== get_page_template_slug( $post_id ) || ! isset( $_POST['rs_cv_nonce'] ) || ! is_scalar( $_POST['rs_cv_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['rs_cv_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'rs_cv_save_content' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$input = isset( $_POST['rs_cv_data'] ) ? wp_unslash( $_POST['rs_cv_data'] ) : array();
	if ( ! is_array( $input ) ) {
		return;
	}

	$current = rs_cv_data( $post_id );
	$clean   = $current;
	$text_fields = array( 'name', 'location', 'phone', 'updated', 'pdf_filename' );
	foreach ( $text_fields as $key ) {
		if ( isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ) {
			$clean[ $key ] = sanitize_text_field( $input[ $key ] );
		}
	}
	foreach ( array( 'whatsapp', 'linkedin_url', 'work_url' ) as $key ) {
		if ( isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ) {
			$clean[ $key ] = esc_url_raw( $input[ $key ] );
		}
	}
	if ( isset( $input['email'] ) && is_scalar( $input['email'] ) ) {
		$clean['email'] = sanitize_email( $input['email'] );
	}
	foreach ( array( 'summary', 'profile', 'languages' ) as $key ) {
		if ( isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ) {
			$clean[ $key ] = sanitize_textarea_field( $input[ $key ] );
		}
	}
	if ( isset( $input['roles'] ) ) {
		$clean['roles'] = rs_cv_editor_sanitize_lines( $input['roles'] );
	}
	if ( isset( $input['labels'] ) && is_array( $input['labels'] ) ) {
		foreach ( $current['labels'] as $key => $default ) {
			if ( isset( $input['labels'][ $key ] ) && is_scalar( $input['labels'][ $key ] ) ) {
				$clean['labels'][ $key ] = sanitize_text_field( $input['labels'][ $key ] );
			}
		}
	}

	$row_fields = array(
		'links'      => array( 'label' => 'text', 'url' => 'url' ),
		'stats'      => array( 'value' => 'text', 'label' => 'text' ),
		'highlights' => array( 'lead' => 'text', 'text' => 'textarea' ),
		'experience' => array( 'title' => 'text', 'org' => 'text', 'place' => 'text', 'dates' => 'text', 'bullets' => 'lines' ),
		'tools'      => array( 'name' => 'text', 'url' => 'url', 'kind' => 'text', 'text' => 'textarea' ),
		'films'      => array( 'title' => 'text', 'url' => 'url', 'meta' => 'text', 'text' => 'textarea' ),
		'skills'     => array( 'group' => 'text', 'items' => 'lines' ),
		'education'  => array( 'lead' => 'text', 'text' => 'textarea' ),
	);
	foreach ( $row_fields as $key => $fields ) {
		if ( isset( $input[ $key ] ) ) {
			$clean[ $key ] = rs_cv_editor_sanitize_rows( $input[ $key ], $fields );
		}
	}
	if ( isset( $input['pdf_attachment_id'] ) && is_scalar( $input['pdf_attachment_id'] ) ) {
		$attachment_id = absint( $input['pdf_attachment_id'] );
		$clean['pdf_attachment_id'] = $attachment_id && 'attachment' === get_post_type( $attachment_id ) && 'application/pdf' === get_post_mime_type( $attachment_id ) ? $attachment_id : 0;
	}
	$clean['pdf_filename'] = sanitize_file_name( $clean['pdf_filename'] );
	if ( $clean['pdf_filename'] && '.pdf' !== strtolower( substr( $clean['pdf_filename'], -4 ) ) ) {
		$clean['pdf_filename'] .= '.pdf';
	}
	update_post_meta( $post_id, '_rs_cv_data', $clean );

	if ( function_exists( 'rs_purge_host_cache_soon' ) ) {
		rs_purge_host_cache_soon();
	}
}
add_action( 'save_post_page', 'rs_cv_save_editor_data', 10, 2 );

/**
 * Load the media picker and the small CV editor bundle only on the CV page.
 *
 * @param string $hook Current admin page hook.
 */
function rs_cv_editor_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( ! $screen || 'page' !== $screen->post_type || ! $post_id || 'page-cv.php' !== get_page_template_slug( $post_id ) ) {
		return;
	}

	$cv = rs_cv_data( $post_id );
	wp_enqueue_media();
	wp_enqueue_style( 'rs-cv-editor', RS_URI . '/assets/cv-editor.min.css', array(), RS_VERSION );
	wp_enqueue_script( 'rs-cv-editor', RS_URI . '/assets/cv-editor.min.js', array(), RS_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'rs_cv_editor_assets' );

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
