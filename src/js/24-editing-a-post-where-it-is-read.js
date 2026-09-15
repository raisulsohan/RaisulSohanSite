	/* ---------------------------------------------------------------
	 * Editing a post where it is read
	 *
	 * For a typo, or a word too many — most of what anyone comes back to
	 * a finished piece to do, and none of it worth opening the dashboard
	 * for. The link beside this one still goes there, for the edits that
	 * are worth it.
	 *
	 * None of this runs for a reader. The button that begins it is
	 * printed only for someone who may edit the post, and the endpoint
	 * asks that question again rather than taking the page's word.
	 * ------------------------------------------------------------ */

	( function () {
		var open = null;

		function barHtml() {
			return (
				'<div class="rs-edit">' +
				'<button class="rs-edit__btn rs-edit__btn--save" type="button" data-rs-edit-save>' + ( ( strings && strings.saveBtn ) || 'সেভ করুন' ) + '</button>' +
				'<button class="rs-edit__btn" type="button" data-rs-edit-cancel>' + ( ( strings && strings.cancelBtn ) || 'বাতিল' ) + '</button>' +
				'<span class="rs-edit__note" aria-live="polite"></span>' +
				'</div>'
			);
		}

		function changed() {
			return !! open && (
				open.title.textContent !== open.wasTitle ||
				open.body.innerHTML !== open.wasBody
			);
		}

		function start( article ) {
			if ( open || ! article ) {
				return;
			}

			var title = $( '.rs-article__title', article );
			var body = $( '.rs-article__body', article );

			if ( ! title || ! body ) {
				return;
			}

			open = {
				article: article,
				title: title,
				body: body,
				wasTitle: title.textContent,
				wasBody: body.innerHTML,
			};

			article.classList.add( 'is-editing' );
			title.setAttribute( 'contenteditable', 'true' );
			body.setAttribute( 'contenteditable', 'true' );

			/* A picture cannot be improved by typing at it, and one stray
			   key would take it out of the post. */
			$$( 'img', body ).forEach( function ( img ) {
				img.setAttribute( 'contenteditable', 'false' );
			} );

			var holder = document.createElement( 'div' );

			holder.innerHTML = barHtml();
			open.bar = holder.firstChild;
			body.parentNode.insertBefore( open.bar, body.nextSibling );

			title.focus();
		}

		function finish() {
			if ( ! open ) {
				return;
			}

			open.title.removeAttribute( 'contenteditable' );
			open.body.removeAttribute( 'contenteditable' );
			open.article.classList.remove( 'is-editing' );

			if ( open.bar && open.bar.parentNode ) {
				open.bar.parentNode.removeChild( open.bar );
			}

			open = null;
		}

		function cancel() {
			if ( ! open ) {
				return;
			}

			open.title.textContent = open.wasTitle;
			open.body.innerHTML = open.wasBody;
			finish();
		}

		/* What came back, not what was sent: kses may have trimmed it, and
		   the reading time is worked out again from whatever survived. */
		function apply( article, data ) {
			var title = $( '.rs-article__title', article );
			var body = $( '.rs-article__body', article );
			var read = $( '.rs-article__read', article );
			var row = document.querySelector( '[data-rs-post="' + data.id + '"] .rs-row__title' );

			if ( title ) {
				title.textContent = data.title;
			}

			if ( body ) {
				body.innerHTML = data.content;
			}

			if ( read && data.readingTime ) {
				read.textContent = data.readingTime;
			}

			/* The modal keeps what it fetched, so without this the old
			   words come back the next time the post is opened. */
			if ( cache[ data.id ] ) {
				cache[ data.id ].title = data.title;
				cache[ data.id ].content = data.content;
				cache[ data.id ].readingTime = data.readingTime;
			}

			/* And the row underneath, if this list happens to show it. */
			if ( row ) {
				row.textContent = data.title;
			}

			if ( postBody && article === postBody ) {
				document.title = data.title + ' – ' + ( cfg.siteName || baseTitle );
			}
		}

		function save() {
			if ( ! open ) {
				return;
			}

			var article = open.article;
			var note = $( '.rs-edit__note', open.bar );
			var id = article.getAttribute( 'data-rs-id' );

			if ( ! id || ! cfg.editNonce ) {
				toast( ( strings && strings.saveFail ) || 'সেভ হয়নি', isEn ? 'Refresh page and try again' : 'পাতাটা রিফ্রেশ করে আবার চেষ্টা করুন' );
				return;
			}

			note.textContent = ( strings && strings.saving ) || 'সেভ হচ্ছে...';

			window.fetch( rest + 'edit/' + id, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					/* What lets WordPress trust the cookie this request
					   carries, and the reason current_user_can() means
					   anything at the other end. */
					'X-WP-Nonce': cfg.editNonce,
				},
				body: JSON.stringify( {
					title: open.title.textContent.trim(),
					content: open.body.innerHTML,
				} ),
			} )
				.then( function ( res ) {
					return res.json().then( function ( data ) {
						if ( ! res.ok ) {
							throw new Error( data && data.message ? data.message : res.status );
						}

						return data;
					} );
				} )
				.then( function ( data ) {
					apply( article, data );
					finish();
					toast( ( strings && strings.saved ) || 'সেভ হয়েছে' );
				} )
				.then( null, function ( err ) {
					/* Left open on purpose: the words are still in the box,
					   and closing it now is the one way to lose them. */
					note.textContent = '';
					toast( ( strings && strings.saveFail ) || 'সেভ হয়নি', err && err.message ? String( err.message ) : '' );
				} );
		}

		document.addEventListener( 'click', function ( event ) {
			var where = event.target.closest ? event.target : null;

			if ( ! where ) {
				return;
			}

			if ( where.closest( '[data-rs-edit]' ) ) {
				event.preventDefault();
				start( where.closest( '.rs-article' ) );
				return;
			}

			if ( where.closest( '[data-rs-edit-save]' ) ) {
				event.preventDefault();
				save();
				return;
			}

			if ( where.closest( '[data-rs-edit-cancel]' ) ) {
				event.preventDefault();
				cancel();
			}
		} );

		/* Capture, so it beats the delegated handlers that would otherwise
		   open a post or follow a link out of the one being edited. */
		document.addEventListener( 'click', function ( event ) {
			if ( ! open || ! event.target.closest ) {
				return;
			}

			var link = event.target.closest( 'a' );

			if ( link && open.body.contains( link ) ) {
				event.preventDefault();
				event.stopPropagation();
			}
		}, true );

		document.addEventListener( 'keydown', function ( event ) {
			/* A title is one line. Enter in it would make a second. */
			if ( open && 'Enter' === event.key && event.target === open.title ) {
				event.preventDefault();
			}
		} );

		/* Paste arrives as plain text. Markup carried in from a word
		   processor or another site is the commonest way a post's HTML
		   ends up in a state nobody wrote. */
		document.addEventListener( 'paste', function ( event ) {
			if ( ! open || ! event.target.closest ) {
				return;
			}

			if ( ! event.target.closest( '[contenteditable="true"]' ) ) {
				return;
			}

			var clip = event.clipboardData || window.clipboardData;

			if ( ! clip ) {
				return;
			}

			event.preventDefault();
			document.execCommand( 'insertText', false, clip.getData( 'text/plain' ) );
		} );

		window.addEventListener( 'beforeunload', function ( event ) {
			if ( changed() ) {
				event.preventDefault();
				event.returnValue = '';
			}
		} );

		isEditing = function () {
			return !! open;
		};

		editorMayClose = function () {
			if ( ! open ) {
				return true;
			}

			if ( ! changed() ) {
				finish();
				return true;
			}

			if ( window.confirm( ( strings && strings.confirmExit ) || 'সম্পাদনা সেভ করা হয়নি। বাতিল করে বেরিয়ে যাবেন?' ) ) {
				cancel();
				return true;
			}

			return false;
		};
	}() );
