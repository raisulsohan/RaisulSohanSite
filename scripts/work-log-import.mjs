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
 *   --exclude <regex>  Leave out pieces whose folder path matches.
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
 * A folder of pages inside a piece's folder goes up with that piece, its pages
 * as clips, not as a second copy. Working folders, copies, raw sources and
 * map data are not searched (tools, qa, source, build, out, raw, work,
 * render, old, backups, *-kept, _not-needed, After Effects, naturalearth,
 * esri/nasa/osm tiles and the like), nor are media folders (audio, frames,
 * cache, footage, assets, data), probes, starters, backups and partials.
 *
 * What is sent. The piece's folder, frames, caches, footage and sound
 * included, less those working folders and less sources, project files and
 * documents (mov, fbx, blend, psd, aep, prproj, zip, docx, txt, npy), plus
 * every file its pages and scripts name, wherever it sits under the root and
 * whatever its kind. A path the code builds (`frames/${i}.jpg`) sends the
 * whole folder before the `${`, up to 1.5 GB. A path from the site root
 * ("/media/clip.mp4") is pointed at the same file inside the piece. A piece
 * over 3 GB is listed as TOO BIG and not sent.
 *
 * The date of a piece is the day its earliest page was made: the earlier of
 * each file's creation and last change. The title is the main page's <title>,
 * or else the folder's name; a title several pieces share is prefixed with
 * the project's folder.
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

const EXCLUDE = opt( 'exclude', '' ) ? new RegExp( opt( 'exclude', '' ), 'i' ) : null;
const MAX_PIECE = 3 * 1024 * 1024 * 1024; // the site's limit for one piece (RS_WORK_LOG_MAX_BYTES)

/* Folders that hold work in progress, copies, raw sources or map data, not
   what a piece plays: never searched for pieces, and not sent inside a piece
   unless a page names a file in them. */
const JUNK = '\\.git|\\.github|\\.claude|\\.vscode|\\.impeccable|node_modules|tools|00_tools|qa|source|sources|feedback|research|export|exports|04_exports|out|build|raw|_cand.*|after effects|backup|backups|_?backup[_-].*|.*[_-]backup|archive-.*-backup.*|old|_old|v\\d+-kept|_not-needed.*|not-needed.*|work|render|renders|natural-?earth|natural_earth|esri.*|nasa.*|osm.*|scripts-.*|__pycache__';
/* Folders that are never searched for pieces (they may still go up inside one). */
const SKIP_DIR = new RegExp( '^(' + JUNK + '|script|scripts|social|kit|lib|mlib|00\\. rules|rules|asset-downloaders|website-page|sequences|plan|notes|probe|probes|mock|src|listen|frames|cache|data-cache|bgm|footage|audio|media|assets|data)$', 'i' );
const SKIP_INSIDE = new RegExp( '^(' + JUNK + ')$', 'i' );
/* Pages that are not finished pieces. */
const SKIP_PAGE = /(^_|probe|starter|backup|\.bak|bak\d|-v\d+\.html$|make-|hook-options|compare_ab|thumbs?\.html$|^index\.template)/i;
/* Files that do not go up unless a page loads them: recordings, sources,
   project files, documents. */
const SKIP_FILE = /\.(aif|aiff|flac|mov|mkv|avi|fbx|blend|blend1|psd|ai|aep|aepx|prproj|zip|7z|rar|exr|tif|tiff|md|srt|log|txt|docx?|xlsx?|pptx?|pth|ckpt|safetensors|onnx|npy|npz|pyc|lnk)$|(^|\/)(\.env.*|\.git.*|package-lock\.json|desktop\.ini|thumbs\.db)$/i;
/* Files that never go up, even when a page names them (the site refuses them too). */
const NEVER = /\.(py|pyc|bat|cmd|ps1|sh|php\d*|phtml|phar|cgi|pl|asp|aspx|jsp|exe)$|(^|\/)(\.ht[^/]*|\.user\.ini|\.env[^/]*)$/i;
/* Top folders whose work was made for a client. */
const CLIENT = /suly|shabuj|real estate|gym|rupom|debangshu|barakat|ashik/i;
const CUT45 = /(4x5|4-5|9x16|vertical)/i;
/* Text files read for the files they load. */
const TEXT = /\.(html?|m?js|css|json)$/i;
const MEDIA_REF = /\.(wav|mp3|ogg|oga|m4a|aac|opus|flac|mp4|webm|m4v|mov|glb|gltf|bin|hdr|exr|ktx2|json|geojson|topojson|csv|png|jpe?g|webp|avif|gif|svg|woff2?|ttf|otf|m?js|css|html?)$/i;

