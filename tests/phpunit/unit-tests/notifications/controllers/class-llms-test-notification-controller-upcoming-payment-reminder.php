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
		$this->assertTrue(	$this->controller->action_callback( $order->get( 'id' ) ) );

		// Check notification not sent for error gateway.
		$order->set( 'payment_gateway', 'garbage' );
		$this->assertFalse(	$this->controller->action_callback( $order->get( 'id' ) ) );

		// Check notification not sent for gateway that do not suppor recurring payments
		$manual = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		$manual->supports['recurring_payments'] = false;
		$order->set( 'gateway', 'manual' );
		$this->assertFalse(	$this->controller->action_callback( $order->get( 'id' ) ) );

		// Re-set recurring payments support for the manual gateway.
		$manual->supports['recurring_payments'] = true;

		// Check notification not sent for unexisting order.
		$this->assertFalse(	$this->controller->action_callback( $order->get( 'id' ) + 1  ) );
		$this->assertFalse(	$this->controller->action_callback( $post_id ) );

		// Check notication not sent for unexisting student.
		$order->set( 'user_id', $order->get( 'user_id' ) + 1 );
		$this->assertFalse(	$this->controller->action_callback( $order->get( 'id' ) ) );

		LLMS_Site::update_feature( 'recurring_payments', false );
		// Check notification not sent for staging sites.
		$this->assertFalse(	$this->controller->action_callback( $order->get( 'id' ) ) );

		LLMS_Site::update_feature( 'recurring_payments', $recurring_payments_site_feature );

	}

}
