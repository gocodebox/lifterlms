<?php
/**
 * Update functions for version 3.0.3
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fix students with the bugged role "students"
 *
 * @since 3.0.3
 *
 * @return void
 */
function llms_update_303_update_students_role() {

	// Add the bugged role so we can remove it.
	// We delete it at the conclusion of the function.
	if ( ! get_role( 'studnet' ) ) {

		add_role(
			'studnet',
			__( 'Student', 'lifterlms' ),
			array(
				'read' => true,
			)
		);

	}

	$users = new WP_User_Query(
		array(
			'number'   => -1,
			'role__in' => array( 'studnet' ),
		)
	);

	if ( $users->get_results() ) {
		foreach ( $users->get_results() as $user ) {
			$user->remove_role( 'studnet' );
			$user->add_role( 'student' );
		}
	}

	// Remove the bugged role when finished.
	remove_role( 'studnet' );

}

/**
 * Update db version at conclusion of 3.0.3 updates
 *
 * @since 3.0.3
 *
 * @return void
 */
function llms_update_303_update_db_version() {

	LLMS_Install::update_db_version( '3.0.3' );
}
