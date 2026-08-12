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
		var wrap = $( '#rs-list-wrap' );

		if ( ! wrap ) {
			return;
		}

		var read = readList();

		$$( '[data-rs-post]', wrap ).forEach( function ( link ) {
			var row = link.closest ? link.closest( '.rs-row' ) : null;

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
		} ).then(
			function () {
				if ( ! first ) {
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
			},
			function () {
				/* A missed count is not worth telling the reader about. */
			}
		);
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

	function editLinkHtml() {
		var icon = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';

		return '<a class="rs-article__edit" href="">' + icon + 'সম্পাদনা</a>';
	}

	function shareHtml( link ) {
		var cpIcon = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';

		return (
			'<div class="rs-share">' +
			'<p class="rs-share__label">শেয়ার করুন</p>' +
			'<div class="rs-share__row">' +
			'<button class="rs-share__btn" type="button" data-rs-copy="' + escapeHtml( link ) + '">' +
			cpIcon + 'লিঙ্ক কপি</button>' +
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
			shareHtml( data.link ) +
			relatedHtml( data ) +
			nextPrevHtml( data );

		$( '.rs-article__date', postBody ).textContent = data.date;

		if ( data.readingTime ) {
			$( '.rs-article__read', postBody ).textContent = data.readingTime;
		}
		$( '.rs-article__title', postBody ).textContent = data.title;
		$( '.rs-article__author', postBody ).textContent = data.author;

		if ( cfg.editBase ) {
			/* href as a property, so the id never passes through innerHTML. */
			$( '.rs-article__edit', postBody ).href = cfg.editBase + data.id;
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
		restoreScroll( postBody.parentNode, readPositions()[ 'p' + data.id ] || 0 );
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
	 * One localStorage entry holding { p<id>: scrollTop }. Keys carry a
	 * "p" so they stay non-numeric: JavaScript orders integer-like object
	 * keys numerically, which would make the oldest-first pruning below
	 * throw away the wrong entries.
	 * ------------------------------------------------------------ */

	var POS_KEY = 'rs-pos';
	/* Below this the reader has barely started, and being dropped a
	   centimetre down the page is worse than starting at the top. */
	var POS_MIN = 200;
	var POS_KEEP = 40;

	function readPositions() {
		try {
			return JSON.parse( store( POS_KEY ) || '{}' ) || {};
		} catch ( e ) {
			return {};
		}
	}

	function savePosition( id, top, travel ) {
		if ( ! id ) {
			return;
		}

		var map = readPositions();
		var key = 'p' + id;

		/* Forget the position once they have reached the end: coming back
		   to a finished post should start at the beginning. */
		if ( top < POS_MIN || ( travel > 0 && top / travel > 0.9 ) ) {
			delete map[ key ];
		} else {
			delete map[ key ];
			map[ key ] = Math.round( top );
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

		savePosition( currentPostId, scroller.scrollTop, scroller.scrollHeight - scroller.clientHeight );
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
			document.title = data.title + ' · ' + ( cfg.siteName || baseTitle );

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

			if ( '' === term ) {
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
	 * Dark / light toggle
	 *
	 * The attribute is already on <html> by the time this runs, set by
	 * the inline script in wp_head. This only flips and remembers it.
	 * ------------------------------------------------------------ */

	( function () {
		var btn = $( '[data-rs-theme]' );

		if ( ! btn ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			var root = document.documentElement;
			var next = 'dark' === root.getAttribute( 'data-theme' ) ? 'light' : 'dark';

			root.setAttribute( 'data-theme', next );
			store( 'rs-theme', next );
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

	/* A post opened at its own address, rather than in the modal. Zero on
	   every other kind of page, and countView() ignores zero. */
	countView( cfg.postId );

	markRead();
}() );
