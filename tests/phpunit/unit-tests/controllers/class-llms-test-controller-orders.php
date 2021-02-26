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
 * @since 4.2.0 Added `test_on_user_enrollment_deleted()`.
 * @version 4.2.0
 */
class LLMS_Test_Controller_Orders extends LLMS_UnitTestCase {

	// Consider dates equal within 60 seconds.
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
	 * @param array  $supports   Gateway features array.
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

		// Student not yet enrolled.
		$this->assertFalse( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// Complete the order.
		$order->set( 'status', 'llms-completed' );

		// Student gets enrolled.
		$this->assertTrue( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// Student now has lifetime access.
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );

		// No next payment date.
		$this->assertTrue( is_a( $order->get_next_payment_due_date(), 'WP_Error' ) );

		// Actions were run.
		$this->assertEquals( 1, did_action( 'lifterlms_product_purchased' ) );
		$this->assertEquals( 1, did_action( 'lifterlms_access_plan_purchased' ) );

		/**
		 * Tests for one-time payment with access expiration
		 */
		$plan = $this->get_mock_plan( '25.99', 0, 'limited-date' );
		$order = $this->get_mock_order( $plan );

		// Student not yet enrolled.
		$this->assertFalse( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// Complete the order.
		$order->set( 'status', 'llms-completed' );

		// Student gets enrolled.
		$this->assertTrue( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// Student will expire based on expiration settings.
		$this->assertEquals( date( 'Y-m-d', current_time( 'timestamp' ) + DAY_IN_SECONDS ), $order->get_access_expiration_date() );

		// No next payment date.
		$this->assertTrue( is_a( $order->get_next_payment_due_date(), 'WP_Error' ) );

		// Actions were run.
		$this->assertEquals( 2, did_action( 'lifterlms_product_purchased' ) );
		$this->assertEquals( 2, did_action( 'lifterlms_access_plan_purchased' ) );

		/**
		 * Tests for recurring payment
		 */
		$plan = $this->get_mock_plan( '25.99', 1 );
		$order = $this->get_mock_order( $plan );

		// Student not yet enrolled.
		$this->assertFalse( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// Complete the order.
		$order->set( 'status', 'llms-active' );

		// Student gets enrolled.
		$this->assertTrue( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) );

		// Student now has lifetime access.
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );

		// No next payment date.
		$this->assertEquals( (float) date( 'U', current_time( 'timestamp' ) + DAY_IN_SECONDS ), (float) $order->get_next_payment_due_date( 'U' ), '', $this->date_delta );

		// Actions were run.
		$this->assertEquals( 3, did_action( 'lifterlms_product_purchased' ) );
		$this->assertEquals( 3, did_action( 'lifterlms_access_plan_purchased' ) );

		// Cancel the order to test reactivation.
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );
		$order->set( 'status', 'llms-pending-cancel' );
		$order->set( 'status', 'llms-active' );

		// Should still have lifetime access after reactivation.
		$this->assertEquals( 'Lifetime Access', $order->get_access_expiration_date() );

		// Expiration event should be cleared.
		$this->assertFalse( as_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) ) );

		// Test a limited date order for reactivation events.
		$plan = $this->get_mock_plan( '25.99', 1, 'limited-date' );
		$order = $this->get_mock_order( $plan );
		$order->set( 'status', 'llms-pending-cancel' );
		$order->set( 'status', 'llms-active' );
		$this->assertEquals( date( 'Y-m-d', current_time( 'timestamp' ) + DAY_IN_SECONDS ), $order->get_access_expiration_date( 'Y-m-d' ) );

		// Expiration event should be reset.
		$this->assertEquals( (float) $order->get_access_expiration_date( 'U' ), (float) as_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) ), '', $this->date_delta );

	}

	/**
	 * Test order error statuses
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

			// Schedule payments & enroll the student.
			$order->set( 'status', 'llms-active' );

			$order->maybe_schedule_payment();

			// Recurring payment is scheduled.
			$this->assertEquals( $order->get_next_payment_due_date( 'U' ), as_next_scheduled_action( 'llms_charge_recurring_payment', array(
				'order_id' => $order->get( 'id' ),
			) ) );

			// Error the order.
			$order->set( 'status', $status );

			// Student should be removed.
			$this->assertFalse( $student->is_enrolled( $order->get( 'product_id' ) ) );

			// Status should be changed.
			$this->assertEquals( $enrollment_status, $student->get_enrollment_status( $order->get( 'product_id' ) ) );

			// Recurring payment is unscheduled.
			$this->assertFalse( as_next_scheduled_action( 'llms_charge_recurring_payment', array(
				'order_id' => $order->get( 'id' ),
			) ) );

		}

	}

	/**
	 * Test delete order
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

		// Schedule payments & enroll the student.
		$order->set( 'status', 'llms-active' );

		$order->maybe_schedule_payment();

		// Recurring payment is scheduled.
		$this->assertEquals( $order->get_next_payment_due_date( 'U' ), as_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order->get( 'id' ),
		) ) );

		// Delete order.
		wp_delete_post( $order->get( 'id' ), false );

		// Student should be removed.
		$this->assertFalse( $student->is_enrolled( $order_product_id ) );

		// More in depth checks.
		// Enrollment status must be false.
		$this->assertFalse( $student->get_enrollment_status( $order_product_id ) );

		// Enrollment trigger must be false.
		$this->assertFalse( $student->get_enrollment_trigger( $order_product_id ) );

		// Enrollment date must be false.
		$this->assertFalse( $student->get_enrollment_date( $order_product_id ) );

		// Recurring payment is unscheduled.
		$this->assertFalse( as_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order_product_id,
		) ) );

	}

	/**
	 * Test on user enrollment deleted.
	 *
	 * The controller's `on_user_enrollment_deleted()` method is reponsible of changing the order status to `cancelled`
	 * in reaction to the deletion of an enrollment with the same order as trigger.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_on_user_enrollment_deleted() {

		$order            = $this->get_mock_order();
		$student_id       = $order->get( 'user_id' );
		$order_product_id = $order->get( 'product_id' );
		$order_id         = $order->get( 'id' );

		// Enroll the student.
		$order->set( 'status', 'llms-active' );

		$order_cancelled_actions = did_action( 'lifterlms_order_status_cancelled' );

		$fake_order_id = $order_id + 999;

		// Delete user enrollment passing a fake order as trigger.
		llms_delete_student_enrollment( $student_id, $order_product_id, "order_{$fake_order_id}" );
		$this->assertEquals( $order_cancelled_actions, did_action( 'lifterlms_order_status_cancelled' ) );
		// Check order status.
		$this->assertEquals( 'llms-active', llms_get_post( $order_id )->get( 'status' ) );

		// Delete user enrollment.
		llms_delete_student_enrollment( $student_id, $order_product_id, "order_{$order_id}" );
		$this->assertEquals( $order_cancelled_actions + 1, did_action( 'lifterlms_order_status_cancelled' ) );
		// Check order status.
		$this->assertEquals( 'llms-cancelled', llms_get_post( $order_id )->get( 'status' ) );

		$order_cancelled_actions = did_action( 'lifterlms_order_status_cancelled' );

		// Check that trying to delete it again doesn't trigger the action again.
		llms_delete_student_enrollment( $student_id, $order_product_id, "order_{$order_id}" );
		$this->assertEquals( $order_cancelled_actions, did_action( 'lifterlms_order_status_cancelled' ) );
		// Check order status.
		$this->assertEquals( 'llms-cancelled', llms_get_post( $order_id )->get( 'status' ) );

		// Enroll the student again on the same course with a different trigger.
		$student = llms_get_student( $student_id );
		llms_enroll_student( $student_id, $order_product_id );

		llms_delete_student_enrollment( $student_id, $order_product_id, "order_{$order_id}" );
		$this->assertEquals( $order_cancelled_actions, did_action( 'lifterlms_order_status_cancelled' ) );
		// Check order status.
		$this->assertEquals( 'llms-cancelled', llms_get_post( $order_id )->get( 'status' ) );

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

		// Recurring -> expire via access settings.
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

		// Simulate a pending-cancel -> cancel.
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

		// Starting action numbers.
		$note_actions      = did_action( 'llms_new_order_note_added' );
		$err_gw_actions    = did_action( 'llms_order_recurring_charge_gateway_error' );
		$pdue_actions      = did_action( 'llms_manual_payment_due' );
		$err_order_actions = did_action( 'llms_order_recurring_charge_gateway_error' );
		$err_user_actions  = did_action( 'llms_order_recurring_charge_user_error' );

		// Emulate a manul order deletion from the db.
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

		// Starting action numbers.
		$note_actions      = did_action( 'llms_new_order_note_added' );
		$err_gw_actions    = did_action( 'llms_order_recurring_charge_gateway_error' );
		$pdue_actions      = did_action( 'llms_manual_payment_due' );
		$err_order_actions = did_action( 'llms_order_recurring_charge_gateway_error' );
		$err_user_actions  = did_action( 'llms_order_recurring_charge_user_error' );

		// Emulate an user deletion.
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

		// Starting action numbers.
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

		// Starting action numbers.
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

		// Starting action numbers.
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

		// Starting action numbers.
		$actions = did_action( 'llms_manual_payment_due' );

		// Trigger recurring payment.
		do_action( 'llms_charge_recurring_payment', $order->get( 'id' ) );

		$this->assertSame( $actions + 1, did_action( 'llms_manual_payment_due' ) );

	}

}
