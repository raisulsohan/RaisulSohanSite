	/* ---------------------------------------------------------------
	 * Remembering where the reader stopped
	 *
	 * One localStorage entry holding { p<id>: { t: scrollTop, n: title,
	 * u: url } }. Keys carry a "p" so they stay non-numeric: JavaScript
	 * orders integer-like object keys numerically, which would make the
	 * oldest-first pruning below throw away the wrong entries.
	 *
	 * The title and the address ride along so that the "you were reading"
	 * line on the front page needs nothing from the server. Entries left
	 * by an older version of this file are bare numbers; those still
	 * restore the scroll, they simply cannot be offered by name.
	 * ------------------------------------------------------------ */

	var POS_KEY = 'rs-pos';
	/* Below this the reader has barely started, and being dropped a
	   centimetre down the page is worse than starting at the top. */
	var POS_MIN = 200;
	var POS_KEEP = 40;

	function readPositions() {
		try {
			var map = JSON.parse( store( POS_KEY ) || '{}' ) || {};
			if ( map['p0'] ) {
				delete map['p0'];
				store( POS_KEY, JSON.stringify( map ) );
			}
			return map;
		} catch ( e ) {
			return {};
		}
	}

	/* Both shapes, as one shape. */
	function positionEntry( id ) {
		var found = readPositions()[ 'p' + id ];

		if ( ! found ) {
			return null;
		}

		if ( 'number' === typeof found ) {
			return { t: found, n: '', u: '' };
		}

		return found;
	}

	function savePosition( id, top, travel, title, url, catId ) {
		if ( ! id ) {
			return;
		}

		var map = readPositions();
		var key = 'p' + id;

		/* Deleted either way, then re-added: an object's keys keep the
		   order they were inserted in, so this also moves the entry to the
		   end, which is what makes the last one the most recent. */
		delete map[ key ];

		/* Forget the position once they have reached the end: coming back
		   to a finished post should start at the beginning. */
		if ( top >= POS_MIN && ! ( travel > 0 && top / travel > 0.9 ) ) {
			map[ key ] = {
				t: Math.round( top ),
				n: title || '',
				u: url || '',
				c: parseInt( catId, 10 ) || 0,
			};
		}

		var keys = Object.keys( map );

		while ( keys.length > POS_KEEP ) {
			delete map[ keys.shift() ];
		}

		store( POS_KEY, JSON.stringify( map ) );
	}

	/* Write the current post's position out now rather than on a timer,
	   used when the modal is about to close or swap posts. */
	function flushPosition() {
		if ( ! currentPostId || ! postBody || ! postBody.parentNode ) {
			return;
		}

		var scroller = postBody.parentNode;
		var known = cache[ currentPostId ] || {};

		savePosition(
			currentPostId,
			scroller.scrollTop,
			scroller.scrollHeight - scroller.clientHeight,
			known.title,
			known.link,
			known.categoryId
		);
	}

	function restoreScroll( scroller, target ) {
		scroller.scrollTop = target || 0;

		if ( ! target ) {
			return;
		}

		/* Images settle after the markup lands and push everything down, so
		   aim again once they have — but only while the reader has not
		   already taken over the scroll themselves. */
		var applied = scroller.scrollTop;
		var images = $$( 'img', scroller );
		var pending = images.length;

		if ( ! pending ) {
			return;
		}

		var settle = function () {
			pending -= 1;

			if ( pending > 0 || Math.abs( scroller.scrollTop - applied ) > 4 ) {
				return;
			}

			scroller.scrollTop = target;
			applied = scroller.scrollTop;
		};

		images.forEach( function ( img ) {
			if ( img.complete ) {
				settle();
				return;
			}

			img.addEventListener( 'load', settle, { once: true } );
			img.addEventListener( 'error', settle, { once: true } );
		} );
	}

	/*
	 * Anchor the modal's grow and shrink to whatever was clicked, so it
	 * flies out of that row and later drops back into it. This is the part
	 * of the macOS minimise that CSS can actually express: transforms are
	 * affine, so the genie's curved warp is out of reach, but "returns to
	 * where it came from" carries most of the meaning.
	 *
	 * Only set while the modal is closed. Reading on to the next post keeps
	 * the original row as the anchor rather than re-anchoring to a link
	 * inside the modal itself.
	 */
	function setModalOrigin( from ) {
		if ( ! postOverlay || ! postOverlay.hidden ) {
			return;
		}

		if ( ! from ) {
			postOverlay.style.removeProperty( '--rs-from-x' );
			postOverlay.style.removeProperty( '--rs-from-y' );
			return;
		}

		var box = from.getBoundingClientRect();

		postOverlay.style.setProperty(
			'--rs-from-x',
			Math.round( box.left + box.width / 2 - window.innerWidth / 2 ) + 'px'
		);
		postOverlay.style.setProperty(
			'--rs-from-y',
			Math.round( box.top + box.height / 2 - window.innerHeight / 2 ) + 'px'
		);
	}

	function openPost( id, url, push ) {
		if ( ! postOverlay || ! postBody ) {
			return;
		}

		var searchOverlay = $( '#rs-search-overlay' );

		if ( searchOverlay && ! searchOverlay.hidden ) {
			closeOverlay( searchOverlay );
		}

		/* Bank where the outgoing post was left before it is replaced. */
		flushPosition();
		currentPostId = id;

		postBody.innerHTML = '<p class="rs-search__hint">' + escapeHtml( strings.loading || 'আসছে...' ) + '</p>';
		openOverlay( postOverlay );

		if ( push ) {
			postDepth += 1;
			window.history.pushState( { rs: 'post', id: id, depth: postDepth }, '', url );
		}

		var done = function ( data ) {
			cache[ id ] = data;
			renderPost( data );
			/* Here rather than at the top of openPost(), so a fetch that
			   never arrives is not counted as a reading. */
			countView( id );
			/* An en dash, because that is what WordPress puts between a title
			   and the site name on the post's own page. The modal and the
			   page are the same reading; a reader flicking between browser
			   tabs should not be able to tell which one they are on. */
			document.title = data.title + ' – ' + ( cfg.siteName || baseTitle );

			var close = $( '.rs-modal__close', postOverlay );

			if ( close ) {
				close.focus();
			}
		};

		if ( cache[ id ] ) {
			done( cache[ id ] );
			return;
		}

		getJSON( rest + 'post/' + id ).then( done, function () {
			postBody.innerHTML =
				'<p class="rs-search__hint">' +
				escapeHtml( strings.error || 'লেখাটি আনা যায়নি' ) +
				' <a href="' + escapeHtml( url ) + '">' + escapeHtml( url ) + '</a></p>';
		} );
	}

	function closePost( fromPop ) {
		if ( ! postOverlay || postOverlay.hidden ) {
			return;
		}

		flushPosition();
		if ( typeof renderResume === 'function' ) renderResume();
		currentPostId = null;

		closeOverlay( postOverlay );
		document.title = baseTitle;

		if ( ! fromPop && postDepth > 0 ) {
			var steps = postDepth;

			postDepth = 0;
			window.history.go( -steps );
		}
	}
