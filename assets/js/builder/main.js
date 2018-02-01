require( [
	'../vendor/backbone.collectionView',
	'../vendor/backbone-forms',
	'../vendor/backbone.trackit',
	'Controllers/Construct',
	'Controllers/Debug',
	'Controllers/Sync',
	'Models/loader',
	'Views/Editors/wysiwyg',
	'Views/Course',
	'Views/Sidebar'
], function(
	Forms,
	CV,
	TrackIt,
	Construct,
	Debug,
	Sync,
	Models,
	WysiwygEditor,
	CourseView,
	SidebarView
) {

	window.llms_builder.debug = new Debug( window.llms_builder.debug );
	window.llms_builder.construct = new Construct();

	// register custom backbone forms editor
	Backbone.Form.editors.Wysiwyg = WysiwygEditor;

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
