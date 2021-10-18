/**
 * Tests Bootstrap.
 *
 * @since Unknown
 * @version [version]
 */

require( 'regenerator-runtime' );

const { existsSync } = require( 'fs' );

// Load dotenv files.
const envFiles = [ '.llmsenv', '.llmsenv.dist' ];
envFiles.some( ( file ) => {
	const path = `${ process.cwd() }/${ file }`;
	if ( existsSync( file ) ) {
		require( 'dotenv' ).config( { path } );
	}
} );

// Setup the WP Base URL for e2e Tests.
if ( ! process.env.WORDPRESS_PORT ) {
	process.env.WORDPRESS_PORT = '8080';
}

// Allow easy override of the default base URL, for example if we want to point to a live URL.
if ( ! process.env.WP_BASE_URL ) {
	process.env.WP_BASE_URL = `http://localhost:${ process.env.WORDPRESS_PORT }`;
}


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
