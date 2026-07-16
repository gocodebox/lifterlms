/**
 * Test the core learning loop: enroll in a free course, complete its lesson.
 *
 * @since 10.0.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	completeLesson,
	enrollInFreeCourse,
	loginStudent,
	logoutUser,
} from '../../utils/index.js';

test.describe( 'StudentEnrollment', () => {
	test( 'enrolls in a free course and completes a lesson', async ( { page } ) => {
		await logoutUser( page );
		await loginStudent( page, 'validcreds@email.tld', 'password' );

		await enrollInFreeCourse( page, 'free-course' );

		// Once enrolled, the lesson exposes the Mark Complete control.
		await page.goto( '/lesson/free-lesson/' );
		await expect( page.locator( '#llms_mark_complete' ) ).toBeVisible();

		await completeLesson( page, 'free-lesson' );

		// The lesson now reports completion.
		await expect( page.locator( '.llms-lesson-button-wrapper' ) ).toContainText(
			'Lesson Complete'
		);

		// And the course reports 100% progress for the enrolled student.
		await page.goto( '/course/free-course/' );
		await expect(
			page.locator( '.llms-progress .progress-bar-complete' )
		).toHaveAttribute( 'data-progress', '100%' );
	} );
} );
