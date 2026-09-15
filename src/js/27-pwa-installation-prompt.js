	/* ---------------------------------------------------------------
	 * PWA installation prompt
	 * ------------------------------------------------------------ */

	( function () {
		var deferredPrompt = null;
		var installBtn = $( '#rs-install-btn' );
		var installBar = $( '#rs-install-bar' );
		var installBarBtn = $( '#rs-install-bar-btn' );
		var installBarClose = $( '#rs-install-bar-close' );
		var DISMISSED_KEY = 'rs-install-dismissed';
		var SNOOZE_MS = 3 * 24 * 60 * 60 * 1000; // 3 days

		function isDismissed() {
			var raw = store( DISMISSED_KEY );

			if ( ! raw ) {
				return false;
			}

			var time = parseInt( raw, 10 );

			if ( ! time || isNaN( time ) ) {
				store( DISMISSED_KEY, String( Date.now() ) );
				return true;
			}

			return ( Date.now() - time ) < SNOOZE_MS;
		}

		var isStandalone = window.matchMedia( '(display-mode: standalone)' ).matches ||
			window.navigator.standalone ||
			document.referrer.indexOf( 'android-app://' ) > -1;

		if ( isStandalone ) {
			return;
		}

		var isIos = /iphone|ipad|ipod/i.test( window.navigator.userAgent ) &&
			! window.MSStream &&
			/safari/i.test( window.navigator.userAgent ) &&
			! /crios|fxios/i.test( window.navigator.userAgent );

		function triggerInstall() {
			if ( deferredPrompt ) {
				deferredPrompt.prompt();
				deferredPrompt.userChoice.then( function ( choiceResult ) {
					if ( choiceResult && choiceResult.outcome === 'accepted' ) {
						hideAll();
						toast( ( strings && strings.installing ) || 'অ্যাপ ইনস্টল হচ্ছে...' );
					}
					deferredPrompt = null;
				} );
			} else if ( isIos ) {
				toast( ( strings && strings.iosInstall ) || 'সাফারির নিচে শেয়ার আইকনে ট্যাপ করে "Add to Home Screen" বেছে নিন' );
			}
		}

		function showPrompt() {
			if ( installBtn ) {
				installBtn.hidden = false;
			}

			if ( ! isDismissed() && installBar ) {
				installBar.hidden = false;
				installBar.style.display = '';
				setTimeout( function () {
					installBar.classList.add( 'is-visible' );
				}, 1500 );
			}
		}

		function hideAll() {
			if ( installBtn ) {
				installBtn.hidden = true;
			}

			if ( installBar ) {
				installBar.classList.remove( 'is-visible' );
				installBar.hidden = true;
				installBar.style.display = 'none';
			}
		}

		window.addEventListener( 'beforeinstallprompt', function ( e ) {
			e.preventDefault();
			deferredPrompt = e;
			showPrompt();
		} );

		window.addEventListener( 'appinstalled', function () {
			hideAll();
			toast( ( strings && strings.installed ) || 'অ্যাপ সফলভাবে ইনস্টল হয়েছে!' );
			deferredPrompt = null;
		} );

		/* Delegated click handler on document — handles close and install clicks reliably */
		document.addEventListener( 'click', function ( e ) {
			if ( ! e.target || ! e.target.closest ) {
				return;
			}

			var close = e.target.closest( '#rs-install-bar-close' );

			if ( close ) {
				e.preventDefault();
				e.stopPropagation();
				store( DISMISSED_KEY, String( Date.now() ) );
				if ( installBar ) {
					installBar.classList.remove( 'is-visible' );
					installBar.hidden = true;
					installBar.style.display = 'none';
				}
				return;
			}

			var barClick = e.target.closest( '#rs-install-bar-btn' );

			if ( barClick ) {
				e.preventDefault();
				e.stopPropagation();
				triggerInstall();
				return;
			}

			var floatBtn = e.target.closest( '#rs-install-btn' );

			if ( floatBtn ) {
				e.preventDefault();
				e.stopPropagation();
				triggerInstall();
				return;
			}
		} );

		/* For iOS where beforeinstallprompt doesn't fire */
		if ( isIos && ! isDismissed() ) {
			showPrompt();
		}
	}() );

	/* A post opened at its own address, rather than in the modal. Zero on
	   every other kind of page, and countView() ignores zero. */
	var mainPid = parseInt( cfg.postId, 10 );
	if ( mainPid ) {
		countView( mainPid );
	}

	markRead();
	staggerRows();
