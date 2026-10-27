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
} );
