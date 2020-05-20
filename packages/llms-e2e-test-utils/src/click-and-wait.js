import { click } from './click';

/**
 * Click an element and wait for navigation.
 *
 * @since 3.37.8
 *
 * @param {String} selector  Query selector for the DOM element to click.
 * @param {String} waitUntil Network connection to wait for, defaults to 'networkidle2'.
 * @return {void}
 */
export async function clickAndWait( selector, waitUntil ) {

	waitUntil = waitUntil || 'networkidle2';
	await Promise.all( [
		click( selector ),
		page.waitForNavigation( { waitUntil } ),
	] );

}
