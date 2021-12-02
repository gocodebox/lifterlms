import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { store as editorStore } from '@wordpress/editor';

import BackgroundControl from './plugin/background-control';
import MarginsControl from './plugin/margins-control';
import OrientationControl from './plugin/orientation-control';
import SizeControl from './plugin/size-control';

/**
 * Render the certificate settings editor panel.
 *
 * @since [version]
 *
 * @param {Object}   args             Component arguments.
 * @param {string}   args.background  Current background color setting.
 * @param {number}   args.height      Current height setting.
 * @param {number[]} args.margins     Current margins setting.
 * @param {string}   args.orientation Current orientation setting.
 * @param {string}   args.size        Current size setting.
 * @param {string}   args.unit        Current unit setting.
 * @param {number}   args.width       Current width setting.
 * @return {PluginDocumentSettingPanel} The component.
 */
function CertificateDocumentSettings( { background, height, margins, orientation, size, unit, width } ) {
	return (

		<PluginDocumentSettingPanel
			className="llms-certificate-doc-settings"
			name="llms-certificate-doc-settings"
			title={ __( 'Settings', 'lifterlms' ) }
			opened={ true }
		>

			<SizeControl { ...{ size, width, height, unit } } />
			<br />
			<OrientationControl { ...{ orientation } } />
			<br />
			<MarginsControl { ...{ margins, unit } } />
			<br />
			<BackgroundControl { ...{ background } } />

		</PluginDocumentSettingPanel>

	);
}

const applyWithSelect = withSelect( ( select ) => {
	const { getEditedPostAttribute } = select( editorStore );

	return {
		background: getEditedPostAttribute( 'certificate_background' ),
		height: getEditedPostAttribute( 'certificate_height' ),
		margins: getEditedPostAttribute( 'certificate_margins' ),
		orientation: getEditedPostAttribute( 'certificate_orientation' ),
		size: getEditedPostAttribute( 'certificate_size' ),
		unit: getEditedPostAttribute( 'certificate_unit' ),
		width: getEditedPostAttribute( 'certificate_width' ),
	};
} );

export default compose( [ applyWithSelect ] )(
	CertificateDocumentSettings
);
