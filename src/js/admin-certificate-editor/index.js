// WP deps.
import { registerPlugin } from '@wordpress/plugins';

// Internal deps.
import './editor';
import './i18n';
import './merge-codes';
import './migrate';
import './modify-blocks';
import CertificateDocumentSettings from './document-settings';
import CertificateResetTemplate from './reset-template';
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

/**
 * Registers the certificate default template reset button.
 *
 * @since [version]
 */
registerPlugin(
	'llms-certificate-post-status-info',
	{
		render: CertificateResetTemplate,
	}
);
