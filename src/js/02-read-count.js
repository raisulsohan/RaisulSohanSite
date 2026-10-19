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
	 *
	 * The same request is the story's line in the site's own statistics, so
	 * it also says where the reader came from: the page before, when the
	 * story is the page they `landed` on, and this site when it was opened
	 * in the modal from one of its lists.
	 */
	function countView( id, base, landed ) {
		if ( ! id || ! window.fetch ) {
			return;
		}

		var where = base || rest;
		var own = where === rest;
		var mark = readMark( id );
		var read = readList();
		var first = own && ! hasRead( read, id );
		var fields = new URLSearchParams( pageFields( landed ? document.referrer : window.location.href ) );

		if ( first ) {
			fields.append( 'first', '1' );
		}

		/* same-origin is what carries the login cookie, which is how the
		   endpoint recognises the author and declines to count them. */
		window.fetch( where + 'view/' + id, {
			method: 'POST',
			body: fields,
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

	/* ---------------------------------------------------------------
	 * The site's own statistics
	 *
	 * Every page and story opened is reported once, with where the
	 * reader came from, the browser's time zone (which is how the
	 * country is known without an address) and whether it starts a
	 * visit. Nothing that names the reader travels with any of it. The
	 * server side is inc/21-statistics.php; the page asks for these in
	 * the script's last part, 30-statistics.js.
	 *
	 * A visit is the usual rule: half an hour without a page ends one.
	 * The time of the last page is the one thing kept for it, here in
	 * the browser, and the server only ever hears the 1 or the 0.
	 * ------------------------------------------------------------ */

	var VISIT_KEY = 'rs-visit';
	var VISIT_GAP = 30 * 60 * 1000;

	function visitStarts() {
		var now = Date.now();
		var last = parseInt( store( VISIT_KEY ) || '0', 10 );

		store( VISIT_KEY, String( now ) );

		return ! last || now - last > VISIT_GAP;
	}

	function timeZone() {
		try {
			return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
		} catch ( e ) {
			return '';
		}
	}

	/* What every page report carries. Asking starts or extends the visit,
	   so it is asked exactly once for each page counted. */
	function pageFields( from ) {
		return {
			ref: from || '',
			visit: visitStarts() ? '1' : '0',
			tz: timeZone(),
		};
	}

	/* One report. A beacon, which outlives the page being closed straight
	   after; fetch with keepalive where there is none. Neither answer is
	   waited for: a missed count is not worth anything to the reader. */
	function tell( route, fields, base ) {
		var url = ( base || rest ) + route;
		var body = new URLSearchParams( fields );

		try {
			if ( navigator.sendBeacon && navigator.sendBeacon( url, body ) ) {
				return;
			}
		} catch ( e ) {
			/* Falls through to fetch. */
		}

		if ( window.fetch ) {
			window.fetch( url, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true } ).then( null, function () {} );
		}
	}

	/* A page that is not a story: the one loaded, a page of the list
	   fetched in place, a project opened in the portfolio's pop-up. The
	   404 page says so about itself, and its address goes on the list of
	   broken links. */
	function countPage( path, from ) {
		var fields = pageFields( from );

		fields.path = path || window.location.pathname;

		if ( ! path && document.body.classList.contains( 'error404' ) ) {
			fields.nf = '1';
		}

		tell( 'hit', fields );
	}

	/* Something the reader did: a link out, a share, a save, a demo. */
	function countEvent( kind, label, id, base ) {
		tell( 'event', { kind: kind, label: label || '', id: id || 0 }, base );
	}

	/*
	 * How far into a story the reader got, and how long it was on screen.
	 *
	 * Kept while they read and sent once, as they leave it: the modal
	 * closing, the next story taking its place, or the page being hidden
	 * or closed. Hidden counts as leaving, because on a phone it is often
	 * the last thing a page hears before it is thrown away. A story on
	 * screen for under a second was not read, and is not reported.
	 */
	var reading = null;

	function readingStart( id, base, scroller ) {
		readingStop();

		reading = {
			id: id,
			base: base || rest,
			scroller: scroller || null,
			max: 0,
			since: 'hidden' === document.visibilityState ? 0 : Date.now(),
			spent: 0,
		};

		readingMeasure();
	}

	/* The furthest the bottom of the screen has reached down the story's
	   text: the modal's own box when it is in the modal, the window when
	   it is a page of its own. */
	function readingMeasure() {
		var text = reading ? $( '.rs-article__body', reading.scroller || document ) : null;

		if ( ! text ) {
			return;
		}

		var box = text.getBoundingClientRect();
		var bottom = reading.scroller ? reading.scroller.getBoundingClientRect().bottom : window.innerHeight;
		var pct = box.height > 0 ? Math.round( ( bottom - box.top ) / box.height * 100 ) : 0;

		reading.max = Math.max( reading.max, Math.min( 100, pct ) );
	}

	/* For a story opened while its tab was in the background: the clock
	   starts when it is first looked at. */
	function readingResume() {
		if ( reading && ! reading.since ) {
			reading.since = Date.now();
		}
	}

	function readingStop() {
		if ( ! reading ) {
			return;
		}

		readingMeasure();

		var done = reading;
		var spent = done.spent + ( done.since ? Date.now() - done.since : 0 );

		reading = null;

		if ( done.max > 0 && spent >= 1000 ) {
			tell( 'depth', { id: done.id, depth: done.max, secs: Math.round( spent / 1000 ) }, done.base );
		}
	}
