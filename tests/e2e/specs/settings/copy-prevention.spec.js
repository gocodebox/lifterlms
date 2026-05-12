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

		test( 'admin can copy content', async ( { page } ) => {
			await visitPage( page, 'integrity-test' );

			const clipboardText = await page.evaluate( () => {
				return new Promise( ( resolve ) => {
					document.addEventListener( 'copy', ( e ) => {
						resolve( e.clipboardData.getData( 'text/plain' ) );
					}, { once: true } );
					document.execCommand( 'copy' );
				} );
			} );

			expect( clipboardText ).not.toBe( 'Copying is not allowed.' );
		} );

		test( 'student cannot copy content', async ( { page } ) => {
			await logoutUser( page );
			await loginStudent( page, 'validcreds@email.tld', 'password' );
			await visitPage( page, 'integrity-test' );

			const clipboardText = await page.evaluate( () => {
				return new Promise( ( resolve ) => {
					document.addEventListener( 'copy', ( e ) => {
						resolve( e.clipboardData.getData( 'text/plain' ) );
					}, { once: true } );
					document.execCommand( 'copy' );
				} );
			} );

			expect( clipboardText ).toBe( 'Copying is not allowed.' );
		} );

		test( 'logged out user cannot copy content', async ( { page } ) => {
			await logoutUser( page );
			await visitPage( page, 'integrity-test' );

			const clipboardText = await page.evaluate( () => {
				return new Promise( ( resolve ) => {
					document.addEventListener( 'copy', ( e ) => {
						resolve( e.clipboardData.getData( 'text/plain' ) );
					}, { once: true } );
					document.execCommand( 'copy' );
				} );
			} );

			expect( clipboardText ).toBe( 'Copying is not allowed.' );
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
