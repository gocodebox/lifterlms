import url from 'url'; // eslint-disable-line no-unused-vars
import { click } from './click';
import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';
import { createEngagement } from './create-engagement';
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new certificate
 *
 * @since 2.1.2
 *
 * @param {Object} args            Optional creation arguments.
 * @param {string} args.title      Certificate title.
 * @param {string} args.content    HTML content of the certificate.
 * @param {string} args.adminTitle Admin title.
 * @param {string} args.engagement If supplied, also creates an engagement trigger. This should be the ID of a trigger
 * @return {Object} {
 *    Object containing information about the created post(s).
 *    @type {number} certificateId WP Post ID of the created certificate post.
 *    @type {number} engagementId  WP Post ID of the created engagement post.
 * }
 */
export async function createCertificate( {
	title = 'Test Certificate',
	content = null,
	adminTitle = null,
	engagement = '',
} = {} ) {
	let engagementId;

	adminTitle = adminTitle || `${ title } Admin Title`;
	content =
		content ||
		'<p style="text-align: center;"><em>Awarded to</em></p><p style="text-align: center;">{first_name} {last_name}</p><p style="text-align: center;">on {current_date}</p>';

	await visitAdminPage(
		'post-new.php',
		`post_type=llms_certificate&post_title=${ adminTitle }`
	);

	await click( '#content-html' );
	await fillField( '#content', content );

	await fillField( '#_llms_certificate_title', title );

	await clickAndWait( '#publish' );

	const certUrl = await page.url(),
		urlObj = new URL( certUrl );

	const certificateId = urlObj.searchParams.get( 'post' );

	if ( engagement ) {
		engagementId = await createEngagement( certificateId, {
			trigger: engagement,
			type: 'certificate',
			title: `Engagement for ${ title } (ID #${ certificateId })`,
		} );

		// Return to the certificate.
		await page.goto( certUrl );
	}

	return {
		certificateId,
		engagementId,
	};
}
