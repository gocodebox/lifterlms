import { createNewPost, setPostContent } from '@wordpress/e2e-test-utils';

import { publishPost } from './publish-post';

/**
 * Create and publish a new post
 *
 * @since 2.2.0
 * @since [version] Set the post's content with `setPostContent()`.
 *
 * @param {string}  postType WP_Post type.
 * @param {string}  title    Post title.
 * @param {?string} content  Post content.
 * @return {number} The created post's WP_Post ID.
 */
export async function createPost( postType, title = 'Test Course', content = null ) {
	await createNewPost( {
		title,
		postType,
	} );

	if ( content ) {
		await setPostContent( content );
	}

	await publishPost();

	return await page.evaluate( () =>
		wp.data.select( 'core/editor' ).getCurrentPostId()
	);
}
