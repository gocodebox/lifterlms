/**
 * Tests Bootstrap.
 *
 * @since Unknown
 * @version 2.0.0
 */

require( 'regenerator-runtime' );

const { existsSync } = require( 'fs' ),
	{ execSync } = require( 'child_process' );

// Load dotenv files.
const envFiles = [ '.llmsenv', '.llmsenv.dist' ];
envFiles.some( ( file ) => {
	const path = `${ process.cwd() }/${ file }`;
	if ( existsSync( file ) ) {
		require( 'dotenv' ).config( { path } );
	}
} );

if ( ! process.env.WP_VERSION ) {

	try {
		const wpVersion = execSync( 'composer run env wp core version', { stdio : 'pipe' } ).toString();
		if ( wpVersion ) {
			process.env.WP_VERSION = wpVersion;
		}
	} catch ( e ) {
		console.warn( 'Unable to automatically determine the WordPress Core Version. You can define the WP_VERSION as an environment variable. Otherwise "latest" is assumed as the WP_VERSION.' );
		process.env.WP_VERSION = 'latest';
	}

}

// Setup the WP Base URL for e2e Tests.
if ( ! process.env.WORDPRESS_PORT ) {
	process.env.WORDPRESS_PORT = '8080';
}

// Allow easy override of the default base URL, for example if we want to point to a live URL.
if ( ! process.env.WP_BASE_URL ) {
	process.env.WP_BASE_URL = `http://localhost:${ process.env.WORDPRESS_PORT }`;
}

// Retry tests automatically to prevent against false positives.
jest.retryTimes( 2 );

// The Jest timeout is increased because these tests are a bit slow.
jest.setTimeout( process.env.PUPPETEER_TIMEOUT || 100000 );

beforeAll( async() => {

	page.on( 'console', ( message ) => {
		if ( [ 'info', 'log' ].includes( message.type() ) ) {
			return;
		}
		console.log( message.type(), message.text() );
	} );

	page.on( 'pageerror', ( err ) => {
		console.log( err.message );
	} );

} );
