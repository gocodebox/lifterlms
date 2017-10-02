<?php
/**
 * Filters and actions related to user permissions
 * @since   3.13.0
 * @version 3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_User_Permissions {

	/**
	 * Constructor
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function __construct() {

		add_filter( 'user_has_cap', array( $this, 'handle_caps' ), 10, 3 );

		if ( is_admin() ) {
			add_filter( 'editable_roles', array( $this, 'editable_roles' ) );
		}

	}

	/**
	 * Filters roles available to users when creating / editing users on the admin panel
	 * Allows LMS Managers to create instructors and other managers
	 * Allows instructors to create & manage assistants
	 * @param    [type]     $all_roles  [description]
	 * @return   [type]                 [description]
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function editable_roles( $all_roles ) {

		$user = wp_get_current_user();

		$lms_roles = apply_filters( 'llms_editable_roles', array(
			'lms_manager' => array( 'instructor', 'instructors_assistant', 'lms_manager', 'student' ),
			'instructor' => array( 'instructors_assistant' ),
		) );

		foreach ( $lms_roles as $role => $allowed_roles ) {

			if ( in_array( $role, $user->roles ) ) {

				foreach ( $all_roles as $the_role => $caps ) {
					if ( ! in_array( $the_role, $allowed_roles ) ) {
						unset( $all_roles[ $the_role ] );
					}
				}
			}
		}

		return $all_roles;

	}

	/**
	 * Custom capability checks for LifterLMS things
	 * @param array  $allcaps  All the capabilities of the user
	 * @param array  $cap      [0] Required capability
	 * @param array  $args     [0] Requested capability
	 *                         [1] User ID
	 *                         [2] Associated object ID
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function handle_caps( $allcaps, $cap, $args ) {

		foreach ( array( 'courses', 'lessons', 'sections', 'quizzes', 'questions', 'memberships' ) as $cpt ) {
			// allow any instructor to edit courses
			// they're attached to
			if ( in_array( sprintf( 'edit_others_%s', $cpt ), $cap ) ) {
				$allcaps = $this->edit_others_lms_content( $allcaps, $cap, $args );
			}
		}

		return $allcaps;

	}

	/**
	 * Handle capabilities checks for lms content to allow *editing* content based on course instructor
	 * meta data
	 * @param array  $allcaps  All the capabilities of the user
	 * @param array  $cap      [0] Required capability
	 * @param array  $args     [0] Requested capability
	 *                         [1] User ID
	 *                         [2] Associated object ID
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function edit_others_lms_content( $allcaps, $cap, $args ) {

		// this might be a problem
		// this happens when in wp-admin/includes/post.php
		// when actually creating/updating a course
		// and no post_id is passed in $args[2]
		if ( empty( $args[2] ) ) {
			$allcaps[ $cap[0] ] = true;
			return $allcaps;
		}

		$instructor = llms_get_instructor( $args[1] );
		if ( $instructor && $instructor->is_instructor( $args[2] ) ) {
			$allcaps[ $cap[0] ] = true;
		}

		return $allcaps;

	}

}

return new LLMS_User_Permissions();
