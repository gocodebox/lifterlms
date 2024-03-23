const { createURL } = require( '@wordpress/e2e-test-utils' );

/**
 * Visits a page on the WordPress site.
 *
 * @since  3.37.8
 * @param {string} path  URL path. Eg: "dashboard" to visit mysite.com/dashboard.
 * @param {string} query Query string to be added to the url. Eg: "myvar=1&anothervar=2".
 * @return {void}
 */
export async function visitPage( path, query ) {
	return await page.goto( createURL( path, query ) );
}
