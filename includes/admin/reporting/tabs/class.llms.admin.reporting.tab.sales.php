<?php
/**
 * Students Tab on Reporting Screen
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Reporting_Tab_Sales {

	/**
	 * Constructor
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function __construct() {

		add_action( 'llms_reporting_after_nav', array( $this, 'output_filters' ), 10, 1 );
		add_action( 'llms_reporting_content_sales', array( $this, 'output' ) );

	}

	public static function get_filter_data() {

		$data = array();

		$data['current_tab'] = LLMS_Admin_Reporting::get_current_tab();

		$data['current_range'] = LLMS_Admin_Reporting::get_current_range();

		$data['current_students'] = LLMS_Admin_Reporting::get_current_students();

		$data['current_courses'] = LLMS_Admin_Reporting::get_current_courses();

		$data['current_memberships'] = LLMS_Admin_Reporting::get_current_memberships();

		$data['dates'] = LLMS_Admin_Reporting::get_dates( $data['current_range'] );
		$data['date_start'] = $data['dates']['start'];
		$data['date_end'] = $data['dates']['end'];

		return $data;

	}

	/**
	 * Get an array of ajax widgets to load on page load
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_widget_data() {
		return apply_filters( 'llms_reporting_tab_sales_widgets', array(
			array(
				'sales' => array(
					'title' => __( '# of Sales', 'lifterlms' ),
					'cols' => '1-4',
					'content' => __( 'loading...', 'lifterlms' ),
					'info' => __( 'Number of new active or completed orders placed within this period', 'lifterlms' ),
				),
				'sold' => array(
					'title' => __( 'Net Sales', 'lifterlms' ),
					'cols' => '1-4',
					'content' => __( 'loading...', 'lifterlms' ),
					'info' => __( 'Total of all successful transactions during this period', 'lifterlms' ),
				),
				'refunds' => array(
					'title' => __( '# of Refunds', 'lifterlms' ),
					'cols' => '1-4',
					'content' => __( 'loading...', 'lifterlms' ),
					'info' => __( 'Number of orders refunded during this period', 'lifterlms' ),
				),
				'refunded' => array(
					'title' => __( 'Amount Refunded', 'lifterlms' ),
					'cols' => '1-4',
					'content' => __( 'loading...', 'lifterlms' ),
					'info' => __( 'Total of all transactions refunded during this period', 'lifterlms' ),
				),
			),
			array(
				// 'revenue' => array(
				// 	'title' => __( 'Grosse Revenue', 'lifterlms' ),
				// 	'cols' => '1-4',
				// 	'content' => __( 'loading...', 'lifterlms' ),
				// 	'info' => __( 'Total of all transactions minus all refunds processed during this period', 'lifterlms' ),
				// ),
				'coupons' => array(
					'title' => __( '# of Coupons Used', 'lifterlms' ),
					'cols' => '1-4',
					'content' => __( 'loading...', 'lifterlms' ),
					'info' => __( 'Number of orders completed using coupons during this period', 'lifterlms' ),
				),
				'discounts' => array(
					'title' => __( 'Amount of Coupons', 'lifterlms' ),
					'cols' => '1-4',
					'content' => __( 'loading...', 'lifterlms' ),
					'info' => __( 'Total amount of coupons used during this period', 'lifterlms' ),
				),
			),
		) );
	}

	/**
	 * Outupt the template for the sales tab
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function output() {

		llms_get_template( 'admin/reporting/tabs/widgets.php', array(
			'json' => json_encode( self::get_filter_data() ),
			'widget_data' => $this->get_widget_data(),
		) );

	}

	/**
	 * Outupt filters navigation
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function output_filters( $tab ) {

		if ( 'sales' === $tab ) {

			llms_get_template( 'admin/reporting/nav-filters.php', self::get_filter_data() );

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Sales();
