// WP deps.
import { registerPlugin } from '@wordpress/plugins';

// Internal deps.
import './editor';
import './i18n';
import './merge-codes';
import './migrate';
import './modify-blocks';
import './notices';
import CertificateDocumentSettings from './document-settings';
import CertificatePostStatusInfo from './post-status-info';
import CertificateUserSettings from './user-settings';

/**
 * Register the document settings plugin with the block editor.
 *
 * @since 6.0.0
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
 * @since 6.0.0
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
 * @since 6.0.0
 */
registerPlugin(
	'llms-certificate-post-status-info',
	{
		render: CertificatePostStatusInfo,
	}
);
