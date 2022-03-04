import { __ } from '@wordpress/i18n';

/**
 * Retrieves the message based on the selectors present in the modal.
 *
 * @since [version]
 *
 * @param {boolean} selectStudent  Whether or not the student selector is present.
 * @param {boolean} selectTemplate Whether or not the template selector is present.
 * @return {string} A message string.
 */
export default function( selectStudent, selectTemplate ) {
	let msg = '';
	if ( selectStudent && selectTemplate ) {
		msg = __( 'Create a new certificate award from the selected template for the selected student.', 'lifterlms' );
	} else if ( selectStudent && ! selectTemplate ) {
		msg = __( 'Create a new certificate award from this template for the selected student.', 'lifterlms' );
	} else if ( ! selectStudent && selectTemplate ) {
		msg = __( 'Create a new certificate award from the selected template for this student.', 'lifterlms' );
	}
	return msg;
}
