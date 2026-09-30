/**
 * Student dashboard mobile navigation.
 *
 * @package LifterLMS/Scripts
 *
 * @since [version]
 * @version [version]
 */

LLMS.DashboardNav = {

	/**
	 * Bind the mobile dashboard navigation form.
	 *
	 * Changing the select does not navigate. Submitting the form does.
	 *
	 * @since [version]
	 *
	 * @return {void}
	 */
	init: function() {

		$( '.llms-sd-mobile-nav' ).on( 'submit', function( e ) {
			var url = $( this ).find( 'select' ).val();
			if ( ! url ) {
				return;
			}
			e.preventDefault();
			window.location.href = url;
		} );

	}

};
