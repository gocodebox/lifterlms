jest.mock( '../../src/utils/get-project-slug' );

// eslint-disable-next-line camelcase
const child_procces = require( 'child_process' ),
	getProjectSlug = require( '../../src/utils/get-project-slug' ),
	{ getProjectPrivacy, isProjectPublic, isProjectPrivate } = require( '../../src/utils/get-project-privacy' );

// Mocked return values.
let mockedSlug,
	mockedApiReturn;

jest.mock( 'child_process' );
child_procces.execSync.mockImplementation( () => mockedApiReturn );

getProjectSlug.mockImplementation( () => mockedSlug );

/**
 * Mock the API return retrieved by `gh api...`.
 *
 * @since [version]
 *
 * @param {boolean} isPublic Whether or not the repo is public. Pass `undefined` to for an "unknown" return.
 * @return {string|Object} A JSON string or object to be parsed. Returning an object causes an error for the test unknown responses.
 */
function getMockApiReturn( isPublic ) {
	if ( undefined === isPublic ) {
		return {}; // Causes an error which is enough to get the proper return.
	}
	return JSON.stringify( { private: ! isPublic } );
}

describe( 'getProjectPrivacy', () => {
	const testData = [
		[ 'Should return "public" for public repos', 'lifterlms', 'public', true ],
		[ 'Should return "private" for private repos', 'lifterlms-groups', 'private', false ],
		[ 'Should return "unknown" for invalid repos', 'fake-repo', 'unknown', undefined ],
	];
	it.each( testData )( '%s', ( name, slug, expected, isPublic ) => {
		mockedApiReturn = getMockApiReturn( isPublic );
		mockedSlug = slug;
		expect( getProjectPrivacy() ).toBe( expected );
	} );
} );

describe( 'isProjectPublic', () => {
	const testData = [
		[ 'Should return true for public repos', 'lifterlms', true ],
		[ 'Should return false for private repos', 'lifterlms-groups', false ],
		[ 'Should return undefined for invalid repos', 'fake-repo', undefined ],
	];
	it.each( testData )( '%s', ( name, slug, expected ) => {
		mockedApiReturn = getMockApiReturn( expected );
		mockedSlug = slug;
		expect( isProjectPublic() ).toBe( expected );
	} );
} );

describe( 'isProjectPrivate', () => {
	const testData = [
		[ 'Should return false for public repos', 'lifterlms', false, true ],
		[ 'Should return true for private repos', 'lifterlms-groups', true, false ],
		[ 'Should return undefined for invalid repos', 'fake-repo', undefined, undefined ],
	];
	it.each( testData )( '%s', ( name, slug, expected, isPublic ) => {
		mockedApiReturn = getMockApiReturn( isPublic );
		mockedSlug = slug;
		expect( isProjectPrivate() ).toBe( expected );
	} );
} );
