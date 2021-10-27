const { getChangelogForVersion } = require( '../../src/utils' );

describe( 'getChangelogForVersion', () => {
	it.each( [ '5.4.0', '3.0.0', '1.0.1' ] )( 'should retrieve the changelog for the existing version %s', ( version ) => {
		const entry = getChangelogForVersion( version, process.cwd() + '/CHANGELOG.md' );
		expect( typeof entry ).toBe( 'object' );
		expect( entry.version ).toStrictEqual( version );
		expect( Object.keys( entry ) ).toStrictEqual( [ 'date', 'version', 'logs' ] );
	} );

	it( 'should return undefined for non-existent versions', () => {
		const entry = getChangelogForVersion( '0.0.1', process.cwd() + '/CHANGELOG.md' );
		expect( entry ).toBeUndefined();
	} );
} );
