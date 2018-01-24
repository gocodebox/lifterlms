/**
 * Single Lesson View
 * @since    [version]
 * @version  [version]
 */
define( [ ], function() {

	return Backbone.View.extend( {

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-question-type',

		events: {
			'click .llms-add-question': 'add_question',
		},

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		id: function() {
			return 'llms-question-type-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-question-type-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

		/**
		 * Add a question of the selected type to the current quiz
		 * @since    [version]
		 * @version  [version]
		 */
		add_question: function() {

			this.quiz.add_question( {
				_expanded: true,
				choices: this.model.get( 'default_choices' ) ? this.model.get( 'default_choices' ) : null,
				question_type: this.model,
			} );

			this.quiz.trigger( 'new-question-added' );

		},

		// filter: function( term ) {

		// 	var words = this.model.get_keywords().map( function( word ) {
		// 		return word.toLowerCase();
		// 	} );

		// 	term = term.toLowerCase();

		// 	if ( -1 === words.indexOf( term ) ) {
		// 		this.$el.addClass( 'filtered' );
		// 	} else {
		// 		this.$el.removeClass( 'filtered' );
		// 	}

		// },

		// clear_filter: function() {
		// 	this.$el.removeClass( 'filtered' );
		// }

	} );

} );
