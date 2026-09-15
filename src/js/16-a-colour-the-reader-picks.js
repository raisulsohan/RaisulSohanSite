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
