import { getAdminUrl } from '../';

describe( 'getAdminUrl', () => {

	afterEach( () => {
		delete window.llms;
	} );

	const testData = [
		[ 'https://example.tld/wp-admin/', 'https://example.tld/wp-admin' ],
		[ 'https://example.tld/wp-admin', 'https://example.tld/wp-admin' ],
		[ 'https://example.tld/custom/url/', 'https://example.tld/custom/url' ],
	];
	it.each( testData )( 'Should return the window variable without a trailing slash (input: %s)', ( input, expected ) => {

		window.llms = {
			admin_url: input,
		};
		expect( getAdminUrl() ).toBe( expected );

	} );

	it( 'Should return the default admin path if window.llms does not exist', () => {
		expect( getAdminUrl() ).toBe( '/wp-admin' );
	} );

	it( 'Should return the default admin path if window.llms.admin_url does not exist', () => {
		window.llms = {};
		expect( getAdminUrl() ).toBe( '/wp-admin' );
	} );

} );
