/* global LLMS, $ */

LLMS.Spinner = {

	// default is 40px spinner (pass null for size)
	// small is 20px spinner (pass "small")
	get: function( $el, size ) {

		// look for an existing spinner
		var $spinner = $el.find( '.llms-spinning' ).first();

		// no spinner inside $el
		if ( !$spinner.length ) {

			size = ' ' + size || '';

			// create the spinner
			$spinner = $( '<div class="llms-spinning"><i class="llms-spinner' + size + '"></i></div>' );

			// add it to the dom
			$el.append( $spinner );

		}

		// return it
		return $spinner;

	},

	start: function( $el, size ) {

		this.get( $el, size ).show();

	},

	stop: function( $el ) {

		this.get( $el ).hide();

	}

};
