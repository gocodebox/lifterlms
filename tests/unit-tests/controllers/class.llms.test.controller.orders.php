<?php
/**
 * Tests for the LLMS_Controller_Orders class
 * @group    orders
 * @since    3.19.0
 * @version  3.19.0
 */
class LLMS_Test_Controller_Orders extends LLMS_UnitTestCase {

	// consider dates equal within 60 seconds
	private $date_delta = 60;

	/**
	 * Test order completion actions
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
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
		$this->assertFalse( wc_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) ) );


		// test a limited date order for reactivation events
		$plan = $this->get_mock_plan( '25.99', 1, 'limited-date' );
		$order = $this->get_mock_order( $plan );
		$order->set( 'status', 'llms-pending-cancel' );
		$order->set( 'status', 'llms-active' );
		$this->assertEquals( date( 'Y-m-d', current_time( 'timestamp' ) + DAY_IN_SECONDS ), $order->get_access_expiration_date( 'Y-m-d' ) );
		// expiration event should be reset
		$this->assertEquals( (float) $order->get_access_expiration_date( 'U' ), (float) wc_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) ), '', $this->date_delta );

	}

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
			$this->assertFalse( wc_next_scheduled_action( 'llms_charge_recurring_payment', array(
				'order_id' => $order->get( 'id' ),
			) ) );

		}

	}

	/**
	 * Test expire access function
	 * @return   [type]
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_expire_access() {

		// recurring -> expire via access settings
		$plan = $this->get_mock_plan( '25.99', 1, 'limited-date' );
		$order = $this->get_mock_order( $plan );
		$order->set_status( 'active' );
		$student = llms_get_student( $order->get( 'user_id' ) );

		do_action( 'llms_access_plan_expiration', $order->get( 'id' ) );

		$this->assertFalse( $student->is_enrolled( $order->get( 'product_id' ) ) );
		$this->assertFalse( wc_next_scheduled_action( 'llms_charge_recurring_payment', array(
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
		$this->assertFalse( wc_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order->get( 'id' ),
		) ) );
		$this->assertEquals( 'cancelled', $student->get_enrollment_status( $order->get( 'product_id' ) ) );
		$this->assertEquals( 'llms-cancelled', get_post_status( $order->get( 'id' ) ) );

	}

}
