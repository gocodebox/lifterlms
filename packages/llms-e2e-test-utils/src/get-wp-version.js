/**
 * Retrieve the WP_VERSION environment variable
 *
 * When running tests locally this will likely be undefined unless running tests with
 * `WP_VERSION=5.7.2 npm run test`.
 *
 * The WP_VERSION env var is defined during CI tests automatically and this function
 * is generally used to determine conditionals based on the WP Core version.
 *
 * For example: block editor selectors change between WP core version, some features
 * aren't available on older versions, etc...
 *
 * @since 5.0.1
 *
 * @return {?string} WordPress version or null if not set.
 */
export function getWPVersion() {
	const { WP_VERSION } = process.env;
	return WP_VERSION || null;
}
