( function( $ ) {

	window.llms = window.llms || {};

	/**
	 * Manage
	 *
	 * @return obj    instance of the class
	 * @since 2.6.0
	 */
	window.llms.widget_syllabus = function() {

		/**
		 * Init
		 *
		 * @return void
		 * @since 2.6.0
		 */
		this.init = function() {

			this.bind();

		};

		/**
		 * Bind DOM events
		 *
		 * @return void
		 * @since 2.6.0
		 */
		this.bind = function() {

			var self = this;

			// bind all existing toggles on load
			self.bind_toggles( $( '#widgets-right .llms-course-outline-collapse' ) );

			$( document ).on( 'ajaxStop', function( r ) {

				// self.toggle( $( this ) );
				$( '#widgets-right .llms-course-outline-collapse:not([data-is-bound="true"])' ).each( function() {

					self.bind_toggles( $( this ) );

				} );

			} );

		};

		/**
		 * Bind change events to a specific toggle or set of toggles
		 *
		 * @param  obj      $toggles   jQuery selector of toggle input ('input.llms-course-outline-collapse')
		 * @return void
		 * @since 2.6.0
		 */
		this.bind_toggles = function( $toggles ) {

			var self = this;

			$toggles.attr( 'data-is-bound', 'true' );

			// bind input change on load
			$toggles.on( 'change', function() {

				self.toggle( $( this ) );

			} );

		};

		/**
		 * Toggle the visibility of the secondary option to display toggles
		 *
		 * @param  obj      $input   jQuery selector of a single collapse toggle element ('input.llms-course-outline-collapse')
		 * @return void
		 * @since 2.6.0
		 */
		this.toggle = function( $input ) {

			$input.closest( '.widget' ).find( '.llms-course-outline-toggle-wrapper' ).toggle();

		};

		// GO
		this.init();

		// whatever
		return this;

	};

	var a = new window.llms.widget_syllabus();

} )( jQuery );
