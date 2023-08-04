/**
 * Quiz Attempt
 *
 * @package LifterLMS/Scripts
 *
 * @since [version]
 * @version [version]
 */

LLMS.Quiz_Attempt = {
	/**
	 * Initialize
	 *
	 * @return void
	 */
	init: function() {

		$( '.llms-quiz-attempt-question-header a.toggle-answer' ).on( 'click', function( e ) {
			console.log('what');
			e.preventDefault();

			var $curr = $( this ).closest( 'header' ).next( '.llms-quiz-attempt-question-main' );

			$( this ).closest( 'li' ).siblings().find( '.llms-quiz-attempt-question-main' ).slideUp( 200 );

			if ( $curr.is( ':visible' ) ) {
				$curr.slideUp( 200 );
			}  else {
				$curr.slideDown( 200 );
			}

		} );
	}

}
