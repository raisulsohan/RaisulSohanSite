	/* ---------------------------------------------------------------
	 * "You were reading"
	 *
	 * A position is only kept while a post is started and unfinished, so
	 * the newest entry is exactly the thread the reader dropped. Offering
	 * it back is the whole point of having remembered it: until now the
	 * position only helped someone who found their way to the same story
	 * again on their own.
	 * ------------------------------------------------------------ */

	function renderResume() {
		var host = $( '#rs-resume' );
		if ( ! host ) { return; }

		var map = readPositions();
		/*
		 * This edition's own stories only. Both editions write to one
		 * localStorage, and a translation left half-read belongs to the
		 * other edition's front page — offering it here would put a link on
		 * this one whose id names a different story on this site.
		 */
		var keys = Object.keys( map ).filter( function ( key ) {
			return ( key.indexOf( '~en' ) > -1 ) === isEn;
		} );

		var targetKey = null;
		var currentCat = parseInt( cfg.catId, 10 ) || 0;
		if ( currentCat > 0 ) {
			for ( var i = keys.length - 1; i >= 0; i-- ) {
				var entry = map[ keys[ i ] ];
				if ( entry && entry.c === currentCat ) {
					targetKey = keys[ i ];
					break;
				}
			}
		}

		if ( ! targetKey && keys.length > 0 ) {
			targetKey = keys[ keys.length - 1 ];
		}

		var last = targetKey ? map[ targetKey ] : null;

		if ( ! last || 'number' === typeof last || ! last.n || ! last.u ) {
			host.innerHTML = '';
			return;
		}

		/* The key carries the edition; the post id is what comes before it. */
		var id = targetKey.slice( 1 ).split( '~' )[ 0 ];

		var resLabel = ( strings && strings.resumeLabel ) || 'আপনি পড়ছিলেন';
		var resClose = ( strings && strings.dismiss ) || 'সরিয়ে দিন';

		host.innerHTML =
			'<div class="rs-resume">' +
			'<a class="rs-resume__link" href="' + escapeHtml( last.u ) +
			'" data-rs-post="' + escapeHtml( id ) + '">' +
			'<span class="rs-resume__label">' + escapeHtml( resLabel ) + '</span>' +
			'<span class="rs-resume__title">' + escapeHtml( last.n ) + '</span>' +
			'</a>' +
			'<button class="rs-resume__close" type="button" aria-label="' + escapeHtml( resClose ) + '">&times;</button>' +
			'</div>';

		$( '.rs-resume__close', host ).addEventListener( 'click', function () {
			var fresh = readPositions();
			delete fresh[ targetKey ];
			store( POS_KEY, JSON.stringify( fresh ) );
			host.innerHTML = '';
		} );
	}
	renderResume();
