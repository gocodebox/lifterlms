<?php
defined( 'ABSPATH' ) || exit;

/**
 * Coupons analytics widget
 * Locates number of active / completed orders from a given date range
 * by a given group of students
 *
 * @since   3.0.0
 * @version 3.18.0
 */
class LLMS_Analytics_Coupons_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	/**
	 * Retrieve data for chart
	 *
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	protected function get_chart_data() {
		return array(
			'type'   => 'count',
			'header' => array(
				'id'    => 'coupons',
				'label' => __( '# of Coupons Used', 'lifterlms' ),
				'type'  => 'number',
			),
		);
	}

	/**
	 * Setup the query
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function set_query() {

		global $wpdb;

		$this->set_order_data_query(
			array(
				'query_function' => 'get_results',
				'select'         => array(
					'orders.post_date AS date',
				),
				'joins'          => array(
					"JOIN {$wpdb->postmeta} AS coupons ON orders.ID = coupons.post_id",
				),
				'statuses'       => array(
					'llms-active',
					'llms-completed',
				),
				'wheres'         => array(
					" AND coupons.meta_key = '_llms_coupon_used'",
					" AND coupons.meta_value = 'yes'",
				),
			)
		);

	}

	/**
	 * Format the response
	 *
	 * @return   int
	 * @since    3.0.0
	 * @version  3.18.0
	 */
	protected function format_response() {

		if ( ! $this->is_error() ) {

			return count( $this->get_results() );

		}

	}

}
