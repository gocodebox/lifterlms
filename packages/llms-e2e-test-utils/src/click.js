/**
 * Click an elements by selector
 *
 * @since 2.0.0
 * @since [version] Always waitForSelector before clicking the element.
 *
 * @param {String} selector Element selector string.
 * @return {Void}
 */
export async function click( selector ) {
	await page.waitForSelector( selector );
	await page.$eval( selector, el => el.click() );
}
