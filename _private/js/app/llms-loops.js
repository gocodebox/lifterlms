/* global LLMS, $ */

/**
 * Handle Lesson Preview Elements
 */
LLMS.Loops = {

	/**
	 * Initilize
	 * @return void
	 */
	init: function() {

		var self = this;

		if ( $( '.llms-loop' ).length ) {

			LLMS.wait_for_matchHeight( function() {

				self.match_height();

			} );

		}

	},

	/**
	 * Match the height of .llms-loop-item
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	match_height: function() {

		$( '.llms-loop-item .llms-loop-item-content' ).matchHeight();

	},

};
