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
				var shown = currentPostId ? cache[ postKey( currentPostRest, currentPostId ) ] : null;
				if ( timeLeftIndicator && shown && shown.readingTime ) {
					var rt = shown.readingTime;
					var totalMinutes = 0;
					var bnDigitsStr = rt.replace(/[^\u09E6-\u09EF]/g, '');
					if ( bnDigitsStr ) {
						var enDigits = bnDigitsStr.replace(/[\u09E6-\u09EF]/g, function(d) {
							return "০১২৩৪৫৬৭৮৯".indexOf(d);
						});
						totalMinutes = parseInt(enDigits, 10);
					} else {
						var m = rt.match(/\d+/);
						if ( m ) {
							totalMinutes = parseInt(m[0], 10);
						}
					}
					if ( totalMinutes > 0 ) {
						var remaining = Math.ceil( totalMinutes * ( 1 - ( pct / 100 ) ) );
						if ( pct >= 97 ) {
							timeLeftIndicator.textContent = ( window.RS && window.RS.isEn ) ? '✓ Render complete' : '✓ রেন্ডার শেষ';
							timeLeftIndicator.classList.add('is-visible');
						} else if ( remaining > 0 && pct > 5 ) {
							timeLeftIndicator.textContent = ( window.RS && window.RS.isEn )
								? ( remaining + " min left" )
								: ( "আর " + bnDigits(remaining) + " মিনিট বাকি" );
							timeLeftIndicator.classList.add('is-visible');
						} else {
							timeLeftIndicator.classList.remove('is-visible');
						}
						/* The end of a story is a finished render. */
						timeLeftIndicator.classList.toggle('is-done', pct >= 97);
						if ( bar ) {
							bar.classList.toggle('rs-bar-done', pct >= 97);
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
