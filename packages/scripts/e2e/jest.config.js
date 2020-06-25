/**
 * Main Jest config
 *
 * @since Unknown
 */

/**
 * Load the jest-puppeteer config file
 *
 * @see https://github.com/smooth-code/jest-puppeteer/issues/160#issuecomment-491975158
 */
process.env.JEST_PUPPETEER_CONFIG = require.resolve( './jest-puppeteer.config.js' );

/**
 * Jest Config
 *
 * @link https://jestjs.io/docs/en/configuration.html
 */
module.exports = {

	preset: 'jest-puppeteer',
	// rootDir: process.cwd(),

	roots: [
		`${ process.cwd() }/tests/e2e/tests`,
	],

	setupFilesAfterEnv: [
		'./bootstrap.js',
		'./screenshot-reporter.js',
	],

	// Sort tests alphabetically by path. Ensures Tests in the "activate" directory run first.
	testSequencer: './sequencer.js',

};
