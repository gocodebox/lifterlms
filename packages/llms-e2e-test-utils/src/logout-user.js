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
 * @since 3.37.8
 * @since 2.1.2 Wait 1 second before navigating to logout page.
 * @since 3.0.0 Use `waitForTimeout()` in favor of deprecated `waitFor()`.
 *
 * @return {void}
 */
export async function logoutUser() {
	await page.waitForTimeout( 1000 );
	await page.goto( createURL( 'wp-login.php', 'action=logout' ) );
	await clickAndWait( 'a' );
}
