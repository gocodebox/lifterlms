/**
 * Main Jest config
 *
 * @since Unknown
 * @version [version]
 */


const
	// Import the initial config to be moified.
	config = require( '@wordpress/scripts/config/jest-unit.config' );

// Set the root directory to the project's root.
config.rootDir = process.cwd();

// Configure code coverage.
const { coverageReporters = [] } = config;
config.coverageReporters = [ ...coverageReporters, 'text', 'html' ];
config.coverageDirectory = `${ config.rootDir }/tmp/coverage-js-unit`;

/**
 * Jest Config
 *
 * @link https://jestjs.io/docs/en/configuration.html
 */
module.exports = config;
