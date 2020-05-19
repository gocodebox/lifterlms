/**
 * Click an elements by selector
 *
 * @since [version]
 *
 * @param {String} selector Element selector string.
 * @return {Void}
 */
export async function click( selector ) {
	await page.$eval( selector, el => el.click() );
}
