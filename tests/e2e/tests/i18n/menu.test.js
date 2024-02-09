/**
 * Bootstraps E2E Tests.
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.0.0-rc.1 Use `runSetupWizard()`.
 * @since 6.0.0 Add theme activation based on current WP version.
 */

import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'i18n menu language', () => {

	it( 'should show Spanish menu items when user switches language', async () => {
		await visitAdminPage( 'profile.php' );

		// Ensure they have "Courses" now
		await page.waitForFunction(
			'document.querySelector("#menu-posts-course").innerText.includes("Courses")'
		);

		await page.select( '#locale', 'es_ES' );
		await page.click( '#submit' );

		await page.waitForFunction(
			'document.querySelector("#menu-posts-course").innerText.includes("Cursos")'
		);

		// Set user language back to English
		await visitAdminPage( 'profile.php' );
		await page.select( '#locale', 'en_US' );
		await page.click( '#submit' );
	} );

} );
