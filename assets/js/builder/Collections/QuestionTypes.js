/**
 * Quiz Question Type Collection
 * @since    [version]
 * @version  [version]
 */
define( [ 'Models/QuestionType' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		/**
		 * Initializer
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			this.on( 'add', this.comparator );
			this.on( 'remove', this.comparator );

		},

		/**
		 * Comparator (sorts collection)
		 * @param    obj   model  QuestionType model
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		comparator: function( model ) {

			return model.get( 'group' ).order;

		},

	} );

} );
