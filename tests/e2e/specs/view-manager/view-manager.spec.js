/**
 * Test the LifterLMS View Manager
 *
 * @since 10.0.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { visitPage } from '../../utils/index.js';

test.describe( 'ViewManager', () => {

	test( 'should display the View Manager in the admin bar', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitPage( page, 'dashboard' );

		const viewMenu = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await expect( viewMenu ).toBeVisible();
	} );

	test( 'should switch to visitor view', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitPage( page, 'dashboard' );

		const topLevel = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await topLevel.hover();

		const visitorLink = page.locator( '#wp-admin-bar-llms-view-as--visitor a.ab-item' );
		await visitorLink.click();
		await page.waitForLoadState( 'networkidle' );

		await expect( topLevel ).toBeVisible();
	} );

	test( 'should switch to student view and back to self', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitPage( page, 'dashboard' );

		const topLevel = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await topLevel.hover();

		const studentLink = page.locator( '#wp-admin-bar-llms-view-as--student a.ab-item' );
		await studentLink.click();
		await page.waitForLoadState( 'networkidle' );

		await expect( topLevel ).toBeVisible();

		const topLevel2 = page.locator( '#wp-admin-bar-llms-view-as-menu' );
		await topLevel2.hover();

		const selfLink = page.locator( '#wp-admin-bar-llms-view-as--self a.ab-item' );
		await selfLink.click();
		await page.waitForLoadState( 'networkidle' );

		await expect( topLevel2 ).toBeVisible();
	} );

} );
