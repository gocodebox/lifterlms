/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.5.0 Use package functions.
 * @since 4.12.0 Added registration test with a voucher.
 * @since 5.0.0 Added tests for form field localization (country, state, etc...).
 * @since 5.5.0 Use `waitForTimeout()` in favor of deprecated `waitFor()`.
 */

import {
	clickAndWait,
	highlightNode,
	logoutUser,
	loginStudent,
	setCheckboxSetting,
	visitPage,
	visitSettingsPage,
} from '@lifterlms/llms-e2e-test-utils';

import { switchUserToAdmin } from '@wordpress/e2e-test-utils';

const context = browser.defaultBrowserContext();
context.overridePermissions( process.env.WP_BASE_URL, [ 'clipboard-read' ] );

/**
 * Watch for an event to run.
 *
 * @since 5.6.0
 *
 * @param {string} eventName The event name.
 * @return {void}
 */
async function watchForEvent( eventName ) {
	return await page.evaluate( ( _eventName ) => {
		document.addEventListener( _eventName, ( event ) => window.watchCopyPreventionEvents( { eventName: _eventName, event } ) );
	}, eventName );
}

describe( 'Setting/CopyPrevention', () => {

	let caughtEvents = [];

	beforeAll( async () => {
		await visitSettingsPage();
		await setCheckboxSetting( '#lifterlms_content_protection', true );
		await page.exposeFunction( 'watchCopyPreventionEvents', ( event ) => {
			caughtEvents.push( event );
		} );
	} );

	afterAll( async () => {
		await visitSettingsPage();
		await setCheckboxSetting( '#lifterlms_content_protection', false );
		await logoutUser();
	} );

	beforeEach( async () => {
		await visitPage( 'integrity-test' );
	} );

	afterEach( () => {
		caughtEvents = [];
	} );

	describe( 'AdminUser', () => {

		beforeAll( async() => {
			await switchUserToAdmin();
		} );

		it ( 'is allowed to copy content', async () => {

			watchForEvent( 'llms-copy-prevented' );
			expect( await highlightNode( 'h1.entry-title', true ) ).toBe( 'Integrity-Test' );
			expect( caughtEvents.length ).toStrictEqual( 0 );

		} );

	} );

	describe( 'StudentUser', () => {

		beforeAll( async() => {

			await logoutUser();
			await loginStudent( 'validcreds@email.tld', 'password' );

		} );

		it ( 'is not allowed to copy content', async () => {

			watchForEvent( 'llms-copy-prevented' );
			expect( await highlightNode( 'h1.entry-title', true ) ).toBe( 'Copying is not allowed.' );
			expect( caughtEvents[0].eventName ).toBe( 'llms-copy-prevented' );

		} );

	} );

	describe( 'LoggedOutUser', () => {

		beforeAll( async() => {

			await logoutUser();
			await visitPage( 'integrity-test' );

		} );

		it ( 'is not allowed to copy content', async () => {

			await visitPage( 'integrity-test' );
			watchForEvent( 'llms-copy-prevented' );
			expect( await highlightNode( 'h1.entry-title', true ) ).toBe( 'Copying is not allowed.' );
			expect( caughtEvents[0].eventName ).toBe( 'llms-copy-prevented' );

		} );

	} );
} );
