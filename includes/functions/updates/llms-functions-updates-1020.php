<?php
/**
 * Update functions for version 10.2.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

namespace LLMS\Updates\Version_10_2_0;

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
	return '10.2.0';
}

/**
 * Deletes zero-value lesson and course time tracking cache rows from the usermeta table.
 *
 * Versions 10.1.x cached a `llms_lesson_time_{$lesson_id}` usermeta row for every
 * student and lesson touched by reporting screens and exports, even when the student
 * had no tracked time, bloating the usermeta table with zero-value rows. Zero totals
 * are no longer cached per lesson, and course totals are recomputed cheaply on demand,
 * so all zero-value rows can be safely removed.
 *
 * @since [version]
 *
 * @return bool Returns `true` if more records need to be deleted and `false` upon completion.
 */
function delete_zero_time_tracking_caches() {

	global $wpdb;

	$per_page = \llms_update_util_get_items_per_page();

	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta}
			 WHERE ( meta_key LIKE %s OR meta_key LIKE %s )
			   AND meta_value = '0'
			 LIMIT %d",
			$wpdb->esc_like( 'llms_lesson_time_' ) . '%',
			$wpdb->esc_like( 'llms_course_time_' ) . '%',
			$per_page
		)
	);

	// If a full page was deleted assume there are more rows and run again.
	return ( $deleted === $per_page );
}

/**
 * Update db version to 10.2.0.
 *
 * @since [version]
 *
 * @return false
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
