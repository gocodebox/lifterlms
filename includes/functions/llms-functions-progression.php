<?php
/**
 * Course / Lesson progression functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.29.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine if lesson completion is allowed for a given user & lesson
 *
 * @param   int    $user_id    WP User ID.
 * @param   int    $lesson_id  WP Post ID of a lesson.
 * @param   string $trigger    Optional trigger description string.
 * @param   array  $args       Optional arguments.
 * @return  boolean
 * @since   3.29.0
 * @version 3.29.0
 */
function llms_allow_lesson_completion( $user_id, $lesson_id, $trigger = '', $args = array() ) {
	/**
	 * @filter llms_allow_lesson_completion
	 * @since 3.17.1
	 * @version 3.17.1
	 */
	return apply_filters( 'llms_allow_lesson_completion', true, $user_id, $lesson_id, $trigger, $args );
}

/**
 * Determine whether a user is authorized to mark a given lesson complete or incomplete.
 *
 * Used both to decide whether to render the front-end mark complete/incomplete buttons and to
 * authorize the form submission server-side, so the two cannot drift apart. Instructors and
 * admins who can edit the lesson are always allowed; everyone else must be enrolled in the
 * lesson's parent course and the lesson must be available (drip).
 *
 * @since 10.0.7
 *
 * @param int             $user_id WP User ID of the student.
 * @param LLMS_Lesson|int $lesson  LLMS_Lesson instance or WP Post ID of a lesson.
 * @return bool
 */
function llms_can_user_complete_lesson( $user_id, $lesson ) {

	if ( ! $lesson instanceof LLMS_Lesson ) {
		$lesson = llms_get_post( $lesson );
	}

	if ( ! $lesson || ! is_a( $lesson, 'LLMS_Lesson' ) ) {
		$allowed = false;
	} elseif ( current_user_can( 'edit_post', $lesson->get( 'id' ) ) ) {
		// Instructors / admins able to edit the lesson are always allowed.
		$allowed = true;
	} else {
		// The student must be enrolled in the lesson's parent course and the lesson must be available.
		$allowed = ( $user_id && llms_is_user_enrolled( $user_id, $lesson->get( 'parent_course' ) ) && $lesson->is_available() );
		if ( $allowed && llms_is_post_restricted_by_stream( $lesson->get( 'id' ), $user_id ) ) {
			$allowed = false;
		}
	}

	/**
	 * Filter whether a user is authorized to mark a lesson complete or incomplete.
	 *
	 * @since 10.0.7
	 *
	 * @param bool             $allowed Whether or not the user is authorized.
	 * @param int              $user_id WP User ID of the student.
	 * @param LLMS_Lesson|bool $lesson  LLMS_Lesson instance, or `false` for an invalid lesson.
	 */
	return apply_filters( 'llms_can_user_complete_lesson', $allowed, $user_id, $lesson );
}

/**
 * Determine whether a student has met a lesson's minimum time requirement.
 *
 * Returns true when the lesson has no minimum time, or when the student's
 * accumulated time is at least the required number of seconds.
 *
 * @since [version]
 *
 * @param int             $user_id WP User ID of the student.
 * @param LLMS_Lesson|int $lesson  LLMS_Lesson instance or WP Post ID of a lesson.
 * @return bool
 */
function llms_has_met_lesson_minimum_time( $user_id, $lesson ) {

	if ( ! $lesson instanceof LLMS_Lesson ) {
		$lesson = llms_get_post( $lesson );
	}

	if ( ! $lesson || ! is_a( $lesson, 'LLMS_Lesson' ) || ! $lesson->has_minimum_time() ) {
		return true;
	}

	$total    = LLMS_Lesson_Time_Tracking::instance()->get_total_seconds( $user_id, $lesson->get( 'id' ) );
	$required = absint( $lesson->get( 'minimum_time' ) );

	return $total >= $required;
}

/**
 * Retrieve the student progress cache keys affected by a change to a given object.
 *
 * Student progress is cached in user meta under deterministic keys (e.g. `course_123_progress`,
 * stored prefixed as `llms_course_123_progress`). This returns the (unprefixed) keys for the
 * object's ancestor tree: the parent section (for lessons), the section itself (for sections),
 * the parent course, and the course's tracks.
 *
 * @since 10.2.0
 *
 * @param int         $object_id   WP Post ID of a lesson, section, or course.
 * @param string|null $object_type Optional. Object post type (`lesson`, `section`, or `course`). Derived from the post when omitted.
 * @return string[] List of unprefixed user meta cache keys.
 */
