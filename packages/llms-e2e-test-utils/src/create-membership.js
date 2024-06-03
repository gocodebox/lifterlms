import { createPost } from './create-post';

/**
 * Create and publish a new membership
 *
 * @since 2.2.0
 *
 * @param {string} title Membership title.
 * @return {number} The created membership's WP_Post ID.
 */
export async function createMembership( title = 'Test Membership' ) {
	return createPost( 'llms_membership', title );
}
