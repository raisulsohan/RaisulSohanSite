/**
 * Raisul Sohan theme.
 *
 * Everything the front page needs: the typing heading, reader font size,
 * hover summaries, the post modal (with real URLs behind it), search,
 * lazy list batches and toasts.
 */

	'use strict';

	var cfg = window.RS || {};
	var strings = cfg.strings || {};
	var isEn = Boolean( cfg.isEn );
	var rest = cfg.rest || '/wp-json/rs/v1/';

	/* Five steps, two either side of the default. FONT_DEFAULT must stay
	   the middle one: the "A" button resets to it, and its value has to
	   match --rs-post-size in style.css, which is what renders before
	   this script runs. */
	var SIZES = [ 16, 17.5, 19, 21, 23 ];
	var FONT_DEFAULT = 2;
	var TOOLTIP_DELAY = 600;

	/* Hero reveal. Durations must stay in sync with .rs-word in style.css. */
	var HERO_IN_DUR = 900;
	var HERO_IN_STEP = 110;
	var HERO_OUT_DUR = 520;
	var HERO_OUT_STEP = 55;
	var HERO_HOLD = 2600;

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
