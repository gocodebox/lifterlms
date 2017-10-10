/* global LLMS, $ */

/**
 * LifterLMS Loops JS
 * @since    3.0.0
 * @version  [versino]
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
	 * @version  3.14.0
	 */
	match_height: function() {

		$( '.llms-loop-item .llms-loop-item-content' ).matchHeight();
		$( '.llms-achievement-loop-item .llms-achievement' ).matchHeight();
		$( '.llms-certificate-loop-item .llms-certificate' ).matchHeight();

	},

};
