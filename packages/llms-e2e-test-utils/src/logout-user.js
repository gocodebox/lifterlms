/**
 * External Dependencies.
 */
const { createURL } = require( '@wordpress/e2e-test-utils' );

/**
 * Internal Dependencies.
 */
const { clickAndWait } = require( './click-and-wait' );

/**
 * Logout the current user.
 *
 * @since  3.37.8
 *
 * @return {void}
 */
export async function logoutUser() {
	await page.goto( createURL( 'wp-login.php', 'action=logout' ) );
	await clickAndWait( 'a' );
}
