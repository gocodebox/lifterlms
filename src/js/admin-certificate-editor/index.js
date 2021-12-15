// WP deps.
import { registerPlugin } from '@wordpress/plugins';

// Internal deps.
import './editor';
import './i18n';
import './merge-codes';
import './migrate';
import CertificateDocumentSettings from './document-settings';
import CertificateUserSettings from './user-settings';

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

/**
 * Registers the awarded certificate user selection / display control.
 *
 * @since [version]
 */
registerPlugin(
	'llms-certificate-user',
	{
		render: CertificateUserSettings,
	}
);
