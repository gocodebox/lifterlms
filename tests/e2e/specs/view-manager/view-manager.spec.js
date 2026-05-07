/**
 * Test the LifterLMS View Manager
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { logoutUser, visitPage } from '../../utils/index.js';

test.describe( 'ViewManager', () => {

	test.beforeAll( async ( { browser } ) => {
		const page = await browser.newPage();
		const baseURL = process.env.WP_BASE_URL || 'http://localhost:8889';
		await page.goto( `${ baseURL }/wp-admin/` );
		await page.close();
	} );

	test.afterAll( async ( { browser } ) => {
		const page = await browser.newPage();
		const baseURL = process.env.WP_BASE_URL || 'http://localhost:8889';
		// Reset to self view.
		await page.goto( `${ baseURL }/wp-admin/` );
		const topLevel = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		if ( await topLevel.count() > 0 ) {
			await topLevel.hover();
			const selfLink = page.locator( '#wp-admin-bar-llms-view-as--self a.ab-item' );
			if ( await selfLink.count() > 0 ) {
				await selfLink.click();
				await page.waitForLoadState( 'networkidle' );
			}
		}
		await page.close();
	} );

	test( 'should display the View Manager in the admin bar', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitPage( page, '' );

		const viewMenu = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await expect( viewMenu ).toBeVisible();
	} );

	test( 'should switch to visitor view', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitPage( page, '' );

		const topLevel = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await topLevel.hover();

		const visitorLink = page.locator( '#wp-admin-bar-llms-view-as--visitor a.ab-item' );
		await visitorLink.click();
		await page.waitForLoadState( 'networkidle' );

		// In visitor view, admin bar should still be visible but context changed.
		await expect( topLevel ).toBeVisible();
	} );

	test( 'should switch to student view', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitPage( page, '' );

		const topLevel = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await topLevel.hover();

		const studentLink = page.locator( '#wp-admin-bar-llms-view-as--student a.ab-item' );
		await studentLink.click();
		await page.waitForLoadState( 'networkidle' );

		await expect( topLevel ).toBeVisible();
	} );

	test( 'should switch back to self view', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitPage( page, '' );

		const topLevel = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await topLevel.hover();

		const selfLink = page.locator( '#wp-admin-bar-llms-view-as--self a.ab-item' );
		await selfLink.click();
		await page.waitForLoadState( 'networkidle' );

		await expect( topLevel ).toBeVisible();
	} );

} );
