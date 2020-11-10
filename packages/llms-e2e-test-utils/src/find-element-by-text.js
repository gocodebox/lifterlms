const cssXPath = require( 'css-xpath' );

/**
 * Find an element by Text
 *
 * @since [version]
 *
 * @link https://stackoverflow.com/a/47829000/400568
 *
 * @param {String} string   Case-insensitive string to search.
 * @param {String} selector Selector to search. Default "*".
 * @return {Array}
 */
export async function findElementByText( string, selector = '*' ) {

	return await page.waitForXPath( `${ cssXPath( selector ) }[contains(text(), '${ string }')]`);

}
