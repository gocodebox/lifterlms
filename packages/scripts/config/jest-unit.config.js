/**
 * Main Jest config
 *
 * @since Unknown
 * @version 2.0.0
 */


const
	// Import the initial config to be moified.
	config = require( '@wordpress/scripts/config/jest-unit.config' );

// Set the root directory to the project's root.
config.rootDir = process.cwd();

/**
 * Jest Config
 *
 * @link https://jestjs.io/docs/en/configuration.html
 */
module.exports = config;
