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

	public function set_query() {

		$this->set_order_data_query( array(
			'query_function' => 'get_var',
			'select' => array(
				'COUNT( orders.ID )',
			),
			'statuses' => array(
				'llms-active',
				'llms-completed',
			),
		) );

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return intval( $this->get_results() );

		}

	}

}
