<?php
/**
* Refunds analytics widget
*
* Locates number of refunded orders from a given date range
* by a given group of students
*
* Uses "post_modified" rather than "post_date" for date query
*
* @since  3.0.0
* @version 3.0.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Refunds_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	protected function get_chart_data() {
		return array(
			'type' => 'count',
			'header' => array(
				'id' => 'refunds',
				'label' => __( '# of Refunds', 'lifterlms' ),
				'type' => 'number',
			),
		);
	}

	public function set_query() {

		$this->set_order_data_query( array(
			'date_field' => 'post_modified',
			'query_function' => 'get_results',
			'select' => array(
				'orders.post_modified AS date',
			),
			'statuses' => array(
				'llms-refunded',
			),
		) );

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return count( $this->get_results() );

		}

	}

}
