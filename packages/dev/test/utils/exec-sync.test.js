// eslint-disable-next-line camelcase
const childProcess = require( 'child_process' ),
	{ execSync } = require( '../../src/utils' );

jest.mock( 'child_process' );

// Mock submitted command options.
let cmdOpts = {};

childProcess.execSync.mockImplementation( ( cmd, opts ) => cmdOpts = opts );

/**
 * Test execSync utility.
 *
 * Note this test doesn't attempt to ensure the output is correct as it assumes
 * that child_proccess.execSync() works as expected. This tests only our logic
 * to ensure that we properly merge the options passed to execSync.
 */
describe( 'execSync', () => {
	// Reset mocked options.
	beforeEach( () => {
		cmdOpts = {};
	} );

	it( 'should output command output by default', () => {
		execSync( 'echo "HELLO"' );
		expect( cmdOpts ).toStrictEqual( { stdio: 'inherit' } );
	} );

	it( 'should silence output', () => {
		execSync( 'echo "HELLO"', true );
		expect( cmdOpts ).toStrictEqual( { stdio: 'pipe' } );
	} );

	it( 'should add accept additional arguments', () => {
		execSync( 'echo "HELLO"', true, { timeout: 1000 } );
		expect( cmdOpts ).toStrictEqual( { stdio: 'pipe', timeout: 1000 } );
	} );
} );
