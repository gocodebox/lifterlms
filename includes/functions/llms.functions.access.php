<?php
/**
 * Functions used for managing page / post access
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine if a WP_Post is accessible to a given user
 *
 * Called during "template_include" to determine if redirects
 * or template overrides are in order.
 *
 * @since 1.0.0
 * @since 3.16.11 Unknown.
 * @since [version] Major refactor; added caching mechanisms.
 *
 * @param int      $post_id   WordPress Post ID of the content.
 * @param int|null $user_id   Optional. WP User ID. Defaults to the current user if none supplied.
 * @param bool     $use_cache If `true`, uses data stored in object cache (where available).
 * @return array {
 *     Associative array of restriction information.
 *
 *     @type int    $content_id     WP_Post ID of the requested content post.
 *     @type int    $restriction_id WP_Post ID of the post governing restriction over the requested content.
 *     @type bool   $is_restricted  Whether or not the requested content is accessible by the requested user.
 *     @type string $reason         A code describing the reason why the requested content is restricted.
 * }
 */
function llms_page_restricted( $post_id, $user_id = null, $use_cache = true ) {

	// Default to current user if none supplied.
	$user_id = $user_id ? $user_id : get_current_user_id();

	/**
	 * Disable caching for the `llms_page_restricted()` function.
	 *
	 * @since [version]
	 *
	 * @param bool $disable If `true`, caching will be disabled regardless of the value of the function's `$use_cache` parameter.
	 */
	$use_cache = apply_filters( 'llms_page_restricted_disable_caching', false ) ? false : $use_cache;

	$cache_key = sprintf( '%1$d::%2$d', $post_id, $user_id );

	// If we are using caching
	$cached = $use_cache && $user_id ? wp_cache_get( $cache_key, 'llms_page_restricted' ) : false;

	// Return early if we have cached data & cached is enabled.
	if ( $cached ) {
		return $cached;
	}

	$results = array(
		'content_id'     => $post_id,
		'is_restricted'  => false,
		'reason'         => 'accessible',
		'restriction_id' => 0,
	);

	$student = $user_id ? llms_get_student( $user_id ) : false;

	$post      = llms_get_post( $post_id );
	$post_type = get_post_type( $post_id );

	$sitewide_membership_id = llms_is_post_restricted_by_sitewide_membership( $post_id, $user_id );

	/**
	 * Setup restriction data for the given post.
	 *
	 * This will determine if the post should be restricted and later we'll check if the user can access it.
	 */
	if ( $sitewide_membership_id ) {

		$results['restriction_id'] = $sitewide_membership_id;
		$results['reason']         = 'sitewide_membership';

	} elseif ( 'lesson' === $post_type && $post && ! $post->is_free() ) {

		$results['restriction_id'] = $post->get_parent_course();
		$results['reason']         = 'enrollment_lesson';

	} elseif ( in_array( $post_type, array( 'course', 'llms_membership' ), true ) ) {

		$results['restriction_id'] = $post_id;
		$results['reason']         = sprintf( 'enrollment_%s', str_replace( 'llms_', '', $post_type ) );

	} else {

		$membership_id = llms_is_post_restricted_by_membership( $post_id, $user_id );
		if ( $membership_id ) {
			$results['restriction_id'] = $membership_id;
			$results['reason']         = 'membership';
		}
	}

	/**
	 * Filter restriction setup data before the user access is checked.
	 *
	 * This filter will allow application of restriction rules for custom post types
	 * or modification of the defaults as put forth above.
	 *
	 * @since Unknown.
	 *
	 * @param array $results {
	 *     Associative array of restriction information.
	 *
	 *     @type int    $content_id     WP_Post ID of the requested content post.
	 *     @type int    $restriction_id WP_Post ID of the post governing restriction over the requested content.
	 *     @type bool   $is_restricted  Whether or not the requested content is accessible by the requested user.
	 *     @type string $reason         A code describing the reason why the requested content is restricted.
	 * }
	 * @param int   $post_id WordPress Post ID of the content.
	 */
	$results = apply_filters( 'llms_page_restricted_before_check_access', $results, $post_id );

	/**
	 * Content should be restricted.
	 *
	 * We have a restriction ID and a reason for restriction and there's no logged in user
	 * or the logged in user does not have access.
	 */
	if ( ! empty( $results['restriction_id'] ) && 'accessible' !== $results['reason'] && ( ! $student || ! $student->is_enrolled( $results['restriction_id'] ) ) ) {

		$results['is_restricted'] = true;

	} else {

		/**
		 * At this point student has access or the content isn't supposed to be restricted.
		 *
		 * We will perform additional checks for specific post types.
		 */
		if ( 'llms_quiz' === $post_type && llms_is_quiz_accessible( $post_id, $user_id ) ) {

			$results['is_restricted']  = true;
			$results['reason']         = 'quiz';
			$results['restriction_id'] = $post_id;

		} elseif ( in_array( $post_type, array( 'lesson', 'llms_quiz' ), true ) ) {

			$course_id = llms_is_post_restricted_by_time_period( $post_id, $user_id );
			if ( $course_id ) {

				$results['is_restricted']  = true;
				$results['reason']         = 'course_time_period';
				$results['restriction_id'] = $course_id;

			} else {

				$prereq_data = llms_is_post_restricted_by_prerequisite( $post_id, $user_id );
				if ( $prereq_data ) {

					$results['is_restricted']  = true;
					$results['reason']         = sprintf( '%s_prerequisite', $prereq_data['type'] );
					$results['restriction_id'] = $prereq_data['id'];

				} else {

					$lesson_id = llms_is_post_restricted_by_drip_settings( $post_id, $user_id );
					if ( $lesson_id ) {

						$results['is_restricted']  = true;
						$results['reason']         = 'lesson_drip';
						$results['restriction_id'] = $lesson_id;

					}
				}
			}
		}
	}

	// There's no restriction for the current user.
	if ( ! $results['is_restricted'] ) {
		$results['reason']         = 'accessible';
		$results['restriction_id'] = 0;
	}

	/**
	 * Allow filtering of the restricted results.
	 *
	 * @since Unknown
	 *
	 * @param array $results Restriction check result data.
	 * @param int   $post_id WordPress Post ID of the content.
	 */
	$results = apply_filters( 'llms_page_restricted', $results, $post_id );

	// Cache results if we have a user.
	if ( $user_id ) {
		wp_cache_set( $cache_key, $results, 'llms_page_restricted' );
	}

	return $results;

}

