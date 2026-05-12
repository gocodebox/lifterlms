/**
 * Test Student Dashboard Login
 *
 * @since 10.0.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { loginStudent, logoutUser, visitPage } from '../../utils/index.js';

test.describe( 'StudentDashboardLogin', () => {

	test.describe( 'Logged in', () => {

		test( 'should not allow a user to login if they are already logged in', async ( { admin, page } ) => {
			await admin.visitAdminPage( '/' );
			await visitPage( page, 'dashboard' );
			await expect(
				page.locator( '.llms-new-person-login-wrapper > h4.llms-form-heading' )
			).toHaveCount( 0 );
		} );

	} );

	test.describe( 'Logged out', () => {

		test( 'should display an error message when invalid credentials are used', async ( { page } ) => {
			await logoutUser( page );
			await loginStudent( page, 'fake@fake.tld', 'fake' );
			await expect(
				page.locator( '.llms-notice.llms-error li' )
			).toHaveText( 'Could not find an account with the supplied email address and password combination.' );
		} );

		test( 'should allow a user with valid credentials to login', async ( { page } ) => {
			await logoutUser( page );
			await loginStudent( page, 'validcreds@email.tld', 'password' );
			await expect( page.locator( 'h2.llms-sd-title' ) ).toHaveText( 'Dashboard' );
		} );

	} );

} );
