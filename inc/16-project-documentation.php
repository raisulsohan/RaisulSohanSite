<?php
/**
 * Project documentation, read from each project's own repository.
 *
 * A project whose GitHub repository has a docs/README.md gets that
 * documentation on the site too, at /portfolio/<project>/documentation/
 * with a page under it for every file. Nothing is copied into the theme:
 * the files are read from GitHub and rendered by GitHub's own Markdown API,
 * so an edit pushed there shows up here within minutes and reads as it
 * does there.
 *
 * What is kept, and where:
 * - the index of each repository's pages (paths, blob SHAs, titles, order)
 *   in the network option rs_project_docs, refreshed alongside the GitHub
 *   numbers (rs_refresh_github_stats) on a clock of its own;
 * - each file's Markdown, and GitHub's HTML for it, in site transients
 *   keyed by the blob SHA, so a page is fetched and rendered once per
 *   version of it and never again.
 *
 * The docs are written in English and are the same on both editions; only
 * the page around them follows the site's language.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * The index: which pages a repository's documentation has
 * ====================================================================== */

/**
 * Every repository's documentation index, keyed by lower-case owner/repo.
 *
 * @return array
 */
function rs_docs_store() {
	$all = get_site_option( 'rs_project_docs', array() );

	return is_array( $all ) ? $all : array();
}

/**
 * A repository's documentation index, or null when it has none.
 *
 * @param string $slug owner/repo.
 * @return array|null { pages: key => page, order: key[] }
 */
function rs_docs_index( $slug ) {
	$all = rs_docs_store();
	$key = strtolower( (string) $slug );

	return ! empty( $all[ $key ]['pages'] ) ? $all[ $key ] : null;
}

/**
 * List one directory of a repository, asking conditionally.
 *
 * @param string $slug owner/repo.
 * @param string $dir  Directory, '' for the top.
 * @param array  $args From rs_github_request_args().
 * @param string $etag ETag from last time, or ''.
 * @return array { status: int (0 on a network error), files: name => file, etag: string }
 */
function rs_docs_list( $slug, $dir, $args, $etag ) {
	if ( '' !== $etag ) {
		$args['headers']['If-None-Match'] = $etag;
	}

	$response = wp_remote_get( 'https://api.github.com/repos/' . $slug . '/contents' . ( '' !== $dir ? '/' . $dir : '' ), $args );

	if ( is_wp_error( $response ) ) {
		return array( 'status' => 0, 'files' => array(), 'etag' => $etag );
	}

	$status = (int) wp_remote_retrieve_response_code( $response );

	if ( 200 !== $status ) {
		return array( 'status' => $status, 'files' => array(), 'etag' => $etag );
	}

	$files = array();
	$items = json_decode( wp_remote_retrieve_body( $response ), true );

	foreach ( is_array( $items ) ? $items : array() as $item ) {
		if ( empty( $item['name'] ) || 'file' !== ( isset( $item['type'] ) ? $item['type'] : '' ) || ! preg_match( '/\.md$/i', $item['name'] ) ) {
			continue;
		}

		$files[ $item['name'] ] = array(
			'path' => (string) $item['path'],
			'sha'  => (string) $item['sha'],
			'html' => esc_url_raw( (string) $item['html_url'] ),
			'raw'  => esc_url_raw( (string) $item['download_url'] ),
		);
	}

	return array(
		'status' => 200,
		'files'  => $files,
		'etag'   => (string) wp_remote_retrieve_header( $response, 'etag' ),
	);
}

/**
 * A file's Markdown, fetched once per version of it.
 *
 * Read from raw.githubusercontent.com, which costs nothing against the
 * API's rate limit.
 *
 * @param array $page { sha, raw }.
 * @return string|null
 */
function rs_docs_markdown( $page ) {
	$key = 'rs_docs_md_' . $page['sha'];
	$md  = get_site_transient( $key );

	if ( is_string( $md ) ) {
		return $md;
	}

	$response = wp_remote_get( $page['raw'], array( 'timeout' => 10 ) );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}

	$md = (string) wp_remote_retrieve_body( $response );

	set_site_transient( $key, $md, 30 * DAY_IN_SECONDS );

	return $md;
}

