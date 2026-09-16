<?php
/**
 * LLMS_Engagements_Thresholds class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fires threshold-based engagement triggers.
 *
 * These triggers piggyback on existing completion hooks but require a
 * per-engagement threshold comparison which the standard
 * `LLMS_Engagements::maybe_trigger_engagement()` flow does not support:
 *
 * + `course_progress`: student reaches N% of course completion.
 * + `course_grade_below`: student completes a course with a grade below N%.
 * + `quiz_failed_multiple`: student fails the same quiz N times.
 *
 * @since [version]
 */
class LLMS_Engagements_Thresholds {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'lifterlms_lesson_completed', array( $this, 'maybe_trigger_course_progress' ), 778, 2 );
		add_action( 'lifterlms_course_completed', array( $this, 'maybe_trigger_course_grade_below' ), 778, 2 );
		add_action( 'lifterlms_quiz_failed', array( $this, 'maybe_trigger_quiz_failed_multiple' ), 778, 2 );
		add_action( 'lifterlms_quiz_passed', array( $this, 'clear_quiz_failed_markers' ), 778, 2 );
	}

	/**
	 * Trigger `course_progress` engagements when a lesson completion crosses a percentage threshold.
	 *
	 * @since [version]
	 *
	 * @param int $user_id   WP_User ID of the student.
	 * @param int $lesson_id WP_Post ID of the completed lesson.
	 * @return void
	 */
	public function maybe_trigger_course_progress( $user_id, $lesson_id ) {

		$lesson = llms_get_post( $lesson_id );
		if ( ! $lesson instanceof LLMS_Lesson ) {
			return;
		}

		$course_id = absint( $lesson->get( 'parent_course' ) );
		if ( ! $course_id ) {
			return;
		}

		$engagements = llms()->engagements()->get_triggerable_engagements( 'course_progress', $course_id );
		if ( ! $engagements ) {
			return;
		}

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			return;
		}

		$progress = $student->get_progress( $course_id, 'course', false );
		if ( ! is_numeric( $progress ) ) {
			return;
		}

		foreach ( $engagements as $engagement ) {

			$threshold = (float) get_post_meta( $engagement->trigger_id, '_llms_engagement_trigger_percentage', true );
			if ( $threshold <= 0 || $progress < $threshold ) {
				continue;
			}

			// Fire once per course: a marker prevents refiring on every subsequent lesson completion.
			if ( $this->has_marker( $user_id, $engagement->trigger_id, $course_id ) ) {
				continue;
			}

			$this->set_marker( $user_id, $engagement->trigger_id, $course_id, $progress );
			llms()->engagements()->trigger( $engagement, $user_id, $course_id );
		}
	}

	/**
	 * Trigger `course_grade_below` engagements when a course is completed with a low grade.
	 *
	 * No fire-once marker is required: course completion is a discrete event.
	 *
	 * @since [version]
	 *
	 * @param int $user_id   WP_User ID of the student.
	 * @param int $course_id WP_Post ID of the completed course.
	 * @return void
	 */
	public function maybe_trigger_course_grade_below( $user_id, $course_id ) {

		$engagements = llms()->engagements()->get_triggerable_engagements( 'course_grade_below', $course_id );
		if ( ! $engagements ) {
			return;
		}

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			return;
		}

		$grade = $student->get_grade( $course_id, false );
		if ( ! is_numeric( $grade ) ) {
			return;
		}

		foreach ( $engagements as $engagement ) {

			$threshold = (float) get_post_meta( $engagement->trigger_id, '_llms_engagement_trigger_percentage', true );
			if ( $threshold <= 0 || $grade >= $threshold ) {
				continue;
			}

			llms()->engagements()->trigger( $engagement, $user_id, $course_id );
		}
	}

	/**
	 * Trigger `quiz_failed_multiple` engagements when a student's failure count reaches the configured threshold.
	 *
	 * Fires once when the count is reached; the marker is cleared when the student
	 * passes the quiz so a later string of failures can fire again.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID of the student.
	 * @param int $quiz_id WP_Post ID of the failed quiz.
	 * @return void
	 */
	public function maybe_trigger_quiz_failed_multiple( $user_id, $quiz_id ) {

		$engagements = llms()->engagements()->get_triggerable_engagements( 'quiz_failed_multiple', $quiz_id );
		if ( ! $engagements ) {
			return;
		}

		$fail_count = $this->count_failed_attempts( $user_id, $quiz_id );

		foreach ( $engagements as $engagement ) {

			$count = absint( get_post_meta( $engagement->trigger_id, '_llms_engagement_trigger_count', true ) );
			if ( ! $count || $fail_count < $count ) {
				continue;
			}

			if ( $this->has_marker( $user_id, $engagement->trigger_id, $quiz_id ) ) {
				continue;
			}

			$this->set_marker( $user_id, $engagement->trigger_id, $quiz_id, $fail_count );
			llms()->engagements()->trigger( $engagement, $user_id, $quiz_id );
		}
	}

	/**
	 * Clear `quiz_failed_multiple` fire-once markers when the student passes the quiz.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID of the student.
	 * @param int $quiz_id WP_Post ID of the passed quiz.
	 * @return void
	 */
	public function clear_quiz_failed_markers( $user_id, $quiz_id ) {

		$engagements = llms()->engagements()->get_triggerable_engagements( 'quiz_failed_multiple', $quiz_id );

		foreach ( $engagements as $engagement ) {
			$this->clear_marker( $user_id, $engagement->trigger_id, $quiz_id );
		}
	}

	/**
	 * Determine whether a fire-once marker exists for a related post on an engagement.
	 *
	 * Markers are stored per related post so an "Any course" / "Any quiz" engagement
	 * fires independently for each course or quiz rather than only for the first one.
	 * Uses the same storage shape as {@see LLMS_Engagements_Scanner::maybe_fire()}.
	 *
	 * @since [version]
	 *
	 * @param int $user_id       WP_User ID of the student.
	 * @param int $engagement_id WP_Post ID of the `llms_engagement` post.
	 * @param int $related_id    WP_Post ID of the related post (course or quiz).
	 * @return boolean
	 */
	protected function has_marker( $user_id, $engagement_id, $related_id ) {

		$markers = llms_get_user_postmeta( $user_id, $engagement_id, LLMS_Engagements_Scanner::MARKER_KEY, true );

		return is_array( $markers ) && isset( $markers[ absint( $related_id ) ] );
	}

	/**
	 * Record a fire-once marker for a related post on an engagement.
	 *
	 * @since [version]
	 *
	 * @param int   $user_id       WP_User ID of the student.
	 * @param int   $engagement_id WP_Post ID of the `llms_engagement` post.
	 * @param int   $related_id    WP_Post ID of the related post (course or quiz).
	 * @param mixed $value         Marker value (progress percentage or failure count).
	 * @return void
	 */
	protected function set_marker( $user_id, $engagement_id, $related_id, $value ) {

		$markers = llms_get_user_postmeta( $user_id, $engagement_id, LLMS_Engagements_Scanner::MARKER_KEY, true );
		$markers = is_array( $markers ) ? $markers : array();

		$markers[ absint( $related_id ) ] = $value;

		llms_update_user_postmeta( $user_id, $engagement_id, LLMS_Engagements_Scanner::MARKER_KEY, $markers, true );
	}

	/**
	 * Remove the fire-once marker for a related post on an engagement.
	 *
	 * Only the given related post's marker is removed: an "Any quiz" engagement's
	 * markers for other quizzes are unaffected.
	 *
	 * @since [version]
	 *
	 * @param int $user_id       WP_User ID of the student.
	 * @param int $engagement_id WP_Post ID of the `llms_engagement` post.
	 * @param int $related_id    WP_Post ID of the related post (course or quiz).
	 * @return void
	 */
	protected function clear_marker( $user_id, $engagement_id, $related_id ) {

		$markers = llms_get_user_postmeta( $user_id, $engagement_id, LLMS_Engagements_Scanner::MARKER_KEY, true );
		if ( ! is_array( $markers ) || ! isset( $markers[ absint( $related_id ) ] ) ) {
			return;
		}

		unset( $markers[ absint( $related_id ) ] );

		if ( $markers ) {
			llms_update_user_postmeta( $user_id, $engagement_id, LLMS_Engagements_Scanner::MARKER_KEY, $markers, true );
		} else {
			llms_delete_user_postmeta( $user_id, $engagement_id, LLMS_Engagements_Scanner::MARKER_KEY );
		}
	}

	/**
	 * Count a student's failed attempts for a given quiz.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID of the student.
	 * @param int $quiz_id WP_Post ID of the quiz.
	 * @return int
	 */
	protected function count_failed_attempts( $user_id, $quiz_id ) {

		global $wpdb;

		return absint(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_quiz_attempts WHERE student_id = %d AND quiz_id = %d AND status = 'fail'",
					$user_id,
					$quiz_id
				)
			)
		); // db call ok; no-cache ok.
	}
}
