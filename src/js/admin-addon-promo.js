/**
 * Admin add-on promo dialogs (lock-with-hint buttons).
 *
 * @package LifterLMS/Scripts/Admin
 *
 * @since [version]
 */

( function() {
	function ready( fn ) {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	ready( function() {
		const dialog = document.getElementById( 'llms-addon-promo-dialog-pdfs' );
		if ( ! dialog ) {
			return;
		}

		document.querySelectorAll( '.llms-addon-promo-trigger' ).forEach( function( button ) {
			button.addEventListener( 'click', function( event ) {
				event.preventDefault();
				if ( typeof dialog.showModal === 'function' ) {
					dialog.showModal();
				}
			} );
		} );

		const closeBtn = dialog.querySelector( '.llms-addon-promo-dialog-close' );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function() {
				dialog.close();
			} );
		}

		dialog.addEventListener( 'click', function( event ) {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );
	} );
}() );
