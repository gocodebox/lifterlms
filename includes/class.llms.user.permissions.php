<?php
/**
 * LLMS_User_Permissions class file
 *
 * @package LifterLMS/Classes
 *
 * @since 3.13.0
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters and actions related to user permissions
 *
 * @since 3.13.0
 * @since 3.34.0 Always add the `editable_roles` filter.
 * @since 3.34.0 Added methods and logic for managing user management of other users.
 *                  Add logic for `view_students`, `edit_students`, and `delete_students` capabilities.
 * @since 3.36.5 Add `llms_user_caps_edit_others_posts_post_types` filter to allow 3rd parties to utilize core methods for modifying other users posts.
 * @since 3.37.14 Use strict comparisons where needed.
 * @since 3.41.0 Improve user management of other users when the managing user has multiple roles.
 */
class LLMS_User_Permissions {

	/**
	 * Constructor
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Always add the `editable_roles` filter.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'user_has_cap', array( $this, 'handle_caps' ), 10, 3 );
		add_filter( 'editable_roles', array( $this, 'editable_roles' ) );

	}

	/**
	 * Determines what other user roles can be managed by a user role
	 *
	 * Allows LMS Managers to create instructors and other managers.
	 * Allows instructors to create & manage assistants.
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Moved the `llms_editable_roles` filter to the class method get_editable_roles().
	 * @since 3.37.14 Use strict comparison.
	 * @since 4.10.0 Better handling of users with multiple roles.
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/editable_roles
	 *
	 * @param array $all_roles All roles array.
	 * @return array
	 */
	public function editable_roles( $all_roles ) {

		/**
		 * Prevent issues when other plugins call get_editable_roles() before `init`.
		 *
		 * @link https://github.com/gocodebox/lifterlms/issues/1727
		 */
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			return $all_roles;
		}

		if ( is_multisite() && is_super_admin() ) {
			return $all_roles;
		}

		$user       = wp_get_current_user();
		$user_roles = $user->roles;

		if ( in_array( 'administrator', $user_roles, true ) ) {
			return $all_roles;
		}

		$editable_roles = self::get_editable_roles();

		if ( empty( array_intersect( $user_roles, array_keys( $editable_roles ) ) ) ) {
			return $all_roles;
		}

		$roles = array();
		foreach ( $user_roles as $user_role ) {
			if ( isset( $editable_roles[ $user_role ] ) ) {
				$roles = array_merge( $roles, $editable_roles[ $user_role ] );
			}
		}

		$roles = array_unique( $roles );

		foreach ( array_keys( $all_roles ) as $role ) {
			if ( ! in_array( $role, $roles, true ) ) {
				unset( $all_roles[ $role ] );
			}
		}

