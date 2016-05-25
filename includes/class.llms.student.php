<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Student Class
 *
 * Manages data and interactions with a LifterLMS Student
 *
 * @since   2.2.3
 */
class LLMS_Student {

	/**
	 * Student's WordPress User ID
	 * @var int
	 */
	private $user_id;


	/**
	 * Constructor
	 *
	 * If no user id provided, will attempt to use the current user id
	 *
	 * @param int $user_id WP User ID
	 * @return void
	 *
	 * @since  2.2.3
	 */
	public function __construct( $user_id = null ) {

		if ( ! $user_id && get_current_user_id() ) {

			$user_id = get_current_user_id();

		}

		$this->user_id = intval( $user_id );

	}


	/**
	 * Add the student to a LifterLMS Membership
	 * @param int $membership_id   WP Post ID of the membership
	 * @return  void
	 *
	 * @since  2.2.3
	 */
	private function add_membership_level( $membership_id ) {

		// add the user to the membership level
		$membership_levels = $this->get_membership_levels();
		array_push( $membership_levels, $membership_id );
		update_user_meta( $this->get_id(), '_llms_restricted_levels', $membership_levels );

		// if there's auto-enroll courses, enroll the user in those courses
		$autoenroll_courses = get_post_meta( $membership_id, '_llms_auto_enroll', true );
		if ( $autoenroll_courses ) {

			foreach ( $autoenroll_courses as $course_id ) {

				$this->enroll( $course_id, 'membership_' . $membership_id );

			}

		}

	}


