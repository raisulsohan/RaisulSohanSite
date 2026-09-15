<?php
/**
 * Icons.
 *
 * Split out of functions.php; functions.php loads every inc/NN-*.php
 * file in numeric order, which is the order the code always ran in.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * 6. Icons
 * ====================================================================== */

/**
 * Inline SVG icon.
 *
 * @param string $name Icon name.
 * @param int    $size Pixel size.
 * @return string
 */
function rs_icon( $name, $size = 15 ) {
	$stroke = array(
		'mail'     => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'linkedin' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
		'search'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'close'    => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'up'       => '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
		'copy'     => '<rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
		/* Lines drawn as paths: rs_svg_tags() allows path and circle, and
		   two more tags for one icon is not worth widening it. */
		'share'    => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/>',
		'left'     => '<path d="m15 18-6-6 6-6"/>',
		'right'    => '<path d="m9 18 6-6-6-6"/>',
		'undo'     => '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>',
		'edit'     => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
		/* One outline for both states. Saved fills it in from CSS, which is
		   a colour change rather than a different shape — so the mark does
		   not appear to move when the reader taps it. */
		'bookmark' => '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
		'sparkles' => '<path d=\"m12 3-1.9 5.8a2 2 0 0 1-1.2 1.2L3 12l5.8 1.9a2 2 0 0 1 1.2 1.2L12 21l1.9-5.8a2 2 0 0 1 1.2-1.2L21 12l-5.8-1.9a2 2 0 0 1-1.2-1.2Z\"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
		'moon'     => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
		'install'  => '<rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/><path d="m9 10 3 3 3-3"/><path d="M12 6v7"/>',
	);

	$fill = array(
		'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
	);

	if ( isset( $stroke[ $name ] ) ) {
		return sprintf(
			'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
			(int) $size,
			$stroke[ $name ]
		);
	}

	if ( isset( $fill[ $name ] ) ) {
		return sprintf(
			'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%2$s</svg>',
			(int) $size,
			$fill[ $name ]
		);
	}

	return '';
}

/**
 * Allow the inline SVG markup through wp_kses.
 *
 * @return array
 */
function rs_svg_tags() {
	$attrs = array(
		'width'            => true,
		'height'           => true,
		'viewbox'          => true,
		'fill'             => true,
		'stroke'           => true,
		'stroke-width'     => true,
		'stroke-linecap'   => true,
		'stroke-linejoin'  => true,
		'aria-hidden'      => true,
		'focusable'        => true,
		'class'            => true,
	);

	return array(
		'svg'    => $attrs,
		'path'   => array_merge( $attrs, array( 'd' => true ) ),
		'rect'   => array_merge( $attrs, array( 'x' => true, 'y' => true, 'rx' => true ) ),
		'circle' => array_merge( $attrs, array( 'cx' => true, 'cy' => true, 'r' => true ) ),
	);
}
