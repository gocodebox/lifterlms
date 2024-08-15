<?php
/**
 * Functions used for managing page / post access
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 7.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine if content should be restricted.
 *
 * Called during "template_include" to determine if redirects
 * or template overrides are in order.
 *
 * @since 1.0.0
 * @since 3.16.11 Unknown.
 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
 *
 * @param int      $post_id WordPress Post ID of the content.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return array Restriction check result data.
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
	 * Do checks to determine if the content should be restricted.
	 */
	$sitewide_membership_id = llms_is_post_restricted_by_sitewide_membership( $post_id, $user_id );
	$membership_id          = llms_is_post_restricted_by_membership( $post_id, $user_id );

	if ( is_home() && $sitewide_membership_id ) {
		$restriction_id = $sitewide_membership_id;
		$reason         = 'sitewide_membership';
		// if it's a search page and the site isn't restricted to a membership bypass restrictions.
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
		// if lesson is free, return accessible results and skip the rest of this function.
		if ( $lesson->is_free() ) {
			return $results;
		} else {
			$restriction_id = $lesson->get( 'parent_course' );
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
		 * Allow filtering of results before checking if the student has access.
		 *
		 * @since Unknown.
		 *
		 * @param array $results Restriction check result data.
		 * @param int   $post_id WordPress Post ID of the content.
		 */
		$results = apply_filters( 'llms_page_restricted_before_check_access', $results, $post_id );
		extract( $results ); // phpcs:ignore

	}

	/**
	 * Content should be restricted, so we'll do the restriction checks
	 * and return restricted results.
	 *
	 * This is run if we have a restriction and a reason for restriction
	 * and we either don't have a logged in student or the logged in student doesn't have access.
	 */
	if ( ! empty( $restriction_id ) && ! empty( $reason ) && ( ! $student || ! $student->is_enrolled( $restriction_id ) ) ) {

		$results['is_restricted']  = true;
		$results['reason']         = $reason;
		$results['restriction_id'] = $restriction_id;

		/**
		 * Allow filtering of the restricted results.
		 *
		 * @since Unknown
		 *
		 * @param array $results Restriction check result data.
		 * @param int   $post_id WordPress Post ID of the content.
		 */
		return apply_filters( 'llms_page_restricted', $results, $post_id );

	}

	/**
	 * At this point student has access or the content isn't supposed to be restricted
	 * we need to do some additional checks for specific post types.
	 */
	if ( is_singular() ) {

		if ( 'llms_quiz' === $post_type ) {

			$quiz_id = llms_is_quiz_accessible( $post_id, $user_id );
			if ( $quiz_id ) {

				$results['is_restricted']  = true;
				$results['reason']         = 'quiz';
				$results['restriction_id'] = $post_id;
				/* This filter is documented above. */
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}
		}

		if ( 'lesson' === $post_type || 'llms_quiz' === $post_type ) {

			$course_id = llms_is_post_restricted_by_time_period( $post_id, $user_id );
			if ( $course_id ) {

				$results['is_restricted']  = true;
				$results['reason']         = 'course_time_period';
				$results['restriction_id'] = $course_id;
				/* This filter is documented above. */
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}

			$prereq_data = llms_is_post_restricted_by_prerequisite( $post_id, $user_id );
			if ( $prereq_data ) {

				$results['is_restricted']  = true;
				$results['reason']         = sprintf( '%s_prerequisite', $prereq_data['type'] );
				$results['restriction_id'] = $prereq_data['id'];
				/* This filter is documented above. */
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}

			$lesson_id = llms_is_post_restricted_by_drip_settings( $post_id, $user_id );
			if ( $lesson_id ) {

				$results['is_restricted']  = true;
				$results['reason']         = 'lesson_drip';
				$results['restriction_id'] = $lesson_id;
				/* This filter is documented above. */
				return apply_filters( 'llms_page_restricted', $results, $post_id );

			}
		}
	}

	/* This filter is documented above. */
	return apply_filters( 'llms_page_restricted', $results, $post_id );
}

/**
 * Retrieve a message describing the reason why content is restricted.
 * Accepts an associative array of restriction data that can be retrieved from llms_page_restricted().
 *
 * This function doesn't handle all restriction types but it should in the future.
 * Currently it's being utilized for tooltips on lesson previews and some messages
 * output during LLMS_Template_Loader handling redirects.
 *
 * @since 3.2.4
 * @since 3.16.12 Unknown.
 *
 * @param array $restriction Array of data from `llms_page_restricted()`.
 * @return string
 */
