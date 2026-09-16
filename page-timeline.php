<?php
/**
 * Template Name: Story Timeline
 *
 * Every story laid out like a composition in After Effects: the months are
 * the ruler, each category is a layer, each story is a clip whose length is
 * its reading time. Long stretches with nothing published fold into a
 * single "break" so the clips are not stranded at the two ends.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* The same dark stage as the portfolio. */
add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'rs-stage';
		return $classes;
	}
);

get_header();

$rs_en  = rs_is_en();
$rs_num = function ( $n ) use ( $rs_en ) {
	return $rs_en ? (string) $n : rs_bn_digits( $n );
};

$rs_posts = get_posts(
	array(
		'post_type'        => 'post',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'orderby'          => 'date',
		'order'            => 'ASC',
		'suppress_filters' => false,
	)
);

/* Scale: pixels per month, per folded break, per minute of reading. */
$rs_month_w = 210;
$rs_gap_w   = 130;
$rs_px_min  = 5;
$rs_clip_lo = 34;
$rs_lane_h  = 32;
$rs_palette = array( '#ea77ff', '#31a8ff', '#3ddc97', '#ffd166', '#ff9a00', '#b9a8ff', '#ff6b8b', '#5ce1e6' );

$rs_items   = array();
$rs_months  = array();
$rs_total   = 0;

foreach ( $rs_posts as $rs_post ) {
	$rs_min = (int) get_post_meta( $rs_post->ID, RS_MINUTES_KEY, true );

	if ( ! $rs_min ) {
		rs_reading_time( $rs_post );
		$rs_min = max( 1, (int) get_post_meta( $rs_post->ID, RS_MINUTES_KEY, true ) );
	}

	$rs_term = rs_primary_category( $rs_post );
	$rs_y    = (int) get_post_time( 'Y', false, $rs_post );
	$rs_m    = (int) get_post_time( 'n', false, $rs_post );

	$rs_items[] = array(
		'id'    => $rs_post->ID,
		'title' => rs_plain_title( $rs_post ),
		'url'   => get_permalink( $rs_post ),
		'date'  => rs_bn_date( $rs_post ),
		'y'     => $rs_y,
		'm'     => $rs_m,
		'd'     => (int) get_post_time( 'j', false, $rs_post ),
		'min'   => $rs_min,
		'cat'   => $rs_term ? (int) $rs_term->term_id : 0,
		'name'  => $rs_term ? $rs_term->name : ( $rs_en ? 'Other' : 'অন্যান্য' ),
	);

	$rs_months[ sprintf( '%04d-%02d', $rs_y, $rs_m ) ] = true;
	$rs_total += $rs_min;
}

/* The ruler: a segment per month, with runs of three or more empty months
   folded into one break. */
$rs_segments = array();
$rs_month_x  = array();
$rs_x        = 0;

