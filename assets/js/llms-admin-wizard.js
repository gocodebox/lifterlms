/**
 * JS from the admin setup wizard
 *
 * @since 4.8.0
 * @version 4.8.0
 */

( function() {
	const
		currStep       = document.getElementById( 'llms-setup-current-step' ),
		exitLink       = document.querySelector( '.llms-exit-setup' ),
		imports        = document.querySelectorAll( 'input[name="llms_setup_course_import_ids[]"]' ),
		checkboxToggle = document.getElementsByClassName( 'llms-checkbox-toggle' )[ 0 ] ?? null;

	if ( imports.length ) {
		const
			submit = document.getElementById( 'llms-setup-submit' ),
			msgs   = document.querySelectorAll( '.llms-importing-msgs .llms-importing-msg' );

		/**
		 * Retrieve the number of courses to be imported
		 *
		 * @since 4.8.0
		 *
		 * @return {Number} The number of courses to be imported.
		 */
		function getSelectedImportCount() {
			let count = 0;

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
				msgs.forEach( function( msg ) {
					msg.style.display = 'none';
				} );

				const selectedCount = getSelectedImportCount();

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
		submit.addEventListener( 'click', function() {
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

	if ( checkboxToggle ) {
		checkboxToggle.addEventListener( 'click', function() {
			const hiddenFields = this.parentNode.parentNode.querySelectorAll( '.is-hidden,.is-visible' );

			for ( let i = 0; i < hiddenFields.length; i++ ) {
				hiddenFields[ i ].classList.toggle( 'is-visible' );
				hiddenFields[ i ].classList.toggle( 'is-hidden' );
			}
		} );
	}
}() );
