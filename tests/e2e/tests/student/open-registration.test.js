/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.5.0 Use package functions.
 * @since 4.12.0 Added registration test with a voucher.
 * @since [version] Added tests for form field localization (country, state, etc...).
 */

import {
	clickAndWait,
	createVoucher,
	fillField,
	logoutUser,
	registerStudent,
	select2Select,
	toggleOpenRegistration,
	visitPage,
} from '@lifterlms/llms-e2e-test-utils';

import { visitAdminPage } from '@wordpress/e2e-test-utils';

let openRegStatus = null;

/**
 * Toggles the open registration setting on or off
 *
 * @since 3.37.8
 * @since 4.5.0 Use toggleOpenRegistration function from utils pacakage.
 *
 * @param  {Boolean} status Whether to toggle on (`true`) or off (`false`).
 * @return {void}
 */
const toggleOpenReg = async function( status ) {

	if ( openRegStatus === status ) {
		return;
	}
	await toggleOpenRegistration( status );

}

describe( 'OpenRegistration', () => {

	afterEach( async () => {
		await logoutUser();
	} );

	describe( 'Registration', () => {

		it ( 'should not allow registration because user is already logged in.', async () => {

			await toggleOpenReg( true );
			await visitPage( 'dashboard' );
			await expect( await page.$( '.llms-new-person-form-wrapper > h4.llms-form-heading' ) ).toBeNull();

		} );

		it ( 'should allow registration.', async () => {

			await toggleOpenReg( true );
			await logoutUser();
			await visitPage( 'dashboard' );
			expect( await page.$eval( '.llms-new-person-form-wrapper > h4.llms-form-heading', el => el.textContent ) ).toBe( 'Register' );

		} );

		it ( 'should register a new user.', async () => {

			await toggleOpenReg( true );
			await registerStudent();
			expect( await page.$eval( 'h2.llms-sd-title', el => el.textContent ) ).toBe( 'Dashboard' );

		} );

		it ( 'should register a new user with a voucher.', async() => {

			await toggleOpenReg( true );
			const codes = await createVoucher( { codes: 1, uses: 1 } );
			await registerStudent( { voucher: codes[0] } );

			expect( await page.$eval( 'h2.llms-sd-title', el => el.textContent ) ).toBe( 'Dashboard' );

			await page.waitForSelector( '.llms-notification .llms-notification-main' );
			expect( await page.$eval( '.llms-notification .llms-notification-main', el => el.innerHTML ) ).toMatchSnapshot();

		} );

		it ( 'should not allow registration because open registration is disabled.', async () => {

			await toggleOpenReg( false );
			await logoutUser();
			await visitPage( 'dashboard' );
			await expect( await page.$( '.llms-new-person-form-wrapper > h4.llms-form-heading' ) ).toBeNull();

		} );

	} );

	describe( 'Localization', () => {

		it ( 'should localize city, state, and postcode fields when changing the selected country', async () => {

			await toggleOpenReg( true );
			await logoutUser();
			await visitPage( 'dashboard' );

			// China.
			await select2Select( '#llms_billing_country', 'China' );
			expect( await page.$eval( '.llms-l10n-state-select', el => el.innerHTML ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

			// Peru changes name of the State & City fields.
			await select2Select( '#llms_billing_country', 'Peru' );
			expect( await page.$eval( '.llms-l10n-state-select', el => el.innerHTML ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

			// United States.
			await select2Select( '#llms_billing_country', 'United States' );
			expect( await page.$eval( '.llms-l10n-state-select', el => el.innerHTML ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

			// UAB has no postal code or city.
			await select2Select( '#llms_billing_country', 'United Arab Emirates' );
			expect( await page.$eval( '.llms-l10n-state-select', el => el.innerHTML ) ).toMatchSnapshot();
			expect( await page.$eval( '#llms_billing_city', el => el.disabled ) ).toBe( true );
			expect( await page.$eval( '#llms_billing_zip', el => el.disabled ) ).toBe( true );

			// Tokelau has no states.
			await select2Select( '#llms_billing_country', 'Tokelau' );
			expect( await page.$eval( '#llms_billing_state', el => el.disabled ) ).toBe( true );
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

		} );

	} );

} );
