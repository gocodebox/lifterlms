/**
 * Test the LifterLMS View Manager
 *
 * @since 4.16.0
 */

import {
	clickAndWait,
	createAccessPlan,
	createCourse,
	logoutUser,
	toggleOpenRegistration,
	visitPage,
} from '@lifterlms/llms-e2e-test-utils';

import {
	visitAdminPage
} from '@wordpress/e2e-test-utils';


/**
 * Select a view from the view manager menu in the WP Admin bar.
 *
 * @since 4.16.0
 *
 * @param {String} view View name to select. Accepts "self", "visitor", or "student".
 * @return {Void}
 */
async function selectView( view ) {

	const
		topLevelSelector = '#wp-admin-bar-llms-view-as-menu',
		viewSelector     = `#wp-admin-bar-llms-view-as--${ view }`;

	await page.waitForSelector( topLevelSelector );
	await page.hover( topLevelSelector );

	await page.waitForSelector( viewSelector );
	await clickAndWait( `${ viewSelector } a.ab-item` );

}

describe( 'ViewManager', () => {

	beforeAll( async () => {
		// Ensure we're a logged in admin that can use the view manager.
		await visitAdminPage( '/' );
	} );

	afterAll( async () => {
		await logoutUser();
	} );

	describe( 'Dashboard', () => {

		beforeAll( async () => {
			await toggleOpenRegistration( true );
		} );
		afterAll( async () => {
			await toggleOpenRegistration( false );
		} );

		beforeEach( async () => {
			await visitPage( 'dashboard' );
		} );

		it ( 'should show forms when viewing as a visitor.', async () => {

			await selectView( 'visitor' );

			// Login and registration forms should exist.
			await page.waitForSelector( '.llms-person-login-form-wrapper' );
			await page.waitForSelector( '.llms-new-person-form-wrapper' );

			// Dashboard header should not exist.
			expect( await page.$( '.llms-sd-header' ) ).toBeNull();

		} );

		it ( 'should show the dashboard when viewing as a student.', async () => {

			await selectView( 'student' );

			// Login and registration forms should not exist.
			expect( await page.$( '.llms-person-login-form-wrapper' ) ).toBeNull();
			expect( await page.$( '.llms-new-person-form-wrapper' ) ).toBeNull();

			// Dashboard header should exist.
			await page.waitForSelector( '.llms-sd-header' );

		} );

	} );

	describe( 'Checkout', () => {

		beforeAll( async () => {

			const
				courseId = await createCourse( 'View Manager Test' ),
				planUrl = await createAccessPlan( {
					postId: courseId,
					price: 5.00,
					title: 'Test VM Plan ' + parseInt( Math.random() * 100000 ),
				} );

			await page.goto( planUrl );

		} );

		// Randomly failing during the create access plan step, investigate.
		xit ( 'should show the checkout form when viewing as a visitor.', async () => {

			await selectView( 'visitor' );

			// Should show the checkout form.
			await page.waitForSelector( '#llms-product-purchase-form' );

		} );

		// Randomly failing during the create access plan step, investigate.
		xit ( 'should show an already enrolled notice when viewing as a student.', async () => {

			await selectView( 'student' );

			// Should show a notice.
			expect( await page.$eval( '.llms-checkout-wrapper .llms-notice', el => el.innerHTML ) ).toMatchSnapshot();

			// Should not show the checkout form.
			expect( await page.$( '#llms-product-purchase-form' ) ).toBeNull();

		} );

	} );

} );
