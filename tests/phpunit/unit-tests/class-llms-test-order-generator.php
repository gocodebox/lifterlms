<?php
/**
 * Test LLMS_Order_Generator
 *
 * @package LifterLMS/Tests
 *
 * @group orders
 * @group order_generator
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Order_Generator extends LLMS_UnitTestCase {

	/**
	 * Retrieves an array of mock user data for use in generator data submission.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_mock_user_data() {

		$email = wp_generate_password( 5, false ) . '@' . wp_generate_password( 5, false ) . '.tld';

		return array(
			'email_address'          => $email,
			'email_address_confirm'  => $email,
			'password'               => '12345678',
			'password_confirm'       => '12345678',
			'first_name'             => 'Fred',
			'last_name'              => 'Stevens',
			'llms_phone'             => '1234567890',
			'llms_billing_address_1' => '123 A Street',
			'llms_billing_address_2' => '#456',
			'llms_billing_city'      => 'City',
			'llms_billing_state'     => 'State',
			'llms_billing_zip'       => '12345',
			'llms_billing_country'   => 'CA',
		);

	}

	private function get_mock_data() {

		$data = array(
			'llms_plan_id'         => $this->get_mock_plan()->get( 'id' ),
			'llms_payment_gateway' => 'manual',
		);
		$user_data = $this->get_mock_user_data();

		return array_merge( $data, $user_data );

	}

	/**
	 * Test confirm() when validation errors are encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_validation_errors() {

		$gen = new LLMS_Order_Generator( array() );
		$res = $gen->confirm();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_ORDER_NOT_FOUND, $res );

	}

	/**
	 * Test confirm() when the gateway encounters an error.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_gateway_errors() {

		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-confirm-err';
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
			public function confirm_pending_order( $order ) {
				return new WP_Error( 'gateway-err', 'Message' );
			}
		};

		$gateway->supports['recurring_payments'] = true;
		$gateway->set_option( 'enabled', 'yes' );
		llms()->payment_gateways()->payment_gateways[] = $gateway;


		$order = new LLMS_Order( 'new' );
		$order->set( 'payment_gateway', 'fake-confirm-err' );

		$data                         = $this->get_mock_data();
		$data['llms_order_key']       = $order->get( 'order_key' );
		$data['llms_payment_gateway'] = 'fake-confirm-err';

		$gen = new LLMS_Order_Generator( $data );
		LLMS_Unit_Test_Util::set_private_property( $gen, 'gateway', $gateway );

		$res = $gen->confirm();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'gateway-err', $res );


	}

	/**
	 * Test confirm() success
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_success() {

		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-confirm-success';
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
			public function confirm_pending_order( $order ) {
				return array(
					'txn_id' => 12345,
				);
			}
		};

		$gateway->supports['recurring_payments'] = true;
		$gateway->set_option( 'enabled', 'yes' );
		llms()->payment_gateways()->payment_gateways[] = $gateway;


		$order = new LLMS_Order( 'new' );
		$order->set( 'payment_gateway', 'fake-confirm-success' );

		$data                         = $this->get_mock_data();
		$data['llms_order_key']       = $order->get( 'order_key' );
		$data['llms_payment_gateway'] = 'fake-confirm-success';

		$gen = new LLMS_Order_Generator( $data );
		LLMS_Unit_Test_Util::set_private_property( $gen, 'gateway', $gateway );

		$this->assertEquals( array( 'txn_id' => 12345 ), $gen->confirm() );

		// User data should have been stored.
		$this->assertEquals( $data['email_address'], $order->get( 'billing_email' ) );

	}

	/**
	 * Test create() when an error is encountered creating the order post.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_error() {

		// Forces an error to be encountered during order creation.
		add_filter( 'wp_insert_post_empty_content', '__return_true' );

		$gen = new LLMS_Order_Generator( array() );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'create' );

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_CREATE_ORDER, $res );

		remove_filter( 'wp_insert_post_empty_content', '__return_true' );

	}

	/**
	 * Test commit_user().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_commit_user() {

		LLMS_Forms::instance()->install( true );

		$data = $this->get_mock_user_data();

		// Register a new user.
		$gen = new LLMS_Order_Generator( $data );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'commit_user' );

		$this->assertTrue( is_numeric( $res ) );
		$student = llms_get_student( $res );
		$this->assertEquals( $data['email_address'], $student->get( 'user_email' ) );
		$this->assertEquals( $data['first_name'], $student->get( 'first_name' ) );

		// Update the user.
		wp_set_current_user( $res );
		$data['first_name'] = 'Albert';
		$gen  = new LLMS_Order_Generator( $data );
		$res  = LLMS_Unit_Test_Util::call_method( $gen, 'commit_user' );

		$this->assertEquals( $student->get( 'id' ), $res );
		$this->assertEquals( $data['email_address'], $student->get( 'user_email' ) );
		$this->assertEquals( $data['first_name'], $student->get( 'first_name' ) );

	}

	/**
	 * Test error().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_error() {

		$data = array( 'input' => 1 );
		$gen  = new LLMS_Order_Generator( $data );

		foreach ( array( 'coupon', 'gateway', 'plan', 'student', 'order' ) as $var ) {
			LLMS_Unit_Test_Util::set_private_property( $gen, $var, "{$var}_value" );
		}


		$res = LLMS_Unit_Test_Util::call_method(
			$gen,
			'error',
			array( 'mock-code', 'Mock Message', array( 'extra' => 1 ) )
		);

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'mock-code', $res );
		$this->assertWPErrorMessageEquals( 'Mock Message', $res );
		$this->assertWPErrorDataEquals(
			array(
				'coupon'      => 'coupon_value',
				'data'        => $data,
				'gateway'     => 'gateway_value',
				'plan'        => 'plan_value',
				'student'     => 'student_value',
				'extra'       => 1,
				'order'       => 'order_value',
			),
			$res
		);

	}

	/**
	 * Test generate() with validation errors.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_validation_errors() {

		$gen = new LLMS_Order_Generator( array() );
		$res = $gen->generate();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_PLAN_REQUIRED, $res );

	}

	/**
	 * Test generate() when an error is encountered during the user commit step.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_user_commit_errors() {

		$data = $this->get_mock_data();

		// After validation passes, register the user so the commit will fail.
		$handler = function( $valid ) use ( $data ) {
			$this->factory->user->create( array(
				'user_email' => $data['email_address'],
			) );
			return $valid;
		};
		add_filter( 'llms_after_generate_order_validation', $handler );

		$gen = new LLMS_Order_Generator( $data );
		$res = $gen->generate();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'existing_user_email', $res );

		remove_filter( 'llms_after_generate_order_validation', $handler );

	}

	/**
	 * Test generate() with commit success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_success_user_commit() {

		$data = $this->get_mock_data();

		$gen = new LLMS_Order_Generator( $data );
		$res = $gen->generate();

		$this->assertTrue( is_a( $res, 'LLMS_Order' ) );

		$this->assertEquals( $data['llms_plan_id'], $res->get( 'plan_id' ) );
		$this->assertEquals( $data['email_address'], $res->get( 'billing_email' ) );

		$this->assertEquals(
			llms_get_student( get_user_by( 'email', $data['email_address'] ) ),
			$gen->get_student()
		);

	}

	/**
	 * Test generate() with user validation only success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_success_user_validate() {

		$data = $this->get_mock_data();

		$gen = new LLMS_Order_Generator( $data );
		$res = $gen->generate( $gen::UA_VALIDATE );

		$this->assertTrue( is_a( $res, 'LLMS_Order' ) );

		$this->assertEquals( $data['llms_plan_id'], $res->get( 'plan_id' ) );
		$this->assertEquals( $data['email_address'], $res->get( 'billing_email' ) );

		$this->assertNull( $gen->get_student() );

	}


	/**
	 * Test the protected property getter methods.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_getters() {

		$gen = new LLMS_Order_Generator( array() );
		foreach ( array( 'coupon', 'gateway', 'plan', 'student', 'order' ) as $var ) {
			$val = "{$var}_value";
			LLMS_Unit_Test_Util::set_private_property( $gen, $var, $val );
			$this->assertEquals( $val, $gen->{"get_{$var}"}() );
		}

	}

	/**
	 * Test get_order_id().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_order_id() {

		// Not submitted: create a new order.
		$gen = new LLMS_Order_Generator( array() );
		$this->assertEquals( 'new', LLMS_Unit_Test_Util::call_method( $gen, 'get_order_id' ) );

		// Invalid key submitted.
		$gen = new LLMS_Order_Generator( array( 'llms_order_key' => 'fake' ) );
		$this->assertEquals( 'new', LLMS_Unit_Test_Util::call_method( $gen, 'get_order_id' ) );

		// Actual key submitted.
		$order = new LLMS_Order( 'new' );
		$gen = new LLMS_Order_Generator( array( 'llms_order_key' => $order->get( 'order_key' ) ) );
		$this->assertEquals( $order->get( 'id' ), LLMS_Unit_Test_Util::call_method( $gen, 'get_order_id' ) );

	}

	/**
	 * Test get_order_id() lookup by user/email & plan.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_order_id_by_user_and_plan() {

		$user_id = $this->factory->user->create( array( 'user_email' => 'email@test.tld' ) );
		$plan_id = $this->factory->post->create( array( 'post_type' => 'llms_access_plan' ) );

		$order = new LLMS_Order( 'new' );
		$order->set_bulk( compact( 'user_id', 'plan_id' ) );

		// Lookup by email of an existing user & plan.
		$gen = new LLMS_Order_Generator( array( 
			'llms_plan_id'  => $plan_id,
			'email_address' => 'email@test.tld',
		) );
		$this->assertEquals( $order->get( 'id' ), LLMS_Unit_Test_Util::call_method( $gen, 'get_order_id' ) );

		// Lookup using current user and plan.
		wp_set_current_user( $user_id );
		$gen = new LLMS_Order_Generator( array( 
			'llms_plan_id'  => $plan_id,
		) );
		$this->assertEquals( $order->get( 'id' ), LLMS_Unit_Test_Util::call_method( $gen, 'get_order_id' ) );

	}


	/**
	 * Test get_order_id() lookup by email (for a non-existent user) & plan.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_order_id_by_email_and_plan() {

		$billing_email = 'notstored@email.tld';
		$plan_id       = $this->factory->post->create( array( 'post_type' => 'llms_access_plan' ) );

		$order = new LLMS_Order( 'new' );
		$order->set_bulk( compact( 'billing_email', 'plan_id' ) );

		// Lookup by email of an existing user & plan.
		$gen = new LLMS_Order_Generator( array( 
			'llms_plan_id'  => $plan_id,
			'email_address' => $billing_email,
		) );
		$this->assertEquals( $order->get( 'id' ), LLMS_Unit_Test_Util::call_method( $gen, 'get_order_id' ) );

	}

	/**
	 * Test get_user_data().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_user_data() {

		$data     = $this->get_mock_user_data();
		$gen      = new LLMS_Order_Generator( $data );
		$expected = array(
			'billing_email'      => $data['email_address'],
			'billing_first_name' => 'Fred',
			'billing_last_name'  => 'Stevens',
			'billing_address_1'  => '123 A Street',
			'billing_address_2'  => '#456',
			'billing_city'       => 'City',
			'billing_state'      => 'State',
			'billing_zip'        => '12345',
			'billing_country'    => 'CA',
			'billing_phone'      => '1234567890',
			'user_id'            => '',
		);
		$this->assertEquals( $expected, $gen->get_user_data() );

		// With Student.
		$student = $this->factory->student->create_and_get();
		LLMS_Unit_Test_Util::set_private_property( $gen, 'student', $student );

		$expected['user_id'] = $student->get( 'id' );
		$this->assertEquals( $expected, $gen->get_user_data() );

	}

	/**
	 * Test get_user_data() when incomplete data is submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_user_data_incomplete() {

		// Incomplete data.
		$gen = new LLMS_Order_Generator( array( 'email_address' => 'a@b.tld' ) );

		$expected = array(
			'billing_email'      => 'a@b.tld',
			'billing_first_name' => '',
			'billing_last_name'  => '',
			'billing_address_1'  => '',
			'billing_address_2'  => '',
			'billing_city'       => '',
			'billing_state'      => '',
			'billing_zip'        => '',
			'billing_country'    => '',
			'billing_phone'      => '',
			'user_id'            => '',
		);
		$this->assertEquals( $expected, $gen->get_user_data() );

	}

	/**
	 * Test validate() during an early return from a 3rd party.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_err_before() {

		$gen = new LLMS_Order_Generator( array() );

		$handler = function() {
			return new WP_Error( 'mock', 'Message' );
		};
		add_filter( 'llms_before_generate_order_validation', $handler );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'mock', $res );
		$this->assertWPErrorMessageEquals( 'Message', $res );

		remove_filter( 'llms_before_generate_order_validation', $handler );

	}

	/**
	 * Test validate() when a validation error is encountered().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_error() {

		$gen = new LLMS_Order_Generator( array() );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_PLAN_REQUIRED, $res );

	}

	/**
	 * Test validate() when a validation error is encountered() with order data.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_error_order() {

		$gen = new LLMS_Order_Generator( array(
			'llms_order_key' => 'fake',
		) );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate', array( true ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_ORDER_NOT_FOUND, $res );

	}


	/**
	 * Test validate() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_success() {

		$data = $this->get_mock_data();
		$gen = new LLMS_Order_Generator( $data );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate' ) );

		// Order not validated.
		$this->assertNull( $gen->get_order() );

	}

	/**
	 * Test validate() success with order validation.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_success_with_order() {

		$order = new LLMS_Order( 'new' );

		$data                   = $this->get_mock_data();
		$data['llms_order_key'] = $order->get( 'order_key' );

		$gen = new LLMS_Order_Generator( $data );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate', array( true ) ) );

		// Order was validated
		$this->assertEquals( $order, $gen->get_order() );

	}

	/**
	 * Test validate_coupon() when no coupon is submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_coupon_not_submitted() {

		$gen = new LLMS_Order_Generator( array() );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_coupon' ) );

	}

	/**
	 * Test validate_coupon() when the supplied coupon can't be found.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_coupon_not_found() {

		$gen = new LLMS_Order_Generator( array( 'llms_coupon_code' => 'fake' ) );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_coupon' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_COUPON_NOT_FOUND, $res );

	}

	/**
	 * Test validate_coupon() when the supplied coupon isn't valid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_coupon_not_valid() {

		$coupon = new LLMS_Coupon( 'new', 'expiredcoupon' );
		$coupon->set( 'status', 'publish' );
		$coupon->set( 'expiration_date', '01/01/2015' );

		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id'     => $this->get_mock_plan()->get( 'id' ),
			'llms_coupon_code' => 'expiredcoupon'
		) );

		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_coupon' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_COUPON_INVALID, $res );

	}

	/**
	 * Test validate_coupon() with a valid coupon.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_coupon_valid() {

		$coupon = new LLMS_Coupon( 'new', 'validcoupon' );
		$coupon->set( 'status', 'publish' );

		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id'     => $this->get_mock_plan()->get( 'id' ),
			'llms_coupon_code' => 'validcoupon'
		) );

		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_coupon' ) );
		$this->assertEquals( $coupon, $gen->get_coupon() );

	}

	/**
	 * Test validate_gateway() when no gateway is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_gateway_no_gateway() {

		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id' => $this->get_mock_plan()->get( 'id' ),
		) );

		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_gateway' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_GATEWAY_REQUIRED, $res );

	}

	/**
	 * Test validate_gateway() when no gateway is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_gateway_invalid() {

		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id'         => $this->get_mock_plan()->get( 'id' ),
			'llms_payment_gateway' => 'fake',
		) );

		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_gateway' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'invalid-gateway', $res );

	}

	/**
	 * Test validate_gateway() when no gateway is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_gateway_manual_for_free() {

		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id' => $this->get_mock_plan( 0 )->get( 'id' ),
		) );

		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_gateway' ) );
		$this->assertEquals( llms()->payment_gateways()->get_gateway_by_id( 'manual' ), $gen->get_gateway() );

	}

	/**
	 * Test validate_gateway() when no gateway is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_gateway_success() {

		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id'         => $this->get_mock_plan()->get( 'id' ),
			'llms_payment_gateway' => 'manual',
		) );

		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_gateway' ) );
		$this->assertEquals( llms()->payment_gateways()->get_gateway_by_id( 'manual' ), $gen->get_gateway() );

	}

	/**
	 * Test validate_order() when the order can't be found.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_order_not_found() {

		$gen = new LLMS_Order_Generator( array() );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_order' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_ORDER_NOT_FOUND, $res );


	}

	/**
	 * Test validate_order() when the order can't be confirmed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_order_not_confirmable() {

		$order = new LLMS_Order( 'new' );
		$order->set( 'status', 'llms-completed' );

		$gen = new LLMS_Order_Generator( array(
			'llms_order_key' => $order->get( 'order_key' ),
		) );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_order' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_ORDER_NOT_CONFIRMABLE, $res );

	}

	/**
	 * Test validate_order() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_order_success() {


		$order = new LLMS_Order( 'new' );

		$gen = new LLMS_Order_Generator( array(
			'llms_order_key' => $order->get( 'order_key' ),
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_order' ) );
		$this->assertEquals( $order, $gen->get_order() );

	}

	/**
	 * Test validate_plan() when no plan is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_plan_missing() {

		$gen = new LLMS_Order_Generator( array() );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_PLAN_REQUIRED, $res );

	}

	/**
	 * Test validate_plan() when an invalid or non-existent plan is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_plan_not_found() {

		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id' => $this->factory->post->create(),
		) );

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_PLAN_NOT_FOUND, $res );

	}

	/**
	 * Test validate_plan() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_plan_success() {

		$plan = $this->get_mock_plan();
		$gen = new LLMS_Order_Generator( array(
			'llms_plan_id' => $plan->get( 'id' ),
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' ) );
		$this->assertEquals( $plan, $gen->get_plan() );

	}

	/**
	 * Test validate_terms() when terms aren't required.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_terms_not_required() {

		update_option( 'lifterlms_registration_require_agree_to_terms', 'no' );

		$gen = new LLMS_Order_Generator( array() );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_terms' ) );

		delete_option( 'lifterlms_registration_require_agree_to_terms' );

	}

	/**
	 * Test validate_terms() when terms aren't required.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_terms_required() {

		update_option( 'lifterlms_registration_require_agree_to_terms', 'yes' );
		update_option( 'lifterlms_terms_page_id', $this->factory->post->create( array( 'post_type' => 'page' ) ) );

		$gen = new LLMS_Order_Generator( array() );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_terms' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_SITE_TERMS, $res );

		$gen = new LLMS_Order_Generator( array(
			'llms_agree_to_terms' => 'yes',
		) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_terms' ) );

		delete_option( 'lifterlms_registration_require_agree_to_terms' );
		delete_option( 'lifterlms_terms_page_id' );

	}

	/**
	 * Test validate_user() with validation errors.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_user_error() {

		LLMS_Forms::instance()->install( true );

		$gen = new LLMS_Order_Generator( array() );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_user' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $res );

	}

	/**
	 * Test validate_user() extra enrollment validations.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_user_enrollment() {

		$plan = $this->get_mock_plan();
		$data = $this->get_mock_user_data();

		$data['llms_plan_id'] = $plan->get( 'id' );

		$uid = $this->factory->user->create( array(
			'user_email' => $data['email_address'],
		) );

		LLMS_Forms::instance()->install( true );

		$gen = new LLMS_Order_Generator( $data );
		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );

		// Existing user not enrolled.
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_user' ) );

		// User is enrolled.
		llms_enroll_student( $uid, $plan->get( 'product_id' ) );
		$res = LLMS_Unit_Test_Util::call_method( $gen, 'validate_user' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( $gen::E_USER_ENROLLED, $res );

	}

	/**
	 * Test validate_user() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_user_success() {

		$data = $this->get_mock_user_data();
		LLMS_Forms::instance()->install( true );

		$gen = new LLMS_Order_Generator( $data );
		LLMS_Unit_Test_Util::call_method( $gen, 'validate_plan' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'validate_user' ) );

	}

}
