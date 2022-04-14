<?php
/**
 * Test LLMS_Payment_Gateway_Manual class.
 *
 * @group checkout
 * @group gateways
 * @group gateway_manual
 *
 * @since [version]
 */
class LLMS_Test_Gateway_Manual extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = llms()->payment_gateways()->get_gateway_by_id( 'manual' );

	}

	/**
	 * Test handle_pending_order() for a paid order.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle_pending_order() {

		$actions = array(
			did_action( 'llms_manual_payment_due' ),
			did_action( 'lifterlms_handle_pending_order_complete' ),
		);

		$plan  = $this->get_mock_plan();
		$order = $this->get_mock_order( $plan );

		$view_link = $order->get_view_link();

		try {

			$this->main->handle_pending_order( $order, $plan, null );

		} catch( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$this->assertEquals( "{$view_link} [302] YES", $exception->getMessage() );

			$this->assertEquals( ++$actions[0], did_action( 'llms_manual_payment_due' ) );
			$this->assertEquals( ++$actions[1], did_action( 'lifterlms_handle_pending_order_complete' ) );

		}

	}

}
