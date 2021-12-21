<?php
/**
 * Test LLMS_Block_Library
 *
 * @package LifterLMS/Tests
 *
 * @group blocks
 * @group block_library
 *
 * @since [version]
 */
class LLMS_Test_Block_Library extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Block_Library();

	}

	/**
	 * Test register()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register() {

		$expected = array(
			'llms/certificate-title',
		);

		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $expected as $block ) {
			$registry->unregister( $block );
		}

		$this->main->register();

		foreach ( $expected as $block ) {
			$this->assertTrue( $registry->is_registered( $block ) );
		}

		// Ensure _doing_it_wrong() isn't thrown when registering a block that's already registered.
		$this->main->register();

	}

}
