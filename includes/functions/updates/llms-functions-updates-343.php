<?php
/**
 * Update functions for version 3.4.3
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rename meta keys for parent section and parent course relationships for all LifterLMS Lessons and Sections
 *
 * @since 3.4.3
 *
 * @return void
 */
function llms_update_343_update_relationships() {

	global $wpdb;

	// Update parent course key for courses and lessons.
	$wpdb->query(
		"UPDATE {$wpdb->postmeta} AS m
		 JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
		 SET m.meta_key = '_llms_parent_course'
		 WHERE m.meta_key = '_parent_course'
		   AND ( p.post_type = 'lesson' OR p.post_type = 'section' );"
	);

	// Update parent section key for lessons.
	$wpdb->query(
		"UPDATE {$wpdb->postmeta} AS m
		 JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
		 SET m.meta_key = '_llms_parent_section'
		 WHERE m.meta_key = '_parent_section'
		   AND p.post_type = 'lesson';"
	);

}

/**
 * Update db version at conclusion of 3.4.3 updates
 *
 * @since 3.4.3
 *
 * @return void
 */
function llms_update_343_update_db_version() {

	LLMS_Install::update_db_version( '3.4.3' );

}
