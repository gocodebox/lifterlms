/**
 * Bootstrap E2E Tests.
 *
 * Activates the correct theme and runs the setup wizard.
 *
 * @since 10.0.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Bootstrap', () => {

	test( 'should load the WordPress admin dashboard', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await expect(
			page.getByRole( 'heading', { name: 'Dashboard', level: 1 } )
		).toBeVisible();
	} );

} );
