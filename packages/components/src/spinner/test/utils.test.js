import { fakeQuery } from './__MOCKS__/jquery';

import { create, ensureElementList, find, loadStyles } from '../utils';
import { SIZE_SMALL, SIZE_DEFAULT, WRAPPER_CLASSNAME } from '../constants';

global.jQuery = fakeQuery;

describe( 'Spinner: utils', () => {
	describe( 'create()', () => {
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

		test.each( tests )( '$name', ( { size } ) => {
			document.body.innerHTML = '<div id="wrapper"></div>';
			const el = create( document.querySelector( '#wrapper' ), size );
			expect( el ).toBeInstanceOf( Element );
			expect( el.innerHTML ).toMatchSnapshot();
		} );
	} );

	describe( 'ensureElementList()', () => {
		const tests = [
			{
				name: 'NodeList',
				getInput: () => document.querySelectorAll( 'div' ),
			},
			{
				name: 'Element',
				getInput: () => document.querySelector( '.abc' ),
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
				getInput: () => global.jQuery( 'div' ),
			},
		];

		test.each( tests )( '$name input', ( { getInput } ) => {
			document.body.innerHTML = `
				<div class="abc"></div>
				<div class="def"></div>
			`;

			const input = getInput(),
				list = ensureElementList( input );

			expect( list ).toMatchSnapshot();
			list.forEach( ( el ) => {
				expect( el ).toBeInstanceOf( Element );
			} );

			// Make sure Elements aren't removed from the DOM.
			expect( document.body.innerHTML ).toMatchSnapshot();
		} );
	} );

	describe( 'find()', () => {
		test( 'No spinners found in wrapper', () => {
			document.body.innerHTML = `
				<div id="wrap">
					<div class="abc"></div>
				</div>
			`;

			expect( find( document.getElementById( 'wrap' ) ) ).toBeNull();
		} );

		test( 'No spinners that are direct children of the wrapper', () => {
			document.body.innerHTML = `
				<div id="wrap">
					<div class="abc"><div class="${ WRAPPER_CLASSNAME }"></div></div>
				</div>
			`;

			expect( find( document.getElementById( 'wrap' ) ) ).toBeUndefined();
		} );

		test( 'Spinner found', () => {
			document.body.innerHTML = `
				<div id="wrap">
					<div class="${ WRAPPER_CLASSNAME }" id="shouldbefound"></div>
				</div>
			`;

			const spinner = find( document.getElementById( 'wrap' ) );
			expect( spinner ).toBeInstanceOf( Element );
			expect( spinner.id ).toBe( 'shouldbefound' );
		} );
	} );

	test( 'loadStyles()', () => {
		document.head.innerHTML = '';

		// Load them.
		loadStyles();
		const headHTML = document.head.innerHTML;
		expect( headHTML ).toMatchSnapshot();

		// Doesn't load them again.
		loadStyles();
		expect( document.head.innerHTML ).toBe( headHTML );
	} );
} );
