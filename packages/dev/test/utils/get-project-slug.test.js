const { getProjectSlug } = require( '../../src/utils' );

describe( 'getProjectSlug', () => {
	it( 'should return the directory name of the project', () => {
		expect( getProjectSlug() ).toBe( 'lifterlms' );
	} );
} );
