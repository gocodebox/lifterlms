<?php
/**
 * Testing Bootstrap
 *
 * @package LifterLMS_REST_API/Tests
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

require_once './vendor/lifterlms/lifterlms-tests/bootstrap.php';

class LLMS_REST_Tests_Bootstrap extends LLMS_Tests_Bootstrap {

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
	public $suite_name = 'LifterLMS REST API';

	/**
	 * Main PHP File for the plugin
	 *
	 * @var string
	 */
	public $plugin_main = 'lifterlms-rest.php';

	/**
	 * Install the plugin
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function install() {
		// Adds filters so tables will be created during LLMS Core installs.
		LLMS_REST_Install::init();
		parent::install();

		add_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

	}

	/**
	 * Load the plugin
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @return void
	 */
	public function load() {

		if ( $this->plugin_main ) {
			require_once( $this->plugin_dir . '/' . $this->plugin_main );
		}

		parent::load();

	}

}

return new LLMS_REST_Tests_Bootstrap();
