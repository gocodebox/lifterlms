<?php
/**
 * Student Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 2.2.3
 * @version 5.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Student model class
 *
 * Manages data and interactions with a LifterLMS Student.
 *
 * @since 2.2.3
 * @since 3.33.0 Added the `delete_student_enrollment` public method that allows student's enrollment unrollment and deletion.
 * @since 3.33.0 Added the `delete_enrollment_postmeta` private method that allows student's enrollment postmeta deletion.
 * @since 3.34.0 Added new filters for differentiating between enrollment update and creation; Added the ability to check enrollment from a section.
 * @since 3.35.0 Prepare all variables when querying for enrollment date.
 * @since 3.36.2 Added logic to physically remove from the membership level and remove enrollments data on related products, when deleting a membership enrollment.
 * @since 3.37.9 Added filters `llms_user_enrollment_allowed_post_types` & `llms_user_enrollment_status_allowed_post_types` which allow 3rd parties to enroll users into additional post types via core enrollment methods.
 * @since 4.0.0 Remove previously deprecated methods.
 * @since 4.2.0 The `$enrollment_trigger` parameter was added to the `'llms_user_enrollment_deleted'` action hook.
 *              Added new filter to allow customization of object completion data.
 * @since 5.2.0 Changed the date to be relative to the local time zone in `get_registration_date`.
 */
class LLMS_Student extends LLMS_Abstract_User_Data {

	/**
	 * Retrieve an instance of the LLMS_Instructor model for the current user
	 *
	 * @return   LLMS_Instructor|false
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
	 *
	 * @return   LLMS_Student_Quizzes
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function quizzes() {
		return new LLMS_Student_Quizzes( $this->get_id() );
	}

	/**
	 * Add the student to a LifterLMS Membership
	 *
	 * @param int $membership_id   WP Post ID of the membership
	 * @return  void
	 *
	 * @since  2.2.3
	 */
	private function add_membership_level( $membership_id ) {

		// Add the user to the membership level.
		$membership_levels = $this->get_membership_levels();
		array_push( $membership_levels, $membership_id );
		update_user_meta( $this->get_id(), '_llms_restricted_levels', $membership_levels );

		// If there's auto-enroll courses, enroll the user in those courses.
		$autoenroll_courses = get_post_meta( $membership_id, '_llms_auto_enroll', true );
		if ( $autoenroll_courses ) {

			foreach ( $autoenroll_courses as $course_id ) {

				$this->enroll( $course_id, 'membership_' . $membership_id );

			}
		}

	}

	/**
	 * Enroll the student into a course or membership
	 *
	 * @since 2.2.3
	 * @since 3.17.0 Unknown.
	 * @since 3.34.0 Added new actions to differentiate between first-time enrollment and enrollment status updates.
	 * @since 3.37.9 Added filter `llms_user_enrollment_allowed_post_types` to customize the post types a user can be enrolled into.
	 * @since 4.4.1 Moved filter `llms_user_enrollment_allowed_post_types` to function `llms_get_enrollable_post_types()`.
	 *
	 * @see llms_enroll_student()
	 *
	 * @param  int    $product_id WP Post ID of the course or membership
	 * @param  string $trigger    String describing the reason for enrollment
	 * @return boolean
	 */
	public function enroll( $product_id, $trigger = 'unspecified' ) {

		/**
		 * Fires before a user is enrolled into a course or membership.
		 *
		 * @param int $user_id WP User ID.
		 * @param int $product_id WP Post ID of the course or membership.
		 */
		do_action( 'before_llms_user_enrollment', $this->get_id(), $product_id );

		// Users can only be enrolled into the following post types.
		if ( ! in_array( get_post_type( $product_id ), llms_get_enrollable_post_types(), true ) ) {
			return false;
		}

		// Check enrollment before enrolling to prevent duplicates.
		if ( llms_is_user_enrolled( $this->get_id(), $product_id ) ) {
			return false;
		}

		// If the student has been previously enrolled, simply update don't run a full enrollment.
		if ( $this->get_enrollment_status( $product_id, false ) ) {
			$insert      = $this->insert_status_postmeta( $product_id, 'enrolled', $trigger );
			$action_type = 'updated';
		} else {
			$insert      = $this->insert_enrollment_postmeta( $product_id, $trigger );
			$action_type = 'created';
		}

		// Add the user postmeta for the enrollment.
		if ( ! empty( $insert ) ) {

			// Update the cache.
			$this->cache_set( sprintf( 'enrollment_status_%d', $product_id ), 'enrolled' );
			$this->cache_delete( sprintf( 'date_enrolled_%d', $product_id ) );
			$this->cache_delete( sprintf( 'date_updated_%d', $product_id ) );

			$post_type = str_replace( 'llms_', '', get_post_type( $product_id ) );

			if ( 'course' === $post_type ) {

				/**
				 * Fires after a user is enrolled in course
				 *
				 * @param int $user_id    WP User ID.
				 * @param int $product_id WP Post ID of the course or membership.
				 */
				do_action( 'llms_user_enrolled_in_course', $this->get_id(), $product_id );

			} elseif ( 'membership' === $post_type ) {

				$this->add_membership_level( $product_id );

				/**
				 * Fires after a user is enrolled in membership
				 *
				 * @param int $user_id    WP User ID.
				 * @param int $product_id WP Post ID of the course or membership.
				 */
				do_action( 'llms_user_added_to_membership_level', $this->get_id(), $product_id );

			}

			/**
			 * Fires after a user's enrollment is created or updated.
			 *
			 * `$post_type` refers to the type of item the user is enrolled in, either 'course' or 'membership'
			 * `$action_type` refers to the type of action taking place, either "created" or "updated".
			 *
			 * @param int $user_id WP User ID.
			 * @param int $product_id WP Post ID of the course or membership.
			 */
			do_action( "llms_user_{$post_type}_enrollment_{$action_type}", $this->get_id(), $product_id );

			return true;

		}

		return false;

	}

