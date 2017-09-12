/* global LLMS, $ */
/* jshint strict: false */
/**
 * Instructors List
 */
LLMS.Instructors = {

	/**
	 * init
	 */
	init: function() {

		var self = this;

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			return;
		}

		if ( $( '.llms-instructors' ).length ) {

			LLMS.wait_for_matchHeight( function() {
				self.bind();
			} );

		}

	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 * @return {[type]} [description]
	 */
	bind: function() {

		$( '.llms-instructors .llms-author' ).matchHeight();

	},

};