if ( $rs_items ) {
	$rs_first = $rs_items[0];
	$rs_last  = $rs_items[ count( $rs_items ) - 1 ];
	$rs_y     = $rs_first['y'];
	$rs_m     = $rs_first['m'];
	$rs_empty = array();

	while ( $rs_y < $rs_last['y'] || ( $rs_y === $rs_last['y'] && $rs_m <= $rs_last['m'] ) ) {
		$rs_key = sprintf( '%04d-%02d', $rs_y, $rs_m );

		if ( isset( $rs_months[ $rs_key ] ) ) {
			if ( count( $rs_empty ) > 2 ) {
				$rs_span  = count( $rs_empty );
				$rs_years = floor( ( $rs_span + 1 ) / 12 );
				$rs_label = $rs_years >= 1
					? ( $rs_en ? $rs_years . ( 1 == $rs_years ? ' year later' : ' years later' ) : $rs_num( $rs_years ) . ' বছর পর' )
					: ( $rs_en ? ( $rs_span + 1 ) . ' months later' : $rs_num( $rs_span + 1 ) . ' মাস পর' );

				$rs_segments[] = array(
					'type'  => 'gap',
					'x'     => $rs_x,
					'w'     => $rs_gap_w,
					'label' => $rs_label,
				);
				$rs_x         += $rs_gap_w;
			} else {
				foreach ( $rs_empty as $rs_e ) {
					$rs_segments[] = array(
						'type'  => 'month',
						'x'     => $rs_x,
						'w'     => $rs_month_w,
						'y'     => $rs_e[0],
						'm'     => $rs_e[1],
						'days'  => (int) gmdate( 't', gmmktime( 0, 0, 0, $rs_e[1], 1, $rs_e[0] ) ),
						'label' => ( $rs_en ? gmdate( 'M', gmmktime( 0, 0, 0, $rs_e[1], 1, 2000 ) ) : rs_bn_months()[ $rs_e[1] ] ) . ' ' . $rs_num( $rs_e[0] ),
					);
					$rs_x         += $rs_month_w;
				}
			}

			$rs_empty = array();

			$rs_month_x[ $rs_key ] = $rs_x;
			$rs_segments[]         = array(
				'type'  => 'month',
				'x'     => $rs_x,
				'w'     => $rs_month_w,
				'y'     => $rs_y,
				'm'     => $rs_m,
				'days'  => (int) gmdate( 't', gmmktime( 0, 0, 0, $rs_m, 1, $rs_y ) ),
				'label' => ( $rs_en ? gmdate( 'M', gmmktime( 0, 0, 0, $rs_m, 1, 2000 ) ) : rs_bn_months()[ $rs_m ] ) . ' ' . $rs_num( $rs_y ),
			);
			$rs_x                 += $rs_month_w;
		} else {
			$rs_empty[] = array( $rs_y, $rs_m );
		}

		if ( 12 === $rs_m ) {
			$rs_m = 1;
			++$rs_y;
		} else {
			++$rs_m;
		}
	}
}

$rs_width = $rs_x + 60;

/* Layers: one per category, busiest first, clips stacked into lanes so
   none overlap. */
$rs_layers = array();

foreach ( $rs_items as $rs_item ) {
	if ( ! isset( $rs_layers[ $rs_item['cat'] ] ) ) {
		$rs_layers[ $rs_item['cat'] ] = array(
			'name'  => $rs_item['name'],
			'clips' => array(),
		);
	}

	$rs_key  = sprintf( '%04d-%02d', $rs_item['y'], $rs_item['m'] );
	$rs_days = (int) gmdate( 't', gmmktime( 0, 0, 0, $rs_item['m'], 1, $rs_item['y'] ) );

	$rs_item['x'] = (int) round( $rs_month_x[ $rs_key ] + ( ( $rs_item['d'] - 1 ) / $rs_days ) * $rs_month_w );
	$rs_item['w'] = (int) max( $rs_clip_lo, min( $rs_month_w * 1.5, 14 + $rs_item['min'] * $rs_px_min ) );

	$rs_layers[ $rs_item['cat'] ]['clips'][] = $rs_item;
}

uasort(
	$rs_layers,
	function ( $a, $b ) {
		return count( $b['clips'] ) - count( $a['clips'] );
	}
);

foreach ( $rs_layers as $rs_cat => $rs_layer ) {
	$rs_ends = array();

	foreach ( $rs_layer['clips'] as $rs_i => $rs_clip ) {
		$rs_lane = 0;

		while ( isset( $rs_ends[ $rs_lane ] ) && $rs_ends[ $rs_lane ] + 6 > $rs_clip['x'] ) {
			++$rs_lane;
		}

		$rs_ends[ $rs_lane ] = $rs_clip['x'] + $rs_clip['w'];

		$rs_layers[ $rs_cat ]['clips'][ $rs_i ]['lane'] = $rs_lane;
	}

	$rs_layers[ $rs_cat ]['lanes'] = max( 1, count( $rs_ends ) );
}

