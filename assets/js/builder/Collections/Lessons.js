/**
 * Lessons Collection
 * @since    3.13.0
 * @version  3.13.0
 */
define( [ 'Models/Lesson', 'Mixins/Syncable', 'Mixins/SortableCollection' ], function( model, Syncable, SortableCollection ) {

	return Backbone.Collection.extend( _.defaults( {

		model: model,
		type_id: 'lesson',

	}, Syncable, SortableCollection ) );

} );
