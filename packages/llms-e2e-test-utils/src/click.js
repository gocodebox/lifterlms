/**
 * Click an elements by selector
 *
 * @since 2.0.0
 * @since 2.2.2 Always waitForSelector before clicking the element.
 *
 * @param {string} selector Element selector string.
 * @return {void}
 */
export async function click( selector ) {
	const target = await page.$( selector );
	target.scrollIntoView();
	await page.waitForSelector( selector );
	await page.$eval( selector, ( el ) => el.click() );
}
