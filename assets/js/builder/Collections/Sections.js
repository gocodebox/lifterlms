/**
 * Sections Collection
 * @since    3.16.0
 * @version  3.16.0
 */
define( [ 'Models/Section' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		/**
		 * Initialize
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			var self = this;

			// reorder called by SectionList view when sortable drops occur
			this.on( 'reorder', this.update_order );

			// when a section is added or removed, update order
			this.on( 'add', this.update_order );
			this.on( 'remove', this.update_order );

		},

		/**
		 * Update the order attr of each section in the list to reflect the order of the collection
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_order: function() {

			var self = this;

			this.each( function( section ) {

				section.set( 'order', self.indexOf( section ) + 1 );

			} );

		},

	} );

} );
