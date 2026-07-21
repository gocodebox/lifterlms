<?php
/**
 * Plugin Name: LifterLMS E2E Export API Mock
 * Description: Intercepts requests to the lifterlms.com "exports" API during e2e tests and serves local fixtures so the setup wizard import flow runs without hitting the network.
 *
 * @package LifterLMS/Tests/E2E
 *
 * @since 10.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sentinel URL used in place of the real lifterlms.com exports endpoint.
 *
 * Requests to this URL are short-circuited by {@see llms_e2e_mock_export_api()},
 * so no real HTTP request is ever made.
 */
define( 'LLMS_E2E_EXPORT_API_URL', 'https://e2e.lifterlms.test/exports' );

/**
 * Point the LifterLMS export API at our local sentinel URL.
 *
 * @since 10.0.1
 *
 * @return string
 */
add_filter(
	'llms_export_api_url',
	static function () {
		return LLMS_E2E_EXPORT_API_URL;
	}
);

/**
 * Short-circuit HTTP requests to the mocked export API.
 *
 * The real import is a server-side `wp_safe_remote_get`, so it can't be mocked
 * from the browser. We intercept it here and return canned fixtures instead.
 *
 * When the `ids` query arg is present the request is treated as a course
 * download ({@see LLMS_Export_API::get()}) and the bundled sample course is
 * returned. Otherwise it's treated as a course listing
 * ({@see LLMS_Export_API::list()}).
 *
 * @since 10.0.1
 *
 * @param false|array|WP_Error $preempt Whether to preempt the request.
 * @param array                $args    HTTP request arguments.
 * @param string               $url     The request URL.
 * @return false|array Response array to short-circuit the request, or `$preempt` to passthrough.
 */
function llms_e2e_mock_export_api( $preempt, $args, $url ) {

	if ( 0 !== strpos( $url, LLMS_E2E_EXPORT_API_URL ) ) {
		return $preempt;
	}

	$query = (string) wp_parse_url( $url, PHP_URL_QUERY );
	parse_str( $query, $params );

	$body = isset( $params['ids'] ) ? llms_e2e_export_api_course() : llms_e2e_export_api_list();

	return array(
		'headers'  => array(),
		'body'     => wp_json_encode( $body ),
		'response' => array(
			'code'    => 200,
			'message' => 'OK',
		),
		'cookies'  => array(),
		'filename' => null,
	);
}
add_filter( 'pre_http_request', 'llms_e2e_mock_export_api', 10, 3 );

/**
 * Fixture for the importable course listing.
 *
 * Shape matches what `step-finish.php` and `importable-course.php` expect:
 * each course must include `id`, `title`, `description`, and `image`.
 *
 * @since 10.0.1
 *
 * @return array[]
 */
function llms_e2e_export_api_list() {
	return array(
		array(
			'id'          => 19000,
			'title'       => 'The Official Quickstart Course for LifterLMS',
			'description' => 'A sample course imported during e2e tests.',
			'image'       => 'https://e2e.lifterlms.test/course.png',
		),
	);
}

/**
 * Fixture for a single course download.
 *
 * Reuses the Generator-format course bundled with the plugin so
 * `LLMS_Generator` builds a real course on import.
 *
 * @since 10.0.1
 *
 * @return array|null
 */
function llms_e2e_export_api_course() {
	$dir  = defined( 'LLMS_PLUGIN_DIR' ) ? LLMS_PLUGIN_DIR : WP_PLUGIN_DIR . '/lifterlms/';
	$path = $dir . 'sample-data/sample-course.json';

	if ( ! file_exists( $path ) ) {
		return null;
	}

	return wp_json_file_decode( $path, array( 'associative' => true ) );
}
