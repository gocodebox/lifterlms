<?php
/**
 * LLMS_Notifications Tests
 *
 * @package LifterLMS/Tests/Notifications
 *
 * @since 3.8.0
 * @since 3.38.0 "DRY"ed existing tests and added tests for processor scheduling related functions.
 *
 * @group notifications
 */
class LLMS_Test_Notifications extends LLMS_UnitTestCase {


	/**
	 * Setup before class.
	 *
	 * Forces notifications debugging on so that we can make assertions against logged data.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		llms_maybe_define_constant( 'LLMS_NOTIFICATIONS_LOGGING', true );
	}

	/**
	 * Setup the test case
	 *
	 * @since 3.38.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = llms()->notifications();

	}

	/**
	 * Tear down the test case
	 *
	 * @since 3.38.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		// Clear data for later tests.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'processors_to_dispatch', array() );

	}

	/**
	 * Test dispatch_processor_async() for a fake processor.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_dispatch_processor_async_fake() {

		$res = $this->main->dispatch_processor_async( 'fake-processor' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'invalid-processor', $res );

	}

	/**
	 * Test dispatch_processor_async() for a fake processor.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_dispatch_processor() {

		$res = $this->main->dispatch_processor_async( 'email' );
		$this->assertTrue( ! is_wp_error( $res ) );

	}

	/**
	 * Test the get_controller() method
	 *
	 * @since 3.8.0
	 * @since 3.38.0 Use $this->main for code DRYness.
	 *
	 * @return void
	 */
	public function test_get_controller() {

		// return the controller instance
		$this->assertTrue( is_a( $this->main->get_controller( 'lesson_complete' ), 'LLMS_Notification_Controller_Lesson_Complete' ) );

		// return false
		$this->assertFalse( $this->main->get_controller( 'thisisveryveryfake' ) );

	}

	/**
	 * Test get_controllers() method
	 *
	 * @since 3.8.0
	 * @since 3.38.0 Use $this->main for code DRYness.
	 *
	 * @return void
	 */
	public function test_get_controllers() {

		// should always return an array
		$this->assertTrue( is_array( $this->main->get_controllers() ) );

		// each item in the array must extend the controller abstract
		foreach ( $this->main->get_controllers() as $controller ) {
			$this->assertTrue( is_subclass_of( $controller, 'LLMS_Abstract_Notification_Controller' ) );
		}

	}

	/**
	 * Test get_processor() method
	 *
	 * @since 3.8.0
	 * @since 3.38.0 Use $this->main for code DRYness.
	 *
	 * @return void
	 */
	public function test_get_processor() {

		// return the controller instance
		$this->assertTrue( is_a( $this->main->get_processor( 'email' ), 'LLMS_Notification_Processor_Email' ) );

		// return false
		$this->assertFalse( $this->main->get_processor( 'thisisveryveryfake' ) );

	}

	/**
	 * test get_processors() method
	 *
	 * @since 3.8.0
	 * @since 3.38.0 Use $this->main for code DRYness.
	 *
	 * @return void
	 */
	public function test_get_processors() {

		// should always return an array
		$this->assertTrue( is_array( $this->main->get_processors() ) );

		// each item in the array must extend the processor abstract
		foreach ( $this->main->get_processors() as $processor ) {
			$this->assertTrue( is_subclass_of( $processor, 'LLMS_Abstract_Notification_Processor' ) );
		}

	}

