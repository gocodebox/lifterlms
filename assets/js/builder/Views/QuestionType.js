/**
 * Single Lesson View
 * @since    3.16.0
 * @version  3.16.0
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
		 * @since    3.16.0
		 * @version  3.16.0
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
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {
			this.$el.html( this.template( this.model ) );
			return this;
		},

		/**
		 * Add a question of the selected type to the current quiz
		 * @since    3.16.0
		 * @version  3.16.0
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
