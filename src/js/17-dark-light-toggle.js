	/* ---------------------------------------------------------------
	 * Dark / light toggle
	 *
	 * The attribute is already on <html> by the time this runs, set by
	 * the inline script in wp_head. This only flips and remembers it.
	 * ------------------------------------------------------------ */

	( function () {
		var btn = $( '[data-rs-theme]' );

		/* The browser chrome (Android status bar, PWA title bar) follows
		   this tag, so it has to change along with the page colour. */
		function syncThemeColor() {
			var meta = document.querySelector( 'meta[name="theme-color"]' );
			var bg   = window.getComputedStyle( document.documentElement ).getPropertyValue( '--rs-bg' ).trim();

			if ( meta && bg ) {
				meta.setAttribute( 'content', bg );
			}
		}

		if ( btn ) {
			btn.setAttribute( 'aria-pressed', 'dark' === document.documentElement.getAttribute( 'data-theme' ) ? 'true' : 'false' );
		}
		syncThemeColor();

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
			btn.setAttribute( 'aria-pressed', 'dark' === next ? 'true' : 'false' );
			syncThemeColor();

			var input = $( '#rs-tint' );

			if ( input ) {
				input.value = currentBg();
			}
		} );
	}() );
