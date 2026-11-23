<?php
/**
 * The work log page, served by rs_work_log_render().
 *
 * Both editions, each in its own language. In the showreel's look: a short
 * pitch, a calendar strip of the days work went up, then two views of the
 * same pieces (rs_work_log_items()):
 *
 * - List: a timeline, newest first, a month at a time and within it a week
 *   at a time (days 1-7, 8-14, 15-21, the rest): the week beside the line,
 *   its pieces in a grid of cards of one size, each with its kind, day, name
 *   and one line about it. A piece with clips is one card too, marked with
 *   their number; the clips stand in a strip under the pop-up player. While
 *   the page scrolls, the line fills in orange up to the middle of the screen
 *   and the bar shows the date there, like a timecode.
 * - Editor: the pieces as clips on an editing timeline, one track per kind,
 *   with a monitor above, an overview of the whole time below it and zoom.
 *
 * A card shows a silent preview of its piece when the pointer rests on it
 * (on a touch screen wider than a phone, when it sits in the middle), and plays it in
 * the pop-up player when clicked. The list is rendered here, so the full
 * page cache keeps it as it is; the editor is drawn by the script, only
 * when it is opened. Without the script every card still opens its piece.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rs_wl_en    = rs_is_en();
$rs_wl_t     = function ( $bn, $en ) use ( $rs_wl_en ) {
	return $rs_wl_en ? $en : $bn;
};
$rs_wl_kinds = rs_work_log_kinds();
$rs_wl_items = rs_work_log_page_items();
$rs_wl_manage = rs_work_log_manage();
$rs_wl_email = rs_option( 'rs_email' ) ? rs_option( 'rs_email' ) : 'lettertosohan@gmail.com';

/* Newest first; a stable sort keeps the listed order within a day. */
usort(
	$rs_wl_items,
	function ( $a, $b ) {
		return strcmp( $b['date'], $a['date'] );
	}
);

$rs_wl_abs = function ( $path ) {
	return preg_match( '#^https?://#', $path ) ? $path : RS_URI . '/' . ltrim( $path, '/' );
};

$rs_wl_months_bn = rs_bn_months_full();

/** "14 অক্টোবর" / "14 October". */
$rs_wl_day = function ( $ymd ) use ( $rs_wl_en, $rs_wl_months_bn ) {
	$ts = strtotime( $ymd . ' 12:00:00 UTC' );
	$d  = (int) gmdate( 'j', $ts );
	$m  = (int) gmdate( 'n', $ts );
	return $rs_wl_en ? $d . ' ' . gmdate( 'F', $ts ) : rs_bn_digits( $d ) . ' ' . $rs_wl_months_bn[ $m ];
};

/** "অক্টোবর ২০২৬" / "October 2026", from Y-m. */
$rs_wl_month = function ( $ym ) use ( $rs_wl_en, $rs_wl_months_bn ) {
	$ts = strtotime( $ym . '-15 12:00:00 UTC' );
	return $rs_wl_en ? gmdate( 'F Y', $ts ) : $rs_wl_months_bn[ (int) gmdate( 'n', $ts ) ] . ' ' . rs_bn_digits( gmdate( 'Y', $ts ) );
};

/** "৩টি কাজ" / "3 pieces". */
$rs_wl_count = function ( $n ) use ( $rs_wl_en ) {
	return $rs_wl_en ? $n . ( 1 === $n ? ' piece' : ' pieces' ) : rs_bn_digits( $n ) . 'টি কাজ';
};

/** "21 নভে" / "21 Nov", on a card. */
$rs_wl_short = function ( $ymd ) use ( $rs_wl_en, $rs_wl_day ) {
	return $rs_wl_en ? gmdate( 'j M', strtotime( $ymd . ' 12:00:00 UTC' ) ) : $rs_wl_day( $ymd );
};

/** The name of a piece or a clip on this edition. */
$rs_wl_name = function ( $it ) use ( $rs_wl_en ) {
	return rs_work_log_name( $it, $rs_wl_en );
};

/* Month → week → pieces, newest first. A week is days 1-7, 8-14, 15-21 or the rest of its month. */
$rs_wl_tree = array();
foreach ( $rs_wl_items as $rs_wl_it ) {
	$rs_wl_w = min( 3, (int) floor( ( (int) substr( $rs_wl_it['date'], 8, 2 ) - 1 ) / 7 ) );
	$rs_wl_tree[ substr( $rs_wl_it['date'], 0, 7 ) ][ $rs_wl_w ][] = $rs_wl_it;
}

/** "15–21 নভেম্বর" / "15–21 November", from Y-m and the week's number. */
$rs_wl_week = function ( $ym, $w ) use ( $rs_wl_day ) {
	$last = 3 === $w ? gmdate( 't', strtotime( $ym . '-15 12:00:00 UTC' ) ) : 7 * ( $w + 1 );
	return rs_bn_digits( 7 * $w + 1 ) . '–' . $rs_wl_day( sprintf( '%s-%02d', $ym, $last ) );
};

/* Only the kinds in use get a filter and a track; Client work counts every piece marked so. */
$rs_wl_used = array();
foreach ( $rs_wl_kinds as $rs_wl_k => $rs_wl_kl ) {
	$rs_wl_n = count(
		array_filter(
			$rs_wl_items,
			function ( $it ) use ( $rs_wl_k ) {
				return 'client' === $rs_wl_k ? ! empty( $it['client'] ) : $it['kind'] === $rs_wl_k;
			}
		)
	);
	if ( $rs_wl_n ) {
		$rs_wl_used[ $rs_wl_k ] = $rs_wl_n;
	}
}

/*
 * The calendar: the recent stretch a day to a cell, every month from this one
 * back to the oldest piece before three empty months in a row; work older
 * than that a year to a row, a month to a cell.
 */
$rs_wl_days_on = array();
foreach ( $rs_wl_items as $rs_wl_it ) {
	$rs_wl_days_on[ $rs_wl_it['date'] ] = true;
}
$rs_wl_first_ym = substr( end( $rs_wl_items )['date'], 0, 7 );
reset( $rs_wl_items );
$rs_wl_now_ym = current_time( 'Y-m' );
if ( $rs_wl_now_ym < $rs_wl_first_ym ) {
	$rs_wl_now_ym = $rs_wl_first_ym;
}
$rs_wl_strip = array();
$rs_wl_run   = array();
$rs_wl_empty = 0;
for ( $rs_wl_ts = strtotime( $rs_wl_now_ym . '-01 12:00:00 UTC' ); gmdate( 'Y-m', $rs_wl_ts ) >= $rs_wl_first_ym; $rs_wl_ts = strtotime( '-1 month', $rs_wl_ts ) ) {
	$rs_wl_ym    = gmdate( 'Y-m', $rs_wl_ts );
	$rs_wl_run[] = $rs_wl_ym;
	if ( isset( $rs_wl_tree[ $rs_wl_ym ] ) ) {
		$rs_wl_strip = $rs_wl_run;
		$rs_wl_empty = 0;
	} elseif ( ++$rs_wl_empty > 2 && $rs_wl_strip ) {
		break;
	}
}
$rs_wl_years = array();
$rs_wl_from  = end( $rs_wl_strip );
foreach ( $rs_wl_tree as $rs_wl_ym => $rs_wl_weeks ) {
	if ( $rs_wl_ym < $rs_wl_from ) {
		$rs_wl_years[ substr( $rs_wl_ym, 0, 4 ) ][ (int) substr( $rs_wl_ym, 5, 2 ) ] = array_sum( array_map( 'count', $rs_wl_weeks ) );
	}
}
krsort( $rs_wl_years );
/* Months from the first piece's to this one, for the line above the calendar. */
$rs_wl_span_m = ( (int) substr( $rs_wl_now_ym, 0, 4 ) - (int) substr( $rs_wl_first_ym, 0, 4 ) ) * 12 + (int) substr( $rs_wl_now_ym, 5, 2 ) - (int) substr( $rs_wl_first_ym, 5, 2 ) + 1;

/* The other edition, for hreflang and the language link. */
$rs_wl_pair  = rs_edition_pair();
$rs_wl_here  = rs_work_log_url();
$rs_wl_other = $rs_wl_pair ? rs_work_log_url( $rs_wl_pair['other'] ) : '';
$rs_wl_lang  = $rs_wl_en ? 'en' : 'bn';

$rs_wl_title = $rs_wl_t( 'কাজের খাতা', 'Work Log' );
$rs_wl_desc  = $rs_wl_t(
	'রাইসুল সোহানের সব কাজ, তারিখ ধরে: টুল, ম্যাপ অ্যানিমেশন, ডকুমেন্টারি আর শোরিল। প্রতিটা কাজ ব্রাউজারেই চলে।',
	'Everything Raisul Sohan makes, by date: tools, map animation, documentary and showreels. Every piece plays in the browser.'
);
$rs_wl_og = $rs_wl_abs( $rs_wl_items[0]['img'] );

$rs_wl_has45 = function ( $it ) {
	return ! empty( $it['src45'] ) || ! empty( $it['map'] );
};

/** The address a card links to without the script: the piece itself. */
$rs_wl_open = function ( $it ) use ( $rs_wl_abs ) {
	if ( ! empty( $it['watch'] ) ) {
		return $it['watch'];
	}
	$src = $rs_wl_abs( $it['src'] );
	if ( ! empty( $it['map'] ) ) {
		$src .= '?ver=' . rawurlencode( RS_VERSION );
	}
	return $src;
};

$rs_wl_icon_play = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.6-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5Z"/></svg>';
$rs_wl_icon_clips = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.5 1.5h9M2.5 4h11M2 6.5h12v8H2z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/></svg>';

/** "11:12", a length in seconds. */
$rs_wl_dur = function ( $s ) {
	$out = $s >= 3600 ? sprintf( '%d:%02d:%02d', $s / 3600, ( $s % 3600 ) / 60, $s % 60 ) : sprintf( '%d:%02d', $s / 60, $s % 60 );
	return rs_bn_digits( $out );
};

/** One card: every piece, with or without clips. */
$rs_wl_card = function ( $it ) use ( $rs_wl_t, $rs_wl_abs, $rs_wl_open, $rs_wl_has45, $rs_wl_en, $rs_wl_icon_play, $rs_wl_icon_clips, $rs_wl_name, $rs_wl_short, $rs_wl_dur ) {
	$n = empty( $it['clips'] ) ? 0 : count( $it['clips'] );
	ob_start();
	?>
	<a class="card<?php echo $n ? ' card--set' : ''; ?>" href="<?php echo esc_url( $rs_wl_open( $it ) ); ?>" data-id="<?php echo esc_attr( $it['id'] ); ?>" target="_blank" rel="noopener">
		<span class="thumb">
			<img src="<?php echo esc_url( $rs_wl_abs( $it['img'] ) ); ?>" alt="" width="640" height="360" loading="lazy" decoding="async">
			<span class="pv-tag" aria-hidden="true"><i></i><?php echo esc_html( $rs_wl_t( 'প্রিভিউ · শব্দ বন্ধ', 'Preview · muted' ) ); ?></span>
			<span class="play" aria-hidden="true"><?php echo $rs_wl_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A fixed SVG. ?></span>
			<span class="tags">
				<?php if ( ! empty( $it['yt'] ) ) : ?><span><?php echo $rs_wl_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A fixed SVG. ?><?php echo esc_html( 'YouTube' . ( ! empty( $it['dur'] ) ? ' · ' . $rs_wl_dur( $it['dur'] ) : '' ) ); ?></span><?php endif; ?>
				<?php if ( $n ) : ?><span><?php echo $rs_wl_icon_clips; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A fixed SVG. ?><?php echo esc_html( $rs_wl_en ? $n . ' clips' : rs_bn_digits( $n ) . 'টি ক্লিপ' ); ?></span><?php endif; ?>
				<?php if ( $rs_wl_has45( $it ) ) : ?><span>16:9 · 4:5</span><?php endif; ?>
			</span>
		</span>
		<span class="body">
			<span class="meta"><span class="kicker"><?php echo esc_html( $rs_wl_t( $it['type_bn'], $it['type_en'] ) ); ?></span><time datetime="<?php echo esc_attr( $it['date'] ); ?>"><?php echo esc_html( $rs_wl_short( $it['date'] ) ); ?></time></span>
			<span class="title"><?php echo esc_html( $rs_wl_name( $it ) ); ?></span>
			<span class="desc"><?php echo esc_html( $rs_wl_t( $it['desc_bn'], $it['desc_en'] ) ); ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
};

