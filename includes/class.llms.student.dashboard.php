<?php
/**
 * Retrieve data sets used by various other classes and functions
 * @since    3.0.0
 * @version  3.14.7
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Student_Dashboard {

	/**
	 * Constructor
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct() {
		add_filter( 'llms_get_endpoints', array( $this, 'add_endpoints' ) );
	}

	/**
	 * Add endpoints to the LLMS_Query class to be automatically registered
	 * @param    array     $endpoints  updated array of endpoints
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function add_endpoints( $endpoints ) {

		return array_merge( $endpoints, $this->get_endpoints() );

	}

	/**
	 * Retreive an array of all endpoint data for student dashboard endpoints
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_endpoints() {

		$endpoints = array();

		foreach ( self::get_tabs() as $var => $data ) {

			if ( empty( $data['endpoint'] ) ) {
				continue;
			}

			$endpoints[ $var ] = $data['endpoint'];

		}

		return $endpoints;

	}

	/**
	 * Get list of student's courses used for recent courses on the dashboard
	 * and all courses (paginated) on the "View Courses" endpoint
	 * @param    integer    $limit  number of courses to return
	 * @param    integer    $skip   number of courses to skip (for pagination)
	 * @return   array
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	private static function get_courses( $limit = 10, $skip = 0 ) {

		// get sorting option
		$option = get_option( 'lifterlms_myaccount_courses_in_progress_sorting', 'date,DESC' );
		// parse to order & orderby
		$option = explode( ',', $option );
		$orderby = ! empty( $option[0] ) ? $option[0] : 'date';
		$order = ! empty( $option[1] ) ? $option[1] : 'DESC';

		$student = new LLMS_Student();
		return $student->get_courses( array(
			'limit' => $limit,
			'order' => $order,
			'orderby' => $orderby,
			'skip' => $skip,
			'status' => 'enrolled',
		) );

	}

	/**
	 * Retreive the current tab when on the student dashboard
	 * @param    string     $return   type of return, either "data" for an array of data or 'slug' for just the slug
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_current_tab( $return = 'data' ) {

		global $wp;

		// set default tab
		$current_tab = apply_filters( 'llms_student_dashboard_default_tab', 'dashboard' );

		$tabs = self::get_tabs();

		foreach ( $tabs as $var => $data ) {
			if ( isset( $wp->query_vars[ $var ] ) ) {
				$current_tab = $var;
				break;
			}
		}

		if ( 'data' === $return ) {
			return $tabs[ $current_tab ];
		} else {
			return $current_tab;
		}

	}

	/**
	 * Retrieve all dashboard tabs and related data
	 * @return   array
	 * @since    3.0.0
	 * @version  3.14.7
	 */
	public static function get_tabs() {

		return apply_filters( 'llms_get_student_dashboard_tabs', array(
			'dashboard' => array(
				'content' => 'lifterlms_template_student_dashboard_home',
				'endpoint' => false,
				'nav_item' => true,
				'title' => __( 'Dashboard', 'lifterlms' ),
				'url' => llms_get_page_url( 'myaccount' ),
			),
			'view-courses' => array(
				'content' => 'lifterlms_template_student_dashboard_my_courses',
				'endpoint' => get_option( 'lifterlms_myaccount_courses_endpoint', 'my-courses' ),
				'nav_item' => true,
				'title' => __( 'My Courses', 'lifterlms' ),
			),
			'view-achievements' => array(
				'content' => 'lifterlms_template_student_dashboard_my_achievements',
				'endpoint' => get_option( 'lifterlms_myaccount_achievements_endpoint', 'my-achievements' ),
				'nav_item' => true,
				'title' => __( 'My Achievements', 'lifterlms' ),
			),
			'notifications' => array(
				'content' => array( __CLASS__, 'output_notifications_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_notifications_endpoint', 'notifications' ),
				'nav_item' => true,
				'title' => __( 'Notifications', 'lifterlms' ),
			),
			'edit-account' => array(
				'content' => array( __CLASS__, 'output_edit_account_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_edit_account_endpoint', 'edit-account' ),
				'nav_item' => true,
				'title' => __( 'Edit Account', 'lifterlms' ),
			),
			'redeem-voucher' => array(
				'content' => array( __CLASS__, 'output_redeem_voucher_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_redeem_vouchers_endpoint', 'redeem-voucher' ),
				'nav_item' => true,
				'title' => __( 'Redeem a Voucher', 'lifterlms' ),
			),
			'orders' => array(
				'content' => array( __CLASS__, 'output_orders_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_orders_endpoint', 'orders' ),
				'nav_item' => true,
				'title' => __( 'Order History', 'lifterlms' ),
			),
			'signout' => array(
				'endpoint' => false,
				'title' => __( 'Sign Out', 'lifterlms' ),
				'nav_item' => false,
				'url' => wp_logout_url( llms_get_page_url( 'myaccount' ) ),
			),
		) );

	}

	/**
	 * Callback to output View Courses endpoint content
	 * @return      void
	 * @since       3.0.0
	 * @version     3.14.0
	 * @deprecated  3.14.0
	 */
	public static function output_courses_content() {

		llms_deprecated_function( 'LLMS_Student_Dashboard::output_courses_content()', '3.14.0', 'lifterlms_template_student_dashboard_my_courses( false )' );
		lifterlms_template_student_dashboard_my_courses( false );

	}

	/**
	 * Callback to output main dashboard content
	 * @return      void
	 * @since       3.0.0
	 * @version     3.14.0
	 * @deprecated  3.14.0
	 */
	public static function output_dashboard_content() {

		llms_deprecated_function( 'LLMS_Student_Dashboard::output_dashboard_content()', '3.14.0', 'lifterlms_template_student_dashboard_home()' );
		lifterlms_template_student_dashboard_home();

	}

	/**
	 * Callback to output the edit account content
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_edit_account_content() {
		llms_get_template( 'myaccount/form-edit-account.php', array(
			'user' => get_user_by( 'id', get_current_user_id() ),
		) );
	}

	/**
	 * Callback to oupput the notifications content
	 * @return   void
	 * @since    3.8.0
	 * @version  3.9.0
	 */
	public static function output_notifications_content() {

		$url = llms_get_endpoint_url( 'notifications', '', llms_get_page_url( 'myaccount' ) );

		$sections = array(
			array(
				'url' => $url,
				'name' => __( 'View Notifications', 'lifterlms' ),
			),
			array(
				'url' => add_query_arg( 'sdview', 'prefs', $url ),
				'name' => __( 'Manage Preferences', 'lifterlms' ),
			),
		);

		$view = isset( $_GET['sdview'] ) ? $_GET['sdview'] : 'view';

		if ( 'view' === $view ) {

			$page = isset( $_GET['sdpage'] ) ? absint( $_GET['sdpage'] ) : 1;

			$notifications = new LLMS_Notifications_Query( array(
				'page' => $page,
				'per_page' => 25,
				'subscriber' => get_current_user_id(),
				'sort' => array(
					'created' => 'DESC',
					'id' => 'DESC',
				),
				'types' => 'basic',
			) );

			$pagination = array(
				'next' => $notifications->is_last_page() || ! $notifications->found_results ? '' : add_query_arg( 'sdpage', $page + 1, $url ),
				'prev' => $notifications->is_first_page() ? '' : add_query_arg( 'sdpage', $page - 1, $url ),
			);

			$args = array(
				'notifications' => $notifications->get_notifications(),
				'pagination' => $pagination,
				'sections' => $sections,
			);

		} else {

			$types = apply_filters( 'llms_notification_subscriber_manageable_types', array( 'email' ) );

			$settings = array();
			$student = new LLMS_Student( get_current_user_id() );

			foreach ( LLMS()->notifications()->get_controllers() as $controller ) {

				foreach ( $types as $type ) {

					$configs = $controller->get_subscribers_settings( $type );
					if ( in_array( 'student', array_keys( $configs ) ) && 'yes' === $configs['student'] ) {

						if ( ! isset( $settings[ $type ] ) ) {
							$settings[ $type ] = array();
						}

						$settings[ $type ][ $controller->id ] = array(
							'name' => $controller->get_title(),
							'value' => $student->get_notification_subscription( $type, $controller->id, 'yes' ),
						);
					}
				}
			}

			$args = array(
				'sections' => $sections,
				'settings' => $settings,
			);

		}// End if().

		llms_get_template( 'myaccount/my-notifications.php', $args );

	}

	/**
	 * Endpoint to output orders content
	 * @return   void
	 * @since    3.0.0
	 * @version  3.8.0
	 */
	public static function output_orders_content() {

		global $wp;

		$args = array();

		if ( ! empty( $wp->query_vars['orders'] ) ) {

			$order = new LLMS_Order( $wp->query_vars['orders'] );

			// ensure people can't locate other peoples orders by dropping numbers into the url bar
			if ( get_current_user_id() !== $order->get( 'user_id' ) ) {
				$order = false;
				$transactions = array();
			} else {
				$transactions = $order->get_transactions( array(
					'per_page' => apply_filters( 'llms_student_dashboard_transactions_per_page', 20 ),
					'paged' => isset( $_GET['txnpage'] ) ? absint( $_GET['txnpage'] ) : 1,
				) );
			}

			llms_get_template( 'myaccount/view-order.php', array(
				'order' => $order,
				'transactions' => $transactions,
			) );

		} else {

			$student = new LLMS_Student();
			llms_get_template( 'myaccount/my-orders.php', array(
				'orders' => $student->get_orders( array(
					'page' => isset( $_GET['opage'] ) ? intval( $_GET['opage'] ) : 1,
				) ),
			) );

		}

	}

	/**
	 * Callback to output content for the voucher endpoint
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_redeem_voucher_content() {

		llms_get_template( 'myaccount/form-redeem-voucher.php', array(
			'user' => get_user_by( 'id', get_current_user_id() ),
		) );

	}

}

return new LLMS_Student_Dashboard();
