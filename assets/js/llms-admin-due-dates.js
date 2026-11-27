/**
 * Per-student due dates table on the student course reporting screen.
 *
 * Each "Change" button shows and hides its form and moves focus into it; Escape closes the form and returns
 * focus to the button. "No due date for this student" disables the date and time fields. After a save, focus
 * moves to the result notice so screen readers announce it.
 *
 * @since [version]
 */
( function() {

	document.querySelectorAll( '.llms-due-dates-toggle' ).forEach( function( button ) {

		var form = document.getElementById( button.getAttribute( 'aria-controls' ) ),
			none = form.querySelector( '[name="llms_due_none"]' );

		function toggle( open ) {
			button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			form.hidden = ! open;
			if ( open ) {
				form.querySelector( 'input:not([type="hidden"]):not(:disabled)' ).focus();
			}
		}

		function syncNone() {
			form.querySelectorAll( '[name="llms_due_date"], [name="llms_due_time"]' ).forEach( function( input ) {
				input.disabled = none.checked;
			} );
		}

		button.addEventListener( 'click', function() {
			toggle( form.hidden );
		} );

		form.addEventListener( 'keydown', function( event ) {
			if ( 'Escape' === event.key ) {
				toggle( false );
				button.focus();
			}
		} );

		none.addEventListener( 'change', syncNone );
		syncNone();

	} );

	var notice = document.getElementById( 'llms-due-dates-notice' );
	if ( notice ) {
		notice.focus();
	}

} )();
