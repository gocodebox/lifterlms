<?php
/**
* Page functions
*
* Functions used for managing page access
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
function llms_page_restricted($post_id) {

	$post = get_post($post_id);
	$restricted = false;
	$reason = '';

	if ( ! current_user_can( 'manage_options' ) ) {

		if (site_restricted_by_membership($post_id)) {
			$restricted = true;
			$reason = 'site_wide_membership';
		}
	
		elseif ( page_restricted_by_membership($post_id) ) {
			$restricted = true;
			$reason = 'membership';
		}
		
		elseif ( is_single() && $post->post_type == 'lesson' ) {

			if( parent_page_restricted_by_membership($post_id) ) {

				$restricted = true;
				$reason = 'parent_membership';
			}
			elseif ( ! llms_is_user_enrolled( get_current_user_id(), $post_id ) ) {
				$restricted = true;
				$reason = 'enrollment';
			}
			elseif ( outstanding_prerequisite_exists(get_current_user_id(), $post_id) ) {

				$restricted = true;
				$reason = 'prerequisite';
			}
			elseif ( lesson_start_date_in_future(get_current_user_id(), $post_id ) ) {

				$restricted = true;
				$reason = 'lesson_start_date';
			}
		}
		elseif ( is_single() && $post->post_type == 'course') {
			
			if ( ! llms_is_user_enrolled( get_current_user_id(), $post_id ) ) {
				$restricted = true;
				$reason = 'enrollment';
			}
			elseif ( outstanding_prerequisite_exists(get_current_user_id(), $post_id) ) {

				$restricted = true;
				$reason = 'prerequisite';
			}
			elseif ( course_start_date_in_future($post_id) ) {

				$restricted = true;
				$reason = 'course_start_date';
			} 
			elseif ( course_end_date_in_past($post_id) ) {

				$restricted = true;
				$reason = 'course_end_date';
			}
		}
	}

	$results = array(
		'id' => $post_id,
		'is_restricted' => $restricted,
		'reason' => $reason
	);

	return $results;
	
}

function site_restricted_by_membership($post_id) {

	//check if membership is required
	$membership_required = get_option('lifterlms_membership_required', '');

	//if it's not required return false
	if (!$membership_required || $membership_required == '') {
		return false;
	}

	if ($membership_required && !$membership_required == '') {
		if( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$user_memberships = get_user_meta( $user_id, '_llms_restricted_levels', true );

			if ( $user_memberships && in_array($membership_required, $user_memberships) ) {
				return false;
			}
		}

	}

	$post = get_post($post_id);
	//if page is not account, purchase, memberships, or is of post type llms_memberships restrict content
	if ($post->post_type == 'llms_membership') {
		return false;
	}
	elseif ( is_post_type_archive( 'llms_membership' ) ) {
		return false;
	}
	elseif ( is_page( llms_get_page_id( 'memberships' ) ) ) {
		return false;
	}
	elseif ( is_page( llms_get_page_id( 'myaccount' ) ) ) {
		return false;
	}
	elseif ( is_page( llms_get_page_id( 'checkout' ) ) ) {
		return false;
	}
	//get site restricted memberships
	return true;
}


//any content
//membership restriction
function page_restricted_by_membership($post_id) {
	$post = get_post($post_id);
	$userid = get_current_user_id();
	$membership_required = get_option('lifterlms_membership_required', '');


	$restrict_access = false;
	$membership_id = '';

	//are there membership restictions on page
	$page_restrictions = get_post_meta( $post_id, '_llms_restricted_levels', true );

	// membership restrictions exist
	if ( ! empty($page_restrictions) ) {
		$restrict_access = true;
		
		//is user logged in 
		if ( is_user_logged_in() ) {
			$user_memberships = get_user_meta( $userid, '_llms_restricted_levels', true );

			//does user have any membership levels
			if( ! empty($user_memberships) ) {

				foreach ( $page_restrictions as $key => $value ){
					if ( in_array($value, $user_memberships) ){
						$restrict_access = false;	
					}
					else if ( $membership_required && !$membership_required == '') {
						if ( in_array($membership_required , $user_memberships) ){
							$restrict_access = false;	
						}
					}	
				}
				//if post type is course and user is enrolled then do not restrict content.
				if ($post->post_type == 'course' ) {
					if ( llms_is_user_enrolled( $userid, $post->id) ) {
						$restrict_access = false;
					}
				}
			}
		}
	}

	return $restrict_access;
}

function llms_get_post_memberships($post_id) {
	$memberships = get_post_meta( $post_id, '_llms_restricted_levels', true );
	return $memberships;
}

function llms_get_parent_post_memberships($post_id) {
	$lesson = new LLMS_Lesson($post_id);
	$parent_id = $lesson->get_parent_course();
	$memberships = get_post_meta( $parent_id, '_llms_restricted_levels', true );
	return $memberships;
}

function parent_page_restricted_by_membership($post_id) {
	$post = get_post( $post_id );
	$restrict_access = false;


	if ($post->post_type == 'lesson') {

		$lesson = new LLMS_Lesson($post_id);
		$parent_course = $lesson->get_parent_course();

		if ( page_restricted_by_membership($parent_course) ) {

			$restrict_access = true;
		}
	}

	return $restrict_access;

}

	

function outstanding_prerequisite_exists($user_id, $post_id) {
	$user = new LLMS_Person;

	$result = false;
	$post = get_post( $post_id );

	if ( $post->post_type == 'course' ) {


		$current_post = new LLMS_Course($post->ID);

		$result = find_prerequisite($user_id, $current_post);

	}
	if ( $post->post_type == 'lesson' ) {

		$current_post = new LLMS_Lesson($post->ID);

		$parent_course_id = $current_post->get_parent_course();

		$parent_course = new LLMS_Course($parent_course_id);

		$result = find_prerequisite($user_id, $parent_course );

		if (! $result) {
			$result = find_prerequisite($user_id, $current_post);
		}

	}
	
	return $result;	

}


function find_prerequisite( $user_id, $post ) {
	$user = new LLMS_Person;

	$lesson = new LLMS_Lesson($post->id);
	$p = $lesson->get_prerequisite();

	$prerequisite_exists = false;

	if ($prerequisite_id = $lesson->get_prerequisite()) {


		$prerequisite_exists = true;

		$prerequisite = get_post( $prerequisite_id );
		$user_postmetas = $user->get_user_postmeta_data( $user_id, $prerequisite->ID );

		if ( isset($user_postmetas) ) {
	
			foreach( $user_postmetas as $key => $value ) {
				
				if ( isset($user_postmetas['_is_complete']) && $user_postmetas['_is_complete']->post_id == $prerequisite_id) {
					$prerequisite_exists = false;
				}
			}
		}
	}

	return $prerequisite_exists;

}

function llms_get_prerequisite($user_id, $post_id) {
	$user = new LLMS_Person;
	$post = get_post( $post_id );

	if ( $post->post_type == 'course' ) {


		$current_post = new LLMS_Course($post->ID);

		$result = find_prerequisite($user_id, $current_post);

	}
	if ( $post->post_type == 'lesson' ) {

		$current_post = new LLMS_Lesson($post->ID);

		$parent_course_id = $current_post->get_parent_course();

		$parent_course = new LLMS_Course($parent_course_id);
		$prerequisite_id = $parent_course->get_prerequisite();
		$prerequisite = get_post( $prerequisite_id );

		if ( empty($prerequisite_id) ) {
			$prerequisite_id = $current_post->get_prerequisite();
			$prerequisite = get_post( $prerequisite_id);
		}
	}

	return $prerequisite;
}


function llms_get_course_start_date($post_id) {
	$post = get_post($post_id);
	$start_date = get_metadata('post', $post->ID, '_course_dates_from', true);
	$start_date = date('M d, Y', $start_date);
	return $start_date;
}

function llms_get_course_end_date($post_id) {
	$post = get_post($post_id);
	$end_date = get_metadata('post', $post->ID, '_course_dates_to', true);
	$end_date = date('M d, Y', $end_date);
	return $end_date;
}



function course_start_date_in_future($post_id) {
	$post = get_post($post_id);
	$course_in_future = false;


	$start_date = get_metadata('post', $post->ID, '_course_dates_from', true);

	if ( $start_date != '' ) {
		
		$todays_date =  strtotime('today');

		if ($todays_date < $start_date) {

			$course_in_future = true;

		}

	}

	return $course_in_future;

}

function course_end_date_in_past($post_id) {
$post = get_post($post_id);

	$course_in_past = false;

	$end_date = get_metadata('post', $post->ID, '_course_dates_to', true);

	if ( $end_date != '' ) {
		
		$todays_date =  strtotime('today');

		if ($todays_date > $end_date) {

			$course_in_past = true;

		}
	}

	if ($course_in_past) {
		$end_date_formatted = date('M d, Y', $end_date);
		do_action('lifterlms_content_restricted_by_end_date', $end_date_formatted);
	}

	return $course_in_past;
}

function llms_get_lesson_start_date($post_id) {
	$lesson = new LLMS_Lesson($post_id);
	$drip_days = get_metadata('post', $post_id, '_days_before_avalailable', true);
	$lesson_start_date = date('M d, Y', strtotime(' +' . $drip_days . ' day'));
	return $lesson_start_date;
}

function lesson_start_date_in_future($user_id, $post_id) {

	$result = false;
	$lesson = new LLMS_Lesson($post_id);

	$parent_course = $lesson->get_parent_course();
	
	if ( course_start_date_in_future($parent_course) ) {

		$result = true;
	}
	elseif ( course_end_date_in_past($parent_course) ) {

		$result = true;
	}
	elseif ( null !== get_metadata('post', $post_id, '_days_before_avalailable', true) ) {

		$drip_days = get_metadata('post', $post_id, '_days_before_avalailable', true);

		$todays_date = date_create('today');

		$lesson_start_date = date('Y-n-j', strtotime(' +' . $drip_days . ' day'));
		$lesson_start_date = date_create($lesson_start_date);

		if ( $todays_date < $lesson_start_date ) {
	
				$result = true;
		}

	}

	return $result;
}

function lesson_restricted($lesson_id) {


}





add_action('lifterlms_content_restricted_by_membership', 'page_restricted_by_membership_alert'); 

function page_restricted_by_membership_alert($membership_id) {

	$required_membership_name = get_the_title( $membership_id );

	llms_add_notice( sprintf( __( '%s membership is required to view this content.', 'lifterlms' ), 
			$required_membership_name ) );

}

/**
 * Check if user is enrolled in course.
 *
 * @return bool
 */
