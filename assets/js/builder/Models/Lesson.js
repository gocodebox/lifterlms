/**
 * Lesson Model
 * @since    3.13.0
 * @version  3.27.0
 */
define( [ 'Models/Quiz', 'Models/_Relationships', 'Models/_Utilities', 'Schemas/Lesson' ], function( Quiz, Relationships, Utilities, LessonSchema ) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * Model relationships
		 * @type  {Object}
		 */
		relationships: {
			parents: {
				model: 'section',
				type: 'model',
			},
			children: {
				quiz: {
					class: 'Quiz',
					conditional: function( model ) {
						// if quiz is enabled OR not enabled but we have some quiz data as an obj
						return ( 'yes' === model.get( 'quiz_enabled' ) || ! _.isEmpty( model.get( 'quiz' ) ) );
					},
					model: 'llms_quiz',
					type: 'model',
				},
			},
		},

		/**
		 * Lesson Settings Schema
		 * @type  {Object}
		 */
		schema: LessonSchema,

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.24.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				title: LLMS.l10n.translate( 'New Lesson' ),
				type: 'lesson',
				order: this.collection ? this.collection.length + 1 : 1,
				parent_course: window.llms_builder.course.id,
				parent_section: '',

				// urls
				edit_url: '',
				view_url: '',

				// editable fields
				content: '',
				audio_embed: '',
				has_prerequisite: 'no',
				require_passing_grade: 'yes',
				require_assignment_passing_grade: 'yes',
				video_embed: '',
				free_lesson: '',
				points: 1,

				// other fields
				assignment: {}, // assignment model/data
				assignment_enabled: 'no',

				quiz: {}, // quiz model/data
				quiz_enabled: 'no',

				_forceSync: false,

			};
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.17.0
		 */
		initialize: function() {

			this.init_custom_schema();
			this.startTracking();
			this.maybe_init_assignments();
			this.init_relationships();

			// if the lesson ID isn't set on a quiz, set it
			var quiz = this.get( 'quiz' );
			if ( ! _.isEmpty( quiz ) && ! quiz.get( 'lesson_id' ) ) {
				quiz.set( 'lesson_id', this.get( 'id' ) );
			}

			window.llms.hooks.doAction( 'llms_lesson_model_init', this );

		},

		/**
		 * Retrieve a reference to the parent course of the lesson
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_course: function() {
			return this.get_parent().get_parent();
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
				return LLMS.l10n.translate( 'lessons' );
			}

			return LLMS.l10n.translate( 'lesson' );
		},

		/**
		 * Override default get_parent to grab from collection if models parent isn't set
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_parent: function() {

			var rels = this.get_relationships();
			if ( rels.parent && rels.parent.reference ) {
				return rels.parent.reference;
			} else if ( this.collection && this.collection.parent ) {
				return this.collection.parent;
			}
			return false;

		},

		/**
		 * Retrieve the questions percentage value within the quiz
		 * @return   string
		 * @since    3.24.0
		 * @version  3.24.0
		 */
		get_points_percentage: function() {

			var total = this.get_course().get_total_points(),
				points = this.get( 'points' ) * 1;

			if ( ! _.isNumber( points ) ) {
				points = 0;
			}

			if ( 0 === total ) {
				return '0%';
			}

			return ( ( points / total ) * 100 ).toFixed( 2 ) + '%';

		},

		/**
		 * Retrieve an array of prerequisite options available for the current lesson
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_available_prereq_options: function() {

			var parent_section_index = this.get_parent().collection.indexOf( this.get_parent() ),
				lesson_index_in_section = this.collection.indexOf( this ),
				options = [];

			this.get_course().get( 'sections' ).each( function( section, curr_sec_index ) {
				if ( curr_sec_index <= parent_section_index ) {
					var group = {
							/* translators: %1$d = section order number, %2$s = section title */
							label: LLMS.l10n.replace( 'Section %1$d: %2$s', {
								'%1$d': section.get( 'order' ),
								'%2$s': section.get( 'title' )
							} ),
							options: [],
						};

					section.get( 'lessons' ).each( function( lesson, curr_les_index ) {
						if ( curr_sec_index !== parent_section_index || curr_les_index < lesson_index_in_section ) {
							/* translators: %1$d = lesson order number, %2$s = lesson title */
							group.options.push( {
								key: lesson.get( 'id' ),
								val: LLMS.l10n.replace( 'Lesson %1$d: %2$s', {
									'%1$d': lesson.get( 'order' ),
									'%2$s': lesson.get( 'title' )
								} ),
							} );
						}
					}, this );

					options.push( group );
				}
			}, this );

			return options;

		},

		/**
		 * Add a new quiz to the lesson
		 * @param    obj   data   object of quiz data used to construct a new quiz model
		 * @return   obj          model for the created quiz
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		add_quiz: function( data ) {

			data = data || {};

			data.lesson_id = this.id;
			data._questions_loaded = true;

			if ( ! data.title ) {

				data.title = LLMS.l10n.replace( '%1$s Quiz', {
					'%1$s': this.get( 'title' ),
				} );

			}

			this.set( 'quiz', data );
			this.init_relationships();

			var quiz = this.get( 'quiz' );
			this.set( 'quiz_enabled', 'yes' );

			window.llms.hooks.doAction( 'llms_lesson_add_quiz', quiz, this );

			return quiz;

		},

		/**
		 * Determine if this is the first lesson
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_first_in_course: function() {

			// if it's not the first item in the section it cant be the first lesson
			if ( this.collection.indexOf( this ) ) {
				return false;
			}

			// if it's not the first section it cant' be first lesson
			var section = this.get_parent();
			if ( section.collection.indexOf( section ) ) {
				return false;
			}

			// it's first lesson in first section
			return true;

		},

		/**
		 * Initialize lesson assignments *if* the assignments addon is availalbe and enabled
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		maybe_init_assignments: function() {

			if ( ! window.llms_builder.assignments ) {
				return;
			}

			this.relationships.children.assignment = {
				class: 'Assignment',
				conditional: function( model ) {
					// if assignment is enabled OR not enabled but we have some assignment data as an obj
					return ( 'yes' === model.get( 'assignment_enabled' ) || ! _.isEmpty( model.get( 'assignment' ) ) );
				},
				model: 'llms_assignment',
				type: 'model',
			};

		},

	}, Relationships, Utilities ) );

} );
