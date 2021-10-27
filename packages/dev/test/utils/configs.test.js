const { getConfig, hasConfig } = require( '../../src/utils' );

describe( 'hasConfig', () => {
	it( 'should return `true` if the project has the specified config file', () => {
		expect( hasConfig( 'package' ) ).toBe( true );
		expect( hasConfig( 'composer' ) ).toBe( true );
	} );

	it( 'should return `false` if the project does not have the config or the config is empty', () => {
		expect( hasConfig( 'fake' ) ).toBe( false );
	} );
} );

describe( 'getConfig', () => {
	it( 'should return the config file as a JS object', () => {
		const expectedPkg = require( '../../../../package.json' ),
			expectedComposer = require( '../../../../composer.json' );

		expect( getConfig( 'package' ) ).toStrictEqual( expectedPkg );
		expect( getConfig( 'composer' ) ).toStrictEqual( expectedComposer );
	} );

	it( 'should return an empty object if the project does not have the specified config', () => {
		expect( getConfig( 'fake' ) ).toStrictEqual( {} );
	} );
} );
