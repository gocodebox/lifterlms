<?php
/**
 * Tests for LifterLMS Transaction Model
 *
 * @group LLMS_Transaction
 * @group LLMS_Post_Model
 *
 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_model() {

		llms_mock_current_time( '2021-03-05 01:05:23' );

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
	 * Skip unneeded test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_edit_date() {
		$this->assertTrue( true );
	}

}
