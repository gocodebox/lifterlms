import { wpVersionCompare } from './wp-version-compare';

/**
 * Retrieves the textContent of the lesson post's title element.
 *
 * This function uses a dynamically-determined selector based on the current WP version (and assumed theme)
 * run by default with that version.
 *
 * @since [version]
 *
 * @return {Promise} A promise that resolves to return the element's text content.
 */
export function getPostTitleTextContent() {
	return page.$eval( getPostTitleSelector(), el => el.textContent )
}

/**
 * Retrieves the CSS selector for the post's title element.
 *
 * On 5.9+ we're testing against the 2022 theme, on 5.8 & earlier we're using 2021.
 *
 * @since [version]
 *
 * @return {Promise} A promise that resolves to return the element's text content.
 */
export function getPostTitleSelector() {
	return wpVersionCompare( '5.9', '>=' ) ? '.wp-block-post-title' : '.entry-title';
}
