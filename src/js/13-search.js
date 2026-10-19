	/* ---------------------------------------------------------------
	 * Search
	 * ------------------------------------------------------------ */

	var searchOverlay = $( '#rs-search-overlay' );
	var searchInput = $( '#rs-search-input' );
	var searchResults = $( '#rs-search-results' );
	var searchCount = $( '#rs-search-count' );
	var searchTimer = null;
	var searchSeq = 0;

	/* The term the reader settled on and how many it found, for the site's
	   statistics. Reported once, when they open a result, close the box or
	   leave the page: the letters typed on the way to it are not searches. */
	var searchSeen = null;

	function reportSearch() {
		if ( ! searchSeen || searchSeen.sent ) {
			return;
		}

		searchSeen.sent = true;
		tell( 'event', { kind: 'search', label: searchSeen.term, found: searchSeen.total } );
	}

	if ( searchOverlay ) {
		searchOverlay.addEventListener( 'rs:closed', reportSearch );
		window.addEventListener( 'pagehide', reportSearch );
	}

	function openSearch() {
		openOverlay( searchOverlay );

		if ( searchInput ) {
			searchInput.focus();
			searchInput.select();
		}

		if ( searchResults && '' === searchInput.value.trim() ) {
			searchResults.innerHTML = '<p class="rs-search__hint">' + escapeHtml( strings.hint || '' ) + '</p>';
		}
	}

	function renderResults( data, term ) {
		if ( ! searchSeen || searchSeen.term !== term ) {
			searchSeen = { term: term, total: data.total || 0 };
		}

		if ( ! data.items.length ) {
			searchResults.innerHTML = '<p class="rs-search__hint">' + escapeHtml( strings.noResult || '' ) + '</p>';
			searchCount.hidden = true;
			return;
		}

		var html = data.items
			.map( function ( item ) {
				return (
					'<button class="rs-result" type="button" data-rs-result="' +
					item.id +
					'" data-href="' + escapeHtml( item.link ) + '">' +
					'<span class="rs-result__head">' +
					'<span class="rs-result__title">' + highlight( item.title, term ) + '</span>' +
					'<span class="rs-result__date">' + escapeHtml( item.date ) + '</span>' +
					'</span>' +
					( item.snippet
						? '<span class="rs-result__snippet">' + highlight( item.snippet, term ) + '</span>'
						: '' ) +
					'</button>'
				);
			} )
			.join( '' );

		searchResults.innerHTML = html;
		searchCount.textContent = bnDigits( data.total ) + ( strings.results || 'টি ফলাফল' );
		searchCount.hidden = false;
	}

	if ( searchInput ) {
		searchInput.addEventListener( 'input', function () {
			var term = searchInput.value.trim();

			window.clearTimeout( searchTimer );

			/* The hint stays up until there are two characters. One letter
			   matches most of the archive, which is not an answer, and the
			   endpoint declines it for the same reason. */
			if ( term.length < 2 ) {
				searchResults.innerHTML = '<p class="rs-search__hint">' + escapeHtml( strings.hint || '' ) + '</p>';
				searchCount.hidden = true;
				/* Typed away again: nothing was settled on. */
				if ( searchSeen && ! searchSeen.sent ) {
					searchSeen = null;
				}
				return;
			}

			var seq = ++searchSeq;

			searchTimer = window.setTimeout( function () {
				getJSON( rest + 'search?q=' + encodeURIComponent( term ) ).then(
					function ( data ) {
						if ( seq !== searchSeq ) {
							return;
						}
						renderResults( data, term );
					},
					function () {
						if ( seq !== searchSeq ) {
							return;
						}
						searchResults.innerHTML =
							'<p class="rs-search__hint">' + escapeHtml( strings.error || '' ) + '</p>';
						searchCount.hidden = true;
					}
				);
			}, 220 );
		} );
	}

	if ( searchResults ) {
		searchResults.addEventListener( 'click', function ( event ) {
			var btn = event.target.closest ? event.target.closest( '.rs-result' ) : null;

			if ( ! btn ) {
				return;
			}

			reportSearch();
			setModalOrigin( btn );
			openPost( btn.getAttribute( 'data-rs-result' ), btn.getAttribute( 'data-href' ), true );
		} );
	}
