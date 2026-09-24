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
	$before_img   = get_post_meta( $post->ID, '_rs_portfolio_before', true );
	$after_img    = get_post_meta( $post->ID, '_rs_portfolio_after', true );
	$demo_url     = get_post_meta( $post->ID, '_rs_portfolio_demo', true );
	$demo_tall    = get_post_meta( $post->ID, '_rs_portfolio_demo_tall', true );
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

		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_before">Before image URL (আগের ছবি, ঐচ্ছিক)</label>
				<input type="url" name="rs_portfolio_before" id="rs_portfolio_before" value="<?php echo esc_attr( $before_img ); ?>" placeholder="https://..." />
			</div>
			<div class="rs-meta-field">
				<label for="rs_portfolio_after">After image URL (পরের ছবি, ঐচ্ছিক)</label>
				<input type="url" name="rs_portfolio_after" id="rs_portfolio_after" value="<?php echo esc_attr( $after_img ); ?>" placeholder="https://..." />
			</div>
		</div>
		<p class="description">দুটো ছবিই দিলে কেস স্টাডিতে টেনে তুলনা করার Before/After স্লাইডার দেখাবে। একই মাপের ছবি দিন, Media Library থেকে ছবির URL কপি করে বসাতে পারেন।</p>
		<div class="rs-meta-grid">
			<div class="rs-meta-field">
				<label for="rs_portfolio_demo">Demo URL, landscape 16:9 (ডেমো, ঐচ্ছিক)</label>
				<input type="url" name="rs_portfolio_demo" id="rs_portfolio_demo" value="<?php echo esc_attr( $demo_url ); ?>" placeholder="https://.../demo.html" />
			</div>
			<div class="rs-meta-field">
				<label for="rs_portfolio_demo_tall">Demo URL, portrait 9:16 for phones (মোবাইলের ডেমো, ঐচ্ছিক)</label>
				<input type="url" name="rs_portfolio_demo_tall" id="rs_portfolio_demo_tall" value="<?php echo esc_attr( $demo_tall ); ?>" placeholder="https://.../demo-vertical.html" />
			</div>
		</div>
		<p class="description">ডেমো দিলে কার্ড, কেস স্টাডি আর প্রজেক্টের পাতায় "Demo" বাটন আসবে, চাপলে ডেমোটা লুপে চলবে। খাড়া স্ক্রিনে (মোবাইলে) মোবাইলের ডেমো দেখাবে, না থাকলে আড়াআড়িটাই। ডেমো হবে এমন একটা HTML পাতা, যেটা নিজে থেকে চলে আর লুপ করে; থিমের assets/demo/ ফোল্ডারে রাখলে সবচেয়ে ভালো।</p>
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
		'rs_portfolio_before'     => '_rs_portfolio_before',
		'rs_portfolio_after'      => '_rs_portfolio_after',
		'rs_portfolio_demo'       => '_rs_portfolio_demo',
		'rs_portfolio_demo_tall'  => '_rs_portfolio_demo_tall',
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
		'rs_drag'     => '',
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
		case 'rs_drag':
			echo '<span class="rs-drag-handle dashicons dashicons-menu" title="ড্র্যাগ করে সিরিয়াল পরিবর্তন করুন"></span>';
			break;

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
 * 6b. Default admin list to menu_order ASC so drag-and-drop
 *     always reflects the live front-end order.
 */
function rs_portfolio_default_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-rs_portfolio' !== $screen->id ) {
		return;
	}

	/* Respect explicit column-header clicks (orderby query var). */
	if ( ! empty( $_GET['orderby'] ) ) {
		return;
	}

	$query->set( 'orderby', 'menu_order' );
	$query->set( 'order', 'ASC' );
}
add_action( 'pre_get_posts', 'rs_portfolio_default_admin_order' );

/**
 * 6c. Drag-and-Drop Reorder UI for Portfolio Admin List
 *
 * Enqueues jQuery UI Sortable + outputs inline CSS and a footer script
 * that makes rows draggable via the dedicated ≡ handle column.
 */
