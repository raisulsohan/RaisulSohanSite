<?php
/**
 * The site's own statistics.
 *
 * Every page a reader opens, every story they read, and the handful of
 * things they do on the way (follow a link out, download a file, share a
 * story, save it for later, try a demo, search) are counted into three small
 * tables, by hour, edition, device, country and where the reader came from.
 * Enough to see what is read and looked at, from where and in which
 * edition, without a third party and without an account that can be taken
 * away. The screen that shows it all is 22-statistics-screen.php.
 *
 * What is never stored: the reader's address, a cookie, or anything that
 * could follow a person from one visit to the next. Whether a page starts a
 * visit is decided in the reader's own browser and arrives as a 1 or a 0.
 *
 * Counted from the browser, like the read counts in 09-rest-endpoints.php,
 * because the pages sit behind a full page cache: a reader served a cached
 * copy runs no PHP at all.
 *
 * The two editions are two sites in one network but share one set of
 * tables, named with the network's base prefix, with the edition as a
 * column. A story keeps its own site's id, so a story is always the pair
 * of edition and id; either dashboard shows the whole picture.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Bump when the tables change shape; rs_stats_ready() rebuilds them once. */
define( 'RS_STATS_SCHEMA', 1 );

/** How many minutes count as "right now". */
define( 'RS_STATS_PULSE_MINUTES', 30 );

/**
 * One of the three tables, with the network's base prefix.
 *
 * @param string $name 'stats' (pages and stories), 'depth' (how far a story
 *                     was read, and for how long) or 'events' (what readers did).
 * @return string
 */
function rs_stats_table( $name = 'stats' ) {
	global $wpdb;

	return $wpdb->base_prefix . 'rs_' . $name;
}

/**
 * A list kept in a network option, so both editions write to the same one.
 *
 * @param string $name Short name, without the rs_stats_ prefix.
 * @return array
 */
function rs_stats_list( $name ) {
	$value = get_site_option( 'rs_stats_' . $name, array() );

	return is_array( $value ) ? $value : array();
}

/**
 * Save what rs_stats_list() read.
 *
 * @param string $name  Short name.
 * @param array  $value The list.
 */
function rs_stats_keep( $name, $value ) {
	update_site_option( 'rs_stats_' . $name, $value );
}

/**
 * Create the tables, and bring them up to date when their shape changes.
 *
 * One row per hour per combination, counted up, not one row per visit: a
 * busy year costs tens of thousands of rows, which is nothing to a
 * database. The primary keys stay under 767 bytes in utf8mb4, so the
 * oldest MySQL a host might still run accepts them.
 */
function rs_stats_install() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$collate = $wpdb->get_charset_collate();

	/* A story has its id and no path; any other page has its path, taken
	   from its edition's root (/portfolio/, never /en/portfolio/), and 0. */
	dbDelta(
		'CREATE TABLE ' . rs_stats_table() . " (
			day date NOT NULL,
			hour tinyint unsigned NOT NULL DEFAULT 0,
			lang char(2) NOT NULL DEFAULT 'bn',
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			path varchar(100) NOT NULL DEFAULT '',
			device varchar(8) NOT NULL DEFAULT 'desktop',
			source varchar(40) NOT NULL DEFAULT 'direct',
			country char(2) NOT NULL DEFAULT '',
			hits int unsigned NOT NULL DEFAULT 0,
			visits int unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (day,hour,lang,post_id,path,device,source,country),
			KEY day (day)
		) {$collate};"
	);

	/* How far down a story readers got, in quarters, and how long they had
	   it on screen. */
	dbDelta(
		'CREATE TABLE ' . rs_stats_table( 'depth' ) . " (
			day date NOT NULL,
			lang char(2) NOT NULL DEFAULT 'bn',
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			bucket tinyint unsigned NOT NULL DEFAULT 0,
			n int unsigned NOT NULL DEFAULT 0,
			secs bigint unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (day,lang,post_id,bucket),
			KEY day (day)
		) {$collate};"
	);

	/* The things a reader does rather than merely reads, one row per day
	   per kind per label, counted up like the rest. */
	dbDelta(
		'CREATE TABLE ' . rs_stats_table( 'events' ) . " (
			day date NOT NULL,
			kind varchar(10) NOT NULL DEFAULT '',
			lang char(2) NOT NULL DEFAULT 'bn',
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			label varchar(120) NOT NULL DEFAULT '',
			n int unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (day,kind,lang,post_id,label),
			KEY day (day)
		) {$collate};"
	);

	update_site_option( 'rs_stats_schema', RS_STATS_SCHEMA );
}

/**
 * Whether the tables are there, building them first when they are behind.
 *
 * Asked by the code that writes and the screen that reads, rather than on
 * every page load: nothing else needs the tables, and the cached pages
 * never reach PHP anyway.
 *
 * @return bool
 */
function rs_stats_ready() {
	static $ready = null;

	if ( null === $ready ) {
		if ( (int) get_site_option( 'rs_stats_schema', 0 ) !== RS_STATS_SCHEMA ) {
			rs_stats_install();
		}

		$ready = true;
	}

	return $ready;
}
add_action( 'admin_init', 'rs_stats_ready' );

/* -------------------------------------------------------------------------
 * What a visit looks like
 * ---------------------------------------------------------------------- */

/**
 * The edition this request is on: 'bn' or 'en'.
 *
 * Taken from which site of the pair this is, when there is a pair, rather
 * than from the site's language: a story's id means something only on its
 * own site, so the edition written beside it has to name that site, and
 * the screen reads it back the same way. A single site falls back to its
 * language.
 *
 * @return string
 */
function rs_stats_lang() {
	$pair = function_exists( 'rs_edition_pair' ) ? rs_edition_pair() : null;

	if ( $pair ) {
		return 'en' === $pair['lang'] ? 'bn' : 'en';
	}

	return rs_is_en() ? 'en' : 'bn';
}

/**
 * Whether this request should be counted at all.
 *
 * The author reading their own work is not a reader, and neither is a
 * machine. The login cookie is read by hand, for the reason given at
 * rs_rest_view(): a REST request without a nonce is anonymous to
 * WordPress, and these pages are cached, so they can carry no nonce.
 *
 * @return bool
 */
function rs_stats_counts() {
	$viewer = wp_validate_auth_cookie( '', 'logged_in' );

	if ( $viewer && user_can( $viewer, 'edit_posts' ) ) {
		return false;
	}

	return ! rs_looks_automated();
}

/**
 * Where the reader came from, as a short label.
 *
 * The full address is not kept, only which site sent them, and only by
 * name for the handful worth naming. Everything else is its own domain,
 * "internal" for this site, or "direct".
 *
 * @param string $referrer The page the reader came from.
 * @return string
 */
