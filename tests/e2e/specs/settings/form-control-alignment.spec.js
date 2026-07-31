/**
 * Admin form control alignment (WP 7.0+)
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { visitSettingsPage } from '../../utils/index.js';

/**
 * Measure rendered height of an element.
 *
 * @param {import('@playwright/test').Page} page     Playwright page.
 * @param {string}                          selector CSS selector.
 * @return {Promise<number|null>}
 */
async function getHeight( page, selector ) {
	return page.locator( selector ).first().evaluate( ( el ) => {
		return el.getBoundingClientRect().height;
	} );
}

test.describe( 'Settings/FormControlAlignment', () => {

	test( 'Select2 singles match native select height on Courses settings', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitSettingsPage( page, { tab: 'courses' } );

		await expect( page.locator( '#lifterlms_focus_mode_content_width' ) ).toBeVisible();
		await expect( page.locator( '#lifterlms_course_completion_page_id + .select2-container .select2-selection--single' ) ).toBeVisible();

		const isWp70 = await page.evaluate( () => document.body.classList.contains( 'llms-wp-version-gte-70' ) );
		test.skip( ! isWp70, 'WP 7.0+ form-control sizing only' );

		const nativeHeight = await getHeight( page, '#lifterlms_focus_mode_content_width' );
		const select2Height = await getHeight( page, '#lifterlms_course_completion_page_id + .select2-container .select2-selection--single' );
		const catalogSelect2Height = await getHeight( page, '#lifterlms_shop_page_id + .select2-container .select2-selection--single' );

		expect( nativeHeight ).toBeGreaterThanOrEqual( 40 );
		expect( Math.abs( select2Height - nativeHeight ) ).toBeLessThanOrEqual( 2 );
		expect( Math.abs( catalogSelect2Height - nativeHeight ) ).toBeLessThanOrEqual( 2 );

		const nativeWidth = await page.locator( '#lifterlms_focus_mode_content_width' ).evaluate( ( el ) => el.getBoundingClientRect().width );
		const select2Width = await page.locator( '#lifterlms_course_completion_page_id + .select2-container' ).evaluate( ( el ) => el.getBoundingClientRect().width );
		expect( Math.abs( select2Width - nativeWidth ) ).toBeLessThanOrEqual( 2 );
	} );

	test( 'Select2 singles match native select height on Account settings', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitSettingsPage( page, { tab: 'account' } );

		await expect( page.locator( '#lifterlms_myaccount_page_id + .select2-container .select2-selection--single' ) ).toBeVisible();

		const isWp70 = await page.evaluate( () => document.body.classList.contains( 'llms-wp-version-gte-70' ) );
		test.skip( ! isWp70, 'WP 7.0+ form-control sizing only' );

		// Native (non-select2) select on the same screen when present; otherwise compare to a text input.
		const nativeSelect = page.locator( '.form-table select:not(.select2-hidden-accessible)' ).first();
		const hasNativeSelect = await nativeSelect.count() > 0;

		const referenceHeight = hasNativeSelect
			? await nativeSelect.evaluate( ( el ) => el.getBoundingClientRect().height )
			: await getHeight( page, '.form-table input[type="text"]' );

		const select2Height = await getHeight( page, '#lifterlms_myaccount_page_id + .select2-container .select2-selection--single' );

		expect( referenceHeight ).toBeGreaterThanOrEqual( 40 );
		expect( Math.abs( select2Height - referenceHeight ) ).toBeLessThanOrEqual( 2 );
	} );

} );
