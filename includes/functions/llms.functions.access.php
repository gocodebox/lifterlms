<?php
/**
* Page functions
*
* Functions used for managing page / post access
*
* @author codeBOX
* @project lifterLMS
*/

/**
 * Main restriction function
 * Runs checks against restriction types based on page / post type.
 * returns array containing information about page restriction
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return array $results
 */
function llms_page_restricted( $post_id ) {

	$post = get_post( $post_id );
	$restricted = false;
	$reason = '';

	if ( ! current_user_can( 'manage_options' ) ) {

		if ( site_restricted_by_membership( $post_id ) ) {
			$restricted = true;
			$reason = 'site_wide_membership';
		} elseif ( page_restricted_by_membership( $post_id ) ) {
			$restricted = true;
			$reason = 'membership';
		} elseif ( is_single() && $post->post_type == 'lesson' ) {

			$l = new LLMS_Lesson( $post_id );
			if ( ! $l->get_is_free()) {
				if ( parent_page_restricted_by_membership( $post_id ) ) {
					$restricted = true;
					$reason = 'parent_membership';
				} elseif ( ! llms_is_user_enrolled( get_current_user_id(), $post_id ) ) {
					$restricted = true;
					$reason = 'enrollment_lesson';
				} elseif ( outstanding_prerequisite_exists( get_current_user_id(), $post_id ) ) {
					$restricted = true;
					$reason = 'prerequisite';
				} elseif ( lesson_start_date_in_future( get_current_user_id(), $post_id ) ) {
					$restricted = true;
					$reason = 'lesson_start_date';
				}
			}

		} elseif ( is_single() && $post->post_type == 'course') {

			if ( ! llms_is_user_enrolled( get_current_user_id(), $post_id ) ) {
				$restricted = true;
				$reason = 'enrollment';
			} elseif ( outstanding_prerequisite_exists( get_current_user_id(), $post_id ) ) {

				$restricted = true;
				$reason = 'prerequisite';
			} elseif ( course_start_date_in_future( $post_id ) ) {

				$restricted = true;
				$reason = 'course_start_date';
			} elseif ( course_end_date_in_past( $post_id ) ) {

				$restricted = true;
				$reason = 'course_end_date';
			}
		} elseif ( is_single() && ( $post->post_type == 'llms_question' || $post->post_type == 'llms_quiz' ) ) {
			if ( quiz_restricted() ) {
				$restricted = true;
				$reason = 'quiz_restricted';
			}
		} elseif ( is_single() && $post->post_type == 'llms_membership' ) {
			if ( membership_page_restricted() ) {
				$restricted = true;
				$reason = 'membership_page';
			}
		}
	}

	$results = array(
		'id' => $post_id,
		'is_restricted' => $restricted,
		'reason' => $reason,
	);

	return apply_filters( 'llms_page_restricted', $results );

}

/**
 * Checks if user has ability to view quiz
 *
 * @return bool [Can user view quiz]
 */
