/**
 * Instructors Metabox
 *
 * @since    3.13.0
 * @version  3.13.0
 */
( function( $ ) {

	window.llms = window.llms || {};

	window.llms.metabox_instructors = function() {

		/**
		 * Initialize
		 *
		 * @return  void
		 * @since   3.13.0
		 * @version 3.13.0
		 */
		this.init = function() {

			// before saving, update the wp core hidden field for post_author
			// so that the first instructor is always set as the post author
			$( '._llms_instructors_data.repeater' ).on( 'llms-repeater-before-save', function( e, params ) {
				var author_id = params.$el.find( '.llms-repeater-rows .llms-repeater-row' ).first().find( 'select[name^="_llms_id"]' ).val();
				$( '#post_author' ).val( author_id );
			} );

			$( '._llms_instructors_data.repeater' ).on( 'llms-new-repeater-row', function( e, params ) {

				var $instructor = params.$row.find( 'select[name^="_llms_id"]' ),
					$target     = params.$row.find( '.llms-repeater-title' );

				$instructor.on( 'select2:select', function( e ) {
					if ( ! e.params ) {
						$target.html( $instructor.find( 'option[selected="selected"]' ).html() );
					} else {
						$target.text( e.params.data.text );
					}
				} ).trigger( 'select2:select' );

			} );

		};

		// go
		this.init();

	};

	var a = new window.llms.metabox_instructors();

} )( jQuery );
