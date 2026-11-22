<?php
/**
 * The work log page, served by rs_work_log_render().
 *
 * Both editions, each in its own language. In the showreel's look: a short
 * pitch, a calendar strip of the days work went up, then two views of the
 * same pieces (rs_work_log_items()):
 *
 * - List: a vertical timeline, newest first, a dot per day. A day with one
 *   piece puts its card left or right of the line in turn; a day with more
 *   puts them side by side in one box; a project (a piece with clips)
 *   shows its clips in a strip under its own card. While the page scrolls,
 *   the line fills in orange up to the middle of the screen and the bar
 *   shows the date there, like a timecode.
 * - Editor: the pieces as clips on an editing timeline, one track per kind,
 *   with a monitor above, an overview of the whole time below it and zoom.
 *
 * A card shows a silent preview of its piece when the pointer rests on it
 * (on a phone, when it sits in the middle of the screen), and plays it in
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
$rs_wl_items = rs_work_log_items();
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

/* Month → day → pieces, newest first. */
$rs_wl_tree = array();
foreach ( $rs_wl_items as $rs_wl_it ) {
	$rs_wl_tree[ substr( $rs_wl_it['date'], 0, 7 ) ][ $rs_wl_it['date'] ][] = $rs_wl_it;
}

/* The calendar strip: every month from the first piece's to this one. */
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
for ( $rs_wl_ts = strtotime( $rs_wl_now_ym . '-01 12:00:00 UTC' ); gmdate( 'Y-m', $rs_wl_ts ) >= $rs_wl_first_ym; $rs_wl_ts = strtotime( '-1 month', $rs_wl_ts ) ) {
	$rs_wl_strip[] = gmdate( 'Y-m', $rs_wl_ts );
}

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
	$src = $rs_wl_abs( $it['src'] );
	if ( ! empty( $it['map'] ) ) {
		$src .= '?ver=' . rawurlencode( RS_VERSION );
	}
	return $src;
};

$rs_wl_icon_play = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.6-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5Z"/></svg>';

/** One card. */
$rs_wl_card = function ( $it, $extra = '' ) use ( $rs_wl_t, $rs_wl_abs, $rs_wl_open, $rs_wl_has45, $rs_wl_icon_play ) {
	$type = $rs_wl_t( $it['type_bn'], $it['type_en'] );
	$desc = $rs_wl_t( $it['desc_bn'], $it['desc_en'] );
	ob_start();
	?>
	<a class="card <?php echo esc_attr( $extra ); ?>" href="<?php echo esc_url( $rs_wl_open( $it ) ); ?>" data-id="<?php echo esc_attr( $it['id'] ); ?>" data-kind="<?php echo esc_attr( $it['kind'] ); ?>" target="_blank" rel="noopener">
		<span class="thumb">
			<img src="<?php echo esc_url( $rs_wl_abs( $it['img'] ) ); ?>" alt="" width="640" height="360" loading="lazy" decoding="async">
			<span class="pv-tag" aria-hidden="true"><i></i><?php echo esc_html( $rs_wl_t( 'প্রিভিউ · শব্দ বন্ধ', 'Preview · muted' ) ); ?></span>
			<span class="play" aria-hidden="true"><?php echo $rs_wl_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A fixed SVG. ?></span>
			<?php if ( $rs_wl_has45( $it ) ) : ?><span class="cuts" aria-hidden="true">16:9 · 4:5</span><?php endif; ?>
		</span>
		<span class="body">
			<span class="kicker"><?php echo esc_html( $type ); ?></span>
			<span class="title"><?php echo esc_html( $it['title'] ); ?></span>
			<span class="desc"><?php echo esc_html( $desc ); ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
};

