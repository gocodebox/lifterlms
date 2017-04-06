<?php
/**
 * LLMS_Notifications Tests
 */

class LLMS_Test_Notifications extends LLMS_UnitTestCase {

	// public function test_dispatch_processors() {}

	/**
	 * Test the get_controller() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_controller() {

		$main = LLMS()->notifications();

		// return the controller instance
		$this->assertTrue( is_a( $main->get_controller( 'lesson_complete' ), 'LLMS_Notification_Controller_Lesson_Complete' ) );

		// return false
		$this->assertFalse( $main->get_controller( 'thisisveryveryfake' ) );

	}

	/**
	 * Test get_controllers() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_controllers() {

		$main = LLMS()->notifications();

		// should always return an array
		$this->assertTrue( is_array( $main->get_controllers() ) );

		// each item in the array must extend the controller abstract
		foreach ( $main->get_controllers() as $controller ) {
			$this->assertTrue( is_subclass_of( $controller, 'LLMS_Abstract_Notification_Controller' ) );
		}

	}

	/**
	 * Test get_processor() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_processor() {

		$main = LLMS()->notifications();

		// return the controller instance
		$this->assertTrue( is_a( $main->get_processor( 'email' ), 'LLMS_Notification_Processor_Email' ) );

		// return false
		$this->assertFalse( $main->get_processor( 'thisisveryveryfake' ) );

	}

	/**
	 * test get_processors() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_processors() {

		$main = LLMS()->notifications();

		// should always return an array
		$this->assertTrue( is_array( $main->get_processors() ) );

		// each item in the array must extend the processor abstract
		foreach ( $main->get_processors() as $processor ) {
			$this->assertTrue( is_subclass_of( $processor, 'LLMS_Abstract_Notification_Processor' ) );
		}

	}

	// public function get_view( $notification ) {}

	// public function load_controller( $trigger, $path = null ) {}

	// public function load_processor( $type, $path = null ) {}

	// public function load_view( $trigger, $path = null ) {}

	// public function schedule_processing( $type ) {}

}