/**
 * A page's title: its first top-level heading, without the Markdown.
 *
 * @param string $md       Markdown.
 * @param string $fallback Used when there is no heading.
 * @return string
 */
function rs_docs_title( $md, $fallback ) {
	if ( preg_match( '/^#\s+(.+?)\s*#*\s*$/m', (string) $md, $found ) ) {
		return sanitize_text_field( str_replace( array( '**', '__', '`' ), '', $found[1] ) );
	}

	return $fallback;
}

/**
 * Which language a page is written in, for its lang attribute: Bengali when
 * Bengali letters make up a good share of it, English otherwise. The docs
 * are mostly English, but a project may keep a checklist or two in Bengali.
 *
 * @param string|null $md Markdown.
 * @return string 'bn' or 'en'.
 */
function rs_docs_lang( $md ) {
	$bn = (int) preg_match_all( '/[\x{0980}-\x{09FF}]/u', (string) $md );
	$en = (int) preg_match_all( '/[A-Za-z]/', (string) $md );

	return ( $bn && $bn > 0.25 * $en ) ? 'bn' : 'en';
}

/**
 * The page key for a file: '' for the documentation's own README, and the
 * file's name without .md for everything else.
 *
 * @param string $name File name.
 * @param bool   $home Whether it is the docs folder's README.
 * @return string
 */
function rs_docs_key( $name, $home = false ) {
	return $home ? '' : sanitize_title( preg_replace( '/\.md$/i', '', $name ) );
}

/**
 * Ask GitHub what a repository's documentation holds now.
 *
 * Every Markdown file in docs/ is a page, the folder's README the first of
 * them, and so is any Markdown file at the top of the repository that those
 * pages link to (a README, a guide to publishing): the docs point at them
 * as part of themselves. The order is the order the docs README links to
 * them in, then anything it does not mention.
 *
 * Both listings are asked for with their ETags, so between pushes this
 * costs nothing against the rate limit. A failure keeps what was known.
 *
 * @param string $slug owner/repo.
 * @param array  $args From rs_github_request_args().
 * @param array  $old  The index as it stands.
 * @return array The index to keep.
 */
