/**
 * Mock jQuery class.
 *
 * Generates a minimal mocked jQuery object for use by unit tests.
 *
 * @since 1.1.0
 *
 * @param {string} selector A selector string that can be passed to `document.querySelectorAll()`.
 * @return {Object} A mock jQuery object.
 */
function fakeQuery( selector ) {
	return {
		isFakeQuery: true,
		toArray: () => document.querySelectorAll( selector ),
	};
}
Object.defineProperty( fakeQuery, Symbol.hasInstance, {
	value: ( instance ) => {
		return instance.isFakeQuery;
	},
} );

export { fakeQuery };
