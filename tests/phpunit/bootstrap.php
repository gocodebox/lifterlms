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
return $lifterlms_tests;
