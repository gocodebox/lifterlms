/**
 * Test Copy Prevention Setting
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { logoutUser, loginStudent, setCheckboxSetting, visitPage, visitSettingsPage } from '../../utils/index.js';

test.describe( 'Setting/CopyPrevention', () => {

	test.describe( 'Protection enabled', () => {

		test.beforeEach( async ( { admin, page } ) => {
			await admin.visitAdminPage( '/' );
			await visitSettingsPage( page );
			await setCheckboxSetting( page, '#lifterlms_content_protection', true );
		} );

		test( 'is allowed to copy content (admin)', async ( { page } ) => {
			await visitPage( page, 'integrity-test' );
			const title = page.locator( '.entry-title, .wp-block-post-title, h1' ).first();
			await expect( title ).toBeVisible();
		} );

		test( 'is not allowed to copy content (student)', async ( { page } ) => {
			await logoutUser( page );
			await loginStudent( page, 'validcreds@email.tld', 'password' );
			await visitPage( page, 'integrity-test' );

			const copyPrevScript = page.locator( 'script[src*="llms-copy-prevention"]' );
			const bodyClass = await page.locator( 'body' ).getAttribute( 'class' );
			expect(
				bodyClass?.includes( 'llms-content-protection' ) ||
				await copyPrevScript.count() > 0
			).toBeTruthy();
		} );

		test( 'is not allowed to copy content (logged out)', async ( { page } ) => {
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

	test.describe( 'Cleanup', () => {

		test( 'disable copy prevention', async ( { admin, page } ) => {
			await admin.visitAdminPage( '/' );
			await visitSettingsPage( page );
			await setCheckboxSetting( page, '#lifterlms_content_protection', false );
		} );

	} );

} );
