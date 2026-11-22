/**
 * The Work Log's controls on the page itself (inc/25-work-log-manage.php), for
 * someone signed in who may manage it: Hide or Show, Edit and Delete under
 * each card, in the pop-up player and in the editor view, and a bar to add a
 * piece or narrow the list.
 *
 * The page's own script hands over its pieces and its player in
 * window.rsWorkLog. Hiding, showing and deleting change the page in place, so
 * the reader keeps their spot; adding and editing load it again, because the
 * piece's place on the timeline may change.
 */
( function () {
	'use strict';

	const C = window.RS_WL;
	const W = window.rsWorkLog;
	if ( ! C || ! W || ! window.RS_WL_API ) {
		return;
	}
	const { api, send } = window.RS_WL_API;
	const T = C.t;
	const $ = ( s, r ) => ( r || document ).querySelector( s );
	const $$ = ( s, r ) => Array.from( ( r || document ).querySelectorAll( s ) );
	const own = ( w ) => w.source === 'upload';
	const size = ( n ) => ( n ? W.digits( n >= 1073741824 ? ( n / 1073741824 ).toFixed( 2 ) + ' GB' : ( n / 1048576 ).toFixed( 1 ) + ' MB' ) : '' );

	/* ── the message at the foot of the screen, with a way back ── */
	const toast = $( '#admToast' );
	let toastTimer = 0;
	function say( text, undo ) {
		clearTimeout( toastTimer );
		toast.textContent = text;
		if ( undo ) {
			const b = document.createElement( 'button' );
			b.type = 'button';
			b.textContent = T.undo;
			b.addEventListener( 'click', () => {
				toast.hidden = true;
				undo();
			} );
			toast.append( b );
		}
		toast.hidden = false;
		toastTimer = setTimeout( () => {
			toast.hidden = true;
		}, undo ? 8000 : 4000 );
	}
	const fail = ( err ) => say( T.error + ' ' + err.message );

	/* ── a piece's row of buttons ── */
	function row( w ) {
		const el = document.createElement( 'div' );
		el.className = 'adm-row';
		el.dataset.adm = w.id;
		const add = ( act, cls ) => {
			const b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'adm-btn adm-btn--s' + ( cls ? ' ' + cls : '' );
			b.dataset.act = act;
			el.append( b );
			return b;
		};
		const st = document.createElement( 'span' );
		st.className = 'adm-st';
		el.append( st );
		add( 'toggle' );
		if ( own( w ) ) {
			add( 'edit' ).textContent = T.edit;
			add( 'delete', 'adm-btn--bad' ).textContent = T.del;
		} else {
			const n = document.createElement( 'small' );
			n.textContent = T.theme;
			el.append( n );
		}
		paintRow( el, w );
		return el;
	}

	function paintRow( el, w ) {
		const st = $( '.adm-st', el );
		st.textContent = w.hidden ? T.hidden : T.shown;
		st.classList.toggle( 'is-off', w.hidden );
		const b = $( '[data-act="toggle"]', el );
		b.textContent = w.hidden ? T.show : T.hide;
		b.setAttribute( 'aria-label', b.textContent + ': ' + w.title );
	}

	/** Every place a piece stands in the list: the cell its card is in. */
	const places = ( id ) => $$( '#list .cell[data-id="' + id + '"]' );

	function paint( w ) {
		places( w.id ).forEach( ( el ) => el.classList.toggle( 'is-off', w.hidden ) );
		$$( '.adm-row[data-adm="' + w.id + '"]' ).forEach( ( el ) => paintRow( el, w ) );
	}

	/* A card is a link, so its buttons stand under it, in the same cell. */
	W.works.forEach( ( w ) => {
		places( w.id ).forEach( ( el ) => el.append( row( w ) ) );
		paint( w );
	} );

	/* ── the counts, and which pieces are listed ── */
	let vis = 'all';
	W.setGate( ( w ) => vis === 'all' || ( vis === 'hidden' ) === !! w.hidden );

	function counts() {
		const off = W.works.filter( ( w ) => w.hidden ).length;
		const n = { all: W.works.length, hidden: off, shown: W.works.length - off };
		Object.keys( n ).forEach( ( k ) => {
			$( '#admBar [data-n="' + k + '"]' ).textContent = W.digits( n[ k ] );
		} );
	}

	$$( '#admBar [data-vis]' ).forEach( ( b ) =>
		b.addEventListener( 'click', () => {
			vis = b.dataset.vis;
			$$( '#admBar [data-vis]' ).forEach( ( x ) => x.setAttribute( 'aria-pressed', x === b ? 'true' : 'false' ) );
			W.refresh();
		} )
	);

	/* ── hide and show ── */
	async function setHidden( w, hidden, quiet ) {
		const r = await api( '/items/' + w.id, { method: 'POST', json: { hidden } } );
		w.hidden = !! r.data.hidden;
		paint( w );
		counts();
		W.refresh();
		if ( ! quiet ) {
			say( ( w.hidden ? T.didHide : T.didShow ) + ' ' + w.title, () => setHidden( w, ! hidden, true ).catch( fail ) );
		}
	}

	/* ── dialogs: one message line, and no second press while one is under way ── */
	async function busy( dlg, work ) {
		const all = $$( 'button', dlg );
		all.forEach( ( b ) => ( b.disabled = true ) );
		try {
			await work();
			dlg.close();
		} catch ( err ) {
			$( '.adm-msg', dlg ).textContent = T.error + ' ' + err.message;
		}
		all.forEach( ( b ) => ( b.disabled = false ) );
	}

	/* ── delete ── */
	const delDlg = $( '#admDelDlg' );
	let delOf = null;

	function askDelete( w ) {
		delOf = w;
		$( '#admDelName' ).textContent = w.title;
		$( '#admDelInfo' ).textContent = [ w.day, w.clips.length ? W.digits( w.clips.length ) + T.clips : '', size( w.bytes ) ].filter( Boolean ).join( ' · ' );
		$( '.adm-msg', delDlg ).textContent = '';
		$( '#admDelHide' ).hidden = w.hidden;
		delDlg.showModal();
		$( '[data-close]', delDlg ).focus();
	}

	/** Take a deleted piece off the page, with its week and month if they are left empty. */
	function take( w ) {
		places( w.id ).forEach( ( el ) => {
			const around = [ el.closest( '.tl-week' ), el.closest( '.tl-month' ) ];
			const mates = $$( '#list .cell[data-date="' + w.date + '"]' ).filter( ( x ) => x !== el );
			el.remove();
			if ( mates.length ) {
				mates[ 0 ].id = 'd-' + w.date;
			} else {
				const dot = $( '.cal a[data-d="' + w.date + '"]' );
				if ( dot ) {
					dot.classList.remove( 'on' );
					[ 'href', 'title', 'aria-label' ].forEach( ( a ) => dot.removeAttribute( a ) );
				}
			}
			around.forEach( ( box ) => {
				if ( box && ! $( '.cell', box ) ) {
					box.remove();
				}
			} );
		} );
		W.remove( w );
		counts();
		W.refresh();
	}

	$( '#admDelYes' ).addEventListener( 'click', () => {
		const w = delOf;
		busy( delDlg, async () => {
			await api( '/items/' + w.id, { method: 'DELETE' } );
			take( w );
			say( T.didDel + ' ' + w.title );
		} );
	} );
	$( '#admDelHide' ).addEventListener( 'click', () => busy( delDlg, () => setHidden( delOf, true ) ) );

	/* ── edit ── */
	const editDlg = $( '#admEditDlg' );
	const editForm = $( '#admEditForm' );
	const FIELDS = [ 'title', 'title_bn', 'date', 'kind', 'desc_bn', 'desc_en' ];
	let editOf = null;

	function askEdit( w ) {
		editOf = w;
		FIELDS.forEach( ( k ) => {
			editForm.elements[ k ].value = ( k === 'title' ? w.name : w[ k ] ) || '';
		} );
		editForm.elements.client.checked = !! w.client;
		$( '.adm-msg', editDlg ).textContent = '';
		editDlg.showModal();
	}

	editForm.addEventListener( 'submit', ( e ) => {
		e.preventDefault();
		busy( editDlg, async () => {
			const json = { client: editForm.elements.client.checked };
			FIELDS.forEach( ( k ) => {
				json[ k ] = editForm.elements[ k ].value;
			} );
			await api( '/items/' + editOf.id, { method: 'POST', json } );
			reload( json.date );
		} );
	} );

	/** Load the page again at a day. */
	function reload( date ) {
		history.replaceState( null, '', location.pathname + location.search + '#d-' + date );
		location.reload();
	}

	/* ── add ── */
	const addDlg = $( '#admAddDlg' );
	const addForm = $( '#admAddForm' );
	const addMsg = $( '.adm-msg', addDlg );
	let sending = false;

	$( '#admAdd' ).addEventListener( 'click', () => {
		if ( ! sending ) {
			addMsg.textContent = '';
		}
		addDlg.showModal();
	} );

	window.RS_WL_API.youtube( addForm );
	const isVideo = ( f ) => /\.(mp4|webm|m4v|mov)$/i.test( f.name );

	/* The title from the file's name, until one is written. */
	let guessed = '';
	addForm.elements.file.addEventListener( 'change', () => {
		const f = addForm.elements.file.files[ 0 ];
		const t = addForm.elements.title;
		if ( f && ( ! t.value || t.value === guessed ) ) {
			guessed = f.name.replace( /\.[^.]+$/, '' ).replace( /[_-]+/g, ' ' ).trim();
			t.value = guessed;
		}
	} );

	/** A frame a quarter of the way into a video, as a JPEG; null when the browser cannot read it. */
	function frameOf( file ) {
		return new Promise( ( done ) => {
			const v = document.createElement( 'video' );
			const url = URL.createObjectURL( file );
			let over = false;
			const end = ( blob ) => {
				if ( ! over ) {
					over = true;
					URL.revokeObjectURL( url );
					done( blob );
				}
			};
			setTimeout( () => end( null ), 15000 );
			v.muted = true;
			v.preload = 'auto';
			v.addEventListener( 'error', () => end( null ) );
			v.addEventListener( 'loadeddata', () => {
				v.currentTime = Math.min( 30, ( v.duration || 0 ) * 0.25 );
			}, { once: true } );
			v.addEventListener( 'seeked', () => {
				const w = Math.min( 1280, v.videoWidth );
				if ( ! w ) {
					end( null );
					return;
				}
				const c = document.createElement( 'canvas' );
				c.width = w;
				c.height = Math.round( ( w * v.videoHeight ) / v.videoWidth );
				try {
					c.getContext( '2d' ).drawImage( v, 0, 0, c.width, c.height );
					c.toBlob( end, 'image/jpeg', 0.85 );
				} catch ( err ) {
					end( null );
				}
			} );
			v.src = url;
		} );
	}

	addForm.addEventListener( 'submit', async ( e ) => {
		e.preventDefault();
		const f = addForm.elements;
		const file = f.file.files[ 0 ];
		const link = f.youtube.value.trim();
		if ( ( ! file && ! link ) || sending ) {
			return;
		}
		const bar = $( 'progress', addForm );
		const buttons = $$( 'button', addForm );
		sending = true;
		buttons.forEach( ( b ) => ( b.disabled = true ) );
		bar.hidden = !! link;
		bar.value = 0;
		try {
			if ( link ) {
				addMsg.textContent = T.asking;
				const got = await api( '/items', { method: 'POST', json: window.RS_WL_API.fromYoutube( addForm ) } );
				addMsg.textContent = T.added;
				sending = false;
				reload( got.data.date );
				return;
			}
			let poster = f.poster.files[ 0 ] || null;
			if ( ! poster && isVideo( file ) ) {
				poster = await frameOf( file );
			}
			const token = await send( file, ( k ) => {
				bar.value = Math.round( k * 100 );
				addMsg.textContent = T.uploading + ' ' + W.digits( Math.round( k * 100 ) ) + '%';
			} );
			const posterToken = poster ? await send( poster ) : '';
			addMsg.textContent = T.unpacking;
			const r = await api( '/items', {
				method: 'POST',
				json: {
					token,
					name: file.name,
					poster_token: posterToken,
					poster_name: poster ? poster.name || 'poster.jpg' : '',
					title: f.title.value,
					title_bn: f.title_bn.value,
					client: f.client.checked,
					date: f.date.value,
					kind: f.kind.value,
					desc_bn: f.desc_bn.value,
					desc_en: f.desc_en.value,
					hidden: f.hidden.checked,
				},
			} );
			addMsg.textContent = T.added;
			sending = false;
			reload( r.data.date || f.date.value );
		} catch ( err ) {
			sending = false;
			bar.hidden = true;
			addMsg.textContent = T.error + ' ' + err.message;
			buttons.forEach( ( b ) => ( b.disabled = false ) );
		}
	} );
	/* An upload must not be lost half way to a closed tab or a stray Escape. */
	addEventListener( 'beforeunload', ( e ) => {
		if ( sending ) {
			e.preventDefault();
		}
	} );
	addDlg.addEventListener( 'cancel', ( e ) => {
		if ( sending ) {
			e.preventDefault();
		}
	} );

	$$( '.adm-dlg [data-close]' ).forEach( ( b ) => b.addEventListener( 'click', () => b.closest( 'dialog' ).close() ) );

	/* ── every row's buttons, wherever the row is ── */
	document.addEventListener( 'click', async ( e ) => {
		const b = e.target.closest( '.adm-row [data-act]' );
		const w = b && W.byId( b.closest( '.adm-row' ).dataset.adm );
		if ( ! w ) {
			return;
		}
		if ( b.dataset.act === 'edit' ) {
			askEdit( w );
		} else if ( b.dataset.act === 'delete' ) {
			askDelete( w );
		} else {
			b.disabled = true;
			await setHidden( w, ! w.hidden ).catch( fail );
			b.disabled = false;
		}
	} );

	/* ── the player and the editor view carry the row of the piece they show ── */
	function slot( host, w ) {
		$$( '.adm-row', host ).forEach( ( el ) => el.remove() );
		if ( w ) {
			host.append( row( w ) );
		}
	}
	W.onLoad = ( w ) => slot( $( '#modal .m-top' ), w );
	W.onSelect = ( w ) => slot( $( '.insp' ), w );
	if ( W.selected() ) {
		W.onSelect( W.selected() );
	}
	if ( W.playing() ) {
		W.onLoad( W.playing() );
		W.fit();
	}

	/* A dialog opens over the player, so the player's keys wait while one is up. */
	addEventListener(
		'keydown',
		( e ) => {
			if ( $( 'dialog[open]' ) && ( e.key === '[' || e.key === ']' || e.key === 'Escape' ) ) {
				e.stopImmediatePropagation();
			}
		},
		true
	);
}() );