function rs_portfolio_reorder_assets( $hook ) {
	if ( 'edit.php' !== $hook ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-rs_portfolio' !== $screen->id ) {
		return;
	}

	/* Only enable when sorted by menu_order (the default). */
	if ( ! empty( $_GET['orderby'] ) && 'menu_order' !== $_GET['orderby'] ) {
		return;
	}

	wp_enqueue_script( 'jquery-ui-sortable' );

	/* --- Inline CSS --- */
	$css = '
		/* Drag handle column */
		.column-rs_drag { width: 28px; padding: 4px 0 !important; text-align: center; }
		.rs-drag-handle {
			cursor: grab; color: #8c8f94; font-size: 18px;
			vertical-align: middle; user-select: none;
		}
		.rs-drag-handle:hover { color: #2271b1; }
		.rs-drag-handle:active { cursor: grabbing; }

		/* Row being dragged */
		#the-list tr.rs-sortable-helper {
			background: #fff !important;
			box-shadow: 0 3px 12px rgba(0,0,0,.15);
			z-index: 999;
		}
		/* Drop placeholder */
		#the-list tr.rs-sortable-placeholder {
			visibility: visible !important;
			background: #f0f6fc !important;
		}
		#the-list tr.rs-sortable-placeholder td {
			border-top: 2px dashed #3582c4;
			border-bottom: 2px dashed #3582c4;
		}

		/* Reorder banner */
		.rs-reorder-banner {
			background: #f0f6fc; border-left: 4px solid #2271b1;
			padding: 8px 14px; margin: 10px 0 6px; font-size: 13px;
			display: flex; align-items: center; gap: 8px;
			border-radius: 0 3px 3px 0;
		}
		.rs-reorder-banner .dashicons { color: #2271b1; }

		/* Toast notification */
		.rs-reorder-toast {
			position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%);
			padding: 10px 22px; border-radius: 4px; font-size: 13px;
			z-index: 100001; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.18);
			opacity: 0; transition: opacity .3s;
		}
		.rs-reorder-toast.is-visible { opacity: 1; }
		.rs-reorder-toast--ok   { background: #00a32a; }
		.rs-reorder-toast--fail { background: #d63638; }
	';
	wp_add_inline_style( 'wp-admin', $css );

	/* Flag so the footer script knows to fire. */
	add_action( 'admin_footer', 'rs_portfolio_reorder_footer_script' );
}
add_action( 'admin_enqueue_scripts', 'rs_portfolio_reorder_assets' );

/**
 * Print the sortable JS at the very bottom of the page so the DOM
 * is guaranteed to exist. Using admin_footer instead of
 * wp_add_inline_script avoids timing issues with table rendering.
 */
function rs_portfolio_reorder_footer_script() {
	$nonce = wp_create_nonce( 'rs_portfolio_reorder' );
	?>
	<script>
	jQuery(function($){
		var $list = $('#the-list');
		if ( ! $list.length ) return;

		/* Instruction banner */
		$('.wp-list-table').before(
			'<div class="rs-reorder-banner">' +
			'<span class="dashicons dashicons-move"></span>' +
			'<span>প্রজেক্ট সিরিয়াল পরিবর্তন করতে ≡ আইকন ধরে টেনে উপরে বা নিচে ছেড়ে দিন।</span>' +
			'</div>'
		);

		/* Toast helper */
		function toast(msg, ok) {
			var $t = $('<div class="rs-reorder-toast ' + (ok ? 'rs-reorder-toast--ok' : 'rs-reorder-toast--fail') + '">' + msg + '</div>');
			$('body').append($t);
			setTimeout(function(){ $t.addClass('is-visible'); }, 30);
			setTimeout(function(){ $t.removeClass('is-visible'); setTimeout(function(){ $t.remove(); }, 400); }, 2400);
		}

		/* Disable text selection on the table so drag is not blocked */
		$list.disableSelection();

		/* Initialize sortable */
		$list.sortable({
			items:       '> tr',
			handle:      '.rs-drag-handle',
			axis:        'y',
			tolerance:   'pointer',
			cursor:      'grabbing',
			distance:    3,
			placeholder: 'rs-sortable-placeholder',

			/* Preserve column widths while the row is detached from the table */
			helper: function( e, tr ) {
				var $origCells = tr.children();
				var $helper    = tr.clone();
				$helper.addClass('rs-sortable-helper');
				$helper.children().each(function( i ) {
					$(this).width( $origCells.eq( i ).outerWidth() );
				});
				return $helper;
			},
			/* Keep the original row's widths too (prevents column collapse) */
			start: function( e, ui ) {
				ui.item.children().each(function() {
					$(this).width( $(this).width() );
				});
			},
			/* Remove inline widths after sort */
			stop: function( e, ui ) {
				ui.item.children().css('width', '');
			},

			update: function() {
				var order = [];
				$list.children('tr').each(function(){
					var id = $(this).attr('id');
					if ( id ) order.push( id.replace('post-', '') );
				});

				$.post( ajaxurl, {
					action:   'rs_portfolio_reorder',
					_wpnonce: '<?php echo esc_js( $nonce ); ?>',
					order:    order
				}, function( r ) {
					if ( r.success ) {
						toast('✓ সিরিয়াল সেভ হয়েছে!', true);
						$list.children('tr').each(function( i ){
							$(this).find('.column-menu_order').text( i + 1 );
						});
					} else {
						toast('✕ সেভ ব্যর্থ হয়েছে', false);
					}
				}).fail(function(){
					toast('✕ সার্ভারে সমস্যা হয়েছে', false);
				});
			}
		});
	});
	</script>
	<?php
}

/**
 * 6d. AJAX Handler: Persist new project order after drag-and-drop.
 */
function rs_portfolio_reorder_ajax() {
	check_ajax_referer( 'rs_portfolio_reorder', '_wpnonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Unauthorized' );
	}

	/* An array, checked rather than assumed: jQuery sends order[] so this is
	   normally one, but array_map() on anything else is a fatal in PHP 8. */
	$order = isset( $_POST['order'] ) && is_array( $_POST['order'] )
		? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['order'] ) ) ) )
		: array();

	if ( empty( $order ) ) {
		wp_send_json_error( 'Empty order' );
	}

	global $wpdb;

	foreach ( $order as $position => $post_id ) {
		$wpdb->update(
			$wpdb->posts,
			array( 'menu_order' => $position + 1 ),
			array( 'ID' => $post_id, 'post_type' => 'rs_portfolio' ),
			array( '%d' ),
			array( '%d', '%s' )
		);

		/* Each row was changed behind the object cache's back, so each row is
		   what has to be forgotten. clean_post_cache( 0 ) stood here and did
		   nothing at all: get_post( 0 ) is null and the function returns at
		   once, so on a site with a persistent cache the old order survived
		   the drag until something else happened to flush it. */
		clean_post_cache( $post_id );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_rs_portfolio_reorder', 'rs_portfolio_reorder_ajax' );

/**
 * A theme file's address on this install, whatever domain or subsite it
 * was saved from.
 *
 * @param string $url URL.
 * @return string
 */
function rs_portfolio_theme_url( $url ) {
	return (string) preg_replace( '#^https?://[^/]+(?:/[a-z]{2})?/wp-content/themes/[^/]+/#i', get_template_directory_uri() . '/', (string) $url );
}

/**
 * A theme image with the theme version on it. Theme files are served with a
 * one-year immutable cache, so a redrawn image at the same address would
 * otherwise never reach anyone who has already seen the old one.
 *
 * @param string $url Image URL.
 * @return string
 */
function rs_portfolio_theme_asset( $url ) {
	$base = get_template_directory_uri() . '/';

	if ( '' === $url || 0 !== strpos( $url, $base ) || false !== strpos( $url, '?' ) ) {
		return $url;
	}

	return $url . '?ver=' . rawurlencode( RS_VERSION );
}

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
	if ( ! get_option( 'rs_portfolio_synced_lazyimage_v2_2' ) && function_exists( 'rs_sync_lazy_image_portfolio_v2' ) ) {
		rs_sync_lazy_image_portfolio_v2();
	}

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
				/* A theme asset seeded from the /en/ sub site was stored with that
				   site's prefix in the path; point it back at this site's theme. */
				'image'        => rs_portfolio_theme_asset( rs_portfolio_theme_url( get_post_meta( $p->ID, '_rs_portfolio_image', true ) ) ),
				'image_fit'    => get_post_meta( $p->ID, '_rs_portfolio_image_fit', true ) ?: 'cover',
				'action_type'  => get_post_meta( $p->ID, '_rs_portfolio_action_type', true ) ?: 'web',
				'action_bn'    => get_post_meta( $p->ID, '_rs_portfolio_action_bn', true ) ?: 'বিস্তারিত দেখুন',
				'action_en'    => get_post_meta( $p->ID, '_rs_portfolio_action_en', true ) ?: 'View Details',
				'direct_url'   => get_post_meta( $p->ID, '_rs_portfolio_direct_url', true ) ?: home_url( '/' ),
				'github_url'   => get_post_meta( $p->ID, '_rs_portfolio_github_url', true ) ?: '',
				'before'       => get_post_meta( $p->ID, '_rs_portfolio_before', true ) ?: '',
				'after'        => get_post_meta( $p->ID, '_rs_portfolio_after', true ) ?: '',
				'demo'         => rs_portfolio_theme_asset( rs_portfolio_theme_url( get_post_meta( $p->ID, '_rs_portfolio_demo', true ) ) ),
				'demo_tall'    => rs_portfolio_theme_asset( rs_portfolio_theme_url( get_post_meta( $p->ID, '_rs_portfolio_demo_tall', true ) ) ),
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
			'type_bn'     => 'অ্যাডোবি CEP এক্সটেনশন ও CDP ব্রাউজার অটোমেশন',
			'type_en'     => 'Adobe CEP Extension & CDP Browser Automation',
			'badge_bn'    => 'Adobe CEP • AE ও Premiere Pro',
			'badge_en'    => 'Adobe CEP • AE & Premiere Pro',
			'title_bn'    => 'Lazy-Image — আফটার ইফেক্টস ও প্রিমিয়ার প্রো নেটিভ AI ইমেজ জেনারেটর',
			'title_en'    => 'Lazy-Image — Native AI Image Generation Inside Adobe After Effects & Premiere Pro',
			'summary_bn'  => 'কোনো পেইড API সাবস্ক্রিপশন বা ব্রাউজার এক্সটেনশন ছাড়া Chrome DevTools Protocol (CDP)-এর মাধ্যমে ব্যাকগ্রাউন্ডে ইনভিজিবল ব্রাউজার রান করে সরাসরি অ্যাডোবি আফটার ইফেক্টস এবং প্রিমিয়ার প্রো টাইমলাইনে প্লেহেডে এআই ছবি তৈরি ও স্বয়ংক্রিয়ভাবে লেয়ার/ক্লিপ হিসেবে ইনসার্ট করার অ্যাডোবি CEP এক্সটেনশন।',
			'summary_en'  => 'A native Adobe CEP extension for After Effects and Premiere Pro enabling zero-cost AI image generation directly onto active timelines via Chrome DevTools Protocol (CDP) browser automation—requiring no API keys, no browser extensions, and running an invisible on-demand browser session.',
			'role_bn'     => 'একক সিস্টেম আর্কিটেক্ট ও ক্রিয়েটিভ টুলস ইঞ্জিনিয়ার',
			'role_en'     => 'Solo System Architect & Creative Tools Engineer',
			'context_bn'  => 'অ্যাডোবি CEP প্লাগইন • Chrome DevTools Protocol (CDP) + ExtendScript + Node.js',
			'context_en'  => 'Adobe CEP Extension • Chrome DevTools Protocol (CDP) + ExtendScript + Node.js',
			'challenge_bn'=> "মোশন ডিজাইনার, ভিডিও এডিটর এবং ভিজ্যুয়াল আর্টিস্টদের দৈনন্দিন প্রোডাকশনে স্টোরিবোর্ড, ব্যাকগ্রাউন্ড টেক্সচার, কনসেপ্ট আর্ট কিংবা বি-রোল গ্রাফিক্সের জন্য ঘন ঘন এআই ইমেজ জেনারেট করতে হয়। প্রচলিত পদ্ধতিতে এডিটরকে আফটার ইফেক্টস বা প্রিমিয়ার প্রো ছেড়ে ব্রাউজারে মিডজার্নি বা চ্যাটজিপিটি ট্যাবে যেতে হয়, প্রম্পট লিখে অপেক্ষা করতে হয়, ইমেজ লোকাল ড্রাইভে ডাউনলোড করতে হয়, প্রজেক্ট বিনে ইমপোর্ট করে টাইমলাইনে প্লেহেড খুঁজে ম্যানুয়ালি ট্র্যাক বা লেয়ারে ড্র্যাগ-অ্যান্ড-ড্রপ করতে হয়। বারবার এই উইন্ডো স্যুইচিং কাজের রিদম ও ক্রিয়েটিভ ফ্লো মারাত্মকভাবে নষ্ট করে। তাছাড়া অফিসিয়াল ওপেনএআই বা মিডজার্নি API ব্যবহার করতে গেলে প্রতি ইমেজে অতিরিক্ত টোকেন বিলিং ও পেইড সাবস্ক্রিপশনের বোঝা তৈরি হয়।\n\nআর্কিটেকচারাল চ্যালেঞ্জসমূহ:\n১. ব্রাউজার এক্সটেনশন নির্ভরতা দূরীকরণ: ১.x সংস্করণে একটি লোকাল লুপব্যাক HTTP সার্ভার ও ক্রোম এক্সটেনশন ব্রিজ ব্যবহার করা হয়েছিল; যা ব্যবহারকারীর জন্য দুটি আলাদা উপাদান ইনস্টল ও কনফিগার করার ঝামেলা তৈরি করত। মূল লক্ষ্য ছিল কোনো ব্রাউজার এক্সটেনশন ছাড়াই ব্যবহারকারীর উইন্ডোজ ডিফল্ট ক্রোমিয়াম ব্রাউজারকে (Chrome, Edge, Brave, Vivaldi) সরাসরি অটোমেট করা।\n২. ডুয়াল হোস্ট টাইমলাইন অটোমেশন (After Effects ও Premiere Pro): আফটার ইফেক্টস এবং প্রিমিয়ার প্রোর ExtendScript ইঞ্জিন সম্পূর্ণ ভিন্ন। আফটার ইফেক্টসে সক্রিয় কম্পোজিশনের বর্তমান প্লেহেডে (comp.time) নতুন ইমেজ লেয়ার ইনসার্ট করা আর প্রিমিয়ার প্রোতে বিদ্যমান কোনো সিকোয়েন্স ক্লিপ ওভাররাইট না করে প্লেহেডের ঠিক উপরের প্রথম ফাঁকা ভিডিও ট্র্যাকে (বা প্রয়োজনে নতুন ট্র্যাক ক্রিয়েট করে) ক্লিপ প্লেস করার ডায়নামিক অ্যালগরিদম প্রতিষ্ঠা করা।\n৩. ফোকাসহীন ইনভিজিবল এক্সিকিউশন ও সেশন নিরাপত্তা: ব্যাকগ্রাউন্ডে ব্রাউজার অটোমেশন চলার সময় এডিটরের কীবোর্ড ফোকাস কোনো অবস্থাতেই নষ্ট না হওয়া (Zero Focus Stealing) এবং ব্যবহারকারীর মূল ব্রাউজারের বুকমার্ক, হিস্ট্রি ও পাসওয়ার্ড সম্পূর্ণ অক্ষত রেখে একটি সংরক্ষিত ডেডিকেটেড সেশন প্রোফাইলে (%APPDATA%\\LazyImage) ChatGPT পরিচালনা করা।",
			'challenge_en'=> "Motion designers, video editors, and visual effects artists routinely need conceptual backgrounds, storyboards, textures, and b-roll graphic assets during post-production. The standard industry workflow forces creators into a frustrating loop: leaving the editor, juggling browser tabs in Midjourney or ChatGPT, waiting for renders, downloading files to disk, navigating local folders, importing footage into the project bin, and manually positioning clips onto the timeline. This repetitive context-switching cripples creative momentum. Furthermore, commercial generative APIs demand recurring per-token subscriptions and billing infrastructure that individual editors and small studios find prohibitive.\n\nTechnical & Architectural Challenges:\n1. Eliminating Browser Extension Dependencies: Version 1.x relied on a local loopback HTTP server and a companion Manifest V3 Chrome extension, introducing multi-step installation friction. The primary architectural objective was creating a standalone bridge capable of directly driving the user's native Windows default Chromium browser (Chrome, Edge, Brave, Vivaldi) without requiring any installed browser extensions.\n2. Dual Host Timeline Automation (After Effects & Premiere Pro): After Effects and Premiere Pro operate on fundamentally different ExtendScript object models. While AE requires instantiating footage items into active composition layers precisely at comp.time, Premiere Pro demands intelligent sequence-level track evaluation—placing clips onto the first unoccupied video track above existing footage at the playhead without overwriting any active timeline clips, and dynamically generating tracks when needed.\n3. Non-Intrusive Invisible Execution & Session Isolation: Running browser automation silently in the background without stealing keyboard focus from active timeline editing, while strictly isolating the ChatGPT session inside a dedicated profile (%APPDATA%\\LazyImage) to ensure the user's personal browser data, bookmarks, logins, and extensions remain untouched.",
			'solution_bn' => "১. ইনভিজিবল সিডিপি ব্রাউজার ইঞ্জিন (Chrome DevTools Protocol): v2.0+ আর্কিটেকচারে কোনো ব্রাউজার এক্সটেনশন ছাড়াই সরাসরি Chrome DevTools Protocol (CDP) WebSocket-এর মাধ্যমে উইন্ডোজের ডিফল্ট ব্রাউজার (Chrome, Edge, Brave, Vivaldi) নিয়ন্ত্রণ করা হয়। প্যানেলে প্রম্পট দিলে একটি সম্পূর্ণ গোপন (Hidden Window) ব্রাউজার প্রসেস রান করে, প্রম্পট ইনপুট দিয়ে ইমেজ সংগ্রহ করে এবং ইমেজ ডাউনলোড হওয়ামাত্রই স্বয়ংক্রিয়ভাবে ব্রাউজার প্রসেস বন্ধ করে দেয়। ফলে কোনো ব্যাকগ্রাউন্ড মেমোরি নষ্ট হয় না।\n\n২. ওয়ান-টাইম সিকিউর লগইন ও আইসোলেটেড প্রোফাইল: ব্যবহারকারীর সাধারণ ব্রাউজিং সুরক্ষিত রাখতে %APPDATA%\\LazyImage ডিরেক্টরিতে একটি সম্পূর্ণ আইসোলেটেড প্রোফাইল তৈরি হয়। প্রথমবারের মতো 'Login to ChatGPT' বাটনে ক্লিক করলে একটি স্বাভাবিক ব্রাউজার উইন্ডো খোলে এবং লগইন সম্পন্ন হওয়ামাত্র উইন্ডোটি স্বয়ংক্রিয়ভাবে বন্ধ হয়ে যায়। একবার লগইন করলে তা আফটার ইফেক্টস ও প্রিমিয়ার প্রো উভয় অ্যাপেই কার্যকর থাকে।\n\n৩. ডুয়াল ExtendScript টাইমলাইন অটোমেশন:\n- After Effects: সক্রিয় কম্পোজিশনের প্লেহেড পজিশনে (comp.time) স্বয়ংক্রিয়ভাবে নতুন লেয়ার হিসেবে ইমেজ যুক্ত হয় এবং প্রজেক্ট উইন্ডোতে ChatGptImages ফোল্ডারে সুসজ্জিত থাকে।\n- Premiere Pro: সক্রিয় সিকোয়েন্সে প্লেহেডের নিচে থাকা ক্লিপগুলো স্ক্যান করে প্রথম খালি ভিডিও ট্র্যাক (Free Video Track) নির্বাচন করে অথবা স্বয়ংক্রিয়ভাবে নতুন ভিডিও ট্র্যাক তৈরি করে ইমেজ প্লেস করে; ফলে কোনো বিদ্যমান ক্লিপ ওভাররাইট হওয়ার ঝুঁকি থাকে না।\n\n৪. স্মার্ট প্রজেক্ট ফোল্ডার অর্গানাইজেশন: এক্সটেনশনটি স্বয়ংক্রিয়ভাবে ওপেন থাকা .aep বা .prproj ফাইলের পাথ শনাক্ত করে এবং প্রজেক্ট ডিরেক্টরির ভেতরেই chatgptimages সাবফোল্ডার তৈরি করে সব ইমেজ সেভ করে (অসংরক্ষিত প্রজেক্টের ক্ষেত্রে Documents/chatgptimages/-এ ব্যাকআপ রাখে)। ফোল্ডার থেকে ফাইল ডিলিট হলে প্যানেলে 'Image removed' অ্যালার্ট দেখায়।\n\n৫. রিয়েল-টাইম প্রোগ্রেস পার্সেন্টেজ ও ক্যানসেল সাপোর্ট: ইমেজ তৈরি হওয়ার সময় প্যানেলে এস্টিমেটেড প্রোগ্রেস পার্সেন্টেজ (%) প্রদর্শিত হয় এবং প্রয়োজন অনুযায়ী এক ক্লিকে রানিং প্রসেস বন্ধ করার জন্য ইনস্ট্যান্ট Cancel বাটন সংযুক্ত রয়েছে।\n\n৬. ওয়ান-ক্লিক কুইক অ্যাকশন ইউটিলিটি: জেনারেট হওয়া হাই-রেজোলিউশন ইমেজ তাৎক্ষণিকভাবে উইন্ডোজ ক্লিপবোর্ডে কপি করার জন্য 'Copy Image' বাটন এবং প্রজেক্টের ইমেজ ফোল্ডার সরাসরি উইন্ডোজ এক্সপ্লোরারে খোলার জন্য 'Open Folder' বাটন রয়েছে।\n\n৭. ইউনিভার্সাল ইউনিকোড ও অ্যাসপেক্ট রেশিও কন্ট্রোল: পূর্ণাঙ্গ UTF-8 ইউনিকোড সাপোর্টের মাধ্যমে বাংলা, ইংরেজি সহ যেকোনো ভাষায় বিস্তারিত প্রম্পট লেখা যায়। ১:১, ১৬:৯, ৯:১৬, ৪:৫ প্রিসেট ছাড়াও কাস্টম ওয়াইড/হাইট (W:H) রেজোলিউশন নিয়ন্ত্রণ করা যায়।\n\n৮. ওয়ান-ক্লিক অটোমেটেড উইন্ডোজ ইনস্টলার: install.bat স্ক্রিপ্টের মাধ্যমে উইন্ডোজ রেজিস্ট্রিতে অ্যাডোবি CEP PlayerDebugMode সক্রিয় করা এবং %APPDATA%\\Adobe\\CEP\\extensions\\ ডিরেক্টরিতে সিম্বলিক লিঙ্ক তৈরি করে সম্পূর্ণ জিরো-কনফিগারেশন ইনস্টলেশন নিশ্চিত করা হয়েছে।",
			'solution_en' => "1. Invisible CDP Browser Engine (Chrome DevTools Protocol): Replaced legacy browser extensions with native Chrome DevTools Protocol (CDP) WebSocket communication. When a generation is triggered, an on-demand Chromium browser process (Chrome, Edge, Brave, or Vivaldi) launches in an invisible window, navigates to the active session, types the prompt and aspect ratio, captures the generated high-resolution asset stream via Base64, and cleanly shuts down the browser process. Zero background memory footprint when idle.\n\n2. Isolated User Profile & One-Time Authentication: Maintains a dedicated profile in %APPDATA%\\LazyImage (e.g., ChromeProfile or EdgeProfile), guaranteeing that the user's everyday browsing history, passwords, and extensions remain untouched. Users log in once through a normal browser popup that closes itself upon verification; the authenticated state is shared across both After Effects and Premiere Pro.\n\n3. Cross-Host Timeline Placement (ExtendScript):\n- After Effects: Reads active composition dimensions and playhead timestamp (comp.time), automatically creating a new visual layer and neatly organizing project bin footage inside a dedicated ChatGptImages folder.\n- Premiere Pro: Scans active sequence video tracks under the current playhead, evaluates track bounds to locate the first unoccupied video track above existing footage (or dynamically creates a new track), and drops the clip without destructive overwrites.\n\n4. Automated Project Directory Asset Management: Automatically resolves the parent directory of currently loaded .aep or .prproj projects, creating a local chatgptimages directory adjacent to project files. Monitors asset integrity and surfaces 'Image removed' notifications if an asset is deleted on disk.\n\n5. Estimated Progress Feedback & Cancellation: Features live progress percentage tracking during generation cycles and an instant Cancel button allowing editors to abort long-running prompts without freezing the host software.\n\n6. Native Quick Action Utilities: Built-in 'Copy Image' utility pipes the full-resolution graphic straight to the Windows OS clipboard via a lightweight native bridge, complemented by an 'Open Folder' shortcut that reveals the asset in Windows Explorer.\n\n7. Universal Unicode & Aspect Ratio Presets: Full UTF-8 multi-language support (Bengali, English, Arabic, Spanish, etc.) and instant aspect ratio toggles (1:1, 16:9, 9:16, 4:5) alongside custom width/height inputs.\n\n8. 1-Click Zero-Config Windows Installer: Streamlined install.bat utility configures Adobe CEP registry debug flags across CC 2019–2026 and establishes an instant symlink into %APPDATA%\\Adobe\\CEP\\extensions\\com.gimage.aftereffects.",
			'highlights_bn'=> array(
				'আফটার ইফেক্টস এবং প্রিমিয়ার প্রো—উভয় সফটওয়্যারের জন্য একক ইউনিফাইড অ্যাডোবি CEP এক্সটেনশন',
				'কোনো ব্রাউজার এক্সটেনশন ছাড়াই স্বয়ংক্রিয় Chrome DevTools Protocol (CDP) ইঞ্জিন',
				'কোনো পেইড API কি বা সাবস্ক্রিপশন চার্জ নেই—বিদ্যমান ফ্রি বা প্লাস চ্যাটজিপিটি অ্যাকাউন্টে সক্রিয়',
				'আফটার ইফেক্টসে প্লেহেড লেয়ার এবং প্রিমিয়ার প্রোতে নন-ডেস্ট্রাক্টিভ ফ্রি ভিডিও ট্র্যাকে স্বয়ংক্রিয় প্লেসমেন্ট',
				'সম্পূর্ণ ইনভিজিবল ও অন-ডিমান্ড এক্সিকিউশন: জেনারেশন শেষে স্বয়ংক্রিয় ব্রাউজার ক্লোজ ও জিরো ফোকাস স্টিলিং',
				'আইসোলেটেড প্রোফাইল (%APPDATA%\\LazyImage) ব্যবহারের ফলে ব্যক্তিগত ব্রাউজিং ও হিস্ট্রি সম্পূর্ণ অক্ষত',
				'.aep ও .prproj প্রজেক্টের পাশে স্বয়ংক্রিয় chatgptimages ফোল্ডার এবং প্রজেক্ট প্যানেলে ChatGptImages বিন',
				'রিয়েল-টাইম প্রোগ্রেস এস্টিমেশন (%) এবং যেকোনো সময় প্রসেস বন্ধ করার জন্য ইনস্ট্যান্ট ক্যানসেল বাটন',
				'ওয়ান-ক্লিক "Copy Image" (ক্লিপবোর্ড) এবং "Open Folder" (উইন্ডোজ এক্সপ্লোরার) ইউটিলিটি',
				'১:১, ১৬:৯, ৯:১৬, ৪:৫ অ্যাসপেক্ট রেশিও প্রিসেট এবং কাস্টম ডাইমেনশন সাপোর্ট',
				'সম্পূর্ণ ইউনিকোড সাপোর্ট: বাংলা সহ যেকোনো ভাষায় বিস্তারিত প্রম্পটিংয়ের সুবিধা',
				'উইন্ডোজের জন্য ওয়ান-ক্লিক অটো-ইনস্টলার স্ক্রিপ্ট (PlayerDebugMode ও সিমলিঙ্ক)'
			),
			'highlights_en'=> array(
				'Unified Adobe CEP panel supporting both Adobe After Effects and Adobe Premiere Pro (CC 2019-2026)',
				'Direct Chrome DevTools Protocol (CDP) WebSocket automation—eliminates browser extensions entirely',
				'Zero API cost or token subscriptions—operates directly through active ChatGPT Free/Plus/Pro accounts',
				'Intelligent timeline placement: AE playhead layer insertion and Premiere Pro non-destructive track placement',
				'Invisible on-demand execution: hidden browser launches per task, exits immediately, and never steals focus',
				'Isolated user profile in %APPDATA%\\LazyImage safeguarding personal browser logins, history, and bookmarks',
				'Smart project file discovery: automated chatgptimages disk folders and structured ChatGptImages project bins',
				'Live progress percentage feedback with instant task cancellation support',
				'Native quick actions: one-click "Copy Image" to clipboard and "Open Folder" in Windows Explorer',
				'One-click aspect ratio presets (1:1, 16:9, 9:16, 4:5) plus custom dimension controls',
				'Full Unicode UTF-8 multi-language support (Bengali, English, and beyond)',
				'1-Click automated Windows installer configuring registry debug keys and extensions symlink'
			),
			'tags'        => array( 'Adobe CEP', 'After Effects', 'Premiere Pro', 'CDP Automation', 'ExtendScript', 'AI Workflow', 'Node.js' ),
			'accent'      => '#6c5ce7',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/lazyimage-v2.png',
			'image_fit'   => 'cover',
			'action_type' => 'code',
			'action_bn'   => 'সোর্স কোড (GitHub)',
			'action_en'   => 'View Source on GitHub',
			'direct_url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
			'github_url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
			'order'       => 80,
		),
		array(
			'id'          => 'lazylord',
			'category'    => 'tools',
			'type_bn'     => 'অ্যাডোবি CEP প্যানেল ও Figma প্লাগইন',
			'type_en'     => 'Adobe CEP Panel & Figma Plugin',
			'badge_bn'    => 'ফ্রি ও ওপেন সোর্স • Overlord-এর বিকল্প',
			'badge_en'    => 'Free & Open Source • Overlord Alternative',
			'title_bn'    => 'LazyLord — Figma, Photoshop, Illustrator ও After Effects-এর মধ্যে আসল আর্টওয়ার্ক আদান-প্রদান',
			'title_en'    => 'LazyLord — Move Real Artwork Between Figma, Photoshop, Illustrator & After Effects',
			'summary_bn'  => 'Figma, Photoshop, Illustrator আর After Effects, যেকোনো অ্যাপ থেকে যেকোনো অ্যাপে ভেক্টর পাথ, এডিটযোগ্য লাইভ টেক্সট আর ছবি পাঠানোর ফ্রি, ওপেন সোর্স টুল। পেইড Overlord-এর পূর্ণ বিকল্প: কিছু সিলেক্ট করে Send চাপলেই অন্য অ্যাপে সেটা চ্যাপ্টা স্ক্রিনশট হয়ে নয়, আসল লেয়ার হয়ে তৈরি হয়। সব কাজ হয় ব্যবহারকারীর নিজের কম্পিউটারেই।',
			'summary_en'  => 'A free, open-source replacement for Overlord that moves vector paths, live editable text and images between Figma, Photoshop, Illustrator and After Effects, in every direction. Select something, press Send, and it is rebuilt natively in the other app as real layers rather than a flattened screenshot, entirely on your own machine.',
			'role_bn'     => 'একক ডেভেলপার, সিস্টেম আর্কিটেক্ট ও ডিজাইনার',
			'role_en'     => 'Solo Developer, System Architect & Designer',
			'context_bn'  => 'Adobe CEP + ExtendScript • Figma Plugin API • TypeScript • লোকাল WebSocket ব্রিজ',
			'context_en'  => 'Adobe CEP + ExtendScript • Figma Plugin API • TypeScript • Local WebSocket Bridge',
			'challenge_bn'=> "ডিজাইন আর মোশনের কাজে একটা অ্যাপ থেকে আরেকটায় আর্টওয়ার্ক নেওয়া মানেই পুরোনো চক্র: SVG এক্সপোর্ট, আবার ইমপোর্ট, গ্রেডিয়েন্ট চ্যাপ্টা হয়ে যাওয়া, ছবি হয়ে আসা টেক্সট আবার টাইপ করা, আর ডিজাইন বদলালেই পুরোটা আবার। এই কাজের জনপ্রিয় টুল Overlord পেইড, অথচ ফ্রিল্যান্সার আর ছোট স্টুডিওর কাছে সেটাই সবচেয়ে বড় বাধা।\n\nআর্কিটেকচারাল চ্যালেঞ্জ:\n১. চারটি ভিন্ন জগৎ: Figma-র Plugin API, আর Photoshop, Illustrator ও After Effects-এর তিনটি সম্পূর্ণ আলাদা ExtendScript অবজেক্ট মডেল। প্রতিটি অ্যাপ অন্য তিনটিতে পাঠাবে ও তিনটি থেকে নেবে, মোট বারোটি পথ; প্রতিটি পথের জন্য আলাদা কনভার্টার লিখলে তা রক্ষণাবেক্ষণের অযোগ্য হয়ে পড়ে।\n২. আসল আর্টওয়ার্ক: বেজিয়ে পাথ পাথ হিসেবেই, টেক্সট এডিটযোগ্য টেক্সট হিসেবেই, লেয়ারের কাঠামো ও অবস্থান ঠিক রেখে পৌঁছাতে হবে।\n৩. আপডেট মানে ধ্বংস নয়: ডিজাইন বদলে আবার পাঠালে আগে বানানো জিনিসটাই হালনাগাদ হবে, কিন্তু ব্যবহারকারী হাতে যা বদলেছেন তা নীরবে মুছে দেওয়া চলবে না।\n৪. গোপনীয়তা ও সহজ ইনস্টল: কোনো ক্লাউড, অ্যাকাউন্ট বা আপলোড নয়; অফলাইনেও চলবে। আর ব্যবহারকারীকে Node.js, এক্সটেনশন ম্যানেজার বা আলাদা সার্ভার চালাতে বলা যাবে না, অথচ Figma কোনো ইনস্টলারকে প্লাগইন যোগ করতে দেয় না।",
			'challenge_en'=> "Moving artwork between design and motion apps usually means the same loop: export an SVG, re-import it, watch gradients flatten, retype text that arrived as a picture, and repeat it all whenever the design changes. Overlord, the popular tool for this job, is paid, which is exactly the barrier for freelancers and small studios.\n\nArchitectural challenges:\n1. Four different worlds: Figma's Plugin API plus three entirely separate ExtendScript object models in Photoshop, Illustrator and After Effects. Every app has to send to and receive from the other three, twelve routes in all, and a converter per route would be unmaintainable.\n2. Real artwork: Bézier paths must stay paths, text must stay editable text, and layer structure and position must survive the trip.\n3. Updating without destroying: sending a changed design again should update what was built before, without silently overwriting edits the user made by hand.\n4. Privacy and zero-friction install: no cloud, no account, no uploads, working offline, and no Node.js, extension manager or separate server for the user to run, even though Figma lets no installer add a plugin.",
			'solution_bn' => "১. একটি সাধারণ মধ্যবর্তী ভাষা (IR): TypeScript-এ লেখা core প্যাকেজ আর্টওয়ার্ককে অ্যাপ-নিরপেক্ষ একটি বর্ণনায় রূপ দেয় (জ্যামিতি, SVG পাথ, প্রোটোকল)। প্রতিটি অ্যাপের একটি reader সেই বর্ণনা বানায় আর একটি writer তা থেকে নেটিভ লেয়ার তৈরি করে। বারোটি কনভার্টারের জায়গায় চারটি reader আর চারটি writer মিলেই বারোটি পথ।\n\n২. নিজের কম্পিউটারেই ব্রিজ: প্রথম যে LazyLord প্যানেল খোলা হয়, সেটাই নীরবে ws://localhost:7878-এ একটি রিলে চালায়; বাকি প্যানেল আর Figma প্লাগইন সেখানে যুক্ত হয়। কোনো আলাদা প্রোগ্রাম বা কনসোল উইন্ডো নেই, কিছুই কম্পিউটারের বাইরে যায় না।\n\n৩. জায়গায় থেকে আপডেট ও এডিট শনাক্তকরণ: আবার পাঠালে আগে তৈরি লেয়ারটি তার অবস্থান ও গ্রুপিং ঠিক রেখে বদলে যায়। কেউ হাতে সেই লেয়ার বদলে থাকলে ওভাররাইটের আগে জিজ্ঞেস করে।\n\n৪. লাইভ মোড: এক অ্যাপে কাজ করার সময় অন্য অ্যাপ সঙ্গে সঙ্গে হালনাগাদ হতে থাকে।\n\n৫. সৎ রিপোর্ট: কোনো অ্যাপ যা পুনর্গঠন করতে পারে না (যেমন After Effects-এ inner shadow নেই), তা নীরবে বাদ না দিয়ে ট্রান্সফারের সঙ্গে তালিকা করে দেখায়।\n\n৬. Figma-র বাড়তি সুবিধা: ১x থেকে ৪x ইমেজ স্কেল, জটিল লেয়ারকে ছবি হিসেবে পাঠানোর অপশন (যা ফাইলেই মনে থাকে), আর একটি ফ্রেমের ভেতরের জিনিস পাঠালে ফ্রেমের মাপে নতুন ডকুমেন্ট বা কম্পোজিশন তৈরি।\n\n৭. ঝামেলাহীন ইনস্টল: উইন্ডোজে .bat আর macOS-এ .command ইনস্টলার; Node.js বা এক্সটেনশন ম্যানেজার লাগে না। Figma-র জন্য আলাদা একটি স্ক্রিপ্ট প্লাগইনটা স্থায়ী জায়গায় কপি করে প্রয়োজনীয় পাথ ক্লিপবোর্ডে রেখে দেয়, বাকি থাকে মাত্র তিনটি ক্লিক।\n\n৮. ফ্রি চিরকাল: MIT লাইসেন্সে ওপেন সোর্স, উইন্ডোজ ও macOS দুটোতেই, Adobe 2021 বা নতুন সংস্করণে।",
			'solution_en' => "1. One shared intermediate representation: a TypeScript core package turns artwork into an app-neutral description (geometry, SVG paths, a message protocol). Each app has a reader that produces that description and a writer that rebuilds native layers from it, so four readers and four writers cover all twelve routes instead of twelve converters.\n\n2. A bridge on your own machine: the first LazyLord panel you open quietly runs a relay on ws://localhost:7878, and the other panels and the Figma plugin connect to it. There is no separate program or console window, and nothing leaves the computer.\n\n3. Update in place with edit detection: sending again replaces what was built before, keeping its position and grouping. If a layer was changed by hand, LazyLord asks before overwriting it.\n\n4. Live mode: keep one app updating as you work in another.\n\n5. Honest reporting: anything an app cannot rebuild, such as inner shadows in After Effects, is listed on the transfer instead of being silently dropped.\n\n6. Figma extras: image scale from 1x to 4x, an option to send complex layers as images that is remembered in the file, and a new document or composition at the frame's size when everything sent sits inside one frame.\n\n7. Friction-free install: a .bat installer on Windows and a .command on macOS, with no Node.js or extension manager. A helper script copies the Figma plugin somewhere permanent and puts its path on the clipboard, leaving three clicks.\n\n8. Free forever: open source under the MIT licence, for Windows and macOS, with Adobe apps from 2021 onwards.",
			'highlights_bn'=> array(
				'Figma, Photoshop, Illustrator ও After Effects, চারটি অ্যাপের যেকোনোটি থেকে যেকোনোটিতে, মোট বারোটি পথ',
				'বেজিয়ে পাথ, এডিটযোগ্য লাইভ টেক্সট আর ছবি নেটিভ লেয়ার হিসেবে পৌঁছায়, স্ক্রিনশট হয়ে নয়',
				'আবার পাঠালে আগের লেয়ার জায়গায় থেকেই হালনাগাদ হয়',
				'হাতে করা এডিট শনাক্ত করে ওভাররাইটের আগে জিজ্ঞেস করে',
				'লাইভ মোড: এক অ্যাপে কাজ, অন্য অ্যাপ সঙ্গে সঙ্গে হালনাগাদ',
				'যা পুনর্গঠন করা যায় না তার স্পষ্ট তালিকা, নীরবে বাদ দেওয়া নয়',
				'TypeScript-এ অ্যাপ-নিরপেক্ষ মধ্যবর্তী বর্ণনা: চারটি reader ও চারটি writer',
				'সম্পূর্ণ লোকাল: ws://localhost:7878 ব্রিজ, কোনো ক্লাউড, অ্যাকাউন্ট বা আপলোড নেই',
				'Figma থেকে ১x–৪x ইমেজ স্কেল ও লেয়ারকে ছবি হিসেবে পাঠানোর অপশন',
				'উইন্ডোজ ও macOS-এ এক-ক্লিক ইনস্টলার, Node.js লাগে না',
				'MIT লাইসেন্সে ফ্রি ও ওপেন সোর্স, Overlord-এর পূর্ণ বিকল্প'
			),
			'highlights_en'=> array(
				'Any of Figma, Photoshop, Illustrator and After Effects to any other: twelve routes',
				'Bézier paths, live editable text and images arrive as native layers, not screenshots',
				'Send again to update what was built before, right where it sits',
				'Detects hand edits and asks before overwriting them',
				'Live mode keeps one app updating as you work in another',
				'Lists anything an app cannot rebuild instead of silently dropping it',
				'App-neutral intermediate representation in TypeScript: four readers, four writers',
				'Fully local: a ws://localhost:7878 bridge with no cloud, account or uploads',
				'Figma image scale from 1x to 4x and an option to send layers as images',
				'One-click installers for Windows and macOS, no Node.js required',
				'Free and open source under MIT, a complete Overlord alternative'
			),
			'tags'        => array( 'Adobe CEP', 'ExtendScript', 'Figma Plugin', 'TypeScript', 'Photoshop', 'Illustrator', 'After Effects', 'WebSocket' ),
			'accent'      => '#6c4cff',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/lazylord.svg',
			'image_fit'   => 'cover',
			'action_type' => 'code',
			'action_bn'   => 'ফ্রি ডাউনলোড',
			'action_en'   => 'Download Free',
			'direct_url'  => 'https://github.com/raisulsohan/LazyLord/releases/latest',
			'github_url'  => 'https://github.com/raisulsohan/LazyLord',
			'order'       => 5,
		),
		array(
			'id'          => 'lazykick',
			'category'    => 'tools',
			'type_bn'     => 'অ্যাডোবি CEP ওয়ার্কফ্লো প্যানেল',
			'type_en'     => 'Adobe CEP Workflow Panel',
			'badge_bn'    => 'ফ্রি ও ওপেন সোর্স • AE ও Premiere Pro',
			'badge_en'    => 'Free & Open Source • AE & Premiere Pro',
			'title_bn'    => 'LazyKick — After Effects ও Premiere Pro-র জন্য ক্লিপবোর্ড থেকে টাইমলাইন, প্রজেক্ট নোট ও স্বয়ংক্রিয় ইমপোর্ট',
			'title_en'    => 'LazyKick — Clipboard to Timeline, Project Notes & Auto-Import for After Effects and Premiere Pro',
			'summary_bn'  => 'After Effects আর Premiere Pro-র জন্য একটি ডক করা যায় এমন প্যানেল, যা এডিটিংয়ের তিনটি রোজকার ঝামেলা একসাথে মেটায়: ক্লিপবোর্ডের ছবি এক ক্লিকে প্রজেক্টের পাশে সেভ হয়ে প্লেহেডে টাইমলাইনে বসে যায়, প্রতিটি প্রজেক্টের নিজস্ব নোট থাকে টাইমকোড স্ট্যাম্পসহ, আর নির্দিষ্ট ফোল্ডারে নতুন মিডিয়া এলে তা নিজে থেকেই বিনে ইমপোর্ট হয়। ফ্রি ও ওপেন সোর্স।',
			'summary_en'  => 'A dockable panel for After Effects and Premiere Pro that folds three everyday editing chores into one place: a clipboard image is saved next to the project and placed on the timeline at the playhead in one click, every project keeps its own notes with timecode stamps, and watched folders import new media into bins by themselves. Free and open source.',
			'role_bn'     => 'একক ডেভেলপার ও ক্রিয়েটিভ টুলস ইঞ্জিনিয়ার',
			'role_en'     => 'Solo Developer & Creative Tools Engineer',
			'context_bn'  => 'Adobe CEP ৯–১২ + ExtendScript • Node.js • সাইন করা ZXP',
			'context_en'  => 'Adobe CEP 9–12 + ExtendScript • Node.js • Signed ZXP',
			'challenge_bn'=> "ভিডিও এডিটিংয়ের দিনে তিনটি ছোট কাজ বারবার ফিরে আসে এবং প্রতিবার মনোযোগ ভাঙে। একটা স্ক্রিনশট বা রেফারেন্স ছবি টাইমলাইনে আনতে হলে সেটা ফাইল হিসেবে সেভ করা, ইমপোর্ট করা, খুঁজে টেনে আনা লাগে। ক্লায়েন্টের ফিডব্যাক আর কাজের তালিকা থাকে অন্য কোনো অ্যাপে, যেখানে টাইমকোড নেই। আর ডাউনলোড বা ক্লায়েন্ট ফোল্ডারে নতুন ফুটেজ এলে প্রতিবার হাতে ইমপোর্ট করে ঠিক বিনে রাখতে হয়।\n\nপ্রযুক্তিগত চ্যালেঞ্জ:\n১. টাইমলাইন অক্ষত রাখা: Premiere Pro-তে ছবি বসাতে গিয়ে পরের ক্লিপ ডানে সরে যাওয়া বা কোনো ক্লিপ ঢাকা পড়া চলবে না।\n২. অসমাপ্ত ও দ্বৈত ইমপোর্ট: কপি হতে থাকা ফাইল অর্ধেক অবস্থায় ইমপোর্ট হওয়া, একই ফাইল দুবার আসা, বা ব্যর্থ ফাইল চিরকালের জন্য 'ইমপোর্ট হয়েছে' ধরে নেওয়া ঠেকাতে হবে।\n৩. প্রজেক্ট চেনা: নোট আর ওয়াচ বিনকে সঠিক প্রজেক্টের সাথে বাঁধতে হবে, এমনকি সেভ না হওয়া After Effects প্রজেক্টেও, আর প্রজেক্ট বদলানোর মাঝখানে ভুল প্রজেক্টে লেখা বা ইমপোর্ট যাওয়া চলবে না।\n৪. পুরোনো CEP-এর সীমা ও ঝামেলাহীন ইনস্টল: CEP 9-এর Chromium 61 আর Node 8-এর মধ্যেই কোড রাখা, আর ব্যবহারকারীকে debug mode বা এক্সটেনশন ম্যানেজার ছাড়াই ইনস্টল করানো।",
			'challenge_en'=> "Three small tasks keep returning through an editing day, and each one breaks concentration. Getting a screenshot or reference image onto the timeline means saving a file, importing it, then finding and dragging it in. Client feedback and to-do lists live in another app with no timecodes. And whenever new footage lands in a downloads or client folder, it has to be imported by hand and filed into the right bin.\n\nTechnical challenges:\n1. Leaving the edit untouched: placing an image in Premiere Pro must never push later clips to the right or cover an existing clip.\n2. Half-copied and duplicate imports: files still being copied must not be imported half-written, the same file must not arrive twice, and a file that failed once must not be remembered as imported forever.\n3. Knowing the project: notes and watch bins must stay tied to the right project, including unsaved After Effects projects, and nothing may be written or imported into the wrong project while the user switches between them.\n4. Old CEP limits and a painless install: the panel code has to run inside CEP 9's Chromium 61 and Node 8, and install without debug mode or an extension manager.",
			'solution_bn' => "১. QuickPaste, ক্লিপবোর্ড থেকে টাইমলাইন: স্ক্রিনশট, যেকোনো অ্যাপ থেকে কপি করা ছবি, এমনকি Explorer বা Finder-এ কপি করা ইমেজ ফাইল এক ক্লিকে (বা Ctrl/Cmd+V) প্রজেক্ট ফোল্ডারে সেভ হয়; PNG-র স্বচ্ছতা অক্ষত থাকে। MD5 ইনডেক্স একই ছবি চিনে ফেলে, তাই কিছুই দুবার ইমপোর্ট হয় না, আর কোনো ফাইল ওভাররাইটও হয় না।\n- After Effects: সক্রিয় কম্পোজিশনে বর্তমান সময় থেকে লেয়ার যোগ হয়, ডিফল্টভাবে guide layer হিসেবে (কাজের সময় দেখা যায়, রেন্ডারে আসে না), বড় ছবি ঐচ্ছিকভাবে কম্পের মাপে ছোট হয়; পুরোটা এক undo ধাপ।\n- Premiere Pro: প্লেহেডে ছবির পুরো সময়জুড়ে ফাঁকা থাকা সবচেয়ে নিচের আনলক ভিডিও ট্র্যাকে শুধু ফাঁকা জায়গায় ওভাররাইট করে বসে। কোনো ক্লিপ সরে না, ঢাকা পড়ে না; জায়গা না থাকলে ছবি বিনে থেকে যায় এবং প্যানেল কারণ জানায়।\n- শেষ ১২টি পেস্টের গ্যালারি রিস্টার্টের পরেও থাকে।\n\n২. Notes & Tasks: প্রতিটি প্রজেক্টের নোট স্বয়ংক্রিয়ভাবে সেভ হয় এবং After Effects ও Premiere Pro দুই জায়গায় একই থাকে। সেভ না হওয়া প্রজেক্টের নোট সেভ করার সময় প্রজেক্টের সাথে চলে যায়। একাধিক ট্যাব, সব প্রজেক্টে শেয়ার করা গ্লোবাল স্ক্র্যাচপ্যাড, চেকলিস্ট, আর অ্যাপ যেভাবে সময় দেখায় সেভাবেই [00:01:24:12] টাইমকোড স্ট্যাম্প। ওয়েব থেকে পেস্ট করলে ফরম্যাটিং বাদ যায়; এক্সপোর্ট কখনো আগের ফাইল মুছে দেয় না।\n\n৩. Watch Bins: Downloads, SFX বা ক্লায়েন্ট আপলোডের মতো ফোল্ডারকে প্রজেক্টের (নেস্টেড) বিনের সাথে যুক্ত করা যায়, ভিডিও/অডিও/ইমেজ ফিল্টার আর সাবফোল্ডারসহ। Auto-Sync প্রতি ৬ সেকেন্ডে দেখে, কিন্তু কোনো ফাইল দুবার স্ক্যানের মাঝে আকার না বদলানো পর্যন্ত ইমপোর্ট করে না। প্রতিটি ফাইল একবারই আসে; ব্যর্থ ফাইল 'skipped' হিসেবে দেখায় এবং পরে আবার চেষ্টা হয়। সিঙ্ক একটার পর একটা চলে, আর যে প্রজেক্টের জন্য স্ক্যান হয়েছে তার বাইরে কিছু ইমপোর্ট হয় না।\n\n৪. নিরাপত্তা: ফোল্ডার বা ফাইলের নাম কখনো HTML হিসেবে পড়া হয় না, আর Explorer/Finder shell ছাড়াই খোলে, তাই অদ্ভুত নামের ফাইল কিছু চালাতে পারে না।\n\n৫. ঝামেলাহীন ইনস্টল: সাইন ও টাইমস্ট্যাম্প করা .zxp আর উইন্ডোজ ও macOS-এর এক-ক্লিক ইনস্টলার; PlayerDebugMode বা এক্সটেনশন ম্যানেজার লাগে না, আপডেটে নোট ও বিন হারায় না।\n\n৬. ব্যাপক সামঞ্জস্য: CEP 9 থেকে 12, After Effects CC 2019+ ও Premiere Pro 2020+, উইন্ডোজ ও macOS; ExtendScript চেকার আর host ও panel-এর স্বয়ংক্রিয় টেস্টসহ, MIT লাইসেন্সে।",
			'solution_en' => "1. QuickPaste, clipboard to timeline: screenshots, images copied from any app, and even image files copied in Explorer or Finder are saved into the project folder in one click (or Ctrl/Cmd+V), with PNG transparency kept. An MD5 index recognises pictures already pasted, so nothing is imported twice, and no file is ever overwritten.\n- After Effects: adds a layer to the active composition at the current time, as a guide layer by default (visible while working, never rendered), optionally shrunk to fit the comp, all in one undo step.\n- Premiere Pro: places the still with an overwrite into empty space only, on the lowest unlocked video track that is free at the playhead for the still's whole duration. No clip moves or gets covered; if there is no room, the image stays in the bin and the panel says why.\n- A gallery of the last 12 pastes survives restarts.\n\n2. Notes & Tasks: notes save automatically per project and are the same in After Effects and Premiere Pro. Notes taken in an unsaved project move with it when it is saved. Multiple tabs, a global scratchpad shared by every project, checklists, and [00:01:24:12] timecode stamps formatted the way the app displays time. Pasting from the web strips formatting, and exports never overwrite an earlier file.\n\n3. Watch Bins: link folders such as Downloads, SFX or client uploads to nested project bins, with video, audio and image filters and optional subfolders. Auto-Sync checks every 6 seconds but only imports a file once its size has held still between two scans. Every file arrives once; failures are shown as skipped and retried later. Sync jobs run one at a time, and nothing is imported into a project other than the one that was scanned.\n\n4. Safety: folder and file names are never parsed as HTML, and Explorer or Finder is launched without a shell, so an oddly named file cannot run anything.\n\n5. Painless install: a signed, timestamped .zxp with one-click installers for Windows and macOS, needing neither PlayerDebugMode nor an extension manager, and updates keep notes and bins.\n\n6. Broad compatibility: CEP 9 through 12, After Effects CC 2019+ and Premiere Pro 2020+, on Windows and macOS, with an ExtendScript checker and automated host and panel tests, under the MIT licence.",
			'highlights_bn'=> array(
				'After Effects ও Premiere Pro, দুই অ্যাপেই একটি ডক করা যায় এমন প্যানেল',
				'ক্লিপবোর্ডের ছবি এক ক্লিকে প্রজেক্টের পাশে সেভ হয়ে প্লেহেডে টাইমলাইনে',
				'Premiere Pro-তে শুধু ফাঁকা জায়গায় বসে, কোনো ক্লিপ সরে না বা ঢাকা পড়ে না',
				'After Effects-এ guide layer, কম্পের মাপে ঐচ্ছিক ছোট করা, এক undo ধাপ',
				'PNG স্বচ্ছতা অক্ষত, আর MD5 দিয়ে একই ছবি দুবার ইমপোর্ট ঠেকানো',
				'প্রতি প্রজেক্টে নোট, ট্যাব, চেকলিস্ট আর টাইমকোড স্ট্যাম্প; সব প্রজেক্টের জন্য গ্লোবাল স্ক্র্যাচপ্যাড',
				'ফোল্ডার থেকে বিনে স্বয়ংক্রিয় ইমপোর্ট, প্রতিটি ফাইল একবারই',
				'কপি শেষ না হওয়া পর্যন্ত অপেক্ষা; ব্যর্থ ফাইল দেখায় ও আবার চেষ্টা করে',
				'প্রজেক্ট বদলানোর সময়ও ভুল প্রজেক্টে কিছু যায় না',
				'সাইন করা ZXP ও এক-ক্লিক ইনস্টলার, debug mode লাগে না',
				'CEP 9–12, উইন্ডোজ ও macOS; MIT লাইসেন্সে ফ্রি ও ওপেন সোর্স'
			),
			'highlights_en'=> array(
				'One dockable panel for both After Effects and Premiere Pro',
				'A clipboard image saved beside the project and placed at the playhead in one click',
				'Premiere Pro placement into empty space only: no clip moves or gets covered',
				'After Effects guide layers, optional shrink-to-fit and a single undo step',
				'PNG transparency kept and duplicate pastes caught with an MD5 index',
				'Per-project notes with tabs, checklists and timecode stamps, plus a global scratchpad',
				'Watched folders import into bins automatically, each file exactly once',
				'Waits for copies to finish; skipped files are shown and retried',
				'Never imports or writes into the wrong project while switching',
				'Signed ZXP with one-click installers, no debug mode needed',
				'CEP 9–12 on Windows and macOS; free and open source under MIT'
			),
			'tags'        => array( 'Adobe CEP', 'ExtendScript', 'After Effects', 'Premiere Pro', 'Node.js', 'Productivity' ),
			'accent'      => '#9999ff',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/lazykick.svg',
			'image_fit'   => 'cover',
			'action_type' => 'code',
			'action_bn'   => 'ফ্রি ডাউনলোড',
			'action_en'   => 'Download Free',
			'direct_url'  => 'https://github.com/raisulsohan/LazyKick/releases/latest',
			'github_url'  => 'https://github.com/raisulsohan/LazyKick',
			'order'       => 4,
		),
		array(
			'id'          => 'lazymotiontoolkit',
			'category'    => 'tools',
			'type_bn'     => 'After Effects ScriptUI ডক প্যানেল',
			'type_en'     => 'After Effects ScriptUI Dockable Panel',
			'badge_bn'    => 'ফ্রি ও ওপেন সোর্স • After Effects',
			'badge_en'    => 'Free & Open Source • After Effects',
			'title_bn'    => 'LazyMotionToolkit — After Effects-এর জন্য নয়টি মোশন টুলের এক প্যানেল',
			'title_en'    => 'LazyMotionToolkit — Nine Motion Design Tools in One After Effects Panel',
			'summary_bn'  => 'After Effects-এর জন্য একটি ডক করা যায় এমন প্যানেল, যেখানে মোশন ডিজাইনের নয়টি রোজকার কাজ এক জায়গায়: অ্যানিমেশন না হারিয়ে স্মার্ট প্রিকম্প, না-চ্যাপ্টা হওয়া অটো টেক্সট বক্স, সাতটি ইজিংয়ের ফেড, অ্যানিমেটেড তীর, 9-পয়েন্ট অ্যাঙ্কর প্যাড, গ্রিড, কালার সোয়াচ, বজ্রপাতের ইফেক্ট আর ব্যাকগ্রাউন্ডে প্রিভিউ রেন্ডার। কোনো প্লাগইন ছাড়া, ফ্রি ও ওপেন সোর্স।',
			'summary_en'  => 'One dockable After Effects panel that gathers nine everyday motion design jobs: smart precomposing that keeps every animation, auto text boxes that never distort, fades with seven easing curves, animated arrows, a 9-point anchor pad, grids, colour swatches, lightning effects and background preview renders. No plugins, free and open source.',
			'role_bn'     => 'একক ডেভেলপার ও মোশন টুলস ইঞ্জিনিয়ার',
			'role_en'     => 'Solo Developer & Motion Tools Engineer',
			'context_bn'  => 'After Effects ScriptUI • ExtendScript (ES3) • aerender',
			'context_en'  => 'After Effects ScriptUI • ExtendScript (ES3) • aerender',
			'challenge_bn'=> "মোশন ডিজাইনের দিনে অনেক ছোট কাজ বারবার হাতে করতে হয়: লেয়ার আলাদা প্রিকম্পে নেওয়া, টেক্সটের পেছনে বক্স বানিয়ে টেক্সটের সাথে মাপ মেলানো, ফেড ইন-আউটের কিফ্রেম বসানো, অ্যাঙ্কর পয়েন্ট ঠিক করা, গ্রিড টানা, রং বসানো, আর ভারী কম্প মসৃণভাবে দেখতে প্রিভিউ রেন্ডার। এগুলোর জন্য হয় আলাদা আলাদা স্ক্রিপ্ট, নয় পেইড প্লাগইন, আর প্রায়ই সেগুলো নীরবে কিছু ভেঙে দেয়।\n\nপ্রযুক্তিগত চ্যালেঞ্জ:\n১. কিছু না ভেঙে কাজ করা: প্রিকম্প করতে গিয়ে কিফ্রেম, ইফেক্ট, মাস্ক, টাইম রিম্যাপ বা প্যারেন্টিং হারানো চলবে না; অ্যাঙ্কর পয়েন্ট সরালে লেয়ার পর্দায় এক পিক্সেলও নড়বে না, অ্যানিমেটেড বা আলাদা X/Y পজিশনেও।\n২. বিকৃতি ছাড়া বক্স: প্রচলিত বক্স-মেকার লেয়ারের scale বদলায়, ফলে কোণের গোলাকার ভাব চ্যাপ্টা হয়ে যায়; টাইপরাইটার অ্যানিমেশনের সাথে বক্সকেও অক্ষর ধরে বাড়তে হবে।\n৩. ভারী কম্পের প্রিভিউ: aerender দিয়ে ব্যাকগ্রাউন্ডে রেন্ডার চালানো, শুধু নিজের রেন্ডারটাই বাতিল করা, রেন্ডার আসলেই শেষ হয়েছে তা নিশ্চিত জানা, আর বাংলার মতো অ-ইংরেজি অক্ষরের প্রজেক্ট ফোল্ডারেও কাজ করা।\n৪. ES3-এর সীমায় নির্ভরযোগ্যতা: পুরোনো ExtendScript ইঞ্জিনে লেখা কোডকে যেকোনো ভাষার After Effects-এ চালানো, আর কোনো লেয়ার প্রক্রিয়া করা না গেলে নীরবে ব্যর্থ না হয়ে কারণ জানানো।",
			'challenge_en'=> "A motion design day is full of small jobs done by hand again and again: moving layers into their own precomps, building a box behind text and keeping it sized to the text, keyframing fades, fixing anchor points, drawing grids, applying colours, and rendering previews to watch heavy comps smoothly. The usual answer is a pile of separate scripts or paid plugins, which often break something quietly.\n\nTechnical challenges:\n1. Changing nothing by accident: precomposing must not lose keyframes, effects, masks, time remapping or parenting, and moving an anchor point must not shift the layer by a pixel, even with animated or separated X/Y position.\n2. Boxes without distortion: typical box makers scale the layer, which squashes rounded corners, and the box has to grow letter by letter with a typewriter animation.\n3. Previews of heavy comps: running aerender in the background, cancelling only that render, knowing for certain when it has finished, and working from project folders with non-English letters such as Bengali.\n4. Reliability within ES3: old ExtendScript code that works in After Effects in any language, and that explains why a layer could not be processed instead of failing silently.",
			'solution_bn' => "১. স্মার্ট প্রিকম্প: Precomp (1:1) প্রতিটি লেয়ারকে আলাদা প্রিকম্পে নেয়। ফুটেজ, সলিড আর কম্পের সব অ্যাট্রিবিউট বাইরে থাকে, তাই কিফ্রেম, ইফেক্ট, মাস্ক, টাইম রিম্যাপ ও প্যারেন্ট অক্ষত; মাস্ক থাকলে সব কিফ্রেম, বেজিয়ে হ্যান্ডেল, ফেদার ও এক্সপ্যানশন হিসাব করে প্রিকম্প ঠিক ততটুকু ক্রপ হয়, আর কোনো ফ্রেমে কিছু নড়ে না। Group Precomp সব লেয়ার এক প্রিকম্পে নেয়, 3D লেয়ার থাকলে ক্যামেরা ঠিক রাখতে Collapse Transformations চালু করে।\n\n২. পিক্সেল-নিখুঁত অটো টেক্সট বক্স: লেয়ার scale নয়, শেপের আয়তক্ষেত্রের জ্যামিতিই বদলায়, তাই কোণ সবসময় নিখুঁত গোল। Text Animator আর Range Selector পড়ে টাইপরাইটারের সাথে বক্স অক্ষর ধরে বাড়ে, টেক্সটের opacity অনুসরণ করে, আর Padding, Roundness, Opacity, Color-এর নিজস্ব কন্ট্রোল থাকে।\n\n৩. Head to Line: যেকোনো পেন-পাথে এক ক্লিকে আট ধরনের মাথা (ত্রিভুজ, বৃত্ত, তারা ইত্যাদি) বসে, বাঁক ধরে নিজে ঘোরে; দুই মাথা, গোল কোণ, আর Trim Paths-এর সাথে আঁকা হওয়ার অ্যানিমেশন।\n\n৪. Fade Animator Pro: Linear থেকে Bounce ও Elastic পর্যন্ত সাতটি গাণিতিক ইজিং, ফ্রেমে সময় ও গতি নিয়ন্ত্রণ, লেয়ারের নিজের opacity মেনে চলা। আবার দিলে আগেরটা বদলায়, আর Clear শুধু টুলকিটের যোগ করা জিনিসই সরায়, ব্যবহারকারীর নিজের expression কখনো মোছে না।\n\n৫. 9-পয়েন্ট অ্যাঙ্কর প্যাড ও Center in Comp: প্রতিটি পজিশন কিফ্রেম আর আলাদা X/Y পজিশন হিসাব করে অ্যাঙ্কর সরায়, যাতে লেয়ার লাফ না দেয়; যা নিরাপদে করা যায় না তা কারণসহ বাদ দেয়।\n\n৬. Grid Designer ও QuickSwatch: প্রিসেট বা নিজের মাপে শেপ টাইল, আউটলাইন বা গাইড নাল দিয়ে গ্রিড (৪০০ ঘরের সীমাসহ); আর শেপ, টেক্সট ও সলিডে এক ক্লিকে ফিল বা স্ট্রোক দেওয়া রং-প্যালেট, যা রিস্টার্টের পরেও মনে থাকে।\n\n৭. LazyStrike FX: বজ্র, ফ্ল্যাশ আর আকাশের ঝলক, সময় ধরে বা অডিওর উচ্চ শব্দ ধরে। ইফেক্ট সেটিং match name দিয়ে খোঁজে, তাই যেকোনো ভাষার After Effects-এ চলে; পুরোটা এক undo ধাপ।\n\n৮. LazyPreview Render: work area ব্যাকগ্রাউন্ডে aerender দিয়ে H.264-এ রেন্ডার হয়, কাজ চলতে থাকে, শেষে কম্পের ওপরে solo করা প্রিভিউ লেয়ার হিসেবে বসে। Cancel শুধু এই রেন্ডারই থামায়, aerender সত্যি বন্ধ হলে তবেই শেষ ধরে, আর বাংলা নামের ফোল্ডারেও Windows short path দিয়ে কাজ করে।\n\n৯. যাচাই ও ইনস্টল: অফলাইন টেস্ট আর After Effects-এর ভেতরে চলা smoke test দিয়ে প্রতিটি পরিবর্তন যাচাই করা হয়েছে (AE 2026-এ)। উইন্ডোজ ও macOS-এর ইনস্টলার কম্পিউটারে থাকা সব After Effects-এ প্যানেলটা বসিয়ে দেয়; MIT লাইসেন্সে ফ্রি।",
			'solution_en' => "1. Smart precomposing: Precomp (1:1) puts each layer in its own precomp. Footage, solids and comps keep every attribute outside, so keyframes, effects, masks, time remapping and parents are untouched; with masks, the precomp is cropped to exactly what they can ever show across all keyframes, Bézier handles, feather and expansion, and nothing moves at any frame. Group Precomp combines layers and turns on Collapse Transformations for 3D layers so they keep the scene camera.\n\n2. Pixel-perfect auto text box: it resizes the shape rectangle itself rather than the layer's scale, so corners stay perfectly round. It reads Text Animators and Range Selectors to grow letter by letter with a typewriter animation, follows the text's opacity, and adds its own Padding, Roundness, Opacity and Color controls.\n\n3. Head to Line: one click puts one of eight head styles on any pen path, rotating along every curve, with double-sided heads, rounded corners and Trim Paths draw-on animation.\n\n4. Fade Animator Pro: seven mathematical easings from Linear to Bounce and Elastic, duration in frames and a speed multiplier, respecting the layer's own opacity. Re-applying replaces the fade, and Clear removes only what the toolkit added, never an expression of your own.\n\n5. 9-point anchor pad and Center in Comp: moves the anchor while compensating every Position keyframe and separated X/Y position so the layer never jumps, and skips with a reason whatever cannot be done safely.\n\n6. Grid Designer and QuickSwatch: grids as shape tiles, outlines or guide nulls from presets or custom sizes, with a 400-cell guard; and a colour palette that fills or strokes shapes, text and solids in one click and is remembered across restarts.\n\n7. LazyStrike FX: lightning bolts, flashes and sky flashes, by timing or driven by audio peaks. Effect settings are found by match name, so it works in any After Effects language, and everything is one undo step.\n\n8. LazyPreview Render: renders the work area to H.264 with aerender in the background while you keep working, then drops it on top of the comp as a solo'd preview layer. Cancel stops only this render, completion is detected when aerender actually exits, and project folders with Bengali or other non-English names work through Windows short paths.\n\n9. Verified and easy to install: every change is checked by offline tests and a smoke test that runs inside After Effects (2026). Installers for Windows and macOS put the panel into every After Effects they find; free under the MIT licence.",
			'highlights_bn'=> array(
				'নয়টি মোশন টুল একটি ডক করা যায় এমন After Effects প্যানেলে',
				'কিফ্রেম, ইফেক্ট, মাস্ক ও প্যারেন্ট অক্ষত রেখে স্মার্ট প্রিকম্প, মাস্কের মাপে নিখুঁত ক্রপ',
				'লেয়ার scale ছাড়া অটো টেক্সট বক্স: কোণ সবসময় গোল, টাইপরাইটারের সাথে বাড়ে',
				'যেকোনো পাথে আট ধরনের অ্যানিমেটেড তীরের মাথা, বাঁক ধরে নিজে ঘোরে',
				'সাতটি গাণিতিক ইজিংয়ের ফেড, লেয়ারের নিজের opacity মেনে',
				'লেয়ার না নড়িয়ে 9-পয়েন্ট অ্যাঙ্কর প্যাড, অ্যানিমেটেড পজিশনেও',
				'প্রিসেটসহ গ্রিড ডিজাইনার আর রিস্টার্টেও মনে থাকা কালার প্যালেট',
				'সময় বা অডিও ধরে বজ্রপাত ও ফ্ল্যাশ ইফেক্ট, যেকোনো ভাষার After Effects-এ',
				'ব্যাকগ্রাউন্ডে aerender প্রিভিউ রেন্ডার, বাংলা নামের ফোল্ডারেও',
				'কোনো লেয়ার বাদ পড়লে কারণসহ জানায়, নীরবে ব্যর্থ হয় না',
				'অফলাইন ও After Effects-এর ভেতরের টেস্টে যাচাই করা; উইন্ডোজ ও macOS ইনস্টলার, MIT লাইসেন্স'
			),
			'highlights_en'=> array(
				'Nine motion design tools in one dockable After Effects panel',
				'Smart precomposing that keeps keyframes, effects, masks and parents, with exact mask cropping',
				'Auto text boxes that never scale the layer: round corners, growing with the typewriter',
				'Animated arrow heads in eight styles on any path, turning with every curve',
				'Fades with seven mathematical easings that respect the layer\'s own opacity',
				'A 9-point anchor pad that never moves the layer, even with animated position',
				'Grid designer with presets and a colour palette remembered across restarts',
				'Lightning and flash effects by timing or audio, in any After Effects language',
				'Background aerender preview renders, even from Bengali-named folders',
				'Explains every skipped layer instead of failing silently',
				'Verified by offline and in-app tests; Windows and macOS installers, MIT licence'
			),
			'tags'        => array( 'After Effects', 'ExtendScript', 'ScriptUI', 'Motion Graphics', 'aerender', 'Automation' ),
			'accent'      => '#f5a524',
			'icon'        => 'extension',
			'image'       => get_template_directory_uri() . '/assets/img/lazymotiontoolkit.svg',
			'image_fit'   => 'cover',
			'action_type' => 'code',
			'action_bn'   => 'ফ্রি ডাউনলোড',
			'action_en'   => 'Download Free',
			'direct_url'  => 'https://github.com/raisulsohan/LazyMotionToolkit/releases/latest',
			'github_url'  => 'https://github.com/raisulsohan/LazyMotionToolkit',
			'order'       => 3,
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

/**
 * 10. Sync Lazy-Image project to v2.2 specifications in the database
 */
function rs_sync_lazy_image_portfolio_v2() {
	/* The flag is written on the main site, so it has to be read there too;
	   read on /en/ first, it was never found and the sync ran every request. */
	$switched = false;
	if ( is_multisite() && ! is_main_site() ) {
		switch_to_blog( get_main_site_id() );
		$switched = true;
	}

	if ( get_option( 'rs_portfolio_synced_lazyimage_v2_2' ) ) {
		if ( $switched ) {
			restore_current_blog();
		}
		return;
	}

	$posts = get_posts( array(
		'post_type'      => 'rs_portfolio',
		'name'           => 'lazy-image-ae',
		'posts_per_page' => 1,
		'post_status'    => 'any',
	) );

	if ( ! empty( $posts ) ) {
		$post_id  = $posts[0]->ID;
		$defaults = rs_get_default_portfolio_projects();
		$item     = null;
		foreach ( $defaults as $d ) {
			if ( 'lazy-image-ae' === $d['id'] ) {
				$item = $d;
				break;
			}
		}

		if ( $item ) {
			wp_update_post( array(
				'ID'         => $post_id,
				'post_title' => $item['title_en'],
			) );

			update_post_meta( $post_id, '_rs_portfolio_category', $item['category'] );
			update_post_meta( $post_id, '_rs_portfolio_type_bn', $item['type_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_type_en', $item['type_en'] );
			update_post_meta( $post_id, '_rs_portfolio_badge_bn', $item['badge_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_badge_en', $item['badge_en'] );
			update_post_meta( $post_id, '_rs_portfolio_title_bn', $item['title_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_title_en', $item['title_en'] );
			update_post_meta( $post_id, '_rs_portfolio_summary_bn', $item['summary_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_summary_en', $item['summary_en'] );
			update_post_meta( $post_id, '_rs_portfolio_role_bn', $item['role_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_role_en', $item['role_en'] );
			update_post_meta( $post_id, '_rs_portfolio_context_bn', $item['context_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_context_en', $item['context_en'] );
			update_post_meta( $post_id, '_rs_portfolio_challenge_bn', $item['challenge_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_challenge_en', $item['challenge_en'] );
			update_post_meta( $post_id, '_rs_portfolio_solution_bn', $item['solution_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_solution_en', $item['solution_en'] );
			update_post_meta( $post_id, '_rs_portfolio_highlights_bn', $item['highlights_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_highlights_en', $item['highlights_en'] );
			update_post_meta( $post_id, '_rs_portfolio_tags', implode( ', ', $item['tags'] ) );
			update_post_meta( $post_id, '_rs_portfolio_accent', $item['accent'] );
			update_post_meta( $post_id, '_rs_portfolio_icon', $item['icon'] );
			update_post_meta( $post_id, '_rs_portfolio_image', $item['image'] );
			update_post_meta( $post_id, '_rs_portfolio_image_fit', $item['image_fit'] );
			update_post_meta( $post_id, '_rs_portfolio_action_type', $item['action_type'] );
			update_post_meta( $post_id, '_rs_portfolio_action_bn', $item['action_bn'] );
			update_post_meta( $post_id, '_rs_portfolio_action_en', $item['action_en'] );
			update_post_meta( $post_id, '_rs_portfolio_direct_url', $item['direct_url'] );
			update_post_meta( $post_id, '_rs_portfolio_github_url', $item['github_url'] );
		}
	}

	update_option( 'rs_portfolio_synced_lazyimage_v2_2', 1 );

	if ( $switched ) {
		restore_current_blog();
	}
}
add_action( 'init', 'rs_sync_lazy_image_portfolio_v2' );

/**
 * 11. Add projects that joined the defaults after the portfolio was seeded.
 *
 * The initial seed only runs on an empty portfolio, so a project added to
 * the defaults later has to be inserted on its own. Each goes to the top of
 * the list, ahead of whatever order the projects were dragged into, and is
 * never re-added once its flag is set (even if it is later trashed). To add
 * another project: put it in rs_get_default_portfolio_projects() and its id
 * at the end of the list below.
 */
function rs_sync_new_portfolio_projects() {
	$switched = false;
	if ( is_multisite() && ! is_main_site() ) {
		switch_to_blog( get_main_site_id() );
		$switched = true;
	}

	if ( get_option( 'rs_portfolio_seeded_v1' ) ) {
		foreach ( array( 'lazylord', 'lazykick', 'lazymotiontoolkit' ) as $slug ) {
			rs_add_portfolio_project_once( $slug );
		}
	}

	rs_seed_portfolio_demo( 'lazylord', 'lazylord-demo.html', 'lazylord-demo-vertical.html' );
	rs_seed_portfolio_demo( 'lazymotiontoolkit', 'lazymotiontoolkit-demo.html', 'lazymotiontoolkit-demo-vertical.html' );
	rs_seed_portfolio_demo( 'lazy-image-ae', 'lazy-image-demo.html', 'lazy-image-demo-vertical.html' );

	if ( $switched ) {
		restore_current_blog();
	}
}
add_action( 'init', 'rs_sync_new_portfolio_projects', 20 );

/**
 * Give an existing project the demo that ships with the theme, once, and
 * never over one set in the dashboard.
 *
 * @param string $slug Project slug.
 * @param string $wide 16:9 demo file in assets/demo/.
 * @param string $tall 9:16 demo file in assets/demo/.
 */
function rs_seed_portfolio_demo( $slug, $wide, $tall ) {
	$flag = 'rs_portfolio_demo_' . $slug;

	if ( get_option( $flag ) ) {
		return;
	}

	$ids = get_posts( array(
		'post_type'      => 'rs_portfolio',
		'name'           => $slug,
		'posts_per_page' => 1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	) );

	if ( ! $ids ) {
		return; /* try again once the project exists */
	}

	if ( ! get_post_meta( $ids[0], '_rs_portfolio_demo', true ) ) {
		$base = get_template_directory_uri() . '/assets/demo/';
		update_post_meta( $ids[0], '_rs_portfolio_demo', $base . $wide );
		update_post_meta( $ids[0], '_rs_portfolio_demo_tall', $base . $tall );
	}

	update_option( $flag, 1 );
}

/**
 * Insert one default project, by id, if it has never been added.
 *
 * @param string $slug Project id in rs_get_default_portfolio_projects().
 */
function rs_add_portfolio_project_once( $slug ) {
	$flag = 'rs_portfolio_added_' . $slug;

	if ( get_option( $flag ) ) {
		return;
	}

	$exists = get_posts( array(
		'post_type'      => 'rs_portfolio',
		'name'           => $slug,
		'posts_per_page' => 1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	) );

	$item = null;
	foreach ( rs_get_default_portfolio_projects() as $d ) {
		if ( $slug === $d['id'] ) {
			$item = $d;
			break;
		}
	}

	if ( empty( $exists ) && $item ) {
		$first = get_posts( array(
			'post_type'      => 'rs_portfolio',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		) );

		$post_id = wp_insert_post( array(
			'post_type'   => 'rs_portfolio',
			'post_status' => 'publish',
			'post_name'   => $slug,
			'post_title'  => $item['title_en'],
			'menu_order'  => $first ? (int) $first[0]->menu_order - 1 : 0,
		) );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			foreach ( array( 'category', 'type_bn', 'type_en', 'badge_bn', 'badge_en', 'title_bn', 'title_en', 'summary_bn', 'summary_en', 'role_bn', 'role_en', 'context_bn', 'context_en', 'challenge_bn', 'challenge_en', 'solution_bn', 'solution_en', 'highlights_bn', 'highlights_en', 'accent', 'icon', 'image', 'image_fit', 'action_type', 'action_bn', 'action_en', 'direct_url', 'github_url' ) as $field ) {
				/* Every default carries all of these today, but the next project
				   added to that list will not necessarily, and a missing key is
				   a warning printed into the page on PHP 8. */
				if ( isset( $item[ $field ] ) ) {
					update_post_meta( $post_id, '_rs_portfolio_' . $field, $item[ $field ] );
				}
			}

			if ( ! empty( $item['tags'] ) && is_array( $item['tags'] ) ) {
				update_post_meta( $post_id, '_rs_portfolio_tags', implode( ', ', $item['tags'] ) );
			}
		}
	}

	update_option( $flag, 1 );
}


/**
 * 12. Live GitHub numbers for the project cards.
 *
 * Stars, total release downloads and the latest release, per repository.
 * Nothing is fetched while a page renders: the numbers live in a network
 * option, a cron event refreshes them twice a day, and a page that finds
 * them stale only asks for that event. A first visit therefore shows the
 * cards without numbers, and the next rendered copy has them.
 */
function rs_github_repo_slug( $url ) {
	if ( ! preg_match( '#github\.com/([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)#i', (string) $url, $m ) ) {
		return '';
	}

	return strtolower( $m[1] . '/' . preg_replace( '/\.git$/i', '', $m[2] ) );
}

/**
 * The stored numbers for one repository URL, or null.
 *
 * @param string $url GitHub URL.
 * @return array|null { stars, downloads, version, published }
 */
function rs_github_stats( $url ) {
	$slug = rs_github_repo_slug( $url );

	if ( ! $slug ) {
		return null;
	}

	$all = rs_github_stats_store();

	return isset( $all['repos'][ $slug ] ) ? $all['repos'][ $slug ] : null;
}

/**
 * The stored GitHub numbers, asking for a refresh when they are stale.
 *
 * @return array
 */
function rs_github_stats_store() {
	$all = get_site_option( 'rs_github_stats', array() );

	/* Cron is only the slow safety net; the page's live request (see
	   rs_rest_github) keeps the numbers minutes fresh. */
	if ( rs_github_is_stale( is_array( $all ) ? $all : array(), 12 * HOUR_IN_SECONDS ) ) {
		if ( ! wp_next_scheduled( 'rs_refresh_github_stats' ) ) {
			wp_schedule_single_event( time(), 'rs_refresh_github_stats' );
		}
	}

	return is_array( $all ) ? $all : array();
}

/**
 * How long fetched numbers count as fresh. A token has 5,000 requests an
 * hour to spend, so it can ask every couple of minutes; without one GitHub
 * allows 60, shared with every site on the server.
 *
 * @return int Seconds.
 */
function rs_github_ttl() {
	return rs_github_token() ? 2 * MINUTE_IN_SECONDS : 12 * HOUR_IN_SECONDS;
}

/**
 * Whether it is time to ask GitHub again.
 *
 * @param array $all Stored numbers.
 * @param int   $ttl Freshness window; defaults to rs_github_ttl().
 * @return bool
 */
function rs_github_is_stale( $all, $ttl = 0 ) {
	$tried = ! empty( $all['tried'] ) ? (int) $all['tried'] : ( ! empty( $all['fetched'] ) ? (int) $all['fetched'] : 0 );
	$wait  = $ttl ? (int) $ttl : rs_github_ttl();

	/* After a failed run, back off instead of hammering. */
	if ( ! empty( $all['failed'] ) ) {
		$wait = rs_github_token() ? 10 * MINUTE_IN_SECONDS : HOUR_IN_SECONDS;
	}

	return ( time() - $tried ) > $wait;
}

/**
 * The stars, downloads and version list for one repository. Cards, project
 * pages and the live refresh all use this, so a swap is seamless.
 *
 * @param string $slug  owner/repo.
 * @param array  $stats { stars, downloads, version, published }.
 * @param bool   $is_en English site.
 * @return string
 */
function rs_github_stats_html( $slug, $stats, $is_en ) {
	$num = function ( $n ) use ( $is_en ) {
		$n = number_format_i18n( (int) $n );
		return $is_en ? $n : rs_bn_digits( $n );
	};

	$html  = '<ul class="rs-pf-card__stats" data-rs-gh="' . esc_attr( $slug ) . '">';
	$html .= '<li><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3.2l2.7 5.5 6 .9-4.35 4.25 1 6L12 17l-5.35 2.85 1-6L3.3 9.6l6-.9z"/></svg><span class="rs-pf-sr">' . esc_html( $is_en ? 'GitHub stars:' : 'GitHub স্টার:' ) . '</span> ' . esc_html( $num( $stats['stars'] ) ) . '</li>';

	if ( ! empty( $stats['downloads'] ) ) {
		$html .= '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></svg><span class="rs-pf-sr">' . esc_html( $is_en ? 'Downloads:' : 'ডাউনলোড:' ) . '</span> ' . esc_html( $num( $stats['downloads'] ) ) . '</li>';
	}

	if ( ! empty( $stats['version'] ) ) {
		$ver = $stats['version'];

		if ( ! empty( $stats['published'] ) && strtotime( $stats['published'] ) ) {
			$ago  = human_time_diff( strtotime( $stats['published'] ), time() );
			$ver .= ' · ' . ( $is_en ? $ago . ' ago' : rs_bn_digits( $ago ) . ' আগে' );
		}

		$html .= '<li class="rs-pf-card__ver">' . esc_html( $ver ) . '</li>';
	}

	return $html . '</ul>';
}

/**
 * The inside of the "now building" strip.
 *
 * @param array $latest { repo, message, date, url }.
 * @param bool  $is_en  English site.
 * @return string
 */
function rs_github_now_html( $latest, $is_en ) {
	$html  = '<span class="rs-pf-now__dot" aria-hidden="true"></span>';
	$html .= '<span class="rs-pf-now__label">' . esc_html( $is_en ? 'Last worked on' : 'সর্বশেষ কাজ' ) . '</span>';
	$html .= '<strong>' . esc_html( $latest['repo'] ) . '</strong>';

	if ( ! empty( $latest['date'] ) && strtotime( $latest['date'] ) ) {
		$ago   = human_time_diff( strtotime( $latest['date'] ), time() );
		$html .= '<time datetime="' . esc_attr( $latest['date'] ) . '">' . esc_html( $is_en ? $ago . ' ago' : rs_bn_digits( $ago ) . ' আগে' ) . '</time>';
	}

	if ( ! empty( $latest['message'] ) ) {
		$html .= '<span class="rs-pf-now__msg">' . esc_html( mb_substr( $latest['message'], 0, 80 ) ) . '</span>';
	}

	return $html;
}

/**
 * The most recent commit across the author's repositories, or null.
 *
 * @return array|null { repo, message, date, url }
 */
function rs_github_latest_commit() {
	$all = rs_github_stats_store();

	return ! empty( $all['latest']['repo'] ) ? $all['latest'] : null;
}

/**
 * Cron: fetch every repository the portfolio links to.
 */
function rs_refresh_github_stats() {
	$slugs = array();

	foreach ( rs_get_portfolio_projects() as $project ) {
		$slug = rs_github_repo_slug( isset( $project['github_url'] ) ? $project['github_url'] : '' );
		if ( $slug ) {
			$slugs[ $slug ] = true;
		}
	}

	$args  = array(
		'timeout' => 8,
		'headers' => array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'raisulsohan.com portfolio',
		),
	);

	/* Without a token GitHub allows 60 requests an hour per IP address, and
	   on shared hosting other sites use that up. A token has its own 5,000. */
	$token = rs_github_token();

	if ( $token ) {
		$args['headers']['Authorization'] = 'Bearer ' . $token;
	}
	$old    = get_site_option( 'rs_github_stats', array() );
	$repos  = isset( $old['repos'] ) && is_array( $old['repos'] ) ? $old['repos'] : array();
	$errors = array();
	$fresh  = 0;

	foreach ( array_keys( $slugs ) as $slug ) {
		$response = wp_remote_get( 'https://api.github.com/repos/' . $slug, $args );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			$code     = is_wp_error( $response ) ? $response->get_error_code() : (int) wp_remote_retrieve_response_code( $response );
			$limited  = ! is_wp_error( $response ) && '0' === (string) wp_remote_retrieve_header( $response, 'x-ratelimit-remaining' );
			$errors[] = $slug . ':' . $code . ( $limited ? ' (rate limit' . ( $token ? '' : ', no token' ) . ')' : '' ) . ( 401 === $code ? ' (token rejected)' : '' );

			/* Rate limited or blocked: stop asking, keep what was known. */
			if ( ! is_wp_error( $response ) && in_array( (int) wp_remote_retrieve_response_code( $response ), array( 401, 403, 429 ), true ) ) {
				break;
			}
			continue;
		}

		++$fresh;

		$repo = json_decode( wp_remote_retrieve_body( $response ), true );
		$data = array(
			'stars'     => isset( $repo['stargazers_count'] ) ? (int) $repo['stargazers_count'] : 0,
			'downloads' => 0,
			'version'   => '',
			'published' => '',
		);

		$response = wp_remote_get( 'https://api.github.com/repos/' . $slug . '/releases?per_page=100', $args );

		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$releases = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( is_array( $releases ) ) {
				foreach ( array_values( $releases ) as $i => $release ) {
					if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
						foreach ( $release['assets'] as $asset ) {
							$data['downloads'] += isset( $asset['download_count'] ) ? (int) $asset['download_count'] : 0;
						}
					}

					if ( 0 === $i ) {
						$data['version']   = isset( $release['tag_name'] ) ? sanitize_text_field( $release['tag_name'] ) : '';
						$data['published'] = isset( $release['published_at'] ) ? sanitize_text_field( $release['published_at'] ) : '';
					}
				}
			}
		}

		$repos[ $slug ] = $data;
	}

	/* Last worked on: the last commit on the repository the author pushed
	   to most recently, leaving out this theme's own repository, whose
	   releases would otherwise crowd out the actual work. */
	$latest = isset( $old['latest'] ) ? $old['latest'] : null;
	$owner  = '';

	foreach ( array_keys( $slugs ) as $slug ) {
		$owner = strtok( $slug, '/' );
		break;
	}

	/**
	 * Repositories (owner/repo) never shown as last worked on.
	 *
	 * @param string[] $skip Lower-case owner/repo slugs; the theme's own by default.
	 */
	$skip = array_map( 'strtolower', (array) apply_filters( 'rs_github_latest_skip', array( rs_github_repo_slug( wp_get_theme( get_template() )->get( 'ThemeURI' ) ) ) ) );

	if ( $owner ) {
		$response = wp_remote_get( 'https://api.github.com/users/' . rawurlencode( $owner ) . '/repos?type=owner&sort=pushed&per_page=10', $args );

		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$list = json_decode( wp_remote_retrieve_body( $response ), true );
			$pick = null;

			foreach ( is_array( $list ) ? $list : array() as $repo_item ) {
				if ( ! empty( $repo_item['full_name'] ) && ! in_array( strtolower( $repo_item['full_name'] ), $skip, true ) ) {
					$pick = $repo_item;
					break;
				}
			}

			if ( $pick ) {
				$response = wp_remote_get( 'https://api.github.com/repos/' . $pick['full_name'] . '/commits?per_page=1', $args );

				if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
					$commits = json_decode( wp_remote_retrieve_body( $response ), true );

					if ( ! empty( $commits[0]['sha'] ) ) {
						$message = isset( $commits[0]['commit']['message'] ) ? (string) $commits[0]['commit']['message'] : '';
						$latest  = array(
							'repo'    => sanitize_text_field( isset( $pick['name'] ) ? $pick['name'] : '' ),
							'message' => sanitize_text_field( strtok( $message, "\n" ) ),
							'date'    => sanitize_text_field( isset( $commits[0]['commit']['author']['date'] ) ? $commits[0]['commit']['author']['date'] : '' ),
							'url'     => esc_url_raw( isset( $commits[0]['html_url'] ) ? $commits[0]['html_url'] : '' ),
						);
					}
				}
			}
		}
	}

	update_site_option(
		'rs_github_stats',
		array(
			/* fetched is the last run that worked; tried is the last run. */
			'fetched' => $fresh ? time() : ( isset( $old['fetched'] ) ? (int) $old['fetched'] : 0 ),
			'tried'   => time(),
			'failed'  => ! $fresh,
			'repos'   => $repos,
			'latest'  => $latest,
			'errors'  => array_slice( $errors, 0, 5 ),
		)
	);
}
add_action( 'rs_refresh_github_stats', 'rs_refresh_github_stats' );

/**
 * 13. A real address for every project: /portfolio/<slug>/.
 *
 * On the portfolio page the case study still opens in the pop-up, and the
 * address changes with it. Opened directly (a shared link, a search result)
 * the same address renders the case study as a page of its own.
 */
function rs_portfolio_rewrites() {
	add_rewrite_rule( '^portfolio/([^/]+)/?$', 'index.php?pagename=portfolio&rs_project=$matches[1]', 'top' );
}
add_action( 'init', 'rs_portfolio_rewrites' );

/**
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function rs_portfolio_query_vars( $vars ) {
	$vars[] = 'rs_project';
	return $vars;
}
add_filter( 'query_vars', 'rs_portfolio_query_vars' );

/**
 * The project address for a slug, on the current site.
 *
 * @param string $slug Project slug.
 * @return string
 */
function rs_project_url( $slug ) {
	return home_url( '/portfolio/' . rawurlencode( $slug ) . '/' );
}

/**
 * The project this request is about, or null.
 *
 * @return array|null
 */
function rs_current_project() {
	static $found = null;

	if ( null !== $found ) {
		return $found ? $found : null;
	}

	$found = false;
	$slug  = sanitize_title( (string) get_query_var( 'rs_project' ) );

	if ( $slug && did_action( 'wp' ) ) {
		foreach ( rs_get_portfolio_projects() as $project ) {
			if ( $project['id'] === $slug ) {
				$found = $project;
				break;
			}
		}
	}

	return $found ? $found : null;
}

/**
 * A project's display name: its title up to the dash.
 *
 * @param array $project Project.
 * @return string[] { name, tagline }
 */
function rs_project_name( $project ) {
	$title = rs_is_en() ? $project['title_en'] : $project['title_bn'];
	$parts = preg_split( '/\s+[—–]\s+/u', $title, 2 );

	return array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
}

/**
 * An address under /portfolio/ that names no project is a 404.
 */
function rs_portfolio_project_404() {
	if ( get_query_var( 'rs_project' ) && ! rs_current_project() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'rs_portfolio_project_404', 1 );

/**
 * @param string $url Canonical URL.
 * @return string
 */
function rs_portfolio_canonical( $url ) {
	$project = rs_current_project();
	return $project ? rs_project_url( $project['id'] ) : $url;
}
add_filter( 'get_canonical_url', 'rs_portfolio_canonical' );

/**
 * @param string $title Document title.
 * @return string
 */
function rs_portfolio_document_title( $title ) {
	$project = rs_current_project();

	if ( ! $project ) {
		return $title;
	}

	$name = rs_project_name( $project );

	return $name[0] . ' — ' . ( rs_is_en() ? 'Portfolio' : 'পোর্টফোলিও' ) . ' — ' . rs_brand();
}
add_filter( 'pre_get_document_title', 'rs_portfolio_document_title', 20 );

/**
 * Every project in the sitemap.
 */
function rs_portfolio_sitemap_provider() {
	if ( ! class_exists( 'WP_Sitemaps_Provider' ) || ( function_exists( 'rs_seo_plugin_active' ) && rs_seo_plugin_active() ) ) {
		return;
	}

	if ( ! class_exists( 'RS_Portfolio_Sitemap' ) ) {
		/**
		 * Lists /portfolio/<slug>/ addresses.
		 */
		class RS_Portfolio_Sitemap extends WP_Sitemaps_Provider {
			public function __construct() {
				$this->name        = 'portfolio';
				$this->object_type = 'portfolio';
			}

			public function get_url_list( $page_num, $object_subtype = '' ) {
				$urls = array();

				foreach ( rs_get_portfolio_projects() as $project ) {
					$urls[] = array( 'loc' => rs_project_url( $project['id'] ) );
				}

				return $urls;
			}

			public function get_max_num_pages( $object_subtype = '' ) {
				return 1;
			}
		}
	}

	wp_register_sitemap_provider( 'portfolio', new RS_Portfolio_Sitemap() );
}
add_action( 'init', 'rs_portfolio_sitemap_provider' );

/**
 * REST: the project list, for the command palette.
 */
function rs_rest_projects_route() {
	register_rest_route(
		'rs/v1',
		'/projects',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rs_rest_projects',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'rs_rest_projects_route' );

/**
 * REST: live GitHub numbers, rendered as the page renders them.
 */
function rs_rest_github_route() {
	register_rest_route(
		'rs/v1',
		'/github',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rs_rest_github',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'rs_rest_github_route' );

/**
 * Whether a project's page carries the interactive demo.
 *
 * @param string $slug Project slug.
 * @return bool
 */
function rs_project_has_demo( $slug ) {
	return (bool) rs_project_demo_kit( $slug );
}

/**
 * Everything a project's interactive demo needs: which page bundle to load,
 * the classes and attribute its markup carries, the global its bundle
 * exposes for the pop-up player to mount with, and its one-line intro.
 *
 * @param string $slug Project slug.
 * @return array|null
 */
function rs_project_demo_kit( $slug ) {
	/**
	 * Projects that have an interactive demo, by slug.
	 *
	 * @param array[] $kits Slug => { bundle, wrap, root, attr, mount, sub_en, sub_bn }.
	 */
	$kits = (array) apply_filters( 'rs_project_interactive_demos', array(
		'lazylord'      => array(
			'bundle' => 'lazylord-demo',
			'wrap'   => 'lld-wrap',
			'root'   => 'lld',
			'attr'   => 'data-lazylord-demo',
			'mount'  => 'LazyLordDemo',
			'sub_en' => 'Pick layers in Figma and send them to Photoshop, Illustrator or After Effects.',
			'sub_bn' => 'Figma-য় লেয়ার বেছে Photoshop, Illustrator বা After Effects-এ পাঠিয়ে দেখুন।',
		),
		'lazy-image-ae' => array(
			'bundle' => 'lazyimage-demo',
			'wrap'   => 'lzi-wrap',
			'root'   => 'lzi',
			'attr'   => 'data-lazyimage-demo',
			'mount'  => 'LazyImageDemo',
			'sub_en' => 'Write a prompt in any language, generate, and watch the picture land on the timeline.',
			'sub_bn' => 'যেকোনো ভাষায় প্রম্পট লিখে ছবি বানান, দেখুন সেটা নিজে থেকেই টাইমলাইনে বসে।',
		),
	) );

	return isset( $kits[ $slug ] ) ? $kits[ $slug ] : null;
}

/**
 * What a link or button needs so the pop-up player can open a project's
 * interactive demo: which bundle to fetch and what to mount it into.
 *
 * @param string $slug Project slug.
 * @return string Escaped attributes, or an empty string.
 */
function rs_project_demo_attrs( $slug ) {
	$kit = rs_project_demo_kit( $slug );

	if ( ! $kit ) {
		return '';
	}

	$base = get_template_directory_uri() . '/assets/' . $kit['bundle'] . '.min';

	return sprintf(
		' data-rs-interactive data-rs-css="%s" data-rs-js="%s" data-rs-mount="%s" data-rs-wrap="%s" data-rs-root="%s"',
		esc_url( $base . '.css?ver=' . RS_VERSION ),
		esc_url( $base . '.js?ver=' . RS_VERSION ),
		esc_attr( $kit['mount'] ),
		esc_attr( $kit['wrap'] ),
		esc_attr( $kit['root'] )
	);
}

/**
 * The interactive demo's page bundle, only on a page that shows it. The
 * stylesheet loads in the head so the demo's box has its size before the
 * first paint; the script waits for the footer.
 */
function rs_project_demo_assets() {
	$project = function_exists( 'rs_current_project' ) ? rs_current_project() : null;
	$kit     = $project ? rs_project_demo_kit( $project['id'] ) : null;

	if ( ! $kit ) {
		return;
	}

	$base = get_template_directory_uri() . '/assets/';

	wp_enqueue_style( 'rs-' . $kit['bundle'], $base . $kit['bundle'] . '.min.css', array( 'rs-style' ), RS_VERSION );
	wp_enqueue_script( 'rs-' . $kit['bundle'], $base . $kit['bundle'] . '.min.js', array(), RS_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'rs_project_demo_assets', 20 );

/**
 * The portfolio page may come from the full-page cache, so after it loads
 * the browser asks here. Stale numbers are fetched from GitHub right away;
 * the page is already on screen, so nobody waits on it.
 *
 * @return WP_REST_Response
 */
function rs_rest_github() {
	$all = get_site_option( 'rs_github_stats', array() );
	$all = is_array( $all ) ? $all : array();

	if ( rs_github_is_stale( $all ) && ! get_site_transient( 'rs_github_busy' ) ) {
		set_site_transient( 'rs_github_busy', 1, MINUTE_IN_SECONDS );
		rs_refresh_github_stats();
		delete_site_transient( 'rs_github_busy' );

		$all = get_site_option( 'rs_github_stats', array() );
		$all = is_array( $all ) ? $all : array();
	}

	$is_en     = rs_is_en();
	$repos     = array();
	$downloads = 0;

	foreach ( rs_get_portfolio_projects() as $project ) {
		$slug = rs_github_repo_slug( isset( $project['github_url'] ) ? $project['github_url'] : '' );

		if ( ! $slug || empty( $all['repos'][ $slug ] ) ) {
			continue;
		}

		$downloads     += (int) $all['repos'][ $slug ]['downloads'];
		$repos[ $slug ] = rs_github_stats_html( $slug, $all['repos'][ $slug ], $is_en );
	}

	$total    = number_format_i18n( $downloads );
	$response = rest_ensure_response(
		array(
			'repos'     => $repos,
			'downloads' => $downloads ? ( $is_en ? $total : rs_bn_digits( $total ) ) : '',
			'now'       => empty( $all['latest']['repo'] ) ? null : array(
				'url'  => $all['latest']['url'],
				'html' => rs_github_now_html( $all['latest'], $is_en ),
			),
			'fetched'   => empty( $all['fetched'] ) ? null : gmdate( 'c', (int) $all['fetched'] ),
		)
	);
	$response->header( 'Cache-Control', 'public, max-age=60, s-maxage=60' );

	return $response;
}

/**
 * @return WP_REST_Response
 */
function rs_rest_projects() {
	$out = array();

	foreach ( rs_get_portfolio_projects() as $project ) {
		$name  = rs_project_name( $project );
		$out[] = array(
			'id'   => $project['id'],
			'name' => $name[0],
			'type' => rs_is_en() ? $project['type_en'] : $project['type_bn'],
			'url'  => rs_project_url( $project['id'] ),
		);
	}

	$response = rest_ensure_response( $out );
	$response->header( 'Cache-Control', 'public, max-age=300, s-maxage=3600' );

	/* A one-line health check for the GitHub numbers; nothing private. */
	$gh = get_site_option( 'rs_github_stats', array() );
	$response->header(
		'X-RS-GitHub',
		sprintf(
			'fetched=%s; repos=%d; latest=%s; errors=%s; next=%s',
			empty( $gh['fetched'] ) ? 'never' : gmdate( 'c', (int) $gh['fetched'] ),
			isset( $gh['repos'] ) ? count( (array) $gh['repos'] ) : 0,
			empty( $gh['latest']['repo'] ) ? 'none' : $gh['latest']['repo'],
			empty( $gh['errors'] ) ? 'none' : implode( ',', (array) $gh['errors'] ),
			wp_next_scheduled( 'rs_refresh_github_stats' ) ? gmdate( 'c', wp_next_scheduled( 'rs_refresh_github_stats' ) ) : 'none'
		)
	);

	return $response;
}

/**
 * Core's canonical redirect would send /portfolio/<slug>/ back to the
 * portfolio page it is routed through; a project address stays put.
 *
 * @param string|false $redirect Target.
 * @return string|false
 */
function rs_portfolio_keep_project_address( $redirect ) {
	return get_query_var( 'rs_project' ) ? false : $redirect;
}
add_filter( 'redirect_canonical', 'rs_portfolio_keep_project_address' );

/**
 * 14. GitHub numbers in the dashboard: their state, and a refresh button.
 *
 * The site is served from a full page cache, so WordPress's own cron runs
 * rarely. The Portfolio screen shows when the numbers were last fetched,
 * what went wrong if anything did, and fetches them on request.
 */
function rs_github_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'edit-rs_portfolio' !== $screen->id || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$gh      = get_site_option( 'rs_github_stats', array() );
	$fetched = empty( $gh['fetched'] ) ? 'কখনো না' : human_time_diff( (int) $gh['fetched'], time() ) . ' আগে';
	$repos   = isset( $gh['repos'] ) ? count( (array) $gh['repos'] ) : 0;
	$errors  = empty( $gh['errors'] ) ? 'কোনো ত্রুটি নেই' : 'ত্রুটি: ' . implode( ', ', (array) $gh['errors'] );
	$latest  = empty( $gh['latest']['repo'] ) ? 'নেই' : $gh['latest']['repo'];
	$url     = wp_nonce_url( admin_url( 'admin-post.php?action=rs_refresh_github' ), 'rs_refresh_github' );
	$done    = isset( $_GET['rs_github'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only.

	printf(
		'<div class="notice %1$s"><p><strong>GitHub:</strong> শেষ আনা হয়েছে %2$s · %3$d টি repo · সর্বশেষ কমিট: %4$s · %5$s &nbsp; <a class="button button-small" href="%6$s">এখনই আনুন</a></p>',
		esc_attr( $done ? ( $repos ? 'notice-success' : 'notice-warning' ) : 'notice-info' ),
		esc_html( $fetched ),
		(int) $repos,
		esc_html( $latest ),
		esc_html( $errors ),
		esc_url( $url )
	);

	if ( defined( 'RS_GITHUB_TOKEN' ) && RS_GITHUB_TOKEN ) {
		echo '<p>GitHub টোকেন: wp-config.php থেকে নেওয়া হচ্ছে।</p>';
	} elseif ( current_user_can( 'manage_options' ) ) {
		$saved = (bool) get_site_option( 'rs_github_token', '' );
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin: 0 0 10px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
			<input type="hidden" name="action" value="rs_save_github_token">
			<?php wp_nonce_field( 'rs_save_github_token' ); ?>
			<label for="rs_github_token"><?php echo esc_html( $saved ? 'GitHub টোকেন সেভ করা আছে। বদলাতে নতুনটা দিন:' : 'GitHub টোকেন (ঐচ্ছিক, রেট লিমিট এড়াতে):' ); ?></label>
			<input type="password" name="rs_github_token" id="rs_github_token" autocomplete="off" spellcheck="false" style="min-width: 320px;" placeholder="github_pat_…">
			<button type="submit" class="button button-primary button-small">সেভ করে এখনই আনুন</button>
			<?php if ( $saved ) : ?>
				<button type="submit" name="rs_github_token_clear" value="1" class="button button-link-delete button-small">টোকেন মুছুন</button>
			<?php endif; ?>
		</form>
		<?php
	}

	echo '</div>';
}

/**
 * The GitHub token: a wp-config.php constant first, then the saved one.
 *
 * @return string
 */
function rs_github_token() {
	if ( defined( 'RS_GITHUB_TOKEN' ) && RS_GITHUB_TOKEN ) {
		return (string) RS_GITHUB_TOKEN;
	}

	return (string) get_site_option( 'rs_github_token', '' );
}

/**
 * admin-post: save or clear the token, then fetch.
 */
function rs_github_save_token() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'raisul-sohan' ) );
	}

	check_admin_referer( 'rs_save_github_token' );

	if ( ! empty( $_POST['rs_github_token_clear'] ) ) {
		delete_site_option( 'rs_github_token' );
	} else {
		$token = isset( $_POST['rs_github_token'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['rs_github_token'] ) ) ) : '';

		if ( '' !== $token ) {
			update_site_option( 'rs_github_token', $token );
		}
	}

	rs_refresh_github_stats();

	wp_safe_redirect( admin_url( 'edit.php?post_type=rs_portfolio&rs_github=done' ) );
	exit;
}
add_action( 'admin_post_rs_save_github_token', 'rs_github_save_token' );
add_action( 'admin_notices', 'rs_github_admin_notice' );

/**
 * admin-post: fetch the GitHub numbers now.
 */
function rs_github_refresh_now() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'raisul-sohan' ) );
	}

	check_admin_referer( 'rs_refresh_github' );
	rs_refresh_github_stats();

	wp_safe_redirect( admin_url( 'edit.php?post_type=rs_portfolio&rs_github=done' ) );
	exit;
}
add_action( 'admin_post_rs_refresh_github', 'rs_github_refresh_now' );
