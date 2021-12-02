import { __ } from '@wordpress/i18n';

import { ButtonGroupControl } from '@lifterlms/components';

import editCertificate from '../edit-certificate';

/**
 * Certificates orientation control component.
 *
 * @since [version]
 *
 * @param {Object} args             Component arguments.
 * @param {string} args.orientation Currently selected orientation value.
 * @return {ButtonGroupControl} Control component.
 */
export default function OrientationControl( { orientation } ) {
	const { orientations } = window.llms.certificates,
		options = Object.entries( orientations ).map( ( [ value, label ] ) => ( { value, label } ) );

	return (
		<ButtonGroupControl
			id="llms-certificate-orientation-control"
			label={ __( 'Orientation', 'lifterlms' ) }
			selected={ orientation }
			options={ options }
			onClick={ ( val ) => editCertificate( 'orientation', val ) }
		/>
	);
}
