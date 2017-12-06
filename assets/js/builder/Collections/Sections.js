/**
 * Sections Collection
 * @since    3.13.0
 * @version  3.13.0
 */
define( [ 'Models/Section', 'Mixins/Syncable', 'Mixins/SortableCollection' ], function( model, Syncable, Sortable ) {

	return Backbone.Collection.extend( _.defaults( {

		model: model,
		type_id: 'section',

		/**
		 * Parse AJAX response
		 * @param    obj   response  JSON from the server
		 * @return   obj             relevant data from the server
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		parse: function( response ) {
			return response.data;
		},

	}, Syncable, Sortable ) );

} );
