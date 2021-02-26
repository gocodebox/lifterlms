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

		// Check notification not sent for unexisting order.
		$this->assertFalse(	$this->controller->action_callback( $order->get( 'id' ) + 1  ) );
		$this->assertFalse(	$this->controller->action_callback( $post_id ) );

		// Check notification sent for existing order and student.
		$this->assertTrue(	$this->controller->action_callback( $order->get( 'id' ) ) );

		// Check notication not sent for unexisting student.
		$order->set( 'user_id', $order->get( 'user_id' ) + 1 );
		$this->assertFalse(	$this->controller->action_callback( $order->get( 'id' ) ) );

	}

}