function rs_stats_source( $referrer ) {
	$host = strtolower( (string) wp_parse_url( trim( (string) $referrer ), PHP_URL_HOST ) );
	$host = preg_replace( '/^(www|m|l|lm|mobile)\./', '', $host );

	if ( '' === $host ) {
		return 'direct';
	}

	$own = strtolower( (string) wp_parse_url( network_home_url(), PHP_URL_HOST ) );

	if ( preg_replace( '/^www\./', '', $own ) === $host ) {
		return 'internal';
	}

	$known = array(
		'google'    => array( 'google.com', 'google.co.in', 'google.com.bd', 'googleusercontent.com' ),
		'facebook'  => array( 'facebook.com', 'fb.com', 'messenger.com' ),
		'youtube'   => array( 'youtube.com', 'youtu.be' ),
		'linkedin'  => array( 'linkedin.com', 'lnkd.in' ),
		'github'    => array( 'github.com', 'github.io' ),
		'whatsapp'  => array( 'whatsapp.com', 'wa.me' ),
		'x'         => array( 'twitter.com', 'x.com', 't.co' ),
		'instagram' => array( 'instagram.com' ),
		'behance'   => array( 'behance.net' ),
		'reddit'    => array( 'reddit.com' ),
		'bing'      => array( 'bing.com' ),
		'duckduckgo' => array( 'duckduckgo.com' ),
		'telegram'  => array( 'telegram.org', 't.me' ),
		'chatgpt'   => array( 'chatgpt.com', 'chat.openai.com' ),
	);

	foreach ( $known as $label => $hosts ) {
		foreach ( $hosts as $candidate ) {
			if ( $host === $candidate || substr( $host, -strlen( '.' . $candidate ) ) === '.' . $candidate ) {
				return $label;
			}
		}
	}

	return substr( $host, 0, 40 );
}

/**
 * Phone, tablet or computer, from the user agent alone.
 *
 * @return string
 */
function rs_stats_device() {
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';

	if ( preg_match( '/ipad|tablet|playbook|silk|(android(?!.*mobile))/', $agent ) ) {
		return 'tablet';
	}

	return wp_is_mobile() ? 'mobile' : 'desktop';
}

/**
 * Which country the reader is in, without ever knowing their address.
 *
 * Three ways, in the order they can be trusted: a header from a CDN that
 * has already worked it out; the browser's own time zone, which the page
 * sends ("Asia/Dhaka" is Bangladesh and nothing else); and the region half
 * of the language the browser asks for. Two letters on a counted row; it
 * can never be narrowed to a person.
 *
 * @param string $timezone What the browser reported.
 * @return string Two-letter code, or '' when nothing said.
 */
function rs_stats_country( $timezone = '' ) {
	foreach ( array( 'HTTP_CF_IPCOUNTRY', 'HTTP_X_COUNTRY_CODE', 'HTTP_X_GEO_COUNTRY', 'GEOIP_COUNTRY_CODE' ) as $header ) {
		if ( empty( $_SERVER[ $header ] ) ) {
			continue;
		}

		$code = strtoupper( substr( sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ), 0, 2 ) );

		if ( preg_match( '/^[A-Z]{2}$/', $code ) && 'XX' !== $code && 'T1' !== $code ) {
			return $code;
		}
	}

	$map = rs_stats_timezone_countries();

	if ( isset( $map[ $timezone ] ) ) {
		return $map[ $timezone ];
	}

	$accept = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) : '';

	if ( preg_match( '/^[a-z]{2,3}-([A-Za-z]{2})/', $accept, $match ) ) {
		return strtoupper( $match[1] );
	}

	return '';
}

/**
 * Time zone => country, for every zone readers plausibly sit in. Anything
 * unlisted counts as unknown.
 *
 * @return array<string, string>
 */
function rs_stats_timezone_countries() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map = array(
		'Asia/Dhaka' => 'BD', 'Asia/Kolkata' => 'IN', 'Asia/Calcutta' => 'IN', 'Asia/Karachi' => 'PK',
		'Asia/Kathmandu' => 'NP', 'Asia/Katmandu' => 'NP', 'Asia/Colombo' => 'LK', 'Asia/Thimphu' => 'BT',
		'Asia/Kabul' => 'AF', 'Asia/Yangon' => 'MM', 'Asia/Rangoon' => 'MM', 'Asia/Bangkok' => 'TH',
		'Asia/Singapore' => 'SG', 'Asia/Kuala_Lumpur' => 'MY', 'Asia/Jakarta' => 'ID', 'Asia/Makassar' => 'ID',
		'Asia/Manila' => 'PH', 'Asia/Ho_Chi_Minh' => 'VN', 'Asia/Saigon' => 'VN', 'Asia/Phnom_Penh' => 'KH',
		'Asia/Hong_Kong' => 'HK', 'Asia/Taipei' => 'TW', 'Asia/Shanghai' => 'CN', 'Asia/Urumqi' => 'CN',
		'Asia/Tokyo' => 'JP', 'Asia/Seoul' => 'KR', 'Asia/Ulaanbaatar' => 'MN',
		'Asia/Dubai' => 'AE', 'Asia/Muscat' => 'OM', 'Asia/Qatar' => 'QA', 'Asia/Bahrain' => 'BH',
		'Asia/Kuwait' => 'KW', 'Asia/Riyadh' => 'SA', 'Asia/Baghdad' => 'IQ', 'Asia/Tehran' => 'IR',
		'Asia/Jerusalem' => 'IL', 'Asia/Amman' => 'JO', 'Asia/Beirut' => 'LB', 'Asia/Istanbul' => 'TR',
		'Europe/Istanbul' => 'TR', 'Asia/Baku' => 'AZ', 'Asia/Tbilisi' => 'GE', 'Asia/Tashkent' => 'UZ',
		'Asia/Almaty' => 'KZ',
		'Europe/London' => 'GB', 'Europe/Dublin' => 'IE', 'Europe/Lisbon' => 'PT', 'Europe/Madrid' => 'ES',
		'Europe/Paris' => 'FR', 'Europe/Brussels' => 'BE', 'Europe/Amsterdam' => 'NL', 'Europe/Berlin' => 'DE',
		'Europe/Zurich' => 'CH', 'Europe/Vienna' => 'AT', 'Europe/Rome' => 'IT', 'Europe/Prague' => 'CZ',
		'Europe/Warsaw' => 'PL', 'Europe/Budapest' => 'HU', 'Europe/Athens' => 'GR', 'Europe/Bucharest' => 'RO',
		'Europe/Sofia' => 'BG', 'Europe/Kiev' => 'UA', 'Europe/Kyiv' => 'UA', 'Europe/Moscow' => 'RU',
		'Europe/Helsinki' => 'FI', 'Europe/Stockholm' => 'SE', 'Europe/Oslo' => 'NO', 'Europe/Copenhagen' => 'DK',
		'America/New_York' => 'US', 'America/Detroit' => 'US', 'America/Chicago' => 'US', 'America/Denver' => 'US',
		'America/Phoenix' => 'US', 'America/Los_Angeles' => 'US', 'America/Anchorage' => 'US', 'Pacific/Honolulu' => 'US',
		'America/Toronto' => 'CA', 'America/Vancouver' => 'CA', 'America/Edmonton' => 'CA', 'America/Winnipeg' => 'CA',
		'America/Halifax' => 'CA', 'America/Mexico_City' => 'MX', 'America/Bogota' => 'CO', 'America/Lima' => 'PE',
		'America/Santiago' => 'CL', 'America/Argentina/Buenos_Aires' => 'AR', 'America/Sao_Paulo' => 'BR',
		'Africa/Cairo' => 'EG', 'Africa/Casablanca' => 'MA', 'Africa/Lagos' => 'NG', 'Africa/Nairobi' => 'KE',
		'Africa/Johannesburg' => 'ZA', 'Africa/Addis_Ababa' => 'ET', 'Africa/Accra' => 'GH',
		'Australia/Sydney' => 'AU', 'Australia/Melbourne' => 'AU', 'Australia/Brisbane' => 'AU',
		'Australia/Perth' => 'AU', 'Australia/Adelaide' => 'AU', 'Pacific/Auckland' => 'NZ', 'Indian/Maldives' => 'MV',
	);

	return $map;
}

