<?php
/**
 * Retrieve data sets used by various other classes and functions
 * @since  3.0.0
 * @version  3.0.0
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
	 * @version  3.0.4
	 */
	public static function get_tabs() {

		return apply_filters( 'llms_get_student_dashboard_tabs', array(
			'dashboard' => array(
				'content' => array( __CLASS__, 'output_dashboard_content' ),
				'endpoint' => false,
				'title' => __( 'Dashboard', 'lifterlms' ),
				'url' => llms_get_page_url( 'myaccount' ),
			),
			'view-courses' => array(
				'content' => array( __CLASS__, 'output_courses_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_courses_endpoint', 'my-courses' ),
				'title' => __( 'My Courses', 'lifterlms' ),
			),
			'edit-account' => array(
				'content' => array( __CLASS__, 'output_edit_account_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_edit_account_endpoint', 'edit-account' ),
				'title' => __( 'Edit Account', 'lifterlms' ),
			),
			'redeem-voucher' => array(
				'content' => array( __CLASS__, 'output_redeem_voucher_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_redeem_vouchers_endpoint', 'redeem-voucher' ),
				'title' => __( 'Redeem a Voucher', 'lifterlms' ),
			),
			'orders' => array(
				'content' => array( __CLASS__, 'output_orders_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_orders_endpoint', 'orders' ),
				'title' => __( 'Order History', 'lifterlms' ),
			),
			'signout' => array(
				'endpoint' => false,
				'title' => 'Sign Out',
				'url' => wp_logout_url( llms_get_page_url( 'myaccount' ) ),
			),
		) );

	}

	/**
	 * Callback to output View Courses endpoint content
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_courses_content() {

		$student = new LLMS_Student();
		$courses = $student->get_courses( array(
			'limit' => ( ! isset( $_GET['limit'] ) ) ? 10 : $_GET['limit'],
			'skip' => ( ! isset( $_GET['skip'] ) ) ? 0 : $_GET['skip'],
			'status' => 'enrolled',
		) );

		llms_get_template( 'myaccount/my-courses.php', array(
			'student' => $student,
			'courses' => $courses,
			'pagination' => $courses['more'],
		) );

	}

	/**
	 * Callback to output main dashboard content
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_dashboard_content() {

		$student = new LLMS_Student();
		$courses = $student->get_courses( array(
			'status' => 'enrolled',
			'limit' => apply_filters( 'llms_dashboard_recent_courses_count', 3 ),
		) );

		llms_get_template( 'myaccount/dashboard.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'student' => $student,
			'courses' => $courses,
		) );

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
	 * Endpoint to output orders content
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_orders_content() {

		global $wp;

		$args = array();

		if ( ! empty( $wp->query_vars['orders'] ) ) {

			$order = new LLMS_Order( $wp->query_vars['orders'] );
			// ensure people can't locate other peoples orders by dropping numbers into the url bar
			if ( get_current_user_id() !== $order->get( 'user_id' ) ) {
				$order = false;
			}

			llms_get_template( 'myaccount/view-order.php', array(
				'order' => $order,
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
