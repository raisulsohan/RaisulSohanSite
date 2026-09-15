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