function llms_get_restriction_message( $restriction ) {

	$msg = __( 'You do not have permission to access this content', 'lifterlms' );

	switch ( $restriction['reason'] ) {

		case 'course_prerequisite':
			$lesson      = new LLMS_Lesson( $restriction['content_id'] );
			$course_id   = $restriction['restriction_id'];
			$prereq_link = '<a href="' . get_permalink( $course_id ) . '">' . get_the_title( $course_id ) . '</a>';
			$msg         = sprintf(
				/* Translators: %$1s = lesson title; %2$s link of the course prerequisite */
				_x(
					'The lesson "%1$s" cannot be accessed until the required prerequisite course "%2$s" is completed.',
					'restricted by course prerequisite message',
					'lifterlms'
				),
				$lesson->get( 'title' ),
				$prereq_link
			);
			break;

		case 'course_track_prerequisite':
			$lesson      = new LLMS_Lesson( $restriction['content_id'] );
			$track       = new LLMS_Track( $restriction['restriction_id'] );
			$prereq_link = '<a href="' . $track->get_permalink() . '">' . $track->term->name . '</a>';
			$msg         = sprintf(
				/* Translators: %$1s = lesson title; %2$s link of the track prerequisite */
				_x(
					'The lesson "%1$s" cannot be accessed until the required prerequisite track "%2$s" is completed.',
					'restricted by course track prerequisite message',
					'lifterlms'
				),
				$lesson->get( 'title' ),
				$prereq_link
			);
			break;

		// this particular case is only utilized by lessons, courses do the check differently in the template.
		case 'course_time_period':
			$course = new LLMS_Course( $restriction['restriction_id'] );
			// if the start date hasn't passed yet.
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
			$msg    = sprintf(
				/* Translators: %$1s = lesson title; %2$s available date */
				_x(
					'The lesson "%1$s" will be available on %2$s',
					'lesson restricted by drip settings message',
					'lifterlms'
				),
				$lesson->get( 'title' ),
				$lesson->get_available_date()
			);
			break;

		case 'lesson_prerequisite':
			$lesson        = new LLMS_Lesson( $restriction['content_id'] );
			$prereq_lesson = new LLMS_Lesson( $restriction['restriction_id'] );
			$prereq_link   = '<a href="' . get_permalink( $prereq_lesson->get( 'id' ) ) . '">' . $prereq_lesson->get( 'title' ) . '</a>';
			$msg           = sprintf(
				/* Translators: %$1s = lesson title; %2$s link of the lesson prerequisite */
				_x(
					'The lesson "%1$s" cannot be accessed until the required prerequisite "%2$s" is completed.',
					'lesson restricted by prerequisite message',
					'lifterlms'
				),
				$lesson->get( 'title' ),
				$prereq_link
			);
			break;

		default:
	}

	/**
	 * Allow filtering the restriction message.
	 *
	 * @since Unknown
	 *
	 * @param string $msg         Restriction message.
	 * @param array  $restriction Array of data from `llms_page_restricted()`.
	 */
	return apply_filters( 'llms_get_restriction_message', do_shortcode( $msg ), $restriction );
}

/**
 * Get a boolean out of llms_page_restricted for easy if checks.
 *
 * @since 3.0.0
 * @since 3.37.10 Made `$user_id` parameter optional. Default is `null`.
 *
 * @param int      $post_id WordPress Post ID of the content.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return bool
 */
function llms_is_page_restricted( $post_id, $user_id = null ) {
	$restrictions = llms_page_restricted( $post_id, $user_id );
	return $restrictions['is_restricted'];
}

/**
 * Determine if a lesson/quiz is restricted by drip settings.
 *
 * @since 3.0.0
 * @since 3.16.11 Unknown.
 * @since 3.37.10 Use strict comparison '===' in place of '=='.
 * @since 6.5.0 Improve code readability turning if-elseif into a switch-case.
 *                Bypass drip content restriction on already completed lessons.
 *
 * @param int      $post_id WP Post ID of a lesson or quiz.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return int|false False if the lesson is available.
 *                   WP Post ID of the lesson if it is not.
 */