/**
 * An address as it is kept: decoded, without the query, and from the
 * edition's own root, so /en/portfolio/ and /portfolio/ are the same page
 * in two editions rather than two pages.
 *
 * @param string $path What the browser said it was on.
 * @return string '' when nothing usable was sent.
 */
function rs_stats_clean_path( $path ) {
	$path = rawurldecode( (string) $path );
	$path = (string) preg_replace( '/[?#].*$/s', '', $path );
	$path = wp_check_invalid_utf8( $path );
	$path = (string) preg_replace( '/[\x00-\x20\x7F<>"\'`\\\\]+/u', '', $path );

	if ( '' === $path || '/' !== $path[0] ) {
		return '';
	}

	$root = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	if ( strlen( $root ) > 1 && 0 === strpos( $path, $root ) ) {
		$path = '/' . substr( $path, strlen( $root ) );
	} elseif ( untrailingslashit( $root ) === $path ) {
		$path = '/';
	}

	$path = preg_replace( '#/{2,}#', '/', $path );
	$path = function_exists( 'mb_strtolower' ) ? mb_strtolower( $path, 'UTF-8' ) : strtolower( $path );

	return function_exists( 'mb_substr' ) ? mb_substr( $path, 0, 100, 'UTF-8' ) : substr( $path, 0, 100 );
}

/**
 * Add one opening to this hour's row.
 *
 * @param int    $post_id  The story, or 0 for any other page.
 * @param string $path     The page's cleaned address, or '' for a story.
 * @param string $referrer Where the reader came from.
 * @param bool   $visit    Whether this page starts a visit.
 * @param string $timezone The browser's time zone, for the country.
 */
function rs_stats_record( $post_id, $path, $referrer = '', $visit = false, $timezone = '' ) {
	global $wpdb;

	rs_stats_ready();

	$table = rs_stats_table();
	$visit = $visit ? 1 : 0;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- A counter; there is no API for an atomic upsert.
	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$table} (day, hour, lang, post_id, path, device, source, country, hits, visits)
			 VALUES (%s, %d, %s, %d, %s, %s, %s, %s, 1, %d)
			 ON DUPLICATE KEY UPDATE hits = hits + 1, visits = visits + %d",
			wp_date( 'Y-m-d' ),
			(int) wp_date( 'G' ),
			rs_stats_lang(),
			(int) $post_id,
			(string) $path,
			rs_stats_device(),
			rs_stats_source( $referrer ),
			rs_stats_country( (string) $timezone ),
			$visit,
			$visit
		)
	);

	rs_stats_pulse_record();
}

/**
 * The last half hour of openings, as bare timestamps.
 *
 * Too short a span for a row in the table, and all the screen needs is how
 * many, so a short list of times is kept and trimmed on every write.
 * Nothing about who; only when.
 */
function rs_stats_pulse_record() {
	$cutoff = time() - RS_STATS_PULSE_MINUTES * MINUTE_IN_SECONDS;
	$pulse  = array_values(
		array_filter(
			rs_stats_list( 'pulse' ),
			function ( $time ) use ( $cutoff ) {
				return (int) $time >= $cutoff;
			}
		)
	);

	$pulse[] = time();

	/* A sudden rush must not turn one option into a megabyte. */
	rs_stats_keep( 'pulse', array_slice( $pulse, -500 ) );
}

/**
 * How many openings in the last N minutes.
 *
 * @param int $minutes How far back.
 * @return int
 */
function rs_stats_pulse( $minutes = RS_STATS_PULSE_MINUTES ) {
	$cutoff = time() - max( 1, (int) $minutes ) * MINUTE_IN_SECONDS;

	return count(
		array_filter(
			rs_stats_list( 'pulse' ),
			function ( $time ) use ( $cutoff ) {
				return (int) $time >= $cutoff;
			}
		)
	);
}

/**
 * Record how much of a story a reader reached, in quarters, and how long
 * they had it on screen.
 *
 * A read count says a story was opened; this says whether it was read.
 * The share is rounded to 0, 25, 50, 75 or 100 before it is stored:
 * precise enough to see where a long piece loses people, too coarse to be
 * about anybody.
 *
 * @param int $post_id A story of this edition.
 * @param int $percent How far down, 0–100.
 * @param int $seconds How long it was on screen.
 * @return bool Whether it was kept.
 */
function rs_stats_record_depth( $post_id, $percent, $seconds = 0 ) {
	global $wpdb;

	$post_id = (int) $post_id;

	if ( ! $post_id || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		return false;
	}

	$percent = max( 0, min( 100, (int) $percent ) );

	if ( $percent >= 90 ) {
		$bucket = 100;
	} elseif ( $percent >= 70 ) {
		$bucket = 75;
	} elseif ( $percent >= 45 ) {
		$bucket = 50;
	} elseif ( $percent >= 20 ) {
		$bucket = 25;
	} else {
		$bucket = 0;
	}

	/* An hour is generous for the longest story here; beyond that a tab was
	   left open rather than read, and the average would be nonsense. */
	$seconds = max( 0, min( HOUR_IN_SECONDS, (int) $seconds ) );

	rs_stats_ready();

	$table = rs_stats_table( 'depth' );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- A counter; there is no API for an atomic upsert.
	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$table} (day, lang, post_id, bucket, n, secs) VALUES (%s, %s, %d, %d, 1, %d)
			 ON DUPLICATE KEY UPDATE n = n + 1, secs = secs + %d",
			wp_date( 'Y-m-d' ),
			rs_stats_lang(),
			$post_id,
			$bucket,
			$seconds,
			$seconds
		)
	);

	return true;
}

