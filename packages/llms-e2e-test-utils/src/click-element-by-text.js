const { findElementByText } = require( './find-element-by-text' );

/**
 * Click an element by Text
 *
 * @since 2.2.0
 *
 * @param {string} string   Case-insensitive string to search.
 * @param {string} selector Selector to search. Default "*".
 * @return {void}
 */
export async function clickElementByText( string, selector = '*' ) {
	const el = await findElementByText( string, selector );
	await el.click();
}
