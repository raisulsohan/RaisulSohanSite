/**
 * Raisul Sohan theme.
 *
 * Everything the front page needs: the typing heading, reader font size,
 * hover summaries, the post modal (with real URLs behind it), search,
 * lazy list batches and toasts.
 */
( function () {
	'use strict';

	var cfg = window.RS || {};
	var strings = cfg.strings || {};
	var rest = cfg.rest || '/wp-json/rs/v1/';

	/* Five steps, two either side of the default. FONT_DEFAULT must stay
	   the middle one: the "A" button resets to it, and its value has to
	   match --rs-post-size in style.css, which is what renders before
	   this script runs. */
	var SIZES = [ 16, 17.5, 19, 21, 23 ];
	var FONT_DEFAULT = 2;
	var TOOLTIP_DELAY = 600;

	/* Hero reveal. Durations must stay in sync with .rs-word in style.css. */
	var HERO_IN_DUR = 900;
	var HERO_IN_STEP = 110;
	var HERO_OUT_DUR = 520;
	var HERO_OUT_STEP = 55;
	var HERO_HOLD = 2600;

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---------------------------------------------------------------
	 * Small helpers
	 * ------------------------------------------------------------ */

	function $( sel, root ) {
		return ( root || document ).querySelector( sel );
	}

	function $$( sel, root ) {
		return Array.prototype.slice.call( ( root || document ).querySelectorAll( sel ) );
	}

	function bnDigits( value ) {
		return String( value ).replace( /[0-9]/g, function ( d ) {
			return '০১২৩৪৫৬৭৮৯'.charAt( Number( d ) );
		} );
	}

	function escapeHtml( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#039;' );
	}

	function escapeRegex( str ) {
		return String( str ).replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
	}

	/* Resolve a URL the way the browser would, so a link's href and
	   location.href can be compared without one of them being relative. */
	function absUrl( url ) {
		var a = document.createElement( 'a' );

		a.href = url;

		return a.href;
	}

	function highlight( text, term ) {
		var safe = escapeHtml( text );

		if ( ! term ) {
			return safe;
		}

		try {
			var re = new RegExp( escapeRegex( escapeHtml( term ) ), 'gi' );
			return safe.replace( re, function ( match ) {
				return '<mark>' + match + '</mark>';
			} );
		} catch ( e ) {
			return safe;
		}
	}

	function store( key, value ) {
		try {
			if ( typeof value === 'undefined' ) {
				return window.localStorage.getItem( key );
			}
			window.localStorage.setItem( key, value );
		} catch ( e ) {
			/* Private mode. Not important enough to bother the reader about. */
		}
		return null;
	}

	/* Set --i on each .rs-row so CSS can stagger the fade-up animation. */
	function staggerRows( root ) {
		if ( ! cfg.animations ) {
			return;
		}
		var rows = ( root || document ).querySelectorAll( '.rs-row' );
		for ( var i = 0; i < rows.length; i++ ) {
			rows[ i ].style.setProperty( '--i', i );
		}
	}

	function getJSON( url ) {
		return window.fetch( url, { credentials: 'same-origin' } ).then( function ( res ) {
			if ( ! res.ok ) {
				throw new Error( res.status );
			}
			return res.json();
		} );
	}

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

	function readList() {
		try {
			return window.localStorage.getItem( READ_KEY ) || '';
		} catch ( e ) {
			/* Private mode: every visit looks like a first one, which is
			   as close to the truth as this can get there. */
			return '';
		}
	}

	function hasRead( list, id ) {
		return list.indexOf( '|' + id + '|' ) > -1;
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

	function countView( id ) {
		if ( ! id || ! window.fetch ) {
			return;
		}

		var mark = '|' + id + '|';
		var read = readList();
		var first = ! hasRead( read, id );

		/* same-origin is what carries the login cookie, which is how the
		   endpoint recognises the author and declines to count them. */
		window.fetch( rest + 'view/' + id + ( first ? '?first=1' : '' ), {
			method: 'POST',
			credentials: 'same-origin',
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
					window.localStorage.setItem( READ_KEY, read + mark );
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
	 * Toasts
	 * ------------------------------------------------------------ */

	function toast( title, description ) {
		var host = $( '#rs-toasts' );

		if ( ! host ) {
			return;
		}

		var el = document.createElement( 'div' );
		el.className = 'rs-toast';
		el.innerHTML =
			'<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">' +
			'<circle cx="12" cy="12" r="10" fill="currentColor"/>' +
			'<path d="m8.5 12.4 2.4 2.4 4.6-5" fill="none" stroke="#faf9f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
			'</svg><div></div>';

		var text = el.lastChild;
		var head = document.createElement( 'div' );
		head.className = 'rs-toast__title';
		head.textContent = title;
		text.appendChild( head );

		if ( description ) {
			var desc = document.createElement( 'div' );
			desc.className = 'rs-toast__desc';
			desc.textContent = description;
			text.appendChild( desc );
		}

		host.appendChild( el );

		window.setTimeout( function () {
			el.classList.add( 'is-out' );
			window.setTimeout( function () {
				if ( el.parentNode ) {
					el.parentNode.removeChild( el );
				}
			}, 200 );
		}, 2500 );
	}

	/* ---------------------------------------------------------------
	 * Clipboard
	 * ------------------------------------------------------------ */

	function copyText( value ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( value );
		}

		return new Promise( function ( resolve, reject ) {
			try {
				var area = document.createElement( 'textarea' );
				area.value = value;
				area.setAttribute( 'readonly', '' );
				area.style.position = 'fixed';
				area.style.opacity = '0';
				document.body.appendChild( area );
				area.select();
				document.execCommand( 'copy' );
				document.body.removeChild( area );
				resolve();
			} catch ( e ) {
				reject( e );
			}
		} );
	}

	/* ---------------------------------------------------------------
	 * Handing a story on
	 *
	 * Copying a link is a step on the way to what the reader meant, which
	 * is nearly always to send it to somebody. Where the browser can open
	 * the sheet they already send things with, that is one step instead
	 * of three. Where it cannot, the button never appears and copying is
	 * still there.
	 * ------------------------------------------------------------ */

	if ( navigator.share ) {
		document.documentElement.classList.add( 'rs-can-share' );
	}

	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest ? event.target.closest( '[data-rs-share]' ) : null;

		if ( ! btn || ! navigator.share ) {
			return;
		}

		event.preventDefault();

		navigator.share( {
			title: btn.getAttribute( 'data-rs-share-title' ) || document.title,
			url: btn.getAttribute( 'data-rs-share' ),
		} ).then( null, function () {
			/* Cancelling the sheet rejects. That is a decision, not a
			   failure, and it needs no toast. */
		} );
	} );

	document.addEventListener( 'click', function ( event ) {
		var trigger = event.target.closest ? event.target.closest( '[data-rs-copy]' ) : null;

		if ( ! trigger ) {
			return;
		}

		event.preventDefault();

		var value = trigger.getAttribute( 'data-rs-copy' );
		var isMail = 'mail' === trigger.getAttribute( 'data-rs-copy-kind' );

		copyText( value ).then(
			function () {
				if ( isMail ) {
					toast( strings.copied || 'Mail copied!', value );
				} else {
					toast( strings.linkCopy || 'লিঙ্ক কপি হয়েছে', value );
				}
			},
			function () {
				toast( strings.copyFail || 'কপি হয়নি' );
			}
		);
	} );

	/* ---------------------------------------------------------------
	 * Reader font size
	 * ------------------------------------------------------------ */

	var fontIdx = parseInt( store( 'rs-font' ), 10 );

	if ( isNaN( fontIdx ) || fontIdx < 0 || fontIdx > SIZES.length - 1 ) {
		fontIdx = FONT_DEFAULT;
	}

	function applyFont() {
		document.documentElement.style.setProperty( '--rs-post-size', SIZES[ fontIdx ] + 'px' );

		$$( '[data-rs-font]' ).forEach( function ( btn ) {
			var action = btn.getAttribute( 'data-rs-font' );

			if ( 'down' === action ) {
				btn.disabled = 0 === fontIdx;
			} else if ( 'up' === action ) {
				btn.disabled = SIZES.length - 1 === fontIdx;
			}
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest ? event.target.closest( '[data-rs-font]' ) : null;

		if ( ! btn ) {
			return;
		}

		var action = btn.getAttribute( 'data-rs-font' );

		if ( 'down' === action ) {
			fontIdx = Math.max( 0, fontIdx - 1 );
		} else if ( 'up' === action ) {
			fontIdx = Math.min( SIZES.length - 1, fontIdx + 1 );
		} else {
			fontIdx = FONT_DEFAULT;
		}

		store( 'rs-font', String( fontIdx ) );
		applyFont();
	} );

	applyFont();

	/* ---------------------------------------------------------------
	 * Hero heading
	 *
	 * Words fade up out of a blur, one after another, then lift away
	 * before the next phrase arrives. Split by word, never by letter:
	 * breaking Bengali between spans would wreck যুক্তাক্ষর and মাত্রা.
	 * ------------------------------------------------------------ */

	function startHeroReveal() {
		var target = $( '#rs-type' );
		var phrases = cfg.phrases || [];

		if ( ! target || phrases.length === 0 ) {
			return;
		}

		if ( reduceMotion ) {
			target.textContent = phrases[ 0 ];
			return;
		}

		var phraseIdx = 0;

		function build( phrase ) {
			var words = phrase.split( /\s+/ ).filter( Boolean );
			var spans = [];

			target.textContent = '';

			words.forEach( function ( word, i ) {
				if ( i > 0 ) {
					target.appendChild( document.createTextNode( ' ' ) );
				}

				var span = document.createElement( 'span' );

				span.className = 'rs-word';
				span.textContent = word;
				span.style.transitionDelay = i * HERO_IN_STEP + 'ms';

				target.appendChild( span );
				spans.push( span );
			} );

			return spans;
		}

		function reveal() {
			var spans = build( phrases[ phraseIdx ] );

			/* Flush the hidden state so the transition has somewhere to start. */
			void target.offsetWidth;

			spans.forEach( function ( span ) {
				span.classList.add( 'is-in' );
			} );

			if ( phrases.length === 1 ) {
				return;
			}

			var settled = HERO_IN_DUR + ( spans.length - 1 ) * HERO_IN_STEP;

			window.setTimeout( function () {
				dismiss( spans );
			}, settled + HERO_HOLD );
		}

		function dismiss( spans ) {
			spans.forEach( function ( span, i ) {
				span.style.transitionDelay = i * HERO_OUT_STEP + 'ms';
				span.classList.remove( 'is-in' );
				span.classList.add( 'is-out' );
			} );

			var gone = HERO_OUT_DUR + ( spans.length - 1 ) * HERO_OUT_STEP;

			window.setTimeout( function () {
				phraseIdx = ( phraseIdx + 1 ) % phrases.length;
				reveal();
			}, gone + 140 );
		}

		reveal();
	}

	startHeroReveal();

	/* ---------------------------------------------------------------
	 * Hover summary
	 * ------------------------------------------------------------ */

	( function () {
		var tip = $( '#rs-tooltip' );
		/* The wrapper rather than the list itself: turning a page replaces
		   everything inside it, and listeners bound to the old rows would
		   go with them. */
		var list = $( '#rs-list-wrap' );

		if ( ! tip || ! list || ! window.matchMedia || ! window.matchMedia( '(hover: hover)' ).matches ) {
			return;
		}

		var timer = null;
		var pos = { x: 0, y: 0 };

		function place() {
			var rect = tip.getBoundingClientRect();
			var left = pos.x + 14;
			var top = pos.y + 14;

			if ( left + rect.width > window.innerWidth - 8 ) {
				left = window.innerWidth - rect.width - 8;
			}

			if ( top + rect.height > window.innerHeight - 8 ) {
				top = pos.y - rect.height - 14;
			}

			tip.style.left = Math.max( 8, left ) + 'px';
			tip.style.top = Math.max( 8, top ) + 'px';
		}

		function hide() {
			window.clearTimeout( timer );
			tip.classList.remove( 'is-visible' );
		}

		list.addEventListener( 'mouseover', function ( event ) {
			var link = event.target.closest ? event.target.closest( '[data-rs-summary]' ) : null;

			if ( ! link ) {
				return;
			}

			var text = link.getAttribute( 'data-rs-summary' );

			if ( ! text ) {
				return;
			}

			pos.x = event.clientX;
			pos.y = event.clientY;

			window.clearTimeout( timer );
			timer = window.setTimeout( function () {
				tip.textContent = text;
				tip.classList.add( 'is-visible' );
				place();
			}, TOOLTIP_DELAY );
		} );

		list.addEventListener( 'mousemove', function ( event ) {
			pos.x = event.clientX;
			pos.y = event.clientY;

			if ( tip.classList.contains( 'is-visible' ) ) {
				place();
			}
		} );

		list.addEventListener( 'mouseout', function ( event ) {
			var link = event.target.closest ? event.target.closest( '[data-rs-summary]' ) : null;

			if ( link ) {
				hide();
			}
		} );

		window.addEventListener( 'scroll', hide, { passive: true } );
	}() );

	/* ---------------------------------------------------------------
	 * Overlays
	 * ------------------------------------------------------------ */

	var openOverlays = [];
	var lastFocus = null;

	function lockBody( locked ) {
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

	/* ---------------------------------------------------------------
	 * Post modal
	 * ------------------------------------------------------------ */

	var postOverlay = $( '#rs-post-overlay' );
	var postBody = $( '#rs-post-body' );
	var baseTitle = document.title;
	var cache = {};

	/* Which page of the list is on screen, and the loader that can fetch a
	   different one. Both belong to the pagination module near the bottom
	   of this file; the popstate handler below is the other user. */
	var listUrl = absUrl( window.location.href );
	var loadListPage = null;

	/*
	 * Set by the editor near the bottom of this file, and consulted by
	 * everything that could carry the reader away from an edit in
	 * progress: the modal's close, and the two swipes that jump to the
	 * next post. All of them would throw the words away without asking.
	 */
	var isEditing = function () {
		return false;
	};

	var editorMayClose = function () {
		return true;
	};

	function fontControlsHtml() {
		return (
			'<div class="rs-fontctl" role="group" aria-label="লেখার আকার">' +
			'<button type="button" data-rs-font="down" data-step="0" aria-label="ছোট করুন">A-</button>' +
			'<span class="rs-fontctl__sep"></span>' +
			'<button type="button" data-rs-font="reset" data-step="1" aria-label="স্বাভাবিক আকার">A</button>' +
			'<span class="rs-fontctl__sep"></span>' +
			'<button type="button" data-rs-font="up" data-step="2" aria-label="বড় করুন">A+</button>' +
			'</div>'
		);
	}

	function nextPrevHtml( data ) {
		if ( ! data.prev && ! data.next ) {
			return '';
		}

		var html = '<nav class="rs-nextprev">';

		/* data-rs-post lets the existing list click handler catch these,
		   so the next post opens in this modal instead of reloading. */
		if ( data.prev ) {
			html += '<a href="' + escapeHtml( data.prev.link ) + '" data-rs-post="' +
				data.prev.id + '">← ' + escapeHtml( data.prev.title ) + '</a>';
		}

		if ( data.next ) {
			html += '<a class="rs-nextprev__next" href="' + escapeHtml( data.next.link ) +
				'" data-rs-post="' + data.next.id + '">' + escapeHtml( data.next.title ) + ' →</a>';
		}

		return html + '</nav>';
	}

	/* The same pair rs_edit_links() prints on a post's own page. */
	function editLinkHtml() {
		var icon = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';

		return (
			'<button class="rs-article__edit" type="button" data-rs-edit>' + icon + 'সম্পাদনা</button>' +
			'<a class="rs-article__edit rs-article__edit--dash" href="">ড্যাশবোর্ডে</a>'
		);
	}

	/* Kept in step with rs_share_row() in functions.php, which draws the
	   same row on a post's own page. */
	function shareHtml( data ) {
		var link = data.link;
		var title = data.title;
		var svg = 'width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
		var cpIcon = '<svg ' + svg + '><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
		var shIcon = '<svg ' + svg + '><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/></svg>';
		var bmIcon = '<svg ' + svg + '><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>';

		return (
			'<div class="rs-share">' +
			'<p class="rs-share__label">অন্যদেরও পড়তে দিন</p>' +
			'<div class="rs-share__row">' +
			'<button class="rs-share__btn" type="button" data-rs-share="' + escapeHtml( link ) +
			'" data-rs-share-title="' + escapeHtml( title || '' ) + '">' +
			shIcon + 'শেয়ার করুন</button>' +
			'<button class="rs-share__btn" type="button" data-rs-copy="' + escapeHtml( link ) + '">' +
			cpIcon + 'লিঙ্ক কপি</button>' +
			/* The modal was built from a fetch, so this button has no row to
			   read itself out of and carries what it needs instead. */
			'<button class="rs-share__btn rs-share__btn--save" type="button" data-rs-later="' +
			escapeHtml( data.id ) + '" data-rs-later-url="' + escapeHtml( link ) +
			'" data-rs-later-title="' + escapeHtml( title || '' ) +
			'" data-rs-later-time="' + escapeHtml( data.readingTime || '' ) + '">' +
			bmIcon + '<span data-rs-later-text>পরে পড়ব</span></button>' +
			'</div></div>'
		);
	}

	function relatedHtml( data ) {
		if ( ! data.related || ! data.related.length ) {
			return '';
		}

		var html = '<nav class="rs-related"><p class="rs-related__label">' +
			escapeHtml( 'আরও ' + ( data.category || 'লেখা' ) ) +
			'</p><ul class="rs-related__list">';

		/* data-rs-post again, so these open in this modal rather than
		   reloading the page under it. */
		data.related.forEach( function ( item ) {
			html += '<li><a href="' + escapeHtml( item.link ) +
				'" data-rs-post="' + item.id + '">' +
				'<span class="rs-related__title">' + escapeHtml( item.title ) + '</span>' +
				'<span class="rs-related__meta">' + escapeHtml( item.readingTime ) + '</span>' +
				'</a></li>';
		} );

		return html + '</ul></nav>';
	}

	function renderPost( data ) {
		postBody.innerHTML =
			'<div class="rs-article__meta">' +
			'<p class="rs-article__date"></p>' +
			( data.readingTime ? '<span class="rs-article__read"></span>' : '' ) +
			( cfg.editBase ? editLinkHtml() : '' ) +
			fontControlsHtml() +
			'</div>' +
			'<div class="rs-article__head">' +
			'<h2 class="rs-article__title" id="rs-post-title"></h2>' +
			( data.category ? '<a class="rs-article__cat"></a>' : '' ) +
			'</div>' +
			'<p class="rs-article__author"></p>' +
			'<div class="rs-article__body"></div>' +
			shareHtml( data ) +
			relatedHtml( data ) +
			nextPrevHtml( data );

		$( '.rs-article__date', postBody ).textContent = data.date;

		if ( data.readingTime ) {
			$( '.rs-article__read', postBody ).textContent = data.readingTime;
		}
		$( '.rs-article__title', postBody ).textContent = data.title;
		$( '.rs-article__author', postBody ).textContent = data.author;

		/* Which post the editor is about to save, found the same way on a
		   post's own page. */
		postBody.setAttribute( 'data-rs-id', data.id );

		if ( cfg.editBase ) {
			/* href as a property, so the id never passes through innerHTML. */
			$( '.rs-article__edit--dash', postBody ).href = cfg.editBase + data.id;
		}

		if ( data.category ) {
			var catEl = $( '.rs-article__cat', postBody );

			/* Set as properties rather than markup: the values never touch
			   innerHTML, so there is nothing to escape. */
			catEl.textContent = data.category;

			if ( data.categoryLink ) {
				catEl.href = data.categoryLink;
			}
		}

		/* Content is this site's own published HTML, already run through
		   the_content filters on the server. */
		$( '.rs-article__body', postBody ).innerHTML = data.content;

		applyFont();

		/* The share row this just built carries a save button, and it has
		   to be told whether the story is already on the shelf. */
		markLater();

		var resume = positionEntry( data.id );

		restoreScroll( postBody.parentNode, resume ? resume.t : 0 );
	}

	/*
	 * How many post entries sit between the list and what is on screen.
	 * Reading on from one post to the next pushes an entry each time, so
	 * closing has to jump back over all of them at once; a single back()
	 * would land on the previous post and reopen it. The count also rides
	 * along in each history state, so the browser's own back and forward
	 * buttons keep it correct.
	 */
	var postDepth = 0;

	/* Which post the modal is showing, so the scroll listener further down
	   knows what it is saving a position for. */
	var currentPostId = null;

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

	/* ---------------------------------------------------------------
	 * Search
	 * ------------------------------------------------------------ */

	var searchOverlay = $( '#rs-search-overlay' );
	var searchInput = $( '#rs-search-input' );
	var searchResults = $( '#rs-search-results' );
	var searchCount = $( '#rs-search-count' );
	var searchTimer = null;
	var searchSeq = 0;

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

			setModalOrigin( btn );
			openPost( btn.getAttribute( 'data-rs-result' ), btn.getAttribute( 'data-href' ), true );
		} );
	}

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

	/* ---------------------------------------------------------------
	 * Scroll to top
	 * ------------------------------------------------------------ */

	( function () {
		var btn = $( '#rs-top' );

		if ( ! btn ) {
			return;
		}

		function update() {
			btn.classList.toggle( 'is-visible', window.scrollY > 300 );
		}

		window.addEventListener( 'scroll', update, { passive: true } );
		update();

		btn.addEventListener( 'click', function () {
			window.scrollTo( {
				top: 0,
				behavior: reduceMotion ? 'auto' : 'smooth',
			} );
		} );
	}() );

	/* ---------------------------------------------------------------
	 * A colour the reader picks
	 *
	 * One colour goes in and a whole palette comes out. The two built in
	 * schemes were put together by eye and then checked; these have to be
	 * arrived at by rule, so the rules below are the ones those two obey:
	 * the text is a near-black or near-white carrying a trace of the
	 * background's hue, the quiet greys are the background nudged towards
	 * the text, and anything that has to be legible is walked away from
	 * the background until it clears 4.5:1 rather than guessed at.
	 *
	 * The finished custom properties are stored, not the colour, so the
	 * script in wp_head can put them back before the first paint without
	 * carrying any of this arithmetic.
	 * ------------------------------------------------------------ */

	var TINT_KEY = 'rs-tint';

	function hexToRgb( hex ) {
		var m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( String( hex ).trim() );

		return m ? [ parseInt( m[ 1 ], 16 ), parseInt( m[ 2 ], 16 ), parseInt( m[ 3 ], 16 ) ] : null;
	}

	function rgbHex( rgb ) {
		var out = '#';

		for ( var i = 0; i < 3; i++ ) {
			var part = Math.max( 0, Math.min( 255, Math.round( rgb[ i ] ) ) ).toString( 16 );
			out += part.length < 2 ? '0' + part : part;
		}

		return out;
	}

	function rgba( rgb, alpha ) {
		return 'rgba(' + rgb[ 0 ] + ', ' + rgb[ 1 ] + ', ' + rgb[ 2 ] + ', ' + alpha + ')';
	}

	/* sRGB relative luminance, the quantity the contrast ratio is built
	   from. Not the same as HSL lightness: yellow and blue of equal
	   lightness are nowhere near equally bright to the eye. */
	function luminance( rgb ) {
		var parts = [];

		for ( var i = 0; i < 3; i++ ) {
			var c = rgb[ i ] / 255;
			parts.push( c <= 0.03928 ? c / 12.92 : Math.pow( ( c + 0.055 ) / 1.055, 2.4 ) );
		}

		return 0.2126 * parts[ 0 ] + 0.7152 * parts[ 1 ] + 0.0722 * parts[ 2 ];
	}

	function contrast( a, b ) {
		var la = luminance( a );
		var lb = luminance( b );

		return ( Math.max( la, lb ) + 0.05 ) / ( Math.min( la, lb ) + 0.05 );
	}

	function rgbToHsl( rgb ) {
		var r = rgb[ 0 ] / 255;
		var g = rgb[ 1 ] / 255;
		var b = rgb[ 2 ] / 255;
		var max = Math.max( r, g, b );
		var min = Math.min( r, g, b );
		var l = ( max + min ) / 2;
		var h = 0;
		var s = 0;

		if ( max !== min ) {
			var d = max - min;

			s = l > 0.5 ? d / ( 2 - max - min ) : d / ( max + min );

			if ( max === r ) {
				h = ( g - b ) / d + ( g < b ? 6 : 0 );
			} else if ( max === g ) {
				h = ( b - r ) / d + 2;
			} else {
				h = ( r - g ) / d + 4;
			}

			h *= 60;
		}

		return [ h, s * 100, l * 100 ];
	}

	function hslToRgb( h, s, l ) {
		h = ( ( ( h % 360 ) + 360 ) % 360 ) / 360;
		s = Math.max( 0, Math.min( 100, s ) ) / 100;
		l = Math.max( 0, Math.min( 100, l ) ) / 100;

		if ( ! s ) {
			var flat = Math.round( l * 255 );

			return [ flat, flat, flat ];
		}

		var q = l < 0.5 ? l * ( 1 + s ) : l + s - l * s;
		var p = 2 * l - q;

		function channel( t ) {
			if ( t < 0 ) {
				t += 1;
			}

			if ( t > 1 ) {
				t -= 1;
			}

			if ( t < 1 / 6 ) {
				return p + ( q - p ) * 6 * t;
			}

			if ( t < 1 / 2 ) {
				return q;
			}

			if ( t < 2 / 3 ) {
				return p + ( q - p ) * ( 2 / 3 - t ) * 6;
			}

			return p;
		}

		return [
			Math.round( channel( h + 1 / 3 ) * 255 ),
			Math.round( channel( h ) * 255 ),
			Math.round( channel( h - 1 / 3 ) * 255 ),
		];
	}

	/* Walk a colour's lightness away from the background until the pair
	   clears the ratio. What the fixed palettes had done for them by hand. */
	function readable( h, s, bg, toDark, ratio ) {
		var step = toDark ? -2 : 2;

		for ( var l = 55; l >= 0 && l <= 100; l += step ) {
			var rgb = hslToRgb( h, s, l );

			if ( contrast( rgb, bg ) >= ratio ) {
				return rgb;
			}
		}

		return hslToRgb( h, s, toDark ? 0 : 100 );
	}

	function paletteFor( hex ) {
		var bg = hexToRgb( hex );

		if ( ! bg ) {
			return null;
		}

		var hsl = rgbToHsl( bg );
		var h = hsl[ 0 ];
		var s = hsl[ 1 ];
		var l = hsl[ 2 ];
		var light = luminance( bg ) > 0.35;

		function shift( amount, sat ) {
			return hslToRgb( h, 'undefined' === typeof sat ? s : sat, light ? l - amount : l + amount );
		}

		/* Never pure black or white: both built in schemes carry a trace
		   of the page's own hue in their text, and it is what keeps the
		   page from reading as a screenshot pasted onto a colour. */
		var fg = hslToRgb( h, Math.min( s, 12 ), light ? 8 : 95 );
		var mutedFg = readable( h, Math.min( s, 22 ), bg, light, 4.5 );
		var edge = light ? '0, 0, 0' : '255, 255, 255';

		var vars = {
			'--rs-bg': rgbHex( bg ),
			'--rs-fg': rgbHex( fg ),
			'--rs-body-fg': rgba( fg, 0.85 ),
			'--rs-muted': rgbHex( shift( 4 ) ),
			'--rs-muted-fg': rgbHex( mutedFg ),
			'--rs-accent': rgbHex( shift( 6 ) ),
			/* Well away from the hue the page is using, so a category tag
			   stays a different kind of thing from the title beside it. */
			'--rs-cat': rgbHex( readable( h + 190, 35, bg, light, 4.5 ) ),
			'--rs-border': 'rgba(' + edge + ', ' + ( light ? 0.12 : 0.18 ) + ')',
			'--rs-border-30': 'rgba(' + edge + ', ' + ( light ? 0.036 : 0.06 ) + ')',
			'--rs-border-40': 'rgba(' + edge + ', ' + ( light ? 0.048 : 0.08 ) + ')',
			'--rs-border-50': 'rgba(' + edge + ', ' + ( light ? 0.06 : 0.1 ) + ')',
			'--rs-border-60': 'rgba(' + edge + ', ' + ( light ? 0.072 : 0.13 ) + ')',
			'--rs-rule': 'rgba(' + edge + ', ' + ( light ? 0.16 : 0.2 ) + ')',
			'--rs-grid': 'rgba(' + edge + ', 0.016)',
			'--rs-bg-blur': rgba( bg, 0.85 ),
			'--rs-surface': rgbHex( light ? hslToRgb( h, Math.min( s, 25 ), 99 ) : shift( 6 ) ),
			'--rs-scrim': light ? 'rgba(0, 0, 0, 0.45)' : 'rgba(0, 0, 0, 0.7)',
			'--rs-fontctl-sep': rgba( bg, light ? 0.2 : 0.28 ),
			'--rs-scrollbar-thumb': rgbHex( shift( 22 ) ),
			'--rs-scrollbar-track': rgbHex( shift( 4 ) ),
			/* Behind found words, with the page's own text sitting on top,
			   so these follow the scheme rather than the hue. */
			'--rs-mark': light ? '#fef08a' : '#4a3aa0',
			'color-scheme': light ? 'light' : 'dark',
		};

		/*
		 * Filled controls and the hover summary. A light page simply
		 * inverts; a dark one lifts a panel out of itself instead,
		 * because a near-white pill on a dark page glares. The same
		 * reasoning the fixed dark palette gives for its own values.
		 */
		if ( light ) {
			vars['--rs-solid-bg'] = rgbHex( fg );
			vars['--rs-solid-fg'] = rgbHex( bg );
			vars['--rs-tooltip-bg'] = rgbHex( fg );
			vars['--rs-tooltip-fg'] = rgbHex( bg );
		} else {
			vars['--rs-solid-bg'] = rgbHex( shift( 22 ) );
			vars['--rs-solid-fg'] = rgbHex( fg );
			vars['--rs-tooltip-bg'] = rgbHex( shift( 14 ) );
			vars['--rs-tooltip-fg'] = rgbHex( fg );
		}

		/* The accent is the site's own and keeps its hue. It is only moved
		   when the chosen background would swallow it. */
		var hover = hexToRgb( light ? '#a24c00' : '#9c8cff' );

		if ( contrast( hover, bg ) < 2 ) {
			var hoverHsl = rgbToHsl( hover );

			hover = readable( hoverHsl[ 0 ], hoverHsl[ 1 ], bg, light, 2.5 );
		}

		vars['--rs-hover'] = rgbHex( hover );

		return { hex: rgbHex( bg ), light: light, vars: vars };
	}

	function readTint() {
		try {
			var raw = store( TINT_KEY );

			return raw ? JSON.parse( raw ) : null;
		} catch ( e ) {
			return null;
		}
	}

	function applyTint( palette ) {
		var root = document.documentElement;

		Object.keys( palette.vars ).forEach( function ( key ) {
			root.style.setProperty( key, palette.vars[ key ] );
		} );

		root.setAttribute( 'data-theme', palette.light ? 'light' : 'dark' );
	}

	/* The colour the page is actually wearing, so the picker opens on it
	   rather than on some unrelated default. */
	function currentBg() {
		var value = window.getComputedStyle( document.documentElement )
			.getPropertyValue( '--rs-bg' )
			.trim();

		return hexToRgb( value ) ? rgbHex( hexToRgb( value ) ) : '#eaecf1';
	}

	function clearTint() {
		var saved = readTint();
		var root = document.documentElement;

		if ( saved && saved.vars ) {
			Object.keys( saved.vars ).forEach( function ( key ) {
				root.style.removeProperty( key );
			} );
		}

		store( TINT_KEY, '' );

		var reset = $( '#rs-tint-reset' );

		if ( reset ) {
			reset.hidden = true;
		}
	}

	( function () {
		var input = $( '#rs-tint' );
		var reset = $( '#rs-tint-reset' );

		if ( ! input ) {
			return;
		}

		var saved = readTint();

		if ( saved && saved.vars ) {
			input.value = saved.hex;

			if ( reset ) {
				reset.hidden = false;
			}
		} else {
			input.value = currentBg();
		}

		/* input rather than change, so the page follows the picker while
		   it is still open. */
		input.addEventListener( 'input', function () {
			var palette = paletteFor( input.value );

			if ( ! palette ) {
				return;
			}

			applyTint( palette );
			store( TINT_KEY, JSON.stringify( palette ) );

			if ( reset ) {
				reset.hidden = false;
			}
		} );

		if ( reset ) {
			reset.addEventListener( 'click', function () {
				clearTint();
				input.value = currentBg();
			} );
		}
	}() );

	/* ---------------------------------------------------------------
	 * Dark / light toggle
	 *
	 * The attribute is already on <html> by the time this runs, set by
	 * the inline script in wp_head. This only flips and remembers it.
	 * ------------------------------------------------------------ */

	( function () {
		var btn = $( '[data-rs-theme]' );

		var animBtn = $( '#rs-anim-toggle' );
		if ( animBtn ) {
			var savedAnim = window.localStorage.getItem( 'rs-anim' );
			var isAnim = savedAnim !== null ? savedAnim === 'true' : !!cfg.animations;

			function applyAnim() {
				if ( isAnim ) {
					document.body.classList.add( 'rs-animated' );
					animBtn.classList.add( 'is-active' );
					cfg.animations = true;
					staggerRows();
				} else {
					document.body.classList.remove( 'rs-animated' );
					animBtn.classList.remove( 'is-active' );
					cfg.animations = false;
					var rows = document.querySelectorAll( '.rs-row' );
					for ( var i = 0; i < rows.length; i++ ) {
						rows[ i ].style.removeProperty( '--i' );
					}
				}
			}

			applyAnim();

			animBtn.addEventListener( 'click', function () {
				isAnim = ! isAnim;
				try { window.localStorage.setItem( 'rs-anim', isAnim ? 'true' : 'false' ); } catch(e) {}
				applyAnim();
			} );
		}


		if ( ! btn ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			var root = document.documentElement;

			/* A chosen colour gives way here. It sits in inline styles,
			   which no stylesheet can outrank, so leaving it in place
			   would make this button appear to do nothing. */
			clearTint();

			var next = 'dark' === root.getAttribute( 'data-theme' ) ? 'light' : 'dark';

			root.setAttribute( 'data-theme', next );
			store( 'rs-theme', next );

			var input = $( '#rs-tint' );

			if ( input ) {
				input.value = currentBg();
			}
		} );
	}() );

	/* ---------------------------------------------------------------
	 * Scroll to top inside the post modal
	 *
	 * The modal scrolls its own element, not the window, so the page
	 * level button above never reacts to it.
	 * ------------------------------------------------------------ */

	( function () {
		var btn = $( '#rs-modal-top' );
		var bar = $( '#rs-modal-progress' );
		var scroller = $( '#rs-post-overlay .rs-modal__scroll' );

		if ( ! scroller || ( ! btn && ! bar ) ) {
			return;
		}

		function update() {
			if ( btn ) {
				btn.classList.toggle( 'is-visible', scroller.scrollTop > 300 );
			}

			if ( bar ) {
				var travel = scroller.scrollHeight - scroller.clientHeight;
				/* A post shorter than the modal has nowhere to scroll, so
				   leave the bar empty rather than dividing by zero. */
				var pct = travel > 0 ? ( scroller.scrollTop / travel ) * 100 : 0;

				bar.style.width = Math.min( 100, Math.max( 0, pct ) ) + '%';

				var timeLeftIndicator = $( '#rs-time-left' );
				if ( timeLeftIndicator && currentPostId && cache[currentPostId] && cache[currentPostId].readingTime ) {
					var rt = cache[currentPostId].readingTime;
					var bnDigitsStr = rt.replace(/[^\u09E6-\u09EF]/g, '');
					if ( bnDigitsStr ) {
						var enDigits = bnDigitsStr.replace(/[\u09E6-\u09EF]/g, function(d) {
							return "০১২৩৪৫৬৭৮৯".indexOf(d);
						});
						var totalMinutes = parseInt(enDigits, 10);
						var remaining = Math.ceil( totalMinutes * ( 1 - ( pct / 100 ) ) );
						if ( remaining > 0 && pct > 5 ) {
							timeLeftIndicator.textContent = "আর " + bnDigits(remaining) + " মিনিট বাকি";
							timeLeftIndicator.classList.add('is-visible');
						} else {
							timeLeftIndicator.classList.remove('is-visible');
						}
					}
				}
			}
		}

		/* Also fires when opening a post resets scrollTop, which hides
		   the button and empties the bar again. */
		scroller.addEventListener( 'scroll', update, { passive: true } );

		/* Save on a trailing delay rather than on every scroll event:
		   writing to localStorage at scroll frequency would be wasteful. */
		var saveTimer = null;

		scroller.addEventListener(
			'scroll',
			function () {
				window.clearTimeout( saveTimer );
				saveTimer = window.setTimeout( flushPosition, 400 );
			},
			{ passive: true }
		);
		
		/* Swipe to next/prev post on mobile */
		var touchStartX = 0;
		var touchStartY = 0;
		scroller.addEventListener('touchstart', function(e) {
			touchStartX = e.changedTouches[0].screenX;
			touchStartY = e.changedTouches[0].screenY;
		}, {passive: true});

		scroller.addEventListener('touchend', function(e) {
			/* Dragging across a line to select it is the same gesture as
			   this one. While an edit is open the swipe stands down. */
			if ( isEditing() ) {
				return;
			}

			var touchEndX = e.changedTouches[0].screenX;
			var touchEndY = e.changedTouches[0].screenY;
			var dx = touchEndX - touchStartX;
			var dy = touchEndY - touchStartY;

			// Swipe left = Next post, Swipe right = Prev post
			if ( Math.abs(dx) > 80 && Math.abs(dx) > Math.abs(dy) * 2 ) {
				var key = dx < 0 ? 'ArrowRight' : 'ArrowLeft';
				var link = $(
					'ArrowLeft' === key
						? '.rs-nextprev a:not(.rs-nextprev__next)'
						: '.rs-nextprev .rs-nextprev__next',
					postOverlay
				);
				if ( link ) {
					link.click();
				}
			}
		}, {passive: true});

		/* A closed tab never runs closePost(), so catch that too. */
		window.addEventListener( 'pagehide', flushPosition );

		if ( btn ) {
			btn.addEventListener( 'click', function () {
				scroller.scrollTo( {
					top: 0,
					behavior: reduceMotion ? 'auto' : 'smooth',
				} );
			} );
		}
	}() );

	/* ---------------------------------------------------------------
	 * Reading a post at its own address
	 *
	 * Everything above was written for the modal, which is how the front
	 * page opens a story. Most readers do not arrive that way: they come
	 * from a shared link, straight to the post's own page, where the
	 * window does the scrolling and none of the modal's machinery is on
	 * screen. This gives that page the same progress bar, and remembers
	 * where they stopped so the line on the front page can offer it back.
	 * ------------------------------------------------------------ */

	( function () {
		var pid = parseInt( cfg.postId, 10 );
		if ( ! pid ) {
			return;
		}

		var bar = $( '#rs-progress' );
		var singleTimeLeft = $( '#rs-single-time-left' );
		var article = $( '.rs-single .rs-article' );
		var readTimeStr = '';
		if ( singleTimeLeft ) {
			var readTimeEl = $( '.rs-article__read', article );
			if ( readTimeEl ) {
				readTimeStr = readTimeEl.textContent;
			}
		}

		function travel() {
			return Math.max(
				0,
				document.documentElement.scrollHeight - window.innerHeight
			);
		}

		function update() {
			var room = travel();
			var pct = room > 0 ? ( window.pageYOffset / room ) * 100 : 0;

			if ( bar ) {
				bar.style.width = Math.min( 100, Math.max( 0, pct ) ) + '%';
			}
			
			if ( singleTimeLeft && readTimeStr ) {
				var bnDigitsStr = readTimeStr.replace(/[^\u09E6-\u09EF]/g, '');
				if ( bnDigitsStr ) {
					var enDigits = bnDigitsStr.replace(/[\u09E6-\u09EF]/g, function(d) {
						return "০১২৩৪৫৬৭৮৯".indexOf(d);
					});
					var totalMinutes = parseInt(enDigits, 10);
					var remaining = Math.ceil( totalMinutes * ( 1 - ( pct / 100 ) ) );
					if ( remaining > 0 && pct > 5 ) {
						singleTimeLeft.textContent = "আর " + bnDigits(remaining) + " মিনিট বাকি";
						singleTimeLeft.classList.add('is-visible');
					} else {
						singleTimeLeft.classList.remove('is-visible');
					}
				}
			}
		}

		function remember() {
			var title = $( '.rs-article__title', article );

			savePosition(
				pid,
				window.pageYOffset,
				travel(),
				title ? title.textContent : document.title,
				window.location.href,
				cfg.catId
			);
		}

		var timer = null;

		window.addEventListener(
			'scroll',
			function () {
				update();

				/* Trailing, like the modal's: writing to localStorage at
				   scroll frequency would be wasteful. */
				window.clearTimeout( timer );
				timer = window.setTimeout( remember, 400 );
			},
			{ passive: true }
		);

		/* A closed tab never fires the timer. */
		window.addEventListener( 'pagehide', remember );

		/* Swipe to next/prev post on mobile for single page */
		var touchStartX = 0;
		var touchStartY = 0;
		document.addEventListener('touchstart', function(e) {
			touchStartX = e.changedTouches[0].screenX;
			touchStartY = e.changedTouches[0].screenY;
		}, {passive: true});

		document.addEventListener('touchend', function(e) {
			/* As in the modal: selecting a line is this same gesture. */
			if ( isEditing() ) {
				return;
			}

			var touchEndX = e.changedTouches[0].screenX;
			var touchEndY = e.changedTouches[0].screenY;
			var dx = touchEndX - touchStartX;
			var dy = touchEndY - touchStartY;

			if ( Math.abs(dx) > 80 && Math.abs(dx) > Math.abs(dy) * 2 ) {
				var key = dx < 0 ? 'ArrowRight' : 'ArrowLeft';
				var link = $(
					'ArrowLeft' === key
						? '.rs-nextprev a:not(.rs-nextprev__next)'
						: '.rs-nextprev .rs-nextprev__next'
				);
				if ( link ) {
					window.location.href = link.href;
				}
			}
		}, {passive: true});

		update();
	}() );

	/* ---------------------------------------------------------------
	 * "You were reading"
	 *
	 * A position is only kept while a post is started and unfinished, so
	 * the newest entry is exactly the thread the reader dropped. Offering
	 * it back is the whole point of having remembered it: until now the
	 * position only helped someone who found their way to the same story
	 * again on their own.
	 * ------------------------------------------------------------ */

	function renderResume() {
		var host = $( '#rs-resume' );
		if ( ! host ) { return; }

		var map = readPositions();
		var keys = Object.keys( map );

		var targetKey = null;
		var currentCat = parseInt( cfg.catId, 10 ) || 0;
		if ( currentCat > 0 ) {
			for ( var i = keys.length - 1; i >= 0; i-- ) {
				var entry = map[ keys[ i ] ];
				if ( entry && entry.c === currentCat ) {
					targetKey = keys[ i ];
					break;
				}
			}
		}

		if ( ! targetKey && keys.length > 0 ) {
			targetKey = keys[ keys.length - 1 ];
		}

		var last = targetKey ? map[ targetKey ] : null;

		if ( ! last || 'number' === typeof last || ! last.n || ! last.u ) {
			host.innerHTML = '';
			return;
		}

		var id = targetKey.slice( 1 );

		host.innerHTML =
			'<div class="rs-resume">' +
			'<a class="rs-resume__link" href="' + escapeHtml( last.u ) +
			'" data-rs-post="' + escapeHtml( id ) + '">' +
			'<span class="rs-resume__label">আপনি পড়ছিলেন</span>' +
			'<span class="rs-resume__title">' + escapeHtml( last.n ) + '</span>' +
			'</a>' +
			'<button class="rs-resume__close" type="button" aria-label="সরিয়ে দিন">&times;</button>' +
			'</div>';

		$( '.rs-resume__close', host ).addEventListener( 'click', function () {
			var fresh = readPositions();
			delete fresh[ 'p' + id ];
			store( POS_KEY, JSON.stringify( fresh ) );
			host.innerHTML = '';
		} );
	}
	renderResume();

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

		$$( '[data-rs-later]' ).forEach( function ( btn ) {
			var on = !! map[ 'p' + btn.getAttribute( 'data-rs-later' ) ];
			var text = $( '[data-rs-later-text]', btn );

			btn.classList.toggle( 'is-saved', on );
			btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			btn.setAttribute( 'title', on ? 'তালিকা থেকে সরান' : 'পরে পড়ব' );

			/* The icon-only buttons in the list have no label to swap. */
			if ( text ) {
				text.textContent = on ? 'তালিকায় আছে' : 'পরে পড়ব';
			} else {
				btn.setAttribute( 'aria-label', on ? 'তালিকা থেকে সরান' : 'পরে পড়ব' );
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
				escapeHtml( id ) + '" aria-label="তালিকা থেকে সরান">&times;</button>' +
				'</li>';
		}

		host.innerHTML = html
			? '<nav class="rs-related rs-later" aria-label="পরে পড়ব">' +
				'<p class="rs-related__label">পরে পড়ব</p>' +
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

		toast( saving ? 'পরে পড়ার তালিকায় রাখা হলো' : 'তালিকা থেকে সরানো হলো' );
	} );

	/* The buttons only do something once this file has run, so they stay
	   out of sight until it has. Same arrangement as the share button. */
	document.body.classList.add( 'rs-can-save' );

	markLater();
	renderLater();

	/* ---------------------------------------------------------------
	 * Carry the source along with copied text
	 *
	 * Bengali writing travels around Facebook without its author's name
	 * more often than with it. This stops nobody — the text is still
	 * theirs to take — it only makes the credit the easier thing to keep
	 * than to remove.
	 * ------------------------------------------------------------ */

	( function () {
		/* Long enough to be a passage rather than a word someone is
		   looking up or a name they are searching for. */
		var CREDIT_MIN = 60;

		document.addEventListener( 'copy', function ( event ) {
			var sel = window.getSelection();

			if ( ! sel || sel.isCollapsed || ! event.clipboardData ) {
				return;
			}

			var node = sel.anchorNode;
			var el = node && 3 === node.nodeType ? node.parentNode : node;

			if ( ! el || ! el.closest || ! el.closest( '.rs-article__body' ) ) {
				return;
			}

			var text = sel.toString();

			if ( text.length < CREDIT_MIN ) {
				return;
			}

			var author = $( '.rs-article__author' );
			var name = author && author.textContent ? author.textContent.trim() : ( cfg.siteName || '' );

			event.clipboardData.setData(
				'text/plain',
				text + '\n\n— ' + name + ', ' + window.location.href
			);
			event.preventDefault();
		} );
	}() );

	/* ---------------------------------------------------------------
	 * Quote-to-Image card
	 *
	 * Select a passage, click the floating button, and a canvas card
	 * is generated for sharing on social media — entirely on the
	 * client, no server round-trip.
	 * ------------------------------------------------------------ */

	( function () {
		var QUOTE_MIN = 20;
		var QUOTE_MAX = 500;
		var popup = null;
		var savedText = '';
		var hideTimer = null;

		/* Read a CSS variable from :root as a trimmed string. */
		function cssVar( name, fallback ) {
			var val = getComputedStyle( document.documentElement )
				.getPropertyValue( name ).trim();

			return val || fallback;
		}

		/* Word-wrap text on a canvas. Returns an array of lines. */
		function wrapText( ctx, text, maxWidth ) {
			var words = text.replace( /\s+/g, ' ' ).trim().split( ' ' );
			var lines = [];
			var line = '';

			for ( var i = 0; i < words.length; i++ ) {
				var test = line ? line + ' ' + words[ i ] : words[ i ];

				if ( ctx.measureText( test ).width > maxWidth && line ) {
					lines.push( line );
					line = words[ i ];
				} else {
					line = test;
				}
			}

			if ( line ) {
				lines.push( line );
			}

			return lines;
		}

		function removePopup() {
			if ( popup && popup.parentNode ) {
				popup.parentNode.removeChild( popup );
			}

			popup = null;
		}

		function showPopup( rect ) {
			removePopup();

			popup = document.createElement( 'button' );
			popup.type = 'button';
			popup.className = 'rs-quote-popup';
			popup.innerHTML =
				'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" ' +
				'stroke="currentColor" stroke-width="2" stroke-linecap="round" ' +
				'stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/>' +
				'<path d="M8 8h.01"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>' +
				'উদ্ধৃতি কার্ড';

			var scrollY = window.pageYOffset || document.documentElement.scrollTop;
			var scrollX = window.pageXOffset || document.documentElement.scrollLeft;

			/* Sit just above the selection, centered horizontally. */
			popup.style.left = Math.round( rect.left + rect.width / 2 + scrollX ) + 'px';
			popup.style.top = Math.round( rect.top + scrollY - 8 ) + 'px';
			popup.style.transform = 'translate(-50%, -100%)';

			document.body.appendChild( popup );

			popup.addEventListener( 'mousedown', function ( e ) {
				/* Keep the selection alive while the button is pressed. */
				e.preventDefault();
			} );

			popup.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
				generateCard();
			} );
		}

		function checkSelection() {
			var sel = window.getSelection();

			if ( ! sel || sel.isCollapsed ) {
				removePopup();
				return;
			}

			var node = sel.anchorNode;
			var el = node && 3 === node.nodeType ? node.parentNode : node;

			if ( ! el || ! el.closest || ! el.closest( '.rs-article__body' ) ) {
				removePopup();
				return;
			}

			var text = sel.toString().trim();

			if ( text.length < QUOTE_MIN ) {
				removePopup();
				return;
			}

			savedText = text.length > QUOTE_MAX
				? text.substring( 0, QUOTE_MAX ) + '...'
				: text;

			try {
				var range = sel.getRangeAt( 0 );
				var rect = range.getBoundingClientRect();

				if ( rect.width > 0 && rect.height > 0 ) {
					showPopup( rect );
				}
			} catch ( e ) {
				/* getRangeAt can throw in edge cases. */
			}
		}

		/* Debounced — works on both desktop and mobile. */
		var debounce = null;

		document.addEventListener( 'selectionchange', function () {
			clearTimeout( debounce );
			debounce = setTimeout( checkSelection, 350 );
		} );

		/* Hide on scroll or click outside. */
		document.addEventListener( 'scroll', function () {
			clearTimeout( hideTimer );
			hideTimer = setTimeout( removePopup, 100 );
		}, true );

		document.addEventListener( 'mousedown', function ( e ) {
			if ( popup && ! popup.contains( e.target ) ) {
				clearTimeout( hideTimer );
				hideTimer = setTimeout( removePopup, 400 );
			}
		} );

		function generateCard() {
			if ( ! savedText ) {
				return;
			}

			var text = savedText;
			removePopup();

			/* Collapse the selection so the popup does not reappear. */
			var sel = window.getSelection();

			if ( sel ) {
				sel.removeAllRanges();
			}

			/* Wait for fonts — they are already on the page, but the
			   promise makes sure the metrics are available. */
			( document.fonts && document.fonts.ready
				? document.fonts.ready
				: Promise.resolve()
			).then( function () {
				renderCard( text );
			} );
		}

		function renderCard( text ) {
			var SIZE = 1080;
			var PAD = 90;
			var canvas = document.createElement( 'canvas' );
			canvas.width = SIZE;
			canvas.height = SIZE;
			var ctx = canvas.getContext( '2d' );

			/* Colours from the current theme. */
			var bg = cssVar( '--rs-bg', '#eaecf1' );
			var fg = cssVar( '--rs-fg', '#1a1a1a' );
			var mutedFg = cssVar( '--rs-muted-fg', '#5f6672' );
			var hover = cssVar( '--rs-hover', '#a24c00' );

			/* Background. */
			ctx.fillStyle = bg;
			ctx.fillRect( 0, 0, SIZE, SIZE );

			/* Accent stripe at the very top. */
			ctx.fillStyle = hover;
			ctx.fillRect( 0, 0, SIZE, 5 );

			/* Large decorative opening quote mark. */
			ctx.font = '160px "Noto Serif Bengali", serif';
			ctx.fillStyle = hover;
			ctx.globalAlpha = 0.12;
			ctx.textBaseline = 'top';
			ctx.fillText( '\u201C', PAD - 12, PAD + 10 );
			ctx.globalAlpha = 1;

			/* --- Quote body — auto-shrink to fit ----------------------- */
			var maxW = SIZE - PAD * 2;
			var fontSize = 44;
			var minFont = 26;
			var lineHeight = 1.7;
			var maxLines = 12;
			var lines;

			do {
				ctx.font = fontSize + 'px "Noto Serif Bengali", serif';
				lines = wrapText( ctx, text, maxW );

				if ( lines.length <= maxLines ) {
					break;
				}

				fontSize -= 2;
			} while ( fontSize >= minFont );

			/* Truncate if it still overflows. */
			if ( lines.length > maxLines ) {
				lines = lines.slice( 0, maxLines );
				var last = lines[ maxLines - 1 ];

				lines[ maxLines - 1 ] = last.replace( /\s+\S*$/, '' ) + '...';
			}

			var lh = Math.round( fontSize * lineHeight );
			var textBlockH = lines.length * lh;
			var textTop = PAD + 140;

			ctx.fillStyle = fg;
			ctx.textBaseline = 'top';

			for ( var i = 0; i < lines.length; i++ ) {
				ctx.fillText( lines[ i ], PAD, textTop + i * lh );
			}

			/* --- Author ------------------------------------------------ */
			var authorEl = $( '.rs-article__author' );
			var authorName = authorEl
				? authorEl.textContent.trim()
				: ( cfg.siteName || '' );

			if ( authorName ) {
				var authorY = textTop + textBlockH + 40;

				ctx.font = '26px "Noto Sans Bengali", sans-serif';
				ctx.fillStyle = mutedFg;
				ctx.fillText( '\u2014 ' + authorName, PAD, authorY );
			}

			/* --- Bottom watermark --------------------------------------- */
			var bottomY = SIZE - PAD;

			ctx.fillStyle = hover;
			ctx.globalAlpha = 0.3;
			ctx.fillRect( PAD, bottomY - 40, 50, 2 );
			ctx.globalAlpha = 1;

			ctx.font = '18px "Noto Sans Bengali", sans-serif';
			ctx.fillStyle = mutedFg;
			ctx.globalAlpha = 0.5;
			ctx.fillText( window.location.hostname, PAD, bottomY - 14 );
			ctx.globalAlpha = 1;

			showPreview( canvas );
		}

		function showPreview( canvas ) {
			var overlay = document.createElement( 'div' );
			overlay.className = 'rs-quote-overlay';

			var dlIcon =
				'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" ' +
				'stroke="currentColor" stroke-width="2" stroke-linecap="round" ' +
				'stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>' +
				'<polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';

			var shIcon =
				'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" ' +
				'stroke="currentColor" stroke-width="2" stroke-linecap="round" ' +
				'stroke-linejoin="round"><circle cx="18" cy="5" r="3"/>' +
				'<circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>' +
				'<path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/></svg>';

			overlay.innerHTML =
				'<div class="rs-quote-panel">' +
				'<div class="rs-quote-actions">' +
				'<button type="button" data-rs-quote-dl>' + dlIcon + 'ডাউনলোড</button>' +
				'<button type="button" data-rs-quote-share>' + shIcon + 'শেয়ার</button>' +
				'</div></div>';

			var panel = overlay.querySelector( '.rs-quote-panel' );
			panel.insertBefore( canvas, panel.firstChild );

			document.body.appendChild( overlay );

			/* Lock scroll if not already locked by the post modal. */
			var wasLocked = document.body.classList.contains( 'rs-locked' );

			if ( ! wasLocked ) {
				document.body.classList.add( 'rs-locked' );
			}

			/* Trigger the CSS transition. */
			requestAnimationFrame( function () {
				overlay.classList.add( 'is-visible' );
			} );

			function closePreview() {
				overlay.classList.remove( 'is-visible' );

				setTimeout( function () {
					if ( overlay.parentNode ) {
						overlay.parentNode.removeChild( overlay );
					}

					if ( ! wasLocked ) {
						document.body.classList.remove( 'rs-locked' );
					}
				}, 300 );
			}

			/* Close on backdrop click. */
			overlay.addEventListener( 'click', function ( e ) {
				if ( e.target === overlay ) {
					closePreview();
				}
			} );

			/* Escape key. */
			var escHandler = function ( e ) {
				if ( 'Escape' === e.key ) {
					closePreview();
					document.removeEventListener( 'keydown', escHandler );
				}
			};

			document.addEventListener( 'keydown', escHandler );

			/* Download. */
			overlay.querySelector( '[data-rs-quote-dl]' ).addEventListener( 'click', function () {
				canvas.toBlob( function ( blob ) {
					var a = document.createElement( 'a' );
					a.href = URL.createObjectURL( blob );
					a.download = 'quote-card.png';
					a.click();
					URL.revokeObjectURL( a.href );
					toast( 'কার্ড ডাউনলোড হয়েছে' );
				}, 'image/png' );
			} );

			/* Share (with file) or download as fallback. */
			overlay.querySelector( '[data-rs-quote-share]' ).addEventListener( 'click', function () {
				canvas.toBlob( function ( blob ) {
					if ( navigator.share && navigator.canShare ) {
						var file = new File( [ blob ], 'quote-card.png', { type: 'image/png' } );
						var payload = { files: [ file ] };

						if ( navigator.canShare( payload ) ) {
							navigator.share( payload ).then( null, function () {} );
							return;
						}
					}

					/* Fallback to download. */
					var a = document.createElement( 'a' );
					a.href = URL.createObjectURL( blob );
					a.download = 'quote-card.png';
					a.click();
					URL.revokeObjectURL( a.href );
					toast( 'কার্ড ডাউনলোড হয়েছে' );
				}, 'image/png' );
			} );
		}
	}() );

	/* ---------------------------------------------------------------
	 * Fetch dynamic featured post (only if not already rendered by server)
	 * ------------------------------------------------------------ */
	( function () {
		var wrap = $( '#rs-featured-wrap' );

		if ( ! wrap || wrap.firstElementChild ) {
			return;
		}

		getJSON( rest + 'featured' + ( cfg.catId ? '?cat=' + cfg.catId : '' ) ).then( function ( data ) {
			if ( data && data.html ) {
				wrap.innerHTML = data.html;
			}
		} ).catch( function () {} );
	}() );

	/* ---------------------------------------------------------------
	 * Editing a post where it is read
	 *
	 * For a typo, or a word too many — most of what anyone comes back to
	 * a finished piece to do, and none of it worth opening the dashboard
	 * for. The link beside this one still goes there, for the edits that
	 * are worth it.
	 *
	 * None of this runs for a reader. The button that begins it is
	 * printed only for someone who may edit the post, and the endpoint
	 * asks that question again rather than taking the page's word.
	 * ------------------------------------------------------------ */

	( function () {
		var open = null;

		function barHtml() {
			return (
				'<div class="rs-edit">' +
				'<button class="rs-edit__btn rs-edit__btn--save" type="button" data-rs-edit-save>সেভ করুন</button>' +
				'<button class="rs-edit__btn" type="button" data-rs-edit-cancel>বাতিল</button>' +
				'<span class="rs-edit__note" aria-live="polite"></span>' +
				'</div>'
			);
		}

		function changed() {
			return !! open && (
				open.title.textContent !== open.wasTitle ||
				open.body.innerHTML !== open.wasBody
			);
		}

		function start( article ) {
			if ( open || ! article ) {
				return;
			}

			var title = $( '.rs-article__title', article );
			var body = $( '.rs-article__body', article );

			if ( ! title || ! body ) {
				return;
			}

			open = {
				article: article,
				title: title,
				body: body,
				wasTitle: title.textContent,
				wasBody: body.innerHTML,
			};

			article.classList.add( 'is-editing' );
			title.setAttribute( 'contenteditable', 'true' );
			body.setAttribute( 'contenteditable', 'true' );

			/* A picture cannot be improved by typing at it, and one stray
			   key would take it out of the post. */
			$$( 'img', body ).forEach( function ( img ) {
				img.setAttribute( 'contenteditable', 'false' );
			} );

			var holder = document.createElement( 'div' );

			holder.innerHTML = barHtml();
			open.bar = holder.firstChild;
			body.parentNode.insertBefore( open.bar, body.nextSibling );

			title.focus();
		}

		function finish() {
			if ( ! open ) {
				return;
			}

			open.title.removeAttribute( 'contenteditable' );
			open.body.removeAttribute( 'contenteditable' );
			open.article.classList.remove( 'is-editing' );

			if ( open.bar && open.bar.parentNode ) {
				open.bar.parentNode.removeChild( open.bar );
			}

			open = null;
		}

		function cancel() {
			if ( ! open ) {
				return;
			}

			open.title.textContent = open.wasTitle;
			open.body.innerHTML = open.wasBody;
			finish();
		}

		/* What came back, not what was sent: kses may have trimmed it, and
		   the reading time is worked out again from whatever survived. */
		function apply( article, data ) {
			var title = $( '.rs-article__title', article );
			var body = $( '.rs-article__body', article );
			var read = $( '.rs-article__read', article );
			var row = document.querySelector( '[data-rs-post="' + data.id + '"] .rs-row__title' );

			if ( title ) {
				title.textContent = data.title;
			}

			if ( body ) {
				body.innerHTML = data.content;
			}

			if ( read && data.readingTime ) {
				read.textContent = data.readingTime;
			}

			/* The modal keeps what it fetched, so without this the old
			   words come back the next time the post is opened. */
			if ( cache[ data.id ] ) {
				cache[ data.id ].title = data.title;
				cache[ data.id ].content = data.content;
				cache[ data.id ].readingTime = data.readingTime;
			}

			/* And the row underneath, if this list happens to show it. */
			if ( row ) {
				row.textContent = data.title;
			}

			if ( postBody && article === postBody ) {
				document.title = data.title + ' – ' + ( cfg.siteName || baseTitle );
			}
		}

		function save() {
			if ( ! open ) {
				return;
			}

			var article = open.article;
			var note = $( '.rs-edit__note', open.bar );
			var id = article.getAttribute( 'data-rs-id' );

			if ( ! id || ! cfg.editNonce ) {
				toast( 'সেভ হয়নি', 'পাতাটা রিফ্রেশ করে আবার চেষ্টা করুন' );
				return;
			}

			note.textContent = 'সেভ হচ্ছে...';

			window.fetch( rest + 'edit/' + id, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					/* What lets WordPress trust the cookie this request
					   carries, and the reason current_user_can() means
					   anything at the other end. */
					'X-WP-Nonce': cfg.editNonce,
				},
				body: JSON.stringify( {
					title: open.title.textContent.trim(),
					content: open.body.innerHTML,
				} ),
			} )
				.then( function ( res ) {
					return res.json().then( function ( data ) {
						if ( ! res.ok ) {
							throw new Error( data && data.message ? data.message : res.status );
						}

						return data;
					} );
				} )
				.then( function ( data ) {
					apply( article, data );
					finish();
					toast( 'সেভ হয়েছে' );
				} )
				.then( null, function ( err ) {
					/* Left open on purpose: the words are still in the box,
					   and closing it now is the one way to lose them. */
					note.textContent = '';
					toast( 'সেভ হয়নি', err && err.message ? String( err.message ) : '' );
				} );
		}

		document.addEventListener( 'click', function ( event ) {
			var where = event.target.closest ? event.target : null;

			if ( ! where ) {
				return;
			}

			if ( where.closest( '[data-rs-edit]' ) ) {
				event.preventDefault();
				start( where.closest( '.rs-article' ) );
				return;
			}

			if ( where.closest( '[data-rs-edit-save]' ) ) {
				event.preventDefault();
				save();
				return;
			}

			if ( where.closest( '[data-rs-edit-cancel]' ) ) {
				event.preventDefault();
				cancel();
			}
		} );

		/* Capture, so it beats the delegated handlers that would otherwise
		   open a post or follow a link out of the one being edited. */
		document.addEventListener( 'click', function ( event ) {
			if ( ! open || ! event.target.closest ) {
				return;
			}

			var link = event.target.closest( 'a' );

			if ( link && open.body.contains( link ) ) {
				event.preventDefault();
				event.stopPropagation();
			}
		}, true );

		document.addEventListener( 'keydown', function ( event ) {
			/* A title is one line. Enter in it would make a second. */
			if ( open && 'Enter' === event.key && event.target === open.title ) {
				event.preventDefault();
			}
		} );

		/* Paste arrives as plain text. Markup carried in from a word
		   processor or another site is the commonest way a post's HTML
		   ends up in a state nobody wrote. */
		document.addEventListener( 'paste', function ( event ) {
			if ( ! open || ! event.target.closest ) {
				return;
			}

			if ( ! event.target.closest( '[contenteditable="true"]' ) ) {
				return;
			}

			var clip = event.clipboardData || window.clipboardData;

			if ( ! clip ) {
				return;
			}

			event.preventDefault();
			document.execCommand( 'insertText', false, clip.getData( 'text/plain' ) );
		} );

		window.addEventListener( 'beforeunload', function ( event ) {
			if ( changed() ) {
				event.preventDefault();
				event.returnValue = '';
			}
		} );

		isEditing = function () {
			return !! open;
		};

		editorMayClose = function () {
			if ( ! open ) {
				return true;
			}

			if ( ! changed() ) {
				finish();
				return true;
			}

			if ( window.confirm( 'সম্পাদনা সেভ করা হয়নি। বাতিল করে বেরিয়ে যাবেন?' ) ) {
				cancel();
				return true;
			}

			return false;
		};
	}() );

	/* ---------------------------------------------------------------
	 * Service Worker helper
	 *
	 * Sends a message to the active service worker, if one is
	 * controlling this page.  Used by the "পরে পড়ব" handlers above
	 * to pre-cache and uncache post JSON.
	 * ------------------------------------------------------------ */

	function swMessage( msg ) {
		if ( navigator.serviceWorker && navigator.serviceWorker.controller ) {
			navigator.serviceWorker.controller.postMessage( msg );
		}
	}

	/* ---------------------------------------------------------------
	 * Register the service worker
	 * ------------------------------------------------------------ */

	( function () {
		if ( ! ( 'serviceWorker' in navigator ) ) {
			return;
		}

		navigator.serviceWorker.register( '/?rs-sw', { scope: '/' } ).catch( function () {
			/* Registration can fail in private mode or restrictive
			   environments.  The site works without it. */
		} );
	}() );

	/* A post opened at its own address, rather than in the modal. Zero on
	   every other kind of page, and countView() ignores zero. */
	var mainPid = parseInt( cfg.postId, 10 );
	if ( mainPid ) {
		countView( mainPid );
	}

	markRead();
	staggerRows();
}() );

