/**
 * Quiz question bank view
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [ 'Views/QuestionChoice' ], function( ChoiceView ) {

	return Backbone.CollectionView.extend( {

		className: 'llms-quiz-questions',

		/**
		 * Choice model view
		 *
		 * @type  {[type]}
		 */
		modelView: ChoiceView,

		/**
		 * Enable keyboard events
		 *
		 * @type  {Bool}
		 */
		processKeyEvents: false,

		/**
		 * Are sections selectable?
		 *
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 *
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			// connectWith: '.llms-lessons',
			cursor: 'move',
			handle: '.llms-choice-id',
			items: '.llms-question-choice',
			placeholder: 'llms-question-choice llms-sortable-placeholder',
		},

		sortable_start: function( model ) {
			this.$el.addClass( 'dragging' );
		},

		sortable_stop: function( model ) {
			this.$el.removeClass( 'dragging' );
		},

	} );

} );