/**
 * Retrieve a list of memberships that a given post is restricted to
 *
 * @since [version]
 *
 * @param int $post_id WP_Post ID of the post.
 * @return int[] List of the WP_Post IDs of llms_membership post types. An empty array signifies the post
 *               has no restrictions.
 */
function llms_get_post_membership_restrictions( $post_id ) {

	$memberships = array();

	/**
	 * Filter the post types which cannot be restricted to a membership.
	 *
	 * These LifterLMS core post types are restricted via enrollment into that
	 * post (or it's parent post) directly so there won't be any related
	 * memberships for these post types.
	 *
	 * @since Unknown
	 *
	 * @param string[] $post_types Array of post type names.
	 */
	$skip_post_types = apply_filters(
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

	if ( ! in_array( get_post_type( $post_id ), $skip_post_types, true ) ) {
		$saved_ids = get_post_meta( $post_id, '_llms_restricted_levels', true );
		if ( llms_parse_bool( get_post_meta( $post_id, '_llms_is_restricted', true ) ) && is_array( $saved_ids ) ) {
			$memberships = array_map( 'absint', $saved_ids );
		}
	}

	/**
	 * Filter the list the membership restrictions for a given post.
	 *
	 * @since [version]
	 *
	 * @param int[] $memberships List of the WP_Post IDs of llms_membership post types.
	 * @param int   $post_id     WP_Post ID of the restricted post.
	 */
	return apply_filters( 'llms_get_post_membership_restrictions', $memberships, $post_id );

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
 * @since [version] Refactored and added filter on return value.
 *
 * @param int      $post_id WP Post ID of a lesson or quiz.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return int|bool False if the lesson is available.
 *                  WP Post ID of the lesson if it is not.
 */
function llms_is_post_restricted_by_drip_settings( $post_id, $user_id = null ) {

	$restriction = false;

	$post_type = get_post_type( $post_id );
	$lesson_id = 'lesson' === $post_type ? $post_id : false;
	if ( 'llms_quiz' === $post_type ) {
		$quiz      = llms_get_post( $post_id );
		$lesson_id = $quiz ? $quiz->get( 'lesson_id' ) : false;
	}

	if ( $lesson_id ) {
		$lesson = llms_get_post( $lesson_id );
		if ( $lesson && ! $lesson->is_available() ) {
			$restriction = $lesson_id;
		}
	}

	/**
	 * Customize the restriction information for a post's drip restrictions
	 *
	 * @since [version]
	 *
	 * @param int|bool $restriction False if the lesson is available.
	 *                              WP Post ID of the lesson if it is not.
	 * @param int      $post_id WP Post ID of a lesson or quiz.
	 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
	 */
	return apply_filters( 'llms_is_post_restricted_by_drip_settings', $restriction, $post_id, $user_id );

}

/**
 * Determine if a lesson/quiz is restricted by a prerequisite lesson.
 *
 * @since 3.0.0
 * @since 3.16.11 Unknown.
 * @since [version] Refactored and added filter to return.
 *
 * @param int      $post_id WP Post ID of a lesson or quiz.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return array|bool Returns `false` if the post is not restricted or the user has completed all applicable prerequisites,
 *                     when at least one incomplete prerequisite has been found, returns an associative array describing the
 *                     prerequisite type and object id.
 */
function llms_is_post_restricted_by_prerequisite( $post_id, $user_id = null ) {

	$restriction = false;

	$post_type = get_post_type( $post_id );
	$lesson_id = 'lesson' === $post_type ? $post_id : false;
	if ( 'llms_quiz' === $post_type ) {
		$quiz      = llms_get_post( $post_id );
		$lesson_id = $quiz ? $quiz->get( 'lesson_id' ) : false;
	}

	if ( $lesson_id ) {
		$lesson = llms_get_post( $lesson_id );
		$course = $lesson ? $lesson->get_course() : false;

		if ( $course ) {

			// Get an array of all possible prereqs.
			$prerequisites = array();

			// Course prereqs.
			foreach ( array( 'course', 'course_track' ) as $prereq_type ) {
				if ( $course->has_prerequisite( $prereq_type ) ) {
					$prerequisites[] = array(
						'id'   => $course->get_prerequisite_id( $prereq_type ),
						'type' => $prereq_type,
					);
				}
			}

			// Lesson prereqs.
			if ( $lesson->has_prerequisite() ) {
				$prerequisites[] = array(
					'id'   => $lesson->get_prerequisite(),
					'type' => 'lesson',
				);
			}

			if ( $prerequisites ) {

				$student = llms_get_student( $user_id );

				foreach ( $prerequisites as $prereq ) {

					if ( ! $student || ! $student->is_complete( $prereq['id'], $prereq['type'] ) ) {
						$restriction = $prereq;
						break;
					}
				}
			}
		}
	}

	/**
	 * Filter restriction data determining if a post is restricted by a prerequisite
	 *
	 * @since [version]
	 *
	 * @param array|bool $restriction Restriction result: `false` if the post is not restricted or the user has
	 *                                completed all applicable prerequisites, when at least one incomplete prerequisite
	 *                                has been found, returns an associative array describing the prerequisite type and object id.
	 * @param int        $post_id     WP Post ID of a lesson or quiz.
	 * @param int|null   $user_id     Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
	 */
	return apply_filters( 'llms_is_post_restricted_by_prerequisite', $restriction, $post_id, $user_id );

}

/**
 * Determine if a course (or lesson/quiz) is "open" according to course time period settings.
 *
 * @since 3.0.0
 * @since 3.16.11 Unknown.
 * @since [version] Refactored to utilize `llms_get_post_parent_course()`.
 *              Added filter on return.
 *
 * @param int      $post_id WP Post ID of a course, lesson, or quiz.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return int|bool False if the post is not restricted by course time period,
 *                  WP Post ID of the course if it is.
 */
function llms_is_post_restricted_by_time_period( $post_id, $user_id = null ) {

	$restriction = false;

	$course = 'course' === get_post_type( $post_id ) ? llms_get_post( $post_id ) : llms_get_post_parent_course( $post_id );
	if ( $course ) {
		$restriction = $course->is_open() ? false : $course->get( 'id' );
	}

	/**
	 * Filter whether or not a give post is restricted as a result of a time period restriction.
	 *
	 * @since [version]
	 *
	 * @param int|bool $restriction False if the post is not restricted by course time period,
	 *                              WP Post ID of the course if it is.
	 * @param int      $post_id     WP_Post ID of the post being checked.
	 * @param int      $user_id     WP_User ID of the user.
	 */
	return apply_filters( 'llms_is_post_restricted_by_time_period', $restriction, $post_id, $user_id );

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

	$restriction = false;

	$memberships = llms_get_post_membership_restrictions( $post_id );
	if ( $memberships ) {

		$student = $user_id ? llms_get_student( $user_id ) : false;
		if ( ! $student ) {
			$restriction = array_shift( $memberships );
		} else {

			/**
			 * Reverse to ensure a user enrolled in none of the memberships,
			 * encounters the same restriction settings as a visitor.
			 */
			foreach ( array_reverse( $memberships ) as $mid ) {

				// Set this as the restriction id.
				$restriction = $mid;

				/*
				 * Once we find the student has access break the loop,
				 * this will be the restriction that the template loader will check against later.
				 */
				if ( $student->is_enrolled( $mid ) ) {
					break;
				}
			}
		}
	}

	/**
	 * Filter the result of `llms_is_post_restricted_by_sitewide_membership()`
	 *
	 * @since [version]
	 *
	 * @param bool|int $restriction Restriction result. WP_Post ID of the membership or `false` when there's no restriction.
	 * @param int      $post_id     WP_Post ID of the requested post.
	 * @param int|null $user_id     WP_User ID of the requested user.
	 */
	return apply_filters( 'llms_is_post_restricted_by_membership', $restriction, $post_id, $user_id );

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
 * @since [version] Refactored to reduce complexity and remove nested conditions.
 *              Added filter on return.
 *
 * @param int      $post_id WP Post ID.
 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
 * @return bool|int If the post is not restricted (or there are not sitewide membership restrictions) returns false.
 *                  If the post is restricted, returns the membership id required.
 */
function llms_is_post_restricted_by_sitewide_membership( $post_id, $user_id = null ) {

	// Return value.
	$restriction = false;

	$membership_id = absint( get_option( 'lifterlms_membership_required', '' ) );
	$membership    = $membership_id ? llms_get_post( $membership_id ) : false;

	// site is restricted to a membership.
	if ( $membership && is_a( $membership, 'LLMS_Membership' ) ) {

		// Restricted contents redirection page id, if any.
		$redirect_page_id = 'page' === $membership->get( 'restriction_redirect_type' ) ? absint( $membership->get( 'redirect_page_id' ) ) : 0;

		$bypass_ids = array(
			$membership_id, // The membership page the site is restricted to.
			get_option( 'lifterlms_terms_page_id' ), // Terms and conditions.
			llms_get_page_id( 'memberships' ), // Membership archives.
			llms_get_page_id( 'myaccount' ), // Student dashboard.
			llms_get_page_id( 'checkout' ), // Checkout page.
			get_option( 'wp_page_for_privacy_policy' ), // WP Core privacy policy page.
			$redirect_page_id, // Restricted contents redirection page id.
		);

		$bypass_ids = array_filter( array_map( 'absint', $bypass_ids ) );

		/**
		 * Filter a list of sitewide membership restriction post IDs.
		 *
		 * Any post id found in this will be accessible regardless of user enrollment into the
		 * site's sitewide membership restriction.
		 *
		 * Note: Post IDs are evaluated with a strict comparator. When filtering ensure that
		 * additional IDs are added to the array as integers, not numeric strings!
		 *
		 * @since Unknown
		 *
		 * @param int[] $bypass_ids Array of WP_Post IDs.
		 */
		$allowed = apply_filters( 'lifterlms_sitewide_restriction_bypass_ids', $bypass_ids );

		$restriction = in_array( $post_id, $allowed, true ) ? false : $membership_id;

	}

	/**
	 * Filter the result of `llms_is_post_restricted_by_sitewide_membership()`
	 *
	 * @since [version]
	 *
	 * @param bool|int $restriction Restriction result. WP_Post ID of the sitewide membership or `false` when there's no restriction.
	 * @param int      $post_id     WP_Post ID of the requested post.
	 * @param int|null $user_id     WP_User ID of the requested user.
	 */
	return apply_filters( 'llms_is_post_restricted_by_sitewide_membership', $restriction, $post_id, $user_id );

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

	// no lesson or the user is not enrolled.
	if ( ! $lesson_id || ! llms_is_user_enrolled( $user_id, $lesson_id ) ) {
		return $post_id;
	}

	return false;

}
