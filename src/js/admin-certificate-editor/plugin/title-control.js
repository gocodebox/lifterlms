import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';

import editCertificate from '../edit-certificate';

/**
 * Certificates title control component.
 *
 * @since [version]
 *
 * @param {Object} args       Component arguments.
 * @param {string} args.title Currently selected title value.
 * @return {TextControl} Control component.
 */
export default function TitleControl( { title } ) {
	return (
		<TextControl
			id="llms-certificate-title-control"
			label={ __( 'Title', 'lifterlms' ) }
			value={ title }
			onChange={ ( val ) => editCertificate( 'title', val ) }
			help={ __( 'Used as the title for certificates generated from this template.', 'lifterlms' ) }
		/>
	);
}
