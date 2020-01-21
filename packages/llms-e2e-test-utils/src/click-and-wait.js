/**
 * Click an element and wait for navigation.
 *
 * @since  [version]
 *
 * @param  {String} selector  Query selector for the DOM element to click.
 * @param  {String} waitUntil Network connection to wait for, defaults to 'networkidle2'.
 * @return {void}
 */
export async function clickAndWait( selector, waitUntil ) {

	waitUntil = waitUntil || 'networkidle2';
	await Promise.all( [
		page.$eval( selector, el => el.click() ),
		page.waitForNavigation( { waitUntil: waitUntil } ),
	] );

}
