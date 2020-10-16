<?php
/**
 * Tests for the LLMS_Admin_Tool_Recurring_Payment_Rescheduler class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 *
 * @since [version]
 */
class LLMS_Test_Admin_Tool_Recurring_Payment_Rescheduler extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include abstract class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-admin-tool.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/tools/class-llms-admin-tool-recurring-payment-rescheduler.php';

	}

	/**
	 * Teardown the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		$this->clear_cache();

	}

	/**
	 * Create N number of orders in the DB
	 *
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	private function clear_cache() {
		wp_cache_delete( 'recurring-payment-rescheduler', 'llms_tool_data' );
	}

	/**
	 * Setup the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->main = new LLMS_Admin_Tool_Recurring_Payment_Rescheduler();
	}

	/**
	 * Test get_description()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_description() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_description' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_label()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_label() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_label' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_text()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_text() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_text' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_orders() during a cache hit
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * Test query_orders()
	 *
	 * @since [version]
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

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'query_orders' );

		$this->assertEqualSets( $to_handle, wp_list_pluck( $res, 'ID' ) );

	}

	/**
	 * Test should_load()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function test_should_load() {

		// No orders to handle.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );

		// Orders to handle.
		$this->create_orders_to_handle( 1 );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );

	}

}
