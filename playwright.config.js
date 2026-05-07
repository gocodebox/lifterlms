import { defineConfig } from '@playwright/test';
import baseConfig from '@wordpress/scripts/config/playwright.config.js';

export default defineConfig( {
	...baseConfig,
	testDir: './tests/e2e/specs',
} );
