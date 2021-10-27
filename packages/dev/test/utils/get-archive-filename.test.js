const { getArchiveFilename } = require( '../../src/utils' );

describe( 'getArchiveFilename', () => {
	it( 'should assume the current version when no version is specified', () => {
		const { version } = require( '../../../../package.json' );
		expect( getArchiveFilename() ).toBe( `lifterlms-${ version }.zip` );
	} );

	it( 'should add the specified version', () => {
		expect( getArchiveFilename( '9.9.9' ) ).toBe( 'lifterlms-9.9.9.zip' );
	} );
} );
