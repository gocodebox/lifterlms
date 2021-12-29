<?php
/**
* Test update utility functions
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates extends LLMS_UnitTestCase {

	/**
	 * Test llms_update_util_get_items_per_page()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_update_util_get_items_per_page() {
		$ret = llms_update_util_get_items_per_page();
		$this->assertTrue( is_int( $ret ) );
	}


}
