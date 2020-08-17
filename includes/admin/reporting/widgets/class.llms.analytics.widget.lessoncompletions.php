<?php
/**
 * Lesson Completions analytics widget
 *
 * @package LifterLMS/Admin/Reporting/Widgets/Classes
 *
 * @since 3.5.0
 * @version 3.5.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Analytics_Lessoncompletions_Widget class
 *
 * @since 3.5.0
 * @since 3.5.3 Unknown.
 */
class LLMS_Analytics_Lessoncompletions_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	protected function get_chart_data() {
		return array(
			'type'   => 'count', // Type of field.
			'header' => array(
				'id'    => 'lessoncompletions',
				'label' => __( '# of Lessons Completed', 'lifterlms' ),
				'type'  => 'number',
			),
		);
	}

	/**
	 * Retrieve an array of lesson ids for all the products in the current filter
	 *
	 * @param    array $products  array of product ids
	 * @return   array
	 * @since    3.5.0
	 * @version  3.5.0
	 */
	private function get_lesson_ids( $products ) {

		$lessons = array();

		// Loop through all products.
		foreach ( $products as $product ) {

			// Ignore the memberships.
			if ( 'llms_membership' === get_post_type( $product ) ) {
				continue;
			}

			// Get the course.
			$course  = llms_get_post( $product );
			$lessons = array_merge( $course->get_lessons( 'ids' ) );

		}

		return $lessons;

	}

	public function set_query() {

		global $wpdb;

		$dates = $this->get_posted_dates();

		$student_ids = '';
		$students    = $this->get_posted_students();
		if ( $students ) {
			$student_ids .= 'AND user_id IN ( ' . implode( ', ', $students ) . ' )';
		}

		$lesson_ids = '';
		$products   = $this->get_posted_posts();

		if ( $products ) {
			$lesson_ids .= 'AND post_id IN ( ' . implode( ', ', $this->get_lesson_ids( $products ) ) . ' )';
		}

		$this->query_function = 'get_results';
		$this->output_type    = OBJECT;

		$this->query = "SELECT updated_date AS date
						FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
						JOIN {$wpdb->posts} AS p ON p.ID = upm.post_id
						WHERE
							    upm.meta_key = '_is_complete'
							AND p.post_type = 'lesson'
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