	private function remove_membership_level( $membership_id, $status = 'Expired' ) {

		// remove the user from the membership level
		$membership_levels = $this->get_membership_levels();
		unset( $membership_levels[ $membership_id ] );
		update_user_meta( $this->get_id(), '_llms_restricted_levels', $membership_levels );

		global $wpdb;
		// locate all enrollments triggered by this membership level
		$q = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id AS FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d AND meta_key = '_enrollment_trigger' AND meta_value = %s",
			array( $this->get_id(), 'membership_' . $membership_id )
		), 'OBJECT_K' );

		$courses = array_keys( $q );

		if ( $courses ) {

			// loop through all the courses and update the enrollment status
			foreach ( $courses  as $course_id ) {
				$this->unenroll( $course_id, $status );
			}

		}

	}


	/**
	 * Enroll the student in a course or membership
	 * @param  int     $product_id   WP Post ID of the course or membership
	 * @param  string  $trigger      String describing the reason for enrollment
	 * @return boolean
	 *
	 * @see  llms_enroll_student()  calls this function without having to instantiate the LLMS_Student class first
	 *
	 * @since  2.2.3
	 * @version  2.8.0  added $trigger parameter
	 */
	public function enroll( $product_id, $trigger = 'unspecified' ) {

		do_action( 'before_llms_user_enrollment', $this->get_id(), $product_id );

		// can only be enrolled in the following post types
		$product_type = get_post_type( $product_id );
		if ( ! in_array( $product_type, array( 'course', 'llms_membership' ) ) ) {

			return false;

		}

		// check enrollemnt before enrolling
		// this will prevent duplicate enrollments
		if ( llms_is_user_enrolled( $this->get_id(), $product_id ) ) {

			return false;

		}

		// add the user postmeta for the enrollment
		if ( $this->insert_enrollment_postmeta( $product_id, $trigger ) ) {

			// trigger additional actions based off post type
			switch ( get_post_type( $product_id ) ) {

				case 'course':

					do_action( 'llms_user_enrolled_in_course', $this->get_id(), $product_id );

				break;

				case 'llms_membership':

					$this->add_membership_level( $product_id );
					do_action( 'llms_user_added_to_membership_level', $this->get_id(), $product_id );

				break;

			}

			return true;

		}

		return false;

	}


	/**
	 * Retrive the student's user id
	 * @return int
	 *
	 * @since  2.2.3
	 */
	public function get_id() {

		return $this->user_id;

	}


	/**
	 * Retrieve certificates that a user has earned
	 * @param  string $orderby field to order the returned results by
	 * @param  string $order   ordering method for returned results (ASC or DESC)
	 * @return array           array of objects
	 *
	 * @since  2.4.0
	 */
	public function get_certificates( $orderby = 'updated_date', $order = 'DESC' ) {

		$orderby = esc_sql( $orderby );
		$order = esc_sql( $order );

		global $wpdb;

		$r = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id, meta_value AS certificate_id, updated_date AS earned_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d and meta_key = '_certificate_earned' ORDER BY $orderby $order",
			$this->get_id()
		) );

		return $r;

	}


	/**
	 * Retrieve IDs of user's courses based on supplied criteria
	 *
	 * @param  array  $args query arguments
	 *                      @arg int    $limit    number of courses to return
	 *                      @arg string $orderby  table reference and field to order results by
	 *                      @arg string $order    result order (DESC, ASC)
	 *                      @arg int    $skip     number of results to skip for pagination purposes
	 *                      @arg string $status   filter results by enrollment status, "any", "enrolled", or "expired"
	 * @return array        "courses" will contain an array of course ids
	 *                      "more" will contain a boolean determining whether or not more courses are available beyond supplied limit/skip criteria
	 */
	public function get_courses( $args = array() ) {

		global $wpdb;

		$args = array_merge( array(
			'limit'   => 20,
			'orderby' => 'upm.updated_date',
			'order'   => 'DESC',
			'skip'    => 0,
			'status'  => 'any', // any, enrolled, expired
		), $args );

		// prepare status
		if ( 'any' !== $args['status'] ) {
			$status = $wpdb->prepare( ' AND upm.meta_value = %s', ucfirst( $args['status'] ) );
		} else {
			$status = '';
		}

		// add one to the limit to see if there's pagination
		$args['limit']++;

		// the query
		$q = $wpdb->get_results( $wpdb->prepare(
			"SELECT upm.post_id AS id
			 FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
			 JOIN {$wpdb->posts} AS p ON p.ID = upm.post_id
			 WHERE p.post_type = 'course'
			   AND upm.meta_key = '_status'
			   AND upm.user_id = %d
			   {$status}
			 ORDER BY {$args['orderby']} {$args['order']}
			 LIMIT %d, %d;
			", array(
				$this->get_id(),
				$args['skip'],
				$args['limit'],
			)
		), 'OBJECT_K' );

		$ids = array_keys( $q );
		$more = false;

		// if we hit our limit we have too many results, pop the last one
		if ( $args['limit'] === count( $ids ) ) {
			array_pop( $ids );
			$more = true;
		}

		// reset args to pass back for pagination
		$args['limit']--;

		$r = array(
			'limit' => $args['limit'],
			'more' => $more,

			'results' => $ids,
			'skip' => $args['skip'],
		);

		return $r;

	}


	/**
	 * Get the formatted date when a user initially enrolled in a product
	 * This will retrieve the *oldest* date in the database for the product
	 * @param  int    $product_id  WP Post ID of a course or membership
	 * @param  string $format      date format as accepted by php date()
	 * @return false|string        will return false if the user is not enrolled
	 */
	public function get_enrollment_date( $product_id, $format = 'M d, Y' ) {

		if ( ! $this->is_enrolled( $product_id ) ) {
			return false;
		}

		global $wpdb;

		// get the oldest recorded Enrollment date
		$q = $wpdb->get_var( $wpdb->prepare(
			"SELECT updated_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_status' AND user_id = %d AND post_id = %d ORDER BY updated_date ASC LIMIT 1",
			array( $this->get_id(), $product_id )
		) );

		return ( $q ) ? date( $format, strtotime( $q ) ) : false;

	}


	/**
	 * Get the current enrollment status of a student for a particular product
	 *
	 * @param  int $product_id WP Post ID of a Course, Lesson, or Membership
	 *
	 * @return false|string
	 *
	 * @since  3.0.0
	 */
	public function get_enrollment_status( $product_id ) {

		$product_type = get_post_type( $product_id );

		// only check the following post types
		if ( ! in_array( $product_type, array( 'course', 'lesson', 'llms_membership' ) ) ) {
			return false;
		}

		// get course ID if we're looking at a lesson
		if ( 'lesson' === $product_type ) {

			$lesson = new LLMS_Lesson( $product_id );
			$product_id = $lesson->get_parent_course();

		}

		global $wpdb;

		// get the most recent recorded status
		$q = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_status' AND user_id = %d AND post_id = %d ORDER BY updated_date DESC LIMIT 1",
			array( $this->get_id(), $product_id )
		) );

		return ( $q ) ? $q : false;

	}

	/**
	 * Get the enrollment trigger for a the student's enrollment in a course
	 * @param  int $product_id WP Post ID of the course or membership
	 * @return string|false
	 */
	public function get_enrollment_trigger( $product_id ) {

		global $wpdb;

		$q = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_enrollment_trigger' AND user_id = %d AND post_id = %d ORDER BY updated_date DESC LIMIT 1",
			array( $this->get_id(), $product_id )
		) );

		return ( $q ) ? $q : false;

	}


	/**
	 * Retrive an array of Membership Levels for a user
	 * @return array
	 *
	 * @since  2.2.3
	 */
	public function get_membership_levels() {

		$levels = get_user_meta( $this->get_id(), '_llms_restricted_levels', true );

		if ( empty( $levels ) ) {

			$levels = array();

		}

		return $levels;

	}


	/**
	 * Add student postmeta data for enrollment into a course or membership
	 * @param  int        $product_id   WP Post ID of the course or membership
	 * @param  string     $trigger      String describing the reason for enrollment
	 * @return boolean
	 *
	 * @since  2.2.3
	 * @version  2.8.0  added $trigger parameter
	 */
	private function insert_enrollment_postmeta( $product_id, $trigger = 'unspecified' ) {

		global $wpdb;

		// add info to the user postmeta table
		$user_metadatas = array(
			'_enrollment_trigger' => $trigger,
			'_start_date'         => 'yes',
			'_status'             => 'Enrolled',
		);

		foreach ( $user_metadatas as $key => $value ) {

			$update = $wpdb->insert( $wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id'      => $this->get_id(),
					'post_id'      => $product_id,
					'meta_key'     => $key,
					'meta_value'   => $value,
					'updated_date' => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%s', '%s' )
			);

			if ( ! $update ) {

				return false;

			}

		}

		return true;

	}

	/**
	 * Add a new status record to the user postmeta table for a specific product
	 * @param  int    $product_id  WP Post ID of the course or membership
	 * @param  string $status      string describing the new status
	 * @return boolean
	 * @since  3.0.0
	 */
	private function insert_status_postmeta( $product_id, $status = '' ) {

		global $wpdb;

		$update = $wpdb->insert( $wpdb->prefix . 'lifterlms_user_postmeta',
			array(
				'user_id'      => $this->get_id(),
				'post_id'      => $product_id,
				'meta_key'     => '_status',
				'meta_value'   => $status,
				'updated_date' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s' )
		);

		if ( ! $update ) {

			return false;

		} else {

			return true;

		}

	}


	/**
	 * Determine if a student is enrolled in a LifterLMS course, lesson, or membership
	 *
	 * @param  int $product_id WP Post ID of a Course, Lesson, or Membership
	 *
	 * @return boolean
	 *
	 * @since  3.0.0
	 */
	public function is_enrolled( $product_id ) {

		$status = $this->get_enrollment_status( $product_id );
		return ( 'Enrolled' === $status ) ? true : false;

	}

	/**
	 * Remove a student from a LifterLMS course or membership
	 *
	 * @param  int    $product_id WordPress Post ID of the course or membership
	 * @param  string $trigger    only remove the student if the original enrollment trigger matches the submitted value
	 *                            "any" will remove regardless of enrollment trigger
	 * @param  string $new_status the value to update the new status with after removal is complete
	 * @return boolean
	 *
	 * @see  llms_unenroll_student()  calls this function without having to instantiate the LLMS_Student class first
	 *
	 * @since  3.0.0
	 */
	public function unenroll( $product_id, $trigger = 'any', $new_status = 'Expired' ) {

		// can only unenroll those are a currently enrolled
		if ( ! $this->is_enrolled( $product_id ) ) {
			return false;
		}

		// assume we can't unenroll
		$update = false;

		// if trigger is "any" we'll just unenroll regardless of the trigger
		if ( 'any' === $trigger ) {

			$update = true;

		} // else we'll check out the trigger
		else {

			$enrollment_trigger = $this->get_enrollment_trigger( $product_id );

			// no enrollment trigger exists b/c pre 3.0.0 enrollment, unenroll the user
			if ( ! $enrollment_trigger  ) {

				$update = apply_filters( 'lifterlms_legacy_unenrollment_action', true );

			} // trigger matches the enrollment trigger so unenroll
			elseif ( $enrollment_trigger == $trigger ) {

				$update = true;

			}

		}

		// update if we can
		if ( $update ) {

			// update enrollemtn for the product
			if ( $this->insert_status_postmeta( $product_id, $new_status ) ) {

				// trigger actions based on product type
				switch ( get_post_type( $product_id ) ) {

					case 'course':
						do_action( 'llms_user_removed_from_course', $this->get_id(), $product_id );
					break;

					case 'llms_membership':
						// also physically remove from the membership level & perform unenrollment
						// on related products
						$this->remove_membership_level( $product_id, $new_status );
						do_action( 'llms_user_removed_from_membership_level', $this->get_id(), $product_id );
					break;

				}

				return true;

			}

		}

		// return false if we didn't updat
		return false;

	}

}
