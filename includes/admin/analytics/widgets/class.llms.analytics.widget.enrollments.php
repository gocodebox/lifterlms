<?php
/**
* Analytics Widget Abstract
*
* @author codeBOX
* @project LifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Enrollments_Widget extends LLMS_Analytics_Widget {

	public function set_query() {

		global $wpdb;

		$dates = $this->get_posted_dates();
		$students = $this->get_posted_students();

		$student_ids = '';
		if ( $students ) {
			$student_ids .= 'AND user_id IN ( ' . implode( ', ', $students ) . ' )';
		}

		$this->query_function = 'get_var';

		$this->query = "SELECT COUNT(meta_id)
						FROM {$wpdb->prefix}lifterlms_user_postmeta
						WHERE
							    meta_key = '_status'
							AND meta_value = 'Enrolled'
							AND updated_date BETWEEN CAST( %s AS DATE ) AND CAST( %s AS  DATE )
							{$student_ids}
						;";

		$this->query_vars = array(
			$this->format_date( $dates['start'], 'start' ),
			$this->format_date( $dates['end'], 'end' ),
		);

	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return intval( $this->get_results() );

		}

	}

}
