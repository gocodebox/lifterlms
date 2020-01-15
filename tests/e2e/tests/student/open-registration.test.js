/**
 * Test the Setup Wizard
 *
 * @since [version]
 */

const { visitAdminPage } = require( '@wordpress/e2e-test-utils' );

const {
	clickAndWait,
	fillField,
	logoutUser,
	visitPage,
} = require( 'llms-e2e-test-utils' );

let openRegStatus = null;

/**
 * Toggles the open registration setting on or off
 *
 * @since  [version]
 *
 * @param  {Boolean} status Whether to toggle on (`true`) or off (`false`).
 * @return {void}
 */
const toggleOpenReg = async function( status ) {

	if ( openRegStatus === status ) {
		return;
	}

	// Launch the Setup Wizard.
	await visitAdminPage( 'admin.php', 'page=llms-settings&tab=account' );

	const curr_status = await page.$eval( '#lifterlms_enable_myaccount_registration', el => el.checked );

	if ( status && ! curr_status || ! status && curr_status ) {
		await page.click( '#lifterlms_enable_myaccount_registration' );
		await clickAndWait( '.llms-save .llms-button-primary' );
		openRegStatus = status;
	}

}

describe( 'OpenRegistration', () => {

	afterEach( async () => {
		await logoutUser();
	} );

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
		await logoutUser();

		await visitPage( 'dashboard' );

		const
			the_int = Math.floor( Math.random() * ( 99990 - 10000 + 1 ) ) + 10000,
			email   = 'help+e2e' + the_int + '@lifterlms.com',
			pass    = Math.random().toString( 36 ).slice( 2 ) + Math.random().toString( 36 ).slice( 2 );

		await fillField( '#email_address', email );
		await fillField( '#password', pass );
		await fillField( '#password_confirm', pass );
		await fillField( '#first_name', 'Jeffrey' );
		await fillField( '#last_name', 'Lebowski' );

		await clickAndWait( '#llms_register_person' );

		expect( await page.$eval( 'h2.llms-sd-title', el => el.textContent ) ).toBe( 'Dashboard' );

	} );

	it ( 'should not allow registration because open registration is disabled.', async () => {

		await toggleOpenReg( false );
		await logoutUser();
		await visitPage( 'dashboard' );
		await expect( await page.$( '.llms-new-person-form-wrapper > h4.llms-form-heading' ) ).toBeNull();

	} );

} );