/** A project: its own card, then its clips in a strip. */
$rs_wl_project = function ( $it ) use ( $rs_wl_t, $rs_wl_abs, $rs_wl_open, $rs_wl_has45, $rs_wl_en, $rs_wl_icon_play ) {
	$type  = $rs_wl_t( $it['type_bn'], $it['type_en'] );
	$desc  = $rs_wl_t( $it['desc_bn'], $it['desc_en'] );
	$clips = $it['clips'];
	$n     = count( $clips );
	$shown = 5;
	ob_start();
	?>
	<div class="proj" data-id="<?php echo esc_attr( $it['id'] ); ?>" data-kind="<?php echo esc_attr( $it['kind'] ); ?>">
		<a class="card card--wide" href="<?php echo esc_url( $rs_wl_open( $it ) ); ?>" data-id="<?php echo esc_attr( $it['id'] ); ?>" target="_blank" rel="noopener">
			<span class="thumb">
				<img src="<?php echo esc_url( $rs_wl_abs( $it['img'] ) ); ?>" alt="" width="640" height="360" loading="lazy" decoding="async">
				<span class="pv-tag" aria-hidden="true"><i></i><?php echo esc_html( $rs_wl_t( 'প্রিভিউ · শব্দ বন্ধ', 'Preview · muted' ) ); ?></span>
				<span class="play play--label" aria-hidden="true"><?php echo $rs_wl_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A fixed SVG. ?><?php echo esc_html( $rs_wl_t( 'মূল ভিডিও', 'Main reel' ) ); ?></span>
			</span>
			<span class="body">
				<span class="kicker"><?php echo esc_html( $type ); ?></span>
				<span class="title"><?php echo esc_html( $it['title'] ); ?></span>
				<span class="desc"><?php echo esc_html( $desc ); ?></span>
				<span class="chips">
					<span><?php echo esc_html( $rs_wl_t( '১টি মূল ভিডিও', '1 main reel' ) ); ?></span>
					<span><?php echo esc_html( $rs_wl_en ? $n . ' clips' : rs_bn_digits( $n ) . 'টি ক্লিপ' ); ?></span>
					<?php if ( $rs_wl_has45( $it ) ) : ?><span>16:9 · 4:5</span><?php endif; ?>
				</span>
			</span>
		</a>
		<div class="strip">
			<div class="strip-head">
				<b><?php echo esc_html( $rs_wl_t( 'এই প্রজেক্টের ক্লিপ', 'Clips in this project' ) ); ?></b>
				<button type="button" class="link-btn" data-playall="<?php echo esc_attr( $it['id'] ); ?>"><?php echo esc_html( $rs_wl_t( 'সব ক্লিপ প্লেলিস্টে চালান →', 'Play all clips as a playlist →' ) ); ?></button>
			</div>
			<ol class="clips" id="clips-<?php echo esc_attr( $it['id'] ); ?>">
				<?php foreach ( $clips as $i => $c ) : ?>
					<li<?php echo $i >= $shown ? ' class="more" hidden' : ''; ?>>
						<a class="clip" href="<?php echo esc_url( $rs_wl_abs( $c['src'] ) ); ?>" data-id="<?php echo esc_attr( $it['id'] ); ?>" data-clip="<?php echo (int) $i; ?>" target="_blank" rel="noopener">
							<img src="<?php echo esc_url( $rs_wl_abs( $c['img'] ) ); ?>" alt="" width="320" height="180" loading="lazy" decoding="async">
							<span><?php echo esc_html( rs_bn_digits( $i + 1 ) . ' · ' . $c['title'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
				<?php if ( $n > $shown ) : ?>
					<li class="clip-more">
						<button type="button" aria-expanded="false" aria-controls="clips-<?php echo esc_attr( $it['id'] ); ?>" data-more="<?php echo esc_attr( $it['id'] ); ?>">
							<b>+<?php echo esc_html( rs_bn_digits( $n - $shown ) ); ?></b>
							<span><?php echo esc_html( $rs_wl_t( 'আরও ক্লিপ', 'More clips' ) ); ?></span>
						</button>
					</li>
				<?php endif; ?>
			</ol>
		</div>
	</div>
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
	html { scroll-behavior: smooth; scroll-padding-top: 140px; }
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
	.hero { padding: 84px 0 36px; text-align: center; }
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

	/* the controls under the bar */
	.ctl { position: sticky; top: var(--bar); z-index: 15; background: rgba(0, 0, 0, .86); -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, .08); }
	.ctl-in { display: flex; align-items: center; justify-content: space-between; gap: 10px 16px; flex-wrap: wrap; padding-block: 10px; }
	.seg { display: inline-flex; padding: 4px; border-radius: 980px; background: rgba(255, 255, 255, .1); }
	.seg[hidden] { display: none; }
	.seg button { min-height: 38px; padding: 0 16px; border: 0; border-radius: 980px; background: none; color: var(--fg2); font-size: 14px; cursor: pointer; }
	.seg button[aria-pressed="true"] { background: var(--fg); color: var(--ink); font-weight: 600; }
	.chips-f { display: flex; gap: 8px; flex-wrap: wrap; }
	.chip { min-height: 40px; padding: 0 16px; border-radius: 980px; border: 1px solid var(--line2); background: none; color: var(--fg2); font-size: 14px; cursor: pointer; }
	.chip[aria-pressed="true"] { border-color: var(--fg); background: var(--fg); color: var(--ink); font-weight: 600; }
	.tc { display: inline-flex; align-items: center; gap: 9px; padding: 7px 13px; border-radius: 8px; border: 1px solid rgba(255, 74, 28, .5); color: var(--acc); font-weight: 700; font-size: 15px; font-variant-numeric: tabular-nums; opacity: 0; transition: opacity .25s; white-space: nowrap; }
	.tc.on { opacity: 1; }
	.tc i { width: 8px; height: 8px; border-radius: 50%; background: var(--acc); }

	/* the list: one line down the middle, a dot per day */
	.tl { position: relative; padding: 56px 0 72px; }
	.tl-rail, .tl-fill { position: absolute; top: 56px; bottom: 72px; left: 50%; width: 2px; margin-left: -1px; }
	.tl-rail { background: rgba(255, 255, 255, .14); }
	.tl-fill { background: var(--acc); transform-origin: top; transform: scaleY(0); }
	/* The playhead sits under a day's box, as the rail does, so it never runs over a card. */
	.tl-head { position: absolute; top: 56px; left: 50%; width: 18px; height: 18px; margin: -9px 0 0 -9px; border-radius: 50%; background: var(--acc); box-shadow: 0 0 0 6px rgba(255, 74, 28, .25), 0 0 24px rgba(255, 74, 28, .6); z-index: 1; pointer-events: none; opacity: 0; transition: opacity .25s; }
	.tl-head.on { opacity: 1; }
	.tl-month { position: relative; }
	.tl-mhead { position: relative; z-index: 2; display: flex; justify-content: center; margin: 0 0 40px; }
	.tl-mhead > span { padding: 10px 22px; border-radius: 980px; background: var(--fg); color: var(--ink); font-size: 18px; font-weight: 700; }
	.tl-mhead small { font-size: inherit; font-weight: 500; color: #515154; }
	.tl-day { position: relative; margin-bottom: 44px; }
	.tl-node { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; }
	.tl-node > i { width: 14px; height: 14px; border-radius: 50%; background: var(--acc); box-shadow: 0 0 0 6px var(--bg); }
	.tl-node time { padding: 2px 8px; background: var(--bg); color: var(--fg2); font-size: 14px; font-weight: 600; }
	.tl-node time b { color: var(--acc); font-weight: 600; }
	.tl-day.single { display: grid; grid-template-columns: minmax(0, 1fr) 150px minmax(0, 1fr); align-items: center; }
	.tl-day.single .tl-node { grid-column: 2; grid-row: 1; }
	.tl-day.single .slot { grid-row: 1; display: flex; }
	.tl-day.single.l .slot { grid-column: 1; justify-content: flex-end; }
	.tl-day.single.r .slot { grid-column: 3; justify-content: flex-start; }
	.tl-day.group .tl-node { margin-bottom: 22px; }
	.box { position: relative; z-index: 1; max-width: 1000px; margin-inline: auto; padding: 18px; border: 1px solid rgba(255, 255, 255, .12); border-radius: 24px; background: var(--tile); display: flex; flex-direction: column; gap: 18px; }
	.box-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; }
	.box .card { max-width: none; background: var(--tile2); }

	/* cards */
	.card { display: block; width: 100%; max-width: 480px; text-decoration: none; border: 1px solid var(--line); border-radius: 18px; background: var(--tile); overflow: hidden; transition: transform .4s var(--out), border-color .3s; }
	.card:hover { transform: translateY(-4px); border-color: rgba(255, 74, 28, .6); }
	.thumb { position: relative; display: block; aspect-ratio: 16 / 9; overflow: hidden; background: #111; }
	.thumb > img { width: 100%; height: 100%; object-fit: cover; display: block; }
	.thumb iframe { position: absolute; left: 0; top: 0; width: 1280px; height: 720px; border: 0; transform-origin: 0 0; pointer-events: none; opacity: 0; transition: opacity .35s; background: #050506; }
	.thumb iframe.on { opacity: 1; }
	.play { position: absolute; inset: 0; margin: auto; width: 56px; height: 56px; border-radius: 50%; background: var(--acc); color: var(--ink); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .25s; }
	.play svg { width: 20px; height: 20px; fill: currentColor; margin-left: 2px; }
	.card:hover .play, .card:focus-visible .play { opacity: 1; }
	.previewing .play { opacity: 0 !important; }
	.play--label { inset: auto auto 14px 14px; margin: 0; width: auto; height: auto; padding: 8px 14px; gap: 8px; border-radius: 980px; font-size: 14px; font-weight: 700; opacity: 1; }
	.play--label svg { width: 12px; height: 12px; margin: 0; }
	.cuts { position: absolute; right: 10px; top: 10px; padding: 3px 9px; border-radius: 980px; background: rgba(0, 0, 0, .7); font-size: 12px; font-weight: 600; color: var(--fg2); }
	.pv-tag { position: absolute; left: 10px; top: 10px; z-index: 2; display: flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 980px; background: rgba(0, 0, 0, .75); font-size: 12px; font-weight: 600; opacity: 0; transition: opacity .25s; }
	.pv-tag i { width: 7px; height: 7px; border-radius: 50%; background: var(--acc); }
	.previewing .pv-tag { opacity: 1; }
	.previewing .cuts { opacity: 0; }
	.body { display: block; padding: 16px 20px 18px; }
	.body .title { display: block; margin: 4px 0 6px; font-size: 21px; font-weight: 700; line-height: 1.3; }
	.body .desc { display: block; color: var(--mut); font-size: 15px; line-height: 1.55; }

	/* a project */
	.proj { border: 1px solid rgba(255, 74, 28, .45); border-radius: 20px; background: var(--tile); overflow: hidden; }
	.card--wide { max-width: none; border: 0; border-radius: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); background: none; }
	.card--wide:hover { transform: none; }
	.card--wide .body { padding: 24px 28px; display: flex; flex-direction: column; justify-content: center; gap: 2px; }
	.card--wide .title { font-size: 28px; }
	.chips { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
	.chips span { padding: 5px 12px; border-radius: 980px; background: rgba(255, 255, 255, .1); font-size: 13px; color: var(--fg2); }
	.strip { border-top: 1px solid var(--line); padding: 18px 22px 22px; }
	.strip-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
	.strip-head b { font-size: 15px; }
	.clips { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; }
	.clip { display: block; text-decoration: none; color: var(--fg2); }
	.clip img { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 8px; display: block; transition: transform .3s var(--out); }
	.clip:hover img { transform: scale(1.04); }
	.clip span { display: block; margin-top: 6px; font-size: 12px; line-height: 1.35; }
	.clip-more button { width: 100%; padding: 0; border: 0; background: none; cursor: pointer; text-align: left; color: var(--mut); }
	.clip-more b { display: flex; align-items: center; justify-content: center; aspect-ratio: 16 / 9; border-radius: 8px; background: var(--tile3); color: var(--fg); font-size: 20px; }
	.clip-more span { display: block; margin-top: 6px; font-size: 12px; }
	.tl-end { position: relative; z-index: 2; display: flex; justify-content: center; }
	.tl-end span { padding: 10px 20px; border-radius: 980px; border: 1px solid var(--line2); background: var(--bg); color: var(--mut); font-size: 15px; }

	/* the editor */
	.ed { padding: 28px 0 72px; }
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
	.m-note { margin: 0; color: var(--dim); font-size: 13px; }

	@media (max-width: 900px) {
		.ed-top { grid-template-columns: 1fr; }
		.clips { grid-template-columns: repeat(3, minmax(0, 1fr)); }
	}
	@media (max-width: 760px) {
		.hero { padding-top: 52px; text-align: left; }
		.hero h1, .hero p { margin-left: 0; }
		.cal { padding: 18px 16px; }
		.cal-row > a, .cal-row > span { width: 64px; font-size: 13px; }
		.cal-days { gap: 2px; }
		.hd { height: 16px; border-radius: 3px; }
		.tl { padding-left: 34px; }
		.tl-rail, .tl-fill, .tl-head { left: 12px; }
		.tl-mhead { justify-content: flex-start; margin-left: -30px; }
		.tl-mhead > span { font-size: 15px; padding: 7px 14px; }
		.tl-day.single { display: block; }
		.tl-node { flex-direction: row; justify-content: flex-start; margin: 0 0 10px -28px; gap: 12px; }
		.tl-node > i { width: 12px; height: 12px; box-shadow: 0 0 0 5px var(--bg); }
		.tl-node time { padding: 0; }
		.tl-day.single .slot { display: block; }
		.card { max-width: none; }
		.box { padding: 12px; }
		.box-grid { grid-template-columns: 1fr; }
		.card--wide { grid-template-columns: 1fr; }
		.card--wide .body { padding: 18px 20px; }
		.clips { grid-template-columns: repeat(2, minmax(0, 1fr)); }
		.tl-end { justify-content: flex-start; margin-left: -30px; }
		.ctl-in { gap: 8px; }
		.chips-f { order: 3; flex: 1 0 100%; flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; max-width: 100%; }
		.tc { order: 2; padding: 5px 10px; font-size: 13px; }
		.chip { flex-shrink: 0; }
		.ed-lab, .ed-labs { width: 86px; }
		.ed-labs div small { display: none; }
		.legend { margin-left: 0; }
		.brand { font-size: 17px; }
		.nav a { padding: 8px 9px; font-size: 14px; }
		.nav .nl { display: none; }
	}
</style>
</head>
<body>

<header class="top">
	<div class="wrap top-in">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $rs_wl_t( 'রাইসুল সোহান', 'Raisul Sohan' ) ); ?><span class="dot" aria-hidden="true"></span></a>
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
		<span class="kicker"><?php echo esc_html( $rs_wl_title . ' · ' . rs_bn_digits( substr( $rs_wl_items[0]['date'], 0, 4 ) ) ); ?></span>
		<h1><?php echo esc_html( $rs_wl_t( 'কবে, কোন কাজ', 'What I made, and when' ) ); ?><span class="dot" aria-hidden="true"></span></h1>
		<p><?php echo esc_html( $rs_wl_t( 'টুল, ম্যাপ অ্যানিমেশন, ডকুমেন্টারি, শোরিল: যা বানাই সব এখানে, তারিখ ধরে। সবচেয়ে নতুনটা সবার উপরে, কার্ডে চাপলেই চলে।', 'Tools, map animation, documentary, showreels: everything I make, by date. The newest is on top; click a card and it plays.' ) ); ?></p>
	</section>

	<div class="wrap">
		<section class="cal" aria-label="<?php echo esc_attr( $rs_wl_t( 'কোন দিনে কাজ এসেছে', 'The days work went up' ) ); ?>">
			<div class="cal-head">
				<b><?php
				$rs_wl_nm = count( $rs_wl_strip );
				$rs_wl_ni = count( $rs_wl_items );
				echo esc_html( $rs_wl_en ? $rs_wl_ni . ' pieces in ' . $rs_wl_nm . ( 1 === $rs_wl_nm ? ' month' : ' months' ) : rs_bn_digits( $rs_wl_nm ) . ' মাসে ' . rs_bn_digits( $rs_wl_ni ) . 'টি কাজ' );
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
				<button type="button" class="chip" data-k="all" aria-pressed="true"><?php echo esc_html( $rs_wl_t( 'সব কাজ', 'All work' ) ); ?></button>
				<?php foreach ( $rs_wl_kinds as $rs_wl_k => $rs_wl_kl ) : ?>
					<button type="button" class="chip" data-k="<?php echo esc_attr( $rs_wl_k ); ?>" aria-pressed="false"><?php echo esc_html( $rs_wl_t( $rs_wl_kl['bn'], $rs_wl_kl['en'] ) ); ?></button>
				<?php endforeach; ?>
			</div>
			<span class="tc" id="tc" aria-hidden="true"><i></i><span id="tcText"></span></span>
		</div>
	</div>

	<section class="wrap tl" id="list" aria-label="<?php echo esc_attr( $rs_wl_t( 'টাইমলাইন', 'Timeline' ) ); ?>">
		<div class="tl-rail" aria-hidden="true"></div>
		<div class="tl-fill" id="tlFill" aria-hidden="true"></div>
		<div class="tl-head" id="tlHead" aria-hidden="true"></div>
		<?php $rs_wl_side = 0; ?>
		<?php foreach ( $rs_wl_tree as $rs_wl_ym => $rs_wl_days ) : ?>
			<?php $rs_wl_mcount = array_sum( array_map( 'count', $rs_wl_days ) ); ?>
			<section class="tl-month" id="m-<?php echo esc_attr( $rs_wl_ym ); ?>">
				<h2 class="tl-mhead"><span><?php echo esc_html( $rs_wl_month( $rs_wl_ym ) ); ?> <small>· <span class="mcount"><?php echo esc_html( $rs_wl_count( $rs_wl_mcount ) ); ?></span></small></span></h2>
				<?php foreach ( $rs_wl_days as $rs_wl_date => $rs_wl_list ) : ?>
					<?php
					$rs_wl_n      = count( $rs_wl_list );
					$rs_wl_single = 1 === $rs_wl_n && empty( $rs_wl_list[0]['clips'] );
					$rs_wl_cls    = $rs_wl_single ? 'single ' . ( 0 === $rs_wl_side++ % 2 ? 'l' : 'r' ) : 'group';
					?>
					<div class="tl-day <?php echo esc_attr( $rs_wl_cls ); ?>" id="d-<?php echo esc_attr( $rs_wl_date ); ?>" data-date="<?php echo esc_attr( $rs_wl_date ); ?>">
						<div class="tl-node">
							<i aria-hidden="true"></i>
							<time datetime="<?php echo esc_attr( $rs_wl_date ); ?>"><?php echo esc_html( $rs_wl_day( $rs_wl_date ) ); ?><?php if ( $rs_wl_n > 1 ) : ?> <b class="dcount">· <?php echo esc_html( $rs_wl_count( $rs_wl_n ) ); ?></b><?php endif; ?></time>
						</div>
						<?php if ( $rs_wl_single ) : ?>
							<div class="slot"><?php echo $rs_wl_card( $rs_wl_list[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside. ?></div>
						<?php else : ?>
							<div class="box">
								<?php
								$rs_wl_plain = array();
								foreach ( $rs_wl_list as $rs_wl_it ) {
									if ( ! empty( $rs_wl_it['clips'] ) ) {
										echo $rs_wl_project( $rs_wl_it ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside.
									} else {
										$rs_wl_plain[] = $rs_wl_it;
									}
								}
								?>
								<?php if ( $rs_wl_plain ) : ?>
									<div class="box-grid">
										<?php foreach ( $rs_wl_plain as $rs_wl_it ) : ?>
											<?php echo $rs_wl_card( $rs_wl_it ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside. ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endforeach; ?>
		<div class="tl-end"><span><?php echo esc_html( $rs_wl_t( 'শুরু', 'The start' ) . ' · ' . $rs_wl_month( $rs_wl_first_ym ) ); ?></span></div>
	</section>

	<section class="wrap ed" id="editor" aria-label="<?php echo esc_attr( $rs_wl_t( 'এডিটর ভিউ', 'Editor view' ) ); ?>" hidden>
		<ol class="ed-help">
			<li><span class="n">১</span><?php echo esc_html( $rs_wl_t( 'নিচের টাইমলাইনে যেকোনো ক্লিপে চাপুন। একেকটা ক্লিপ মানে একেকটা কাজ।', 'Click any clip on the timeline below. Each clip is one piece of work.' ) ); ?></li>
			<li><span class="n">২</span><?php echo esc_html( $rs_wl_t( 'উপরের বড় পর্দায় সেই কাজের ছবি আর ডানে তার তথ্য চলে আসবে।', 'The monitor shows that piece, and the panel beside it tells you about it.' ) ); ?></li>
			<li><span class="n">৩</span><?php echo esc_html( $rs_wl_t( '"চালান" চাপলে ভিডিওটা পুরো স্ক্রিনে চলবে।', 'Press "Play" and it plays in the player.' ) ); ?></li>
			<li><span class="n">৪</span><?php echo esc_html( $rs_wl_t( 'অন্য সময়ে যেতে উপরের সরু দাগে চাপুন, আর + − দিয়ে জুম করুন।', 'Click the thin strip to move to another time; + and − zoom.' ) ); ?></li>
		</ol>
		<div class="ed-top">
			<div class="mon">
				<img id="edImg" src="" alt="">
				<span class="stamp"><span class="n n--s">২</span><span id="edStamp"></span></span>
				<button type="button" id="edMonPlay" aria-label="<?php echo esc_attr( $rs_wl_t( 'চালান', 'Play' ) ); ?>"></button>
			</div>
			<div class="insp" aria-live="polite">
				<span class="kicker" id="edType"></span>
				<h2 id="edTitle"></h2>
				<p id="edDesc"></p>
				<div class="chips"><span id="edDate"></span><span id="edTrack"></span></div>
				<div class="insp-sub" id="edSameWrap" hidden><span><?php echo esc_html( $rs_wl_t( 'একই দিনে', 'Same day' ) ); ?></span><div class="insp-list" id="edSame"></div></div>
				<div class="insp-sub" id="edClipsWrap" hidden><span><?php echo esc_html( $rs_wl_t( 'এই প্রজেক্টের ক্লিপ', 'Clips in this project' ) ); ?></span><div class="insp-list" id="edClips"></div></div>
				<div class="insp-play"><span class="n">৩</span><button type="button" class="pill pill--acc" id="edPlay"><?php echo $rs_wl_icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A fixed SVG. ?><?php echo esc_html( $rs_wl_t( 'চালান', 'Play' ) ); ?></button></div>
			</div>
		</div>
		<div class="ed-panel">
			<div class="ed-row">
				<span class="ed-lab"><span class="n n--s">৪</span><?php echo esc_html( $rs_wl_t( 'পুরো সময়', 'All time' ) ); ?></span>
				<div class="mini" id="edMini" role="slider" tabindex="0" aria-label="<?php echo esc_attr( $rs_wl_t( 'টাইমলাইনের কোন অংশ দেখছেন', 'The part of the timeline in view' ) ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100"><span class="mini-win" id="edWin"></span></div>
			</div>
			<div class="ed-row" style="margin-top: -6px;">
				<span class="ed-lab"><span class="n n--s">১</span><?php echo esc_html( $rs_wl_t( 'ক্লিপে চাপুন', 'Click a clip' ) ); ?></span>
				<div class="zoom" role="group" aria-label="<?php echo esc_attr( $rs_wl_t( 'জুম', 'Zoom' ) ); ?>">
					<button type="button" id="edOut" aria-label="<?php echo esc_attr( $rs_wl_t( 'ছোট করুন', 'Zoom out' ) ); ?>">−</button>
					<button type="button" id="edIn" aria-label="<?php echo esc_attr( $rs_wl_t( 'বড় করুন', 'Zoom in' ) ); ?>">+</button>
				</div>
			</div>
			<div class="ed-body">
				<div class="ed-labs" aria-hidden="true">
					<?php $rs_wl_v = count( $rs_wl_kinds ); ?>
					<?php foreach ( $rs_wl_kinds as $rs_wl_k => $rs_wl_kl ) : ?>
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
	<p><?php echo esc_html( $rs_wl_t( 'বানিয়েছেন', 'Made by' ) ); ?> <a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>"><?php echo esc_html( $rs_wl_t( 'রাইসুল সোহান', 'Raisul Sohan' ) ); ?></a>. <?php echo esc_html( $rs_wl_t( 'এখানের প্রতিটা কাজ ব্রাউজারে কোডে আঁকা; কোনোটাই ভিডিও ফাইল নয়, শোরিল ছাড়া।', 'Every piece here is drawn in code in the browser; none is a video file, save the showreel.' ) ); ?></p>
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
		<iframe class="m-frame" id="mFrame" title="<?php echo esc_attr( $rs_wl_t( 'কাজের প্লেয়ার', 'Work player' ) ); ?>" allow="autoplay; fullscreen" allowfullscreen></iframe>
		<p class="m-note" id="mNote"><?php echo esc_html( $rs_wl_t( '[ ] দিয়ে আগে-পরে · Esc দিয়ে বন্ধ', '[ ] previous and next · Esc to close' ) ); ?></p>
	</div>
</div>

<script>
(function () {
'use strict';
const VER = <?php echo wp_json_encode( RS_VERSION ); ?>, EN = <?php echo $rs_wl_en ? 'true' : 'false'; ?>;
const KINDS = <?php
$rs_wl_kjs = array();
foreach ( $rs_wl_kinds as $rs_wl_k => $rs_wl_kl ) {
	$rs_wl_kjs[ $rs_wl_k ] = $rs_wl_t( $rs_wl_kl['bn'], $rs_wl_kl['en'] );
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
		'title'   => $rs_wl_it['title'],
		'type'    => $rs_wl_t( $rs_wl_it['type_bn'], $rs_wl_it['type_en'] ),
		'desc'    => $rs_wl_t( $rs_wl_it['desc_bn'], $rs_wl_it['desc_en'] ),
		'img'     => $rs_wl_abs( $rs_wl_it['img'] ),
		'src'     => $rs_wl_abs( $rs_wl_it['src'] ),
		'src45'   => empty( $rs_wl_it['src45'] ) ? '' : $rs_wl_abs( $rs_wl_it['src45'] ),
		'map'     => ! empty( $rs_wl_it['map'] ),
		'local'   => ! preg_match( '#^https?://#', $rs_wl_it['src'] ),
		'preview' => ! isset( $rs_wl_it['preview'] ) || false !== $rs_wl_it['preview'],
		'clips'   => array(),
	);
	if ( ! empty( $rs_wl_it['clips'] ) ) {
		foreach ( $rs_wl_it['clips'] as $rs_wl_i => $rs_wl_c ) {
			$rs_wl_row['clips'][] = array(
				'title' => $rs_wl_c['title'],
				'num'   => rs_bn_digits( $rs_wl_i + 1 ),
				'src'   => $rs_wl_abs( $rs_wl_c['src'] ),
				'img'   => $rs_wl_abs( $rs_wl_c['img'] ),
			);
		}
	}
	$rs_wl_js[] = $rs_wl_row;
}
echo wp_json_encode( $rs_wl_js );
?>;
const T = <?php
echo wp_json_encode(
	array(
		'clip'  => $rs_wl_t( 'ক্লিপ', 'Clip' ),
		'clips' => $rs_wl_t( 'টি ক্লিপ', ' clips' ),
		'same'  => $rs_wl_t( 'একই দিনে', 'Same day' ),
		'year'  => $rs_wl_t( '২০২৬', '2026' ),
		'today' => $rs_wl_t( 'আজ', 'Today' ),
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
const shown = w => kind === 'all' || w.kind === kind;

function applyFilter() {
  document.querySelectorAll('.chip').forEach(c => c.setAttribute('aria-pressed', c.dataset.k === kind ? 'true' : 'false'));
  let side = 0;
  document.querySelectorAll('.tl-month').forEach(m => {
    let mn = 0;
    m.querySelectorAll('.tl-day').forEach(d => {
      let n = 0;
      d.querySelectorAll('[data-kind]').forEach(el => {
        const on = kind === 'all' || el.dataset.kind === kind;
        el.hidden = !on; if (on) n++;
      });
      d.hidden = n === 0; mn += n;
      const dc = d.querySelector('.dcount');
      if (dc) { dc.textContent = n > 1 ? '· ' + count(n) : ''; }
      if (d.classList.contains('single') && n) { const l = side++ % 2 === 0; d.classList.toggle('l', l); d.classList.toggle('r', !l); }
    });
    m.hidden = mn === 0;
    const mc = m.querySelector('.mcount'); if (mc) mc.textContent = count(mn);
  });
  if (edBuilt) drawEditor(false);
  onScroll();
}
document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => { kind = c.dataset.k; applyFilter(); }));

// ── the pop-up player
const modal = $('modal'), mFrame = $('mFrame');
const P = { list: [], i: 0, vert: store.get('worklog.vert') === '1', last: null, hash: '' };
function fitModal() {
  if (modal.hidden) return;
  const top = $('mBox').querySelector('.m-top').offsetHeight + 12, room = innerHeight - 32 - top - 34;
  const vert = P.vert && canVert(P.list[P.i]);
  let w = Math.min(1240, innerWidth - 32), h;
  if (vert) { h = Math.max(380, room); w = Math.min(w, Math.max(Math.min(640, w), h * 4 / 5)); }
  else { h = w * 9 / 16; if (h > room) { h = Math.max(260, room); w = Math.min(w, h * 16 / 9); } }
  $('mBox').style.maxWidth = Math.round(w) + 'px'; mFrame.style.height = Math.round(h) + 'px';
}
function loadModal() {
  const p = P.list[P.i], v = P.vert && canVert(p);
  $('mType').textContent = p.type; $('mTitle').textContent = p.title;
  $('mSeg').hidden = !canVert(p);
  document.querySelectorAll('[data-ar]').forEach(b => b.setAttribute('aria-pressed', (b.dataset.ar === '45') === v ? 'true' : 'false'));
  $('mOpen').href = srcOf(p, v);
  const multi = P.list.length > 1; $('mPrev').hidden = !multi; $('mNext').hidden = !multi;
  mFrame.src = srcOf(p, v);
  if (p.hash && location.hash !== p.hash) history.replaceState(null, '', p.hash);
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
const entry = w => ({ title: w.title, type: w.type, src: w.src, src45: w.src45, map: w.map, local: w.local, hash: '#' + w.id });
const clipEntry = (w, c, i) => ({ title: c.num + ' · ' + c.title, type: w.title, src: c.src, src45: '', map: w.map, local: true, hash: '#' + w.id + '/' + (i + 1) });
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

// cards, clips and buttons in the list
$('list').addEventListener('click', e => {
  const more = e.target.closest('[data-more]');
  if (more) {
    const ol = $('clips-' + more.dataset.more), open = more.getAttribute('aria-expanded') !== 'true';
    ol.querySelectorAll('li.more').forEach(li => { li.hidden = !open; });
    more.setAttribute('aria-expanded', open ? 'true' : 'false');
    more.querySelector('b').textContent = open ? '−' : '+' + bn(ol.querySelectorAll('li.more').length);
    return;
  }
  const all = e.target.closest('[data-playall]');
  if (all) { playClip(all.dataset.playall, 0); return; }
  const clip = e.target.closest('a.clip');
  if (clip) { e.preventDefault(); playClip(clip.dataset.id, +clip.dataset.clip); return; }
  const card = e.target.closest('a.card');
  if (card) { e.preventDefault(); playWork(card.dataset.id); }
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
    if (fine.matches) return;
    es.forEach(en => {
      if (en.isIntersecting) { clearTimeout(pvTimer); pvTimer = setTimeout(() => startPreview(en.target), 700); }
      else if (pvEl === en.target) stopPreview();
    });
  }, { rootMargin: '-42% 0px -42% 0px' });
  document.querySelectorAll('#list a.card').forEach(c => io.observe(c));
}

// ── the line fills in as the page scrolls, and the bar shows the date in the middle of the screen
const list = $('list'), fill = $('tlFill'), head = $('tlHead'), tc = $('tc'), tcText = $('tcText');
let ticking = false;
function onScroll() {
  if (ticking) return; ticking = true;
  requestAnimationFrame(() => {
    ticking = false;
    if (list.hidden) { tc.classList.remove('on'); return; }
    const rail = list.querySelector('.tl-rail').getBoundingClientRect(), mid = innerHeight * .5;
    const y = Math.max(0, Math.min(rail.height, mid - rail.top));
    fill.style.transform = 'scaleY(' + (rail.height ? y / rail.height : 0) + ')';
    head.style.transform = 'translateY(' + y + 'px)';
    const inside = rail.top < mid && rail.bottom > mid;
    head.classList.toggle('on', inside);
    let cur = null;
    list.querySelectorAll('.tl-day:not([hidden])').forEach(d => {
      const n = d.querySelector('.tl-node'); if (n.getBoundingClientRect().top <= mid) cur = d;
    });
    if (!cur) cur = list.querySelector('.tl-day:not([hidden])');
    if (cur) tcText.textContent = timecode(cur.dataset.date);
    tc.classList.toggle('on', rail.top < innerHeight && rail.bottom > 0);
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
const oldest = WORKS[WORKS.length - 1].date;
const d0 = toDay(oldest.slice(0, 8) + '01');
const dEnd = Math.max(localToday(), toDay(WORKS[0].date)) + 4;
const span = dEnd - d0;
const ymdOf = d => new Date(d * dayMs).toISOString().slice(0, 10);
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
  const px = ZOOM[zi], W = Math.ceil(span * px), sl = edScroll.scrollLeft;
  edInner.style.width = W + 'px';
  // ruler: the first of each month, and days when there is room
  let r = '';
  for (let d = d0; d <= dEnd; d++) {
    const s = ymdOf(d), dd = +s.slice(8, 10), x = (d - d0) * px + px / 2;
    if (dd === 1) r += '<b style="left:' + ((d - d0) * px) + 'px">' + MONTHS[+s.slice(5, 7)] + ' ' + yearOf(s) + '</b>';
    if (px >= 24 || dd % 7 === 1) {
      const on = WORKS.some(w => w.date === s);
      r += '<span class="' + (on ? 'w' : '') + '" style="left:' + x + 'px">' + bn(dd) + '</span>';
    }
  }
  $('edRuler').innerHTML = r;
  // tracks
  const g = groups(), tracks = $('edTracks');
  tracks.innerHTML = '';
  Object.keys(KINDS).forEach(k => {
    const tr = document.createElement('div'); tr.className = 'track';
    tr.setAttribute('role', 'group'); tr.setAttribute('aria-label', KINDS[k]);
    const keys = Object.keys(g).filter(key => key.startsWith(k + '|')).sort();
    keys.forEach((key, idx) => {
      const ws = g[key], w = ws[0], d = toDay(w.date) - d0;
      const next = keys[idx + 1] ? toDay(g[keys[idx + 1]][0].date) - d0 : d + 3;
      const width = Math.max(12, Math.min(next - d, 3) * px - 6);
      const b = document.createElement('button');
      b.type = 'button'; b.className = 'eclip' + (ws.length > 1 ? ' stack' : '') + (ws.some(shown) ? '' : ' dim');
      b.style.left = (d * px + 3) + 'px'; b.style.width = width + 'px';
      b.dataset.key = key;
      b.setAttribute('aria-label', w.day + ': ' + ws.map(x => x.title).join(', '));
      b.setAttribute('aria-pressed', sel && ws.includes(sel) ? 'true' : 'false');
      b.innerHTML = '<img src="' + w.img + '" alt="" loading="lazy">' +
        (ws.length > 1 ? '<span class="bdg">' + bn(ws.length) + '</span>' : (w.clips.length ? '<span class="bdg">+' + bn(w.clips.length) + '</span>' : ''));
      b.addEventListener('click', () => {
        const at = sel && ws.includes(sel) ? (ws.indexOf(sel) + 1) % ws.length : 0;   // a stacked clip steps through its day
        select(ws[at]);
      });
      tr.appendChild(b);
    });
    tracks.appendChild(tr);
  });
  // today
  const t = localToday() - d0;
  const td = document.createElement('div'); td.className = 'today'; td.style.left = (t * px + px / 2) + 'px';
  td.innerHTML = '<em>' + T.today + '</em>'; edInner.appendChild(td);
  edInner.querySelectorAll('.today').forEach((n, i, all) => { if (i < all.length - 1) n.remove(); });
  // the overview strip
  const mini = $('edMini');
  mini.querySelectorAll('i, em').forEach(n => n.remove());
  WORKS.forEach(w => { const i = document.createElement('i'); i.style.left = ((toDay(w.date) - d0) / span * 100) + '%'; if (!shown(w)) i.style.opacity = '.25'; mini.appendChild(i); });
  for (let d = d0; d <= dEnd; d++) { const s = ymdOf(d); if (s.endsWith('-01')) { const em = document.createElement('em'); em.style.left = ((d - d0) / span * 100) + '%'; em.textContent = MONTHS[+s.slice(5, 7)]; mini.appendChild(em); } }
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
  const px = ZOOM[zi], d = toDay(sel.date) - d0;
  const b = edInner.querySelector('.eclip[data-key="' + sel.kind + '|' + sel.date + '"]');
  $('edPh').style.left = (b ? b.offsetLeft + b.offsetWidth / 2 : d * px) + 'px';
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
  const mid = (edScroll.scrollLeft + edScroll.clientWidth / 2) / ZOOM[zi];
  zi = n; store.set('worklog.zoom', String(zi));
  drawEditor(false);
  edScroll.scrollLeft = mid * ZOOM[zi] - edScroll.clientWidth / 2; syncMini();
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
<?php if ( function_exists( 'rs_stats_beacon' ) ) { rs_stats_beacon(); } ?>
</body>
</html>
