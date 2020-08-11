/**
 * Main Jest config
 *
 * @since Unknown
 * @since 1.3.0 Restructured to use defaults from @wordpress/scripts/config/jest-e2e.config module.
 */

/**
 * Load the jest-puppeteer config file
 *
 * @see https://github.com/smooth-code/jest-puppeteer/issues/160#issuecomment-491975158
 */
process.env.JEST_PUPPETEER_CONFIG = require.resolve( './jest-puppeteer.config.js' );

const
	// Main Config.
	config    = require( '@wordpress/scripts/config/jest-e2e.config' ),
	// List of uncompiled es modlues.
	esModules = [ '@lifterlms/llms-e2e-test-utils' ].join( '|' );

// Setup files.
config.setupFilesAfterEnv = [
	require.resolve( './bootstrap.js' ),
	require.resolve( './screenshot-reporter.js' ),
];

config.rootDir = process.cwd();

// Sort tests alphabetically by path. Ensures Tests in the "activate" directory run first.
config.testSequencer = require.resolve( './sequencer.js' );

// Look for tests with with ".test.js" as a suffix.
config.testMatch = [ '**/tests/**/*.test.[jt]s?(x)' ];
config.transformIgnorePatterns = [ `/node_modules/(?!${ esModules })` ];

/**
 * Jest Config
 *
 * @link https://jestjs.io/docs/en/configuration.html
 */
module.exports = config;
