/**
 * The Work Log's routes (rs/v1/work-log) from the browser: calls with the REST
 * nonce, and a file sent in chunks. Shared by Dashboard › Work Log
 * (work-log-admin.js) and the controls on the page itself (work-log-manage.js).
 * The settings come in window.RS_WL; the functions go out in window.RS_WL_API.
 */
( function () {
	'use strict';

	const C = window.RS_WL;
	if ( ! C ) {
		return;
	}

	async function api( path, opts ) {
		const o = Object.assign( { method: 'GET', headers: {} }, opts || {} );
		o.headers[ 'X-WP-Nonce' ] = C.nonce;
		if ( o.json !== undefined ) {
			o.headers[ 'Content-Type' ] = 'application/json';
			o.body = JSON.stringify( o.json );
			delete o.json;
		}
		const res = await fetch( C.api + path, o );
		const data = await res.json().catch( () => ( {} ) );
		if ( ! res.ok && res.status !== 409 ) {
			throw new Error( data.message || res.status + ' ' + res.statusText );
		}
		return { status: res.status, data };
	}

	function token() {
		const a = new Uint8Array( 16 );
		crypto.getRandomValues( a );
		return Array.from( a, ( b ) => b.toString( 16 ).padStart( 2, '0' ) ).join( '' );
	}

	/* Send a file in chunks; a refused chunk says where to carry on from. */
	async function send( file, onStep ) {
		const id = token();
		let offset = 0;
		while ( offset < file.size ) {
			const fd = new FormData();
			fd.append( 'token', id );
			fd.append( 'offset', String( offset ) );
			fd.append( 'total', String( file.size ) );
			fd.append( 'chunk', file.slice( offset, offset + C.chunk ), 'chunk' );
			const r = await api( '/upload', { method: 'POST', body: fd } );
			offset = r.data.received;
			if ( onStep ) {
				onStep( offset / file.size );
			}
		}
		return id;
	}

	window.RS_WL_API = { api, send };
}() );
