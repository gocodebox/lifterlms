/**
 * Main Jest config
 *
 * @since Unknown
 * @version 3.1.0
 */

const
	// Import the initial config to be moified.
	config = require( '@wordpress/scripts/config/jest-unit.config' ),
    testPathIgnorePatterns = config.testPathIgnorePatterns || [];

// Set the root directory to the project's root.
config.rootDir = process.cwd();

// Exclude dev tmp directory automatically.
config.testPathIgnorePatterns = [
    ...testPathIgnorePatterns,
    '/node_modules/',
    '<rootDir>/tmp/'
];

/**
 * Jest Config
 *
 * @link https://jestjs.io/docs/en/configuration.html
 */
module.exports = config;
