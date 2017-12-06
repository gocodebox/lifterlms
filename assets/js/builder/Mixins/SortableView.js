/**
 * Handles UX and Events for sorting models via jQuery UI Sortable
 * Use with a Collection's View
 * Used with Section and Lesson List (collection) views
 * @type     {Object}
 * @since    3.13.0
 * @version  3.13.0
 */
define( [], function() {

	return {

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		events: {
			'update-sort': 'update_sort',
		},

		/**
		 * Resorst an out-of-order collection by the order property on its models
		 * Rerenders the view when completed
		 * @param    {obj}   collection  a backbone collection with models that have an "order" prop
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		sort_collection: function( collection ) {

			if ( collection.length ) {

				collection.each( function( model, index ) {
					model.set( 'order', index + 1 );
				} );

			}

			collection.trigger( 'rerender' );

		},

		/**
		 * Triggered by element dropping event
		 * Moves models into the new collection, resorts collections, and optionally syncs data to DB
		 * @param    {obj}    event            js event object
		 * @param    {obj}    model            model being moved
		 * @param    {obj}    order            new order (not index) of the model
		 * @param    {obj}    to_collection    collection the model is to be added to
		 * @param    {obj}    from_collection  collection the model is coming from
		 *                                       	new items don't have a from collection
		 *                                        	this will be the same if it's a reorder and not moving to a new collection
		 * @param    {bool}   auto_save        when true, saves to the database
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		update_sort: function ( event, model, order, to_collection, from_collection, auto_save ) {

			event.stopPropagation();

			auto_save = undefined === auto_save ? true : auto_save;

			var to_self = ( ! from_collection || to_collection === from_collection )
				remove_from_collection = to_self ? to_collection : from_collection;

			// dropped items won't have a collection yet...
			if ( remove_from_collection ) {
				remove_from_collection.remove( model );
			}

			// when moving lessons to a new section we need to update the old collection
			if ( remove_from_collection && ! to_self ) {
				this.sort_collection( from_collection );
				from_collection.sync_order();
			}

			to_collection.add( model, { at: order - 1 } );
			this.sort_collection( to_collection );

			if ( auto_save ) {
				to_collection.sync_order();
			}

		},

	};

} );