	/**
	 * Test schedule_processing()
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_schedule_processing() {

		$expect = array( 'email' );

		// Schedule.
		$this->main->schedule_processing( 'email' );
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'processors_to_dispatch' ) );

		// Don't add duplicates.
		$this->main->schedule_processing( 'email' );
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'processors_to_dispatch' ) );

	}

	/**
	 * Test schedule_processors_dispatch()
	 *
	 * @since 3.38.0
	 * @since 6.0.0 Unschedule processors scheduled during earlier tests.
	 *
	 * @return void
	 */
	public function test_schedule_processors_dispatch() {

		as_unschedule_action( 'llms_dispatch_notification_processor_async', 'email' );

		$now = time();
		llms_tests_mock_current_time( $now );

		$this->main->schedule_processing( 'email' );
		$this->main->schedule_processing( 'fake-processor' );

		$res = $this->main->schedule_processors_dispatch();

		$this->assertArrayHaskey( 'email', $res );
		$this->assertArrayHaskey( 'fake-processor', $res );

		$this->assertEquals( $now, $res['email'] );

		$this->assertIsWPError( $res['fake-processor'] );
		$this->assertWPErrorCodeEquals( 'invalid-processor', $res['fake-processor'] );

	}

	/**
	 * Test schedule_processors_dispatch() when none are scheduled
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_schedule_processors_dispatch_none_scheduled() {

		$this->assertEquals( array(), $this->main->schedule_processors_dispatch() );

	}

	/**
	 * Test schedule_single_processor() when an event is already scheduled
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_schedule_single_processor_already_scheduled() {

		$email = $this->main->get_processor( 'email' )->push_to_queue( 1 );

		// Schedule the event.
		$orig = LLMS_Unit_Test_Util::call_method( $this->main, 'schedule_single_processor', array( $email, 'email' ) );

		// Time travel.
		llms_tests_mock_current_time( time() + HOUR_IN_SECONDS );

		// Schedule the event again.
		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'schedule_single_processor', array( $email, 'email' ) );

		// Original timestamp should be returned.
		$this->assertEquals( $orig, $res );

	}

	/**
	 * Test schedule_single_processor() when an existing event does not already exist.
	 *
	 * @since 3.38.0
	 * @since 6.0.0 Unschedule processors scheduled during earlier tests.
	 *
	 * @return void
	 */
	public function test_schedule_single_processor_new() {

		as_unschedule_action( 'llms_dispatch_notification_processor_async', 'email' );

		$email = $this->main->get_processor( 'email' )->push_to_queue( 1 );

		$now = time();
		llms_tests_mock_current_time( $now );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'schedule_single_processor', array( $email, 'email' ) );
		$this->assertEquals( $now, $res );

	}

	/**
	 * Test email processor task's method on errored notification.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_email_processor_errored_notification_task() {

		$this->markTestSkipped( "in progress" );

		$email = $this->main->get_processor( 'email' );
		$user  = $this->factory->user->create();

		$order = $this->get_mock_order();
		$txn   = $order->record_transaction(
			array(
				'amount'             => $order->get_initial_price( array(), 'float' ),
				'source_description' => 'Mock Payment',
				'transaction_id'     => uniqid( 'mock-' ),
				'status'             => 'llms-txn-succeeded',
				'payment_gateway'    => 'manual',
			)
		);

		// Create a notification for a purcahse receipt.
		$n1    = new LLMS_Notification();
		$nid_1 = $n1->create(
			array(
				'post_id'    => $txn->get('id'),
				'subscriber' => $user,
				'type'       => 'basic',
				'trigger_id' => 'purchase_receipt',
				'user_id'    => 1,
			)
		);
		$this->assertEquals( 'new', $n1->get('status') );
		// Process notification email.
		$email_processor = $this->main->get_processor( 'email' );
		$res = LLMS_Unit_Test_Util::call_method( $email_processor, 'task', array( $nid_1 ) );
		$this->assertEquals( false, $res );
		$this->assertEquals( 'sent', $n1->get('status') );

		// Delete the order so that a fatal error will be produced.
		wp_delete_post( $order->get('id') );

		$res = LLMS_Unit_Test_Util::call_method( $email_processor, 'task', array( $nid_1 ) );
		$this->assertEquals( false, $res );
		$this->assertEquals( 'error', $n1->get('status') );

		$this->assertContains(
			'Error caught Call to a member function get() on null',
			$this->logs->get( 'notifications' )
		);

		$this->logs->clear( 'notifications' );

	}

}
