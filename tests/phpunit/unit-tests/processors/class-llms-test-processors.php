<?php
/**
 * Test LLMS_Processors
 *
 * @package LifterLMS/Tests
 *
 * @group processors
 *
 * @since 5.0.0
 */
class LLMS_Test_Processors extends LLMS_Unit_Test_Case {

	/**
	 * Setup test case
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->main = LLMS_Processors::instance();
	}

	/**
	 * Test `instance()`.
	 *
	 * @since 5.0.0
	 * @since 5.3.0 Rename `_instance` property to `instance`.
	 * @since 6.0.0 Removed testing of the removed `LLMS_Processors::$_instance` property.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_instance() {

		$this->main->fake = 'mock';
		$this->assertEquals( $this->main, LLMS_Processors::instance() );

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'instance', null );
		$new_instance = LLMS_Processors::instance();
		$this->assertInstanceOf( 'LLMS_Processors', $new_instance );
		$this->assertTrue( ! isset( $new_instance->fake ) );

	}

	/**
	 * Test get()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get() {

		$this->assertInstanceOf( 'LLMS_Processor_Course_Data', $this->main->get( 'course_data' ) );
		$this->assertFalse( $this->main->get( 'fake' ) );

	}

	/**
	 * Test load_processor()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_load_processor() {

		$this->assertTrue( $this->main->load_processor( 'course_data' ) );
		$this->assertFalse( $this->main->load_processor( 'fake' ) );

	}

}
