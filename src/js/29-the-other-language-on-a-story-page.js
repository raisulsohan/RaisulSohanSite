	/* ---------------------------------------------------------------
	 * The other language, on a story read at its own address
	 *
	 * The reading modal keeps the story's other-language link in its
	 * corner once the one in the meta row has scrolled away. A story
	 * opened on its own page had nothing of the sort: the link went up
	 * with the date and stayed there. Same rule here, against the window
	 * and under the sticky header: never two on screen at once, and never
	 * a trip back to the top to change language. The link itself is the
	 * ordinary one, printed by PHP; this only decides when it shows.
	 * ------------------------------------------------------------ */

	( function () {
		var corner = $( '#rs-page-lang' );

		if ( ! corner ) {
			return;
		}

		var pill = $( '.rs-article__meta .rs-lang-pill' );

		if ( ! pill || ! corner.querySelector( '.rs-lang-pill' ) ) {
			return;
		}

		var header = $( '.rs-header' );
		var ticking = false;

		function update() {
			ticking = false;

			var edge = header ? header.getBoundingClientRect().bottom : 0;

			corner.classList.toggle( 'is-visible', pill.getBoundingClientRect().bottom < edge + 4 );
		}

		function onScroll() {
			if ( ticking ) {
				return;
			}

			ticking = true;
			window.requestAnimationFrame( update );
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll );
		update();
	}() );
