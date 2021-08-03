<?php
/**
 * Upcoming Payment Reminder Notification Controller tests
 *
 * @package LifterLMS/Tests/Notifications/Controllers
 *
 * @group notification
 * @group notification_controller
 *
 * @since [version]
 */
class LLMS_Test_Notification_Controller_Upcoming_Payment_Reminder extends LLMS_UnitTestCase {

	/**
	 * LLMS_Abstract_Notification_Controller extending class instance
	 *
	 * @var LLMS_Abstract_Notification_Controller
	 */
	private $controller;

	/**
	 * Consider dates equal within 60 seconds
	 *
	 * @var int
	 */
	private $date_delta = 60;

	/**
	 * Set up
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setup();
		$this->controller = LLMS_Notification_Controller_Upcoming_Payment_Reminder::instance();
	}

	/**
	 * Test action_callback() method
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_action_callback() {

		// Create order.
		$order = $this->get_mock_order();
		// Create post.
		$post_id = $this->factory->post->create();

		$recurring_payments_site_feature = LLMS_Site::get_feature( 'recurring_payments' );

		LLMS_Site::update_feature( 'recurring_payments', true );

		// Check notification sent for existing order and student.
		$this->assertTrue( $this->controller->action_callback( $order->get( 'id' ) ) );

		// Check notification not sent for error gateway.
		$order->set( 'payment_gateway', 'garbage' );
		$this->assertFalse( $this->controller->action_callback( $order->get( 'id' ) ) );

		// Check notification not sent for gateway that do not support recurring payments.
		$manual = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		$manual->supports['recurring_payments'] = false;
		$order->set( 'gateway', 'manual' );
		$this->assertFalse( $this->controller->action_callback( $order->get( 'id' ) ) );

		// Re-set recurring payments support for the manual gateway.
		$manual->supports['recurring_payments'] = true;

		// Check notification not sent for unexisting order.
		$this->assertFalse( $this->controller->action_callback( $order->get( 'id' ) + 1  ) );
		$this->assertFalse( $this->controller->action_callback( $post_id ) );

		// Check notication not sent for unexisting student.
		$order->set( 'user_id', $order->get( 'user_id' ) + 1 );
		$this->assertFalse( $this->controller->action_callback( $order->get( 'id' ) ) );

		LLMS_Site::update_feature( 'recurring_payments', false );
		// Check notification not sent for staging sites.
		$this->assertFalse( $this->controller->action_callback( $order->get( 'id' ) ) );

		LLMS_Site::update_feature( 'recurring_payments', $recurring_payments_site_feature );

	}

	/**
	 * Test get_upcoming_payment_reminder_test()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_upcoming_payment_reminder_test() {

		$plan = $this->get_mock_plan( 25.99, 1, 'lifetime', false, false );
		$plan->set( 'period', 'month' ); // Month.
		$plan->set( 'length', 3 ); // for 3 total payments.

		$order = $this->get_mock_order( $plan );

		$next_payment_date = $order->get_recurring_payment_due_date_for_scheduler();

		// Reminder days (prior to the payment due date): default is 1.
		$this->assertEquals(
			strtotime( "-1 day", $next_payment_date ),
			LLMS_Unit_Test_Util::call_method(
				$this->controller,
				'get_upcoming_payment_reminder_date',
				array( $order )
			)
		);

		// Test 10 days before.
		$days_option = $this->controller->get_option( 'reminder_days' );
		$this->controller->set_option( 'reminder_days', 10 );

		// Reminder days (prior to the payment due date):10.
		$this->assertEquals(
			strtotime( "-10 day", $next_payment_date ),
			LLMS_Unit_Test_Util::call_method(
				$this->controller,
				'get_upcoming_payment_reminder_date',
				array( $order )
			)
		);

		$this->controller->set_option( 'reminder_days', $days_option );

	}

	/**
	 * Test schedule_upcoming_payment_reminder()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_schedule_upcoming_payment_reminder() {

		$plan = $this->get_mock_plan( 25.99, 1, 'lifetime', false, false );
		$plan->set( 'period', 'month' ); // Month.
		$plan->set( 'length', 3 ); // for 3 total payments.

		$order = $this->get_mock_order( $plan );

		// Upcoming payment reminder is unscheduled.
		$this->assertFalse(
			as_next_scheduled_action(
				'llms_send_upcoming_payment_reminder_notification',
				array(
					'order_id' => $order->get( 'id' ),
				)
			)
		);

		$next_payment_date = $order->get_recurring_payment_due_date_for_scheduler();

		// Schedule.
		$this->controller->schedule_upcoming_payment_reminder( $order, $next_payment_date );

		// Check next payment reminder scheduled 1 day prior to payment due date.
		$this->assertEquals(
			(float) strtotime( "-1 day", $next_payment_date ),
			as_next_scheduled_action(
				'llms_send_upcoming_payment_reminder_notification',
				array(
					'order_id' => $order->get( 'id' ),
				)
			),
			'',
			$this->date_delta
		);

		// Unschedule.
		$this->controller->unschedule_upcoming_payment_reminder( $order );

		// Fast forward.
		llms_mock_current_time( date( 'Y-m-d', $next_payment_date + WEEK_IN_SECONDS ) );

		// Try to schedule a notification that should be happen 1 week - 1 day in the past.
		$this->assertWPErrorCodeEquals( 'upcoming-payment-reminder-passed', $this->controller->schedule_upcoming_payment_reminder( $order, $next_payment_date ) );

		$this->assertFalse(
			as_next_scheduled_action(
				'llms_send_upcoming_payment_reminder_notification',
				array(
					'order_id' => $order->get( 'id' ),
				)
			)
		);

	}

}
