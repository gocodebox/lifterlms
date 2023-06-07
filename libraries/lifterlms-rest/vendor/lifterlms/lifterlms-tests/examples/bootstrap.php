<?php
/**
 * LifterLMS Add-On Testing Bootstrap
 *
 * @package LifterLMS_Add_On/Tests
 */

require_once './vendor/lifterlms/lifterlms-tests/bootstrap.php';

class LLMS_Add_On_Tests_Bootstrap extends LLMS_Tests_Bootstrap {

	/**
	 * __FILE__ reference, should be defined in the extending class
	 * @var [type]
	 */
	public $file = __FILE__;

	/**
	 * Name of the testing suite
	 * @var string
	 */
	public $suite_name = 'LifterLMS Add-On';

	/**
	 * Main PHP File for the plugin
	 * @var string
	 */
	public $plugin_main = 'lifterlms-add-on.php';

}
return new LLMS_Add_On_Tests_Bootstrap();