function rs_docs_refresh( $slug, $args, $old ) {
	$old            = is_array( $old ) ? $old : array();
	$new            = $old;
	$new['checked'] = time();

	/* An ETag is only worth sending with the listing it stands for kept:
	   a 304 without that listing would leave nothing to build from. */
	$docs = rs_docs_list( $slug, 'docs', $args, isset( $old['etag_docs'], $old['files_docs'] ) ? (string) $old['etag_docs'] : '' );

	/* No docs folder, or no longer one. */
	if ( 404 === $docs['status'] ) {
		return array( 'checked' => time() );
	}

	if ( 200 !== $docs['status'] && 304 !== $docs['status'] ) {
		return $new;
	}

	$root = rs_docs_list( $slug, '', $args, isset( $old['etag_root'], $old['files_root'] ) ? (string) $old['etag_root'] : '' );

	if ( 200 !== $root['status'] && 304 !== $root['status'] ) {
		return $new;
	}

	/* Nothing moved since the last look. */
	if ( 304 === $docs['status'] && 304 === $root['status'] && ! empty( $old['pages'] ) ) {
		return $new;
	}

	/* A 304 has no body: the files it stands for are the ones kept. */
	$in_docs = 200 === $docs['status'] ? $docs['files'] : ( isset( $old['files_docs'] ) ? (array) $old['files_docs'] : array() );
	$in_root = 200 === $root['status'] ? $root['files'] : ( isset( $old['files_root'] ) ? (array) $old['files_root'] : array() );

	if ( empty( $in_docs ) ) {
		return array( 'checked' => time() );
	}

	/* A docs folder with no README of its own still gets a home page: one
	   made here, listing the pages (see rs_docs_made_html()). The
	   repository's README joins them, as the tour the folder is missing. */
	$made   = empty( $in_docs['README.md'] );
	$folder = '';
	$pages  = array();
	$linked = array();
	$order  = array();

	if ( $made && isset( $in_root['README.md'] ) ) {
		$linked['README.md'] = true;
	}

	foreach ( $in_docs as $name => $file ) {
		$home = 'README.md' === $name;
		$md   = rs_docs_markdown( $file );
		$key  = rs_docs_key( $name, $home );

		$file['title'] = rs_docs_title( (string) $md, $home ? 'Documentation' : ucfirst( str_replace( '-', ' ', $key ) ) );
		$file['lang']  = rs_docs_lang( $md );
		$pages[ $key ] = $file;

		if ( '' === $folder && ! empty( $file['html'] ) ) {
			$folder = preg_replace( '~/blob/([^/]+)/.*$~', '/tree/$1/docs', $file['html'] );
		}

		/* What the page links to: docs pages and top-level Markdown files. */
		if ( null !== $md && preg_match_all( '/\]\(\s*<?([^)\s>]+)/', $md, $found ) ) {
			foreach ( $found[1] as $href ) {
				$href = strtok( $href, '#' );

				if ( preg_match( '~^(?:\./)?([^/]+\.md)$~i', (string) $href, $m ) && isset( $in_docs[ $m[1] ] ) ) {
					$target = rs_docs_key( $m[1], 'README.md' === $m[1] );
				} elseif ( preg_match( '~^\.\./([^/]+\.md)$~i', (string) $href, $m ) && isset( $in_root[ $m[1] ] ) ) {
					$target            = 'top:' . $m[1];
					$linked[ $m[1] ] = true;
				} else {
					continue;
				}

				if ( $home ) {
					$order[] = $target;
				}
			}
		}
	}

	foreach ( array_keys( $linked ) as $name ) {
		$key = rs_docs_key( $name );

		/* A docs page of the same name wins. */
		if ( isset( $pages[ $key ] ) ) {
			continue;
		}

		$file          = $in_root[ $name ];
		$md            = rs_docs_markdown( $file );
		$file['title'] = rs_docs_title( (string) $md, ucfirst( strtolower( preg_replace( '/\.md$/i', '', $name ) ) ) );
		$file['lang']  = rs_docs_lang( $md );

		/* The repository's README titles itself with a whole sentence, too
		   long for a list of pages; there it is simply the README. */
		$file['label'] = 'README.md' === $name ? 'README' : '';
		$pages[ $key ] = $file;
	}

	if ( $made ) {
		$pages[''] = array(
			'made'  => true,
			'path'  => 'docs',
			'sha'   => 'made',
			'html'  => $folder,
			'raw'   => '',
			'title' => (string) substr( $slug, strpos( $slug, '/' ) + 1 ) . ' documentation',
			'lang'  => 'en',
		);
	}

	/* The home page first, then the README's own order, then the rest: in
	   the order a reader would want when no README has set one, or by name. */
	$keys = array( '' );
	foreach ( $order as $target ) {
		$key = 0 === strpos( $target, 'top:' ) ? rs_docs_key( substr( $target, 4 ) ) : $target;

		if ( isset( $pages[ $key ] ) && ! in_array( $key, $keys, true ) ) {
			$keys[] = $key;
		}
	}

	$rest = array();
	foreach ( array_keys( $pages ) as $key ) {
		if ( ! in_array( (string) $key, $keys, true ) ) {
			$rest[] = (string) $key;
		}
	}
	if ( $made ) {
		usort(
			$rest,
			function ( $a, $b ) {
				$d = rs_docs_weight( $a ) - rs_docs_weight( $b );

				return $d ? ( $d < 0 ? -1 : 1 ) : strcmp( $a, $b );
			}
		);
	}
	$keys = array_merge( $keys, $rest );

	$new['pages']      = $pages;
	$new['order']      = $keys;
	$new['files_docs'] = $in_docs;
	$new['files_root'] = $in_root;
	$new['etag_docs']  = $docs['etag'];
	$new['etag_root']  = $root['etag'];

	return $new;
}

/**
 * Where a page goes in a list nobody has ordered: what gets someone
 * started first, what is for someone changing the code last.
 *
 * @param string $key Page key.
 * @return int
 */
