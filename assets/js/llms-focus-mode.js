/**
 * LifterLMS Focus Mode sidebar toggle.
 *
 * @package LifterLMS
 *
 * @since [version]
 * @version [version]
 */
( function() {
	'use strict';

	var STORAGE_KEY = 'llms_focus_sidebar_collapsed';

	var chevronLeft = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>';
	var chevronRight = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>';

	function init() {
		var sidebar = document.querySelector( '.llms-focus-mode-sidebar' );
		if ( ! sidebar ) {
			return;
		}

		var body    = document.body;
		var isRight = body.classList.contains( 'llms-focus-mode-sidebar-right' );

		var toggle = document.createElement( 'button' );
		toggle.className = 'llms-focus-mode-sidebar-toggle';
		toggle.type = 'button';
		toggle.setAttribute( 'aria-label', 'Toggle sidebar' );
		sidebar.appendChild( toggle );

		var collapsed = localStorage.getItem( STORAGE_KEY ) === '1';
		if ( collapsed ) {
			body.classList.add( 'llms-sidebar-collapsed' );
		}
		setIcon( toggle, collapsed, isRight );

		toggle.addEventListener( 'click', function() {
			collapsed = body.classList.toggle( 'llms-sidebar-collapsed' );
			localStorage.setItem( STORAGE_KEY, collapsed ? '1' : '0' );
			setIcon( toggle, collapsed, isRight );
		} );
	}

	function setIcon( el, collapsed, isRight ) {
		// Sidebar left: expanded shows left-arrow (collapse), collapsed shows right-arrow (expand).
		// Sidebar right: expanded shows right-arrow (collapse), collapsed shows left-arrow (expand).
		if ( isRight ) {
			el.innerHTML = collapsed ? chevronLeft : chevronRight;
		} else {
			el.innerHTML = collapsed ? chevronRight : chevronLeft;
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
