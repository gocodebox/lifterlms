/**
 * Section Model
 * @since    3.13.0
 * @version  3.14.4
 */
define( [ 'Mixins/Syncable' ], function( Syncable ) {

	return Backbone.Model.extend( _.defaults( {

		type_id: 'section',

		/**
		 * New section defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		defaults: function() {
			var order = this.collection ? this.collection.next_order() : 1;
			return {
				active: false,
				title: 'New Section',
				type: 'section',
				lessons: [],
				order: order,
			};
		},

		/**
		 * Retrieve the next section in the section's collection
		 * @return   obj     App.Models.Section
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_next: function() {
			return this.collection.at( this.collection.indexOf( this ) + 1 );
		},

		/**
		 * Retrieve the prev section in the section's collection
		 * @return   obj     App.Models.Section
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_prev: function() {
			return this.collection.at( this.collection.indexOf( this ) - 1 );
		},

	}, Syncable ) );

} );
