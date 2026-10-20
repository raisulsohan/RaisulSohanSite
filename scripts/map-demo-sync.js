#!/usr/bin/env node
/*
 * Copies the map animation demos into assets/demo/map-animation/, the folder
 * the /map-animation-demo/ page (parts/map-animation-demo.php) plays them from.
 *
 *   node scripts/map-demo-sync.js ["path/to/map animation demo"]
 *
 * The source is the working folder the demos are made in; Shifting Shores
 * sits beside it in "Vox Map Demo". What changes on the way in:
 *
 * - Each player asks only for its MP3 voice (the WAV and M4A are not shipped).
 * - Local scripts get ?v=<hash of the file>. The .htaccess caches .js for a
 *   year and the service worker serves scripts cache first, so a changed
 *   player.js or vo-env.js needs a new URL to reach returning visitors.
 * - Shifting Shores reads its voice and vo-env.js from audio/ next to it,
 *   its score is score-vo.wav encoded to MP3 (needs ffmpeg), the unducked
 *   score.wav it would preload first is not shipped.
 * - Every player starts at full volume (the working copies start lower:
 *   lib/player.js at 5 %, Shifting Shores at 80 %).
 * - index.html is a small page that sends the player's "All demos" link
 *   back to /map-animation-demo/ (or /en/map-animation-demo/), out of the
 *   portfolio's demo pop-up too.
 * - The showreel (showreel.html, its 4:5 cut showreel-4x5.html and the
 *   showreel/clips.js it plays the demos through) comes along; it is the Map
 *   Animation Showreel project's demo on the portfolio. clips.js is made in
 *   the source by `node showreel/build.mjs`, so the sync stops if a demo page
 *   is newer than it.
 * - Strait of Hormuz (demo 13) comes from "Map Animation for client work"
 *   beside the demo folder. It has its own player, voice (01-vo.mp3) and
 *   vo-env.js, so it goes into hormuz/ with them: hormuz-v2.html and its
 *   4:5 cut hormuz-v2-4x5.html. The map images ship as files, not as the
 *   25 MB data/tex.js that inlines them for file://, and the CodeFlow-only
 *   <audio> tags are dropped. hormuz-v2.html?ar=45 opens the 4:5 file.
 * - Bahrain (demo 14) comes from the client documentary's map parts in
 *   "07. Business Basic/…/map-animations/feedback": its 23 s highlights,
 *   drawn live from the parts' code (highlights-live.html, whose scenes
 *   tools/build_live_scenes.js collects into highlights/scenes.js; the sync
 *   stops if a part page is newer than that file). It goes into bahrain/
 *   with its own player, map engine, map data and sound: the two big JPEG
 *   satellite pictures as WebP (needs ffmpeg), the mix as MP3, and an
 *   index.html for the player's "All demos" link. ?ar=45 is its 4:5 cut.
 *
 * Only the demos in PAGES are copied. Add a number here and a card in
 * parts/map-animation-demo.php to publish another one.
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const crypto = require( 'crypto' );
const { execFileSync } = require( 'child_process' );

const PAGES = [ '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '12' ];
const SHORES = '11';

const SRC = path.resolve( process.argv[ 2 ] || 'D:/GitHub/04. Animation for video/05. Fun/map animation demo' );
const VOX = path.join( SRC, '..', 'Vox Map Demo' );
const HORMUZ = path.join( SRC, '..', 'Map Animation for client work' );
const BAHRAIN = path.join( SRC, '..', '..', '07. Business Basic', 'Why 1.5 Million People Live on a Desert Island With No Rivers', 'map-animations', 'feedback' );
const OUT = path.join( __dirname, '..', 'assets', 'demo', 'map-animation' );

const read = ( f ) => fs.readFileSync( f, 'utf8' );
const hash = ( f ) => crypto.createHash( 'sha1' ).update( fs.readFileSync( f ) ).digest( 'hex' ).slice( 0, 8 );

/* `from` is a string (every copy is replaced) or a RegExp (the first match). */
function replaceOnce( text, from, to, file ) {
	if ( from instanceof RegExp ? ! from.test( text ) : ! text.includes( from ) ) {
		throw new Error( `${ file }: expected to find ${ from instanceof RegExp ? from : JSON.stringify( from ) }` );
	}
	return from instanceof RegExp ? text.replace( from, to ) : text.split( from ).join( to );
}

