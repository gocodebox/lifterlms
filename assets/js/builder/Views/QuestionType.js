/**
 * Single Lesson View
 * @since    3.16.0
 * @version  3.27.0
 */
define( [ 'Views/Popover', 'Views/PostSearch' ], function( Popover, QuestionSearch ) {

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
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		add_question: function() {

			if ( 'existing' === this.model.get( 'id' ) ) {
				this.add_existing_question_click();
			} else {
				this.add_new_question();
			}

		},

		/**
		 * Add a new question to the quiz
		 * @return  void
		 * @since   3.27.0
		 * @version 3.27.0
		 */
		add_existing_question_click: function() {

			var pop = new Popover( {
				el: '#llms-add-question--existing',
				args: {
					backdrop: true,
					closeable: true,
					container: '#llms-builder-sidebar',
					dismissible: true,
					placement: 'top-left',
					width: 'calc( 100% - 40px )',
					offsetLeft: 250,
					offsetTop: 60,
					title: LLMS.l10n.translate( 'Add Existing Question' ),
					content: new QuestionSearch( {
						post_type: 'llms_question',
						searching_message: LLMS.l10n.translate( 'Search for existing questions...' ),
					} ).render().$el,
				}
			} );

			pop.show();
			Backbone.pubSub.on( 'question-search-select', function( event ) {
				pop.hide();
				this.add_existing_question( event );
			}, this );

		},

		add_existing_question: function( event ) {

			var question = event.data;

			if ( 'clone' === event.action ) {
				question = _.prepareQuestionObjectForCloning( question );
			} else {
				question._forceSync = true;
			}

			question._expanded = true;
			this.quiz.add_question( question );

			this.quiz.trigger( 'new-question-added' );

		},

		/**
		 * Add a new question to the quiz
		 * @return  void
		 * @since   3.27.0
		 * @version 3.27.0
		 */
		add_new_question: function() {

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
