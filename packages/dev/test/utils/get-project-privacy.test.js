jest.mock( '../../src/utils/get-project-slug' );

const getProjectSlug = require( '../../src/utils/get-project-slug' ),
	{ getProjectPrivacy, isProjectPublic, isProjectPrivate } = require( '../../src/utils/get-project-privacy' );

let mockedSlug;

getProjectSlug.mockImplementation( () => mockedSlug );

describe( 'getProjectPrivacy', () => {
	const testData = [
		[ 'Should return "public" for public repos', 'lifterlms', 'public' ],
		[ 'Should return "private" for private repos', 'lifterlms-groups', 'private' ],
		[ 'Should return "unknown" for invalid repos', 'fake-repo', 'unknown' ],
	];
	it.each( testData )( '%s', ( name, slug, expected ) => {
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
		mockedSlug = slug;
		expect( isProjectPublic() ).toBe( expected );
	} );
} );

describe( 'isProjectPrivate', () => {
	const testData = [
		[ 'Should return false for public repos', 'lifterlms', false ],
		[ 'Should return true for private repos', 'lifterlms-groups', true ],
		[ 'Should return undefined for invalid repos', 'fake-repo', undefined ],
	];
	it.each( testData )( '%s', ( name, slug, expected ) => {
		mockedSlug = slug;
		expect( isProjectPrivate() ).toBe( expected );
	} );
} );