/* Every src="lib/…js", "audio/…js", "data/…js", "showreel/…js" or "highlights/…js" gets the hash of the copied file in `dir`. */
function stamp( html, dir = OUT ) {
	return html.replace( /src="((?:lib|audio|data|showreel|highlights)\/[\w./-]+\.js)"/g, ( m, rel ) => {
		const f = path.join( dir, rel );
		if ( ! fs.existsSync( f ) ) {
			throw new Error( `missing ${ rel }` );
		}
		return `src="${ rel }?v=${ hash( f ) }"`;
	} );
}

/* Checked before anything is removed: the showreel's bundle has to be made from the demos being copied. */
const CLIPS = path.join( SRC, 'showreel', 'clips.js' );
for ( const n of fs.readdirSync( SRC ).filter( ( f ) => /^\d\d-.+\.html$/.test( f ) ) ) {
	if ( fs.statSync( path.join( SRC, n ) ).mtimeMs > fs.statSync( CLIPS ).mtimeMs ) {
		throw new Error( `${ n } is newer than showreel/clips.js: run  node showreel/build.mjs  in the demo folder first` );
	}
}

/* The Bahrain highlights play the scenes collected from its part pages; a changed part needs them collected again. */
const SCENES = path.join( BAHRAIN, 'highlights', 'scenes.js' );
for ( const n of fs.readdirSync( BAHRAIN ).filter( ( f ) => /^\d\d-.+\.html$/.test( f ) ) ) {
	if ( fs.statSync( path.join( BAHRAIN, n ) ).mtimeMs > fs.statSync( SCENES ).mtimeMs ) {
		throw new Error( `${ n } is newer than highlights/scenes.js: run  node tools/build_live_scenes.js  in the Bahrain feedback folder first` );
	}
}

fs.rmSync( OUT, { recursive: true, force: true } );
for ( const d of [ 'lib', 'audio', 'thumbs', 'showreel' ] ) {
	fs.mkdirSync( path.join( OUT, d ), { recursive: true } );
}

/* Shared scripts, voices, thumbnails. */
for ( const f of fs.readdirSync( path.join( SRC, 'lib' ) ).filter( ( n ) => n.endsWith( '.js' ) ) ) {
	fs.copyFileSync( path.join( SRC, 'lib', f ), path.join( OUT, 'lib', f ) );
}
function fullVolume( playerFile ) {
	let player = read( playerFile );
	player = replaceOnce( player, /step="0\.01" value="[\d.]+" title="Volume"/, 'step="0.01" value="1" title="Volume"', playerFile );
	player = replaceOnce( player, /muted: false, vol: [\d.]+,/, 'muted: false, vol: 1,', playerFile );
	fs.writeFileSync( playerFile, player );
}
fullVolume( path.join( OUT, 'lib', 'player.js' ) );
fs.copyFileSync( path.join( SRC, 'audio', 'vo-env.js' ), path.join( OUT, 'audio', 'vo-env.js' ) );
for ( const id of [ ...PAGES, SHORES ] ) {
	fs.copyFileSync( path.join( SRC, 'audio', `${ id }-vo.mp3` ), path.join( OUT, 'audio', `${ id }-vo.mp3` ) );
	fs.copyFileSync( path.join( SRC, 'thumbs', `${ id }.jpg` ), path.join( OUT, 'thumbs', `${ id }.jpg` ) );
}

/* Map data the pages load (data/<place>/), without the scripts that built it. */
const DATA = path.join( SRC, 'data' );
if ( fs.existsSync( DATA ) ) {
	for ( const place of fs.readdirSync( DATA, { withFileTypes: true } ).filter( ( e ) => e.isDirectory() ) ) {
		const from = path.join( DATA, place.name ), to = path.join( OUT, 'data', place.name );
		fs.mkdirSync( to, { recursive: true } );
		for ( const f of fs.readdirSync( from ).filter( ( n ) => /\.(js|json|jpe?g|png|webp)$/.test( n ) && n !== 'build.mjs' ) ) {
			fs.copyFileSync( path.join( from, f ), path.join( to, f ) );
		}
	}
}

