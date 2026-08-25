<?php
/**
 * LLMS_Admin_Customers class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Customers screen under Orders.
 *
 * @since [version]
 */
class LLMS_Admin_Customers {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'llms_reporting_single_student_overview_after_widgets', array( $this, 'output_student_commerce_teaser' ) );
	}

	/**
	 * Output LTV / orders teaser widgets on the Students reporting overview.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Student $student Student instance.
	 * @return void
	 */
	public function output_student_commerce_teaser( $student ) {

		if ( ! $student || ! current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_lifterlms' ) ) ) {
			return;
		}

		if ( ! llms_is_customer( $student->get_id() ) ) {
			return;
		}

		$metrics = llms_get_customer_metrics( $student->get_id() );
		$url     = llms_get_customers_admin_url( $student->get_id() );

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of2',
				'icon'      => 'money',
				'id'        => 'llms-reporting-student-ltv',
				'data'      => $metrics['ltv'],
				'data_type' => 'monetary',
				'text'      => sprintf(
					/* translators: %s: link to customer screen */
					__( 'Lifetime value %s', 'lifterlms' ),
					'<a href="' . esc_url( $url ) . '">(' . esc_html__( 'view customer', 'lifterlms' ) . ')</a>'
				),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols' => 'd-1of2',
				'icon' => 'shopping-cart',
				'id'   => 'llms-reporting-student-order-count',
				'data' => $metrics['order_count'],
				'text' => sprintf(
					/* translators: %s: link to customer screen */
					__( 'Orders %s', 'lifterlms' ),
					'<a href="' . esc_url( $url ) . '">(' . esc_html__( 'view customer', 'lifterlms' ) . ')</a>'
				),
			)
		);
	}

	/**
	 * Output the Customers admin page.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function output() {

		if ( ! current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_lifterlms' ) ) ) {
			wp_die( esc_html__( 'You do not have permission to access customers.', 'lifterlms' ) );
		}

		$customer_id = llms_filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );

		if ( $customer_id ) {
			self::output_single( absint( $customer_id ) );
			return;
		}

		self::output_list();
	}

	/**
	 * Output the customers list view.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected static function output_list() {

		$segment  = llms_filter_input_sanitize_string( INPUT_GET, 'segment' );
		$segments = llms_get_customer_segments();
		if ( ! $segment || ! isset( $segments[ $segment ] ) ) {
			$segment = 'all';
		}

		llms_get_template(
			'admin/customers/list.php',
			array(
				'current_segment' => $segment,
				'segments'        => $segments,
			)
		);
	}

	/**
	 * Output a single customer overview.
	 *
	 * @since [version]
	 *
	 * @param int $customer_id WP user ID.
	 * @return void
	 */
	protected static function output_single( $customer_id ) {

		$student = llms_get_student( $customer_id );
		if ( ! $student || ! llms_is_customer( $customer_id ) ) {
			wp_die( esc_html__( 'This customer does not exist.', 'lifterlms' ) );
		}

		$metrics = llms_get_customer_metrics( $customer_id );
		$stab    = llms_filter_input_sanitize_string( INPUT_GET, 'stab' );
		if ( ! in_array( $stab, array( 'overview', 'orders' ), true ) ) {
			$stab = 'overview';
		}

		$orders_result = $student->get_orders(
			array(
				'count' => ( 'orders' === $stab ) ? 50 : 10,
				'page'  => max( 1, absint( llms_filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) ) ),
			)
		);

		$latest_order = ! empty( $orders_result['orders'] ) ? $orders_result['orders'][0] : null;

		llms_get_template(
			'admin/customers/customer.php',
			array(
				'student'       => $student,
				'metrics'       => $metrics,
				'current_tab'   => $stab,
				'orders_result' => $orders_result,
				'latest_order'  => $latest_order,
			)
		);
	}
}

return new LLMS_Admin_Customers();
