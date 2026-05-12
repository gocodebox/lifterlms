/**
 * Test Student Dashboard Voucher Redemption
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { fillField, loginStudent, logoutUser, visitPage } from '../../utils/index.js';

test.describe( 'StudentDashboard/RedeemVoucher', () => {

	test( 'should display an error for an invalid voucher', async ( { page } ) => {
		const code = 'fakecode';

		await logoutUser( page );
		await loginStudent( page, 'voucher@email.tld', 'password' );
		await visitPage( page, 'dashboard/redeem-voucher' );
		await fillField( page, '#llms-voucher-code', code );
		await page.locator( '#llms-redeem-voucher-submit' ).click();
		await page.waitForLoadState( 'networkidle' );

		await expect(
			page.locator( '.llms-notice.llms-error' )
		).toContainText( `could not be found` );
	} );

} );
