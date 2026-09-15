	/* ---------------------------------------------------------------
	 * Overlays
	 * ------------------------------------------------------------ */

	var openOverlays = [];
	var lastFocus = null;

	function lockBody( locked ) {
		/* The overlays sit after the footer, so everything before them can
		   be taken out of the tab order and the accessibility tree while
		   one is open; a screen reader then cannot wander behind it. */
		var behind = document.querySelectorAll( '.rs-header, main, .rs-footer' );

		for ( var i = 0; i < behind.length; i++ ) {
			if ( locked ) {
				behind[ i ].setAttribute( 'inert', '' );
			} else {
				behind[ i ].removeAttribute( 'inert' );
			}
		}

		if ( locked ) {
			document.body.classList.add( 'rs-locked' );
		} else {
			document.body.classList.remove( 'rs-locked' );
		}
	}

	function openOverlay( el ) {
		if ( ! el || openOverlays.indexOf( el ) > -1 ) {
			return;
		}

		if ( openOverlays.length === 0 ) {
			lastFocus = document.activeElement;
			lockBody( true );
		}

		el.hidden = false;
		openOverlays.push( el );

		var focusable = $( 'input, button, [href]', el );

		if ( focusable ) {
			focusable.focus();
		}
	}

	function closeOverlay( el ) {
		if ( ! el ) {
			return;
		}

		var at = openOverlays.indexOf( el );

		if ( at > -1 ) {
			openOverlays.splice( at, 1 );
		}

		el.hidden = true;

		if ( openOverlays.length === 0 ) {
			lockBody( false );

			if ( lastFocus && lastFocus.focus ) {
				lastFocus.focus();
			}
		}
	}

	function topOverlay() {
		return openOverlays[ openOverlays.length - 1 ] || null;
	}

	/* Click on the backdrop closes. */
	$$( '.rs-overlay' ).forEach( function ( overlay ) {
		overlay.addEventListener( 'mousedown', function ( event ) {
			if ( event.target === overlay ) {
				requestClose( overlay );
			}
		} );
	} );

	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest ? event.target.closest( '[data-rs-close]' ) : null;

		if ( ! btn ) {
			return;
		}

		var overlay = btn.closest( '.rs-overlay' );
		requestClose( overlay );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key && 'Esc' !== event.key ) {
			return;
		}

		var overlay = topOverlay();

		if ( overlay ) {
			event.preventDefault();
			requestClose( overlay );
		}
	} );

	/* Keep Tab inside the open modal. */
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Tab' !== event.key ) {
			return;
		}

		var overlay = topOverlay();

		if ( ! overlay ) {
			return;
		}

		var items = $$( 'a[href], button:not([disabled]), input, [tabindex]:not([tabindex="-1"])', overlay )
			.filter( function ( el ) {
				return el.offsetParent !== null;
			} );

		if ( items.length === 0 ) {
			return;
		}

		var first = items[ 0 ];
		var last = items[ items.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	function requestClose( overlay ) {
		if ( ! overlay ) {
			return;
		}

		if ( 'rs-post-overlay' === overlay.id ) {
			/* An unsaved edit owns the modal until it is saved or given
			   up. Escape and the backdrop both arrive here. */
			if ( ! editorMayClose() ) {
				return;
			}

			closePost( false );
			return;
		}

		closeOverlay( overlay );
	}

	/* Header buttons. */
	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest ? event.target.closest( '[data-rs-open]' ) : null;

		if ( ! btn ) {
			return;
		}

		event.preventDefault();
		var name = btn.getAttribute( 'data-rs-open' );

		if ( 'about' === name ) {
			openOverlay( $( '#rs-about-overlay' ) );
		} else if ( 'search' === name ) {
			openSearch();
		}
	} );
