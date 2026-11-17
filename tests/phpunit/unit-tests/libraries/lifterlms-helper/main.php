<?php
/**
 * Test Main plugin file.
 *
 * @package LifterLMS_Helper/Tests
 *
 * @group main
 *
 * @since 3.2.0
 * @version 3.2.0
 */
class LLMS_Helper_Test_Main extends LLMS_Helper_Unit_Test_Case {

	/**
	 * Test constant definitions.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function test_constants() {

		$this->assertTrue( defined( 'LLMS_HELPER_PLUGIN_DIR' ) );
		$this->assertTrue( defined( 'LLMS_HELPER_PLUGIN_FILE' ) );

		$this->assertTrue( ! empty( LLMS_HELPER_PLUGIN_DIR ) );
		$this->assertTrue( ! empty( LLMS_HELPER_PLUGIN_FILE ) );

	}

	/**
	 * Test main plugin class is included.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function test_main_include() {

		$this->assertTrue( class_exists( 'LifterLMS_Helper' ) );

	}

	/**
	 * Test LLMS_Helper() function.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function test_main_function() {

		$this->assertTrue( is_a( LLMS_Helper(), 'LifterLMS_Helper' ) );

	}

}
