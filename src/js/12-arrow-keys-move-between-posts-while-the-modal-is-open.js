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

	/*
	 * The same story in the other language.
	 *
	 * Only while the modal is open, and only for a link inside it: on a
	 * story's own page this is an ordinary link to an ordinary page, and the
	 * browser should be left to follow it. Inside the modal the translation
	 * is fetched from the other edition's own route and swapped in, so the
	 * reader keeps the list behind them and the place they were reading.
	 */
	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '[data-rs-lang]' ) : null;

		if ( ! link || ! window.fetch ) {
			return;
		}

		if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0 ) {
			return;
		}

		if ( ! postOverlay || postOverlay.hidden || ! postOverlay.contains( link ) ) {
			return;
		}

		/* An unsaved edit owns the modal until it is saved or given up. */
		if ( ! editorMayClose() ) {
			event.preventDefault();
			return;
		}

		event.preventDefault();

		openPost( link.getAttribute( 'data-rs-lang' ), link.getAttribute( 'href' ), true, {
			rest: link.getAttribute( 'data-rs-lang-rest' ),
			lang: link.getAttribute( 'data-rs-lang-code' ),
		} );
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
				/* The edition was written into the entry when it was pushed;
				   entries from before that carry none and are this site's. */
				openPost( state.id, window.location.href, false, {
					rest: state.rest,
					lang: state.lang,
				} );
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