$rs_wl_t_title = $rs_wl_title . ' · ' . get_bloginfo( 'name' );
?><!doctype html>
<html lang="<?php echo esc_attr( $rs_wl_lang ); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $rs_wl_t_title ); ?></title>
<meta name="description" content="<?php echo esc_attr( $rs_wl_desc ); ?>">
<link rel="canonical" href="<?php echo esc_url( $rs_wl_here ); ?>">
<?php if ( $rs_wl_other ) : ?>
<link rel="alternate" hreflang="<?php echo esc_attr( $rs_wl_lang ); ?>" href="<?php echo esc_url( $rs_wl_here ); ?>">
<link rel="alternate" hreflang="<?php echo esc_attr( $rs_wl_pair['lang'] ); ?>" href="<?php echo esc_url( $rs_wl_other ); ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo esc_attr( $rs_wl_title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $rs_wl_desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( $rs_wl_here ); ?>">
<meta property="og:image" content="<?php echo esc_url( $rs_wl_og ); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#000000">
<?php wp_site_icon(); ?>
<?php rs_inline_fonts(); ?>
<style>
	/* The showreel's look: black, neutral tiles, one orange, bold sans. */
	:root {
		--bg: #000; --tile: #0b0b0d; --tile2: #161617; --tile3: #1d1d1f; --line: rgba(255, 255, 255, .1); --line2: rgba(255, 255, 255, .22);
		--fg: #f5f5f7; --fg2: #d2d2d7; --mut: #a1a1a6; --dim: #6e6e73;
		--acc: #ff4a1c; --acc2: #ff6238; --ink: #0b0a0d;
		--sans: "Inter", "Anek Bangla", -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
		--out: cubic-bezier(.22, 1, .36, 1);
		--gut: clamp(16px, 4vw, 32px);
		--bar: 64px;
		color-scheme: dark;
	}
	* { box-sizing: border-box; }
	[hidden] { display: none !important; }
	html { scroll-behavior: smooth; scroll-padding-top: 146px; }
	@media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
	body { margin: 0; color: var(--fg); background: var(--bg); font: 17px/1.5 var(--sans); -webkit-font-smoothing: antialiased; overflow-x: clip; }
	body.locked { overflow: hidden; }
	a { color: inherit; }
	img { max-width: 100%; }
	button { font: inherit; color: inherit; }
	:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; }
	.wrap { max-width: 1200px; margin-inline: auto; padding-inline: var(--gut); }
	.dot { display: inline-block; width: .19em; height: .19em; margin-left: .08em; border-radius: 50%; background: var(--acc); }
	.sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
	.pill { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 44px; padding: 0 1.3rem; border: 0; border-radius: 980px; cursor: pointer; text-decoration: none; font-weight: 600; font-size: 16px; color: var(--fg); background: rgba(255, 255, 255, .09); transition: background-color .2s; }
	.pill:hover { background: rgba(255, 255, 255, .16); }
	.pill--acc { background: var(--acc); color: var(--ink); font-weight: 700; }
	.pill--acc:hover { background: var(--acc2); }
	.pill svg { width: 14px; height: 14px; fill: currentColor; }
	.link-btn { all: unset; cursor: pointer; color: var(--acc); font-size: 14px; }
	.link-btn:hover { color: var(--acc2); }
	.link-btn:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; }

	/* the glass bar */
	.top { position: sticky; top: 0; z-index: 20; background: rgba(0, 0, 0, .8); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); border-bottom: 1px solid var(--line); }
	.top-in { height: var(--bar); display: flex; align-items: center; justify-content: space-between; gap: 16px; }
	.brand { font-weight: 700; font-size: 19px; text-decoration: none; white-space: nowrap; }
	.nav { display: flex; gap: 2px; overflow-x: auto; scrollbar-width: none; }
	.nav a { padding: 10px 13px; border-radius: 980px; color: var(--mut); text-decoration: none; font-size: 15px; white-space: nowrap; }
	.nav a:hover { color: var(--fg); }
	.nav a[aria-current] { color: var(--fg); background: rgba(255, 255, 255, .1); }

	/* hero */
	.hero { padding-block: 84px 36px; text-align: center; }
	.kicker { display: block; color: var(--acc); font-size: 14px; font-weight: 600; }
	.hero .kicker { font-size: 15px; margin-bottom: 14px; }
	.hero h1 { margin: 0 auto; max-width: 900px; font-size: clamp(44px, 7vw, 76px); line-height: 1.1; font-weight: 700; }
	.hero p { margin: 22px auto 0; max-width: 640px; color: var(--mut); font-size: clamp(17px, 2vw, 20px); line-height: 1.6; }

	/* the calendar strip */
	.cal { max-width: 1000px; margin: 0 auto 48px; padding: 24px 28px; border: 1px solid var(--line); border-radius: 22px; background: var(--tile); }
	.cal-head { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px 16px; margin-bottom: 14px; }
	.cal-head b { font-size: 17px; }
	.cal-head span { color: var(--mut); font-size: 14px; }
	.cal-head span i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; background: var(--acc); margin-right: 6px; }
	.cal-row { display: flex; align-items: center; gap: 14px; margin-top: 8px; }
	.cal-row > a, .cal-row > span { width: 92px; flex-shrink: 0; font-size: 14px; color: var(--fg2); text-decoration: none; }
	.cal-days { flex: 1; display: grid; grid-template-columns: repeat(31, minmax(0, 1fr)); gap: 4px; }
	.hd { height: 20px; border-radius: 4px; background: rgba(255, 255, 255, .08); }
	.hd.on { background: var(--acc); }
	a.hd.on:hover { background: var(--acc2); }
	.hd.fut { background: none; border: 1px dashed rgba(255, 255, 255, .14); }
	/* older work: a year to a row, a month to a cell */
	.cal-row:not(.cal-yr) + .cal-yr { margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--line); }
	.cal-ms { grid-template-columns: repeat(12, minmax(0, 1fr)); }
	.cal-ms .hd { display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 11px; line-height: 1; white-space: nowrap; color: var(--dim); text-decoration: none; }
	.cal-ms .hd.on { color: var(--ink); font-weight: 600; }

	/* the controls under the bar */
	.ctl { position: sticky; top: var(--bar); z-index: 15; background: rgba(0, 0, 0, .86); -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, .08); }
	.ctl-in { display: flex; align-items: center; gap: 10px 14px; padding-block: 9px; }
	.seg { display: inline-flex; flex-shrink: 0; padding: 4px; border-radius: 980px; background: rgba(255, 255, 255, .1); }
	.seg[hidden] { display: none; }
	.seg button { min-height: 38px; padding: 0 16px; border: 0; border-radius: 980px; background: none; color: var(--fg2); font-size: 14px; cursor: pointer; }
	.seg button[aria-pressed="true"] { background: var(--fg); color: var(--ink); font-weight: 600; }
	.chips-f { flex: 1; min-width: 0; display: flex; gap: 5px; overflow-x: auto; overscroll-behavior-x: contain; scrollbar-width: none; }
	.chips-f.is-more { -webkit-mask-image: linear-gradient(90deg, #000 calc(100% - 44px), transparent); mask-image: linear-gradient(90deg, #000 calc(100% - 44px), transparent); }
	.chip { flex-shrink: 0; min-height: 40px; padding: 0 11px; border-radius: 980px; border: 1px solid var(--line2); background: none; color: var(--fg2); font-size: 13.5px; white-space: nowrap; cursor: pointer; }
	.chip[aria-pressed="true"] { border-color: var(--fg); background: var(--fg); color: var(--ink); font-weight: 600; }
	.tc { display: inline-flex; align-items: center; gap: 9px; padding: 7px 13px; border-radius: 8px; border: 1px solid rgba(255, 74, 28, .5); color: var(--acc); font-weight: 700; font-size: 15px; font-variant-numeric: tabular-nums; opacity: 0; transition: opacity .25s; white-space: nowrap; }
	.tc.on { opacity: 1; }
	.ctl .tc { display: none; }
	.tc i { width: 8px; height: 8px; border-radius: 50%; background: var(--acc); }

	.chip small { display: none; margin-left: 6px; font-size: 12px; opacity: .65; font-variant-numeric: tabular-nums; }
	.chip[aria-pressed="true"] small { display: inline; }

	/* the list: the line on the left with a week beside it, its cards in a grid */
	.tl { position: relative; padding-block: 48px 72px; }
	.tl-rail, .tl-fill { position: absolute; top: 60px; bottom: 88px; left: calc(var(--gut) + 6px); width: 2px; margin-left: -1px; }
	.tl-rail { background: rgba(255, 255, 255, .14); }
	.tl-fill { background: var(--acc); transform-origin: top; transform: scaleY(0); }
	.tl-head { position: absolute; top: 60px; left: calc(var(--gut) + 6px); width: 16px; height: 16px; margin: -8px 0 0 -8px; border-radius: 50%; background: var(--acc); box-shadow: 0 0 0 5px rgba(255, 74, 28, .25), 0 0 24px rgba(255, 74, 28, .6); z-index: 3; pointer-events: none; opacity: 0; transition: opacity .25s; }
	.tl-head.on { opacity: 1; }
	.tl-mhead { position: relative; z-index: 2; display: flex; align-items: baseline; flex-wrap: wrap; gap: 0 14px; margin: 0 0 22px; padding-block: 4px; background: var(--bg); font-size: clamp(26px, 3.4vw, 36px); line-height: 1.2; font-weight: 700; }
	.tl-mhead small { font-size: 16px; font-weight: 500; color: var(--mut); white-space: nowrap; }
	.tl-week { position: relative; display: grid; grid-template-columns: 138px minmax(0, 1fr); column-gap: 30px; padding-bottom: 48px; }
	.tl-node { position: sticky; top: calc(var(--bar) + 80px); align-self: start; z-index: 2; padding: 2px 0 2px 30px; }
	.tl-node > i { position: absolute; left: 0; top: 7px; width: 12px; height: 12px; border-radius: 50%; background: var(--acc); box-shadow: 0 0 0 5px var(--bg); }
	.tl-node b { display: block; font-size: 16px; line-height: 1.35; font-weight: 700; }
	.tl-node span { display: block; margin-top: 2px; color: var(--mut); font-size: 14px; }
	.tl-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 26px 20px; }
	.cell { display: flex; flex-direction: column; gap: 10px; min-width: 0; scroll-margin-top: 0; }
	.cell.flash .card { border-color: var(--acc); box-shadow: 0 0 0 3px rgba(255, 74, 28, .35); }

	/* cards, all of one size */
	.card { position: relative; flex: 1; display: flex; flex-direction: column; text-decoration: none; border: 1px solid var(--line); border-radius: 16px; background: var(--tile); overflow: hidden; transition: transform .4s var(--out), border-color .3s, box-shadow .3s; }
	.card:hover { transform: translateY(-3px); border-color: rgba(255, 74, 28, .6); }
	.thumb { position: relative; display: block; flex-shrink: 0; aspect-ratio: 16 / 9; overflow: hidden; background: #111; }
	.thumb > img { position: relative; width: 100%; height: 100%; object-fit: cover; display: block; }
	/* a standing picture is shown whole, over a blur of itself */
	.thumb.is-tall::before { content: ""; position: absolute; inset: -12%; background: var(--p) center / cover; filter: blur(16px) brightness(.5); }
	.thumb.is-tall > img { object-fit: contain; }
	.thumb iframe { position: absolute; left: 0; top: 0; width: 1280px; height: 720px; border: 0; transform-origin: 0 0; pointer-events: none; opacity: 0; transition: opacity .35s; background: #050506; }
	.thumb iframe.on { opacity: 1; }
	.play { position: absolute; inset: 0; margin: auto; width: 52px; height: 52px; border-radius: 50%; background: var(--acc); color: var(--ink); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .25s; }
	.play svg { width: 18px; height: 18px; fill: currentColor; margin-left: 2px; }
	.card:hover .play, .card:focus-visible .play { opacity: 1; }
	.previewing .play { opacity: 0 !important; }
	.tags { position: absolute; left: 10px; right: 10px; bottom: 10px; display: flex; gap: 6px; transition: opacity .25s; }
	.tags > span { display: inline-flex; align-items: center; gap: 6px; padding: 3px 9px; border-radius: 980px; background: rgba(0, 0, 0, .78); color: var(--fg); font-size: 12px; font-weight: 600; line-height: 1.5; white-space: nowrap; }
	.tags svg { width: 12px; height: 12px; fill: currentColor; }
	.tags > span + span { margin-left: auto; color: var(--fg2); }
	.pv-tag { position: absolute; left: 10px; top: 10px; z-index: 2; display: flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 980px; background: rgba(0, 0, 0, .75); font-size: 12px; font-weight: 600; opacity: 0; transition: opacity .25s; }
	.pv-tag i { width: 7px; height: 7px; border-radius: 50%; background: var(--acc); }
	.previewing .pv-tag { opacity: 1; }
	.previewing .tags { opacity: 0; }
	.body { display: flex; flex-direction: column; flex: 1; padding: 14px 16px 16px; }
	.meta { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; font-size: 13px; }
	.meta .kicker { font-size: inherit; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.meta time { flex-shrink: 0; color: var(--mut); }
	.body .title { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; margin: 5px 0 5px; font-size: 18px; font-weight: 700; line-height: 1.35; }
	.body .desc { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; color: var(--mut); font-size: 14px; line-height: 1.5; }
	.body .desc:empty { display: none; }
	.chips { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
	.chips span { padding: 5px 12px; border-radius: 980px; background: rgba(255, 255, 255, .1); font-size: 13px; color: var(--fg2); }
	.tl-end { position: relative; z-index: 2; }
	.tl-end span { display: inline-block; padding: 8px 18px; border-radius: 980px; border: 1px solid var(--line2); background: var(--bg); color: var(--mut); font-size: 15px; }

	/* the editor */
	.ed { padding-block: 28px 72px; }
	.ed-help { margin: 0 0 18px; padding: 14px 18px; list-style: none; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px 16px; border-radius: 14px; background: rgba(255, 74, 28, .1); border: 1px solid rgba(255, 74, 28, .35); }
	.ed-help li { display: flex; gap: 10px; align-items: flex-start; font-size: 14px; line-height: 1.5; }
	.n { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--acc); color: var(--ink); font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; }
	.n--s { width: 20px; height: 20px; font-size: 11px; }
	.ed-top { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 20px; margin-bottom: 18px; }
	.mon { position: relative; aspect-ratio: 16 / 9; border-radius: 14px; overflow: hidden; background: #111114; }
	.mon img { width: 100%; height: 100%; object-fit: cover; display: block; }
	.mon .stamp { position: absolute; left: 14px; top: 14px; display: flex; align-items: center; gap: 8px; padding: 5px 10px; border-radius: 6px; background: rgba(0, 0, 0, .75); color: var(--acc); font-size: 13px; font-weight: 700; font-variant-numeric: tabular-nums; }
	.mon button { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: none; cursor: pointer; }
	.insp { border: 1px solid var(--line); border-radius: 14px; background: var(--tile); padding: 22px 24px; display: flex; flex-direction: column; gap: 10px; min-width: 0; }
	.insp h2 { margin: 0; font-size: 28px; line-height: 1.25; }
	.insp p { margin: 0; color: var(--mut); font-size: 16px; line-height: 1.6; }
	.insp .chips { margin-top: 0; }
	.insp-sub { border-top: 1px solid var(--line); padding-top: 12px; }
	.insp-sub > span { display: block; color: var(--mut); font-size: 13px; margin-bottom: 8px; }
	.insp-list { display: flex; flex-wrap: wrap; gap: 8px; }
	.insp-list button { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 4px 12px 4px 4px; border-radius: 10px; border: 1px solid var(--line); background: var(--tile2); cursor: pointer; font-size: 14px; }
	.insp-list button:hover { border-color: rgba(255, 74, 28, .6); }
	.insp-list img { width: 56px; height: 32px; object-fit: cover; border-radius: 6px; }
	.insp .pill { align-self: flex-start; margin-top: auto; }
	.insp-play { display: flex; align-items: center; gap: 10px; margin-top: auto; }
	.insp-play .pill { margin-top: 0; }
	.ed-panel { border: 1px solid var(--line); border-radius: 14px; background: var(--tile); padding: 14px 18px 16px; }
	.ed-row { display: flex; align-items: center; gap: 14px; }
	.ed-lab { width: 150px; flex-shrink: 0; font-size: 13px; color: var(--fg2); display: flex; align-items: center; gap: 8px; }
	.ed-lab small { color: var(--dim); font-size: inherit; }
	.mini { position: relative; flex: 1; height: 24px; border-radius: 6px; background: rgba(255, 255, 255, .05); cursor: pointer; margin-bottom: 22px; }
	.mini i { position: absolute; top: 7px; width: 3px; height: 10px; border-radius: 2px; background: var(--acc); }
	.mini em { position: absolute; top: 28px; font-style: normal; font-size: 11px; color: var(--mut); white-space: nowrap; }
	.mini-win { position: absolute; top: 0; height: 24px; border: 1.5px solid var(--fg); border-radius: 6px; background: rgba(255, 255, 255, .08); pointer-events: none; }
	.zoom { display: flex; gap: 4px; }
	.zoom button { width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--line2); background: none; cursor: pointer; font-size: 17px; line-height: 1; }
	.ed-body { display: flex; gap: 14px; margin-top: 4px; }
	.ed-labs { width: 150px; flex-shrink: 0; padding-top: 40px; display: flex; flex-direction: column; gap: 6px; }
	.ed-labs div { height: 64px; display: flex; align-items: center; font-size: 13px; color: var(--fg2); }
	.ed-labs div small { color: var(--dim); margin-right: 6px; font-size: inherit; }
	.ed-scroll { flex: 1; min-width: 0; overflow-x: auto; overflow-y: hidden; scrollbar-color: #3a3a3c transparent; }
	.ed-inner { position: relative; }
	.ruler { position: relative; height: 34px; border-bottom: 1px solid rgba(255, 255, 255, .15); margin-bottom: 6px; }
	.ruler span { position: absolute; bottom: 4px; font-size: 11px; color: var(--dim); white-space: nowrap; transform: translateX(-50%); }
	.ruler span.w { color: var(--fg2); }
	.ruler b { position: absolute; top: 0; font-size: 12px; font-weight: 500; color: var(--mut); white-space: nowrap; }
	.ruler b::before { content: ""; position: absolute; left: 0; top: 16px; width: 1px; height: 18px; background: rgba(255, 255, 255, .25); }
	.ruler b { padding-left: 6px; }
	.tracks { display: flex; flex-direction: column; gap: 6px; padding-bottom: 6px; }
	.track { position: relative; height: 64px; border-radius: 8px; background: rgba(255, 255, 255, .03); }
	.eclip { position: absolute; top: 4px; height: 56px; padding: 0; border-radius: 7px; overflow: hidden; border: 1px solid rgba(255, 255, 255, .25); background: #2a2a2e; cursor: pointer; transition: transform .2s, opacity .2s; }
	.eclip:hover { transform: translateY(-2px); }
	.eclip img { width: 100%; height: 100%; object-fit: cover; display: block; }
	.eclip.stack { box-shadow: 3px -3px 0 #2c2c2e; }
	.eclip[aria-pressed="true"] { border: 3px solid var(--acc); }
	.eclip.dim { opacity: .2; }
	.eclip .bdg { position: absolute; right: 3px; top: 3px; padding: 1px 6px; border-radius: 980px; background: var(--fg); color: var(--ink); font-size: 10px; font-weight: 700; }
	.ph { position: absolute; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: var(--acc); z-index: 3; pointer-events: none; transition: left .3s var(--out); }
	.ph::before { content: ""; position: absolute; top: 0; left: -6px; width: 14px; height: 10px; border-radius: 3px; background: var(--acc); }
	.today { position: absolute; top: 0; bottom: 0; width: 1px; background: rgba(255, 255, 255, .35); pointer-events: none; }
	.today em { position: absolute; top: 0; left: 4px; font-style: normal; font-size: 11px; color: var(--fg); white-space: nowrap; }
	/* a stretch without work, cut out of the axis */
	.brk { position: absolute; top: 0; bottom: 6px; border-inline: 1px dashed rgba(255, 255, 255, .22); background: repeating-linear-gradient(135deg, rgba(255, 255, 255, .06) 0 6px, transparent 6px 12px); pointer-events: none; }
	.brk em { position: absolute; top: 4px; left: 0; right: 0; padding: 0 4px; font-style: normal; font-size: 10.5px; line-height: 1.25; text-align: center; color: var(--mut); }
	.legend { display: flex; gap: 10px 24px; flex-wrap: wrap; margin: 12px 0 0 164px; color: var(--mut); font-size: 13px; }
	.legend b { display: inline-block; padding: 0 6px; border-radius: 980px; background: var(--fg); color: var(--ink); margin-right: 6px; }
	.legend i { display: inline-block; width: 2px; height: 12px; background: var(--acc); margin-right: 6px; vertical-align: middle; }

	/* contact and footer */
	.cta { border-top: 1px solid var(--line); }
	.cta-in { padding-block: 72px; display: flex; justify-content: space-between; align-items: center; gap: 24px; flex-wrap: wrap; }
	.cta h2 { margin: 0 0 8px; font-size: clamp(26px, 4vw, 36px); }
	.cta p { margin: 0; color: var(--mut); font-size: 18px; }
	.cta .pill { background: var(--fg); color: var(--ink); font-weight: 700; min-height: 52px; padding: 0 28px; }
	.foot { padding-block: 28px 48px; color: var(--dim); font-size: 14px; border-top: 1px solid var(--line); }
	.foot a { color: var(--mut); }

	/* the pop-up player */
	.modal { position: fixed; inset: 0; z-index: 50; background: rgba(0, 0, 0, .94); display: flex; align-items: center; justify-content: center; padding: 16px; }
	.modal[hidden] { display: none; }
	.m-box { width: 100%; max-width: 1240px; display: flex; flex-direction: column; gap: 12px; }
	.m-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
	.m-top b { display: block; font-size: 22px; }
	.m-ctl { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
	.icon-btn { width: 44px; height: 44px; border-radius: 50%; border: 0; background: rgba(255, 255, 255, .1); cursor: pointer; font-size: 15px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
	.icon-btn:hover { background: rgba(255, 255, 255, .18); }
	.m-frame { width: 100%; border: 0; border-radius: 14px; background: #050506; display: block; }
	.m-strip { display: flex; gap: 10px; margin: 0; padding: 2px 2px 8px; list-style: none; overflow-x: auto; overscroll-behavior-x: contain; scrollbar-color: #3a3a3c transparent; scrollbar-width: thin; }
	.m-strip li { flex: 0 0 148px; min-width: 0; }
	.m-strip button { display: block; width: 100%; padding: 0; border: 0; background: none; color: var(--fg2); text-align: left; cursor: pointer; }
	.m-strip img { display: block; width: 100%; height: auto; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 8px; border: 2px solid transparent; background: #111; }
	.m-strip button:hover img { border-color: var(--line2); }
	.m-strip button[aria-current] img { border-color: var(--acc); }
	.m-strip button[aria-current] { color: var(--fg); }
	.m-strip span { display: block; margin-top: 5px; font-size: 12px; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.m-note { margin: 0; color: var(--dim); font-size: 13px; }

	/* back to top: the site's button in the showreel's dark glass */
	.to-top {
		all: unset; position: fixed; right: 22px; bottom: 22px; z-index: 30; box-sizing: border-box; display: grid; place-items: center; width: 44px; height: 44px; cursor: pointer;
		border-radius: 50%; color: #fff; background: rgba(44, 44, 46, .72);
		-webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .14), 0 8px 24px rgba(0, 0, 0, .35);
		opacity: 0; visibility: hidden; transform: translateY(8px); transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
	}
	.to-top.is-visible { opacity: 1; visibility: visible; transform: none; }
	.to-top:hover { color: #000; background: #f5f5f7; }
	.to-top:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; }
	body.locked .to-top { display: none; }
	@media (max-width: 600px) { .to-top { right: 16px; bottom: 16px; } }
	@media (prefers-reduced-motion: reduce) { .to-top { transition: none; } }

	@media (max-width: 1060px) {
		.tl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
	}
	@media (max-width: 900px) {
		.ed-top { grid-template-columns: 1fr; }
		.cal-ms .hd span { display: none; }
	}
	@media (max-width: 760px) {
		/* the controls are two rows here, so a day lands lower */
		html { scroll-padding-top: 194px; }
		.hero { padding-top: 52px; text-align: left; }
		.hero h1, .hero p { margin-left: 0; }
		.cal { padding: 18px 16px; }
		.cal-row > a, .cal-row > span { width: 64px; font-size: 13px; }
		.cal-days { gap: 2px; }
		.hd { height: 16px; border-radius: 3px; }
		.tl { padding-block: 32px 56px; }
		.tl-rail, .tl-fill, .tl-head { display: none; }
		.tl-mhead { margin-bottom: 14px; }
		.tl-week { display: block; padding-bottom: 34px; }
		.tl-node { position: static; display: flex; align-items: baseline; gap: 10px; margin-bottom: 14px; padding: 0 0 0 22px; }
		.tl-node > i { top: 6px; box-shadow: none; }
		.tl-node span { margin: 0; }
		.tl-grid { gap: 20px 14px; }
		.ctl-in { flex-wrap: wrap; justify-content: space-between; gap: 8px; }
		.chips-f { order: 3; flex: 1 0 100%; max-width: 100%; }
		.chip { padding: 0 14px; font-size: 14px; }
		.top .tc { display: none; }
		.ctl .tc { display: inline-flex; order: 2; padding: 5px 10px; font-size: 13px; }
		.ed-lab, .ed-labs { width: 86px; }
		.ed-labs div small { display: none; }
		.legend { margin-left: 0; }
		.brand { font-size: 17px; }
		.nav a { padding: 8px 9px; font-size: 14px; }
		.nav .nl { display: none; }
	}
	/* a phone: the picture beside the words, so a screen holds five or six pieces */
	@media (max-width: 600px) {
		.tl-grid { grid-template-columns: 1fr; gap: 12px; }
		.card { flex-direction: row; }
		.card:hover { transform: none; }
		.thumb { width: 38%; max-width: 150px; aspect-ratio: auto; min-height: 96px; }
		.thumb > img { position: absolute; inset: 0; }
		.tags { left: 6px; right: 6px; bottom: 6px; }
		.tags > span { padding: 1px 7px; font-size: 11px; }
		.tags > span + span { display: none; }
		.body { padding: 10px 12px 11px; min-width: 0; justify-content: center; }
		.meta { font-size: 12px; }
		.body .title { margin: 3px 0 2px; font-size: 16px; -webkit-line-clamp: 3; }
		.body .desc { -webkit-line-clamp: 1; font-size: 13px; }
		.pv-tag { display: none; }
		.m-strip li { flex-basis: 118px; }
	}
	@media (max-width: 360px) {
		.ctl .seg button { padding: 0 11px; }
		.ctl .tc { gap: 6px; padding: 5px 8px; font-size: 12px; }
	}
</style>
<?php rs_work_log_manage_head(); ?>
</head>
<body>

<header class="top">
	<div class="wrap top-in">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $rs_wl_t( 'রাইসুল সোহান', 'Raisul Sohan' ) ); ?><span class="dot" aria-hidden="true"></span></a>
		<span class="tc" aria-hidden="true"><i></i><span></span></span>
		<nav class="nav" aria-label="<?php echo esc_attr( $rs_wl_t( 'প্রধান মেনু', 'Main menu' ) ); ?>">
			<a class="nl" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $rs_wl_t( 'লেখা', 'Writing' ) ); ?></a>
			<a class="nl" href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>"><?php echo esc_html( $rs_wl_t( 'পোর্টফোলিও', 'Portfolio' ) ); ?></a>
			<a href="<?php echo esc_url( $rs_wl_here ); ?>" aria-current="page"><?php echo esc_html( $rs_wl_title ); ?></a>
			<a class="nl" href="<?php echo esc_url( home_url( '/cv/' ) ); ?>"><?php echo esc_html( $rs_wl_t( 'সিভি', 'CV' ) ); ?></a>
			<?php if ( $rs_wl_other ) : ?>
				<a href="<?php echo esc_url( $rs_wl_other ); ?>" hreflang="<?php echo esc_attr( $rs_wl_pair['lang'] ); ?>" lang="<?php echo esc_attr( $rs_wl_pair['lang'] ); ?>"><?php echo esc_html( $rs_wl_en ? 'বাংলা' : 'English' ); ?></a>
			<?php endif; ?>
		</nav>
	</div>
</header>

<main id="main">
	<section class="wrap hero">
		<?php
		$rs_wl_y0 = substr( $rs_wl_first_ym, 0, 4 );
		$rs_wl_y1 = substr( $rs_wl_items[0]['date'], 0, 4 );
		?>
		<span class="kicker"><?php echo esc_html( $rs_wl_title . ' · ' . rs_bn_digits( $rs_wl_y0 === $rs_wl_y1 ? $rs_wl_y1 : $rs_wl_y0 . '–' . $rs_wl_y1 ) ); ?></span>
		<h1><?php echo esc_html( $rs_wl_t( 'কবে, কোন কাজ', 'What I made, and when' ) ); ?><span class="dot" aria-hidden="true"></span></h1>
		<p><?php echo esc_html( $rs_wl_t( 'টুল, ম্যাপ অ্যানিমেশন, ডকুমেন্টারি, শোরিল: যা বানাই সব এখানে, তারিখ ধরে। সবচেয়ে নতুনটা সবার উপরে, কার্ডে চাপলেই চলে।', 'Tools, map animation, documentary, showreels: everything I make, by date. The newest is on top; click a card and it plays.' ) ); ?></p>
	</section>

	<div class="wrap">
		<section class="cal" aria-label="<?php echo esc_attr( $rs_wl_t( 'কোন দিনে কাজ এসেছে', 'The days work went up' ) ); ?>">
			<div class="cal-head">
				<b><?php
				$rs_wl_nm = $rs_wl_span_m;
				$rs_wl_ni = count( $rs_wl_items );
				if ( $rs_wl_nm > 12 ) {
					echo esc_html( $rs_wl_en ? $rs_wl_ni . ' pieces since ' . $rs_wl_month( $rs_wl_first_ym ) : $rs_wl_month( $rs_wl_first_ym ) . ' থেকে ' . rs_bn_digits( $rs_wl_ni ) . 'টি কাজ' );
				} else {
					echo esc_html( $rs_wl_en ? $rs_wl_ni . ' pieces in ' . $rs_wl_nm . ( 1 === $rs_wl_nm ? ' month' : ' months' ) : rs_bn_digits( $rs_wl_nm ) . ' মাসে ' . rs_bn_digits( $rs_wl_ni ) . 'টি কাজ' );
				}
				?></b>
				<span><i aria-hidden="true"></i><?php echo esc_html( $rs_wl_t( 'যেদিন নতুন কাজ এসেছে', 'A day new work went up' ) ); ?></span>
			</div>
			<?php foreach ( $rs_wl_strip as $rs_wl_ym ) : ?>
				<div class="cal-row">
					<?php
					$rs_wl_has = isset( $rs_wl_tree[ $rs_wl_ym ] );
					$rs_wl_mts = strtotime( $rs_wl_ym . '-01 12:00:00 UTC' );
					$rs_wl_mn  = $rs_wl_en ? gmdate( 'F', $rs_wl_mts ) : $rs_wl_months_bn[ (int) gmdate( 'n', $rs_wl_mts ) ];
					?>
					<?php if ( $rs_wl_has ) : ?>
						<a href="#m-<?php echo esc_attr( $rs_wl_ym ); ?>"><?php echo esc_html( $rs_wl_mn ); ?></a>
					<?php else : ?>
						<span><?php echo esc_html( $rs_wl_mn ); ?></span>
					<?php endif; ?>
					<div class="cal-days">
						<?php
						$rs_wl_len = (int) gmdate( 't', $rs_wl_mts );
						for ( $rs_wl_d = 1; $rs_wl_d <= $rs_wl_len; $rs_wl_d++ ) {
							$rs_wl_ymd = $rs_wl_ym . '-' . str_pad( (string) $rs_wl_d, 2, '0', STR_PAD_LEFT );
							if ( isset( $rs_wl_days_on[ $rs_wl_ymd ] ) ) {
								printf( '<a class="hd on" href="#d-%1$s" data-d="%1$s" title="%2$s" aria-label="%2$s"></a>', esc_attr( $rs_wl_ymd ), esc_attr( $rs_wl_day( $rs_wl_ymd ) ) );
							} else {
								printf( '<span class="hd" data-d="%s"></span>', esc_attr( $rs_wl_ymd ) );
							}
						}
						?>
					</div>
				</div>
			<?php endforeach; ?>
			<?php foreach ( $rs_wl_years as $rs_wl_y => $rs_wl_ms ) : ?>
				<div class="cal-row cal-yr">
					<span><?php echo esc_html( rs_bn_digits( $rs_wl_y ) ); ?></span>
					<div class="cal-days cal-ms">
						<?php
						for ( $rs_wl_m = 1; $rs_wl_m <= 12; $rs_wl_m++ ) {
							$rs_wl_ym = sprintf( '%s-%02d', $rs_wl_y, $rs_wl_m );
							$rs_wl_mn = $rs_wl_en ? gmdate( 'M', strtotime( $rs_wl_ym . '-15 12:00:00 UTC' ) ) : $rs_wl_months_bn[ $rs_wl_m ];
							if ( isset( $rs_wl_ms[ $rs_wl_m ] ) ) {
								printf( '<a class="hd on" href="#m-%1$s" data-m="%1$s" title="%2$s" aria-label="%2$s"><span aria-hidden="true">%3$s</span></a>', esc_attr( $rs_wl_ym ), esc_attr( $rs_wl_month( $rs_wl_ym ) . ' · ' . $rs_wl_count( $rs_wl_ms[ $rs_wl_m ] ) ), esc_html( $rs_wl_mn ) );
							} else {
								printf( '<span class="hd"><span>%s</span></span>', esc_html( $rs_wl_mn ) );
							}
						}
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</section>
		<script>
		// Days still to come are drawn as outlines, by the reader's own clock, before the strip is painted.
		(function () {
			var n = new Date(), t = n.getFullYear() + '-' + ('0' + (n.getMonth() + 1)).slice(-2) + '-' + ('0' + n.getDate()).slice(-2);
			document.querySelectorAll('.cal span.hd').forEach(function (s) { if (s.dataset.d > t) s.classList.add('fut'); });
		})();
		</script>
	</div>

	<div class="ctl">
		<div class="wrap ctl-in">
			<div class="seg" role="group" aria-label="<?php echo esc_attr( $rs_wl_t( 'দেখার ধরন', 'View' ) ); ?>">
				<button type="button" id="vList" aria-pressed="true" aria-controls="list"><?php echo esc_html( $rs_wl_t( 'তালিকা', 'List' ) ); ?></button>
				<button type="button" id="vEd" aria-pressed="false" aria-controls="editor"><?php echo esc_html( $rs_wl_t( 'এডিটর', 'Editor' ) ); ?></button>
			</div>
			<div class="chips-f" role="group" aria-label="<?php echo esc_attr( $rs_wl_t( 'ধরন অনুযায়ী বাছাই', 'Filter by kind' ) ); ?>">
				<button type="button" class="chip" data-k="all" aria-pressed="true"><?php echo esc_html( $rs_wl_t( 'সব কাজ', 'All work' ) ); ?><small><?php echo esc_html( rs_bn_digits( count( $rs_wl_items ) ) ); ?></small></button>
				<?php foreach ( $rs_wl_used as $rs_wl_k => $rs_wl_n ) : ?>
					<button type="button" class="chip" data-k="<?php echo esc_attr( $rs_wl_k ); ?>" aria-pressed="false"><?php echo esc_html( $rs_wl_t( $rs_wl_kinds[ $rs_wl_k ]['bn'], $rs_wl_kinds[ $rs_wl_k ]['en'] ) ); ?><small><?php echo esc_html( rs_bn_digits( $rs_wl_n ) ); ?></small></button>
				<?php endforeach; ?>
			</div>
			<span class="tc" aria-hidden="true"><i></i><span></span></span>
		</div>
		<?php rs_work_log_manage_bar( count( $rs_wl_items ), count( array_filter( wp_list_pluck( $rs_wl_items, 'hidden' ) ) ) ); ?>
	</div>

	<section class="wrap tl" id="list" aria-label="<?php echo esc_attr( $rs_wl_t( 'টাইমলাইন', 'Timeline' ) ); ?>">
		<div class="tl-rail" aria-hidden="true"></div>
		<div class="tl-fill" id="tlFill" aria-hidden="true"></div>
		<div class="tl-head" id="tlHead" aria-hidden="true"></div>
		<?php $rs_wl_seen = array(); ?>
		<?php foreach ( $rs_wl_tree as $rs_wl_ym => $rs_wl_weeks ) : ?>
			<section class="tl-month" id="m-<?php echo esc_attr( $rs_wl_ym ); ?>">
				<h2 class="tl-mhead"><?php echo esc_html( $rs_wl_month( $rs_wl_ym ) ); ?> <small class="mcount"><?php echo esc_html( $rs_wl_count( array_sum( array_map( 'count', $rs_wl_weeks ) ) ) ); ?></small></h2>
				<?php foreach ( $rs_wl_weeks as $rs_wl_w => $rs_wl_list ) : ?>
					<div class="tl-week">
						<div class="tl-node">
							<i aria-hidden="true"></i>
							<b><?php echo esc_html( $rs_wl_week( $rs_wl_ym, $rs_wl_w ) ); ?></b>
							<span class="dcount"><?php echo esc_html( $rs_wl_count( count( $rs_wl_list ) ) ); ?></span>
						</div>
						<div class="tl-grid">
							<?php foreach ( $rs_wl_list as $rs_wl_it ) : ?>
								<?php
								/* The first card of a day is where the calendar and a link to that day land. */
								$rs_wl_anchor = isset( $rs_wl_seen[ $rs_wl_it['date'] ] ) ? '' : ' id="d-' . esc_attr( $rs_wl_it['date'] ) . '"';
								$rs_wl_seen[ $rs_wl_it['date'] ] = true;
								?>
								<div class="cell"<?php echo $rs_wl_anchor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?> data-id="<?php echo esc_attr( $rs_wl_it['id'] ); ?>" data-kind="<?php echo esc_attr( $rs_wl_it['kind'] ); ?>" data-date="<?php echo esc_attr( $rs_wl_it['date'] ); ?>">
									<?php echo $rs_wl_card( $rs_wl_it ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside. ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endforeach; ?>
		<div class="tl-end"><span><?php echo esc_html( $rs_wl_t( 'শুরু', 'The start' ) . ' · ' . $rs_wl_month( $rs_wl_first_ym ) ); ?></span></div>
	</section>

	<section class="wrap ed" id="editor" aria-label="<?php echo esc_attr( $rs_wl_t( 'এডিটর ভিউ', 'Editor view' ) ); ?>" hidden>
		<ol class="ed-help">
			<li><span class="n"><?php echo esc_html( rs_bn_digits( 1 ) ); ?></span><?php echo esc_html( $rs_wl_t( 'নিচের টাইমলাইনে যেকোনো ক্লিপে চাপুন। একেকটা ক্লিপ মানে একেকটা কাজ।', 'Click any clip on the timeline below. Each clip is one piece of work.' ) ); ?></li>
			<li><span class="n"><?php echo esc_html( rs_bn_digits( 2 ) ); ?></span><?php echo esc_html( $rs_wl_t( 'উপরের বড় পর্দায় সেই কাজের ছবি আর ডানে তার তথ্য চলে আসবে।', 'The monitor shows that piece, and the panel beside it tells you about it.' ) ); ?></li>
			<li><span class="n"><?php echo esc_html( rs_bn_digits( 3 ) ); ?></span><?php echo esc_html( $rs_wl_t( '"চালান" চাপলে ভিডিওটা পুরো স্ক্রিনে চলবে।', 'Press "Play" and it plays in the player.' ) ); ?></li>
			<li><span class="n"><?php echo esc_html( rs_bn_digits( 4 ) ); ?></span><?php echo esc_html( $rs_wl_t( 'অন্য সময়ে যেতে উপরের সরু দাগে চাপুন, আর + − দিয়ে জুম করুন।', 'Click the thin strip to move to another time; + and − zoom.' ) ); ?></li>
		</ol>
		<div class="ed-top">
			<div class="mon">
				<img id="edImg" src="" alt="">
				<span class="stamp"><span class="n n--s"><?php echo esc_html( rs_bn_digits( 2 ) ); ?></span><span id="edStamp"></span></span>
				<button type="button" id="edMonPlay" aria-label="<?php echo esc_attr( $rs_wl_t( 'চালান', 'Play' ) ); ?>"></button>
			</div>
			<div class="insp" aria-live="polite">
				<span class="kicker" id="edType"></span>
				<h2 id="edTitle"></h2>
				<p id="edDesc"></p>
				<div class="chips"><span id="edDate"></span><span id="edTrack"></span></div>
				<div class="insp-sub" id="edSameWrap" hidden><span><?php echo esc_html( $rs_wl_t( 'একই দিনে', 'Same day' ) ); ?></span><div class="insp-list" id="edSame"></div></div>
				<div class="insp-sub" id="edClipsWrap" hidden><span><?php echo esc_html( $rs_wl_t( 'এই প্রজেক্টের ক্লিপ', 'Clips in this project' ) ); ?></span><div class="insp-list" id="edClips"></div></div>
				<div class="insp-play"><span class="n"><?php echo esc_html( rs_bn_digits( 3 ) ); ?></span><button type="button" class="pill pill--acc" id="edPlay"><?php echo $rs_wl_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A fixed SVG. ?><?php echo esc_html( $rs_wl_t( 'চালান', 'Play' ) ); ?></button></div>
			</div>
		</div>
		<div class="ed-panel">
			<div class="ed-row">
				<span class="ed-lab"><span class="n n--s"><?php echo esc_html( rs_bn_digits( 4 ) ); ?></span><?php echo esc_html( $rs_wl_t( 'পুরো সময়', 'All time' ) ); ?></span>
				<div class="mini" id="edMini" role="slider" tabindex="0" aria-label="<?php echo esc_attr( $rs_wl_t( 'টাইমলাইনের কোন অংশ দেখছেন', 'The part of the timeline in view' ) ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100"><span class="mini-win" id="edWin"></span></div>
			</div>
			<div class="ed-row" style="margin-top: -6px;">
				<span class="ed-lab"><span class="n n--s"><?php echo esc_html( rs_bn_digits( 1 ) ); ?></span><?php echo esc_html( $rs_wl_t( 'ক্লিপে চাপুন', 'Click a clip' ) ); ?></span>
				<div class="zoom" role="group" aria-label="<?php echo esc_attr( $rs_wl_t( 'জুম', 'Zoom' ) ); ?>">
					<button type="button" id="edOut" aria-label="<?php echo esc_attr( $rs_wl_t( 'ছোট করুন', 'Zoom out' ) ); ?>">−</button>
					<button type="button" id="edIn" aria-label="<?php echo esc_attr( $rs_wl_t( 'বড় করুন', 'Zoom in' ) ); ?>">+</button>
				</div>
			</div>
			<div class="ed-body">
				<div class="ed-labs" aria-hidden="true">
					<?php $rs_wl_tracks = array_intersect_key( $rs_wl_kinds, array_flip( wp_list_pluck( $rs_wl_items, 'kind' ) ) ); ?>
					<?php $rs_wl_v = count( $rs_wl_tracks ); ?>
					<?php foreach ( $rs_wl_tracks as $rs_wl_k => $rs_wl_kl ) : ?>
						<div><small>V<?php echo (int) $rs_wl_v--; ?></small><?php echo esc_html( $rs_wl_t( $rs_wl_kl['bn'], $rs_wl_kl['en'] ) ); ?></div>
					<?php endforeach; ?>
				</div>
				<div class="ed-scroll" id="edScroll">
					<div class="ed-inner" id="edInner">
						<div class="ruler" id="edRuler"></div>
						<div class="tracks" id="edTracks"></div>
						<div class="ph" id="edPh" aria-hidden="true"></div>
					</div>
				</div>
			</div>
			<div class="legend">
				<span><i aria-hidden="true"></i><?php echo esc_html( $rs_wl_t( 'কমলা দাগ: যেটা বাছাই করেছেন', 'Orange line: the one you picked' ) ); ?></span>
				<span><b><?php echo esc_html( rs_bn_digits( 2 ) ); ?></b><?php echo esc_html( $rs_wl_t( 'একই দিনে কয়েকটা কাজ', 'Several pieces on one day' ) ); ?></span>
				<span><b>+<?php echo esc_html( rs_bn_digits( 12 ) ); ?></b><?php echo esc_html( $rs_wl_t( 'প্রজেক্টের ভেতরের ক্লিপ', 'Clips inside a project' ) ); ?></span>
				<span><?php echo esc_html( $rs_wl_t( 'একেকটা সারি = কাজের একেক ধরন', 'Each row is one kind of work' ) ); ?></span>
			</div>
		</div>
	</section>
</main>

<section class="cta">
	<div class="wrap cta-in">
		<div>
			<h2><?php echo esc_html( $rs_wl_t( 'পরের কাজটা আপনার হতে পারে', 'The next piece could be yours' ) ); ?></h2>
			<p><?php echo esc_html( $rs_wl_t( 'ম্যাপ অ্যানিমেশন, এক্সপ্লেইনার, ডকুমেন্টারি, প্রোডাক্ট ভিডিও।', 'Map animation, explainers, documentary, product videos.' ) ); ?></p>
		</div>
		<a class="pill" href="mailto:<?php echo esc_attr( antispambot( $rs_wl_email ) ); ?>"><?php echo esc_html( $rs_wl_t( 'যোগাযোগ করুন', 'Get in touch' ) ); ?></a>
	</div>
</section>

<footer class="wrap foot">
	<p><?php echo esc_html( $rs_wl_t( 'বানিয়েছেন', 'Made by' ) ); ?> <a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>"><?php echo esc_html( $rs_wl_t( 'রাইসুল সোহান', 'Raisul Sohan' ) ); ?></a>. <?php echo esc_html( $rs_wl_t( 'এখানের প্রতিটা কাজ এই পেজেই চলে।', 'Every piece plays right here on the page.' ) ); ?></p>
</footer>

<div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="mTitle" hidden>
	<div class="m-box" id="mBox">
		<div class="m-top">
			<div><span class="kicker" id="mType"></span><b id="mTitle"></b></div>
			<div class="m-ctl">
				<button type="button" class="icon-btn" id="mPrev" aria-label="<?php echo esc_attr( $rs_wl_t( 'আগেরটা', 'Previous' ) ); ?>">◀</button>
				<div class="seg" role="group" aria-label="<?php echo esc_attr( $rs_wl_t( 'ফ্রেম', 'Frame' ) ); ?>" id="mSeg">
					<button type="button" data-ar="169" aria-pressed="true">16:9</button><button type="button" data-ar="45" aria-pressed="false">4:5</button>
				</div>
				<a class="pill" id="mOpen" target="_blank" rel="noopener" style="min-height: 44px;"><?php echo esc_html( $rs_wl_t( 'আলাদা খুলুন ↗', 'Open ↗' ) ); ?></a>
				<button type="button" class="icon-btn" id="mNext" aria-label="<?php echo esc_attr( $rs_wl_t( 'পরেরটা', 'Next' ) ); ?>">▶</button>
				<button type="button" class="icon-btn" id="mClose" aria-label="<?php echo esc_attr( $rs_wl_t( 'বন্ধ করুন', 'Close' ) ); ?>">✕</button>
			</div>
		</div>
		<iframe class="m-frame" id="mFrame" title="<?php echo esc_attr( $rs_wl_t( 'কাজের প্লেয়ার', 'Work player' ) ); ?>" allow="autoplay; fullscreen; encrypted-media; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
		<ol class="m-strip" id="mStrip" aria-label="<?php echo esc_attr( $rs_wl_t( 'এই কাজের ক্লিপ', 'Clips in this piece' ) ); ?>" hidden></ol>
		<p class="m-note" id="mNote"><?php echo esc_html( $rs_wl_t( '[ ] দিয়ে আগে-পরে · Esc দিয়ে বন্ধ', '[ ] previous and next · Esc to close' ) ); ?></p>
	</div>
</div>

<button type="button" class="to-top" id="toTop" aria-label="<?php echo esc_attr( $rs_wl_t( 'উপরে যান', 'Back to top' ) ); ?>" title="<?php echo esc_attr( $rs_wl_t( 'উপরে যান', 'Back to top' ) ); ?>"><?php echo wp_kses( rs_icon( 'up', 18 ), rs_svg_tags() ); ?></button>

<script>
(function () {
'use strict';
const VER = <?php echo wp_json_encode( RS_VERSION ); ?>, EN = <?php echo $rs_wl_en ? 'true' : 'false'; ?>;
const KINDS = <?php
$rs_wl_kjs = array();
foreach ( $rs_wl_kinds as $rs_wl_k => $rs_wl_kl ) {
	if ( isset( $rs_wl_used[ $rs_wl_k ] ) ) {
		$rs_wl_kjs[ $rs_wl_k ] = $rs_wl_t( $rs_wl_kl['bn'], $rs_wl_kl['en'] );
	}
}
echo wp_json_encode( $rs_wl_kjs );
?>;
const WORKS = <?php
$rs_wl_js = array();
foreach ( $rs_wl_items as $rs_wl_it ) {
	$rs_wl_row = array(
		'id'      => $rs_wl_it['id'],
		'date'    => $rs_wl_it['date'],
		'day'     => $rs_wl_day( $rs_wl_it['date'] ),
		'kind'    => $rs_wl_it['kind'],
		'client'  => ! empty( $rs_wl_it['client'] ),
		'title'   => $rs_wl_name( $rs_wl_it ),
		'type'    => $rs_wl_t( $rs_wl_it['type_bn'], $rs_wl_it['type_en'] ),
		'desc'    => $rs_wl_t( $rs_wl_it['desc_bn'], $rs_wl_it['desc_en'] ),
		'img'     => $rs_wl_abs( $rs_wl_it['img'] ),
		'src'     => $rs_wl_abs( $rs_wl_it['src'] ),
		'src45'   => empty( $rs_wl_it['src45'] ) ? '' : $rs_wl_abs( $rs_wl_it['src45'] ),
		'map'     => ! empty( $rs_wl_it['map'] ),
		'watch'   => empty( $rs_wl_it['watch'] ) ? '' : $rs_wl_it['watch'],
		'local'   => ! preg_match( '#^https?://#', $rs_wl_it['src'] ),
		'preview' => ! isset( $rs_wl_it['preview'] ) || false !== $rs_wl_it['preview'],
		'clips'   => array(),
	);
	if ( $rs_wl_manage ) {
		$rs_wl_row = array_merge( $rs_wl_row, array_intersect_key( rs_wla_row( $rs_wl_it ), array_flip( array( 'hidden', 'source', 'bytes', 'title_bn', 'desc_bn', 'desc_en' ) ) ) );
		$rs_wl_row['name'] = $rs_wl_it['title'];
	}
	if ( ! empty( $rs_wl_it['clips'] ) ) {
		foreach ( $rs_wl_it['clips'] as $rs_wl_i => $rs_wl_c ) {
			$rs_wl_row['clips'][] = array(
				'title' => $rs_wl_name( $rs_wl_c ),
				'num'   => rs_bn_digits( $rs_wl_i + 1 ),
				'src'   => $rs_wl_abs( $rs_wl_c['src'] ),
				'watch' => empty( $rs_wl_c['watch'] ) ? '' : $rs_wl_c['watch'],
				'tall'  => ! empty( $rs_wl_c['tall'] ),
				'img'   => $rs_wl_abs( $rs_wl_c['img'] ),
			);
		}
	}
	$rs_wl_js[] = $rs_wl_row;
}
echo wp_json_encode( $rs_wl_js );
?>;
const TRACKS = Object.keys(KINDS).filter(k => WORKS.some(w => w.kind === k));
const T = <?php
echo wp_json_encode(
	array(
		'clip'  => $rs_wl_t( 'ক্লিপ', 'Clip' ),
		'clips' => $rs_wl_t( 'টি ক্লিপ', ' clips' ),
		'same'  => $rs_wl_t( 'একই দিনে', 'Same day' ),
		'today' => $rs_wl_t( 'আজ', 'Today' ),
		'main'  => $rs_wl_t( 'মূল ভিডিও', 'Main video' ),
		'open'  => $rs_wl_t( 'আলাদা খুলুন ↗', 'Open ↗' ),
		'tube'  => $rs_wl_t( 'YouTube-এ দেখুন ↗', 'Watch on YouTube ↗' ),
	)
);
?>;

const $ = id => document.getElementById(id);
const byId = id => WORKS.find(w => w.id === id);
const bn = s => EN ? String(s) : String(s).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
const count = n => EN ? n + (n === 1 ? ' piece' : ' pieces') : bn(n) + 'টি কাজ';
const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
const conn = navigator.connection;
const lightNet = () => !!(conn && (conn.saveData || /^(slow-)?2g$|^3g$/.test(conn.effectiveType || '')));
const store = { get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }, set(k, v) { try { localStorage.setItem(k, v); } catch (e) {} } };
const timecode = ymd => { const p = ymd.split('-'); return bn(p[2] + ' · ' + p[1] + ' · ' + p[0]); };
const yearOf = ymd => bn(ymd.slice(0, 4));

/** The address of a piece (or a clip of one) in the 16:9 or 4:5 frame. */
function srcOf(p, vert) {
  const own45 = vert && p.src45;
  let f = own45 ? p.src45 : p.src;
  if (p.map) {
    const q = ['embed', 'ver=' + encodeURIComponent(VER)];
    if (vert && !own45) q.push('ar=45');
    return f + '?' + q.join('&');
  }
  return p.local ? f + '?ver=' + encodeURIComponent(VER) : f;
}
const canVert = p => !!(p.src45 || p.map);

// ── filters
let kind = 'all';
let gate = null;   // a further test, set by the controls of someone who manages the log
const ofKind = (w, k) => k === 'all' || (k === 'client' ? w.client : w.kind === k);
const listed = w => !gate || gate(w);
const shown = w => !!w && ofKind(w, kind) && listed(w);

function applyFilter() {
  document.querySelectorAll('.chip').forEach(c => {
    c.setAttribute('aria-pressed', c.dataset.k === kind ? 'true' : 'false');
    c.querySelector('small').textContent = bn(WORKS.filter(w => ofKind(w, c.dataset.k) && listed(w)).length);
  });
  document.querySelectorAll('.tl-month').forEach(m => {
    let mn = 0;
    m.querySelectorAll('.tl-week').forEach(d => {
      let n = 0;
      d.querySelectorAll('.cell').forEach(el => {
        const on = shown(byId(el.dataset.id));
        el.hidden = !on; if (on) n++;
      });
      d.hidden = n === 0; mn += n;
      d.querySelector('.dcount').textContent = count(n);
    });
    m.hidden = mn === 0;
    m.querySelector('.mcount').textContent = count(mn);
  });
  if (edBuilt) drawEditor(false);
  onScroll();
}
document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => { kind = c.dataset.k; applyFilter(); }));

// ── the pop-up player
const modal = $('modal'), mFrame = $('mFrame');
const hooks = {};   // filled in below for someone who manages the log
const P = { list: [], i: 0, vert: store.get('worklog.vert') === '1', last: null, hash: '' };
function fitModal() {
  if (modal.hidden) return;
  // twice: a narrower box may break its top row in two, which takes height from the frame
  fitOnce();
  fitOnce();
}
function fitOnce() {
  const strip = $('mStrip'), top = $('mBox').querySelector('.m-top').offsetHeight + 12;
  const room = innerHeight - 32 - top - 34 - (strip.hidden ? 0 : strip.offsetHeight + 12);
  const vert = P.list[P.i].tall || (P.vert && canVert(P.list[P.i]));
  let w = Math.min(1240, innerWidth - 32), h;
  if (vert) { h = Math.max(380, room); w = Math.min(w, Math.max(Math.min(640, w), h * 4 / 5)); }
  else { h = w * 9 / 16; if (h > room) { h = Math.max(260, room); w = Math.min(w, h * 16 / 9); } }
  if (!strip.hidden) w = Math.max(w, Math.min(720, innerWidth - 32));
  $('mBox').style.maxWidth = Math.round(w) + 'px'; mFrame.style.height = Math.round(h) + 'px';
}
/* The clips of the piece in the player, the one that plays marked. A piece whose
   main video is one of its clips has no card of its own for it. */
function drawStrip(p) {
  const w = byId(p.id), ol = $('mStrip');
  ol.hidden = !w || !w.clips.length;
  ol.textContent = '';
  if (ol.hidden) return;
  const inClips = w.clips.findIndex(c => c.src === w.src);
  const now = p.clip >= 0 ? p.clip : inClips;
  const rows = w.clips.map((c, i) => ({ i, img: c.img, name: c.num + ' · ' + c.title }));
  if (inClips < 0) rows.unshift({ i: -1, img: w.img, name: T.main });
  rows.forEach(r => {
    const li = document.createElement('li'), b = document.createElement('button'), im = document.createElement('img'), t = document.createElement('span');
    b.type = 'button'; im.src = r.img; im.alt = ''; im.width = 320; im.height = 180; im.loading = 'lazy'; t.textContent = r.name; b.title = r.name;
    if (r.i === now) b.setAttribute('aria-current', 'true');
    b.append(im, t); li.append(b); ol.append(li);
    b.addEventListener('click', () => { if (r.i < 0) playWork(w.id); else playClip(w.id, r.i); });
  });
  const cur = ol.querySelector('[aria-current]');
  if (cur) ol.scrollLeft = cur.parentNode.offsetLeft - ol.offsetLeft - (ol.clientWidth - cur.offsetWidth) / 2;
}
function loadModal() {
  const p = P.list[P.i], v = P.vert && canVert(p);
  $('mType').textContent = p.type; $('mTitle').textContent = p.title;
  $('mSeg').hidden = !canVert(p);
  document.querySelectorAll('[data-ar]').forEach(b => b.setAttribute('aria-pressed', (b.dataset.ar === '45') === v ? 'true' : 'false'));
  $('mOpen').href = p.watch || srcOf(p, v);
  $('mOpen').textContent = p.watch ? T.tube : T.open;
  const multi = P.list.length > 1; $('mPrev').hidden = !multi; $('mNext').hidden = !multi;
  mFrame.src = srcOf(p, v);
  if (p.hash && location.hash !== p.hash) history.replaceState(null, '', p.hash);
  drawStrip(p);
  if (hooks.onLoad) hooks.onLoad(byId(p.id));
  fitModal();
}
function openPlayer(list, i) {
  const first = modal.hidden;
  stopPreview();
  P.list = list; P.i = i;
  if (first) { P.last = document.activeElement; modal.hidden = false; document.body.classList.add('locked'); }
  loadModal();
  if (first) $('mClose').focus();
}
function closePlayer() {
  if (modal.hidden) return;
  modal.hidden = true; document.body.classList.remove('locked'); mFrame.src = 'about:blank';
  history.replaceState(null, '', location.pathname + location.search);
  if (P.last) P.last.focus({ preventScroll: true });
}
const entry = w => ({ id: w.id, title: w.title, type: w.type, src: w.src, src45: w.src45, map: w.map, local: w.local, watch: w.watch, clip: -1, hash: '#' + w.id });
const clipEntry = (w, c, i) => ({ id: w.id, title: c.num + ' · ' + c.title, type: w.title, src: c.src, src45: '', map: w.map, local: !c.watch, watch: c.watch, tall: c.tall, clip: i, hash: '#' + w.id + '/' + (i + 1) });
function playWork(id) {
  const list = WORKS.filter(shown);
  let i = list.findIndex(w => w.id === id);
  if (i < 0) { list.unshift(byId(id)); i = 0; }
  openPlayer(list.map(entry), i);
}
function playClip(id, i) { const w = byId(id); openPlayer(w.clips.map((c, k) => clipEntry(w, c, k)), i); }
const step = k => { P.i = (P.i + k + P.list.length) % P.list.length; loadModal(); };
$('mPrev').onclick = () => step(-1);
$('mNext').onclick = () => step(1);
$('mClose').onclick = closePlayer;
modal.addEventListener('click', e => { if (e.target === modal) closePlayer(); });
document.querySelectorAll('[data-ar]').forEach(b => b.addEventListener('click', () => {
  const v = b.dataset.ar === '45'; if (v === P.vert) return;
  P.vert = v; store.set('worklog.vert', v ? '1' : '0'); loadModal();
}));
modal.addEventListener('keydown', e => {
  if (e.key !== 'Tab') return;
  const f = [...modal.querySelectorAll('button, a[href], iframe')].filter(el => !el.hidden && el.offsetParent !== null);
  if (!f.length) return;
  const a = f[0], z = f[f.length - 1];
  if (e.shiftKey && document.activeElement === a) { e.preventDefault(); z.focus(); }
  else if (!e.shiftKey && document.activeElement === z) { e.preventDefault(); a.focus(); }
});
addEventListener('keydown', e => {
  if (modal.hidden) return;
  if (e.key === 'Escape') { e.preventDefault(); closePlayer(); }
  else if (e.key === '[' && P.list.length > 1) step(-1);
  else if (e.key === ']' && P.list.length > 1) step(1);
});
addEventListener('resize', fitModal);

// the kinds fade at the right while there are more of them to scroll to
const kinds = document.querySelector('.chips-f');
const more = () => kinds.classList.toggle('is-more', kinds.scrollLeft + kinds.clientWidth < kinds.scrollWidth - 4);
kinds.addEventListener('scroll', more, { passive: true });
addEventListener('resize', more);
more();

// cards in the list
$('list').addEventListener('click', e => {
  const card = e.target.closest('a.card');
  if (card) { e.preventDefault(); playWork(card.dataset.id); }
});
// a standing poster is shown whole
const tall = im => {
  if (!im.closest('.thumb') || im.naturalHeight <= im.naturalWidth) return;
  im.parentNode.style.setProperty('--p', 'url("' + im.currentSrc.replace(/"/g, '%22') + '")');
  im.parentNode.classList.add('is-tall');
};
$('list').addEventListener('load', e => { if (e.target.tagName === 'IMG') tall(e.target); }, true);
document.querySelectorAll('#list .thumb > img').forEach(im => { if (im.complete && im.naturalWidth) tall(im); });
// a day in the calendar (or a month of an older year): its first listed card, and all its cards lit for a moment
let flashT = 0;
document.querySelector('.cal').addEventListener('click', e => {
  const a = e.target.closest('a.hd'); if (!a) return;
  e.preventDefault();
  if (list.hidden) setView('list', true);
  const day = a.dataset.d, at = day ? 'd-' + day : 'm-' + a.dataset.m;
  let cells = [...document.querySelectorAll(day ? '.cell[data-date="' + day + '"]' : '.cell[data-date^="' + a.dataset.m + '-"]')];
  if (cells.length && cells.every(c => c.hidden)) { kind = 'all'; applyFilter(); }
  cells = cells.filter(c => !c.hidden);
  if (!cells.length) return;
  cells[0].scrollIntoView({ block: 'start', behavior: reduced() ? 'auto' : 'smooth' });
  clearTimeout(flashT);
  document.querySelectorAll('.cell.flash').forEach(c => c.classList.remove('flash'));
  cells.forEach(c => c.classList.add('flash'));
  flashT = setTimeout(() => cells.forEach(c => c.classList.remove('flash')), 2600);
  history.replaceState(null, '', location.pathname + location.search + '#' + at);
});

// ── the silent preview: on a resting pointer, or in the middle of a phone's screen
let pvEl = null, pvTimer = 0;
function startPreview(card) {
  if (pvEl === card) return;
  stopPreview();
  const w = byId(card.dataset.id);
  if (!w || !w.preview || reduced() || lightNet() || !modal.hidden) return;
  const th = card.querySelector('.thumb'), fr = document.createElement('iframe');
  fr.tabIndex = -1; fr.title = ''; fr.setAttribute('aria-hidden', 'true');
  fr.setAttribute('allow', "autoplay 'none'");
  fr.style.transform = 'scale(' + (th.clientWidth / 1280) + ')';
  fr.addEventListener('load', () => {
    // same origin: silence anything that would play sound anyway
    try { fr.contentDocument.querySelectorAll('audio, video').forEach(m => { m.muted = true; }); } catch (err) {}
    fr.classList.add('on');
  });
  fr.src = srcOf(w, false);
  th.appendChild(fr); card.classList.add('previewing'); pvEl = card;
}
function stopPreview() {
  clearTimeout(pvTimer);
  if (!pvEl) return;
  const fr = pvEl.querySelector('.thumb iframe'); if (fr) { fr.src = 'about:blank'; fr.remove(); }
  pvEl.classList.remove('previewing'); pvEl = null;
}
const fine = matchMedia('(hover: hover) and (pointer: fine)');
document.querySelectorAll('#list a.card').forEach(card => {
  card.addEventListener('pointerenter', () => { if (!fine.matches) return; clearTimeout(pvTimer); pvTimer = setTimeout(() => startPreview(card), 350); });
  card.addEventListener('pointerleave', () => { if (!fine.matches) return; if (pvEl === card) stopPreview(); else clearTimeout(pvTimer); });
});
if ('IntersectionObserver' in window) {
  const io = new IntersectionObserver(es => {
    if (fine.matches || innerWidth <= 600) return;   // a phone's thumbnails are too small to preview in
    es.forEach(en => {
      if (en.isIntersecting) { clearTimeout(pvTimer); pvTimer = setTimeout(() => startPreview(en.target), 700); }
      else if (pvEl === en.target) stopPreview();
    });
  }, { rootMargin: '-42% 0px -42% 0px' });
  document.querySelectorAll('#list a.card').forEach(c => io.observe(c));
}

// ── the line fills in as the page scrolls, and the bar shows the date in the middle of the screen
const list = $('list'), fill = $('tlFill'), head = $('tlHead'), tcs = [...document.querySelectorAll('.tc')];
let ticking = false;
function onScroll() {
  if (ticking) return; ticking = true;
  requestAnimationFrame(() => {
    ticking = false;
    if (list.hidden) { tcs.forEach(tc => tc.classList.remove('on')); return; }
    // the line by its place in the list (top 60, bottom 88), which holds on a phone too, where it is not drawn
    const box = list.getBoundingClientRect(), rail = { top: box.top + 60, bottom: box.bottom - 88, height: Math.max(0, box.height - 148) }, mid = innerHeight * .5;
    const y = Math.max(0, Math.min(rail.height, mid - rail.top));
    fill.style.transform = 'scaleY(' + (rail.height ? y / rail.height : 0) + ')';
    head.style.transform = 'translateY(' + y + 'px)';
    const inside = rail.top < mid && rail.bottom > mid;
    head.classList.toggle('on', inside);
    let cur = null;
    const cells = list.querySelectorAll('.tl-week:not([hidden]) .cell:not([hidden])');
    for (const c of cells) { cur = c; if (c.getBoundingClientRect().bottom >= mid) break; }
    tcs.forEach(tc => {
      if (cur) tc.lastElementChild.textContent = timecode(cur.dataset.date);
      tc.classList.toggle('on', rail.top < mid && rail.bottom > 0);
    });
  });
}
addEventListener('scroll', onScroll, { passive: true });
addEventListener('resize', onScroll);

// ── the editor view
const ed = $('editor'), edScroll = $('edScroll'), edInner = $('edInner');
const ZOOM = [14, 24, 40, 60, 84];
let zi = +(store.get('worklog.zoom') || 2), edBuilt = false, sel = null;
if (!(zi >= 0 && zi < ZOOM.length)) zi = 2;
const dayMs = 864e5;
const toDay = ymd => Date.UTC(+ymd.slice(0, 4), +ymd.slice(5, 7) - 1, +ymd.slice(8, 10)) / dayMs;
const localToday = () => { const n = new Date(); return Date.UTC(n.getFullYear(), n.getMonth(), n.getDate()) / dayMs; };
const ymdOf = d => new Date(d * dayMs).toISOString().slice(0, 10);
/*
 * The time axis: the days with work in runs; a stretch of more than GAP days
 * with none is cut out and drawn as a narrow break (BRK pixels at every zoom),
 * so pieces years apart do not leave a screen of empty track between them.
 * A run starts on the first of its month and ends ten days after its last
 * piece; the newest run ends four days after today.
 */
const GAP = 45, BRK = 64;
let CL = [];
function axis() {
  const days = [...new Set(WORKS.map(w => toDay(w.date)).concat(localToday()))].sort((a, b) => a - b);
  CL = [];
  days.forEach(d => { const c = CL[CL.length - 1]; if (c && d - c.last <= GAP) c.last = d; else CL.push({ first: d, last: d }); });
  let v = 0;
  CL.forEach((c, i) => {
    c.s = toDay(ymdOf(c.first).slice(0, 8) + '01');
    c.e = c.last + (i === CL.length - 1 ? 4 : 10);
    c.i = i; c.v = v; v += c.e - c.s + 1;
  });
}
const runOf = d => CL.find(c => d <= c.e) || CL[CL.length - 1];
// the left edge of a day (or part of one) in pixels, at this zoom
const at = d => { const c = runOf(d); return (c.v + Math.max(0, d - c.s)) * ZOOM[zi] + c.i * BRK; };
// and back: the day at a point, for keeping the middle of the view on a zoom
const dayAt = x => {
  const px = ZOOM[zi];
  for (const c of CL) {
    const x0 = c.v * px + c.i * BRK, n = c.e - c.s + 1;
    if (x < x0 + n * px + BRK || c.i === CL.length - 1) return c.s + Math.max(0, Math.min(x - x0, n * px)) / px;
  }
  return 0;
};
const axisWidth = () => { const c = CL[CL.length - 1]; return (c.v + c.e - c.s + 1) * ZOOM[zi] + c.i * BRK; };
// "1 yr 3 mo" / "১ বছর ৩ মাস": how long a break is
const gapText = days => {
  const m = Math.max(1, Math.round(days / 30.44)), y = Math.floor(m / 12), r = m % 12;
  return EN ? [y ? y + ' yr' : '', r ? r + ' mo' : ''].filter(Boolean).join(' ') : [y ? bn(y) + ' বছর' : '', r ? bn(r) + ' মাস' : ''].filter(Boolean).join(' ');
};
const MONTHS = <?php
echo wp_json_encode( $rs_wl_en ? array( '', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ) : array_merge( array( '' ), array_values( $rs_wl_months_bn ) ) );
?>;

/* clips: one per kind and day; several pieces on one day are one stacked clip */
function groups() {
  const g = {};
  WORKS.forEach(w => { const k = w.kind + '|' + w.date; (g[k] = g[k] || []).push(w); });
  return g;
}
function drawEditor(keepScroll) {
  axis();
  const px = ZOOM[zi], W = Math.ceil(axisWidth()), sl = edScroll.scrollLeft;
  edInner.style.width = W + 'px';
  // ruler: the first of each month, and days when there is room; a break between runs
  let r = '';
  edInner.querySelectorAll('.brk').forEach(n => n.remove());
  CL.forEach(c => {
    for (let d = c.s; d <= c.e; d++) {
      const s = ymdOf(d), dd = +s.slice(8, 10), x0 = at(d);
      if (dd === 1) r += '<b style="left:' + x0 + 'px">' + MONTHS[+s.slice(5, 7)] + ' ' + yearOf(s) + '</b>';
      if (px >= 24 || dd % 7 === 1) {
        const on = WORKS.some(w => w.date === s);
        r += '<span class="' + (on ? 'w' : '') + '" style="left:' + (x0 + px / 2) + 'px">' + bn(dd) + '</span>';
      }
    }
    const next = CL[c.i + 1];
    if (next) {
      const k = document.createElement('div'); k.className = 'brk'; k.setAttribute('aria-hidden', 'true');
      k.style.left = (at(c.e) + px) + 'px'; k.style.width = BRK + 'px';
      k.innerHTML = '<em>' + gapText(next.s - c.e - 1) + '</em>';
      edInner.appendChild(k);
    }
  });
  $('edRuler').innerHTML = r;
  // tracks
  const g = groups(), tracks = $('edTracks');
  tracks.innerHTML = '';
  TRACKS.forEach(k => {
    const tr = document.createElement('div'); tr.className = 'track';
    tr.setAttribute('role', 'group'); tr.setAttribute('aria-label', KINDS[k]);
    const keys = Object.keys(g).filter(key => key.startsWith(k + '|')).sort();
    keys.forEach((key, idx) => {
      const ws = g[key], w = ws[0], x = at(toDay(w.date));
      const nx = keys[idx + 1] ? at(toDay(g[keys[idx + 1]][0].date)) : x + 3 * px;
      const width = Math.max(12, Math.min(nx - x, 3 * px) - 6);
      const b = document.createElement('button');
      b.type = 'button'; b.className = 'eclip' + (ws.length > 1 ? ' stack' : '') + (ws.some(shown) ? '' : ' dim') + (ws.every(x => x.hidden) ? ' off' : '');
      b.style.left = (x + 3) + 'px'; b.style.width = width + 'px';
      b.dataset.key = key;
      b.setAttribute('aria-label', w.day + ': ' + ws.map(x => x.title).join(', '));
      b.setAttribute('aria-pressed', sel && ws.includes(sel) ? 'true' : 'false');
      b.innerHTML = '<img src="' + w.img + '" alt="" loading="lazy">' +
        (ws.length > 1 ? '<span class="bdg">' + bn(ws.length) + '</span>' : (w.clips.length ? '<span class="bdg">+' + bn(w.clips.length) + '</span>' : ''));
      b.addEventListener('click', () => {
        const nth = sel && ws.includes(sel) ? (ws.indexOf(sel) + 1) % ws.length : 0;   // a stacked clip steps through its day
        select(ws[nth]);
      });
      tr.appendChild(b);
    });
    tracks.appendChild(tr);
  });
  // today
  const td = document.createElement('div'); td.className = 'today'; td.style.left = (at(localToday()) + px / 2) + 'px';
  td.innerHTML = '<em>' + T.today + '</em>'; edInner.appendChild(td);
  edInner.querySelectorAll('.today').forEach((n, i, all) => { if (i < all.length - 1) n.remove(); });
  // the overview strip: month names over one run, the years over several
  const mini = $('edMini'), mark = (d, text) => { const em = document.createElement('em'); em.style.left = (at(d) / W * 100) + '%'; em.textContent = text; mini.appendChild(em); };
  mini.querySelectorAll('i, em').forEach(n => n.remove());
  WORKS.forEach(w => { const i = document.createElement('i'); i.style.left = (at(toDay(w.date)) / W * 100) + '%'; if (!shown(w)) i.style.opacity = '.25'; mini.appendChild(i); });
  if (CL.length === 1) { for (let d = CL[0].s; d <= CL[0].e; d++) { const s = ymdOf(d); if (s.endsWith('-01')) mark(d, MONTHS[+s.slice(5, 7)]); } }
  else CL.forEach(c => { if (!c.i || ymdOf(c.s).slice(0, 4) !== ymdOf(CL[c.i - 1].s).slice(0, 4)) mark(c.s, yearOf(ymdOf(c.s))); });
  const te = document.createElement('em'); te.style.right = '0'; te.style.color = 'var(--fg)'; te.textContent = T.today; mini.appendChild(te);
  edBuilt = true;
  placePh();
  if (keepScroll === 'end') edScroll.scrollLeft = edScroll.scrollWidth;
  else if (keepScroll && typeof keepScroll === 'number') edScroll.scrollLeft = keepScroll;
  else if (keepScroll !== false) edScroll.scrollLeft = sl;
  syncMini();
}
function placePh() {
  if (!sel) return;
  const b = edInner.querySelector('.eclip[data-key="' + sel.kind + '|' + sel.date + '"]');
  $('edPh').style.left = (b ? b.offsetLeft + b.offsetWidth / 2 : at(toDay(sel.date))) + 'px';
}
function select(w, scroll) {
  sel = w;
  edInner.querySelectorAll('.eclip').forEach(b => b.setAttribute('aria-pressed', b.dataset.key === w.kind + '|' + w.date ? 'true' : 'false'));
  $('edImg').src = w.img; $('edImg').alt = w.title;
  $('edStamp').textContent = timecode(w.date);
  $('edType').textContent = w.type; $('edTitle').textContent = w.title; $('edDesc').textContent = w.desc;
  $('edDate').textContent = w.day + ' ' + yearOf(w.date); $('edTrack').textContent = KINDS[w.kind];
  const same = WORKS.filter(x => x.date === w.date && x !== w);
  $('edSameWrap').hidden = !same.length;
  $('edSame').innerHTML = '';
  same.forEach(x => { const b = document.createElement('button'); b.type = 'button'; b.innerHTML = '<img src="' + x.img + '" alt="">'; b.append(x.title); b.onclick = () => select(x, true); $('edSame').appendChild(b); });
  $('edClipsWrap').hidden = !w.clips.length;
  $('edClips').innerHTML = '';
  w.clips.forEach((c, i) => { const b = document.createElement('button'); b.type = 'button'; b.innerHTML = '<img src="' + c.img + '" alt="">'; b.append(c.num + ' · ' + c.title); b.onclick = () => playClip(w.id, i); $('edClips').appendChild(b); });
  if (hooks.onSelect) hooks.onSelect(w);
  placePh();
  if (scroll) { const x = parseFloat($('edPh').style.left) || 0; edScroll.scrollTo({ left: x - edScroll.clientWidth / 2, behavior: reduced() ? 'auto' : 'smooth' }); }
}
function syncMini() {
  const sw = edScroll.scrollWidth || 1, win = $('edWin');
  win.style.left = (edScroll.scrollLeft / sw * 100) + '%';
  win.style.width = Math.min(100, edScroll.clientWidth / sw * 100) + '%';
  $('edMini').setAttribute('aria-valuenow', String(Math.round(edScroll.scrollLeft / Math.max(1, sw - edScroll.clientWidth) * 100)));
}
edScroll.addEventListener('scroll', syncMini, { passive: true });
$('edMini').addEventListener('click', e => {
  const r = $('edMini').getBoundingClientRect(), f = (e.clientX - r.left) / r.width;
  edScroll.scrollTo({ left: f * edScroll.scrollWidth - edScroll.clientWidth / 2, behavior: reduced() ? 'auto' : 'smooth' });
});
$('edMini').addEventListener('keydown', e => {
  const k = { ArrowLeft: -1, ArrowRight: 1 }[e.key]; if (!k) return;
  e.preventDefault(); edScroll.scrollBy({ left: k * edScroll.clientWidth * .5 });
});
function zoom(k) {
  const n = Math.max(0, Math.min(ZOOM.length - 1, zi + k)); if (n === zi) return;
  const mid = dayAt(edScroll.scrollLeft + edScroll.clientWidth / 2);
  zi = n; store.set('worklog.zoom', String(zi));
  drawEditor(false);
  edScroll.scrollLeft = at(mid) - edScroll.clientWidth / 2; syncMini();
}
$('edIn').onclick = () => zoom(1);
$('edOut').onclick = () => zoom(-1);
const playSel = () => { if (sel) playWork(sel.id); };
$('edPlay').onclick = playSel;
$('edMonPlay').onclick = playSel;
addEventListener('resize', () => { if (edBuilt && !ed.hidden) syncMini(); });

// ── list or editor
function setView(v, save) {
  const isEd = v === 'editor';
  stopPreview();
  ed.hidden = !isEd; list.hidden = isEd;
  $('vList').setAttribute('aria-pressed', isEd ? 'false' : 'true');
  $('vEd').setAttribute('aria-pressed', isEd ? 'true' : 'false');
  if (save) store.set('worklog.view', v);
  if (isEd && !edBuilt) { sel = WORKS.find(shown) || WORKS[0]; drawEditor('end'); select(sel, false); edScroll.scrollLeft = (parseFloat($('edPh').style.left) || 0) - edScroll.clientWidth * .7; syncMini(); }
  onScroll();
}
$('vList').onclick = () => setView('list', true);
$('vEd').onclick = () => setView('editor', true);

// ── back to top
const toTop = $('toTop');
const topBtn = () => toTop.classList.toggle('is-visible', scrollY > 300);
addEventListener('scroll', topBtn, { passive: true });
toTop.onclick = () => {
  scrollTo({ top: 0, behavior: reduced() ? 'auto' : 'smooth' });
  document.querySelector('.brand').focus({ preventScroll: true });   // the button hides at the top, so keyboard focus moves to the first link
};
topBtn();

<?php if ( $rs_wl_manage ) : ?>
// ── for the controls of someone who manages the log (src/js/work-log-manage.js)
window.rsWorkLog = Object.assign(hooks, {
  works: WORKS, byId, digits: bn, fit: fitModal,
  setGate(fn) { gate = fn; },
  refresh: applyFilter,
  selected: () => sel,
  playing: () => modal.hidden ? null : byId(P.list[P.i].id),
  /* Take a deleted piece out of the player and the editor view. */
  remove(w) {
    const i = WORKS.indexOf(w); if (i >= 0) WORKS.splice(i, 1);
    if (!modal.hidden) {
      const cur = P.list[P.i];
      P.list = P.list.filter(p => p.id !== w.id);
      if (!P.list.length) closePlayer();
      else if (cur.id === w.id) { P.i = Math.min(P.i, P.list.length - 1); loadModal(); }
      else P.i = P.list.indexOf(cur);
    }
    if (sel === w) { sel = WORKS.find(shown) || WORKS[0] || null; if (edBuilt && sel) select(sel); }
  },
});
<?php endif; ?>
// ── start
if (store.get('worklog.view') === 'editor') setView('editor', false);
onScroll();
// an address with a piece (#id) or a clip (#id/3) opens it
const m = location.hash.match(/^#([a-z0-9-]+)(?:\/(\d+))?$/);
if (m && byId(m[1])) {
  if (m[2] && byId(m[1]).clips[+m[2] - 1]) playClip(m[1], +m[2] - 1); else playWork(m[1]);
}
})();
</script>
<?php rs_work_log_manage_tail(); ?>
<?php if ( function_exists( 'rs_stats_beacon' ) ) { rs_stats_beacon(); } ?>
</body>
</html>
