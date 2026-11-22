<?php
/**
 * Dashboard › Work Log: the list of every piece on /work/, where a piece is
 * hidden, shown, edited or deleted, and where a new one is added by uploading
 * its folder as a ZIP (or a single HTML file).
 *
 * The same routes serve the screen and scripts/work-log-import.mjs, which
 * sends a whole folder of work from a computer with an Application Password:
 *
 * - GET    rs/v1/work-log               Every piece (theme and uploaded).
 * - POST   rs/v1/work-log/upload        One chunk of a file; chunks are
 *                                       joined under rs-work/.incoming/, so a
 *                                       piece of any size gets past PHP's
 *                                       upload limit.
 * - POST   rs/v1/work-log/items         Turn an uploaded file into a piece.
 * - POST   rs/v1/work-log/items/<id>    Edit a piece, or hide or show it.
 * - DELETE rs/v1/work-log/items/<id>    Delete an uploaded piece and its
 *                                       files; a theme piece is hidden.
 * - POST   rs/v1/work-log/bulk          Hide, show or delete several.
 *
 * Every route needs manage_options. An uploaded piece is unpacked into the
 * main site's uploads, rs-work/<id>/, with its own folder and file names (its
 * pages load their files by relative paths). Server-side code never comes
 * in: PHP and the like are dropped on the way, and the folder refuses to run
 * scripts on servers that read .htaccess.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Files that are never unpacked: anything a server could run. */
const RS_WORK_LOG_BLOCKED = '/(^|\/)\.ht|(^|\/)\.user\.ini$|\.(php\d*|phtml|phar|phps|pht|cgi|pl|py|sh|asp|aspx|jsp|exe|bat|cmd)$/i';

/** The largest file the routes take, joined from its chunks. */
const RS_WORK_LOG_MAX_BYTES = 3 * 1024 * 1024 * 1024;

/**
 * The screen's words in the dashboard's edition.
 *
 * @param string $bn Bengali.
 * @param string $en English.
 * @return string
 */
function rs_wla_t( $bn, $en ) {
	return rs_is_en() ? $en : $bn;
}

/** Everything the routes do needs this. */
function rs_wla_can() {
	return current_user_can( 'manage_options' );
}

/**
 * The folder for uploaded pieces, made on first use, with a guard file for
 * servers that read .htaccess.
 *
 * @return string|WP_Error The folder.
 */
function rs_wla_root() {
	$root = rs_work_log_files()['dir'];

	if ( ! wp_mkdir_p( $root . '/.incoming' ) ) {
		return new WP_Error( 'rs_wl_dir', rs_wla_t( 'আপলোডের ফোল্ডার বানানো গেল না।', 'Could not create the upload folder.' ), array( 'status' => 500 ) );
	}

	$guard = $root . '/.htaccess';
	if ( ! file_exists( $guard ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $guard, "# Written by the Raisul Sohan theme: pieces of work are static files.\n<IfModule mod_php.c>\nphp_flag engine off\n</IfModule>\n<FilesMatch \"\\.(php\\d*|phtml|phar|phps|pht)$\">\nRequire all denied\n</FilesMatch>\nOptions -ExecCGI -Indexes\n" );
	}
	$inbox = $root . '/.incoming/.htaccess';
	if ( ! file_exists( $inbox ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $inbox, "Require all denied\n" );
	}

	return $root;
}

/**
 * An upload token: letters and digits only, so it is safe as a file name.
 *
 * @param string $token As sent.
 * @return string Empty when it is not a token.
 */
function rs_wla_token( $token ) {
	$token = (string) $token;
	return preg_match( '/^[A-Za-z0-9]{16,64}$/', $token ) ? $token : '';
}

/**
 * A path inside a piece, as it may be used: forward slashes, no leading
 * slash, no "." or ".." parts, nothing blocked.
 *
 * @param string $path As written in the ZIP or the form.
 * @return string Empty when it may not be used.
 */
function rs_wla_clean_path( $path ) {
	$path  = str_replace( '\\', '/', (string) $path );
	$parts = array();
	foreach ( explode( '/', $path ) as $part ) {
		if ( '' === $part || '.' === $part ) {
			continue;
		}
		if ( '..' === $part || false !== strpos( $part, "\0" ) ) {
			return '';
		}
		$parts[] = $part;
	}
	$path = implode( '/', $parts );

	return '' === $path || preg_match( RS_WORK_LOG_BLOCKED, $path ) ? '' : $path;
}

/**
 * Remove a folder and everything in it, only ever under rs-work/.
 *
 * @param string $dir The folder.
 */
function rs_wla_rmdir( $dir ) {
	$root = realpath( rs_work_log_files()['dir'] );
	$real = realpath( $dir );
	if ( ! $root || ! $real || 0 !== strpos( $real, $root . DIRECTORY_SEPARATOR ) || ! is_dir( $real ) ) {
		return;
	}

	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $real, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $it as $f ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
	rmdir( $real );
}

/**
 * A poster drawn from the title, for a piece that brings none: the page's
 * black, the orange rule, the kind and the title.
 *
 * @param string $title The piece's title.
 * @param string $label The line above it.
 * @param string $date  Y-m-d.
 * @return string SVG.
 */
function rs_wla_poster_svg( $title, $label, $date ) {
	$words = preg_split( '/\s+/u', trim( $title ) );
	$lines = array( '' );
	foreach ( $words as $w ) {
		$try = trim( end( $lines ) . ' ' . $w );
		if ( mb_strlen( $try ) > 22 && '' !== end( $lines ) ) {
			$lines[] = $w;
		} else {
			$lines[ count( $lines ) - 1 ] = $try;
		}
	}
	$lines = array_slice( $lines, 0, 3 );
	$y     = 400 - ( count( $lines ) - 1 ) * 46;
	$text  = '';
	foreach ( $lines as $i => $line ) {
		$text .= '<text x="96" y="' . ( $y + $i * 92 ) . '" font-size="84" font-weight="800" fill="#f5f5f7">' . esc_html( $line ) . '</text>';
	}

	return '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">'
		. '<defs><radialGradient id="g" cx="85%" cy="10%" r="90%"><stop offset="0" stop-color="#ff4a1c" stop-opacity=".35"/><stop offset=".6" stop-color="#ff4a1c" stop-opacity="0"/></radialGradient></defs>'
		. '<rect width="1280" height="720" fill="#0b0a0d"/><rect width="1280" height="720" fill="url(#g)"/>'
		. '<g font-family="Inter, \'Anek Bangla\', \'Noto Sans Bengali\', system-ui, sans-serif">'
		. '<rect x="96" y="' . ( $y - 150 ) . '" width="72" height="8" rx="4" fill="#ff4a1c"/>'
		. '<text x="96" y="' . ( $y - 96 ) . '" font-size="34" font-weight="700" fill="#ff4a1c">' . esc_html( $label ) . '</text>'
		. $text
		. '<text x="96" y="640" font-size="30" font-weight="600" fill="#86868b">' . esc_html( $date ) . '</text>'
		. '</g></svg>';
}

