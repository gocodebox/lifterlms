<?php
/**
 * Test updates functions when updating to 10.1.0.
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_1010
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_1010 extends LLMS_UnitTestCase {

	/**
	 * Setup before class.
	 *
	 * Include update functions file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-1010.php';
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Setup the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		add_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );
	}

	/**
	 * Tear down the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		remove_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );
	}

	/**
	 * Callback to reduce items per page for testing pagination.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function per_page() {
		return 2;
	}

	/**
	 * Create a legacy order with a transaction but no `_llms_has_transaction` flag.
	 *
	 * @since [version]
	 *
	 * @return int Order post ID.
	 */
	private function create_legacy_order_with_transaction() {
		$order_id = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );
		$txn_id   = $this->factory->post->create( array( 'post_type' => 'llms_transaction' ) );
		update_post_meta( $txn_id, '_llms_order_id', $order_id );
		// Simulate legacy data: flag not yet set.
		delete_post_meta( $order_id, '_llms_has_transaction' );
		return $order_id;
	}

	/**
	 * Test backfill_has_transaction_flag() flags only orders with transactions and
	 * paginates, returning true while more remain and false when complete.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_backfill_has_transaction_flag() {

		// 3 orders with transactions (per_page is 2, so this requires two passes).
		$with_txns = array(
			$this->create_legacy_order_with_transaction(),
			$this->create_legacy_order_with_transaction(),
			$this->create_legacy_order_with_transaction(),
		);

		// 1 order without a transaction (should never be flagged).
		$without_txn = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );

		// First pass: full page processed, more remain.
		$this->assertTrue( \LLMS\Updates\Version_10_1_0\backfill_has_transaction_flag() );

		// Second pass: remaining order processed, none left -> returns false.
		$this->assertFalse( \LLMS\Updates\Version_10_1_0\backfill_has_transaction_flag() );

		foreach ( $with_txns as $order_id ) {
			$this->assertEquals( 'yes', get_post_meta( $order_id, '_llms_has_transaction', true ), "Order {$order_id} should be flagged." );
		}

		$this->assertEmpty( get_post_meta( $without_txn, '_llms_has_transaction', true ) );
	}

	/**
	 * Test backfill_has_transaction_flag() returns false immediately with nothing to do.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_backfill_has_transaction_flag_noop() {
		$this->assertFalse( \LLMS\Updates\Version_10_1_0\backfill_has_transaction_flag() );
	}

	/**
	 * Test update_db_version().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		delete_option( 'lifterlms_db_version' );

		\LLMS\Updates\Version_10_1_0\update_db_version();

		$this->assertEquals( \LLMS\Updates\Version_10_1_0\_get_db_version(), get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );
	}

}
