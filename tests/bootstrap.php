<?php
/**
 * LifterLMS Unit Testing Bootstrap
 * @since    3.3.1
 * @version  [version]
 * @thanks   WooCommerce <3
 */
class LLMS_Unit_Tests_Bootstrap {

	/**
	 * Singleton Instance of LLMS_Unit_Tests_Bootstrap
	 * @var  obj
	 */
	protected static $instance = null;

	/**
	 * WP Tests Directory Path
	 * @var  string
	 */
	public $wp_tests_dir;

	/**
	 * Tests Directory Path
	 * @var  string
	 */
	public $tests_dir;

	/**
	 * Plugin Directory Path
	 * @var  string
	 */
	public $plugin_dir;

	/**
	 * Get Singleton Class Instance
	 * @return   LLMS_Unit_Tests_Bootstrap
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function __construct() {

		echo 'Welcome to the LifterLMS Test Suite' . PHP_EOL . PHP_EOL . PHP_EOL;

		ini_set( 'display_errors','on' );
		error_reporting( E_ALL );

		// Ensure server variable is set for WP email functions.
		if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
			$_SERVER['SERVER_NAME'] = 'localhost';
		}

		$this->tests_dir    = dirname( __FILE__ );
		$this->plugin_dir   = dirname( $this->tests_dir );
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

		// load test function so tests_add_filter() is available
		require_once $this->wp_tests_dir . '/includes/functions.php';

		require_once 'tests/framework/llms.test.functions.php';

		// load LLMS
		tests_add_filter( 'muplugins_loaded', array( $this, 'load_llms' ) );

		// install LLMS
		tests_add_filter( 'setup_theme', array( $this, 'install_llms' ) );

		// load the WP testing environment
		require_once( $this->wp_tests_dir . '/includes/bootstrap.php' );

		// load LLMS testing framework
		$this->includes();
	}

	/**
	 * Load LifterLMS
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function load_llms() {

		// override this constant otherwise a bunch of includes will fail when running tests
		define( 'LLMS_PLUGIN_DIR', trailingslashit( $this->plugin_dir ) );

		require_once( $this->plugin_dir . '/lifterlms.php' );

	}

	/**
	 * Install LifterLMS
	 * @return   void
	 * @since    3.3.1
	 * @version  [version]
	 */
	public function install_llms() {

		echo 'Installing LifterLMS...' . PHP_EOL;

		// Clean existing install first.
		define( 'WP_UNINSTALL_PLUGIN', true );
		define( 'LLMS_REMOVE_ALL_DATA', true );
		include( $this->plugin_dir . '/uninstall.php' );

		// setup translation files to ensure localization loads in the correct order during plugin initialization
		if ( file_exists( WP_LANG_DIR . '/lifterlms/lifterlms-en_US.mo' ) ) {
			unlink( WP_LANG_DIR . '/lifterlms/lifterlms-en_US.mo' );
		}
		copy( LLMS_TESTS_DIR . '/assets/custom-lifterlms-en_US.mo', WP_LANG_DIR . '/lifterlms/lifterlms-en_US.mo' );

		if ( file_exists( WP_LANG_DIR . '/plugins/lifterlms-en_US.mo' ) ) {
			unlink( WP_LANG_DIR . '/plugins/lifterlms-en_US.mo' );
		}
		copy( LLMS_TESTS_DIR . '/assets/lifterlms-en_US.mo', WP_LANG_DIR . '/plugins/lifterlms-en_US.mo' );

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
	 * Load LifterLMS Tests & Related
	 * @return   void
	 * @since    3.3.1
	 * @version  3.19.4
	 */
	public function includes() {

		require 'tests/framework/class.llms.unit.test.case.php';
		require 'tests/framework/class.llms.notification.test.case.php';
		require 'tests/framework/class.llms.post.model.unit.test.case.php';

		require 'tests/framework/exceptions/class-llms-testing-exception-exit.php';
		require 'tests/framework/exceptions/class-llms-testing-exception-redirect.php';

	}


}
LLMS_Unit_Tests_Bootstrap::instance();