function llms_is_user_enrolled( $user_id, $product_id ) {
	global $wpdb;
	$enrolled = false;

	$post = get_post( $product_id );
	if (!$post->post_type == 'lesson' || !$post->post_type == 'course') 
		return true;


	if ( is_user_logged_in() ) {
	
		if ( !empty($user_id) && !empty( $product_id ) ) {

			$user = new LLMS_Person;

			if ( $post->post_type == 'lesson' ) {
				$lesson = new LLMS_Lesson($post->ID);
				$product_id = $lesson->get_parent_course();
			}

			$user_postmetas = $user->get_user_postmeta_data( $user_id, $product_id );

			if (isset($user_postmetas['_status'])) {
				$course_status = $user_postmetas['_status']->meta_value;

				if ( $course_status == 'Enrolled' ) {
					$enrolled = true;
				}

			}
		}
	}

	return $enrolled;
}

function llms_is_user_member($user_id, $post_id) {
	$user_memberships = get_user_meta( $user_id, '_llms_restricted_levels', true );

	$is_member = false;

	if ( empty($user_memberships) ) {
		$is_member = false;

	}
	else {
		foreach ( $user_memberships as $key => $value ){

			if ( $post_id == $value){
				$is_member = true;
				
			}
		}
	}
	return $is_member;
}
