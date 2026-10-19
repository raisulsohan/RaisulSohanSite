<?php
/**
 * The statistics screen: Dashboard → Statistics, and a small panel on the
 * dashboard's front page.
 *
 * Everything on it comes from the site's own tables (21-statistics.php):
 * no account to sign in to, no permission to be granted, nothing that
 * expires. Drawn with plain HTML and inline SVG; no charting library is
 * loaded. Its stylesheet and the few lines that read figures off the chart
 * are admin-only page bundles (src/css/stats-admin.css, src/js/stats-admin.js).
 *
 * The same screen on both editions: the tables hold both, so a story or a
 * page carries an EN mark when it belongs to the English edition. Written
 * in English with ordinary digits, like the other admin screens, because
 * it is a tool and not something a reader sees.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The windows the screen offers. The first one is what it opens on. */
function rs_stats_ranges() {
	return array(
		'24h'  => __( 'Last 24 hours', 'raisul-sohan' ),
		'7d'   => __( 'Last 7 days', 'raisul-sohan' ),
		'30d'  => __( 'Last 30 days', 'raisul-sohan' ),
		'90d'  => __( 'Last 90 days', 'raisul-sohan' ),
		'365d' => __( 'Last year', 'raisul-sohan' ),
	);
}

function rs_stats_menu() {
	add_menu_page(
		__( 'Statistics', 'raisul-sohan' ),
		__( 'Statistics', 'raisul-sohan' ),
		'edit_posts',
		'rs-stats',
		'rs_stats_page',
		'dashicons-chart-area',
		3
	);
}
add_action( 'admin_menu', 'rs_stats_menu' );

/**
 * The screen's stylesheet, here and on the dashboard (for the panel), and
 * the chart's pointer on the screen alone.
 *
 * @param string $hook The admin page being drawn.
 */
