<?php
/**
 * Manage WP Admin users table
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.34.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Users_Table class
 *
 * @since 3.34.0
 * @since 4.0.0 Add custom user table columns and action links.
 * @since 6.0.0 Removed the deprecated `LLMS_Admin_Users_Table::load_dependencies()` method.
 */
class LLMS_Admin_Users_Table {

	/**
	 * Date/time format used to format last login timestamps
	 *
	 * This "caches" the data on the instance so that multiple requests
	 * to get_option() / wp_cache_get() don't need to be made when outputting
	 * a user table view.
	 *
	 * @var string
	 */
	protected $login_date_format = '';

	/**
	 * Constructor.
	 *
	 * @since 3.34.0
	 * @since 4.0.0 Add custom user table columns and action links.
	 * @since 4.7.0 Remove `load_dependencies()` method hook.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'manage_users_columns', array( $this, 'add_cols' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'output_col' ), 10, 3 );

		add_filter( 'users_list_table_query_args', array( $this, 'modify_query_args' ) );
		add_filter( 'views_users', array( $this, 'modify_views' ) );

		add_filter( 'user_row_actions', array( $this, 'add_actions' ), 20, 2 );

	}

	/**
	 * Add custom actions links
	 *
	 * Outputs a "Reports" action link seen when hovering over a user in the table.
	 *
	 * @since 4.0.0
	 *
	 * @param string[] $actions Array of existing action links.
	 * @param WP_User  $user    User object.
	 * @return string[]
	 */
	public function add_actions( $actions, $user ) {
		$url                       = LLMS_Admin_Reporting::get_current_tab_url(
			array(
				'student_id' => $user->ID,
			)
		);
		$actions['llms-reporting'] = '<a href="' . esc_url( $url ) . '">' . __( 'Reports', 'lifterlms' ) . '</a>';
		return $actions;

	}

	/**
	 * Add Custom Columns to the Admin Users Table Screen
	 *
	 * @param  array $columns key=>val array of existing columns
	 *
	 * @return array $columns updated columns
	 */
	public function add_cols( $columns ) {
		$columns['llms-last-login']  = __( 'Last Login', 'lifterlms' );
		$columns['llms-enrollments'] = __( 'Enrollments', 'lifterlms' );
		return $columns;
	}

	/**
	 * Retrieve the date/time format used to display a user's last login.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_login_column_date_format() {

		if ( ! $this->login_date_format ) {
			$this->login_date_format = get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', ' h:i:s a' );
		}

		return $this->login_date_format;

	}

	/**
	 * Retrieves the output for the "enrollments" column.
	 *
	 * @since 4.0.0
	 *
	 * @param LLMS_Student $student Student object.
	 * @return string
	 */
	protected function get_enrollments_column_output( $student ) {

		$info = array();

		$types = array(
			'courses'     => __( 'Courses', 'lifterlms' ),
			'memberships' => __( 'Memberships', 'lifterlms' ),
		);

		foreach ( $types as $type => $name ) {

			$url = LLMS_Admin_Reporting::get_current_tab_url(
				array(
					'stab'       => $type,
					'student_id' => $student->get_id(),
				)
			);

			$query = call_user_func( array( $student, 'get_' . $type ), array( 'limit' => 1 ) );

			$info[] = sprintf( '%1$s: <a href="%2$s">%3$d</a>', $name, esc_url( $url ), $query['found'] );

		}

		return implode( '<br>', $info );

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

	/**
	 * Register custom columns
	 *
	 * @since 4.0.0
	 *
	 * @param string $output   Column output value to display (defaults to empty).
	 * @param string $col_name Column name/id.
	 * @param int    $user_id  WP_User ID.
	 * @return string
	 */
	public function output_col( $output, $col_name, $user_id ) {

		switch ( $col_name ) {

			case 'llms-enrollments':
				$student = llms_get_student( $user_id );
				if ( $student ) {
					$output = $this->get_enrollments_column_output( $student );
				}
				break;

			case 'llms-last-login':
				$last   = get_user_meta( $user_id, 'llms_last_login', true );
				$last   = is_numeric( $last ) ? $last : strtotime( $last );
				$output = $last ? date_i18n( $this->get_login_column_date_format(), $last ) : __( 'Never', 'lifterlms' );
				break;

		}

		return $output;

	}

}

return new LLMS_Admin_Users_Table();