function llms_is_post_restricted_by_drip_settings( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	switch ( $post_type ) {
		// If we're on a lesson, lesson id is the post id.
		case 'lesson':
			$lesson_id = $post_id;
			break;
		case 'llms_quiz':
			$quiz      = llms_get_post( $post_id );
			$lesson_id = $quiz->get( 'lesson_id' );
			if ( ! $lesson_id ) {
				return false;
			}
			break;
		default: // Don't pass other post types.
			return false;
	}

	$lesson  = new LLMS_Lesson( $lesson_id );
	$user_id = $user_id ?? get_current_user_id();
	/**
	 * Filters whether or not to bypass drip restrictions on completed lessons.
	 *
	 * @since 6.5.0
	 *
	 * @param boolean $drip_bypass Whether or not to bypass drip restrictions on completed lessons.
	 * @param int     $post_id     WP Post ID of a lesson or quiz potentially restricted by drip settings.
	 * @param int     $user_id     WP User ID.
	 */
	$drip_bypass  = apply_filters( 'llms_lesson_drip_bypass_if_completed', true, $post_id, $user_id );
	$is_available = ( $drip_bypass && $user_id && llms_is_complete( $user_id, $lesson_id, 'lesson' ) ) || $lesson->is_available();

	return $is_available ? false : $lesson_id;
}

/**
 * Determine if a lesson/quiz is restricted by a prerequisite lesson.
 *
 * @since 3.0.0
 * @since 3.16.11 Unknown.
 * @since 6.5.0 Improve code readability turning if-elseif into a switch-case.
 *
 * @param int      $post_id WP Post ID of a lesson or quiz.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return array|false False if the post is not restricted or the user has completed the prereq
 *                     associative array with prereq type and prereq id
 *                     array(
 *                         type => [course|course_track|lesson]
 *                         id => int (object id)
 *                     ).
 */
function llms_is_post_restricted_by_prerequisite( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	switch ( $post_type ) {
		// If we're on a lesson, lesson id is the post id.
		case 'lesson':
			$lesson_id = $post_id;
			break;
		case 'llms_quiz':
			$quiz      = llms_get_post( $post_id );
			$lesson_id = $quiz->get( 'lesson_id' );
			if ( ! $lesson_id ) {
				return false;
			}
			break;
		default: // Don't pass other post types.
			return false;
	}

	$lesson = llms_get_post( $lesson_id );
	$course = $lesson->get_course();

	if ( ! $course ) {
		return false;
	}

	// Get an array of all possible prerequisites.
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

	// Prerequisites exist and user is not logged in, return the first prereq id.
	if ( $prerequisites && ! $user_id ) {

		return array_shift( $prerequisites );

		// If incomplete, send the prereq id.
	} else {

		$student = new LLMS_Student( $user_id );
		foreach ( $prerequisites as $prereq ) {
			if ( ! $student->is_complete( $prereq['id'], $prereq['type'] ) ) {
				return $prereq;
			}
		}
	}

	// Otherwise return false: no prerequisite.
	return false;
}

/**
 * Determine if a course (or lesson/quiz) is "open" according to course time period settings.
 *
 * @since 3.0.0
 * @since 3.16.11 Unknown.
 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
 * @since 6.5.0 Improve code readability turning if-elseif into a switch-case.
 *
 * @param int      $post_id WP Post ID of a course, lesson, or quiz.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return int|false False if the post is not restricted by course time period,
 *                   WP Post ID of the course if it is.
 */
function llms_is_post_restricted_by_time_period( $post_id, $user_id = null ) {

	$post_type = get_post_type( $post_id );

	switch ( $post_type ) {
		// If we're on a lesson, get course information.
		case 'lesson':
			$lesson    = new LLMS_Lesson( $post_id );
			$course_id = $lesson->get( 'parent_course' );
			break;
		case 'llms_quiz':
			$quiz      = llms_get_post( $post_id );
			$lesson_id = $quiz->get( 'lesson_id' );
			if ( ! $lesson_id ) {
				return false;
			}
			$lesson = llms_get_post( $lesson_id );
			if ( ! $lesson_id ) {
				return false;
			}
			$course_id = $lesson->get( 'parent_course' );
			break;
		case 'course':
			$course_id = $post_id;
			break;
		default: // Don't pass other post types.
			return false;
	}

	$course = new LLMS_Course( $course_id );

	return $course->is_open() ? false : $course_id;
}

/**
 * Determine if a WordPress post (of any type) is restricted to at least one LifterLMS Membership level.
 *
 * This function replaces the now deprecated page_restricted_by_membership() (and has slightly different functionality).
 *
 * @since 3.0.0
 * @since 3.16.14 Unknown.
 * @since 3.37.10 Call `in_array()` with strict comparison.
 *
 * @param int      $post_id WP_Post ID.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return bool|int WP_Post ID of the membership if a restriction is found.
 *                  False if no restrictions found.
 */
