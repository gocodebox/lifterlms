/**
 * JS from the admin setup wizard
 *
 * @since [version]
 * @version [version
 */

( function() {

	var currStep = document.getElementById( 'llms-setup-current-step' ),
		exitLink = document.querySelector( '.llms-exit-setup' ),
		imports  = document.querySelectorAll( 'input[name="llms_setup_course_import_ids[]"]' );

	if ( imports ) {

		/**
		 * When users toggle course imports, ensure that at least one course import is checked
		 *
		 * If there are no courses to be imported, disable the main submit button
		 *
		 * @since [version]
		 */
		imports.forEach( function( el ) {
			el.addEventListener( 'change', function() {

				var isImportable = false;

				imports.forEach( function( el ) {
					isImportable = el.checked ? true : isImportable;
				} );

				document.getElementById( 'llms-setup-submit' ).disabled = isImportable ? null : 'disabled';

			} );
		} );

	}


	if ( exitLink && 'finish' !== currStep.value ) {

		exitLink.addEventListener( 'click', function( e ) {
			if ( ! window.confirm( exitLink.dataset.confirm ) ) {
				e.preventDefault();
			}
		} );

	}

} )();
