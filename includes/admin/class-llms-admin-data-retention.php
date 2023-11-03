<?php
/**
 * LLMS_Admin_Data_Retention class
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles data retention for Pending Orders and Inactive accounts.
 *
 * @since [version]
 */
class LLMS_Admin_Data_Retention {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'llms_delete_pending_orders', array( $this, 'llms_delete_pending_orders' ) );
		add_action( 'llms_delete_inactive_accounts', array( $this, 'llms_delete_inactive_accounts' ) );

	}

	/**
	 * Deletes all pending orders after held duration.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function llms_delete_pending_orders() {

		$days = get_option( 'lifterlms_pending_orders_deletion' );

		if ( ! $days ) {
			return;
		}

		// Fetching llms_orders with post_status `llms-pending` created more than $days ago.
		$pending_orders = get_posts(
			array(
				'post_type'      => 'llms_order',
				'post_status'    => 'llms-pending',
				'posts_per_page' => -1, // to get all posts.
				'date_query'     => array(
					array(
						'before' => $days . ' days ago',
					),
				),
			)
		);

		// Delete the pending orders.
		foreach ( $pending_orders as $order ) {
			wp_delete_post( $order->ID, true );
		}

	}

	/**
	 * Deletes all inactive accounts without any enrollments after held duration.
	 *
	 * @since [version]
	 *
	 * @return null
	 */
	public function llms_delete_inactive_accounts() {

		$days = get_option( 'lifterlms_inactive_accounts_deletion' );

		if ( ! $days ) {
			return;
		}

		// Get all users with role `student` created more than $days ago.
		$users = get_users(
			array(
				'role'          => 'student',
				'number'        => -1, // to get all users.
				'date_query'    => array(
					array(
						'before' => $days . ' days ago',
					),
				),
				'count_total'   => false,
				'fields'        => 'ID',
				'no_found_rows' => true,
			)
		);

		// Check if users has any enrollments (courses or memberships).
		$inactive_users = array_filter(
			$users,
			function( $user ) {

				$student     = llms_get_student( $user );
				$enrollments = $student->get_courses( array( 'limit' => 1 ) )['results'] || $student->get_memberships( array( 'limit' => 1 ) )['results'];

				if ( ! $enrollments ) {
					return $user;
				}

			}
		);

		// Delete the inactive users.
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $inactive_users as $user ) {
			wp_delete_user( $user );
		}

	}

}

return new LLMS_Admin_Data_Retention();
