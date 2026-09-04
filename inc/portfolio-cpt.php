<?php
/**
 * Portfolio Custom Post Type & Admin Management System
 *
 * Allows managing portfolio projects (Web Development, Video & Animation,
 * Browser Extensions & Tools) directly from the WordPress Admin Dashboard.
 * Includes bilingual meta fields (Bengali & English), native media uploader,
 * custom accent color, and auto-seeding for existing authentic projects.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Register rs_portfolio Custom Post Type
 */
function rs_register_portfolio_cpt() {
	$labels = array(
		'name'               => 'Portfolio',
		'singular_name'      => 'Portfolio Project',
		'add_new'            => 'Add New Project',
		'add_new_item'       => 'Add New Portfolio Project',
		'edit_item'          => 'Edit Portfolio Project',
		'new_item'           => 'New Portfolio Project',
		'view_item'          => 'View Portfolio',
		'search_items'       => 'Search Projects',
		'not_found'          => 'No portfolio projects found.',
		'not_found_in_trash' => 'No portfolio projects found in Trash.',
		'all_items'          => 'All Projects',
		'menu_name'          => 'Portfolio',
	);

	register_post_type( 'rs_portfolio', array(
		'labels'              => $labels,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 22,
		'menu_icon'           => 'dashicons-portfolio',
		'supports'            => array( 'title', 'page-attributes' ),
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => false,
	) );
}
add_action( 'init', 'rs_register_portfolio_cpt' );

/**
 * 2. Enqueue Media Uploader on rs_portfolio edit screen
 */
function rs_portfolio_admin_assets( $hook ) {
	global $post_type;

	if ( 'rs_portfolio' !== $post_type ) {
		return;
	}

	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'rs_portfolio_admin_assets' );

/**
 * 3. Register Meta Boxes for Portfolio Project Details
 */
function rs_portfolio_meta_boxes() {
	add_meta_box(
		'rs_portfolio_details',
		'Project Details & Case Study (প্রজেক্ট ও কেস স্টাডি বিবরণ)',
		'rs_portfolio_meta_box_html',
		'rs_portfolio',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'rs_portfolio_meta_boxes' );

/**
 * 4. Render Meta Box Form HTML
 */
function rs_portfolio_meta_box_html( $post ) {
	wp_nonce_field( 'rs_portfolio_save_meta', 'rs_portfolio_meta_nonce' );

	// Retrieve existing meta values
	$category     = get_post_meta( $post->ID, '_rs_portfolio_category', true );
	if ( empty( $category ) ) {
		$category = 'web';
	}
	$accent       = get_post_meta( $post->ID, '_rs_portfolio_accent', true );
	if ( empty( $accent ) ) {
		$accent = '#0984e3';
	}
	$icon         = get_post_meta( $post->ID, '_rs_portfolio_icon', true );
	if ( empty( $icon ) ) {
		$icon = 'code';
	}
	$action_type  = get_post_meta( $post->ID, '_rs_portfolio_action_type', true );
	if ( empty( $action_type ) ) {
		$action_type = 'web';
	}
	$image        = get_post_meta( $post->ID, '_rs_portfolio_image', true );
	$image_fit    = get_post_meta( $post->ID, '_rs_portfolio_image_fit', true );
	if ( empty( $image_fit ) ) {
		$image_fit = 'cover';
	}
	$direct_url   = get_post_meta( $post->ID, '_rs_portfolio_direct_url', true );
	$github_url   = get_post_meta( $post->ID, '_rs_portfolio_github_url', true );
	$tags         = get_post_meta( $post->ID, '_rs_portfolio_tags', true );

	// Bengali fields
	$title_bn     = get_post_meta( $post->ID, '_rs_portfolio_title_bn', true );
	$type_bn      = get_post_meta( $post->ID, '_rs_portfolio_type_bn', true );
	$badge_bn     = get_post_meta( $post->ID, '_rs_portfolio_badge_bn', true );
	$action_bn    = get_post_meta( $post->ID, '_rs_portfolio_action_bn', true );
	$summary_bn   = get_post_meta( $post->ID, '_rs_portfolio_summary_bn', true );
	$role_bn      = get_post_meta( $post->ID, '_rs_portfolio_role_bn', true );
	$context_bn   = get_post_meta( $post->ID, '_rs_portfolio_context_bn', true );
	$challenge_bn = get_post_meta( $post->ID, '_rs_portfolio_challenge_bn', true );
	$solution_bn  = get_post_meta( $post->ID, '_rs_portfolio_solution_bn', true );
	$hl_bn        = get_post_meta( $post->ID, '_rs_portfolio_highlights_bn', true );
	$highlights_bn = is_array( $hl_bn ) ? implode( "\n", $hl_bn ) : $hl_bn;

	// English fields
	$title_en     = get_post_meta( $post->ID, '_rs_portfolio_title_en', true );
	$type_en      = get_post_meta( $post->ID, '_rs_portfolio_type_en', true );
	$badge_en     = get_post_meta( $post->ID, '_rs_portfolio_badge_en', true );
	$action_en    = get_post_meta( $post->ID, '_rs_portfolio_action_en', true );
	$summary_en   = get_post_meta( $post->ID, '_rs_portfolio_summary_en', true );
	$role_en      = get_post_meta( $post->ID, '_rs_portfolio_role_en', true );
	$context_en   = get_post_meta( $post->ID, '_rs_portfolio_context_en', true );
	$challenge_en = get_post_meta( $post->ID, '_rs_portfolio_challenge_en', true );
	$solution_en  = get_post_meta( $post->ID, '_rs_portfolio_solution_en', true );
	$hl_en        = get_post_meta( $post->ID, '_rs_portfolio_highlights_en', true );
	$highlights_en = is_array( $hl_en ) ? implode( "\n", $hl_en ) : $hl_en;
	?>

	<style>
		.rs-meta-tabs {
			display: flex;
			gap: 6px;
			border-bottom: 2px solid #ccd0d4;
			margin-bottom: 18px;
			padding-bottom: 0;
		}
		.rs-meta-tab-btn {
			background: #f0f0f1;
			border: 1px solid #ccd0d4;
			border-bottom: none;
			padding: 9px 18px;
			cursor: pointer;
			font-size: 13px;
			font-weight: 600;
			color: #50575e;
			border-radius: 4px 4px 0 0;
			margin-bottom: -2px;
			transition: all 0.15s ease;
		}
		.rs-meta-tab-btn.is-active {
			background: #fff;
			color: #2271b1;
			border-color: #ccd0d4;
			border-bottom: 2px solid #fff;
		}
		.rs-meta-tab-btn:hover:not(.is-active) {
			background: #f6f7f7;
			color: #1d2327;
		}
		.rs-meta-panel {
			display: none;
		}
		.rs-meta-panel.is-active {
			display: block;
		}
		.rs-meta-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 16px;
			margin-bottom: 14px;
		}
		.rs-meta-field {
			margin-bottom: 14px;
		}
		.rs-meta-field label {
			display: block;
			font-weight: 600;
			font-size: 13px;
			margin-bottom: 5px;
			color: #1d2327;
		}
		.rs-meta-field input[type="text"],
		.rs-meta-field input[type="url"],
		.rs-meta-field select,
		.rs-meta-field textarea {
			width: 100%;
			box-sizing: border-box;
			border: 1px solid #8c8f94;
			border-radius: 4px;
			padding: 6px 10px;
			font-size: 13px;
		}
		.rs-meta-field textarea {
			resize: vertical;
		}
		.rs-meta-field .description {
			font-size: 12px;
			color: #646970;
			margin-top: 4px;
			font-style: normal;
		}
		.rs-media-preview-wrap {
			display: flex;
			align-items: center;
			gap: 14px;
			margin-top: 8px;
		}
		.rs-media-thumb {
			width: 120px;
			height: 68px;
			border-radius: 4px;
			border: 1px solid #ccd0d4;
			object-fit: cover;
			background: #f0f0f1;
			display: block;
		}
		.rs-color-preview-wrap {
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.rs-color-picker {
			width: 44px;
			height: 36px;
			padding: 0;
			border: 1px solid #ccd0d4;
			border-radius: 4px;
			cursor: pointer;
		}
	</style>

	<div class="rs-meta-tabs" role="tablist">
		<button type="button" class="rs-meta-tab-btn is-active" data-tab="setup">⚙️ General & Media</button>
		<button type="button" class="rs-meta-tab-btn" data-tab="bengali">🇧🇩 বাংলা কনটেন্ট (Bengali)</button>
		<button type="button" class="rs-meta-tab-btn" data-tab="english">🇬🇧 English Content</button>
	</div>

	<!-- TAB 1: General & Media -->
	<div class="rs-meta-panel is-active" id="rs-tab-setup">
		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_category">Category (ক্যাটাগরি) *</label>
				<select name="rs_portfolio_category" id="rs_portfolio_category" required>
					<option value="web" <?php selected( $category, 'web' ); ?>>ওয়েব ডেভেলপমেন্ট (Web Development)</option>
					<option value="video" <?php selected( $category, 'video' ); ?>>ভিডিও এডিটিং ও মোশন (Video & Animation)</option>
					<option value="tools" <?php selected( $category, 'tools' ); ?>>এক্সটেনশন ও টুলস (Extensions & Tools)</option>
				</select>
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_action_type">Action / Button Type *</label>
				<select name="rs_portfolio_action_type" id="rs_portfolio_action_type">
					<option value="web" <?php selected( $action_type, 'web' ); ?>>ওয়েবসাইট / লাইভ সাইট (Web)</option>
					<option value="video" <?php selected( $action_type, 'video' ); ?>>ভিডিও লিংক / ইউটিউব (Video)</option>
					<option value="code" <?php selected( $action_type, 'code' ); ?>>সোর্স কোড / গিটহাব (Code / GitHub)</option>
				</select>
			</div>
		</div>

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_accent">Accent Color (অ্যাকসেন্ট কালার)</label>
				<div class="rs-color-preview-wrap">
					<input type="color" id="rs_portfolio_accent_picker" class="rs-color-picker" value="<?php echo esc_attr( $accent ); ?>" />
					<input type="text" name="rs_portfolio_accent" id="rs_portfolio_accent" value="<?php echo esc_attr( $accent ); ?>" placeholder="#0984e3" />
				</div>
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_icon">Card Icon (কার্ড আইকন)</label>
				<select name="rs_portfolio_icon" id="rs_portfolio_icon">
					<option value="code" <?php selected( $icon, 'code' ); ?>>Code & Web (code)</option>
					<option value="video" <?php selected( $icon, 'video' ); ?>>Video & Reel (video)</option>
					<option value="extension" <?php selected( $icon, 'extension' ); ?>>Browser Extension (extension)</option>
					<option value="terminal" <?php selected( $icon, 'terminal' ); ?>>Script & Terminal (terminal)</option>
					<option value="layout" <?php selected( $icon, 'layout' ); ?>>Design & Layout (layout)</option>
				</select>
			</div>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_image">Project Artwork / Preview Screenshot (প্রজেক্ট ইমেজ)</label>
			<div style="display: flex; gap: 8px;">
				<input type="text" name="rs_portfolio_image" id="rs_portfolio_image" value="<?php echo esc_attr( $image ); ?>" placeholder="https://..." />
				<button type="button" class="button button-secondary" id="rs_upload_image_btn">Choose / Upload</button>
				<button type="button" class="button button-link-delete" id="rs_remove_image_btn" style="<?php echo empty( $image ) ? 'display:none;' : ''; ?>">Remove</button>
			</div>
			<div class="rs-media-preview-wrap">
				<img id="rs_image_preview" class="rs-media-thumb" src="<?php echo esc_attr( $image ? $image : get_template_directory_uri() . '/screenshot.png' ); ?>" alt="Preview" style="<?php echo empty( $image ) ? 'display:none;' : ''; ?>" />
				<div class="description">Recommended: 16:9 ratio (1024x576px or 1280x720px) for videos & web, or crisp screenshot.</div>
			</div>
		</div>

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_image_fit">Image Fit Mode</label>
				<select name="rs_portfolio_image_fit" id="rs_portfolio_image_fit">
					<option value="cover" <?php selected( $image_fit, 'cover' ); ?>>Cover (পূর্ণ ফ্রেম জুড়ে সুন্দরভাবে ক্রপ)</option>
					<option value="contain" <?php selected( $image_fit, 'contain' ); ?>>Contain (পুরো ছবি অক্ষুণ্ণ রেখে ফিট)</option>
				</select>
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_tags">Tags & Technologies (কমা দিয়ে আলাদা করুন)</label>
				<input type="text" name="rs_portfolio_tags" id="rs_portfolio_tags" value="<?php echo esc_attr( $tags ); ?>" placeholder="After Effects, Illustrator, SaaS Explainer" />
			</div>
		</div>

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_direct_url">Direct Destination URL (মূল লিংক) *</label>
				<input type="url" name="rs_portfolio_direct_url" id="rs_portfolio_direct_url" value="<?php echo esc_attr( $direct_url ); ?>" placeholder="https://..." required />
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_github_url">GitHub Repository URL (ঐচ্ছিক)</label>
				<input type="url" name="rs_portfolio_github_url" id="rs_portfolio_github_url" value="<?php echo esc_attr( $github_url ); ?>" placeholder="https://github.com/..." />
			</div>
		</div>
	</div>

	<!-- TAB 2: Bengali Content -->
	<div class="rs-meta-panel" id="rs-tab-bengali">
		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_title_bn">প্রজেক্ট টাইটেল (বাংলা) *</label>
				<input type="text" name="rs_portfolio_title_bn" id="rs_portfolio_title_bn" value="<?php echo esc_attr( $title_bn ); ?>" placeholder="যেমন: বিচিত্র বিজ্ঞান — জিরো-প্লাগিন ডিজিটাল ম্যাগাজিন" />
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_type_bn">কাজের ধরন / সাবটাইটেল (বাংলা)</label>
				<input type="text" name="rs_portfolio_type_bn" id="rs_portfolio_type_bn" value="<?php echo esc_attr( $type_bn ); ?>" placeholder="যেমন: মোশন ডিজাইন ও ২ডি অ্যানিমেশন শোরিল" />
			</div>
		</div>

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_badge_bn">ব্যাজ টেক্সট (বাংলা)</label>
				<input type="text" name="rs_portfolio_badge_bn" id="rs_portfolio_badge_bn" value="<?php echo esc_attr( $badge_bn ); ?>" placeholder="যেমন: অফিসিয়াল শোরিল • শোকেস" />
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_action_bn">বাটন লেবেল (বাংলা)</label>
				<input type="text" name="rs_portfolio_action_bn" id="rs_portfolio_action_bn" value="<?php echo esc_attr( $action_bn ); ?>" placeholder="যেমন: ইউটিউবে শোরিলটি দেখুন" />
			</div>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_summary_bn">সংক্ষিপ্ত বিবরণ (কার্ডের সারাংশ)</label>
			<textarea name="rs_portfolio_summary_bn" id="rs_portfolio_summary_bn" rows="3"><?php echo esc_textarea( $summary_bn ); ?></textarea>
		</div>

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_role_bn">আপনার ভূমিকা (Role)</label>
				<input type="text" name="rs_portfolio_role_bn" id="rs_portfolio_role_bn" value="<?php echo esc_attr( $role_bn ); ?>" placeholder="যেমন: মোশন ডিজাইনার ও ২ডি অ্যানিমেটর" />
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_context_bn">প্রেক্ষিত / সময়কাল (Context)</label>
				<input type="text" name="rs_portfolio_context_bn" id="rs_portfolio_context_bn" value="<?php echo esc_attr( $context_bn ); ?>" placeholder="যেমন: অফিসিয়াল SaaS ওভারভিউ ভিডিও • ৩ মিনিট • ১৬:৯" />
			</div>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_challenge_bn">মূল চ্যালেঞ্জ (The Challenge & Context)</label>
			<textarea name="rs_portfolio_challenge_bn" id="rs_portfolio_challenge_bn" rows="5"><?php echo esc_textarea( $challenge_bn ); ?></textarea>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_solution_bn">সমাধান ও সৃজনশীল প্রক্রিয়া (The Solution & Creative Process)</label>
			<textarea name="rs_portfolio_solution_bn" id="rs_portfolio_solution_bn" rows="6"><?php echo esc_textarea( $solution_bn ); ?></textarea>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_highlights_bn">প্রধান হাইলাইটস (Key Highlights — প্রতি লাইনে একটি করে পয়েন্ট লিখুন)</label>
			<textarea name="rs_portfolio_highlights_bn" id="rs_portfolio_highlights_bn" rows="5"><?php echo esc_textarea( $highlights_bn ); ?></textarea>
		</div>
	</div>

	<!-- TAB 3: English Content -->
	<div class="rs-meta-panel" id="rs-tab-english">
		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_title_en">Project Title (English) *</label>
				<input type="text" name="rs_portfolio_title_en" id="rs_portfolio_title_en" value="<?php echo esc_attr( $title_en ); ?>" placeholder="e.g. Motion Design & 2D Animation Showreel" />
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_type_en">Work Type / Subtitle (English)</label>
				<input type="text" name="rs_portfolio_type_en" id="rs_portfolio_type_en" value="<?php echo esc_attr( $type_en ); ?>" placeholder="e.g. SaaS Product Explainer & Motion Animation" />
			</div>
		</div>

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_badge_en">Badge Text (English)</label>
				<input type="text" name="rs_portfolio_badge_en" id="rs_portfolio_badge_en" value="<?php echo esc_attr( $badge_en ); ?>" placeholder="e.g. Official Showreel • Showcase" />
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_action_en">Button Label (English)</label>
				<input type="text" name="rs_portfolio_action_en" id="rs_portfolio_action_en" value="<?php echo esc_attr( $action_en ); ?>" placeholder="e.g. Watch Showreel on YouTube" />
			</div>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_summary_en">Short Summary (Card Preview)</label>
			<textarea name="rs_portfolio_summary_en" id="rs_portfolio_summary_en" rows="3"><?php echo esc_textarea( $summary_en ); ?></textarea>
		</div>

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_role_en">Your Role (English)</label>
				<input type="text" name="rs_portfolio_role_en" id="rs_portfolio_role_en" value="<?php echo esc_attr( $role_en ); ?>" placeholder="e.g. Solo Extension Architect & Developer" />
			</div>

			<div class="rs-meta-field">
				<label for="rs_portfolio_context_en">Context / Duration (English)</label>
				<input type="text" name="rs_portfolio_context_en" id="rs_portfolio_context_en" value="<?php echo esc_attr( $context_en ); ?>" placeholder="e.g. Official SaaS Overview Video • 3 Mins • 16:9" />
			</div>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_challenge_en">The Challenge & Context (English)</label>
			<textarea name="rs_portfolio_challenge_en" id="rs_portfolio_challenge_en" rows="5"><?php echo esc_textarea( $challenge_en ); ?></textarea>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_solution_en">The Solution & Creative Process (English)</label>
			<textarea name="rs_portfolio_solution_en" id="rs_portfolio_solution_en" rows="6"><?php echo esc_textarea( $solution_en ); ?></textarea>
		</div>

		<div class="rs-meta-field">
			<label for="rs_portfolio_highlights_en">Key Highlights & Results (One bullet point per line)</label>
			<textarea name="rs_portfolio_highlights_en" id="rs_portfolio_highlights_en" rows="5"><?php echo esc_textarea( $highlights_en ); ?></textarea>
		</div>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Tab Switcher
		var tabs = document.querySelectorAll('.rs-meta-tab-btn');
		tabs.forEach(function(btn) {
			btn.addEventListener('click', function() {
				tabs.forEach(function(t) { t.classList.remove('is-active'); });
				document.querySelectorAll('.rs-meta-panel').forEach(function(p) { p.classList.remove('is-active'); });
				btn.classList.add('is-active');
				var target = document.getElementById('rs-tab-' + btn.getAttribute('data-tab'));
				if (target) { target.classList.add('is-active'); }
			});
		});

		// Color Picker sync
		var picker = document.getElementById('rs_portfolio_accent_picker');
		var textInput = document.getElementById('rs_portfolio_accent');
		if (picker && textInput) {
			picker.addEventListener('input', function() { textInput.value = picker.value; });
			textInput.addEventListener('input', function() { picker.value = textInput.value; });
		}

		// Media Uploader
		var uploadBtn = document.getElementById('rs_upload_image_btn');
		var removeBtn = document.getElementById('rs_remove_image_btn');
		var imageInput = document.getElementById('rs_portfolio_image');
		var imagePreview = document.getElementById('rs_image_preview');

		if (uploadBtn && imageInput) {
			var frame;
			uploadBtn.addEventListener('click', function(e) {
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: 'Select or Upload Portfolio Project Artwork',
					button: { text: 'Use this Image' },
					multiple: false
				});
				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					imageInput.value = attachment.url;
					if (imagePreview) {
						imagePreview.src = attachment.url;
						imagePreview.style.display = 'block';
					}
					if (removeBtn) { removeBtn.style.display = 'inline-block'; }
				});
				frame.open();
			});
		}

		if (removeBtn && imageInput && imagePreview) {
			removeBtn.addEventListener('click', function(e) {
				e.preventDefault();
				imageInput.value = '';
				imagePreview.src = '';
				imagePreview.style.display = 'none';
				removeBtn.style.display = 'none';
			});
		}
	});
	</script>
	<?php
}

