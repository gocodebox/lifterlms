<?php
/**
* Sales analytics widget
*
* Locates number of active / completed orders from a given date range
* by a given group of students
*
* @since  3.0.0
* @version 3.0.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Sales_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	protected function get_chart_data() {
		return array(
			'type' => 'count',
			'header' => array(
				'id' => 'sales',
				'label' => __( '# of Sales', 'lifterlms' ),
				'type' => 'number',
			),
		);
	}

	public function set_query() {

		$this->set_order_data_query( array(
			'query_function' => 'get_results',
			'select' => array(
				'orders.post_date AS date',
			),
			'statuses' => array(
				'llms-active',
				'llms-completed',
			),
		) );

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return count( $this->get_results() );

		}

	}

}
