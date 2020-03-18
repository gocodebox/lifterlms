/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 */

import {
	// clickAndWait,
	createUser,
	loginStudent,
	logoutUser,
	visitPage,
} from '@lifterlms/llms-e2e-test-utils';

import {
	loginUser,
	visitAdminPage
} from '@wordpress/e2e-test-utils';

describe( 'StudentDashboardLogin', () => {

	afterEach( async () => {
		await logoutUser();
	} );

	it ( 'should not allow a user to login if they are already logged in.', async () => {

		await loginUser();
		await visitPage( 'dashboard' );
		await expect( await page.$( '.llms-new-person-login-wrapper > h4.llms-form-heading' ) ).toBeNull();

	} );

	it ( 'should display an error message when invalid credentials are used.', async () => {

		await loginStudent( 'fake@fake.tld', 'fake' );
		await expect( await page.$eval( '.llms-notice.llms-error li', el => el.textContent ) ).toBe( 'Could not find an account with the supplied email address and password combination.' );

	} );

	it ( 'should allow a user with valid credentials to login.', async () => {

		const student = await createUser( student );
		await logoutUser();

		await loginStudent( student.email, student.password );

		expect( await page.$eval( 'h2.llms-sd-title', el => el.textContent ) ).toBe( 'Dashboard' );

	} );

} );