const posix = ( p ) => p.split( path.sep ).join( '/' );
const under = ( child, parent ) => {
	const r = path.relative( parent, child );
	return r === '' || ( ! r.startsWith( '..' ) && ! path.isAbsolute( r ) );
};

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
			if ( st.size >= 600 ) {
				pages.push( { file: full, name: d.name, st } );
			}
		}
	}
	if ( pages.length ) {
		out.push( { dir, pages, nested: [] } );
	}
	return out;
}

const ENT = { amp: '&', lt: '<', gt: '>', quot: '"', apos: "'", nbsp: ' ', raquo: '»', laquo: '«', mdash: '—', ndash: '–', middot: '·', hellip: '…', rsquo: '’', lsquo: '‘', rdquo: '”', ldquo: '“', copy: '©' };
function decode( s ) {
	for ( let i = 0; i < 3 && /&[#a-z0-9]+;/i.test( s ); i++ ) {
		s = s.replace( /&(#x[0-9a-f]+|#\d+|[a-z]+);/gi, ( m, e ) => {
			if ( e[ 0 ] === '#' ) {
				const n = e[ 1 ].toLowerCase() === 'x' ? parseInt( e.slice( 2 ), 16 ) : parseInt( e.slice( 1 ), 10 );
				return Number.isFinite( n ) ? String.fromCodePoint( n ) : m;
			}
			return ENT[ e.toLowerCase() ] ?? m;
		} );
	}
	return s;
}

function titleOf( file, fallback ) {
	try {
		const head = fs.readFileSync( file, 'utf8' ).slice( 0, 20000 );
		const m = head.match( /<title[^>]*>([^<]{2,160})<\/title>/i );
		if ( m ) {
			return decode( m[ 1 ] ).replace( /\s+/g, ' ' ).trim().slice( 0, 120 );
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

function common( a, b ) {
	const pa = a.split( path.sep );
	const pb = b.split( path.sep );
	let i = 0;
	while ( i < pa.length && i < pb.length && pa[ i ].toLowerCase() === pb[ i ].toLowerCase() ) {
		i++;
	}
	return pa.slice( 0, i ).join( path.sep );
}

/* Does a piece sent from `dir` carry the folder `sub` inside it? */
function carries( dir, sub ) {
	if ( ! under( sub, dir ) || sub === dir ) {
		return false;
	}
	return ! path.relative( dir, sub ).split( path.sep ).some( ( s ) => SKIP_INSIDE.test( s ) );
}

function plan() {
	const root = path.resolve( ROOT );
	let groups = walk( root, [] );

	/* A folder of pages inside another piece's folder travels with that piece:
	   its pages become that piece's clips instead of a second copy. */
	groups.sort( ( a, b ) => a.dir.length - b.dir.length );
	const kept = [];
	for ( const g of groups ) {
		const host = kept.filter( ( k ) => carries( k.dir, g.dir ) ).pop();
		if ( host ) {
			host.nested.push( ...g.pages, ...g.nested );
		} else {
			kept.push( g );
		}
	}
	groups = kept;

	const pieces = [];
	for ( const g of groups ) {
		const rel = posix( path.relative( root, g.dir ) );
		if ( ONLY && ! rel.toLowerCase().includes( ONLY.toLowerCase() ) ) {
			continue;
		}
		if ( EXCLUDE && EXCLUDE.test( rel ) ) {
			continue;
		}
		const top = rel.split( '/' )[ 0 ] || path.basename( root );
		const folder = path.basename( g.dir );
		const byName = ( a, b ) => a.file.localeCompare( b.file, undefined, { numeric: true } );
		g.pages.sort( byName );
		g.nested.sort( byName );

		const plain = g.pages.filter( ( p ) => ! CUT45.test( p.name ) );
		const main =
			g.pages.find( ( p ) => /^episode\.html?$/i.test( p.name ) ) ||
			g.pages.find( ( p ) => /^index\.html?$/i.test( p.name ) ) ||
			g.pages.find( ( p ) => pretty( p.name ).toLowerCase() === pretty( folder ).toLowerCase() ) ||
			plain[ 0 ] ||
			g.pages[ 0 ];
		const stem = main.name.replace( /\.html?$/i, '' ).toLowerCase();
		const cut45 = g.pages.find( ( p ) => p !== main && CUT45.test( p.name ) && p.name.toLowerCase().replace( /[-_ ]?(4x5|4-5|9x16|vertical)/i, '' ).replace( /\.html?$/i, '' ) === stem );
		const clips = [ ...g.pages, ...g.nested ].filter( ( p ) => p !== main && p !== cut45 );
		const all = [ ...g.pages, ...g.nested ];
		const date = all.map( ( p ) => dayOf( p.st ) ).sort()[ 0 ];

		pieces.push( {
			origin: rel || folder,
			dir: g.dir,
			title: titleOf( main.file, pretty( folder === path.basename( root ) ? main.name : folder ) ),
			project: pretty( rel.split( '/' )[ 1 ] || folder ),
			date,
			kind: CLIENT.test( top ) ? 'client' : 'anim',
			mainFile: main.file,
			cutFile: cut45 ? cut45.file : '',
			clipFiles: clips.map( ( c ) => ( { title: titleOf( c.file, pretty( c.name ) ), file: c.file } ) ),
		} );
	}

	/* The same title on several pieces: say which project each is from. */
	const seen = {};
	pieces.forEach( ( p ) => {
		seen[ p.title.toLowerCase() ] = ( seen[ p.title.toLowerCase() ] || 0 ) + 1;
	} );
	pieces.forEach( ( p ) => {
		if ( seen[ p.title.toLowerCase() ] > 1 && p.project && ! p.title.toLowerCase().includes( p.project.toLowerCase() ) ) {
			p.title = p.project + ' · ' + p.title;
		}
	} );
	return pieces;
}

/* ── the files of a piece ── */
function filesOf( dir ) {
	const out = [];
	const go = ( d0 ) => {
		let list;
		try {
			list = fs.readdirSync( d0, { withFileTypes: true } );
		} catch ( e ) {
			return;
		}
		for ( const d of list ) {
			const full = path.join( d0, d.name );
			if ( d.isDirectory() ) {
				if ( ! SKIP_INSIDE.test( d.name ) ) {
					go( full );
				}
			} else if ( d.isFile() && ! SKIP_FILE.test( d.name ) && ! NEVER.test( d.name ) ) {
				out.push( { full, size: fs.statSync( full ).size } );
			}
		}
	};
	go( dir );
	return out;
}

/* The files a text file loads: quoted paths with a known extension. A script's
   path is looked for beside the script and beside the piece's pages, since a
   browser reads it from the page. A path from the site root ("/media/x.mp4")
   is looked for in each folder above, and when found it is rewritten relative
   to the file, since the piece is served from its own folder on the site. A
   path the code builds (`frames/${i}.jpg`, `audio/mix/${key}.wav`) sends the
   whole folder before the first `${`. */
const REF = /["'`(]\s*((?:\.{1,2}\/|\/)?[^"'`()\s<>{}|\\*]{1,300}?)(?:[?#][^"'`()\s<>]*)?\s*["'`)]/g;
const TPL = /[`'"]\s*((?:\.{1,2}\/|\/)?(?:[\w .@-]+\/)+)(?:[\w .@-]*)\$\{/g;
const MAX_DIR = 1536 * 1048576;
const isFile = ( t ) => {
	try {
		return fs.statSync( t ).isFile();
	} catch ( e ) {
		return false;
	}
};
const isDir = ( t ) => {
	try {
		return fs.statSync( t ).isDirectory();
	} catch ( e ) {
		return false;
	}
};
function dirFiles( dir, out, cap ) {
	let list;
	try {
		list = fs.readdirSync( dir, { withFileTypes: true } );
	} catch ( e ) {
		return out;
	}
	for ( const d of list ) {
		const full = path.join( dir, d.name );
		if ( d.isDirectory() ) {
			if ( ! /^(\.git|node_modules|__pycache__)$/i.test( d.name ) ) {
				dirFiles( full, out, cap );
			}
		} else if ( d.isFile() && ! NEVER.test( d.name ) && ! /\.(psd|aep|aepx|prproj|blend1?|zip|7z|rar|npy|npz|pyc)$/i.test( d.name ) ) {
			out.size += fs.statSync( full ).size;
			if ( out.size > cap ) {
				out.over = true;
				return out;
			}
			out.list.push( full );
		}
	}
	return out;
}
function refsOf( file, root, homes ) {
	let text;
	try {
		text = fs.readFileSync( file, 'utf8' );
	} catch ( e ) {
		return { found: [], missing: 0, rewrite: [], dirs: [] };
	}
	const found = [];
	const rewrite = [];
	const dirs = [];
	let missing = 0;
	const dir = path.dirname( file );
	const from = [ dir, ...homes.filter( ( h ) => h !== dir ) ];
	const locate = ( s, test ) => {
		if ( s.startsWith( '/' ) ) {
			for ( let d = dir; under( d, root ); d = path.dirname( d ) ) {
				const t = path.join( d, s );
				if ( test( t ) ) {
					return { t, abs: true };
				}
				if ( d === root ) {
					break;
				}
			}
			return null;
		}
		for ( const d of from ) {
			const t = path.resolve( d, s );
			if ( under( t, root ) && test( t ) ) {
				return { t, abs: false };
			}
		}
		return null;
	};
	let m;
	REF.lastIndex = 0;
	while ( ( m = REF.exec( text ) ) ) {
		let s = m[ 1 ];
		if ( ! MEDIA_REF.test( s ) || /^(https?:|data:|blob:|\/\/|mailto:|#)/i.test( s ) || s.includes( '${' ) ) {
			continue;
		}
		try {
			s = decodeURIComponent( s );
		} catch ( e ) {}
		const hit = locate( s, isFile );
		if ( hit ) {
			if ( hit.abs ) {
				let relTo = posix( path.relative( dir, hit.t ) );
				if ( ! relTo.startsWith( '.' ) ) {
					relTo = './' + relTo;
				}
				rewrite.push( [ m[ 1 ], relTo ] );
			}
			if ( ! NEVER.test( hit.t ) ) {
				found.push( hit.t );
			}
		} else if ( /^\.{0,2}\//.test( s ) || /^[\w.-]+\//.test( s ) ) {
			missing++;
		}
	}
	TPL.lastIndex = 0;
	while ( ( m = TPL.exec( text ) ) ) {
		const s = m[ 1 ];
		if ( /^(https?:|\/\/)/i.test( s ) || s.startsWith( '/' ) ) {
			continue;
		}
		const hit = locate( s, isDir );
		if ( hit ) {
			dirs.push( hit.t );
		}
	}
	return { found, missing, rewrite, dirs };
}

/* Everything a piece sends: its folder (less the working parts) and every file
   its pages and scripts load, wherever it sits under the root. */
function gather( p, root ) {
	const files = new Map();
	for ( const f of filesOf( p.dir ) ) {
		files.set( f.full, f );
	}
	p.missing = 0;
	p.rewrites = {};
	p.bigDirs = [];
	const dirsDone = new Set();
	const homes = [ ...new Set( [ p.mainFile, p.cutFile, ...p.clipFiles.map( ( c ) => c.file ) ].filter( Boolean ).map( ( f ) => path.dirname( f ) ) ) ];
	const read = new Set();
	for ( let round = 0; round < 4; round++ ) {
		const todo = [ ...files.values() ].filter( ( f ) => TEXT.test( f.full ) && ! read.has( f.full ) && f.size < 8 * 1048576 );
		if ( ! todo.length ) {
			break;
		}
		for ( const f of todo ) {
			read.add( f.full );
			const r = refsOf( f.full, root, homes );
			p.missing += r.missing;
			if ( r.rewrite.length ) {
				p.rewrites[ f.full ] = r.rewrite;
			}
			for ( const t of r.found ) {
				if ( ! files.has( t ) ) {
					files.set( t, { full: t, size: fs.statSync( t ).size } );
				}
			}
			for ( const d of r.dirs ) {
				/* Not the piece's folder or one above it: that would be everything. */
				if ( under( p.dir, d ) || dirsDone.has( d ) ) {
					continue;
				}
				dirsDone.add( d );
				const got = dirFiles( d, { list: [], size: 0 }, MAX_DIR );
				if ( got.over ) {
					p.bigDirs.push( posix( path.relative( root, d ) ) );
					continue;
				}
				for ( const t of got.list ) {
					if ( ! files.has( t ) ) {
						files.set( t, { full: t, size: fs.statSync( t ).size } );
					}
				}
			}
		}
	}
	let base = p.dir;
	for ( const f of files.values() ) {
		if ( ! under( f.full, base ) ) {
			base = common( base, path.dirname( f.full ) );
		}
	}
	const rel = ( f ) => posix( path.relative( base, f ) );
	p.base = base;
	p.files = [ ...files.values() ].map( ( f ) => ( { ...f, rel: rel( f.full ), rewrite: p.rewrites[ f.full ] } ) );
	p.bytes = p.files.reduce( ( s, f ) => s + f.size, 0 );
	p.entry = rel( p.mainFile );
	p.entry45 = p.cutFile ? rel( p.cutFile ) : '';
	p.clips = p.clipFiles.map( ( c ) => ( { title: c.title, entry: rel( c.file ) } ) );
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

const esc = ( s ) => s.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
/* Paths from the site root, pointed at the same files inside the piece. */
function rewritten( text, pairs ) {
	for ( const [ from, to ] of pairs ) {
		text = text.replace( new RegExp( '(["\'`(]\\s*)' + esc( from ) + '(?=[?#"\'`)\\s])', 'g' ), ( m, q ) => q + to );
	}
	return text;
}

async function zip( files, out ) {
	const fh = await fsp.open( out, 'w' );
	const central = [];
	let offset = 0;
	const write = async ( b ) => {
		await fh.write( b );
		offset += b.length;
	};
	for ( const f of files ) {
		let data = await fsp.readFile( f.full );
		if ( f.rewrite ) {
			data = Buffer.from( rewritten( data.toString( 'utf8' ), f.rewrite ), 'utf8' );
		}
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
	const root = path.resolve( ROOT );
	const pieces = plan();
	let total = 0;
	for ( const p of pieces ) {
		gather( p, root );
		p.tooBig = p.bytes > MAX_PIECE;
		total += p.bytes;
	}

	console.log( pieces.length + ' pieces, ' + mb( total ) + ' before packing.\n' );
	for ( const p of pieces ) {
		const note = [ p.clips.length ? '+' + p.clips.length + ' clips' : '', p.entry45 ? '4:5' : '', p.missing ? p.missing + ' missing' : '', p.tooBig ? 'TOO BIG' : '' ].filter( Boolean ).join( ', ' );
		console.log( `${ p.date }  ${ p.kind.padEnd( 6 ) } ${ mb( p.bytes ).padStart( 10 ) }  ${ p.origin }  →  ${ p.title }${ note ? '  (' + note + ')' : '' }` );
	}
	const planFile = opt( 'plan', '' );
	if ( planFile ) {
		fs.writeFileSync(
			planFile,
			JSON.stringify(
				pieces.map( ( p ) => ( {
					origin: p.origin,
					title: p.title,
					date: p.date,
					kind: p.kind,
					mb: +( p.bytes / 1048576 ).toFixed( 1 ),
					tooBig: p.tooBig,
					base: p.base,
					entry: p.entry,
					entry45: p.entry45,
					clips: p.clips,
					missing: p.missing,
					tooBigFolders: p.bigDirs,
					rewritten: Object.keys( p.rewrites ).map( ( f ) => posix( path.relative( p.base, f ) ) ),
					files: p.files.map( ( f ) => f.rel ),
				} ) ),
				null,
				1
			)
		);
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
		if ( p.tooBig ) {
			console.log( label + ': ' + mb( p.bytes ) + ' is over the site\'s 3 GB limit for one piece, skipped' );
			done.push( { origin: p.origin, error: 'too big: ' + mb( p.bytes ) } );
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