	/**
	 * Retrieve achievements that a user has earned
	 *
	 * @param    string $orderby field to order the returned results by
	 * @param    string $order   ordering method for returned results (ASC or DESC)
	 * @param    string $return  return type
	 *                              obj => array of objects from $wpdb->get_results
	 *                              achievements => array of LLMS_User_Achievement instances
	 * @return   array
	 * @since    2.4.0
	 * @version  3.14.0
	 */
	public function get_achievements( $orderby = 'updated_date', $order = 'DESC', $return = 'obj' ) {

		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_value AS achievement_id, updated_date AS earned_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d and meta_key = '_achievement_earned' ORDER BY $orderby $order",
				$this->get_id()
			)
		);// db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
	 * Retrieve the order which enrolled a student in a given course or membership.
	 *
	 * Retrieves the most recently updated order for the given product.
	 *
	 * @since 3.0.0
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
	 *
	 * @param int $product_id WP Post ID of the LifterLMS Product (course, lesson, or membership)
	 * @return LLMS_Order|false Instance of the LLMS_Order or false if none found
	 */
	public function get_enrollment_order( $product_id ) {

		// If a lesson id was passed in, cascade up to the course for order retrieval.
		if ( 'lesson' === get_post_type( $product_id ) ) {
			$lesson     = new LLMS_Lesson( $product_id );
			$product_id = $lesson->get( 'parent_course' );
		}

		// Attempt to locate the order via the enrollment trigger.
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

		// Couldn't find via enrollment trigger, do a WP_Query.
		$q = new WP_Query(
			array(
				'order'          => 'DESC',
				'orderby'        => 'modified',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => '_llms_user_id',
						'value' => $this->get_id(),
					),
					array(
						'key'   => '_llms_product_id',
						'value' => $product_id,
					),
				),
				'posts_per_page' => 1,
				'post_type'      => 'llms_order',
			)
		);

		if ( $q->have_posts() ) {
			return new LLMS_Order( $q->posts[0] );
		}

		// Couldn't find an order, return false.
		return false;

	}

	/**
	 * Retrieve certificates that a user has earned
	 *
	 * @param    string $orderby field to order the returned results by
	 * @param    string $order   ordering method for returned results (ASC or DESC)
	 * @param    string $return  return type
	 *                              obj => array of objects from $wpdb->get_results
	 *                              certificates => array of LLMS_User_Certificate instances
	 * @return   array
	 * @since    2.4.0
	 * @version  3.14.1
	 */
	public function get_certificates( $orderby = 'updated_date', $order = 'DESC', $return = 'obj' ) {

		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_value AS certificate_id, updated_date AS earned_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d and meta_key = '_certificate_earned' ORDER BY $orderby $order",
				$this->get_id()
			)
		); // db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
	 * @param    array $args   see `get_enrollments`
	 * @return   array
	 * @since    3.0.0
	 * @version  3.15.0
	 */
	public function get_courses( $args = array() ) {

		return $this->get_enrollments( 'course', $args );

	}

	/**
	 * Retrieve IDs of courses a user has completed
	 *
	 * @param  array $args query arguments
	 *                      @arg int    $limit    number of courses to return
	 *                      @arg string $orderby  table reference and field to order results by
	 *                      @arg string $order    result order (DESC, ASC)
	 *                      @arg int    $skip     number of results to skip for pagination purposes
	 * @return array        "courses" will contain an array of course ids
	 *                      "more" will contain a boolean determining whether or not more courses are available beyond supplied limit/skip criteria
	 * @since   ??
	 * @version 3.24.0
	 */
	public function get_completed_courses( $args = array() ) {

		global $wpdb;

		$args = array_merge(
			array(
				'limit'   => 20,
				'orderby' => 'upm.updated_date',
				'order'   => 'DESC',
				'skip'    => 0,
			),
			$args
		);

		// Add one to the limit to see if there's pagination.
		$args['limit']++;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$q = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT upm.post_id AS id
			 FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
			 JOIN {$wpdb->posts} AS p ON p.ID = upm.post_id
			 WHERE p.post_type = 'course'
			   AND upm.meta_key = '_is_complete'
			   AND upm.meta_value = 'yes'
			   AND upm.user_id = %d
			 ORDER BY {$args['orderby']} {$args['order']}
			 LIMIT %d, %d;
			",
				array(
					$this->get_id(),
					$args['skip'],
					$args['limit'],
				)
			),
			'OBJECT_K'
		); // db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$ids  = array_keys( $q );
		$more = false;

		// If we hit our limit we have too many results, pop the last one.
		if ( count( $ids ) === $args['limit'] ) {
			array_pop( $ids );
			$more = true;
		}

		// Reset args to pass back for pagination.
		$args['limit']--;

		$r = array(
			'limit'   => $args['limit'],
			'more'    => $more,
			'results' => $ids,
			'skip'    => $args['skip'],
		);

		return $r;

	}

	/**
	 * Get the formatted date when a course or lesson was completed by the student
	 *
	 * @param    int    $object_id  WP Post ID of a course or lesson
	 * @param    string $format     date format as accepted by php date()
	 * @return   false|string            will return false if the user is not enrolled
	 * @since    ??
	 * @version  ??
	 */
	public function get_completion_date( $object_id, $format = 'F d, Y' ) {

		global $wpdb;

		$q = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT updated_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_is_complete' AND meta_value = 'yes' AND user_id = %d AND post_id = %d ORDER BY updated_date DESC LIMIT 1",
				array( $this->get_id(), $object_id )
			)
		); // db call ok; no-cache ok.

		return ( $q ) ? date_i18n( $format, strtotime( $q ) ) : false;

	}

	/**
	 * Retrieve IDs of user's enrollments by post type (and additional criteria)
	 *
	 * @param  string $post_type  name of the post type (course|membership)
	 * @param  array  $args query arguments
	 *                      @arg int    $limit    number of courses to return
	 *                      @arg string $orderby  table reference and field to order results by
	 *                      @arg string $order    result order (DESC, ASC)
	 *                      @arg int    $skip     number of results to skip for pagination purposes
	 *                      @arg string $status   filter results by enrollment status, "any", "enrolled", "cancelled", or "expired"
	 * @return array        "results" will contain an array of course ids
	 *                      "more" will contain a boolean determining whether or not more courses are available beyond supplied limit/skip criteria
	 *                      "found" will contain the total possible FOUND_ROWS() for the query
	 * @since    3.0.0
	 * @version  3.15.1
	 */
	public function get_enrollments( $post_type = 'course', $args = array() ) {

		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'limit'   => 20,
				'orderby' => 'upm.updated_date',
				'order'   => 'DESC',
				'skip'    => 0,
				'status'  => 'any', // Any, enrolled, cancelled, expired.
			)
		);

		// Prefix membership.
		if ( 'membership' === $post_type ) {
			$post_type = 'llms_membership';
		}

		// Sanitize order & orderby.
		$args['orderby'] = preg_replace( '/[^a-zA-Z_.]/', '', $args['orderby'] );
		$args['order']   = preg_replace( '/[^a-zA-Z_.]/', '', $args['order'] );

		// Allow "short" orderby's to be passed in without a table reference.
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

		// Prepare additional status AND clauses.
		if ( 'any' !== $args['status'] ) {
			$status = $wpdb->prepare(
				"
				AND upm.meta_value = %s
				AND upm.updated_date = (
					SELECT MAX( upm2.updated_date )
					  FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm2
					 WHERE upm2.meta_key = '_status'
					   AND upm2.user_id = %d
					   AND upm2.post_id = upm.post_id
					)",
				$args['status'],
				$this->get_id()
			);
		} else {
			$status = '';
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS DISTINCT upm.post_id AS id
			 FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
			 JOIN {$wpdb->posts} AS p ON p.ID = upm.post_id
			 WHERE p.post_type = %s
			   AND p.post_status = 'publish'
			   AND upm.meta_key = '_status'
			   AND upm.user_id = %d
			   {$status}
			 ORDER BY {$args['orderby']} {$args['order']}
			 LIMIT %d, %d;
			",
				array(
					$post_type,
					$this->get_id(),
					$args['skip'],
					$args['limit'],
				)
			),
			'OBJECT_K'
		); // db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$found = absint( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) ); // db call ok; no-cache ok.

		return array(
			'found'   => $found,
			'limit'   => $args['limit'],
			'more'    => ( $found > ( ( $args['skip'] / $args['limit'] + 1 ) * $args['limit'] ) ),
			'skip'    => $args['skip'],
			'results' => array_keys( $query ),
		);

	}

	/**
	 * Get the formatted date when a user initially enrolled in a product or when they were last updated
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Prepare SQL properly.
	 *
	 * @param   int    $product_id  WP Post ID of a course or membership
	 * @param   string $date        "enrolled" will get the most recent start date, "updated" will get the most recent status change date
	 * @param   string $format      date format as accepted by php date(), if none supplied uses the WP core "date_format" option
	 * @return  false|string        will return false if the user is not enrolled
	 */
	public function get_enrollment_date( $product_id, $date = 'enrolled', $format = null ) {

		if ( ! $format ) {
			$format = get_option( 'date_format', 'M d, Y' );
		}

		$cache_key = sprintf( 'date_%1$s_%2$s', $date, $product_id );
		$res       = $this->cache_get( $cache_key );

		if ( false === $res ) {

			$key = ( 'enrolled' === $date ) ? '_start_date' : '_status';

			global $wpdb;

			// Get the oldest recorded Enrollment date.
			$res = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT updated_date FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = %s AND user_id = %d AND post_id = %d ORDER BY updated_date DESC LIMIT 1",
					array( $key, $this->get_id(), $product_id )
				)
			);

			$this->cache_set( $cache_key, $res );

		}

		return ( $res ) ? date_i18n( $format, strtotime( $res ) ) : false;

	}

	/**
	 * Get the current enrollment status of a student for a particular product
	 *
	 * @since 3.0.0
	 * @since 3.17.0 Unknown.
	 * @since 3.37.9 Added filter `llms_user_enrollment_status_allowed_post_types`.
	 * @since 4.4.1 Moved filter `llms_user_enrollment_status_allowed_post_types` to function `llms_get_enrollable_status_check_post_types()`.
	 * @since 4.18.0 Added a tie-breaker when there are multiple enrollment statuses with the same date & time.
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
	 *
	 * @param  int  $product_id  WP Post ID of a Course, Section, Lesson, or Membership
	 * @param  bool $use_cache   If true, returns cached data if available, if false will run a db query
	 * @return false|string      When no enrollment status exists, returns `false`. Otherwise returns the
	 *                           enrollment status as a string.
	 */
	public function get_enrollment_status( $product_id, $use_cache = true ) {

		$status       = false;
		$product_type = get_post_type( $product_id );

		if ( ! in_array( $product_type, llms_get_enrollable_status_check_post_types(), true ) ) {
			/* This filter is documented at the end of this method. */
			return apply_filters( 'llms_get_enrollment_status', $status, $this->get_id(), $product_id, $use_cache );
		}

		// Get course ID if we're looking at a lesson or section.
		if ( in_array( $product_type, array( 'section', 'lesson' ), true ) ) {

			$llms_post = llms_get_post( $product_id );
			if ( $llms_post ) {
				$product_id = $llms_post->get( 'parent_course' );
			}
		}

		if ( $use_cache ) {
			$status = $this->cache_get( sprintf( 'enrollment_status_%d', $product_id ) );
		}

		/**
		 * After checking the cache, $status will be:
		 *     + `false` if there was nothing in the cache or the function was instructed to not use the cache: Query the database to get the status.
		 *     + a string if there was a status: No need to query the database.
		 *     + `null` if there's no status: No need to query the database.
		 */
		if ( false === $status ) {

			global $wpdb;

			// Get the most recent recorded status.
			$status = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->prefix}lifterlms_user_postmeta
					 WHERE meta_key = '_status' AND user_id = %d AND post_id = %d
					 ORDER BY updated_date DESC, meta_id DESC LIMIT 1;",
					array( $this->get_id(), $product_id )
				)
			);

			// Cache the data: `null` will be stored if the student has no status.
			$this->cache_set( sprintf( 'enrollment_status_%d', $product_id ), $status );

		}

		// Don't return `null` values from the database.
		$status = $status ? $status : false;

		/**
		 * Filter a user's enrollment status for a specific post.
		 *
		 * Note that if a value is modified by this filter the modified value is *not* cached. Therefore you should
		 * consider implementing caching of your modified value which matches the caching implemented by this method
		 * so that the modified value obeys the default caching behavior.
		 *
		 * @since Unknown
		 *
		 * @param false|string $status     When no enrollment status exists, returns `false`. Otherwise returns the
		 *                                     enrollment status as a string.
		 * @param int          $user_id    WP_User ID of the student
		 * @param int          $product_id WP_Post ID of the post used to check the enrollment status.
		 * @param boolean      $use_cache  Whether or not to use the local cache.
		 */
		return apply_filters( 'llms_get_enrollment_status', $status, $this->get_id(), $product_id, $use_cache );

	}

	/**
	 * Get the enrollment trigger for a the student's enrollment in a course
	 *
	 * @param    int $product_id   WP Post ID of the course or membership
	 * @return   string|false
	 * @since    ??
	 * @version  3.21.0
	 */
	public function get_enrollment_trigger( $product_id ) {

		$trigger = llms_get_user_postmeta( $this->get_id(), $product_id, '_enrollment_trigger', true );
		return $trigger ? $trigger : false;

	}

	/**
	 * Get the enrollment trigger id for a the student's enrollment in a course
	 *
	 * @param    int $product_id  WP Post ID of the course or membership
	 * @return   int|false
	 * @since    3.0.0
	 * @version  3.17.2
	 */
	public function get_enrollment_trigger_id( $product_id ) {

		$trigger = $this->get_enrollment_trigger( $product_id );
		$id      = false;
		if ( $trigger && false !== strpos( $trigger, 'order_' ) ) {
			$trigger_obj = $this->get_enrollment_order( $product_id );
			if ( $trigger_obj instanceof LLMS_Order ) {
				$id = $trigger_obj->get( 'id' );
			} elseif ( $trigger_obj instanceof WP_Post ) {
				$id = $trigger_obj->ID;
			}
		} elseif ( $trigger && false !== strpos( $trigger, 'admin_' ) ) {
			$id = absint( str_replace( 'admin_', '', $trigger ) );
		}
		return $id;

	}

	/**
	 * Retrieve postmeta events related to the student
	 *
	 * @param    array $args  default args, see LLMS_Query_User_Postmeta
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_events( $args = array() ) {

		$query = new LLMS_Query_User_Postmeta(
			wp_parse_args(
				$args,
				array(
					'types'    => 'all',
					'per_page' => 10,
					'user_id'  => $this->get_id(),
				)
			)
		);

		return $query->get_metas();

	}

	/**
	 * Get the students grade for a lesson / course
	 * All grades are based on quizzes assigned to lessons
	 *
	 * @param    int  $object_id  WP Post ID of a course or lesson
	 * @param    bool $use_cache  If true, uses cached results
	 * @return   mixed
	 * @since    ??
	 * @version  3.24.0
	 */
	public function get_grade( $object_id, $use_cache = true ) {
		$grade = LLMS()->grades()->get_grade( $object_id, $this, $use_cache );
		if ( is_null( $grade ) ) {
			$grade = _x( 'N/A', 'Grade to display when no quizzes taken or available', 'lifterlms' );
		}
		return apply_filters( 'llms_student_get_grade', $grade, $this, $object_id, get_post_type( $object_id ) );
	}

	/**
	 * Retrieve IDs of user's memberships based on supplied criteria
	 *
	 * @param    array $args   see `get_enrollments`
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_memberships( $args = array() ) {

		return $this->get_enrollments( 'membership', $args );

	}

	/**
	 * Retrieve a user's notification subscription preferences for a given type & trigger
	 *
	 * @param    string $type     notification type: email, basic, etc...
	 * @param    string $trigger  notification trigger: eg purchase_receipt, lesson_complete, etc...
	 * @param    string $default  value to return if no setting is saved in the db
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
	 *
	 * Grade = sum of grades for all courses divided by number of enrolled courses
	 * if a course has no quizzes in it, it cannot be graded and is therefore excluded from the calculation.
	 *
	 * Cached data is automatically cleared when a student completes a quiz.
	 *
	 * @since 3.2.0
	 *
	 * @param boolean $use_cache If `false`, calculates the grade, otherwise utilizes cached data (if available)
	 * @return float|string Grade as float or "N/A"
	 */
	public function get_overall_grade( $use_cache = true ) {

		$grade = null;

		// Attempt to pull from the cache first.
		if ( $use_cache ) {

			$grade = $this->get( $this->meta_prefix . 'overall_grade' );

			if ( is_numeric( $grade ) ) {
				$grade = floatval( $grade );
			}
		}

		// Cache disabled or no cached data available.
		if ( ! $use_cache || null === $grade || '' === $grade ) {

			$grades = array();

			// Get courses.
			$courses = $this->get_courses(
				array(
					'limit' => 9999,
				)
			);

			// Loop through courses.
			foreach ( $courses['results'] as $course_id ) {

				// Get course grade.
				$g = $this->get_grade( $course_id );

				// If an actual grade (not N/A) is returned.
				if ( is_numeric( $g ) ) {
					array_push( $grades, $g );
				}
			}

			// If we have at least one grade.
			$count = count( $grades );
			if ( $count ) {

				$grade = round( array_sum( $grades ) / $count, 2 );

			} else {

				$grade = _x( 'N/A', 'overall grade when no quizzes', 'lifterlms' );

			}

			// Cache the grade.
			$this->set( 'overall_grade', $grade );

		}

		return apply_filters( 'llms_student_get_overall_grade', $grade, $this );

	}

	/**
	 * Retrieve a student's overall progress
	 * Overall progress is the total percentage completed based on all courses the student is enrolled in
	 * Cached data is cleared every time the student completes a lesson
	 *
	 * @param    boolean $use_cache  if false, calculates the progress, otherwise utilizes cached data (if available)
	 * @return   float
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_overall_progress( $use_cache = true ) {

		$progress = null;

		// Attempt to pull from the cache first.
		if ( $use_cache ) {

			$progress = $this->get( $this->meta_prefix . 'overall_progress' );

			if ( is_numeric( $progress ) ) {
				$progress = floatval( $progress );
			}
		}

		// Cache disabled or no cached data available.
		if ( ! $use_cache || null === $progress || '' === $progress ) {

			$progresses = array();

			// Get courses.
			$courses = $this->get_courses(
				array(
					'limit' => 9999,
				)
			);

			// Loop through courses.
			foreach ( $courses['results'] as $course_id ) {
				array_push( $progresses, $this->get_progress( $course_id, 'course' ) );
			}

			$count = count( $progresses );
			if ( $count ) {

				$progress = round( array_sum( $progresses ) / $count, 2 );

			} else {

				$progress = 0;

			}

			// Cache the grade.
			$this->set( 'overall_progress', $progress );

		}

		return apply_filters( 'llms_student_get_overall_progress', $progress, $this );

	}

	/**
	 * Get the students last completed lesson in a course
	 *
	 * @param    int $course_id    WP_Post ID of the course
	 * @return   int                   WP_Post ID of the lesson or false if no progress has been made
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_last_completed_lesson( $course_id ) {

		$course  = new LLMS_Course( $course_id );
		$lessons = array_reverse( $course->get_lessons( 'ids' ) );

		foreach ( $lessons as $lesson ) {
			if ( $this->is_complete( $lesson, 'lesson' ) ) {
				return $lesson;
			}
		}

		return false;

	}

	/**
	 * Retrieve an array of Membership Levels for a user
	 *
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
	 *
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
	 *
	 * @param    int $course_id    WP_Post ID of the course
	 * @return   int                   WP_Post ID of the lesson or false if all courses are complete
	 * @since    3.0.1
	 * @version  3.0.1
	 */
	public function get_next_lesson( $course_id ) {

		$course  = new LLMS_Course( $course_id );
		$lessons = $course->get_lessons( 'ids' );

		foreach ( $lessons as $lesson ) {
			if ( ! $this->is_complete( $lesson, 'lesson' ) ) {
				return $lesson;
			}
		}

		return false;

	}

	public function get_orders( $params = array() ) {

		$params = wp_parse_args(
			$params,
			array(

				'count'    => 25,
				'page'     => 1,
				'statuses' => array_keys( llms_get_order_statuses() ),

			)
		);

		extract( $params );

		$q = new WP_Query(
			array(
				'order'          => 'DESC',
				'orderby'        => 'date',
				'meta_query'     => array(
					array(
						'key'   => '_llms_user_id',
						'value' => $this->get_id(),
					),
				),
				'paged'          => $page,
				'posts_per_page' => $count,
				'post_status'    => $statuses,
				'post_type'      => 'llms_order',
			)
		);

		$orders = array();

		if ( $q->have_posts() ) {

			foreach ( $q->posts as $post ) {

				$orders[ $post->ID ] = new LLMS_Order( $post );

			}
		}

		return array(
			'count'  => count( $q->posts ),
			'page'   => $page,
			'pages'  => $q->max_num_pages,
			'orders' => $orders,
		);

	}

	/**
	 * Get students progress through a course or track
	 *
	 * @param    int     $object_id  course or track id
	 * @param    string  $type       object type [course|course_track|section]
	 * @param    boolean $use_cache  if true, will use cached data from the usermeta table (if available)
	 *                               if false, will bypass cached data and recalculate the progress from scratch
	 * @return   float
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	public function get_progress( $object_id, $type = 'course', $use_cache = true ) {

		$ret       = 0;
		$cache_key = sprintf( '%1$s_%2$d_progress', $type, $object_id );
		$cached    = $use_cache ? $this->get( $cache_key ) : '';

		if ( '' === $cached ) {

			$total     = 0;
			$completed = 0;

			if ( 'course' === $type ) {

				$course  = new LLMS_Course( $object_id );
				$lessons = $course->get_lessons( 'ids' );
				$total   = count( $lessons );
				foreach ( $lessons as $lesson ) {
					if ( $this->is_complete( $lesson, 'lesson' ) ) {
						$completed++;
					}
				}
			} elseif ( 'course_track' === $type ) {

				$track   = new LLMS_Track( $object_id );
				$courses = $track->get_courses();
				$total   = count( $courses );
				foreach ( $courses as $course ) {
					if ( $this->is_complete( $course->ID, 'course' ) ) {
						$completed++;
					}
				}
			} elseif ( 'section' === $type ) {

				$section = new LLMS_Section( $object_id );
				$lessons = $section->get_lessons( 'ids' );
				$total   = count( $lessons );
				foreach ( $lessons as $lesson ) {
					if ( $this->is_complete( $lesson, 'lesson' ) ) {
						$completed++;
					}
				}
			}

			$ret = ( ! $completed || ! $total ) ? 0 : round( 100 / ( $total / $completed ), 2 );
			$this->set( $cache_key, $ret );

		} else {
			$ret = $cached;
		}// End if().

		/**
		 * @filter llms_student_get_progress
		 * Filters the return of get_progress method
		 * @param    float   $ret        student's progress
		 * @param    int     $object_id  WP_Post ID of the object
		 * @param    string  $type       object post type [course|course_track|section]
		 * @param    int     $user_id    WP_User ID of the student
		 * @since    unknown
		 * @version  3.24.0
		 */
		return apply_filters( 'llms_student_get_progress', $ret, $object_id, $type, $this->get_id() );

	}

	/**
	 * Retrieve the student's original registration date in the chosen format.
	 *
	 * @since Unknown
	 * @since 5.2.0 Changed the date to be relative to the local time zone.
	 *
	 * @param string $format Any date format that can be passed to date().
	 * @return string
	 */
	public function get_registration_date( $format = '' ) {

		if ( ! $format ) {
			$format = get_option( 'date_format' );
		}

		return wp_date( $format, strtotime( $this->get( 'user_registered' ) ) );

	}

	/**
	 * Determine if the student is active in at least one course or membership
	 *
	 * @since 3.14.0
	 *
	 * @return boolean
	 */
	public function is_active() {

		// Check memberships first, it's a faster query.
		if ( $this->get_membership_levels() ) {
			return true;
		}

		// Check for at least one enrolled course.
		$courses = $this->get_courses(
			array(
				'limit'  => 1,
				'status' => 'enrolled',
			)
		);

		if ( $courses['results'] ) {
			return true;
		}

		// Not active.
		return false;

	}

	/**
	 * Determine if the student has completed a course, track, or lesson
	 *
	 * @param    int    $object_id  WP Post ID of a course or lesson or section or the term id of the track
	 * @param    string $type    Object type (course, lesson, section, or track)
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	public function is_complete( $object_id, $type = 'course' ) {

		// check tracks by progress
		// this is done because tracks can have the same id as another object...
		// @todo tracks should have a different table or format since the post_id col won't guarantee uniqueness...
		if ( 'course_track' === $type ) {

			$ret = ( 100 == $this->get_progress( $object_id, $type ) );

			// Everything else can be checked on the postmeta table.
		} else {

			$query = new LLMS_Query_User_Postmeta(
				array(
					'types'                 => 'completion',
					'include_post_children' => false,
					'user_id'               => $this->get_id(),
					'post_id'               => $object_id,
					'per_page'              => 1,
				)
			);

			$ret = $query->has_results();

		}

		return apply_filters( 'llms_is_' . $type . '_complete', $ret, $object_id, $type, $this );

	}

	/**
	 * Determine if the student is a LifterLMS Instructor (of any kind)
	 *
	 * Can be admin, manager, instructor, assistant.
	 *
	 * @return   boolean
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function is_instructor() {
		return $this->user->has_cap( 'lifterlms_instructor' );
	}

	/**
	 * Add student postmeta data for completion of a lesson, section, course or track
	 *
	 * @param  int    $object_id    WP Post ID of the lesson, section, course or track
	 * @param  string $trigger      String describing the reason for mark completion
	 * @return boolean
	 * @since    3.3.1
	 * @version  3.21.0
	 */
	private function insert_completion_postmeta( $object_id, $trigger = 'unspecified' ) {

		// Add info to the user postmeta table.
		$user_metadatas = array(
			'_is_complete'        => 'yes',
			'_completion_trigger' => $trigger,
		);

		$update = llms_bulk_update_user_postmeta( $this->get_id(), $object_id, $user_metadatas, false );

		// Returns an array with errored keys or true on success.
		return is_array( $update ) ? false : true;

	}

	/**
	 * Add student postmeta data for incompletion of a lesson, section, course or track
	 * An "_is_complete" value of "no" is inserted into postmeta
	 *
	 * @param    int    $object_id    WP Post ID of the lesson, section, course or track
	 * @param    string $trigger      String describing the reason for mark incompletion
	 * @return   boolean
	 * @since    3.5.0
	 * @version  3.24.0
	 */
	private function insert_incompletion_postmeta( $object_id, $trigger = 'unspecified' ) {

		global $wpdb;

		// Add '_is_complete' to the user postmeta table for object.
		$user_metadatas = array(
			'_is_complete'        => 'no',
			'_completion_trigger' => $trigger,
		);

		foreach ( $user_metadatas as $key => $value ) {

			/**
			 * It's too difficult to keep track of multiple postmetas for each lesson incomplete
			 * Instead, I'm just replacing the old '_is_complete' value with 'no'
			 *
			 * Lessons that have never been complete will not have an '_is_complete' record,
			 * Lessons that were completed will have an '_is_complete' record of 'yes',
			 * Lessons that have been completed once but were marked incomplete will have an '_is_complete' record of 'no'
			 */
			$update = $wpdb->update(
				$wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id'      => $this->get_id(),
					'post_id'      => $object_id,
					'meta_key'     => $key,
					'meta_value'   => $value,
					'updated_date' => current_time( 'mysql' ),
				),
				array(
					'user_id'  => $this->get_id(),
					'post_id'  => $object_id,
					'meta_key' => $key,
				),
				array( '%d', '%d', '%s', '%s', '%s' )
			); // db call ok; no-cache ok.

			if ( false === $update ) {

				return false;

			}
		}

		return true;

	}

	/**
	 * Add student postmeta data for enrollment into a course or membership
	 *
	 * @param    int    $product_id   WP Post ID of the course or membership
	 * @param    string $trigger      String describing the reason for enrollment
	 * @return   boolean
	 * @since    2.2.3
	 * @version  3.21.0
	 */
	private function insert_enrollment_postmeta( $product_id, $trigger = 'unspecified' ) {

		// Add info to the user postmeta table.
		$user_metadatas = array(
			'_enrollment_trigger' => $trigger,
			'_start_date'         => 'yes',
			'_status'             => 'enrolled',
		);

		$update = llms_bulk_update_user_postmeta( $this->get_id(), $product_id, $user_metadatas, false );

		// Returns an array with errored keys or true on success.
		return is_array( $update ) ? false : true;

	}

	/**
	 * Remove student enrollment postmeta for a given product.
	 *
	 * @since 3.33.0
	 *
	 * @param int    $product_id WP Post ID of the course or membership.
	 * @param string $trigger    Optional. String the reason for enrollment. Default `null`
	 * @return boolean Whether or not the enrollment records have been succesfully removed.
	 */
	private function delete_enrollment_postmeta( $product_id, $trigger = null ) {

		// Delete info from the user postmeta table.
		$user_metadatas = array(
			'_enrollment_trigger' => $trigger,
			'_start_date'         => null,
			'_status'             => null,
		);

		$delete = llms_bulk_delete_user_postmeta( $this->get_id(), $product_id, $user_metadatas );

		return is_array( $delete ) ? false : true;
	}

	/**
	 * Add a new status record to the user postmeta table for a specific product
	 *
	 * @param    int    $product_id   WP Post ID of the course or membership
	 * @param    string $status       string describing the new status
	 * @param    string $trigger  String describing the reason for enrollment (optional)
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.21.0
	 */
	private function insert_status_postmeta( $product_id, $status = '', $trigger = null ) {

		$update = llms_update_user_postmeta( $this->get_id(), $product_id, '_status', $status, false );

		if ( $update && $trigger ) {
			$update = llms_update_user_postmeta( $this->get_id(), $product_id, '_enrollment_trigger', $trigger, false );
		}

		return $update;

	}

	/**
	 * Determine if a student is enrolled in a Course or Membership.
	 *
	 * @see     llms_is_user_enrolled()
	 *
	 * @param   int|array $product_ids WP Post ID of a Course, Section, Lesson, or Membership or array of multiple IDs.
	 * @param   string    $relation    Comparator for enrollment check.
	 *                                     All = user must be enrolled in all $product_ids.
	 *                                     Any = user must be enrolled in at least one of the $product_ids.
	 * @param   bool      $use_cache  If true, returns cached data if available, if false will run a db query.
	 *
	 * @return  boolean
	 *
	 * @since   3.0.0
	 * @version 3.25.0
	 */
	public function is_enrolled( $product_ids = null, $relation = 'all', $use_cache = true ) {

		// Assume enrollment unless we find otherwise.
		$ret = true;

		// Allow a single product ID to be submitted (backwards compat).
		$product_ids = ! is_array( $product_ids ) ? array( $product_ids ) : $product_ids;

		foreach ( $product_ids as $id ) {

			$enrolled = ( 'enrolled' === strtolower( $this->get_enrollment_status( $id, $use_cache ) ) );

			// If use must be enrolled in all products and one is not enrolled: quit the loop & return false.
			if ( 'all' === $relation && ! $enrolled ) {
				$ret = false;
				break;

				// If user must be enrolled in any.
			} elseif ( 'any' === $relation ) {

				// If we find an enrollment: return true and quit the loop.
				if ( $enrolled ) {
					$ret = true;
					break;

					// If not switch return to false but keep looking.
				} else {
					$ret = false;
				}
			}
		}

		return apply_filters( 'llms_is_user_enrolled', $ret, $this, $product_ids, $relation, $use_cache );

	}

	/**
	 * Mark a lesson, section, course, or track complete for the given user
	 *
	 * @param  int    $object_id    WP Post ID of the lesson, section, course, or track
	 * @param  string $object_type  object type [lesson|section|course|track]
	 * @param  string $trigger      String describing the reason for marking complete
	 * @return boolean
	 *
	 * @see    llms_mark_complete() calls this function without having to instantiate the LLMS_Student class first
	 *
	 * @since    3.3.1
	 * @version  3.17.1
	 */
	public function mark_complete( $object_id, $object_type, $trigger = 'unspecified' ) {

		// Short circuit if it's already completed.
		if ( $this->is_complete( $object_id, $object_type ) ) {
			return true;
		}

		return $this->update_completion_status( 'complete', $object_id, $object_type, $trigger );

	}

	/**
	 * Mark a lesson, section, course, or track incomplete for the given user
	 * Gives an "_is_complete" value of "no" for the given object
	 *
	 * @param  int    $object_id    WP Post ID of the lesson, section, course, or track
	 * @param  string $object_type  object type [lesson|section|course|track]
	 * @param  string $trigger      String describing the reason for marking incomplete
	 * @return boolean
	 *
	 * @see    llms_mark_incomplete() calls this function without having to instantiate the LLMS_Student class first
	 *
	 * @since    3.5.0
	 * @version  3.17.0
	 */
	public function mark_incomplete( $object_id, $object_type, $trigger = 'unspecified' ) {

		return $this->update_completion_status( 'incomplete', $object_id, $object_type, $trigger );

	}

	/**
	 * Remove a student from a membership level.
	 *
	 * @since 2.7
	 * @since 3.7.5 Unknown.
	 * @since 3.36.2 Added the $delete paramater, that will allow related courses enrollments data deletion.
	 *
	 * @param  int     $membership_id WP Post ID of the membership.
	 * @param  string  $status        Optional. Status to update the removal to. Default is `expired`.
	 * @param  boolean $delete        Optional. Status to update the removal to. Default is `false`.
	 * @return void
	 */
	private function remove_membership_level( $membership_id, $status = 'expired', $delete = false ) {

		// Remove the user from the membership level.
		$membership_levels = $this->get_membership_levels();
		$key               = array_search( $membership_id, $membership_levels );
		if ( false !== $key ) {
			unset( $membership_levels[ $key ] );
		}
		update_user_meta( $this->get_id(), '_llms_restricted_levels', $membership_levels );

		global $wpdb;
		// Locate all enrollments triggered by this membership level.
		$q = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d AND meta_key = '_enrollment_trigger' AND meta_value = %s",
				array( $this->get_id(), 'membership_' . $membership_id )
			),
			'OBJECT_K'
		); // db call ok; no-cache ok.

		$courses = array_keys( $q );

		if ( $courses ) {

			// Loop through all the courses and update the enrollment status.
			foreach ( $courses  as $course_id ) {
				if ( ! $delete ) {
					$this->unenroll( $course_id, 'membership_' . $membership_id, $status );
				} else {
					$this->delete_enrollment( $course_id, 'membership_' . $membership_id );
				}
			}
		}

	}

	/**
	 * Remove a student from a LifterLMS course or membership
	 *
	 * @since 3.0.0
	 * @since 3.26.0 Unknown.
	 * @since 3.37.9 Update to accommodate custom post type enrollments added through new filters.
	 *               Marked action `llms_user_removed_from_membership_level` as deprecated, use `llms_user_removed_from_membership` instead.
	 *
	 * @see llms_unenroll_student()
	 *
	 * @param  int    $product_id WordPress Post ID of the course or membership.
	 * @param  string $trigger    Only remove the student if the original enrollment trigger matches the submitted value.
	 *                            Passing `any` will remove regardless of enrollment trigger.
	 * @param  string $new_status the value to update the new status with after removal is complete.
	 * @return boolean
	 */
	public function unenroll( $product_id, $trigger = 'any', $new_status = 'expired' ) {

		// Can only unenroll those that are a currently enrolled.
		if ( ! $this->is_enrolled( $product_id, 'all', false ) ) {
			return false;
		}

		// Assume we can't unenroll.
		$update = false;

		// If trigger is "any" we'll unenroll regardless of the trigger.
		if ( 'any' === $trigger ) {

			$update = true;

		} else {

			$enrollment_trigger = $this->get_enrollment_trigger( $product_id );

			// No enrollment trigger exists b/c pre 3.0.0 enrollment, unenroll the user as if it was an 'any' trigger.
			if ( ! $enrollment_trigger ) {

				/**
				 * This filter allows customization of enrollments created prior to version 3.0.0
				 *
				 * Prior to 3.0.0 enrollments did not track an enrollment trigger so any unenrollments
				 * performed on an enrollment in this state will automatically be unenrolled.
				 *
				 * Returning `false` will prevent unenrollments against enrollments which don't have
				 * an enrollment trigger.
				 *
				 * @since 3.0.0
				 *
				 * @param bool $allow_unenrollment If true, allows unenrollment, otherwise prevents unenrollment.
				 */
				$update = apply_filters( 'lifterlms_legacy_unenrollment_action', true );

			} elseif ( $enrollment_trigger === $trigger ) {

				$update = true;

			}
		}

		// Update if we can.
		if ( $update ) {

			// Update enrollment for the product.
			if ( $this->insert_status_postmeta( $product_id, $new_status ) ) {

				// Update the cache.
				$this->cache_set( sprintf( 'enrollment_status_%d', $product_id ), $new_status );
				$this->cache_delete( sprintf( 'date_enrolled_%d', $product_id ) );
				$this->cache_delete( sprintf( 'date_updated_%d', $product_id ) );

				$post_type = str_replace( 'llms_', '', get_post_type( $product_id ) );

				/**
				 * Trigger an action immediately following user unenrollment
				 *
				 * The dynamic portion of this hook, `{$post_type}` corresponds to the post type of the
				 * `$product_id`. Note that any post type prefixed with `llms_` is stripped. For example
				 * when triggered by a memebership (`llms_membership`) the hook will be `llms_user_removed_from_membership`.
				 *
				 * @since 3.37.9
				 *
				 * @param int    $user_id    WP_User ID of the student
				 * @param int    $product_id WP_Post ID of the product.
				 * @param string $trigger    Enrollment trigger.
				 * @param string $new_status New enrollment status of the student after the unenrollment has taken place.
				 */
				do_action( "llms_user_removed_from_{$post_type}", $this->get_id(), $product_id, $trigger, $new_status );

				// Run legacy action and trigger cascading unenrollments for membership relationships.
				if ( 'membership' === $post_type ) {

					// Users should be unenrolled from all courses they accessed through this membership.
					$this->remove_membership_level( $product_id, $new_status );

					/**
					 * Execute the (deprecated) legacy action.
					 *
					 * @since      Unknown
					 * @deprecated 3.37.9 Use `llms_user_removed_from_membership` instead for consistency with courses.
					 *
					 * @param int    $user_id    WP_User ID of the student
					 * @param int    $product_id WP_Post ID of the product.
					 */
					do_action( 'llms_user_removed_from_membership_level', $this->get_id(), $product_id );

				}

				return true;

			}
		}

		// Update was prevented.
		return false;

	}

	/**
	 * Delete a student enrollment.
	 *
	 * @since 3.33.0
	 * @since 3.36.2 Added logic to physically remove from the membership level and remove enrollments data on related products.
	 * @since 4.2.0 The `$enrollment_trigger` parameter was added to the `llms_user_enrollment_deleted` action hook.
	 *
	 * @see `llms_delete_student_enrollment()` calls this function without having to instantiate the LLMS_Student class first.
	 *
	 * @param int    $product_id WP Post ID of the course or membership.
	 * @param string $trigger    Optional. Only delete the student's enrollment if the original enrollment trigger matches the submitted value.
	 *                           "any" will remove regardless of enrollment trigger. Default "any".
	 * @return boolean Whether or not the enrollment records have been successfully removed.
	 */
	public function delete_enrollment( $product_id, $trigger = 'any' ) {

		// Assume we can't delete the enrollment.
		$delete = false;

		// Get the stored trigger.
		$enrollment_trigger = $this->get_enrollment_trigger( $product_id );

		// Okay to delete if trigger is "any" or if it matches the stored enrollment trigger.
		if ( 'any' === $trigger || $enrollment_trigger === $trigger ) {

			$delete = true;

		} elseif ( ! $enrollment_trigger ) {

			/**
			 * Customize the behavior of enrollment deletion for "legacy" orders.
			 *
			 * These orders were created before version 3.0.0 when there was no stored
			 * enrollment trigger.
			 *
			 * By default, we'll automatically delete these enrollments regardless of trigger.
			 *
			 * @since 3.33.0
			 *
			 * @param boolean $delete Whether or not to delete the enrollment.
			 */
			$delete = apply_filters( 'lifterlms_legacy_delete_enrollment_action', true );

			// Ensure we have an `$enrollment_trigger` when firing the `llms_user_enrollment_deleted` hook.
			$enrollment_trigger = $trigger;

		}

		// Delete the enrollment.
		if ( $delete && $this->delete_enrollment_postmeta( $product_id ) ) {

			// Clean the cache.
			$this->cache_delete( sprintf( 'enrollment_status_%d', $product_id ) );
			$this->cache_delete( sprintf( 'date_enrolled_%d', $product_id ) );
			$this->cache_delete( sprintf( 'date_updated_%d', $product_id ) );

			if ( 'llms_membership' === get_post_type( $product_id ) ) {
				// Physically remove from the membership level & remove enrollments data on related products.
				$this->remove_membership_level( $product_id, '', true );
			}

			/**
			 * Fires after an user enrollment has been deleted.
			 *
			 * @since 3.33.0
			 * @since 4.2.0 The `$enrollment_trigger` parameter was added.
			 *
			 * @param int    $user_id            WP User ID.
			 * @param int    $product_id         WP Post ID of the course or membership.
			 * @param string $enrollment_trigger The enrollment trigger.
			 */
			do_action( 'llms_user_enrollment_deleted', $this->get_id(), $product_id, $enrollment_trigger );

			// Success.
			return true;

		}

		// Nothing was deleted.
		return false;

	}

	/**
	 * Update the completion status of a track, course, section, or lesson for the current student
	 *
	 * Cascades up to parents and clears progress caches for parents.
	 *
	 * Triggers actions for completion/incompletion.
	 *
	 * Inserts / updates necessary user postmeta data.
	 *
	 * @since 3.17.0
	 * @since 4.2.0 Use filterable functions to determine if the object is completable.
	 *              Added filter to allow customization of object parent data.
	 *
	 * @param string $status      New status to update to, either "complete" or "incomplete".
	 * @param int    $object_id   WP_Post ID of the object.
	 * @param string $object_type The type of object. A lesson, section, course, or course_track.
	 * @param string $trigger     String describing the reason for the status change.
	 * @return boolean
	 */
	private function update_completion_status( $status, $object_id, $object_type, $trigger = 'unspecified' ) {

		$student_id = $this->get_id();

		/**
		 * Fires before a student's object completion status is updated.
		 *
		 * The dynamic portion of this hook, `$status`, refers to the new completion status of the object,
		 * either "complete" or "incomplete"
		 *
		 * @since Unknown
		 *
		 * @param int    $student_id  WP_User ID of the student.
		 * @param int    $object_id   WP_Post ID of the object.
		 * @param string $object_type The type of object. A lesson, section, course, or course_track.
		 * @param string $trigger     String describing the reason for the status change.
		 */
		do_action( "before_llms_mark_{$status}", $student_id, $object_id, $object_type, $trigger );

		// Retrieve an instance of the objec we're acting on.
		if ( in_array( $object_type, llms_get_completable_post_types(), true ) ) {
			$object = llms_get_post( $object_id );
		} elseif ( in_array( $object_type, llms_get_completable_taxonomies(), true ) ) {
			$object = get_term( $object_id, $object_type );
		} else {
			return false;
		}

		/**
		 * Lessons have binary completion (complete or incomplete).
		 *
		 * Other objects are dependent on their children's statuses. These other object types
		 * must check the combined progress of their children to see if it's complete / incomplete.
		 */
		$complete = ( 'lesson' === $object_type ) ? ( 'complete' === $status ) : ( 100 == $this->get_progress( $object_id, $object_type, false ) );

		// Get parent information.
		$parent_data = array(
			'ids'  => array(),
			'type' => false,
		);

		// Get the immediate parent so we can cascade up and maybe update the parent's status.
		switch ( $object_type ) {

			case 'lesson':
				$parent_data['ids']  = array( $object->get( 'parent_section' ) );
				$parent_data['type'] = 'section';
				break;

			case 'section':
				$parent_data['ids']  = array( $object->get( 'parent_course' ) );
				$parent_data['type'] = 'course';
				break;

			case 'course':
				$parent_data['ids']  = wp_list_pluck( $object->get_tracks(), 'term_id' );
				$parent_data['type'] = 'course_track';
				break;

		}

		/**
		 * Filter the parent data used to cascade object completion up to an object's parent(s).
		 *
		 * @since 4.2.0
		 *
		 * @param array  $parent_data {
		 *     Array of the object's parent information.
		 *
		 *     @type int[]  $ids  Object ids for the parent object(s).
		 *     @type string $type Object type (course, course_track, etc...).
		 * }
		 * @param object $object      The object. An `LLMS_Course`, for example.
		 * @param int    $ojbect_id   The object's ID.
		 * @param string $object_type The object's type.
		 */
		$parent_data = apply_filters( 'llms_mark_complete_parent_data', $parent_data, $object, $object_id, $object_type );

		// Reset the cached progress for any objects with children.
		if ( 'lesson' !== $object_type ) {
			$this->set( sprintf( '%1$s_%2$d_progress', $object_type, $object_id ), '' );
		}

		// Reset cache for all parents.
		if ( $parent_data['ids'] && $parent_data['type'] ) {
			foreach ( $parent_data['ids'] as $pid ) {
				$this->set( sprintf( '%1$s_%2$d_progress', $parent_data['type'], $pid ), '' );
			}
		}

		// Determine if an update should be made.
		$update = ( 'complete' === $status && $complete ) || ( 'incomplete' === $status && ! $complete );

		if ( $update ) {

			// Insert meta data.
			if ( 'complete' === $status ) {
				$this->insert_completion_postmeta( $object_id, $trigger );
			} elseif ( 'incomplete' === $status ) {
				$this->insert_incompletion_postmeta( $object_id, $trigger );
			}

			/**
			 * Hook that fires when a student's completion status is updated for any object.
			 *
			 * The dynamic portion of this hook, `$status`, refers to the new completion status of the object,
			 * either "complete" or "incomplete"
			 *
			 * @since Unknown
			 *
			 * @param int    $student_id  WP_User ID of the student.
			 * @param int    $object_id   WP_Post ID of the object.
			 * @param string $object_type The type of object. A lesson, section, course, or course_track.
			 * @param string $trigger     String describing the reason for the status change.
			 */
			do_action( "llms_mark_{$status}", $student_id, $object_id, $object_type, $trigger );

			/**
			 * Hook that fires when a student's completion status is updated for a specific object type.
			 *
			 * The dynamic portion of this hook, `$object_type` refers to the WP_Post post_type of the object
			 * which the student's completion status is being updated for.
			 *
			 * The dynamic portion of this hook, `$status`, refers to the new completion status of the object,
			 * either "complete" or "incomplete"
			 *
			 * @since Unknown
			 *
			 * @param int $student_id WP_User ID of the student.
			 * @param int $object_id  WP_Post ID of the object.
			 */
			do_action( "lifterlms_{$object_type}_{$status}d", $student_id, $object_id );

			// Cascade up for parents.
			if ( $parent_data['ids'] && $parent_data['type'] ) {
				foreach ( $parent_data['ids'] as $pid ) {
					$this->update_completion_status( $status, $pid, $parent_data['type'], $trigger );
				}
			}

			/**
			 * Hook that fires after a student's completion status for an object and it's parents have
			 * been updated.
			 *
			 * The dynamic portion of this hook, `$status`, refers to the new completion status of the object,
			 * either "complete" or "incomplete"
			 *
			 * @since Unknown
			 *
			 * @param int    $student_id  WP_User ID of the student.
			 * @param int    $object_id   WP_Post ID of the object.
			 * @param string $object_type The type of object. A lesson, section, course, or course_track.
			 * @param string $trigger     String describing the reason for the status change.
			 */
			do_action( "after_llms_mark_{$status}", $student_id, $object_id, $object_type, $trigger );

		}

		return $update;

	}

}
