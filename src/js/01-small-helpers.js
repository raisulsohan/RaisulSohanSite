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
		if ( isEn ) {
			return String( value );
		}
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

	/*
	 * A story's name in this browser's own lists.
	 *
	 * The two editions are two sites but one domain, so they share one
	 * localStorage — and each site hands out its own post ids, which means a
	 * story and its translation are both 190. Left at the bare number, the
	 * three lists kept here (already read, where reading stopped, read
	 * later) could not tell them apart: finishing the English piece greyed
	 * out the Bengali row, and one saved position overwrote the other.
	 *
	 * Bengali keeps the bare number so every list saved before the editions
	 * were paired still reads; the English edition's entries are marked.
	 */
	function langKey( id, lang ) {
		var code = lang || ( isEn ? 'en' : 'bn' );

		return String( id ) + ( 'en' === code ? '~en' : '' );
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
