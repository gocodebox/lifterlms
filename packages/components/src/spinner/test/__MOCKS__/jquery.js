// Create a mock jQuery for testing.
function fakeQuery(selector) {
	return {
		isFakeQuery: true,
		toArray: () => document.querySelectorAll(selector),
	};
}
Object.defineProperty(fakeQuery, Symbol.hasInstance, {
	value: (instance) => {
		return instance.isFakeQuery;
	},
});

export { fakeQuery };
