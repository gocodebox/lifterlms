import { fakeQuery } from './__MOCKS__/jquery';

import { get, start, stop } from '../';
import { SIZE_SMALL, SIZE_DEFAULT } from '../constants';

global.jQuery = fakeQuery;

describe('Spinner: public API', () => {
	describe('get() ', () => {
		test('Autoload styles', () => {
			document.head.innerHTML = '';
			get('div', SIZE_DEFAULT, false);
			expect(document.head.innerHTML).toMatchSnapshot();
		});

		test('Empty return', () => {
			document.body.innerHTML = '<div class="abc"></div>';
			expect(get('.xyz', SIZE_DEFAULT, false)).toBeNull();
		});

		test('Create and return', () => {
			document.body.innerHTML = '<div class="abc"></div>';
			const createResult = get('.abc', SIZE_DEFAULT, false),
				body = document.body.innerHTML;
			expect(createResult).toBeInstanceOf(Element);
			expect(body).toMatchSnapshot();

			// Don't need to create it again.
			const alreadExistsResult = get('.abc', SIZE_DEFAULT, false);
			expect(alreadExistsResult).toBe(createResult);
			expect(document.body.innerHTML).toBe(body);
		});

		test('Create small size', () => {
			document.body.innerHTML = '<div class="abc"></div>';
			const res = get('.abc', SIZE_SMALL, false),
				body = document.body.innerHTML;
			expect(res).toBeInstanceOf(Element);
			expect(body).toMatchSnapshot();
		});

		test('Return a jQuery selection', () => {
			document.body.innerHTML = '<div class="abc"></div>';
			expect(get('.abc', SIZE_DEFAULT)).toBeInstanceOf(jQuery);
		});
	});

	test('start() and stop()', () => {
		document.body.innerHTML =
			'<div class="abc xyz"></div><div class="def xyz"></div>';
		get('.abc');
		get('.def');

		start('.xyz');
		expect(document.body.innerHTML).toMatchSnapshot();

		stop('.xyz');
		expect(document.body.innerHTML).toMatchSnapshot();
	});
});
