<?php
/**
 * Retrieve data sets used by various other classes and functions
 * @since  3.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Student_Dashboard {

	public function __construct() {
		add_filter( 'llms_get_endpoints', array( $this, 'add_endpoints' ) );
	}

	/**
	 * Add endpoints to the LLMS_Query class to be automatically registered
	 * @param    [type]     $endpoints  [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_endpoints( $endpoints ) {

		return array_merge( $endpoints, $this->get_endpoints() );

	}

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

	public static function get_tabs() {

		return apply_filters( 'llms_get_student_dashboard_tabs', array(
			'dashboard' => array(
				'content' => array( __CLASS__, 'output_dashboard_content' ),
				'endpoint' => false,
				'title' => __( 'Dashboard', 'lifterlms' ),
				'url' => llms_get_page_url( 'myaccount' ),
			),
			'my-courses' => array(
				'content' => array( __CLASS__, 'output_courses_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_courses_endpoint', 'my-courses' ),
				'title' => __( 'My Courses', 'lifterlms' ),
				'url' => llms_get_endpoint_url( 'my-courses', '', llms_get_page_url( 'myaccount' ) ),
			),
			'edit-account' => array(
				'content' => array( __CLASS__, 'output_edit_account_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_edit_account_endpoint', 'edit-account' ),
				'title' => __( 'Edit Account', 'lifterlms' ),
				'url' => llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ),
			),
			'redeem-voucher' => array(
				'content' => array( __CLASS__, 'output_redeem_voucher_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_redeem_vouchers_endpoint', 'redeem-voucher' ),
				'title' => __( 'Redeem a Voucher', 'lifterlms' ),
				'url' => llms_get_endpoint_url( 'redeem-voucher', '', llms_get_page_url( 'myaccount' ) ),
			),
			'orders' => array(
				'content' => array( __CLASS__, 'output_orders_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_orders_endpoint', 'orders' ),
				'title' => __( 'Order History', 'lifterlms' ),
				'url' => llms_get_endpoint_url( 'orders', '', llms_get_page_url( 'myaccount' ) ),
			),
			'signout' => array(
				'endpoint' => false,
				'title' => 'Sign Out',
				'url' => wp_logout_url( llms_get_page_url( 'myaccount' ) ),
			),
		) );

	}

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

	public static function output_dashboard_content() {

		$student = new LLMS_Student();
		$courses = $student->get_courses( array(
			'status' => 'enrolled',
			'limit' => 3,
		) );

		llms_get_template( 'myaccount/dashboard.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'student' => $student,
			'courses' => $courses,
		) );

	}

	public static function output_edit_account_content() {
		llms_get_template( 'myaccount/form-edit-account.php', array(
			'user' => get_user_by( 'id', get_current_user_id() ),
		) );
	}

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

	public static function output_redeem_voucher_content() {

		llms_get_template( 'myaccount/form-redeem-voucher.php', array(
			'user' => get_user_by( 'id', get_current_user_id() ),
		) );

	}

}

return new LLMS_Student_Dashboard();
