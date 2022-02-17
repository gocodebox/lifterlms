/**
 * Tests Bootstrap.
 *
 * @since Unknown
 * @version [version]
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

	page.on( 'dialog', ( dialog ) => dialog.accept() );

	page.on( 'console', ( log ) => {

		const shouldLog = ( _log ) => {

			// Skip logs by type.
			if ( [ 'info', 'log', 'endGroup' ].includes( log.type() ) ) {
				return false;
			}

			const logText = log.text();

			// Skip 403s.
			if ( logText.includes( 'Failed to load resource: the server responded with a status of 403 (Forbidden)' ) ) {
				return false;
			}

			if ( logText.includes( 'Failed to load resource: the server responded with a status of 404 (Not Found)' ) ) {
				return false;
			}

			// Skip core block update messages.
			if ( logText.includes( 'Updated Block: %s core/' ) ) {
				return false;
			}

			return true;

		};

		if ( ! shouldLog( log ) ) {
			return;
		}

		console.log( `[${ log.type()}] ${ log.text() }` );

	} );

	page.on( 'pageerror', ( err ) => {
		console.log( `[pageerror] ${ err.message }` );
	} );

} );
