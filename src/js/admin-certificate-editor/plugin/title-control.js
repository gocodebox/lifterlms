import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as blockEditorStore } from '@wordpress/block-editor';

import editCertificate from '../edit-certificate';

/**
 * Determine if the TitleControl should be displayed.
 *
 * The control is not available for `llms_my_certificate` post types at all and is not used
 * when a `lifterlms/certificate-title` block is found in the content of an `llms_certificate`
 * post type.
 *
 * @since 6.0.0
 *
 * @param {Object}      props          Component properties.
 * @param {WPElement[]} props.children Child components.
 * @return {?WPElement[]} Child components list or `null` if the components shouldn't display.
 */
export function Check( { children } ) {
	const { postType, hasTitleBlock } = useSelect( ( select ) => ( {
		postType: select( editorStore ).getCurrentPostType(),
		hasTitleBlock: select( blockEditorStore ).getGlobalBlockCount( 'llms/certificate-title' ) > 0,
	} ), [] );

	if ( 'llms_certificate' !== postType || hasTitleBlock ) {
		return null;
	}

	return children;
}

/**
 * Certificates title control component.
 *
 * @since 6.0.0
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
