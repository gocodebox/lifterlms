/**
 * Lesson Model
 * @since    3.13.0
 * @version  [version]
 */
define( [ 'Models/Quiz', 'Models/_Relationships', 'Models/_Utilities' ], function( Quiz, Relationships, Utilities ) {

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
		 * New lesson defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.16.0
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
				video_embed: '',
				free_lesson: '',

				// other fields
				quiz: {}, // quiz model/data
				quiz_enabled: 'no',

				_forceSync: false,

			};
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.4
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

			// if the lesson ID isn't set on a quiz, set it
			var quiz = this.get( 'quiz' );
			if ( ! _.isEmpty( quiz ) && ! quiz.get( 'lesson_id' ) ) {
				quiz.set( 'lesson_id', this.get( 'id' ) );
			}

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
		 * @since    [version]
		 * @version  [version]
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
		 * Add a new quiz to the lesson
		 * @param    obj   data   object of quiz data used to construct a new quiz model
		 * @return   obj          model for the created quiz
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		add_quiz: function( data ) {

			data = data || {};

			data.lesson_id = this.id;

			if ( ! data.title ) {

				data.title = LLMS.l10n.replace( '%1$s Quiz', {
					'%1$s': this.get( 'title' ),
				} );

			}

			this.set( 'quiz', data );
			this.init_relationships();

			var quiz = this.get( 'quiz' );
			this.set( 'quiz_enabled', 'yes' );

			return quiz;

		},

		/**
		 * Determine if this is the first lesson
		 * @return   {Boolean}
		 * @since    [version]
		 * @version  [version]
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

	}, Relationships, Utilities ) );

} );
