<?php
/**
 * LLMS Frontend Assets Tests
 *
 * @package LifterLMS/Tests
 *
 * @group assets
 * @group frontend_assets
 *
 * @since 4.4.0
 */
class LLMS_Test_Frontend_Assets extends LLMS_UnitTestCase {

	/**
	 * Test inline script management functions
	 *
	 * @since 3.4.1
	 *
	 * @expectedDeprecated LLMS_Frontend_Assets::enqueue_inline_script()
	 * @expectedDeprecated LLMS_Frontend_Assets::is_inline_enqueued()
	 *
	 * @return void
	 */
	public function test_inline_scripts() {

		// New script should return true.
		$this->assertTrue( LLMS_Frontend_Assets::enqueue_inline_script( 'test-id', 'alert("hello");', 'footer', 25 ) );

		// Script should be enqueued.
		$this->assertTrue( LLMS_Frontend_Assets::is_inline_script_enqueued( 'test-id' ) );

		// Fake script not enqueued.
		$this->assertFalse( LLMS_Frontend_Assets::is_inline_script_enqueued( 'fake-id' ) );

	}
}
