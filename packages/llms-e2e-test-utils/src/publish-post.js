import { arePrePublishChecksEnabled } from '@wordpress/e2e-test-utils';

import { updatePost } from './update-post';

/**
 * Disables prepublish checks and clicks the post publish button.
 *
 * @since [version]
 *
 * @return {Promise} Promise which resolves when the close button element is successfully clicked.
 */
export async function publishPost() {
	const enabled = await arePrePublishChecksEnabled();
	if ( enabled ) {
		await page.evaluate( () => window.wp.data.dispatch( 'core/editor' ).disablePublishSidebar() );
	}

	return updatePost();
}
