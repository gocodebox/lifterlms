/**
 * Lessons Collection
 *
 * @since    3.13.0
 * @version  3.17.0
 */
define( [ 'Models/Lesson' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 *
		 * @type  obj
		 */
		model: model,

		/**
		 * Initializer
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.17.0
		 */
		initialize: function() {

			// reorder called by LessonList view when sortable drops occur
			this.on( 'reorder', this.on_reorder );

			// when a lesson is added or removed, update order
			this.on( 'add', this.on_reorder );
			this.on( 'remove', this.on_reorder );

		},

		/**
		 * On lesson reorder callback
		 *
		 * Update the order attr of each lesson to reflect the new lesson order
		 * Validate prerequisite (if set) and unset it if it's no longer a valid prereq
		 *
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		on_reorder: function() {
			this.update_order();
			this.validate_prereqs();
		},

		/**
		 * Update lesson order attribute of all lessons when lessons are reordered
		 *
		 * @return      void
		 * @since       3.16.0
		 * @version     3.17.0
		 */
		update_order: function() {

			this.each( function( lesson ) {
				lesson.set( 'order', this.indexOf( lesson ) + 1 );
			}, this );

		},

		/**
		 * Validate prerequisite (if set) and unset it if it's no longer a valid prereq
		 *
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		validate_prereqs: function() {

			this.each( function( lesson ) {

				// validate prereqs
				if ( 'yes' === lesson.get( 'has_prerequisite' ) ) {
					var valid = _.pluck( _.flatten( _.pluck( lesson.get_available_prereq_options(), 'options' ) ), 'key' );
					if ( -1 === valid.indexOf( lesson.get( 'prerequisite' ) * 1 ) ) {
						lesson.set( {
							prerequisite: 0,
							has_prerequisite: 'no',
						} );
					}
				}

			}, this );

		},

	} );

} );
