/**
 * Course Model
 * @since    3.13.0
 * @version  3.13.0
 */
define( [ 'Mixins/Syncable' ], function( Syncable ) {

	return Backbone.Model.extend( _.defaults( {

		type_id: 'course',

		/**
		 * New Course Defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		defaults: function() {
			return {
				title: 'New Course',
				edit_url: '',
				view_url: '',
			}
		},

	}, Syncable ) );

} );