		return $all_roles;

	}

	/**
	 * Handle capabilities checks for lms content to allow *editing* content based on course instructor
	 *
	 * @since 3.13.0
	 *
	 * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name and boolean values
	 *                          represent whether the user has that capability.
	 * @param string[] $cap     Required primitive capabilities for the requested capability.
	 * @param array    $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type string    $0 Requested capability.
	 *     @type int       $1 Concerned user ID.
	 *     @type mixed  ...$2 Optional second and further parameters, typically object ID.
	 * }
	 * @return array
	 */
	public function edit_others_lms_content( $allcaps, $cap, $args ) {

		/**
		 * this might be a problem
		 * this happens when in wp-admin/includes/post.php
		 * when actually creating/updating a course
		 * and no post_id is passed in $args[2].
		 */
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
	 * Modify a users ability to `view_grades`
	 *
	 * Users can view the grades (quiz results) if one of the following conditions is met:
	 *   + Users can view their own grades.
	 *   + Admins and LMS Managers can view anyone's grade.
	 *   + Any user who has been explicitly granted the `view_grades` cap can view anyone's grade (via custom code).
	 *   + Any instructor/assistant who can `edit_post` for the course the quiz belongs to can view grades of the students within that course.
	 *
	 * @since 4.21.2
	 *
	 * @param bool[] $allcaps Array of key/value pairs where keys represent a capability name and boolean values
	 *                        represent whether the user has that capability.
	 * @param array  $args {
	 *   Arguments that accompany the requested capability check.
	 *
	 *     @type string $0 Requested capability: 'view_grades'.
	 *     @type int    $1 Current User ID.
	 *     @type int    $2 Requested User ID.
	 *     @type int    $3 WP_Post ID of the quiz (optional)
	 * }
	 * @return array
	 */
	private function handle_cap_view_grades( $allcaps, $args ) {

		// Logged out user or missing required args.
		if ( empty( $args[1] ) || empty( $args[2] ) ) {
			return $allcaps;
		}

		$requested_cap = $args[0];
		$current_user_id = intval( $args[1] );
		$requested_user_id = intval( $args[2] );
		$post_id = isset( $args[3] ) ? intval( $args[3] ) : false;

		// Administrators and LMS managers explicitly have the cap so we don't need to perform any further checks.
		if ( ! empty( $allcaps[ $requested_cap ] ) ) {
			return $allcaps;
		}

		// Users can view their own grades.
		if ( $current_user_id === $requested_user_id ) {
			$allcaps[ $requested_cap ] = true;
		} elseif ( $post_id && current_user_can( 'edit_post', $post_id ) ) {
			if ( $this->instructor_has_student( $current_user_id, $requested_user_id ) ) {
				$allcaps[ $requested_cap ] = true;
			}
		} elseif ( ! $post_id && current_user_can( 'view_students', $requested_user_id ) ) {
			if ( $this->instructor_has_student( $current_user_id, $requested_user_id ) ) {
				$allcaps[ $requested_cap ] = true;
			}
		}

		return $allcaps;

	}

	/**
	 * Custom capability checks for LifterLMS things
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Add logic for `edit_users` and `delete_users` capabilities with regards to LifterLMS user roles.
	 *               Add logic for `view_students`, `edit_students`, and `delete_students` capabilities.
	 * @since 3.36.5 Add `llms_user_caps_edit_others_posts_post_types` filter.
	 * @since 3.37.14 Use strict comparison.
	 * @since 4.21.2 Add logic to handle the `view_grades` capability.
	 *
	 * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name and boolean values
	 *                          represent whether the user has that capability.
	 * @param string[] $cap     Required primitive capabilities for the requested capability.
	 * @param array    $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type string    $0 Requested capability.
	 *     @type int       $1 Concerned user ID.
	 *     @type mixed  ...$2 Optional second and further parameters, typically object ID.
	 * }
	 * @return array
	 */
	public function handle_caps( $allcaps, $cap, $args ) {

		/**
		 * Modify the list of post types that users may not own but can still edit based on instructor permissions on the course
		 *
		 * @since 3.36.5
		 *
		 * @param string[] $post_types Array of unprefixed post type names.
		 */
		$post_types = apply_filters( 'llms_user_caps_edit_others_posts_post_types', array( 'courses', 'lessons', 'sections', 'quizzes', 'questions', 'memberships' ) );
		foreach ( $post_types as $cpt ) {
			// Allow any instructor to edit courses they're attached to.
			if ( in_array( sprintf( 'edit_others_%s', $cpt ), $cap, true ) ) {
				$allcaps = $this->edit_others_lms_content( $allcaps, $cap, $args );
			}
		}

		$required_cap = ! empty( $cap[0] ) ? $cap[0] : false;

		if ( 'view_grades' === $required_cap ) {
			return $this->handle_cap_view_grades( $allcaps, $args );
		}

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
	 * @since 3.41.0 Better handling of users with multiple roles.
	 *
	 * @param int $user_id WP User ID of the user requesting to perform the action.
	 * @param int $edit_id WP User ID of the user the action will be performed on.
	 * @return bool|null Returns true if the user performs the action, false if it can't, and null for core user roles which are skipped.
	 */
	protected function user_can_manage_user( $user_id, $edit_id ) {

		$user = get_user_by( 'id', $user_id );

		/**
		 * Filter the list of "ignored" user roles
		 *
		 * If a user has one of the roles specified in this list, LifterLMS
		 * will not attempt to determine if the user can manage other users
		 * and will instead allow the WordPress core (or another plugin)
		 * to determine if they have the required permissions.
		 *
		 * @since 3.41.0
		 *
		 * @param string[] $ignored Array of user roles.
		 */
		$ignored   = apply_filters( 'llms_user_can_manage_user_ignored_roles', array( 'administrator' ) );
		$lms_roles = array_keys( LLMS_Roles::get_roles() );

		$user_roles         = array_intersect( $user->roles, $lms_roles );
		$user_ignored_roles = array_intersect( $user->roles, $ignored );

		/**
		 * Skip the user because:
		 *
		 * + User has no LMS roles, eg: Administrator, Editor, or Subscriber.
		 * + User has an LMS role and a "protected" role, eg: Administrator and student.
		 *
		 * In both scenarios we will return `null` which signals that the WordPress core (or another plugin)
		 * should take care of determining if the user can manage the user.
		 */
		if ( ! $user_roles || ! empty( $user_ignored_roles ) ) {
			return null;
		}

		$edit_id = absint( $edit_id );
		$user_id = absint( $user_id );

		// Users can edit themselves.
		if ( $user_id === $edit_id ) {
			return true;
		}

		$edit_user  = get_user_by( 'id', $edit_id );
		$edit_roles = array_intersect( $edit_user->roles, $lms_roles );

		$editable_roles = self::get_editable_roles();

		foreach ( $user_roles as $role ) {

			if ( 'instructor' === $role && in_array( 'instructors_assistant', $edit_roles, true ) ) {
				$instructor = llms_get_instructor( $user );
				if ( in_array( $edit_id, array_map( 'absint', $instructor->get_assistants() ), true ) ) {
					return true;
				}
			} elseif ( ! empty( $editable_roles[ $role ] ) && array_intersect( $edit_roles, $editable_roles[ $role ] ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Determine if an instructor has a student.
	 *
	 * @since 7.6.0
	 *
	 * @param int $current_user_id WP User ID of the user requesting to perform the action.
	 * @param int $requested_user_id WP User ID of the user the action will be performed on.
	 * @return bool Returns true if the user has the student, false if it doesn't
	 */
	protected function instructor_has_student( $current_user_id, $requested_user_id )
	{

		$instructor = llms_get_instructor( $current_user_id );
		return $instructor && $instructor->has_student( $requested_user_id );

	}

}

return new LLMS_User_Permissions();
