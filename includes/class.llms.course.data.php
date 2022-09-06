<?php
/**
 * Query data about a course
 *
 * @package LifterLMS/Classes
 *
 * @since 3.15.0
 * @version 5.10.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query data about a course
 *
 * @since 3.15.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 3.31.0 Extends LLMS_Abstract_Post_Data.
 * @since 4.0.0 Remove previously deprecated class properties `$course` and `$course_id`.
 */
class LLMS_Course_Data extends LLMS_Abstract_Post_Data {

	use LLMS_Trait_Post_Enrollment_Data;
	use LLMS_Trait_Post_Order_Data;

	/**
	 * Constructor
	 *
	 * @since 3.15.0
	 *
	 * @param int $course_id WP Post ID of the course
	 */
	public function __construct( $course_id ) {

		$this->course_id = $course_id;
		$this->course    = llms_get_post( $this->course_id );
		parent::__construct( $course_id );

	}

	/**
	 * Retrieve an array of all post ids in the course
	 *
	 * Includes course id, all section ids, all lesson ids, and all quiz ids.
	 *
	 * @since 3.15.0
	 * @since 3.31.0 Use $this->post_id instead of deprecated $this->course_id.
	 *
	 * @return array
	 */
	private function get_all_ids() {
		return array_merge(
			array( $this->post_id ),
			$this->post->get_sections( 'ids' ),
			$this->post->get_lessons( 'ids' ),
			$this->post->get_quizzes()
		);
	}

	/**
	 * Retrieve # of course completions within the period
	 *
	 * @since 3.15.0
	 * @since 3.31.0 Use $this->post_id instead of deprecated $this->course_id.
	 *
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_completions( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);// db call ok; no-cache ok.

	}

	/**
	 * Retrieves and returns the number of lessons completed within the period.
	 *
	 * @since 3.15.0
	 * @since 5.10.0 Fixed issue when the course has no lessons.
	 *
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_lesson_completions( $period = 'current' ) {

		global $wpdb;

		$lessons = implode( ',', $this->post->get_lessons( 'ids' ) );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Return early for courses without any lessons.
		if ( empty( $lessons ) ) {
			return 0;
		}

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( * )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id IN ( {$lessons} )
			  AND updated_date BETWEEN %s AND %s
			",
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);// db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	}

}
