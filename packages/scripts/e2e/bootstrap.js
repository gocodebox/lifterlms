/**
 * Tests Bootstrap.
 */

// Load the .llmsenv file.
require('dotenv').config( {
	path: `${ process.cwd() }/.llmsenv`,
} );

// Setup the WP Base URL for e2e Tests.
if ( ! process.env.WORDPRESS_PORT ) {
	process.env.WORDPRESS_PORT = '8080';
}

process.env.WP_BASE_URL = `http://localhost:${ process.env.WORDPRESS_PORT }`;

// The Jest timeout is increased because these tests are a bit slow.
jest.setTimeout( process.env.PUPPETEER_TIMEOUT || 100000 );
