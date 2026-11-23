/**
 * Student dashboard Edit Account email and password toggles.
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { loginStudent, logoutUser, visitPage } from '../../utils/index.js';

const EMAIL_TOGGLE = 'a.llms-toggle-fields[data-fields^="#email_address"]';
const PASSWORD_TOGGLE = 'a.llms-toggle-fields[data-fields^="#password,"]';

test.describe( 'EditAccountToggles', () => {

	test.beforeEach( async ( { page } ) => {
		await logoutUser( page );
		await loginStudent( page, 'validcreds@email.tld', 'password' );
		await visitPage( page, 'dashboard/edit-account' );
	} );

	test( 'keeps Current Password usable when the password change is cancelled during an email change', async ( { page } ) => {
		await page.locator( EMAIL_TOGGLE ).click();
		await page.locator( PASSWORD_TOGGLE ).click();
		await page.locator( PASSWORD_TOGGLE ).click();

		await expect( page.locator( '#password_current' ) ).toBeVisible();
		await expect( page.locator( '#password_current' ) ).toBeEnabled();
		await expect( page.locator( '#password' ) ).toBeHidden();
		await expect( page.locator( '#password' ) ).toBeDisabled();
	} );

	test( 'keeps Current Password usable when the email change is cancelled during a password change', async ( { page } ) => {
		await page.locator( PASSWORD_TOGGLE ).click();
		await page.locator( EMAIL_TOGGLE ).click();
		await page.locator( EMAIL_TOGGLE ).click();

		await expect( page.locator( '#password_current' ) ).toBeVisible();
		await expect( page.locator( '#password_current' ) ).toBeEnabled();
		await expect( page.locator( '#email_address' ) ).toBeHidden();
		await expect( page.locator( '#email_address' ) ).toBeDisabled();
	} );

	test( 'hides and disables Current Password once both changes are cancelled', async ( { page } ) => {
		await page.locator( EMAIL_TOGGLE ).click();
		await page.locator( PASSWORD_TOGGLE ).click();
		await page.locator( PASSWORD_TOGGLE ).click();
		await page.locator( EMAIL_TOGGLE ).click();

		await expect( page.locator( '#password_current' ) ).toBeHidden();
		await expect( page.locator( '#password_current' ) ).toBeDisabled();
	} );

} );
