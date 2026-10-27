/**
 * Quiz Attempt
 *
 * @package LifterLMS/Scripts
 *
 * @since 7.3.0
 * @since 10.1.0 Toggle aria-expanded and screen-reader labels on answer details.
 * @version 10.1.0
 */

LLMS.Quiz_Attempt = {
	/**
	 * Initialize
	 *
	 * @return void
	 */
	init: function() {

		$( '.llms-quiz-attempt-question-header .toggle-answer' ).on( 'click', function( e ) {

			e.preventDefault();

			var $btn      = $( this ),
				$curr     = $btn.closest( 'header' ).next( '.llms-quiz-attempt-question-main' ),
				$siblings = $btn.closest( 'li' ).siblings(),
				expand    = $btn.attr( 'data-label-expand' ) || '',
				collapse  = $btn.attr( 'data-label-collapse' ) || '';

			$siblings.find( '.llms-quiz-attempt-question-main' ).slideUp( 200 );
			$siblings.find( '.toggle-answer' ).attr( 'aria-expanded', 'false' )
				.find( '.llms-toggle-answer-text' ).text( expand );

			if ( $curr.is( ':visible' ) ) {
				$curr.slideUp( 200 );
				$btn.attr( 'aria-expanded', 'false' );
				$btn.find( '.llms-toggle-answer-text' ).text( expand );
			} else {
				$curr.slideDown( 200 );
				$btn.attr( 'aria-expanded', 'true' );
				$btn.find( '.llms-toggle-answer-text' ).text( collapse );
			}

		} );
	}

}
