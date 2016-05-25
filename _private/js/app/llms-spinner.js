/* global LLMS, $ */

/**
 * Main Ajax class
 * Handles Primary Ajax connection
 * @type {Object}
 */
LLMS.Spinner = {

	get: function( $el ) {

		// look for an existing spinner
		var $spinner = $el.find( '.llms-spinning' ).first();

		// no spinner inside $el
		if ( !$spinner.length ) {

			// create the spinner
			$spinner = $( '<div class="llms-spinning"><i class="llms-spinner"></i></div>' );

			// add it to the dom
			$el.append( $spinner );

		}

		// return it
		return $spinner;

	},

	start: function( $el ) {

		this.get( $el ).show();

	},

	stop: function( $el ) {

		this.get( $el ).hide();

	}

};
