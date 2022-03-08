/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.5.0 Use package functions.
 * @since 4.12.0 Added registration test with a voucher.
 * @since 5.0.0 Added tests for form field localization (country, state, etc...).
 * @since 5.5.0 Use `waitForTimeout()` in favor of deprecated `waitFor()`.
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

			const selectCountry = async ( country ) => {
				await select2Select( '#llms_billing_country', country );
			};

			const getStatesList = async () => {
				const list = await page.$$eval( '#llms_billing_state option', els =>
					els.map( ( { value, textContent } ) => ( [ value, textContent ] ) ) );

				return Object.fromEntries( list );
			};

			await toggleOpenReg( true );
			await logoutUser();
			await visitPage( 'dashboard' );

			// China.
			await selectCountry( 'China' );
			expect( await page.$eval( 'label[for="llms_billing_state"]', el => el.textContent ) ).toMatchSnapshot();
			expect ( await getStatesList() ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

			await page.waitForTimeout( 1000 );

			// Peru changes name of the State & City fields.
			await selectCountry( 'Peru' );
			expect( await page.$eval( 'label[for="llms_billing_state"]', el => el.textContent ) ).toMatchSnapshot();
			expect ( await getStatesList() ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

			await page.waitForTimeout( 1000 );

			// United States.
			await selectCountry( 'United States' );
			expect ( await getStatesList() ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_state"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

			await page.waitForTimeout( 1000 );

			// UAB has no postal code or city.
			await selectCountry( 'United Arab Emirates' );
			expect ( await getStatesList() ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_state"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( '#llms_billing_city', el => el.disabled ) ).toBe( true );
			expect( await page.$eval( '#llms_billing_zip', el => el.disabled ) ).toBe( true );

			await page.waitForTimeout( 1000 );

			// Tokelau has no states.
			await selectCountry( 'Tokelau' );
			expect ( await getStatesList() ).toMatchSnapshot();
			expect( await page.$eval( '#llms_billing_state', el => el.disabled ) ).toBe( true );
			expect( await page.$eval( 'label[for="llms_billing_city"]', el => el.textContent ) ).toMatchSnapshot();
			expect( await page.$eval( 'label[for="llms_billing_zip"]', el => el.textContent ) ).toMatchSnapshot();

		} );

	} );

} );
