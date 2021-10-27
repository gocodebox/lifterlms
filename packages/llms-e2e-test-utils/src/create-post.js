import { createNewPost, publishPost } from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new post
 *
 * @since 2.2.0
 *
 * @param {string} postType WP_Post type.
 * @param {string} title    Post title.
 * @return {number} The created post's WP_Post ID.
 */
export async function createPost( postType, title = 'Test Course' ) {
	page.on( 'dialog', ( dialog ) => dialog.accept() );

	await createNewPost( {
		title,
		postType,
	} );

	await publishPost();

	return await page.evaluate( () =>
		wp.data.select( 'core/editor' ).getCurrentPostId()
	);
}