function quiz_restricted() {
	if ( LLMS()->session->get( 'llms_quiz' ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Checks if site is restricted by master membership
 * If site is restricted checks if user has authority to view current page.
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool [Can user view page based on membership restriction]
 */
function site_restricted_by_membership( $post_id ) {

	// if this is a course and the user is already enrolled, ignore the sitewide restriction
	// this allows users to buy a course that's available on it's own OR with a membership
	if ( 'course' == get_post_type( $post_id ) || 'lesson' == get_post_type( $post_id ) ) {

		if ( llms_is_user_enrolled( get_current_user_id(), $post_id ) ) {

			return false;

		}

	}

	// check if we should bypass restrictions because it's the t&c page
	$terms_page = get_option( 'lifterlms_terms_page_id' );
	if ($post_id === (int) $terms_page) {
		return false;
	}

	//check if membership is required
	$membership_required = get_option( 'lifterlms_membership_required', '' );

	//if it's not required return false
	if ( ! $membership_required || $membership_required == '') {
		return false;
	}

	if ($membership_required && ! $membership_required == '') {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$user_memberships = get_user_meta( $user_id, '_llms_restricted_levels', true );

			if ( $user_memberships && in_array( $membership_required, $user_memberships ) ) {
				return false;
			}
		}

	}

	$post = get_post( $post_id );
	//if page is not account, purchase, memberships, or is of post type llms_memberships restrict content
	if ( $post->post_type == 'llms_membership') {
		return false;
	} elseif ( is_post_type_archive( 'llms_membership' ) ) {
		return false;
	} elseif ( is_page( llms_get_page_id( 'memberships' ) ) ) {
		return false;
	} elseif ( is_page( llms_get_page_id( 'myaccount' ) ) ) {
		return false;
	} elseif ( is_page( llms_get_page_id( 'checkout' ) ) ) {
		return false;
	}
	//get site restricted memberships
	return true;
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
 * Checks if specific page / post is restricted by membership(s)
 * If page is restricted checks user authority to view content.
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool $resticted_access [Is page restricted by membership level]
 */
function page_restricted_by_membership( $post_id ) {

	$post = get_post( $post_id );

	$userid = get_current_user_id();
	$membership_required = get_option( 'lifterlms_membership_required', '' );

	$restrict_access = false;
	$membership_id = '';

	if ( is_single() || is_page() ) {

		// if this is a course and the user is already enrolled, ignore the sitewide restriction
		// this allows users to buy a course that's available on it's own OR with a membership
		if ( 'course' == get_post_type( $post_id ) || 'lesson' == get_post_type( $post_id ) ) {

			if ( llms_is_user_enrolled( get_current_user_id(), $post_id ) ) {

				return false;

			}

		}

		//are there membership restictions on page
		$page_restrictions = get_post_meta( $post_id, '_llms_restricted_levels', true );

		if ( ! $page_restrictions) {
			//check if page is a topic and restict if parent is restricted (bbpress)
			$page_restrictions = is_topic_restricted( $post );
		}

		// membership restrictions exist
		if ( ! empty( $page_restrictions ) ) {
			$restrict_access = true;

			//is user logged in
			if ( is_user_logged_in() ) {
				$user_memberships = get_user_meta( $userid, '_llms_restricted_levels', true );

				//does user have any membership levels
				if ( ! empty( $user_memberships ) ) {

					foreach ( $page_restrictions as $key => $value ) {
						if ( in_array( $value, $user_memberships ) ) {
							$restrict_access = false;
						} else if ( $membership_required && ! $membership_required == '') {
							if ( in_array( $membership_required , $user_memberships ) ) {
								$restrict_access = false;
							}
						}
					}
					//if post type is course and user is enrolled then do not restrict content.
					if ($post->post_type == 'course' ) {
						if ( llms_is_user_enrolled( $userid, $post->id ) ) {
							$restrict_access = false;
						}
					}
				}

			}
		}
	}

	return $restrict_access;
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
 * Checks if course end date is less than current date.
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool $course_in_past [Hast the course end date past?]
 */
function course_end_date_in_past( $post_id ) {
	$course_in_past = false;

	$course = new LLMS_Course( $post_id );
	$end_date = $course->get_end_date( $post_id ); //removed copy and past code here just becuase it was so glaring

	if ( $end_date != '' ) {
		$todays_date = current_time( 'mysql' );

		if ($todays_date > $end_date) {
			$course_in_past = true;
		}
	}

	// break out and display an error
	// TODO should this take the drip feed into account, I would assume so...
	if ($course_in_past) {
		$end_date_formatted = LLMS_Date::pretty_date( $end_date );
		do_action( 'lifterlms_content_restricted_by_end_date', $end_date_formatted );
	}

	return $course_in_past;
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
 * Checks if course start date is greater than current date.
 *
 * @param  int $post_id [ID of current post or page]
 *
 * @return bool $course_in_future [Does the course have a future start date?]
 */
function course_start_date_in_future( $post_id ) {
	$course = new LLMS_Course( $post_id );
	$start_date = $course->get_start_date( $post_id );

	$course_in_future = false;

	if (current_time( 'mysql' ) < $start_date) {
		$course_in_future = true;
	}

	return $course_in_future;
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
 * Checks if user is currently enrolled in course
 *
 * @param  int $user_id [ID of the current user]
 * @param  int $product_id [ID of the product ($course post id)]
 *
 * @return bool $enrolled [Is user currently enrolled in the course?]
 */
function llms_is_user_enrolled( $user_id, $product_id ) {

	$enrolled = false;

	if ( ! empty( $user_id ) && ! empty( $product_id ) ) {

		$product_type = get_post_type( $product_id );

		// only check the following post types
		if ( ! in_array( $product_type, array( 'course', 'lesson', 'llms_membership' ) ) ) {
			return false;
		}

		$user = new LLMS_Person;

		// get course ID if we're looking at a lesson
		if ( 'lesson' === $product_type ) {

			$lesson = new LLMS_Lesson( $product_id );
			$product_id = $lesson->get_parent_course();

		}

		// retrieve user postmeta data for the product
		$user_postmetas = $user->get_user_postmeta_data( $user_id, $product_id );

		// if status is returned
		if ( isset( $user_postmetas['_status'] ) && 'Enrolled' === $user_postmetas['_status']->meta_value ) {
			$enrolled = true;

		}

	}

	return $enrolled;
}

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
}