$rs_first_year = $rs_items ? $rs_items[0]['y'] : 0;
$rs_last_year  = $rs_items ? $rs_items[ count( $rs_items ) - 1 ]['y'] : 0;
$rs_hours      = max( 1, (int) round( $rs_total / 60 ) );
?>

<main class="rs-pf rs-tlx" id="rs-content">
	<div class="rs-pf__glow rs-pf__glow--a" aria-hidden="true"></div>
	<div class="rs-pf__glow rs-pf__glow--b" aria-hidden="true"></div>

	<header class="rs-pf__wrap rs-tlx__head">
		<p class="rs-pf__eyebrow"><?php echo esc_html( $rs_en ? 'Archive' : 'আর্কাইভ' ); ?></p>
		<h1 class="rs-pf__title rs-tlx__title">
			<?php if ( $rs_en ) : ?>
				Every story, on <em>one timeline</em>
			<?php else : ?>
				সব লেখা, <em>একটা টাইমলাইনে</em>
			<?php endif; ?>
		</h1>
		<p class="rs-pf__bio">
			<?php
			echo esc_html(
				$rs_en
					? 'Each category is a layer and each story a clip, as long as it takes to read. Hover a clip for its title, click to read.'
					: 'প্রতিটি বিভাগ একটা লেয়ার, প্রতিটি লেখা একটা ক্লিপ, যার দৈর্ঘ্য তার পড়ার সময়ের সমান। ক্লিপে মাউস রাখলে শিরোনাম, ক্লিক করলে লেখা।'
			);
			?>
		</p>

		<dl class="rs-pf__stats">
			<div>
				<dt><?php echo esc_html( $rs_en ? 'Stories' : 'লেখা' ); ?></dt>
				<dd><?php echo esc_html( $rs_num( count( $rs_items ) ) ); ?></dd>
			</div>
			<div>
				<dt><?php echo esc_html( $rs_en ? 'Layers' : 'লেয়ার' ); ?></dt>
				<dd><?php echo esc_html( $rs_num( count( $rs_layers ) ) ); ?></dd>
			</div>
			<div>
				<dt><?php echo esc_html( $rs_en ? 'Hours of reading' : 'ঘণ্টা পড়ার সময়' ); ?></dt>
				<dd><?php echo esc_html( $rs_num( $rs_hours ) ); ?></dd>
			</div>
			<?php if ( $rs_first_year ) : ?>
				<div>
					<dt><?php echo esc_html( $rs_en ? 'Span' : 'সময়কাল' ); ?></dt>
					<dd><?php echo esc_html( $rs_num( $rs_first_year ) . ( $rs_last_year !== $rs_first_year ? '–' . $rs_num( $rs_last_year ) : '' ) ); ?></dd>
				</div>
			<?php endif; ?>
		</dl>
	</header>

	<section class="rs-pf__wrap rs-tlx__section" aria-label="<?php echo esc_attr( $rs_en ? 'Story timeline' : 'লেখার টাইমলাইন' ); ?>">
		<div class="rs-tlx__panel">
			<div class="rs-tlx__scroller" tabindex="0" data-rs-tlx data-segments="<?php echo esc_attr( wp_json_encode( $rs_segments ) ); ?>" data-months="<?php echo esc_attr( wp_json_encode( $rs_en ? array() : array_values( rs_bn_months_full() ) ) ); ?>" data-en="<?php echo $rs_en ? '1' : '0'; ?>">
				<div class="rs-tlx__grid" style="--w: <?php echo (int) $rs_width; ?>px">

					<div class="rs-tlx__row rs-tlx__row--ruler">
						<div class="rs-tlx__name rs-tlx__corner"><span class="rs-tlx__tc" data-rs-tlx-tc><?php echo esc_html( $rs_en ? 'Timeline' : 'টাইমলাইন' ); ?></span></div>
						<div class="rs-tlx__ruler" aria-hidden="true">
							<?php foreach ( $rs_segments as $rs_seg ) : ?>
								<span class="rs-tlx__mark<?php echo 'gap' === $rs_seg['type'] ? ' is-gap' : ''; ?>" style="left: <?php echo (int) $rs_seg['x']; ?>px; width: <?php echo (int) $rs_seg['w']; ?>px"><?php echo esc_html( $rs_seg['label'] ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>

					<?php $rs_li = 0; ?>
					<?php foreach ( $rs_layers as $rs_layer ) : ?>
						<?php $rs_color = $rs_palette[ $rs_li % count( $rs_palette ) ]; ?>
						<div class="rs-tlx__row" style="--c: <?php echo esc_attr( $rs_color ); ?>; --lanes: <?php echo (int) $rs_layer['lanes']; ?>">
							<div class="rs-tlx__name">
								<span class="rs-tlx__label"><?php echo esc_html( $rs_layer['name'] ); ?></span>
								<span class="rs-tlx__count"><?php echo esc_html( $rs_num( count( $rs_layer['clips'] ) ) ); ?></span>
							</div>
							<div class="rs-tlx__track">
								<?php foreach ( $rs_segments as $rs_seg ) : ?>
									<?php if ( 'gap' === $rs_seg['type'] ) : ?>
										<i class="rs-tlx__gap" style="left: <?php echo (int) $rs_seg['x']; ?>px; width: <?php echo (int) $rs_seg['w']; ?>px" aria-hidden="true"></i>
									<?php endif; ?>
								<?php endforeach; ?>
								<?php foreach ( $rs_layer['clips'] as $rs_clip ) : ?>
									<a class="rs-tlx__clip" href="<?php echo esc_url( $rs_clip['url'] ); ?>" data-rs-post="<?php echo (int) $rs_clip['id']; ?>" data-date="<?php echo esc_attr( $rs_clip['date'] ); ?>" data-min="<?php echo esc_attr( $rs_num( $rs_clip['min'] ) ); ?>" style="left: <?php echo (int) $rs_clip['x']; ?>px; width: <?php echo (int) $rs_clip['w']; ?>px; top: <?php echo (int) ( 6 + $rs_clip['lane'] * $rs_lane_h ); ?>px">
										<span><?php echo esc_html( $rs_clip['title'] ); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
						<?php ++$rs_li; ?>
					<?php endforeach; ?>

					<span class="rs-tlx__cursor" aria-hidden="true"></span>
				</div>
			</div>
			<span class="rs-tlx__tip" role="tooltip" aria-hidden="true"></span>
		</div>
		<p class="rs-tlx__hint">
			<?php echo esc_html( $rs_en ? 'Drag the timeline, or scroll sideways, to travel through the years.' : 'বছরের পর বছর ঘুরে দেখতে টাইমলাইনটা টেনে সরান, বা পাশে স্ক্রল করুন।' ); ?>
		</p>
	</section>
</main>

<script>
/* The timeline archive: a cursor with its date, a tooltip, drag to pan. */
(function () {
	'use strict';

	var scroller = document.querySelector('[data-rs-tlx]');
	if (!scroller) {
		return;
	}

	var grid     = scroller.querySelector('.rs-tlx__grid');
	var cursor   = scroller.querySelector('.rs-tlx__cursor');
	var tc       = scroller.querySelector('[data-rs-tlx-tc]');
	var tip      = scroller.parentNode.querySelector('.rs-tlx__tip');
	var ruler    = scroller.querySelector('.rs-tlx__ruler');
	var segments = [];
	var months   = [];
	var en       = '1' === scroller.getAttribute('data-en');
	var digits   = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
	var tcIdle   = tc ? tc.textContent : '';

	try {
		segments = JSON.parse(scroller.getAttribute('data-segments') || '[]');
		months   = JSON.parse(scroller.getAttribute('data-months') || '[]');
	} catch (e) {}

	function num(n) {
		return en ? String(n) : String(n).replace(/\d/g, function (d) {
			return digits[d];
		});
	}

	function dateAt(x) {
		for (var i = 0; i < segments.length; i++) {
			var s = segments[i];

			if (x >= s.x && x < s.x + s.w) {
				if ('gap' === s.type) {
					return s.label;
				}

				var day = Math.min(s.days, Math.floor(((x - s.x) / s.w) * s.days) + 1);
				var name = en
					? new Date(Date.UTC(s.y, s.m - 1, 1)).toLocaleString('en', { month: 'short', timeZone: 'UTC' })
					: months[s.m - 1];

				return en ? name + ' ' + day + ', ' + s.y : num(day) + ' ' + name + ' ' + num(s.y);
			}
		}

		return '';
	}

	/* Newest first: open at the right-hand end. */
	scroller.scrollLeft = scroller.scrollWidth;

	scroller.addEventListener('pointermove', function (e) {
		var r = ruler.getBoundingClientRect();
		var x = e.clientX - r.left;

		if (x < 0 || e.clientX > scroller.getBoundingClientRect().right) {
			cursor.classList.remove('is-on');
			if (tc) {
				tc.textContent = tcIdle;
			}
			return;
		}

		cursor.style.left = (ruler.offsetLeft + x) + 'px';
		cursor.classList.add('is-on');

		if (tc) {
			tc.textContent = dateAt(x) || tcIdle;
		}
	});

	scroller.addEventListener('pointerleave', function () {
		cursor.classList.remove('is-on');
		if (tc) {
			tc.textContent = tcIdle;
		}
	});

	/* Tooltip for the clip under the pointer or the keyboard. */
	function showTip(clip) {
		var r = clip.getBoundingClientRect();
		var box = scroller.parentNode.getBoundingClientRect();
		var title = clip.textContent.trim();

		tip.textContent = '';
		var strong = document.createElement('strong');
		strong.textContent = title;
		var meta = document.createElement('span');
		meta.textContent = clip.getAttribute('data-date') + ' · ' + clip.getAttribute('data-min') + (en ? ' min' : ' মিনিট');
		tip.appendChild(strong);
		tip.appendChild(meta);

		tip.style.left = Math.max(8, Math.min(box.width - 280, r.left - box.left)) + 'px';
		tip.style.top = (r.top - box.top - 8) + 'px';
		tip.classList.add('is-on');
	}

	function hideTip() {
		tip.classList.remove('is-on');
	}

	grid.addEventListener('pointerover', function (e) {
		var clip = e.target.closest ? e.target.closest('.rs-tlx__clip') : null;
		if (clip) {
			showTip(clip);
		}
	});

	grid.addEventListener('pointerout', function (e) {
		var clip = e.target.closest ? e.target.closest('.rs-tlx__clip') : null;
		if (clip && !clip.contains(e.relatedTarget)) {
			hideTip();
		}
	});

	grid.addEventListener('focusin', function (e) {
		if (e.target.classList && e.target.classList.contains('rs-tlx__clip')) {
			showTip(e.target);
		}
	});

	grid.addEventListener('focusout', hideTip);
	scroller.addEventListener('scroll', hideTip, { passive: true });

	/* Drag with a mouse to pan; touch scrolls natively. */
	var drag = null;

	scroller.addEventListener('pointerdown', function (e) {
		if ('mouse' !== e.pointerType || 0 !== e.button || (e.target.closest && e.target.closest('.rs-tlx__clip'))) {
			return;
		}

		drag = { x: e.clientX, left: scroller.scrollLeft, moved: false };
		scroller.classList.add('is-dragging');
	});

	window.addEventListener('pointermove', function (e) {
		if (!drag) {
			return;
		}

		var dx = e.clientX - drag.x;
		if (Math.abs(dx) > 3) {
			drag.moved = true;
		}
		scroller.scrollLeft = drag.left - dx;
	});

	window.addEventListener('pointerup', function () {
		drag = null;
		scroller.classList.remove('is-dragging');
	});
}());
</script>

<?php
get_footer();
