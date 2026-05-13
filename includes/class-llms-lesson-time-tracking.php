<?php
/**
 * Lesson Time Tracking service
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Lesson_Time_Tracking class
 *
 * Service class for lesson time tracking operations including session
 * management, cached time totals, and admin overrides.
 *
 * @since [version]
 */
class LLMS_Lesson_Time_Tracking {

	use LLMS_Trait_Singleton;

	/**
	 * Find a session by its token.
	 *
	 * @since [version]
	 *
	 * @param string $token Session token.
	 * @return LLMS_Lesson_Time_Session|false Session object or false if not found.
	 */
	public function find_by_token( $token ) {

		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}lifterlms_lesson_time_sessions WHERE session_token = %s LIMIT 1",
				$token
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return false;
		}

		$session = new LLMS_Lesson_Time_Session( absint( $row['id'] ) );
		$session->setup( $row );
		return $session;
	}

	/**
	 * Expire all open sessions for a user.
	 *
	 * Sets session_end and applies final credit for each open session.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP User ID.
	 * @return void
	 */
	public function expire_open_sessions( $user_id ) {

		global $wpdb;
		$now = current_time( 'mysql' );

		$max_credit = absint(
			/**
			 * Filter the maximum seconds credited per heartbeat.
			 *
			 * @since [version]
			 *
			 * @param int $max_credit Maximum seconds. Default 300 (5 minutes).
			 */
			apply_filters( 'llms_lesson_time_max_credit_per_heartbeat', 300 )
		);

		$open_sessions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, lesson_id, session_start, last_heartbeat_at, accumulated_seconds
				 FROM {$wpdb->prefix}lifterlms_lesson_time_sessions
				 WHERE user_id = %d AND session_end IS NULL",
				$user_id
			)
		);

		if ( ! $open_sessions ) {
			return;
		}

		$now_ts = strtotime( $now );

		foreach ( $open_sessions as $row ) {

			$last_hb_ts   = strtotime( $row->last_heartbeat_at );
			$start_ts     = strtotime( $row->session_start );
			$delta        = $now_ts - $last_hb_ts;
			$credit       = min( $delta, $max_credit );
			$wall_elapsed = $now_ts - $start_ts;

			$interval = absint(
				/**
				 * Filter the heartbeat interval in seconds.
				 *
				 * @since [version]
				 *
				 * @param int $interval Heartbeat interval. Default 30.
				 */
				apply_filters( 'llms_lesson_time_heartbeat_interval', 30 )
			);
			$tolerance = $interval;

			$proposed = $row->accumulated_seconds + $credit;
			if ( $proposed > $wall_elapsed + $tolerance ) {
				$credit = max( 0, $wall_elapsed + $tolerance - $row->accumulated_seconds );
			}

			$new_accumulated = $row->accumulated_seconds + $credit;

			$wpdb->update(
				$wpdb->prefix . 'lifterlms_lesson_time_sessions',
				array(
					'session_end'         => $now,
					'accumulated_seconds' => $new_accumulated,
					'last_heartbeat_at'   => $now,
				),
				array( 'id' => $row->id ),
				array( '%s', '%d', '%s' ),
				array( '%d' )
			);

			$this->update_cached_time( $user_id, $row->lesson_id );
		}
	}

	/**
	 * Create a new lesson time session.
	 *
	 * @since [version]
	 *
	 * @param int $user_id   WP User ID.
	 * @param int $lesson_id Lesson post ID.
	 * @return LLMS_Lesson_Time_Session|false New session object or false on failure.
	 */
	public function start_session( $user_id, $lesson_id ) {

		$this->expire_open_sessions( $user_id );

		$now   = current_time( 'mysql' );
		$token = wp_generate_password( 32, false );

		$session = new LLMS_Lesson_Time_Session();
		$session->set( 'user_id', $user_id );
		$session->set( 'lesson_id', $lesson_id );
		$session->set( 'session_token', $token );
		$session->set( 'session_start', $now );
		$session->set( 'last_heartbeat_at', $now );
		$session->set( 'accumulated_seconds', 0 );
		$session->set( 'heartbeat_count', 0 );
		$session->set( 'flagged_gaps', 0 );

		if ( $session->save() ) {
			return $session;
		}

		return false;
	}

	/**
	 * Get total accumulated seconds for a user on a lesson across all sessions.
	 *
	 * @since [version]
	 *
	 * @param int  $user_id   WP User ID.
	 * @param int  $lesson_id Lesson post ID.
	 * @param bool $use_cache Whether to use the cached value. Default true.
	 * @return int Total seconds.
	 */
	public function get_total_seconds( $user_id, $lesson_id, $use_cache = true ) {

		if ( $use_cache ) {
			$student = llms_get_student( $user_id );
			if ( $student ) {
				$cached = $student->get( 'lesson_time_' . $lesson_id );
				if ( '' !== $cached && ! is_null( $cached ) ) {
					return absint( $cached );
				}
			}
		}

		global $wpdb;
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE( SUM( accumulated_seconds ), 0 )
				 FROM {$wpdb->prefix}lifterlms_lesson_time_sessions
				 WHERE user_id = %d AND lesson_id = %d",
				$user_id,
				$lesson_id
			)
		);

		$student = llms_get_student( $user_id );
		if ( $student ) {
			$student->set( 'lesson_time_' . $lesson_id, $total );
		}

		return $total;
	}

	/**
	 * Update cached time values for a user and lesson.
	 *
	 * Updates both the per-lesson and per-course cached totals.
	 *
	 * @since [version]
	 *
	 * @param int $user_id   WP User ID.
	 * @param int $lesson_id Lesson post ID.
	 * @return void
	 */
	public function update_cached_time( $user_id, $lesson_id ) {

		$this->get_total_seconds( $user_id, $lesson_id, false );

		$lesson = llms_get_post( $lesson_id );
		if ( $lesson && is_a( $lesson, 'LLMS_Lesson' ) ) {
			$course_id = $lesson->get( 'parent_course' );
			if ( $course_id ) {
				delete_user_meta( $user_id, 'llms_course_time_' . $course_id );
			}
		}
	}

	/**
	 * Update cached course time for a user.
	 *
	 * @since [version]
	 *
	 * @param int $user_id   WP User ID.
	 * @param int $course_id Course post ID.
	 * @return int Total course time in seconds.
	 */
	public function update_cached_course_time( $user_id, $course_id ) {

		$course = llms_get_post( $course_id );
		if ( ! $course || ! is_a( $course, 'LLMS_Course' ) ) {
			return 0;
		}

		$lessons = $course->get_lessons( 'ids' );
		$total   = 0;

		foreach ( $lessons as $lid ) {
			$total += $this->get_total_seconds( $user_id, $lid );
		}

		$student = llms_get_student( $user_id );
		if ( $student ) {
			$student->set( 'course_time_' . $course_id, $total );
		}

		return $total;
	}

	/**
	 * Get cached course time for a user.
	 *
	 * @since [version]
	 *
	 * @param int  $user_id   WP User ID.
	 * @param int  $course_id Course post ID.
	 * @param bool $use_cache Whether to use cached value. Default true.
	 * @return int Total seconds.
	 */
	public function get_course_time( $user_id, $course_id, $use_cache = true ) {

		if ( $use_cache ) {
			$student = llms_get_student( $user_id );
			if ( $student ) {
				$cached = $student->get( 'course_time_' . $course_id );
				if ( '' !== $cached && ! is_null( $cached ) ) {
					return absint( $cached );
				}
			}
		}

		return $this->update_cached_course_time( $user_id, $course_id );
	}

	/**
	 * Record an admin override for lesson completion.
	 *
	 * @since [version]
	 *
	 * @param int    $user_id   WP User ID of the student.
	 * @param int    $lesson_id Lesson post ID.
	 * @param string $trigger   Completion trigger string.
	 * @return void
	 */
	public function record_admin_override( $user_id, $lesson_id, $trigger ) {

		$lesson   = llms_get_post( $lesson_id );
		$total    = $this->get_total_seconds( $user_id, $lesson_id );
		$required = $lesson ? $lesson->get( 'minimum_time' ) : 0;

		$data = array(
			'admin_id'      => get_current_user_id(),
			'student_id'    => $user_id,
			'lesson_id'     => $lesson_id,
			'trigger'       => $trigger,
			'actual_time'   => $total,
			'required_time' => $required,
			'date'          => current_time( 'mysql' ),
		);

		/**
		 * Fires when an admin overrides the minimum time requirement to complete a lesson.
		 *
		 * @since [version]
		 *
		 * @param array $data Override details.
		 */
		do_action( 'llms_lesson_time_admin_override', $data );

		update_user_meta( $user_id, 'llms_lesson_time_override_' . $lesson_id, $data );
	}

	/**
	 * Check if a lesson was completed via admin override.
	 *
	 * @since [version]
	 *
	 * @param int $user_id   WP User ID.
	 * @param int $lesson_id Lesson post ID.
	 * @return array|false Override data or false.
	 */
	public function get_admin_override( $user_id, $lesson_id ) {

		$data = get_user_meta( $user_id, 'llms_lesson_time_override_' . $lesson_id, true );
		return $data ? $data : false;
	}

	/**
	 * Format seconds as H:MM:SS.
	 *
	 * @since [version]
	 *
	 * @param int $seconds Total seconds.
	 * @return string Formatted time string.
	 */
	public function format_time( $seconds ) {

		$seconds = absint( $seconds );
		$hours   = floor( $seconds / 3600 );
		$minutes = floor( ( $seconds % 3600 ) / 60 );
		$secs    = $seconds % 60;

		return sprintf( '%d:%02d:%02d', $hours, $minutes, $secs );
	}
}
