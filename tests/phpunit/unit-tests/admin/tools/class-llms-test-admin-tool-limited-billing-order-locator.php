<?php
/**
 * Tests for the LLMS_Admin_Tool_Limited_Billing_Order_Locator class.
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 * @group limited_billing
 *
 * @since 5.3.0
 * @version 5.4.0
 */
class LLMS_Test_Admin_Tool_Limited_Billing_Order_Locator extends LLMS_Admin_Tool_Test_Case {

	/**
	 * Name of the class being tested.
	 *
	 * @var sting
	 */
	const CLASS_NAME = 'LLMS_Admin_Tool_Limited_Billing_Order_Locator';

	/**
	 * Teardown the test case.
	 *
	 * @since 5.3.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		$this->clear_cache();
	}

	/**
	 * Clear cached tool data.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	private function clear_cache() {
		wp_cache_delete( 'limited-billing-order-locator', 'llms_tool_data' );
	}

	/**
	 * Create mock orders.
	 *
	 * @since 5.3.0
	 *
	 * @param int   $count Number of orders.
	 * @param array $meta  Order meta data.
	 * @param array $args  Additional args.
	 * @return int[]
	 */
	private function create_mock_orders( $count, $meta = array(), $args = array() ) {

		return $this->factory->post->create_many( $count, wp_parse_args( $args, array(
			'post_type'   => 'llms_order',
			'post_status' => 'llms-active',
			'meta_input'  => $meta,
		) ) );

	}

	/**
	 * Test generate_csv().
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_generate_csv() {

		$this->assertEquals( 0, count( LLMS_Unit_Test_Util::call_method( $this->main, 'generate_csv' ) ) );

		// Not qualifying.
		$this->create_mock_orders( 1 );
		$this->assertEquals( 0, count( LLMS_Unit_Test_Util::call_method( $this->main, 'generate_csv' ) ) );

		// Has length but wrong status.
		$this->create_mock_orders( 1, array( '_llms_billing_length' => 2, '_llms_date_billing_end' => '2021-05-05' ), array( 'post_status' => 'llms-cancelled' ) );
		$this->assertEquals( 0, count( LLMS_Unit_Test_Util::call_method( $this->main, 'generate_csv' ) ) );

		// Qualifying.
		$this->create_mock_orders( 2, array( '_llms_billing_length' => 2, '_llms_date_billing_end' => '2021-05-05', '_llms_plan_ended' => 'yes' ) );
		$this->assertEquals( 2, count( LLMS_Unit_Test_Util::call_method( $this->main, 'generate_csv' ) ) );

	}

	/**
	 * Test get_order_csv(): doesn't quality because the order hasn't ended and there's no refunds.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_order_csv_not_ended_no_refunds() {

		$order = llms_get_post( $this->create_mock_orders( 1, array( '_llms_billing_length' => 2 ) )[0] );
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'get_order_csv', array( $order ) ) );

	}

	/**
	 * Test get_order_csv(): Doesn't qualify because it has ended but has the expected number of payments.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_order_right_number_of_payments() {

		$order = llms_get_post( $this->create_mock_orders( 1, array( '_llms_billing_length' => 2 ) )[0] );
		$order->record_transaction();
		$order->record_transaction();
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'get_order_csv', array( $order ) ) );

	}

	/**
	 * Test get_order_csv(): Qualifies because it has ended and is missing a payment.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_order_missing_payments() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Post_Types::register_post_types();

		$order_id = $this->create_mock_orders( 1, array( '_llms_billing_length' => 2, '_llms_plan_ended' => 'yes' ) )[0];
		$order = llms_get_post( $order_id );
		$expect = array( $order_id, 2, 1, 1, 0, get_edit_post_link( $order_id, 'raw' ) );
		$order->record_transaction();
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_order_csv', array( $order ) ) );

	}

	/**
	 * Test get_order_csv(): Qualifies because it has a refund.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_order_has_refund() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Post_Types::register_post_types();

		$order_id = $this->create_mock_orders( 1, array( '_llms_billing_length' => 5 ) )[0];
		$order = llms_get_post( $order_id );
		$expect = array( $order_id, 5, 1, 0, 1, get_edit_post_link( $order_id, 'raw' ) );
		$order->record_transaction( array( 'status' => 'llms-txn-refunded' ) );
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_order_csv', array( $order ) ) );

	}

	/**
	 * Test get_csv() when there's nothing cached.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_csv_cache_miss() {

		$this->clear_cache();

		$this->create_mock_orders( 2, array( '_llms_billing_length' => 2, '_llms_plan_ended' => 'yes' ) );
		$expect = LLMS_Unit_Test_Util::call_method( $this->main, 'generate_csv' );
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_csv' ) );

		// Should be cached.
		$this->assertEquals( $expect, wp_cache_get( 'limited-billing-order-locator', 'llms_tool_data' ) );

	}

	/**
	 * Test get_csv() when there's cached results.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_csv_cache_hit() {

		wp_cache_set( 'limited-billing-order-locator', 'fake', 'llms_tool_data' );
		$this->assertEquals( 'fake', LLMS_Unit_Test_Util::call_method( $this->main, 'get_csv' ) );

	}

	/**
	 * Test handle().
	 *
	 * @since 5.3.0
	 * @since 5.4.0 Made sure to compare the lists of orders with the same ordering.
	 *
	 * @return void
	 */
	public function test_handle() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Post_Types::register_post_types();

		// Included.
		$orders = $this->create_mock_orders( 3, array( '_llms_billing_length' => 2, '_llms_date_billing_end' => '2021-05-05', '_llms_plan_ended' => 'yes' ) );

		// Not included bc it was created after the migration..
		$this->create_mock_orders( 1, array( '_llms_billing_length' => 2, '_llms_plan_ended' => 'yes' ) );

		try {

			LLMS_Unit_Test_Util::call_method( $this->main, 'handle' );

		} catch ( LLMS_Unit_Test_Exception_Exit $exception ) {

			$csv = $exception->get_status();

			$this->assertTrue( is_string( $csv ) );

			$lines = explode( "\n", $csv );
			$this->assertEquals( '"Order ID","Expected Payments","Total Payments","Successful Payments","Refunded Payments","Edit Link"', $lines[0] );
			array_shift( $lines );
			$orders = array_reverse( $orders ); // Orders affected by the change ($lines) are ordered by their `ID` `DESC`.

			foreach ( $lines as $i => $line ) {
				// Empty line at the end of the file.
				if ( 3 === $i ) {
					$this->assertEmpty( $line );
				} else {
					$link = get_edit_post_link( $orders[ $i ], 'raw' );
					$this->assertEquals( "{$orders[ $i ]},2,0,0,0,{$link}", $line );
				}
			}

		}

	}

	/**
	 * Test should_load()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_should_load() {

		// Shouldn't load.
		$this->create_mock_orders( 1 );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );

		// Created after upgrade, shouldn't load.
		$this->create_mock_orders( 1, array( '_llms_billing_length' => 2, '_llms_plan_ended' => 'yes' ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );

		// Should load.
		$this->create_mock_orders( 1, array( '_llms_billing_length' => 2, '_llms_date_billing_end' => '2021-05-05', '_llms_plan_ended' => 'yes' ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );

	}

}