/* The demo pages. */
const pages = fs.readdirSync( SRC ).filter( ( n ) => /^\d\d-.+\.html$/.test( n ) );
for ( const id of PAGES ) {
	const name = pages.find( ( n ) => n.startsWith( id + '-' ) );
	if ( ! name ) {
		throw new Error( `no page for demo ${ id }` );
	}
	/* The player tries each voice file in turn; only the MP3 ships, so asking for the WAV first is a 404. */
	const html = read( path.join( SRC, name ) ).replace( /vo: \[[^\]]*'(audio\/\d\d-vo\.mp3)'[^\]]*\]/, "vo: ['$1']" );
	fs.writeFileSync( path.join( OUT, name ), stamp( html ) );
}

/* Shifting Shores. */
execFileSync( 'ffmpeg', [ '-v', 'error', '-y', '-i', path.join( VOX, 'assets', 'score-vo.wav' ), '-codec:a', 'libmp3lame', '-b:a', '192k', path.join( OUT, 'audio', 'score-vo.mp3' ) ] );
const shoresFile = path.join( VOX, 'Shifting Shores.html' );
let shores = read( shoresFile );
shores = replaceOnce( shores, '../map%20animation%20demo/audio/', 'audio/', 'Shifting Shores' );
shores = replaceOnce( shores, '<audio id="score" src="assets/score.wav"', '<audio id="score" src="audio/score-vo.mp3"', 'Shifting Shores' );
shores = replaceOnce( shores, "audio.src = 'assets/score-vo.wav'", "audio.src = 'audio/score-vo.mp3'", 'Shifting Shores' );
shores = replaceOnce( shores, 'let volume = 0.8;', 'let volume = 1;', 'Shifting Shores' );
shores = replaceOnce( shores, 'id="vol" type="range" min="0" max="1" step="0.01" value="0.8"', 'id="vol" type="range" min="0" max="1" step="0.01" value="1"', 'Shifting Shores' );
fs.writeFileSync( path.join( OUT, 'shifting-shores.html' ), stamp( shores ) );

/* The showreel. Its 4:5 page only reloads showreel.html with ?ar=45, so it goes as it is. */
fs.copyFileSync( CLIPS, path.join( OUT, 'showreel', 'clips.js' ) );
fs.writeFileSync( path.join( OUT, 'showreel.html' ), stamp( read( path.join( SRC, 'showreel.html' ) ) ) );
fs.copyFileSync( path.join( SRC, 'showreel-4x5.html' ), path.join( OUT, 'showreel-4x5.html' ) );

/* Strait of Hormuz, in hormuz/ with its own player, voice and map data. */
const HZ = path.join( OUT, 'hormuz' );
for ( const d of [ 'lib', 'audio', 'data' ] ) {
	fs.mkdirSync( path.join( HZ, d ), { recursive: true } );
}
for ( const f of [ 'lib/kit.js', 'lib/player.js', 'audio/vo-env.js', 'audio/01-vo.mp3', 'data/geo.js' ] ) {
	fs.copyFileSync( path.join( HORMUZ, f ), path.join( HZ, f ) );
}
for ( const f of fs.readdirSync( path.join( HORMUZ, 'data' ) ).filter( ( n ) => /\.(jpe?g|png)$/.test( n ) ) ) {
	fs.copyFileSync( path.join( HORMUZ, 'data', f ), path.join( HZ, 'data', f ) );
}
fullVolume( path.join( HZ, 'lib', 'player.js' ) );
fs.copyFileSync( path.join( HORMUZ, 'thumbs', 'hormuz-v2.jpg' ), path.join( OUT, 'thumbs', '13.jpg' ) );
/* The page's featured player shows a large poster of each featured demo before it loads. */
fs.copyFileSync( path.join( HORMUZ, 'thumbs', 'hormuz-v2-poster.jpg' ), path.join( OUT, 'thumbs', '13-poster.jpg' ) );
for ( const name of [ 'hormuz-v2.html', 'hormuz-v2-4x5.html' ] ) {
	let html = read( path.join( HORMUZ, name ) );
	html = replaceOnce( html, /<script src="data\/tex\.js"[^>]*><\/script>\r?\n/, '', name );
	html = replaceOnce( html, /<!-- sound for CodeFlow[^\n]*\n(?:<audio id="cf\w+"[^\n]*\n)+/, '', name );
	if ( name === 'hormuz-v2.html' ) {
		html = replaceOnce( html, /<meta charset="utf-8">\r?\n/, ( m ) => m + "<script>if (/^(45|4x5|4:5|v|vertical)$/i.test(new URLSearchParams(location.search).get('ar') || '')) location.replace('hormuz-v2-4x5.html' + location.search);</script>\n", name );
	}
	fs.writeFileSync( path.join( HZ, name ), stamp( html, HZ ) );
}

