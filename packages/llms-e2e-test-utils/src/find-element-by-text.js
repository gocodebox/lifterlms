const cssXPath = require( 'css-xpath' );

/**
 * Find an element by Text
 *
 * @since 2.2.0
 *
 * @see {@link https://stackoverflow.com/a/47829000/400568}
 *
 * @param {string} string   Case-insensitive string to search.
 * @param {string} selector Selector to search. Default "*".
 * @return {Array} Element.
 */
export async function findElementByText( string, selector = '*' ) {
	return await page.waitForXPath(
		`${ cssXPath( selector ) }[contains(text(), '${ string }')]`
	);
}
