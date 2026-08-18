<?php
$content = file_get_contents('functions.php');

// 1. Version bump
$content = preg_replace("/define\(\s*'RS_VERSION',\s*'[\d\.]+'\s*\);/", "define( 'RS_VERSION', '2.22.0' );", $content);

// 2. Add to settings fields
$settings_search = "/('rs_verify'\s*=>\s*array\([^)]+\),\s+)\);\s+}/s";
$settings_replace = "\t\t'rs_featured_block_offset' => array(
			__( 'Featured block vertical offset (px)', 'raisul-sohan' ),
			'number',
			'intval',
			__( 'Adjust the vertical position of the entire featured section. Can be negative, e.g., -5, 10.', 'raisul-sohan' ),
		),
		'rs_featured_summary_length' => array(
			__( 'Featured summary length (chars)', 'raisul-sohan' ),
			'number',
			'absint',
			__( 'How many characters to show in the featured post sneak peek. Default is 250.', 'raisul-sohan' ),
		),
	);
}";
$content = preg_replace($settings_search, $settings_replace, $content);

// 3. Add to defaults
$defaults_search = "/('rs_verify'\s*=>\s*'[^']*',\s+)\);\s+}/s";
$defaults_replace = "\t\t'rs_featured_block_offset' => 0,
		'rs_featured_summary_length' => 250,
	);
}";
$content = preg_replace($defaults_search, $defaults_replace, $content);

// 4. Add the function itself before rs_render_list
$fn_search = "/function rs_render_list\(\)\s*\{/s";
$fn_replace = "function rs_render_featured_post() {
	if ( is_search() ) {
		return;
	}

	\ = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'rand',
		'no_found_rows'  => true,
	);

	if ( is_category() ) {
		\ = get_queried_object();
		if ( \ instanceof WP_Term ) {
			\['cat'] = \->term_id;
		}
	}

	\ = new WP_Query( \ );

	if ( ! \->have_posts() ) {
		return;
	}

	\->the_post();
	global \;

	\ = (int) rs_option( 'rs_featured_summary_length' );
	if ( ! \ ) \ = 250;
	\ = rs_summary( \, \ );

	// Match first letter and its combining marks/virama-linked letters
	\ = '/^([\x{0980}-\x{09FF}](?:\x{09CD}[\x{0980}-\x{09FF}])*[\x{09BE}-\x{09CC}\x{09D7}\x{09E2}\x{09E3}]?[\x{0981}-\x{0983}]?)/u';
	if ( preg_match( \, \, \ ) ) {
		\ = \[0];
	} else {
		// Fallback for non-Bengali or basic chars
		preg_match('/^\X/u', \, \);
		\ = \[0] ?? '';
	}
	\ = mb_substr( \, mb_strlen( \, 'UTF-8' ), null, 'UTF-8' );

	\ = (int) rs_option( 'rs_featured_block_offset' );
	\ = \ ? ' style=\"margin-top: ' . \ . 'px;\"' : '';
	?>
	<div class=\"rs-wrap\"<?php echo \; ?>>
		<div class=\"rs-featured\">
			<div class=\"rs-featured__label\">
				<span class=\"rs-featured__line\"></span>ফিচার্ড
			</div>
			<h2 class=\"rs-featured__title\">
				<a href=\"<?php the_permalink(); ?>\" data-rs-post=\"<?php the_ID(); ?>\"><?php the_title(); ?></a>
			</h2>
			<div class=\"rs-featured__date\"><?php echo esc_html( rs_bn_date( \ ) ); ?></div>
			<div class=\"rs-featured__summary\">
				<span class=\"rs-featured__dropcap\"><?php echo esc_html( \ ); ?></span><?php echo esc_html( \ ); ?>
			</div>
			<div class=\"rs-featured__action\">
				<a href=\"<?php the_permalink(); ?>\" class=\"rs-featured__btn\" data-rs-post=\"<?php the_ID(); ?>\">সম্পূর্ণ লেখা পড়ুন &rarr;</a>
			</div>
		</div>
	</div>
	<?php
	wp_reset_postdata();
}

function rs_render_list() {";
$content = preg_replace($fn_search, $fn_replace, $content);

file_put_contents('functions.php', $content);
echo "Done";