/**
 * The kinds of thing a reader can do that are worth counting, each with
 * whether it is about a story (counted by the story) or about a label.
 *
 * @return array<string, bool> Kind => counted by story.
 */
function rs_stats_kinds() {
	return array(
		'out'     => false, // Followed a link to another site.
		'file'    => false, // Downloaded a file from this one: the CV, a release.
		'share'   => false, // Opened the share sheet or copied a story's link.
		'save'    => true,  // Put a story on the read-later shelf.
		'demo'    => false, // Tried a project's demo.
		'contact' => false, // Reached for the email address, WhatsApp or the phone.
	);
}

/**
 * Count something a reader did.
 *
 * @param string $kind    A key of rs_stats_kinds().
 * @param int    $post_id The story it happened on, or 0.
 * @param string $label   Which link, file or demo; never who.
 * @return bool Whether it was counted.
 */
function rs_stats_record_event( $kind, $post_id = 0, $label = '' ) {
	global $wpdb;

	$kinds = rs_stats_kinds();

	if ( ! isset( $kinds[ $kind ] ) ) {
		return false;
	}

	$label = sanitize_text_field( (string) $label );
	$label = function_exists( 'mb_substr' ) ? mb_substr( $label, 0, 120, 'UTF-8' ) : substr( $label, 0, 120 );

	/* Counted by the story or by the label, never by both. */
	if ( $kinds[ $kind ] ) {
		$post_id = (int) $post_id;
		$label   = '';

		if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
			return false;
		}
	} elseif ( '' === $label ) {
		return false;
	}

	rs_stats_ready();

	$table = rs_stats_table( 'events' );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- A counter; there is no API for an atomic upsert.
	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$table} (day, kind, lang, post_id, label, n) VALUES (%s, %s, %s, %d, %s, 1)
			 ON DUPLICATE KEY UPDATE n = n + 1",
			wp_date( 'Y-m-d' ),
			$kind,
			rs_stats_lang(),
			(int) $post_id,
			$label
		)
	);

	return true;
}

/**
 * Remember a search, with how often it has been asked for.
 *
 * Only the term the reader settled on, which the search box sends once
 * they open a result or close the box; the letters typed on the way are
 * not searches. What somebody looked for is about the site, not about
 * them, and one that found nothing is a reader telling the site what it
 * does not have.
 *
 * @param string $term  What was searched for.
 * @param int    $found How many stories came back, or -1 when not known.
 * @return bool Whether it was kept.
 */
function rs_stats_record_search( $term, $found = -1 ) {
	$term = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $term ) ) );
	$size = function_exists( 'mb_strlen' ) ? mb_strlen( $term, 'UTF-8' ) : strlen( $term );

	if ( $size < 2 || $size > 60 ) {
		return false;
	}

	$key      = rs_stats_lang() . '|' . ( function_exists( 'mb_strtolower' ) ? mb_strtolower( $term, 'UTF-8' ) : strtolower( $term ) );
	$searches = rs_stats_list( 'searches' );

	if ( ! isset( $searches[ $key ] ) || ! is_array( $searches[ $key ] ) ) {
		$searches[ $key ] = array( 'n' => 0 );
	}

	$searches[ $key ]['n'] = (int) $searches[ $key ]['n'] + 1;
	$searches[ $key ]['d'] = wp_date( 'Y-m-d' );

	if ( 0 === (int) $found ) {
		$searches[ $key ]['miss'] = ( isset( $searches[ $key ]['miss'] ) ? (int) $searches[ $key ]['miss'] : 0 ) + 1;
	}

	/* The 300 most asked for; the tail is one-offs. */
	if ( count( $searches ) > 300 ) {
		uasort(
			$searches,
			function ( $a, $b ) {
				return (int) $b['n'] <=> (int) $a['n'];
			}
		);
		$searches = array_slice( $searches, 0, 300, true );
	}

	rs_stats_keep( 'searches', $searches );

	return true;
}

/**
 * Whether an address is somebody poking at the site rather than a reader
 * who followed a broken link. A scanner asking for /.env would otherwise
 * crowd the addresses that can actually be fixed off the list.
 *
 * @param string $path The address asked for.
 * @return bool
 */
