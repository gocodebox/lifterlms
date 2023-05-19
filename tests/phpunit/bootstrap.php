<?php
/**
 * LifterLMS Add-On Testing Bootstrap
 *
 * @package LifterLMS/Tests
 *
 * @since 3.3.1
 * @version [version]
 */

require_once './vendor/autoload.php';
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
	 * Installs the plugin.
	 *
	 * @since 3.28.0
	 * @since [version] Disable LLMS_Session session initialization.
	 */
	public function install() {

		tests_add_filter( 'llms_session_should_init', '__return_false' );
		parent::install();

		// install LLMS
		LLMS_Install::install();

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
	 * @since 3.28.0
	 *
	 * @return void
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
