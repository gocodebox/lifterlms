/**
 * Test the core Tabs block on a focus-mode lesson.
 *
 * Requires WordPress 7.1+ (when core/tabs shipped). Without processing
 * lesson blocks before wp_head(), the Interactivity import map is empty on
 * block themes and tab clicks do nothing.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { loginStudent, logoutUser } from '../../utils/index.js';

test.describe( 'FocusModeTabs', () => {
	test( 'switches tab panels when a tab is clicked', async ( { page } ) => {
		const interactivityErrors = [];
		page.on( 'pageerror', ( error ) => {
			if ( error.message.includes( '@wordpress/interactivity' ) ) {
				interactivityErrors.push( error.message );
			}
		} );

		await logoutUser( page );
		await loginStudent( page, 'validcreds@email.tld', 'password' );

		await page.goto( '/lesson/focus-mode-tabs-lesson/' );

		await expect( page.locator( 'body' ) ).toHaveClass( /llms-focus-mode/ );
		await expect( page.getByRole( 'tab', { name: 'Overview' } ) ).toBeVisible();
		await expect( page.getByRole( 'tab', { name: 'Details' } ) ).toBeVisible();

		await expect( page.getByText( 'Overview tab content.' ) ).toBeVisible();
		await expect( page.getByText( 'Details tab content.' ) ).toBeHidden();

		await page.getByRole( 'tab', { name: 'Details' } ).click();

		await expect( page.getByText( 'Details tab content.' ) ).toBeVisible();
		await expect( page.getByText( 'Overview tab content.' ) ).toBeHidden();
		await expect( page.getByRole( 'tab', { name: 'Details' } ) ).toHaveAttribute(
			'aria-selected',
			'true'
		);

		 expect( interactivityErrors, 'Interactivity API failed to resolve' ).toEqual( [] );
	} );

	test( 'opens and closes mobile course navigation accessibly', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await logoutUser( page );
		await loginStudent( page, 'validcreds@email.tld', 'password' );

		await page.goto( '/lesson/focus-mode-tabs-lesson/' );

		const toggle = page.getByRole( 'button', { name: 'Lessons', exact: true } );
		const sidebar = page.locator( '#llms-focus-mode-sidebar' );

		await expect( toggle ).toBeVisible();
		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( sidebar ).toHaveAttribute( 'aria-hidden', 'true' );

		await toggle.click();

		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( page.locator( 'body' ) ).toHaveClass( /llms-mobile-sidebar-open/ );
		await expect( sidebar ).not.toHaveAttribute( 'aria-hidden', 'true' );
		await expect( sidebar ).toBeFocused();

		await page.keyboard.press( 'Escape' );

		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( page.locator( 'body' ) ).not.toHaveClass( /llms-mobile-sidebar-open/ );
		await expect( sidebar ).toHaveAttribute( 'aria-hidden', 'true' );
		await expect( toggle ).toBeFocused();
	} );
} );
