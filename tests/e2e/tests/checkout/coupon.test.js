import {
	click,
	createAccessPlan,
	createCoupon,
	createCourse,
	fillField,
	logoutUser,
} from '@lifterlms/llms-e2e-test-utils';

let courseId = null,
	coupon   = null,
	planUrl  = null;

/**
 * Setup the test
 *
 * @since 3.39.0
 *
 * @return {Void}
 */
async function setupTest() {

	if ( ! courseId ) {
		courseId = await createCourse( 'Test Coupons' );
	}

	if ( ! planUrl ) {
		planUrl = await createAccessPlan( {
			postId: courseId,
			price: 9.99,
			title: 'Test Plan ' + parseInt( Math.random() * 100000 ),
		} );

	}

	if ( ! coupon ) {
		coupon = await createCoupon( {} );
	}

	await logoutUser();

}

/**
 * Apply a coupon
 *
 * @since 3.39.0
 *
 * @param {String} code Coupon code.
 * @return {Void}
 */
async function applyCoupon( code ) {

	await page.goto( planUrl );

	await click( '.llms-coupon-wrapper a[href="#llms-coupon-toggle"]' );

	await page.waitForSelector( '#llms_coupon_code' );

	await fillField( '#llms_coupon_code', code );

	await click( '#llms-apply-coupon' );

}

describe( 'Checkout/Coupons', () => {

	beforeEach( async () => {
		await setupTest();
	} );

	// Randomly failing during the create access plan step, investigate.
	xit ( 'should respond with an error for an unknown coupon', async () => {

		const codeNotFound = 'notfound';

		await applyCoupon( codeNotFound );

		await page.waitForSelector( '.llms-coupon-messages' );
		// Wait for animation.
		await page.waitForTimeout( 700 );

		expect( await page.$eval( '.llms-coupon-messages .llms-notice.llms-error li:first-child', el => el.textContent ) ).toBe( `Coupon code "${ codeNotFound }" not found.` );

	} );

	// Randomly failing during the create access plan step, investigate.
	xit ( 'should accept an existing coupon, save it to session data, and allow it to be removed', async () => {

		// Add a valid coupon.
		await applyCoupon( coupon );
		await page.waitForSelector( '.llms-coupon-wrapper .llms-notice.llms-success' );
		expect( await page.$eval( '.llms-coupon-wrapper .llms-notice.llms-success', el => el.textContent ) ).toBe( `Coupon code "${ coupon }" has been applied to your order.` );

		// Navigate away.
		await page.goto( process.env.WP_BASE_URL );

		// Return and it still found due to it being saved in session data.
		await page.goto( planUrl );

		expect( await page.$eval( '.llms-coupon-wrapper .llms-notice.llms-success', el => el.textContent ) ).toMatchStringWithQuotes( `Coupon code "${ coupon }" has been applied to your order.` );

		// Remove it.
		await click( '#llms-remove-coupon' );
		await page.waitForSelector( '.llms-coupon-wrapper a[href="#llms-coupon-toggle"]' );
		expect( await page.$eval( '.llms-coupon-wrapper a[href="#llms-coupon-toggle"]', el => el.textContent ) ).toBe( 'Click here to enter your code' );

	} );

} );
