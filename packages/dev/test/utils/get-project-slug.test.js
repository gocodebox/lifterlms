const { getProjectSlug } = require( '../../src/utils' );

describe( 'getProjectSlug', () => {
	it( 'should return the plugin slug even when the checkout directory differs', () => {
		expect( getProjectSlug() ).toBe( 'lifterlms' );
	} );
} );
