<?php
/**
 * LLMS_Trait_Post_Enrollment_Data
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enrollment reporting utility methods intended to be used by classes that extend
 * {@see LLMS_Abstract_Post_Data}.
 *
 * @since [version]
 */
trait LLMS_Trait_Post_Enrollment_Data {

	/**
	 * Retrieve # of course enrollments within the period
	 *
	 * @since [version]
	 *
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_enrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_start_date'
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
	 * Retrieve the number of unenrollments on a given date.
	 *
	 * @since [version]
	 *
	 * @param string $period Optional. Date period [current|previous]. Default 'current'.
	 * @return int
	 */
	public function get_unenrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value != 'enrolled'
			  AND meta_key = '_status'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

	}

}