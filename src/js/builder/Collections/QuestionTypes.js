/**
 * Quiz Question Type Collection
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [ 'Models/QuestionType' ], function( model ) {

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
		 * @version  3.16.0
		 */
		initialize: function() {

			this.on( 'add', this.comparator );
			this.on( 'remove', this.comparator );

		},

		/**
		 * Comparator (sorts collection)
		 *
		 * @param    obj   model  QuestionType model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		comparator: function( model ) {

			return model.get( 'group' ).order;

		},

	} );

} );
