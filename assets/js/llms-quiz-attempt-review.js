;/**
 * Quiz attempt review / grading UI & UX
 *
 * @since 3.16.0
 * @since 3.30.3 Unknown.
 * @version 11.0.0
 */( function( $ ) {

	/**
	 * Handle UX for graving quiz attempts.
	 *
	 * @since 3.16.0
	 * @since 3.30.3 Improve grading UX
	 */
	var Grading = function() {

		/**
		 * Bind DOM events
		 *
		 * @since 3.16.0
		 * @since 3.16.9 Unknown.
		 * @return {Void}
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
		 *
		 * @since 3.16.0
		 * @since 3.30.3 When starting a review only toggle first item if it's hidden and always automatically focus on the remarks field.
		 * @since 5.3.0 Exclude removed question items.
		 * @since 11.0.0 Only use top-level question items and build the fields without parsing attribute values as HTML.
		 *
		 * @return {Grading}
		 */
		function setup_fields() {

			// Only direct children of the results list: answer content must not be able to add question elements.
			$els = $( '.llms-quiz-attempt-results > .llms-quiz-attempt-question:not(.type--content):not(.type--removed)' );

			if ( $els.length < 1 ) {
				return;
			}

			var title  = LLMS.l10n.translate( 'Remarks to Student' ),
				points = LLMS.l10n.translate( 'points' );

			$els.each( function() {

				var id        = parseInt( $( this ).attr( 'data-question-id' ), 10 ),
					$existing = $( this ).children( '.llms-quiz-attempt-question-main' ).children( '.llms-quiz-attempt-answer-section.llms-remarks' ),
					$ui       = $( '<div class="llms-quiz-attempt-answer-section llms-remarks" />' ),
					$textarea = $( '<textarea class="llms-remarks-field" />' ).attr( 'name', 'remarks[' + id + ']' ),
					gradeable = ( 'yes' === $( this ).attr( 'data-grading-manual' ) );

				if ( ! id ) {
					return;
				}

				$ui.append( $( '<p class="llms-quiz-results-label remarks" />' ).text( title + ':' ) );
				$ui.append( $textarea );
				if ( gradeable ) {
					var pts = parseFloat( $( this ).attr( 'data-points-curr' ) ),
						max = parseFloat( $( this ).attr( 'data-points' ) ) || 0;
					$ui.append(
						$( '<input type="number" min="0" />' ).attr( {
							name: 'points[' + id + ']',
							max: max,
							value: isNaN( pts ) ? '' : pts,
						} ),
						document.createTextNode( ' / ' + max + ' ' + points )
					);
				}

				if ( $existing.length ) {

					$textarea.text( $existing.children( '.llms-remarks' ).text() );
					$existing.replaceWith( $ui );

				} else {

					$( this ).children( '.llms-quiz-attempt-question-main' ).append( $ui );

				}

			} );

			var $els_first = $els.first();
			if ( ! $els_first.find( '.llms-quiz-attempt-question-main' ).is( ':visible' ) ) {
				// expand the first question toggle.
				$els_first.find( '.toggle-answer' ).trigger( 'click' );
			}
			// focus on its remark textarea.
			$els_first.find( '.llms-remarks-field' ).focus();
		}

		bind();

		return this;

	};

	window.llms         = window.llms || {};
	window.llms.grading = new Grading();

} )( jQuery );