/**
 * The page that plays a video piece: the video filling the frame, with its
 * sound when the visitor opened it (muted when the browser will not allow
 * that, as in the list's silent preview).
 *
 * @param string $video  The video's file name, beside the page.
 * @param string $poster Its poster's file name, or ''.
 * @param string $title  The piece's title.
 * @return string
 */
function rs_wla_player_html( $video, $poster = '', $title = '' ) {
	$src = implode( '/', array_map( 'rawurlencode', explode( '/', $video ) ) );
	$pos = $poster ? ' poster="' . esc_attr( implode( '/', array_map( 'rawurlencode', explode( '/', $poster ) ) ) ) . '"' : '';
	return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html( $title ) . '</title>'
		. '<style>html,body{margin:0;height:100%;background:#000}video{display:block;width:100%;height:100%;object-fit:contain;background:#000}</style></head><body>'
		. '<video src="' . esc_attr( $src ) . '"' . $pos . ' controls autoplay playsinline preload="metadata"></video>'
		. '<script>var v=document.querySelector("video");var p=v.play();if(p&&p.catch){p.catch(function(){v.muted=true;v.play();});}</script>'
		. '</body></html>';
}

/**
 * Unpack an uploaded file into a piece's folder.
 *
 * A ZIP keeps its paths; when everything in it sits in one top folder, that
 * folder is dropped. A single HTML file is copied as it is; a single video
 * (mp4, webm, m4v, mov) is copied with a page that plays it (index.html).
 *
 * @param string $file   The joined upload.
 * @param string $name   Its original file name.
 * @param string $target The piece's folder (made here).
 * @return array{files: string[], bytes: int}|WP_Error
 */
function rs_wla_unpack( $file, $name, $target ) {
	if ( ! wp_mkdir_p( $target ) ) {
		return new WP_Error( 'rs_wl_dir', rs_wla_t( 'কাজের ফোল্ডার বানানো গেল না।', 'Could not create the folder.' ), array( 'status' => 500 ) );
	}

	$files = array();
	$bytes = 0;

	if ( preg_match( '/\.html?$/i', $name ) ) {
		$base = rs_wla_clean_path( sanitize_file_name( wp_basename( $name ) ) );
		if ( '' === $base ) {
			return new WP_Error( 'rs_wl_name', rs_wla_t( 'ফাইলের নামটা চলবে না।', 'That file name cannot be used.' ), array( 'status' => 400 ) );
		}
		copy( $file, $target . '/' . $base );
		return array(
			'files' => array( $base ),
			'bytes' => (int) filesize( $file ),
		);
	}

	if ( preg_match( '/\.(mp4|webm|m4v|mov)$/i', $name ) ) {
		$base = rs_wla_clean_path( sanitize_file_name( wp_basename( $name ) ) );
		if ( '' === $base || 'index.html' === strtolower( $base ) ) {
			$base = 'video.mp4';
		}
		copy( $file, $target . '/' . $base );
		file_put_contents( $target . '/index.html', rs_wla_player_html( $base ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return array(
			'files' => array( 'index.html', $base ),
			'bytes' => (int) filesize( $file ),
			'video' => true,
		);
	}

	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'rs_wl_zip', rs_wla_t( 'সার্ভারে ZipArchive নেই, তাই zip খোলা যাচ্ছে না।', 'The server has no ZipArchive, so a ZIP cannot be opened.' ), array( 'status' => 500 ) );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $file ) ) {
		return new WP_Error( 'rs_wl_zip', rs_wla_t( 'zip ফাইলটা খোলা গেল না।', 'The ZIP could not be opened.' ), array( 'status' => 400 ) );
	}

	/* The paths that may be unpacked, and their one shared top folder, if any. */
	$take = array();
	$tops = array();
	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$raw = $zip->getNameIndex( $i );
		if ( '/' === substr( str_replace( '\\', '/', $raw ), -1 ) || 0 === strpos( $raw, '__MACOSX' ) ) {
			continue;
		}
		$path = rs_wla_clean_path( $raw );
		if ( '' === $path ) {
			continue;
		}
		$take[ $i ]                         = $path;
		$tops[ strtok( $path, '/' ) ]       = true;
	}
	$strip = '';
	if ( 1 === count( $tops ) ) {
		$top = array_key_first( $tops );
		$all = true;
		foreach ( $take as $path ) {
			if ( 0 !== strpos( $path, $top . '/' ) ) {
				$all = false;
				break;
			}
		}
		$strip = $all ? $top . '/' : '';
	}

	foreach ( $take as $i => $path ) {
		$rel = $strip ? substr( $path, strlen( $strip ) ) : $path;
		$out = $target . '/' . $rel;
		wp_mkdir_p( dirname( $out ) );
		$in = $zip->getStream( $zip->getNameIndex( $i ) );
		if ( ! $in ) {
			continue;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$fh = fopen( $out, 'wb' );
		if ( $fh ) {
			$bytes += (int) stream_copy_to_stream( $in, $fh );
			fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			$files[] = $rel;
		}
		fclose( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	}
	$zip->close();

	if ( ! $files ) {
		return new WP_Error( 'rs_wl_empty', rs_wla_t( 'zip-এ কাজে লাগার মতো কোনো ফাইল নেই।', 'The ZIP holds no usable file.' ), array( 'status' => 400 ) );
	}

	return array(
		'files' => $files,
		'bytes' => $bytes,
	);
}

/**
 * The page a piece opens on, when none was named: index.html, then
 * episode.html, then the first HTML page nearest the top.
 *
 * @param string[] $files The piece's files.
 * @return string
 */
function rs_wla_pick_entry( $files ) {
	$html = array_values(
		array_filter(
			$files,
			function ( $f ) {
				return (bool) preg_match( '/\.html?$/i', $f );
			}
		)
	);
	foreach ( array( 'index.html', 'episode.html' ) as $want ) {
		if ( in_array( $want, $html, true ) ) {
			return $want;
		}
	}
	usort(
		$html,
		function ( $a, $b ) {
			return substr_count( $a, '/' ) - substr_count( $b, '/' ) ?: strnatcasecmp( $a, $b );
		}
	);

	return $html ? $html[0] : '';
}

/**
 * The fields a piece can be given or changed, cleaned.
 *
 * @param array $in As sent.
 * @return array
 */
function rs_wla_meta( $in ) {
	$out = array();
	foreach ( array( 'title', 'title_bn', 'type_bn', 'type_en' ) as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = sanitize_text_field( wp_unslash( $in[ $k ] ) );
		}
	}
	foreach ( array( 'desc_bn', 'desc_en' ) as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = sanitize_textarea_field( wp_unslash( $in[ $k ] ) );
		}
	}
	if ( isset( $in['date'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $in['date'] ) ) {
		$out['date'] = $in['date'];
	}
	if ( isset( $in['kind'] ) && array_key_exists( $in['kind'], rs_work_log_kinds() ) ) {
		$out['kind'] = $in['kind'];
	}
	if ( isset( $in['client'] ) ) {
		$out['client'] = rest_sanitize_boolean( $in['client'] );
	}

	return $out;
}

