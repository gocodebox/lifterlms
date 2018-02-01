/**
 * Quiz Question Choice
 * @since    [version]
 * @version  [version]
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
		 * @since    [version]
		 * @version  [version]
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
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function( data, options ) {

			this.startTracking();
			this.init_relationships( options );

		},

		/**
		 * Retrieve the choice's parent question
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		get_parent: function() {
			return this.collection.parent;
		},

		/**
		 * Determine if "selection" is enabled for the question type
		 * Choice type questions are selectable by reorder type questions are not but still use choices
		 * @return   {Boolean}
		 * @since    [version]
		 * @version  [version]
		 */
		is_selectable: function() {
			return this.get_parent().get( 'question_type' ).get_choice_selectable();
		},

	}, Relationships ) );

} );