function rs_docs_weight( $key ) {
	$tests = array(
		'~^readme$~'                                                 => 0,
		'~develop|contribut|build|architect|scripting|internal~'     => 5,
		'~changelog|history|release|publish|testing|listing~'        => 6,
		'~troubleshoot|faq|problem|wrong~'                           => 4,
		'~user|manual|getting|start|quick|install|guide~'            => 1,
		'~how|work|timing|transfer|feature|reference~'               => 2,
	);

	foreach ( $tests as $pattern => $weight ) {
		if ( preg_match( $pattern, $key ) ) {
			return $weight;
		}
	}

	return 3;
}

/**
 * What a reader could tell apart in an index: the pages, their versions
 * and their titles.
 *
 * @param array $index Index.
 * @return string
 */
function rs_docs_signature( $index ) {
	$pages = isset( $index['pages'] ) ? (array) $index['pages'] : array();
	$marks = array();

	foreach ( $pages as $key => $page ) {
		$marks[] = $key . ':' . $page['sha'] . ':' . $page['title'];
	}

	return md5( implode( '|', $marks ) . '#' . implode( ',', isset( $index['order'] ) ? (array) $index['order'] : array() ) );
}

/**
 * Look over the documentation of every repository the portfolio links to.
 *
 * Called from rs_refresh_github_stats(). A repository known to have docs is
 * looked at every five minutes, which with ETags is free until something
 * changes; one without is looked at four times a day in case it gains some.
 * Either is looked at straight away once it has been pushed to since the
 * last look, so documentation added to a repository shows up within a
 * couple of minutes rather than at the end of that clock. When anything a
 * reader would see has changed, the page cache is emptied, so the new pages,
 * and the links to them, are not held back for an hour.
 *
 * @param array $slugs  owner/repo => true.
 * @param array $args   From rs_github_request_args().
 * @param int[] $pushed owner/repo => when it was last pushed to, where known.
 * @param bool  $force  Look at every repository now, whatever its clock says.
 */
function rs_docs_refresh_all( $slugs, $args, $pushed = array(), $force = false ) {
	$all     = rs_docs_store();
	$changed = false;
	$touched = false;

	foreach ( array_keys( (array) $slugs ) as $slug ) {
		$key    = strtolower( $slug );
		$old    = isset( $all[ $key ] ) && is_array( $all[ $key ] ) ? $all[ $key ] : array();
		$wait   = empty( $old['pages'] ) ? 6 * HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS;
		$looked = ! empty( $old['checked'] ) ? (int) $old['checked'] : 0;
		$pushes = isset( $pushed[ $slug ] ) ? (int) $pushed[ $slug ] : 0;

		if ( ! $force && $looked && ( time() - $looked ) < $wait && $pushes <= $looked ) {
			continue;
		}

		$new         = rs_docs_refresh( $slug, $args, $old );
		$all[ $key ] = $new;
		$touched     = true;

		if ( rs_docs_signature( $old ) !== rs_docs_signature( $new ) ) {
			$changed = true;
		}
	}

	/* A repository the portfolio no longer links to, under a name it no
	   longer has for instance, is forgotten. */
	$keep = array_map( 'strtolower', array_keys( (array) $slugs ) );
	foreach ( array_keys( $all ) as $key ) {
		if ( ! in_array( $key, $keep, true ) ) {
			unset( $all[ $key ] );
			$touched = true;
		}
	}

	if ( $touched ) {
		update_site_option( 'rs_project_docs', $all );
	}

	if ( $changed && function_exists( 'rs_purge_host_cache_soon' ) ) {
		rs_purge_host_cache_soon();
	}
}

/* =========================================================================
 * A project's documentation
 * ====================================================================== */

/**
 * A project's documentation index, or null when it has none.
 *
 * @param array $project Project.
 * @return array|null
 */
function rs_project_docs( $project ) {
	$slug = rs_github_repo_slug( isset( $project['github_url'] ) ? $project['github_url'] : '' );

	return $slug ? rs_docs_index( $slug ) : null;
}

/**
 * The address of a project's documentation, or of one page of it.
 *
 * @param string $project_id Project slug.
 * @param string $key        Page key; '' for the documentation's home.
 * @return string
 */
function rs_project_docs_url( $project_id, $key = '' ) {
	return home_url( '/portfolio/' . rawurlencode( $project_id ) . '/documentation/' . ( '' !== $key ? rawurlencode( $key ) . '/' : '' ) );
}

