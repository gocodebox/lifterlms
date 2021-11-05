import url from 'url'; // eslint-disable-line no-unused-vars
import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';
import { setSelect2Option } from './set-select2-option';
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new certificate
 *
 * @since 2.1.2
 *
 * @param {number} engagementId WP_Post ID of the a certificate, email, or achievement post.
 * @param {Object} args         Optional creation arguments.
 * @param {string} args.title   Engagement title.
 * @param {string} args.trigger ID of the engagement trigger event.
 * @param {string} args.type    Engagement type: certificate, email, or achievement.
 * @param {number} args.delay   Engagement delay, in days.
 * @return {number} WP Post ID of the created certificate post.
 */
export async function createEngagement(
	engagementId,
	{
		title = 'Test Engagement',
		trigger = 'user_registration',
		type = 'certificate',
		delay = 0,
	} = {}
) {
	await visitAdminPage(
		'post-new.php',
		`post_type=llms_engagement&post_title=${ title }`
	);

	await setSelect2Option( '#_llms_trigger_type', trigger );
	await setSelect2Option( '#_llms_engagement_type', type );
	await setSelect2Option( '#_llms_engagement', engagementId.toString() );

	await fillField( '#_llms_engagement_delay', delay.toString() );

	await clickAndWait( '#publish' );

	const currUrl = new URL( await page.url() );
	return currUrl.searchParams.get( 'post' );
}