function rs_stats_admin_assets( $hook ) {
	if ( 'toplevel_page_rs-stats' !== $hook && 'index.php' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'rs-stats', RS_URI . '/assets/stats-admin.min.css', array(), RS_VERSION );

	if ( 'toplevel_page_rs-stats' === $hook ) {
		wp_enqueue_script( 'rs-stats', RS_URI . '/assets/stats-admin.min.js', array(), RS_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'rs_stats_admin_assets' );

/* -------------------------------------------------------------------------
 * Saying it
 * ---------------------------------------------------------------------- */

/**
 * A number, grouped the way the dashboard groups numbers.
 *
 * @param int|float $number Number.
 * @param int       $places Decimal places.
 * @return string
 */
function rs_stats_number( $number, $places = 0 ) {
	return number_format_i18n( $number, $places );
}

/**
 * Where readers came from, by name.
 *
 * @param string $source Stored source.
 * @return string
 */
function rs_stats_source_label( $source ) {
	$names = array(
		'direct'     => __( 'Direct / bookmark', 'raisul-sohan' ),
		'internal'   => __( 'From this site', 'raisul-sohan' ),
		'google'     => 'Google',
		'facebook'   => 'Facebook',
		'youtube'    => 'YouTube',
		'linkedin'   => 'LinkedIn',
		'github'     => 'GitHub',
		'whatsapp'   => 'WhatsApp',
		'x'          => 'X / Twitter',
		'instagram'  => 'Instagram',
		'behance'    => 'Behance',
		'reddit'     => 'Reddit',
		'bing'       => 'Bing',
		'duckduckgo' => 'DuckDuckGo',
		'telegram'   => 'Telegram',
		'chatgpt'    => 'ChatGPT',
	);

	return isset( $names[ $source ] ) ? $names[ $source ] : $source;
}

/**
 * A device or an edition, by name.
 *
 * @param string $key Stored value.
 * @return string
 */
function rs_stats_plain_label( $key ) {
	$names = array(
		'mobile'  => __( 'Phone', 'raisul-sohan' ),
		'tablet'  => __( 'Tablet', 'raisul-sohan' ),
		'desktop' => __( 'Computer', 'raisul-sohan' ),
		'bn'      => __( 'Bengali', 'raisul-sohan' ),
		'en'      => __( 'English (/en/)', 'raisul-sohan' ),
	);

	return isset( $names[ $key ] ) ? $names[ $key ] : $key;
}

/**
 * A country's name, and its flag where the system can draw one: the two
 * letters shifted into the regional-indicator block, so no image is
 * loaded for it.
 *
 * @param string $code Two-letter country code.
 * @return string
 */
function rs_stats_country_label( $code ) {
	$code = strtoupper( trim( (string) $code ) );

	if ( ! preg_match( '/^[A-Z]{2}$/', $code ) ) {
		return __( 'Unknown', 'raisul-sohan' );
	}

	$names = array(
		'BD' => 'Bangladesh', 'IN' => 'India', 'PK' => 'Pakistan', 'NP' => 'Nepal', 'LK' => 'Sri Lanka',
		'BT' => 'Bhutan', 'MV' => 'Maldives', 'MM' => 'Myanmar', 'TH' => 'Thailand', 'SG' => 'Singapore',
		'MY' => 'Malaysia', 'ID' => 'Indonesia', 'PH' => 'Philippines', 'VN' => 'Vietnam', 'CN' => 'China',
		'HK' => 'Hong Kong', 'TW' => 'Taiwan', 'JP' => 'Japan', 'KR' => 'South Korea',
		'AE' => 'United Arab Emirates', 'SA' => 'Saudi Arabia', 'QA' => 'Qatar', 'KW' => 'Kuwait',
		'OM' => 'Oman', 'BH' => 'Bahrain', 'IQ' => 'Iraq', 'IR' => 'Iran', 'TR' => 'Türkiye',
		'GB' => 'United Kingdom', 'IE' => 'Ireland', 'FR' => 'France', 'DE' => 'Germany', 'IT' => 'Italy',
		'ES' => 'Spain', 'PT' => 'Portugal', 'NL' => 'Netherlands', 'BE' => 'Belgium', 'CH' => 'Switzerland',
		'AT' => 'Austria', 'SE' => 'Sweden', 'NO' => 'Norway', 'DK' => 'Denmark', 'FI' => 'Finland',
		'PL' => 'Poland', 'CZ' => 'Czechia', 'GR' => 'Greece', 'RO' => 'Romania', 'RU' => 'Russia',
		'UA' => 'Ukraine', 'US' => 'United States', 'CA' => 'Canada', 'MX' => 'Mexico', 'BR' => 'Brazil',
		'AR' => 'Argentina', 'CL' => 'Chile', 'CO' => 'Colombia', 'EG' => 'Egypt', 'MA' => 'Morocco',
		'NG' => 'Nigeria', 'KE' => 'Kenya', 'ZA' => 'South Africa', 'AU' => 'Australia', 'NZ' => 'New Zealand',
	);

	$name = isset( $names[ $code ] ) ? $names[ $code ] : $code;

	if ( ! function_exists( 'mb_chr' ) ) {
		return $name;
	}

	$flag = '';

	foreach ( str_split( $code ) as $letter ) {
		$flag .= mb_chr( 0x1F1E6 + ( ord( $letter ) - 65 ), 'UTF-8' );
	}

	return $flag . ' ' . $name;
}

/**
 * A stretch of time, said the way a person would.
 *
 * @param int $seconds Seconds.
 * @return string
 */
function rs_stats_duration( $seconds ) {
	$seconds = max( 0, (int) $seconds );

	if ( $seconds < 60 ) {
		/* translators: %s: a number of seconds. */
		return sprintf( __( '%ss', 'raisul-sohan' ), rs_stats_number( $seconds ) );
	}

	/* translators: 1: minutes, 2: seconds. */
	return sprintf( __( '%1$sm %2$ss', 'raisul-sohan' ), rs_stats_number( floor( $seconds / 60 ) ), rs_stats_number( $seconds % 60 ) );
}

/**
 * An hour of the day, the way the rest of the screen writes one: 2 pm.
 *
 * @param int $hour 0–23.
 * @return string
 */
function rs_stats_clock( $hour ) {
	$hour = (int) $hour;

	return ( 0 === $hour % 12 ? 12 : $hour % 12 ) . ( $hour < 12 ? ' am' : ' pm' );
}

/**
 * How this window compares with the one before it, as a small chip.
 *
 * @param int $now    This window.
 * @param int $before The window before.
 */
function rs_stats_change( $now, $before ) {
	if ( $before <= 0 ) {
		if ( $now > 0 ) {
			echo '<span class="rs-stats-change rs-stats-change--up">' . esc_html__( 'new this period', 'raisul-sohan' ) . '</span>';
		}

		return;
	}

	$change = (int) round( 100 * ( $now - $before ) / $before );

	if ( 0 === $change ) {
		echo '<span class="rs-stats-change">' . esc_html__( 'level with the period before', 'raisul-sohan' ) . '</span>';
		return;
	}

	printf(
		'<span class="rs-stats-change rs-stats-change--%1$s">%2$s %3$s%%</span><span class="rs-stats-figure__against">%4$s</span>',
		$change > 0 ? 'up' : 'down',
		$change > 0 ? '▲' : '▼',
		esc_html( rs_stats_number( abs( $change ) ) ),
		esc_html__( 'vs the period before', 'raisul-sohan' )
	);
}

/* -------------------------------------------------------------------------
 * Names for what was counted
 * ---------------------------------------------------------------------- */

/**
 * Run something on the site that holds an edition, and come back.
 *
 * A story's id means something only on its own site, so its title and
 * address are read there.
 *
 * @param string   $lang     'bn' or 'en'.
 * @param callable $callback What to run.
 * @return mixed What it returned.
 */
function rs_stats_on_edition( $lang, $callback ) {
	$blog = 0;

	if ( rs_stats_lang() !== $lang && function_exists( 'rs_edition_pair' ) ) {
		$pair = rs_edition_pair();
		$blog = $pair ? (int) $pair['other'] : 0;
	}

	if ( $blog ) {
		switch_to_blog( $blog );
	}

	try {
		return $callback();
	} finally {
		if ( $blog ) {
			restore_current_blog();
		}
	}
}

/**
 * A story's title and address, read from its own edition.
 *
 * @param string $lang    'bn' or 'en'.
 * @param int    $post_id Its id there.
 * @return array{title:string,url:string}
 */
function rs_stats_story( $lang, $post_id ) {
	static $seen = array();

	$key = $lang . '|' . (int) $post_id;

	if ( ! isset( $seen[ $key ] ) ) {
		$seen[ $key ] = rs_stats_on_edition(
			$lang,
			function () use ( $post_id ) {
				$post  = get_post( (int) $post_id );
				$title = $post ? rs_plain_title( $post ) : '';

				return array(
					'title' => '' !== $title ? $title : sprintf( '#%d', (int) $post_id ),
					'url'   => $post ? (string) get_permalink( $post ) : '',
				);
			}
		);
	}

	return $seen[ $key ];
}

/**
 * A page's name and address, from its path: the portfolio's projects and
 * documentation by the project's name, the pages the theme answers itself
 * by theirs, and anything WordPress knows by its title.
 *
 * @param string $lang 'bn' or 'en'.
 * @param string $path Stored path.
 * @return array{title:string,url:string}
 */
function rs_stats_page_name( $lang, $path ) {
	static $seen = array();

	$key = $lang . '|' . $path;

	if ( isset( $seen[ $key ] ) ) {
		return $seen[ $key ];
	}

	if ( '404' === $path ) {
		$seen[ $key ] = array(
			'title' => __( 'A missing page (404)', 'raisul-sohan' ),
			'url'   => '',
		);

		return $seen[ $key ];
	}

	$seen[ $key ] = rs_stats_on_edition(
		$lang,
		function () use ( $lang, $path ) {
			$bare  = untrailingslashit( $path );
			$fixed = array(
				''                    => __( 'Home page', 'raisul-sohan' ),
				'/showreel'           => 'Showreel 2026',
				'/map-animation-demo' => __( 'Map animation demos', 'raisul-sohan' ),
				'/3dinformation'      => __( '3D asset map', 'raisul-sohan' ),
			);
			$title = isset( $fixed[ $bare ] ) ? $fixed[ $bare ] : '';

			if ( '' === $title && preg_match( '#^/page/(\d+)$#', $bare, $match ) ) {
				/* translators: %s: page number of the story list. */
				$title = sprintf( __( 'Home page, page %s', 'raisul-sohan' ), $match[1] );
			}

			if ( '' === $title && preg_match( '#^/portfolio/([^/]+)(/documentation(/.*)?)?$#', $bare, $match ) && function_exists( 'rs_get_portfolio_projects' ) ) {
				foreach ( rs_get_portfolio_projects() as $project ) {
					if ( $project['id'] !== $match[1] ) {
						continue;
					}

					$name  = 'en' === $lang ? $project['title_en'] : $project['title_bn'];
					$name  = preg_split( '/\s+[—–]\s+/u', (string) $name, 2 );
					$title = $name[0];

					if ( ! empty( $match[2] ) ) {
						/* translators: %s: a portfolio project's name. */
						$title = sprintf( __( '%s · documentation', 'raisul-sohan' ), $title );
					}

					break;
				}
			}

			if ( '' === $title && preg_match( '#^/(category|tag)/([^/]+)$#', $bare, $match ) ) {
				$term = get_term_by( 'slug', $match[2], 'category' === $match[1] ? 'category' : 'post_tag' );

				if ( $term ) {
					/* translators: %s: a category or tag name. */
					$title = sprintf( __( 'Stories in %s', 'raisul-sohan' ), $term->name );
				}
			}

			if ( '' === $title ) {
				$id    = url_to_postid( home_url( $path ) );
				$title = $id ? rs_plain_title( get_post( $id ) ) : '';
			}

			return array(
				'title' => '' !== $title ? $title : $path,
				'url'   => home_url( $path ),
			);
		}
	);

	return $seen[ $key ];
}

/**
 * A story or a page, as a link with its edition marked.
 *
 * @param string $lang    'bn' or 'en'.
 * @param int    $post_id The story, or 0.
 * @param string $path    The page, when it is not a story.
 */
function rs_stats_item_link( $lang, $post_id, $path = '' ) {
	$item = $post_id ? rs_stats_story( $lang, $post_id ) : rs_stats_page_name( $lang, $path );

	if ( $item['url'] ) {
		printf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( $item['url'] ), esc_html( $item['title'] ) );
	} else {
		echo esc_html( $item['title'] );
	}

	rs_stats_edition_tag( $lang );
}

/**
 * The EN mark beside anything from the English edition.
 *
 * @param string $lang 'bn' or 'en'.
 */
function rs_stats_edition_tag( $lang ) {
	if ( 'en' === $lang ) {
		echo '<span class="rs-stats-tag">EN</span>';
	}
}

/* -------------------------------------------------------------------------
 * The pieces of the screen
 * ---------------------------------------------------------------------- */

/**
 * A round number to put at the top of the scale, so the lines across land
 * on figures a person would choose.
 *
 * @param int $highest The biggest value.
 * @return float
 */
function rs_stats_nice_ceiling( $highest ) {
	$highest = max( 1, (int) $highest );

	if ( $highest <= 4 ) {
		return 4;
	}

	$magnitude = pow( 10, floor( log10( $highest ) ) );

	foreach ( array( 1, 1.2, 1.6, 2, 2.5, 4, 5, 8, 10 ) as $step ) {
		if ( $highest <= $step * $magnitude ) {
			return $step * $magnitude;
		}
	}

	return 10 * $magnitude;
}

/**
 * The line: views per hour, or per day.
 *
 * The plot keeps one height whatever the window, so the drawing stretches
 * sideways, and nothing that must keep its shape can live inside it: the
 * numbers, the dates, the guide and the dot are ordinary HTML laid over
 * the top, and the SVG holds only the lines, their thickness pinned by
 * vector-effect. stats-admin.js reads the points off data-rs-chart.
 *
 * @param array $series From rs_stats_series().
 * @param bool  $hourly One point per hour.
 */
function rs_stats_chart( array $series, $hourly ) {
	$rows  = array_values( $series );
	$count = count( $rows );

	if ( ! $count ) {
		return;
	}

	$hits    = wp_list_pluck( $rows, 'hits' );
	$ceiling = rs_stats_nice_ceiling( max( $hits ) );

	$x = function ( $i ) use ( $count ) {
		return $count > 1 ? round( 100 * $i / ( $count - 1 ), 3 ) : 50;
	};
	$y = function ( $value ) use ( $ceiling ) {
		return round( 100 - 100 * $value / $ceiling, 3 );
	};

	$points = array();
	$data   = array();

	foreach ( $rows as $i => $row ) {
		$points[] = $x( $i ) . ',' . $y( $row['hits'] );
		$data[]   = array(
			'x' => $x( $i ),
			'y' => $y( $row['hits'] ),
			'v' => rs_stats_number( $row['hits'] ),
			's' => rs_stats_number( $row['visits'] ),
			'l' => $hourly ? wp_date( 'j M, g:00 a', $row['time'] ) : wp_date( 'D j M Y', $row['time'] ),
		);
	}

	$line = implode( ' ', $points );
	$grid = array();

	for ( $step = 4; $step >= 0; $step-- ) {
		$grid[] = array(
			'y'     => $y( $ceiling * $step / 4 ),
			'label' => rs_stats_number( round( $ceiling * $step / 4 ) ),
		);
	}

	/* A handful of dates along the bottom, not one per point. */
	$every  = max( 1, (int) ceil( $count / 8 ) );
	$labels = array();

	foreach ( $rows as $i => $row ) {
		if ( 0 !== $i % $every && $i !== $count - 1 ) {
			continue;
		}

		/* The last point always gets its date, so the regular one just
		   before it steps aside when the two would touch. */
		if ( $i === $count - 1 && $labels && $i - $labels[ count( $labels ) - 1 ]['i'] < $every ) {
			array_pop( $labels );
		}

		$labels[] = array(
			'i'    => $i,
			'x'    => $x( $i ),
			'text' => $hourly ? wp_date( 'g a', $row['time'] ) : wp_date( 'j M', $row['time'] ),
		);
	}
	?>
	<div class="rs-stats-chart" data-rs-chart="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
		<div class="rs-stats-chart__scale" aria-hidden="true">
			<?php foreach ( $grid as $row ) : ?>
				<span style="top:<?php echo esc_attr( $row['y'] ); ?>%"><?php echo esc_html( $row['label'] ); ?></span>
			<?php endforeach; ?>
		</div>

		<div class="rs-stats-chart__plot">
			<svg viewBox="0 0 100 100" preserveAspectRatio="none" role="img" aria-label="<?php esc_attr_e( 'Views over time', 'raisul-sohan' ); ?>">
				<defs>
					<linearGradient id="rs-stats-fill" x1="0" y1="0" x2="0" y2="1">
						<stop offset="0" stop-color="#5b4dff" stop-opacity=".22" />
						<stop offset="1" stop-color="#5b4dff" stop-opacity="0" />
					</linearGradient>
				</defs>
				<?php foreach ( $grid as $index => $row ) : ?>
					<line class="rs-stats-chart__grid<?php echo ( count( $grid ) - 1 ) === $index ? ' is-floor' : ''; ?>" x1="0" y1="<?php echo esc_attr( $row['y'] ); ?>" x2="100" y2="<?php echo esc_attr( $row['y'] ); ?>" vector-effect="non-scaling-stroke" />
				<?php endforeach; ?>
				<polygon fill="url(#rs-stats-fill)" points="<?php echo esc_attr( '0,100 ' . $line . ' 100,100' ); ?>" />
				<polyline class="rs-stats-chart__line" points="<?php echo esc_attr( $line ); ?>" vector-effect="non-scaling-stroke" />
			</svg>

			<span class="rs-stats-chart__guide" hidden></span>
			<span class="rs-stats-chart__dot" hidden></span>
		</div>

		<div class="rs-stats-chart__labels" aria-hidden="true">
			<?php foreach ( $labels as $label ) : ?>
				<span style="left:<?php echo esc_attr( $label['x'] ); ?>%"><?php echo esc_html( $label['text'] ); ?></span>
			<?php endforeach; ?>
		</div>

		<div class="rs-stats-chart__tip" hidden>
			<span class="rs-stats-chart__tip-when"></span>
			<strong class="rs-stats-chart__tip-value"></strong>
			<span class="rs-stats-chart__tip-label"><?php esc_html_e( 'views', 'raisul-sohan' ); ?> · <b class="rs-stats-chart__tip-visits"></b> <?php esc_html_e( 'visits', 'raisul-sohan' ); ?></span>
		</div>
	</div>
	<?php
}

/**
 * The hour-of-day pattern, as 24 small columns.
 *
 * @param array $hours Hour => views.
 */
function rs_stats_hour_pattern( array $hours ) {
	$top  = max( 1, max( $hours ) );
	$peak = array_search( max( $hours ), $hours, true );
	?>
	<div class="rs-stats-hours">
		<?php foreach ( $hours as $hour => $value ) : ?>
			<?php
			/* translators: 1: hour of the day, 2: how many views. */
			$title = sprintf( __( '%1$s: %2$s views', 'raisul-sohan' ), rs_stats_clock( $hour ), rs_stats_number( $value ) );
			?>
			<div class="rs-stats-hours__col<?php echo ( $value > 0 && $hour === $peak ) ? ' is-peak' : ''; ?>" title="<?php echo esc_attr( $title ); ?>">
				<span class="rs-stats-hours__bar" style="height:<?php echo (int) max( 2, round( 100 * $value / $top ) ); ?>%"></span>
				<?php if ( 0 === $hour % 6 ) : ?>
					<span class="rs-stats-hours__tick"><?php echo esc_html( rs_stats_clock( $hour ) ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( max( $hours ) > 0 ) : ?>
		<p class="rs-stats-tile__foot">
			<?php
			/* translators: %s: the busiest hour of the day, e.g. 9 pm. */
			printf( esc_html__( 'Busiest around %s, on the site\'s own clock.', 'raisul-sohan' ), '<strong>' . esc_html( rs_stats_clock( $peak ) ) . '</strong>' );
			?>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * One bar in a breakdown list.
 *
 * @param string $label Row label, already escaped HTML.
 * @param int    $hits  Count.
 * @param int    $max   The biggest row, for the bar's width.
 * @param int    $total Everything, for the share.
 */
function rs_stats_bar( $label, $hits, $max, $total ) {
	$width = $max > 0 ? max( 2, round( 100 * $hits / $max ) ) : 0;
	$share = $total > 0 ? round( 100 * $hits / $total ) : 0;
	?>
	<li class="rs-stats-bar">
		<span class="rs-stats-bar__label"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the caller. ?></span>
		<span class="rs-stats-bar__track"><span class="rs-stats-bar__fill" style="width:<?php echo (int) $width; ?>%"></span></span>
		<span class="rs-stats-bar__value">
			<?php echo esc_html( rs_stats_number( $hits ) ); ?>
			<small><?php echo esc_html( rs_stats_number( $share ) ); ?>%</small>
		</span>
	</li>
	<?php
}

/**
 * A list of bars, or the line that says there is nothing yet.
 *
 * @param array    $rows  Rows with 'hits'.
 * @param callable $label Row => escaped HTML label.
 * @param int      $total What the shares are of; 0 for the rows' own sum.
 * @param string   $empty What to say when there are no rows.
 */
function rs_stats_bars( array $rows, $label, $total, $empty ) {
	if ( empty( $rows ) ) {
		echo '<p class="rs-stats__empty">' . esc_html( $empty ) . '</p>';
		return;
	}

	$hits  = wp_list_pluck( $rows, 'hits' );
	$total = $total ? $total : array_sum( $hits );

	echo '<ul class="rs-stats-bars">';

	foreach ( $rows as $row ) {
		rs_stats_bar( $label( $row ), $row['hits'], max( $hits ), $total );
	}

	echo '</ul>';
}

/**
 * One tile: a heading, a line explaining it, and whatever it shows.
 *
 * A tile does not decide how wide it is; the box it sits in does. The
 * small ones go into the bento, which packs them without the gaps a row of
 * equal-height cards leaves.
 *
 * @param string $title Heading.
 * @param string $hint  A line under it, or ''.
 */
function rs_stats_tile_open( $title, $hint = '' ) {
	?>
	<section class="rs-stats-tile">
		<div class="rs-stats-tile__head">
			<h2 class="rs-stats-tile__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( '' !== $hint ) : ?>
				<p class="rs-stats-tile__hint"><?php echo esc_html( $hint ); ?></p>
			<?php endif; ?>
		</div>
		<div class="rs-stats-tile__body">
	<?php
}

/** Close what rs_stats_tile_open() opened. */
function rs_stats_tile_close() {
	echo '</div></section>';
}

/**
 * How far down stories were read, as a row of quarters.
 *
 * @param array $depth From rs_stats_depth_summary().
 */
function rs_stats_depth_bars( array $depth ) {
	if ( ! $depth['total'] ) {
		echo '<p class="rs-stats__empty">' . esc_html__( 'Not measured yet. It is recorded as a reader leaves a story.', 'raisul-sohan' ) . '</p>';
		return;
	}

	$labels = array(
		100 => __( 'Read to the end', 'raisul-sohan' ),
		75  => __( 'Three quarters', 'raisul-sohan' ),
		50  => __( 'Half', 'raisul-sohan' ),
		25  => __( 'A quarter', 'raisul-sohan' ),
		0   => __( 'Barely started', 'raisul-sohan' ),
	);
	?>
	<p class="rs-stats-depth__average">
		<?php
		printf(
			/* translators: 1: average share of a story that gets read, 2: average time with it. */
			esc_html__( 'On average a reader gets %1$s of the way down, in %2$s.', 'raisul-sohan' ),
			'<strong>' . esc_html( rs_stats_number( $depth['average'] ) ) . '%</strong>',
			'<strong>' . esc_html( rs_stats_duration( $depth['seconds'] ) ) . '</strong>'
		);
		?>
	</p>
	<ul class="rs-stats-bars">
		<?php
		foreach ( $labels as $bucket => $label ) {
			rs_stats_bar( esc_html( $label ), $depth['buckets'][ $bucket ], max( $depth['buckets'] ), $depth['total'] );
		}
		?>
	</ul>
	<?php
}

/**
 * A table of searches, with the edition marked.
 *
 * @param array  $rows  From rs_stats_searches().
 * @param string $field 'hits' or 'miss'.
 * @param string $empty What to say when there are none.
 */
function rs_stats_search_table( array $rows, $field, $empty ) {
	if ( empty( $rows ) ) {
		echo '<p class="rs-stats__empty">' . esc_html( $empty ) . '</p>';
		return;
	}
	?>
	<table class="rs-stats-table">
		<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row['term'] ); ?><?php rs_stats_edition_tag( $row['lang'] ); ?></td>
					<td class="rs-stats-table__num"><?php echo esc_html( rs_stats_number( $row[ $field ] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/* -------------------------------------------------------------------------
 * The screen
 * ---------------------------------------------------------------------- */

function rs_stats_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to see this page.', 'raisul-sohan' ) );
	}

	$notice = '';

	/* Forgetting the addresses that led nowhere, once they have been dealt with. */
	if ( isset( $_POST['rs_stats_clear_missing'] ) && check_admin_referer( 'rs_stats_clear_missing' ) ) {
		rs_stats_clear_missing();
		$notice = __( 'The list of missing addresses has been emptied.', 'raisul-sohan' );
	}

	$ranges = rs_stats_ranges();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Choosing how far back to look changes nothing.
	$key    = isset( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '24h';
	$key    = isset( $ranges[ $key ] ) ? $key : '24h';
	$hourly = '24h' === $key;

	/*
	 * The lists are kept short on purpose. A tile that runs to fifteen rows
	 * stops being a tile, and the fifteenth row of anything here has never
	 * said something the eighth did not.
	 */
	$totals    = rs_stats_totals( $key );
	$before    = rs_stats_totals( $key, true );
	$series    = rs_stats_series( $key );
	$stories   = rs_stats_top_stories( $key, 8 );
	$pages     = rs_stats_top_pages( $key, 8 );
	$per_story = rs_stats_depth_by_story( $key );
	$depth     = rs_stats_depth_summary( $key );
	$hours     = rs_stats_by_hour( $key );
	$weekdays  = rs_stats_by_weekday( $key );
	$entries   = rs_stats_entry_pages( $key, 8 );
	$climbing  = rs_stats_climbing( $key, 6 );
	$sources   = rs_stats_grouped( 'source', $key, 8 );
	$countries = rs_stats_grouped( 'country', $key, 8 );
	$devices   = rs_stats_grouped( 'device', $key );
	$langs     = rs_stats_grouped( 'lang', $key );
	$demos     = rs_stats_events( 'demo', $key, 8 );
	$outbound  = rs_stats_events( 'out', $key, 8 );
	$files     = rs_stats_events( 'file', $key, 6 );
	$contact   = rs_stats_events( 'contact', $key, 6 );
	$shares    = rs_stats_events( 'share', $key, 6 );
	$saves     = rs_stats_events( 'save', $key, 6 );
	$searches  = rs_stats_searches( 10 );
	$missed    = rs_stats_searches( 8, true );
	$missing   = rs_stats_missing( 8 );
	$first     = rs_stats_first_day();
	$now       = rs_stats_pulse();
	$per_visit = $totals['visits'] > 0 ? $totals['hits'] / $totals['visits'] : 0;
	?>
	<div class="wrap rs-stats">
		<div class="rs-stats__bar">
			<div>
				<h1 class="rs-stats__title"><?php esc_html_e( 'Statistics', 'raisul-sohan' ); ?></h1>
				<p class="rs-stats__lede"><?php esc_html_e( 'The site\'s own count of both editions. Nothing that identifies a reader is stored, and visits by the people who run the site are left out.', 'raisul-sohan' ); ?></p>
			</div>

			<nav class="rs-stats__range" aria-label="<?php esc_attr_e( 'How far back to look', 'raisul-sohan' ); ?>">
				<?php foreach ( $ranges as $value => $label ) : ?>
					<a class="rs-stats__range-tab<?php echo $value === $key ? ' is-on' : ''; ?>"<?php echo $value === $key ? ' aria-current="page"' : ''; ?>
						href="<?php echo esc_url( admin_url( 'admin.php?page=rs-stats&range=' . rawurlencode( $value ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! $first ) : ?>
			<div class="notice notice-info inline">
				<p>
					<strong><?php esc_html_e( 'Counting has started.', 'raisul-sohan' ); ?></strong>
					<?php esc_html_e( 'Nothing has been counted yet. It appears here as soon as somebody visits. To see your own visit, open the site logged out, or in another browser.', 'raisul-sohan' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<div class="rs-stats__figures">
			<div class="rs-stats-figure rs-stats-figure--now">
				<span class="rs-stats-figure__label"><span class="rs-stats-dot" aria-hidden="true"></span><?php esc_html_e( 'Right now', 'raisul-sohan' ); ?></span>
				<strong class="rs-stats-figure__value"><?php echo esc_html( rs_stats_number( $now ) ); ?></strong>
				<span class="rs-stats-figure__note"><?php esc_html_e( 'pages opened in the last 30 minutes', 'raisul-sohan' ); ?></span>
			</div>
			<div class="rs-stats-figure">
				<span class="rs-stats-figure__label"><?php esc_html_e( 'Views', 'raisul-sohan' ); ?></span>
				<strong class="rs-stats-figure__value"><?php echo esc_html( rs_stats_number( $totals['hits'] ) ); ?></strong>
				<?php rs_stats_change( $totals['hits'], $before['hits'] ); ?>
			</div>
			<div class="rs-stats-figure">
				<span class="rs-stats-figure__label"><?php esc_html_e( 'Visits', 'raisul-sohan' ); ?></span>
				<strong class="rs-stats-figure__value"><?php echo esc_html( rs_stats_number( $totals['visits'] ) ); ?></strong>
				<?php rs_stats_change( $totals['visits'], $before['visits'] ); ?>
				<span class="rs-stats-figure__note">
					<?php
					/* translators: %s: average number of pages opened in one visit. */
					printf( esc_html__( '%s pages a visit', 'raisul-sohan' ), esc_html( rs_stats_number( $per_visit, 1 ) ) );
					?>
				</span>
			</div>
			<div class="rs-stats-figure">
				<span class="rs-stats-figure__label"><?php esc_html_e( 'Stories read', 'raisul-sohan' ); ?></span>
				<strong class="rs-stats-figure__value"><?php echo esc_html( rs_stats_number( $totals['stories'] ) ); ?></strong>
				<span class="rs-stats-figure__note">
					<?php
					/* translators: 1: readings of stories, 2: views of every other page. */
					printf( esc_html__( '%1$s readings, %2$s other pages', 'raisul-sohan' ), esc_html( rs_stats_number( $totals['story_hits'] ) ), esc_html( rs_stats_number( $totals['page_hits'] ) ) );
					?>
				</span>
			</div>
			<div class="rs-stats-figure">
				<span class="rs-stats-figure__label"><?php esc_html_e( 'Read through', 'raisul-sohan' ); ?></span>
				<strong class="rs-stats-figure__value"><?php echo $depth['total'] ? esc_html( rs_stats_number( $depth['average'] ) ) . '%' : '—'; ?></strong>
				<span class="rs-stats-figure__note">
					<?php
					echo $depth['total']
						/* translators: %s: average time a story is on screen. */
						? esc_html( sprintf( __( 'of a story, %s on screen', 'raisul-sohan' ), rs_stats_duration( $depth['seconds'] ) ) )
						: esc_html__( 'measured as a reader leaves a story', 'raisul-sohan' );
					?>
				</span>
			</div>
			<div class="rs-stats-figure">
				<span class="rs-stats-figure__label"><?php esc_html_e( 'Counting since', 'raisul-sohan' ); ?></span>
				<strong class="rs-stats-figure__value rs-stats-figure__value--small"><?php echo esc_html( $first ? wp_date( 'j M Y', strtotime( $first . ' 12:00:00' ) ) : '—' ); ?></strong>
				<span class="rs-stats-figure__note"><?php esc_html_e( 'nothing from before this is here', 'raisul-sohan' ); ?></span>
			</div>
		</div>

		<?php /* The one tile that earns the whole width: a line across time reads better the wider it gets. */ ?>
		<div class="rs-stats__full">
			<?php
			rs_stats_tile_open( $hourly ? __( 'Views by the hour', 'raisul-sohan' ) : __( 'Views per day', 'raisul-sohan' ), __( 'Every page and story opened. Point at the line for the figure.', 'raisul-sohan' ) );
			rs_stats_chart( $series, $hourly );
			rs_stats_tile_close();
			?>
		</div>

		<?php
		/*
		 * Two pairs that want more than a column but nothing like the whole
		 * screen: a table stretched across a wide monitor puts half a metre
		 * between a title and its figures.
		 */
		?>
		<div class="rs-stats__pair">
			<?php rs_stats_tile_open( __( 'Most read stories', 'raisul-sohan' ), __( '"Read" is how far down the story readers got on average, "Time" how long they had it on screen.', 'raisul-sohan' ) ); ?>
				<?php if ( empty( $stories ) ) : ?>
					<p class="rs-stats__empty"><?php esc_html_e( 'No story was read in this period.', 'raisul-sohan' ); ?></p>
				<?php else : ?>
					<table class="rs-stats-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Story', 'raisul-sohan' ); ?></th>
								<th class="rs-stats-table__num"><?php esc_html_e( 'Reads', 'raisul-sohan' ); ?></th>
								<th class="rs-stats-table__num"><?php esc_html_e( 'Read', 'raisul-sohan' ); ?></th>
								<th class="rs-stats-table__num"><?php esc_html_e( 'Time', 'raisul-sohan' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $stories as $row ) :
								$reading = isset( $per_story[ $row['lang'] . '|' . $row['post_id'] ] ) ? $per_story[ $row['lang'] . '|' . $row['post_id'] ] : null;
								?>
								<tr>
									<td><?php rs_stats_item_link( $row['lang'], $row['post_id'] ); ?></td>
									<td class="rs-stats-table__num"><strong><?php echo esc_html( rs_stats_number( $row['hits'] ) ); ?></strong></td>
									<td class="rs-stats-table__num"><?php echo $reading ? esc_html( rs_stats_number( $reading['depth'] ) . '%' ) : '—'; ?></td>
									<td class="rs-stats-table__num"><?php echo $reading && $reading['seconds'] ? esc_html( rs_stats_duration( $reading['seconds'] ) ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php rs_stats_tile_close(); ?>

			<?php
			rs_stats_tile_open( __( 'When they come', 'raisul-sohan' ), __( 'Views by hour of the day across this whole period.', 'raisul-sohan' ) );
			rs_stats_hour_pattern( $hours );
			rs_stats_tile_close();
			?>
		</div>

		<div class="rs-stats__pair">
			<?php rs_stats_tile_open( __( 'Most visited pages', 'raisul-sohan' ), __( 'Everything that is not a story: the portfolio and each project, the CV, the showreel, the lists.', 'raisul-sohan' ) ); ?>
				<?php if ( empty( $pages ) ) : ?>
					<p class="rs-stats__empty"><?php esc_html_e( 'No page was opened in this period.', 'raisul-sohan' ); ?></p>
				<?php else : ?>
					<table class="rs-stats-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Page', 'raisul-sohan' ); ?></th>
								<th class="rs-stats-table__num"><?php esc_html_e( 'Views', 'raisul-sohan' ); ?></th>
								<th class="rs-stats-table__num"><?php esc_html_e( 'Share', 'raisul-sohan' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $pages as $row ) : ?>
								<tr>
									<td>
										<div class="rs-stats-table__page">
											<span><?php rs_stats_item_link( $row['lang'], 0, $row['path'] ); ?></span>
											<?php if ( '/' !== $row['path'] ) : ?>
												<code class="rs-stats-path"><?php echo esc_html( $row['path'] ); ?></code>
											<?php endif; ?>
										</div>
									</td>
									<td class="rs-stats-table__num"><strong><?php echo esc_html( rs_stats_number( $row['hits'] ) ); ?></strong></td>
									<td class="rs-stats-table__num"><?php echo esc_html( rs_stats_number( $totals['page_hits'] ? round( 100 * $row['hits'] / $totals['page_hits'] ) : 0 ) ); ?>%</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Where visits begin', 'raisul-sohan' ), __( 'The first page of a visit: where readers come in.', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					array_map(
						function ( $row ) {
							$row['hits'] = $row['visits'];
							return $row;
						},
						$entries
					),
					function ( $row ) {
						$item = $row['post_id'] ? rs_stats_story( $row['lang'], $row['post_id'] ) : rs_stats_page_name( $row['lang'], $row['path'] );
						return esc_html( $item['title'] ) . ( 'en' === $row['lang'] ? '<span class="rs-stats-tag">EN</span>' : '' );
					},
					$totals['visits'],
					__( 'Nothing to show yet.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>
		</div>

		<?php
		/*
		 * And the rest, packed into columns. Laid out in rows, every tile in a
		 * row was as tall as the tallest one in it, and a tile with four lines
		 * beside a tile with twelve left eight lines of nothing.
		 */
		?>
		<div class="rs-stats__bento">

			<?php rs_stats_tile_open( __( 'Climbing', 'raisul-sohan' ), __( 'Stories read far more than in the period before. Ones with a handful of reads are left out.', 'raisul-sohan' ) ); ?>
				<?php if ( empty( $climbing ) ) : ?>
					<p class="rs-stats__empty"><?php esc_html_e( 'Nothing is climbing in this period.', 'raisul-sohan' ); ?></p>
				<?php else : ?>
					<table class="rs-stats-table">
						<tbody>
							<?php foreach ( $climbing as $row ) : ?>
								<tr>
									<td><?php rs_stats_item_link( $row['lang'], $row['post_id'] ); ?></td>
									<td class="rs-stats-table__num"><span class="rs-stats-change rs-stats-change--up">▲ <?php echo esc_html( rs_stats_number( $row['change'] ) ); ?>%</span></td>
									<td class="rs-stats-table__num"><?php echo esc_html( rs_stats_number( $row['before'] ) . ' → ' . rs_stats_number( $row['now'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Which day', 'raisul-sohan' ), __( 'The days of the week readers turn up on.', 'raisul-sohan' ) ); ?>
				<?php
				$names = array(
					__( 'Sunday', 'raisul-sohan' ),
					__( 'Monday', 'raisul-sohan' ),
					__( 'Tuesday', 'raisul-sohan' ),
					__( 'Wednesday', 'raisul-sohan' ),
					__( 'Thursday', 'raisul-sohan' ),
					__( 'Friday', 'raisul-sohan' ),
					__( 'Saturday', 'raisul-sohan' ),
				);
				echo '<ul class="rs-stats-bars">';
				foreach ( $weekdays as $index => $value ) {
					rs_stats_bar( esc_html( $names[ $index ] ), $value, max( $weekdays ), array_sum( $weekdays ) );
				}
				echo '</ul>';
				?>
			<?php rs_stats_tile_close(); ?>

			<?php
			rs_stats_tile_open( __( 'How far they read', 'raisul-sohan' ), __( 'Measured as a reader leaves a story: a read count says it was opened, this says it was read.', 'raisul-sohan' ) );
			rs_stats_depth_bars( $depth );
			rs_stats_tile_close();
			?>

			<?php rs_stats_tile_open( __( 'Where visitors come from', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$sources,
					function ( $row ) {
						return esc_html( rs_stats_source_label( $row['label'] ) );
					},
					$totals['hits'],
					__( 'Nothing to show yet.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Where in the world', 'raisul-sohan' ), __( 'From the browser\'s own time zone, never from an address.', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$countries,
					function ( $row ) {
						return esc_html( rs_stats_country_label( $row['label'] ) );
					},
					$totals['hits'],
					__( 'Nothing to show yet.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'What they use', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$devices,
					function ( $row ) {
						return esc_html( rs_stats_plain_label( $row['label'] ) );
					},
					$totals['hits'],
					__( 'Nothing to show yet.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Which edition', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$langs,
					function ( $row ) {
						return esc_html( rs_stats_plain_label( $row['label'] ) );
					},
					$totals['hits'],
					__( 'Nothing to show yet.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Demos tried', 'raisul-sohan' ), __( 'Portfolio demos that were opened, from a card, a project page or the pop-up.', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$demos,
					function ( $row ) {
						return esc_html( $row['label'] );
					},
					0,
					__( 'No demo has been opened in this period.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Links they followed out', 'raisul-sohan' ), __( 'GitHub, YouTube, the stores and every other site a link here leads to.', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$outbound,
					function ( $row ) {
						return esc_html( $row['label'] );
					},
					0,
					__( 'No link out has been followed in this period.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Files downloaded', 'raisul-sohan' ), __( 'Files served from this site, such as the CV.', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$files,
					function ( $row ) {
						return esc_html( $row['label'] );
					},
					0,
					__( 'Nothing has been downloaded in this period.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Got in touch', 'raisul-sohan' ), __( 'Clicks on the email address, WhatsApp or the phone number, and the email copied from the header.', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$contact,
					function ( $row ) {
						return esc_html( $row['label'] );
					},
					0,
					__( 'Nobody has reached for a way to get in touch in this period.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'How they shared it', 'raisul-sohan' ), __( 'The share sheet and the copied links under a story.', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$shares,
					function ( $row ) {
						return esc_html( $row['label'] );
					},
					0,
					__( 'Nothing has been shared from the buttons in this period.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php rs_stats_tile_open( __( 'Saved to read later', 'raisul-sohan' ) ); ?>
				<?php
				rs_stats_bars(
					$saves,
					function ( $row ) {
						$item = rs_stats_story( $row['lang'], $row['post_id'] );
						return esc_html( $item['title'] ) . ( 'en' === $row['lang'] ? '<span class="rs-stats-tag">EN</span>' : '' );
					},
					0,
					__( 'Nobody has saved a story in this period.', 'raisul-sohan' )
				);
				?>
			<?php rs_stats_tile_close(); ?>

			<?php
			rs_stats_tile_open( __( 'Searches that found nothing', 'raisul-sohan' ), __( 'Readers came wanting these, and the site did not have them.', 'raisul-sohan' ) );
			rs_stats_search_table( $missed, 'miss', __( 'Every search so far has found something.', 'raisul-sohan' ) );
			rs_stats_tile_close();
			?>

			<?php
			rs_stats_tile_open( __( 'What they searched for', 'raisul-sohan' ), __( 'The term a reader settled on in the search box, all time.', 'raisul-sohan' ) );
			rs_stats_search_table( $searches, 'hits', __( 'Nobody has searched yet.', 'raisul-sohan' ) );
			rs_stats_tile_close();
			?>

			<?php rs_stats_tile_open( __( 'Addresses that led nowhere', 'raisul-sohan' ), __( 'Pages readers asked for and did not get. Scanners are left out.', 'raisul-sohan' ) ); ?>
				<?php if ( empty( $missing ) ) : ?>
					<p class="rs-stats__empty"><?php esc_html_e( 'Nobody has hit a missing page. Good.', 'raisul-sohan' ); ?></p>
				<?php else : ?>
					<table class="rs-stats-table">
						<tbody>
							<?php foreach ( $missing as $row ) : ?>
								<tr>
									<td>
										<code><?php echo esc_html( $row['path'] ); ?></code><?php rs_stats_edition_tag( $row['lang'] ); ?>
										<?php if ( $row['from'] && 'internal' !== $row['from'] && 'direct' !== $row['from'] ) : ?>
											<span class="rs-stats-from">
												<?php
												/* translators: %s: the site a broken link was followed from. */
												echo esc_html( sprintf( __( 'from %s', 'raisul-sohan' ), rs_stats_source_label( $row['from'] ) ) );
												?>
											</span>
										<?php elseif ( 'internal' === $row['from'] ) : ?>
											<span class="rs-stats-from rs-stats-from--own"><?php esc_html_e( 'linked from this site', 'raisul-sohan' ); ?></span>
										<?php endif; ?>
									</td>
									<td class="rs-stats-table__num"><?php echo esc_html( rs_stats_number( $row['hits'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<form method="post" class="rs-stats-clear">
						<?php wp_nonce_field( 'rs_stats_clear_missing' ); ?>
						<button type="submit" name="rs_stats_clear_missing" value="1" class="button button-small"><?php esc_html_e( 'Empty this list', 'raisul-sohan' ); ?></button>
					</form>
				<?php endif; ?>
			<?php rs_stats_tile_close(); ?>

		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * On the dashboard's own front page
 * ---------------------------------------------------------------------- */

/** A small panel: today at a glance, and the way through to the screen. */
function rs_stats_dashboard_widget() {
	if ( current_user_can( 'edit_posts' ) ) {
		wp_add_dashboard_widget( 'rs_stats_today', __( 'Statistics: today', 'raisul-sohan' ), 'rs_stats_dashboard_panel' );
	}
}
add_action( 'wp_dashboard_setup', 'rs_stats_dashboard_widget' );

/** What the panel shows: the three figures, and the most opened of the day. */
function rs_stats_dashboard_panel() {
	$totals = rs_stats_totals( '24h' );
	$before = rs_stats_totals( '24h', true );
	$top    = array();

	foreach ( rs_stats_top_stories( '24h', 4 ) as $row ) {
		$top[] = $row;
	}

	foreach ( rs_stats_top_pages( '24h', 4 ) as $row ) {
		$row['post_id'] = 0;
		$top[]          = $row;
	}

	usort(
		$top,
		function ( $a, $b ) {
			return $b['hits'] <=> $a['hits'];
		}
	);
	?>
	<div class="rs-dash">
		<div class="rs-dash__figures">
			<div class="rs-dash__figure">
				<strong><span class="rs-stats-dot" aria-hidden="true"></span><?php echo esc_html( rs_stats_number( rs_stats_pulse() ) ); ?></strong>
				<span><?php esc_html_e( 'right now', 'raisul-sohan' ); ?></span>
			</div>
			<div class="rs-dash__figure">
				<strong><?php echo esc_html( rs_stats_number( $totals['hits'] ) ); ?></strong>
				<span><?php esc_html_e( 'views today', 'raisul-sohan' ); ?></span>
			</div>
			<div class="rs-dash__figure">
				<strong><?php echo esc_html( rs_stats_number( $totals['visits'] ) ); ?></strong>
				<span><?php esc_html_e( 'visits', 'raisul-sohan' ); ?></span>
			</div>
		</div>

		<p class="rs-dash__change"><?php rs_stats_change( $totals['hits'], $before['hits'] ); ?></p>

		<?php if ( $top ) : ?>
			<ul class="rs-dash__top">
				<?php foreach ( array_slice( $top, 0, 5 ) as $row ) : ?>
					<li>
						<span class="rs-dash__name"><?php rs_stats_item_link( $row['lang'], $row['post_id'], isset( $row['path'] ) ? $row['path'] : '' ); ?></span>
						<span class="rs-dash__count"><?php echo esc_html( rs_stats_number( $row['hits'] ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="rs-stats__empty"><?php esc_html_e( 'Nothing has been opened yet today.', 'raisul-sohan' ); ?></p>
		<?php endif; ?>

		<p class="rs-dash__more"><a href="<?php echo esc_url( admin_url( 'admin.php?page=rs-stats' ) ); ?>"><?php esc_html_e( 'The whole picture', 'raisul-sohan' ); ?> &rarr;</a></p>
	</div>
	<?php
}
