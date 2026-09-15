	/* ---------------------------------------------------------------
	 * Reader font size
	 * ------------------------------------------------------------ */

	var fontIdx = parseInt( store( 'rs-font' ), 10 );

	if ( isNaN( fontIdx ) || fontIdx < 0 || fontIdx > SIZES.length - 1 ) {
		fontIdx = FONT_DEFAULT;
	}

	function applyFont() {
		document.documentElement.style.setProperty( '--rs-post-size', SIZES[ fontIdx ] + 'px' );

		$$( '[data-rs-font]' ).forEach( function ( btn ) {
			var action = btn.getAttribute( 'data-rs-font' );

			if ( 'down' === action ) {
				btn.disabled = 0 === fontIdx;
			} else if ( 'up' === action ) {
				btn.disabled = SIZES.length - 1 === fontIdx;
			}
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest ? event.target.closest( '[data-rs-font]' ) : null;

		if ( ! btn ) {
			return;
		}

		var action = btn.getAttribute( 'data-rs-font' );

		if ( 'down' === action ) {
			fontIdx = Math.max( 0, fontIdx - 1 );
		} else if ( 'up' === action ) {
			fontIdx = Math.min( SIZES.length - 1, fontIdx + 1 );
		} else {
			fontIdx = FONT_DEFAULT;
		}

		store( 'rs-font', String( fontIdx ) );
		applyFont();
	} );

	applyFont();
