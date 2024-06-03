import formatNumber from '../format-number';

describe( 'formatNumber()', () => {
	const testData = [
		[ 1000, '1,000' ],
		[ 1000.95, '1,000.95' ],
		[ '1000', '1,000' ],
		[ 1, '1' ],
		[ 1.00, '1' ],
		[ 0.01, '0.01' ],
		[ 9999999, '9,999,999' ],
	];
	test.each( testData )( '%s', ( input, expected ) => {
		expect( formatNumber( input ) ).toStrictEqual( expected );
	} );
} );
