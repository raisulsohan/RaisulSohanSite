	/* ---------------------------------------------------------------
	 * Post modal
	 * ------------------------------------------------------------ */

	var postOverlay = $( '#rs-post-overlay' );
	var postBody = $( '#rs-post-body' );
	var baseTitle = document.title;
	var cache = {};

	/* Which page of the list is on screen, and the loader that can fetch a
	   different one. Both belong to the pagination module near the bottom
	   of this file; the popstate handler below is the other user. */
	var listUrl = absUrl( window.location.href );
	var loadListPage = null;

	/*
	 * Set by the editor near the bottom of this file, and consulted by
	 * everything that could carry the reader away from an edit in
	 * progress: the modal's close, and the two swipes that jump to the
	 * next post. All of them would throw the words away without asking.
	 */
	var isEditing = function () {
		return false;
	};

	var editorMayClose = function () {
		return true;
	};

	function fontControlsHtml() {
		var fontLabel = ( strings && strings.fontSize ) || 'লেখার আকার';
		var fontDown  = ( strings && strings.fontDown ) || 'ছোট করুন';
		var fontReset = ( strings && strings.fontReset ) || 'স্বাভাবিক আকার';
		var fontUp    = ( strings && strings.fontUp ) || 'বড় করুন';

		return (
			'<div class="rs-fontctl" role="group" aria-label="' + escapeHtml( fontLabel ) + '">' +
			'<button type="button" data-rs-font="down" data-step="0" aria-label="' + escapeHtml( fontDown ) + '">A-</button>' +
			'<span class="rs-fontctl__sep"></span>' +
			'<button type="button" data-rs-font="reset" data-step="1" aria-label="' + escapeHtml( fontReset ) + '">A</button>' +
			'<span class="rs-fontctl__sep"></span>' +
			'<button type="button" data-rs-font="up" data-step="2" aria-label="' + escapeHtml( fontUp ) + '">A+</button>' +
			'</div>'
		);
	}

	function nextPrevHtml( data ) {
		if ( ! data.prev && ! data.next ) {
			return '';
		}

		var html = '<nav class="rs-nextprev">';

		/* data-rs-post lets the existing list click handler catch these,
		   so the next post opens in this modal instead of reloading. */
		if ( data.prev ) {
			html += '<a href="' + escapeHtml( data.prev.link ) + '" data-rs-post="' +
				data.prev.id + '">← ' + escapeHtml( data.prev.title ) + '</a>';
		}

		if ( data.next ) {
			html += '<a class="rs-nextprev__next" href="' + escapeHtml( data.next.link ) +
				'" data-rs-post="' + data.next.id + '">' + escapeHtml( data.next.title ) + ' →</a>';
		}

		return html + '</nav>';
	}

	/* The same pair rs_edit_links() prints on a post's own page. */
	function editLinkHtml() {
		var icon = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
		var editLabel = ( strings && strings.edit ) || 'সম্পাদনা';
		var dashLabel = ( strings && strings.dashboard ) || 'ড্যাশবোর্ডে';

		return (
			'<button class="rs-article__edit" type="button" data-rs-edit>' + icon + escapeHtml( editLabel ) + '</button>' +
			'<a class="rs-article__edit rs-article__edit--dash" href="">' + escapeHtml( dashLabel ) + '</a>'
		);
	}

	/* Kept in step with rs_share_row() in functions.php, which draws the
	   same row on a post's own page. */
	function shareHtml( data ) {
		var link = data.link;
		var title = data.title;
		var svg = 'width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
		var cpIcon = '<svg ' + svg + '><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
		var shIcon = '<svg ' + svg + '><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/></svg>';
		var bmIcon = '<svg ' + svg + '><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>';

		var shareLabel = ( strings && strings.shareLabel ) || 'অন্যদেরও পড়তে দিন';
		var shareBtn   = ( strings && strings.shareBtn ) || 'শেয়ার করুন';
		var copyBtn    = ( strings && strings.copyBtn ) || 'লিঙ্ক কপি';
		var readLater  = ( strings && strings.readLater ) || 'পরে পড়ব';

		return (
			'<div class="rs-share">' +
			'<p class="rs-share__label">' + escapeHtml( shareLabel ) + '</p>' +
			'<div class="rs-share__row">' +
			'<button class="rs-share__btn" type="button" data-rs-share="' + escapeHtml( link ) +
			'" data-rs-share-title="' + escapeHtml( title || '' ) + '">' +
			shIcon + escapeHtml( shareBtn ) + '</button>' +
			'<button class="rs-share__btn" type="button" data-rs-copy="' + escapeHtml( link ) + '">' +
			cpIcon + escapeHtml( copyBtn ) + '</button>' +
			/* The modal was built from a fetch, so this button has no row to
			   read itself out of and carries what it needs instead. */
			'<button class="rs-share__btn rs-share__btn--save" type="button" data-rs-later="' +
			escapeHtml( data.id ) + '" data-rs-later-url="' + escapeHtml( link ) +
			'" data-rs-later-title="' + escapeHtml( title || '' ) +
			'" data-rs-later-time="' + escapeHtml( data.readingTime || '' ) + '">' +
			bmIcon + '<span data-rs-later-text>' + escapeHtml( readLater ) + '</span></button>' +
			'</div></div>'
		);
	}

	function relatedHtml( data ) {
		if ( ! data.related || ! data.related.length ) {
			return '';
		}

		var relLabel = ( window.RS && window.RS.isEn )
			? ( 'More in ' + ( data.category || 'writings' ) )
			: ( 'আরও ' + ( data.category || 'লেখা' ) );

		var html = '<nav class="rs-related"><p class="rs-related__label">' +
			escapeHtml( relLabel ) +
			'</p><ul class="rs-related__list">';

		/* data-rs-post again, so these open in this modal rather than
		   reloading the page under it. */
		data.related.forEach( function ( item ) {
			html += '<li><a href="' + escapeHtml( item.link ) +
				'" data-rs-post="' + item.id + '">' +
				'<span class="rs-related__title">' + escapeHtml( item.title ) + '</span>' +
				'<span class="rs-related__meta">' + escapeHtml( item.readingTime ) + '</span>' +
				'</a></li>';
		} );

		return html + '</ul></nav>';
	}

	function renderPost( data ) {
		postBody.innerHTML =
			'<div class="rs-article__meta">' +
			'<p class="rs-article__date"></p>' +
			( data.readingTime ? '<span class="rs-article__read"></span>' : '' ) +
			( cfg.editBase ? editLinkHtml() : '' ) +
			fontControlsHtml() +
			'</div>' +
			'<div class="rs-article__head">' +
			'<h2 class="rs-article__title" id="rs-post-title"></h2>' +
			( data.category ? '<a class="rs-article__cat"></a>' : '' ) +
			'</div>' +
			'<p class="rs-article__author"></p>' +
			'<div class="rs-article__body"></div>' +
			shareHtml( data ) +
			relatedHtml( data ) +
			nextPrevHtml( data );

		$( '.rs-article__date', postBody ).textContent = data.date;

		if ( data.readingTime ) {
			$( '.rs-article__read', postBody ).textContent = data.readingTime;
		}
		$( '.rs-article__title', postBody ).textContent = data.title;
		$( '.rs-article__author', postBody ).textContent = data.author;

		/* Which post the editor is about to save, found the same way on a
		   post's own page. */
		postBody.setAttribute( 'data-rs-id', data.id );

		if ( cfg.editBase ) {
			/* href as a property, so the id never passes through innerHTML. */
			$( '.rs-article__edit--dash', postBody ).href = cfg.editBase + data.id;
		}

		if ( data.category ) {
			var catEl = $( '.rs-article__cat', postBody );

			/* Set as properties rather than markup: the values never touch
			   innerHTML, so there is nothing to escape. */
			catEl.textContent = data.category;

			if ( data.categoryLink ) {
				catEl.href = data.categoryLink;
			}
		}

		/* Content is this site's own published HTML, already run through
		   the_content filters on the server. */
		$( '.rs-article__body', postBody ).innerHTML = data.content;

		applyFont();

		/* The share row this just built carries a save button, and it has
		   to be told whether the story is already on the shelf. */
		markLater();

		var resume = positionEntry( data.id );

		restoreScroll( postBody.parentNode, resume ? resume.t : 0 );
	}

	/*
	 * How many post entries sit between the list and what is on screen.
	 * Reading on from one post to the next pushes an entry each time, so
	 * closing has to jump back over all of them at once; a single back()
	 * would land on the previous post and reopen it. The count also rides
	 * along in each history state, so the browser's own back and forward
	 * buttons keep it correct.
	 */
	var postDepth = 0;

	/* Which post the modal is showing, so the scroll listener further down
	   knows what it is saving a position for. */
	var currentPostId = null;
