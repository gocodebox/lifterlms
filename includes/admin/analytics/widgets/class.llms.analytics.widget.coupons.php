<?php
/**
* Coupons analytics widget
*
* Locates number of active / completed orders from a given date range
* by a given group of students
*
* @since  3.0.0
* @version 3.0.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Coupons_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	protected function get_chart_data() {
		return array(
			'type' => 'count',
			'header' => array(
				'id' => 'coupons',
				'label' => __( '# of Coupons Used', 'lifterlms' ),
				'type' => 'number',
			),
		);
	}

	public function set_query() {

		global $wpdb;

		$this->set_order_data_query( array(
			'query_function' => 'get_results',
			'select' => array(
				'orders.post_date AS date',
			),
			'joins' => array(
				"JOIN {$wpdb->postmeta} AS coupons ON orders.ID = coupons.post_id"
			),
			'statuses' => array(
				'llms-active',
				'llms-completed',
			),
			'wheres' => array(
				" AND coupons.meta_key = '_llms_coupon_used'",
				" AND coupons.meta_value = 'yes'",
			),
		) );

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return intval( $this->get_results() );

		}

	}

}
