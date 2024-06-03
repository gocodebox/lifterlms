import getCounterTextColor from '../get-counter-text-color';

describe( 'getCounterTextColor()', () => {
	const testData = [
		[ 'No min and no max', 'initial', 5, { min: null, max: null } ],
		[ 'Under min', 'red', 5, { min: 10, max: null, colorError: 'red' } ],
		[ 'Over max', 'red', 5, { min: null, max: 3, colorError: 'red' } ],
		[ 'Approaching max', 'orange', 9, { min: null, max: 10, colorWarning: 'orange' } ],
		[ 'Within min and max', 'initial', 8, { min: 7, max: 10 } ],
	];
	test.each( testData )( '%s', ( testName, expected, count, opts ) => {
		expect( getCounterTextColor( count, opts ) ).toBe( expected );
	} );
} );
