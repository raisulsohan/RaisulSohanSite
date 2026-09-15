	/* ---------------------------------------------------------------
	 * Hover summary
	 * ------------------------------------------------------------ */

	( function () {
		var tip = $( '#rs-tooltip' );
		/* The wrapper rather than the list itself: turning a page replaces
		   everything inside it, and listeners bound to the old rows would
		   go with them. */
		var list = $( '#rs-list-wrap' );

		if ( ! tip || ! list || ! window.matchMedia || ! window.matchMedia( '(hover: hover)' ).matches ) {
			return;
		}

		var timer = null;
		var pos = { x: 0, y: 0 };

		function place() {
			var rect = tip.getBoundingClientRect();
			var left = pos.x + 14;
			var top = pos.y + 14;

			if ( left + rect.width > window.innerWidth - 8 ) {
				left = window.innerWidth - rect.width - 8;
			}

			if ( top + rect.height > window.innerHeight - 8 ) {
				top = pos.y - rect.height - 14;
			}

			tip.style.left = Math.max( 8, left ) + 'px';
			tip.style.top = Math.max( 8, top ) + 'px';
		}

		function hide() {
			window.clearTimeout( timer );
			tip.classList.remove( 'is-visible' );
		}

		list.addEventListener( 'mouseover', function ( event ) {
			var link = event.target.closest ? event.target.closest( '[data-rs-summary]' ) : null;

			if ( ! link ) {
				return;
			}

			var text = link.getAttribute( 'data-rs-summary' );

			if ( ! text ) {
				return;
			}

			pos.x = event.clientX;
			pos.y = event.clientY;

			window.clearTimeout( timer );
			timer = window.setTimeout( function () {
				tip.textContent = text;
				tip.classList.add( 'is-visible' );
				place();
			}, TOOLTIP_DELAY );
		} );

		list.addEventListener( 'mousemove', function ( event ) {
			pos.x = event.clientX;
			pos.y = event.clientY;

			if ( tip.classList.contains( 'is-visible' ) ) {
				place();
			}
		} );

		list.addEventListener( 'mouseout', function ( event ) {
			var link = event.target.closest ? event.target.closest( '[data-rs-summary]' ) : null;

			if ( link ) {
				hide();
			}
		} );

		window.addEventListener( 'scroll', hide, { passive: true } );
	}() );
