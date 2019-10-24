<?php
/**
 * Test inclusion and initialization of the blocks library.
 *
 * @package LifterLMS/Tests
 *
 * @group blocks
 * @group packages
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Blocks extends LLMS_Unit_Test_Case {

	/**
	 * Test blocks lib exists and is loaded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_blocks_lib_exists() {
		$this->assertTrue( class_exists( 'LLMS_Blocks' ) );
		$this->assertTrue( defined( 'LLMS_BLOCKS_VERSION' ) );
		$this->assertNotNull( LLMS_BLOCKS_VERSION );
	}

}
