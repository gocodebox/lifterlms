/**
 * LifterLMS Focus Mode sidebar toggle.
 *
 * @package LifterLMS
 *
 * @since 10.0.0
 * @since [version] Added accessible mobile lesson navigation.
 * @version [version]
 */
( function() {
	'use strict';

	var STORAGE_KEY = 'llms_focus_sidebar_collapsed';
	var MOBILE_QUERY = '(max-width: 768px)';

	function init() {
		var toggle = document.querySelector( '.llms-focus-mode-sidebar-toggle' );
		var mobileToggle = document.querySelector( '.llms-focus-mode-mobile-sidebar-toggle' );
		var backdrop = document.querySelector( '.llms-focus-mode-sidebar-backdrop' );
		var sidebar = document.querySelector( '.llms-focus-mode-sidebar' );
		if ( ! toggle || ! mobileToggle || ! backdrop || ! sidebar ) {
			return;
		}

		var body = document.body;
		var media = window.matchMedia( MOBILE_QUERY );

		function updateDesktopToggle() {
			toggle.setAttribute( 'aria-expanded', body.classList.contains( 'llms-sidebar-collapsed' ) ? 'false' : 'true' );
		}

		function closeMobileSidebar( returnFocus ) {
			body.classList.remove( 'llms-mobile-sidebar-open' );
			mobileToggle.setAttribute( 'aria-expanded', 'false' );
			if ( media.matches ) {
				sidebar.setAttribute( 'aria-hidden', 'true' );
				sidebar.setAttribute( 'inert', '' );
			}
			if ( returnFocus ) {
				mobileToggle.focus();
			}
		}

		function openMobileSidebar() {
			body.classList.add( 'llms-mobile-sidebar-open' );
			mobileToggle.setAttribute( 'aria-expanded', 'true' );
			sidebar.removeAttribute( 'aria-hidden' );
			sidebar.removeAttribute( 'inert' );
			sidebar.focus();
		}

		if ( ! media.matches && localStorage.getItem( STORAGE_KEY ) === '1' ) {
			body.classList.add( 'llms-sidebar-collapsed' );
		}
		if ( media.matches ) {
			sidebar.setAttribute( 'aria-hidden', 'true' );
			sidebar.setAttribute( 'inert', '' );
		}
		updateDesktopToggle();

		toggle.addEventListener( 'click', function() {
			var collapsed = body.classList.toggle( 'llms-sidebar-collapsed' );
			localStorage.setItem( STORAGE_KEY, collapsed ? '1' : '0' );
			updateDesktopToggle();
		} );

		mobileToggle.addEventListener( 'click', function() {
			if ( body.classList.contains( 'llms-mobile-sidebar-open' ) ) {
				closeMobileSidebar( true );
			} else {
				openMobileSidebar();
			}
		} );

		backdrop.addEventListener( 'click', function() {
			closeMobileSidebar( true );
		} );

		document.addEventListener( 'keydown', function( event ) {
			if ( ! body.classList.contains( 'llms-mobile-sidebar-open' ) ) {
				return;
			}

			if ( 'Escape' === event.key ) {
				closeMobileSidebar( true );
			} else if ( 'Tab' === event.key ) {
				var focusable = sidebar.querySelectorAll( 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])' );
				var first = focusable[ 0 ];
				var last = focusable[ focusable.length - 1 ];

				if ( ! first ) {
					event.preventDefault();
					sidebar.focus();
				} else if ( event.shiftKey && ( document.activeElement === first || document.activeElement === sidebar ) ) {
					event.preventDefault();
					last.focus();
				} else if ( ! event.shiftKey && document.activeElement === last ) {
					event.preventDefault();
					first.focus();
				}
			}
		} );

		function handleViewportChange( event ) {
			closeMobileSidebar( false );
			if ( event.matches ) {
				sidebar.setAttribute( 'aria-hidden', 'true' );
				sidebar.setAttribute( 'inert', '' );
			} else {
				sidebar.removeAttribute( 'aria-hidden' );
				sidebar.removeAttribute( 'inert' );
				if ( localStorage.getItem( STORAGE_KEY ) === '1' ) {
					body.classList.add( 'llms-sidebar-collapsed' );
				}
			}
			updateDesktopToggle();
		}

		if ( media.addEventListener ) {
			media.addEventListener( 'change', handleViewportChange );
		} else {
			media.addListener( handleViewportChange );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