/**
 * A free id from a title: lower case, hyphens, made unique.
 *
 * @param string $want  The id or title asked for.
 * @param array  $taken Ids in use.
 * @return string
 */
function rs_wla_new_id( $want, $taken ) {
	/* Latin letters, digits and hyphens only: the id is part of a route and an address. */
	$base = preg_replace( '/%[0-9a-f]{2}/i', '', sanitize_title( $want ) );
	$base = trim( preg_replace( '/[^a-z0-9]+/', '-', $base ), '-' );
	$base = '' !== $base ? rtrim( substr( $base, 0, 60 ), '-' ) : 'work';
	$id   = $base;
	for ( $n = 2; isset( $taken[ $id ] ); $n++ ) {
		$id = $base . '-' . $n;
	}

	return $id;
}

/** Every id in use, the theme's and the store's. */
function rs_wla_taken() {
	$taken = array();
	foreach ( rs_work_log_all() as $it ) {
		$taken[ $it['id'] ] = true;
	}

	return $taken;
}

/**
 * Turn an uploaded file into a piece.
 *
 * @param array $req token, name, title, title_bn, date, kind, client, desc_bn,
 *                   desc_en, entry, entry45,
 *                   clips [{title, title_bn, entry, poster}], poster, video,
 *                   poster_token, poster_name, origin, hidden, replace.
 * @return array|WP_Error The stored record.
 */
