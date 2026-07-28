<?php
/**
 * Course / Lesson progression functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.29.0
 * @version 3.29.0
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
