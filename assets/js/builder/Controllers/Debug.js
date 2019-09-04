/**
 * LifterLMS Builder Debugging suite
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return function( settings ) {

		var self    = this,
			enabled = settings.enabled || false;

		/**
		 * Disable debugging
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.disable = function() {

			self.log( 'LifterLMS Builder debugging disabled' );
			enabled = false;

		};

		/**
		 * Enable debugging
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.enable = function() {

			enabled = true;
			self.log( 'LifterLMS Builder debugging enabled' );

		};

		/**
		 * General logging function
		 * Logs to the js console only if logging is enabled
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.log = function() {

			if ( ! enabled ) {
				return;
			}

			_.each( arguments, function( data ) {
				console.log( data );
			} );

		};

		/**
		 * Toggles current state of the logger on or off
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.toggle = function() {

			if ( enabled ) {
				self.disable();
			} else {
				self.enable();
			}

		};

		// on startup, log a message if logging is enabled
		if ( enabled ) {
			self.enable();
		}

	}

} );