/**
 * 5. Save Meta Box Data
 */
function rs_save_portfolio_meta( $post_id ) {
	if ( ! isset( $_POST['rs_portfolio_meta_nonce'] ) || ! wp_verify_nonce( $_POST['rs_portfolio_meta_nonce'], 'rs_portfolio_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields_text = array(
		'rs_portfolio_category'    => '_rs_portfolio_category',
		'rs_portfolio_accent'      => '_rs_portfolio_accent',
		'rs_portfolio_icon'        => '_rs_portfolio_icon',
		'rs_portfolio_action_type' => '_rs_portfolio_action_type',
		'rs_portfolio_image_fit'   => '_rs_portfolio_image_fit',
		'rs_portfolio_tags'        => '_rs_portfolio_tags',
		'rs_portfolio_title_bn'    => '_rs_portfolio_title_bn',
		'rs_portfolio_type_bn'     => '_rs_portfolio_type_bn',
		'rs_portfolio_badge_bn'    => '_rs_portfolio_badge_bn',
		'rs_portfolio_action_bn'   => '_rs_portfolio_action_bn',
		'rs_portfolio_role_bn'     => '_rs_portfolio_role_bn',
		'rs_portfolio_context_bn'  => '_rs_portfolio_context_bn',
		'rs_portfolio_title_en'    => '_rs_portfolio_title_en',
		'rs_portfolio_type_en'     => '_rs_portfolio_type_en',
		'rs_portfolio_badge_en'    => '_rs_portfolio_badge_en',
		'rs_portfolio_action_en'   => '_rs_portfolio_action_en',
		'rs_portfolio_role_en'     => '_rs_portfolio_role_en',
		'rs_portfolio_context_en'  => '_rs_portfolio_context_en',
	);

	foreach ( $fields_text as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) );
		}
	}

	$fields_url = array(
		'rs_portfolio_image'      => '_rs_portfolio_image',
		'rs_portfolio_direct_url' => '_rs_portfolio_direct_url',
		'rs_portfolio_github_url' => '_rs_portfolio_github_url',
	);

	foreach ( $fields_url as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $meta_key, esc_url_raw( trim( wp_unslash( $_POST[ $post_key ] ) ) ) );
		}
	}

	$fields_textarea = array(
		'rs_portfolio_summary_bn'   => '_rs_portfolio_summary_bn',
		'rs_portfolio_challenge_bn' => '_rs_portfolio_challenge_bn',
		'rs_portfolio_solution_bn'  => '_rs_portfolio_solution_bn',
		'rs_portfolio_summary_en'   => '_rs_portfolio_summary_en',
		'rs_portfolio_challenge_en' => '_rs_portfolio_challenge_en',
		'rs_portfolio_solution_en'  => '_rs_portfolio_solution_en',
	);

	foreach ( $fields_textarea as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_textarea_field( wp_unslash( $_POST[ $post_key ] ) ) );
		}
	}

	// Highlights (split newline into array)
	if ( isset( $_POST['rs_portfolio_highlights_bn'] ) ) {
		$lines = explode( "\n", wp_unslash( $_POST['rs_portfolio_highlights_bn'] ) );
		$clean = array();
		foreach ( $lines as $l ) {
			$trimmed = trim( $l );
			if ( ! empty( $trimmed ) ) {
				$clean[] = sanitize_text_field( $trimmed );
			}
		}
		update_post_meta( $post_id, '_rs_portfolio_highlights_bn', $clean );
	}

	if ( isset( $_POST['rs_portfolio_highlights_en'] ) ) {
		$lines = explode( "\n", wp_unslash( $_POST['rs_portfolio_highlights_en'] ) );
		$clean = array();
		foreach ( $lines as $l ) {
			$trimmed = trim( $l );
			if ( ! empty( $trimmed ) ) {
				$clean[] = sanitize_text_field( $trimmed );
			}
		}
		update_post_meta( $post_id, '_rs_portfolio_highlights_en', $clean );
	}
}
add_action( 'save_post_rs_portfolio', 'rs_save_portfolio_meta' );

/**
 * 6. Admin Table Columns for rs_portfolio
 */
function rs_portfolio_admin_columns( $columns ) {
	$new_columns = array(
		'cb'          => $columns['cb'],
		'rs_thumb'    => 'Preview',
		'title'       => 'Project Name',
		'rs_category' => 'Category',
		'rs_type'     => 'Type',
		'menu_order'  => 'Order',
		'date'        => 'Date',
	);
	return $new_columns;
}
add_filter( 'manage_rs_portfolio_posts_columns', 'rs_portfolio_admin_columns' );

function rs_portfolio_admin_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'rs_thumb':
			$img = get_post_meta( $post_id, '_rs_portfolio_image', true );
			if ( $img ) {
				echo '<img src="' . esc_url( $img ) . '" style="width: 60px; height: 34px; object-fit: cover; border-radius: 3px; border: 1px solid #ccd0d4;" alt="" />';
			} else {
				echo '<span style="display:inline-block; width: 60px; height: 34px; background: #f0f0f1; border-radius: 3px; line-height: 34px; text-align: center; color: #8c8f94; font-size: 11px;">No Img</span>';
			}
			break;

		case 'rs_category':
			$cat = get_post_meta( $post_id, '_rs_portfolio_category', true );
			$badges = array(
				'web'   => '<span style="display:inline-block; padding: 2px 7px; background: #e3f2fd; color: #0d47a1; border-radius: 3px; font-size: 11px; font-weight:600;">Web</span>',
				'video' => '<span style="display:inline-block; padding: 2px 7px; background: #f3e5f5; color: #4a148c; border-radius: 3px; font-size: 11px; font-weight:600;">Video</span>',
				'tools' => '<span style="display:inline-block; padding: 2px 7px; background: #e8f5e9; color: #1b5e20; border-radius: 3px; font-size: 11px; font-weight:600;">Tools</span>',
			);
			echo isset( $badges[ $cat ] ) ? $badges[ $cat ] : esc_html( ucfirst( $cat ) );
			break;

		case 'rs_type':
			$type = get_post_meta( $post_id, '_rs_portfolio_type_en', true );
			if ( empty( $type ) ) {
				$type = get_post_meta( $post_id, '_rs_portfolio_type_bn', true );
			}
			echo esc_html( $type );
			break;

		case 'menu_order':
			$post = get_post( $post_id );
			echo esc_html( $post->menu_order );
			break;
	}
}
add_action( 'manage_rs_portfolio_posts_custom_column', 'rs_portfolio_admin_custom_column', 10, 2 );

function rs_portfolio_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
}
add_filter( 'manage_edit-rs_portfolio_sortable_columns', 'rs_portfolio_sortable_columns' );

/**
 * 7. Query Portfolio Projects for Frontend Display
 *
 * Checks database for rs_portfolio posts. If multisite, switches to the main blog.
 * If database posts are found, converts them to the exact array expected by page-portfolio.php.
 * If none found, gracefully falls back to the static 8 authentic projects.
 *
 * @return array
 */
function rs_get_portfolio_projects() {
	$switched = false;
	if ( is_multisite() && ! is_main_site() ) {
		switch_to_blog( get_main_site_id() );
		$switched = true;
	}

	$posts = get_posts( array(
		'post_type'      => 'rs_portfolio',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );

	$projects = array();

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $p ) {
			$tags_raw = get_post_meta( $p->ID, '_rs_portfolio_tags', true );
			$tags = array();
			if ( ! empty( $tags_raw ) ) {
				$tags = array_map( 'trim', explode( ',', $tags_raw ) );
			}

			$hl_bn = get_post_meta( $p->ID, '_rs_portfolio_highlights_bn', true );
			$hl_en = get_post_meta( $p->ID, '_rs_portfolio_highlights_en', true );

			$title_bn = get_post_meta( $p->ID, '_rs_portfolio_title_bn', true );
			if ( empty( $title_bn ) ) {
				$title_bn = $p->post_title;
			}
			$title_en = get_post_meta( $p->ID, '_rs_portfolio_title_en', true );
			if ( empty( $title_en ) ) {
				$title_en = $p->post_title;
			}

			$projects[] = array(
				'id'           => $p->post_name,
				'category'     => get_post_meta( $p->ID, '_rs_portfolio_category', true ) ?: 'web',
				'type_bn'      => get_post_meta( $p->ID, '_rs_portfolio_type_bn', true ) ?: '',
				'type_en'      => get_post_meta( $p->ID, '_rs_portfolio_type_en', true ) ?: '',
				'badge_bn'     => get_post_meta( $p->ID, '_rs_portfolio_badge_bn', true ) ?: '',
				'badge_en'     => get_post_meta( $p->ID, '_rs_portfolio_badge_en', true ) ?: '',
				'title_bn'     => $title_bn,
				'title_en'     => $title_en,
				'summary_bn'   => get_post_meta( $p->ID, '_rs_portfolio_summary_bn', true ) ?: '',
				'summary_en'   => get_post_meta( $p->ID, '_rs_portfolio_summary_en', true ) ?: '',
				'role_bn'      => get_post_meta( $p->ID, '_rs_portfolio_role_bn', true ) ?: '',
				'role_en'      => get_post_meta( $p->ID, '_rs_portfolio_role_en', true ) ?: '',
				'context_bn'   => get_post_meta( $p->ID, '_rs_portfolio_context_bn', true ) ?: '',
				'context_en'   => get_post_meta( $p->ID, '_rs_portfolio_context_en', true ) ?: '',
				'challenge_bn' => get_post_meta( $p->ID, '_rs_portfolio_challenge_bn', true ) ?: '',
				'challenge_en' => get_post_meta( $p->ID, '_rs_portfolio_challenge_en', true ) ?: '',
				'solution_bn'  => get_post_meta( $p->ID, '_rs_portfolio_solution_bn', true ) ?: '',
				'solution_en'  => get_post_meta( $p->ID, '_rs_portfolio_solution_en', true ) ?: '',
				'highlights_bn'=> is_array( $hl_bn ) ? $hl_bn : array(),
				'highlights_en'=> is_array( $hl_en ) ? $hl_en : array(),
				'tags'         => $tags,
				'accent'       => get_post_meta( $p->ID, '_rs_portfolio_accent', true ) ?: '#0984e3',
				'icon'         => get_post_meta( $p->ID, '_rs_portfolio_icon', true ) ?: 'code',
				'image'        => get_post_meta( $p->ID, '_rs_portfolio_image', true ) ?: '',
				'image_fit'    => get_post_meta( $p->ID, '_rs_portfolio_image_fit', true ) ?: 'cover',
				'action_type'  => get_post_meta( $p->ID, '_rs_portfolio_action_type', true ) ?: 'web',
				'action_bn'    => get_post_meta( $p->ID, '_rs_portfolio_action_bn', true ) ?: 'বিস্তারিত দেখুন',
				'action_en'    => get_post_meta( $p->ID, '_rs_portfolio_action_en', true ) ?: 'View Details',
				'direct_url'   => get_post_meta( $p->ID, '_rs_portfolio_direct_url', true ) ?: home_url( '/' ),
				'github_url'   => get_post_meta( $p->ID, '_rs_portfolio_github_url', true ) ?: '',
			);
		}
	}

	if ( $switched ) {
		restore_current_blog();
	}

	// Fallback to the 8 authentic hardcoded projects if none seeded or found yet
	if ( empty( $projects ) && ! get_option( 'rs_portfolio_seeded_v1' ) ) {
		$projects = rs_get_default_portfolio_projects();
	}

	return $projects;
}

