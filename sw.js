/**
 * Raisul Sohan — Service Worker.
 *
 * RS_SW_CONFIG is prepended by the PHP that serves this file.
 * It carries the theme version, the theme URI, and the list of
 * shell assets to pre-cache on install.
 */

/* eslint-disable no-restricted-globals */

var CFG    = self.RS_SW_CONFIG || {};
var VER    = CFG.version || '0';
var SHELL  = 'rs-shell-' + VER;
var POSTS  = 'rs-posts-' + VER;
var PAGES  = 'rs-pages-' + VER;
var CACHES = [ SHELL, POSTS, PAGES ];

/* -------------------------------------------------------------------
 * Install — pre-cache the app shell
 * ---------------------------------------------------------------- */

self.addEventListener( 'install', function ( event ) {
	event.waitUntil(
		caches.open( SHELL ).then( function ( cache ) {
			return cache.addAll( CFG.shell || [] );
		} ).then( function () {
			return self.skipWaiting();
		} )
	);
} );

/* -------------------------------------------------------------------
 * Activate — drop every cache from an older version
 * ---------------------------------------------------------------- */

self.addEventListener( 'activate', function ( event ) {
	event.waitUntil(
		caches.keys().then( function ( names ) {
			return Promise.all(
				names.filter( function ( n ) {
					return n.startsWith( 'rs-' ) && CACHES.indexOf( n ) === -1;
				} ).map( function ( n ) {
					return caches.delete( n );
				} )
			);
		} ).then( function () {
			return self.clients.claim();
		} )
	);
} );

/* -------------------------------------------------------------------
 * Fetch strategies
 * ---------------------------------------------------------------- */

function cacheFirst( request, bucket ) {
	return caches.match( request ).then( function ( hit ) {
		if ( hit ) {
			return hit;
		}

		return fetch( request ).then( function ( res ) {
			if ( res.ok ) {
				var copy = res.clone();

				caches.open( bucket ).then( function ( c ) {
					c.put( request, copy );
				} );
			}

			return res;
		} );
	} );
}

function networkFirst( request, bucket ) {
	return fetch( request ).then( function ( res ) {
		if ( res.ok ) {
			var copy = res.clone();

			caches.open( bucket ).then( function ( c ) {
				c.put( request, copy );
			} );
		}

		return res;
	} ).catch( function () {
		return caches.match( request );
	} );
}

self.addEventListener( 'fetch', function ( event ) {
	var url = new URL( event.request.url );

	/* 1. Shell assets — cache first. */
	if ( /\.(css|js|woff2?)(?:\?|$)/i.test( url.pathname ) ) {
		event.respondWith( cacheFirst( event.request, SHELL ) );
		return;
	}

	/* 2. Post JSON — network first, cache fallback for offline. */
	if ( /\/wp-json\/rs\/v1\/post\/\d+/.test( url.pathname ) ) {
		event.respondWith( networkFirst( event.request, POSTS ) );
		return;
	}

	/* 3. Page navigations — network first, offline.html fallback. */
	if ( event.request.mode === 'navigate' ) {
		event.respondWith(
			fetch( event.request ).then( function ( res ) {
				if ( res.ok ) {
					var copy = res.clone();

					caches.open( PAGES ).then( function ( c ) {
						c.put( event.request, copy );
					} );
				}

				return res;
			} ).catch( function () {
				return caches.match( event.request ).then( function ( hit ) {
					return hit || caches.match( CFG.offlineUrl || '/offline' );
				} );
			} )
		);
		return;
	}
} );

/* -------------------------------------------------------------------
 * Messages from app.js — pre-cache and uncache posts
 * ---------------------------------------------------------------- */

self.addEventListener( 'message', function ( event ) {
	var data = event.data;

	if ( ! data ) {
		return;
	}

	if ( data.type === 'CACHE_POST' && data.url ) {
		caches.open( POSTS ).then( function ( c ) {
			c.add( data.url ).catch( function () {
				/* Network hiccup — the post will be cached on next open. */
			} );
		} );
	}

	if ( data.type === 'UNCACHE_POST' && data.url ) {
		caches.open( POSTS ).then( function ( c ) {
			c.delete( data.url ).catch( function () {} );
		} );
	}
} );
