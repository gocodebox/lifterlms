<?php
/**
* Test update utility functions
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 *
 * @since 6.0.0
 */
class LLMS_Test_Functions_Updates extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Test llms_update_util_get_items_per_page()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_update_util_get_items_per_page() {
		$ret = llms_update_util_get_items_per_page();
		$this->assertTrue( is_int( $ret ) );
	}


}
