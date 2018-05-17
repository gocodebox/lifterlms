/**
 * Quiz attempt review / grading UI & UX
 * @since    3.16.0
 * @version  3.16.9
 */
;( function( $ ) {

	var Grading = function() {

		/**
		 * Bind DOM events
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.9
		 */
		function bind() {

			$( 'button[name="llms_quiz_attempt_action"][value="llms_attempt_grade"]' ).one( 'click', function( e ) {

				e.preventDefault();

				$( this ).addClass( 'grading' );

				setup_fields();

			} );

		}

		/**
		 * Create editable fields for grading / remarking
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function setup_fields() {

			$els = $( '.llms-quiz-attempt-question:not(.type--content)' );

			var title = LLMS.l10n.translate( 'Remarks to Student' ),
				points = LLMS.l10n.translate( 'points' );

			$els.each( function() {

				var id = $( this ).attr( 'data-question-id' ),
					$existing = $( this ).find( '.llms-quiz-attempt-answer-section.llms-remarks' ),
					$ui = $( '<div class="llms-quiz-attempt-answer-section llms-remarks" />' ),
					$textarea = $( '<textarea class="llms-remarks-field" name="remarks[' + id + ']"></textarea>' )
					gradeable = ( 'yes' === $( this ).attr( 'data-grading-manual' ) );

				$ui.append( '<p class="llms-quiz-results-label remarks">' + title + ':</p>')
				$ui.append( $textarea );
				if ( gradeable ) {
					var pts = $( this ).attr( 'data-points-curr' ),
						max = $( this ).attr( 'data-points' );
					$ui.append( '<input name="points[' + id + ']" max="' + max + '" min="0" type="number" value="' + pts + '"> / ' + max + ' ' + points );
				}

				if ( $existing.length ) {

					$textarea.text( $existing.find( '.llms-remarks' ).text() );
					$existing.replaceWith( $ui );

				} else {

					$( this ).find( '.llms-quiz-attempt-question-main' ).append( $ui );

				}


			} );


			$els.first().find( '.toggle-answer' ).trigger( 'click' );

		}

		bind();

		return this;

	};

	window.llms = window.llms || {};
	window.llms.grading = new Grading();


} )( jQuery );
