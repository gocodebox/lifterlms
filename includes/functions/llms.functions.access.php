<?php
/**
* Functions used for managing page / post access
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Determine if content should be restricted
 * Called during "template_include" to determine if redirects
 * or template overrides are in order
 *
 * @param    int    $post_id   WordPress Post ID of the
 * @return   array             restriction check result data
 * @since    1.0.0
 * @version  3.1.6
 */
function llms_page_restricted( $post_id, $user_id = null ) {

	$results = array(
		'content_id' => $post_id,
		'is_restricted' => false,
		'reason' => 'accessible',
		'restriction_id' => 0,
	);

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$student = false;
	if ( $user_id ) {
		$student = new LLMS_Student( $user_id );
	}

	$post_type = get_post_type( $post_id );

	/**
	 * Do checks to determine if the content should be restricted
	 */

	// content is restricted by a sitewide membership
	if ( $membership_id = llms_is_post_restricted_by_sitewide_membership( $post_id, $user_id ) ) {
		$restriction_id = $membership_id;
		$reason = 'sitewide_membership';
	} // content is restricted by a membership
	elseif ( $membership_id = llms_is_post_restricted_by_membership( $post_id, $user_id ) ) {
		$restriction_id = $membership_id;
		$reason = 'membership';
	} // checks for lessons
	elseif ( is_singular() && 'lesson' === $post_type ) {
		$lesson = new LLMS_Lesson( $post_id );
		// if lesson is free, return accessible results and skip the rest of this function
		if ( $lesson->is_free() ) {
			return $results;
		} // must be enrolled b/c it's a lesson, alright?
		else {
			$restriction_id = $lesson->get_parent_course();
			$reason = 'enrollment_lesson';
		}
	} // checks for course
	elseif ( is_singular() && 'course' === $post_type ) {
		$restriction_id = $post_id;
		$reason = 'enrollment_course';
	} // checks for memberships
	elseif ( is_singular() && 'llms_membership' === $post_type ) {
		$restriction_id = $post_id;
		$reason = 'enrollment_membership';
	} else {

		/**
		 * Allow filtering of results before checking if the student has access
		 */
		$results = apply_filters( 'llms_page_restricted_before_check_access', $results, $post_id );
		extract( $results );

	}

	/**
	 * Content should be restricted, so we'll do the restriction checks
	 * and return restricted results
	 *
	 * this is run if we have a restriction and a reason for restriction
	 * and we either don't have a logged in student or the logged in student doesn't have access
	 */
	if ( ! empty( $restriction_id ) && ! empty( $reason ) && ( ! $student || ! $student->has_access( $restriction_id ) ) ) {

		$results['is_restricted'] = true;
		$results['reason'] = $reason;
		$results['restriction_id'] = $restriction_id;

		return apply_filters( 'llms_page_restricted', $results, $post_id );

	}

	/**
	 * At this point student has access or the content isn't supposed to be restricted
	 * we need to do some additional checks for specific post types
	 */

	if ( is_singular() ) {

		if ( 'llms_quiz' === $post_type ) {

			if ( $quiz_id = llms_is_quiz_accessible( $post_id, $user_id ) ) {

				$results['is_restricted'] = true;
				$results['reason'] = 'quiz';
				$results['restriction_id'] = $post_id;
				return $results;

			}

		}

		if ( 'lesson' === $post_type || 'llms_quiz' === $post_type ) {

			if ( $course_id = llms_is_post_restricted_by_time_period( $post_id, $user_id ) ) {

				$results['is_restricted'] = true;
				$results['reason'] = 'course_time_period';
				$results['restriction_id'] = $course_id;
				return $results;

			}

			if ( $lesson_id = llms_is_post_restricted_by_prerequisite( $post_id, $user_id ) ) {

				$results['is_restricted'] = true;
				$results['reason'] = 'lesson_prerequisite';
				$results['restriction_id'] = $lesson_id;
				return $results;

			}

			if ( $lesson_id = llms_is_post_restricted_by_drip_settings( $post_id, $user_id ) ) {

				$results['is_restricted'] = true;
				$results['reason'] = 'lesson_drip';
				$results['restriction_id'] = $lesson_id;
				return $results;

			}

		}

	}

	return apply_filters( 'llms_page_restricted', $results, $post_id );

}

