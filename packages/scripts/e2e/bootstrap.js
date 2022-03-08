/**
 * Tests Bootstrap.
 *
 * @since Unknown
 * @version [version]
 */

require( 'regenerator-runtime' );

const { existsSync } = require( 'fs' ),
	{ execSync } = require( 'child_process' ),
	{ diff } = require('jest-diff');

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
// jest.retryTimes( 2 );

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

	page.on( 'error', ( err ) => {
		console.log( `[error] ${ err.message }`, err );
	} );

} );


expect.extend( {

	/**
	 * A custom matcher for comparing strings that may or may not contain "smart" quotes.
	 *
	 * This helps us test code on FSE (block themes) when `wp_texturize()` is run against the HTML template. In
	 * LifterLMS we have quite a few notices that are run through the function on FSE themes but not on PHP themes.
	 *
	 * This matcher allows us to check strings that have quotes in them that may or may not be texturized depending
	 * on the theme being tested against.
	 *
	 * @since [version]
	 *
	 * @see {@link https://github.com/WordPress/gutenberg/issues/37754}
	 *
	 * @param {string} received The received string. This string may or may not contain "smart" quotes.
	 * @param {string} expected The expected string. This string should contain "dumb" quotes.
	 * @return {Promise} Jest expect matcher return.
	 */
	async toMatchStringWithQuotes( received, expected ) {

		received = received.replace( /[“”]/g, '"' ).replace( /[‘’]/g, "'" );

		const options = {
			comment: 'String with quotes equality',
			isNot: this.isNot,
			promise: this.promise,
		};

		const pass = received === expected;

		const message = pass
			? () =>
					this.utils.matcherHint( 'toMatchStringWithQuotes', undefined, undefined, options ) +
					'\n\n' +
					`Expected: not ${ this.utils.printExpected( expected ) }\n` +
					`Received: ${ this.utils.printReceived( received ) }`
			: () => {
					const diffString = diff(expected, received, {
						expand: this.expand,
					} );
					return (
						this.utils.matcherHint( 'toMatchStringWithQuotes', undefined, undefined, options ) +
						'\n\n' +
						( diffString && diffString.includes( '- Expect' )
							? `Difference:\n\n${ diffString }`
							: `Expected: ${ this.utils.printExpected( expected ) }\n` +
								`Received: ${ this.utils.printReceived(received ) }` )
					);
				};

		return {
			actual: received,
			message,
			pass
		};

	}
} );


/**
 * Global helper function that conditionally runs a describe() block if the condition is met.
 *
 * @since [version]
 *
 * @example describeIf( true )( 'SuiteName', () => {} )
 *
 * @param {boolean} condition If truthy, the suite runs as normal, otherwise it's skipped.
 * @return {Function} Returns either `describe()` or `describe.skip()` depending on the condition.
 */
global.describeIf = condition => condition ? describe : describe.skip;

/**
 * Global helper function that conditionally runs a test() if the condition is met.
 *
 * @since [version]
 *
 * @example testIf( true )( 'SuiteName', () => {} )
 *
 * @param {boolean} condition If truthy, the suite runs as normal, otherwise it's skipped.
 * @return {Function} Returns either `test()` or `test.skip()` depending on the condition.
 */
global.testIf = condition => condition ? test : test.skip;
