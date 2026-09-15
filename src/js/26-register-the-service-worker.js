	/* ---------------------------------------------------------------
	 * Register the service worker
	 * ------------------------------------------------------------ */

	( function () {
		if ( ! ( 'serviceWorker' in navigator ) ) {
			return;
		}

		navigator.serviceWorker.register( '/?rs-sw', { scope: '/' } ).catch( function () {
			/* Registration can fail in private mode or restrictive
			   environments.  The site works without it. */
		} );
	}() );
