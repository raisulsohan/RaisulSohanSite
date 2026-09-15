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
