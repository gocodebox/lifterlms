const
	{ getNextVersion } = require( '../../src/utils' );

describe( 'getNextVersion', () => {
	describe( 'increment=patch', () => {
		const testData = [
			[ '1.0.0', '1.0.1' ],
			[ '5.1.2', '5.1.3' ],
			[ '0.12.9', '0.12.10' ],
			[ '1.0.0-beta.1', '1.0.0' ],
			[ '999.9.9-rc.1', '999.9.9' ],
		];
		it.each( testData )( 'Should increment %s to %s', ( current, next ) => {
			expect( getNextVersion( current, 'patch' ) ).toBe( next );
		} );
	} );

	describe( 'increment=minor', () => {
		const testData = [
			[ '1.0.0', '1.1.0' ],
			[ '5.3.9', '5.4.0' ],
			[ '0.54.3', '0.55.0' ],
			[ '1.0.0-beta.1', '1.0.0' ],
			[ '999.9.9-rc.1', '999.10.0' ],
		];
		it.each( testData )( 'Should increment %s to %s', ( current, next ) => {
			expect( getNextVersion( current, 'minor' ) ).toBe( next );
		} );
	} );

	describe( 'increment=major', () => {
		const testData = [
			[ '1.0.0', '2.0.0' ],
			[ '1.15.999', '2.0.0' ],
			[ '5.3.9', '6.0.0' ],
			[ '0.54.3', '1.0.0' ],
			[ '1.0.0-beta.1', '1.0.0' ],
			[ '999.9.9-rc.1', '1000.0.0' ],
		];
		it.each( testData )( 'Should increment %s to %s', ( current, next ) => {
			expect( getNextVersion( current, 'major' ) ).toBe( next );
		} );
	} );

	describe( 'increment=prerelease preid=beta', () => {
		const testData = [
			[ '1.0.0', '1.0.1-beta.1' ],
			[ '1.0.0-beta.1', '1.0.0-beta.2' ],
			[ '3.2.5', '3.2.6-beta.1' ],
		];
		it.each( testData )( 'Should increment %s to %s', ( current, next ) => {
			expect( getNextVersion( current, 'prerelease', 'beta' ) ).toBe( next );
		} );
	} );
} );
