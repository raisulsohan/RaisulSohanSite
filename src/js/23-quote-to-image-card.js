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
				( ( strings && strings.cardTitle ) || 'উদ্ধৃতি কার্ড' );

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

			var dlText = ( strings && strings.download ) || 'ডাউনলোড';
			var shText = ( strings && strings.shareBtn ) || 'শেয়ার';

			overlay.innerHTML =
				'<div class="rs-quote-panel">' +
				'<div class="rs-quote-actions">' +
				'<button type="button" data-rs-quote-dl>' + dlIcon + escapeHtml( dlText ) + '</button>' +
				'<button type="button" data-rs-quote-share>' + shIcon + escapeHtml( shText ) + '</button>' +
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
					toast( ( strings && strings.cardSaved ) || 'কার্ড ডাউনলোড হয়েছে' );
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
					toast( ( strings && strings.cardSaved ) || 'কার্ড ডাউনলোড হয়েছে' );
				}, 'image/png' );
			} );
		}
	}() );

	/* The featured post used to be fetched from here and swapped in, which
	   worked but showed the reader one post being replaced by another. It
	   is picked in the page now, before the block is ever painted — see
	   rs_render_featured_pool() in functions.php. */