/**
 * The link to a project's documentation, as the portfolio shows it beside
 * the project's type and role. Empty when the project has none.
 *
 * @param array  $project Project.
 * @param bool   $is_en   English site.
 * @param string $class   Extra class.
 * @return string
 */
function rs_project_docs_link( $project, $is_en, $class = '' ) {
	if ( ! rs_project_docs( $project ) ) {
		return '';
	}

	return sprintf(
		'<a class="rs-pf-docs-link%1$s" href="%2$s"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 4.5h6a4 4 0 0 1 4 4V21a3 3 0 0 0-3-3H2z"/><path d="M22 4.5h-6a4 4 0 0 0-4 4V21a3 3 0 0 1 3-3h7z"/></svg><span>%3$s</span></a>',
		$class ? ' ' . esc_attr( $class ) : '',
		esc_url( rs_project_docs_url( $project['id'] ) ),
		esc_html( $is_en ? 'Documentation' : 'ডকুমেন্টেশন' )
	);
}

/* =========================================================================
 * Rendering a page
 * ====================================================================== */

/**
 * GitHub's HTML for a page, rendered once per version of it.
 *
 * "markdown" mode renders a document the way GitHub renders a README:
 * line breaks inside a paragraph stay spaces, where "gfm" mode would make
 * every one of them a <br>.
 *
 * @param array $page { sha, raw }.
 * @return string|null
 */
function rs_docs_rendered( $page ) {
	$key  = 'rs_docs_html_' . $page['sha'];
	$html = get_site_transient( $key );

	if ( is_string( $html ) && '' !== $html ) {
		return $html;
	}

	$md = rs_docs_markdown( $page );

	if ( null === $md ) {
		return null;
	}

	$args                            = rs_github_request_args();
	$args['timeout']                 = 15;
	$args['headers']['Content-Type'] = 'application/json';
	$args['body']                    = wp_json_encode(
		array(
			'text' => $md,
			'mode' => 'markdown',
		)
	);

	$response = wp_remote_post( 'https://api.github.com/markdown', $args );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}

	$html = (string) wp_remote_retrieve_body( $response );

	set_site_transient( $key, $html, 30 * DAY_IN_SECONDS );

	return $html;
}

/**
 * Resolve a relative reference against the address of the file it is in,
 * the way GitHub does: "../x.md" from docs/a.md is x.md, and a leading
 * slash is the top of the repository.
 *
 * @param string $base Absolute address of the file (a blob or raw URL).
 * @param string $rel  The reference, without a scheme.
 * @param int    $keep How many leading path segments make the repository's root (4 for a blob URL, 3 for a raw one).
 * @return string
 */
function rs_docs_resolve( $base, $rel, $keep ) {
	$parts = wp_parse_url( $base );
	$path  = isset( $parts['path'] ) ? $parts['path'] : '/';
	$tail  = '';

	foreach ( array( '#', '?' ) as $mark ) {
		$at = strpos( $rel, $mark );

		if ( false !== $at ) {
			$tail = substr( $rel, $at ) . $tail;
			$rel  = substr( $rel, 0, $at );
		}
	}

	$base_dirs = explode( '/', trim( $path, '/' ) );
	$dirs      = '/' === substr( $rel, 0, 1 ) ? array_slice( $base_dirs, 0, $keep ) : array_slice( $base_dirs, 0, -1 );

	foreach ( explode( '/', ltrim( $rel, '/' ) ) as $step ) {
		if ( '..' === $step ) {
			if ( count( $dirs ) > 2 ) {
				array_pop( $dirs );
			}
		} elseif ( '' !== $step && '.' !== $step ) {
			$dirs[] = $step;
		}
	}

	return $parts['scheme'] . '://' . $parts['host'] . '/' . implode( '/', $dirs ) . $tail;
}

/**
 * Turn GitHub's HTML for a page into this site's.
 *
 * - Headings get the ids GitHub gives them, so the contents lists written
 *   into the docs ("#installing") land where they do on GitHub, and a small
 *   link of their own.
 * - A link to another page of the documentation stays on this site; any
 *   other relative link goes to the file on GitHub; images come from the
 *   repository's raw files.
 * - Tables get a box that scrolls sideways on a phone.
 * - The result is run through wp_kses_post().
 *
 * @param string $html       GitHub's HTML.
 * @param array  $page       The page { path, html, raw }.
 * @param array  $index      The documentation index.
 * @param string $project_id Project slug.
 * @param bool   $is_en      English site.
 * @return string
 */
