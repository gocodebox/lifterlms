<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @package LifterLMS/Tests/Admin/Functions
 *
 * @group functions_admin
 * @group functions
 * @group admin
 *
 * @since 3.23.0
 * @since [version] Added tests for `llms_admin_field_upload()`.
 */
class LLMS_Test_Functions_Admin extends LLMS_UnitTestCase {

	/**
	 * Test llms_admin_field_upload()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_admin_field_upload() {

		$output = $this->get_output( 'llms_admin_field_upload', array( 'id', 'src', 'val' ) );

		$this->assertStringContains( '<img class="llms-image-field-preview" src="src">', $output );
		$this->assertStringContains( 'data-id="id"', $output );
		$this->assertStringContains( 'data-id="id"', $output );
		$this->assertStringContains( 'name="id"', $output );
		$this->assertStringContains( 'id="id"', $output );

		$output = $this->get_output( 'llms_admin_field_upload', array( 'id', 'src', 'val', array( 'name' => 'name' ) ) );
		$this->assertStringContains( 'id="id"', $output );
		$this->assertStringContains( 'name="name"', $output );

	}

	// public function test_llms_create_page() {}

	// public function test_llms_get_add_ons() {}

	// public function test_llms_get_add_on() {}

	/**
	 * test the llms_get_sales_page_types function
	 * @return   void
	 * @since    3.23.0
	 * @version  3.23.0
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
