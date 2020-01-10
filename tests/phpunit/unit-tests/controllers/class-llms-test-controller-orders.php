<?php
/**
 * Tests for the LLMS_Controller_Orders class
 *
 * @package LifterLMS/Tests
 *
 * @group orders
 *
 * @since 3.19.0
 * @since 3.32.0 Update to use latest action-scheduler functions.
 * @since 3.33.0 Add test for the `on_delete_order` method.
 * @since 3.36.1 When testing deleting/erroring orders make sure to schedule a recurring payment when setting an order as active so that,
 *               when subsequently we error/delete the order, checking the recurring payment is unscheduled makes sense.
 *               Also add tests on recurrint payments not processed when order or user deleted.
 *
 * @version 3.36.1
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
	 * @since 3.32.0
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
	 * @since 3.32.0 Update to use latest action-scheduler functions.
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
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 * @since 3.36.1 Make sure to schedule a recurring payment when setting an order as active so that,
	 *               when subsequently we error the order, checking the recurring payment is unscheduled maskes sense.
	 *
	 * @return void
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

			$order->maybe_schedule_payment();

			// recurring payment is scheduled
			$this->assertEquals( $order->get_next_payment_due_date( 'U' ), as_next_scheduled_action( 'llms_charge_recurring_payment', array(
				'order_id' => $order->get( 'id' ),
			) ) );

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
	 * test delete order
	 *
	 * @since 3.33.0
	 * @since 3.36.1 Check recurring payment is unscheduled.
	 *
	 * @return void
	 */
	public function test_on_delete_order() {

		$order   = $this->get_mock_order();
		$student = llms_get_student( $order->get( 'user_id' ) );

		$order_product_id = $order->get( 'product_id' );

		// schedule payments & enroll the student
		$order->set( 'status', 'llms-active' );

		$order->maybe_schedule_payment();

		// recurring payment is scheduled
		$this->assertEquals( $order->get_next_payment_due_date( 'U' ), as_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order->get( 'id' ),
		) ) );

		// delete order
		wp_delete_post( $order->get( 'id' ), false );

		// student should be removed
		$this->assertFalse( $student->is_enrolled( $order_product_id ) );

		// more in depth checks
		// enrollment status must be false
		$this->assertFalse( $student->get_enrollment_status( $order_product_id ) );

		// enrollment trigger must be false
		$this->assertFalse( $student->get_enrollment_trigger( $order_product_id ) );

		// enrollment date must be false
		$this->assertFalse( $student->get_enrollment_date( $order_product_id ) );

		// recurring payment is unscheduled
		$this->assertFalse( as_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order_product_id,
		) ) );

	}


	/**
	 * Test expire access function
	 *
	 * @since 3.19.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
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
	 * Test recurring_charge attempts on orders manually removed from the database.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_recurring_charge_on_manually_deleted_order() {

		$plan     = $this->get_mock_plan( '200.00', 1 );
		$order    = $this->get_mock_order( $plan );
		$order_id = $order->get( 'id' );

		// starting action numbers.
		$note_actions      = did_action( 'llms_new_order_note_added' );
		$err_gw_actions    = did_action( 'llms_order_recurring_charge_gateway_error' );
		$pdue_actions      = did_action( 'llms_manual_payment_due' );
		$err_order_actions = did_action( 'llms_order_recurring_charge_gateway_error' );
		$err_user_actions  = did_action( 'llms_order_recurring_charge_user_error' );

		// emulate a manul order deletion from the db.
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'posts', array( 'id' => $order_id ) );
		clean_post_cache( $order_id );

		// Trigger recurring payment.
		do_action( 'llms_charge_recurring_payment', $order_id );

		$this->assertSame( $pdue_actions, did_action( 'llms_manual_payment_due' ) );
		$this->assertSame( $note_actions, did_action( 'llms_new_order_note_added' ) );
		$this->assertSame( $err_gw_actions, did_action( 'llms_order_recurring_charge_gateway_error' ) );
		$this->assertSame( $err_order_actions + 1, did_action( 'llms_order_recurring_charge_order_error' ) );
		$this->assertSame( $err_user_actions, did_action( 'llms_order_recurring_charge_user_error' ) );

	}


	/**
	 * Test recurring_charge attempts on orders whose user has been deleted.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_recurring_charge_on_deleted_user() {

		$plan     = $this->get_mock_plan( '200.00', 1 );
		$order    = $this->get_mock_order( $plan );
		$order_id = $order->get( 'id' );

		// starting action numbers.
		$note_actions      = did_action( 'llms_new_order_note_added' );
		$err_gw_actions    = did_action( 'llms_order_recurring_charge_gateway_error' );
		$pdue_actions      = did_action( 'llms_manual_payment_due' );
		$err_order_actions = did_action( 'llms_order_recurring_charge_gateway_error' );
		$err_user_actions  = did_action( 'llms_order_recurring_charge_user_error' );

		// emulate an user deletion.
		wp_delete_user( $order->get( 'user_id' ) );

		// Trigger recurring payment.
		do_action( 'llms_charge_recurring_payment', $order_id );

		$this->assertSame( $pdue_actions, did_action( 'llms_manual_payment_due' ) );
		$this->assertSame( $note_actions + 1, did_action( 'llms_new_order_note_added' ) );
		$this->assertSame( $err_gw_actions, did_action( 'llms_order_recurring_charge_gateway_error' ) );
		$this->assertSame( $err_order_actions, did_action( 'llms_order_recurring_charge_order_error' ) );
		$this->assertSame( $err_user_actions + 1, did_action( 'llms_order_recurring_charge_user_error' ) );

	}

	/**
	 * Test gateway-related errors encountered during a recurring_charge attempt.
	 *
	 * @since 3.32.0
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
	 * @since 3.32.0
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
	 * @since 3.32.0
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
	 * @since 3.32.0
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
