/**
 * Jest Puppeteer Config
 *
 * @since Unknown
 * @version Unknown
 *
 * @link https://github.com/smooth-code/jest-puppeteer#jest-puppeteerconfigjs
 */

const window = process.env.PUPPETEER_WINDOW || '1440x900',
	dimensions = window.split( 'x' ).map( ( int ) => parseInt( int, 10 ) );

const config = {
	launch: {
		ignoreHTTPSErrors: true,
		headless: process.env.PUPPETEER_HEADLESS !== 'false',
		slowMo: parseInt( process.env.PUPPETEER_SLOWMO, 10 ) || 0,
		defaultViewport: {
			width: dimensions[ 0 ],
			height: dimensions[ 1 ],
		},
	},
	exitOnPageError: false,
};

if ( false === config.launch.headless ) {
	config.launch.args = [
		`--window-size=${ dimensions[ 0 ] },${ dimensions[ 1 ] }`,
	];
}

module.exports = config;
