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

	window.llms_builder.questions = window.llms_builder.construct.get_collection( 'QuestionTypes', window.llms_builder.questions );


	var CourseModel = window.llms_builder.construct.get_model( 'Course', window.llms_builder.course );
	window.llms_builder.CourseModel = CourseModel;
	console.log( CourseModel );

	window.llms_builder.sync = new Sync( CourseModel, window.llms_builder.sync );

	var Course = new CourseView( {
		model: CourseModel,
	} );

	new SidebarView( {
		CourseView: Course
	} );



	setTimeout( function() {

		$( '#llms-sections a[href="#llms-toggle"]' ).first().trigger( 'click' );

		setTimeout( function() {

			$( '.llms-lesson' ).first().find( '.edit-quiz' ).trigger( 'click' );

			// setTimeout( function() {

			// 	$( '#llms-enable-quiz' ).trigger( 'click' );

			// 	setTimeout( function() {

			// 		var i = 0;
			// 		while ( i <= 5 ) {

			// 			setTimeout( function() {

			// 				$( '#llms-show-question-bank' ).trigger( 'click' );

			// 				setTimeout( function() {

			// 					var $btns = $( 'button.llms-add-question' );
			// 					$btns.eq( _.random( 0, $btns.length - 1 ) ).trigger( 'click' );

			// 				}, 100 );

			// 			}, i * 150 );

			// 			i++;

			// 		}

			// 	}, 100 );


			// }, 500 );

		}, 100 );

	}, 100 );


	// add_test_sections();

	// console.log( CourseModel.get( 'sections' )[0].get( 'lessons' )[0] );

} );
