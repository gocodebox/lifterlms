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
			'click .bulk-toggle': 'bulk_toggle',
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

				var last_group = null,
					group = null;
				// let all the question types reference the quiz for adding questions quickly
				this.get_subview( 'bank' ).instance.viewManager.each( function( view ) {

					view.quiz = this.model;

					group = view.model.get( 'group' ).name;

					if ( last_group !== group ) {
						last_group = group;
						view.$el.before( '<li class="llms-question-bank-header"><h4>' + group + '</h4></li>' );
					}

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

				this.model.on( 'new-question-added', function() {
					var $questions = this.$el.find( '#llms-quiz-questions' );
					$questions.animate( { scrollTop: $questions.prop( 'scrollHeight' ) }, 200 );
				}, this );

			}

			return this;

		},

		/**
		 * Bulk expand / collapse question buttons
		 * @param    obj   event  js event object
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		bulk_toggle: function( event ) {

			var expanded = ( 'expand' === $( event.target ).attr( 'data-action' ) );

			this.model.get( 'questions' ).each( function( question ) {
				question.set( '_expanded', expanded );
			} );

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

		/**
		 * "Add Question" button click event
		 * Creates a popover with question type list interface
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		show_tools: function() {

			// create popover
			var pop = new Popover( {
				el: '#llms-show-question-bank',
				args: {
					backdrop: true,
					closeable: true,
					container: '#llms-builder-sidebar',
					dismissible: true,
					placement: 'top-left',
					width: 'calc( 100% - 40px )',
					title: LLMS.l10n.translate( 'Add a Question' ),
					url: '#llms-quiz-tools',
				}
			} );

			// show it
			pop.show();

			// if a question is added, hide the popover
			this.model.on( 'new-question-added', function() {
				pop.hide();
			} );

		},

		get_question_list: function( options ) {
			return new QuestionList( options );
		}

	}, Editable, Subview ) );

} );
