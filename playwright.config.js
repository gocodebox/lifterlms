const baseConfig = require( '@wordpress/scripts/config/playwright.config' );
const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	...baseConfig,
	testDir: './tests/e2e/specs',
} );
