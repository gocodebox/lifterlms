// Internal dependencies.
import { click } from './click';
import { setSelect2Option } from './set-select2-option';

// External dependencies.
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Enroll a student into a course
 *
 * This performs as "manual" enrollment using the enrollment
 * area on the course or membership.
 *
 * @since 2.2.0
 * @since 3.0.0 Use `waitForTimeout()` in favor of deprecated `waitFor()`.
 *
 * @param {number} postId    WP_Post ID.
 * @param {number} studentId WP_User ID.
 * @return {void}
 */
export async function enrollStudent( postId, studentId ) {
	await visitAdminPage( 'post.php', `post=${ postId }&action=edit` );

	await setSelect2Option( '#llms-add-student-select', studentId );

	await click( '#llms-enroll-students' );

	// Lazy waiting for ajax save.
	await page.waitForTimeout( 2000 );
}
