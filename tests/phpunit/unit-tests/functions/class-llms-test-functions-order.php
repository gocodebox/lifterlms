<?php
/**
 * Test Order Functions
 * @group    orders
 * @since    3.27.0
 * @version  3.30.1
 *
 */
class LLMS_Test_Functions_Order extends LLMS_UnitTestCase {

	/**
	 * Test the llms_get_order_by_key() method.
	 *
	 * @since 3.30.1
	 * @version 3.30.1
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
	 * Test llms_get_order_status_name()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_order_status_name() {
		$this->assertNotEmpty( llms_get_order_status_name( 'llms-active' ) );
		$this->assertEquals( 'Active', llms_get_order_status_name( 'llms-active' ) );
		$this->assertEquals( 'wut', llms_get_order_status_name( 'wut' ) );
	}

	/**
	 * test llms_get_order_statuses()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.19.0
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
	 * Test llms_locate_order_for_user_and_plan() method
	 *
	 * @since 3.30.1
	 * @version 3.30.1
	 *
	 * @return void
	 */
	public function test_llms_locate_order_for_user_and_plan() {

		$order = new LLMS_Order( 'new' );

		$uid = $this->factory->student->create();
		$pid = $this->factory->post->create( array(
			'post_type' => 'llms_access_plan',
		) );

		// fake student & fake plan
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid + 1, $pid + 1 ) ) );

		// real student & fake plan
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid, $pid + 1 ) ) );

		// fake student & real plan
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid + 1, $pid ) ) );

		// real student & real plan & no order exists.
		$this->assertTrue( is_null( llms_locate_order_for_user_and_plan( $uid + 1, $pid ) ) );

		// real student & real plan & order exists.
		$order->set( 'user_id', $uid );
		$order->set( 'plan_id', $pid );
		$this->assertSame( $order->get( 'id' ), llms_locate_order_for_user_and_plan( $uid, $pid ) );

	}

	/**
	 * Test llms_setup_pending_order()
	 *
	 * @since 3.27.0
	 * @version 3.27.0
	 *
	 * @return void
	 */
	public function test_llms_setup_pending_order() {

		// enable t&c
		update_option( 'lifterlms_registration_require_agree_to_terms', 'yes' );
		update_option( 'lifterlms_terms_page_id', 123456789 );

		// order data to pass to tests
		// will be built upon as we go through tests below
		$order_data = array(
			'plan_id' => '',
			'agree_to_terms' => '',
			'payment_gateway' => '',
			'coupon_code' => '',
			'customer' => array(),
		);

		// didn't agree to t&c
		$this->setup_pending_order_fail( $order_data, 'terms-violation' );

		// agree to t&c for all future tests
		$order_data['agree_to_terms'] = 'yes';

		// missing plan id
		$this->setup_pending_order_fail( $order_data, 'missing-plan-id' );

		// add a fake plan id
		$order_data['plan_id'] = 123;
		$this->setup_pending_order_fail( $order_data, 'invalid-plan-id' );

		// create a real plan and add it to the order data
		$order_data['plan_id'] = $this->factory->post->create( array(
			'post_type' => 'llms_access_plan',
			'post_title' => 'plan name',
		) );
		update_post_meta( $order_data['plan_id'], '_llms_price', '25.00' );
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		update_post_meta( $order_data['plan_id'], '_llms_product_id', $course_id );

		// fake coupon code
		$order_data['coupon_code'] = 'coupon';
		$this->setup_pending_order_fail( $order_data, 'coupon-not-found' );

		// create a real coupon
		$coupon_id = $this->factory->post->create( array(
			'post_type' => 'llms_coupon',
			'post_title' => 'coupon',
		) );
		// but make it unusable
		update_post_meta( $coupon_id, '_llms_expiration_date', date( 'm/d/Y', strtotime( '-1 year' ) ) );
		$this->setup_pending_order_fail( $order_data, 'invalid-coupon' );

		// make the coupon usable
		update_post_meta( $coupon_id, '_llms_expiration_date', date( 'm/d/Y', strtotime( '+5 years' ) ) );

		// missing payment gateway
		$this->setup_pending_order_fail( $order_data, 'missing-gateway-id' );

		// fake payment gateway
		$order_data['payment_gateway'] = 'fakeway';
		$this->setup_pending_order_fail( $order_data, 'invalid-gateway' );

		// real payment gateway
		$order_data['payment_gateway'] = 'manual';

		// no customer data
		$this->setup_pending_order_fail( $order_data, 'missing-customer' );

		// most customer data but missing required first name field
		$order_data['customer'] = array(
			'user_login' => 'arstehnarst',
			'email_address' => 'arstinhasrteinharst@test.net',
			'email_address_confirm' => 'arstinhasrteinharst@test.net',
			'password' => '123456',
			'password_confirm' => '123456',
			'last_name' => 'Person',
			'llms_billing_address_1' => '123',
			'llms_billing_address_2' => '123',
			'llms_billing_city' => 'City',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '91231',
			'llms_billing_country' => 'US',
			'llms_phone' => '1234567890',
		);

		// missing required field
		$this->setup_pending_order_fail( $order_data, 'first_name' );

		// existing user who's already enrolled
		$order_data['customer']['first_name'] = 'Test';
		$order_data['customer']['user_id'] = $this->factory->user->create( array( 'role' => 'student' ) );
		llms_enroll_student( $order_data['customer']['user_id'], $course_id );
		$this->setup_pending_order_fail( $order_data, 'already-enrolled' );

		// this should return an array of details we need to create a new order!
		unset( $order_data['customer']['user_id'] );
		$order_data['customer']['email_address'] = 'arstarst@ats.net';
		$order_data['customer']['email_address_confirm'] = 'arstarst@ats.net';
		$setup = llms_setup_pending_order( $order_data );
		$this->assertEquals( array( 'person', 'plan', 'gateway', 'coupon' ), array_keys( $setup ) );

	}

	/**
	 * Test llms_setup_pending_order() failure
	 *
	 * @since 3.27.0
	 * @version 3.27.0
	 *
	 * @return void
	 */
	private function setup_pending_order_fail( $order_data = array(), $expected_code ) {

		$setup = llms_setup_pending_order( $order_data );

		$this->assertTrue( is_wp_error( $setup ) );
		$this->assertEquals( $expected_code, $setup->get_error_code() );

	}

}
