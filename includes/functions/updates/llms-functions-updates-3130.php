<?php
/**
 * Update functions for version 3.13.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;


/**
 * Setup default instructor data for courses and memberships
 *
 * @since 3.13.0
 *
 * @return void
 */
function llms_update_3130_create_default_instructors() {

	$query = new WP_Query(
		array(
			'post_type'      => array( 'course', 'llms_membership' ),
			'posts_per_page' => -1,
		)
	);

	foreach ( $query->posts as $post ) {
		$course = llms_get_post( $post );
		$course->set_instructors();
	}

}

/**
 * Add an admin notice about the new builder
 *
 * @since 3.13.0
 *
 * @return void
 */
function llms_update_3130_builder_notice() {

	require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

	LLMS_Admin_Notices::add_notice(
		'update-3130',
		array(
			'html'        => sprintf(
				__( 'Welcome to LifterLMS 3.13.0! We\'ve packed a ton of features into this release: Take a moment to get familiar with the all new %1$scourse builder%3$s and our new %2$suser roles%3$s.', 'lifterlms' ),
				'<a href="https://lifterlms.com/docs/using-course-builder/" target="_blank">',
				'<a href="https://lifterlms.com/docs/roles-and-capabilities/" target="_blank">',
				'</a>'
			),
			'type'        => 'info',
			'dismissible' => true,
			'remindable'  => false,
		)
	);

}

/**
 * Update db version at conclusion of 3.13.0 updates
 *
 * @since 3.13.0
 *
 * @return void
 */
function llms_update_3130_update_db_version() {

	LLMS_Install::update_db_version( '3.13.0' );

}
