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

			var self = this;

			// The primary instructor the Author control shows, set once the rows load.
			this.primary_id = null;

			// before saving, update the wp core hidden field for post_author
			// so that the first instructor is always set as the post author
			$( '._llms_instructors_data.repeater' ).on( 'llms-repeater-before-save', function( e, params ) {
				var author_id = self.get_primary_id( params.$el );
				$( '#post_author' ).val( author_id );

				// Only when the primary instructor changed, so a change made in the Author control is kept on submit.
				if ( author_id && author_id !== self.primary_id ) {
					self.primary_id = author_id;
					self.sync_author( author_id );
				}
			} );

			$( '._llms_instructors_data.repeater' ).on( 'llms-new-repeater-row', function( e, params ) {

				var $instructor = params.$row.find( 'select[name^="_llms_id"]' ),
					$target     = params.$row.find( '.llms-repeater-title' );

				if ( null === self.primary_id ) {
					self.primary_id = self.get_primary_id( $( this ) ) || null;
				}

				$instructor.on( 'select2:select', function( e ) {
					if ( ! e.params ) {
						$target.html( $instructor.find( 'option[selected="selected"]' ).html() );
					} else {
						$target.text( e.params.data.text );
					}
				} ).trigger( 'select2:select' );

			} );

		};

		/**
		 * Get the user ID of the first (primary) instructor in the repeater
		 *
		 * @since [version]
		 *
		 * @param {Object} $repeater jQuery selector for the instructors repeater.
		 * @return {string|undefined}
		 */
		this.get_primary_id = function( $repeater ) {
			return $repeater.find( '.llms-repeater-rows .llms-repeater-row' ).first().find( 'select[name^="_llms_id"]' ).val();
		};

		/**
		 * Show the new primary instructor in the editor's Author control
		 *
		 * @since [version]
		 *
		 * @param {string} author_id WP_User ID of the new primary instructor.
		 * @return {void}
		 */
		this.sync_author = function( author_id ) {

			// The loaded author the server compares against when the post is saved.
			$( 'input[name="_llms_instructors_author"]' ).val( author_id );

			// Block editor Author panel, for post types that support an author.
			if ( $( 'body' ).hasClass( 'block-editor-page' ) && window.wp && wp.data && undefined !== wp.data.select( 'core/editor' ).getEditedPostAttribute( 'author' ) ) {
				wp.data.dispatch( 'core/editor' ).editPost( { author: parseInt( author_id, 10 ) } );
			}

			// Classic editor Author meta box, when the user is one of its options.
			var $override = $( '#post_author_override' );
			if ( $override.find( 'option[value="' + author_id + '"]' ).length ) {
				$override.val( author_id ).trigger( 'change' );
			}

		};

		// go
		this.init();

	};

	var a = new window.llms.metabox_instructors();

} )( jQuery );
