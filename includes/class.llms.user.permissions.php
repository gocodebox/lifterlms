<?php
/**
 * Filters and actions related to user permissions
 *
 * @since 3.13.0
 * @version 3.34.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters and actions related to user permissions
 *
 * @since 3.13.0
 * @since 3.34.0 Always add the `editable_roles` filter.
 * @since 3.34.0 Added methods and logic for managing user management of other users.
 *                  Add logic for `view_students`, `edit_students`, and `delete_students` capabilities.
 */
class LLMS_User_Permissions {

	/**
	 * Constructor
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Always add the `editable_roles` filter.
	 */
	public function __construct() {

		add_filter( 'user_has_cap', array( $this, 'handle_caps' ), 10, 3 );
		add_filter( 'editable_roles', array( $this, 'editable_roles' ) );

	}

	/**
	 * Determines what other user roles can be managed by a user role.
	 *
	 * Allows LMS Managers to create instructors and other managers.
	 * Allows instructors to create & manage assistants.
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Moved the `llms_editable_roles` filter to the class method get_editable_roles().
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/editable_roles
	 *
	 * @param array $all_roles All roles array.
	 * @return array
	 */
	public function editable_roles( $all_roles ) {

		$user = wp_get_current_user();

		$lms_roles = self::get_editable_roles();

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
	 * Handle capabilities checks for lms content to allow *editing* content based on course instructor
	 *
	 * @since 3.13.0
	 *
	 * @param array $allcaps  All the capabilities of the user
	 * @param array $cap      [0] Required capability
	 * @param array $args     [0] Requested capability
	 *                        [1] User ID
	 *                        [2] Associated object ID
	 * @return array
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

	/**
	 * Get a map of roles that can be managed by LifterLMS User Roles
	 *
	 * @since 3.34.0
	 *
	 * @return array
	 */
	public static function get_editable_roles() {

		/**
		 * Get a map of roles that can be managed by LifterLMS User Roles
		 *
		 * @since 3.13.0
		 *
		 * @param array $roles Array of user roles. The array key is the role and the value is an array of roles that can be managed by that role.
		 */
		$roles = apply_filters(
			'llms_editable_roles',
			array(
				'lms_manager' => array( 'instructor', 'instructors_assistant', 'lms_manager', 'student' ),
				'instructor'  => array( 'instructors_assistant' ),
			)
		);

		return $roles;

	}

	/**
	 * Custom capability checks for LifterLMS things
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Add logic for `edit_users` and `delete_users` capabilities with regards to LifterLMS user roles.
	 *                  Add logic for `view_students`, `edit_students`, and `delete_students` capabilities.
	 *
	 * @param array $allcaps  All the capabilities of the user
	 * @param array $cap      [0] Required capability
	 * @param array $args     [0] Requested capability
	 *                        [1] User ID
	 *                        [2] Associated object ID
	 * @return array
	 */
	public function handle_caps( $allcaps, $cap, $args ) {

		foreach ( array( 'courses', 'lessons', 'sections', 'quizzes', 'questions', 'memberships' ) as $cpt ) {
			// allow any instructor to edit courses
			// they're attached to
			if ( in_array( sprintf( 'edit_others_%s', $cpt ), $cap ) ) {
				$allcaps = $this->edit_others_lms_content( $allcaps, $cap, $args );
			}
		}

		$required_cap = ! empty( $cap[0] ) ? $cap[0] : false;

		// We don't have a cap or the user doesn't have the requested cap.
		if ( ! $required_cap || empty( $allcaps[ $required_cap ] ) ) {
			return $allcaps;
		}

		$user_id   = ! empty( $args[1] ) ? $args[1] : false;
		$object_id = ! empty( $args[2] ) ? $args[2] : false;

		if ( in_array( $required_cap, array( 'edit_users', 'delete_users' ), true ) ) {
			if ( $user_id && $object_id && false === $this->user_can_manage_user( $user_id, $object_id ) ) {
				unset( $allcaps[ $required_cap ] );
			}
		}

		if ( in_array( $required_cap, array( 'view_students', 'edit_students', 'delete_students' ), true ) ) {
			$others_cap = str_replace( '_', '_others_', $required_cap );
			if ( $user_id && $object_id && ! user_can( $user_id, $others_cap ) ) {
				$instructor = llms_get_instructor( $user_id );
				if ( ! $instructor || ! $instructor->has_student( $object_id ) ) {
					unset( $allcaps[ $required_cap ] );
				}
			}
		}

		return $allcaps;

	}

	/**
	 * Determines if the current user is an instructor.
	 *
	 * @since 3.34.0
	 *
	 * @return bool
	 */
	public static function is_current_user_instructor() {

		return ( current_user_can( 'lifterlms_instructor' ) && current_user_can( 'list_users' ) && ! current_user_can( 'manage_lifterlms' ) );

	}

	/**
	 * Determine if a user can manage another user.
	 *
	 * Run on `user_has_cap` filters for the `edit_users` and `delete_users` capabilities.
	 *
	 * @since 3.34.0
	 *
	 * @param int $user_id WP User ID of the user requesting to perform the action.
	 * @param int $edit_id WP User ID of the user the action will be performed on.
	 * @return bool|null Returns true if the user preform the action, false if it can't, and null for core user roles which are skipped.
	 */
	protected function user_can_manage_user( $user_id, $edit_id ) {

		$lms_roles = array_keys( LLMS_Roles::get_roles() );

		$user       = get_user_by( 'id', $user_id );
		$user_roles = array_intersect( $user->roles, $lms_roles );

		// Core roles return are skipped, ie null means "I don't know".
		if ( ! $user_roles ) {
			return null;
		}

		// User's can edit themselves.
		if ( absint( $user_id ) === absint( $edit_id ) ) {
			return true;
		}

		$edit_user  = get_user_by( 'id', $edit_id );
		$edit_roles = array_intersect( $edit_user->roles, $lms_roles );

		$editable_roles = self::get_editable_roles();

		foreach ( $user_roles as $role ) {

			if ( 'instructor' === $role && in_array( 'instructors_assistant', $edit_roles, true ) ) {
				$instructor = llms_get_instructor( $user );
				if ( in_array( $edit_id, $instructor->get_assistants(), false ) ) {
					return true;
				}
			} elseif ( ! empty( $editable_roles[ $role ] ) && array_intersect( $edit_roles, $editable_roles[ $role ] ) ) {
				return true;
			}
		}

		return false;

	}

}

return new LLMS_User_Permissions();
