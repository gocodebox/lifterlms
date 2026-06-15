/**
 * Test the LifterLMS Setup Wizard.
 *
 * Walks the full wizard (intro -> pages -> payments -> coupon -> finish) and
 * imports a sample course. The lifterlms.com export API is mocked at the WP
 * layer by the `llms-export-api-mock` mu-plugin, so no real network request is
 * made during the import.
 *
 * @since 10.0.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'SetupWizard', () => {
	test( 'walks every step and imports a course with the network mocked', async ( { admin, page } ) => {
		// Intro step.
		await admin.visitAdminPage( 'admin.php', 'page=llms-setup&step=intro' );
		await expect(
			page.getByRole( 'heading', { name: 'Welcome to LifterLMS!' } )
		).toBeVisible();
		await page.getByRole( 'link', { name: 'Get Started Now' } ).click();

		// Pages step: creates the required LifterLMS pages on submit.
		await expect( page ).toHaveURL( /step=pages/ );
		await expect(
			page.getByRole( 'heading', { name: 'Page Setup' } )
		).toBeVisible();
		await page.locator( '#llms-setup-submit' ).click();

		// Payments step: persist country/currency and enable offline payments.
		await expect( page ).toHaveURL( /step=payments/ );
		await expect(
			page.getByRole( 'heading', { name: 'Payments', exact: true } )
		).toBeVisible();
		await page.locator( '#llms_manual' ).check();
		await page.locator( '#llms-setup-submit' ).click();

		// Coupon step: skip it so we don't fire the usage-tracking request home.
		await expect( page ).toHaveURL( /step=coupon/ );
		await page.getByRole( 'link', { name: 'No thanks' } ).click();

		// Finish step: the importable course list comes from the mocked API.
		await expect( page ).toHaveURL( /step=finish/ );
		await expect(
			page.getByRole( 'heading', { name: 'Import Sample Courses and Templates!' } )
		).toBeVisible();

		// The import control is a CSS-styled toggle whose real checkbox sits
		// off-viewport, so set it directly and fire the change the wizard JS
		// listens for to enable the submit button.
		const importToggle = page
			.locator( 'input[name="llms_setup_course_import_ids[]"]' )
			.first();
		await importToggle.evaluate( ( el ) => {
			el.checked = true;
			el.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );

		const submit = page.locator( '#llms-setup-submit' );
		await expect( submit ).toBeEnabled();
		await submit.click();

		// The mocked download builds a real course and redirects to its editor.
		await expect( page ).toHaveURL( /post\.php\?post=\d+&action=edit/ );

		// Confirm the imported course exists in the course list table.
		await admin.visitAdminPage( 'edit.php', 'post_type=course' );
		await expect( page.locator( '.wp-list-table' ) ).toContainText(
			'The Official Quickstart Course for LifterLMS'
		);
	} );
} );
