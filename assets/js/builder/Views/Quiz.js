/**
 * Single Quiz View
 * @since    [version]
 * @version  [version]
 */
define( [
		'Models/Quiz',
		'Views/Popover',
		'Views/QuizHeader',
		'Views/QuestionBank',
		'Views/QuestionList',
		'Views/_Editable',
		'Views/_Subview'
	], function(
		QuizModel,
		Popover,
		QuizHeader,
		QuestionBank,
		QuestionList,
		Editable,
		Subview
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'default', // [lesson|quiz]

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			header: {
				class: QuizHeader,
				instance: null,
				state: 'default',
			},
			bank: {
				class: QuestionBank,
				instance: null,
				state: 'default',
			},
			list: {
				class: QuestionList,
				instance: null,
				state: 'default',
			},
		},

		el: '#llms-editor-quiz',

		/**
		 * Events
		 * @type  {Object}
		 */
		events: _.defaults( {
			'click #llms-enable-quiz': 'enable_quiz',
			'click #llms-show-question-bank': 'show_tools',
			// 'keyup #llms-question-bank-filter': 'filter_question_types',
			// 'search #llms-question-bank-filter': 'filter_question_types',
		}, Editable.events ),

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-quiz-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function( data ) {

			this.lesson = data.lesson;

			// initialize the model if the quiz is enabled or it's disabled but we still have data for a quiz
			if ( 'yes' === this.lesson.get( 'quiz_enabled' ) || ! _.isEmpty( this.lesson.get( 'quiz' ) ) ) {
				this.model = this.lesson.get( 'quiz' );

				/**
				 * @todo  this is a terrilbe terrible patch
				 *        I've spent nearly 3 days trying to figure out how to not use this line of code
				 *        ISSUE REPRODUCTION:
				 *        Open course builder
				 *        Open a lesson (A) and add a quiz
				 *        Switch to a new lesson (B)
				 *        Add a new quiz
				 *        Return to lesson A and the quizzes parent will be set to LESSON B
				 *        This will happen for *every* quiz in the builder...
				 *        Adding this set_parent on init guarantees that the quizzes correct parent is set
				 *        after adding new quizzes to other lessons
				 *        it's awful and it's gross...
				 *        I'm confused and tired and going to miss release dates again because of it
				 */
				this.model.set_parent( this.lesson );
			}

			this.events_subscribe( {
				'clone-question': this.clone_question,
			} );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			// render the quiz builder
			if ( this.model ) {

				this.render_subview( 'header', {
					model: this.model,
				} );

				this.render_subview( 'bank', {
					collection: window.llms_builder.questions,
				} );
				// let all the question types reference the quiz for adding questions quickly
				this.get_subview( 'bank' ).instance.viewManager.each( function( view ) {
					view.quiz = this.model;
				}, this );

				this.render_subview( 'list', {
					el: '#llms-quiz-questions',
					collection: this.model.get( 'questions' ),
				} );
				var list = this.get_subview( 'list' ).instance;
				list.quiz = this;
				list.collection.on( 'add', function() {
					list.collection.trigger( 'reorder' );
				}, this );
				list.on( 'sortStart', list.sortable_start );
				list.on( 'sortStop', list.sortable_stop );


			}

			return this;

		},

		clone_question: function( model ) {

			var clone = _.clone( model.attributes );
			delete clone.id;

			clone.image = _.clone( model.get( 'image' ).attributes );

			if ( model.get( 'choices' ) ) {

				clone.choices = [];

				model.get( 'choices' ).each( function ( choice ) {

					var choice_clone = _.clone( choice.attributes );
					delete choice_clone.id;
					delete choice_clone.question_id;

					clone.choices.push( choice_clone );

				} );

			}

			this.model.add_question( clone );

		},

		/**
		 * Adds a new quiz to a lesson which currently has no quiz associated with it
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		enable_quiz: function() {

			var quiz = this.lesson.get( 'quiz' );
			if ( _.isEmpty( quiz ) ) {
				quiz = this.lesson.add_quiz();
			} else {
				this.lesson.set( 'quiz_enabled', 'yes' );
			}

			this.model = quiz;
			this.render();

		},

		// filter_question_types: _.debounce( function( event ) {

		// 	var term = $( event.target ).val();

		// 	this.QuestionBankView.viewManager.each( function( view ) {
		// 		if ( ! term ) {
		// 			view.clear_filter();
		// 		} else {
		// 			view.filter( term );
		// 		}
		// 	} );


		// }, 300 ),

		show_tools: function() {

			var pop = new Popover( {
				el: '#llms-show-question-bank',
				args: {
					backdrop: true,
					closeable: true,
					container: '#llms-builder-sidebar',
					dismissible: true,
					placement: 'vertical',
					width: 'calc( 100% - 40px )',
					title: LLMS.l10n.translate( 'Add a Question' ),
					url: '#llms-quiz-tools',
				}
			} );

			pop.show();
			this.model.on( 'new-question-added', function() {
				pop.hide();
			} );

		},

		get_question_list: function( options ) {
			return new QuestionList( options );
		}

	}, Editable, Subview ) );

} );
