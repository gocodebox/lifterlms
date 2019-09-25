<?php
/**
 * Query session data for a given student.
 *
 * @package  LifterLMS/Reporting/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Student_Session_Data class.
 *
 * @since [version]
 */
class LLMS_Student_Session_Data extends LLMS_Abstract_Student_Data {

	public function get_sessions( $period = 'current' ) {

		$query = new LLMS_Events_Query( array(
			'actor'         => $this->get_student_id(),
			'date_after'    => $this->get_date( $period, 'start'),
			'date_before'   => $this->get_date( $period, 'end' ),
			'object_type'   => 'session',
			'event_type'    => 'session',
			'event_action'  => 'start',
			'per_page'      => 1,
		) );

		return $query->found_results;

	}

}
