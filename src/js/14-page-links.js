	/* ---------------------------------------------------------------
	 * Page links
	 *
	 * The links are ordinary /page/2/ anchors, so with JavaScript off they
	 * load that page the usual way. With it on, the click is caught and the
	 * server is asked for the same list on its own (?rs_ajax=1), which is
	 * dropped in place of the current one: the hero keeps its phrase, the
	 * header stays put, and only the rows change.
	 *
	 * The server decides what is on a page, so a category, a tag and a
	 * search each paginate over their own posts without this code knowing
	 * that any of them exist.
	 * ------------------------------------------------------------ */

	( function () {
		var wrap = $( '#rs-list-wrap' );

		if ( ! wrap || ! window.fetch ) {
			return;
		}

		/* A back press can arrive while a click's page is still on its way.
		   Whichever request was asked for last is the one the reader is
		   waiting on, so earlier replies are dropped rather than queued. */
		var seq = 0;

		function fragmentUrl( url ) {
			return url + ( url.indexOf( '?' ) > -1 ? '&' : '?' ) + 'rs_ajax=1';
		}

		/* The header is sticky, so the top of the list has to clear it or
		   the first row lands underneath. */
		function scrollToList() {
			var header = $( '.rs-header' );
			var clear = header ? header.offsetHeight : 0;
			var top = wrap.getBoundingClientRect().top + window.pageYOffset - clear - 16;

			window.scrollTo( {
				top: Math.max( 0, top ),
				behavior: reduceMotion ? 'auto' : 'smooth',
			} );
		}

		function swap( html ) {
			var holder = document.createElement( 'div' );

			holder.innerHTML = html;

			var next = holder.firstElementChild;

			/* A login screen, a maintenance page or a plugin's redirect
			   would all come back with a 200 and something else entirely.
			   Better to hand the URL to the browser than to paste it in. */
			if ( ! next || 'rs-list-wrap' !== next.id ) {
				throw new Error( 'unexpected fragment' );
			}

			/* The contents, not the element: the hover summary listeners
			   are bound to this wrapper and have to survive the swap. */
			wrap.innerHTML = next.innerHTML;

			var title = next.getAttribute( 'data-rs-title' );

			if ( title ) {
				document.title = title;
				/* Closing a post modal restores this, so it has to follow
				   whichever page of the list is underneath. */
				baseTitle = title;
			}

			staggerRows( wrap );
		}

		function load( url, push ) {
			url = absUrl( url );

			var mine = ++seq;

			wrap.classList.add( 'is-loading' );

			window.fetch( fragmentUrl( url ), { credentials: 'same-origin' } )
				.then( function ( res ) {
					if ( ! res.ok ) {
						throw new Error( res.status );
					}

					return res.text();
				} )
				.then( function ( html ) {
					if ( mine !== seq ) {
						return;
					}

					swap( html );
					markRead();
					markLater();

					listUrl = url;
					wrap.classList.remove( 'is-loading' );

					if ( push ) {
						window.history.pushState( { rs: 'list' }, '', url );
					}

					/* Nothing was focused after the old rows went away, so
					   a keyboard reader would be back at the top of the
					   document. preventScroll keeps this from fighting the
					   smooth scroll; browsers that ignore it simply jump
					   to the same place. */
					wrap.focus( { preventScroll: true } );
					scrollToList();
				} )
				.then( null, function () {
					if ( mine !== seq ) {
						return;
					}

					/* The URL is a real page whatever went wrong here, so
					   let the browser go and fetch it properly. */
					window.location.href = url;
				} );
		}

		document.addEventListener( 'click', function ( event ) {
			var link = event.target.closest ? event.target.closest( '.rs-pagination a' ) : null;

			if ( ! link ) {
				return;
			}

			if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0 ) {
				return;
			}

			event.preventDefault();
			load( link.href, true );
		} );

		loadListPage = load;
	}() );
