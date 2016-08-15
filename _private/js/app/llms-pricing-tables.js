/* global LLMS, $ */
/* jshint strict: false */
/**
 * Pricing Table UI
 */
LLMS.Pricing_Tables = {

	/**
	 * init
	 */
	init: function() {

		var self = this;

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			return;
		}

		if ( $( '.llms-access-plans' ).length ) {

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

		$( '.llms-access-plan-content' ).matchHeight();
		$( '.llms-access-plan-pricing.trial' ).matchHeight();

	}
};
