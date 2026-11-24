<?php
/**
 * Plugin Name: LifterLMS E2E LifterLMS.com Mock
 * Description: Serves the lifterlms.com products (add-ons catalog) API from a local fixture and fails other requests to LifterLMS.com sites during e2e tests, so no test depends on the network.
 *
 * @package LifterLMS/Tests/E2E
 *
 * @since 11.1.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Short-circuit requests to LifterLMS.com sites.
 *
 * The products API returns `tests/assets/lifterlms-com-products.json`, the fixture the PHPUnit suite also uses.
 * Any other request to lifterlms.com or one of its subdomains, such as the dashboard widget feeds, translations,
 * notifications, or an image in an imported course, fails as if the site were unreachable.
 *
 * @since 11.1.1
 *
 * @param false|array|WP_Error $preempt Whether to preempt the request.
 * @param array                $args    HTTP request arguments.
 * @param string               $url     The request URL.
 * @return false|array|WP_Error Response array or error to short-circuit the request, or `$preempt` to passthrough.
 */
function llms_e2e_mock_lifterlms_com( $preempt, $args, $url ) {

	if ( false !== $preempt || ! preg_match( '/(^|\.)lifterlms\.com$/', (string) wp_parse_url( $url, PHP_URL_HOST ) ) ) {
		return $preempt;
	}

	if ( 'https://lifterlms.com/wp-json/llms/v3/products' !== strtok( $url, '?' ) ) {
		return new WP_Error( 'http_request_failed', sprintf( 'Network requests to %s are disabled in e2e tests.', $url ) );
	}

	$dir = defined( 'LLMS_PLUGIN_DIR' ) ? LLMS_PLUGIN_DIR : WP_PLUGIN_DIR . '/lifterlms/';

	return array(
		'headers'  => array(),
		'body'     => file_get_contents( $dir . 'tests/assets/lifterlms-com-products.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local fixture.
		'response' => array(
			'code'    => 200,
			'message' => 'OK',
		),
		'cookies'  => array(),
		'filename' => null,
	);
}
add_filter( 'pre_http_request', 'llms_e2e_mock_lifterlms_com', 99, 3 );
