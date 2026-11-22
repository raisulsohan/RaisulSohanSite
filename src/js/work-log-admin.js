/**
 * Dashboard › Work Log (inc/24-work-log-admin.php): add a piece by sending its
 * file in chunks, hide, show, edit and delete pieces, one at a time or the
 * ones ticked, and narrow the list by a search or a filter.
 *
 * The routes and the chunked upload are work-log-api.js's; the settings come
 * in window.RS_WL.
 */
( function () {
	'use strict';

	const C = window.RS_WL;
	if ( ! C || ! window.RS_WL_API ) {
		return;
	}
	const { api, send } = window.RS_WL_API;
	const T = C.t;
	const $ = ( s, r ) => ( r || document ).querySelector( s );
	const $$ = ( s, r ) => Array.from( ( r || document ).querySelectorAll( s ) );

	/* ── adding a piece ── */
	const form = $( '#rsWlAdd' );
	if ( form ) {
		const prog = $( '.rs-wl-progress progress', form );
		const note = $( '.rs-wl-progress span', form );
		window.RS_WL_API.youtube( form );
		form.addEventListener( 'submit', async ( e ) => {
			e.preventDefault();
			const file = form.file.files[ 0 ];
			const link = form.elements.youtube.value.trim();
			if ( ! file && ! link ) {
				note.textContent = T.pickFile;
				return;
			}
			const btn = $( 'button[type=submit]', form );
			btn.disabled = true;
			prog.hidden = ! file;
			try {
				if ( link ) {
					note.textContent = T.asking;
					await api( '/items', { method: 'POST', json: window.RS_WL_API.fromYoutube( form ) } );
					note.textContent = T.added;
					location.reload();
					return;
				}
				const fileToken = await send( file, ( f ) => {
					prog.value = Math.round( f * 100 );
					note.textContent = T.uploading + ' ' + Math.round( f * 100 ) + '%';
				} );
				const poster = form.poster.files[ 0 ];
				const posterToken = poster ? await send( poster ) : '';
				note.textContent = T.unpacking;
				await api( '/items', {
					method: 'POST',
					json: {
						token: fileToken,
						name: file.name,
						poster_token: posterToken,
						poster_name: poster ? poster.name : '',
						title: form.elements.title.value,
						title_bn: form.elements.title_bn.value,
						client: form.elements.client.checked,
						date: form.date.value,
						kind: form.kind.value,
						desc_bn: form.desc_bn.value,
						desc_en: form.desc_en.value,
						entry: form.entry.value.trim(),
						entry45: form.entry45.value.trim(),
						hidden: form.hidden.checked,
					},
				} );
				note.textContent = T.added;
				location.reload();
			} catch ( err ) {
				note.textContent = T.error + ' ' + err.message;
				btn.disabled = false;
			}
		} );
	}

	/* ── the list ── */
	const rows = () => $$( '.rs-wl-table tbody tr[data-id]' );

	function paint( tr, row ) {
		tr.dataset.hidden = row.hidden ? '1' : '0';
		tr.dataset.row = JSON.stringify( row );
		tr.dataset.kind = row.kind;
		const st = $( '.rs-wl-status', tr );
		st.textContent = row.hidden ? T.hidden : T.shown;
		st.classList.toggle( 'is-hidden', row.hidden );
		$( '[data-toggle]', tr ).textContent = row.hidden ? T.show : T.hide;
		$( '.rs-wl-title', tr ).textContent = ( document.documentElement.lang.startsWith( 'bn' ) && row.title_bn ) || row.title;
		$( '.rs-wl-date', tr ).textContent = row.date;
		const opt = $( '#rsWlEdit' ).content.querySelector( 'option[value="' + row.kind + '"]' );
		if ( opt ) {
			$( '.rs-wl-kind', tr ).textContent = opt.textContent;
		}
	}

	function filter() {
		const q = $( '#rsWlSearch' ).value.trim().toLowerCase();
		const f = $( '#rsWlFilter' ).value;
		rows().forEach( ( tr ) => {
			let ok = ! q || tr.dataset.find.includes( q );
			if ( f === 'shown' ) {
				ok = ok && tr.dataset.hidden === '0';
			} else if ( f === 'hidden' ) {
				ok = ok && tr.dataset.hidden === '1';
			} else if ( f.startsWith( 'kind:' ) ) {
				ok = ok && tr.dataset.kind === f.slice( 5 );
			}
			tr.hidden = ! ok;
			const ed = tr.nextElementSibling;
			if ( ed && ed.classList.contains( 'rs-wl-editrow' ) ) {
				ed.hidden = ! ok;
			}
		} );
		count();
	}

	function ticked() {
		return rows()
			.filter( ( tr ) => ! tr.hidden && $( 'input[type=checkbox]', tr ).checked )
			.map( ( tr ) => tr.dataset.id );
	}

	function count() {
		const n = ticked().length;
		$( '.rs-wl-sel' ).textContent = n ? '✓ ' + n : '';
	}

	$( '#rsWlSearch' ).addEventListener( 'input', filter );
	$( '#rsWlFilter' ).addEventListener( 'change', filter );
	$( '#rsWlAll' ).addEventListener( 'change', ( e ) => {
		rows().forEach( ( tr ) => {
			if ( ! tr.hidden ) {
				$( 'input[type=checkbox]', tr ).checked = e.target.checked;
			}
		} );
		count();
	} );
	$( '.rs-wl-table tbody' ).addEventListener( 'change', count );

	$$( '[data-bulk]' ).forEach( ( b ) =>
		b.addEventListener( 'click', async () => {
			const ids = ticked();
			const action = b.dataset.bulk;
			if ( ! ids.length || ( action === 'delete' && ! window.confirm( T.confirm ) ) ) {
				return;
			}
			b.disabled = true;
			try {
				await api( '/bulk', { method: 'POST', json: { ids, action } } );
				location.reload();
			} catch ( err ) {
				b.disabled = false;
				window.alert( T.error + ' ' + err.message );
			}
		} )
	);

	$( '.rs-wl-table tbody' ).addEventListener( 'click', async ( e ) => {
		const tr = e.target.closest( 'tr[data-id]' );
		const toggle = e.target.closest( '[data-toggle]' );
		const edit = e.target.closest( '[data-edit]' );
		if ( toggle && tr ) {
			toggle.disabled = true;
			const row = JSON.parse( tr.dataset.row );
			try {
				const r = await api( '/items/' + tr.dataset.id, { method: 'POST', json: { hidden: ! row.hidden } } );
				paint( tr, r.data );
			} catch ( err ) {
				window.alert( T.error + ' ' + err.message );
			}
			toggle.disabled = false;
		}
		if ( edit && tr ) {
			const open = tr.nextElementSibling;
			if ( open && open.classList.contains( 'rs-wl-editrow' ) ) {
				open.remove();
				return;
			}
			const row = JSON.parse( tr.dataset.row );
			const ed = $( '#rsWlEdit' ).content.firstElementChild.cloneNode( true );
			const f = $( 'form', ed );
			const F = [ 'title', 'title_bn', 'date', 'kind', 'desc_bn', 'desc_en' ];
			F.forEach( ( k ) => {
				f.elements[ k ].value = row[ k ] || '';
			} );
			f.elements.client.checked = !! row.client;
			$( '[data-cancel]', ed ).addEventListener( 'click', () => ed.remove() );
			f.addEventListener( 'submit', async ( ev ) => {
				ev.preventDefault();
				try {
					const r = await api( '/items/' + tr.dataset.id, {
						method: 'POST',
						json: F.reduce( ( o, k ) => Object.assign( o, { [ k ]: f.elements[ k ].value } ), { client: f.elements.client.checked } ),
					} );
					paint( tr, r.data );
					ed.remove();
				} catch ( err ) {
					window.alert( T.error + ' ' + err.message );
				}
			} );
			tr.after( ed );
		}
	} );
}() );
