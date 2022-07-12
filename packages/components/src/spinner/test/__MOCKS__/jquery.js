// Create a mock jQuery for testing.
/**
 * @param  selector
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
