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
 *   score.wav it would preload first is not shipped, and its volume starts
 *   at 25 % like the other players.
 * - index.html is a small page that sends the player's "All demos" link
 *   back to /map-animation-demo/ (or /en/map-animation-demo/).
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
const OUT = path.join( __dirname, '..', 'assets', 'demo', 'map-animation' );

const read = ( f ) => fs.readFileSync( f, 'utf8' );
const hash = ( f ) => crypto.createHash( 'sha1' ).update( fs.readFileSync( f ) ).digest( 'hex' ).slice( 0, 8 );

function replaceOnce( text, from, to, file ) {
	if ( ! text.includes( from ) ) {
		throw new Error( `${ file }: expected to find ${ JSON.stringify( from ) }` );
	}
	return text.split( from ).join( to );
}

/* Every src="lib/…js", "audio/…js" or "data/…js" gets the hash of the copied file. */
function stamp( html ) {
	return html.replace( /src="((?:lib|audio|data)\/[\w./-]+\.js)"/g, ( m, rel ) => {
		const f = path.join( OUT, rel );
		if ( ! fs.existsSync( f ) ) {
			throw new Error( `missing ${ rel }` );
		}
		return `src="${ rel }?v=${ hash( f ) }"`;
	} );
}

fs.rmSync( OUT, { recursive: true, force: true } );
for ( const d of [ 'lib', 'audio', 'thumbs' ] ) {
	fs.mkdirSync( path.join( OUT, d ), { recursive: true } );
}

/* Shared scripts, voices, thumbnails. */
for ( const f of fs.readdirSync( path.join( SRC, 'lib' ) ).filter( ( n ) => n.endsWith( '.js' ) ) ) {
	fs.copyFileSync( path.join( SRC, 'lib', f ), path.join( OUT, 'lib', f ) );
}
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
shores = replaceOnce( shores, 'let volume = 0.8;', 'let volume = 0.25;', 'Shifting Shores' );
shores = replaceOnce( shores, 'id="vol" type="range" min="0" max="1" step="0.01" value="0.8"', 'id="vol" type="range" min="0" max="1" step="0.01" value="0.25"', 'Shifting Shores' );
fs.writeFileSync( path.join( OUT, 'shifting-shores.html' ), stamp( shores ) );

/* The players' "All demos" link points at index.html. */
fs.writeFileSync( path.join( OUT, 'index.html' ), `<!doctype html>
<meta charset="utf-8">
<title>Map Animation Demos</title>
<meta name="robots" content="noindex">
<script>
var root = location.pathname.split( '/wp-content/' )[ 0 ];
location.replace( root + ( /\\/en\\//.test( document.referrer ) ? '/en' : '' ) + '/map-animation-demo/' );
</script>
<a href="/map-animation-demo/">Map Animation Demos</a>
` );

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
console.log( `map-animation: ${ PAGES.length + 1 } demos, ${ ( bytes / 1048576 ).toFixed( 1 ) } MB in ${ path.relative( process.cwd(), OUT ) }` );
