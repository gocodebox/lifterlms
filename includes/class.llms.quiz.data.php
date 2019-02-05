<?php
defined( 'ABSPATH' ) || exit;

/**
 * Query data about a quiz
 * @since    3.16.0
 * @version  3.24.0
 */
class LLMS_Quiz_Data extends LLMS_Course_Data {

	/**
	 * Constructor
	 * @param    int     $quiz_id  WP Post ID of the quiz
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function __construct( $quiz_id ) {

		$this->quiz_id = $quiz_id;
		$this->quiz = llms_get_post( $this->quiz_id );

	}

	/**
	 * Retrieve # of quiz attempts within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_attempt_count( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( id )
			FROM {$wpdb->prefix}lifterlms_quiz_attempts
			WHERE quiz_id = %d
			  AND update_date BETWEEN %s AND %s
			",
			$this->quiz_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	/**
	 * Retrieve avg grade of quiz attempts within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_average_grade( $period = 'current' ) {

		global $wpdb;

		$grade = $wpdb->get_var( $wpdb->prepare( "
			SELECT ROUND( AVG( grade ), 3 )
			FROM {$wpdb->prefix}lifterlms_quiz_attempts
			WHERE quiz_id = %d
			  AND update_date BETWEEN %s AND %s
			",
			$this->quiz_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

		return $grade ? $grade : 0;

	}

	/**
	 * Retrieve the number assignments with a given status
	 * @param    string     $status  status name
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_count_by_status( $status, $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( id )
			FROM {$wpdb->prefix}lifterlms_quiz_attempts
			WHERE quiz_id = %d
			  AND status = %s
			  AND update_date BETWEEN %s AND %s
			",
			$this->quiz_id,
			$status,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	/**
	 * Retrieve # of quiz fails within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.16.0
	 * @version  3.24.0
	 */
	public function get_fail_count( $period = 'current' ) {
		return $this->get_count_by_status( 'fail', $period );
	}

	/**
	 * Retrieve # of quiz passes within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_pass_count( $period = 'current' ) {
		return $this->get_count_by_status( 'pass', $period );
	}

	/**
	 * Retrieve recent LLMS_User_Postmeta for the quiz
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function recent_events() {

		$query = new LLMS_Query_User_Postmeta( array(
			'per_page' => 10,
			'post_id' => $this->quiz_id,
		) );

		return $query->get_metas();

	}

}
