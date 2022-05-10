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

}
