<?php
/**
 * Retrieve data sets used by various other classes and functions
 *
 * @package LifterLMS/Classes
 *
 * @since 3.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Student_Dashboard class.
 *
 * @since 3.0.0
 * @since 3.28.2 Unknown.
 * @since 4.0.0 Removed deprecated methods.
 */
class LLMS_Student_Dashboard {

	/**
	 * Constructor
	 *
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	public function __construct() {

		add_filter( 'llms_get_endpoints', array( $this, 'add_endpoints' ) );
		add_filter( 'lifterlms_student_dashboard_title', array( $this, 'modify_dashboard_title' ), 5 );
		add_filter( 'rewrite_rules_array', array( $this, 'modify_rewrite_rules_order' ) );

	}

	/**
	 * Add endpoints to the LLMS_Query class to be automatically registered
	 *
	 * @param    array $endpoints  updated array of endpoints
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function add_endpoints( $endpoints ) {

		return array_merge( $endpoints, $this->get_endpoints() );

	}

	/**
	 * Retrieve an array of all endpoint data for student dashboard endpoints
	 *
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
	 *
	 * @param    integer $limit  number of courses to return
	 * @param    integer $skip   number of courses to skip (for pagination)
	 * @return   array
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	private static function get_courses( $limit = 10, $skip = 0 ) {

		// Get sorting option.
		$option = get_option( 'lifterlms_myaccount_courses_in_progress_sorting', 'date,DESC' );
		// Parse to order & orderby.
		$option  = explode( ',', $option );
		$orderby = ! empty( $option[0] ) ? $option[0] : 'date';
		$order   = ! empty( $option[1] ) ? $option[1] : 'DESC';

		$student = new LLMS_Student();
		return $student->get_courses(
			array(
				'limit'   => $limit,
				'order'   => $order,
				'orderby' => $orderby,
				'skip'    => $skip,
				'status'  => 'enrolled',
			)
		);

	}

	/**
	 * Retrieve the current tab when on the student dashboard
	 *
	 * @param    string $return   type of return, either "data" for an array of data or 'slug' for just the slug
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_current_tab( $return = 'data' ) {

		global $wp;

		// Set default tab.
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
	 *
	 * @since 3.0.0
	 * @since 3.28.2 Unknown.
	 * @since 6.0.0 Add pagination to the view-achievements and view-certificates tabs.
	 * @since 7.5.0 Add view-favorites tab.
	 *
	 * @return array
	 */
	public static function get_tabs() {

		$tabs = array(
			'dashboard'         => array(
				'content'  => 'lifterlms_template_student_dashboard_home',
				'endpoint' => false,
				'nav_item' => true,
				'title'    => __( 'Dashboard', 'lifterlms' ),
				'url'      => llms_get_page_url( 'myaccount' ),
			),
			'view-courses'      => array(
				'content'  => 'lifterlms_template_student_dashboard_my_courses',
				'endpoint' => get_option( 'lifterlms_myaccount_courses_endpoint', 'my-courses' ),
				'paginate' => true,
				'nav_item' => true,
				'title'    => __( 'My Courses', 'lifterlms' ),
			),
			'my-grades'         => array(
				'content'  => 'lifterlms_template_student_dashboard_my_grades',
				'endpoint' => get_option( 'lifterlms_myaccount_grades_endpoint', 'my-grades' ),
				'paginate' => true,
				'nav_item' => true,
				'title'    => __( 'My Grades', 'lifterlms' ),
			),
			'view-memberships'  => array(
				'content'  => 'lifterlms_template_student_dashboard_my_memberships',
				'endpoint' => get_option( 'lifterlms_myaccount_memberships_endpoint', 'my-memberships' ),
				'nav_item' => true,
				'title'    => __( 'My Memberships', 'lifterlms' ),
			),
			'view-achievements' => array(
				'content'  => 'lifterlms_template_student_dashboard_my_achievements',
				'endpoint' => get_option( 'lifterlms_myaccount_achievements_endpoint', 'my-achievements' ),
				'paginate' => true,
				'nav_item' => true,
				'title'    => __( 'My Achievements', 'lifterlms' ),
			),
			'view-certificates' => array(
				'content'  => 'lifterlms_template_student_dashboard_my_certificates',
				'endpoint' => get_option( 'lifterlms_myaccount_certificates_endpoint', 'my-certificates' ),
				'paginate' => true,
				'nav_item' => true,
				'title'    => __( 'My Certificates', 'lifterlms' ),
			),
			'notifications'     => array(
				'content'  => 'lifterlms_template_student_dashboard_my_notifications',
				'endpoint' => get_option( 'lifterlms_myaccount_notifications_endpoint', 'notifications' ),
				'paginate' => true,
				'nav_item' => true,
				'title'    => __( 'Notifications', 'lifterlms' ),
			),
			'edit-account'      => array(
				'content'  => array( __CLASS__, 'output_edit_account_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_edit_account_endpoint', 'edit-account' ),
				'nav_item' => true,
				'title'    => __( 'Edit Account', 'lifterlms' ),
			),
			'redeem-voucher'    => array(
				'content'  => array( __CLASS__, 'output_redeem_voucher_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_redeem_vouchers_endpoint', 'redeem-voucher' ),
				'nav_item' => true,
				'title'    => __( 'Redeem a Voucher', 'lifterlms' ),
			),
			'orders'            => array(
				'content'  => array( __CLASS__, 'output_orders_content' ),
				'endpoint' => get_option( 'lifterlms_myaccount_orders_endpoint', 'orders' ),
				'nav_item' => true,
				'title'    => __( 'Order History', 'lifterlms' ),
			),
			'signout'           => array(
				'endpoint' => false,
				'title'    => __( 'Sign Out', 'lifterlms' ),
				'nav_item' => false,
				'url'      => wp_logout_url( llms_get_page_url( 'myaccount' ) ),
			),
		);

		if ( llms_is_favorites_enabled() ) {
			$tabs = llms_assoc_array_insert(
				$tabs,
				'view-certificates',
				'view-favorites',
				array(
					'content'  => 'llms_template_student_dashboard_my_favorites',
					'endpoint' => get_option( 'lifterlms_myaccount_favorites_endpoint', 'my-favorites' ),
					'paginate' => true,
					'nav_item' => true,
					'title'    => __( 'My Favorites', 'lifterlms' ),
				)
			);
		}

		return apply_filters(
			'llms_get_student_dashboard_tabs',
			$tabs
		);

	}

