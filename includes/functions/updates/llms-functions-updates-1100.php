<?php
/**
 * Update functions for version 11.0.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

namespace LLMS\Updates\Version_11_0_0;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since [version]
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '11.0.0';
}

/**
 * Replace the stock "weak password is required" meter description.
 *
 * Core forms store the password helper text in post content. The default
 * minimum strength is weak, and that sentence reads as if a weak password
 * is what the site wants. Only the exact stock sentence is replaced, in
 * English and in the current locale. Custom descriptions, and medium or
 * strong requirements, are left as they are.
 *
 * @since [version]
 *
 * @return false Always returns `false` because every matching form is updated in one pass.
 */
function replace_weak_password_meter_descriptions() {

	global $wpdb;

	$replacement = \llms_get_password_meter_description( 'weak' );

	$searches = array(
		'A weak password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.',
		sprintf(
			// Translators: %s = Minimum password strength.
			\__( 'A %s password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.', 'lifterlms' ),
			\llms_get_minimum_password_strength_name( 'weak' )
		),
	);

	foreach ( array_unique( $searches ) as $search ) {

		if ( '' === $search || $search === $replacement ) {
			continue;
		}

		$like = '%' . $wpdb->esc_like( $search ) . '%';

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( %s, %s ) AND post_content LIKE %s",
				'llms_form',
				'wp_block',
				$like
			)
		); // db call ok; no-cache ok.

		if ( empty( $ids ) ) {
			continue;
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->posts} SET post_content = REPLACE( post_content, %s, %s ) WHERE post_type IN ( %s, %s ) AND post_content LIKE %s",
				$search,
				$replacement,
				'llms_form',
				'wp_block',
				$like
			)
		); // db call ok; no-cache ok.

		foreach ( $ids as $post_id ) {
			clean_post_cache( (int) $post_id );
		}
	}

	return false;
}

/**
 * Backfill the `_llms_has_transaction` flag on orders that have a visible transaction.
 *
 * Walks `_llms_order_id` rows once, using `llms_has_transaction_backfill_cursor` so each
 * batch continues after the last meta ID instead of rescanning rows already flagged.
 * Trashed and auto-draft transactions do not count: the report hides them, and an order
 * whose only transaction is trashed must stay unflagged so it can appear as its own row.
 *
 * Returns `true` while a full page was processed so the background updater calls it again,
 * otherwise `false`. Re-running is safe. `update_db_version()` runs only after this
 * returns `false`, which is what turns on the report's indexed query.
 *
 * @since [version]
 *
 * @return bool
 */
function backfill_has_transaction_flag() {

	global $wpdb;

	$per_page = \llms_update_util_get_items_per_page();
	$cursor   = (int) \get_option( 'llms_has_transaction_backfill_cursor', 0 );

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT txn.meta_id, txn.meta_value
			FROM {$wpdb->postmeta} AS txn
			INNER JOIN {$wpdb->posts} AS p ON p.ID = txn.post_id
			WHERE txn.meta_key = '_llms_order_id'
			  AND txn.meta_id > %d
			  AND txn.meta_value <> ''
			  AND p.post_type = 'llms_transaction'
			  AND p.post_status NOT IN ( 'trash', 'auto-draft' )
			ORDER BY txn.meta_id ASC
			LIMIT %d
			",
			$cursor,
			$per_page
		)
	); // db call ok; no-cache ok.

	if ( empty( $rows ) ) {
		\delete_option( 'llms_has_transaction_backfill_cursor' );
		return false;
	}

	foreach ( $rows as $row ) {
		\update_post_meta( (int) $row->meta_value, '_llms_has_transaction', 'yes' );
		$cursor = (int) $row->meta_id;
	}

	\update_option( 'llms_has_transaction_backfill_cursor', $cursor, false );

	if ( count( $rows ) < $per_page ) {
		\delete_option( 'llms_has_transaction_backfill_cursor' );
		return false;
	}

	return true;
}

/**
 * Update db version to 11.0.0.
 *
 * @since [version]
 *
 * @return false
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
