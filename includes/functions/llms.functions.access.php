<?php
/**
* Functions used for managing page / post access
*
* @author  LifterLMS
* @since   1.0.0
* @version 1.0.0
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
 * @version  3.0.0
 */
function llms_page_restricted( $post_id ) {

	$results = array(
		'content_id' => $post_id,
		'is_restricted' => false,
		'reason' => 'accessible',
		'restriction_id' => 0,
	);

	$student = false;
	if ( get_current_user_id() ) {
		$student = new LLMS_Student();
	}

	$post_type = get_post_type( $post_id );

	/**
	 * Do checks to determine if the content should be restricted
	 */

	// content is restricted by a sitewide membership
	if ( $membership_id = llms_is_post_restricted_by_sitewide_membership( $post_id ) ) {
		$restriction_id = $membership_id;
		$reason = 'sitewide_membership';
	} // content is restricted by a membership
	elseif ( $membership_id = llms_is_post_restricted_by_membership( $post_id ) ) {
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
	}

	/**
	 * Allow filtering of results before checking if the student has access
	 */
	$results = apply_filters( 'llms_page_restricted_before_check_access', $results, $post_id );

	/**
	 * Content should be restricted, so we'll do the restriction checks
	 * and return restricted results
	 *
	 * this is run if we have a restriction and a reason for restriction
	 * and we either don't have a logged in student or the logged in student doesn't have access
	 */
	if ( isset( $restriction_id ) && isset( $reason ) && ( ! $student || ! $student->has_access( $restriction_id ) ) ) {

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

		if ( 'lesson' === $post_type || 'llms_quiz' === $post_type ) {

			if ( $course_id = llms_is_post_restricted_by_time_period( $post_id ) ) {

				$results['is_restricted'] = true;
				$results['reason'] = 'course_time_period';
				$results['restriction_id'] = $course_id;
				return $results;

			}

			if ( $lesson_id = llms_is_post_restricted_by_prerequisite( $post_id ) ) {

				$results['is_restricted'] = true;
				$results['reason'] = 'lesson_prerequisite';
				$results['restriction_id'] = $lesson_id;
				return $results;

			}

		}

		if ( 'lesson' === $post_type ) {

			if ( $lesson_id = llms_is_post_restricted_by_drip_settings( $post_id ) ) {

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
 * Determine if a lesson/quiz is restricted by drip settings
 * @param    int     $post_id  WP Post ID of a lesson or quiz
 * @return   int|false         false if the lesson is available
 *                             WP Post ID of the lesson if it is not
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_post_restricted_by_drip_settings( $post_id ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, lesson id is the post id
	if ( 'lesson' === $post_type ) {
		$lesson_id = $post_id;
	} // quizzes need to cascade up to get lesson id
	elseif ( 'llms_quiz' == $post_type ) {
		$quiz = new LLMS_Quiz( $post_id );
		$lesson_id = $quiz->get_assoc_lesson( get_current_user_id() );
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
function llms_is_post_restricted_by_prerequisite( $post_id ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, lesson id is the post id
	if ( 'lesson' === $post_type ) {
		$lesson_id = $post_id;
	} // quizzes need to cascade up to get lesson id
	elseif ( 'llms_quiz' == $post_type ) {
		$quiz = new LLMS_Quiz( $post_id );
		$lesson_id = $quiz->get_assoc_lesson( get_current_user_id() );
	} // dont pass other post types in here dumb dumb
	else {
		return false;
	}

	$lesson = new LLMS_Lesson( $lesson_id );

	if ( $lesson->has_prerequisite() ) {

		$uid = get_current_user_id();

		$prerequisite_id = $lesson->get( 'prerequisite' );

		// not logged in, so send the prereq id back
		if ( ! $uid ) {
			return $prerequisite_id;
		} // student is logged in, check completion of the preq
		// if incomplete, send the prereq id
		// otherwise return false
		else {
			$student = new LLMS_Student( $uid );
			return ( ! $student->is_complete( $prerequisite_id, 'lesson' ) ) ? $prerequisite_id : false;
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
function llms_is_post_restricted_by_time_period( $post_id ) {

	$post_type = get_post_type( $post_id );

	// if we're on a lesson, get course information
	if ( 'lesson' === $post_type ) {

		$lesson = new LLMS_Lesson( $post_id );
		$course_id = $lesson->get_parent_course();

	} // quizzes need to cascade up to get course info
	elseif ( 'llms_quiz' == $post_type ) {

		$quiz = new LLMS_Quiz( $post_id );
		$lesson = new LLMS_Lesson( $quiz->get_assoc_lesson( get_current_user_id() ) );
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
function llms_is_post_restricted_by_membership( $post_id ) {

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

	$page_restrictions = get_post_meta( $post_id, '_llms_restricted_levels', true );

	if ( $page_restrictions && is_array( $page_restrictions ) ) {

		return absint( array_shift( $page_restrictions ) );

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
function llms_is_post_restricted_by_sitewide_membership( $post_id ) {

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
 * @todo  deprecate remaining functions in this file
 */
return;


























// course_end_date_in_past()
// course_start_date_in_future()
// function llms_check_course_date_restrictions( $course_id ) {

// 	$course = new LLMS_Course( $course_id );

// 	$start = $course->get_start_date( $post_id );
// 	$end = $course->get_end_date( $post_id );

// 	$now = current_time( 'timestamp' );



// 		if ( $end_date != '' ) {
// 			$todays_date = current_time( 'mysql' );

// 			if ($todays_date > $end_date) {
// 				$course_in_past = true;
// 			}
// 		}

// 		// break out and display an error
// 		// TODO should this take the drip feed into account, I would assume so...
// 		if ($course_in_past) {
// 			$end_date_formatted = LLMS_Date::pretty_date( $end_date );
// 			do_action( 'lifterlms_content_restricted_by_end_date', $end_date_formatted );
// 		}

// 		return $course_in_past;
// 	}




// 		$course = new LLMS_Course( $post_id );
// 		$start_date =

// 		$course_in_future = false;

// 		if (current_time( 'mysql' ) < $start_date) {
// 			$course_in_future = true;
// 		}

// 		return $course_in_future;
// 	}

// }

/**
 * Checks if user has ability to view quiz
 *
 * @return bool [Can user view quiz]
 */
function quiz_restricted() {

	$quiz = LLMS()->session->get( 'llms_quiz' );

	if ( $quiz && $quiz->end_date == '' ) {
		return false;
	} else {
		return true;
	}
}



/**
 * Checks if user is a member of the membership post they are viewing
 * @return [type] [description]
 */
function membership_page_restricted() {

	global $post;

	$restricted = true;

	if (is_single() && $post->post_type === 'llms_membership') {
		if ( is_user_logged_in() ) {
			$user_memberships = get_user_meta( get_current_user_id(), '_llms_restricted_levels', true );

			if ( $user_memberships && in_array( $post->ID, $user_memberships ) ) {
				$restricted = false;
			}
		}
	}

	return $restricted;
}


/**
 * Custom restriction for bbpress topics
 * @param  [type]  $post [description]
 * @return boolean       [description]
 */
function is_topic_restricted( $post ) {

	$page_restrictions = array();

	if (isset( $post->post_type ) && $post->post_type === 'topic') {

		$parent_id = wp_get_post_parent_id( $post->ID );

		if ($parent_id) {
			$page_restrictions = get_post_meta( $parent_id, '_llms_restricted_levels', true );
			llms_log( $page_restrictions );
		}
	}

	return $page_restrictions;

}

/**
 * Get membership levels associated with post / page
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return array          [Membership levels associated with post / page]
 */
function llms_get_post_memberships( $post_id ) {
	$memberships = get_post_meta( $post_id, '_llms_restricted_levels', true );
	return $memberships;
}

/**
 * Queries course membership level if post type is lesson
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return array [membership levels associated with parent course]
 */
function llms_get_parent_post_memberships( $post_id ) {
	$lesson = new LLMS_Lesson( $post_id );
	$parent_id = $lesson->get_parent_course();
	$memberships = get_post_meta( $parent_id, '_llms_restricted_levels', true );
	return $memberships;
}

/**
 * Checks if parent course membership should restrict user from viewing content
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool [Restrict access to user?]
 */
function parent_page_restricted_by_membership( $post_id ) {
	$post = get_post( $post_id );
	$restrict_access = false;

	if ($post->post_type == 'lesson') {

		$lesson = new LLMS_Lesson( $post_id );
		$parent_course = $lesson->get_parent_course();

		if ( page_restricted_by_membership( $parent_course ) ) {

			$restrict_access = true;

		}

	}

	return $restrict_access;

}

/**
 * Checks if lesson or course has outstanding prerequisites that need to be met
 *
 * @param  int $user_id [ID of the current user]
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool $result [Does post have outstanding prerequisite?]
 */
function outstanding_prerequisite_exists( $user_id, $post_id ) {
	$user = new LLMS_Person;

	$result = false;
	$post = get_post( $post_id );

	if ( $post->post_type == 'course' ) {

		$current_post = new LLMS_Course( $post->ID );

		$result = find_prerequisite( $user_id, $current_post );

	}
	if ( $post->post_type == 'lesson' ) {

		$current_post = new LLMS_Lesson( $post->ID );

		$parent_course_id = $current_post->get_parent_course();

		$parent_course = new LLMS_Course( $parent_course_id );

		$result = find_prerequisite( $user_id, $parent_course );

		if ( ! $result) {
			$result = find_prerequisite( $user_id, $current_post );
		}

	}

	return $result;

}

/**
 * Queries post metadata for prerequisite
 *
 * @param  int $user_id [ID of current user]
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool $prerequisite_exists [Does a prerequisite exist for post?]
 */
function find_prerequisite( $user_id, $post ) {
	$user = new LLMS_Person;

	$course = new LLMS_Course( $post->id );
	$p = $course->get_prerequisite();

	$prerequisite_exists = false;
	$initial_prereq = false;

	if ($prerequisite_id = $course->get_prerequisite()) {
		$prerequisite_exists = true;

		$prerequisite = get_post( $prerequisite_id );
		$user_postmetas = $user->get_user_postmeta_data( $user_id, $prerequisite->ID );

		if ( isset( $user_postmetas ) ) {

			foreach ( $user_postmetas as $key => $value ) {

				if ( isset( $user_postmetas['_is_complete'] ) && $user_postmetas['_is_complete']->post_id == $prerequisite_id) {
					$prerequisite_exists = false;
				}
			}
		}
		$initial_prereq = $prerequisite_exists;
	}
	if ($prerequisite_id = $course->get_prerequisite_track()) {
		$prerequisite_exists = true;

		$args = array(
			'posts_per_page' 	=> 1000,
			'post_type' 		=> 'course',
			'nopaging' 			=> true,
			'post_status' 		=> 'publish',
			'orderby'          	=> 'post_title',
			'order'            	=> 'ASC',
			'suppress_filters' 	=> true,
			'tax_query' => array(
				array(
					'taxonomy' 	=> 'course_track',
					'field'		=> 'term_id',
					'terms'		=> $prerequisite_id,
				),
			),
		);
		$prerequisites = get_posts( $args );
		$prerequisite_exists = false;
		foreach ($prerequisites as $prerequisite) {
			$user_postmetas = $user->get_user_postmeta_data( $user_id, $prerequisite->ID );

			if ( isset( $user_postmetas ) ) {

				foreach ( $user_postmetas as $key => $value ) {

					if ( ! isset( $user_postmetas['_is_complete'] ) && $user_postmetas['_is_complete']->post_id == $prerequisite->ID) {
						$prerequisite_exists = true;
					}
				}
			} else {
				$prerequisite_exists = true;
			}
		}
	}

	return ($initial_prereq || $prerequisite_exists);

}


/**
 * Queries course metadata to get the date the user enrolled.
 *
 * @param  int $user_id [ID of current user]
 * @param  int $post_id [ID of current post or page]
 *
 * @return datetime $start_date [Start Date in M, d, Y format] or empty string if user is not enrolled.
 */
function llms_get_course_enrolled_date( $user_id, $post_id ) {
		$post = get_post( $post_id );

		$course_id = -1;
	if ($post->post_type == 'course') {
		$course_id = $post_id;
	} else if ($post->post_type == 'lesson') {
		$lesson = new LLMS_Lesson( $post->ID );
		$course_id = $lesson->get_parent_course();
	}

		$start_date = '';
		$llms_person = new LLMS_Person();
		$user_postmetas = $llms_person->get_user_postmeta_data( $user_id, $course_id );

	if ( isset( $user_postmetas['_status'] ) ) {
		if ( $user_postmetas['_status']->meta_value == 'Enrolled' ) {
			$start_date = date( 'Y-m-d', strtotime( $user_postmetas['_status']->updated_date ) );
		}
	}

		return $start_date;
}





/**
 * Returns the start date for the lesson
 * Returns the date the lesson can start
 * If drip days are set it calculates the drip days
 *
 * @param  int $user_id [ID of current user]
 * @param  int $post_id [ID of lesson]
 *
 * @return datetime $lesson_start_date [Start Date in M, d, Y format]
 */
function llms_get_lesson_start_date( $user_id, $post_id ) {

	$lesson = new LLMS_Lesson( $post_id );
	$course_id = $lesson->get_parent_course();
	$course = new LLMS_Course( $course_id );

	//get the course start date
	//get the date the user enrolled
	$course_start_date = $course->get_start_date();
	$user_enrolled_date = $course->get_user_enroll_date( $user_id );
	$drip_days = $lesson->get_drip_days();

	//get the greater of the two dates
	if ( $course_start_date > $user_enrolled_date ) {
		$start_date = $course_start_date;
	} else {
		$start_date = $user_enrolled_date;
	}

	//add drip days
	$start_date = LLMS_Date::db_date( $start_date . '+ ' . $drip_days . ' days' );

	return $start_date;
}


/**
 * Checks if lesson start date is greater than current date.
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool $result [Does the lesson have a future start date?]
 */
function lesson_start_date_in_future( $user_id, $post_id ) {
	return course_end_date_in_past( $post_id ) || (date_create( current_time( 'mysql' ) ) < date_create( llms_get_lesson_start_date( $user_id, $post_id ) ));
}






/**
 * On screen notice passed to user when page is restricted by membership
 *
 * @param  int $membership_id [ID of the membership]
 *
 * @return void
 */
function page_restricted_by_membership_alert( $membership_id ) {

	$required_membership_name = get_the_title( $membership_id );

	llms_add_notice( sprintf( __( '%s membership is required to view this content.', 'lifterlms' ),
	$required_membership_name ) );

}
add_action( 'lifterlms_content_restricted_by_membership', 'page_restricted_by_membership_alert' );


/**
 * Checks if user has the membership level required to view the post / page
 *
 * @param  int $user_id [ID of the current user]
 * @param  int $post_id [ID of the post / page]
 *
 * @return bool $is_member [Does the user have the required membership level required to view page / post?]
 */
function llms_is_user_member( $user_id, $post_id ) {
	$user_memberships = get_user_meta( $user_id, '_llms_restricted_levels', true );

	if ( empty( $user_memberships ) ) {
		return false;
	} else {
		foreach ( $user_memberships as $value ) {

			if ( $post_id == $value ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Checks if user has the membership level required to enroll in course
 *
 * @param  int $user_id [ID of the current user]
 * @param  int $post_id [ID of the course]
 *
 * @return bool $is_member [Does the user have the required membership level required to enroll in course?]
 */
function llms_does_user_memberships_contain_course( $user_id, $post_id ) {
	$memberships_required = get_post_meta( $post_id, '_llms_restricted_levels', true );

	if ( empty( $memberships_required ) ) {
		return false;
	} else {
		foreach ( $memberships_required as $membership_id ) {

			if ( llms_is_user_member( $user_id, $membership_id ) ) {
				return true;
			}
		}
	}

	return false;
}
