import { click } from './click';

/**
 * Clicks the button to save / update a post in the block editor.
 *
 * @since [version]
 *
 * @return {Promise} A promise that resolves when the button is successfully pressed.
 */
export async function updatePost() {
	const SELECTOR = '.editor-post-publish-button__button';
	page.waitForSelector( SELECTOR );
	return click( SELECTOR );
}
