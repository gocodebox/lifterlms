import { openPublishPanel } from '@wordpress/e2e-test-utils';

/**
 * Opens the post publish panel and publishes the current post.
 *
 * Replaces the function of the same name from `@wordpress/e2e-test-utils` because
 * it constantly errors due to awaiting `.components-snackbar` at the end of it.
 *
 * @since [version]
 *
 * @return {Promise} Promise which resolves when the close button element is successfully clicked.
 */
export async function publishPost() {
	await openPublishPanel();

	const publishButton = await page.waitForSelector(
		'.editor-post-publish-button:not([aria-disabled="true"])'
	);
	await publishButton.click();

	const closeButton = await page.waitForSelector(
		'.editor-post-publish-panel__header .components-button[aria-label="Close panel"]'
	);

	return closeButton.click();
}
