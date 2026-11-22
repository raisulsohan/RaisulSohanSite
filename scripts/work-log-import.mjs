#!/usr/bin/env node
/**
 * Send a folder of finished work to the site's Work Log, every piece hidden
 * until it is shown from Dashboard › Work Log.
 *
 *   node scripts/work-log-import.mjs --root "D:\GitHub\04. Animation for video" --dry
 *   node scripts/work-log-import.mjs --root "…" --site https://raisulsohan.com --user <login> --password "<application password>"
 *
 * Options:
 *   --root <dir>       The folder to read (required).
 *   --site <url>       The site (required unless --dry).
 *   --user, --password An administrator and an Application Password
 *                      (Users › Profile › Application Passwords). The password
 *                      can also come from the RS_WL_PASSWORD environment variable.
 *   --dry              Only list what would be sent, with sizes; send nothing.
 *   --only <text>      Only pieces whose folder path contains this text.
 *   --show             Send pieces shown instead of hidden.
 *   --replace          Send again a piece the site already has (same folder).
 *   --chunk <MB>       Upload chunk size, 4 by default; halved on a refusal.
 *   --plan <file>      Write the plan as JSON to this file.
 *
 * What is a piece. Every folder that holds finished HTML pages is one piece:
 * with one page it is a single piece; with more, the main page (episode.html,
 * index.html, the page named like the folder, or else the first by name)
 * plays and the others are its clips, in name order. A page named for a 4:5
 * or vertical cut (4x5, 9x16, vertical) of the main page becomes its 4:5 cut.
 * Working folders are skipped (tools, qa, source, node_modules, rules, kits,
 * backups and the like), as are probes, starters, backups and partials.
 *
 * What is sent. The piece's folder with everything its pages need, as one
 * ZIP: when a page loads files from above its folder (../), the folder sent
 * is the one that holds them all. Recordings, sources and project files
 * (wav, mov, fbx, blend, psd, aep, prproj, zip) and the working folders stay
 * behind.
 *
 * The date of a piece is the day its main page was made: the earlier of the
 * file's creation and last change. The title is the page's <title>, or else
 * the folder's name.
 *
 * Needs Node 18 or later; nothing to install.
 */

import fs from 'node:fs';
import fsp from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import zlib from 'node:zlib';
import crypto from 'node:crypto';

/* ── options ── */
const args = process.argv.slice( 2 );
const opt = ( name, fallback ) => {
	const i = args.indexOf( '--' + name );
	return i === -1 ? fallback : args[ i + 1 ];
};
const flag = ( name ) => args.includes( '--' + name );

const ROOT = opt( 'root' );
const SITE = ( opt( 'site', '' ) || '' ).replace( /\/+$/, '' );
const USER = opt( 'user', '' );
const PASS = opt( 'password', process.env.RS_WL_PASSWORD || '' );
const DRY = flag( 'dry' );
const ONLY = opt( 'only', '' );
let CHUNK = Math.round( parseFloat( opt( 'chunk', '4' ) ) * 1024 * 1024 );

if ( ! ROOT || ( ! DRY && ( ! SITE || ! USER || ! PASS ) ) ) {
	console.error( 'Usage: node work-log-import.mjs --root <dir> [--dry] | --site <url> --user <login> --password <app password>' );
	process.exit( 2 );
}

