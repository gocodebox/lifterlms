<?php
/**
 * Update functions for version 3.12.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add end dates to LifterLMS Orders which have a length but no saved end date
 *
 * @since 3.12.0
 *
 * @return void
 */
function llms_update_3120_update_order_end_dates() {

	global $wpdb;

	$ids = $wpdb->get_col(
		"SELECT posts.ID
		 FROM {$wpdb->posts} AS posts
		 JOIN {$wpdb->postmeta} AS meta1 ON meta1.post_id = posts.ID AND meta1.meta_key = '_llms_billing_length'
		 LEFT JOIN {$wpdb->postmeta} AS meta2 ON meta2.post_id = posts.ID AND meta2.meta_key = '_llms_date_billing_end'
		 WHERE posts.post_type = 'llms_order'
		   AND meta2.meta_value IS NULL
		   AND meta1.meta_value > 0;"
	); // db call ok; no-cache ok.

	foreach ( $ids as $id ) {

		$order = llms_get_post( $id );
		if ( ! is_a( $order, 'LLMS_Order' ) ) {
			continue;
		}

		$order->maybe_schedule_payment( true );

	}

}

/**
 * Rename options for bbPress and BuddyPress to follow the abstract integration options structure
 *
 * @since 3.12.0
 *
 * @return void
 */
function llms_update_3120_update_integration_options() {

	global $wpdb;
	$wpdb->update(
		$wpdb->options,
		array(
			'option_name' => 'llms_integration_bbpress_enabled',
		),
		array(
			'option_name' => 'lifterlms_bbpress_enabled',
		)
	); // db call ok; no-cache ok.

	$wpdb->update(
		$wpdb->options,
		array(
			'option_name' => 'llms_integration_buddypress_enabled',
		),
		array(
			'option_name' => 'lifterlms_buddypress_enabled',
		)
	); // db call ok; no-cache ok.

}

/**
 * Update db version at conclusion of 3.12.0 updates
 *
 * @since 3.12.0
 *
 * @return void
 */
function llms_update_3120_update_db_version() {

	LLMS_Install::update_db_version( '3.12.0' );

}
