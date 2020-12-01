/**
 * JS from the admin setup wizard
 *
 * @since 4.8.0
 * @version 4.8.0
 */

( function() {

	var
		currStep = document.getElementById( 'llms-setup-current-step' ),
		exitLink = document.querySelector( '.llms-exit-setup' ),
		imports  = document.querySelectorAll( 'input[name="llms_setup_course_import_ids[]"]' );

	if ( imports.length ) {

		var
			submit = document.getElementById( 'llms-setup-submit' ),
			msgs   = document.querySelectorAll( '.llms-importing-msgs .llms-importing-msg' );

		/**
		 * Retrieve the number of courses to be imported
		 *
		 * @since 4.8.0
		 *
		 * @return {Integer}
		 */
		function getSelectedImportCount() {

			var count = 0;

			imports.forEach( function( el ) {
				if ( el.checked ) {
					++count;
				}
			} );

			return count;

		}

		/**
		 * Update UI when a user toggles an import on or off.
		 *
		 * @since 4.8.0
		 */
		imports.forEach( function( el ) {

			el.addEventListener( 'change', function() {

				// Hide all messages.
				msgs.forEach( function( el ) {
					el.style.display = 'none';
				} );

				var selectedCount = getSelectedImportCount();

				// If there's no courses to be imported, disable the submit button.
				submit.disabled = 0 === getSelectedImportCount() ? 'disabled' : null;

				// Show messages where applicable.
				if ( 1 === selectedCount ) {
					msgs[0].style.display = 'block';
				} else if ( selectedCount >= 2 ) {
					msgs[1].style.display = 'block';
					document.getElementById( 'llms-importing-number' ).textContent = selectedCount;
				}

			} );

		} );

		// Trigger a change event so the UI displays properly on page load.
		imports[0].dispatchEvent( new Event( 'change' ) );

		/**
		 * Start a spinner when the "Import Courses" button is clicked.
		 *
		 * @since 4.8.0
		 */
		submit.addEventListener( 'click', function( e ) {
			LLMS.Spinner.start( jQuery( submit ), 'small' );
		} );

	}


	if ( exitLink && 'finish' !== currStep.value ) {

		/**
		 * When users click "Exit Setup" prior to setup completion, open a confirmation dialog
		 *
		 * @since 4.8.0
		 */
		exitLink.addEventListener( 'click', function( e ) {
			if ( ! window.confirm( exitLink.dataset.confirm ) ) {
				e.preventDefault();
			}
		} );

	}

} )();
