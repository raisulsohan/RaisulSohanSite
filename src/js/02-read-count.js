	/* ---------------------------------------------------------------
	 * Read count
	 *
	 * Counted from the browser rather than while the page renders,
	 * because the pages sit behind a full page cache: a reader served a
	 * cached copy never runs any PHP, so a count kept during a render
	 * would miss most of them.
	 *
	 * Every opening counts, including the same reader coming back. The
	 * one exclusion is whoever wrote the posts, and that is decided at
	 * the other end: the cookie travels with the request, and the server
	 * is the only side of this that can read it.
	 *
	 * Whether this is a first reading is the opposite case — the browser
	 * is the only side that knows, so it keeps the list of what it has
	 * opened and says so. That earns the post a reader as well as a
	 * reading.
	 * ------------------------------------------------------------ */

	var READ_KEY = 'rs-read';

	/*
	 * How many openings are remembered. It had no limit at all, so the entry
	 * grew by one id for every story ever opened and was never pruned — small
	 * in bytes, but a list that only grows is a list nobody has thought about.
	 * The other two lists hold 40 (where reading stopped) and 50 (read later);
	 * this one is allowed more because it answers "which of these have I read"
	 * over a whole archive, which is precisely the question a short list
	 * cannot answer. Oldest go first, as they do in the other two.
	 */
	var READ_KEEP = 200;

	function readList() {
		try {
			return window.localStorage.getItem( READ_KEY ) || '';
		} catch ( e ) {
			/* Private mode: every visit looks like a first one, which is
			   as close to the truth as this can get there. */
			return '';
		}
	}

	function readMark( id ) {
		return '|' + langKey( id ) + '|';
	}

	function hasRead( list, id ) {
		return list.indexOf( readMark( id ) ) > -1;
	}

	/* The stored shape is "|3||17||8|", so an id is always surrounded by
	   its own pair of bars and hasRead() can match on that pair alone. */
	function trimReadList( list ) {
		var ids = list.split( '|' ).filter( Boolean );

		if ( ids.length <= READ_KEEP ) {
			return list;
		}

		return ids.slice( ids.length - READ_KEEP ).map( function ( id ) {
			return '|' + id + '|';
		} ).join( '' );
	}

	/*
	 * Dim the rows this browser has already opened.
	 *
	 * The same list the count uses, put to a second purpose. Thirty
	 * stories can be held in the head; six hundred cannot, and "which of
	 * these have I read" is the question a long archive keeps asking.
	 */
	function markRead() {
		/* The list on the front page and archives, and the index, which is
		   the same question asked of a longer list: forty seven titles is
		   well past what anyone holds in their head. */
		var wrap = $( '#rs-list-wrap' ) || $( '.rs-index' );

		if ( ! wrap ) {
			return;
		}

		var read = readList();

		$$( '[data-rs-post]', wrap ).forEach( function ( link ) {
			var row = link.closest ? link.closest( '.rs-row, .rs-index__row' ) : null;

			if ( row && hasRead( read, link.getAttribute( 'data-rs-post' ) ) ) {
				row.classList.add( 'is-read' );
			}
		} );
	}

	/*
	 * `base` is the REST route of the edition the story belongs to, which is
	 * this site's own unless the reader asked for a translation: the number
	 * being counted lives on whichever site holds the story.
	 *
	 * The "already read" list is this edition's alone. It exists to grey out
	 * rows in the list on screen, and a translation has no row there — while
	 * its id would collide with a local story that does.
	 */
	function countView( id, base ) {
		if ( ! id || ! window.fetch ) {
			return;
		}

		var where = base || rest;
		var own = where === rest;
		var mark = readMark( id );
		var read = readList();
		var first = own && ! hasRead( read, id );

		/* same-origin is what carries the login cookie, which is how the
		   endpoint recognises the author and declines to count them. */
		window.fetch( where + 'view/' + id + ( first ? '?first=1' : '' ), {
			method: 'POST',
			credentials: 'same-origin',
			/* Lets the request finish even if the reader closes the tab
			   straight after opening the piece. */
			keepalive: true,
		} )
			.then( function ( res ) {
				return res.ok ? res.json() : null;
			} )
			.then( function ( data ) {
				/*
				 * The reply says whether this was counted, and that is the
				 * answer to a second question too: the author opens their
				 * own posts constantly, and a list where every row has
				 * gone grey tells them nothing. Only a reading the server
				 * accepted is worth remembering.
				 *
				 * Asked of the server rather than worked out here, because
				 * the page these scripts came in may well have been served
				 * from the cache, and a cached page looks logged out to
				 * everyone including whoever wrote it.
				 */
				if ( ! first || ! data || ! data.counted ) {
					return;
				}

				/* Remembered only once the server has it. A request that
				   never arrived should still be a first reading next
				   time. */
				try {
					window.localStorage.setItem( READ_KEY, trimReadList( read + mark ) );
				} catch ( e ) {
					/* As above. */
				}

				/* The row is still there behind the modal, and should be
				   dimmed by the time the reader closes it. */
				markRead();
			} )
			.then( null, function () {
				/* A missed count is not worth telling the reader about. */
			} );
	}
