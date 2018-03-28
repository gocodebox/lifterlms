/**
 * LifterLMS JS Builder App Bootstrap
 * @since    3.16.0
 * @version  3.17.0
 */
require( [
	'../vendor/backbone.collectionView',
	'../vendor/backbone.trackit',
	'Controllers/Construct',
	'Controllers/Debug',
	'Controllers/Schemas',
	'Controllers/Sync',
	'Models/loader',
	'Views/Course',
	'Views/Sidebar'
], function(
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
	 * Underscores templating utilities
	 * @since    3.17.0
	 * @version  3.17.0
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
		 * @version  3.17.0
		 */
		checked: function( expected, actual ) {
			if ( expected == actual ) {
				return ' checked="checked"';
			}
			return '';
		},

		/**
		 * Determine if two values are equal and output seleted attribute if they are
		 * Useful for templating select elements
		 * Like WP Core PHP selected() but in JS
		 * @param    mixed   expected  expected element value
		 * @param    mixed   actual    actual element value
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		selected: function( expected, actual ) {
			if ( expected == actual ) {
				return ' selected="selected"';
			}
			return '';
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

} );
