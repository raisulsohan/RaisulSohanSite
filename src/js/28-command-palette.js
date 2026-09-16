	/* ---------------------------------------------------------------
	 * Command palette (Ctrl+K / Cmd+K)
	 *
	 * One box for everything a keyboard person wants: stories by title
	 * (the same search endpoint the search pop-up uses), portfolio
	 * projects, and the site's own actions. Built on first use, so pages
	 * that never open it pay for nothing but this code.
	 * ------------------------------------------------------------ */

	( function () {
		var isEn    = !! cfg.isEn;
		var home    = ( cfg.home || '/' ).replace( /\/?$/, '/' );
		var rest    = cfg.rest || '';
		var box     = null;
		var input   = null;
		var list    = null;
		var items   = [];
		var active  = 0;
		var opener  = null;
		var projects = null;
		var stories = [];
		var seq     = 0;
		var timer   = 0;

		function t( en, bn ) {
			return isEn ? en : bn;
		}

		function go( url ) {
			window.location.href = url;
		}

		function click( selector ) {
			var el = document.querySelector( selector );
			if ( el ) {
				el.click();
			}
		}

		function commands() {
			var list = [
				{ label: t( 'Home', 'প্রথম পাতা' ), keys: 'home index front', run: function () { go( home ); } },
				{ label: t( 'Portfolio', 'পোর্টফোলিও' ), keys: 'portfolio work projects', run: function () { go( home + 'portfolio/' ); } },
				{ label: t( 'Story timeline', 'লেখার টাইমলাইন' ), keys: 'timeline archive all stories', run: function () { go( home + 'timeline/' ); } },
				{ label: t( 'A random story', 'যেকোনো একটা লেখা' ), keys: 'random surprise lucky', run: function () { go( home + '?rs_random=1' ); } },
				{ label: t( 'About me', 'আমি' ), keys: 'about me author', run: function () { click( '[data-rs-open="about"]' ); } },
				{ label: t( 'Search stories', 'লেখা খুঁজুন' ), keys: 'search find', run: function () { click( '[data-rs-open="search"]' ); } },
				{ label: t( 'Copy email address', 'ইমেইল ঠিকানা কপি করুন' ), keys: 'email mail contact copy', run: function () { click( '[data-rs-copy-kind="mail"]' ); } },
				{ label: t( 'Privacy', 'গোপনীয়তা' ), keys: 'privacy', run: function () { go( home + 'privacy/' ); } }
			];

			if ( ! document.body.classList.contains( 'rs-stage' ) && document.querySelector( '[data-rs-theme]' ) ) {
				list.splice( 4, 0, { label: t( 'Switch dark / light mode', 'ডার্ক / লাইট মোড বদলান' ), keys: 'dark light theme mode night', run: function () { click( '[data-rs-theme]' ); } } );
			}

			var lang = document.querySelector( '.rs-lang-switcher' );
			if ( lang ) {
				list.push( { label: t( 'বাংলায় পড়ুন', 'Read in English' ), keys: 'language english bangla bengali', run: function () { go( lang.getAttribute( 'href' ) ); } } );
			}

			return list.map( function ( item ) {
				item.group = t( 'Go to', 'যান' );
				return item;
			} );
		}

		function build() {
			box = document.createElement( 'div' );
			box.className = 'rs-cmdk';
			box.hidden = true;
			box.setAttribute( 'role', 'dialog' );
			box.setAttribute( 'aria-modal', 'true' );
			box.setAttribute( 'aria-label', t( 'Command palette', 'কমান্ড প্যালেট' ) );
			box.innerHTML =
				'<div class="rs-cmdk__panel">' +
					'<div class="rs-cmdk__field">' +
						'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>' +
						'<input class="rs-cmdk__input" type="text" role="combobox" aria-expanded="true" aria-controls="rs-cmdk-list" aria-autocomplete="list" autocomplete="off" spellcheck="false">' +
						'<kbd>Esc</kbd>' +
					'</div>' +
					'<ul class="rs-cmdk__list" id="rs-cmdk-list" role="listbox"></ul>' +
					'<div class="rs-cmdk__foot"><span><kbd>↑</kbd><kbd>↓</kbd> ' + t( 'choose', 'বাছুন' ) + '</span><span><kbd>Enter</kbd> ' + t( 'open', 'খুলুন' ) + '</span><span><kbd>Ctrl</kbd><kbd>K</kbd></span></div>' +
				'</div>';

			document.body.appendChild( box );

			input = box.querySelector( 'input' );
			list  = box.querySelector( 'ul' );
			input.setAttribute( 'placeholder', t( 'Stories, projects, commands…', 'লেখা, প্রজেক্ট, কমান্ড… কী খুঁজছেন?' ) );

			box.addEventListener( 'mousedown', function ( e ) {
				if ( e.target === box ) {
					close();
				}
			} );

			input.addEventListener( 'input', function () {
				active = 0;
				render();
				queueSearch();
			} );

			input.addEventListener( 'keydown', function ( e ) {
				if ( 'ArrowDown' === e.key || 'ArrowUp' === e.key ) {
					e.preventDefault();
					if ( items.length ) {
						active = ( active + ( 'ArrowDown' === e.key ? 1 : -1 ) + items.length ) % items.length;
						paint();
					}
				} else if ( 'Enter' === e.key ) {
					e.preventDefault();
					choose( active );
				} else if ( 'Escape' === e.key ) {
					e.preventDefault();
					close();
				} else if ( 'Tab' === e.key ) {
					e.preventDefault();
				}
			} );

			list.addEventListener( 'mousemove', function ( e ) {
				var li = e.target.closest ? e.target.closest( '[data-i]' ) : null;
				if ( li && +li.getAttribute( 'data-i' ) !== active ) {
					active = +li.getAttribute( 'data-i' );
					paint();
				}
			} );

			list.addEventListener( 'click', function ( e ) {
				var li = e.target.closest ? e.target.closest( '[data-i]' ) : null;
				if ( li ) {
					choose( +li.getAttribute( 'data-i' ) );
				}
			} );
		}

		function matches( item, q ) {
			return ! q || ( item.label + ' ' + ( item.keys || '' ) + ' ' + ( item.hint || '' ) ).toLowerCase().indexOf( q ) !== -1;
		}

		function render() {
			var q = input.value.trim().toLowerCase();
			var groups = [];

			groups.push( commands().filter( function ( item ) {
				return matches( item, q );
			} ) );

			if ( projects ) {
				groups.push( projects.filter( function ( item ) {
					return matches( item, q );
				} ).slice( 0, q ? 8 : 5 ) );
			}

			if ( q.length >= 2 ) {
				groups.push( stories );
			}

			items = [].concat.apply( [], groups );
			active = Math.min( active, Math.max( 0, items.length - 1 ) );

			list.textContent = '';

			var lastGroup = '';

			items.forEach( function ( item, i ) {
				if ( item.group !== lastGroup ) {
					var head = document.createElement( 'li' );
					head.className = 'rs-cmdk__group';
					head.setAttribute( 'role', 'presentation' );
					head.textContent = item.group;
					list.appendChild( head );
					lastGroup = item.group;
				}

				var li = document.createElement( 'li' );
				li.className = 'rs-cmdk__item';
				li.id = 'rs-cmdk-' + i;
				li.setAttribute( 'role', 'option' );
				li.setAttribute( 'data-i', i );

				var label = document.createElement( 'span' );
				label.className = 'rs-cmdk__label';
				label.textContent = item.label;
				li.appendChild( label );

				if ( item.hint ) {
					var hint = document.createElement( 'span' );
					hint.className = 'rs-cmdk__hint';
					hint.textContent = item.hint;
					li.appendChild( hint );
				}

				list.appendChild( li );
			} );

			if ( ! items.length ) {
				var none = document.createElement( 'li' );
				none.className = 'rs-cmdk__empty';
				none.setAttribute( 'role', 'presentation' );
				none.textContent = t( 'Nothing matches', 'কিছু মিলল না' );
				list.appendChild( none );
			}

			paint();
		}

		function paint() {
			Array.prototype.forEach.call( list.querySelectorAll( '[data-i]' ), function ( li ) {
				var on = +li.getAttribute( 'data-i' ) === active;
				li.classList.toggle( 'is-active', on );
				li.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				if ( on ) {
					input.setAttribute( 'aria-activedescendant', li.id );
					if ( li.scrollIntoView ) {
						li.scrollIntoView( { block: 'nearest' } );
					}
				}
			} );
		}

		function choose( i ) {
			var item = items[ i ];
			if ( ! item ) {
				return;
			}
			close( true );
			item.run();
		}

		function loadProjects() {
			if ( projects || ! rest || ! window.fetch ) {
				return;
			}

			projects = [];

			window.fetch( rest + 'projects' ).then( function ( r ) {
				return r.ok ? r.json() : [];
			} ).then( function ( data ) {
				projects = ( data || [] ).map( function ( p ) {
					return {
						group: t( 'Projects', 'প্রজেক্ট' ),
						label: p.name,
						hint: p.type,
						keys: 'project portfolio ' + p.id,
						run: function () {
							go( p.url );
						}
					};
				} );
				if ( box && ! box.hidden ) {
					render();
				}
			} ).catch( function () {} );
		}

		function queueSearch() {
			window.clearTimeout( timer );

			var q = input.value.trim();

			if ( q.length < 2 || ! rest || ! window.fetch ) {
				stories = [];
				return;
			}

			var mine = ++seq;

			timer = window.setTimeout( function () {
				window.fetch( rest + 'search?q=' + encodeURIComponent( q ) ).then( function ( r ) {
					return r.ok ? r.json() : { items: [] };
				} ).then( function ( data ) {
					if ( mine !== seq ) {
						return;
					}
					stories = ( ( data && data.items ) || [] ).slice( 0, 7 ).map( function ( s ) {
						return {
							group: t( 'Stories', 'লেখা' ),
							label: s.title,
							hint: s.date,
							run: function () {
								go( s.link );
							}
						};
					} );
					render();
				} ).catch( function () {} );
			}, 180 );
		}

		function open() {
			if ( ! box ) {
				build();
			}

			opener = document.activeElement;
			box.hidden = false;
			document.body.classList.add( 'rs-cmdk-open' );
			input.value = '';
			stories = [];
			active = 0;
			render();
			loadProjects();
			window.setTimeout( function () {
				input.focus();
			}, 0 );
		}

		function close( keepFocus ) {
			if ( ! box || box.hidden ) {
				return;
			}

			box.hidden = true;
			document.body.classList.remove( 'rs-cmdk-open' );

			if ( keepFocus !== true && opener && opener.focus ) {
				opener.focus();
			}
		}

		document.addEventListener( 'keydown', function ( e ) {
			if ( ( e.ctrlKey || e.metaKey ) && ! e.altKey && ! e.shiftKey && 'k' === ( e.key || '' ).toLowerCase() ) {
				e.preventDefault();

				if ( box && ! box.hidden ) {
					close();
				} else {
					open();
				}
			}
		} );
	}() );
