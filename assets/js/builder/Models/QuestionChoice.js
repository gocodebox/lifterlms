/**
 * Quiz Question Choice
 * @since    3.16.0
 * @version  3.16.0
 */
define( [ 'Models/Image', 'Models/_Relationships' ], function( Image, Relationships ) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * Model relationships
		 * @type  {Object}
		 */
		relationships: {
			parent: {
				model: 'llms_question',
				type: 'model',
			},
			children: {
				choice: {
					conditional: function( model ) {
						return ( 'image' === model.get( 'choice_type' ) );
					},
					class: 'Image',
					model: 'image',
					type: 'model',
				},
			},
		},

		/**
		 * Model defaults
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				choice: '',
				choice_type: 'text',
				correct: false,
				marker: 'A',
				question_id: '',
				type: 'choice',
			}
		},

		/**
		 * Initializer
		 * @param    obj   data     object of model attributes
		 * @param    obj   options  additional options
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data, options ) {

			this.startTracking();
			this.init_relationships( options );

		},

		/**
		 * Retrieve the choice's parent question
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent: function() {
			return this.collection.parent;
		},

		/**
		 * Retrieve the ID used when trashing the model
		 * @return   string
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		get_trash_id: function() {
			return this.get( 'question_id' ) + ':' + this.get( 'id' );
		},

		/**
		 * Determine if "selection" is enabled for the question type
		 * Choice type questions are selectable by reorder type questions are not but still use choices
		 * @return   {Boolean}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		is_selectable: function() {
			return this.get_parent().get( 'question_type' ).get_choice_selectable();
		},

	}, Relationships ) );

} );
