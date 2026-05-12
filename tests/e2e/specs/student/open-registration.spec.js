/**
 * Test Open Registration
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	logoutUser,
	registerStudent,
	select2Select,
	toggleOpenRegistration,
	visitPage,
} from '../../utils/index.js';

test.describe( 'OpenRegistration', () => {

	test.describe( 'Registration enabled', () => {

		test.beforeEach( async ( { admin, page } ) => {
			await admin.visitAdminPage( '/' );
			await toggleOpenRegistration( page, true );
		} );

		test( 'should not allow registration because user is already logged in', async ( { page } ) => {
			await visitPage( page, 'dashboard' );
			await expect(
				page.locator( '.llms-new-person-form-wrapper > h4.llms-form-heading' )
			).toHaveCount( 0 );
		} );

		test( 'should allow registration', async ( { page } ) => {
			await logoutUser( page );
			await visitPage( page, 'dashboard' );
			await expect(
				page.locator( '.llms-new-person-form-wrapper > .llms-form-heading' )
			).toHaveText( 'Register' );
		} );

		test( 'should register a new user', async ( { page } ) => {
			await registerStudent( page );
			await expect( page.locator( 'h2.llms-sd-title' ) ).toHaveText( 'Dashboard' );
		} );

	} );

	test.describe( 'Registration disabled', () => {

		test.beforeEach( async ( { admin, page } ) => {
			await admin.visitAdminPage( '/' );
			await toggleOpenRegistration( page, false );
		} );

		test( 'should not allow registration because open registration is disabled', async ( { page } ) => {
			await logoutUser( page );
			await visitPage( page, 'dashboard' );
			await expect(
				page.locator( '.llms-new-person-form-wrapper > h4.llms-form-heading' )
			).toHaveCount( 0 );
		} );

	} );

	test.describe( 'Localization', () => {

		test.beforeEach( async ( { admin, page } ) => {
			await admin.visitAdminPage( '/' );
			await toggleOpenRegistration( page, true );
		} );

		test( 'should localize city, state, and postcode fields when changing the selected country', async ( { page } ) => {
			await logoutUser( page );
			await visitPage( page, 'dashboard' );

			// China.
			await select2Select( page, '#llms_billing_country', 'China' );
			await expect( page.locator( 'label[for="llms_billing_state"]' ) ).toContainText( 'Province' );

			// United States.
			await select2Select( page, '#llms_billing_country', 'United States' );
			await expect( page.locator( 'label[for="llms_billing_state"]' ) ).toContainText( 'State' );
			await expect( page.locator( 'label[for="llms_billing_city"]' ) ).toContainText( 'City' );
			await expect( page.locator( 'label[for="llms_billing_zip"]' ) ).toContainText( 'ZIP code' );
		} );

	} );

} );
