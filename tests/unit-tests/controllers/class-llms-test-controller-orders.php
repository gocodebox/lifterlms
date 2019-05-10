<?php
/**
 * Tests for the LLMS_Controller_Orders class
 *
 * @package LifterLMS/Tests
 *
 * @group orders
 *
 * @since 3.19.0
 * @since [version] Update to use latest action-scheduler functions.
 */
class LLMS_Test_Controller_Orders extends LLMS_UnitTestCase {

	// consider dates equal within 60 seconds
	private $date_delta = 60;

	public function setUp() {

		parent::setUp();
		LLMS_Site::update_feature( 'recurring_payments', true );

	}

	/**
	 * Disable manual gateway recurring payments for mocking error conditions.
	 *
	 * @since [version]
	 *
	 * @param array $supports Gateway features array.
	 * @param string $gateway_id Gateway ID.
	 * @return array
	 */
	public function mod_gateway_features( $supports, $gateway_id ) {

		if ( 'manual' === $gateway_id ) {
			$supports['recurring_payments'] = false;
		}

		return $supports;

	}

	/**
	 * Test order completion actions
	 *
	 * @since 3.19.0
	 * @since [version] Update to use latest action-scheduler functions.
	 *
	 * @return void
	 */
	public function test_complete_order() {

		/**
		 * Tests for one-time payment with no access expiration
		 */
		$plan = $this->get_mock_plan( '25.99', 0 );
		$order = $this->get_mock_order( $plan );

		// student not yet enrolled
		$this->assertFalse( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// complete the order
		$order->set( 'status', 'llms-completed' );

		// student gets enrolled
		$this->assertTrue( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// student now has lifetime access
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );

		// no next payment date
		$this->assertTrue( is_a( $order->get_next_payment_due_date(), 'WP_Error' ) );

		// actions were run
		$this->assertEquals( 1, did_action( 'lifterlms_product_purchased' ) );
		$this->assertEquals( 1, did_action( 'lifterlms_access_plan_purchased' ) );


		/**
		 * Tests for one-time payment with access expiration
		 */
		$plan = $this->get_mock_plan( '25.99', 0, 'limited-date' );
		$order = $this->get_mock_order( $plan );

		// student not yet enrolled
		$this->assertFalse( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// complete the order
		$order->set( 'status', 'llms-completed' );

		// student gets enrolled
		$this->assertTrue( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// student will expire based on expiration settings
		$this->assertEquals( date( 'Y-m-d', current_time( 'timestamp' ) + DAY_IN_SECONDS ), $order->get_access_expiration_date() );

		// no next payment date
		$this->assertTrue( is_a( $order->get_next_payment_due_date(), 'WP_Error' ) );

		// actions were run
		$this->assertEquals( 2, did_action( 'lifterlms_product_purchased' ) );
		$this->assertEquals( 2, did_action( 'lifterlms_access_plan_purchased' ) );


		/**
		 * Tests for recurring payment
		 */
		$plan = $this->get_mock_plan( '25.99', 1 );
		$order = $this->get_mock_order( $plan );

		// student not yet enrolled
		$this->assertFalse( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// complete the order
		$order->set( 'status', 'llms-active' );

		// student gets enrolled
		$this->assertTrue( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// student now has lifetime access
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );

		// no next payment date
		$this->assertEquals( (float) date( 'U', current_time( 'timestamp' ) + DAY_IN_SECONDS ), (float) $order->get_next_payment_due_date( 'U' ), '', $this->date_delta );

		// actions were run
		$this->assertEquals( 3, did_action( 'lifterlms_product_purchased' ) );
		$this->assertEquals( 3, did_action( 'lifterlms_access_plan_purchased' ) );


		// cancel the order to test reactivation
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );
		$order->set( 'status', 'llms-pending-cancel' );
		$order->set( 'status', 'llms-active' );
		// should still have lifetime access after reactivation
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );
		// expiration event should be cleared
		$this->assertFalse( as_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) ) );


		// test a limited date order for reactivation events
		$plan = $this->get_mock_plan( '25.99', 1, 'limited-date' );
		$order = $this->get_mock_order( $plan );
		$order->set( 'status', 'llms-pending-cancel' );
		$order->set( 'status', 'llms-active' );
		$this->assertEquals( date( 'Y-m-d', current_time( 'timestamp' ) + DAY_IN_SECONDS ), $order->get_access_expiration_date( 'Y-m-d' ) );
		// expiration event should be reset
		$this->assertEquals( (float) $order->get_access_expiration_date( 'U' ), (float) as_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) ), '', $this->date_delta );

	}

	/**
	 * test order error statuses
	 *
	 * @since 3.19.0
	 * @since [version] Update to use latest action-scheduler functions.
	 *
	 * @return [version]
	 */
	public function test_error_order() {

		$err_statuses = array(
			'llms-refunded' => 'cancelled',
			'llms-cancelled' => 'cancelled',
			'llms-expired' => 'expired',
			'llms-failed' => 'expired',
			'llms-on-hold' => 'cancelled',
			'llms-trash' => 'cancelled',
		);

		foreach ( $err_statuses as $status => $enrollment_status ) {

			$order = $this->get_mock_order();

			$student = llms_get_student( $order->get( 'user_id' ) );

			// schedule payments & enroll the student
			$order->set( 'status', 'llms-active' );

			// error the order
			$order->set( 'status', $status );

			// student should be removed
			$this->assertFalse( $student->is_enrolled( $order->get( 'product_id' ) ) );

			// status should be changed
			$this->assertEquals( $enrollment_status, $student->get_enrollment_status( $order->get( 'product_id' ) ) );

			// recurring payment is unscheduled
			$this->assertFalse( as_next_scheduled_action( 'llms_charge_recurring_payment', array(
				'order_id' => $order->get( 'id' ),
			) ) );

		}

	}

	/**
	 * Test expire access function
	 *
	 * @since 3.19.0
	 * @since [version] Update to use latest action-scheduler functions.
	 *
	 * @return void
	 */
	public function test_expire_access() {

		// recurring -> expire via access settings
		$plan = $this->get_mock_plan( '25.99', 1, 'limited-date' );
		$order = $this->get_mock_order( $plan );
		$order->set_status( 'active' );
		$student = llms_get_student( $order->get( 'user_id' ) );

		do_action( 'llms_access_plan_expiration', $order->get( 'id' ) );

		$this->assertFalse( $student->is_enrolled( $order->get( 'product_id' ) ) );
		$this->assertFalse( as_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order->get( 'id' ),
		) ) );
		$this->assertEquals( 'expired', $student->get_enrollment_status( $order->get( 'product_id' ) ) );
		$this->assertEquals( 'llms-active', $order->get( 'status' ) );


		// simulate a pending-cancel -> cancel
		$plan = $this->get_mock_plan( '25.99', 1, 'limited-date' );
		$order = $this->get_mock_order( $plan );
		$order->set_status( 'active' );
		$order->set_status( 'pending-cancel' );
		$student = llms_get_student( $order->get( 'user_id' ) );

		do_action( 'llms_access_plan_expiration', $order->get( 'id' ) );

		$this->assertFalse( $student->is_enrolled( $order->get( 'product_id' ) ) );
		$this->assertFalse( as_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order->get( 'id' ),
		) ) );
		$this->assertEquals( 'cancelled', $student->get_enrollment_status( $order->get( 'product_id' ) ) );
		$this->assertEquals( 'llms-cancelled', get_post_status( $order->get( 'id' ) ) );

	}

	/**
	 * Test gateway-related errors encountered during a recurring_charge attempt.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_recurring_charge_gateway_errors() {

		$plan = $this->get_mock_plan( '200.00', 1 );
		$order = $this->get_mock_order( $plan );

		$order->set( 'payment_gateway', 'fake-gateway' );

		// starting action numbers.
		$note_actions = did_action( 'llms_new_order_note_added' );
		$err_actions = did_action( 'llms_order_recurring_charge_gateway_error' );

		// Trigger recurring payment.
		do_action( 'llms_charge_recurring_payment', $order->get( 'id' ) );

		$this->assertSame( $note_actions + 1, did_action( 'llms_new_order_note_added' ) );
		$this->assertSame( $err_actions + 1, did_action( 'llms_order_recurring_charge_gateway_error' ) );

	}

	/**
	 * Test a recurring payment processed when recurring payments are disabled on the site.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_recurring_charge_staging_mode() {

		// Disable recurring payments.
		LLMS_Site::update_feature( 'recurring_payments', false );

		$plan = $this->get_mock_plan( '200.00', 1 );
		$order = $this->get_mock_order( $plan );

		// starting action numbers.
		$skip_actions = did_action( 'llms_order_recurring_charge_skipped' );
		$note_actions = did_action( 'llms_new_order_note_added' );

		// Trigger recurring payment.
		do_action( 'llms_charge_recurring_payment', $order->get( 'id' ) );

		$this->assertSame( $note_actions + 1, did_action( 'llms_new_order_note_added' ) );
		$this->assertSame( $skip_actions + 1, did_action( 'llms_order_recurring_charge_skipped' ) );

	}

	/**
	 * Test gateway-related errors encountered during a recurring_charge attempt.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_recurring_charge_gateway_support_disabled() {

		$plan = $this->get_mock_plan( '200.00', 1 );
		$order = $this->get_mock_order( $plan );

		// Disable recurring payments.
		add_filter( 'llms_get_gateway_supported_features', array( $this, 'mod_gateway_features' ), 10, 2 );

		// starting action numbers.
		$err_actions = did_action( 'llms_order_recurring_charge_gateway_payments_disabled' );
		$note_actions = did_action( 'llms_new_order_note_added' );

		// Trigger recurring payment.
		do_action( 'llms_charge_recurring_payment', $order->get( 'id' ) );

		$this->assertSame( $note_actions + 1, did_action( 'llms_new_order_note_added' ) );
		$this->assertSame( $err_actions + 1, did_action( 'llms_order_recurring_charge_gateway_payments_disabled' ) );

		// Re-enable recurring payments.
		remove_filter( 'llms_get_gateway_supported_features', array( $this, 'mod_gateway_features' ), 10, 2 );

	}

	/**
	 * Test gateway-related errors encountered during a recurring_charge attempt.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_recurring_charge_success() {

		$plan = $this->get_mock_plan( '200.00', 1 );
		$order = $this->get_mock_order( $plan );

		// starting action numbers.
		$actions = did_action( 'llms_manual_payment_due' );

		// Trigger recurring payment.
		do_action( 'llms_charge_recurring_payment', $order->get( 'id' ) );

		$this->assertSame( $actions + 1, did_action( 'llms_manual_payment_due' ) );

	}

}