/**
 * 8. Default Authentic Projects (Initial Seed & Fallback Data)
 */
function rs_get_default_portfolio_projects() {
	return array(
		array(
			'id'          => 'bichitro-biggan',
			'category'    => 'web',
			'type_bn'     => 'ম্যাগাজিন ও ডিজিটাল পাবলিকেশন থিম',
			'type_en'     => 'Digital Magazine Platform',
			'badge_bn'    => 'ফ্ল্যাগশিপ প্রজেক্ট',
			'badge_en'    => 'Flagship Project',
			'title_bn'    => 'বিচিত্র বিজ্ঞান — জিরো-প্লাগিন ডিজিটাল সায়েন্স ম্যাগাজিন',
			'title_en'    => 'Bichitro Biggan — Zero-Plugin Science Magazine Platform',
			'summary_bn'  => 'বাংলা বিজ্ঞান ম্যাগাজিনের জন্য স্ক্র্যাচ থেকে তৈরি অত্যাধুনিক, সুপারফাস্ট এবং সম্পূর্ণ জিরো-প্লাগিন ক্লাসিক ওয়ার্ডপ্রেস থিম। এতে রয়েছে ৮-কার্ড হিরো মোজাইক, সিনেমেটিক ভিডিও পপ-আপ, লাইভ সার্চ ও নেটিভ এসইও ইঞ্জিন।',
			'summary_en'  => 'A modern, ultra-fast, zero-plugin classic WordPress theme engineered from scratch for a Bengali science magazine. Features an 8-card mosaic hero, native AJAX reading modal, built-in SEO engine, and dynamic video popups.',
			'role_bn'     => 'একক ডিজাইনার ও ফুলস্ট্যাক থিম ডেভেলপার',
			'role_en'     => 'Lead Designer & Full-Stack Theme Developer',
			'context_bn'  => 'বিজ্ঞান ম্যাগাজিন ও ডিজিটাল প্রকাশনা • ভার্সন ৭.৩',
			'context_en'  => 'Science Magazine & Digital Publication • v7.3',
			'challenge_bn'=> "অনলাইন বিজ্ঞান ও প্রযুক্তি ম্যাগাজিনগুলোতে সাধারণত হিরো গ্রিড, এজেক্স সার্চ, ভিডিও পপ-আপ, এসইও মেটা এবং বুকমার্কের মতো ফিচারগুলোর জন্য অন্তত ২০-৩০টি প্লাগিন ব্যবহার করতে হয়। এতে প্রতিটি পেজে বিপুল পরিমাণ অপ্রয়োজনীয় CSS/JS স্ক্রিপ্ট জমে সাইট ভারী হয়ে যায়, ডাটাবেস কোয়েরি বাড়ে এবং মোবাইল ইন্টারনেটে পাঠকদের জন্য সাইট লোড হতে প্রচুর সময় লাগে।\n\nআমাদের মূল চ্যালেঞ্জ ছিল: কোনো রেডিমেড পেজবিল্ডার (Elementor/Divi), কোনো থার্ড-পার্টি এসইও প্লাগিন (Yoast/RankMath) এবং এমনকি কোনো jQuery লাইব্রেরি ছাড়াই শুধুমাত্র ওয়ার্ডপ্রেসের কোর পিএইচপি এপিআই এবং আধুনিক ভ্যানিলা ES6+ জাভাস্ক্রিপ্ট ব্যবহার করে একটি অত্যন্ত আকর্ষণীয়, ফ্লুইড ও জটিল প্রকাশনা সাইট তৈরি করা।",
			'challenge_en'=> "Digital magazines traditionally rely on 20 to 30 external plugins to support features like hero mosaics, live search, video popups, SEO meta, and reading bookmarks. This introduces severe database overhead, render-blocking scripts, and sluggish performance on mobile connections.\n\nOur architectural goal was to eliminate third-party dependencies entirely: building a modern, feature-dense science publication without page builders (Elementor/Divi), without SEO plugins (Yoast/RankMath), and without jQuery—relying strictly on native WordPress PHP APIs and clean Vanilla ES6+ JavaScript.",
			'solution_bn' => "১. ফিগমা স্টাইল ৮-কার্ড হিরো মোজাইক: জটিল পেজবিল্ডার বাদ দিয়ে পিওর আধুনিক CSS Grid ও Flexbox আর্কিটেকচারে একটি রেসপনসিভ ৮-কার্ড মোজাইক তৈরি করা হয়েছে, যা বড় ডেস্কটপ থেকে শুরু করে মোবাইলেও স্বয়ংক্রিয়ভাবে খাপ খেয়ে যায়।\n\n২. নেটিভ এজেক্স রিডিং মডাল: পাঠক কোনো লেখায় ক্লিক করলে পুরো পেজ রিলোড না হয়ে তাৎক্ষণিকভাবে ব্যাকগ্রাউন্ডে কনটেন্ট ফেচ করে একটি সিনেমাটিক রিডিং মডাল ওপেন হয়, যাতে পড়ার অভিজ্ঞতা কোনো বাধা ছাড়াই অব্যাহত থাকে।\n\n৩. ডায়নামিক রেশিও সিনেমেটিক ভিডিও প্লেয়ার: ইউটিউব ভিডিও দেখানোর জন্য ৪টি ভিন্ন অ্যাসপেক্ট রেশিও (১৬:৯, ৯:১৬, ৪:৫, ১:১) সাপোর্ট সহ একটি লাইটওয়েট নেটিভ পপ-আপ প্লেয়ার তৈরি করা হয়েছে।\n\n৪. বিল্ট-ইন এসইও ইঞ্জিন ও লাইভ গুগল প্রিভিউ: ক্লাসিক এডিটরে একটি কাস্টম মেটাবক্স ডিজাইন করা হয়েছে যা গুগল সার্চের মতো রিয়েল-টাইম ডেস্কটপ ও মোবাইল প্রিভিউ দেখায়, মেটা ডেসক্রিপশন তৈরি করে এবং স্বয়ংক্রিয়ভাবে Article ও Organization-এর জন্য বৈধ JSON-LD Rich Snippet স্কিমা জেনারেট করে।\n\n৫. লোকালস্টোরেজ বুকমার্ক ড্রয়ার: সার্ভারে কোনো বাড়তি লোড না দিয়ে ব্রাউজারের নিজস্ব লোকালস্টোরেজে 'পরে পড়ার তালিকা' সংরক্ষণের জন্য স্লাইড-আউট ড্রয়ার ইন্টারফেস তৈরি করা হয়েছে।\n\n৬. গিটহাব সেলফ-আপডেটার ও লাইসেন্স ম্যানেজার: থিমের ভবিষ্যৎ আপডেটগুলো যেন সরাসরি ওয়ার্ডপ্রেস ড্যাশবোর্ডে ওয়ান-ক্লিকে পাওয়া যায়, সেজন্য গিটহাব কমিট এসএইচএ (Commit SHA) ভ্যালিডেশন ভিত্তিক নিজস্ব আপডেটার এবং ডোমেইন লাইসেন্সিং সিস্টেম যুক্ত করা হয়েছে।",
			'solution_en' => "1. Figma-Grade 8-Card Hero Mosaic: Engineered an adaptive 8-post visual grid using pure CSS Grid and Flexbox, eliminating bulky page builders while maintaining responsive fluid scaling across all viewports.\n\n2. Native AJAX Reading Modal: Clicking any article fetches content via background AJAX and presents a distraction-free cinematic reading modal with zero page reloads.\n\n3. Dynamic Aspect-Ratio Video Engine: Custom lightweight popup player supporting 4 dynamic YouTube aspect ratios (16:9, 9:16, 4:5, 1:1) with responsive sizing.\n\n4. Built-in SEO Suite with Live Google Preview: Custom classic editor metabox rendering real-time mobile/desktop Google snippet previews, automated meta generation, and valid JSON-LD Article & Organization structured data.\n\n5. LocalStorage Reading Bookmarks Drawer: Zero server database overhead; articles are saved directly to the reader's device storage with a sleek slide-out drawer interface.\n\n6. Native GitHub Auto-Updater & License Manager: Delivers one-click updates directly inside the WordPress dashboard pinned to commit SHAs, integrated with a domain licensing validation screen.",
			'highlights_bn'=> array(
				'০টি এক্সটার্নাল প্লাগিন — এসইও, লাইভ সার্চ, বুকমার্ক, মডাল ও কাস্টমাইজার সম্পূর্ণ নেটিভ কোডে নির্মিত',
				'১০০% ভ্যানিলা ES6+ জাভাস্ক্রিপ্ট (কোনো jQuery নেই) ও আধুনিক CSS ভ্যারিয়েবল আর্কিটেকচার',
				'ফিগমা ও ম্যাগাজিন লেআউট: ৮-কার্ড হিরো মোজাইক এবং রেসপনসিভ মাল্টি-কলাম ক্যাটাগরি গ্রিড',
				'ইউটিউবের জন্য সিনেমেটিক ভিডিও পপ-আপ (১৬:৯, ৯:১৬, ৪:৫, ১:১ অ্যাসপেক্ট রেশিও সাপোর্ট)',
				'ওয়ার্ডপ্রেস ক্লাসিক এডিটরে লাইভ গুগল স্নিপেট প্রিভিউ সহ নিজস্ব এসইও মেটাবক্স ও JSON-LD স্কিমা',
				'নেটিভ গিটহাব অটো-আপডেটার — ড্যাশবোর্ড থেকেই এক ক্লিকে থিম আপডেট পাওয়ার সুবিধা',
				'কোর ওয়েব ভাইটালস ও WCAG অপ্টিমাইজড — মোবাইল ও ডেস্কটপ উভয়েই সাব-সেকেন্ড পেজ লোড ও ৯৫+ পারফরম্যান্স স্কোর'
			),
			'highlights_en'=> array(
				'Zero-Plugin Architecture: Native built-in SEO, live search, bookmarks, and modals without third-party plugins',
				'Pure Vanilla ES6+ JavaScript (zero jQuery) with modular CSS custom property architecture',
				'Figma Mac & Magazine layout: 8-card hero mosaic and responsive multi-column category grids',
				'Cinematic video popups supporting dynamic aspect ratios (16:9, 9:16, 4:5, 1:1) from YouTube',
				'In-editor Google search snippet preview, Open Graph cards, and native JSON-LD schema generator',
				'Native GitHub Auto-Updater delivering instant dashboard updates via commit SHA verification',
				'Core Web Vitals & WCAG optimized: sub-second load times and 95+ PageSpeed scores on mobile & desktop'
			),
			'tags'        => array( 'WordPress', 'PHP 7.4+', 'Vanilla JS (ES6+)', 'CSS3 Grid', 'Built-in SEO', 'Auto-Updater' ),
			'accent'      => '#00b894',
			'icon'        => 'code',
			'image'       => 'https://bichitrobiggan.com/wp-content/uploads/2026/08/logo-wide.png',
			'image_fit'   => 'contain',
			'action_type' => 'web',
			'action_bn'   => 'লাইভ সাইট দেখুন',
			'action_en'   => 'Visit Live Website',
			'direct_url'  => 'https://bichitrobiggan.com',
			'github_url'  => 'https://github.com/raisulsohan/BichitroBiggan',
			'order'       => 10,
		),
		array(
			'id'          => 'theme-dev',
			'category'    => 'web',
			'type_bn'     => 'ব্যক্তিগত ব্লগ ও সাহিত্য থিম',
			'type_en'     => 'Personal Bespoke Theme',
			'badge_bn'    => 'ওপেন সোর্স থিম',
			'badge_en'    => 'Open Source Theme',
			'title_bn'    => 'জিরো-প্লাগিন বেস্পোক ওয়ার্ডপ্রেস থিম আর্কিটেকচার',
			'title_en'    => 'Zero-Plugin Bespoke WordPress Theme Architecture',
			'summary_bn'  => 'কোনো থার্ড-পার্টি প্লাগিন ছাড়াই নির্মিত সুপারফাস্ট ব্যক্তিগত ওয়েবসাইট থিম। এতে রয়েছে এজেক্স নেভিগেশন, ডার্ক মোড ও ডায়নামিক কালার প্যালেট জেনারেশন।',
			'summary_en'  => 'An ultra-fast, zero-plugin custom WordPress theme featuring seamless AJAX pagination, distraction-free reading modal, and WCAG-compliant dynamic theming.',
			'role_bn'     => 'ফুলস্ট্যাক থিম আর্কিটেক্ট ও লেখক',
			'role_en'     => 'Full-Stack Theme Architect & Writer',
			'context_bn'  => 'ব্যক্তিগত সাহিত্য ও ব্লগ পোর্টাল • ভার্সন ৭.৪',
			'context_en'  => 'Personal Literature & Blog Portal • v7.4',
			'challenge_bn'=> "সাহিত্যের জন্য একটি বিভ্রান্তিমুক্ত (distraction-free), পরিচ্ছন্ন এবং দীর্ঘ সময় পড়ার উপযোগী ব্যক্তিগত প্রকাশনা সাইট তৈরি করা যেখানে পাঠক স্বাচ্ছন্দ্যে হারিয়ে যেতে পারেন। সাধারণ ওয়ার্ডপ্রেস সাইটে পেজ বদলালে পুরো ব্রাউজার রিলোড হয়ে সাদা পর্দা ভেসে ওঠে, যা পড়ার একাগ্রতা ভেঙে দেয়।\n\nএকই সাথে দ্বিভাষিক মাল্টিসাইট নেটওয়ার্কে পারমালিংক কনফ্লিক্ট এড়ানো, কোনো ট্র্যাকিং স্ক্রিপ্ট বা ভারী ডাটাবেস কোয়েরি ছাড়াই পাঠকের পড়ার অগ্রগতি (Resume Reading) ও বুকমার্ক মনে রাখা এবং ব্যবহারকারীর পছন্দের যে কোনো রঙে সাইটের লেখার স্পষ্টতা (Contrast) নিশ্চিত করাই ছিল প্রধান কারিগরি চ্যালেঞ্জ।",
			'challenge_en'=> "Creating an immersive, distraction-free digital literature platform that honors long-form reading. Standard WordPress sites suffer from jarring full-page browser reloads that disrupt reading flow, while multisite bilingual installations often introduce permalink collisions and /blog prefix bloat.\n\nThe challenge was achieving seamless pushState AJAX navigation, robust client-side reading state preservation (Resume Reading & Bookmarks) with absolute user privacy, and mathematical color accessibility across any user-selected palette without external plugins.",
			'solution_bn' => "১. পুশ-স্টেট এজেক্স নেভিগেশন ও সিঙ্গেল ফলব্যাক: প্রতিটি পোস্টের লিংকে ক্লিক করলে ব্যাকগ্রাউন্ডে এজেক্স ফ্র্যাগমেন্ট এনে মডালে ওপেন হয় এবং ব্রাউজারের ইউআরএল বারে আসল পারমালিংক লাইভ আপডেট হয় (window.history.pushState)। পাঠক ব্যাক বাটনে চাপ দিলে মডাল বন্ধ হয়ে আগের তালিকায় ফিরে যায়। আবার সেই পোস্টের ইউআরএল সরাসরি খুললে single.php স্বয়ংক্রিয়ভাবে ফুল-পেজ অভিজ্ঞতায় একই রূপ নিয়ে লোড হয়।\n\n২. গাণিতিক কালার প্যালেট ও WCAG 4.5:1 অ্যাক্সেসিবিলিটি: পাঠক নিজের ইচ্ছেমতো যেকোনো অ্যাকসেন্ট কালার বেছে নিতে পারেন। একটি নিজস্ব অ্যালগরিদম সেই রঙের উজ্জ্বলতা ও স্যাচুরেশন গণনা করে স্বয়ংক্রিয়ভাবে ডার্ক/লাইট মোডের জন্য WCAG অনুমোদিত নিখুঁত ৪.৫:১ কনট্রাস্ট রেশিও তৈরি করে।\n\n৩. জিরো-ইমেজ প্রসিডিউরাল ৩ডি বুকশেল্ফ: শত শত বইয়ের কভার ইমেজ লোড করার ব্যান্ডউইথ খরচ কমাতে একটি পিওর সিএসএস বুকশেল্ফ তৈরি করা হয়েছে। বইয়ের জঁরা (Genre) থেকে হ্যাশ ফাংশন দিয়ে কালার প্যালেট এবং টাইটেলের দৈর্ঘ্য মেপে বইয়ের বাস্তবসম্মত উচ্চতা নির্ধারণ করে স্বয়ংক্রিয়ভাবে স্পাইন আঁকা হয়—এতে ০ কিলোবাইট এক্সটার্নাল ইমেজ খরচ হয়!\n\n৪. রিডিং প্রগ্রেস ও রিজ্যুম নোটিফিকেশন: পাঠক কতদূর পড়েছেন তা স্ক্রল প্রগ্রেস বার ও 'বাকি সময় (Time Left)' দিয়ে লাইভ ট্র্যাক হয়। মাঝপথে চলে গেলে পরবর্তীতে সাইটে এলে স্বয়ংক্রিয়ভাবে যেখান থেকে পড়া শেষ হয়েছিল সেখান থেকে শুরু করার স্মার্ট প্রম্পট দেওয়া হয়।\n\n৫. স্বয়ংসম্পূর্ণ এসইও ও স্মার্ট ৪০৪ রিকভারি: কোনো থার্ড-পার্টি এসইও প্লাগিন ছাড়াই স্বয়ংক্রিয় OpenGraph, Twitter Cards এবং BreadcrumbList সহ JSON-LD স্কিমা তৈরি হয়। কোনো লিংক ভেঙে গেলে স্মার্ট রিকভারি অ্যালগরিদম আর্টিকেলের স্লাগের বানান বিশ্লেষণ করে সবচেয়ে প্রাসঙ্গিক লেখার সন্ধান দেয়।\n\n৬. সেলফ-হোস্টেড বাংলা ফন্ট ও গিটহাব কমিট আপডেটার: কোনো এক্সটার্নাল গুগল ফন্ট রিকোয়েস্ট ছাড়া নিজস্ব সার্ভার থেকে WOFF2 ফরম্যাটে বাংলা ফন্ট পরিবেশন এবং ব্রাঞ্চ ক্যাশিং সমস্যা এড়াতে কমিট হ্যাশ দিয়ে ভ্যালিডেট করে স্বয়ংক্রিয় থিম আপডেট।",
			'solution_en' => "1. PushState AJAX Architecture & Fallback: Clicking an article opens an instant modal while dynamically synchronizing the browser URL (window.history.pushState). Browser back/forward buttons seamlessly restore navigation state, while direct visits gracefully render single.php with identical visual presentation.\n\n2. Algorithmic Color Engine with WCAG 4.5:1 Contrast: Allows users to choose any custom accent hue. A custom luminance and saturation calculator procedurally adjusts the entire theme palette to strictly guarantee WCAG AA 4.5:1 contrast compliance.\n\n3. Procedural CSS 3D Bookshelf (Zero Images): Renders hundreds of dynamic book spines purely in procedural CSS. Spines calculate HSL colors from genre hashes and dynamic vertical heights from title lengths, incurring 0 KB external image load.\n\n4. Reading Progress & Smart Resume Notification: Tracks scroll depth and dynamically recalculates reading time left. Remembers reading scroll positions locally to display an instant 'Resume Reading' toast on return.\n\n5. Self-Contained SEO & Fuzzy 404 Recovery: Native Open Graph, Twitter Cards, and BreadcrumbList JSON-LD structured data. A custom phoneme-based fuzzy slug analyzer intelligently redirects broken URLs to the closest published article.\n\n6. Self-Hosted Bengali WOFF2 Fonts & Commit-SHA Updater: Completely independent from external Google Fonts servers for optimal privacy and speed, paired with a GitHub self-updater pinned to immutable commit hashes.",
			'highlights_bn'=> array(
				'১০০/১০০ গুগল পেজস্পিড ও কোর ওয়েব ভাইটালস — কোনো ভারী ফ্রন্টএন্ড ফ্রেমওয়ার্কের বোঝা নেই',
				'০টি এক্সটার্নাল প্লাগিন — রিডিং মডাল, বুকমার্ক, ভিউ ট্র্যাকার, এসইও ও কাস্টমাইজার সম্পূর্ণ অন্তর্নির্মিত',
				'দ্বিভাষিক মাল্টিসাইট অপ্টিমাইজেশন — বাংলা (/) ও ইংরেজি (/en/) সাবসাইটের জন্য নিরবচ্ছিন্ন ল্যাঙ্গুয়েজ সুইচিং ও ক্লিন পারমালিংক',
				'স্মার্ট ভিউ ও রিডার কাউন্টার — ক্যাশ বাইপাস করে রিয়েল ইউনিক রিডার ট্র্যাক করতে নিজস্ব লাইটওয়েট REST এন্ডপয়েন্ট',
				'অটো ইমেজ অপ্টিমাইজেশন — আপলোড করা ছবি স্বয়ংক্রিয়ভাবে থিমের ভেতর থেকেই WebP ফরম্যাটে রূপান্তর ও রিসাইজ',
				'গিটহাব পিনড কমিট আপডেটার — ব্রাঞ্চ ক্যাশিং সমস্যা এড়াতে কমিট হ্যাশ দিয়ে ভ্যালিডেট করে স্বয়ংক্রিয় থিম আপডেট',
				'জিরো-ইমেজ সিএসএস ভার্চুয়াল বুকশেল্ফ — প্রসিডিউরাল অ্যালগরিদম ভিত্তিক অনন্য ইন্টারেক্টিভ ইন্টারফেস'
			),
			'highlights_en'=> array(
				'100/100 Google PageSpeed & Core Web Vitals across mobile and desktop devices',
				'Zero external plugins: reading modal, bookmarks, view tracking, SEO, and settings completely built-in',
				'Multisite bilingual optimization: clean permalinks and instant language switching between / and /en/',
				'Smart reader counter: lightweight native REST endpoint (POST /wp-json/rs/v1/view/<id>) bypassing cache',
				'Automated image conversion: auto-resizes and converts uploads to next-gen WebP without third-party tools',
				'GitHub Pinned-Commit Auto-Updater: reliable one-click dashboard updates pinned to immutable commit SHAs',
				'Procedural 3D CSS Bookshelf: procedural genre-to-HSL hash rendering with zero external graphics load'
			),
			'tags'        => array( 'WordPress', 'PHP 8', 'Vanilla JS', 'Semantic CSS', 'REST API' ),
			'accent'      => '#0984e3',
			'icon'        => 'code',
			'image'       => get_template_directory_uri() . '/screenshot.png',
			'image_fit'   => 'cover',
			'action_type' => 'web',
			'action_bn'   => 'লাইভ সাইট দেখুন',
			'action_en'   => 'Visit Live Site',
			'direct_url'  => home_url( '/' ),
			'github_url'  => 'https://github.com/raisulsohan/RaisulSohanSite',
			'order'       => 20,
		),
		array(
			'id'          => 'motion-showreel',
			'category'    => 'video',
			'type_bn'     => 'মোশন ডিজাইন ও ২ডি অ্যানিমেশন শোরিল',
			'type_en'     => 'Motion Design & 2D Animation Showreel',
			'badge_bn'    => 'অফিসিয়াল শোরিল • শোকেস',
			'badge_en'    => 'Official Showreel • Showcase',
			'title_bn'    => 'মোশন ডিজাইন, ২ডি অ্যানিমেশন ও প্রোডাক্ট ভিডিও শোরিল',
			'title_en'    => 'Motion Design, 2D Animation & Product Video Showreel',
			'summary_bn'  => 'সাস প্রোডাক্ট ওয়াকথ্রু, ২ডি ক্যারেক্টার ও ইনফোগ্রাফিক অ্যানিমেশন, কাইনেটিক টাইপোগ্রাফি এবং ডায়নামিক কমার্শিয়াল মোশন গ্রাফিক্সের নির্বাচিত কাজের হাই-এনার্জি রিদমিক সংকলন। সম্পূর্ণ ভেক্টর আর্টওয়ার্ক অ্যাডোবি ইলাস্ট্রেটরে তৈরি এবং কিফ্রেম ও ভেলোসিটি কার্ভস আফটার ইফেক্টসে কম্পোজ করা।',
			'summary_en'  => 'A high-impact motion showcase featuring curated excerpts of SaaS product walkthroughs, 2D character and infographic animation, kinetic typography, and dynamic product commercials—crafted with Adobe After Effects and Illustrator.',
			'role_bn'     => 'মোশন ডিজাইনার, ২ডি অ্যানিমেটর ও কম্পোজিটর',
			'role_en'     => 'Motion Designer, 2D Animator & Compositor',
			'context_bn'  => 'অফিসিয়াল মোশন পোর্টফোলিও শোরিল • ১৬:৯ • আফটার ইফেক্টস ও ইলাস্ট্রেটর',
			'context_en'  => 'Official Motion Portfolio Showreel • 16:9 • After Effects & Illustrator',
			'challenge_bn'=> "একটি পেশাদার মোশন শোরিলের প্রধান চ্যালেঞ্জ হলো স্বল্প সময়ের মধ্যে বহুমুখী টেকনিক্যাল ও নান্দনিক দক্ষতার গভীরতা বিশ্বাসযোগ্যভাবে প্রমাণ করা। আন্তর্জাতিক ক্লায়েন্ট, ক্রিয়েটিভ ডিরেক্টর এবং প্রোডাক্ট টিম সাধারণত দেখতে চায় অ্যানিমেটর একাধিক জঁনরে—যেমন জটিল SaaS ড্যাশবোর্ড ও ওয়ার্কফ্লো অ্যানিমেশন, ২ডি ভেক্টর ও ইনফোগ্রাফিক ভিজ্যুয়ালাইজেশন (যেমন ফাইন্যান্সিয়াল ও ক্রেডিট কার্ড অ্যানিমেশন), কাইনেটিক টাইপোগ্রাফি এবং হাই-কনভার্টিং প্রোডাক্ট ভিডিও—সমানভাবে দক্ষ কিনা।\n\nদ্বিতীয় বড় চ্যালেঞ্জ ছিল পেসিং ও অডিও-ভিজ্যুয়াল কন্টিনিউটি। ভিন্ন ভিন্ন প্রজেক্টের কালার স্কিম ও ভিজ্যুয়াল স্টাইল থাকা সত্ত্বেও পুরো রিলকে এমনভাবে ট্রানজিশন, ক্যামেরা মুভমেন্ট ও গ্রাফ এডিটর ভেলোসিটিতে গাঁথা হয়েছে যাতে এটি কোনো বিচ্ছিন্ন ক্লিপের সংকলন না হয়ে একটি আকর্ষণীয় একক গতিশীল অভিজ্ঞতা তৈরি করে। প্রতিটি সিন ও কাটে নিখুঁত অডিও ফলি এবং সাউন্ড ডিজাইনের সিঙ্ক বজায় রাখা ছিল অন্যতম সূক্ষ্ম কারিগরি কাজ।",
			'challenge_en'=> "The core challenge of crafting a definitive motion showreel is condensing multifaceted technical and aesthetic capabilities into a high-retention, fast-paced portfolio piece. Prospective SaaS founders, creative directors, and global agencies look for proven versatility across multiple animation disciplines: dense software UI walkthroughs, 2D vector infographics (such as financial breakdown and credit card sequences), kinetic typography, and punchy product commercial rhythms.\n\nThe secondary challenge was pacing, color harmony, and seamless continuity. Rather than an arbitrary montage of unrelated clips, the reel demanded fluid spatial transitions, matching camera velocity curves, and cohesive kinetic choreography across distinct brand styles. Every visual pop had to preserve razor-sharp vector clarity while locking tightly to the rhythmic tempo and custom micro-audio foley.",
			'solution_bn' => "১. ১০০% ভেক্টর আর্টওয়ার্ক পাইপলাইন (Adobe Illustrator): শোরিলের প্রতিটি গ্রাফিক, আইকন, ড্যাশবোর্ড কার্ড, ক্যারেক্টার ও ইনফোগ্রাফিক সিন অ্যাডোবি ইলাস্ট্রেটরে নিখুঁত স্কেলেবল ভেক্টর লেয়ারে ডিজাইন করা হয়েছে। ফলে Full HD থেকে 4K ডিসপ্লেতেও প্রতিটি টেক্সট ও শেপ ক্রিস্টাল-ক্লিয়ার শার্প থাকে।\n\n২. গ্রাফ এডিটর ভেলোসিটি ও অর্গানিক মোশন (After Effects): আফটার ইফেক্টসের স্পিড ও ভ্যালু গ্রাফ এডিটর ব্যবহার করে প্রতিটি মুভমেন্টে কাস্টম ইজিং, অর্গানিক ইলাস্টিক বাউন্স ও স্ন্যাপি এক্সিলারেশন যুক্ত করা হয়েছে, যা রোবোটিক ভাব দূর করে আন্তর্জাতিক মানের মোশন নিশ্চিত করে।\n\n৩. বহুমুখী স্কিল ক্লাস্টার প্রদর্শন: শোরিলটিতে সুপরিকল্পিতভাবে ৪টি মূল অ্যানিমেশন ডোমেইন উপস্থাপন করা হয়েছে— (ক) SaaS প্রোডাক্ট ও ইউআই ডেটা ফ্লো অ্যানিমেশন; (খ) ২.৫ডি আইসোমেট্রিক সিটিস্কেপ, ফ্লোটিং অ্যাসেট ও ইনফোগ্রাফিক কম্পোজিশন; (গ) কাইনেটিক টাইপোগ্রাফি ও শেপ মর্ফিং; এবং (ঘ) স্ন্যাপি ক্যামেরা পুশ ও মসৃণ স্প্যাশিয়াল ট্রানজিশন।\n\n৪. ২.৫ডি স্প্যাশিয়াল এনভায়রনমেন্ট ও লাইটিং: সমতল ভেক্টর আর্টওয়ার্কে সিনেম্যাটিক গভীরতা দিতে আফটার ইফেক্টসের ৩ডি লেয়ার স্পেস, ডেপথ অফ ফিল্ড, সফট নিয়ন গ্লো এবং মেঝেতে গ্লসি রিফ্লেকশন টেকনিক ব্যবহার করা হয়েছে।\n\n৫. বিট-পারফেক্ট সাউন্ড ডিজাইন ও অডিও সিঙ্ক: প্রতিটি কার্ড স্লাইড, হুপ, স্ন্যাপ এবং টেক্সট ট্রানজিশনের জন্য মাল্টি-লেয়ার্ড সাউন্ড এফেক্টস (foley, whooshes, clicks, impacts) তৈরি করে ব্যাকগ্রাউন্ড মিউজিকের ড্রপ ও বিটের সাথে ফ্রেম-পারফেক্টভাবে সিঙ্ক করা হয়েছে।",
			'solution_en' => "1. 100% Scalable Vector Pipeline (Adobe Illustrator): Handcrafted every graphic element, device mock, infographic scene, and typography layout in Adobe Illustrator as pristine vector paths, guaranteeing razor-sharp high-DPI rendering without pixelation.\n\n2. Velocity Curve Smoothing (Adobe After Effects): Rigged and animated using After Effects Graph Editor (Value & Speed curves). Engineered snappy spring physics, fluid ease curves, and energetic overshoot dynamics to deliver world-class broadcast-ready motion.\n\n3. Multi-Discipline Skill Architecture: Strategically orchestrated 4 primary animation domains: (a) SaaS UI deconstruction and product walkthroughs; (b) 2.5D isometric infographic environments (such as urban nightscapes, floating currency, and data cards); (c) Kinetic typography and narrative pacing; and (d) Dynamic camera choreography and spatial transitions.\n\n4. 2.5D Spatial Parallax & Compositing: Transformed flat vector artwork into rich three-dimensional space using After Effects 3D layers, camera focal depth, subtle ambient neon glow effects, and glossy floor reflections.\n\n5. Layered UI Sound Design & Rhythm Lock: Engineered bespoke tactile sound design—pneumatic whooshes, UI pops, impacts, and tempo risers—locked frame-for-frame to motion transformations and musical cadence.",
			'highlights_bn'=> array(
				'সাস প্রোডাক্ট ওয়াকথ্রু, ২ডি ক্যারেক্টার ও ইনফোগ্রাফিক অ্যানিমেশনের বৈচিত্র্যময় শোকেস',
				'অ্যাডোবি ইলাস্ট্রেটরে প্রস্তুতকৃত ১০০% কাস্টম ও স্কেলেবল ভেক্টর আর্টওয়ার্ক',
				'আফটার ইফেক্টস গ্রাফ এডিটর দিয়ে তৈরি স্ন্যাপি ভেলোসিটি কার্ভস ও অর্গানিক ইজিং',
				'২.৫ডি আইসোমেট্রিক সিটিস্কেপ, ফ্লোর রিফ্লেকশন ও নিয়ন গ্লো কম্পোজিটিং',
				'কাইনেটিক টাইপোগ্রাফি, শেপ মর্ফিং এবং সিমলেস ক্যামেরা ট্রানজিশন',
				'হাই-এনার্জি মিউজিক বিট-সিঙ্ক ও নিখুঁত মাইক্রো-অডিও সাউন্ড ডিজাইন',
				'আপওয়ার্ক ও আন্তর্জাতিক ক্লায়েন্টদের জন্য প্রফেশনাল স্ট্যান্ডার্ড ভিডিও প্রোডাকশন'
			),
			'highlights_en'=> array(
				'Versatile motion showcase highlighting SaaS walkthroughs, 2D characters, and infographics',
				'100% custom scalable vector illustration pipeline crafted in Adobe Illustrator',
				'Snappy velocity curve tuning and organic spring dynamics via After Effects Graph Editor',
				'2.5D isometric cityscapes, reflective glossy floors, and ambient neon glow compositing',
				'Kinetic typography, shape morphing, and seamless spatial camera pushes',
				'Tempo-locked audio synchronization with layered micro-foley and sound effects',
				'Engineered to international commercial standards for global SaaS and agency clientele'
			),
			'tags'        => array( 'After Effects', 'Illustrator', 'Showreel', 'SaaS Explainer', '2D Animation', 'Kinetic Typography', 'Sound Design' ),
			'accent'      => '#8c7ae6',
			'icon'        => 'video',
			'image'       => get_template_directory_uri() . '/assets/img/showreel.png',
			'action_type' => 'video',
			'action_bn'   => 'ইউটিউবে শোরিলটি দেখুন',
			'action_en'   => 'Watch Showreel on YouTube',
			'direct_url'  => 'https://youtu.be/4RUm_kR_wgA',
			'order'       => 30,
		),
		array(
			'id'          => 'thrivedesk-explainer',
			'category'    => 'video',
			'type_bn'     => 'সাস প্রডাক্ট এক্সপ্লেইনার ও মোশন অ্যানিমেশন',
			'type_en'     => 'SaaS Product Explainer & Motion Animation',
			'badge_bn'    => 'সাস অ্যানিমেশন • ৩ মিনিট',
			'badge_en'    => 'SaaS Explainer • 3 Mins',
			'title_bn'    => 'ThriveDesk Overview — ৩ মিনিটের সাস মোশন অ্যানিমেশন ও প্রোডাক্ট ওয়াকথ্রু',
			'title_en'    => 'ThriveDesk Overview — 3-Minute SaaS Motion Animation & Product Walkthrough',
			'summary_bn'  => 'গ্রোয়িং বিজনেসের কাস্টমার সাপোর্ট সহজ করতে থ্রাইভডেস্কের সেন্ট্রালাইজড ইনবক্স, টিকেট ডেলিগেশন, এআই রেসপন্স ড্রাফটিং, অটোমেশন রুলস, লাইভ চ্যাট অ্যাসিস্ট্যান্ট ও নলেজবেস ফিচারগুলোর আকর্ষণীয় ২ডি মোশন গ্রাফিক্স ওয়াকথ্রু।',
			'summary_en'  => 'Dynamic 2D SaaS motion explainer visualizing ThriveDesk\'s all-in-one customer support suite—shared inboxes, AI drafting, ticket delegation, automated workflows, live chat widgets, and self-service knowledge base in 3 minutes.',
			'role_bn'     => 'মোশন ডিজাইনার ও এক্সপ্লেইনার অ্যানিমেটর',
			'role_en'     => 'Motion Designer & Explainer Animator',
			'context_bn'  => 'অফিসিয়াল SaaS ওভারভিউ ভিডিও • ৩ মিনিট • ১৬:৯',
			'context_en'  => 'Official SaaS Overview Video • 3 Mins • 16:9',
			'challenge_bn'=> "থ্রাইভডেস্কের মতো একটি পরিপূর্ণ মাল্টি-ফাংশনাল কাস্টমার সাপোর্ট স্যুট—যাতে রয়েছে সেন্ট্রালাইজড ইনবক্স (Gmail, Outlook, Zoho), টিকেট ডেলিগেশন, ট্যাগ ও কালার কোডিং, ক্যানড রিপ্লাই, এআই ড্রাফটিং, প্রাইভেট নোটস ও টিম @মেনশন, ডুপ্লিকেট টিকেট মার্জিং, অটোমেশন রুলস ও স্প্যাম ফিল্টারিং, লাইভ চ্যাট অ্যাসিস্ট্যান্ট, রিয়েল-টাইম ভিজিটর ট্র্যাকিং, নলেজবেস পোর্টাল এবং ওয়ার্ডপ্রেস, WooCommerce ও Shopify-এর গভীর ইন্টিগ্রেশন—এই বিশাল জটিল প্ল্যাটফর্মকে মাত্র ৩ মিনিটের ভেতর সাধারণ ব্যবসা পরিচালকদের কাছে আকর্ষণীয় ও সাবলীলভাবে ফুটিয়ে তোলা ছিল মূল চ্যালেঞ্জ।\n\nসাধারণ স্ক্রিন রেকর্ডিং দিয়ে দেখালে ভিডিওটি একঘেয়ে ও ধীরগতির হয়ে পড়ে। তাই সফটওয়্যারটির প্রতিটি ইউআই এলিমেন্টকে স্ক্রিন থেকে আলাদা করে স্কেলেবল ভেক্টর গ্রাফিক্সে রূপান্তর করা, স্ন্যাপি ট্রানজিশনের মাধ্যমে জটিল ওয়ার্কফ্লোকে সহজ ভিজ্যুয়াল মেটাফোরে উপস্থাপন করা এবং প্রফেশনাল ভয়েসওভারের রিদমের সাথে নিখুঁত সিঙ্ক বজায় রাখা ছিল প্রধান কারিগরি ও ক্রিয়েটিভ চ্যালেঞ্জ।",
			'challenge_en'=> "ThriveDesk encompasses a comprehensive customer support ecosystem: unified email syncing (Gmail, Outlook, Zoho), team ticket delegation, color-coded tagging, canned replies, AI drafting, internal private notes, team @mentions, conversation history, ticket deduplication, automated routing & spam filtering, live website chat assistants, real-time visitor tracking, self-service knowledge bases, and deep e-commerce integrations (WordPress, WooCommerce, Shopify). Distilling this sprawling platform into a concise, engaging 3-minute visual walkthrough without overwhelming business owners was a profound communication challenge.\n\nTraditional desktop screencasts feel static, cluttered, and sluggish. The creative and technical challenge was deconstructing the product's UI into modular vector compositions, orchestrating fluid camera choreographies and snappy transition curves across 15+ feature states, and establishing rhythmic kinetic motion tightly locked to voiceover cadence.",
			'solution_bn' => "১. ৩-অ্যাক্ট ভিজ্যুয়াল আর্কিটেকচার: ভিডিওটিকে তিনটি স্পষ্ট অধ্যায়ে বিন্যস্ত করা হয়েছে— (ক) ইনবক্স সেটআপ, টিম অনবোর্ডিং ও টিকেট ডেলিগেশন; (খ) অটোমেশন রুলস, এআই ড্রাফটিং, ক্যানড রিপ্লাই ও ইন্টারনাল কোলাবোরেশন; এবং (গ) লাইভ চ্যাট অ্যাসিস্ট্যান্ট, রিয়েল-টাইম ভিজিটর এনগেজমেন্ট, নলেজবেস ও ই-কমার্স ইন্টিগ্রেশন।\n\n২. মডুলার ভেক্টর ইউআই রিকনস্ট্রাকশন: প্রতিটি স্ক্রিন, বাটন, ড্রপডাউন এবং চ্যাট বাবলকে ইলাস্ট্রেটরে স্কেলেবল ভেক্টর লেয়ারে নিখুঁতভাবে তৈরি করা হয়েছে, যাতে আল্ট্রা-এইচডি ডিসপ্লেতেও প্রতিটি টেক্সট ও আইকন ক্রিস্টাল ক্লিয়ার থাকে।\n\n৩. স্ন্যাপি কিফ্রেম ও ভেলোসিটি কার্ভস: আফটার ইফেক্টসের গ্রাফ এডিটর ব্যবহার করে অর্গানিক ইলাস্টিক বাউন্স, স্মুথ ইজিং ও স্ন্যাপি ট্রানজিশন তৈরি করা হয়েছে, যা দর্শকদের দৃষ্টিকে স্বাভাবিকভাবে এক ফিচার থেকে অন্য ফিচারে প্রবাহিত করে।\n\n৪. ভিজ্যুয়াল মেটাফোর ও আইকনোগ্রাফি: ইমেল প্রোভাইডার কানেকশন, ডুপ্লিকেট মার্জিং এবং এআই রেসপন্স তৈরির ক্ষেত্রে জটিল টেকনিক্যাল ধারণাকে সহজ ও উপভোগ্য অ্যানিমেশনে রূপান্তর করা হয়েছে।\n\n৫. সাউন্ড ডিজাইন ও অডিও সিঙ্ক: প্রতিটি বাটন ক্লিক, পপ-আপ, কার্ড সোয়াইপ এবং ট্রানজিশনের জন্য নিখুঁত সাউন্ড এফেক্টস (foley, whooshes, UI pops) লেয়ারিং করা হয়েছে, যা ভয়েসওভারের গতি ও মেজাজের সাথে নিখুঁতভাবে সিঙ্ক হয়ে সর্বোচ্চ রিটেনশন নিশ্চিত করে।",
			'solution_en' => "1. Three-Act Narrative Architecture: Segmented the 3-minute script into a structured narrative arc: (a) Shared Inbox foundation, team onboarding, and ticket delegation; (b) Workflow automation, canned responses, spam filtering, private notes, and AI drafting; (c) Live chat assistants, real-time visitor monitoring, self-service knowledge bases, and WordPress/e-commerce integrations.\n\n2. Modular Vector UI Reconstruction: Vectorized every screen, ticket row, dropdown modal, and widget icon in Adobe Illustrator, ensuring razor-sharp high-DPI rendering and clean layout hierarchy without visual clutter.\n\n3. Velocity Curves & Fluid Transitions: Rigged dynamic motion inside Adobe After Effects using custom speed graph curves, kinetic pops, and camera pans that guide viewer eye tracking effortlessly across dense interface workflows.\n\n4. Visual Metaphor Orchestration: Translated technical concepts—such as multi-provider email synchronization, internal notes/@mentions, ticket deduplication, and automated routing rules—into engaging kinetic sequences.\n\n5. Layered UI Sound Design: Engineered bespoke micro-audio cues (clicks, whooshes, notification chimes, and swooshes) synchronized with screen transformations, elevating brand polish and auditory immersion.",
			'highlights_bn'=> array(
				'৩ মিনিটে থ্রাইভডেস্কের সম্পূর্ণ কাস্টমার সাপোর্ট প্ল্যাটফর্মের আকর্ষণীয় উপস্থাপন',
				'শেয়ার্ড ইনবক্স, টিকেট ডেলিগেশন ও টিম কোলাবোরেশনের স্ন্যাপি মোশন',
				'এআই রেসপন্স ড্রাফটিং, ক্যানড রিপ্লাই ও অটোমেশন রুলসের ভিজ্যুয়াল ওয়াকথ্রু',
				'লাইভ চ্যাট উইজেট, রিয়েল-টাইম ভিজিটর ট্র্যাকিং ও নলেজবেস অ্যানিমেশন',
				'ওয়ার্ডপ্রেস, WooCommerce ও Shopify ই-কমার্স ইন্টিগ্রেশনের পরিষ্কার চিত্রায়ন',
				'কাস্টম ভেক্টর ইউআই ডিজাইন ও আফটার ইফেক্টস গ্রাফ এডিটর ভেলোসিটি স্মুথিং',
				'প্রফেশনাল সাউন্ড ডিজাইন ও ভয়েসওভারের সাথে নিখুঁত বিট-সিঙ্ক'
			),
			'highlights_en'=> array(
				'Comprehensive 3-minute SaaS overview animating ThriveDesk’s complete support suite',
				'Dynamic visualization of shared inboxes, ticket assignment, and team collaboration',
				'Kinetic walkthrough of AI drafting, canned responses, and workflow automations',
				'Animated presentation of live chat assistant widgets, visitor monitoring, and knowledge bases',
				'Clear depiction of WordPress, WooCommerce, and Shopify native integrations',
				'Full vector UI reconstruction with After Effects velocity curve smoothing',
				'Precision UI sound design synchronized with motion transitions and voiceover cadence'
			),
			'tags'        => array( 'After Effects', 'Illustrator', 'SaaS Explainer', 'Motion Graphics', '2D Animation', 'Sound Design' ),
			'accent'      => '#6c5ce7',
			'icon'        => 'video',
			'image'       => get_template_directory_uri() . '/assets/img/thrivedesk.png',
			'action_type' => 'video',
			'action_bn'   => 'ইউটিউবে ভিডিওটি দেখুন',
			'action_en'   => 'Watch Video on YouTube',
			'direct_url'  => 'https://youtu.be/ff0j6OYG4Ms',
			'order'       => 40,
		),
		array(
			'id'          => 'quiet-scroll',
			'category'    => 'tools',
			'type_bn'     => 'ক্রোম ও এজ এক্সটেনশন',
			'type_en'     => 'Chrome & Edge Extension',
			'badge_bn'    => 'Manifest V3 • ওপেন সোর্স',
			'badge_en'    => 'Manifest V3 • Open Source',
			'title_bn'    => 'QuietScroll — স্মার্ট পার-সাইট মিডিয়া ভলিউম কন্ট্রোল',
			'title_en'    => 'QuietScroll — Smart Per-Site Media Volume Control',
			'summary_bn'  => 'যেকোনো ভিডিও বা অডিও প্লেয়ারে Alt + মাউস হুইল ঘুরিয়ে নিরবচ্ছিন্ন সাউন্ড নিয়ন্ত্রণ, অটোপ্লে থেকে রক্ষা করতে ভলিউম গার্ড এবং প্রতিটি সাইটের জন্য আলাদা ভলিউম মেমোরি মনে রাখার হালকা ক্রোম এক্সটেনশন।',
			'summary_en'  => 'Lightweight Chromium extension for controlling any media volume using Alt + Mouse Wheel. Features per-site volume memory, MAIN-world Volume Guard, ultra-low presets, and instant Night Mode.',
			'role_bn'     => 'একক এক্সটেনশন আর্কিটেক্ট ও ডেভেলপার',
			'role_en'     => 'Solo Extension Architect & Developer',
			'context_bn'  => 'ক্রোমিয়াম ব্রাউজার এক্সটেনশন • Manifest V3 • v1.7',
			'context_en'  => 'Chromium Browser Extension • Manifest V3 • v1.7',
			'challenge_bn'=> "ইউটিউব, ফেসবুক, টুইটার কিংবা বিভিন্ন নিউজ পোর্টালে ভিডিওর অডিও লেভেল একেক সাইটে একেক রকম থাকে। অনেক সাইট স্বয়ংক্রিয়ভাবে ভিডিও অটোপ্লে করে বা ইউজারের নিজস্ব সাউন্ড প্রেফারেন্স ওভাররাইড করে অতিরিক্ত উচ্চ শব্দে বাজতে শুরু করে। তাছাড়া গভীর রাতে হেডফোন দিয়ে শোনার সময় সাধারণ ব্রাউজার স্লাইডারের ১% ভলিউমও অনেক বেশি উচ্চকিত মনে হয়।\n\nকারিগরি দিক থেকে প্রধান চ্যালেঞ্জ ছিল: পেজের স্বাভাবিক স্ক্রলে কোনো প্রকার ব্যাঘাত না ঘটিয়ে কিংবা ফুলস্ক্রিন প্লেয়ার নষ্ট না করে যেকোনো HTML5 ভিডিও/অডিও প্লেয়ারের অডিও স্ট্রিম ইন্টারসেপ্ট করা, অটোপ্লে প্লেয়ারের জোরপূর্বক সাউন্ড পরিবর্তন প্রতিহত করা এবং কোনো ট্র্যাকিং ছাড়াই সম্পূর্ণ লোকাল স্টোরেজে ডোমেইন ভিত্তিক ভলিউম মেমোরি ধরে রাখা।",
			'challenge_en'=> "Web video and audio players across platforms (YouTube, Twitter/X, news portals) suffer from wildly inconsistent mixing levels and aggressive autoplay volume resets. Furthermore, standard volume ladders lack the granular resolution needed for ultra-sensitive in-ear monitors (IEMs) during late-night listening.\n\nThe engineering challenge was intercepting mousewheel gestures strictly over media elements without disrupting normal vertical scrolling or breaking fullscreen APIs, neutralizing third-party player script overrides in the browser's MAIN world, and maintaining persistent per-origin state with zero telemetry under Manifest V3 restrictions.",
			'solution_bn' => "১. Alt + মাউস হুইল জেসচার ইন্টারসেপশন: পেজের সাধারণ স্ক্রলে কোনো ব্যাঘাত না ঘটিয়ে শুধুমাত্র Alt কি চেপে মাউস হুইল ঘুরালে নিখুঁত ভলিউম পরিবর্তন হয় এবং স্ক্রিনে একটি আধুনিক অন-স্ক্রিন ওএসডি (HUD) ভেসে ওঠে।\n\n২. পার-সাইট স্বয়ংক্রিয় ভলিউম মেমোরি: প্রতিটি ওয়েবসাইটের জন্য আলাদা আলাদা ভলিউম লেভেল স্বয়ংক্রিয়ভাবে Chrome Storage API-তে সংরক্ষিত থাকে। পরবর্তীতে সেই সাইটে প্রবেশ করলে ভিডিও নিজে থেকেই কাঙ্ক্ষিত সাউন্ডে প্লে হয়।\n\n৩. আর্কিটেকচারাল ভলিউম গার্ড (MAIN-World Script): কিছু আগ্রাসী প্লেয়ার যাতে জোরপূর্বক ইউজারের ভলিউম রিসেট করতে না পারে, সেজন্য Chrome 111+ এর document_start MAIN-ওয়ার্ল্ড কনটেন্ট স্ক্রিপ্ট দিয়ে HTMLMediaElement.prototype.volume প্রোপার্টি ডিসক্রিপ্টর ইন্টারসেপ্ট করে কাঙ্ক্ষিত লেভেল অবিচল লক রাখা হয়।\n\n৪. আল্ট্রা-লো প্রিসেটস (০.১২৫% পর্যন্ত): সূক্ষ্ম ও শান্ত শোনার জন্য পপ-আপে ৬টি কুইক ওয়ান-ক্লিক প্রিসেট: ০.১২৫%, ০.১৮৭৫%, ০.২৫%, ০.৩৭৫%, ০.৫% এবং ১%।\n\n৫. গ্লোবাল ওয়ান-ক্লিক নাইট মোড: পপ-আপ থেকে নাইট মোড অন করলেই সমস্ত ওয়েবসাইটের ভলিউম এক নিমেষে নির্ধারিত শান্ত স্তরে নেমে আসে। অফ করলে প্রতিটি সাইট তার নিজস্ব আগের মেমোরি ফিরে পায়।\n\n৬. শতভাগ প্রাইভেট ও অফলাইন: এক্সটেনশনটি কোনো অ্যানালিটিক্স বা ট্র্যাকিং স্ক্রিপ্ট ব্যবহার করে না। সমস্ত ডাটা ইউজারের নিজস্ব ব্রাউজারে সম্পূর্ণ বিচ্ছিন্ন ও সুরক্ষিত থাকে।",
			'solution_en' => "1. Alt + Mouse Wheel Gesture Interception: Transparently hooks wheel events exclusively when Alt is depressed, calculating proportional audio steps and projecting a sleek on-screen HUD without interfering with normal vertical page scroll.\n\n2. Persistent Per-Domain Volume Memory: Utilizes chrome.storage.local to map domain origins to custom volume preferences, automatically applying remembered levels on DOM navigation.\n\n3. Architectural Volume Guard (MAIN-World Script): Implemented a document_start script running in the browser's MAIN world to wrap HTMLMediaElement.prototype.volume property descriptors, neutralizing aggressive autoplay overrides by third-party web players.\n\n4. Ultra-Low Acoustic Presets: Engineered sub-linear stepping down to 0.125%, 0.1875%, 0.25%, and 0.5% tailored for high-sensitivity in-ear monitors (IEMs) and late-night listening.\n\n5. Global One-Click Night Mode: Instantly caps all active tabs and domain profiles to a preconfigured quiet ceiling without overwriting individual site memories.\n\n6. 100% Offline & Zero-Telemetry: Designed with strict Manifest V3 permissions (storage, activeTab), zero remote script dependencies, and absolute local data isolation.",
			'highlights_bn'=> array(
				'Alt + মাউস হুইল দিয়ে যেকোনো ওয়েব মিডিয়া প্লেয়ারের শব্দ নিখুঁতভাবে নিয়ন্ত্রণের সুবিধা',
				'প্রতিটি ওয়েবসাইটের জন্য আলাদা ভলিউম স্বয়ংক্রিয়ভাবে মনে রাখার স্মার্ট মেমোরি সিস্টেম',
				'ভলিউম গার্ড: অটোপ্লে ভিডিও সাইটগুলোর জোরপূর্বক সাউন্ড পরিবর্তন প্রতিহত করার আর্কিটেকচার',
				'গভীর রাতে শোনার জন্য ০.১২৫% পর্যন্ত আল্ট্রা-লো ভলিউম প্রিসেট',
				'এক ক্লিকে সমস্ত সাইট শান্ত করার ডেডিকেটেড নাইট মোড (Night Mode)',
				'গুগল ক্রোম Manifest V3 স্ট্যান্ডার্ডের সাথে ১০০% সামঞ্জস্যপূর্ণ',
				'১০০% অফলাইন ও প্রাইভেট — কোনো ট্র্যাকিং নেই, কোনো এক্সটার্নাল সার্ভার কল নেই'
			),
			'highlights_en'=> array(
				'Alt + Mouse Wheel gesture control over any HTML5 video or audio player',
				'Intelligent per-site volume memory persisted across browser sessions',
				'MAIN-world Volume Guard engine preventing aggressive autoplay resets',
				'Precision ultra-low audio presets stepping down to 0.125% for late-night listening',
				'Global One-Click Night Mode toggle with non-destructive volume restoration',
				'Full compliance with Google Chrome Manifest V3 modern extension standards',
				'100% private and offline: zero tracking, zero analytics, zero external network requests'
			),
			'tags'        => array( 'Chrome Extension', 'Manifest V3', 'JavaScript (ES6+)', 'Chrome Storage API', 'Audio Engineering' ),
			'accent'      => '#6c5ce7',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/quietscroll.png',
			'image_fit'   => 'contain',
			'action_type' => 'code',
			'action_bn'   => 'সোর্স কোড (GitHub)',
			'action_en'   => 'View Source on GitHub',
			'direct_url'  => 'https://github.com/raisulsohan/QuietScroll',
			'github_url'  => 'https://github.com/raisulsohan/QuietScroll',
			'order'       => 50,
		),
		array(
			'id'          => 'tickersnap',
			'category'    => 'tools',
			'type_bn'     => 'ক্রোম এক্সটেনশন',
			'type_en'     => 'Chrome Extension',
			'badge_bn'    => 'Manifest V3 • ডুয়েল এক্সট্র্যাক্টর',
			'badge_en'    => 'Manifest V3 • Dual Extractor',
			'title_bn'    => 'TickerSnap — ফুটবল কমেন্টারি ও আর্টিকেল টেক্সট এক্সট্র্যাক্টর',
			'title_en'    => 'TickerSnap — Match Commentary & Article Text Extractor',
			'summary_bn'  => 'ফুটবল ম্যাচ চলাকালীন লাইভ টেক্সট কমেন্টারি (FotMob ও Sofascore) এবং মোজিলা রিড্যাবিলিটি ইঞ্জিনের সাহায্যে যেকোনো নিউজ আর্টিকেল, পিডিএফ ও গুগল ডক্স থেকে বিজ্ঞাপনমুক্ত ক্লিন টেক্সট এক ক্লিকে এক্সট্র্যাক্ট করার ব্রাউজার এক্সটেনশন।',
			'summary_en'  => 'A high-performance Chrome extension featuring two one-click extractors: automated live football match commentary from FotMob/Sofascore and distraction-free article text powered by Mozilla\'s Readability.js engine.',
			'role_bn'     => 'একক এক্সটেনশন ডিজাইনার ও ডেভেলপার',
			'role_en'     => 'Solo Extension Architect & Developer',
			'context_bn'  => 'ক্রোমিয়াম ব্রাউজার এক্সটেনশন • Manifest V3',
			'context_en'  => 'Chromium Browser Extension • Manifest V3',
			'challenge_bn'=> "ফুটবল ম্যাচ চলাকালীন লাইভ টেক্সট কমেন্টারি সাধারণত লেজি-লোড (Lazy-load) হয়ে ধাপে ধাপে আসে এবং বিভিন্ন সাইটে (FotMob, Sofascore) ডম স্ট্রাকচার ও ক্লাসনেম ঘন ঘন পরিবর্তিত হয়। ফলে সাধারণ সাইট-স্পেসিফিক সিএসএস সিলেক্টর দিয়ে কমেন্টারি স্ক্র্যাপ করতে গেলে কোড ভেঙে যায়।\n\nঅন্য দিকে, আধুনিক ওয়েব আর্টিকেল, ব্লগ এবং অনলাইন পিডিএফগুলোতে বিজ্ঞাপন, সাইডবার, ট্র্যাকিং ব্যানার ও জটিল নেভিগেশনের ভিড়ে মূল টেক্সট আলাদা করা দুরূহ। একই সাথে ব্যবহারকারীর ব্রাউজিং প্রাইভেসি রক্ষা করে কোনো সাইট-ওয়াইড পারমিশন ছাড়া এবং কোনো দূরবর্তী সার্ভারে ডেটা না পাঠিয়ে সম্পূর্ণ ব্রাউজারের ভেতর টেক্সট এক্সট্র্যাক্ট করা ছিল প্রধান চ্যালেঞ্জ।",
			'challenge_en'=> "Live football commentary feeds on platforms like FotMob and Sofascore are continuously lazy-loaded, dynamically rendered, and frequently change their DOM class signatures. Hardcoded, site-specific CSS selectors break easily and fail to capture full match timelines.\n\nSimultaneously, extracting clean prose from articles, blogs, Google Docs, and web PDFs is heavily obstructed by aggressive ad banners, paywall overlays, navigation clutter, and custom pagination. The challenge was building an adaptive dual-mode extraction engine that operates strictly client-side under Chromium's activeTab privacy sandbox without full-host permissions.",
			'solution_bn' => "১. ক্লাস্টার-হিওরিস্টিক কমেন্টারি অ্যালগরিদম: কোনো নির্দিষ্ট ক্লাস বা হার্ডকোডেড সিলেক্টরের ওপর নির্ভর না করে পেজের রিয়েল সেন্টেন্স ব্লকগুলোকে কার্ডে গ্রুপ করে এবং সবচেয়ে ঘন ক্লাস্টারটিকে স্বয়ংক্রিয়ভাবে কমেন্টারি ফিড হিসেবে শনাক্ত করে। সাথে স্বয়ংক্রিয় পেজ স্ক্রলিংয়ের মাধ্যমে লেজি-লোডেড পুরনো এন্ট্রিগুলোও নিখুঁতভাবে সংগ্রহ করে।\n\n২. মোজিলা রিড্যাবিলিটি (Readability.js) ইন্টিগ্রেশন: ফায়ারফক্স রিডার ভিউয়ের শক্তিশালী ইঞ্জিন ব্যবহার করে যেকোনো নিউজ সাইট, ব্লগ, স্টোরি পোর্টাল কিংবা অনলাইন পিডিএফ ও গুগল ডক্স থেকে সব জঞ্জাল দূর করে শুধুমাত্র মূল কনটেন্ট নিষ্কাশন।\n\n৩. মিনিট স্ট্যাম্প ও ফরম্যাটিং প্রিজারভেশন: ম্যাচের মিনিট স্ট্যাম্প (যেমন: ৪৫', ৯০+৩') এবং হাফ-টাইম বুলেট সামারি হুবহু ফরম্যাট বজায় রেখে সাজিয়ে দেয়।\n\n৪. রিয়েল-টাইম প্রিভিউ ও সেশন পারসিস্টেন্স: পপ-আপে স্ক্রলেবল লাইভ প্রিভিউ, ওয়ার্ড ও ক্যারেক্টার কাউন্টার প্রদর্শন। পপ-আপ বন্ধ করে দিলেও ক্যাপচার করা টেক্সট ব্রাউজার সেশন জুড়ে মেমোরিতে অক্ষুণ্ণ থাকে।\n\n৫. ওয়ান-ক্লিক কপি ও .txt ডাউনলোড: সংগৃহীত টেক্সট নিমেষেই ক্লিপবোর্ডে কপি করা কিংবা ফাইল আকারে ডাউনলোড করার সুবিধা।\n\n৬. activeTab সিকিউরিটি মডেল: ব্রাউজারের কোনো সাইট-ওয়াইড পারমিশন ওয়ার্নিং নেই; ব্যবহারকারী ক্লিক করলেই কেবল নির্দিষ্ট ট্যাবে এক্সটেনশন কাজ করে এবং সমস্ত টেক্সট ১০০% ইউজারের ডিভাইসেই প্রসেস হয়।",
			'solution_en' => "1. Density-Cluster Commentary Heuristic: Bypasses fragile CSS selectors by analyzing sentence density clusters across the DOM, identifying the commentary list, and executing programmatic auto-scrolling to accumulate lazy-loaded timelines.\n\n2. Mozilla Readability.js Core: Embeds the proven Mozilla Readability engine (behind Firefox Reader View) to strip ads, sidebars, cookie banners, and navigational clutter, extracting pristine prose from news articles, stories, Google Docs, and PDF.js viewers.\n\n3. Match Minute Precision: Intelligently parses timestamps (e.g., 45', 90+3') and halftime bullet notes across varied typography and apostrophe encodings.\n\n4. Persistent Session Preview Panel: Instant preview with real-time character and word counts; captures survive popup closures and persist across the active browser session.\n\n5. One-Click Copy & .txt File Export: Formats plain-text outputs ready for instant clipboard copying or .txt downloading.\n\n6. Zero-Telemetry activeTab Security: Operates under Chromium's strict activeTab sandbox—only interacts with a page upon explicit user invocation with zero remote servers or telemetry.",
			'highlights_bn'=> array(
				'ডুয়েল এক্সট্র্যাক্টর: ফুটবল কমেন্টারি ও আর্টিকেল রিডার ভিউ একই এক্সটেনশনে',
				'ক্লাস্টার-হিওরিস্টিক ইঞ্জিন: কোনো নির্দিষ্ট ক্লাসের ওপর নির্ভর না করে স্বয়ংক্রিয় কমেন্টারি শনাক্তকরণ',
				'লেজি-লোড অটো-স্ক্রলিং: পুরো ৯০ মিনিটের কমেন্টারি স্বয়ংক্রিয়ভাবে স্ক্রল করে ক্যাপচার',
				'মোজিলা Readability.js পাওয়ারড: যেকোনো ওয়েব পেজ, পিডিএফ ও গুগল ডক্স থেকে ক্লিন টেক্সট',
				'রিয়েল-টাইম স্ক্রলেবল প্রিভিউ, ওয়ার্ড কাউন্টার ও সেশন পারসিস্টেন্স',
				'এক ক্লিকে ক্লিপবোর্ডে কপি ও .txt ফাইল ডাউনলোডের সুবিধা',
				'activeTab পারমিশন: সম্পূর্ণ ১০০% অফলাইন ও ব্যক্তিগত গোপনীয়তা রক্ষা'
			),
			'highlights_en'=> array(
				'Dual Extraction Engine: Live match commentary & clean article reading view in one tool',
				'Density-cluster heuristics: Identifies commentary feeds without fragile hardcoded selectors',
				'Automated lazy-load scrolling: Accumulates full 90+ minute timelines seamlessly',
				'Powered by Mozilla Readability.js: Cleans ads and sidebars from articles, PDFs, and Docs',
				'Live scrollable preview with word/character counter and persistent session memory',
				'Instant One-Click Clipboard Copy and .txt file download',
				'Chromium activeTab security: 100% client-side execution with zero external data transfer'
			),
			'tags'        => array( 'Chrome Extension', 'Manifest V3', 'Readability.js', 'DOM Heuristics', 'Text Extraction' ),
			'accent'      => '#00b894',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/tickersnap.png',
			'image_fit'   => 'contain',
			'action_type' => 'code',
			'action_bn'   => 'সোর্স কোড (GitHub)',
			'action_en'   => 'View Source on GitHub',
			'direct_url'  => 'https://github.com/raisulsohan/Tickersnap',
			'github_url'  => 'https://github.com/raisulsohan/Tickersnap',
			'order'       => 60,
		),
		array(
			'id'          => 'ruler-for-browser',
			'category'    => 'tools',
			'type_bn'     => 'ক্রোম ও এজ এক্সটেনশন',
			'type_en'     => 'Chrome & Edge Extension',
			'badge_bn'    => 'Manifest V3 • ডিজাইন ও মেজারমেন্ট',
			'badge_en'    => 'Manifest V3 • Precision Ruler & Snapping',
			'title_bn'    => 'Ruler for Browser — ফটোশপ স্টাইল রুলার, স্ন্যাপিং গাইড ও মেজারমেন্ট',
			'title_en'    => 'Ruler for Browser — Photoshop-Style Rulers, Draggable Guides & On-Page Measurement',
			'summary_bn'  => 'যেকোনো ওয়েব পেজের ওপর ফটোশপ ধাঁচের অনুভূমিক ও উল্লম্ব রুলার, ড্র্যাগ-অ্যান্ড-ড্রপ গাইডলাইন, রিয়েল ডম এলিমেন্ট স্ন্যাপিং এবং নিখুঁত ডিস্ট্যান্স মেজারমেন্টের লাইটওয়েট ক্রোম এক্সটেনশন।',
			'summary_en'  => 'Lightweight Chromium extension projecting Photoshop-style rulers, draggable guide overlays, smart element-edge snapping, and real-time pixel distance measurement directly over any webpage.',
			'role_bn'     => 'একক এক্সটেনশন ডিজাইনার ও ডেভেলপার',
			'role_en'     => 'Solo Extension Architect & Developer',
			'context_bn'  => 'ক্রোমিয়াম ব্রাউজার এক্সটেনশন • Manifest V3 • v1.0',
			'context_en'  => 'Chromium Browser Extension • Manifest V3 • v1.0',
			'challenge_bn'=> "ওয়েব UI ডেভেলপমেন্ট এবং ডিজাইন কিউএ (QA) অডিটের সময় বিভিন্ন উপাদানের মার্জিন, প্যাডিং কিংবা অ্যালাইনমেন্ট নিখুঁত আছে কি না তা যাচাই করা বেশ কঠিন। সাধারণত এজন্য ফুল-পেজ স্ক্রিনশট নিয়ে ফিগমা বা ফটোশপে নিয়ে মাপতে হয়, যা কাজের গতি নষ্ট করে।\n\nব্রাউজারের ভেতর সরাসরি রুলার ও গাইডলাইন যুক্ত করার ক্ষেত্রে প্রধান চ্যালেঞ্জ ছিল: মূল ওয়েবসাইটের নিজস্ব সিএসএস স্টাইল বা স্ক্রিপ্ট যাতে এক্সটেনশনের রুলারকে বিকৃত করতে না পারে, পেজ স্ক্রল করার সময় গাইডলাইনগুলো যাতে তাদের আসল ডম এলিমেন্টের সাথে নির্ভুলভাবে পিন্ড থাকে, এলিমেন্টগুলোর এজ (ধার) ও সেন্টারে ৬ পিক্সেলের ম্যাগনেটিক স্ন্যাপিং নিশ্চিত করা, এবং ব্রাউজার রিলোড করলেও ডোমেনভেদে গাইডগুলো স্বয়ংক্রিয়ভাবে রিকল করা।",
			'challenge_en'=> "During web UI development and design QA audits, verifying visual alignment, margin balances, and relative spacing across components conventionally requires capturing screenshots and measuring in Figma or Photoshop, breaking the developer flow.\n\nInjecting measurement overlays directly into arbitrary third-party pages poses significant technical hurdles: host page CSS resets and aggressive stylesheet inheritance bleed into the overlay, arbitrary z-indexes occlude ruler bars, vertical page scrolling displaces coordinates, and DOM shifts disrupt alignment. The challenge was building an isolated, zero-leak measurement overlay with magnetic edge-snapping and persistent document-space guides under Manifest V3.",
			'solution_bn' => "১. শ্যাডো ডম (Shadow DOM) আইসোলেশন: হোস্ট পেজের সিএসএস বা স্ক্রিপ্ট যাতে কোনোভাবেই এক্সটেনশনের স্টাইলকে প্রভাবিত করতে না পারে, সেজন্য সম্পূর্ণ রুলার ইন্টারফেস Shadow Root-এর ভেতরে সম্পূর্ণ বিচ্ছিন্নভাবে রেন্ডার করা হয়েছে।\n\n২. ফটোশপ স্টাইল ড্র্যাগ-অ্যান্ড-ড্রপ গাইড: ওপরের বা বামের রুলার থেকে মাউস ড্র্যাগ করে টানলেই তাত্ক্ষণিক অনুভূমিক বা উল্লম্ব গাইড তৈরি হয়। ড্র্যাগ করে সরানো, ডাবল ক্লিকে বা রুলারে ফেরত নিয়ে ডিলিট করা এবং হটকি (Alt+R, Ctrl+;, Ctrl+Alt+;) দিয়ে সহজে পরিচালনা করা যায়।\n\n৩. ম্যাগনেটিক স্ন্যাপিং ইঞ্জিন (Snap Engine): মাউস ড্র্যাগ করার সময় নিকটবর্তী ডম উপাদানের বাম, ডান, কেন্দ্র কিংবা শীর্ষ, তলদেশ ও মধ্যবিন্দুর ৬ পিক্সেলের মধ্যে ম্যাগনেটিক স্ন্যাপ করে (Shift কি চেপে স্ন্যাপ সাময়িক অফও রাখা যায়)।\n\n৪. Alt-কি ডাইমেনশন ও ডিস্ট্যান্স মেজারমেন্ট: Alt চেপে মাউস হোভার করলেই উপাদানের সাইজ ব্যাজ দেখা যায় এবং ড্র্যাগ করলে উপাদানগুলোর মধ্যকার রিয়েল-টাইম পিক্সেল দূরত্ব নিঁখুতভাবে ডিসপ্লে হয়।\n\n৫. ৩টি অ্যাডাপ্টিভ ডিসপ্লে মোড: ফ্লোটিং Overlay মোড ছাড়াও Push মোডে পেজ কনটেন্ট ২২ পিক্সেল নিচে নেমে আসে যাতে কোনো হেডার ঢাকা না পড়ে। আর Auto-hide মোডে মাউস স্ক্রিনের প্রান্তে গেলে রুলার মসৃণভাবে ভেসে ওঠে।\n\n৬. স্ক্রল-রেসিলিয়েন্ট পার-অরিজিন লোকাল স্টোরেজ: গাইডগুলো ডকুমেন্ট কোঅর্ডিনেটে কাজ করায় পেজ স্ক্রল করলেও উপাদানগুলোর সাথে আটকে থাকে। এছাড়া প্রতিটি ওয়েবসাইটের গাইড লোকাল মেমরিতে সংরক্ষিত থাকে, ফলে পেজ রিলোড করলেও গাইড অক্ষত থাকে।",
			'solution_en' => "1. Shadow DOM Encapsulation: Hosts all ruler tracks, guide markers, and HUD controls within a closed Shadow Root, entirely preventing host page CSS bleed, style pollution, and inheritance collisions.\n\n2. Native Photoshop-Style Guide Mechanics: Dragging down from the top bar or right from the left bar creates persistent guides. Supports real-time dragging, double-click deletion, ruler flick discarding, and ergonomic hotkeys (Alt+R, Ctrl+; for hide/show, Ctrl+Alt+; for locking).\n\n3. Spatial Snap Heuristics: Evaluates on-screen viewport elements in real time, snapping guides to element boundaries (left, right, center, top, bottom, middle) within a 6px threshold, bypassable on-the-fly via Shift key.\n\n4. Alt-Key Distance & Dimension Measurement: Pressing Alt inspects hovered element bounds with live dimension badges, while dragging casts relative distance readouts between disparate page components.\n\n5. Three Adaptable View Modes: Supports default Floating Overlay, Content Push (offsetting document content by 22px to prevent header obstruction), and Edge Proximity Auto-hide (revealing rulers smoothly on hover near viewport boundaries).\n\n6. Document-Pinned Per-Origin Persistence: Anchors guides to absolute document coordinates so markers stay attached during page scrolling, stored locally per domain origin via chrome.storage.local with 100% offline security.",
			'highlights_bn'=> array(
				'ফটোশপ ধাঁচের ড্র্যাগ-অ্যান্ড-ড্রপ অনুভূমিক ও উল্লম্ব গাইডলাইন',
				'নিকটবর্তী ডম উপাদানের এজ ও সেন্টারে ৬px ম্যাগনেটিক স্ন্যাপিং',
				'Alt কি চেপে ইনস্ট্যান্ট এলিমেন্ট ডাইমেনশন ও পিক্সেল দূরত্ব পরিমাপ',
				'শ্যাডো ডম (Shadow DOM) দিয়ে মূল সাইটের সিএসএস থেকে ১০০% বিচ্ছিন্ন',
				'৩টি ডিসপ্লে মোড: ফ্লোটিং ওভারলে, কনটেন্ট পুশ (Push) এবং অটো-হাইড (Auto-hide)',
				'স্ক্রল-রেসিলিয়েন্ট ডকুমেন্ট কোঅর্ডিনেট ও পার-ডোমেইন অটো-সেভ',
				'১০০% অফলাইন ও ব্যক্তিগত গোপনীয়তা রক্ষা — কোনো দূরবর্তী সার্ভার কল নেই'
			),
			'highlights_en'=> array(
				'Photoshop-style draggable horizontal and vertical guide overlays',
				'Smart magnetic snapping to element boundaries and centers within 6px',
				'Alt-key real-time element dimension inspection and distance readout',
				'Encapsulated inside Shadow DOM preventing host stylesheet conflicts',
				'3 flexible display modes: Overlay, Content Push, and Edge Auto-hide',
				'Scroll-persistent document coordinates with per-origin local storage',
				'Zero analytics or network calls: 100% private client-side execution'
			),
			'tags'        => array( 'Chrome Extension', 'Manifest V3', 'Shadow DOM', 'DOM Snapping', 'UI/UX Measurement' ),
			'accent'      => '#0984e3',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/rulerforbrowser.png',
			'image_fit'   => 'contain',
			'action_type' => 'code',
			'action_bn'   => 'সোর্স কোড (GitHub)',
			'action_en'   => 'View Source on GitHub',
			'direct_url'  => 'https://github.com/raisulsohan/RulerForBrowser',
			'github_url'  => 'https://github.com/raisulsohan/RulerForBrowser',
			'order'       => 70,
		),
		array(
			'id'          => 'lazy-image-ae',
			'category'    => 'tools',
			'type_bn'     => 'আফটার ইফেক্টস এক্সটেনশন ও ব্রিজ',
			'type_en'     => 'After Effects CEP & Chrome Bridge',
			'badge_bn'    => 'Adobe CEP • AI ওয়ার্কফ্লো',
			'badge_en'    => 'Adobe CEP • AI Motion Workflow',
			'title_bn'    => 'Lazy-Image — আফটার ইফেক্টস নেটিভ AI ইমেজ জেনারেটর',
			'title_en'    => 'Lazy-Image — Native AI Image Generation Inside Adobe After Effects',
			'summary_bn'  => 'কোনো API ফি বা অতিরিক্ত খরচ ছাড়া ব্রাউজারের সক্রিয় ChatGPT সেশন ব্যবহার করে সরাসরি আফটার ইফেক্টস প্যানেলে এআই ছবি তৈরি, প্রজেক্ট ফোল্ডারে সেভ এবং স্বয়ংক্রিয়ভাবে অ্যাক্টিভ কম্পোজিশন টাইমলাইনে প্লেহেডে লেয়ার হিসেবে ইনসার্ট করার অ্যাডোবি CEP এক্সটেনশন।',
			'summary_en'  => 'An Adobe After Effects CEP extension and Chrome companion bridge enabling zero-cost AI image generation directly inside After Effects via your active ChatGPT session—saving assets to your project directory and auto-placing them onto the active timeline at the playhead.',
			'role_bn'     => 'একক সিস্টেম আর্কিটেক্ট ও ক্রিয়েটিভ টুলস ইঞ্জিনিয়ার',
			'role_en'     => 'Solo System Architect & Creative Tools Engineer',
			'context_bn'  => 'অ্যাডোবি আফটার ইফেক্টস প্লাগইন • CEP + ExtendScript + MV3',
			'context_en'  => 'Adobe After Effects Extension • CEP + ExtendScript + MV3',
			'challenge_bn'=> "মোশন ডিজাইনার ও ভিজ্যুয়াল আর্টিস্টদের কনসেপ্ট আর্ট, ব্যাকগ্রাউন্ড কিংবা টেক্সচার তৈরির জন্য প্রতিনিয়ত ব্রাউজারে মিডজার্নি বা চ্যাটজিপিটিতে প্রম্পট দিয়ে ছবি তৈরি করতে হয়, তা ডাউনলোড করে ফোল্ডার খুঁজে আফটার ইফেক্টসের প্রজেক্ট বিনে ইমপোর্ট করতে হয় এবং সেখান থেকে ড্র্যাগ করে কম্পোজিশন টাইমলাইনের সঠিক সময়ে বসাতে হয়। বারবার উইন্ডো পরিবর্তন এবং ম্যানুয়াল ফাইল হ্যান্ডলিং কাজের গতি মারাত্মকভাবে ধীর করে দেয়। তাছাড়া অফিসিয়াল API ব্যবহার করতে গেলে অতিরিক্ত সাবস্ক্রিপশন ও পেইড টোকেন খরচ হয়।\n\nকারিগরি দিক থেকে প্রধান চ্যালেঞ্জ ছিল তিনটি ভিন্ন রানটাইম আর্কিটেকচারের মধ্যে নিরবচ্ছিন্ন ডেটা ও কন্ট্রোল পাইপলাইন প্রতিষ্ঠা করা: আফটার ইফেক্টসের ভেতরের ক্রোমিয়াম-বেসড CEP প্যানেল (Node.js), ব্যবহারকারীর ক্রোম ব্রাউজারের Manifest V3 সার্ভিস ওয়ার্কার এবং অ্যাডোবি আফটার ইফেক্টসের অভ্যন্তরীণ ExtendScript (C++) ইঞ্জিন।",
			'challenge_en'=> "Motion designers and visual effects artists routinely need conceptual backgrounds, storyboards, and texture assets during editing. The standard workflow demands juggling browser tabs, generating imagery via separate web interfaces, downloading files, locating them on disk, importing them into the After Effects project bin, and manually dragging them onto the timeline at the playhead. This repetitive context-switching cripples creative momentum, while official commercial APIs introduce recurring per-token billing.\n\nThe engineering challenge was bridging three isolated runtime environments: Adobe's internal CEP Chromium panel (with Node.js disk access), a Chromium Manifest V3 service worker executing in the user's personal browser with an active ChatGPT session, and the native Adobe ExtendScript C++ scripting engine driving the After Effects project timeline.",
			'solution_bn' => "১. ট্রাই-লেয়ার মাইক্রো-সার্ভিস আর্কিটেকচার: আফটার ইফেক্টসের ভেতরের CEP প্যানেলে একটি সুরক্ষিত লোকাল লুপব্যাক HTTP সার্ভার (127.0.0.1:7890) রান করে। ক্রোম এক্সটেনশনের ব্যাকগ্রাউন্ড সার্ভিস ওয়ার্কার এই লোকাল সার্ভারের সাথে যোগাযোগ করে ব্রাউজারে সক্রিয় ChatGPT ট্যাবে প্রম্পট ও অ্যাসপেক্ট রেশিও পাঠায় এবং ডম অটোমেশনের মাধ্যমে উচ্চমানের ইমেজ স্ট্রিম সংগ্রহ করে।\n\n২. স্বয়ংক্রিয় টাইমলাইন ইনসার্শন (ExtendScript Automation): ছবি জেনারেট হওয়ামাত্রই ExtendScript ইঞ্জিনের মাধ্যমে আফটার ইফেক্টসে সক্রিয় ওপেন কম্পোজিশনের বর্তমান প্লেহেড পজিশনে (comp.time) লেয়ার হিসেবে স্বয়ংক্রিয়ভাবে প্লেস করা হয়—ব্যবহারকারীকে কোনো ম্যানুয়াল ড্র্যাগ-অ্যান্ড-ড্রপ করতে হয় না।\n\n৩. স্মার্ট প্রজেক্ট ফাইল অর্গানাইজেশন: এক্সটেনশনটি স্বয়ংক্রিয়ভাবে আফটার ইফেক্টসের সেভ করা .aep প্রজেক্ট ফোল্ডার শনাক্ত করে এবং তার ভেতরে একটি সুসজ্জিত AI_Generated সাবফোল্ডার তৈরি করে ছবিগুলো স্বয়ংক্রিয়ভাবে সংরক্ষণ করে (প্রজেক্ট সেভ না থাকলে নিরাপদে ডকুমেন্টস ফোল্ডারে ব্যাকআপ রাখে)।\n\n৪. অ্যাসপেক্ট রেশিও প্রিসেট ও মাল্টি-ল্যাঙ্গুয়েজ প্রম্পট: ওয়ান-ক্লিকে ১:১, ১৬:৯, ৯:১৬ এবং ৪:৫ ছাড়াও কাস্টম রেজোলিউশন সাপোর্ট। ইউনিকোড এনকোডিংয়ের ফলে বাংলা, ইংরেজি সহ যেকোনো ভাষায় প্রম্পট দেওয়া যায়।\n\n৫. নো-এপিআই কস্ট জিরো-বিলিং: কোনো পেইড API কি বা ক্রেডিট ছাড়াই ইউজারের নিজস্ব ক্রোম ব্রাউজারের ফ্রি কিংবা প্লাস চ্যাটজিপিটি লগইন সেশন কাজে লাগিয়ে নিখরচায় ছবি তৈরি করা যায়।\n\n৬. ওয়ান-ক্লিক অটো-ইনস্টলার: রেজিস্ট্রিতে PlayerDebugMode সক্রিয় করা এবং উইন্ডোজ সিম্বলিক লিঙ্কের মাধ্যমে আফটার ইফেক্টস এক্সটেনশন ডিরেক্টরিতে প্লাগইন লিংক করার জন্য একটি স্বয়ংক্রিয় .bat স্ক্রিপ্ট অন্তর্ভুক্ত রয়েছে।",
			'solution_en' => "1. Tri-Layer Micro-Service Architecture: The CEP panel runs an internal Node.js loopback HTTP server on 127.0.0.1:7890. A companion Manifest V3 Chrome extension polls tasks, injects prompts and aspect ratios into an active chatgpt.com session via DOM automation, and streams the high-resolution image back as base64.\n\n2. Automatic Timeline Injection (ExtendScript): Once retrieved, the Node.js layer writes the asset to disk, invokes the After Effects ExtendScript bridge (host/index.jsx), imports the footage into the project bin, and automatically creates a new layer on the active composition timeline precisely at the current playhead position (comp.time).\n\n3. Intelligent Project File Discovery: Automatically resolves the file path of the currently open .aep project and organizes generated imagery neatly inside a dedicated <ProjectDir>/AI_Generated/ subfolder (with fallback to Documents/GImage_Generated/).\n\n4. Preset Aspect Ratios & Full Unicode Prompts: Instant one-click selection for 1:1, 16:9, 9:16, 4:5 ratios plus custom dimension inputs, fully supporting multi-language prompts (Bengali, English, etc.) without character corruption.\n\n5. Zero API Costs: Operates without API key subscriptions or per-generation fees by securely tapping into the user's authorized ChatGPT browser session in Chrome.\n\n6. 1-Click Automated Windows Installer: Includes an install.bat utility enabling Adobe CEP PlayerDebugMode in the Windows registry and symlinking the bundle into %APPDATA%\\Adobe\\CEP\\extensions\\ for effortless zero-configuration setup.",
			'highlights_bn'=> array(
				'সরাসরি আফটার ইফেক্টস প্যানেল থেকে ওয়ান-ক্লিকে AI ইমেজ জেনারেট',
				'স্বয়ংক্রিয়ভাবে কম্পোজিশন টাইমলাইনে প্লেহেড পজিশনে লেয়ার ইনসার্ট',
				'কোনো পেইড API কি বা অতিরিক্ত খরচ নেই—বিদ্যমান চ্যাটজিপিটি ব্রাউজার সেশনে সক্রিয়',
				'স্মার্ট .aep প্রজেক্ট ফোল্ডার ডিটেকশন ও ডেডিকেটেড AI_Generated ডিরেক্টরি',
				'১:১, ১৬:৯, ৯:১৬, ৪:৫ অ্যাসপেক্ট রেশিও এবং কাস্টম রেজোলিউশন প্রিসেট',
				'ইউনিকোড ফুল সাপোর্ট: বাংলা বা যেকোনো ভাষায় প্রম্পট দেওয়ার সুবিধা',
				'উইন্ডোজের জন্য ওয়ান-ক্লিক অটো-ইনস্টলার স্ক্রিপ্ট (PlayerDebugMode ও সিমলিঙ্ক)'
			),
			'highlights_en'=> array(
				'Native Adobe After Effects panel for prompt-based AI image generation',
				'Instant auto-placement on active composition timeline at the current playhead',
				'Zero API subscription or credit billing—connects to active browser ChatGPT session',
				'Automated .aep project directory discovery with structured AI_Generated asset bins',
				'One-click aspect ratio presets: 1:1, 16:9, 9:16, 4:5 and custom resolutions',
				'Full Unicode prompt support: seamlessly accepts Bengali, English, and other languages',
				'Streamlined 1-click Windows installer automating registry debug flags and symlinks'
			),
			'tags'        => array( 'Adobe CEP', 'After Effects', 'ExtendScript', 'Chrome Extension', 'AI Automation', 'Node.js' ),
			'accent'      => '#6c5ce7',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/lazyimage.png',
			'image_fit'   => 'cover',
			'action_type' => 'code',
			'action_bn'   => 'সোর্স কোড (GitHub)',
			'action_en'   => 'View Source on GitHub',
			'direct_url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
			'github_url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
			'order'       => 80,
		),
	);
}

