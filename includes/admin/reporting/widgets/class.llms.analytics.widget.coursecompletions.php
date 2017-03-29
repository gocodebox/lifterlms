<?php
/**
* Course Completions analytics widget
* @since   3.5.0
* @version 3.5.3
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Coursecompletions_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	protected function get_chart_data() {
		return array(
			'type' => 'count', // type of field
			'header' => array(
				'id' => 'coursecompletions',
				'label' => __( '# of Courses Completed', 'lifterlms' ),
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
			$student_ids .= 'AND user_id IN ( ' . implode( ', ', $students ) . ' )';
		}

		$lesson_ids = '';
		$products = $this->get_posted_posts();

		if ( $products ) {
			$lesson_ids .= 'AND post_id IN ( ' . implode( ', ', $products ) . ' )';
		}

		$this->query_function = 'get_results';
		$this->output_type = OBJECT ;

		$this->query = "SELECT updated_date AS date
						FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
						JOIN {$wpdb->posts} AS p ON p.ID = upm.post_id
						WHERE
							    upm.meta_key = '_is_complete'
							AND p.post_type = 'course'
							AND upm.meta_value = 'yes'
							AND upm.updated_date BETWEEN CAST( %s AS DATETIME ) AND CAST( %s AS  DATETIME )
							{$student_ids}
							{$lesson_ids}
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
