<?php
/**
 * Tests Bootstrap
 *
 * @since 1.0.0
 * @since Use `getcwd()` to set the `$plugin_dir` variable.
 * @version 1.6.1
 */

require './vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

class LLMS_Tests_Bootstrap {

	/**
	 * Path to the test assets directory
	 *
	 * @var string
	 */
	public $assets_dir;

	/**
	 * __FILE__ reference, should be defined in the extending class
	 * @var [type]
	 */
	public $file = __FILE__;

	/**
	 * Plugin Directory Path
	 * @var  string
	 */
	public $plugin_dir;

	/**
	 * Main PHP File for the plugin
	 * @var string
	 */
	public $plugin_main;

	/**
	 * Name of the testing suite
	 * @var string
	 */
	public $suite_name = 'LifterLMS';

	/**
	 * Tests Directory Path
	 * @var  string
	 */
	public $tests_dir;

	/**
	 * Determines if the LifterLMS core should be loaded
	 * @var bool
	 */
	public $use_core = true;

	/**
	 * WP Tests Directory Path
	 * @var  string
	 */
	public $wp_tests_dir;

	/**
	 * Constructor
	 * @since    1.0.0
	 * @version  1.2.0
	 */
	public function __construct() {

		echo 'Welcome to the ' . $this->suite_name . ' Test Suite' . PHP_EOL . PHP_EOL . PHP_EOL;

		ini_set( 'display_errors','on' );
		error_reporting( E_ALL );

		// Ensure server variable is set for WP email functions.
		if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
			$_SERVER['SERVER_NAME'] = 'localhost';
		}

		$this->tests_dir    = dirname( $this->file );
		$this->plugin_dir   = getcwd();
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : 'tmp/tests/wordpress-tests-lib';
		$this->assets_dir   = $this->tests_dir . '/assets';

		// load test function so tests_add_filter() is available
		require_once $this->wp_tests_dir . '/includes/functions.php';

		// Include llms-tests lib functions.
		require_once dirname( __FILE__ ) . '/framework/functions-llms-tests.php';
		// Include add-on tests functions (if they exist).
		if ( file_exists( $this->tests_dir . '/framework/functions-llms-tests.php' ) ) {
			require_once $this->tests_dir . '/framework/functions-llms-tests.php';
		}

		// Load the plugin.
		tests_add_filter( 'muplugins_loaded', array( $this, 'load' ) );

		// Install the plugin.
		tests_add_filter( 'setup_theme', array( $this, 'install' ) );
		tests_add_filter( 'setup_theme', array( $this, 'install_after' ) );

		// Load the WP testing environment.
		require_once( $this->wp_tests_dir . '/includes/bootstrap.php' );

		// Load any includes.
		$this->includes();

		// Expose the bootstrap class.
		global $_llms_tests_bootstrap;
		$_llms_tests_bootstrap = $this;

	}

	/**
	 * Load test suite files/includes
	 *
	 * @since 1.0.0
	 *
	 * @return   void
	 */
	public function includes() {

		$dir = dirname( __FILE__ );

		// Framework files included with llms-tests.
		$files = array_merge(
			glob( $dir . '/framework/traits/*.php' ),
			glob( $dir . '/framework/*.php' ),
			glob( $dir . '/framework/exceptions/*.php' ),
			glob( $dir . '/framework/factory/*.php' )
		);

		// Framework files from the add-on.
		if ( file_exists( $this->tests_dir . '/framework' ) ) {

			// Top level files in the framework dir.
			$files = array_merge( $files, glob( $this->tests_dir . '/framework/*.php' ) );

			foreach ( glob( $this->tests_dir . '/framework/**', GLOB_ONLYDIR ) as $dir ) {
				$files = array_merge( $files, glob( $dir . '/*.php' ) );
			}

		}

		foreach ( $files as $file ) {
			require_once $file;
		}

	}

	/**
	 * Install the plugin
	 * @return   void
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	public function install() {

		$this->uninstall();

		echo 'Installing '. $this->suite_name .'...' . PHP_EOL;

		if ( $this->use_core ) {
			LLMS_Install::install();
		}

	}

	/**
	 * Runs immediately after $this->install()
	 *
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function install_after() {

		// Reload capabilities after install, see https://core.trac.wordpress.org/ticket/28374.
		$GLOBALS['wp_roles'] = null;
		wp_roles();

	}

	/**
	 * Load the plugin
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function load() {

		if ( $this->use_core ) {
			define( 'LLMS_USE_PHP_SESSIONS', true );
			define( 'LLMS_PLUGIN_DIR', WP_PLUGIN_DIR . '/lifterlms/' );
			$this->load_plugin( 'lifterlms', 'lifterlms.php' );
		}

		if ( $this->plugin_main ) {
			require_once( $this->plugin_dir . '/' . $this->plugin_main );
		}

	}

	/**
	 * Load a plugin dependency
	 *
	 * @param   string    $dir  directory name for the plugin (eg lifterlms).
	 * @param   string    $file filename for the plugin (eg: lifterlms.php).
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function load_plugin( $dir, $file ) {

		if ( file_exists( WP_PLUGIN_DIR . '/' . $dir ) ) {
			require_once( WP_PLUGIN_DIR . '/' . $dir . '/' . $file );
		}

	}

	/**
	 * Uninstall the plugin.
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function uninstall() {

		echo 'Removing '. $this->suite_name .'...' . PHP_EOL;

		define( 'WP_UNINSTALL_PLUGIN', true );

		if ( $this->use_core ) {
			define( 'LLMS_REMOVE_ALL_DATA', true );
			include( WP_PLUGIN_DIR . '/lifterlms/uninstall.php' );
		}

		// Clean existing install first.
		if ( file_exists( $this->plugin_dir . '/uninstall.php' ) ) {
			require_once $this->plugin_dir . '/uninstall.php';
		}


	}

}
