<?php
/**
 * Student Class
 *
 * Manages data and interactions with a LifterLMS Student
 *
 * @since   2.2.3
 * @version 3.14.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Student extends LLMS_Abstract_User_Data {

	/**
	 * Retrieve an instance of the LLMS_Instructor model for the current user
	 * @return   obj|false
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function instructor() {
		if ( $this->is_instructor() ) {
			return llms_get_instructor( $this->get_id() );
		}
		return false;
	}

	/**
	 * Retrieve an instance of the student quiz data model
	 * @return   LLMS_Student_Quizzes
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function quizzes() {
		return new LLMS_Student_Quizzes( $this->get_id() );
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

	/**
	 * Enroll the student in a course or membership
	 * @param  int     $product_id   WP Post ID of the course or membership
	 * @param  string  $trigger      String describing the reason for enrollment
	 * @return boolean
	 *
	 * @see  llms_enroll_student()  calls this function without having to instantiate the LLMS_Student class first
	 *
	 * @since    2.2.3
	 * @version  3.0.0  added $trigger parameter
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

		// if the student has been previously enrolled, simply update don't run a full enrollment
		if ( $this->get_enrollment_status( $product_id ) ) {

			$insert = $this->insert_status_postmeta( $product_id, 'enrolled', $trigger );

		} // End if().
		else {

			$insert = $this->insert_enrollment_postmeta( $product_id, $trigger );

		}

		// add the user postmeta for the enrollment
		if ( ! empty( $insert ) ) {

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
	 * Retrieve achievements that a user has earned
	 * @param    string $orderby field to order the returned results by
	 * @param    string $order   ordering method for returned results (ASC or DESC)
	 * @param    string $return  return type
	 *                           	obj => array of objects from $wpdb->get_results
	 *                           	achievements => array of LLMS_User_Achievement instances
	 * @return   array
	 * @since    2.4.0
	 * @version  3.14.0
	 */
	public function get_achievements( $orderby = 'updated_date', $order = 'DESC', $return = 'obj' ) {

		$orderby = esc_sql( $orderby );
		$order = esc_sql( $order );

		global $wpdb;

		$query = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id, meta_value AS achievement_id, updated_date AS earned_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d and meta_key = '_achievement_earned' ORDER BY $orderby $order",
			$this->get_id()
		) );

		if ( 'achievements' === $return ) {
			$ret = array();
			foreach ( $query as $obj ) {
				$ret[] = new LLMS_User_Achievement( $obj->achievement_id );
			}
			return $ret;
		}

		return $query;

	}


	public function get_avatar( $size = 96 ) {
		return '<span class="llms-student-avatar">' . get_avatar( $this->get_id(), $size, null, $this->get_name() ) . '</span>';
	}


	/**
	 * Retrieve the order which enrolled a studnet in a given course or membership
	 * Retrieves the most recently updated order for the given product
	 *
	 * @param    int        $product_id  WP Post ID of the LifterLMS Product (course, lesson, or membership)
	 * @return   obj|false               Instance of the LLMS_Order or false if none found
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_enrollment_order( $product_id ) {

		// if a lesson id was passed in, cascade up to the course for order retrieval
		if ( 'lesson' === get_post_type( $product_id ) ) {
			$lesson = new LLMS_Lesson( $product_id );
			$product_id = $lesson->get_parent_course();
		}

		// attempt to locate the order via the enrollment trigger
		$trigger = $this->get_enrollment_trigger( $product_id );
		if ( strpos( $trigger, 'order_' ) !== false ) {

			$id = str_replace( array( 'order_', 'wc_' ), '', $trigger );
			if ( is_numeric( $id ) ) {
				if ( 'llms_order' === get_post_type( $id ) ) {
					return new LLMS_Order( $id );
				} else {

					return get_post( $id );
				}
			}
		}

		// couldn't find via enrollment trigger, do a WP_Query
		$q = new WP_Query( array(
			'order' => 'DESC',
			'orderby' => 'modified',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_llms_user_id',
					'value' => $this->get_id(),
				),
				array(
					'key' => '_llms_product_id',
					'value' => $product_id,
				),
			),
			'posts_per_page' => 1,
			// 'post_status' => $statuses,
			'post_type' => 'llms_order',
		) );

		if ( $q->have_posts() ) {
			return new LLMS_Order( $q->posts[0] );
		}

		// couldn't find an order, return false
		return false;

	}

	/**
	 * Retrieve certificates that a user has earned
	 * @param    string $orderby field to order the returned results by
	 * @param    string $order   ordering method for returned results (ASC or DESC)
	 * @param    string $return  return type
	 *                           	obj => array of objects from $wpdb->get_results
	 *                           	certificates => array of LLMS_User_Achievement instances
	 * @return   array
	 * @since    2.4.0
	 * @version  3.14.1
	 */
	public function get_certificates( $orderby = 'updated_date', $order = 'DESC', $return = 'obj' ) {

		$orderby = esc_sql( $orderby );
		$order = esc_sql( $order );

		global $wpdb;

		$query = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id, meta_value AS certificate_id, updated_date AS earned_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d and meta_key = '_certificate_earned' ORDER BY $orderby $order",
			$this->get_id()
		) );

		if ( 'certificates' === $return ) {
			$ret = array();
			foreach ( $query as $obj ) {
				$ret[] = new LLMS_User_Certificate( $obj->certificate_id );
			}
			return $ret;
		}

		return $query;

	}

	/**
	 * Retrieve IDs of user's courses based on supplied criteria
	 *
	 * @param  array  $args query arguments
	 *                      @arg int    $limit    number of courses to return
	 *                      @arg string $orderby  table reference and field to order results by
	 *                      @arg string $order    result order (DESC, ASC)
	 *                      @arg int    $skip     number of results to skip for pagination purposes
	 *                      @arg string $status   filter results by enrollment status, "any", "enrolled", "cancelled", or "expired"
	 * @return array        "courses" will contain an array of course ids
	 *                      "more" will contain a boolean determining whether or not more courses are available beyond supplied limit/skip criteria
	 * @since    3.0.0
	 * @version  3.6.0
	 */
	public function get_courses( $args = array() ) {

		global $wpdb;

		$args = array_merge( array(
			'limit'   => 20,
			'orderby' => 'upm.updated_date',
			'order'   => 'DESC',
			'skip'    => 0,
			'status'  => 'any', // any, enrolled, cancelled, expired
		), $args );

		// allow "short" orderby's to be passed in without a table reference
		switch ( $args['orderby'] ) {
			case 'date':
				$args['orderby'] = 'upm.updated_date';
			break;
			case 'order':
				$args['orderby'] = 'p.menu_order';
			break;
			case 'title':
				$args['orderby'] = 'p.post_title';
			break;
		}

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
			"SELECT DISTINCT upm.post_id AS id
			 FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
			 JOIN {$wpdb->posts} AS p ON p.ID = upm.post_id
			 WHERE p.post_type = 'course'
			   AND p.post_status = 'publish'
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
	 * Retrieve IDs of courses a user has completed
	 *
	 * @param  array  $args query arguments
	 *                      @arg int    $limit    number of courses to return
	 *                      @arg string $orderby  table reference and field to order results by
	 *                      @arg string $order    result order (DESC, ASC)
	 *                      @arg int    $skip     number of results to skip for pagination purposes
	 * @return array        "courses" will contain an array of course ids
	 *                      "more" will contain a boolean determining whether or not more courses are available beyond supplied limit/skip criteria
	 * @since   ??
	 * @version ??
	 */
	public function get_completed_courses( $args = array() ) {

		global $wpdb;

		$args = array_merge( array(
			'limit'   => 20,
			'orderby' => 'upm.updated_date',
			'order'   => 'DESC',
			'skip'    => 0,
		), $args );

		// add one to the limit to see if there's pagination
		$args['limit']++;

		// the query
		$q = $wpdb->get_results( $wpdb->prepare(
			"SELECT upm.post_id AS id
			 FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
			 JOIN {$wpdb->posts} AS p ON p.ID = upm.post_id
			 WHERE p.post_type = 'course'
			   AND upm.meta_key = '_is_complete'
			   AND upm.meta_value = 'yes'
			   AND upm.user_id = %d
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
	 * Get the formatted date when a course or lesson was completed by the student
	 * @param    int        $object_id  WP Post ID of a course or lesson
	 * @param    string     $format     date format as accepted by php date()
	 * @return   false|string            will return false if the user is not enrolled
	 * @since    ??
	 * @version  ??
	 */
	public function get_completion_date( $object_id, $format = 'F d, Y' ) {

		global $wpdb;

		$q = $wpdb->get_var( $wpdb->prepare(
			"SELECT updated_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_is_complete' AND meta_value = 'yes' AND user_id = %d AND post_id = %d ORDER BY updated_date DESC LIMIT 1",
			array( $this->get_id(), $object_id )
		) );

		return ( $q ) ? date_i18n( $format, strtotime( $q ) ) : false;

	}

	/**
	 * Get the formatted date when a user initially enrolled in a product or when they were last updated
	 * @param   int    $product_id  WP Post ID of a course or membership
	 * @param   string $date        "enrolled" will get the most recent start date, "updated" will get the most recent status change date
	 * @param   string $format      date format as accepted by php date(), if none supplied uses the WP core "date_format" option
	 * @return  false|string        will return false if the user is not enrolled
	 * @since   3.0.0
	 * @version 3.14.0
	 */
	public function get_enrollment_date( $product_id, $date = 'enrolled', $format = null ) {

		if ( ! $format ) {
			$format = get_option( 'date_format', 'M d, Y' );
		}

		global $wpdb;

		$key = ( 'enrolled' == $date ) ? '_start_date' : '_status';

		// get the oldest recorded Enrollment date
		$q = $wpdb->get_var( $wpdb->prepare(
			"SELECT updated_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '$key' AND user_id = %d AND post_id = %d ORDER BY updated_date DESC LIMIT 1",
			array( $this->get_id(), $product_id )
		) );

		return ( $q ) ? date_i18n( $format, strtotime( $q ) ) : false;

	}

	/**
	 * Get the current enrollment status of a student for a particular product
	 *
	 * @param    int $product_id WP Post ID of a Course, Lesson, or Membership
	 * @return   false|string
	 * @since    3.0.0
	 * @version  3.7.0
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
		$status = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_status' AND user_id = %d AND post_id = %d ORDER BY updated_date DESC LIMIT 1",
			array( $this->get_id(), $product_id )
		) );

		$status = ( $status ) ? $status : false;

		return apply_filters( 'llms_get_enrollment_status', $status, $this->get_id(), $product_id );

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
	 * Get the enrollment trigger id for a the student's enrollment in a course
	 * @param  int  $product_id  WP Post ID of the course or membership
	 * @return int|false
	 */
	public function get_enrollment_trigger_id( $product_id ) {

		$trigger = $this->get_enrollment_trigger( $product_id );
		$id = false;
		if ( $trigger && false !== strpos( $trigger, 'order_' ) ) {
			$trigger_obj = $this->get_enrollment_order( $product_id );
			if ( $trigger_obj instanceof LLMS_Order ) {
				$id = $trigger_obj->get( 'id' );
			} elseif ( $trigger_obj instanceof WP_Post ) {
				$id = $trigger_obj->ID;
			}
		}
		return $id;

	}

	/**
	 * Get the students grade for a lesson / course
	 * All grades are based on quizzes assigned to lessons
	 * @param    int     $object_id  WP Post ID of a course or lesson
	 * @return   mixed
	 * @since    ??
	 * @version  ??
	 */
	public function get_grade( $object_id ) {

		$type = get_post_type( $object_id );

		switch ( $type ) {

			case 'course':

				$course = new LLMS_Course( $object_id );
				$lessons = $course->get_lessons( 'ids' );

				$grades = array();

				foreach ( $lessons as $lid ) {

					$grade = $this->get_grade( $lid );

					if ( is_numeric( $grade ) ) {
						array_push( $grades, $grade );
					}
				}

				$taken = count( $grades );

				if ( ! $taken ) {

					$grade = _x( 'N/A', 'course grade when no quizzes taken or in course', 'lifterlms' );

				} else {

					$total = array_sum( $grades );

					// prevent division by zero
					if ( 0 === $total ) {
						$grade = 0;
					} else {
						$grade = $total / $taken;
					}
				}

			break;

			case 'lesson':

				$l = new LLMS_Lesson( $object_id );
				$q = $l->get( 'assigned_quiz' );

				$grade = _x( 'N/A', 'lesson grade when lesson has no quiz', 'lifterlms' );

				if ( $q ) {

					$q = new LLMS_Quiz( $q );

					if ( $q->get_total_attempts_by_user( $this->get_id() ) ) {

						$grade = $q->get_best_grade( $this->get_id() );

					}
				}

			break;

		}// End switch().

		if ( is_numeric( $grade ) ) {

			$grade = round( $grade, 2 );

		}

		return apply_filters( 'llms_student_get_grade', $grade, $this, $object_id, $type );

	}

	/**
	 * Retrieve a user's notification subsription preferences for a given type & trigger
	 * @param    string   $type     notification type: email, basic, etc...
	 * @param    string   $trigger  notification trigger: eg purchase_reciept, lesson_complete, etc...
	 * @param    string   $default  value to return if no setting is saved in the db
	 * @return   string             yes or no
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function get_notification_subscription( $type, $trigger, $default = 'no' ) {

		$prefs = $this->get( 'notification_subscriptions' );
		if ( ! $prefs ) {
			$prefs = array();
		}

		if ( isset( $prefs[ $type ] ) && isset( $prefs[ $type ][ $trigger ] ) ) {
			return $prefs[ $type ][ $trigger ];
		}

		return $default;

	}

	/**
	 * Retrieve the student's overall grade
	 * Grade = sum of grades for all courses divided by number of enrolled courses
	 * if a course has no quizzes in it, it cannot be graded and is therefore excluded from the calculation
	 *
	 * cached data is automatically cleared when a student completes a quiz
	 *
	 * @param    boolean      $use_cache   if false, calculates the grade, otherwise utilizes cached data (if available)
	 * @return   float|string              grade as float or "N/A"
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_overall_grade( $use_cache = true ) {

		$grade = null;

		// attempt to pull from the cache first
		if ( $use_cache ) {

			$grade = $this->get( $this->meta_prefix . 'overall_grade' );

			if ( is_numeric( $grade ) ) {
				$grade = floatval( $grade );
			}
		}

		// cache disabled or no cached data available
		if ( ! $use_cache || null === $grade || '' === $grade ) {

			$grades = array();

			// get courses
			$courses = $this->get_courses( array(
				'limit' => 9999,
			) );

			// loop through courses
			foreach ( $courses['results'] as $course_id ) {

				// get course grade
				$g = $this->get_grade( $course_id );

				// if an actual grade (not N/A) is returned
				if ( is_numeric( $g ) ) {
					array_push( $grades, $g );
				}
			}

			// if we have at least one grade
			$count = count( $grades );
			if ( $count ) {

				$grade = round( array_sum( $grades ) / $count, 2 );

			} else {

				$grade = _x( 'N/A', 'overall grade when no quizzes', 'lifterlms' );

			}

			// cache the grade
			$this->set( 'overall_grade', $grade );

		}// End if().

		return apply_filters( 'llms_student_get_overall_grade', $grade, $this );

	}

	/**
	 * Retrieve a student's overall progess
	 * Overall progress is the total percentage completed based on all courses the student is enrolled in
	 * Cached data is cleared everytime the student completes a lesson
	 *
	 * @param    boolean    $use_cache  if false, calculates the progress, otherwise utilizes cached data (if available)
	 * @return   float
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_overall_progress( $use_cache = true ) {

		$progress = null;

		// attempt to pull from the cache first
		if ( $use_cache ) {

			$progress = $this->get( $this->meta_prefix . 'overall_progress' );

			if ( is_numeric( $progress ) ) {
				$progress = floatval( $progress );
			}
		}

		// cache disabled or no cached data available
		if ( ! $use_cache || null === $progress || '' === $progress ) {

			$progresses = array();

			// get courses
			$courses = $this->get_courses( array(
				'limit' => 9999,
			) );

			// loop through courses
			foreach ( $courses['results'] as $course_id ) {
				array_push( $progresses, $this->get_progress( $course_id, 'course' ) );
			}

			$count = count( $progresses );
			if ( $count ) {

				$progress = round( array_sum( $progresses ) / $count, 2 );

			} else {

				$progress = 0;

			}

			// cache the grade
			$this->set( 'overall_progress', $progress );

		}

		return apply_filters( 'llms_student_get_overall_progress', $progress, $this );

	}

	/**
	 * Get the students last completed lesson in a course
	 * @param    int     $course_id    WP_Post ID of the course
	 * @return   int                   WP_Post ID of the lesson or false if no progress has been made
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_last_completed_lesson( $course_id ) {

		$course = new LLMS_Course( $course_id );
		$lessons = array_reverse( $course->get_lessons( 'ids' ) );

		foreach ( $lessons as $lesson ) {
			if ( $this->is_complete( $lesson, 'lesson' ) ) {
				return $lesson;
			}
		}

		return false;

	}

	/**
	 * Retrive an array of Membership Levels for a user
	 * @return array
	 * @since   2.2.3
	 * @version 2.2.3
	 */
	public function get_membership_levels() {

		$levels = get_user_meta( $this->get_id(), '_llms_restricted_levels', true );

		if ( empty( $levels ) ) {

			$levels = array();

		}

		return $levels;

	}

	/**
	 * Get the full name of a student
	 * @return   string
	 * @since    3.0.4
	 * @version  3.5.1
	 */
	public function get_name() {

		$name = trim( $this->get( 'first_name' ) . ' ' . $this->get( 'last_name' ) );

		if ( ! $name ) {
			$name = $this->display_name;
		}

		return apply_filters( 'llms_student_get_name', $name, $this->get_id(), $this );

	}

	/**
	 * Get the next lesson a student needs to complete in a course
	 * @param    int     $course_id    WP_Post ID of the course
	 * @return   int                   WP_Post ID of the lesson or false if all courses are complete
	 * @since    3.0.1
	 * @version  3.0.1
	 */
	public function get_next_lesson( $course_id ) {

		$course = new LLMS_Course( $course_id );
		$lessons = $course->get_lessons( 'ids' );

		foreach ( $lessons as $lesson ) {
			if ( ! $this->is_complete( $lesson, 'lesson' ) ) {
				return $lesson;
			}
		}

		return false;

	}

	public function get_orders( $params = array() ) {

		$params = wp_parse_args( $params, array(

			'count' => 25,
			'page' => 1,
			'statuses' => array_keys( llms_get_order_statuses() ),

		) );

		extract( $params );

		$q = new WP_Query( array(
			'order' => 'DESC',
			'orderby' => 'date',
			'meta_query' => array(
				array(
					'key' => '_llms_user_id',
					'value' => $this->get_id(),
				),
			),
			'paged' => $page,
			'posts_per_page' => $count,
			'post_status' => $statuses,
			'post_type' => 'llms_order',
		) );

		$orders = array();

		if ( $q->have_posts() ) {

			foreach ( $q->posts as $post ) {

				$orders[ $post->ID ] = new LLMS_Order( $post );

			}
		}

		return array(
			'count' => count( $q->posts ),
			'page' => $page,
			'pages' => $q->max_num_pages,
			'orders' => $orders,
		);

	}

	/**
	 * Get students progress through a course or track
	 * @param    int        $object_id  course or track id
	 * @param    string     $type       object type [course|course_track|section]
	 * @return   float
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	public function get_progress( $object_id, $type = 'course' ) {

		$total = 0;
		$completed = 0;

		if ( 'course' === $type ) {

			$course = new LLMS_Course( $object_id );
			$lessons = $course->get_lessons( 'ids' );
			$total = count( $lessons );
			foreach ( $lessons as $lesson ) {
				if ( $this->is_complete( $lesson, 'lesson' ) ) {
					$completed++;
				}
			}
		} elseif ( 'course_track' === $type ) {

			$track = new LLMS_Track( $object_id );
			$courses = $track->get_courses();
			$total = count( $courses );
			foreach ( $courses as $course ) {
				if ( $this->is_complete( $course->ID, 'course' ) ) {
					$completed++;
				}
			}
		} elseif ( 'section' === $type ) {

			$section = new LLMS_Section( $object_id );
			$lessons = $section->get_lessons( 'ids' );
			$total = count( $lessons );
			foreach ( $lessons as $lesson ) {
				if ( $this->is_complete( $lesson, 'lesson' ) ) {
					$completed++;
				}
			}
		}

		return ( ! $completed || ! $total ) ? 0 : round( 100 / ( $total / $completed ), 2 );

	}

	/**
	 * Retrieve the Students original registration date in chosen format
	 * @param    string     $format  any date format that can be passed to date()
	 * @return   string
	 * @since    ??
	 * @version  3.14.0
	 */
	public function get_registration_date( $format = '' ) {

		if ( ! $format ) {
			$format = get_option( 'date_format' );
		}

		return date_i18n( $format, strtotime( $this->get( 'user_registered' ) ) );

	}

	/**
	 * Determine if a student has access to a product's content
	 * @param      int     $product_id    WP Post ID of a course or membership
	 * @return     boolean
	 * @since      3.0.0
	 * @version    3.12.2
	 * @deprecated 3.12.2   This function previously differed from $this->is_enrolled() by
	 *                      checking the status of an order and only returning true when
	 *                      the order status and enrollment status were both true
	 *                      this causes issues when a student is expired from a limited-access product
	 *                      and is then manually re-enrolled by an admin
	 *                      there is no way to change the access expiration information
	 *                      and the enrollment status says "Enrolled" but the student still cannot
	 *                      access the content
	 *
	 * 						Additionally redundant due to the fact that access is expired automatically
	 * 						via action scheduler `do_action( 'llms_access_plan_expiration', $order_id );`
	 * 						This action changes the enrollment status thereby rendering this additional
	 * 						access check redundant, confusing, unnecessary
	 */
	public function has_access( $product_id ) {

		llms_deprecated_function( 'LLMS_Student::has_access()', '3.12.2', 'LLMS_Student::is_enrolled()' );
		return $this->is_enrolled( $product_id );

	}

	/**
	 * Determine if the student is active in at least one course or membership
	 * @return   boolean
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function is_active() {

		// this is a simpler, faster query, check first
		if ( $this->get_membership_levels() ) {
			return true;
		}

		// check for at least one enrolled course
		$courses = $this->get_courses( array(
			'limit'   => 1,
			'status'  => 'enrolled',
		) );

		if ( $courses['results'] ) {
			return true;
		}

		// not active
		return false;

	}


	/**
	 * Determine if the student has completed a course, track, or lesson
	 *
	 * @param    int     $object_id  WP Post ID of a course or lesson or section or the term id of the track
	 * @param    string     $type    Object type (course, lesson, section, or track)
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	public function is_complete( $object_id, $type = 'course' ) {

		switch ( $type ) {

			case 'course':
			case 'section':
			case 'course_track':
				$ret = ( 100 == $this->get_progress( $object_id, $type ) );
			break;

			case 'lesson':
				global $wpdb;
				$query = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*)
					 FROM {$wpdb->prefix}lifterlms_user_postmeta
					 WHERE user_id = %d
					   AND post_id = %d
					   AND meta_key = '_is_complete'
					   AND meta_value = 'yes'
					 ORDER BY updated_date ASC
					 LIMIT 1
					;",
					array( $this->get_id(), $object_id )
				) );
				$ret = ( $query >= 1 );
			break;

			default:
				$ret = false;

		}

		return apply_filters( 'llms_is_' . $type . '_complete', $ret, $object_id, $type, $this );

	}

	/**
	 * Determine if the student is a LifterLMS Instructor (of any kind)
	 * Can be admin, manager, instructor, assistant
	 * @return   boolean
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function is_instructor() {
		return $this->user->has_cap( 'lifterlms_instructor' );
	}

	/**
	 * Add student postmeta data for completion of a lesson, section, course or track
	 * @param  int        $object_id    WP Post ID of the lesson, section, course or track
	 * @param  string     $trigger      String describing the reason for mark completion
	 * @return boolean
	 *
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	private function insert_completion_postmeta( $object_id, $trigger = 'unspecified' ) {

		global $wpdb;

		// add info to the user postmeta table
		$user_metadatas = array(
			'_is_complete'        => 'yes',
			'_completion_trigger' => $trigger,
		);

		foreach ( $user_metadatas as $key => $value ) {

			$update = $wpdb->insert( $wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id'      => $this->get_id(),
					'post_id'      => $object_id,
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
	 * Add student postmeta data for incompletion of a lesson, section, course or track
	 * An "_is_complete" value of "no" is inserted into postmeta
	 * @param  int        $object_id    WP Post ID of the lesson, section, course or track
	 * @param  string     $trigger      String describing the reason for mark incompletion
	 * @return boolean
	 *
	 * @since    3.5.0
	 * @version  3.5.0
	 */
	private function insert_incompletion_postmeta( $object_id, $trigger = 'unspecified' ) {

		global $wpdb;

		// add '_is_complete' to the user postmeta table for object
		$user_metadatas = array(
			'_is_complete'        => 'no',
			'_completion_trigger' => $trigger,
		);

		foreach ( $user_metadatas as $key => $value ) {

			// It's too difficult to keep track of multiple postmetas for each lesson incomplete
			// Instead, I'm just replacing the old '_is_complete' value with 'no'
			//
			// lessons that have never been complete will not have an '_is_complete' record,
			// lessons that were completed will have an '_is_complete' record of 'yes',
			// lessons that have been completed once but were marked incomplete will have an '_is_complete' record of 'no'
			$update = $wpdb->update( $wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id'      => $this->get_id(),
					'post_id'      => $object_id,
					'meta_key'     => $key,
					'meta_value'   => $value,
					'updated_date' => current_time( 'mysql' ),
				),
				array(
					'user_id'	   => $this->get_id(),
					'post_id'      => $object_id,
					'meta_key'     => $key,
				),
				array( '%d', '%d', '%s', '%s', '%s' )
			);

			if ( $update === false ) {

				return false;

			}
		}

		return true;

	}

	/**
	 * Add student postmeta data for enrollment into a course or membership
	 * @param  int        $product_id   WP Post ID of the course or membership
	 * @param  string     $trigger      String describing the reason for enrollment
	 * @return boolean
	 *
	 * @since  2.2.3
	 * @version  3.0.0  added $trigger parameter
	 */
	private function insert_enrollment_postmeta( $product_id, $trigger = 'unspecified' ) {

		global $wpdb;

		// add info to the user postmeta table
		$user_metadatas = array(
			'_enrollment_trigger' => $trigger,
			'_start_date'         => 'yes',
			'_status'             => 'enrolled',
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
	 * @param  int    $product_id   WP Post ID of the course or membership
	 * @param  string $status       string describing the new status
	 * @param  string     $trigger  String describing the reason for enrollment (optional)
	 * @return boolean
	 * @since  3.0.0
	 */
	private function insert_status_postmeta( $product_id, $status = '', $trigger = null ) {

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

		if ( $update ) {

			if ( $trigger ) {

				$update = $wpdb->insert( $wpdb->prefix . 'lifterlms_user_postmeta',
					array(
						'user_id'      => $this->get_id(),
						'post_id'      => $product_id,
						'meta_key'     => '_enrollment_trigger',
						'meta_value'   => $trigger,
						'updated_date' => current_time( 'mysql' ),
					),
					array( '%d', '%d', '%s', '%s', '%s' )
				);

			}
		}

		if ( ! $update ) {

			return false;

		} else {

			return true;

		}

	}

	/**
	 * Determine if a student is enrolled in a LifterLMS course, lesson, or membership
	 * @param  int $product_id WP Post ID of a Course, Lesson, or Membership
	 * @return boolean
	 * @since  3.0.0
	 */
	public function is_enrolled( $product_id ) {

		$status = $this->get_enrollment_status( $product_id );
		return ( 'enrolled' === strtolower( $status ) ) ? true : false;

	}

	/**
	 * Mark a lesson, section, course, or track complete for the given user
	 * @param  int     $object_id    WP Post ID of the lesson, section, course, or track
	 * @param  string  $object_type  object type [lesson|section|course|track]
	 * @param  string  $trigger      String describing the reason for marking complete
	 * @return boolean
	 *
	 * @see    llms_mark_complete() calls this function without having to instantiate the LLMS_Student class first
	 *
	 * @since    3.3.1
	 * @version  3.7.0
	 */
	public function mark_complete( $object_id, $object_type, $trigger = 'unspecified' ) {

		do_action( 'before_llms_mark_complete', $this->get_id(), $object_id, $object_type, $trigger );

		// can only be marked compelete in the following post types
		if ( in_array( $object_type, apply_filters( 'llms_completable_post_types', array( 'course', 'lesson', 'section' ) ) ) ) {
			$object = llms_get_post( $object_id );
		} // End if().
		elseif ( 'course_track' === $object_type ) {
			$object = get_term( $object_id, 'course_track' );
		} // i said no
		else {
			return false;
		}

		// parent(s) to cascade up and check for completion
		// lessons -> section -> course -> track(s)
		$parent_ids = array();
		$parent_type = false;

		// lessons are complete automatically
		// other object types are only complete when all of their children are also complete
		// so the other object types need to check if their complete before being marked as complete
		$complete = ( 'lesson' === $object_type ) ? true : $this->is_complete( $object_id, $object_type );

		// get the immediate parent so we can cascade up and maybe mark the parent as complete as well
		switch ( $object_type ) {

			case 'lesson':
				$parent_ids = array( $object->get( 'parent_section' ) );
				$parent_type = 'section';
			break;

			case 'section':
				$parent_ids = array( $object->get( 'parent_course' ) );
				$parent_type = 'course';
			break;

			case 'course':
				$parent_ids = wp_list_pluck( $object->get_tracks(), 'term_id' );
				$parent_type = 'course_track';
			break;

		}

		// object is complete
		if ( $complete ) {

			// insert meta data
			$this->insert_completion_postmeta( $object_id, $trigger );

			// generic action hook
			do_action( 'llms_mark_complete', $this->get_id(), $object_id, $object_type, $trigger );

			// specific hook for each type, also backwards compatible for existing hooks
			do_action( 'lifterlms_' . $object_type . '_completed', $this->get_id(), $object_id );

			// cascade up
			if ( $parent_ids && $parent_type ) {

				foreach ( $parent_ids as $pid ) {

					$this->mark_complete( $pid, $parent_type, $trigger );

				}
			}

			do_action( 'after_llms_mark_complete', $this->get_id(), $object_id, $object_type, $trigger );

			return true;

		}

		return false;

	}

	/**
	 * Mark a lesson, section, course, or track INcomplete for the given user
	 * Gives an "_is_complete" value of "no" for the given object
	 * @param  int     $object_id    WP Post ID of the lesson, section, course, or track
	 * @param  string  $object_type  object type [lesson|section|course|track]
	 * @param  string  $trigger      String describing the reason for marking incomplete
	 * @return boolean
	 *
	 * @see    llms_mark_incomplete() calls this function without having to instantiate the LLMS_Student class first
	 *
	 * @since    3.5.0
	 * @version  3.7.0
	 */
	public function mark_incomplete( $object_id, $object_type, $trigger = 'unspecified' ) {

		do_action( 'before_llms_mark_incomplete', $this->get_id(), $object_id, $object_type, $trigger );

		// can only be marked incompelete in the following post types
		if ( in_array( $object_type, apply_filters( 'llms_completable_post_types', array( 'course', 'lesson', 'section' ) ) ) ) {
			$object = llms_get_post( $object_id );
		} // End if().
		elseif ( 'course_track' === $object_type ) {
			$object = get_term( $object_id, 'course_track' );
		} // i said no
		else {
			return false;
		}

		// parent(s) to cascade up and check for incompletion
		// lessons -> section -> course -> track(s)
		$parent_ids = array();
		$parent_type = false;

		// lessons are incomplete automatically
		// other object types are only incomplete when all of their children are also incomplete
		// so the other object types need to check if their complete before being marked as complete
		$complete = ( 'lesson' === $object_type ) ? false : $this->is_complete( $object_id, $object_type );

		// get the immediate parent so we can cascade up and maybe mark the parent as incomplete as well
		switch ( $object_type ) {

			case 'lesson':
				$parent_ids = array( $object->get( 'parent_section' ) );
				$parent_type = 'section';
			break;

			case 'section':
				$parent_ids = array( $object->get( 'parent_course' ) );
				$parent_type = 'course';
			break;

			case 'course':
				$parent_ids = wp_list_pluck( $object->get_tracks(), 'term_id' );
				$parent_type = 'course_track';
			break;

		}

		// object is incomplete
		if ( $complete === false ) {

			// insert meta data
			$this->insert_incompletion_postmeta( $object_id, $trigger );

			// generic action hook
			do_action( 'llms_mark_incomplete', $this->get_id(), $object_id, $object_type, $trigger );

			// specific hook for each type, also backwards compatible for existing hooks
			do_action( 'lifterlms_' . $object_type . '_incompleted', $this->get_id(), $object_id );

			// cascade up
			if ( $parent_ids && $parent_type ) {

				foreach ( $parent_ids as $pid ) {

					$this->mark_incomplete( $pid, $parent_type, $trigger );

				}
			}

			return true;

		}

		return false;

	}

	/**
	 * Remove a student from a membership level
	 * @param    int        $membership_id  WP Post ID of the membership
	 * @param    string     $status         status to update the removal to
	 * @return   void
	 * @since    2.7
	 * @version  3.7.5
	 */
	private function remove_membership_level( $membership_id, $status = 'expired' ) {

		// remove the user from the membership level
		$membership_levels = $this->get_membership_levels();
		$key = array_search( $membership_id, $membership_levels );
		if ( false !== $key ) {
			unset( $membership_levels[ $key ] );
		}
		update_user_meta( $this->get_id(), '_llms_restricted_levels', $membership_levels );

		global $wpdb;
		// locate all enrollments triggered by this membership level
		$q = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d AND meta_key = '_enrollment_trigger' AND meta_value = %s",
			array( $this->get_id(), 'membership_' . $membership_id )
		), 'OBJECT_K' );

		$courses = array_keys( $q );

		if ( $courses ) {

			// loop through all the courses and update the enrollment status
			foreach ( $courses  as $course_id ) {
				$this->unenroll( $course_id, 'membership_' . $membership_id, $status );
			}
		}

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
	public function unenroll( $product_id, $trigger = 'any', $new_status = 'expired' ) {

		// can only unenroll those are a currently enrolled
		if ( ! $this->is_enrolled( $product_id ) ) {
			return false;
		}

		// assume we can't unenroll
		$update = false;

		// if trigger is "any" we'll just unenroll regardless of the trigger
		if ( 'any' === $trigger ) {

			$update = true;

		} // End if().
		else {

			$enrollment_trigger = $this->get_enrollment_trigger( $product_id );

			// no enrollment trigger exists b/c pre 3.0.0 enrollment, unenroll the user
			if ( ! $enrollment_trigger ) {

				$update = apply_filters( 'lifterlms_legacy_unenrollment_action', true );

			} // End if().
			elseif ( $enrollment_trigger == $trigger ) {

				$update = true;

			}
		}

		// update if we can
		if ( $update ) {

			// update enrollment for the product
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





	/*
		       /$$                                                               /$$                     /$$
		      | $$                                                              | $$                    | $$
		  /$$$$$$$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$  /$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$$
		 /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$_____/ |____  $$|_  $$_/   /$$__  $$ /$$__  $$
		| $$  | $$| $$$$$$$$| $$  \ $$| $$  \__/| $$$$$$$$| $$        /$$$$$$$  | $$    | $$$$$$$$| $$  | $$
		| $$  | $$| $$_____/| $$  | $$| $$      | $$_____/| $$       /$$__  $$  | $$ /$$| $$_____/| $$  | $$
		|  $$$$$$$|  $$$$$$$| $$$$$$$/| $$      |  $$$$$$$|  $$$$$$$|  $$$$$$$  |  $$$$/|  $$$$$$$|  $$$$$$$
		 \_______/ \_______/| $$____/ |__/       \_______/ \_______/ \_______/   \___/   \_______/ \_______/
		                    | $$
		                    | $$
		                    |__/
	*/

	/**
	 * Remove Student Quiz attempts
	 * @param    int     $quiz_id    WP Post ID of a Quiz
	 * @param    int     $lesson_id  WP Post ID of a lesson
	 * @param    int     $attempt    optional attempt number, if ommitted all attempts for quiz & lesson will be deleted
	 * @return   array               updated array quiz data for the student
	 * @since    3.4.4
	 * @version  3.9.0
	 */
	public function delete_quiz_attempt( $quiz_id, $lesson_id, $attempt = null ) {
		llms_deprecated_function( 'LLMS_Student->delete_quiz_attempt()', '[version]', 'LLMS_Student->quizzes()->delete_attempt()' );
		return $this->quizzes()->delete_attempt( $quiz_id, $lesson_id, $attempt );
	}

	/**
	 * Get the quiz attempt with the highest grade for a given quiz and lesson combination
	 * @param    int     $quiz_id    WP Post ID of a Quiz
	 * @param    int     $lesson_id  WP Post ID of a lesson
	 * @return   array
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_best_quiz_attempt( $quiz = null, $lesson = null ) {
		llms_deprecated_function( 'LLMS_Student->get_best_quiz_attempt()', '[version]', 'LLMS_Student->quizzes()->get_best_attempt()' );
		return $this->quizzes()->get_best_attempt( $quiz, $lesson );
	}

	/**
	 * Retrieve quiz data for a student for a lesson / quiz combination
	 * @param    int     $quiz    WP Post ID of a Quiz
	 * @param    int     $lesson  WP Post ID of a lesson
	 * @return   array
	 * @since    3.2.0
	 * @version  3.9.0
	 */
	public function get_quiz_data( $quiz = null, $lesson = null ) {
		llms_deprecated_function( 'LLMS_Student->get_quiz_data()', '[version]', 'LLMS_Student->quizzes()->get_all()' );
		return $this->quizzes()->get_all( $quiz, $lesson );
	}

}
