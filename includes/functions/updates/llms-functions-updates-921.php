<?php
/**
 * Update functions for version 9.2.1
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 9.2.1
 */

namespace LLMS\Updates\Version_9_2_1;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since 9.2.1
 *
 * @return string
 */
function _get_db_version() {
	return '9.2.1';
}

/**
 * Clear stale course data locks and re-schedule course data processing.
 *
 * Finds all `_llms_temp_calc_data_lock` postmeta entries, deletes them, and
 * triggers `llms_course_calculate_data` for each affected course.
 *
 * Returns `true` if there may be more records to process so the background
 * updater can call this again, otherwise `false` when done.
 *
 * @since 9.2.1
 *
 * @return bool
 */
function reset_course_calc_data_locks() {

	global $wpdb;

	$per_page = \llms_update_util_get_items_per_page();

	// Find a page of locked courses.
	$course_ids = $wpdb->get_col(
		$wpdb->prepare(
			"
			SELECT DISTINCT pm.post_id
			FROM {$wpdb->postmeta} AS pm
			INNER JOIN {$wpdb->posts} AS p
				ON p.ID = pm.post_id
			WHERE pm.meta_key = %s
			  AND p.post_type = %s
			LIMIT %d
			",
			'_llms_temp_calc_data_lock',
			'course',
			$per_page
		)
	);// db call ok; no-cache ok.

	if ( empty( $course_ids ) ) {
		return false;
	}

	foreach ( $course_ids as $course_id ) {

		$course_id = (int) $course_id;

		// Drop the temp lock meta.
		\delete_post_meta( $course_id, '_llms_temp_calc_data_lock' );

		// Kick off a fresh course data calculation round.
		// LLMS_Processor_Course_Data::schedule_calculation() is already
		// wired to this action and internally avoids duplicate scheduling
		// via wp_next_scheduled().
		\do_action( 'llms_course_calculate_data', $course_id );
	}

	// If there were $per_page results, assume there might be more.
	return ( count( $course_ids ) === $per_page );
}

/**
 * Update db version to 9.2.1.
 *
 * @since 9.2.1
 *
 * @return false
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
