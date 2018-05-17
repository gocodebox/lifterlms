/* global LLMS, $ */

/**
 * Add Spinners for AJAX events
 * @since 3.0.0
 * @version 3.0.0
 */
LLMS.Spinner = {

	/**
	 * Get an exiting spinner element or create a new one
	 * @param    obj      $el   jQuery selector of the parent element that should hold and be mased by a spinnner
	 * @param    string   size  size or the spinner [default|small]
	 *                          default is 40px
	 *                          small is 20px
	 * @return   obj
	 * @since 3.0.0
	 * @version 3.0.0
	 */
	get: function( $el, size ) {

		// look for an existing spinner
		var $spinner = $el.find( '.llms-spinning' ).first();

		// no spinner inside $el
		if ( !$spinner.length ) {

			size = ( size ) ? size : 'default';

			// create the spinner
			$spinner = $( '<div class="llms-spinning"><i class="llms-spinner ' + size + '"></i></div>' );

			// add it to the dom
			$el.append( $spinner );

		}

		// return it
		return $spinner;

	},

	/**
	 * Start spinner(s) inr=side a given element
	 * Creates them if they don't exist!
	 * @param   obj      $el   jQuery selector of the parent element that should hold and be mased by a spinnner
	 * @param   string   size  size or the spinner [default|small]
	 *                          default is 40px
	 *                          small is 20px
	 * @return  void
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	start: function( $el, size ) {

		var self = this;

		$el.each( function() {

			self.get( $( this ), size ).show();

		} );

	},

	/**
	 * Stor spinners within an element
	 * @param   obj      $el   jQuery selector of the parent element that should hold and be mased by a spinnner
	 * @return  void
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	stop: function( $el ) {

		var self = this;

		$el.each( function() {

			self.get( $( this ) ).hide();

		} );

	}

};