function rs_stats_is_probe( $path ) {
	$marks = array( '/.well-known/', '/.env', '/.git', '/.aws', '/vendor/', '/wp-config', '/xmlrpc', '/phpmyadmin', '/cgi-bin/', 'graphql', '/wp-content/plugins/', '/wp-includes/', '/wp-admin', '.php', '.asp', '.sql', '.bak', '.zip', '.tar' );

	foreach ( $marks as $mark ) {
		if ( false !== strpos( $path, $mark ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Remember an address that led nowhere, and which site sent the reader.
 *
 * Told by the 404 page itself from the browser, so it is heard about even
 * when the page cache answered. A broken link is the one thing on the
 * screen that can be fixed, so the address matters more than the count.
 *
 * @param string $path     The cleaned address.
 * @param string $referrer The page that linked to it.
 * @return bool Whether it was kept.
 */
function rs_stats_record_missing( $path, $referrer = '' ) {
	if ( '' === $path || rs_stats_is_probe( $path ) ) {
		return false;
	}

	$key  = rs_stats_lang() . '|' . $path;
	$rows = rs_stats_list( 'missing' );

	if ( ! isset( $rows[ $key ] ) || ! is_array( $rows[ $key ] ) ) {
		$rows[ $key ] = array(
			'n'    => 0,
			'from' => '',
		);
	}

	$rows[ $key ]['n'] = (int) $rows[ $key ]['n'] + 1;
	$rows[ $key ]['d'] = wp_date( 'Y-m-d' );

	if ( '' !== $referrer ) {
		$rows[ $key ]['from'] = rs_stats_source( $referrer );
	}

	if ( count( $rows ) > 200 ) {
		uasort(
			$rows,
			function ( $a, $b ) {
				return (int) $b['n'] <=> (int) $a['n'];
			}
		);
		$rows = array_slice( $rows, 0, 200, true );
	}

	rs_stats_keep( 'missing', $rows );

	return true;
}

/* -------------------------------------------------------------------------
 * The routes the browser reports to
 * ---------------------------------------------------------------------- */

/**
 * Three routes beside rs/v1/view, written to the same rule: no nonce
 * (a cached page cannot carry one), nothing taken but a few short fields,
 * and nothing done with them but adding one to a number.
 */
function rs_stats_routes() {
	register_rest_route(
		'rs/v1',
		'/hit',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'rs_stats_rest_hit',
			'permission_callback' => '__return_true',
			'args'                => array(
				'path'  => array( 'sanitize_callback' => 'rs_stats_clean_path' ),
				'ref'   => array( 'sanitize_callback' => 'esc_url_raw' ),
				'visit' => array( 'sanitize_callback' => 'absint' ),
				'tz'    => array( 'sanitize_callback' => 'sanitize_text_field' ),
				/* Sent by the 404 page about itself. */
				'nf'    => array( 'sanitize_callback' => 'absint' ),
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/depth',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'rs_stats_rest_depth',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id'    => array( 'sanitize_callback' => 'absint' ),
				'depth' => array( 'sanitize_callback' => 'absint' ),
				'secs'  => array( 'sanitize_callback' => 'absint' ),
			),
		)
	);

	register_rest_route(
		'rs/v1',
		'/event',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'rs_stats_rest_event',
			'permission_callback' => '__return_true',
			'args'                => array(
				'kind'  => array( 'sanitize_callback' => 'sanitize_key' ),
				'id'    => array( 'sanitize_callback' => 'absint' ),
				'label' => array( 'sanitize_callback' => 'sanitize_text_field' ),
				/* With a search: how many stories it found. A closure
				   rather than intval, which is handed the request as a
				   number base and throws. */
				'found' => array(
					'sanitize_callback' => function ( $value ) {
						return (int) $value;
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'rs_stats_routes' );

/**
 * A page that is not a story was opened. Stories are told to
 * rs/v1/view instead, which counts them for both.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function rs_stats_rest_hit( $request ) {
	$path = (string) $request->get_param( 'path' );

	if ( '' === $path || ! rs_stats_counts() ) {
		return rest_ensure_response( array( 'counted' => false ) );
	}

	$ref = (string) $request->get_param( 'ref' );

	/* A missing page is one page however it was spelt, and its address
	   goes on the list of broken links instead. */
	if ( $request->get_param( 'nf' ) ) {
		rs_stats_record_missing( $path, $ref );
		$path = '404';
	}

	rs_stats_record( 0, $path, $ref, (bool) $request->get_param( 'visit' ), (string) $request->get_param( 'tz' ) );

	return rest_ensure_response( array( 'counted' => true ) );
}

/**
 * How far down a story the reader got, reported as they left it.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function rs_stats_rest_depth( $request ) {
	$counted = rs_stats_counts() && rs_stats_record_depth(
		(int) $request->get_param( 'id' ),
		(int) $request->get_param( 'depth' ),
		(int) $request->get_param( 'secs' )
	);

	return rest_ensure_response( array( 'counted' => (bool) $counted ) );
}

/**
 * Something a reader did, or the search they settled on.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function rs_stats_rest_event( $request ) {
	if ( ! rs_stats_counts() ) {
		return rest_ensure_response( array( 'counted' => false ) );
	}

	$kind  = (string) $request->get_param( 'kind' );
	$label = (string) $request->get_param( 'label' );

	if ( 'search' === $kind ) {
		$found   = $request->get_param( 'found' );
		$counted = rs_stats_record_search( $label, null === $found ? -1 : (int) $found );
	} else {
		$counted = rs_stats_record_event( $kind, (int) $request->get_param( 'id' ), $label );
	}

	return rest_ensure_response( array( 'counted' => (bool) $counted ) );
}

/**
 * The few lines that count a standalone page, the showreel and the map
 * demos, which carry none of the site's own script.
 *
 * The same report app.js sends for a page, and the same rule for a visit
 * (half an hour without a page ends one, kept in this browser only), plus
 * the links out, which is most of what is done on those pages.
 */
function rs_stats_beacon() {
	$rest = esc_url_raw( rest_url( 'rs/v1/' ) );
	?>
<script>
( function () {
	var rest = <?php echo wp_json_encode( $rest ); ?>;

	function tell( route, fields ) {
		var body = new URLSearchParams( fields );
		try {
			if ( navigator.sendBeacon && navigator.sendBeacon( rest + route, body ) ) return;
		} catch ( e ) {}
		if ( window.fetch ) {
			window.fetch( rest + route, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true } ).then( null, function () {} );
		}
	}

	function visitStarts() {
		var now = Date.now(), last = 0;
		try {
			last = parseInt( window.localStorage.getItem( 'rs-visit' ) || '0', 10 );
			window.localStorage.setItem( 'rs-visit', String( now ) );
		} catch ( e ) {}
		return ! last || now - last > 1800000;
	}

	function send() {
		var tz = '';
		try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch ( e ) {}
		tell( 'hit', { path: window.location.pathname, ref: document.referrer, visit: visitStarts() ? 1 : 0, tz: tz } );
	}

	if ( 'visible' === document.visibilityState ) {
		send();
	} else {
		document.addEventListener( 'visibilitychange', function seen() {
			if ( 'visible' !== document.visibilityState ) return;
			document.removeEventListener( 'visibilitychange', seen );
			send();
		} );
	}

	function noteLink( event ) {
		if ( event.button > 1 || ! event.target.closest ) return;
		var link = event.target.closest( 'a[href]' );
		if ( ! link ) return;
		var href = link.href || '';
		if ( /^mailto:/i.test( href ) ) return tell( 'event', { kind: 'contact', label: 'Email' } );
		if ( /^tel:/i.test( href ) ) return tell( 'event', { kind: 'contact', label: 'Phone' } );
		if ( ! /^https?:/i.test( href ) || link.hostname === window.location.hostname ) return;
		if ( 'wa.me' === link.hostname ) return tell( 'event', { kind: 'contact', label: 'WhatsApp' } );
		tell( 'event', { kind: 'out', label: link.hostname.replace( /^www\./, '' ) + link.pathname.split( '/' ).slice( 0, 3 ).join( '/' ) } );
	}

	document.addEventListener( 'click', noteLink, true );
	document.addEventListener( 'auxclick', noteLink, true );
}() );
</script>
	<?php
}

/* -------------------------------------------------------------------------
 * Reading it back
 * ---------------------------------------------------------------------- */

/**
 * The windows the screen offers, and how each is measured. The first is
 * the one it opens on.
 *
 * @return array<string, array{mode:string,length:int}>
 */
function rs_stats_windows() {
	return array(
		'24h'  => array(
			'mode'   => 'hours',
			'length' => 24,
		),
		'7d'   => array(
			'mode'   => 'days',
			'length' => 7,
		),
		'30d'  => array(
			'mode'   => 'days',
			'length' => 30,
		),
		'90d'  => array(
			'mode'   => 'days',
			'length' => 90,
		),
		'365d' => array(
			'mode'   => 'days',
			'length' => 365,
		),
		/* From the first day counted to today. The period before it is the
		   stretch before counting began, so it always comes back empty. */
		'all'  => array(
			'mode'   => 'days',
			'length' => rs_stats_all_days(),
		),
	);
}

/**
 * How many days "all time" covers: the first day counted to today, at least one.
 *
 * @return int
 */
function rs_stats_all_days() {
	static $days = null;

	if ( null === $days ) {
		$first = rs_stats_first_day();
		$start = $first ? strtotime( $first . ' 12:00:00' ) : false;
		$today = strtotime( wp_date( 'Y-m-d' ) . ' 12:00:00' );

		$days = $start ? max( 1, (int) round( ( $today - $start ) / DAY_IN_SECONDS ) + 1 ) : 1;
	}

	return $days;
}

/**
 * Whether the chart for a window goes by the month: all time, once it runs
 * past a year, would be too many days for one line to show.
 *
 * @param string $key Window key.
 * @return bool
 */
function rs_stats_by_month( $key ) {
	return 'all' === $key && rs_stats_all_days() > 366;
}

/**
 * The WHERE clause for a window, and the values it needs.
 *
 * Written with plain comparisons on the day and the hour, so it means the
 * same on MySQL and on the SQLite a test install runs on.
 *
 * @param string $key      A key of rs_stats_windows().
 * @param bool   $previous The window just before this one, for comparison.
 * @param bool   $hourly   Whether the table keeps the hour. The depth and
 *                         event tables do not, and fall back to whole days.
 * @return array{0:string,1:array}
 */
function rs_stats_where( $key, $previous = false, $hourly = true ) {
	$windows = rs_stats_windows();
	$window  = isset( $windows[ $key ] ) ? $windows[ $key ] : $windows['24h'];
	$length  = (int) $window['length'];
	$back    = $previous ? $length * 2 - 1 : $length - 1;
	$until   = $previous ? $length : 0;

	if ( 'hours' === $window['mode'] ) {
		$from = time() - $back * HOUR_IN_SECONDS;
		$to   = time() - $until * HOUR_IN_SECONDS;

		if ( ! $hourly ) {
			return array( 'day BETWEEN %s AND %s', array( wp_date( 'Y-m-d', $from ), wp_date( 'Y-m-d', $to ) ) );
		}

		return array(
			'( day > %s OR ( day = %s AND hour >= %d ) ) AND ( day < %s OR ( day = %s AND hour <= %d ) )',
			array(
				wp_date( 'Y-m-d', $from ),
				wp_date( 'Y-m-d', $from ),
				(int) wp_date( 'G', $from ),
				wp_date( 'Y-m-d', $to ),
				wp_date( 'Y-m-d', $to ),
				(int) wp_date( 'G', $to ),
			),
		);
	}

	return array(
		'day BETWEEN %s AND %s',
		array(
			wp_date( 'Y-m-d', time() - $back * DAY_IN_SECONDS ),
			wp_date( 'Y-m-d', time() - $until * DAY_IN_SECONDS ),
		),
	);
}

/**
 * Run one read against a table, with the window's WHERE spliced in.
 *
 * @param string $sql    The query, with {table} and {where} in it.
 * @param string $key    Window key.
 * @param array  $extra  Values for placeholders after the window's own.
 * @param string $table  Which table.
 * @param bool   $before The window before this one.
 * @return array[]
 */
function rs_stats_query( $sql, $key, $extra = array(), $table = 'stats', $before = false ) {
	global $wpdb;

	rs_stats_ready();

	list( $where, $params ) = rs_stats_where( $key, $before, 'stats' === $table );

	$sql = str_replace( array( '{table}', '{where}' ), array( rs_stats_table( $table ), $where ), $sql );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- The table name and the window's clause are the theme's own; every value goes through prepare().
	$rows = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $params, $extra ) ), ARRAY_A );

	return is_array( $rows ) ? $rows : array();
}

/**
 * Totals for a window.
 *
 * Stories are counted by edition and id together, since the two editions
 * hand out their own ids; post_id * 2 plus one for English is that pair as
 * a single number, without a string function SQLite might not have.
 *
 * @param string $key    Window key.
 * @param bool   $before The window before it.
 * @return array{hits:int,visits:int,stories:int,story_hits:int,page_hits:int}
 */
function rs_stats_totals( $key, $before = false ) {
	$rows = rs_stats_query(
		"SELECT SUM(hits) AS hits,
				SUM(visits) AS visits,
				COUNT(DISTINCT CASE WHEN post_id > 0 THEN post_id * 2 + CASE WHEN lang = 'en' THEN 1 ELSE 0 END END) AS stories,
				SUM(CASE WHEN post_id > 0 THEN hits ELSE 0 END) AS story_hits,
				SUM(CASE WHEN post_id = 0 THEN hits ELSE 0 END) AS page_hits
		 FROM {table} WHERE {where}",
		$key,
		array(),
		'stats',
		$before
	);

	$row = $rows ? $rows[0] : array();

	return array(
		'hits'       => (int) ( $row['hits'] ?? 0 ),
		'visits'     => (int) ( $row['visits'] ?? 0 ),
		'stories'    => (int) ( $row['stories'] ?? 0 ),
		'story_hits' => (int) ( $row['story_hits'] ?? 0 ),
		'page_hits'  => (int) ( $row['page_hits'] ?? 0 ),
	);
}

/**
 * The line across the chart: one point per hour, or per day, with the
 * empty ones filled in.
 *
 * @param string $key Window key.
 * @return array<string, array{hits:int,visits:int,time:int}>
 */
function rs_stats_series( $key ) {
	$windows = rs_stats_windows();
	$window  = isset( $windows[ $key ] ) ? $windows[ $key ] : $windows['24h'];
	$hourly  = 'hours' === $window['mode'];

	$rows = rs_stats_query(
		$hourly
			? 'SELECT day, hour, SUM(hits) AS hits, SUM(visits) AS visits FROM {table} WHERE {where} GROUP BY day, hour'
			: 'SELECT day, 0 AS hour, SUM(hits) AS hits, SUM(visits) AS visits FROM {table} WHERE {where} GROUP BY day',
		$key
	);

	$found = array();

	foreach ( $rows as $row ) {
		$slot           = $hourly ? $row['day'] . ' ' . sprintf( '%02d', (int) $row['hour'] ) : $row['day'];
		$found[ $slot ] = $row;
	}

	$series = array();

	/* One point per calendar month, from the first one counted to this one.
	   The days are added up here rather than in SQL, so nothing depends on a
	   string function SQLite might not have. */
	if ( rs_stats_by_month( $key ) ) {
		$month = substr( rs_stats_first_day(), 0, 7 ) . '-01';
		$last  = wp_date( 'Y-m' );

		while ( substr( $month, 0, 7 ) <= $last ) {
			$series[ substr( $month, 0, 7 ) ] = array(
				'hits'   => 0,
				'visits' => 0,
				'time'   => strtotime( $month . ' 12:00:00' ),
			);

			$month = gmdate( 'Y-m-d', strtotime( $month . ' +1 month' ) );
		}

		foreach ( $rows as $row ) {
			$slot = substr( $row['day'], 0, 7 );

			if ( isset( $series[ $slot ] ) ) {
				$series[ $slot ]['hits']   += (int) $row['hits'];
				$series[ $slot ]['visits'] += (int) $row['visits'];
			}
		}

		return $series;
	}

	$step = $hourly ? HOUR_IN_SECONDS : DAY_IN_SECONDS;

	for ( $i = (int) $window['length'] - 1; $i >= 0; $i-- ) {
		$time = time() - $i * $step;
		$slot = wp_date( $hourly ? 'Y-m-d H' : 'Y-m-d', $time );

		$series[ $slot ] = array(
			'hits'   => isset( $found[ $slot ] ) ? (int) $found[ $slot ]['hits'] : 0,
			'visits' => isset( $found[ $slot ] ) ? (int) $found[ $slot ]['visits'] : 0,
			'time'   => $time,
		);
	}

	return $series;
}

/**
 * The most read stories of the window.
 *
 * @param string $key   Window key.
 * @param int    $limit How many.
 * @return array<int, array{lang:string,post_id:int,hits:int}>
 */
function rs_stats_top_stories( $key, $limit = 10 ) {
	$rows = rs_stats_query(
		'SELECT lang, post_id, SUM(hits) AS hits FROM {table}
		 WHERE {where} AND post_id > 0
		 GROUP BY lang, post_id ORDER BY hits DESC LIMIT %d',
		$key,
		array( max( 1, (int) $limit ) )
	);

	return array_map(
		function ( $row ) {
			return array(
				'lang'    => (string) $row['lang'],
				'post_id' => (int) $row['post_id'],
				'hits'    => (int) $row['hits'],
			);
		},
		$rows
	);
}

/**
 * The most opened pages that are not stories: the portfolio and its
 * projects, the CV, the showreel, the home page and the rest.
 *
 * @param string $key   Window key.
 * @param int    $limit How many.
 * @return array<int, array{lang:string,path:string,hits:int}>
 */
function rs_stats_top_pages( $key, $limit = 10 ) {
	$rows = rs_stats_query(
		"SELECT lang, path, SUM(hits) AS hits FROM {table}
		 WHERE {where} AND post_id = 0 AND path <> '' AND path <> '404'
		 GROUP BY lang, path ORDER BY hits DESC LIMIT %d",
		$key,
		array( max( 1, (int) $limit ) )
	);

	return array_map(
		function ( $row ) {
			return array(
				'lang' => (string) $row['lang'],
				'path' => (string) $row['path'],
				'hits' => (int) $row['hits'],
			);
		},
		$rows
	);
}

/**
 * Openings grouped by one column, biggest first.
 *
 * @param string $column One of source, device, lang, country.
 * @param string $key    Window key.
 * @param int    $limit  How many rows.
 * @return array<int, array{label:string,hits:int}>
 */
function rs_stats_grouped( $column, $key, $limit = 12 ) {
	if ( ! in_array( $column, array( 'source', 'device', 'lang', 'country' ), true ) ) {
		return array();
	}

	$rows = rs_stats_query(
		"SELECT {$column} AS label, SUM(hits) AS hits FROM {table}
		 WHERE {where} GROUP BY {$column} ORDER BY hits DESC LIMIT %d",
		$key,
		array( max( 1, (int) $limit ) )
	);

	return array_map(
		function ( $row ) {
			return array(
				'label' => (string) $row['label'],
				'hits'  => (int) $row['hits'],
			);
		},
		$rows
	);
}

/**
 * When in the day people read, across the whole window.
 *
 * @param string $key Window key.
 * @return array<int, int> Hour (0–23) => openings.
 */
function rs_stats_by_hour( $key ) {
	$hours = array_fill( 0, 24, 0 );

	foreach ( rs_stats_query( 'SELECT hour, SUM(hits) AS hits FROM {table} WHERE {where} GROUP BY hour', $key ) as $row ) {
		$hours[ (int) $row['hour'] % 24 ] = (int) $row['hits'];
	}

	return $hours;
}

/**
 * Which day of the week people read on. Worked out here from the dates
 * rather than by the database, whose weekday function differs between
 * MySQL and SQLite.
 *
 * @param string $key Window key.
 * @return array<int, int> 0 = Sunday … 6 = Saturday.
 */
function rs_stats_by_weekday( $key ) {
	$days = array_fill( 0, 7, 0 );

	foreach ( rs_stats_query( 'SELECT day, SUM(hits) AS hits FROM {table} WHERE {where} GROUP BY day', $key ) as $row ) {
		$time = strtotime( $row['day'] . ' 12:00:00 UTC' );

		if ( $time ) {
			$days[ (int) gmdate( 'w', $time ) ] += (int) $row['hits'];
		}
	}

	return $days;
}

/**
 * How far readers got, across a window.
 *
 * @param string $key Window key.
 * @return array{average:int,buckets:array<int,int>,total:int,seconds:int}
 */
function rs_stats_depth_summary( $key ) {
	$buckets = array(
		0   => 0,
		25  => 0,
		50  => 0,
		75  => 0,
		100 => 0,
	);
	$total   = 0;
	$weight  = 0;
	$secs    = 0;

	foreach ( rs_stats_query( 'SELECT bucket, SUM(n) AS n, SUM(secs) AS secs FROM {table} WHERE {where} GROUP BY bucket', $key, array(), 'depth' ) as $row ) {
		$bucket = (int) $row['bucket'];

		if ( ! isset( $buckets[ $bucket ] ) ) {
			continue;
		}

		$buckets[ $bucket ] += (int) $row['n'];
		$total              += (int) $row['n'];
		$weight             += $bucket * (int) $row['n'];
		$secs               += (int) $row['secs'];
	}

	return array(
		'average' => $total > 0 ? (int) round( $weight / $total ) : 0,
		'buckets' => $buckets,
		'total'   => $total,
		'seconds' => $total > 0 ? (int) round( $secs / $total ) : 0,
	);
}

/**
 * How far, and how long, each story was read, for the table.
 *
 * @param string $key Window key.
 * @return array<string, array{depth:int,seconds:int,n:int}> Keyed "lang|id".
 */
function rs_stats_depth_by_story( $key ) {
	$out = array();

	foreach ( rs_stats_query( 'SELECT lang, post_id, SUM(n) AS n, SUM(bucket * n) AS weight, SUM(secs) AS secs FROM {table} WHERE {where} GROUP BY lang, post_id', $key, array(), 'depth' ) as $row ) {
		$n = max( 1, (int) $row['n'] );

		$out[ $row['lang'] . '|' . (int) $row['post_id'] ] = array(
			'depth'   => (int) round( (int) $row['weight'] / $n ),
			'seconds' => (int) round( (int) $row['secs'] / $n ),
			'n'       => (int) $row['n'],
		);
	}

	return $out;
}

/**
 * The stories climbing fastest: this window against the one before it.
 *
 * Anything with fewer than a handful of reads is left out: with small
 * numbers every rise is a thousand per cent and none of it means anything.
 *
 * @param string $key   Window key.
 * @param int    $limit How many.
 * @return array<int, array{lang:string,post_id:int,now:int,before:int,change:int}>
 */
function rs_stats_climbing( $key, $limit = 6 ) {
	$reads = function ( $before ) use ( $key ) {
		$out = array();

		foreach ( rs_stats_query( 'SELECT lang, post_id, SUM(hits) AS hits FROM {table} WHERE {where} AND post_id > 0 GROUP BY lang, post_id', $key, array(), 'stats', $before ) as $row ) {
			$out[ $row['lang'] . '|' . (int) $row['post_id'] ] = (int) $row['hits'];
		}

		return $out;
	};

	$now    = $reads( false );
	$was    = $reads( true );
	$rising = array();

	foreach ( $now as $story => $hits ) {
		$before = isset( $was[ $story ] ) ? $was[ $story ] : 0;

		if ( $hits < 5 || $hits <= $before ) {
			continue;
		}

		list( $lang, $post_id ) = explode( '|', $story );

		$rising[] = array(
			'lang'    => $lang,
			'post_id' => (int) $post_id,
			'now'     => $hits,
			'before'  => $before,
			'change'  => $before > 0 ? (int) round( 100 * ( $hits - $before ) / $before ) : 100,
		);
	}

	usort(
		$rising,
		function ( $a, $b ) {
			return $a['change'] === $b['change'] ? $b['now'] <=> $a['now'] : $b['change'] <=> $a['change'];
		}
	);

	return array_slice( $rising, 0, max( 1, (int) $limit ) );
}

/**
 * Where visits begin: the first page a reader lands on, story or not.
 *
 * @param string $key   Window key.
 * @param int    $limit How many.
 * @return array<int, array{lang:string,post_id:int,path:string,visits:int}>
 */
function rs_stats_entry_pages( $key, $limit = 8 ) {
	$rows = rs_stats_query(
		'SELECT lang, post_id, path, SUM(visits) AS visits FROM {table}
		 WHERE {where} AND visits > 0
		 GROUP BY lang, post_id, path ORDER BY visits DESC LIMIT %d',
		$key,
		array( max( 1, (int) $limit ) )
	);

	return array_map(
		function ( $row ) {
			return array(
				'lang'    => (string) $row['lang'],
				'post_id' => (int) $row['post_id'],
				'path'    => (string) $row['path'],
				'visits'  => (int) $row['visits'],
			);
		},
		$rows
	);
}

/**
 * What readers did, of one kind, biggest first.
 *
 * @param string $kind  A key of rs_stats_kinds().
 * @param string $key   Window key.
 * @param int    $limit How many.
 * @return array<int, array{lang:string,post_id:int,label:string,hits:int}>
 */
function rs_stats_events( $kind, $key, $limit = 8 ) {
	$kinds = rs_stats_kinds();

	if ( ! isset( $kinds[ $kind ] ) ) {
		return array();
	}

	$rows = rs_stats_query(
		$kinds[ $kind ]
			? "SELECT lang, post_id, '' AS label, SUM(n) AS n FROM {table} WHERE {where} AND kind = %s GROUP BY lang, post_id ORDER BY n DESC LIMIT %d"
			: "SELECT '' AS lang, 0 AS post_id, label, SUM(n) AS n FROM {table} WHERE {where} AND kind = %s GROUP BY label ORDER BY n DESC LIMIT %d",
		$key,
		array( $kind, max( 1, (int) $limit ) ),
		'events'
	);

	return array_map(
		function ( $row ) {
			return array(
				'lang'    => (string) $row['lang'],
				'post_id' => (int) $row['post_id'],
				'label'   => (string) $row['label'],
				'hits'    => (int) $row['n'],
			);
		},
		$rows
	);
}

/**
 * The most asked-for searches, all time.
 *
 * @param int  $limit  How many.
 * @param bool $missed Only the ones that found nothing, most missed first.
 * @return array<int, array{lang:string,term:string,hits:int,miss:int,last:string}>
 */
function rs_stats_searches( $limit = 10, $missed = false ) {
	$rows = array();

	foreach ( rs_stats_list( 'searches' ) as $key => $row ) {
		$parts = explode( '|', (string) $key, 2 );
		$miss  = isset( $row['miss'] ) ? (int) $row['miss'] : 0;

		if ( $missed && ! $miss ) {
			continue;
		}

		$rows[] = array(
			'lang' => $parts[0],
			'term' => isset( $parts[1] ) ? $parts[1] : (string) $key,
			'hits' => isset( $row['n'] ) ? (int) $row['n'] : 0,
			'miss' => $miss,
			'last' => isset( $row['d'] ) ? (string) $row['d'] : '',
		);
	}

	$field = $missed ? 'miss' : 'hits';

	usort(
		$rows,
		function ( $a, $b ) use ( $field ) {
			return $b[ $field ] <=> $a[ $field ];
		}
	);

	return array_slice( $rows, 0, max( 1, (int) $limit ) );
}

/**
 * The addresses readers asked for and did not get, most asked first.
 *
 * @param int $limit How many.
 * @return array<int, array{lang:string,path:string,hits:int,from:string,last:string}>
 */
function rs_stats_missing( $limit = 8 ) {
	$rows = rs_stats_list( 'missing' );

	uasort(
		$rows,
		function ( $a, $b ) {
			return (int) $b['n'] <=> (int) $a['n'];
		}
	);

	$out = array();

	foreach ( array_slice( $rows, 0, max( 1, (int) $limit ), true ) as $key => $row ) {
		$parts = explode( '|', (string) $key, 2 );
		$out[] = array(
			'lang' => $parts[0],
			'path' => isset( $parts[1] ) ? $parts[1] : (string) $key,
			'hits' => (int) $row['n'],
			'from' => isset( $row['from'] ) ? (string) $row['from'] : '',
			'last' => isset( $row['d'] ) ? (string) $row['d'] : '',
		);
	}

	return $out;
}

/** Forget the addresses that led nowhere, once they have been dealt with. */
function rs_stats_clear_missing() {
	delete_site_option( 'rs_stats_missing' );
}

/** @return string The day counting began, or '' while nothing is counted. */
function rs_stats_first_day() {
	global $wpdb;

	rs_stats_ready();

	$table = rs_stats_table();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- The theme's own table; no values.
	$day = $wpdb->get_var( "SELECT MIN(day) FROM {$table}" );

	return $day ? (string) $day : '';
}