function rs_docs_prepare( $html, $page, $index, $project_id, $is_en ) {
	$section = $is_en ? 'Link to this section' : 'এই অংশের লিংক';

	$html = preg_replace_callback(
		'~<div class="markdown-heading"><h([1-6])[^>]*>(.*?)</h\1><a id="user-content-([^"]+)"[^>]*>.*?</a></div>~s',
		function ( $m ) use ( $section ) {
			return '<h' . $m[1] . ' id="' . esc_attr( $m[3] ) . '">' . $m[2] . '<a class="rs-doc__hash" href="#' . esc_attr( $m[3] ) . '" aria-label="' . esc_attr( $section ) . '">#</a></h' . $m[1] . '>';
		},
		$html
	);

	$html = preg_replace( '~</?markdown-accessiblity-table[^>]*>~', '', $html );
	$html = preg_replace( '~<table\b~', '<div class="rs-doc__table"><table', $html );
	$html = str_replace( '</table>', '</table></div>', $html );

	/* Which repository file is which page. */
	$by_path = array();
	foreach ( $index['pages'] as $key => $item ) {
		$by_path[ strtolower( $item['path'] ) ] = (string) $key;
	}

	/* The part of a blob address that comes before a path in the repository:
	   https://github.com/<owner>/<repo>/blob/<branch>/ */
	$blob_root = preg_match( '~^(https://github\.com/[^/]+/[^/]+/blob/[^/]+/)~i', $page['html'], $m ) ? $m[1] : '';

	$html = preg_replace_callback(
		'~\b(href|src)="([^"]*)"~',
		function ( $m ) use ( $page, $by_path, $blob_root, $project_id ) {
			$url = html_entity_decode( $m[2], ENT_QUOTES );

			if ( '' === $url || '#' === $url[0] || preg_match( '~^[a-z][a-z0-9+.-]*:~i', $url ) ) {
				return $m[0];
			}

			/* An image, and the link GitHub wraps around one: both to the
			   picture itself, not to GitHub's page for the file. */
			if ( 'src' === $m[1] || preg_match( '~\.(?:png|jpe?g|gif|webp|svg)$~i', (string) strtok( $url, '#?' ) ) ) {
				return $m[1] . '="' . esc_url( rs_docs_resolve( $page['raw'], $url, 3 ) ) . '"';
			}

			$to = rs_docs_resolve( $page['html'], $url, 4 );

			if ( $blob_root && 0 === stripos( $to, $blob_root ) ) {
				$tail = '';
				$path = substr( $to, strlen( $blob_root ) );
				$hash = strpos( $path, '#' );

				if ( false !== $hash ) {
					$tail = substr( $path, $hash );
					$path = substr( $path, 0, $hash );
				}

				if ( isset( $by_path[ strtolower( rawurldecode( $path ) ) ] ) ) {
					return 'href="' . esc_url( rs_project_docs_url( $project_id, $by_path[ strtolower( rawurldecode( $path ) ) ] ) . $tail ) . '"';
				}
			}

			return 'href="' . esc_url( $to ) . '"';
		},
		$html
	);

	/* Anything leaving the site opens beside it. */
	$home = preg_quote( untrailingslashit( network_home_url() ), '~' );
	$html = preg_replace( '~<a href="(?!#|' . $home . ')(https?://[^"]+)"~', '<a href="$1" target="_blank" rel="noopener noreferrer"', $html );
	$html = wp_kses_post( $html );

	/* Screenshots run to a few hundred kilobytes each: fetched as they come
	   into view, not all at once with the page. */
	return preg_replace( '~<img\b(?![^>]*\bloading=)~', '<img loading="lazy" decoding="async"', $html );
}

/**
 * The headings a page's "on this page" list is made of: its second-level
 * sections.
 *
 * @param string $html Prepared HTML.
 * @return array[] { id, text }
 */
