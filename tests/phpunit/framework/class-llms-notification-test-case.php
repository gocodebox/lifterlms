<?php
require_once 'class-llms-unit-test-case.php';

/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS Notification Classes
 *
 * @since 3.8.0
 */
abstract class LLMS_NotificationTestCase extends LLMS_UnitTestCase {

	/**
	 * The ID of the tested notification.
	 *
	 * @var string
	 */
	protected $notification_id = '';

	/**
	 * The name of the controller class for the tested notification.
	 *
	 * @var string
	 */
	protected $controller_class = '';

	/**
	 * The name of the view class for the tested notification.
	 *
	 * @var string
	 */
	protected $view_class = '';

	/**
	 * Function used to setup arguments passed to a notification controller's `action_callback()` function.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	abstract protected function setup_args();

	/**
	 * Retrieve a notification controller for the tested notification.
	 *
	 * @since [version]
	 *
	 * @return object A child class of an LLMS_Abstract_Notification_Controller.
	 */
	protected function get_controller() {

		$main = llms()->notifications();
		return $main->get_controller( $this->notification_id );

	}

	/**
	 * Retrieve a notification object for the tested notification.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Notification
	 */
	protected function get_notification() {

		$this->last_setup_args = $this->setup_args();

		// Create a notification.
		$this->get_controller()->action_callback( ...$this->last_setup_args );

		global $wpdb;
		return new LLMS_Notification( $wpdb->insert_id );

	}

	/**
	 * Retrieve a notification view for the tested notification.
	 *
	 * @since [version]
	 *
	 * @return object A child class of an LLMS_Abstract_Notification_View.
	 */
	protected function get_view() {

		$main = llms()->notifications();
		return $main->get_view( $this->get_notification() );

	}

	/**
	 * Test notification view and controller are registered.
	 *
	 * @since 3.8.0
	 * @since [version] Test the notification view exists.
	 *
	 * @return void
	 */
	public function test_is_registered() {

		$controller = $this->get_controller();
		$this->assertTrue( is_a( $controller, 'LLMS_Abstract_Notification_Controller' ) );
		$this->assertTrue( is_a( $controller, $this->controller_class ) );
		$this->assertEquals( $this->notification_id, $controller->id );

		$view = $this->get_view();

		$this->assertTrue( is_a( $view, 'LLMS_Abstract_Notification_View' ) );
		$this->assertTrue( is_a( $view, $this->view_class ) );
		$this->assertEquals( $this->notification_id, $view->trigger_id );

	}

}
