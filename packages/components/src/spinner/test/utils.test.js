import { fakeQuery } from './__MOCKS__/jquery';

import { get, start, stop } from '../';
import { create, ensureElementList, loadStyles } from '../utils';
import { SIZE_SMALL, SIZE_DEFAULT } from '../constants';

global.jQuery = fakeQuery;

describe('Spinner: utils', () => {
	describe('create() ', () => {
		const tests = [
			{
				name: 'Default size (not specified)',
				size: undefined,
			},
			{
				name: 'Default size (passed)',
				size: SIZE_DEFAULT,
			},
			{
				name: 'Small size',
				size: SIZE_SMALL,
			},
		];

		test.each(tests)('$name', ({ size }) => {
			document.body.innerHTML = '<div id="wrapper"></div>';
			const el = create(document.querySelector('#wrapper'), size);
			expect(el).toBeInstanceOf(Element);
			expect(el.innerHTML).toMatchSnapshot();
		});
	});

	describe('ensureElementList()', () => {
		const tests = [
			{
				name: 'NodeList',
				getInput: () => document.querySelectorAll('div'),
			},
			{
				name: 'Element',
				getInput: () => document.querySelector('.abc'),
			},
			{
				name: 'String (single)',
				getInput: () => '.def',
			},
			{
				name: 'String (multiple)',
				getInput: () => 'div',
			},
			{
				name: 'jQuery',
				getInput: () => global.jQuery('div'),
			},
		];

		test.each(tests)('$name input', ({ getInput }) => {
			document.body.innerHTML = `
				<div class="abc"></div>
				<div class="def"></div>
			`;

			const input = getInput(),
				list = ensureElementList(input);

			expect(list).toMatchSnapshot();
			list.forEach((el) => {
				expect(el).toBeInstanceOf(Element);
			});

			// Make sure Elements aren't removed from the DOM.
			expect(document.body.innerHTML).toMatchSnapshot();
		});
	});

	test('loadStyles()', () => {
		document.head.innerHTML = '';

		// Load them.
		loadStyles();
		const headHTML = document.head.innerHTML;
		expect(headHTML).toMatchSnapshot();

		// Doesn't load them again.
		loadStyles();
		expect(document.head.innerHTML).toBe(headHTML);
	});
});
