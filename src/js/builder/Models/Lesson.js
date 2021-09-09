/**
 * Lesson Model
 *
 * @since 3.13.0
 * @version 4.20.0
 */
define( [ 'Models/Quiz', 'Models/_Relationships', 'Models/_Utilities', 'Schemas/Lesson' ], function( Quiz, Relationships, Utilities, LessonSchema ) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * Model relationships
		 *
		 * @type {Object}
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
		 *
		 * @type {Object}
		 */
		schema: LessonSchema,

		/**
		 * New lesson defaults
		 *
		 * @since 3.13.0
		 * @since 3.24.0 Unknown.
		 *
		 * @return {Object} Default options associative array (js object).
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				title: LLMS.l10n.translate( 'New Lesson' ),
				type: 'lesson',
				order: this.collection ? this.collection.length + 1 : 1,
				parent_course: window.llms_builder.course.id,
				parent_section: '',

				// Urls.
				edit_url: '',
				view_url: '',

				// Editable fields.
				content: '',
				audio_embed: '',
				has_prerequisite: 'no',
				require_passing_grade: 'yes',
				require_assignment_passing_grade: 'yes',
				video_embed: '',
				free_lesson: '',
				points: 1,

				// Other fields.
				assignment: {}, // Assignment model/data.
				assignment_enabled: 'no',

				quiz: {}, // Quiz model/data.
				quiz_enabled: 'no',

				_forceSync: false,

			};
		},

		/**
		 * Initializer
		 *
		 * @since 3.16.0
		 * @since 3.17.0 Unknown
		 *
		 * @return {void}
		 */
		initialize: function() {

			this.init_custom_schema();
			this.startTracking();
			this.maybe_init_assignments();
			this.init_relationships();

			// If the lesson ID isn't set on a quiz, set it.
			var quiz = this.get( 'quiz' );
			if ( ! _.isEmpty( quiz ) && ! quiz.get( 'lesson_id' ) ) {
				quiz.set( 'lesson_id', this.get( 'id' ) );
			}

			window.llms.hooks.doAction( 'llms_lesson_model_init', this );

		},

		/**
		 * Retrieve a reference to the parent course of the lesson
		 *
		 * @since 3.16.0
		 * @since 4.14.0 Use Section.get_course() in favor of Section.get_parent().
		 *
		 * @return {Object} The parent course model of the lesson.
		 */
		get_course: function() {
			return this.get_parent().get_course();
		},

		/**
		 * Retrieve the translated post type name for the model's type
		 *
		 * @since  3.16.12
		 *
		 * @param bool plural If true, returns the plural, otherwise returns singular.
		 * @return string The translated post type name.
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'lessons' );
			}

			return LLMS.l10n.translate( 'lesson' );
		},

		/**
		 * Override default get_parent to grab from collection if models parent isn't set
		 *
		 * @since 3.17.0
		 *
		 * @return {Object}|false The parent model or false if not available.
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
		 *
		 * @since 3.24.0
		 *
		 * @return {String} Questions percentage value within the quiz.
		 */
		get_points_percentage: function() {

			var total  = this.get_course().get_total_points(),
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
		 *
		 * @since 3.17.0
		 *
		 * @return {Object} Prerequisite options.
		 */
		get_available_prereq_options: function() {

			var parent_section_index    = this.get_parent().collection.indexOf( this.get_parent() ),
				lesson_index_in_section = this.collection.indexOf( this ),
				options                 = [];

			this.get_course().get( 'sections' ).each( function( section, curr_sec_index ) {
				if ( curr_sec_index <= parent_section_index ) {
					var group = {
							// Translators: %1$d = section order number, %2$s = section title.
						label: LLMS.l10n.replace( 'Section %1$d: %2$s', {
							'%1$d': section.get( 'order' ),
							'%2$s': section.get( 'title' )
						} ),
					options: [],
					};

					section.get( 'lessons' ).each( function( lesson, curr_les_index ) {
						if ( curr_sec_index !== parent_section_index || curr_les_index < lesson_index_in_section ) {
							// Translators: %1$d = lesson order number, %2$s = lesson title.
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
		 *
		 * @since 3.16.0
		 * @since 3.27.0 Unknown.
		 *
		 * @param {Object} data Object of quiz data used to construct a new quiz model.
		 * @return {Object} Model for the created quiz.
		 */
		add_quiz: function( data ) {

			data = data || {};

			data.lesson_id         = this.id;
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
		 *
		 * @since 3.17.0
		 * @since 4.20.0 Use is_first_in_section() new method.
		 *
		 * @return {Boolean} Whether this is the first lesson of its course.
		 */
		is_first_in_course: function() {

			// If it's not the first item in the section it cant be the first lesson.
			if ( ! this.is_first_in_section() ) {
				return false;
			}

			// If it's not the first section it cant' be first lesson.
			var section = this.get_parent();
			if ( section.collection.indexOf( section ) ) {
				return false;
			}

			// It's first lesson in first section.
			return true;

		},

		/**
		 * Determine if this is the last lesson of the course
		 *
		 * @since 4.20.0
		 *
		 * @return {Boolean} Whether this is the last lesson of its course.
		 */
		 is_last_in_course: function() {

			// If it's not last item in the section it cant be the last lesson.
			if ( ! this.is_last_in_section() ) {
				return false;
			}

			// If it's not the last section it cant' be last lesson.
			var section = this.get_parent();
			if ( section.collection.indexOf( section ) < ( section.collection.size() - 1 ) ) {
				return false;
			}

			// It's last lesson in last section.
			return true;

		},

		/**
		 * Determine if this is the first lesson within its section
		 *
		 * @since 4.20.0
		 *
		 * @return {Boolean} Whether this is the first lesson of its section.
		 */
		is_first_in_section: function() {
			return 0 === this.collection.indexOf( this );
		},

		/**
		 * Determine if this is the last lesson within its section
		 *
		 * @since 4.20.0
		 *
		 * @return {Boolean} Whether this is the last lesson of its section.
		 */
		is_last_in_section: function() {
			return this.collection.indexOf( this ) === ( this.collection.size() - 1 );
		},

		/**
		 * Get prev lesson in a course
		 *
		 * @since 4.20.0
		 *
		 * @param {String} status Prev lesson post status. If not specified any status will be taken into account.
		 * @return {Object}|false Previous lesson model or `false` if no previous lesson could be found.
		 */
		get_prev: function( status ) {
			return this.get_sibling( 'prev', status );
		},

		/**
		 * Get next lesson in a course
		 *
		 * @since 4.20.0
		 *
		 * @param {String} status Next lesson post status. If not specified any status will be taken into account.
		 * @return {Object}|false Next lesson model or `false` if no next lesson could be found.
		 */
		get_next: function( status ) {
			return this.get_sibling( 'next', status );
		},

		/**
		 * Get a sibling lesson
		 *
		 * @param {String} direction Siblings direction [next|prev]. If not specified will fall back on 'prev'.
		 * @param {String} status    Sibling lesson post status. If not specified any status will be taken into account.
		 * @return {Object}|false Sibling lesson model, in the specified direction, or `false` if no sibling lesson could be found.
		 */
		get_sibling: function( direction, status ) {

			direction = 'next' === direction ? direction : 'prev';

			// Functions and vars to use when direction is 'prev' (default).
			var is_course_limit_reached_f               = 'is_first_in_course',
				is_section_limit_reached_f              = 'is_first_in_section',
				sibling_index_increment                 = -1,
				get_sibling_lesson_in_sibling_section_f = 'last';

			if ( 'next' === direction ) {
				is_course_limit_reached_f               = 'is_last_in_course';
				is_section_limit_reached_f              = 'is_last_in_section';
				sibling_index_increment                 = 1,
				get_sibling_lesson_in_sibling_section_f = 'first';
			}

			if ( this[ is_course_limit_reached_f ]() ) {
				return false;
			}

			var sibling_index  = this.collection.indexOf( this ) + sibling_index_increment,
				sibling_lesson = this.collection.at( sibling_index );

			if ( this[ 'next' === direction ? 'is_last_in_section' : 'is_first_in_section' ]() ) {
				var cur_section     = this.get_parent(),
					sibling_section = cur_section[ 'get_' + direction ]( false );

				// Skip sibling empty sections.
				while ( sibling_section && ! sibling_section.get( 'lessons' ).size() ) {
					sibling_section = sibling_section[ 'get_' + direction ]( false );
				}

				// Couldn't find any suitable lesson.
				if ( ! sibling_section || ! sibling_section.get( 'lessons' ).size() ) {
					return false;
				}

				sibling_lesson = sibling_section.get( 'lessons' )[ get_sibling_lesson_in_sibling_section_f ]();

			}

			// If we need a specific lesson status.
			if ( status && status !== sibling_lesson.get( 'status' ) ) {
				return sibling_lesson.get_sibling( direction, status );
			}

			return sibling_lesson;

		},

		/**
		 * Initialize lesson assignments *if* the assignments addon is available and enabled
		 *
		 * @since 3.17.0
		 *
		 * @return {Void}
		 */
		maybe_init_assignments: function() {

			if ( ! window.llms_builder.assignments ) {
				return;
			}

			this.relationships.children.assignment = {
				class: 'Assignment',
				conditional: function( model ) {
					// If assignment is enabled OR not enabled but we have some assignment data as an obj.
					return ( 'yes' === model.get( 'assignment_enabled' ) || ! _.isEmpty( model.get( 'assignment' ) ) );
				},
				model: 'llms_assignment',
				type: 'model',
			};

		},

	}, Relationships, Utilities ) );

} );
