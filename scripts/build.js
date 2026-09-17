/**
 * Build the theme's front-end assets.
 *
 * src/css/NN-name.css  ->  assets/style.min.css   (joined in name order, minified)
 * src/js/NN-name.js    ->  assets/app.min.js      (joined in name order, wrapped in
 *                                                  one IIFE, minified)
 * src/css/name.css     ->  assets/name.min.css    (no number: a page bundle, built on
 * src/js/name.js       ->  assets/name.min.js      its own and enqueued only on the
 *                                                  page that needs it)
 *
 * The numeric prefixes decide the order, which matters: the JS parts share
 * one closure, exactly as they did when they were one file, and the CSS
 * parts rely on the cascade.
 *
 *   npm run build          write both files
 *   npm run check          exit 1 if the committed files are out of date
 *
 * The built files are committed on purpose: the theme updates itself from
 * the GitHub repository, so whatever is in the repository is what the live
 * site serves. Bump RS_VERSION after every build so browsers fetch the new
 * copy.
 */

'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const esbuild = require( 'esbuild' );

const root = path.resolve( __dirname, '..' );
const check = process.argv.includes( '--check' );
const banner = '/* Built from src/ by `npm run build`. Edit the sources, not this file. */\n';
const numbered = /^\d+-/;

function join( dir, ext ) {
	const full = path.join( root, dir );

	return fs
		.readdirSync( full )
		.filter( ( name ) => name.endsWith( ext ) && numbered.test( name ) )
		.sort()
		.map( ( name ) => fs.readFileSync( path.join( full, name ), 'utf8' ) )
		.join( '\n' );
}

const css = esbuild.transformSync( join( 'src/css', '.css' ), {
	loader: 'css',
	minify: true,
	legalComments: 'none',
} ).code;

const js = esbuild.transformSync( '( function () {\n' + join( 'src/js', '.js' ) + '\n}() );\n', {
	loader: 'js',
	minify: true,
	target: 'es2017',
	legalComments: 'none',
} ).code;

const outputs = {
	'assets/style.min.css': banner + css,
	'assets/app.min.js': banner + js,
};

/* Page bundles: one source file each, already wrapped in its own scope. */
for ( const [ dir, ext, loader ] of [ [ 'src/css', '.css', 'css' ], [ 'src/js', '.js', 'js' ] ] ) {
	fs.readdirSync( path.join( root, dir ) )
		.filter( ( name ) => name.endsWith( ext ) && ! numbered.test( name ) )
		.sort()
		.forEach( ( name ) => {
			const options = { loader, minify: true, legalComments: 'none' };

			if ( 'js' === loader ) {
				options.target = 'es2017';
			}

			outputs[ 'assets/' + name.slice( 0, -ext.length ) + '.min' + ext ] =
				banner + esbuild.transformSync( fs.readFileSync( path.join( root, dir, name ), 'utf8' ), options ).code;
		} );
}

let stale = false;

for ( const [ file, content ] of Object.entries( outputs ) ) {
	const target = path.join( root, file );
	const current = fs.existsSync( target ) ? fs.readFileSync( target, 'utf8' ) : null;

	if ( check ) {
		if ( current !== content ) {
			console.error( file + ' is out of date; run `npm run build` and commit the result.' );
			stale = true;
		}
		continue;
	}

	fs.writeFileSync( target, content );
	console.log( file + ': ' + content.length + ' bytes' );
}

if ( stale ) {
	process.exit( 1 );
}
