const { teardown: wpTeardown } = require( '@wordpress/scripts/config/jest-environment-puppeteer/global' );

async function teardown( jestConfig = {} ) {
	try {
		await wpTeardown( jestConfig );
	} catch ( error ) {
		console.log( 'Catch teardown.' );
		console.log( error );
	}
}

module.exports = teardown;
