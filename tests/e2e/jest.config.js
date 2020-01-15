// See https://github.com/smooth-code/jest-puppeteer/issues/160#issuecomment-491975158.
process.env.JEST_PUPPETEER_CONFIG = require.resolve('./jest-puppeteer.config.js');

// For a detailed explanation regarding each configuration property, visit:
// https://jestjs.io/docs/en/configuration.html
module.exports = {

	// A preset that is used as a base for Jest's configuration
	preset: 'jest-puppeteer',

	setupFilesAfterEnv: [
		'./bootstrap.js',
	],

};
