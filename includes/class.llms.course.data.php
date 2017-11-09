<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Query data about a course
 * @since    [version]
 * @version  [version]
 */
class LLMS_Course_Data {

	private $dates = array();

	/**
	 * Constructor
	 * @param    int     $course_id  WP Post ID of the course
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct( $course_id ) {

		$this->course_id = $course_id;
		$this->course = llms_get_post( $this->course_id );

	}

	/**
	 * Allow dates and timestamps to be passed into various data functions
	 * @param    mixed     $date  date string or timestamp
	 * @return   int
	 * @since    [version]
	 * @version  [version]
	 */
	private function strtotime( $date ) {
		if ( ! is_numeric( $date ) ) {
			$date = date( 'U', strtotime( $date ) );
		}
		return $date;
	}

	/**
	 * Retrieve an array of all post ids in the course
	 * Includes course id, all section ids, all lesson ids, and all quiz ids
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_all_ids() {
		return array_merge(
			array( $this->course_id ),
			$this->course->get_sections( 'ids' ),
			$this->course->get_lessons( 'ids' ),
			$this->course->get_quizzes()
		);
	}

	private function get_date( $period, $date ) {

		return date( 'Y-m-d H:i:s', $this->dates[ $period ][ $date ] );

	}

	public function set_period( $period = 'today' ) {

		$now = current_time( 'timestamp' );

		switch ( $period ) {

			case 'last_year':
				$curr_start = strtotime( 'first day of january last year', $now );
				$curr_end = strtotime( 'last day of december last year', $now );

				$prev_start = strtotime( 'first day of january last year', $curr_start );
				$prev_end = strtotime( 'last day of december last year', $curr_start );
			break;

			case 'year':
				$curr_start = strtotime( 'first day of january this year', $now );
				$curr_end = strtotime( 'last day of december this year', $now );

				$prev_start = strtotime( 'first day of january last year', $now );
				$prev_end = strtotime( 'last day of december last year', $now );
			break;

			case 'last_month':
				$curr_start = strtotime( 'first day of previous month', $now );
				$curr_end = strtotime( 'last day of previous month', $now );

				$prev_start = strtotime( 'first day of previous month', $curr_start );
				$prev_end = strtotime( 'last day of previous month', $curr_start );
			break;

			case 'month':
				$curr_start = strtotime( 'first day of this month', $now );
				$curr_end = strtotime( 'last day of this month', $now );

				$prev_start = strtotime( 'first day of previous month', $now );
				$prev_end = strtotime( 'last day of previous month', $now );
			break;

			case 'last_week':
				$curr_start = strtotime( 'monday this week', $now - WEEK_IN_SECONDS );
				$curr_end = $now;

				$prev_start = strtotime( 'monday previous week', $curr_start - WEEK_IN_SECONDS );
				$prev_end = $curr_start - DAY_IN_SECONDS;
			break;

			case 'week':
				$curr_start = strtotime( 'monday this week', $now );
				$curr_end = $now;

				$prev_start = strtotime( 'monday previous week', $now );
				$prev_end = $curr_start - DAY_IN_SECONDS;
			break;

			case 'yesterday':
				$curr_start = $now - DAY_IN_SECONDS;
				$curr_end = $curr_start;

				$prev_start = $curr_start - DAY_IN_SECONDS;
				$prev_end = $prev_start;
			break;

			case 'today':
			default:

				$curr_start = $now;
				$curr_end = $now;

				$prev_start = $now - DAY_IN_SECONDS;
				$prev_end = $prev_start;

		}// End switch().

		$this->dates = array(
			'current' => array(
				'start' => strtotime( 'midnight', $curr_start ),
				'end' => strtotime( 'tomorrow', $curr_end ) - 1,
			),
			'previous' => array(
				'start' => strtotime( 'midnight', $prev_start ),
				'end' => strtotime( 'tomorrow', $prev_end ) - 1,
			),
		);

	}


	public function get_completions( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	public function get_enrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_start_date'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	public function get_engagements( $type, $period = 'current' ) {

		global $wpdb;

		$ids = implode( ',', $this->get_all_ids() );

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_key = %s
			  AND post_id IN ( {$ids} )
			  AND updated_date BETWEEN %s AND %s
			",
			'_' . $type,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	public function get_lesson_completions( $period = 'current' ) {

		global $wpdb;

		$lessons = implode( ',', $this->course->get_lessons( 'ids' ) );

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( * )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id IN ( {$lessons} )
			  AND updated_date BETWEEN %s AND %s
			",
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}


	/**
	 * Retrieve the number of unenrollments on a given date
	 * @param    mixed     $start  date string or timestamp
	 * @param    mixed     $end    date string or timestamp
	 * @return   int
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_unenrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value != 'enrolled'
			  AND meta_key = '_status'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}


	public function recent_events() {

		global $wpdb;

		$ids = implode( ',', $this->get_all_ids() );

		return $wpdb->get_results( "
			SELECT *
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE post_id IN ( {$ids} )
			  AND meta_key IN ( '_is_complete', '_status' )
			ORDER BY updated_date DESC
			LIMIT 10;"
		);

	}

}
