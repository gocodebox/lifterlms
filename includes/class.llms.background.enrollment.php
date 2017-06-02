<?php
/**
 * Class to handle enrollment of a large number of students into a course or membership
 * @since    3.4.0
 * @version  3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Background_Enrollment extends WP_Background_Process {

	protected $action = 'llms_background_enrollment';

	/**
	 * Process items in the queueu
	 * return false to remove the item from the queue after processing
	 * @param    array     $item  array of processing data
	 * @return   boolean
	 * @since    3.4.0
	 * @version  3.9.0
	 */
	protected function task( $item ) {

		// ensure the item has all the data we need to process it
		if ( ! is_array( $item ) || ! isset( $item['enroll_into_id'] ) || ! isset( $item['query_args'] ) || ! isset( $item['trigger'] ) ) {
			return false;
		}

		$query = new LLMS_Student_Query( $item['query_args'] );

		if ( $query->found_results ) {
			foreach ( $query->get_students() as $student ) {
				$student->enroll( $item['enroll_into_id'], $item['trigger'] );
			}
		}

		return false;

	}

}

