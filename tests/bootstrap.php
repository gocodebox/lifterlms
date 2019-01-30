<?php
/**
 * LifterLMS Add-On Testing Bootstrap
 *
 * @package LifterLMS/Tests
 * @since   3.3.1
 * @version 3.28.0
 */

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
	 * @return  void
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function load() {

		$files = array(
			array(
				'orig' => $this->tests_dir . '/assets/custom-lifterlms-en_US.mo',
				'dest' => WP_LANG_DIR . '/lifterlms/lifterlms-en_US.mo',
			),
			array(
				'orig' => $this->tests_dir . '/assets/lifterlms-en_US.mo',
				'dest' => WP_LANG_DIR . '/plugins/lifterlms-en_US.mo',
			),
		);

		foreach ( $files as $file ) {

			// remove the destination file to replace it each time we run a test
			// copy fails if the dest file already exists
			if ( file_exists( $file['dest'] ) ) {
				unlink( $file['dest'] );
			}

			// make sure the destination dir exists
			$path = pathinfo( $file['dest'] );
		    if ( ! file_exists( $path['dirname'] ) ) {
		        mkdir( $path['dirname'], 0777, true );
		    }

		    // copy the original to the destination
		    copy( $file['orig'], $file['dest'] );

		}

		// override this constant otherwise a bunch of includes will fail when running tests
		define( 'LLMS_PLUGIN_DIR', trailingslashit( $this->plugin_dir ) );

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

return new LLMS_Unit_Tests_Bootstrap();
