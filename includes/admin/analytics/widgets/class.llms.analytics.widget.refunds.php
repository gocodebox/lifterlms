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

	public function set_query() {

		$this->set_order_data_query( array(
			'date_field' => 'post_modified',
			'query_function' => 'get_var',
			'select' => array(
				'COUNT( orders.ID )',
			),
			'statuses' => array(
				'llms-refunded',
			),
		) );

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return intval( $this->get_results() );

		}

	}

}
