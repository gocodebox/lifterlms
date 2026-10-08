<?php
/**
 * Update functions for version 10.2.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 10.2.0
 * @version 10.2.0
 */

namespace LLMS\Updates\Version_10_2_0;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since 10.2.0
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '10.2.0';
}

/**
 * Deletes zero-value per-lesson time tracking cache rows from the usermeta table.
 *
 * Versions 10.1.x cached a `llms_lesson_time_{$lesson_id}` usermeta row for every
 * student and lesson touched by course reporting screens and exports, even when the
 * student had no tracked time, bloating the usermeta table with zero-value rows.
 * Those screens now compute time at the course level and no longer read or write
 * per-lesson caches, so the bulk-created zero rows can be safely removed: they only
 * get recreated individually when a specific student/lesson is viewed.
 *
 * Course-level (`llms_course_time_{$course_id}`) rows are intentionally kept, as
 * reports still use them and would recreate them on the next render.
 *
 * @since 10.2.0
 *
 * @return bool Returns `true` if more records need to be deleted and `false` upon completion.
 */
function delete_zero_lesson_time_caches() {

	global $wpdb;

	$per_page = \llms_update_util_get_items_per_page();

	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta}
			 WHERE meta_key LIKE %s
			   AND meta_value = '0'
			 LIMIT %d",
			$wpdb->esc_like( 'llms_lesson_time_' ) . '%',
			$per_page
		)
	);

	// If a full page was deleted assume there are more rows and run again.
	return ( $deleted === $per_page );
}

/**
 * Update db version to 10.2.0.
 *
 * @since 10.2.0
 *
 * @return false
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
