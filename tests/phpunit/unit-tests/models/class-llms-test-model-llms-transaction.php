<?php
/**
 * Tests for LifterLMS Transaction Model
 *
 * @group LLMS_Transaction
 * @group LLMS_Post_Model
 *
 * @since 5.9.0
 */
class LLMS_Test_LLMS_Transaction extends LLMS_PostModelUnitTestCase {

	/**
	 * Class name for the model being tested by the class.
	 *
	 * @var  string
	 */
	protected $class_name = 'LLMS_Transaction';

	/**
	 * DB post type of the model being tested.
	 *
	 * @var  string
	 */
	protected $post_type = 'llms_transaction';

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters
	 *
	 * @since 5.9.0
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'api_mode'                   => 'live',
			'amount'                     => 25.99,
			'currency'                   => 'USD',
			'gateway_completed_date'     => '2021-02-24 23:23:59',
			'gateway_customer_id'        => 'customer_id',
			'gateway_fee_amount'         => 1.99,
			'gateway_source_id'          => 'source_id',
			'gateway_source_description' => 'Vist **** 2342',
			'gateway_transaction_id'     => 'transaction_id',
			'order_id'                   => 123,
			'payment_type'               => 'recurring',
			'payment_gateway'            => 'payway',
			'refund_amount'              => 2.99,
			'refund_data'                => array( 'stuff' => 123 ),
		);
	}

	/**
	 * Test creation of the model.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_create_model() {

		llms_tests_mock_current_time( '2021-03-05 01:05:23' );

		$this->create( 123 );

		$id = $this->obj->get( 'id' );

		$test = llms_get_post( $id );

		$this->assertEquals( $id, $test->get( 'id' ) );
		$this->assertEquals( 'llms_transaction', $test->get( 'type' ) );
		$this->assertEquals( 'Transaction for Order #123 &ndash; Mar 05, 2021 @ 01:05 AM', $test->get( 'title' ) );

		$this->assertEquals( '2021-03-05 01:05:23', $test->get( 'date' ) );
		$this->assertEquals( 'llms-txn-pending', $test->get( 'status' ) );

		$this->assertTrue( post_password_required( $id ) );

	}

	/**
	 * Test generate_refund_id() when processing manually.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_refund_id_manual() {

		$this->create( 123 );

		$expected_id = 'ABCDE12345';
		$id_handler = function( $id ) use ( $expected_id ) {
			return $expected_id;
		};
		add_filter( 'llms_manual_refund_id', $id_handler );

		$this->assertEquals( 
			$expected_id,
			LLMS_Unit_Test_Util::call_method( $this->obj, 'generate_refund_id', array( 'manual', 1.00 ) )
		);

		remove_filter( 'llms_manual_refund_id', $id_handler );

	}

	/**
	 * Test generate_refund_id() when processing via a gateway.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_refund_gateway() {

		$this->create( 123 );
		$res = LLMS_Unit_Test_Util::call_method( $this->obj, 'generate_refund_id', array( 'gateway', 1.00 ) );
		$this->assertWPErrorCodeEquals( 'llms-txn-refund-gateway-invalid', $res );

	}

	/**
	 * Test generate_refund_id() when using a custom processing method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_refund_custom() {

		$this->create( 123 );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'generate_refund_id', array( 'custom', 1.00 ) ) );

	}

	/**
	 * Test get_refund_method_title() with manual processing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_refund_method_title_manual() {

		$this->create( 123 );
		$this->assertEquals( 
			'manual refund',
			LLMS_Unit_Test_Util::call_method( $this->obj, 'get_refund_method_title', array( 'manual' ) )
		);

	}

	/**
	 * Test get_refund_method_title() with gateway processing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_refund_method_title_gateway() {

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-process-refund-title';
			public $admin_title = 'Gateway Title';
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
		};
		$this->load_payment_gateway( $gateway );

		$this->create( 123 );
		$this->obj->set( 'payment_gateway', 'fake-process-refund-title' );
		$this->assertEquals( 
			'Gateway Title',
			LLMS_Unit_Test_Util::call_method( $this->obj, 'get_refund_method_title', array( 'gateway' ) )
		);

		$this->unload_payment_gateway( 'fake-process-refund-title' );

	}

	/**
	 * Test get_refund_method_title() with custom processing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_refund_method_title_custom() {

		$this->create( 123 );
		$this->assertEquals( 
			'custom',
			LLMS_Unit_Test_Util::call_method( $this->obj, 'get_refund_method_title', array( 'custom' ) )
		);	

	}

	/**
	 * Test get_refunds() and get_refund().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_refund_and_get_refunds() {

		$this->create( 123 );

		$refund_data = array(
			'123' => array(
				'id'     => '123',
				'amount' => 12.99,
				'method' => 'manual',
				'date'   => llms_current_time( 'mysql' ),
			),
		);

		$this->obj->set( 'refund_data', $refund_data );

		// Get all.
		$this->assertEquals( $refund_data, $this->obj->get_refunds() );

		// Get one.
		$this->assertEquals( $refund_data['123'], $this->obj->get_refund( '123' ) );

		// Get one (invalid).
		$this->assertFalse( $this->obj->get_refund( '456' ) );

	}

	/**
	 * Skip unneeded test.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_edit_date() {
		$this->assertTrue( true );
	}

	/**
	 * Test can_be_refunded().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_can_be_refunded() {
		
		$this->create();
		$this->obj->set( array(
			'order_id' => $this->factory->order->create(),
			'amount'   => 25.00,
		) );

		$tests = array(
			'llms-txn-failed'    => false,
			'llms-txn-pending'   => false,
			'llms-txn-refunded'  => true,
			'llms-txn-succeeded' => true,
		);

		foreach ( $tests as $status => $expected ) {
			$this->obj->set( 'status', $status );
			$this->assertEquals( $expected, $this->obj->can_be_refunded(), $status );
		}

		// No money left to refund.
		$this->obj->set( 'refund_amount', 25.00 );
		$this->assertFalse( $this->obj->can_be_refunded() );

	}

	/**
	 * Test process_refund() when the transaction isn't eligible for a refund.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_refund_no_eligible() {

		$this->create();
		$this->obj->set( array(
			'order_id' => $this->factory->order->create(),
		) );

		$res = $this->obj->process_refund( 25.00 );
		$this->assertWPErrorCodeEquals( 'llms-txn-refund-not-eligible', $res );

	}

	/**
	 * Test process_refund() when the requested refund amount exceeds the remaining available amount on the transaction.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_refund_amount_too_high() {

		$this->create();
		$this->obj->set( array(
			'order_id' => $this->factory->order->create(),
			'amount'   => 25.00,
		) );
		$this->obj->set( 'status', 'llms-txn-succeeded' );


		$res = $this->obj->process_refund( 35.00 );
		$this->assertWPErrorCodeEquals( 'llms-txn-refund-amount-too-high', $res );

	}

	/**
	 * Test process_refund() unknown error.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_refund_err_unknown() {

		$order = $this->factory->order->create_and_get();
		$this->create();
		$this->obj->set( array(
			'order_id' => $order->get( 'id' ),
			'amount'   => 25.00,
		) );
		$this->obj->set( 'status', 'llms-txn-succeeded' );	

		$res = $this->obj->process_refund( 10.00, 'A note', 'fake-method' );
		$this->assertWPErrorCodeEquals( 'llms-txn-refund-unknown-error', $res );

	}

	/**
	 * Test process_refund() through a manual gateway success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_refund_manual() {

		$now = time();
		llms_tests_mock_current_time( $now );

		// Remove filter so we can test order notes.
		remove_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );

		$expected_id = (string) $now;
		$id_handler = function( $id ) use ( $expected_id ) {
			return $expected_id;
		};
		add_filter( 'llms_manual_refund_id', $id_handler );

		$order = $this->factory->order->create_and_get();
		$this->create();
		$this->obj->set( array(
			'order_id' => $order->get( 'id' ),
			'amount'   => 25.00,
		) );
		$this->obj->set( 'status', 'llms-txn-succeeded' );	

		$txn_id = $this->obj->get( 'id' );

		// Expected ID is returned.
		$res = $this->obj->process_refund( 10.00, 'A note' );
		$this->assertSame( $res, $expected_id );

		// Note is recorded.
		$notes = $order->get_notes();
		$found_note = false;
		foreach ( $notes as $note ) {
			$expected_note = "Refunded &#36;10.00 for transaction #{$txn_id} via manual refund [Refund ID: {$expected_id}]\r\nRefund Notes: \r\nA note";
			if ( $note->comment_content === $expected_note ) {
				$found_note = true;
				break;
			}
		}
		$this->assertTrue( $found_note );

		// Transaction status updated.
		$this->assertEquals( 'llms-txn-refunded', get_post_status( $txn_id ) );

		// Refund amount updated.
		$this->assertEquals( 10.00, $this->obj->get( 'refund_amount' ) );

		// Refund data saved.
		$expected_data = array(
			$expected_id => array(
				'amount' => 10.00,
				'date'   => date( 'Y-m-d H:i:s', $now ),
				'id'     => $expected_id,
				'method' => 'manual',
			),
		);
		$this->assertEquals( $expected_data, $this->obj->get( 'refund_data' ) );

		add_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );
		remove_filter( 'llms_manual_refund_id', $id_handler );

	}

	/**
	 * Test process_refund() through an invalid gateway.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_refund_gateway_invalid() {

		$order = $this->factory->order->create_and_get();
		$this->create();
		$this->obj->set( array(
			'order_id'        => $order->get( 'id' ),
			'amount'          => 25.00,
			'payment_gateway' => 'fake-process-refund',
		) );
		$this->obj->set( 'status', 'llms-txn-succeeded' );		

		$res = $this->obj->process_refund( 10.00, '', 'gateway' );
		$this->assertWPErrorCodeEquals( 'llms-txn-refund-gateway-invalid', $res );

	}

	/**
	 * Test process_refund() through a gateway that doesn't support refunds.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_refund_gateway_doesnt_support_refunds() {

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-process-refund';
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
		};
		$this->load_payment_gateway( $gateway );

		$order = $this->factory->order->create_and_get();
		$this->create();
		$this->obj->set( array(
			'order_id'        => $order->get( 'id' ),
			'amount'          => 25.00,
			'payment_gateway' => 'fake-process-refund',
		) );
		$this->obj->set( 'status', 'llms-txn-succeeded' );		

		$res = $this->obj->process_refund( 10.00, '', 'gateway' );
		$this->assertWPErrorCodeEquals( 'llms-txn-refund-gateway-support', $res );

		$this->unload_payment_gateway( 'fake-process-refund' );

	}

	/**
	 * Test process_refund() through a gateway success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_refund_gateway_success() {

		// Setup a fake payment gateway.
		$gateway = new class() extends LLMS_Payment_Gateway {
			public $id = 'fake-process-refund';
			public $supports = array(
				'refunds' => true,
			);
			public function handle_pending_order( $order, $plan, $person, $coupon = false ) {}
			public function process_refund( $transaction, $amount = 0, $note = '' ) {
				return 'ABCDE12345';
			}
		};
		$this->load_payment_gateway( $gateway );

		$order = $this->factory->order->create_and_get();
		$this->create();
		$this->obj->set( array(
			'order_id'        => $order->get( 'id' ),
			'amount'          => 25.00,
			'payment_gateway' => 'fake-process-refund',
		) );
		$this->obj->set( 'status', 'llms-txn-succeeded' );		

		$res = $this->obj->process_refund( 10.00, '', 'gateway' );
		$this->assertEquals( 'ABCDE12345', $res );

		$this->unload_payment_gateway( 'fake-process-refund' );

	}

}
