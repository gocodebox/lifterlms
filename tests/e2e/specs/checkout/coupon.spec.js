/**
 * Test Checkout Coupons
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { fillField, logoutUser } from '../../utils/index.js';

test.describe( 'Checkout/Coupons', () => {

	// These tests are marked as skipped in the original suite pending investigation
	// of random failures during the create access plan step.
	test.skip( 'should respond with an error for an unknown coupon', async ( { page } ) => {
		await logoutUser( page );
	} );

	test.skip( 'should accept an existing coupon and allow removal', async ( { page } ) => {
		await logoutUser( page );
	} );

} );
