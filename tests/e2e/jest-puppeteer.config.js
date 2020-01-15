// https://github.com/smooth-code/jest-puppeteer#jest-puppeteerconfigjs

const width = 1440, height = 900;

let config = {
	launch: {
		ignoreHTTPSErrors: true,
		headless: process.env.HEADLESS !== 'false',
		defaultViewport: {
			width: width,
			height: height,
		},
	},
};

if ( false === config.launch.headless ) {
	config.launch.slowMo = 60;
	config.launch.args = [
		`--window-size=${width},${height}`
	];
}

module.exports = config;
