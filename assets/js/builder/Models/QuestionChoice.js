/**
 * Quiz Question Choice
 * @since    [version]
 * @version  [version]
 */
define( [ 'Models/Image', 'Models/_Relationships' ], function( Image, Relationships ) {

	return Backbone.Model.extend( _.defaults( {

		relationships: {
			parent: {
				model: 'question',
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

		initialize: function() {

			this.startTracking();
			this.init_relationships();

		},

	}, Relationships ) );

} );