/* Folders that hold work in progress, not work. */
const SKIP_DIR = /^(\.git|\.claude|\.vscode|\.impeccable|node_modules|tools|00_tools|qa|source|sources|feedback|research|script|scripts|social|export|exports|04_exports|out|build|backup|backups|archive|kit|lib|mlib|templates?|00\. rules|rules|asset-downloaders|website-page|scenes|sequences|plan|notes|data-cache|bgm|footage|frames|probe|probes|mock|film)$/i;
/* Pages that are not finished pieces. */
const SKIP_PAGE = /(^_|probe|starter|backup|\.bak|bak\d|-v\d+\.html$|make-|hook-options|compare_ab|thumbs?\.html$|^index\.template)/i;
/* Files that never go up: recordings, sources, project files. */
const SKIP_FILE = /\.(wav|aif|aiff|flac|mov|mkv|avi|fbx|blend|blend1|psd|ai|aep|aepx|prproj|zip|7z|rar|exr|tif|tiff|md|srt|log|py|bat|cmd|ps1|sh|php\d*|phtml|lnk)$|(^|\/)(\.env|\.git.*|package-lock\.json|desktop\.ini|thumbs\.db)$/i;
/* Folders that never go up inside a piece. */
const SKIP_INSIDE = /^(\.git|\.github|\.claude|\.vscode|\.impeccable|node_modules|tools|00_tools|qa|source|sources|feedback|research|export|exports|04_exports|backup|backups|archive|bgm|footage)$/i;
/* Top folders whose work was made for a client. */
const CLIENT = /suly|shabuj|real estate|gym|rupom|debangshu|barakat|ashik/i;
const CUT45 = /(4x5|4-5|9x16|vertical)/i;

/* ── finding the pieces ── */
function walk( dir, out ) {
	let list;
	try {
		list = fs.readdirSync( dir, { withFileTypes: true } );
	} catch ( e ) {
		return out;
	}
	const pages = [];
	for ( const d of list ) {
		const full = path.join( dir, d.name );
		if ( d.isDirectory() ) {
			if ( ! SKIP_DIR.test( d.name ) ) {
				walk( full, out );
			}
		} else if ( /\.html?$/i.test( d.name ) && ! SKIP_PAGE.test( d.name ) ) {
			const st = fs.statSync( full );
			if ( st.size >= 1500 ) {
				pages.push( { file: full, name: d.name, st } );
			}
		}
	}
	if ( pages.length ) {
		out.push( { dir, pages } );
	}
	return out;
}

function titleOf( file, fallback ) {
	try {
		const head = fs.readFileSync( file, 'utf8' ).slice( 0, 20000 );
		const m = head.match( /<title[^>]*>([^<]{2,120})<\/title>/i );
		if ( m ) {
			return m[ 1 ].replace( /\s+/g, ' ' ).trim();
		}
	} catch ( e ) {}
	return fallback;
}

const pretty = ( s ) => s.replace( /\.html?$/i, '' ).replace( /^\d+[._\s-]+/, '' ).replace( /[_-]+/g, ' ' ).replace( /\s+/g, ' ' ).trim();

function dayOf( st ) {
	const t = Math.min( st.birthtimeMs || st.mtimeMs, st.mtimeMs );
	const d = new Date( t );
	const pad = ( n ) => String( n ).padStart( 2, '0' );
	return d.getFullYear() + '-' + pad( d.getMonth() + 1 ) + '-' + pad( d.getDate() );
}