/* Bahrain highlights, in bahrain/ with their own player, engine, map data and sound. */
const BH = path.join( OUT, 'bahrain' );
for ( const d of [ 'lib', 'data/tex', 'highlights' ] ) {
	fs.mkdirSync( path.join( BH, d ), { recursive: true } );
}
for ( const f of [ 'lib/kit.js', 'lib/player.js', 'lib/geomap.js', 'lib/ui.js', 'lib/fx.js', 'data/geo.js', 'data/parts.js', 'data/places.js',
	'highlights/edl.js', 'highlights/hl.js', 'highlights/email-track.js', 'highlights/scenes.js' ] ) {
	fs.copyFileSync( path.join( BAHRAIN, f ), path.join( BH, f ) );
}
fullVolume( path.join( BH, 'lib', 'player.js' ) );
let bhLayers = read( path.join( BAHRAIN, 'data', 'layers.js' ) );
for ( const f of fs.readdirSync( path.join( BAHRAIN, 'data', 'tex' ) ) ) {
	const from = path.join( BAHRAIN, 'data', 'tex', f );
	if ( /\.jpe?g$/.test( f ) ) {
		const webp = f.replace( /\.jpe?g$/, '.webp' );
		execFileSync( 'ffmpeg', [ '-v', 'error', '-y', '-i', from, '-c:v', 'libwebp', '-quality', '90', path.join( BH, 'data', 'tex', webp ) ] );
		bhLayers = replaceOnce( bhLayers, `data/tex/${ f }`, `data/tex/${ webp }`, 'bahrain layers.js' );
	} else {
		fs.copyFileSync( from, path.join( BH, 'data', 'tex', f ) );
	}
}
fs.writeFileSync( path.join( BH, 'data', 'layers.js' ), bhLayers );
execFileSync( 'ffmpeg', [ '-v', 'error', '-y', '-i', path.join( BAHRAIN, 'highlights', 'mix.wav' ), '-codec:a', 'libmp3lame', '-b:a', '192k', path.join( BH, 'highlights', 'mix.mp3' ) ] );
let bhPage = read( path.join( BAHRAIN, 'highlights-live.html' ) );
bhPage = replaceOnce( bhPage, "audio: 'highlights/mix.wav'", "audio: 'highlights/mix.mp3'", 'highlights-live.html' );
fs.writeFileSync( path.join( BH, 'highlights.html' ), stamp( bhPage, BH ) );
fs.copyFileSync( path.join( BAHRAIN, 'highlights', 'thumb.jpg' ), path.join( OUT, 'thumbs', '14.jpg' ) );
fs.copyFileSync( path.join( BAHRAIN, 'highlights', 'poster.jpg' ), path.join( OUT, 'thumbs', '14-poster.jpg' ) );

/* The players' "All demos" link points at index.html. A player in the
   portfolio's demo pop-up opens the page in the whole window, not inside the
   pop-up, and the window's address says which edition it came from. The
   Bahrain player has the link too, so bahrain/ gets the same page. */
const INDEX_HTML = `<!doctype html>
<meta charset="utf-8">
<title>Map Animation Demos</title>
<meta name="robots" content="noindex">
<script>
var root = location.pathname.split( '/wp-content/' )[ 0 ];
var win = window, from = document.referrer;
try {
	if ( window.top !== window ) {
		from = window.top.location.pathname;
		win = window.top;
	}
} catch ( e ) {}
win.location.replace( root + ( /\\/en\\//.test( from ) ? '/en' : '' ) + '/map-animation-demo/' );
</script>
<a href="/map-animation-demo/">Map Animation Demos</a>
`;
fs.writeFileSync( path.join( OUT, 'index.html' ), INDEX_HTML );
fs.writeFileSync( path.join( BH, 'index.html' ), INDEX_HTML );

let bytes = 0;
const walk = ( d ) => fs.readdirSync( d, { withFileTypes: true } ).forEach( ( e ) => {
	const p = path.join( d, e.name );
	if ( e.isDirectory() ) {
		walk( p );
	} else {
		bytes += fs.statSync( p ).size;
	}
} );
walk( OUT );
console.log( `map-animation: ${ PAGES.length + 3 } demos and the showreel, ${ ( bytes / 1048576 ).toFixed( 1 ) } MB in ${ path.relative( process.cwd(), OUT ) }` );
