const { setup: wpSetup } = require( '@wordpress/scripts/config/jest-environment-puppeteer/global' );

async function setup( jestConfig = {} ) {
	try {
		await wpSetup( jestConfig );
	} catch ( error ) {
		console.log( 'Catch setup' );
		console.log( error );
	}
}

module.exports = setup;
