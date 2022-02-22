import {
	clickAndWait,
	createVoucher,
	fillField,
	loginStudent,
	logoutUser,
	visitPage,
} from '@lifterlms/llms-e2e-test-utils';

describe( 'StudentDashboard/RedeemVoucher', () => {

	afterEach( async () => {
		await logoutUser();
	} );

	it ( 'Should redeem a valid voucher', async () => {

		// Setup.
		const [ code ] = await createVoucher( { codes: 1, uses: 1 } );

		await logoutUser();

		// Use the voucher.
		await loginStudent( 'voucher@email.tld', 'password' );
		await visitPage( 'dashboard/redeem-voucher' );
		await fillField( '#llms-voucher-code', code );
		await clickAndWait( '#llms-redeem-voucher-submit' );

		// Success.
		expect( await page.$eval( '.llms-notice.llms-success', el => el.textContent ) ).toMatchSnapshot();

		await page.waitForSelector( '.llms-notification .llms-notification-main' );
		expect( await page.$eval( '.llms-notification .llms-notification-main', el => el.innerHTML ) ).toMatchSnapshot();

	} );

	it ( 'Should display an error for an invalid voucher', async() => {

		const code = 'fakecode';

		await logoutUser();

		await loginStudent( 'voucher@email.tld', 'password' );
		await visitPage( 'dashboard/redeem-voucher' );
		await fillField( '#llms-voucher-code', 'fakecode' );
		await clickAndWait( '#llms-redeem-voucher-submit' );

		// Error message.
		expect( await page.$eval( '.llms-notice.llms-error', el => el.textContent ) ).toMatchStringWithQuotes( `Voucher code "${ code }" could not be found.` );

	} );

} );
