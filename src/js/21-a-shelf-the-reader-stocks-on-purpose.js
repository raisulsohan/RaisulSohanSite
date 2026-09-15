	/* ---------------------------------------------------------------
	 * পরে পড়ব — a shelf the reader stocks on purpose
	 *
	 * The two lists above both watch: one records what has been opened,
	 * the other where a story was put down. Neither catches the thought
	 * a long piece most often provokes, which is "not now" — because
	 * nothing has happened yet when the reader thinks it. That one has
	 * to be said out loud, so it gets a button.
	 *
	 * One localStorage entry, { p<id>: { n: title, u: url, r: reading
	 * time } }. The "p" prefix is there for the same reason as in the
	 * positions above: JavaScript orders integer-like object keys
	 * numerically, which would throw away the insertion order this list
	 * is drawn from.
	 *
	 * The title and the address are kept alongside the id so the front
	 * page can draw the list without asking the server anything — which
	 * is what lets it work on a page served from the cache.
	 * ------------------------------------------------------------ */

	var LATER_KEY = 'rs-later';
	var LATER_KEEP = 50;

	function readLater() {
		try {
			return JSON.parse( store( LATER_KEY ) || '{}' ) || {};
		} catch ( e ) {
			/* Private mode, or something else wrote here. Either way an
			   empty shelf is the honest answer. */
			return {};
		}
	}

	/*
	 * A row already carries everything worth saving, so it is read rather
	 * than repeated into attributes ten times over. The button in a post's
	 * share row has no row to read, and is told instead.
	 */
	function laterEntry( btn ) {
		var row = btn.closest ? btn.closest( '.rs-row' ) : null;

		if ( row ) {
			var link = $( '.rs-row__link', row );
			var title = $( '.rs-row__title', row );
			var read = $( '.rs-row__read', row );

			return {
				n: title ? title.textContent.trim() : '',
				u: link ? link.getAttribute( 'href' ) : '',
				r: read ? read.textContent.trim() : '',
			};
		}

		return {
			n: btn.getAttribute( 'data-rs-later-title' ) || '',
			u: btn.getAttribute( 'data-rs-later-url' ) || '',
			r: btn.getAttribute( 'data-rs-later-time' ) || '',
		};
	}

	/* Every toggle on the page, told what the shelf currently holds. Runs
	   again after the list is paginated and after the modal is built,
	   because both put new buttons on the page. */
	function markLater() {
		var map = readLater();
		var removeText = ( strings && strings.removeLater ) || 'তালিকা থেকে সরান';
		var laterText  = ( strings && strings.readLater ) || 'পরে পড়ব';
		var inText     = ( strings && strings.inLater ) || 'তালিকায় আছে';

		$$( '[data-rs-later]' ).forEach( function ( btn ) {
			var on = !! map[ 'p' + btn.getAttribute( 'data-rs-later' ) ];
			var text = $( '[data-rs-later-text]', btn );

			btn.classList.toggle( 'is-saved', on );
			btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			btn.setAttribute( 'title', on ? removeText : laterText );

			/* The icon-only buttons in the list have no label to swap. */
			if ( text ) {
				text.textContent = on ? inText : laterText;
			} else {
				btn.setAttribute( 'aria-label', on ? removeText : laterText );
			}
		} );
	}

	function renderLater() {
		var host = $( '#rs-later' );

		if ( ! host ) {
			return;
		}

		var map = readLater();
		var keys = Object.keys( map );
		var html = '';
		var removeText = ( strings && strings.removeLater ) || 'তালিকা থেকে সরান';
		var laterText  = ( strings && strings.readLater ) || 'পরে পড়ব';

		/* Backwards, so the story just put aside is the one at the top. */
		for ( var i = keys.length - 1; i >= 0; i-- ) {
			var entry = map[ keys[ i ] ];

			if ( ! entry || ! entry.u || ! entry.n ) {
				continue;
			}

			var id = keys[ i ].slice( 1 );

			html += '<li class="rs-later__item">' +
				'<a href="' + escapeHtml( entry.u ) + '" data-rs-post="' + escapeHtml( id ) + '">' +
				'<span class="rs-related__title">' + escapeHtml( entry.n ) + '</span>' +
				'<span class="rs-related__meta">' + escapeHtml( entry.r || '' ) + '</span>' +
				'</a>' +
				'<button class="rs-later__drop" type="button" data-rs-later-drop="' +
				escapeHtml( id ) + '" aria-label="' + escapeHtml( removeText ) + '">&times;</button>' +
				'</li>';
		}

		host.innerHTML = html
			? '<nav class="rs-related rs-later" aria-label="' + escapeHtml( laterText ) + '">' +
				'<p class="rs-related__label">' + escapeHtml( laterText ) + '</p>' +
				'<ul class="rs-related__list">' + html + '</ul></nav>'
			: '';
	}

	function dropLater( id ) {
		var map = readLater();

		delete map[ 'p' + id ];
		store( LATER_KEY, JSON.stringify( map ) );
		swMessage( { type: 'UNCACHE_POST', url: rest + 'post/' + id } );

		markLater();
		renderLater();
	}

	document.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest ) {
			return;
		}

		var drop = event.target.closest( '[data-rs-later-drop]' );

		if ( drop ) {
			event.preventDefault();
			dropLater( drop.getAttribute( 'data-rs-later-drop' ) );
			return;
		}

		var btn = event.target.closest( '[data-rs-later]' );

		if ( ! btn ) {
			return;
		}

		event.preventDefault();

		var id = btn.getAttribute( 'data-rs-later' );
		var entry = laterEntry( btn );

		/* Without a title and an address the entry could be stored but never
		   drawn, which would look like the button doing nothing. */
		if ( ! id || ! entry.n || ! entry.u ) {
			return;
		}

		var map = readLater();
		var key = 'p' + id;
		var saving = ! map[ key ];

		if ( saving ) {
			map[ key ] = entry;

			var keys = Object.keys( map );

			while ( keys.length > LATER_KEEP ) {
				delete map[ keys.shift() ];
			}
		} else {
			delete map[ key ];
		}

		store( LATER_KEY, JSON.stringify( map ) );
		swMessage( { type: saving ? 'CACHE_POST' : 'UNCACHE_POST', url: rest + 'post/' + id } );

		markLater();
		renderLater();

		var addedText   = ( strings && strings.laterAdded ) || 'পরে পড়ার তালিকায় রাখা হলো';
		var removedText = ( strings && strings.laterRemoved ) || 'তালিকা থেকে সরানো হলো';
		toast( saving ? addedText : removedText );
	} );

	/* The buttons only do something once this file has run, so they stay
	   out of sight until it has. Same arrangement as the share button. */
	document.body.classList.add( 'rs-can-save' );

	markLater();
	renderLater();
