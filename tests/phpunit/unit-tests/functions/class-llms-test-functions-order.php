<?php
/**
 * Test Order Functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group orders
 *
 * @group orders
 * @group functions
 * @group functions_orders
 *
 * @since 3.27.0
 * @since 5.0.0 Updated for form handler error codes & install forms on setup.
 * @since 5.4.0 Added tests for `llms_get_possible_order_statuses()`.
 */
class LLMS_Test_Functions_Order extends LLMS_UnitTestCase {

	/**
	 * Test the llms_get_order_by_key() method.
	 *
	 * @since 3.30.1
	 *
	 * @return void
	 */
	public function test_llms_get_order_by_key() {

		// Errors.
		$this->assertTrue( is_null( llms_get_order_by_key( 'arst' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( 'arst', 'order' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( 'arst', 'id' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( 'arst', 'fake' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '1' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '1', 'order' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '1', 'id' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '1', 'fake' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( 12345 ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( 12345, 'order' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( 12345, 'id' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( 12345, 'fake' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '', 'order' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '', 'id' ) ) );
		$this->assertTrue( is_null( llms_get_order_by_key( '', 'fake' ) ) );

		// Success.
		$order = new LLMS_Order( 'new' );
		$this->assertEquals( $order, llms_get_order_by_key( $order->get( 'order_key' ) ) ); // Default.
		$this->assertEquals( $order, llms_get_order_by_key( $order->get( 'order_key' ), 'order' ) ); // Explicit.
		$this->assertEquals( $order->get( 'id' ), llms_get_order_by_key( $order->get( 'order_key' ), 'id' ) ); // Id.
		$this->assertEquals( $order->get( 'id' ), llms_get_order_by_key( $order->get( 'order_key' ), 'somethingelse' ) ); // Fake.

	}

	/**
	 * Test llms_get_order_status_name().
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_order_status_name() {
		$this->assertNotEmpty( llms_get_order_status_name( 'llms-active' ) );
		$this->assertEquals( 'Active', llms_get_order_status_name( 'llms-active' ) );
		$this->assertEquals( 'wut', llms_get_order_status_name( 'wut' ) );
	}

	/**
	 * Test llms_get_order_statuses().
	 *
	 * @since 3.3.1
	 * @since 3.19.0 Unknown.
	 *
	 * @return void
	 */
	public function test_llms_get_order_statuses() {

		$this->assertTrue( is_array( llms_get_order_statuses() ) );
		$this->assertFalse( empty( llms_get_order_statuses() ) );
		$this->assertEquals( array(
			'llms-completed',
			'llms-active',
			'llms-expired',
			'llms-on-hold',
			'llms-pending-cancel',
			'llms-pending',
			'llms-cancelled',
			'llms-refunded',
			'llms-failed',
		), array_keys( llms_get_order_statuses() ) );

		$this->assertTrue( is_array( llms_get_order_statuses( 'recurring' ) ) );
		$this->assertFalse( empty( llms_get_order_statuses( 'recurring' ) ) );
		$this->assertEquals( array(
			'llms-active',
			'llms-expired',
			'llms-on-hold',
			'llms-pending-cancel',
			'llms-pending',
			'llms-cancelled',
			'llms-refunded',
			'llms-failed',
		), array_keys( llms_get_order_statuses( 'recurring' ) ) );

		$this->assertTrue( is_array( llms_get_order_statuses( 'single' ) ) );
		$this->assertFalse( empty( llms_get_order_statuses( 'single' ) ) );
		$this->assertEquals( array(
			'llms-completed',
			'llms-pending',
			'llms-cancelled',
			'llms-refunded',
			'llms-failed',
		), array_keys( llms_get_order_statuses( 'single' ) ) );

	}

	/**
	 * Test llms_locate_order_for_email_and_plan().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_locate_order_for_email_and_plan() {

		$order   = new LLMS_Order( 'new' );
		$email   = 'locate_order_for_email_and_plan@fake.tld';
		$plan_id = $this->factory->post->create( array(
			'post_type' => 'llms_access_plan',
		) );

		$order->set( 'plan_id', $plan_id );
		$order->set_status( 'llms-pending' );

		// Invalid email & plan.
		$this->assertNull( llms_locate_order_for_email_and_plan( $email, $plan_id + 1 ) );

		// Invalid email & valid plan.
		$this->assertNull( llms_locate_order_for_email_and_plan( $email, $plan_id ) );

		$order->set( 'billing_email', $email );
		
		// Valid email & invalid plan.
		$this->assertNull( llms_locate_order_for_email_and_plan( $email, $plan_id + 1 ) );

		// Valid email & valid plan.
		$this->assertEquals( $order->get( 'id' ), llms_locate_order_for_email_and_plan( $email, $plan_id ) );

		// Only locates pending orders.
		$order->set_status( 'llms-failed' ); 
		$this->assertNull( llms_locate_order_for_email_and_plan( $email, $plan_id ) );

	}

	/**
	 * Test llms_locate_order_for_user_and_plan() method.
	 *
	 * @since 3.30.1
	 *
	 * @return void
	 */
	public function test_llms_locate_order_for_user_and_plan() {

		$order = new LLMS_Order( 'new' );

		$uid = $this->factory->student->create();
		$pid = $this->factory->post->create( array(
			'post_type' => 'llms_access_plan',
		) );

		// Fake student & fake plan
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid + 1, $pid + 1 ) ) );

		// Real student & fake plan
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid, $pid + 1 ) ) );

		// Fake student & real plan
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid + 1, $pid ) ) );

		// Real student & real plan & no order exists.
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid + 1, $pid ) ) );

		// Real student & real plan & order exists.
		$order->set( 'user_id', $uid );
		$order->set( 'plan_id', $pid );
		$this->assertSame( $order->get( 'id' ), llms_locate_order_for_user_and_plan( $uid, $pid ) );

	}

	/**
	 * Test llms_setup_pending_order()
	 *
	 * @since 3.27.0
	 * @since 5.0.0 Install forms & Updated expected error code.
	 *              Only logged in users can edit themselves.
	 * @return void
	 */
	public function test_llms_setup_pending_order() {

		LLMS_Forms::instance()->install( true );

		// Enable t&c.
		update_option( 'lifterlms_registration_require_agree_to_terms', 'yes' );
		update_option( 'lifterlms_terms_page_id', 123456789 );

		// Order data to pass to tests.
		// Will be built upon as we go through tests below.
		$order_data = array(
			'plan_id' => '',
			'agree_to_terms' => '',
			'payment_gateway' => '',
			'coupon_code' => '',
			'customer' => array(),
		);

		// Didn't agree to t&c.
		$this->setup_pending_order_fail( $order_data, 'terms-violation' );

		// Agree to t&c for all future tests.
		$order_data['agree_to_terms'] = 'yes';

		// Missing plan id.
		$this->setup_pending_order_fail( $order_data, 'missing-plan-id' );

		// Add a fake plan id.
		$order_data['plan_id'] = 123;
		$this->setup_pending_order_fail( $order_data, 'invalid-plan-id' );

		// Create a real plan and add it to the order data.
		$order_data['plan_id'] = $this->factory->post->create( array(
			'post_type' => 'llms_access_plan',
			'post_title' => 'plan name',
		) );
		update_post_meta( $order_data['plan_id'], '_llms_price', '25.00' );
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		update_post_meta( $order_data['plan_id'], '_llms_product_id', $course_id );

		// Fake coupon code.
		$order_data['coupon_code'] = 'coupon';
		$this->setup_pending_order_fail( $order_data, 'coupon-not-found' );

		// Create a real coupon.
		$coupon_id = $this->factory->post->create( array(
			'post_type' => 'llms_coupon',
			'post_title' => 'coupon',
		) );
		// But make it unusable.
		update_post_meta( $coupon_id, '_llms_expiration_date', date( 'm/d/Y', strtotime( '-1 year' ) ) );
		$this->setup_pending_order_fail( $order_data, 'invalid-coupon' );

		// Make the coupon usable.
		update_post_meta( $coupon_id, '_llms_expiration_date', date( 'm/d/Y', strtotime( '+5 years' ) ) );

		// Missing payment gateway.
		$this->setup_pending_order_fail( $order_data, 'missing-gateway-id' );

		// Fake payment gateway.
		$order_data['payment_gateway'] = 'fakeway';
		$this->setup_pending_order_fail( $order_data, 'invalid-gateway' );

		// Real payment gateway.
		$order_data['payment_gateway'] = 'manual';

		// No customer data.
		$this->setup_pending_order_fail( $order_data, 'missing-customer' );

		// Most customer data but missing required email confirm field.
		$order_data['customer'] = array(
			'user_login' => 'arstehnarst',
			'email_address' => 'arstinhasrteinharst@test.net',
			'password' => '12345678',
			'password_confirm' => '12345678',
			'first_name' => 'Test',
			'last_name' => 'Person',
			'llms_billing_address_1' => '123',
			'llms_billing_address_2' => '123',
			'llms_billing_city' => 'City',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '91231',
			'llms_billing_country' => 'US',
			'llms_phone' => '1234567890',
		);

		// Missing required field.
		$this->setup_pending_order_fail( $order_data, 'llms-form-missing-required' );

		// Existing user who's already enrolled.
		$uid = $this->factory->user->create( array( 'role' => 'student' ) );
		wp_set_current_user( $uid );
		$order_data['customer']['email_address_confirm'] = 'arstinhasrteinharst@test.net';
		$order_data['customer']['user_id'] = $uid;
		llms_enroll_student( $uid, $course_id );
		$this->setup_pending_order_fail( $order_data, 'already-enrolled' );

		// This should return an array of details we need to create a new order!
		unset( $order_data['customer']['user_id'] );
		wp_set_current_user( null );
		$order_data['customer']['email_address'] = 'arstarst@ats.net';
		$order_data['customer']['email_address_confirm'] = 'arstarst@ats.net';
		$setup = llms_setup_pending_order( $order_data );
		$this->assertEquals( array( 'person', 'plan', 'gateway', 'coupon' ), array_keys( $setup ) );

	}

	/**
	 * Test llms_get_possible_order_statuses() function for a recurring order.
	 *
	 * @since 5.4.0
	 *
	 * @return void
	 */
	public function test_get_possible_recurring_order_statuses() {
		$order = $this->get_mock_order();
		$this->assertTrue( $order->is_recurring() );
		$this->assertEquals(
			llms_get_order_statuses( 'recurring' ),
			llms_get_possible_order_statuses( $order )
		);
	}

	/**
	 * Test llms_get_possible_order_statuses() function for a single order.
	 *
	 * @since 5.4.0
	 *
	 * @return void
	 */
	public function test_get_possible_single_order_statuses() {
		$order = $this->get_mock_order();
		$order->set( 'order_type', 'single' );
		$this->assertFalse( $order->is_recurring() );
		$this->assertEquals(
			llms_get_order_statuses( 'single' ),
			llms_get_possible_order_statuses( $order )
		);
	}

	/**
	 * Test llms_get_possible_order_statuses() function for a recurring order with deleted product.
	 *
	 * @since 5.4.0
	 *
	 * @return void
	 */
	public function test_get_possible_recurring_order_statuses_deleted_product() {

		$order = $this->get_mock_order();

		// Delete product.
		wp_delete_post( $order->get( 'product_id' ) );

		$this->assertTrue( $order->is_recurring() );
		$this->assertEquals(
			array(
				'llms-expired',
				'llms-cancelled',
				'llms-refunded',
				'llms-failed',
			),
			array_keys( llms_get_possible_order_statuses( $order ) )
		);

	}

	/**
	 * Test llms_get_possible_order_statuses() function for a single with deleted product.
	 *
	 * @since 5.4.0
	 *
	 * @return void
	 */
	public function test_get_possible_single_order_statuses_deleted_product() {

		$order = $this->get_mock_order();
		$order->set( 'order_type', 'single' );

		// Delete product.
		wp_delete_post( $order->get( 'product_id' ) );

		$this->assertFalse( $order->is_recurring() );
		$this->assertEquals(
			llms_get_order_statuses( 'single' ),
			llms_get_possible_order_statuses( $order )
		);

	}


	/**
	 * Test llms_setup_pending_order() failure
	 *
	 * @since 3.27.0
	 * @since 4.9.0 Remove default optional value from `$order_data` arg for php8 compat.
	 *
	 * @param array  $order_data    Array of order data to pass to `llms_setup_pending_order()`.
	 * @param string $expected_code Expected error code.
	 * @return void
	 */
	private function setup_pending_order_fail( $order_data, $expected_code ) {

		$setup = llms_setup_pending_order( $order_data );
		$this->assertTrue( is_wp_error( $setup ) );
		$this->assertEquals( $expected_code, $setup->get_error_code() );

	}

}
