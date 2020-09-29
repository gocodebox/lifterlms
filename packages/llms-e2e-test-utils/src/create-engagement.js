import url from 'url';
import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';
import { setSelect2Option } from './set-select2-option';
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new certificate
 *
 * @since [version]
 *
 * @param {Integer} engagementId WP_Post ID of the a certificate, email, or achievement post.
 * @param {Object}  args         {
 *     Optional creation arguments.
 *
 *     @type {String}  title   Engagement title.
 *     @type {String}  trigger ID of the engagement trigger event.
 *     @type {String}  type    Engagement type: certificate, email, or achievement.
 *     @type {Integer} delay   Engagement delay, in days.
 * }
 * @return {Integer} WP Post ID of the created certificate post.
 */
export async function createEngagement(
		engagementId,
		{
			title   = 'Test Engagement',
			trigger = 'user_registration',
			type    = 'certificate',
			delay   = 0
		} = {}
	) {

	await visitAdminPage( 'post-new.php', `post_type=llms_engagement&post_title=${ title }` );

	await setSelect2Option( '#_llms_trigger_type', trigger );
	await setSelect2Option( '#_llms_engagement_type', type );
	await setSelect2Option( '#_llms_engagement', engagementId.toString() );

	await fillField( '#_llms_engagement_delay', delay.toString() );

	await clickAndWait( '#publish' );

	const  url = new URL( await page.url() );
	return url.searchParams.get( 'post' );

}
