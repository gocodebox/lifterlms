<?php
/**
 * Query data about a quiz
 *
 * @since 3.16.0
 * @version 3.31.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query data about a quiz
 *
 * @since 3.16.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 3.31.0 Extends LLMS_Abstract_Post_Data.
 */
class LLMS_Quiz_Data extends LLMS_Abstract_Post_Data {

	/**
	 * Quiz object.
	 *
	 * @since 3.16.0
	 * @deprecated 3.31.0 Use $this->post instead.
	 *
	 * @var LLMS_Quiz
	 */
	public $quiz;

	/**
	 * WP Post ID of the quiz
	 *
	 * @since 3.16.0
	 * @deprecated 3.31.0 Use $this->post_id instead.
	 *
	 * @var int
	 */
	public $quiz_id;

	/**
	 * Constructor
	 *
	 * @since    3.16.0
	 *
	 * @param    int $quiz_id  WP Post ID of the quiz
	 */
	public function __construct( $quiz_id ) {

		$this->quiz_id = $quiz_id;
		$this->quiz    = llms_get_post( $this->quiz_id );
		parent::__construct( $quiz_id );

	}

	/**
	 * Retrieve # of quiz attempts within the period
	 *
	 * @since    3.16.0
	 *
	 * @param    string $period  date period [current|previous]
	 * @return   int
	 */
	public function get_attempt_count( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( id )
			FROM {$wpdb->prefix}lifterlms_quiz_attempts
			WHERE quiz_id = %d
			  AND update_date BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

	}

	/**
	 * Retrieve avg grade of quiz attempts within the period
	 *
	 * @since    3.16.0
	 *
	 * @param    string $period  date period [current|previous]
	 * @return   int
	 */
	public function get_average_grade( $period = 'current' ) {

		global $wpdb;

		$grade = $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT ROUND( AVG( grade ), 3 )
			FROM {$wpdb->prefix}lifterlms_quiz_attempts
			WHERE quiz_id = %d
			  AND update_date BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

		return $grade ? $grade : 0;

	}

	/**
	 * Retrieve the number assignments with a given status
	 *
	 * @since    3.24.0
	 *
	 * @param    string $status  status name
	 * @param    string $period  date period [current|previous]
	 * @return   int
	 */
	public function get_count_by_status( $status, $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( id )
			FROM {$wpdb->prefix}lifterlms_quiz_attempts
			WHERE quiz_id = %d
			  AND status = %s
			  AND update_date BETWEEN %s AND %s
			",
				$this->post_id,
				$status,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

	}

	/**
	 * Retrieve # of quiz fails within the period
	 *
	 * @since    3.16.0
	 *
	 * @param    string $period  date period [current|previous]
	 * @return   int
	 */
	public function get_fail_count( $period = 'current' ) {
		return $this->get_count_by_status( 'fail', $period );
	}

	/**
	 * Retrieve # of quiz passes within the period
	 *
	 * @since    3.16.0
	 *
	 * @param    string $period  date period [current|previous]
	 * @return   int
	 */
	public function get_pass_count( $period = 'current' ) {
		return $this->get_count_by_status( 'pass', $period );
	}

	/**
	 * Retrieve recent LLMS_User_Postmeta for the quiz.
	 * This overrides the LLMS_Abstract_Post_Data method.
	 *
	 * @since    3.16.0
	 *
	 * @return   array
	 */
	public function recent_events( $args = array() ) {

		$query_args = wp_parse_args(
			$args,
			array(
				'types' => array(),
			)
		);

		return parent::recent_events( $query_args );
	}
}
