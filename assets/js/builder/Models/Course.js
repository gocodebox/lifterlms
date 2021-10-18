/**
 * Course Model.
 *
 * @since 3.16.0
 * @since 3.24.0 Added `get_total_points()` method.
 * @since 3.37.11 Use lesson author ID instead of author object when adding existing lessons to a course.
 * @version 5.4.0
 */
define( [ 'Collections/Sections', 'Models/_Relationships', 'Models/_Utilities' ], function( Sections, Relationships, Utilities ) {

	return Backbone.Model.extend( _.defaults( {

		relationships: {
			children: {
				sections: {
					class: 'Sections',
					model: 'section',
					type: 'collection',
				},
			}
		},

		/**
		 * New Course Defaults.
		 *
		 * @since 3.16.0
		 *
		 * @return {Object}
		 */
		defaults: function() {
			return {
				edit_url: '',
				sections: [],
				title: 'New Course',
				type: 'course',
				view_url: '',
			}
		},

		/**
		 * Init.
		 *
		 * @since 3.16.0
		 *
		 * @return {Void}
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

			// Sidebar "New Section" button broadcast.
			Backbone.pubSub.on( 'add-new-section', this.add_section, this );

			// Sidebar "New Lesson" button broadcast.
			Backbone.pubSub.on( 'add-new-lesson', this.add_lesson, this );

			Backbone.pubSub.on( 'lesson-search-select', this.add_existing_lesson, this );

		},

		/**
		 * Add an existing lesson to the course.
		 *
		 * Duplicate a lesson from this or another course or attach an orphaned lesson.
		 *
		 * @since 3.16.0
		 * @since 3.24.0 Unknown.
		 * @since 3.37.11 Use the author id instead of the author object.
		 * @since 5.4.0 Added filter hook 'llms_adding_existing_lesson_data'.
		 *               On cloning, duplicate assignments too, if assignment add-on active and assignment attached.
		 *
		 * @param {Object} lesson Lesson data obj.
		 * @return {Void}
		 */
		add_existing_lesson: function( lesson ) {

			var data = lesson.data;

			if ( 'clone' === lesson.action ) {

				delete data.id;

				// If a quiz is attached, duplicate the quiz also.
				if ( data.quiz ) {
					data.quiz                   = _.prepareQuizObjectForCloning( data.quiz );
					data.quiz._questions_loaded = true;
				}

				// If assignment add-on active and assignment attached, duplicate the assignment too.
				if ( window.llms_builder.assignments && data.assignment ) {
					data.assignment = _.prepareAssignmentObjectForCloning( data.assignment );
				}

			} else {

				data._forceSync = true;

			}

			delete data.order;
			delete data.parent_course;
			delete data.parent_section;

			// Use author id instead of the lesson author object.
			data = _.prepareExistingPostObjectDataForAddingOrCloning( data );

			/**
			 * Filters the data of the existing lesson being added.
			 *
			 * @since 5.4.0
			 *
			 * @param {Object} data   Lesson data.
			 * @param {String} action Action being performed. [clone|attach].
			 * @param {Object} course The lesson's course parent model.
			 */
			data = window.llms.hooks.applyFilters( 'llms_adding_existing_lesson_data', data, lesson.action, this );

			this.add_lesson( data );

		},

		/**
		 * Add a new lesson to the course.
		 *
		 * @since 3.16.0
		 *
		 * @param {Object} data Lesson data.
		 * @return {Object} Backbone.Model of the lesson.
		 */
		add_lesson: function( data ) {

			data        = data || {};
			var options = {},
				section;

			if ( ! data.parent_section ) {
				section = this.get_selected_section();
				if ( ! section ) {
					section = this.get( 'sections' ).last();
				}
			} else {
				section = this.get( 'sections' ).get( data.parent_section );
			}

			data._selected = true;

			data.parent_course = this.get( 'id' );

			var lesson = section.add_lesson( data, options );
			Backbone.pubSub.trigger( 'new-lesson-added', lesson );

			// Expand the section.
			section.set( '_expanded', true );

			return lesson;

		},

		/**
		 * Add a new section to the course.
		 *
		 * @since 3.16.0
		 *
		 * @param {Object} data Section data.
		 * @return {Void}
		 */
		add_section: function( data ) {

			data         = data || {};
			var sections = this.get( 'sections' ),
				options  = {},
				selected = this.get_selected_section();

			// If a section is selected, add the new section after the currently selected one.
			if ( selected ) {
				options.at = sections.indexOf( selected ) + 1;
			}

			sections.add( data, options );

		},

		/**
		 * Retrieve the currently selected section in the course.
		 *
		 * @since 3.16.0
		 *
		 * @return {Object|undefined}
		 */
		get_selected_section: function() {

			return this.get( 'sections' ).find( function( model ) {
				return model.get( '_selected' );
			} );

		},

		/**
		 * Retrieve the total number of points in the course.
		 *
		 * @since 3.24.0
		 *
		 * @return {Integer}
		 */
		get_total_points: function() {

			var points = 0;

			this.get( 'sections' ).each( function( section ) {
				section.get( 'lessons' ).each( function( lesson ) {
					var lesson_points = lesson.get( 'points' );
					if ( ! _.isNumber( lesson_points ) ) {
						lesson_points = 0;
					}
					points += lesson_points * 1;
				} );
			} );

			return points;

		},

	}, Relationships, Utilities ) );

} );
