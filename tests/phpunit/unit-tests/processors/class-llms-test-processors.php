<?php
/**
 * Test LLMS_Processors
 *
 * @package LifterLMS/Tests
 *
 * @group processors
 *
 * @since [version]
 */
class LLMS_Test_Processors extends LLMS_Unit_Test_Case {

	/**
	 * Setup test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->main = LLMS_Processors::instance();
	}

	/**
	 * Test instance()
	 *
	 * @since [version]
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_instance() {

		$this->main->fake = 'mock';
		$this->assertEquals( $this->main, LLMS_Processors::instance() );

		LLMS_Unit_Test_Util::set_private_property( $this->main, '_instance', null );
		$new_instance = LLMS_Processors::instance();
		$this->assertInstanceOf( 'LLMS_Processors', $new_instance );
		$this->assertTrue( ! isset( $new_instance->fake ) );

	}

	/**
	 * Test get()
	 *
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_load_processor() {

		$this->assertTrue( $this->main->load_processor( 'table_to_csv' ) );
		$this->assertFalse( $this->main->load_processor( 'fake' ) );

	}

}
