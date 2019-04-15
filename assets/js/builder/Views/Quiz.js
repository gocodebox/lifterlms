/**
 * Single Quiz View
 * @since    3.16.0
 * @version  3.24.0
 */
define( [
		'Models/Quiz',
		'Views/Popover',
		'Views/PostSearch',
		'Views/QuestionBank',
		'Views/QuestionList',
		'Views/SettingsFields',
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Subview',
		'Views/_Trashable'
	], function(
		QuizModel,
		Popover,
		PostSearch,
		QuestionBank,
		QuestionList,
		SettingsFields,
		Detachable,
		Editable,
		Subview,
		Trashable
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'default',

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			settings: {
				class: SettingsFields,
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
			'click #llms-existing-quiz': 'add_existing_quiz_click',
			'click #llms-new-quiz': 'add_new_quiz',
			'click #llms-show-question-bank': 'show_tools',
			'click .bulk-toggle': 'bulk_toggle',
			// 'keyup #llms-question-bank-filter': 'filter_question_types',
			// 'search #llms-question-bank-filter': 'filter_question_types',
		}, Detachable.events, Editable.events, Trashable.events ),

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
		 * @since    3.16.0
		 * @version  3.19.2
		 */
		initialize: function( data ) {

			this.lesson = data.lesson;

			// initialize the model if the quiz is enabled or it's disabled but we still have data for a quiz
			if ( 'yes' === this.lesson.get( 'quiz_enabled' ) || ! _.isEmpty( this.lesson.get( 'quiz' ) ) ) {

				this.model = this.lesson.get( 'quiz' );

				/**
				 * @todo  this is a terrible terrible patch
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

				this.listenTo( this.model, 'change:_points', this.render_points );

			}

			this.on( 'model-trashed', this.on_trashed );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.19.2
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			// render the quiz builder
			if ( this.model ) {

				// don't allow interaction until questions are lazy loaded
				LLMS.Spinner.start( this.$el );

				this.render_subview( 'settings', {
					el: '#llms-quiz-settings-fields',
					model: this.model,
				} );

				this.init_datepickers();
				this.init_selects();

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

				this.model.load_questions( _.bind( function( err ) {

					if ( err ) {
						alert( LLMS.l10n.translate( 'An error occurred while trying to load the questions. Please refresh the page and try again.' ) );
						return this;
					}

					LLMS.Spinner.stop( this.$el );
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

				}, this ) );

				this.model.on( 'new-question-added', function() {
					var $questions = this.$el.find( '#llms-quiz-questions' );
					$questions.animate( { scrollTop: $questions.prop( 'scrollHeight' ) }, 200 );
				}, this );

			}

			return this;

		},

		/**
		 * On quiz points update, update the value of the Total Points area in the header
		 * @param    obj   quiz    Instance of the quiz model
		 * @param    int   points  Updated number of points
		 * @return   void
		 * @since    3.17.6
		 * @version  3.17.6
		 */
		render_points: function( quiz, points ) {

			this.$el.find( '#llms-quiz-total-points' ).text( points );

		},

		/**
		 * Bulk expand / collapse question buttons
		 * @param    obj   event  js event object
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
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
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_new_quiz: function() {

			var quiz = this.lesson.get( 'quiz' );
			if ( _.isEmpty( quiz ) ) {
				quiz = this.lesson.add_quiz();
			} else {
				this.lesson.set( 'quiz_enabled', 'yes' );
			}

			this.model = quiz;
			this.render();

		},


		/**
		 * Add an existing quiz to a lesson
		 * @param    obj  event  js event object
		 * @since    3.16.0
		 * @version  3.24.0
		 */
		add_existing_quiz: function( event ) {

			this.post_search_popover.hide();

			var quiz = event.data;

			if ( 'clone' === event.action ) {

				quiz = _.prepareQuizObjectForCloning( quiz );

			} else {

				quiz._forceSync = true;

			}

			delete quiz.lesson_id;

			this.lesson.add_quiz( quiz );
			this.model = this.lesson.get( 'quiz' );
			this.render();

		},

		/**
		 * Open add existing quiz popover
		 * @param    obj   event  JS event object
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		add_existing_quiz_click: function( event ) {

			event.preventDefault();

			this.post_search_popover = new Popover( {
				el: '#llms-existing-quiz',
				args: {
					backdrop: true,
					closeable: true,
					container: '.wrap.lifterlms.llms-builder',
					dismissible: true,
					placement: 'left',
					width: 480,
					title: LLMS.l10n.translate( 'Add Existing Quiz' ),
					content: new PostSearch( {
						post_type: 'llms_quiz',
						searching_message: LLMS.l10n.translate( 'Search for existing quizzes...' ),
					} ).render().$el,
					onHide: function() {
						Backbone.pubSub.off( 'quiz-search-select' );
					},
				}
			} );

			this.post_search_popover.show();
			Backbone.pubSub.once( 'quiz-search-select', this.add_existing_quiz, this );

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
		 * Callback function when the quiz has been deleted
		 * @param    object   quiz  Quiz Model
		 * @return   void
		 * @since    3.16.6
		 * @version  3.16.6
		 */
		on_trashed: function( quiz ) {

			this.lesson.set( 'quiz_enabled', 'no' );
			this.lesson.set( 'quiz', '' );

			delete this.model;

			this.render();

		},

		/**
		 * "Add Question" button click event
		 * Creates a popover with question type list interface
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
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

	}, Detachable, Editable, Subview, Trashable, SettingsFields ) );

} );
