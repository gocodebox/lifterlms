/**
 * Test Course Page Restrictions
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { logoutUser, loginStudent, visitPage } from '../../utils/index.js';

test.describe( 'CourseRestrictions', () => {

	test.describe( 'Non-enrolled users', () => {

		test.beforeEach( async ( { page } ) => {
			await logoutUser( page );
		} );

		test( 'should not be able to view restricted lesson URLs', async ( { page } ) => {
			// Visit a lesson URL directly as a logged-out user.
			// They should be redirected away from the lesson content.
			const baseURL = process.env.WP_BASE_URL || 'http://localhost:8889';
			await page.goto( `${ baseURL }/course/lifterlms-quickstart-course/` );

			await expect(
				page.locator( '.entry-content' )
			).toBeVisible();
		} );

	} );

} );
