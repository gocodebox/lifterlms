/**
 * Admin form control alignment (WP 7.0+)
 *
 * @since 10.1.1
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

	test( 'Select2 clear control is spaced from the chevron and clickable', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitSettingsPage( page, { tab: 'courses' } );

		const isWp70 = await page.evaluate( () => document.body.classList.contains( 'llms-wp-version-gte-70' ) );
		test.skip( ! isWp70, 'WP 7.0+ form-control sizing only' );

		const container = page.locator( '#lifterlms_course_completion_page_id + .select2-container' );
		await expect( container ).toBeVisible();

		// Ensure a value is selected so the clear (x) renders.
		let clear = container.locator( '.select2-selection__clear' );
		if ( await clear.count() === 0 ) {
			await container.locator( '.select2-selection--single' ).click();
			await page.locator( '.select2-results__option' ).first().click();
			clear = container.locator( '.select2-selection__clear' );
		}
		await expect( clear ).toBeVisible();

		const layout = await container.evaluate( ( el ) => {
			const clearEl = el.querySelector( '.select2-selection__clear' );
			const arrowEl = el.querySelector( '.select2-selection__arrow' );
			const clearBox = clearEl.getBoundingClientRect();
			const arrowBox = arrowEl.getBoundingClientRect();
			return {
				clearRight: clearBox.right,
				arrowLeft: arrowBox.left,
				gap: arrowBox.left - clearBox.right,
			};
		} );

		// Clear must sit left of the arrow with a tight, clickable gap.
		expect( layout.gap ).toBeGreaterThanOrEqual( 2 );
		expect( layout.gap ).toBeLessThanOrEqual( 14 );

		await clear.click();
		await expect( container.locator( '.select2-selection__clear' ) ).toHaveCount( 0 );
		await expect( page.locator( '#lifterlms_course_completion_page_id' ) ).toHaveValue( '' );
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
