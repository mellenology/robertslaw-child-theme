/**
 * Quick exit for sensitive pages.
 *
 * Loaded only on pages flagged 'sensitive' in config/pages.php.
 *
 * What this can and cannot do, plainly: it navigates away and tries to replace
 * this page in the session history so the Back button does not return to it. It
 * CANNOT erase the browser's history, cached pages, or a network-level log. The
 * on-page copy says so — do not change it to promise more than this delivers.
 *
 * @package RobertsLaw
 */

( function () {
	'use strict';

	var SAFE_URL = 'https://www.weather.com/';
	var DECOY_URL = 'https://www.google.com/';

	/**
	 * Leave the page.
	 *
	 * Opens a neutral page in a new tab first so the visible foreground tab
	 * changes immediately, then replaces the current entry in this tab so the
	 * Back button does not land back here.
	 */
	function quickExit() {
		try {
			window.open( DECOY_URL, '_blank' );
		} catch ( e ) {
			// Popup blocked. The replace below still gets them off the page,
			// which is the part that matters.
		}

		try {
			// replace(), not assign() — assign() would leave this page in the
			// history stack, which is the thing we are trying to avoid.
			window.location.replace( SAFE_URL );
		} catch ( e ) {
			window.location.href = SAFE_URL;
		}
	}

	document.addEventListener( 'click', function ( event ) {
		var trigger = event.target.closest( '[data-rl-quick-exit]' );

		if ( ! trigger ) {
			return;
		}

		event.preventDefault();
		quickExit();
	} );

	/*
	 * Three Escape presses within two seconds. Three rather than one so it does
	 * not fire while someone is dismissing a menu, and no modifier key because
	 * a chord is harder to hit under stress.
	 */
	var presses = [];

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}

		var now = Date.now();

		presses.push( now );
		presses = presses.filter( function ( time ) {
			return now - time < 2000;
		} );

		if ( presses.length >= 3 ) {
			presses = [];
			quickExit();
		}
	} );

	/*
	 * Replace this page's history entry with the safe URL as soon as the page
	 * loads. The visitor stays here, but the Back button from here goes to the
	 * neutral page rather than showing this one again.
	 */
	if ( window.history && window.history.replaceState ) {
		try {
			window.history.replaceState( null, '', window.location.href );
			window.history.pushState( null, '', window.location.href );

			window.addEventListener( 'popstate', function () {
				window.location.replace( SAFE_URL );
			} );
		} catch ( e ) {
			// Non-fatal: the button and the Escape shortcut both still work.
		}
	}
}() );
