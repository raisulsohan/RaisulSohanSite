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
				var totalMinutes = 0;
				var bnDigitsStr = readTimeStr.replace(/[^\u09E6-\u09EF]/g, '');
				if ( bnDigitsStr ) {
					var enDigits = bnDigitsStr.replace(/[\u09E6-\u09EF]/g, function(d) {
						return "০১২৩৪৫৬৭৮৯".indexOf(d);
					});
					totalMinutes = parseInt(enDigits, 10);
				} else {
					var m = readTimeStr.match(/\d+/);
					if ( m ) {
						totalMinutes = parseInt(m[0], 10);
					}
				}
				if ( totalMinutes > 0 ) {
					var remaining = Math.ceil( totalMinutes * ( 1 - ( pct / 100 ) ) );
					if ( remaining > 0 && pct > 5 ) {
						singleTimeLeft.textContent = ( window.RS && window.RS.isEn )
							? ( remaining + " min left" )
							: ( "আর " + bnDigits(remaining) + " মিনিট বাকি" );
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
