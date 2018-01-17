/**
 * Questions Collection
 * @since    [version]
 * @version  [version]
 */
define( [ 'Models/Question' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		/**
		 * Initialize
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			// reorder called by QuestionList view when sortable drops occur
			this.on( 'reorder', this.update_order );

			// when a question is added or removed, update order
			this.on( 'add', this.update_order );
			this.on( 'remove', this.update_order );

			this.on( 'add', this.update_parent );

		},

		/**
		 * Update the order attr of each question in the list to reflect the order of the collection
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		update_order: function() {

			var self = this;

			this.each( function( question ) {

				question.set( 'order', self.indexOf( question ) + 1 );

			} );

		},

		/**
		 * When adding a question to a question list, update the question's parent
		 * Will ensure that questions moved into and out of groups always have the corerct parent_id
		 * @param    obj   model  instance of the question model
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		update_parent: function( model ) {

			model.set( 'parent_id', this.parent.get( 'id' ) );

		},

	} );

} );
