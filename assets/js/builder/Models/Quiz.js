/**
 * Quiz Model
 * @since    3.16.0
 * @version  3.24.0
 */
define( [
		'Collections/Questions',
		'Models/Lesson',
		'Models/Question',
		'Models/_Relationships',
		'Models/_Utilities',
		'Schemas/Quiz',
	], function(
		Questions,
		Lesson,
		Question,
		Relationships,
		Utilities,
		QuizSchema
	) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * model relationships
		 * @type  {Object}
		 */
		relationships: {
			parent: {
				model: 'lesson',
				type: 'model',
			},
			children: {
				questions: {
					class: 'Questions',
					model: 'llms_question',
					type: 'collection',
				},
			}
		},

		/**
		 * Lesson Settings Schema
		 * @type  {Object}
		 */
		schema: QuizSchema,

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.6
		 */
		defaults: function() {

			return {
				id: _.uniqueId( 'temp_' ),
				title: LLMS.l10n.translate( 'New Quiz' ),
				type: 'llms_quiz',
				lesson_id: '',

				status: 'draft',

				// editable fields
				content: '',
				allowed_attempts: 5,
				limit_attempts: 'no',
				limit_time: 'no',
				passing_percent: 65,
				name: '',
				random_answers: 'no',
				time_limit: 30,
				show_correct_answer: 'no',

				questions: [],

				// calculated
				_points: 0,

				// display
				permalink: '',
				_show_settings: false,
				_questions_loaded: false,
			};

		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.24.0
		 */
		initialize: function() {

			this.init_custom_schema();
			this.startTracking();
			this.init_relationships();

			this.listenTo( this.get( 'questions' ), 'add', this.update_points );
			this.listenTo( this.get( 'questions' ), 'remove', this.update_points );

			this.set( '_points', this.get_total_points() );

			// when a quiz is published, ensure the parent lesson is marked as "Enabled" for quizzing
			this.on( 'change:status', function() {
				if ( 'publish' === this.get( 'status' ) ) {
					this.get_parent().set( 'quiz_enabled', 'yes' );
				}
			} );

			window.llms.hooks.doAction( 'llms_quiz_model_init', this );

		},

		/**
		 * Add a new question to the quiz
		 * @param    obj   data   question data
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_question: function( data ) {

			data.parent_id = this.get( 'id' );
			var question = this.get( 'questions' ).add( data, {
				parent: this,
			} );
			Backbone.pubSub.trigger( 'quiz-add-question', question, this );

		},

		/**
		 * Retrieve the translated post type name for the model's type
		 * @param    bool     plural  if true, returns the plural, otherwise returns singular
		 * @return   string
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'quizzes' );
			}

			return LLMS.l10n.translate( 'quiz' );
		},

		/**
		 * Retrieve the quiz's total points
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_total_points: function() {

			var points = 0;

			this.get( 'questions' ).each( function( question ) {
				points += question.get_points();
			} );

			return points;

		},

		/**
		 * Lazy load questions via AJAX
		 * @param    {Function}  cb  callback function
		 * @return   void
		 * @since    3.19.2
		 * @version  3.19.2
		 */
		load_questions: function( cb ) {

			if ( this.get( '_questions_loaded' ) ) {

				cb();

			} else {

				var self = this;

				LLMS.Ajax.call( {
					data: {
						action: 'llms_builder',
						action_type: 'lazy_load',
						course_id: window.llms_builder.CourseModel.get( 'id' ),
						load_id: this.get( 'id' ),
					},
					error: function( xhr, status, error ) {

						console.log( xhr, status, error );
						window.llms_builder.debug.log( '==== start load_questions error ====', xhr, status, error, '==== finish load_questions error ====' );
						cb( true );

					},
					success: function( res ) {
						if ( res && res.questions ) {
							self.set( '_questions_loaded', true );
							if ( res.questions ) {
								_.each( res.questions, self.add_question, self );
							}
							cb();
						} else {
							cb( true );
						}
					}

				} );

			}


		},

		/**
		 * Update total number of points calculated property
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_points: function() {

			this.set( '_points', this.get_total_points() );

		},

	}, Relationships, Utilities ) );

} );
