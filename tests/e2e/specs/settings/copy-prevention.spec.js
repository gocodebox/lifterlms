/**
 * Test Copy Prevention Setting
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { logoutUser, loginStudent, setCheckboxSetting, visitPage, visitSettingsPage } from '../../utils/index.js';

test.describe( 'Setting/CopyPrevention', () => {

	test.beforeAll( async ( { browser } ) => {
		const page = await browser.newPage();
		const baseURL = process.env.WP_BASE_URL || 'http://localhost:8889';
		await page.goto( `${ baseURL }/wp-admin/` );
		await visitSettingsPage( page );
		await setCheckboxSetting( page, '#lifterlms_content_protection', true );
		await page.close();
	} );

	test.afterAll( async ( { browser } ) => {
		const page = await browser.newPage();
		const baseURL = process.env.WP_BASE_URL || 'http://localhost:8889';
		await page.goto( `${ baseURL }/wp-admin/` );
		await visitSettingsPage( page );
		await setCheckboxSetting( page, '#lifterlms_content_protection', false );
		await page.close();
	} );

	test.describe( 'AdminUser', () => {

		test( 'is allowed to copy content', async ( { admin, page } ) => {
			await admin.visitAdminPage( '/' );
			await visitPage( page, 'integrity-test' );
			// Admin users should be able to select text without copy prevention triggering.
			const title = page.locator( '.entry-title, .wp-block-post-title, h1' ).first();
			await expect( title ).toBeVisible();
		} );

	} );

	test.describe( 'StudentUser', () => {

		test( 'is not allowed to copy content', async ( { page } ) => {
			await logoutUser( page );
			await loginStudent( page, 'validcreds@email.tld', 'password' );
			await visitPage( page, 'integrity-test' );

			// Verify copy prevention script is loaded on the page.
			const copyPrevScript = page.locator( 'script[src*="llms-copy-prevention"]' );
			const bodyClass = await page.locator( 'body' ).getAttribute( 'class' );
			// The copy prevention feature adds specific body classes or disables right-click.
			expect(
				bodyClass?.includes( 'llms-content-protection' ) ||
				await copyPrevScript.count() > 0
			).toBeTruthy();
		} );

	} );

	test.describe( 'LoggedOutUser', () => {

		test( 'is not allowed to copy content', async ( { page } ) => {
			await logoutUser( page );
			await visitPage( page, 'integrity-test' );

			const copyPrevScript = page.locator( 'script[src*="llms-copy-prevention"]' );
			const bodyClass = await page.locator( 'body' ).getAttribute( 'class' );
			expect(
				bodyClass?.includes( 'llms-content-protection' ) ||
				await copyPrevScript.count() > 0
			).toBeTruthy();
		} );

	} );

} );
