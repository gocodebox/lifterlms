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
 * @since [version]
 *
 * @see [Reference]
 * @link [URL]
 *
 * @return {[type]} [description]
 */
export function getWPVersion() {
	const { WP_VERSION } = process.env;
	return WP_VERSION || null;
}
