	/* ---------------------------------------------------------------
	 * Scroll to top
	 * ------------------------------------------------------------ */

	( function () {
		var btn = $( '#rs-top' );

		if ( ! btn ) {
			return;
		}

		function update() {
			btn.classList.toggle( 'is-visible', window.scrollY > 300 );
		}

		window.addEventListener( 'scroll', update, { passive: true } );
		update();

		btn.addEventListener( 'click', function () {
			window.scrollTo( {
				top: 0,
				behavior: reduceMotion ? 'auto' : 'smooth',
			} );
		} );
	}() );
