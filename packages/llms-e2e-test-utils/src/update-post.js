import { click } from './click';

/**
 * Clicks the button to save / update a post in the block editor.
 *
 * @since 3.3.0
 *
 * @return {Promise} A promise that resolves when the button is successfully pressed.
 */
export async function updatePost() {
	return click( '.editor-post-publish-button__button' );
}
