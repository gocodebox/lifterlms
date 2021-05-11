/**
 * Test checkout via the Manual Payment Gateway
 *
 * @since [version]
 * @version [version]
 */

import {
	click,
	createAccessPlan,
	createCoupon,
	createCourse,
	fillField,
	logoutUser,
} from '@lifterlms/llms-e2e-test-utils';

let gateway  = null,
	courseId = null,
	planUrl  = null;

/**
 * Setup the test
 *
 * @since [version]
 *
 * @return {Void}
 */
async function setupTest() {

	if ( ! gateway ) {
		await toggleGateway( true );
		gateway = true;
	}

	if ( ! courseId ) {
		courseId = await createCourse( 'Manual Checkout' );
	}

	if ( ! planUrl ) {
		planUrl = await createAccessPlan( {
			postId: courseId,
			price: 9.99,
		} );

	}

	await logoutUser();

}

describe( 'Checkout/Gateway/Manual', () => {

	beforeEach( async () => {
		await setupTest();
	} );

	it ( 'should allow a visitor to checkout', () => {

		await page.goto( planUrl );



	} );

} );
