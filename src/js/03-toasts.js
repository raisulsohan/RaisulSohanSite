	/* ---------------------------------------------------------------
	 * Toasts
	 * ------------------------------------------------------------ */

	function toast( title, description ) {
		var host = $( '#rs-toasts' );

		if ( ! host ) {
			return;
		}

		var el = document.createElement( 'div' );
		el.className = 'rs-toast';
		el.innerHTML =
			'<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">' +
			'<circle cx="12" cy="12" r="10" fill="currentColor"/>' +
			'<path d="m8.5 12.4 2.4 2.4 4.6-5" fill="none" stroke="#faf9f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
			'</svg><div></div>';

		var text = el.lastChild;
		var head = document.createElement( 'div' );
		head.className = 'rs-toast__title';
		head.textContent = title;
		text.appendChild( head );

		if ( description ) {
			var desc = document.createElement( 'div' );
			desc.className = 'rs-toast__desc';
			desc.textContent = description;
			text.appendChild( desc );
		}

		host.appendChild( el );

		window.setTimeout( function () {
			el.classList.add( 'is-out' );
			window.setTimeout( function () {
				if ( el.parentNode ) {
					el.parentNode.removeChild( el );
				}
			}, 200 );
		}, 2500 );
	}
