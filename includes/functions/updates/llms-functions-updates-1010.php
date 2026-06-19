<?php
/**
 * Update functions for version 10.1.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

namespace LLMS\Updates\Version_10_1_0;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since [version]
 *
 * @return string
 */
function _get_db_version() {
	return '10.1.0';
}

/**
 * Backfill the `_llms_has_transaction` flag on orders that already have transactions.
 *
 * The Orders & Transactions report uses this flag to cheaply exclude orders that are
 * represented by their transaction rows (via an indexed `NOT EXISTS` lookup) instead of
 * a potentially huge `post__not_in` list. This migration flags existing orders.
 *
 * Processes a single page of orders per call and returns `true` while there may be more
 * to process so the background updater calls it again, otherwise `false` when complete.
 * The `NOT EXISTS` guard makes each batch idempotent and safe to re-run.
 *
 * @since [version]
 *
 * @return bool
 */
function backfill_has_transaction_flag() {

	global $wpdb;

	$per_page = \llms_update_util_get_items_per_page();

	// Distinct order IDs that have at least one transaction but no `_llms_has_transaction` flag yet.
	$order_ids = $wpdb->get_col(
		$wpdb->prepare(
			"
			SELECT DISTINCT txn.meta_value
			FROM {$wpdb->postmeta} AS txn
			WHERE txn.meta_key = '_llms_order_id'
			  AND txn.meta_value <> ''
			  AND NOT EXISTS (
				SELECT 1 FROM {$wpdb->postmeta} AS flag
				WHERE flag.post_id = txn.meta_value
				  AND flag.meta_key = '_llms_has_transaction'
			  )
			LIMIT %d
			",
			$per_page
		)
	);// db call ok; no-cache ok.

	if ( empty( $order_ids ) ) {
		return false;
	}

	foreach ( $order_ids as $order_id ) {
		\update_post_meta( (int) $order_id, '_llms_has_transaction', 'yes' );
	}

	// If a full page was processed, assume there might be more.
	return count( $order_ids ) === $per_page;
}

/**
 * Update db version to 10.1.0.
 *
 * @since [version]
 *
 * @return false
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
