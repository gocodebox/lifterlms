/**
 * Test Reporter that takes screenshots when a test fails.
 *
 * @since Unknown
 * @version [version]
 *
 * @link https://github.com/smooth-code/jest-puppeteer/issues/131#issuecomment-424073620
 */

const
	path   = require( 'path' ),
	mkdirp = require( 'mkdirp' );

/**
 * Take a Screenshot.
 *
 * @since Unknown.
 * @since [version] Added `result` argument.
 *
 * @param {String} name   Screenshot name.
 * @param {Object} result Full test result object.
 * @return {Void}
 */
async function takeScreenshot( name, result ) {

	const
		dir        = './tmp/e2e-screenshots',
		toFilename = s => s.replace( /[^a-z0-9.-]+/gi, '-' ),
		filePath   = toFilename( `${ new Date().toISOString() }-${ name }.png` );

	mkdirp.sync( dir );

	await page.screenshot( {
		path: path.join( dir, filePath ),
		fullPage: true,
	} );

};

/**
 * Jasmine reporter does not support async.
 *
 * Store the screenshot promise and wait for it before each test.
 */
let screenshotPromise = Promise.resolve();
beforeEach( () => screenshotPromise );
afterAll( () => screenshotPromise );

/**
 * Add the test Reporter.
 *
 * @since Unknown.
 * @since [version] Pass the full test result to `takeScreenshot()`.
 *
 * @return {Void}
 */
jasmine.getEnv().addReporter( {

	specDone: result => {
		if ( 'false' !== process.env.PUPPETEER_HEADLESS && 'failed' === result.status ) {
			screenshotPromise = screenshotPromise
				.catch()
				.then( () => takeScreenshot( result.fullName, result ) );
		}
	},

} );
