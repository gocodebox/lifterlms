/**
 * External Dependencies.
 */
const { createURL } = require( '@wordpress/e2e-test-utils' );

/**
 * Visits a page on the WordPress site.
 *
 * @since  [version]
 *
 * @param  {String} path  URL path. Eg: "dashboard" to visit mysite.com/dashboard.
 * @param  {String} query Query string to be added to the url. Eg: "myvar=1&anothervar=2".
 * @return {Void}
 */
export async function visitPage( path, query ) {
	await page.goto( createURL( path, query ) );
}
