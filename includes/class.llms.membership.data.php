<?php
/**
 * Query data about a membership.
 *
 * @package LifterLMS/Classes
 *
 * @since 3.32.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query data about a membership.
 *
 * @since 3.32.0
 * @since 3.35.0 Sanitize post ids from WP_Query before using for a new DB query.
 */
class LLMS_Membership_Data extends LLMS_Abstract_Post_Data {

	use LLMS_Trait_Post_Enrollment_Data;
	use LLMS_Trait_Post_Order_Data;

	/**
	 * Retrieve # of engagements related to the membership awarded within the period.
	 *
	 * @since 3.32.0
	 *
	 * @param string $type   Engagement type [email|certificate|achievement].
	 * @param string $period Optional. Date period [current|previous]. Default 'current'.
	 * @return int
	 */
	public function get_engagements( $type, $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_key = %s
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
				'_' . $type,
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

	}

}