function rs_docs_sections( $html ) {
	$out = array();

	if ( preg_match_all( '~<h2 id="([^"]+)">(.*?)<a class="rs-doc__hash"~s', $html, $found, PREG_SET_ORDER ) ) {
		foreach ( $found as $m ) {
			$text = trim( wp_strip_all_tags( $m[2] ) );

			if ( '' !== $text && 'contents' !== strtolower( $text ) ) {
				$out[] = array(
					'id'   => $m[1],
					'text' => $text,
				);
			}
		}
	}

	return $out;
}

/* =========================================================================
 * The addresses
 * ====================================================================== */

/**
 * /portfolio/<project>/documentation/ and a page under it.
 */
function rs_docs_rewrites() {
	add_rewrite_rule( '^portfolio/([^/]+)/documentation/?$', 'index.php?pagename=portfolio&rs_project=$matches[1]&rs_doc=index', 'top' );
	add_rewrite_rule( '^portfolio/([^/]+)/documentation/([^/]+)/?$', 'index.php?pagename=portfolio&rs_project=$matches[1]&rs_doc=$matches[2]', 'top' );
}
add_action( 'init', 'rs_docs_rewrites' );

/**
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function rs_docs_query_vars( $vars ) {
	$vars[] = 'rs_doc';
	return $vars;
}
add_filter( 'query_vars', 'rs_docs_query_vars' );

/**
 * The documentation page this request is for, worked out once.
 *
 * @return array|null { project, index, key, page } — page is null when the
 *                    project has documentation but no page by that name.
 */
function rs_current_doc() {
	static $found = null;

	if ( null !== $found ) {
		return $found ? $found : null;
	}

	$found = false;
	$doc   = (string) get_query_var( 'rs_doc' );

	if ( '' === $doc || ! did_action( 'wp' ) ) {
		return null;
	}

	$project = rs_current_project();

	if ( ! $project ) {
		return null;
	}

	$slug  = rs_github_repo_slug( isset( $project['github_url'] ) ? $project['github_url'] : '' );
	$index = $slug ? rs_docs_index( $slug ) : null;

	/* Asked for before the refresh has ever looked: look now, once. */
	if ( ! $index && $slug ) {
		$all  = rs_docs_store();
		$seen = isset( $all[ strtolower( $slug ) ]['checked'] );

		if ( ! $seen ) {
			$all[ strtolower( $slug ) ] = rs_docs_refresh( $slug, rs_github_request_args(), array() );
			update_site_option( 'rs_project_docs', $all );
			$index = rs_docs_index( $slug );
		}
	}

	$key   = 'index' === $doc ? '' : sanitize_title( $doc );
	$found = array(
		'project' => $project,
		'index'   => $index,
		'key'     => $key,
		'page'    => ( $index && isset( $index['pages'][ $key ] ) ) ? $index['pages'][ $key ] : null,
	);

	return $found;
}

/**
 * An address for documentation that is not there is a 404.
 */
