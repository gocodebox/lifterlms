/**
 * Lesson Model
 * @since    [version]
 * @version  [version]
 */
define( [ 'Collections/Questions', 'Models/Lesson', 'Models/Question', 'Models/_Relationships' ], function( Questions, Lesson, Question, Relationships ) {

	return Backbone.Model.extend( _.defaults( {

		relationships: {
			parent: {
				model: 'lesson',
				type: 'model',
			},
			children: {
				questions: {
					class: 'Questions',
					model: 'question',
					type: 'collection',
				},
			}
		},

		schema: {
			title: {
				title: 'Title',
				type: 'Text',
				validators: [ 'required' ],
			},
			content: {
				title: 'Content',
				type: 'Wysiwyg',
			},

		},

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    [version]
		 * @version  3.14.8
		 */
		defaults: function() {

			return {

				id: _.uniqueId( 'temp_' ),
				title: LLMS.l10n.translate( 'New Quiz' ),
				type: 'quiz',
				lesson_id: '',

				status: 'draft',

				// editable fields
				content: '',
				allowed_attempts: -1,
				passing_percent: 65,
				random_answers: 'no',
				time_limit: -1,

				questions: [],

				// calculated
				points: 0,

			};

		},

		/**
		 * Initializer
		 * @return   void
		 * @since    [version]
		 * @version  3.14.4
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

			this.listenTo( this.get( 'questions' ), 'add', this.update_points );
			this.listenTo( this.get( 'questions' ), 'remove', this.update_points );

			this.set( 'points', this.get_total_points() );

		},

		/**
		 * Add a new question to the quiz
		 * @param    obj   data   question data
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		add_question: function( data ) {

			data.parent_id = this.get( 'id' );
			this.get( 'questions' ).add( data, {
				parent: this,
			} );

		},

		get_total_points: function() {

			var points = 0;

			this.get( 'questions' ).each( function( question ) {
				points += question.get_points();
			} );

			return points;

		},

		update_points: function() {

			this.set( 'points', this.get_total_points() );

		},

	}, Relationships ) );

} );
