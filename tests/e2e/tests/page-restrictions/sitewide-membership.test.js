/**
 * Test restrictions when a sitewide membership is enabled.
 *
 * @since [version]
 */

import {
	clickAndWait,
	createMembership,
	setSelect2Option,
	visitSettingsPage,
} from '@lifterlms/llms-e2e-test-utils';

import {
	loginUser,
	visitAdminPage
} from '@wordpress/e2e-test-utils';

describe( 'SitewideMembershipRestrictions', () => {

	// beforeAll( async () => {
	// 	const membership_id = await createMembership( 'Sitewide Membership' );
	// 	await visitSettingsPage( { tab: 'memberships' } );
	// 	await setSelect2Option( '#lifterlms_membership_required', membership_id );
	// 	await clickAndWait( '.llms-save .llms-button-primary' );
	// } );

	it ( 'should not allow logged out users to view the homepage', async () => {
	} );

} );
