/**
 * LifterLMS JS Builder App Bootstrap
 *
 * @since 3.16.0
 * @since 3.37.11 Added `_.getEditor()` helper.
 * @version 5.4.0
 */
require( [
	'vendor/wp-hooks',
	'vendor/backbone.collectionView',
	'vendor/backbone.trackit',
	'Controllers/Construct',
	'Controllers/Debug',
	'Controllers/Schemas',
	'Controllers/Sync',
	'Models/loader',
	'Views/Course',
	'Views/Sidebar'
	], function(
	Hooks,
	CV,
	TrackIt,
	Construct,
	Debug,
	Schemas,
	Sync,
	Models,
	CourseView,
	SidebarView
	) {

		window.llms_builder.debug     = new Debug( window.llms_builder.debug );
		window.llms_builder.construct = new Construct();
		window.llms_builder.schemas   = new Schemas( window.llms_builder.schemas );

		/**
		 * Compare values, used by _.checked & _.selected mixins.
		 *
		 * @since 3.17.2
		 *
		 * @param {Mixed} expected expected Value, probably a string (the value of a select option or checkbox element).
		 * @param {Mixed} mixed    actual   Actual value, probably a string (the return of model.get( 'something' ) )
		 *                                  but could be an array like a multiselect.
		 * @return {Bool}
		 */
		function value_compare( expected, actual ) {
			return ( ( _.isArray( actual ) && -1 !== actual.indexOf( expected ) ) || expected == actual );
		};

		/**
		 * Underscores templating utilities
		 *
		 * @since    3.17.0
		 * @version  3.27.0
		 */
		_.mixin( {

			/**
			 * Determine if two values are equal and output checked attribute if they are.
			 *
			 * Useful for templating checkboxes & radio elements
			 * like WP Core PHP checked() but in JS.
			 *
			 * @since 3.17.0
			 * @since 3.17.2 Unknown.
			 *
			 * @param {Mixed} expected Expected element value.
			 * @param {Mixed} actual   Actual element value.
			 * @return {String}
			 */
			checked: function( expected, actual ) {
				if ( value_compare( expected, actual ) ) {
					return ' checked="checked"';
				}
				return '';
			},

			/**
			 * Recursively clone an object via _.clone().
			 *
			 * @since 3.17.7
			 *
			 * @param {Object} obj Object to clone.
			 * @return {Object}
			 */
			deepClone: function( obj ) {

				var clone = _.clone( obj );

				_.each( clone, function( val, key ) {
					if ( ! _.isFunction( val ) && _.isObject( val ) ) {
						clone[ key ] = _.deepClone( val );
					};
				} );

				return clone;

			},

			/**
			 * Retrieve the wp.editor instance.
			 *
			 * Uses `wp.oldEditor` (when available) which was added in WordPress 5.0.
			 *
			 * Falls back to `wp.editor()` which will usually be the same as `wp.oldEditor` unless
			 * the `@wordpress/editor` module has been loaded by another plugin or a theme.
			 *
			 * @since 3.37.11
			 *
			 * @return {Object}
			 */
			getEditor: function() {

				if ( undefined !== wp.oldEditor ) {

					var ed = wp.oldEditor;

					// Inline scripts added by WordPress are not ported to `wp.oldEditor`, see https://github.com/WordPress/WordPress/blob/641c632b0c9fde4e094b217f50749984ca43a2fa/wp-includes/class-wp-editor.php#L977.
					if ( undefined !== wp.editor && undefined !== wp.editor.getDefaultSettings ) {
						ed.getDefaultSettings = wp.editor.getDefaultSettings;
					}

					return ed;

				} else if ( undefined !== wp.editor && undefined !== wp.editor.autop ){

					return wp.editor;

				}

			},

			/**
			 * Strips IDs & Parent References from quizzes and all quiz questions.
			 *
			 * @since 3.24.0
			 * @since 3.27.0 Unknown.
			 * @since 5.4.0 Use author id instead of the question author object.
			 *
			 * @param {Object} quiz Raw quiz object (not a model).
			 * @return {Object}
			 */
			prepareQuizObjectForCloning: function( quiz ) {

				delete quiz.id;
				delete quiz.lesson_id;

				_.each( quiz.questions, function( question ) {

					question = _.prepareQuestionObjectForCloning( question );

				} );

				// Use author id instead of the quiz author object.
				quiz = _.prepareExistingPostObjectDataForAddingOrCloning( quiz );

				return quiz;

			},

			/**
			 * Strips IDs & Parent References from a question.
			 *
			 * @since 3.27.0
			 * @since 5.4.0 Use author id instead of the question author object.
			 *
			 * @param {Object} question Raw question object (not a model).
			 * @return {Object}
			 */
			prepareQuestionObjectForCloning: function( question ) {

				delete question.id;
				delete question.parent_id;

				if ( question.image && _.isObject( question.image ) ) {
					question.image._forceSync = true;
				}

				if ( question.choices ) {

					_.each( question.choices, function( choice ) {

						delete choice.question_id;
						delete choice.id;
						if ( 'image' === choice.choice_type && _.isObject( choice.choice ) ) {
							choice.choice._forceSync = true;
						}

					} );

				}

				// Use author id instead of the question author object.
				question = _.prepareExistingPostObjectDataForAddingOrCloning( question );

				return question;

			},

			/**
			 * Strips IDs & Parent References from assignments and all assignment tasks.
			 *
			 * @since 5.4.0
			 *
			 * @param {Object} assignment Raw assignment object (not a model).
			 * @return {Object}
			 */
			 prepareAssignmentObjectForCloning: function( assignment ) {

				delete assignment.id;
				delete assignment.lesson_id;

				// Clone tasks.
				if ( 'tasklist' === assignment.assignment_type ) {
					_.each( assignment.tasks, function( task ) {
						delete task.id;
						delete task.assignment_id;
					} );
				}

				// Use author id instead of the quiz author object.
				assignment = _.prepareExistingPostObjectDataForAddingOrCloning( assignment );

				return assignment;

			},

			/**
			 * Prepare post object data for adding or cloning.
			 *
			 * Use author id instead of the post type author object.
			 *
			 * @since 5.4.0
			 *
			 * @param {Object} quiz Raw post object (not a model).
			 * @return {Object}
			 */
			prepareExistingPostObjectDataForAddingOrCloning: function( post_data ) {

				if ( post_data.author && _.isObject( post_data.author ) && post_data.author.id ) {
					post_data.author = post_data.author.id;
				}

				return post_data;

			},

			/**
			 * Determine if two values are equal and output selected attribute if they are.
			 *
			 * Useful for templating select elements
			 * like WP Core PHP selected() but in JS.
			 *
			 *
			 * @since 3.17.0
			 * @since 3.17.2 Unknown.
			 *
			 * @param {Mixed} expected Expected element value.
			 * @param {Mixed} actual   Actual element value.
			 * @return {String}
			 */
			selected: function( expected, actual ) {
				if ( value_compare( expected, actual ) ) {
					return ' selected="selected"';
				}
				return '';
			},

			/**
			 * Generic function for stripping HTML tags from a string.
			 *
			 * @since 3.17.8
			 *
			 * @param {String} content      Raw string.
			 * @param {Array}  allowed_tags Array of allowed HTML tags.
			 * @return {String}
			 */
			stripFormatting: function( content, allowed_tags ) {

				if ( ! allowed_tags ) {
					allowed_tags = [ 'b', 'i', 'u', 'strong', 'em' ];
				}

				var $html = $( '<div>' + content + '</div>' );

				$html.find( '*' ).not( allowed_tags.join( ',' ) ).each( function( ) {

					$( this ).replaceWith( this.innerHTML );

				} );

				return $html.html();

			},

		} );

		Backbone.pubSub = _.extend( {}, Backbone.Events );

		$( document ).trigger( 'llms-builder-pre-init' );

		window.llms_builder.questions = window.llms_builder.construct.get_collection( 'QuestionTypes', window.llms_builder.questions );

		var CourseModel                 = window.llms_builder.construct.get_model( 'Course', window.llms_builder.course );
		window.llms_builder.CourseModel = CourseModel;

		window.llms_builder.sync = new Sync( CourseModel, window.llms_builder.sync );

		var Course = new CourseView( {
			model: CourseModel,
		} );

		var Sidebar = new SidebarView( {
			CourseView: Course
		} );

		$( document ).trigger( 'llms-builder-init', {
			course: Course,
			sidebar: Sidebar,
		} );

		/**
		 * Do deep linking to Lesson / Quiz / Assignments.
		 *
		 * Hash should be in the form of #lesson:{lesson_id}:{subtab}
		 * subtab can be either "quiz" or "assignment". If none found assumes the "lesson" tab.
		 *
		 * @since 3.27.0
		 * @since 3.30.1 Wait for wp.editor & window.tinymce to load before opening deep link tabs.
		 * @since 3.37.11 Use `_.getEditor()` helper when checking for the presence of `wp.editor`.
		 */
		if ( window.location.hash ) {

			var hash = window.location.hash;
			if ( -1 === hash.indexOf( '#lesson:' ) ) {
				return;
			}
			var parts = hash.replace( '#lesson:', '' ).split( ':' ),
			$lesson   = $( '#llms-lesson-' + parts[0] );

			if ( $lesson.length ) {

				LLMS.wait_for( function() {
					return ( undefined !== _.getEditor() && undefined !== window.tinymce );
                    }, function() {
					$lesson.closest( '.llms-builder-item.llms-section' ).find( 'a.llms-action-icon.expand' ).trigger( 'click' );
					var subtab = parts[1] ? parts[1] : 'lesson';
					$( '#llms-lesson-' + parts[0] ).find( 'a.llms-action-icon.edit-' + subtab ).trigger( 'click' );
				} );

			}

		}

	} );
