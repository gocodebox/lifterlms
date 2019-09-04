<?php
/**
 * Functions used for managing page / post access
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 3.16.14
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine if content should be restricted
 * Called during "template_include" to determine if redirects
 * or template overrides are in order
 *
 * @param    int $post_id   WordPress Post ID of the
 * @return   array             restriction check result data
 * @since    1.0.0
 * @version  3.16.11
 */
function llms_page_restricted( $post_id, $user_id = null ) {

	$results = array(
		'content_id'     => $post_id,
		'is_restricted'  => false,
		'reason'         => 'accessible',
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
	$sitewide_membership_id = llms_is_post_restricted_by_sitewide_membership( $post_id, $user_id );
	$membership_id          = llms_is_post_restricted_by_membership( $post_id, $user_id );

	if ( is_home() && $sitewide_membership_id ) {
		$restriction_id = $sitewide_membership_id;
		$reason         = 'sitewide_membership';
		// if it's a search page and the site isn't restricted to a membership bypass restrictions
	} elseif ( ( is_search() ) && ! get_option( 'lifterlms_membership_required', '' ) ) {
		return apply_filters( 'llms_page_restricted', $results, $post_id );
	} elseif ( is_singular() && $sitewide_membership_id ) {
		$restriction_id = $sitewide_membership_id;
		$reason         = 'sitewide_membership';
	} elseif ( is_singular() && $membership_id ) {
		$restriction_id = $membership_id;
		$reason         = 'membership';
	} elseif ( is_singular() && 'lesson' === $post_type ) {
		$lesson = new LLMS_Lesson( $post_id );
		// if lesson is free, return accessible results and skip the rest of this function
		if ( $lesson->is_free() ) {
			return $results;
		} else {
			$restriction_id = $lesson->get_parent_course();
			$reason         = 'enrollment_lesson';
		}
	} elseif ( is_singular() && 'course' === $post_type ) {
		$restriction_id = $post_id;
		$reason         = 'enrollment_course';
	} elseif ( is_singular() && 'llms_membership' === $post_type ) {
		$restriction_id = $post_id;
		$reason         = 'enrollment_membership';
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
	if ( ! empty( $restriction_id ) && ! empty( $reason ) && ( ! $student || ! $student->is_enrolled( $restriction_id ) ) ) {

		$results['is_restricted']  = true;
		$results['reason']         = $reason;
		$results['restriction_id'] = $restriction_id;

		return apply_filters( 'llms_page_restricted', $results, $post_id );

	}

	/**
	 * At this point student has access or the content isn't supposed to be restricted
	 * we need to do some additional checks for specific post types
	 */

	if ( is_singular() ) {

		if ( 'llms_quiz' === $post_type ) {

			$quiz_id = llms_is_quiz_accessible( $post_id, $user_id );
			if ( $quiz_id ) {

				$results['is_restricted']  = true;
				$results['reason']         = 'quiz';
				$results['restriction_id'] = $post_id;
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}
		}

		if ( 'lesson' === $post_type || 'llms_quiz' === $post_type ) {

			$course_id = llms_is_post_restricted_by_time_period( $post_id, $user_id );
			if ( $course_id ) {

				$results['is_restricted']  = true;
				$results['reason']         = 'course_time_period';
				$results['restriction_id'] = $course_id;
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}

			$prereq_data = llms_is_post_restricted_by_prerequisite( $post_id, $user_id );
			if ( $prereq_data ) {

				$results['is_restricted']  = true;
				$results['reason']         = sprintf( '%s_prerequisite', $prereq_data['type'] );
				$results['restriction_id'] = $prereq_data['id'];
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}

			$lesson_id = llms_is_post_restricted_by_drip_settings( $post_id, $user_id );
			if ( $lesson_id ) {

				$results['is_restricted']  = true;
				$results['reason']         = 'lesson_drip';
				$results['restriction_id'] = $lesson_id;
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}
		}
	}// End if().

	return apply_filters( 'llms_page_restricted', $results, $post_id );

}

/**
 * Retrieve a message describing the reason why content is restricted
 * Accepts an associative array of restriction data that can be retrieved from llms_page_restricted()
 *
 * This function doesn't handle all restriction types but it should in the future
 * Currently it's being utilized for tooltips on lesson previews and some messages
 * output during LLMS_Template_Loader handling redirects
 *
 * @param    array $restriction  array of data from llms_page_restricted()
 * @return   string
 * @since    3.2.4
 * @version  3.16.12
 */
function llms_get_restriction_message( $restriction ) {

	$msg = __( 'You do not have permission to access this content', 'lifterlms' );

	switch ( $restriction['reason'] ) {

		case 'course_prerequisite':
			$lesson      = new LLMS_Lesson( $restriction['content_id'] );
			$course_id   = $restriction['restriction_id'];
			$prereq_link = '<a href="' . get_permalink( $course_id ) . '">' . get_the_title( $course_id ) . '</a>';
			$msg         = sprintf( _x( 'The lesson "%1$s" cannot be accessed until the required prerequisite course "%2$s" is completed.', 'restricted by course prerequisite message', 'lifterlms' ), $lesson->get( 'title' ), $prereq_link );
			break;

		case 'course_track_prerequisite':
			$lesson      = new LLMS_Lesson( $restriction['content_id'] );
			$track       = new LLMS_Track( $restriction['restriction_id'] );
			$prereq_link = '<a href="' . $track->get_permalink() . '">' . $track->term->name . '</a>';
			$msg         = sprintf( _x( 'The lesson "%1$s" cannot be accessed until the required prerequisite track "%2$s" is completed.', 'restricted by course track prerequisite message', 'lifterlms' ), $lesson->get( 'title' ), $prereq_link );
			break;

		// this particular case is only utilized by lessons, courses do the check differently in the template
		case 'course_time_period':
			$course = new LLMS_Course( $restriction['restriction_id'] );
			// if the start date hasn't passed yet
			if ( ! $course->has_date_passed( 'start_date' ) ) {
				$msg = $course->get( 'course_opens_message' );
			} elseif ( $course->has_date_passed( 'end_date' ) ) {
				$msg = $course->get( 'course_closed_message' );
			}
			break;

		case 'enrollment_lesson':
			$course = new LLMS_Course( $restriction['restriction_id'] );
			$msg    = $course->get( 'content_restricted_message' );
			break;

		case 'lesson_drip':
			$lesson = new LLMS_Lesson( $restriction['restriction_id'] );
			$msg    = sprintf( _x( 'The lesson "%1$s" will be available on %2$s', 'lesson restricted by drip settings message', 'lifterlms' ), $lesson->get( 'title' ), $lesson->get_available_date() );
			break;

		case 'lesson_prerequisite':
			$lesson        = new LLMS_Lesson( $restriction['content_id'] );
			$prereq_lesson = new LLMS_Lesson( $restriction['restriction_id'] );
			$prereq_link   = '<a href="' . get_permalink( $prereq_lesson->get( 'id' ) ) . '">' . $prereq_lesson->get( 'title' ) . '</a>';
			$msg           = sprintf( _x( 'The lesson "%1$s" cannot be accessed until the required prerequisite "%2$s" is completed.', 'lesson restricted by prerequisite message', 'lifterlms' ), $lesson->get( 'title' ), $prereq_link );
			break;

		default:
	}// End switch().

	return apply_filters( 'llms_get_restriction_message', do_shortcode( $msg ), $restriction );
}

/**
 * Get a boolean out of llms_page_restricted for easy if checks
 *
 * @param    int $post_id   WordPress Post ID of the
 * @param    int $user_id   optional user id (will use get_current_user_id() if none supplied)
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
 *
 * @param    int $post_id  WP Post ID of a lesson or quiz
 * @return   int|false         false if the lesson is available
 *                             WP Post ID of the lesson if it is not
 * @since    3.0.0
 * @version  3.16.11
 */
function llms_is_post_restricted_by_drip_settings( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, lesson id is the post id.
	if ( 'lesson' === $post_type ) {
		$lesson_id = $post_id;
	} elseif ( 'llms_quiz' == $post_type ) {
		$quiz      = llms_get_post( $post_id );
		$lesson_id = $quiz->get( 'lesson_id' );
		if ( ! $lesson_id ) {
			return false;
		}
	} else {
		// dont pass other post types in here dumb dumb.
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
 *
 * @param    int $post_id  WP Post ID of a lesson or quiz
 * @return   array|false       false if the post is not restricted or the user has completed the prereq
 *                             associative array with prereq type and prereq id
 *                             array(
 *                                  type => [course|course_track|lesson]
 *                                  id => int (object id)
 *                             )
 * @since    3.0.0
 * @version  3.16.11
 */
function llms_is_post_restricted_by_prerequisite( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	if ( 'lesson' === $post_type ) {
		$lesson_id = $post_id;
	} elseif ( 'llms_quiz' === $post_type ) {
		$quiz      = llms_get_post( $post_id );
		$lesson_id = $quiz->get( 'lesson_id' );
		if ( ! $lesson_id ) {
			return false;
		}
	} else {
		return false;
	}

	$lesson = llms_get_post( $lesson_id );
	$course = $lesson->get_course();

	if ( ! $course ) {
		return false;
	}

	// get an array of all possible prereqs
	$prerequisites = array();

	if ( $course->has_prerequisite( 'course' ) ) {
		$prerequisites[] = array(
			'id'   => $course->get_prerequisite_id( 'course' ),
			'type' => 'course',
		);
	}

	if ( $course->has_prerequisite( 'course_track' ) ) {
		$prerequisites[] = array(
			'id'   => $course->get_prerequisite_id( 'course_track' ),
			'type' => 'course_track',
		);
	}

	if ( $lesson->has_prerequisite() ) {
		$prerequisites[] = array(
			'id'   => $lesson->get_prerequisite(),
			'type' => 'lesson',
		);
	}

	// prereqs exist and user is not logged in
	// return the first prereq id
	if ( $prerequisites && ! $user_id ) {

		return array_shift( $prerequisites );

		// if incomplete, send the prereq id
	} else {

		$student = new LLMS_Student( $user_id );
		foreach ( $prerequisites as $prereq ) {
			if ( ! $student->is_complete( $prereq['id'], $prereq['type'] ) ) {
				return $prereq;
			}
		}
	}

	// otherwise return false
	// no prereq
	return false;

}

/**
 * Determine if a course (or lesson/quiz) is "open" according to course time period settings
 *
 * @param    int $post_id  WP Post ID of a course, lesson, or quiz
 * @return   int|false         false if the post is not restricted by course time period
 *                             WP Post ID of the course if it is
 * @since    3.0.0
 * @version  3.16.11
 */
function llms_is_post_restricted_by_time_period( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, get course information
	if ( 'lesson' === $post_type ) {

		$lesson    = new LLMS_Lesson( $post_id );
		$course_id = $lesson->get_parent_course();

	} elseif ( 'llms_quiz' === $post_type ) {
		$quiz      = llms_get_post( $post_id );
		$lesson_id = $quiz->get( 'lesson_id' );
		if ( ! $lesson_id ) {
			return false;
		}
		$lesson = llms_get_post( $lesson_id );
		if ( ! $lesson_id ) {
			return false;
		}
		$course_id = $lesson->get_parent_course();

	} elseif ( 'course' == $post_type ) {

		$course_id = $post_id;

	} else {

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
 * @param    int $post_id  WP_Post ID
 * @return   bool|int          WP_Post ID of the membership if a restriction is found
 *                             false if no restrictions found
 * @since    3.0.0
 * @version  3.16.14
 */
function llms_is_post_restricted_by_membership( $post_id, $user_id = null ) {

	// don't check these posts types
	$skip = apply_filters(
		'llms_is_post_restricted_by_membership_skip_post_types',
		array(
			'course',
			'lesson',
			'llms_quiz',
			'llms_membership',
			'llms_question',
			'llms_certificate',
			'llms_my_certificate',
		)
	);

	if ( in_array( get_post_type( $post_id ), $skip ) ) {
		return false;
	}

	$memberships = get_post_meta( $post_id, '_llms_restricted_levels', true );
	$restricted  = get_post_meta( $post_id, '_llms_is_restricted', true );

	if ( 'yes' === $restricted && $memberships && is_array( $memberships ) ) {

		// if no user, return the first membership from the array as the restriction id
		if ( ! $user_id ) {

			$restriction_id = array_shift( $memberships );

		} else {

			$student = llms_get_student( $user_id );
			if ( ! $student ) {

				 $restriction_id = array_shift( $memberships );

			} else {

				// reverse so to ensure that if user is in none of the memberships
				// they'd encounter the same restriction settings as a visitor
				$memberships = array_reverse( $memberships );

				// loop through the memberships
				foreach ( $memberships as $mid ) {

					// set this as the restriction id
					$restriction_id = $mid;

					// once we find the student has access break the loop
					// this will be the restriction that the template loader will check against later
					if ( $student->is_enrolled( $mid ) ) {
						break;
					}
				}
			}
		}

		return absint( $restriction_id );

	}// End if().

	return false;

}

/**
 * Determine if a post should bypass sitewide membership restrictions
 * If sitewide membership restriction is disabled, this will always return false
 *
 * This function replaces the now deprecated site_restricted_by_membership() (and has slightly different functionality)
 *
 * @param    int $post_id  WP Post ID
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
		$allowed = apply_filters(
			'lifterlms_sitewide_restriction_bypass_ids',
			array(
				absint( $membership_id ), // the membership page the site is restricted to
				absint( get_option( 'lifterlms_terms_page_id' ) ), // terms and conditions
				llms_get_page_id( 'memberships' ), // membership archives
				llms_get_page_id( 'myaccount' ), // lifterlms account page
				llms_get_page_id( 'checkout' ), // lifterlms checkout page
			)
		);

		if ( in_array( $post_id, $allowed ) ) {
			return false;
		}

		return $membership_id;

	} else {

		return false;

	}

}

/**
 * Determine if a quiz should be accessible by a user
 *
 * @param    int $post_id  WP Post ID
 * @return   bool|int          if the post is not restricted returns false
 *                             if the post is restricted, returns the quiz id
 * @since    3.1.6
 * @version  3.16.1
 */
function llms_is_quiz_accessible( $post_id, $user_id = null ) {

	$quiz      = llms_get_post( $post_id );
	$lesson_id = $quiz->get( 'lesson_id' );

	// no lesson or the user is not enrolled
	if ( ! $lesson_id || ! llms_is_user_enrolled( $user_id, $lesson_id ) ) {
		return $post_id;
	}

	return false;

}