function llms_is_post_restricted_by_membership( $post_id, $user_id = null ) {

	// don't check these posts types.
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

	if ( in_array( get_post_type( $post_id ), $skip, true ) ) {
		return false;
	}

	$memberships = get_post_meta( $post_id, '_llms_restricted_levels', true );
	$restricted  = get_post_meta( $post_id, '_llms_is_restricted', true );

	if ( 'yes' === $restricted && $memberships && is_array( $memberships ) ) {

		// if no user, return the first membership from the array as the restriction id.
		if ( ! $user_id ) {

			$restriction_id = array_shift( $memberships );

		} else {

			$student = llms_get_student( $user_id );
			if ( ! $student ) {

				$restriction_id = array_shift( $memberships );

			} else {

				// reverse so to ensure that if user is in none of the memberships,
				// they'd encounter the same restriction settings as a visitor.
				$memberships = array_reverse( $memberships );

				// loop through the memberships.
				foreach ( $memberships as $mid ) {

					// set this as the restriction id.
					$restriction_id = $mid;

					// once we find the student has access break the loop,
					// this will be the restriction that the template loader will check against later.
					if ( $student->is_enrolled( $mid ) ) {
						break;
					}
				}
			}
		}

		return absint( $restriction_id );

	}

	return false;
}

/**
 * Determine if a post should bypass sitewide membership restrictions.
 *
 * If sitewide membership restriction is disabled, this will always return false.
 * This function replaces the now deprecated site_restricted_by_membership() (and has slightly different functionality).
 *
 * @since 3.0.0
 * @since 3.37.10 Do not apply membership restrictions on the page set as membership's restriction redirect page.
 *                  Exclude the privacy policy from the sitewide restriction.
 *                  Call `in_array()` with strict comparison.
 *
 * @param int      $post_id WP Post ID.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return bool|int If the post is not restricted (or there are not sitewide membership restrictions) returns false.
 *                  If the post is restricted, returns the membership id required.
 */
function llms_is_post_restricted_by_sitewide_membership( $post_id, $user_id = null ) {

	$membership_id = absint( get_option( 'lifterlms_membership_required', '' ) );

	// site is restricted to a membership.
	if ( ! empty( $membership_id ) ) {

		$membership = new LLMS_Membership( $membership_id );

		if ( ! $membership || ! is_a( $membership, 'LLMS_Membership' ) ) {
			return false;
		}

		// Restricted contents redirection page id, if any.
		$redirect_page_id = 'page' === $membership->get( 'restriction_redirect_type' ) ? absint( $membership->get( 'redirect_page_id' ) ) : 0;

		/**
		 * Pages that can be bypassed when sitewide restrictions are enabled.
		 */
		$allowed = apply_filters(
			'lifterlms_sitewide_restriction_bypass_ids',
			array_filter(
				array(
					absint( $membership_id ), // the membership page the site is restricted to.
					absint( get_option( 'lifterlms_terms_page_id' ) ), // terms and conditions.
					llms_get_page_id( 'memberships' ), // membership archives.
					llms_get_page_id( 'myaccount' ), // lifterlms account page.
					llms_get_page_id( 'checkout' ), // lifterlms checkout page.
					absint( get_option( 'wp_page_for_privacy_policy' ) ), // wp privacy policy page.
					$redirect_page_id, // Restricted contents redirection page id.
				)
			)
		);

		if ( in_array( $post_id, $allowed, true ) ) {
			return false;
		}

		return $membership_id;

	} else {

		return false;

	}
}

/**
 * Determine if a quiz should be accessible by a user.
 *
 * @since 3.1.6
 * @since 3.16.1 Unknown.
 *
 * @param int      $post_id WP Post ID.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return bool|int If the post is not restricted returns false.
 *                  If the post is restricted, returns the quiz id.
 */
function llms_is_quiz_accessible( $post_id, $user_id = null ) {

	$quiz      = llms_get_post( $post_id );
	$lesson_id = $quiz->get( 'lesson_id' );

	// No lesson or the user is not enrolled.
	if ( ! $lesson_id || ! llms_is_user_enrolled( $user_id, $lesson_id ) ) {
		return $post_id;
	}

	return false;
}