/**
 * Get a boolean out of llms_page_restricted for easy if checks
 * @param    int     $post_id   WordPress Post ID of the
 * @param    int     $user_id   optional user id (will use get_current_user_id() if none supplied)
 * @return   bool
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_page_restricted( $post_id, $user_id ) {
	$restrictions = llms_page_restricted( $post_id, $user_id );
	return $restrictions['is_restricted'];
}

/**
 * Determine if a lesson/quiz is restricted by drip settings
 * @param    int     $post_id  WP Post ID of a lesson or quiz
 * @return   int|false         false if the lesson is available
 *                             WP Post ID of the lesson if it is not
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_post_restricted_by_drip_settings( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, lesson id is the post id
	if ( 'lesson' === $post_type ) {
		$lesson_id = $post_id;
	} // quizzes need to cascade up to get lesson id
	elseif ( 'llms_quiz' == $post_type ) {
		$quiz = new LLMS_Quiz( $post_id );
		$lesson_id = $quiz->get_assoc_lesson( $user_id );
	} // dont pass other post types in here dumb dumb
	else {
		return false;
	}

	$lesson = new LLMS_Lesson( $lesson_id );

	if ( $lesson->is_available() ) {
		return false;
	} else {
		return $lesson_id;
	}

}

/**
 * Determine if a lesson/quiz is restricted by a prerequisite lesson
 * @param    int     $post_id  WP Post ID of a lesson or quiz
 * @return   int|false         false if the post is not restricted or the user has completed the prereq
 *                             WP Post ID of the prerequisite lesson if it is
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_post_restricted_by_prerequisite( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, lesson id is the post id
	if ( 'lesson' === $post_type ) {
		$lesson_id = $post_id;
	} // quizzes need to cascade up to get lesson id
	elseif ( 'llms_quiz' == $post_type ) {
		$quiz = new LLMS_Quiz( $post_id );
		$lesson_id = $quiz->get_assoc_lesson( $user_id );
	} // dont pass other post types in here dumb dumb
	else {
		return false;
	}

	$lesson = new LLMS_Lesson( $lesson_id );
	$course = $lesson->get_course();

	// get an array of all possible prereqs
	$prerequisites = array();

	if ( $course->has_prerequisite( 'course' ) ) {
		$prerequisites[ $course->get_prerequisite_id( 'course' ) ] = 'course';
	}

	if ( $course->has_prerequisite( 'track' ) ) {
		$prerequisites[ $course->get_prerequisite_id( 'track' ) ] = 'track';
	}

	if ( $lesson->has_prerequisite() ) {
		$prerequisites[ $lesson->get_prerequisite() ] = 'lesson';
	}

	// prereqs exist and user is not logged in
	// return the first prereq id
	if ( $prerequisites && ! $user_id ) {
		return array_shift( array_keys( $prerequisites ) );
	} // student is logged in, check completion of the preq
	// if incomplete, send the prereq id
	// otherwise return false
	else {

		$student = new LLMS_Student( $user_id );
		foreach ( $prerequisites as $obj_id => $obj_type ) {
			if ( ! $student->is_complete( $obj_id, $obj_type ) ) {
				return $obj_id;
			}
		}

	}

	// no prereq
	return false;

}

/**
 * Determine if a course (or lesson/quiz) is "open" according to course time period settings
 * @param    int     $post_id  WP Post ID of a course, lesson, or quiz
 * @return   int|false         false if the post is not restricted by course time period
 *                             WP Post ID of the course if it is
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_post_restricted_by_time_period( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, get course information
	if ( 'lesson' === $post_type ) {

		$lesson = new LLMS_Lesson( $post_id );
		$course_id = $lesson->get_parent_course();

	} // quizzes need to cascade up to get course info
	elseif ( 'llms_quiz' == $post_type ) {

		$quiz = new LLMS_Quiz( $post_id );
		$lesson = new LLMS_Lesson( $quiz->get_assoc_lesson( $user_id ) );
		$course_id = $lesson->get_parent_course();

	} // course id is the post id
	elseif ( 'course' == $post_type ) {

		$course_id = $post_id;

	} // don't pass any other post types into this function, dumb dumb
	else {

		return false;

	}

	$course = new LLMS_Course( $course_id );
	if ( $course->is_open() ) {
		return false;
	} else {
		return $course_id;
	}

}

/**
 * Determine if a WordPress post (of any type) is restricted to at least one LifterLMS Membership level
 *
 * This function replaces the now deprecated page_restricted_by_membership() (and has slightly different functionality)
 *
 * @param    int     $post_id  WP_Post ID
 * @return   bool|int          WP_Post ID of the membership if a restriction is found
 *                             false if no restrictions found
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_post_restricted_by_membership( $post_id, $user_id = null ) {

	// don't check these posts types
	$skip = apply_filters( 'llms_is_post_restricted_by_membership_skip_post_types', array(
		'course',
		'lesson',
		'llms_quiz',
		'llms_membership',
		'llms_question',
		'llms_certificate',
		'llms_my_certificate',
	) );

	if ( in_array( get_post_type( $post_id ), $skip ) ) {
		return false;
	}

	$memberships = get_post_meta( $post_id, '_llms_restricted_levels', true );
	$restricted = get_post_meta( $post_id, '_llms_is_restricted', true );

	if ( 'yes' === $restricted && $memberships && is_array( $memberships ) ) {

		return absint( array_shift( $memberships ) );

	}

	return false;

}

/**
 * Determine if a post should bypass sitewide membership restrictions
 * If sitewide membership restriction is disabled, this will always return false
 *
 * This function replaces the now deprecated site_restricted_by_membership() (and has slightly different functionality)
 *
 * @param    int     $post_id  WP Post ID
 * @return   bool|int          if the post is not restricted (or there are not sitewide membership restrictions) returns false
 *                             if the post is restricted, returns the membership id required
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_post_restricted_by_sitewide_membership( $post_id, $user_id = null ) {

	$membership_id = absint( get_option( 'lifterlms_membership_required', '' ) );

	// site is restricted to a membership
	if ( ! empty( $membership_id ) ) {

		/**
		 * Pages that can be bypassed when sitewide restrictions are enabled
		 */
		$allowed = apply_filters( 'lifterlms_sitewide_restriction_bypass_ids', array(
			absint( $membership_id ), // the membership page the site is restricted to
			absint( get_option( 'lifterlms_terms_page_id' ) ), // terms and conditions
			llms_get_page_id( 'memberships' ), // membership archives
			llms_get_page_id( 'myaccount' ), // lifterlms account page
			llms_get_page_id( 'checkout' ), // lifterlms checkout page
		) );

		if ( in_array( $post_id, $allowed ) ) {
			return false;
		}

		return $membership_id;

	} // site is note restricted to a membership
	else {

		return false;

	}

}

/**
 * Determine if a quiz should be accessible by a user
 * @param    int     $post_id  WP Post ID
 * @return   bool|int          if the post is not restricted returns false
 *                             if the post is restricted, returns the quiz id
 * @since    3.1.6
 * @version  3.1.6
 */
function llms_is_quiz_accessible( $post_id, $user_id = null ) {

	$quiz = new LLMS_Quiz( $post_id );
	$lesson_id = $quiz->get_assoc_lesson( $user_id );

	// if we don't have a lesson id, try to retrieve it from the session
	if ( ! $lesson_id ) {
		$quiz = LLMS()->session->get( 'llms_quiz' );
		if ( $quiz ) {
			$lesson_id = $quiz->assoc_lesson;
		}
	}

	// no lesson or the user is not enrolled
	if ( ! $lesson_id || ! llms_is_user_enrolled( $user_id, $lesson_id ) ) {
		return $post_id;
	}

	return false;

}
