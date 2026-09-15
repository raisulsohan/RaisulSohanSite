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