function llms_get_progress_cache_keys( $object_id, $object_type = null ) {

	$object_type = $object_type ? $object_type : get_post_type( $object_id );

	$section_id = 0;
	$course_id  = 0;

	if ( 'lesson' === $object_type ) {
		$lesson = llms_get_post( $object_id );
		if ( ! $lesson || ! is_a( $lesson, 'LLMS_Lesson' ) ) {
			return array();
		}
		$section_id = absint( $lesson->get( 'parent_section' ) );
		$course_id  = absint( $lesson->get( 'parent_course' ) );
	} elseif ( 'section' === $object_type ) {
		$section = llms_get_post( $object_id );
		if ( ! $section || ! is_a( $section, 'LLMS_Section' ) ) {
			return array();
		}
		$section_id = absint( $object_id );
		$course_id  = absint( $section->get( 'parent_course' ) );
	} elseif ( 'course' === $object_type ) {
		$course_id = absint( $object_id );
	} else {
		return array();
	}

	$keys = array();

	if ( $section_id ) {
		$keys[] = sprintf( 'section_%d_progress', $section_id );
	}

	if ( $course_id ) {
		$keys[] = sprintf( 'course_%d_progress', $course_id );

		$course = llms_get_post( $course_id );
		if ( $course && is_a( $course, 'LLMS_Course' ) ) {
			foreach ( wp_list_pluck( $course->get_tracks(), 'term_id' ) as $track_id ) {
				$keys[] = sprintf( 'course_track_%d_progress', $track_id );
			}
		}
	}

	return $keys;
}

/**
 * Reset the cached student progress for an object's ancestor tree, for all students.
 *
 * Used when a structural change (trash, delete, untrash, reparent) invalidates the cached
 * progress of every student at once, in contrast to `LLMS_Student::update_completion_status()`
 * which resets the cache for a single student when their own completion changes.
 *
 * @since 10.2.0
 *
 * @param int         $object_id   WP Post ID of a lesson, section, or course.
 * @param string|null $object_type Optional. Object post type (`lesson`, `section`, or `course`). Derived from the post when omitted.
 * @return string[] List of unprefixed cache keys that were reset.
 */
function llms_reset_progress_cache( $object_id, $object_type = null ) {

	$keys = llms_get_progress_cache_keys( $object_id, $object_type );

	foreach ( $keys as $key ) {
		delete_metadata( 'user', 0, 'llms_' . $key, '', true );
	}

	return $keys;
}

/**
 * Determines whether or not a "Mark Complete" button should be displayed for a given lesson
 *
 * If the lesson has a quiz, the button will only be shown if the current user has
 * already met the quiz requirements (passed the quiz, or completed at least one attempt
 * if passing is not required).
 *
 * @since 3.29.0
 * @since 10.0.0 Show button when quiz requirements are already met. Fixes issue #3058.
 *
 * @param LLMS_Lesson $lesson LLMS_Lesson instance.
 * @return boolean
 */
function llms_show_mark_complete_button( $lesson ) {

	$show = true;

	// If a quiz button should be shown, check if user already met quiz requirements.
	if ( llms_show_take_quiz_button( $lesson ) ) {
		$show = false;

		// Check if current user has already met quiz requirements.
		$user_id = get_current_user_id();
		if ( $user_id && $lesson->is_quiz_enabled() ) {
			$student = llms_get_student( $user_id );
			if ( $student ) {
				$quiz_id = $lesson->get( 'quiz' );
				$attempt = $student->quizzes()->get_best_attempt( $quiz_id );

				if ( $attempt ) {
					$passing_required = llms_parse_bool( $lesson->get( 'require_passing_grade' ) );
					// Show button if: passing not required, OR attempt is passing.
					if ( ! $passing_required || $attempt->is_passing() ) {
						$show = true;
					}
				}
			}
		}
	}

	return apply_filters( 'llms_show_mark_complete_button', $show, $lesson );
}


/**
 * Determines whether or not a "Take Quiz" button should be displayed for a given lesson.
 *
 * @param   obj $lesson LLMS_Lesson.
 * @return  boolean
 * @since   3.29.0
 * @version 3.29.0
 */
function llms_show_take_quiz_button( $lesson ) {

	// If a lesson has a quiz, show the button, otherwise don't.
	$show = $lesson->has_quiz();

	// if the lesson has a quiz make sure we can show the button to the current user.
	if ( $show ) {

		$quiz_id = $lesson->get( 'quiz' );

		// if the quiz isn't published and the current user can't edit the quiz don't show the button.
		if ( 'publish' !== get_post_status( $quiz_id ) && ! current_user_can( 'edit_post', $quiz_id ) ) {
			$show = false;
		}
	}

	// allow 3rd parties to modify default behavior.
	return apply_filters( 'llms_show_take_quiz_button', $show, $lesson );
}
