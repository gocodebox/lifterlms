/**
 * LifterLMS JS Builder App Bootstrap
 * @since    3.16.0
 * @version  3.27.0
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

	window.llms_builder.debug = new Debug( window.llms_builder.debug );
	window.llms_builder.construct = new Construct();
	window.llms_builder.schemas = new Schemas( window.llms_builder.schemas );

	/**
	 * Compare values, used by _.checked & _.selected mixins
	 * @param    mixed   expected  expected value, probably a string (the value of a select option or checkbox element)
	 * @param    mixed   actual    actual value, probably a string (the return of model.get( 'something' ) )
	 *                             				 but could be an array like a multiselect
	 * @return   boolean
	 * @since    3.17.2
	 * @version  3.17.2
	 */
	function value_compare( expected, actual ) {
		return ( ( _.isArray( actual ) && -1 !== actual.indexOf( expected ) ) || expected == actual );
	};

	/**
	 * Underscores templating utilities
	 * @since    3.17.0
	 * @version  3.27.0
	 */
	_.mixin( {

		/**
		 * Determine if two values are equal and output checked attribute if they are
		 * Useful for templating checkboxes & radio elements
		 * Like WP Core PHP checked() but in JS
		 * @param    mixed   expected  expected element value
		 * @param    mixed   actual    actual element value
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		checked: function( expected, actual ) {
			if ( value_compare( expected, actual ) ) {
				return ' checked="checked"';
			}
			return '';
		},

		/**
		 * Recursively clone an object via _.clone()
		 * @param    obj   obj  object to clone
		 * @return   obj
		 * @since    3.17.7
		 * @version  3.17.7
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
		 * Strips IDs & Parent References from quizzes and all quiz questions
		 * @param    obj   quiz   raw quiz object (not a model)
		 * @return   obj
		 * @since    3.24.0
		 * @version  3.27.0
		 */
		prepareQuizObjectForCloning: function( quiz ) {

			delete quiz.id;
			delete quiz.lesson_id;

			_.each( quiz.questions, function( question ) {

				question = _.prepareQuestionObjectForCloning( question );

			} );

			return quiz;

		},

		/**
		 * Strips IDs & Parent References from a question
		 * @param    obj   question   raw question object (not a model).
		 * @return   obj
		 * @since    3.27.0
		 * @version  3.27.0
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

			return question;

		},

		/**
		 * Determine if two values are equal and output seleted attribute if they are
		 * Useful for templating select elements
		 * Like WP Core PHP selected() but in JS
		 * @param    mixed   expected  expected element value
		 * @param    mixed   actual    actual element value
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		selected: function( expected, actual ) {
			if ( value_compare( expected, actual ) ) {
				return ' selected="selected"';
			}
			return '';
		},

		/**
		 * Generic function for stripping HTML tags from a string
		 * @param    string   content       raw string
		 * @param    array   allowed_tags  array of allowed HTML tags
		 * @return   string
		 * @since    3.17.8
		 * @version  3.17.8
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

	var CourseModel = window.llms_builder.construct.get_model( 'Course', window.llms_builder.course );
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
	 * Do deeplinking to Lesson / Quiz / Assignments
	 * Hash should be in the form of #lesson:{lesson_id}:{subtab}
	 * subtab can be either "quiz" or "assignment". If none found assumes the "lesson" tab
	 * @since   3.27.0
	 * @version 3.27.0
	 */
	if ( window.location.hash ) {
		var hash = window.location.hash;
		if ( -1 === hash.indexOf( '#lesson:' ) ) {
			return;
		}
		var parts = hash.replace( '#lesson:', '' ).split( ':' ),
			$lesson = $( '#llms-lesson-' + parts[0] );

		if ( $lesson.length ) {
			$lesson.closest( '.llms-builder-item.llms-section' ).find( 'a.llms-action-icon.expand' ).trigger( 'click' );
			var subtab = parts[1] ? parts[1] : 'lesson';
			$( '#llms-lesson-' + parts[0] ).find( 'a.llms-action-icon.edit-' + subtab ).trigger( 'click' );
		}

	}

} );
