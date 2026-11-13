<?php
/**
 * Tests for the LLMS_Admin_Post_Table_Orders class.
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group post_tables
 * @group post_table_orders
 *
 * @since [version]
 */
class LLMS_Test_Admin_Post_Table_Orders extends LLMS_UnitTestCase {

	/**
	 * Set up before class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/post-tables/class.llms.admin.post.table.orders.php';

	}

	/**
	 * Test manage_columns() shows a trashed order's status from before it was trashed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_manage_columns_payment_status_trashed_order() {

		$order = $this->get_mock_order();
		$order->set_status( 'llms-on-hold' );
		wp_trash_post( $order->get( 'id' ) );

		$this->expectOutputRegex( '/<span class="llms-status llms-size--large llms-on-hold ">On Hold<\/span>/' );
		( new LLMS_Admin_Post_Table_Orders() )->manage_columns( 'payment_status', $order->get( 'id' ) );

	}

}
