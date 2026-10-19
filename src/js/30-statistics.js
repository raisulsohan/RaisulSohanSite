	/* ---------------------------------------------------------------
	 * The site's own statistics: when the page reports
	 *
	 * The reports themselves are made by the helpers beside the read
	 * count (02-read-count.js) and stored by inc/21-statistics.php. This
	 * is where the page asks for them: once for the page, as a story is
	 * read, and for the few things a reader does that the server would
	 * never hear about.
	 * ------------------------------------------------------------ */

	( function () {
		var storyHere = parseInt( cfg.postId, 10 ) || 0;

		/*
		 * The page itself, once it is actually in front of somebody: a tab
		 * opened in the background is counted when it is first looked at,
		 * and one that never is, is not. A story at its own address is
		 * counted by countView() instead (the PWA part), which reports it
		 * to the statistics as well.
		 */
		function whenSeen( run ) {
			if ( 'hidden' !== document.visibilityState ) {
				run();
				return;
			}

			document.addEventListener( 'visibilitychange', function seen() {
				if ( 'hidden' === document.visibilityState ) {
					return;
				}

				document.removeEventListener( 'visibilitychange', seen );
				run();
			} );
		}

		if ( storyHere ) {
			readingStart( storyHere, rest, null );
		} else {
			whenSeen( function () {
				countPage( '', document.referrer );
			} );
		}

		/* A project opened in the portfolio's pop-up has its own address,
		   and is as much a page as the project page it stands in for. */
		document.addEventListener( 'rs:page', function ( event ) {
			if ( event.detail && event.detail.path ) {
				countPage( event.detail.path, window.location.href );
			}
		} );

		/* How far down the story: measured as the reader scrolls, the
		   modal's own box included (its scroll events reach the document
		   only on the way down). */
		var measuring = false;

		function onScroll() {
			if ( measuring || ! reading ) {
				return;
			}

			measuring = true;
			window.requestAnimationFrame( function () {
				measuring = false;
				readingMeasure();
			} );
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		document.addEventListener( 'scroll', onScroll, { passive: true, capture: true } );

		document.addEventListener( 'visibilitychange', function () {
			if ( 'hidden' === document.visibilityState ) {
				readingStop();
			} else {
				readingResume();
			}
		} );
		window.addEventListener( 'pagehide', readingStop );

		/* The story a control belongs to: the one in the modal when it is
		   inside the modal, the page's own otherwise. */
		function storyOf( el ) {
			if ( postOverlay && postOverlay.contains( el ) && currentPostId ) {
				return { id: currentPostId, base: currentPostRest || rest };
			}

			return { id: storyHere, base: rest };
		}

		/* Files worth knowing were taken: the CV, a release, a project file. */
		var FILES = /\.(pdf|zip|rar|7z|exe|msi|dmg|apk|aep|prproj|psd|mp4|mov)$/i;

		/*
		 * Where a link goes, short enough to read in a list and long enough
		 * to tell two projects apart: the site and the first two parts of
		 * the path, a YouTube video by its id, and a GitHub release marked
		 * as one, since that is the Download button.
		 */
		function linkLabel( link, host ) {
			var parts = link.pathname.split( '/' ).filter( Boolean );
			var video = /(^|\.)youtube\.com$/.test( host ) && 'watch' === parts[ 0 ] ? /[?&]v=([\w-]+)/.exec( link.search ) : null;

			if ( video ) {
				return host + '/watch?v=' + video[ 1 ];
			}

			return host + ( parts.length ? '/' + parts.slice( 0, 2 ).join( '/' ) : '' ) +
				( 'github.com' === host && 'releases' === parts[ 2 ] ? ' · release' : '' );
		}

		function noteLink( link, story ) {
			var href = link.href;

			/* An SVG link's href is an object, not an address. */
			if ( 'string' !== typeof href ) {
				return;
			}

			if ( /^mailto:/i.test( href ) ) {
				countEvent( 'contact', 'Email' );
				return;
			}

			if ( /^tel:/i.test( href ) ) {
				countEvent( 'contact', 'Phone' );
				return;
			}

			if ( ! /^https?:/i.test( href ) ) {
				return;
			}

			var host = link.hostname.replace( /^www\./, '' );

			if ( link.hostname === window.location.hostname ) {
				var file = link.pathname.split( '/' ).pop();

				if ( FILES.test( file ) ) {
					try {
						file = decodeURIComponent( file );
					} catch ( e ) {
						/* Kept as it is spelt. */
					}

					countEvent( 'file', file, story.id, story.base );
				}

				return;
			}

			if ( 'wa.me' === host || 'api.whatsapp.com' === host ) {
				countEvent( 'contact', 'WhatsApp' );
				return;
			}

			countEvent( 'out', linkLabel( link, host ), story.id, story.base );
		}

		/*
		 * In the capture phase, so each of these is heard before its own
		 * handler acts on it. That matters for the shelf: by the time its
		 * handler has run, a story being saved and a story being taken off
		 * look the same.
		 */
		document.addEventListener( 'click', function ( event ) {
			var el = event.target.closest
				? event.target.closest( '[data-rs-share], [data-rs-copy], [data-rs-later], [data-rs-demo-title], a[href]' )
				: null;

			if ( ! el ) {
				return;
			}

			var story = storyOf( el );

			if ( el.hasAttribute( 'data-rs-share' ) ) {
				if ( navigator.share ) {
					countEvent( 'share', 'Share sheet', story.id, story.base );
				}
			} else if ( el.hasAttribute( 'data-rs-copy' ) ) {
				if ( 'mail' === el.getAttribute( 'data-rs-copy-kind' ) ) {
					countEvent( 'contact', 'Copied the email address' );
				} else {
					countEvent( 'share', 'Copied the link', story.id, story.base );
				}
			} else if ( el.hasAttribute( 'data-rs-later' ) ) {
				if ( 'true' !== el.getAttribute( 'aria-pressed' ) ) {
					countEvent( 'save', '', el.getAttribute( 'data-rs-later' ), el.getAttribute( 'data-rs-later-rest' ) || rest );
				}
			} else if ( el.hasAttribute( 'data-rs-demo-title' ) ) {
				countEvent( 'demo', el.getAttribute( 'data-rs-demo-title' ) );
			} else {
				noteLink( el, story );
			}
		}, true );

		/* A link opened with the middle button never fires a click. */
		document.addEventListener( 'auxclick', function ( event ) {
			var link = 1 === event.button && event.target.closest ? event.target.closest( 'a[href]' ) : null;

			if ( link ) {
				noteLink( link, storyOf( link ) );
			}
		}, true );
	}() );
