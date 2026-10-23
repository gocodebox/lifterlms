/**
 * Student dashboard mobile navigation.
 *
 * @package LifterLMS/Scripts
 *
 * @since [version]
 * @version [version]
 */

/**
 * Same-origin http(s) URL from a dashboard select option.
 *
 * @since [version]
 *
 * @param {string} url Candidate URL.
 * @return {string} Safe URL, or an empty string.
 */
function llmsSafeSameOriginUrl( url ) {
	var parsed;

	if ( 'string' !== typeof url || '' === url ) {
		return '';
	}

	try {
		parsed = new URL( url, window.location.href );
	} catch ( error ) {
		return '';
	}

	if ( 'http:' !== parsed.protocol && 'https:' !== parsed.protocol ) {
		return '';
	}

	if ( parsed.origin !== window.location.origin ) {
		return '';
	}

	return parsed.href;
}

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
			var url = llmsSafeSameOriginUrl( $( this ).find( 'select' ).val() );
			e.preventDefault();
			if ( ! url ) {
				return;
			}
			window.location.assign( url );
		} );

	}

};
