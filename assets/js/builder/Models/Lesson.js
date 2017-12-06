/**
 * Lesson Model
 * @since    3.13.0
 * @version  3.14.8
 */
define( [ 'Mixins/Syncable' ], function( Syncable ) {

	return Backbone.Model.extend( _.defaults( {

		type_id: 'lesson',

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.14.8
		 */
		defaults: function() {
			var order = this.collection ? this.collection.next_order() : 1,
				// section_id = App.Methods.get_last_section().id;
				section_id = 1;
			return {
				title: LLMS.l10n.translate( 'New Lesson' ),
				type: 'lesson',
				order: order,
				section_id: section_id,

				// urls
				edit_url: '',
				view_url: '',

				// icon info
				date_available: '',
				days_before_available: '',
				drip_method: '',
				has_content: false,
				is_free: false,
				prerequisite: false,
				quiz: false,
			};
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.14.0
		 * @version  3.14.4
		 */
		initialize: function() {

			this.listenTo( this, 'detach', this.detach );

		},

		/**
		 * Detach lesson from section
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		detach: function() {

			var id = 'detach_' + this.id;

			this.set( 'section_id', '' );
			this.collection.remove( this.id );
			this.save( null, {
				beforeSend: function() {
					Instance.Status.add( id );
				},
				success: function( res ) {
					Instance.Status.remove( id );
				},
			} );
		},

		/**
		 * Retrieve the parent section of the lesson
		 * @return   {obj}   App.Models.Section
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_section: function() {
			return Instance.Syllabus.collection.get( this.get( 'section_id' ) );
		},

	}, Syncable ) );

} );
