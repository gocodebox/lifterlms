<?php
/**
 * Test LLMS_Block_Templates
 *
 * @package LifterLMS/Tests
 *
 * @group block_templates
 *
 * @since 5.8.0
 */
class LLMS_Test_Block_Templates extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public function set_up() {

		$this->main = LLMS_Block_Templates::instance();
		parent::set_up();

	}

	/**
	 * Test __construct()
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public function test_constructor() {

		// Reset data.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'block_templates_config', null );
		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' ) );

		remove_filter( 'get_block_templates', array( $this->main, 'add_llms_block_templates' ), 10 );
		remove_filter( 'pre_get_block_file_template', array( $this->main, 'maybe_return_blocks_template' ), 10 );
		remove_action( 'admin_enqueue_scripts', array( $this->main, 'localize_blocks' ), 9999 );

		LLMS_Unit_Test_Util::call_method( $this->main, '__construct' );

		// Configuration runs.
		$this->assertNotNull( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' ) );

		// Hooks added.
		$this->assertEquals( 10, has_filter( 'get_block_templates', array( $this->main, 'add_llms_block_templates' ) ) );
		$this->assertEquals( 10, has_filter( 'pre_get_block_file_template', array( $this->main, 'maybe_return_blocks_template' ) ) );
		$this->assertEquals( 9999, has_action( 'admin_enqueue_scripts', array( $this->main, 'localize_blocks' ) ) );

	}

}
