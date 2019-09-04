/**
 * Course Model
 *
 * @since    3.16.0
 * @version  3.24.0
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
		 * New Course Defaults
		 *
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
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
		 * Init
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

			// Sidebar "New Section" button broadcast
			Backbone.pubSub.on( 'add-new-section', this.add_section, this );

			// Sidebar "New Lesson" button broadcast
			Backbone.pubSub.on( 'add-new-lesson', this.add_lesson, this );

			Backbone.pubSub.on( 'lesson-search-select', this.add_existing_lesson, this );

		},

		/**
		 * Add an existing lesson to the course
		 * Duplicate a lesson from this or another course or attach an orphaned lesson
		 *
		 * @param    obj   lesson  lesson data obj
		 * @return   void
		 * @since    3.16.0
		 * @version  3.24.0
		 */
		add_existing_lesson: function( lesson ) {

			var data = lesson.data;

			if ( 'clone' === lesson.action ) {

				delete data.id;

				// if a quiz is attached, duplicate the quiz also
				if ( data.quiz ) {
					data.quiz                   = _.prepareQuizObjectForCloning( data.quiz );
					data.quiz._questions_loaded = true;
				}

			} else {

				data._forceSync = true;

			}

			delete data.order;
			delete data.parent_course;
			delete data.parent_section;

			this.add_lesson( data );

		},

		/**
		 * Add a new lesson to the course
		 *
		 * @param    obj   data   lesson data
		 * @return   obj          Backbone.Model of the lesson
		 * @since    3.16.0
		 * @version  3.16.0
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

			// expand the section
			section.set( '_expanded', true );

			return lesson;

		},

		/**
		 * Add a new section to the course
		 *
		 * @param    obj   data   section data
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_section: function( data ) {

			data         = data || {};
			var sections = this.get( 'sections' ),
				options  = {},
				selected = this.get_selected_section();

			// if a section is selected, add the new section after the currently selected one
			if ( selected ) {
				options.at = sections.indexOf( selected ) + 1;
			}

			sections.add( data, options );

		},

		/**
		 * Retrieve the currently selected section in the course
		 *
		 * @return   obj|undefined
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_selected_section: function() {

			return this.get( 'sections' ).find( function( model ) {
				return model.get( '_selected' );
			} );

		},

		/**
		 * Retrieve the total number of points in the course
		 *
		 * @return   int
		 * @since    3.24.0
		 * @version  3.24.0
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
