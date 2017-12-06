/**
 * Handles syncing of a sortable collections order
 * Use with a Collection
 * Used with Section and Lesson Collections
 * @type     {Object}
 * @since    3.13.0
 * @version  3.13.0
 */
define( [], function() {
	return {

		/**
		 * Define the collections compartor
		 * @type  {String}
		 */
		comparator: 'order',

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		events: {
			'change:order': 'sort',
		},

		/**
		 * Retrieve the next order in the collection
		 * @return   int
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		next_order: function() {
			if ( ! this.length ) {
				return 1;
			}
			return this.last().get( 'order' ) + 1;
		},

		/**
		 * Save collection order to the DB
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		sync_order: function() {
			var id = _.uniqueId( 'saving_' );
			this.sync( 'update', this, {
				beforeSend: function() {
					window.llms_builder.Instance.Status.add( id );
				},
				error: function( jqxhr, status, msg ) {
					console.log( status, msg );
				},
				success: function( res ) {
					window.llms_builder.Instance.Status.remove( id );
				},
			} );
		}
	};
} );
