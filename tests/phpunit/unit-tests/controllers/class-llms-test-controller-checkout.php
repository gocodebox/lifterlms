<?php
/**
 * Tests for the LLMS_Controller_Checkout class.
 *
 * @package LifterLMS/Tests
 *
 * @group orders
 * @group controllers
 * @group checkout
 * @group controller_checkout
 *
 * @since [version]
 */
class LLMS_Test_Controller_Checkout extends LLMS_UnitTestCase {

	public function set_up() {

		parent::set_up();
		$this->main = LLMS_Controller_Checkout::instance();

	}

	/**
	 * Test constructor().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor() {

		$actions = array(
			'create_pending_order_ajax'  => 5,
			'create_pending_order'       => 10,
			'confirm_pending_order_ajax' => 5,
			'confirm_pending_order'      => 10,
			'switch_payment_source_ajax' => 5,
			'switch_payment_source'      => 10,
		);

		foreach ( $actions as $hook => $priority ) {
			remove_action( 'init', array( $this->main, $hook ), $priority );
		}

		LLMS_Unit_Test_Util::call_method( $this->main, '__construct' );

		foreach ( $actions as $hook => $priority ) {
			$this->assertEquals( $priority, has_action( 'init', array( $this->main, $hook ) ) );
		}		

	}

	/**
	 * Test confirm_pending_order() when the form hasn't been submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_not_submitted() {
		$this->assertNull( $this->main->confirm_pending_order() );
	}

	/**
	 * Test confirm_pending_order() when a nonce error is encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_nonce_error() {
		$this->mockPostRequest( array(
			'_wpnonce' => 'fake',
		) );
		$this->assertNull( $this->main->confirm_pending_order() );
	}

	/**
	 * Test confirm_pending_order() when an missing action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_missing_action() {

		$this->mockPostRequest( array(
			'_wpnonce'       => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'llms_order_key' => 'NOT-A-REAL-ORDER-KEY',
		) );
		$this->assertFalse( $this->main->confirm_pending_order() );
		
	}

	/**
	 * Test confirm_pending_order() when an invalid order key is submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_invalid_action() {

		$this->mockPostRequest( array(
			'_wpnonce'       => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'action'         => 'FAKE',
		) );
		$this->assertFalse( $this->main->confirm_pending_order() );

	}

	/**
	 * Test confirm_pending_order() when an missing order key is submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_missing_order_key() {

		$this->mockPostRequest( array(
			'_wpnonce'       => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'action'         => $this->main::ACTION_CONFIRM_PENDING_ORDER,
		) );
		$this->assertNull( $this->main->confirm_pending_order() );
		$this->assertHasNotice( 'Could not locate an order to confirm.', 'error' );

	}

	/**
	 * Test confirm_pending_order() when an invalid order key is submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_invalid_order_key() {

		$this->mockPostRequest( array(
			'_wpnonce'       => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'action'         => $this->main::ACTION_CONFIRM_PENDING_ORDER,
			'llms_order_key' => 'NOT-A-REAL-ORDER-KEY',
		) );
		$this->assertNull( $this->main->confirm_pending_order() );
		$this->assertHasNotice( 'Could not locate an order to confirm.', 'error' );

	}

	/**
	 * Test confirm_pending_order() when an the order cannot be confirmed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_order_cannot_be_confirmed() {

		$order = new LLMS_Order( 'new' );
		$order->set_status( 'llms-failed' );

		$this->mockPostRequest( array(
			'_wpnonce'       => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'action'         => $this->main::ACTION_CONFIRM_PENDING_ORDER,
			'llms_order_key' => $order->get( 'order_key' ),
		) );
		$this->assertNull( $this->main->confirm_pending_order() );
		$this->assertHasNotice( 'Only pending orders can be confirmed.', 'error' );

	}

	/**
	 * Test confirm_pending_order() is passed to the specified gateway and runs the gateway's method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_success() {

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-confirm-pending-success';
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
			public function confirm_pending_order( $order ) {
				do_action( 'llms_gateway_fake_confirm_pending_success' );
			}
		};
		$this->load_payment_gateway( $gateway );

		// Setup the order.
		$order = new LLMS_Order( 'new' );
		$order->set( 'payment_gateway', 'fake-confirm-pending-success' );

		// Mock the request.
		$this->mockPostRequest( array(
			'_wpnonce'       => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'action'         => $this->main::ACTION_CONFIRM_PENDING_ORDER,
			'llms_order_key' => $order->get( 'order_key' ),
		) );
		$this->assertNull( $this->main->confirm_pending_order() );

		// The fake gateway's confirm method should have run.
		$this->assertSame( 1, did_action( 'llms_gateway_fake_confirm_pending_success' ) );

		$this->unload_payment_gateway( 'fake-confirm-pending-success' );

	}

	/**
	 * Test confirm_pending_order_ajax() when the form was not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_ajax_not_submitted() {
		$this->assertNull( $this->main->confirm_pending_order_ajax() );
	}

	/**
	 * Test confirm_pending_order_ajax() when the nonce is invalid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_ajax_invalid_nonce() {
		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => 'fake',
		) );
		$this->assertNull( $this->main->confirm_pending_order_ajax() );
	}

	/**
	 * Test confirm_pending_order_ajax() when the action is missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_ajax_missing_action() {
		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
		) );
		$this->assertFalse( $this->main->confirm_pending_order_ajax() );
	}

	/**
	 * Test confirm_pending_order_ajax() when the action is invalid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_ajax_invalid_action() {
		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'action'                 => 'invalid',
		) );
		$this->assertFalse( $this->main->confirm_pending_order_ajax() );
	}

	/**
	 * Test confirm_pending_order_ajax() successfully passes to the order generator.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_confirm_pending_order_ajax_success() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CONFIRM_PENDING_ORDER ),
			'action'                 => $this->main::ACTION_CONFIRM_PENDING_ORDER,
		) );

		try {
			ob_start();
			$this->main->confirm_pending_order_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'llms-order-gen-order-not-found', $res['errors'] );
		
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test create_pending_order() when the form is not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_not_submitted() {
		$this->assertNull( $this->main->create_pending_order() );
	}

	/**
	 * Test create_pending_order() when a nonce error is encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_nonce_error() {
		$this->mockPostRequest( array(
			'_llms_checkout_nonce' => 'fake',
		) );
		$this->assertNull( $this->main->create_pending_order() );
	}

	/**
	 * Test create_pending_order() when the action is not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_missing_action() {
		$this->mockPostRequest( array(
			'_llms_checkout_nonce' => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
		) );
		$this->assertFalse( $this->main->create_pending_order() );
	}

	/**
	 * Test create_pending_order() when the submitted action is invalid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_invalid_action() {
		$this->mockPostRequest( array(
			'_llms_checkout_nonce' => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
			'action'               => 'fake',
		) );
		$this->assertFalse( $this->main->create_pending_order() );
	}

	/**
	 * Test create_pending_order() when custom validation errors are encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_custom_validation() {
		$this->mockPostRequest( array(
			'_llms_checkout_nonce' => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
			'action'               => $this->main::ACTION_CREATE_PENDING_ORDER,
		) );
		add_filter( 'llms_before_checkout_validation', '__return_true' );
		$this->assertFalse( $this->main->create_pending_order() );
		remove_filter( 'llms_before_checkout_validation', '__return_true' );
	}

	/**
	 * Test create_pending_order() when an error is encountered running `llms_setup_pending_order()`.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_setup_error() {

		LLMS_Forms::instance()->install( true );

		$this->mockPostRequest( array(
			'_llms_checkout_nonce' => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
			'action'               => $this->main::ACTION_CREATE_PENDING_ORDER,
		) );

		$res = $this->main->create_pending_order();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'missing-plan-id', $res );

		$this->assertHasNotice( 'Missing an Access Plan ID.', 'error' );

	}

	/**
	 * Test create_pending_order() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_success() {

		LLMS_Forms::instance()->install( true );

		$plan = $this->get_mock_plan();

		$post_data = array(
			'_llms_checkout_nonce'   => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
			'action'                 => $this->main::ACTION_CREATE_PENDING_ORDER,

			'llms_plan_id'           => $plan->get( 'id' ),

			'llms_payment_gateway'   => 'manual',

			'user_login'             => 'creatependingorder',
			'email_address'          => 'creatependingorder@example.tld',
			'email_address_confirm'  => 'creatependingorder@example.tld',
			'password'               => '12345678',
			'password_confirm'       => '12345678',
			'first_name'             => 'Test',
			'last_name'              => 'Person',
			'llms_billing_address_1' => '123',
			'llms_billing_address_2' => '123',
			'llms_billing_city'      => 'City',
			'llms_billing_state'     => 'CA',
			'llms_billing_zip'       => '91231',
			'llms_billing_country'   => 'US',
			'llms_phone'             => '1234567890',
		);

		$this->mockPostRequest( $post_data );

		// Assert some things using an action hook from the manual payment gateway.
		$handler = function( $order ) use ( $plan, $post_data ) {

			$this->assertEquals( $plan->get( 'id' ), $order->get( 'plan_id' ) );

			$student = llms_get_student( $order->get( 'user_id' ) );

			$this->assertEquals( $order->get( 'user_id' ), get_user_by( 'email', $post_data['email_address'] )->ID );
			$this->assertEquals( $order->get( 'billing_email' ), $post_data['email_address'] );


		};
		add_action( 'llms_manual_payment_due', $handler );

		try {
			$this->main->create_pending_order();
		} catch ( LLMS_Unit_Test_Exception_Exit $exception ) {
			remove_action( 'llms_manual_payment_due', $handler );
		}

	}

	/**
	 * Test create_pending_order_ajax() when the form was not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_ajax_not_submitted() {
		$this->assertNull( $this->main->create_pending_order_ajax() );
	}

	/**
	 * Test create_pending_order_ajax() when the nonce is invalid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_ajax_invalid_nonce() {
		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => 'fake',
		) );
		$this->assertNull( $this->main->create_pending_order_ajax() );
	}

	/**
	 * Test create_pending_order_ajax() when the action is missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_ajax_missing_action() {
		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
		) );
		$this->assertFalse( $this->main->create_pending_order_ajax() );
	}

	/**
	 * Test create_pending_order_ajax() when the action is invalid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_ajax_invalid_action() {
		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
			'action'                 => 'invalid',
		) );
		$this->assertFalse( $this->main->create_pending_order_ajax() );
	}

	/**
	 * Test create_pending_order_ajax() when an order validation error is encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_ajax_order_validation_error() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
			'action'                 => $this->main::ACTION_CREATE_PENDING_ORDER,
		) );

		try {
			ob_start();
			$this->main->create_pending_order_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'llms-order-gen-plan-required', $res['errors'] );
		
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test create_pending_order_ajax() when a gateway error is encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_ajax_order_gateway_error() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-create-pending-order-ajax-gateway-err';
			public $supports = array(
				'recurring_payments' => true,
			);
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {
				return new WP_Error( 'gateway-err' );
			}
		};
		$this->load_payment_gateway( $gateway );

		$this->mockPostRequest( 
			array_merge(
				$this->get_mock_checkout_data_array(),
				array(
					$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
					'action'                 => $this->main::ACTION_CREATE_PENDING_ORDER,
					'llms_payment_gateway'   => 'fake-create-pending-order-ajax-gateway-err',
				),
			)
		);

		try {
			ob_start();
			$this->main->create_pending_order_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'gateway-err', $res['errors'] );
		
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$this->unload_payment_gateway( 'fake-create-pending-order-ajax-gateway-err' );

	}

	/**
	 * Test create_pending_order_ajax() when an order validation error is encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_pending_order_ajax_order_success() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-create-pending-order-ajax-success';
			public $supports = array(
				'recurring_payments' => true,
			);
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {
				return array( 'success' => 'yes' );
			}
		};
		$this->load_payment_gateway( $gateway );

		$this->mockPostRequest( 
			array_merge(
				$this->get_mock_checkout_data_array(),
				array(
					$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_CREATE_PENDING_ORDER ),
					'action'                 => $this->main::ACTION_CREATE_PENDING_ORDER,
					'llms_payment_gateway'   => 'fake-create-pending-order-ajax-success',
				),
			)
		);

		try {
			ob_start();
			$this->main->create_pending_order_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertEquals( 'yes', $res['success'] );
		$this->assertArrayHasKey( 'order_key', $res ); 
		
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$this->unload_payment_gateway( 'fake-create-pending-order-ajax-success' );

	}

	/**
	 * Test get_url().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_url() {
		$nonce = wp_create_nonce( 'action' );
		$this->assertEquals( "http://example.org?llms-checkout={$nonce}", $this->main->get_url( 'action' ) );
	}

	/**
	 * Test switch_payment_source() when the form isn't submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_not_submitted() {
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertNoticeCountEquals( 0, 'error' );
	}

	/**
	 * Test switch_payment_source() with an invalid nonce.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_invalid_nonce() {
		$this->mockPostRequest( array(
			'_switch_source_nonce' => 'fake',
		) );
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertNoticeCountEquals( 0, 'error' );
	}

	/**
	 * Test switch_payment_source() when the order_id is missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_missing_order_id() {
		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
		) );
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertHasNotice( 'Missing order information.', 'error' );
	}

	/**
	 * Test switch_payment_source() when an invalid order ID is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_invalid_order_id() {
		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => 'NaN',
		) );
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertHasNotice( 'Missing order information.', 'error' );
	}

	/**
	 * Test switch_payment_source() when the order ID isn't an order.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_not_an_order() {

		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $this->factory->post->create(),
		) );
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertHasNotice( 'Invalid order.', 'error' );	

	}

	/**
	 * Test switch_payment_source() when there's no logged in user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_mismatched_order() {

		$order = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_order' ) ) );
		$order->set( 'user_id', 123 );

		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $order->get( 'id' ),
		) );
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertHasNotice( 'Invalid order.', 'error' );	

	}

	/**
	 * Test switch_payment_source() when the gateway is missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_missing_gateway() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$order = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_order' ) ) );
		$order->set( 'user_id', $user_id );

		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $order->get( 'id' ),
		) );
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertHasNotice( 'Missing gateway information.', 'error' );	

	}

	/**
	 * Test switch_payment_source() when there's a gateway validation error.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_gateway_validation_error() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$order = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_order' ) ) );
		$order->set( 'user_id', $user_id );

		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $order->get( 'id' ),
			'llms_payment_gateway' => 'FAKE',
		) );
		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertHasNotice( 'The selected payment gateway is not valid.', 'error' );	

	}

	/**
	 * Test switch_payment_source() when the switch action is invalid, fake, or missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_invalid_switch_action() {

		$gateway = llms()->payment_gateways()->get_gateway_by_id( 'manual' );
		$gateway->set_option( 'enabled', 'yes' );

		$actions = did_action( 'llms_order_payment_source_switched' );

		$plan = llms_insert_access_plan( array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price'      => 25.00,
			'frequency'  => 1,
		) );
		$order = $this->factory->order->create_and_get( array( 'plan_id' => $plan->get( 'id' ) ) );
		$order->set( 'payment_gateway', 'not-manual' );
		$order->set_status( 'pending-cancel' );
		wp_set_current_user( $order->get( 'user_id' ) );

		foreach ( array( null, false, 'pay', 'fake ' ) as $action ) {

			llms_clear_notices();
			$this->mockPostRequest( array(
				'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
				'order_id'             => $order->get( 'id' ),
				'llms_payment_gateway' => 'manual',
				'llms_switch_action'   => $action,
			) );

			$this->assertNull( $this->main->switch_payment_source() );
			$this->assertHasNotice( 'Invalid action.', 'error' );

		}

	}

	/**
	 * Test switch_payment_source() when the gateway handler returns an error.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_gateway_error() {

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'mock-switch-source-err';
			public $supports = array(
				'recurring_payments' => true,
			);
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
			public function handle_payment_source_switch( $order, $data = array() ) {
				return llms_add_notice( 'Mock gateway error.', 'error' );
			}
		};
		$this->load_payment_gateway( $gateway );

		$plan = llms_insert_access_plan( array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price'      => 25.00,
			'frequency'  => 1,
		) );
		$order = $this->factory->order->create_and_get( array( 'plan_id' => $plan->get( 'id' ) ) );
		$order->set( 'payment_gateway', 'manual' );
		$order->set_status( 'on-hold' );
		wp_set_current_user( $order->get( 'user_id' ) );

		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $order->get( 'id' ),
			'llms_payment_gateway' => 'mock-switch-source-err',
			'llms_switch_action'   => 'pay'
		) );

		$this->assertNull( $this->main->switch_payment_source() );
		$this->assertHasNotice( 'Mock gateway error.', 'error' );

		// Re-initialized the order object.
		$order = llms_get_post( $order->get( 'id' ) );

		$this->assertSame( 0, did_action( 'llms_order_payment_source_switched' ) );
		$this->assertEquals( 'manual', $order->get( 'payment_gateway' ) );
		$this->assertEquals( 'llms-on-hold', $order->get( 'status' ) );

		$this->unload_payment_gateway( 'mock-switch-source-err' );


	}

	/**
	 * Test switch_payment_source() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_success() {

		$gateway = llms()->payment_gateways()->get_gateway_by_id( 'manual' );
		$gateway->set_option( 'enabled', 'yes' );

		$actions = did_action( 'llms_order_payment_source_switched' );

		$plan = llms_insert_access_plan( array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price'      => 25.00,
			'frequency'  => 1,
		) );
		$order = $this->factory->order->create_and_get( array( 'plan_id' => $plan->get( 'id' ) ) );
		$order->set( 'payment_gateway', 'not-manual' );
		$order->set_status( 'pending-cancel' );
		wp_set_current_user( $order->get( 'user_id' ) );

		$this->mockPostRequest( array(
			'_switch_source_nonce' => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $order->get( 'id' ),
			'llms_payment_gateway' => 'manual',
			'llms_switch_action'   => 'switch',
		) );

		$this->assertNull( $this->main->switch_payment_source() );

		// Re-initialized the order object.
		$order = llms_get_post( $order->get( 'id' ) );

		$this->assertEquals( ++$actions, did_action( 'llms_order_payment_source_switched' ) );
		$this->assertEquals( 'manual', $order->get( 'payment_gateway' ) );
		$this->assertEquals( 'llms-active', $order->get( 'status' ) );

		$gateway->set_option( 'enabled', 'no' );

	}




















	/**
	 * Test switch_payment_source_ajax() when the form isn't submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_not_submitted() {
		$this->assertNull( $this->main->switch_payment_source_ajax() );
		$this->assertNoticeCountEquals( 0, 'error' );
	}

	/**
	 * Test switch_payment_source_ajax() with an invalid nonce.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_invalid_nonce() {
		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => 'fake',
		) );
		$this->assertNull( $this->main->switch_payment_source_ajax() );
		$this->assertNoticeCountEquals( 0, 'error' );
	}

	/**
	 * Test switch_payment_source_ajax() when the order_id is missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_missing_order_id() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'switch-source-order-missing', $res['errors'] );

		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() when an invalid order ID is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_invalid_order_id() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'               => 'NaN',
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'switch-source-order-missing', $res['errors'] );
		
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() when the order ID isn't an order.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_not_an_order() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'               => $this->factory->post->create(),
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'switch-source-order-invalid', $res['errors'] );
		
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() when there's no logged in user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_mismatched_order() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$order = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_order' ) ) );
		$order->set( 'user_id', 123 );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'               => $order->get( 'id' ),
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'switch-source-order-invalid', $res['errors'] );
		
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() when the gateway is missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_missing_gateway() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$order = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_order' ) ) );
		$order->set( 'user_id', $user_id );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'               => $order->get( 'id' ),
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'switch-source-gateway-missing', $res['errors'] );

		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() when there's a gateway validation error.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_gateway_validation_error() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$order = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_order' ) ) );
		$order->set( 'user_id', $user_id );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'               => $order->get( 'id' ),
			'llms_payment_gateway'   => 'FAKE',
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'gateway-invalid', $res['errors'] );

		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() when the switch action is invalid, fake, or missing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_invalid_switch_action() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		$gateway = llms()->payment_gateways()->get_gateway_by_id( 'manual' );
		$gateway->set_option( 'enabled', 'yes' );

		$actions = did_action( 'llms_order_payment_source_switched' );

		$plan = llms_insert_access_plan( array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price'      => 25.00,
			'frequency'  => 1,
		) );
		$order = $this->factory->order->create_and_get( array( 'plan_id' => $plan->get( 'id' ) ) );
		$order->set( 'payment_gateway', 'not-manual' );
		$order->set_status( 'pending-cancel' );
		wp_set_current_user( $order->get( 'user_id' ) );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'               => $order->get( 'id' ),
			'llms_payment_gateway'   => 'manual',
			'llms_switch_action'     => 'fake',
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'switch-source-action-invalid', $res['errors'] );


		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() when the gateway handler returns an error.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_gateway_error() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'mock-switch-source-err';
			public $supports = array(
				'recurring_payments' => true,
			);
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
			public function handle_payment_source_switch( $order, $data = array() ) {
				return new WP_Error( 'mock-gateway-error', 'ERR' );
			}
		};
		$this->load_payment_gateway( $gateway );

		$plan = llms_insert_access_plan( array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price'      => 25.00,
			'frequency'  => 1,
		) );
		$order = $this->factory->order->create_and_get( array( 'plan_id' => $plan->get( 'id' ) ) );
		$order->set( 'payment_gateway', 'manual' );
		$order->set_status( 'on-hold' );
		wp_set_current_user( $order->get( 'user_id' ) );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $order->get( 'id' ),
			'llms_payment_gateway' => 'mock-switch-source-err',
			'llms_switch_action'   => 'pay'
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'mock-gateway-error', $res['errors'] );

		// Re-initialized the order object.
		$order = llms_get_post( $order->get( 'id' ) );

		$this->assertSame( 0, did_action( 'llms_order_payment_source_switched' ) );
		$this->assertEquals( 'manual', $order->get( 'payment_gateway' ) );
		$this->assertEquals( 'llms-on-hold', $order->get( 'status' ) );

		$this->unload_payment_gateway( 'mock-switch-source-err' );

		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

	/**
	 * Test switch_payment_source_ajax() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_switch_payment_source_ajax_success() {

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'mock-switch-source-success';
			public $supports = array(
				'recurring_payments' => true,
			);
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
			public function handle_payment_source_switch( $order, $data = array() ) {
				return array( 'yes' => true );
			}
		};
		$this->load_payment_gateway( $gateway );

		$actions = did_action( 'llms_order_payment_source_switched' );

		$plan = llms_insert_access_plan( array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price'      => 25.00,
			'frequency'  => 1,
		) );
		$order = $this->factory->order->create_and_get( array( 'plan_id' => $plan->get( 'id' ) ) );
		$order->set( 'payment_gateway', 'not-manual' );
		$order->set_status( 'pending-cancel' );
		wp_set_current_user( $order->get( 'user_id' ) );

		$this->mockPostRequest( array(
			$this->main::AJAX_QS_VAR => wp_create_nonce( $this->main::ACTION_SWITCH_PAYMENT_SOURCE ),
			'order_id'             => $order->get( 'id' ),
			'llms_payment_gateway' => 'mock-switch-source-success',
			'llms_switch_action'   => 'switch',
		) );

		try {
			ob_start();
			$this->main->switch_payment_source_ajax();
		} catch ( WPDieException $e ) {}

		$res = json_decode( ob_get_clean(), true );
		$this->assertEquals( array( 'yes' => true ), $res );

		// Re-initialized the order object.
		$order = llms_get_post( $order->get( 'id' ) );

		// Ensure order note is recorded.
		remove_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );
		$has_note = false;
		foreach ( $order->get_notes() as $note ) {
			if ( 'Payment source updated by customer. Payment gateway changed from "not-manual" to "mock-switch-source-success".' === $note->comment_content ) {
				$has_note = true;
				break;
			}
		}
		add_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );
		$this->assertTrue( $has_note );

		$this->assertEquals( ++$actions, did_action( 'llms_order_payment_source_switched' ) );
		$this->assertEquals( 'mock-switch-source-success', $order->get( 'payment_gateway' ) );
		$this->assertEquals( 'llms-active', $order->get( 'status' ) );

		$gateway->set_option( 'enabled', 'no' );

		$this->unload_payment_gateway( 'mock-switch-source-success' );

		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler') );

	}

}
