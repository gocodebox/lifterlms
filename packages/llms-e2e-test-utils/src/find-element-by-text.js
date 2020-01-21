/**
 * Find an element by Text
 *
 * @link https://stackoverflow.com/a/47829000/400568
 *
 * @param  {String} string   Case-insensitive string to search.
 * @param  {String} selector Selector to search. Default "*".
 * @param  {String} leaf     Leaf of the element. Accepts 'outerHTML' (default) or 'innerHTML'.
 * @return {Array}
 */
export function findElementByText( string, selector = '*', leaf = 'outerHTML' ) {

	const
		regex = new RegExp( string, 'gmi' ),
		matcher = e => ( regex.test( e[ leaf ] ) ),
		elementArray = [ ...document.querySelectorAll( selector ) ];

	return elementArray.filter( matcher )

}
