/**
 * Student dashboard and catalog accessibility.
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { loginStudent, logoutUser, visitPage } from '../../utils/index.js';

test.describe( 'DashboardAccessibility', () => {

	test( 'prefixes the logged-out dashboard document title', async ( { page } ) => {
		await logoutUser( page );
		await visitPage( page, 'dashboard' );
		await expect( page ).toHaveTitle( /Log In/ );
	} );

	test( 'uses the current tab in the document title and navigation', async ( { page } ) => {
		await logoutUser( page );
		await loginStudent( page, 'validcreds@email.tld', 'password' );

		await expect( page ).toHaveTitle( /Dashboard/ );
		await expect( page ).not.toHaveTitle( /My Courses/ );

		await visitPage( page, 'dashboard/my-courses' );

		await expect( page ).toHaveTitle( /My Courses/ );
		await expect( page.locator( '.llms-sd-item.current .llms-sd-link' ) ).toHaveAttribute( 'aria-current', 'page' );
		await expect( page.locator( '.llms-sd-mobile-nav' ) ).toHaveCount( 1 );
		await expect( page.locator( '.llms-sd-mobile-nav button[type="submit"]' ) ).toHaveText( 'Go' );
		await expect( page.locator( '#llms-sd-mobile-nav' ) ).not.toHaveAttribute( 'onchange', /.+/ );
	} );

	test( 'links only the course title in the catalog', async ( { page, requestUtils } ) => {
		const title = `A11y Catalog ${ Date.now() }`;
		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title,
				content: 'Course content.',
				status: 'publish',
			},
		} );

		try {
			await visitPage( page, 'courses' );
			const item = page.locator( '.llms-loop-item', { hasText: title } );
			await expect( item ).toHaveCount( 1 );
			await expect( item.locator( 'a' ) ).toHaveCount( 1 );
			await expect( item.locator( 'h2.llms-loop-title a' ) ).toHaveText( title );
		} finally {
			try {
				await requestUtils.rest( {
					method: 'DELETE',
					path: `/llms/v1/courses/${ course.id }?force=true`,
				} );
			} catch {
				// A successful delete can return an empty body, which the REST helper cannot parse.
			}
		}
	} );

} );
