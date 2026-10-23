/**
 * LifterLMS Focus Mode sidebar toggle.
 *
 * @package LifterLMS
 *
 * @since 10.0.0
 * @since [version] Keep aria-expanded in sync with the collapsed sidebar.
 * @version [version]
 */
( function() {
	'use strict';

	var STORAGE_KEY = 'llms_focus_sidebar_collapsed';

	function init() {
		var toggle = document.querySelector( '.llms-focus-mode-sidebar-toggle' );
		if ( ! toggle ) {
			return;
		}

		var body = document.body;

		function setExpanded() {
			var collapsed = body.classList.contains( 'llms-sidebar-collapsed' );
			toggle.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
		}

		if ( localStorage.getItem( STORAGE_KEY ) === '1' ) {
			body.classList.add( 'llms-sidebar-collapsed' );
		}
		setExpanded();

		toggle.addEventListener( 'click', function() {
			var collapsed = body.classList.toggle( 'llms-sidebar-collapsed' );
			localStorage.setItem( STORAGE_KEY, collapsed ? '1' : '0' );
			setExpanded();
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
