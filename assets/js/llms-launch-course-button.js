/**
 * Add Launch Course Builder button to the classic editor.
 *
 * @since Unknown
 * @version 3.35.0
 */

( function( $ ){

	var mainCourseButton = $( '.page-title-action' );

	if ( mainCourseButton.length ) {
		// Add your custom button beside the "Add Course" button
		$( '<a href="' + llms_launch_course.builder_url + '" class="page-title-action button-primary">' + LLMS.l10n.translate( 'Launch Course Builder' ) + '</a>' ).insertAfter( mainCourseButton );
	}

} )( jQuery );
