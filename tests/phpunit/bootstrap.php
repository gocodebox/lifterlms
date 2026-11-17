<?php
/**
 * LifterLMS Add-On Testing Bootstrap
 *
 * @package LifterLMS/Tests
 *
 * @since 3.3.1
 * @since 3.28.0 Unknown
 * @since 3.37.8 Added class variable to access the tests assets directory.
 */

/*
 * PHP 8.4+ emits deprecation notices (e.g. for implicitly nullable parameters) when loading
 * third-party code such as Action Scheduler, the lifterlms-tests framework, and plugins
 * installed into the tests WordPress install. Tests run in isolated processes error out when
 * that output reaches stderr, because PHPUnit treats any child process stderr output as a
 * test error. Silence deprecations originating from third-party code only; deprecations
 * triggered by LifterLMS's own code are still reported.
 *
 * The handler is removed again at the bottom of this file, once bootstrapping is complete:
 * PHPUnit will not register its own error handler when a custom one is already present,
 * which would silently disable its convertWarningsToExceptions/convertNoticesToExceptions
 * behavior that some tests (e.g. the importer's generation error tests) rely on.
 */
if ( PHP_VERSION_ID >= 80400 ) {
	set_error_handler(
		function ( $errno, $errstr, $errfile = '' ) {
			foreach ( array( '/vendor/', 'tmp/tests/' ) as $third_party_path ) {
				if ( false !== strpos( $errfile, $third_party_path ) ) {
					return true;
				}
			}
			return false;
		},
		E_DEPRECATED
	);
}

require_once './vendor/lifterlms/lifterlms-tests/bootstrap.php';

class LLMS_Unit_Tests_Bootstrap extends LLMS_Tests_Bootstrap {

	/**
	 * __FILE__ reference, should be defined in the extending class
	 *
	 * @var [type]
	 */
	public $file = __FILE__;

	/**
	 * Name of the testing suite
	 *
	 * @var string
	 */
	public $suite_name = 'LifterLMS';

	/**
	 * Main PHP File for the plugin
	 *
	 * @var string
	 */
	public $plugin_main = 'lifterlms.php';

	/**
	 * Location of testing assets.
	 *
	 * @var string
	 */
	public $assets_dir = '';

	/**
	 * Determines if the LifterLMS core should be loaded
	 *
	 * @var bool
	 */
	public $use_core = false;

	/**
	 * Install the plugin
	 *
	 * @return   void
	 * @since    3.28.0
	 * @version  3.28.0
	 */
	public function install() {

		parent::install();

		// Serve LifterLMS.com API responses from fixtures so no test depends on the network.
		add_filter( 'pre_http_request', array( $this, 'mock_lifterlms_com_request' ), 999, 3 );

		// install LLMS
		LLMS_Install::install();

		// Prevent webhook pings during bundled REST API library tests.
		add_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

		// Admin functions used by bundled library tests (helper).
		require_once LLMS_PLUGIN_DIR . 'includes/admin/llms.functions.admin.php';

		// Reload capabilities after install, see https://core.trac.wordpress.org/ticket/28374
		if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
			$GLOBALS['wp_roles']->reinit();
		} else {
			$GLOBALS['wp_roles'] = null;
			wp_roles();
		}

	}


	/**
	 * Fakes requests to the LifterLMS.com APIs the plugin calls.
	 *
	 * Runs late and only for requests no test has mocked, so a test can still mock a specific response
	 * with `mock_http_request()` or its own `pre_http_request` filter.
	 *
	 * - Products API: the add-ons catalog from `tests/assets/lifterlms-com-products.json`.
	 * - Exports API: the course list, the export for course #33579, or the API's 404 error for any other ID.
	 * - Any other request to academy.lifterlms.com or demo.lifterlms.com, such as an image in an export, and any
	 *   lifterlms.com request outside its REST API, such as an oEmbed probe: an error.
	 *
	 * @since [version]
	 *
	 * @param false|array|WP_Error $preempt Response from an earlier filter, or `false` to make the request.
	 * @param array                $args    HTTP request arguments.
	 * @param string               $url     Request URL.
	 * @return false|array|WP_Error
	 */
	public function mock_lifterlms_com_request( $preempt, $args, $url ) {

		if ( false !== $preempt ) {
			return $preempt;
		}

		$response = function ( $code, $body ) {
			return array(
				'headers'  => array( 'content-type' => 'application/json' ),
				'body'     => $body,
				'response' => array(
					'code'    => $code,
					'message' => get_status_header_desc( $code ),
				),
				'cookies'  => array(),
				'filename' => null,
			);
		};
		$fixture  = function ( $name ) {
			return file_get_contents( $this->assets_dir . 'lifterlms-com-' . $name . '.json' );
		};

		$path = strtok( $url, '?' );

		if ( 'https://lifterlms.com/wp-json/llms/v3/products' === $path ) {
			return $response( 200, $fixture( 'products' ) );
		}

		if ( 'https://academy.lifterlms.com/wp-json/llms-academy/v1/exports' === $path ) {

			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
			if ( empty( $query['ids'] ) ) {
				return $response( 200, $fixture( 'exports-list' ) );
			}

			$missing = array_diff( explode( ',', $query['ids'] ), array( '33579' ) );
			if ( $missing ) {
				$error = array(
					'code'    => 'not-found',
					'message' => sprintf( 'No export found for ID #%s.', reset( $missing ) ),
					'data'    => array( 'status' => 404 ),
				);
				return $response( 404, wp_json_encode( $error ) );
			}

			return $response( 200, $fixture( 'export-33579' ) );

		}

		$host = wp_parse_url( $url, PHP_URL_HOST );
		if (
			in_array( $host, array( 'academy.lifterlms.com', 'demo.lifterlms.com' ), true ) ||
			( 'lifterlms.com' === $host && 0 !== strpos( (string) wp_parse_url( $url, PHP_URL_PATH ), '/wp-json/' ) )
		) {
			return new WP_Error( 'http_request_failed', sprintf( 'Network requests to %s are disabled in tests.', $url ) );
		}

		return $preempt;

	}

	/**
	 * Load the plugin
	 *
	 * @since 3.28.0
	 * @since 3.37.8 Use $this->assets_dir.
	 *
	 * @return void
	 */
	public function load() {

		// Assets are shared between phpunit and e2e tests.
		$this->assets_dir = dirname( $this->tests_dir ) . '/assets/';

		// override this constant otherwise a bunch of includes will fail when running tests
		// define( 'LLMS_PLUGIN_DIR', trailingslashit( $this->plugin_dir ) );

		parent::load();

	}

	/**
	 * Uninstall the plugin.
	 *
	 * @return  void
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function uninstall() {

		parent::uninstall();

		// Clean existing install first.
		define( 'LLMS_REMOVE_ALL_DATA', true );
		include( $this->plugin_dir . '/uninstall.php' );

	}

}

global $lifterlms_tests;
$lifterlms_tests = new LLMS_Unit_Tests_Bootstrap();

// See the E_DEPRECATED handler registered at the top of this file.
if ( PHP_VERSION_ID >= 80400 ) {
	restore_error_handler();
}

return $lifterlms_tests;
