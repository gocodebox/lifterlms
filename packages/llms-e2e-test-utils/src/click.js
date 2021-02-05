/**
 * Click an elements by selector
 *
 * @since 2.0.0
 * @since 2.2.2 Always waitForSelector before clicking the element.
 *
 * @param {String} selector Element selector string.
 * @return {Void}
 */
export async function click( selector ) {
	await page.waitForSelector( selector );
	await page.$eval( selector, el => el.click() );
}
