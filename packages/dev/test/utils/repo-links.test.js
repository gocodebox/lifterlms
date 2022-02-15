jest.mock( '../../src/utils/get-project-slug' );

const { getFileLink, getRepoLink, getIssueLink } = require( '../../src/utils' ),
	getProjectSlug = require( '../../src/utils/get-project-slug' );

let mockedSlug = '';
getProjectSlug.mockImplementation( () => mockedSlug ? mockedSlug : 'lifterlms' );

describe( 'repoLinks', () => {

	beforeEach( () => {
		mockedSlug = 'lifterlms';
	} );

	describe( 'getFileLink', () => {
		const path   = 'inc/file.php',
			testData = [
			[ 'Should use trunk if a branch is not specified', 'https://github.com/gocodebox/lifterlms/blob/trunk/inc/file.php' ],
			[ 'Should use the specified branch', 'https://github.com/gocodebox/lifterlms/blob/dev-123/inc/file.php', 'dev-123' ],
			[ 'Should use the specified version tag', 'https://github.com/gocodebox/lifterlms/blob/v1.0.0/inc/file.php', 'v1.0.0' ],
			[ 'Should use the specified prerelease version tag', 'https://github.com/gocodebox/lifterlms/blob/v1.0.0-beta.3/inc/file.php', 'v1.0.0-beta.3' ],
		];
		it.each( testData )( '%s', ( name, expected, branch = undefined ) => {
			expect( getFileLink( path, branch ) ).toBe( expected );
		} );
	} );

	describe( 'getIssueLink', () => {
		const testData = [
			[ 'Should accept issue references to the current project', 'https://github.com/gocodebox/lifterlms/issues/123', '#123' ],
			[ 'Should accept issue references to the another project', 'https://github.com/org/repo/issues/456', 'org/repo#456' ],
		];
		it.each( testData )( '%s', ( name, expected, issue ) => {
			expect( getIssueLink( issue ) ).toBe( expected );
		} );
	} );

	describe( 'getRepoLink', () => {
		const testData = [
			[ 'Should use the default slug and organization when no values (undefined) are provided', 'https://github.com/gocodebox/lifterlms', undefined, undefined ],
			[ 'Should use the default slug and organization when null values are provided', 'https://github.com/gocodebox/lifterlms', null, null ],
			[ 'Should use the default slug and organization when empty values are provided', 'https://github.com/gocodebox/lifterlms', '', false ],
			[ 'Should use the specified slug and organization when provided', 'https://github.com/org/slug', 'slug', 'org' ],
		];
		it.each( testData )( '%s', ( name, expected, project, org ) => {
			mockedSlug = project;
			expect( getRepoLink( project, org ) ).toBe( expected );
		} );
	} );

} );

