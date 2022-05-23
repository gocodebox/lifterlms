<?php
/**
 * LifterLMS singleton trait.
 *
 * @package LifterLMS/Traits
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve data related to awards earned by a student.
 *
 * This trait should only be used by classes that extend from the {@see LLMS_Abstract_User_Data} class.
 *
 * @since 6.0.0
 */
trait LLMS_Trait_Student_Awards {

	/**
	 * Retrieve achievements that a user has earned.
	 *
	 * @since 2.4.0
	 * @since 3.14.0 Unknown.
	 * @since 6.0.0 Moved from `LLMS_Student` class.
	 *              Introduced alternate usage via `LLMS_Awards_Query` and deprecated previous behavior.
	 *
	 * @param string|array $args_or_orderby An array of arguments to pass to LLMS_Awards_Query. The deprecated method
	 *                                      signature accepts a string representing the field to order the returned results by.
	 * @param string       $order           Deprecated signature only: Ordering method for returned results (ASC or DESC).
	 * @param string       $return          Deprecated signature only: Return type. Accepts "obj" for an array of objects from
	 *                                      $wpdb->get_results and "certificates" for an array of LLMS_User_Certificate instances.
	 * @return LLMS_Awards_Query|object[]|LLMS_User_Achievement[]
	 */
	public function get_achievements( $args_or_orderby = 'updated_date', $order = 'DESC', $return = 'obj' ) {

		// New behavior.
		if ( is_array( $args_or_orderby ) ) {
			return $this->get_awards( $args_or_orderby, 'achievement' );
		}

		_deprecated_function( 'LLMS_Student::get_achievements()', '6.0.0', 'The behavior of this method has changed. Please refer to https://developer.lifterlms.com/reference/classes/llms_student/get_achievements/ for more information.' );

		$orderby = esc_sql( $args_or_orderby );
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

	/**
	 * Query student awards.
	 *
	 * @since 6.0.0
	 *
	 * @param array  $args Query arguments to pass into `LLMS_Awards_Query`.
	 * @param string $type Award type. Accepts "any", "achievement", or "certificate".
	 * @return LLMS_Awards_Query
	 */
	public function get_awards( $args = array(), $type = 'any' ) {

		$args['type']  = $type;
		$args['users'] = $this->get_id();

		// Prevent potential funny business.
		unset( $args['users__exclude'] );

		return new LLMS_Awards_Query( $args );
	}

	/**
	 * Retrieve the total number of awards earned by the student.
	 *
	 * @since 6.0.0
	 *
	 * @param string $type Award type. Accepts "any", "achievement", or "certificate".
	 * @return int
	 */
	public function get_awards_count( $type = 'any' ) {

		$query = $this->get_awards(
			array(
				'per_page' => 1,
				'fields'   => 'ids',
			),
			$type
		);
		return $query->get_found_results();

	}

	/**
	 * Retrieve certificates that the student has been awarded.
	 *
	 * The default behavior of this method is deprecated since version 6.0.0. The previous behavior
	 * is retained for backwards compatibility but will be removed in the next major release.
	 *
	 * @since 2.4.0
	 * @since 3.14.1 Unknown.
	 * @since 6.0.0 Moved from `LLMS_Student` class.
	 *              Introduced alternate usage via `LLMS_Awards_Query` and deprecated previous behavior.
	 *
	 * @param string|array $args_or_orderby An array of arguments to pass to LLMS_Awards_Query. The deprecated method
	 *                                      signature accepts a string representing the field to order the returned results by.
	 * @param string       $order           Deprecated signature only: Ordering method for returned results (ASC or DESC).
	 * @param string       $return          Deprecated signature only: Return type. Accepts "obj" for an array of objects from
	 *                                      $wpdb->get_results and "certificates" for an array of LLMS_User_Certificate instances.
	 * @return LLMS_Awards_Query|object[]|LLMS_User_Certificate[]
	 */
	public function get_certificates( $args_or_orderby = 'updated_date', $order = 'DESC', $return = 'obj' ) {

		// New behavior.
		if ( is_array( $args_or_orderby ) ) {
			return $this->get_awards( $args_or_orderby, 'certificate' );
		}

		_deprecated_function( 'LLMS_Student::get_certificates()', '6.0.0', 'The behavior of this method has changed. Please refer to https://developer.lifterlms.com/reference/classes/llms_student/get_certificates/ for more information.' );

		$orderby = esc_sql( $args_or_orderby );
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

}
