import wordCountModule, { setOptions } from '../module';

function MockQuill( container ) {

	this.container = container;

	this.getText = function() {
		return container.innerHTML;
	};

	this.on = function( action, callback ) {
		callback();
	};

	return this;
};

describe( 'module', () => {

	describe( 'setOptions()', () => {
		const testData = [
			[ 'Use defaults', {}, undefined ],
			[ 'Specify min and max', { min: 5, max: 1000 }, undefined ],
			[ 'Specify partial l10n object', { l10n: { min: 'Min' } }, undefined ],
			[ 'Specify onChange function', { onChange: () => true }, true ],
		];
		test.each( testData )( '%s', ( testName, opts, expectedCallbackResult ) => {
			const merged = setOptions( opts );
			expect( setOptions( opts ) ).toMatchSnapshot();
			expect( merged.onChange() ).toStrictEqual( expectedCallbackResult );
		} );
	} );

	describe( 'module()', () => {
		const testData = [
			[ 'No starting text and default options', '', {} ],
			[ 'Latin characters and specified options', 'Lorem ipsum dolor sit.', { min: 1, max: 1000, l10n: { max: 'Max' } } ],
			[ 'Chinese characters and default options', '我去了一家中餐馆，买了一条面包。', { min: 1, max: 14 } ],
		];
		test.each( testData )( '%s', ( testName, startingText, opts ) => {

			const container = document.createElement( 'div' );

			container.id        = 'ql-editor';
			container.innerHTML = startingText;

			document.body.appendChild( container );	

			wordCountModule( new MockQuill( container ), opts );
			expect( document.body.innerHTML ).toMatchSnapshot();

			document.body.innerHTML = '';

		} );
	} );

} );
