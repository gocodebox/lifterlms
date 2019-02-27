<?php
/**
 * Test Order Functions
 * @group    orders
 * @since    3.27.0
 * @version  3.27.0
 *
 */
class LLMS_Test_Functions_Order extends LLMS_UnitTestCase {

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

		// make the coupon useable
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


	private function setup_pending_order_fail( $order_data = array(), $expected_code ) {

		$setup = llms_setup_pending_order( $order_data );

		$this->assertTrue( is_wp_error( $setup ) );
		$this->assertEquals( $expected_code, $setup->get_error_code() );

	}

}
