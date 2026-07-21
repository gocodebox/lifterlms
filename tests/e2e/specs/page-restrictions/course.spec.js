/**
 * Test Course Page Restrictions
 *
 * @since 10.0.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { logoutUser } from '../../utils/index.js';

test.describe( 'CourseRestrictions', () => {

	test.describe( 'Non-enrolled users', () => {

		test.beforeEach( async ( { page } ) => {
			await logoutUser( page );
		} );

		test( 'should not be able to view restricted lesson URLs', async ( { page } ) => {
			await page.goto( '/lesson/test-lesson/' );

			// Non-enrolled users should see restriction notice content, not the lesson content.
			const content = page.locator( '.entry-content, .llms-notice, .llms-restriction-message' );
			await expect( content.first() ).toBeVisible();
		} );

	} );

} );
