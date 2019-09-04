<?php
/**
 * Course / Lesson progression functions
 *
 * @param  LifterLMS/Functions/Progression
 * @since  3.29.0
 * @version  3.29.0
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
 * Determines whether or not a "Mark Complete" button should be displayed for a given lesson
 *
 * @param   obj $lesson LLMS_Lesson.
 * @return  boolean
 * @since   3.29.0
 * @version 3.29.0
 */
function llms_show_mark_complete_button( $lesson ) {

	$show = true;

	if ( llms_show_take_quiz_button( $lesson ) ) {
		$show = false;
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
