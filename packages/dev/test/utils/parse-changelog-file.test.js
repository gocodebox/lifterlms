const
	semver = require( 'semver' ),
	{ parseChangelogFile } = require( '../../src/utils' );

describe( 'parseChangelogFile', () => {
	it( 'should parse the changelog file', () => {
		const parsed = parseChangelogFile( process.cwd() + '/CHANGELOG.md' );

		parsed.forEach( ( { date, version, logs } ) => {
			// Valid version.
			expect( semver.valid( version ) ).toStrictEqual( version );

			// Valid date.
			expect( Date.parse( date ) ).not.toBeNaN();

			// Should be a string.
			expect( typeof logs ).toStrictEqual( 'string' );
		} );
	} );
} );