function rs_docs_404() {
	if ( '' === (string) get_query_var( 'rs_doc' ) || ! rs_current_project() ) {
		return;
	}

	$doc = rs_current_doc();

	if ( ! $doc || ! $doc['page'] ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'rs_docs_404', 2 );

/**
 * The page's title and name for the browser tab: "The LazyLord manual —
 * LazyLord documentation — Raisul Sohan".
 *
 * @return array|null { page, docs } Titles, or null off the documentation.
 */
function rs_docs_titles() {
	$doc = rs_current_doc();

	if ( ! $doc || ! $doc['page'] ) {
		return null;
	}

	$name = rs_project_name( $doc['project'] );

	return array(
		'page' => $doc['page']['title'],
		'docs' => $name[0] . ( rs_is_en() ? ' documentation' : ' ডকুমেন্টেশন' ),
	);
}

/**
 * @param string $title Document title.
 * @return string
 */
function rs_docs_document_title( $title ) {
	$titles = rs_docs_titles();

	if ( ! $titles ) {
		return $title;
	}

	return ( $titles['page'] === $titles['docs'] || '' === rs_current_doc()['key'] ? $titles['docs'] : $titles['page'] . ' — ' . $titles['docs'] ) . ' — ' . rs_brand();
}
add_filter( 'pre_get_document_title', 'rs_docs_document_title', 30 );

/**
 * @param string $url Canonical URL.
 * @return string
 */
function rs_docs_canonical( $url ) {
	$doc = rs_current_doc();

	return ( $doc && $doc['page'] ) ? rs_project_docs_url( $doc['project']['id'], $doc['key'] ) : $url;
}
add_filter( 'get_canonical_url', 'rs_docs_canonical', 20 );

/**
 * The first paragraph of a page, as plain text, for a list of pages.
 *
 * @param array $page The page { sha, raw }.
 * @return string '' when there is none to be had.
 */
function rs_docs_lead( $page ) {
	$md = rs_docs_markdown( $page );

	if ( null === $md ) {
		return '';
	}

	$lead = array();

	foreach ( preg_split( '/\r?\n/', $md ) as $line ) {
		$line = trim( $line );

		if ( $lead && '' === $line ) {
			break;
		}

		/* Not a heading, picture, tag, table row, list item, quote, rule,
		   fence or a line that is only an aside in italics. */
		if ( '' === $line || preg_match( '~^(#|!\[|<|\||[-*+]\s|\d+\.\s|>|---|```|\*[^*]+\*$|_[^_]+_$)~', $line ) ) {
			if ( $lead ) {
				break;
			}
			continue;
		}

		$lead[] = $line;
	}

	$text = implode( ' ', $lead );
	$text = preg_replace( '~!?\[([^\]]*)\]\([^)]*\)~', '$1', $text );
	$text = str_replace( array( '**', '__', '`' ), '', $text );

	return function_exists( 'rs_shorten' ) ? rs_shorten( $text, 160 ) : $text;
}

/**
 * The home page made for a docs folder that has no README of its own: the
 * project's summary, then every page with its first paragraph.
 *
 * @param array $doc From rs_current_doc().
 * @return string
 */
function rs_docs_made_html( $doc ) {
	$is_en   = rs_is_en();
	$project = $doc['project'];
	$index   = $doc['index'];
	$name    = rs_project_name( $project );

	$html  = '<h1>' . esc_html( $name[0] . ( $is_en ? ' documentation' : ' ডকুমেন্টেশন' ) ) . '</h1>';
	$html .= '<p>' . esc_html( $is_en ? $project['summary_en'] : $project['summary_bn'] ) . '</p>';
	$html .= '<p>' . esc_html( $is_en ? "The pages below are the project's own documentation, read from its repository on GitHub." : 'নিচের পাতাগুলো প্রজেক্টের নিজের ডকুমেন্টেশন, GitHub-এর রিপোজিটরি থেকে পড়া।' ) . '</p>';
	$html .= '<ul class="rs-doc__index">';

	foreach ( (array) $index['order'] as $key ) {
		if ( '' === $key || ! isset( $index['pages'][ $key ] ) ) {
			continue;
		}

		$page = $index['pages'][ $key ];
		$lead = rs_docs_lead( $page );

		$html .= '<li><a href="' . esc_url( rs_project_docs_url( $project['id'], $key ) ) . '">';
		$html .= '<strong>' . esc_html( ! empty( $page['label'] ) ? $page['label'] : $page['title'] ) . '</strong>';
		$html .= '' !== $lead ? '<span>' . esc_html( $lead ) . '</span>' : '';
		$html .= '</a></li>';
	}

	return $html . '</ul>';
}

/**
 * The prepared HTML of the page this request is for, made once.
 *
 * @return string|null Null when GitHub could not be reached for a page that
 *                     has never been rendered.
 */
function rs_current_doc_html() {
	static $html = false;

	if ( false !== $html ) {
		return $html;
	}

	$html = null;
	$doc  = rs_current_doc();

	if ( $doc && $doc['page'] && ! empty( $doc['page']['made'] ) ) {
		$html = rs_docs_made_html( $doc );
	} elseif ( $doc && $doc['page'] ) {
		$raw = rs_docs_rendered( $doc['page'] );

		if ( null !== $raw ) {
			$html = rs_docs_prepare( $raw, $doc['page'], $doc['index'], $doc['project']['id'], rs_is_en() );
		}
	}

	return $html;
}
