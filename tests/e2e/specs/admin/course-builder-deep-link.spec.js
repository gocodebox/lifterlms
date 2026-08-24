/**
 * Course Builder — deep linking to a lesson opens the lesson editor panel.
 *
 * Guards against the reporting screen lesson/quiz links no longer opening
 * the relevant editor panel after the builder action icons were changed from
 * anchors to buttons.
 *
 * @since 10.1.2
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Course Builder / Deep Link', () => {

	test( 'deep linking to a lesson opens the lesson editor panel', async ( {
		page,
		requestUtils,
	} ) => {

		const stamp = Date.now();

		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: `Deep Link Course ${ stamp }`,
				content: 'x',
				status: 'publish',
			},
		} );

		const section = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/sections',
			data: {
				title: 'Section 1',
				parent_id: course.id,
				order: 1,
			},
		} );

		const lesson = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/lessons',
			data: {
				title: `Deep Link Lesson ${ stamp }`,
				content: 'lesson content',
				status: 'publish',
				parent_id: section.id,
				order: 1,
			},
		} );

		await page.goto( `/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }#lesson:${ lesson.id }` );
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );

		await expect( page.locator( '#llms-editor-lesson' ) ).toBeVisible( { timeout: 10000 } );

	} );

} );
