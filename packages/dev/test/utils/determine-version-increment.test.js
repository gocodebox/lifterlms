jest.mock( '../../src/utils/get-changelog-entries' );

const getChangelogEntries = require( '../../src/utils/get-changelog-entries' ),
	determineVersionIncrement = require( '../../src/utils/determine-version-increment' );

let mockedEntries;

/**
 * Create mock entries for the return of getChangelogEntries().
 *
 * @since 0.0.2
 *
 * @param {string} significance Highest significance to add to the list.
 * @return {Object[]} Array of partial log entry objects.
 */
function setupMockEntries( significance ) {
	const entries = [];

	for ( let i = 0; i <= 3; i++ ) {
		entries.push( { significance: 'patch' } );
	}

	entries.push( { significance } );

	// Shuffle entries.
	return entries.slice().sort( () => Math.random() - 0.5 );
}

getChangelogEntries.mockImplementation( () => mockedEntries );

describe( 'determineVersionIncrement', () => {
	it.each( [ 'major', 'minor', 'patch' ] )( 'Should return "%s" when it is the highest significance', ( significance ) => {
		mockedEntries = setupMockEntries( significance );
		expect( determineVersionIncrement( 'dir', '1.0.0' ) ).toBe( significance );
	} );

	it.each( [ 'major', 'minor', 'patch' ] )( 'Should return "pre%s" when it is the highest significance, a preid is specified, and the current version is not a prerelease', ( significance ) => {
		mockedEntries = setupMockEntries( significance );
		expect( determineVersionIncrement( 'dir', '1.0.0', 'beta' ) ).toBe( `pre${ significance }` );
	} );

	const testData = [
		[ '1.0.0-beta.1', 'beta' ],
		[ '1.3.0-rc.3', 'alpha' ],
		[ '5.2.0-alpha.23', 'rc' ],
	];
	it.each( testData )( 'Should return "prerelease" for currentVersion=%s and preid=%s', ( currVersion, preid ) => {
		expect( determineVersionIncrement( 'dir', currVersion, preid ) ).toBe( 'prerelease' );
	} );

	it( 'Should return "patch" when no entries can be found', () => {
		mockedEntries = [];
		expect( determineVersionIncrement( 'dir', '1.0.0' ) ).toBe( 'patch' );
	} );
} );
