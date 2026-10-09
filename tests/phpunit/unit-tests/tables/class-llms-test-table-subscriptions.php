<?php
/**
 * Test the subscriptions reporting table.
 *
 * @package LifterLMS/Tests/Tables
 *
 * @group reporting_tables
 *
 * @since [version]
 */
class LLMS_Test_Table_Subscriptions extends LLMS_UnitTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.subscriptions.php';
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Get the IDs of the orders returned by the table for the given args.
	 *
	 * @since [version]
	 *
	 * @param array $args Arguments passed to get_results().
	 * @return int[]
	 */
	private function get_result_ids( $args ) {
		$table = new LLMS_Table_Subscriptions();
		$table->get_results( array_merge( array( 'per_page' => 100 ), $args ) );
		return array_map(
			function ( $order ) {
				return $order->get( 'id' );
			},
			$table->get_tbody_data()
		);
	}

	/**
	 * Sorting by a meta column keeps subscriptions missing that meta and lists them last.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_meta_sort_keeps_orders_missing_meta() {

		$plan = $this->get_mock_plan( 25.99, 1 );

		$early = $this->get_mock_order( $plan );
		$late  = $this->get_mock_order( $plan );
		$ended = $this->get_mock_order( $plan );

		update_post_meta( $early->get( 'id' ), '_llms_date_next_payment', '2030-01-01 00:00:00' );
		update_post_meta( $late->get( 'id' ), '_llms_date_next_payment', '2031-01-01 00:00:00' );
		delete_post_meta( $ended->get( 'id' ), '_llms_date_next_payment' );

		$expected_ids = array( $early->get( 'id' ), $late->get( 'id' ), $ended->get( 'id' ) );

		$asc = array_values( array_intersect( $this->get_result_ids( array( 'orderby' => 'next_payment', 'order' => 'ASC' ) ), $expected_ids ) );
		$this->assertSame( $expected_ids, $asc );

		$desc = array_values( array_intersect( $this->get_result_ids( array( 'orderby' => 'next_payment', 'order' => 'DESC' ) ), $expected_ids ) );
		$this->assertSame( array( $late->get( 'id' ), $early->get( 'id' ), $ended->get( 'id' ) ), $desc );
	}

	/**
	 * Sorting by product does not drop subscriptions missing a product title.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_product_sort_keeps_orders_missing_title() {

		$plan    = $this->get_mock_plan( 25.99, 1 );
		$titled  = $this->get_mock_order( $plan );
		$missing = $this->get_mock_order( $plan );

		delete_post_meta( $missing->get( 'id' ), '_llms_product_title' );

		$ids = $this->get_result_ids( array( 'orderby' => 'product', 'order' => 'ASC' ) );

		$this->assertContains( $titled->get( 'id' ), $ids );
		$this->assertContains( $missing->get( 'id' ), $ids );
	}

	/**
	 * The plan cell uses a translated period, and the export cell has no HTML entities.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_plan_period_is_localized_and_export_decodes_entities() {

		$order = $this->get_mock_order( $this->get_mock_plan( 25.99, 1 ) );
		$order->set( 'billing_period', 'month' );
		$order->set( 'billing_frequency', 2 );

		$table = new LLMS_Table_Subscriptions();
		$html  = LLMS_Unit_Test_Util::call_method( $table, 'get_data', array( 'plan', $order ) );

		$this->assertStringContainsString( '2 months', strtolower( wp_strip_all_tags( $html ) ) );

		$export = $table->get_export_data( 'plan', $order );
		$this->assertStringNotContainsString( '&#', $export );
		$this->assertStringNotContainsString( '<', $export );
	}

}
