<?php
/**
 * Update functions for version 7.7.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 */

namespace LLMS\Updates\Version_7_7_0;

use function llms_is_elementor_post;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since 7.7.0
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '7.7.0';
}

/**
 * Migrate courses edited in Elementor, by adding our widgets to the elementor data.
 *
 * @since [version]
 *
 * @return void
 */
function elementor_migrate_courses() {
	$courses = get_posts(
		array(
			'post_type'      => 'course',
			'posts_per_page' => -1,
		)
	);

	global $llms_elementor_migrate;

	foreach ( $courses as $course ) {
		if ( ! llms_is_elementor_post( $course->ID ) ) {
			continue;
		}

		if ( $llms_elementor_migrate->should_migrate_post( $course->ID ) ) {
			$llms_elementor_migrate->add_template_to_post( $course->ID );
		}
	}
}

/**
 * Update db version to 7.7.0
 *
 * @since [version]
 *
 * @return false.
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
