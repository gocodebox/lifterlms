// See https://github.com/smooth-code/jest-puppeteer/issues/160#issuecomment-491975158.
process.env.JEST_PUPPETEER_CONFIG = require.resolve('./jest-puppeteer.config.js');

// For a detailed explanation regarding each configuration property, visit:
// https://jestjs.io/docs/en/configuration.html
module.exports = {

	preset: 'jest-puppeteer',

	setupFilesAfterEnv: [
		'./bootstrap.js'
	],

	// Sort tests alphabetically by path. Ensures Tests in the "activate" directory run first.
	testSequencer: './sequencer.js',

};
