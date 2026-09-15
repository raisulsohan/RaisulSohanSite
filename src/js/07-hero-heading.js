	/* ---------------------------------------------------------------
	 * Hero heading
	 *
	 * Words fade up out of a blur, one after another, then lift away
	 * before the next phrase arrives. Split by word, never by letter:
	 * breaking Bengali between spans would wreck যুক্তাক্ষর and মাত্রা.
	 * ------------------------------------------------------------ */

	function startHeroReveal() {
		var target = $( '#rs-type' );
		var phrases = cfg.phrases || [];

		if ( ! target || phrases.length === 0 ) {
			return;
		}

		if ( reduceMotion ) {
			target.textContent = phrases[ 0 ];
			return;
		}

		var phraseIdx = 0;

		function build( phrase ) {
			var words = phrase.split( /\s+/ ).filter( Boolean );
			var spans = [];

			target.textContent = '';

			words.forEach( function ( word, i ) {
				if ( i > 0 ) {
					target.appendChild( document.createTextNode( ' ' ) );
				}

				var span = document.createElement( 'span' );

				span.className = 'rs-word';
				span.textContent = word;
				span.style.transitionDelay = i * HERO_IN_STEP + 'ms';

				target.appendChild( span );
				spans.push( span );
			} );

			return spans;
		}

		function reveal() {
			var spans = build( phrases[ phraseIdx ] );

			/* Flush the hidden state so the transition has somewhere to start. */
			void target.offsetWidth;

			spans.forEach( function ( span ) {
				span.classList.add( 'is-in' );
			} );

			if ( phrases.length === 1 ) {
				return;
			}

			var settled = HERO_IN_DUR + ( spans.length - 1 ) * HERO_IN_STEP;

			window.setTimeout( function () {
				dismiss( spans );
			}, settled + HERO_HOLD );
		}

		function dismiss( spans ) {
			spans.forEach( function ( span, i ) {
				span.style.transitionDelay = i * HERO_OUT_STEP + 'ms';
				span.classList.remove( 'is-in' );
				span.classList.add( 'is-out' );
			} );

			var gone = HERO_OUT_DUR + ( spans.length - 1 ) * HERO_OUT_STEP;

			window.setTimeout( function () {
				phraseIdx = ( phraseIdx + 1 ) % phrases.length;
				reveal();
			}, gone + 140 );
		}

		reveal();
	}

	startHeroReveal();