/**
 * 9. Auto-Seed Initial Authentic Projects into Database
 *
 * Runs once on admin_init on the main site so the user immediately
 * sees and can edit all 8 authentic projects from WP Admin > Portfolio.
 */
function rs_seed_initial_portfolio_projects() {
	if ( ! is_admin() ) {
		return;
	}

	if ( is_multisite() && ! is_main_site() ) {
		return;
	}

	if ( get_option( 'rs_portfolio_seeded_v1' ) ) {
		return;
	}

	// Check if any portfolio post already exists to prevent duplicate seeding
	$existing = get_posts( array(
		'post_type'      => 'rs_portfolio',
		'posts_per_page' => 1,
		'post_status'    => 'any',
	) );

	if ( ! empty( $existing ) ) {
		update_option( 'rs_portfolio_seeded_v1', 1 );
		return;
	}

	$defaults = rs_get_default_portfolio_projects();
	foreach ( $defaults as $item ) {
		$post_id = wp_insert_post( array(
			'post_title'   => ! empty( $item['title_en'] ) ? $item['title_en'] : ( isset( $item['title_bn'] ) ? $item['title_bn'] : 'Portfolio Item' ),
			'post_name'    => sanitize_title( $item['id'] ),
			'post_type'    => 'rs_portfolio',
			'post_status'  => 'publish',
			'menu_order'   => isset( $item['order'] ) ? intval( $item['order'] ) : 0,
		) );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		// Update all meta fields
		update_post_meta( $post_id, '_rs_portfolio_category', isset( $item['category'] ) ? $item['category'] : 'web' );
		update_post_meta( $post_id, '_rs_portfolio_type_bn', isset( $item['type_bn'] ) ? $item['type_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_type_en', isset( $item['type_en'] ) ? $item['type_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_badge_bn', isset( $item['badge_bn'] ) ? $item['badge_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_badge_en', isset( $item['badge_en'] ) ? $item['badge_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_title_bn', isset( $item['title_bn'] ) ? $item['title_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_title_en', isset( $item['title_en'] ) ? $item['title_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_summary_bn', isset( $item['summary_bn'] ) ? $item['summary_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_summary_en', isset( $item['summary_en'] ) ? $item['summary_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_role_bn', isset( $item['role_bn'] ) ? $item['role_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_role_en', isset( $item['role_en'] ) ? $item['role_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_context_bn', isset( $item['context_bn'] ) ? $item['context_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_context_en', isset( $item['context_en'] ) ? $item['context_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_challenge_bn', isset( $item['challenge_bn'] ) ? $item['challenge_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_challenge_en', isset( $item['challenge_en'] ) ? $item['challenge_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_solution_bn', isset( $item['solution_bn'] ) ? $item['solution_bn'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_solution_en', isset( $item['solution_en'] ) ? $item['solution_en'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_highlights_bn', isset( $item['highlights_bn'] ) && is_array( $item['highlights_bn'] ) ? $item['highlights_bn'] : array() );
		update_post_meta( $post_id, '_rs_portfolio_highlights_en', isset( $item['highlights_en'] ) && is_array( $item['highlights_en'] ) ? $item['highlights_en'] : array() );
		update_post_meta( $post_id, '_rs_portfolio_tags', ! empty( $item['tags'] ) ? implode( ', ', $item['tags'] ) : '' );
		update_post_meta( $post_id, '_rs_portfolio_accent', isset( $item['accent'] ) ? $item['accent'] : '#0984e3' );
		update_post_meta( $post_id, '_rs_portfolio_icon', isset( $item['icon'] ) ? $item['icon'] : 'code' );
		update_post_meta( $post_id, '_rs_portfolio_image', isset( $item['image'] ) ? $item['image'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_image_fit', isset( $item['image_fit'] ) ? $item['image_fit'] : 'cover' );
		update_post_meta( $post_id, '_rs_portfolio_action_type', isset( $item['action_type'] ) ? $item['action_type'] : 'web' );
		update_post_meta( $post_id, '_rs_portfolio_action_bn', isset( $item['action_bn'] ) ? $item['action_bn'] : 'বিস্তারিত দেখুন' );
		update_post_meta( $post_id, '_rs_portfolio_action_en', isset( $item['action_en'] ) ? $item['action_en'] : 'View Details' );
		update_post_meta( $post_id, '_rs_portfolio_direct_url', isset( $item['direct_url'] ) ? $item['direct_url'] : '' );
		update_post_meta( $post_id, '_rs_portfolio_github_url', isset( $item['github_url'] ) ? $item['github_url'] : '' );
	}

	update_option( 'rs_portfolio_seeded_v1', 1 );
}
add_action( 'admin_init', 'rs_seed_initial_portfolio_projects' );
