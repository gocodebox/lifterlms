<?php
/**
 * Tests for LifterLMS User Postmeta functions
 * @group    functions
 * @group    admin_functions
 * @group    admin
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Functions_Admin extends LLMS_UnitTestCase {

	// public function test_llms_create_page() {}

	// public function test_llms_get_add_ons() {}

	// public function test_llms_get_add_on() {}

	/**
	 * test the llms_get_sales_page_types function
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_get_sales_page_types() {

		$this->assertEquals( array(
			'none' => 'Display default course content',
			'content' => 'Show custom content',
			'page' => 'Redirect to WordPress Page',
			'url' => 'Redirect to custom URL',
		), llms_get_sales_page_types() );

	}

	// public function test_llms_merge_code_button() {}


}