	/**
	 * Retrieve dashboard tab data as required to display navigation links
	 * Excludes any endpoint disabled by deleting the slug from account settings
	 *
	 * @return   array
	 * @since    3.17.5
	 * @version  3.17.5
	 */
	public static function get_tabs_for_nav() {

		$tabs = array();

		foreach ( self::get_tabs() as $var => $data ) {

			if ( isset( $data['url'] ) ) {
				$url = $data['url'];
			} elseif ( ! empty( $data['endpoint'] ) ) {
				$url = llms_get_endpoint_url( $var, '', llms_get_page_url( 'myaccount' ) );
			} else {
				continue;
			}

			$tabs[ $var ] = array(
				'url'   => $url,
				'title' => $data['title'],
			);

		}

		return apply_filters( 'llms_get_student_dashboard_tabs_for_nav', $tabs );

	}

	/**
	 * Determine if an endpoint is disabled
	 * If the custom endpoint option is an empty string (blank) the settings define the endpoint as disabled
	 *
	 * @param    string $endpoint  endpoint slug (eg: my-courses)
	 * @return   bool
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public static function is_endpoint_enabled( $endpoint ) {

		$tabs = self::get_tabs();
		if ( isset( $tabs[ $endpoint ] ) && ! empty( $tabs[ $endpoint ]['endpoint'] ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Handle modification of the default dashboard title for certain pages and sub pages
	 *
	 * @param    string $title  default title HTML
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function modify_dashboard_title( $title ) {

		global $wp_query;
		$tab = self::get_current_tab( 'tab' );

		if ( 'my-grades' === $tab && ! empty( $wp_query->query['my-grades'] ) ) {

			$course = get_posts(
				array(
					'name'      => $wp_query->query['my-grades'],
					'post_type' => 'course',
				)
			);

			$course = array_shift( $course );
			if ( $course ) {

				$data = self::get_current_tab();

				$new_title  = '<a href="' . esc_url( llms_get_endpoint_url( 'my-grades' ) ) . '">' . $data['title'] . '</a>';
				$new_title .= sprintf( ' %1$s <a href="%2$s">%3$s</a>', apply_filters( 'llms_student_dashboard_title_separator', '<small>&gt;</small>' ), get_permalink( $course->ID ), get_the_title( $course->ID ) );

				$title = str_replace( $data['title'], $new_title, $title );

			}
		}

		return $title;

	}

	public function modify_rewrite_rules_order( $rules ) {
		return $rules;
	}

	/**
	 * Callback to output the edit account content
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_edit_account_content() {
		llms_get_template(
			'myaccount/form-edit-account.php',
			array(
				'user' => get_user_by( 'id', get_current_user_id() ),
			)
		);
	}

	/**
	 * Endpoint to output orders content
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Unknown.
	 * @since 6.0.0 Use `llms_template_view_order()` in favor of including the template file directly.
	 *
	 * @return void
	 */
	public static function output_orders_content() {

		global $wp;

		$args = array();

		if ( ! empty( $wp->query_vars['orders'] ) ) {

			$order = llms_get_post( $wp->query_vars['orders'] );
			llms_template_view_order( $order );

		} else {

			$student = new LLMS_Student();
			llms_get_template(
				'myaccount/my-orders.php',
				array(
					'orders' => $student->get_orders(
						array(
							'page' => isset( $_GET['opage'] ) ? intval( $_GET['opage'] ) : 1,
						)
					),
				)
			);

		}

	}

	/**
	 * Callback to output content for the voucher endpoint
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_redeem_voucher_content() {

		llms_get_template(
			'myaccount/form-redeem-voucher.php',
			array(
				'user' => get_user_by( 'id', get_current_user_id() ),
			)
		);

	}

}

return new LLMS_Student_Dashboard();
