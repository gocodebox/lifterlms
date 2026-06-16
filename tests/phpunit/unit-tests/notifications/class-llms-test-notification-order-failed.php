<?php
/**
 * LLMS_Notification Order Failed
 *
 * @package LifterLMS/Tests/Notifications
 *
 * @group notifications
 *
 * @since [version]
 */
class LLMS_Test_Notification_Order_Failed extends LLMS_NotificationTestCase {

	/**
	 * The ID of the tested notification.
	 *
	 * @var string
	 */
	protected $notification_id = 'order_failed';

	/**
	 * The name of the controller class for the tested notification.
	 *
	 * @var string
	 */
	protected $controller_class = 'LLMS_Notification_Controller_Order_Failed';

	/**
	 * The name of the view class for the tested notification.
	 *
	 * @var string
	 */
	protected $view_class = 'LLMS_Notification_View_Order_Failed';

	/**
	 * The order created for the test.
	 *
	 * @var LLMS_Order
	 */
	private $order;

	/**
	 * Function used to setup arguments passed to a notification controller's `action_callback()` function.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function setup_args() {

		$order = $this->get_mock_order();
		$this->order = $order;

		// Mark the order as failed which fires `lifterlms_order_status_failed`.
		$order->set( 'status', 'llms-failed' );

		return array( $order );

	}

	/**
	 * Test set_merge_data()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_merge_data() {

		$view = $this->get_view();

		$order = $this->order;

		$tests = array(
			'{{ORDER_ID}}'       => $order->get( 'id' ),
			'{{ORDER_URL}}'      => esc_url( $order->get_view_link() ),
			'{{PRODUCT_TITLE}}'  => $order->get( 'product_title' ),
			'{{PLAN_TITLE}}'     => $order->get( 'plan_title' ),
			'{{CUSTOMER_PHONE}}' => $order->get( 'billing_phone' ),
			'{{FAKE_CODE}}'      => '{{FAKE_CODE}}',
		);

		foreach ( $tests as $code => $expected ) {
			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $view, 'set_merge_data', array( $code ) ) );
		}

		// {{RETRY_NOTICE}} should always return a non-empty string.
		$notice = LLMS_Unit_Test_Util::call_method( $view, 'set_merge_data', array( '{{RETRY_NOTICE}}' ) );
		$this->assertNotEmpty( $notice );

	}

	/**
	 * Test that the controller exposes a title.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_title() {
		$this->assertEquals( 'Order Failed', $this->get_controller()->get_title() );
	}

}