function rs_work_log_import( $req ) {
	$root = rs_wla_root();
	if ( is_wp_error( $root ) ) {
		return $root;
	}

	$token = rs_wla_token( isset( $req['token'] ) ? $req['token'] : '' );
	$part  = $root . '/.incoming/' . $token . '.part';
	if ( ! $token || ! file_exists( $part ) ) {
		return new WP_Error( 'rs_wl_upload', rs_wla_t( 'আপলোড করা ফাইলটা পাওয়া গেল না।', 'The uploaded file was not found.' ), array( 'status' => 400 ) );
	}

	$meta = rs_wla_meta( $req );
	if ( empty( $meta['title'] ) ) {
		return new WP_Error( 'rs_wl_title', rs_wla_t( 'কাজের একটা নাম দিন।', 'Give the piece a title.' ), array( 'status' => 400 ) );
	}

	$store  = rs_work_log_store();
	$origin = isset( $req['origin'] ) ? sanitize_text_field( wp_unslash( $req['origin'] ) ) : '';

	/* The same folder sent again replaces the piece it made, under its id. */
	$id = '';
	if ( $origin ) {
		foreach ( $store['items'] as $old ) {
			if ( isset( $old['origin'] ) && $old['origin'] === $origin ) {
				if ( empty( $req['replace'] ) ) {
					unlink( $part ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
					return $old;
				}
				$id = $old['id'];
				rs_wla_rmdir( $root . '/' . $old['dir'] );
			}
		}
	}
	if ( ! $id ) {
		$id = rs_wla_new_id( ! empty( $req['id'] ) ? $req['id'] : $meta['title'], rs_wla_taken() );
	}

	$target = $root . '/' . $id;
	$name   = isset( $req['name'] ) ? (string) $req['name'] : 'work.zip';
	$got    = rs_wla_unpack( $part, $name, $target );
	unlink( $part ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
	if ( is_wp_error( $got ) ) {
		rs_wla_rmdir( $target );
		return $got;
	}

	$has = array_flip( $got['files'] );
	$pick = function ( $key ) use ( $req, $has ) {
		$p = isset( $req[ $key ] ) ? rs_wla_clean_path( $req[ $key ] ) : '';
		return $p && isset( $has[ $p ] ) ? $p : '';
	};

	$entry = $pick( 'entry' );
	$entry = $entry ? $entry : rs_wla_pick_entry( $got['files'] );
	if ( ! $entry ) {
		rs_wla_rmdir( $target );
		return new WP_Error( 'rs_wl_entry', rs_wla_t( 'ফাইলে কোনো html পেজ নেই।', 'There is no HTML page in the upload.' ), array( 'status' => 400 ) );
	}

	$kinds = rs_work_log_kinds();
	$kind  = isset( $meta['kind'] ) ? $meta['kind'] : 'anim';
	$date  = isset( $meta['date'] ) ? $meta['date'] : current_time( 'Y-m-d' );
	$label = rs_is_en() ? $kinds[ $kind ]['en'] : $kinds[ $kind ]['bn'];

	/* The poster: its own upload, a file named in the piece, or one drawn. */
	$poster = $pick( 'poster' );
	$ptoken = rs_wla_token( isset( $req['poster_token'] ) ? $req['poster_token'] : '' );
	$pname  = isset( $req['poster_name'] ) ? (string) $req['poster_name'] : '';
	if ( $ptoken && file_exists( $root . '/.incoming/' . $ptoken . '.part' ) && preg_match( '/\.(jpe?g|png|webp|gif|svg)$/i', $pname, $m ) ) {
		$poster = '_poster.' . strtolower( $m[1] );
		rename( $root . '/.incoming/' . $ptoken . '.part', $target . '/' . $poster );
	}
	if ( ! $poster ) {
		$poster = '_poster.svg';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $target . '/' . $poster, rs_wla_poster_svg( $meta['title'], $label, $date ) );
	}

	$clips = array();
	if ( ! empty( $req['clips'] ) && is_array( $req['clips'] ) ) {
		foreach ( array_values( $req['clips'] ) as $n => $c ) {
			$ce = isset( $c['entry'] ) ? rs_wla_clean_path( $c['entry'] ) : '';
			if ( ! $ce || ! isset( $has[ $ce ] ) ) {
				continue;
			}
			$ct = isset( $c['title'] ) && '' !== trim( $c['title'] ) ? sanitize_text_field( $c['title'] ) : preg_replace( '/\.html?$/i', '', wp_basename( $ce ) );
			$cp = isset( $c['poster'] ) ? rs_wla_clean_path( $c['poster'] ) : '';
			if ( ! $cp || ! isset( $has[ $cp ] ) ) {
				$cp = '_poster-' . ( $n + 1 ) . '.svg';
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( $target . '/' . $cp, rs_wla_poster_svg( $ct, $meta['title'], $date ) );
			}
			$clips[] = array(
				'title'  => $ct,
				'title_bn' => isset( $c['title_bn'] ) ? sanitize_text_field( $c['title_bn'] ) : '',
				'entry'  => $ce,
				'poster' => $cp,
			);
		}
	}

	$item = array(
		'id'      => $id,
		'date'    => $date,
		'kind'    => $kind,
		'title'   => $meta['title'],
		'title_bn' => isset( $meta['title_bn'] ) ? $meta['title_bn'] : '',
		'client'  => ! empty( $meta['client'] ),
		'type_bn' => isset( $meta['type_bn'] ) ? $meta['type_bn'] : '',
		'type_en' => isset( $meta['type_en'] ) ? $meta['type_en'] : '',
		'desc_bn' => isset( $meta['desc_bn'] ) ? $meta['desc_bn'] : '',
		'desc_en' => isset( $meta['desc_en'] ) ? $meta['desc_en'] : '',
		'dir'     => $id,
		'entry'   => $entry,
		'entry45' => $pick( 'entry45' ),
		'poster'  => $poster,
		'clips'   => $clips,
		'bytes'   => $got['bytes'],
		'files'   => count( $got['files'] ),
		'origin'  => $origin,
		'added'   => time(),
		'video'   => ! empty( $got['video'] ) || ! empty( $req['video'] ),
	);

	$store                 = rs_work_log_store();
	$store['items'][ $id ] = $item;
	if ( ! empty( $req['hidden'] ) ) {
		$store['hidden'][ $id ] = true;
	} else {
		unset( $store['hidden'][ $id ] );
	}
	rs_work_log_store_save( $store );

	return $item;
}

/**
 * One piece as the screen and the import script see it.
 *
 * @param array $it From rs_work_log_all().
 * @return array
 */
function rs_wla_row( $it ) {
	$store = rs_work_log_store();
	$raw   = isset( $store['items'][ $it['id'] ] ) ? $store['items'][ $it['id'] ] : array();
	$abs   = function ( $p ) {
		return preg_match( '#^https?://#', $p ) ? $p : RS_URI . '/' . ltrim( $p, '/' );
	};

	return array(
		'id'      => $it['id'],
		'date'    => $it['date'],
		'kind'    => $it['kind'],
		'title'   => $it['title'],
		'title_bn' => isset( $it['title_bn'] ) ? $it['title_bn'] : '',
		'client'  => ! empty( $it['client'] ),
		'desc_bn' => $it['desc_bn'],
		'desc_en' => $it['desc_en'],
		'img'     => $abs( $it['img'] ),
		'src'     => $abs( $it['src'] ),
		'clips'   => isset( $it['clips'] ) ? count( $it['clips'] ) : 0,
		'hidden'  => ! empty( $it['hidden'] ),
		'source'  => $it['source'],
		'bytes'   => isset( $raw['bytes'] ) ? (int) $raw['bytes'] : 0,
		'origin'  => isset( $raw['origin'] ) ? $raw['origin'] : '',
	);
}

/* ── routes ─────────────────────────────────────────────────────────────── */

function rs_work_log_routes() {
	$can = 'rs_wla_can';

	register_rest_route(
		'rs/v1',
		'/work-log',
		array(
			'methods'             => 'GET',
			'permission_callback' => $can,
			'callback'            => function () {
				return rest_ensure_response( array_map( 'rs_wla_row', rs_work_log_all() ) );
			},
		)
	);

	register_rest_route(
		'rs/v1',
		'/work-log/upload',
		array(
			'methods'             => 'POST',
			'permission_callback' => $can,
			'callback'            => 'rs_work_log_upload_chunk',
		)
	);

	register_rest_route(
		'rs/v1',
		'/work-log/items',
		array(
			'methods'             => 'POST',
			'permission_callback' => $can,
			'callback'            => function ( WP_REST_Request $r ) {
				$req = $r->get_json_params();
				$req = is_array( $req ) && $req ? $req : $r->get_params();
				$it  = rs_work_log_import( $req );
				if ( is_wp_error( $it ) ) {
					return $it;
				}
				foreach ( rs_work_log_all() as $row ) {
					if ( $row['id'] === $it['id'] ) {
						return rest_ensure_response( rs_wla_row( $row ) );
					}
				}
				return rest_ensure_response( $it );
			},
		)
	);

	register_rest_route(
		'rs/v1',
		'/work-log/items/(?P<id>[a-z0-9-]+)',
		array(
			array(
				'methods'             => 'POST',
				'permission_callback' => $can,
				'callback'            => 'rs_work_log_update_item',
			),
			array(
				'methods'             => 'DELETE',
				'permission_callback' => $can,
				'callback'            => function ( WP_REST_Request $r ) {
					rs_work_log_change( array( $r['id'] ), 'delete' );
					return rest_ensure_response( array( 'deleted' => $r['id'] ) );
				},
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/work-log/bulk',
		array(
			'methods'             => 'POST',
			'permission_callback' => $can,
			'callback'            => function ( WP_REST_Request $r ) {
				$ids    = array_map( 'sanitize_key', (array) $r->get_param( 'ids' ) );
				$action = (string) $r->get_param( 'action' );
				if ( ! in_array( $action, array( 'hide', 'show', 'delete' ), true ) ) {
					return new WP_Error( 'rs_wl_action', 'Unknown action.', array( 'status' => 400 ) );
				}
				return rest_ensure_response( array( 'changed' => rs_work_log_change( $ids, $action ) ) );
			},
		)
	);
}
add_action( 'rest_api_init', 'rs_work_log_routes' );

/**
 * Take one chunk and add it to its file. A chunk sent at the wrong offset
 * is refused with the size so far, so a sender can carry on from there.
 *
 * @param WP_REST_Request $r token, offset, total; the chunk as the file "chunk".
 * @return WP_REST_Response|WP_Error
 */
function rs_work_log_upload_chunk( WP_REST_Request $r ) {
	$root = rs_wla_root();
	if ( is_wp_error( $root ) ) {
		return $root;
	}

	$token  = rs_wla_token( $r->get_param( 'token' ) );
	$offset = (int) $r->get_param( 'offset' );
	$total  = (int) $r->get_param( 'total' );
	$files  = $r->get_file_params();
	if ( ! $token || empty( $files['chunk']['tmp_name'] ) || ! is_uploaded_file( $files['chunk']['tmp_name'] ) ) {
		return new WP_Error( 'rs_wl_chunk', rs_wla_t( 'অংশটা পৌঁছায়নি।', 'The chunk did not arrive.' ), array( 'status' => 400 ) );
	}
	if ( $total <= 0 || $total > RS_WORK_LOG_MAX_BYTES ) {
		return new WP_Error( 'rs_wl_size', rs_wla_t( 'ফাইলটা খুব বড়।', 'The file is too large.' ), array( 'status' => 413 ) );
	}

	$part = $root . '/.incoming/' . $token . '.part';
	clearstatcache( true, $part );
	$have = file_exists( $part ) ? (int) filesize( $part ) : 0;
	if ( $offset !== $have ) {
		return new WP_REST_Response(
			array(
				'received' => $have,
				'resend'   => true,
			),
			409
		);
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	$out = fopen( $part, 'ab' );
	$in  = fopen( $files['chunk']['tmp_name'], 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	stream_copy_to_stream( $in, $out );
	fclose( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	clearstatcache( true, $part );

	/* Leftovers of uploads that never finished go after a day. */
	foreach ( glob( $root . '/.incoming/*.part' ) as $old ) {
		if ( filemtime( $old ) < time() - DAY_IN_SECONDS ) {
			unlink( $old ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		}
	}

	return rest_ensure_response( array( 'received' => (int) filesize( $part ) ) );
}

/**
 * Edit a piece: the fields of an uploaded one, and hidden for any.
 *
 * @param WP_REST_Request $r The piece's id and the fields.
 * @return WP_REST_Response|WP_Error
 */
function rs_work_log_update_item( WP_REST_Request $r ) {
	$id    = $r['id'];
	$store = rs_work_log_store();
	$found = null;
	foreach ( rs_work_log_all() as $row ) {
		if ( $row['id'] === $id ) {
			$found = $row;
		}
	}
	if ( ! $found ) {
		return new WP_Error( 'rs_wl_missing', rs_wla_t( 'কাজটা পাওয়া গেল না।', 'The piece was not found.' ), array( 'status' => 404 ) );
	}

	$params = $r->get_json_params();
	$params = is_array( $params ) && $params ? $params : $r->get_params();

	if ( isset( $store['items'][ $id ] ) ) {
		$item = array_merge( $store['items'][ $id ], rs_wla_meta( $params ) );
		if ( ! empty( $params['clips'] ) && is_array( $params['clips'] ) ) {
			$item['clips'] = rs_wla_clip_names( $item['clips'], $params['clips'] );
		}
		$poster = rs_wla_new_poster( $item, $params );
		if ( $poster ) {
			$item['poster'] = $poster;
		}
		$store['items'][ $id ] = $item;

		/* A new address for the piece; its folder stays where it is. */
		if ( ! empty( $params['slug'] ) ) {
			$taken = rs_wla_taken();
			unset( $taken[ $id ] );
			$new = rs_wla_new_id( (string) $params['slug'], $taken );
			if ( $new !== $id ) {
				$item['id'] = $new;
				unset( $store['items'][ $id ] );
				$store['items'][ $new ] = $item;
				if ( isset( $store['hidden'][ $id ] ) ) {
					unset( $store['hidden'][ $id ] );
					$store['hidden'][ $new ] = true;
				}
				$id = $new;
			}
		}
	}
	if ( isset( $params['hidden'] ) ) {
		if ( rest_sanitize_boolean( $params['hidden'] ) ) {
			$store['hidden'][ $id ] = true;
		} else {
			unset( $store['hidden'][ $id ] );
		}
	}
	rs_work_log_store_save( $store );

	foreach ( rs_work_log_all() as $row ) {
		if ( $row['id'] === $id ) {
			return rest_ensure_response( rs_wla_row( $row ) );
		}
	}

	return rest_ensure_response( array( 'id' => $id ) );
}

/**
 * New names for a piece's clips; each is told by the page it plays.
 *
 * @param array $clips The stored clips.
 * @param array $names [{entry, title, title_bn}].
 * @return array
 */
function rs_wla_clip_names( $clips, $names ) {
	$by = array();
	foreach ( $names as $n ) {
		if ( is_array( $n ) && ! empty( $n['entry'] ) ) {
			$by[ rs_wla_clean_path( $n['entry'] ) ] = $n;
		}
	}
	$clips = (array) $clips;
	foreach ( $clips as $i => $c ) {
		if ( ! isset( $by[ $c['entry'] ] ) ) {
			continue;
		}
		foreach ( array( 'title', 'title_bn' ) as $k ) {
			$v = isset( $by[ $c['entry'] ][ $k ] ) ? sanitize_text_field( $by[ $c['entry'] ][ $k ] ) : '';
			if ( 'title' !== $k || '' !== $v ) {
				$clips[ $i ][ $k ] = $v;
			}
		}
	}

	return $clips;
}

/**
 * A new poster for a stored piece: a picture sent on its own, or one that is
 * already in the piece's folder.
 *
 * @param array $item   The stored piece.
 * @param array $params poster_token and poster_name, or poster.
 * @return string Its path in the piece's folder, or ''.
 */
function rs_wla_new_poster( $item, $params ) {
	$dir   = rs_work_log_files()['dir'];
	$to    = $dir . '/' . $item['dir'];
	$token = rs_wla_token( isset( $params['poster_token'] ) ? $params['poster_token'] : '' );
	$part  = $dir . '/.incoming/' . $token . '.part';
	$name  = isset( $params['poster_name'] ) ? (string) $params['poster_name'] : '';

	if ( $token && file_exists( $part ) ) {
		if ( ! preg_match( '/\.(jpe?g|png|webp)$/i', $name, $m ) || ! is_dir( $to ) ) {
			unlink( $part ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			return '';
		}
		/* Named after its contents, so no cache keeps an older picture. */
		$file = 'cover-' . substr( md5_file( $part ), 0, 10 ) . '.' . strtolower( $m[1] );
		return rename( $part, $to . '/' . $file ) ? $file : '';
	}

	$own = isset( $params['poster'] ) ? rs_wla_clean_path( $params['poster'] ) : '';
	return $own && preg_match( '/\.(jpe?g|png|webp|gif|svg)$/i', $own ) && is_file( $to . '/' . $own ) ? $own : '';
}

/**
 * Hide, show or delete pieces. A theme piece cannot be deleted, so delete
 * hides it.
 *
 * @param string[] $ids    The pieces.
 * @param string   $action hide, show or delete.
 * @return string[] The ids changed.
 */
function rs_work_log_change( $ids, $action ) {
	$store   = rs_work_log_store();
	$root    = rs_work_log_files()['dir'];
	$changed = array();

	foreach ( $ids as $id ) {
		if ( 'show' === $action ) {
			unset( $store['hidden'][ $id ] );
		} elseif ( 'delete' === $action && isset( $store['items'][ $id ] ) ) {
			rs_wla_rmdir( $root . '/' . $store['items'][ $id ]['dir'] );
			unset( $store['items'][ $id ], $store['hidden'][ $id ] );
		} else {
			$store['hidden'][ $id ] = true;
		}
		$changed[] = $id;
	}
	rs_work_log_store_save( $store );

	return $changed;
}

/* ── the screen ─────────────────────────────────────────────────────────── */

function rs_work_log_menu() {
	add_menu_page(
		rs_wla_t( 'কাজের খাতা', 'Work Log' ),
		rs_wla_t( 'কাজের খাতা', 'Work Log' ),
		'manage_options',
		'rs-work-log',
		'rs_work_log_screen',
		'dashicons-video-alt3',
		4
	);
}
add_action( 'admin_menu', 'rs_work_log_menu' );

/**
 * What the scripts need, on the dashboard's screen and on the page itself
 * (inc/25-work-log-manage.php): the routes, the nonce, the chunk size and
 * the words.
 *
 * @return array
 */
function rs_work_log_admin_config() {
	$limit = (int) wp_max_upload_size();

	return array(
		'api'   => esc_url_raw( rest_url( 'rs/v1/work-log' ) ),
		'nonce' => wp_create_nonce( 'wp_rest' ),
		/* Each chunk well under the server's upload limit. */
		'chunk' => max( 256 * 1024, min( 8 * MB_IN_BYTES, (int) floor( $limit * 0.8 ) ) ),
		't'     => array(
			'uploading' => rs_wla_t( 'আপলোড হচ্ছে', 'Uploading' ),
			'unpacking' => rs_wla_t( 'খোলা হচ্ছে…', 'Unpacking…' ),
			'added'     => rs_wla_t( 'যোগ হয়েছে। পেজটা আবার লোড হচ্ছে…', 'Added. Reloading…' ),
			'pickFile'  => rs_wla_t( 'একটা zip বা html ফাইল বাছুন।', 'Choose a ZIP or HTML file.' ),
			'confirm'   => rs_wla_t( 'বাছাই করা কাজগুলো মুছে ফেলবেন? আপলোড করা ফাইলও মুছে যাবে।', 'Delete the selected pieces? Their uploaded files go too.' ),
			'hide'      => rs_wla_t( 'লুকান', 'Hide' ),
			'show'      => rs_wla_t( 'দেখান', 'Show' ),
			'shown'     => rs_wla_t( 'দেখা যাচ্ছে', 'Shown' ),
			'hidden'    => rs_wla_t( 'লুকানো', 'Hidden' ),
			'saved'     => rs_wla_t( 'সেভ হয়েছে', 'Saved' ),
			'error'     => rs_wla_t( 'হলো না:', 'Failed:' ),
			'edit'      => rs_wla_t( 'বদলান', 'Edit' ),
			'del'       => rs_wla_t( 'মুছুন', 'Delete' ),
			'undo'      => rs_wla_t( 'ফেরান', 'Undo' ),
			'theme'     => rs_wla_t( 'থিমের সাথে আসা', 'Comes with the theme' ),
			'clips'     => rs_wla_t( 'টি ক্লিপ', ' clips' ),
			'didHide'   => rs_wla_t( 'লুকানো হয়েছে:', 'Hidden:' ),
			'didShow'   => rs_wla_t( 'পেজে দেখানো হচ্ছে:', 'Now shown:' ),
			'didDel'    => rs_wla_t( 'মুছে ফেলা হয়েছে:', 'Deleted:' ),
		),
	);
}

/**
 * The screen's own stylesheet and script, on the screen alone.
 *
 * @param string $hook The admin page being drawn.
 */
function rs_work_log_admin_assets( $hook ) {
	if ( 'toplevel_page_rs-work-log' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'rs-work-log-admin', RS_URI . '/assets/work-log-admin.min.css', array(), RS_VERSION );
	wp_enqueue_script( 'rs-work-log-api', RS_URI . '/assets/work-log-api.min.js', array(), RS_VERSION, true );
	wp_enqueue_script( 'rs-work-log-admin', RS_URI . '/assets/work-log-admin.min.js', array( 'rs-work-log-api' ), RS_VERSION, true );
	wp_add_inline_script( 'rs-work-log-api', 'window.RS_WL=' . wp_json_encode( rs_work_log_admin_config() ) . ';', 'before' );
}
add_action( 'admin_enqueue_scripts', 'rs_work_log_admin_assets' );

/**
 * A size for people: 12.3 MB.
 *
 * @param int $bytes Bytes.
 * @return string
 */
function rs_wla_size( $bytes ) {
	if ( $bytes <= 0 ) {
		return '';
	}

	return rs_bn_digits( size_format( $bytes, $bytes >= GB_IN_BYTES ? 2 : 1 ) );
}

/** Dashboard › Work Log. */
function rs_work_log_screen() {
	if ( ! rs_wla_can() ) {
		return;
	}

	$rows   = array_map( 'rs_wla_row', rs_work_log_all() );
	$kinds  = rs_work_log_kinds();
	$en     = rs_is_en();
	$hidden = count( array_filter( wp_list_pluck( $rows, 'hidden' ) ) );
	usort(
		$rows,
		function ( $a, $b ) {
			return strcmp( $b['date'], $a['date'] ) ?: strcmp( $a['title'], $b['title'] );
		}
	);
	$kind_label = function ( $k ) use ( $kinds, $en ) {
		return isset( $kinds[ $k ] ) ? ( $en ? $kinds[ $k ]['en'] : $kinds[ $k ]['bn'] ) : $k;
	};
	?>
	<div class="wrap rs-wl">
		<h1><?php echo esc_html( rs_wla_t( 'কাজের খাতা', 'Work Log' ) ); ?></h1>
		<p class="rs-wl-lede">
			<?php
			echo esc_html(
				$en
					? sprintf( '%d pieces, %d of them shown on the page and %d hidden.', count( $rows ), count( $rows ) - $hidden, $hidden )
					: sprintf( 'মোট %sটি কাজ: পেজে দেখা যাচ্ছে %sটি, লুকানো %sটি।', rs_bn_digits( count( $rows ) ), rs_bn_digits( count( $rows ) - $hidden ), rs_bn_digits( $hidden ) )
			);
			?>
			<a href="<?php echo esc_url( rs_work_log_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( rs_wla_t( 'পেজটা দেখুন ↗', 'Open the page ↗' ) ); ?></a>
		</p>

		<details class="rs-wl-add">
			<summary><?php echo esc_html( rs_wla_t( '+ নতুন কাজ যোগ করুন', '+ Add a piece' ) ); ?></summary>
			<form id="rsWlAdd">
				<p class="rs-wl-help"><?php echo esc_html( rs_wla_t( 'একটা mp4 ভিডিও, অথবা কাজের পুরো ফোল্ডারটা zip করে (ভেতরের ফাইল আর ফোল্ডারের নাম যেমন আছে তেমন থাকবে), অথবা একটা html ফাইল দিন। বড় ফাইলও চলবে, ছোট ছোট অংশে যায়।', 'Upload an mp4 video, or the piece\'s whole folder as a ZIP (its file and folder names stay as they are), or a single HTML file. Large files are fine; they go up in chunks.' ) ); ?></p>
				<div class="rs-wl-grid">
					<label class="span2"><?php echo esc_html( rs_wla_t( 'ফাইল (zip বা html)', 'File (ZIP or HTML)' ) ); ?>
						<input type="file" name="file" accept=".zip,.html,.htm,.mp4,.webm,.m4v,.mov" required></label>
					<label class="span2"><?php echo esc_html( rs_wla_t( 'নাম (ইংরেজি)', 'Title (English)' ) ); ?>
						<input type="text" name="title" required></label>
					<label class="span2"><?php echo esc_html( rs_wla_t( 'নাম (বাংলা, না দিলে ইংরেজিটা যাবে)', 'Title (Bengali; the English is used if empty)' ) ); ?>
						<input type="text" name="title_bn"></label>
					<label><?php echo esc_html( rs_wla_t( 'তারিখ', 'Date' ) ); ?>
						<input type="date" name="date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" required></label>
					<label><?php echo esc_html( rs_wla_t( 'ধরন', 'Kind' ) ); ?>
						<select name="kind">
							<?php foreach ( $kinds as $k => $l ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, 'anim' ); ?>><?php echo esc_html( $en ? $l['en'] : $l['bn'] ); ?></option>
							<?php endforeach; ?>
						</select></label>
					<label class="span2"><?php echo esc_html( rs_wla_t( 'এক লাইনের বর্ণনা (বাংলা)', 'One line about it (Bengali)' ) ); ?>
						<input type="text" name="desc_bn"></label>
					<label class="span2"><?php echo esc_html( rs_wla_t( 'এক লাইনের বর্ণনা (ইংরেজি, না দিলে বাংলাটা যাবে)', 'One line about it (English; the Bengali is used if empty)' ) ); ?>
						<input type="text" name="desc_en"></label>
					<label><?php echo esc_html( rs_wla_t( 'কোন পেজ চলবে (না দিলে নিজেই বেছে নেবে)', 'Page to play (picked for you if empty)' ) ); ?>
						<input type="text" name="entry" placeholder="index.html"></label>
					<label><?php echo esc_html( rs_wla_t( '৪:৫ কাটের পেজ (থাকলে)', '4:5 cut page (if any)' ) ); ?>
						<input type="text" name="entry45" placeholder="film-4x5.html"></label>
					<label><?php echo esc_html( rs_wla_t( 'পোস্টার ছবি (না দিলে নাম দিয়ে বানানো হবে)', 'Poster image (drawn from the title if empty)' ) ); ?>
						<input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,.gif,.svg"></label>
					<label class="check"><input type="checkbox" name="client" value="1"> <?php echo esc_html( rs_wla_t( 'ক্লায়েন্টের জন্য করা', 'Made for a client' ) ); ?></label>
					<label class="check"><input type="checkbox" name="hidden" value="1"> <?php echo esc_html( rs_wla_t( 'আপাতত লুকিয়ে রাখুন', 'Keep it hidden for now' ) ); ?></label>
				</div>
				<p><button type="submit" class="button button-primary"><?php echo esc_html( rs_wla_t( 'যোগ করুন', 'Add' ) ); ?></button>
					<span class="rs-wl-progress" aria-live="polite"><progress max="100" value="0" hidden></progress> <span></span></span></p>
			</form>
		</details>

		<div class="rs-wl-bar">
			<input type="search" id="rsWlSearch" placeholder="<?php echo esc_attr( rs_wla_t( 'নাম বা ফোল্ডার দিয়ে খুঁজুন', 'Search by title or folder' ) ); ?>">
			<select id="rsWlFilter">
				<option value="all"><?php echo esc_html( rs_wla_t( 'সব', 'All' ) ); ?></option>
				<option value="shown"><?php echo esc_html( rs_wla_t( 'দেখা যাচ্ছে', 'Shown' ) ); ?></option>
				<option value="hidden"><?php echo esc_html( rs_wla_t( 'লুকানো', 'Hidden' ) ); ?></option>
				<?php foreach ( $kinds as $k => $l ) : ?>
					<option value="kind:<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $en ? $l['en'] : $l['bn'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="rs-wl-sel"></span>
			<button type="button" class="button" data-bulk="show"><?php echo esc_html( rs_wla_t( 'বাছাইগুলো দেখান', 'Show selected' ) ); ?></button>
			<button type="button" class="button" data-bulk="hide"><?php echo esc_html( rs_wla_t( 'বাছাইগুলো লুকান', 'Hide selected' ) ); ?></button>
			<button type="button" class="button rs-wl-danger" data-bulk="delete"><?php echo esc_html( rs_wla_t( 'বাছাইগুলো মুছুন', 'Delete selected' ) ); ?></button>
		</div>

		<table class="widefat rs-wl-table">
			<thead>
				<tr>
					<td class="check-column"><input type="checkbox" id="rsWlAll" aria-label="<?php echo esc_attr( rs_wla_t( 'সব বাছুন', 'Select all' ) ); ?>"></td>
					<th><?php echo esc_html( rs_wla_t( 'কাজ', 'Piece' ) ); ?></th>
					<th><?php echo esc_html( rs_wla_t( 'তারিখ', 'Date' ) ); ?></th>
					<th><?php echo esc_html( rs_wla_t( 'ধরন', 'Kind' ) ); ?></th>
					<th><?php echo esc_html( rs_wla_t( 'অবস্থা', 'Status' ) ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr data-id="<?php echo esc_attr( $row['id'] ); ?>" data-hidden="<?php echo $row['hidden'] ? '1' : '0'; ?>" data-kind="<?php echo esc_attr( $row['kind'] ); ?>" data-find="<?php echo esc_attr( strtolower( $row['title'] . ' ' . $row['title_bn'] . ' ' . $row['origin'] . ' ' . $row['id'] ) ); ?>" data-row="<?php echo esc_attr( wp_json_encode( $row ) ); ?>">
						<th scope="row" class="check-column"><input type="checkbox" value="<?php echo esc_attr( $row['id'] ); ?>"></th>
						<td class="rs-wl-piece">
							<img src="<?php echo esc_url( $row['img'] ); ?>" alt="" width="128" height="72" loading="lazy">
							<div>
								<a class="rs-wl-title" href="<?php echo esc_url( $row['src'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( rs_work_log_name( $row, $en ) ); ?></a>
								<small>
									<?php
									echo esc_html(
										implode(
											' · ',
											array_filter(
												array(
													'theme' === $row['source'] ? rs_wla_t( 'থিমের সাথে আসা', 'Comes with the theme' ) : rs_wla_t( 'আপলোড', 'Uploaded' ),
													$row['clips'] ? ( $en ? $row['clips'] . ' clips' : rs_bn_digits( $row['clips'] ) . 'টি ক্লিপ' ) : '',
													rs_wla_size( $row['bytes'] ),
													$row['origin'],
												)
											)
										)
									);
									?>
								</small>
							</div>
						</td>
						<td class="rs-wl-date"><?php echo esc_html( $row['date'] ); ?></td>
						<td class="rs-wl-kind"><?php echo esc_html( $kind_label( $row['kind'] ) ); ?></td>
						<td><span class="rs-wl-status<?php echo $row['hidden'] ? ' is-hidden' : ''; ?>"><?php echo esc_html( $row['hidden'] ? rs_wla_t( 'লুকানো', 'Hidden' ) : rs_wla_t( 'দেখা যাচ্ছে', 'Shown' ) ); ?></span></td>
						<td class="rs-wl-actions">
							<button type="button" class="button button-small" data-toggle><?php echo esc_html( $row['hidden'] ? rs_wla_t( 'দেখান', 'Show' ) : rs_wla_t( 'লুকান', 'Hide' ) ); ?></button>
							<?php if ( 'upload' === $row['source'] ) : ?>
								<button type="button" class="button button-small" data-edit><?php echo esc_html( rs_wla_t( 'বদলান', 'Edit' ) ); ?></button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<template id="rsWlEdit">
			<tr class="rs-wl-editrow">
				<td></td>
				<td colspan="5">
					<form class="rs-wl-grid">
						<label class="span2"><?php echo esc_html( rs_wla_t( 'নাম (ইংরেজি)', 'Title (English)' ) ); ?> <input type="text" name="title" required></label>
						<label class="span2"><?php echo esc_html( rs_wla_t( 'নাম (বাংলা)', 'Title (Bengali)' ) ); ?> <input type="text" name="title_bn"></label>
						<label><?php echo esc_html( rs_wla_t( 'তারিখ', 'Date' ) ); ?> <input type="date" name="date" required></label>
						<label><?php echo esc_html( rs_wla_t( 'ধরন', 'Kind' ) ); ?>
							<select name="kind">
								<?php foreach ( $kinds as $k => $l ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $en ? $l['en'] : $l['bn'] ); ?></option>
								<?php endforeach; ?>
							</select></label>
						<label class="span2"><?php echo esc_html( rs_wla_t( 'বর্ণনা (বাংলা)', 'About it (Bengali)' ) ); ?> <input type="text" name="desc_bn"></label>
						<label class="span2"><?php echo esc_html( rs_wla_t( 'বর্ণনা (ইংরেজি)', 'About it (English)' ) ); ?> <input type="text" name="desc_en"></label>
						<label class="check"><input type="checkbox" name="client"> <?php echo esc_html( rs_wla_t( 'ক্লায়েন্টের জন্য করা', 'Made for a client' ) ); ?></label>
						<p class="span4"><button type="submit" class="button button-primary"><?php echo esc_html( rs_wla_t( 'সেভ করুন', 'Save' ) ); ?></button>
							<button type="button" class="button" data-cancel><?php echo esc_html( rs_wla_t( 'বাদ দিন', 'Cancel' ) ); ?></button></p>
					</form>
				</td>
			</tr>
		</template>
	</div>
	<?php
}
