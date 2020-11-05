/**
 * JS from the admin setup wizard
 *
 * @since [version]
 * @version [version
 */

( function() {

	const
		currStep = document.getElementById( 'llms-setup-current-step' ),
		exitLink = document.querySelector( '.llms-exit-setup' ),
		imports  = document.querySelectorAll( 'input[name="llms_setup_course_import_ids[]"]' );

	if ( imports ) {

		const submit = document.getElementById( 'llms-setup-submit' );

		/**
		 * When users toggle course imports, ensure that at least one course import is checked
		 *
		 * If there are no courses to be imported, disable the main submit button
		 *
		 * @since [version]
		 */
		imports.forEach( function( el ) {
			el.addEventListener( 'change', function() {

				let isImportable = false;

				imports.forEach( function( el ) {
					isImportable = el.checked ? true : isImportable;
				} );

				submit.disabled = isImportable ? null : 'disabled';

			} );
		} );

		submit.addEventListener( 'click', function( e ) {

			LLMS.Spinner.start( jQuery( submit ), 'small' );

		} );

	}


	if ( exitLink && 'finish' !== currStep.value ) {

		/**
		 * When users click "Exit Setup" prior to setup completion, open a confirmation dialog
		 *
		 * @since [version]
		 */
		exitLink.addEventListener( 'click', function( e ) {
			if ( ! window.confirm( exitLink.dataset.confirm ) ) {
				e.preventDefault();
			}
		} );

	}

} )();
