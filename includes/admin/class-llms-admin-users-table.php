<?php
/**
 * Manage WP Admin users table.
 *
 * @package  LifterLMS/Admin/Classes
 *
 * @since 3.34.0
 * @version 3.34.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Users_Table class.
 *
 * @since 3.34.0
 */
class LLMS_Admin_Users_Table {

	/**
	 * Constructor.
	 *
	 * @since 3.34.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'users_list_table_query_args', array( $this, 'modify_query_args' ) );
		add_filter( 'views_users', array( $this, 'modify_views' ) );

	}

	/**
	 * Modify the query arguments of the users table query.
	 *
	 * If the current user is an instructor and no `role` argument is provided will limit the query to users
	 * with the `instructors_assistant` and `instructor` roles.
	 *
	 * @since 3.34.0
	 *
	 * @param array $args Array of arguments to be passed to a WP_User_Query.
	 * @return array
	 */
	public function modify_query_args( $args ) {

		if ( LLMS_User_Permissions::is_current_user_instructor() && empty( $args['role'] ) ) {
			$args['role__in'] = array( 'instructors_assistant', 'instructor' );
		}

		return $args;
	}

	/**
	 * Modify the list of role "view" filter links at the top of the user table.
	 *
	 * An instructor can only manage instructors and instructor's assistants so we'll remove these links from the list
	 * and additionally modify the count on the "All" filter to reflect the total number of users who are visible
	 * to the current instructor.
	 *
	 * @since 3.34.0
	 *
	 * @param array $views Associative array of views where the key is the role name and the value is the HTML for the view link.
	 * @return array
	 */
	public function modify_views( $views ) {

		if ( LLMS_User_Permissions::is_current_user_instructor() ) {

			$users = count_users();

			// Allow the instructor to see roles they're allowed to edit.
			$all_roles = LLMS_User_Permissions::get_editable_roles();
			$roles     = array_merge( array( 'all', 'instructor' ), $all_roles['instructor'] );

			$all = 0;

			foreach ( array_keys( $views ) as $view ) {
				if ( ! in_array( $view, $roles, true ) ) {
					// Unset any views they're not allowed to edit.
					unset( $views[ $view ] );
				} elseif ( ! empty( $users['avail_roles'][ $view ] ) ) {
					// Add roles they're allowed to view to the new all count.
					$all += $users['avail_roles'][ $view ];
				}
			}

			// Replace the count on the "All" link with our updated count.
			$format       = '<span class="count">(%s)</span>';
			$views['all'] = str_replace( sprintf( $format, $users['total_users'] ), sprintf( $format, $all ), $views['all'] );

		}

		return $views;
	}

}

return new LLMS_Admin_Users_Table();
