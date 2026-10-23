<?php
/**
 * Test the orders & transactions reporting table.
 *
 * @package LifterLMS/Tests/Tables
 *
 * @group reporting_tables
 *
 * @since [version]
 */
class LLMS_Test_Table_Orders_Transactions extends LLMS_UnitTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.orders.transactions.php';
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Table_Orders_Transactions::clear_cache();
	}

	/**
	 * Instructors can view reports but cannot open the Orders screen, so this table is closed to them.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_instructor_cannot_load_results() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'instructor' ) ) );

		$table = new LLMS_Table_Orders_Transactions();
		$this->assertFalse( $table->user_can_access() );

		$table->get_results();
		$this->assertSame( array(), $table->get_tbody_data() );
	}

	/**
	 * Amount sorting for transaction-less orders uses the initial (trial) price that is displayed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_amount_sort_uses_initial_price() {

		$trial = $this->get_mock_order( $this->get_mock_plan( 100, 1 ) );
		update_post_meta( $trial->get( 'id' ), '_llms_trial_offer', 'yes' );
		update_post_meta( $trial->get( 'id' ), '_llms_trial_total', 1 );
		update_post_meta( $trial->get( 'id' ), '_llms_total', 100 );

		$single = $this->get_mock_order( $this->get_mock_plan( 50, 0 ) );
		update_post_meta( $single->get( 'id' ), '_llms_total', 50 );

		$table = new LLMS_Table_Orders_Transactions();
		$table->get_results(
			array(
				'orderby'  => 'amount',
				'order'    => 'ASC',
				'per_page' => 100,
			)
		);

		$ids = array_map(
			function ( $row ) {
				return $row->get( 'id' );
			},
			$table->get_tbody_data()
		);
		$ids = array_values( array_intersect( $ids, array( $trial->get( 'id' ), $single->get( 'id' ) ) ) );

		$this->assertSame( array( $trial->get( 'id' ), $single->get( 'id' ) ), $ids );
	}

	/**
	 * The month filter ignores dates of orders that are hidden because they have transactions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_available_months_excludes_orders_with_transactions() {

		$order = $this->get_mock_order();
		wp_update_post(
			array(
				'ID'        => $order->get( 'id' ),
				'post_date' => '2001-01-15 00:00:00',
			)
		);

		$txn = $order->record_transaction(
			array(
				'amount'       => 10,
				'status'       => 'llms-txn-succeeded',
				'payment_type' => 'single',
			)
		);
		wp_update_post(
			array(
				'ID'        => $txn->get( 'id' ),
				'post_date' => '2002-02-15 00:00:00',
			)
		);

		$txnless = $this->get_mock_order();
		wp_update_post(
			array(
				'ID'        => $txnless->get( 'id' ),
				'post_date' => '2003-03-15 00:00:00',
			)
		);

		LLMS_Table_Orders_Transactions::clear_cache();

		$months = array_map(
			function ( $row ) {
				return sprintf( '%04d-%02d', $row->year, $row->month );
			},
			LLMS_Unit_Test_Util::call_method( new LLMS_Table_Orders_Transactions(), 'get_available_months' )
		);

		$this->assertNotContains( '2001-01', $months );
		$this->assertContains( '2002-02', $months );
		$this->assertContains( '2003-03', $months );
	}

}
