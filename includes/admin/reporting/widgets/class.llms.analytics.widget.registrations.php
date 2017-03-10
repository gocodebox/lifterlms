<?php
/**
* Registrations analytics widget
* @since   3.5.0
* @version 3.5.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Registrations_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	protected function get_chart_data() {
		return array(
			'type' => 'count', // type of field
			'header' => array(
				'id' => 'registrations',
				'label' => __( '# of Registrations', 'lifterlms' ),
				'type' => 'number',
			),
		);
	}

	public function set_query() {

		global $wpdb;

		$dates = $this->get_posted_dates();

		$student_ids = '';
		$students = $this->get_posted_students();
		if ( $students ) {
			$student_ids .= 'AND ID IN ( ' . implode( ', ', $students ) . ' )';
		}

		$this->query_function = 'get_results';
		$this->output_type = OBJECT ;

		$this->query = "SELECT user_registered AS date
						FROM {$wpdb->users}
						WHERE
							user_registered BETWEEN CAST( %s AS DATETIME ) AND CAST( %s AS  DATETIME )
							{$student_ids}
						;";

		$this->query_vars = array(
			$this->format_date( $dates['start'], 'start' ),
			$this->format_date( $dates['end'], 'end' ),
		);

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return count( $this->get_results() );

		}

	}

}
