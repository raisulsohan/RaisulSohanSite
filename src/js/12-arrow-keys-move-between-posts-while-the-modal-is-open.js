	/* ---------------------------------------------------------------
	 * Arrow keys move between posts while the modal is open
	 * ------------------------------------------------------------ */

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'ArrowLeft' !== event.key && 'ArrowRight' !== event.key ) {
			return;
		}

		if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}

		if ( ! postOverlay || postOverlay.hidden ) {
			return;
		}

		/* Leave the caret alone when the reader is typing. */
		var active = document.activeElement;

		if ( active && /^(INPUT|TEXTAREA|SELECT)$/.test( active.tagName ) ) {
			return;
		}

		var link = $(
			'ArrowLeft' === event.key
				? '.rs-nextprev a:not(.rs-nextprev__next)'
				: '.rs-nextprev .rs-nextprev__next',
			postOverlay
		);

		if ( ! link ) {
			return;
		}

		event.preventDefault();

		/* Goes through the delegated click handler below, so the history
		   entry, the fly-from origin and the fetch all behave exactly as
		   they do for a real click. */
		link.click();
	} );

	/* Intercept list clicks, but leave modified clicks alone so that
	   "open in new tab" still works. */
	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '[data-rs-post]' ) : null;

		if ( ! link ) {
			return;
		}

		if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0 ) {
			return;
		}

		event.preventDefault();
		setModalOrigin( link );
		openPost( link.getAttribute( 'data-rs-post' ), link.getAttribute( 'href' ), true );
	} );

	/* "Any one of them". The href is a redirect to the post's own page and
	   is what happens without JavaScript; with it, the story opens in the
	   modal like every other story on the list. */
	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '[data-rs-random]' ) : null;

		if ( ! link || ! window.fetch ) {
			return;
		}

		if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0 ) {
			return;
		}

		event.preventDefault();

		var cat = link.getAttribute( 'data-rs-random' );
		var where = cat && '0' !== cat ? '?cat=' + encodeURIComponent( cat ) : '';

		getJSON( rest + 'random' + where ).then(
			function ( data ) {
				setModalOrigin( link );
				openPost( data.id, data.link, true );
			},
			function () {
				/* Let the plain link do what it was always going to. */
				window.location.href = link.href;
			}
		);
	} );

	if ( document.body.classList.contains( 'rs-is-list' ) ) {
		/* Same tag the page links push, so every list entry in the history
		   looks alike and only 'post' is the special case below. */
		window.history.replaceState( { rs: 'list' }, '', window.location.href );

		window.addEventListener( 'popstate', function ( event ) {
			var state = event.state;

			if ( state && 'post' === state.rs ) {
				/* Trust the entry's own depth: the reader may have arrived
				   here by back or forward, not just by opening posts. */
				postDepth = state.depth || 1;
				/* Arrived by back or forward, so there is no row to fly
				   from: grow from the centre instead. */
				setModalOrigin( null );
				openPost( state.id, window.location.href, false );
			} else {
				postDepth = 0;
				closePost( true );

				/* Opening and closing a post always returns to the URL the
				   list was on, so anything else here means the reader moved
				   between pages of the list. */
				if ( loadListPage && listUrl !== absUrl( window.location.href ) ) {
					loadListPage( window.location.href, false );
				}
			}
		} );
	}
