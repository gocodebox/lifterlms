import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

import editCertificate from '../edit-certificate';

/**
 * Certificates next sequential id control component.
 *
 * @since 6.0.0
 *
 * @param {Object} args              Component arguments.
 * @param {string} args.sequentialId Current sequential ID.
 * @return {TextControl} Control component.
 */
export default function SequentialIdControl( { sequentialId } ) {
	const [ currId, setId ] = useState( sequentialId );

	let { minSequentialId } = window.llms.certificates;

	if ( ! minSequentialId ) {
		minSequentialId = sequentialId;
		window.llms.certificates.minSequentialId = minSequentialId;
	}

	return (
		<TextControl
			id="llms-certificate-title-control"
			label={ __( 'Next Sequential ID', 'lifterlms' ) }
			value={ currId }
			type="number"
			step="1"
			min={ minSequentialId }
			onChange={ ( val ) => {
				setId( val );
				editCertificate( 'sequential_id', val );
			} }
			help={ __( 'Used for the {sequential_id} merge code when generating a certificate from this template.', 'lifterlms' ) }
		/>
	);
}
