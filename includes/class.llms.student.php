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

				$this->enroll( $course_id );

			}

		}

	}


	/**
	 * Enroll the student in a course or membership
	 * @param  int     $product_id  WP Post ID of the course or membership
	 * @return boolean
	 *
	 * @since  2.2.3
	 */
	public function enroll( $product_id ) {

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
		if ( $this->insert_enrollment_postmeta( $product_id ) ) {

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
	 * @return boolean
	 *
	 * @since  2.2.3
	 */
	private function insert_enrollment_postmeta( $product_id ) {

		global $wpdb;

		// add info to the user postmeta table
		$user_metadatas = array(
			'_start_date'        => 'yes',
			'_status'            => 'Enrolled',
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

}
