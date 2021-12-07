// WP deps.
import { registerPlugin } from '@wordpress/plugins';

// Internal deps.
import './editor';
import './merge-codes';
import CertificateDocumentSettings from './document-settings';

/**
 * Register the document settings plugin with the block editor.
 *
 * @since [version]
 */
registerPlugin(
	'llms-certificate-doc-settings',
	{
		render: CertificateDocumentSettings,
		icon: '',
	}
);
