export const defaultState = {
	plans: {},
	errors: [],
};

export const mockErrors = [
	{ name: 'error-1', message: 'An error' },
	{ name: 'error-2', message: 'Another error' },
];

export const mockPlans = {
	123: { id: 123, price: 4.56, post_id: 321 },
	789: { id: 789, price: 1011.12, post_id: 987 },
};

export const mockApiError = {
	err: true,
	code: 'fake-api-err',
}
