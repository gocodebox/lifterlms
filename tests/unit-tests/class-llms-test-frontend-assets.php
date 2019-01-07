<?php
/**
 * LLMS Frontend Assets Tests
 */

class LLMS_Test_Frontend_Assets extends LLMS_UnitTestCase {

	/**
	 * Test inline script managment functions
	 * @return   void
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public function test_inline_scripts() {

		// new script should return true
		$this->assertTrue( LLMS_Frontend_Assets::enqueue_inline_script( 'test-id', 'alert("hello");', 'footer', 25 ) );

		// script should be enqueued
		$this->assertTrue( LLMS_Frontend_Assets::is_inline_script_enqueued( 'test-id' ) );

		// duplicate should assert false
		$this->assertFalse( LLMS_Frontend_Assets::enqueue_inline_script( 'test-id', 'alert("hello");', 'footer', 25 ) );

		// fake script not enqueued
		$this->assertFalse( LLMS_Frontend_Assets::is_inline_script_enqueued( 'fake-id' ) );

	}

}
