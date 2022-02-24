import { trailingSlashIt, untrailingSlashIt } from '../';

describe( 'Formatting', () => {

	describe( 'trailingSlashIt', () => {

		const testData = [
			[ 'Should leave a string with a trailing slash unchanged', 'string/', 'string/' ],
			[ 'Should add a trailing slash to a string without one', 'string', 'string/' ],
		];
		it.each( testData )( '%s', ( name, input, expected ) => {
			expect( trailingSlashIt( input ) ).toBe( expected );
		} );

	} );

	describe( 'untrailingSlashIt', () => {

		const testData = [
			[ 'Should leave a string without a trailing slash unchanged', 'string', 'string' ],
			[ 'Should remove a trailing slash to a string with one', 'string/', 'string' ],
		];
		it.each( testData )( '%s', ( name, input, expected ) => {
			expect( untrailingSlashIt( input ) ).toBe( expected );
		} );

	} );

} );
