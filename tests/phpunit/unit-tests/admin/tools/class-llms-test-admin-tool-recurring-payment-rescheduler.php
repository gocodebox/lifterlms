<?php
/**
 * Tests for the LLMS_Admin_Tool_Recurring_Payment_Rescheduler class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 * @group recurring_rescheduler
 *
 * @since 4.6.0
 * @since 5.3.0 Use `LLMS_Admin_Tool_Test_Case` and remove redundant methods/tests.
 */
class LLMS_Test_Admin_Tool_Recurring_Payment_Rescheduler extends LLMS_Admin_Tool_Test_Case {

	/**
	 * Name of the class being tested.
	 *
	 * @var sting
	 */
	const CLASS_NAME = 'LLMS_Admin_Tool_Recurring_Payment_Rescheduler';

	/**
	 * Teardown the test case.
	 *
	 * @since 4.6.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		$this->clear_cache();

	}

	/**
	 * Create N number of orders in the DB
	 *
	 * @since 4.6.0
	 *
	 * @param integer $count         Number of orders to create.
	 * @param boolean $remove_action Whether or not to remove a scheduled payment action.
	 *                               If `true`, creates orders that would be handled by the tool, otherwise creates orders
	 *                               that should be missed by the tool's queries.
	 * @return int[] An array of WP_Post IDs for the created orders.
	 */
	private function create_orders_to_handle( $count = 3, $remove_action = true ) {

		$orders = array();

		$i = 1;
		while ( $i <= $count ) {

			$order = $this->get_mock_order();
			$order->set_status( 'llms-active' );
			$order->maybe_schedule_payment();

			if ( $remove_action ) {
				$order->unschedule_recurring_payment();
			}

			$orders[] = $order->get( 'id' );

			++$i;
		}

		return $orders;

	}

	/**
	 * Clear cached batch count data.
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	private function clear_cache() {
		wp_cache_delete( 'recurring-payment-rescheduler', 'llms_tool_data' );
		wp_cache_delete( 'recurring-payment-rescheduler-total-results', 'llms_tool_data' );
	}

	/**
	 * Test get_orders() during a cache hit
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function test_get_orders_cache_hit() {

		wp_cache_set( 'recurring-payment-rescheduler', 'mock cache', 'llms_tool_data' );
		$this->assertEquals( 'mock cache',  LLMS_Unit_Test_Util::call_method( $this->main, 'get_orders' ) );

	}

	/**
	 * Test get_orders() during a cache miss
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function test_get_orders_cache_miss() {

		$orders = $this->create_orders_to_handle();

		// Order IDs returned.
		$this->assertEqualSets( $orders, LLMS_Unit_Test_Util::call_method( $this->main, 'get_orders' ) );

		// Cache is set.
		$this->assertEqualSets( $orders, wp_cache_get( 'recurring-payment-rescheduler', 'llms_tool_data' ) );

	}

	/**
	 * Test handle()
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function test_handle() {

		$orders = $this->create_orders_to_handle();

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'handle' );

		// All expected orders were handled.
		$this->assertEqualSets( $orders, $res );

		// Cache erased.
		$this->assertFalse( wp_cache_get( 'recurring-payment-rescheduler', 'llms_tool_data' ) );

		foreach ( $res as $id ) {

			$order = llms_get_post( $id );

			// Action is rescheduled.
			$this->assertEquals( $order->get_next_payment_due_date( 'U' ), $order->get_next_scheduled_action_time( 'llms_charge_recurring_payment' ) );

		}

	}

	/**
	 * Test handle() properly handles "legacy" orders that don't have `plan_ended()` meta data.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_handle_orders_with_no_meta() {

		// Force a WP_Error to be returned by LLMS_Order::get_next_payment_due_date().
		add_filter( 'llms_order_calculate_next_payment_date', '__return_empty_string' );

		$orders = $this->create_orders_to_handle( 1 );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'handle' );

		// No orders handled.
		$this->assertEquals( array(), $res );

		// The missing metadata has been added by the tool.
		$this->assertEquals( 'yes', llms_get_post( $orders[0] )->get( 'plan_ended' ) );

		remove_filter( 'llms_order_calculate_next_payment_date', '__return_empty_string' );

	}

	/**
	 * Test query_orders()
	 *
	 * @since 4.6.0
	 * @since 4.7.0 Add an order with `plan_ended` meta that should be ignored and add tests for `FOUND_ROWS()` cached data.
	 * @since 7.0.1 Remove reference to undefined property.
	 *
	 * @return void
	 */
	public function test_query_orders() {

		// No orders.
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'query_orders' ) );

		// Should be found.
		$to_handle = $this->create_orders_to_handle();

		// This order should not be in the returned array.
		$to_ignore = $this->create_orders_to_handle( 1, false );

		// Ignored because of `plan_ended` meta data.
		$to_ignore_2 = $this->create_orders_to_handle( 1 );
		llms_get_post( $to_ignore_2[0] )->set( 'plan_ended', 'yes' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'query_orders' );

		$this->assertEqualSets( $to_handle, wp_list_pluck( $res, 'ID' ) );

		// Test FOUND_ROWS() cache data.
		$this->assertEquals( 3, wp_cache_get( 'recurring-payment-rescheduler-total-results', 'llms_tool_data' ) );

	}

	/**
	 * Test should_load()
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function test_should_load() {

		// No orders to handle.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );

		// Orders to handle.
		$this->create_orders_to_handle( 1 );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );

	}

}