/* Files a page loads from above its own folder. */
function upRefs( file ) {
	let html = '';
	try {
		html = fs.readFileSync( file, 'utf8' );
	} catch ( e ) {
		return [];
	}
	const refs = [];
	const re = /(?:src|href)\s*=\s*["'](\.\.\/[^"'?#]+)/gi;
	let m;
	while ( ( m = re.exec( html ) ) ) {
		refs.push( path.resolve( path.dirname( file ), decodeURIComponent( m[ 1 ] ) ) );
	}
	return refs;
}

function common( a, b ) {
	const pa = a.split( path.sep );
	const pb = b.split( path.sep );
	let i = 0;
	while ( i < pa.length && i < pb.length && pa[ i ].toLowerCase() === pb[ i ].toLowerCase() ) {
		i++;
	}
	return pa.slice( 0, i ).join( path.sep );
}

function plan() {
	const root = path.resolve( ROOT );
	const groups = walk( root, [] );
	const pieces = [];

	for ( const g of groups ) {
		const rel = path.relative( root, g.dir ).split( path.sep ).join( '/' );
		if ( ONLY && ! rel.toLowerCase().includes( ONLY.toLowerCase() ) ) {
			continue;
		}
		const top = rel.split( '/' )[ 0 ] || path.basename( root );
		const folder = path.basename( g.dir );
		g.pages.sort( ( a, b ) => a.name.localeCompare( b.name, undefined, { numeric: true } ) );

		const names = g.pages.map( ( p ) => p.name.toLowerCase() );
		const plain = g.pages.filter( ( p ) => ! CUT45.test( p.name ) );
		let main =
			g.pages.find( ( p ) => /^episode\.html?$/i.test( p.name ) ) ||
			g.pages.find( ( p ) => /^index\.html?$/i.test( p.name ) ) ||
			g.pages.find( ( p ) => pretty( p.name ).toLowerCase() === pretty( folder ).toLowerCase() ) ||
			plain[ 0 ] ||
			g.pages[ 0 ];
		const stem = main.name.replace( /\.html?$/i, '' ).toLowerCase();
		const cut45 = g.pages.find( ( p ) => p !== main && CUT45.test( p.name ) && p.name.toLowerCase().replace( /[-_ ]?(4x5|4-5|9x16|vertical)/i, '' ).replace( /\.html?$/i, '' ) === stem );
		const clips = g.pages.filter( ( p ) => p !== main && p !== cut45 );

		/* The folder to send: the piece's own, or higher when its pages reach up. */
		let base = g.dir;
		const roots = new Set( [ g.dir ] );
		for ( const p of g.pages ) {
			for ( const r of upRefs( p.file ) ) {
				if ( r.toLowerCase().startsWith( root.toLowerCase() ) && fs.existsSync( r ) ) {
					base = common( base, path.dirname( r ) );
					roots.add( path.dirname( r ) );
				}
			}
		}
		const toBase = ( f ) => path.relative( base, f ).split( path.sep ).join( '/' );

		pieces.push( {
			origin: rel || folder,
			base,
			roots: [ ...roots ],
			title: titleOf( main.file, pretty( folder === path.basename( root ) ? main.name : folder ) ),
			date: dayOf( main.st ),
			kind: CLIENT.test( top ) ? 'client' : 'anim',
			entry: toBase( main.file ),
			entry45: cut45 ? toBase( cut45.file ) : '',
			clips: clips.map( ( c ) => ( { title: titleOf( c.file, pretty( c.name ) ), entry: toBase( c.file ) } ) ),
			// only for the plan
			pages: names.length,
		} );
	}

	/* Pieces that share a folder to send go up together; each keeps its pages. */
	return pieces;
}

/* ── the files of a piece ── */
function filesOf( base, roots, keep ) {
	const out = [];
	const seen = new Set();
	const go = ( dir ) => {
		let list;
		try {
			list = fs.readdirSync( dir, { withFileTypes: true } );
		} catch ( e ) {
			return;
		}
		for ( const d of list ) {
			const full = path.join( dir, d.name );
			const rel = path.relative( base, full ).split( path.sep ).join( '/' );
			if ( d.isDirectory() ) {
				if ( ! SKIP_INSIDE.test( d.name ) ) {
					go( full );
				}
			} else if ( d.isFile() && ! seen.has( rel ) && ( keep.has( rel ) || ! SKIP_FILE.test( rel ) ) ) {
				seen.add( rel );
				out.push( { full, rel, size: fs.statSync( full ).size } );
			}
		}
	};
	/* The piece's own folder, and each folder above it that its pages load from
	   (only those, not everything beside them). */
	roots.forEach( go );
	return out;
}

/* ── a small ZIP writer: deflate for text, store for media ── */
const CRC = ( () => {
	const t = new Int32Array( 256 );
	for ( let n = 0; n < 256; n++ ) {
		let c = n;
		for ( let k = 0; k < 8; k++ ) {
			c = c & 1 ? 0xedb88320 ^ ( c >>> 1 ) : c >>> 1;
		}
		t[ n ] = c;
	}
	return t;
} )();
function crc32( buf ) {
	if ( zlib.crc32 ) {
		return zlib.crc32( buf ) >>> 0;
	}
	let c = -1;
	for ( let i = 0; i < buf.length; i++ ) {
		c = CRC[ ( c ^ buf[ i ] ) & 0xff ] ^ ( c >>> 8 );
	}
	return ( c ^ -1 ) >>> 0;
}
const PACKED = /\.(mp4|webm|m4v|mp3|ogg|oga|m4a|aac|opus|jpe?g|png|webp|gif|avif|woff2?|glb|gz|br)$/i;

async function zip( files, out ) {
	const fh = await fsp.open( out, 'w' );
	const central = [];
	let offset = 0;
	const write = async ( b ) => {
		await fh.write( b );
		offset += b.length;
	};
	for ( const f of files ) {
		const data = await fsp.readFile( f.full );
		const crc = crc32( data );
		const store = PACKED.test( f.rel ) || data.length < 64;
		const body = store ? data : zlib.deflateRawSync( data, { level: 6 } );
		const name = Buffer.from( f.rel, 'utf8' );
		const h = Buffer.alloc( 30 );
		h.writeUInt32LE( 0x04034b50, 0 );
		h.writeUInt16LE( 20, 4 );
		h.writeUInt16LE( 0x0800, 6 ); // UTF-8 names
		h.writeUInt16LE( store ? 0 : 8, 8 );
		h.writeUInt32LE( 0, 10 );
		h.writeUInt32LE( crc, 14 );
		h.writeUInt32LE( body.length, 18 );
		h.writeUInt32LE( data.length, 22 );
		h.writeUInt16LE( name.length, 26 );
		h.writeUInt16LE( 0, 28 );
		central.push( { name, crc, csize: body.length, size: data.length, method: store ? 0 : 8, at: offset } );
		await write( h );
		await write( name );
		await write( body );
	}
	const start = offset;
	for ( const c of central ) {
		const h = Buffer.alloc( 46 );
		h.writeUInt32LE( 0x02014b50, 0 );
		h.writeUInt16LE( 20, 4 );
		h.writeUInt16LE( 20, 6 );
		h.writeUInt16LE( 0x0800, 8 );
		h.writeUInt16LE( c.method, 10 );
		h.writeUInt32LE( 0, 12 );
		h.writeUInt32LE( c.crc, 16 );
		h.writeUInt32LE( c.csize, 20 );
		h.writeUInt32LE( c.size, 24 );
		h.writeUInt16LE( c.name.length, 28 );
		h.writeUInt32LE( c.at, 42 );
		await write( h );
		await write( c.name );
	}
	const end = Buffer.alloc( 22 );
	end.writeUInt32LE( 0x06054b50, 0 );
	end.writeUInt16LE( central.length, 8 );
	end.writeUInt16LE( central.length, 10 );
	end.writeUInt32LE( offset - start, 12 );
	end.writeUInt32LE( start, 16 );
	await write( end );
	await fh.close();
	return offset;
}

/* ── the site ── */
const AUTH = 'Basic ' + Buffer.from( USER + ':' + PASS ).toString( 'base64' );
async function api( route, init ) {
	for ( let tries = 1; ; tries++ ) {
		try {
			const res = await fetch( SITE + '/wp-json/rs/v1/work-log' + route, {
				...init,
				headers: { Authorization: AUTH, ...( init && init.headers ) },
			} );
			const text = await res.text();
			let data;
			try {
				data = JSON.parse( text );
			} catch ( e ) {
				data = { message: text.slice( 0, 300 ) };
			}
			return { status: res.status, data };
		} catch ( e ) {
			if ( tries >= 5 ) {
				throw e;
			}
			await new Promise( ( r ) => setTimeout( r, 2000 * tries ) );
		}
	}
}

async function upload( file, size ) {
	const token = crypto.randomBytes( 16 ).toString( 'hex' );
	const fh = await fsp.open( file, 'r' );
	let offset = 0;
	try {
		while ( offset < size ) {
			const len = Math.min( CHUNK, size - offset );
			const buf = Buffer.alloc( len );
			await fh.read( buf, 0, len, offset );
			const fd = new FormData();
			fd.append( 'token', token );
			fd.append( 'offset', String( offset ) );
			fd.append( 'total', String( size ) );
			fd.append( 'chunk', new Blob( [ buf ] ), 'chunk' );
			const r = await api( '/upload', { method: 'POST', body: fd } );
			if ( r.status === 413 && CHUNK > 256 * 1024 ) {
				CHUNK = Math.floor( CHUNK / 2 );
				continue;
			}
			if ( r.status !== 200 && r.status !== 409 ) {
				throw new Error( 'upload ' + r.status + ': ' + ( r.data.message || '' ) );
			}
			offset = r.data.received;
			process.stdout.write( '\r   ' + Math.round( ( offset / size ) * 100 ) + '%   ' );
		}
	} finally {
		await fh.close();
	}
	process.stdout.write( '\r' );
	return token;
}

const mb = ( n ) => ( n / 1048576 ).toFixed( 1 ) + ' MB';

async function main() {
	const pieces = plan();
	let total = 0;
	for ( const p of pieces ) {
		const keep = new Set( [ p.entry, p.entry45, ...p.clips.map( ( c ) => c.entry ) ].filter( Boolean ) );
		p.files = filesOf( p.base, p.roots, keep );
		p.bytes = p.files.reduce( ( s, f ) => s + f.size, 0 );
		total += p.bytes;
	}

	console.log( pieces.length + ' pieces, ' + mb( total ) + ' before packing.\n' );
	for ( const p of pieces ) {
		console.log( `${ p.date }  ${ p.kind.padEnd( 6 ) } ${ mb( p.bytes ).padStart( 10 ) }  ${ p.origin }  →  ${ p.title }${ p.clips.length ? '  (+' + p.clips.length + ' clips)' : '' }` );
	}
	const planFile = opt( 'plan', '' );
	if ( planFile ) {
		fs.writeFileSync( planFile, JSON.stringify( pieces.map( ( { files, roots, ...p } ) => ( { ...p, files: files.map( ( f ) => f.rel ) } ) ), null, 1 ) );
	}
	if ( DRY ) {
		return;
	}

	const have = await api( '', {} );
	if ( have.status !== 200 ) {
		throw new Error( 'The site refused the login (' + have.status + '): ' + ( have.data.message || '' ) );
	}
	const known = new Set( have.data.map( ( r ) => r.origin ).filter( Boolean ) );
	const tmp = fs.mkdtempSync( path.join( os.tmpdir(), 'rs-work-' ) );
	const done = [];

	for ( const [ i, p ] of pieces.entries() ) {
		const label = `[${ i + 1 }/${ pieces.length }] ${ p.origin }`;
		if ( known.has( p.origin ) && ! flag( 'replace' ) ) {
			console.log( label + ': already on the site, skipped' );
			continue;
		}
		try {
			const out = path.join( tmp, 'piece.zip' );
			const size = await zip( p.files, out );
			console.log( label + ': ' + mb( size ) );
			const token = await upload( out, size );
			const r = await api( '/items', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify( {
					token,
					id: p.origin.split( '/' ).slice( -2 ).join( ' ' ),
					name: 'piece.zip',
					title: p.title,
					date: p.date,
					kind: p.kind,
					entry: p.entry,
					entry45: p.entry45,
					clips: p.clips,
					origin: p.origin,
					hidden: ! flag( 'show' ),
					replace: flag( 'replace' ),
				} ),
			} );
			fs.rmSync( out, { force: true } );
			if ( r.status !== 200 ) {
				throw new Error( r.status + ': ' + ( r.data.message || '' ) );
			}
			console.log( '   added as ' + r.data.id );
			done.push( { origin: p.origin, id: r.data.id } );
		} catch ( e ) {
			console.log( '   FAILED: ' + e.message );
			done.push( { origin: p.origin, error: e.message } );
		}
	}
	fs.rmSync( tmp, { recursive: true, force: true } );
	fs.writeFileSync( 'work-log-import.result.json', JSON.stringify( done, null, 1 ) );
	const bad = done.filter( ( d ) => d.error ).length;
	console.log( `\nDone: ${ done.length - bad } sent, ${ bad } failed. The list is in work-log-import.result.json.` );
}

main().catch( ( e ) => {
	console.error( e.message );
	process.exit( 1 );
} );
