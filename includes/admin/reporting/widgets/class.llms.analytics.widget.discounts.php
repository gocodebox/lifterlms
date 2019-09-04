<?php
/**
 * Total amount of coupon discount savings
 *
 * Totals all coupon discounts applied to orders in the given filters
 *
 * @since  3.0.0
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Analytics_Discounts_Widget extends LLMS_Analytics_Widget {

	public function set_query() {

		global $wpdb;

		$this->set_order_data_query(
			array(
				'query_function' => 'get_var',
				'select'         => array(
					'SUM( cp_val.meta_value )',
				),
				'joins'          => array(
					"JOIN {$wpdb->postmeta} AS cp ON orders.ID = cp.post_id",
					"JOIN {$wpdb->postmeta} AS cp_val ON orders.ID = cp_val.post_id",
				),
				'statuses'       => array(
					'llms-active',
					'llms-completed',
				),
				'wheres'         => array(
					" AND cp.meta_key = '_llms_coupon_used'",
					" AND cp.meta_value = 'yes'",
					" AND cp_val.meta_key = '_llms_coupon_value'",
				),
			)
		);

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return llms_price_raw( floatval( $this->get_results() ) );

		}

	}

}
