/**
 * Course Builder — deleting the last section leaves one empty section.
 *
 * A new course still opens with a demo section and three lessons. Deleting that
 * section leaves a single section and no lessons, including after a reload.
 *
 * @since 10.3.0
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Course Builder / Delete last section', () => {

	test( 'deleting the demo section leaves one empty section', async ( {
		page,
		requestUtils,
	} ) => {

		const stamp = Date.now();

		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: `Builder Delete Section ${ stamp }`,
				content: 'x',
				status: 'publish',
			},
		} );

		await page.goto( `/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }` );
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );

		await expect( page.locator( '.llms-section' ) ).toHaveCount( 1 );
		await expect( page.locator( '.llms-lesson' ) ).toHaveCount( 3 );

		page.once( 'dialog', ( dialog ) => dialog.accept() );

		const dismissed = page.waitForResponse( ( response ) => {
			const body = response.request().postData() || '';
			return response.url().includes( 'admin-ajax.php' ) && body.includes( 'dismiss_starter' );
		} );

		await page.locator( '.llms-section .trash--section' ).click();
		await dismissed;

		await expect( page.locator( '.llms-section' ) ).toHaveCount( 1 );
		await expect( page.locator( '.llms-lesson' ) ).toHaveCount( 0 );

		await page.reload();
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );

		await expect( page.locator( '.llms-section' ) ).toHaveCount( 1 );
		await expect( page.locator( '.llms-lesson' ) ).toHaveCount( 0 );

	} );

} );
