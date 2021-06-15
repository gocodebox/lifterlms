<?php
/**
 * Test inclusion and initialization of the helper library.
 *
 * @package LifterLMS/Tests
 *
 * @group helper
 * @group packages
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Helper extends LLMS_Unit_Test_Case {

	/**
	 * Test helper lib exists and is loaded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_helper_lib_exists() {
		$this->assertTrue( class_exists( 'LifterLMS_Helper' ) );
		$this->assertTrue( defined( 'LLMS_HELPER_VERSION' ) );
		$this->assertNotNull( LLMS_HELPER_VERSION );
	}

}
