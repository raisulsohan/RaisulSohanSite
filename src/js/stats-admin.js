/**
 * Admin-only page bundle: the pointer on the statistics chart
 * (inc/22-statistics-screen.php).
 *
 * The pointer reads the figure off the line: a dashed guide, a dot on the
 * nearest point, and a small card with the views, the visits and when it
 * was. The points are in percentages of the plot, written into
 * data-rs-chart by the server, so nothing here needs to know the scale.
 */
( function () {
	'use strict';

	Array.prototype.forEach.call( document.querySelectorAll( '[data-rs-chart]' ), function ( chart ) {
		var points;

		try {
			points = JSON.parse( chart.getAttribute( 'data-rs-chart' ) || '[]' );
		} catch ( e ) {
			return;
		}

		if ( ! points.length ) {
			return;
		}

		var plot = chart.querySelector( '.rs-stats-chart__plot' );
		var guide = chart.querySelector( '.rs-stats-chart__guide' );
		var dot = chart.querySelector( '.rs-stats-chart__dot' );
		var tip = chart.querySelector( '.rs-stats-chart__tip' );
		var value = chart.querySelector( '.rs-stats-chart__tip-value' );
		var visits = chart.querySelector( '.rs-stats-chart__tip-visits' );
		var when = chart.querySelector( '.rs-stats-chart__tip-when' );

		function show( clientX ) {
			var box = plot.getBoundingClientRect();

			if ( ! box.width ) {
				return;
			}

			var inside = ( clientX - box.left ) / box.width * 100;
			var nearest = 0;

			for ( var i = 1; i < points.length; i++ ) {
				if ( Math.abs( points[ i ].x - inside ) < Math.abs( points[ nearest ].x - inside ) ) {
					nearest = i;
				}
			}

			var point = points[ nearest ];

			guide.style.left = point.x + '%';
			dot.style.left = point.x + '%';
			dot.style.top = point.y + '%';
			guide.hidden = false;
			dot.hidden = false;

			value.textContent = point.v;
			visits.textContent = point.s;
			when.textContent = point.l;
			tip.hidden = false;

			/*
			 * Beside the guide, never over it: to the right of the point in
			 * the left half of the chart, to the left of it in the right
			 * half, and level with the point as far as the plot allows. A
			 * card above the point had nowhere to go when the point was near
			 * the top, and sat on the very dot and line it was explaining.
			 */
			var x = point.x / 100 * box.width + plot.offsetLeft;
			var y = point.y / 100 * box.height + plot.offsetTop;
			var width = tip.offsetWidth;
			var height = tip.offsetHeight;
			var left = point.x > 50 ? x - 14 - width : x + 14;
			var top = Math.min( plot.offsetTop + box.height - height, y - height / 2 );

			tip.style.left = Math.max( 0, Math.min( chart.clientWidth - width, left ) ) + 'px';
			tip.style.top = Math.max( plot.offsetTop, top ) + 'px';
		}

		function hide() {
			guide.hidden = true;
			dot.hidden = true;
			tip.hidden = true;
		}

		chart.addEventListener( 'mousemove', function ( e ) {
			show( e.clientX );
		} );
		chart.addEventListener( 'mouseleave', hide );
		chart.addEventListener( 'touchstart', function ( e ) {
			if ( e.touches[ 0 ] ) {
				show( e.touches[ 0 ].clientX );
			}
		}, { passive: true } );
		chart.addEventListener( 'touchmove', function ( e ) {
			if ( e.touches[ 0 ] ) {
				show( e.touches[ 0 ].clientX );
			}
		}, { passive: true } );
		chart.addEventListener( 'touchend', hide );
	} );
}() );
