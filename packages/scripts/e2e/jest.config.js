/**
 * Main Jest config
 *
 * @since Unknown
 * @version 3.0.0
 */

const path = require( 'path' );

/**
 * Load the jest-puppeteer config file
 *
 * @see https://github.com/smooth-code/jest-puppeteer/issues/160#issuecomment-491975158
 */
process.env.JEST_PUPPETEER_CONFIG = require.resolve( './jest-puppeteer.config.js' );

process.env.WP_ARTIFACTS_PATH = `${ process.cwd() }/tmp/artifacts`;

const
	// Import the initial config to be moified.
	config = require( '@wordpress/scripts/config/jest-e2e.config' ),

	// List of uncompiled es modlues.
	esModules = [ '@lifterlms/llms-e2e-test-utils' ].join( '|' );

// Setup files.
config.setupFilesAfterEnv = [
	require.resolve( './bootstrap.js' ),
];

config.rootDir = process.cwd();

// Sort tests alphabetically by path. Ensures Tests in the "activate" directory run first.
config.testSequencer = require.resolve( './sequencer.js' );

// Look for tests with with ".test.js" as a suffix.
config.testMatch = [ '**/tests/**/*.test.[jt]s?(x)' ];

// Don't transform specified modules.
config.transformIgnorePatterns = [ `/node_modules/(?!${ esModules })` ];

// config.reporters = [ require.resolve( './jest-puppeteer-reporter.js' ) ];

/**
 * Jest Config
 *
 * @link https://jestjs.io/docs/en/configuration.html
 */
module.exports = config;
