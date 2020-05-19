import {
	createNewPost,
	publishPost,
} from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new course
 *
 * @param string title Course title.
 * @return int The created course's WP_Post ID.
 */
export async function createCourse( title = 'Test Course' ) {

	page.on( 'dialog', dialog => dialog.accept() );

	await createNewPost( {
		title,
		postType: 'course',
	} );

	await publishPost();

	return await page.evaluate( () => wp.data.select( 'core/editor' ).getCurrentPostId() );

}

