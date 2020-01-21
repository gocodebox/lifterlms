
const { findElementByText } = require( './find-element-by-text' );

/**
 * Click an element by Text
 *
 * @param  {String} string   Case-insensitive string to search.
 * @param  {String} selector Selector to search. Default "*".
 * @param  {String} leaf     Leaf of the element. Accepts 'outerHTML' (default) or 'innerHTML'.
 * @return {Array}
 */
export async function clickElementByText( string, selector = '*', leaf = 'outerHTML' ) {

	await page.exposeFunction( 'findElementByText', findElementByText )

	await page.evaluate( ( string, selector, leaf ) => {
		return findElementByText( string, selector, leaf )[0].click();
	}, string, selector, leaf );

}
