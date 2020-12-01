
const { findElementByText } = require( './find-element-by-text' );

/**
 * Click an element by Text
 *
 * @since 2.2.0
 *
 * @param {String} string   Case-insensitive string to search.
 * @param {String} selector Selector to search. Default "*".
 * @return {Array}
 */
export async function clickElementByText( string, selector = '*' ) {

	const el = await findElementByText( string, selector );
	await el.click();

}
