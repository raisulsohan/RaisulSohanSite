	/* ---------------------------------------------------------------
	 * Service Worker helper
	 *
	 * Sends a message to the active service worker, if one is
	 * controlling this page.  Used by the "পরে পড়ব" handlers above
	 * to pre-cache and uncache post JSON.
	 * ------------------------------------------------------------ */

	function swMessage( msg ) {
		if ( navigator.serviceWorker && navigator.serviceWorker.controller ) {
			navigator.serviceWorker.controller.postMessage( msg );
		}
	}
