<?php
/**
 * LLMS_Abstract_User_Engagement class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for shared functionality for earned engagements (certificates and achievements).
 *
 * @since [version]
 */
abstract class LLMS_Abstract_User_Engagement extends LLMS_Post_Model {

	/**
	 * Delete the engagement
	 *
	 * @since 3.18.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return void
	 */
	public function delete() {

		/**
		 * Action fired immediately prior to the deletion of a user's earned engagement.
		 *
		 * They dynamic portion of this hook, `{$this->model_post_type}`, refers to the engagement type,
		 * either "achievement" or "certificate".
		 *
		 * @since 3.18.0
		 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
		 *
		 * @param LLMS_User_Certificate $certificate Certificate class object.
		 */
		do_action( "llms_before_delete_{$this->model_post_type}", $this );

		global $wpdb;
		$id = $this->get( 'id' );
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'user_id'    => $this->get_user_id(),
				'meta_key'   => $this->get_user_post_meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			),
			array( '%d', '%s', '%d' )
		);
		wp_delete_post( $id, true );

		/**
		 * Action fired immediately after the deletion of a user's earned engagement.
		 * They dynamic portion of this hook, `{$this->model_post_type}`, refers to the engagement type,
		 * either "achievement" or "certificate".
		 *
		 * @since 3.18.0
		 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
		 *
		 * @param LLMS_User_Certificate $certificate Certificate class object.
		 */
		do_action( "llms_delete_{$this->model_post_type}", $this );

	}

	/**
	 * Retrieve the date the achievement was earned (created)
	 *
	 * @since 3.14.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @param string $format Date format string.
	 * @return string
	 */
	public function get_earned_date( $format = null ) {
		$format = $format ? $format : get_option( 'date_format' );
		return $this->get_date( 'date', $format );
	}

	/**
	 * Get the WP Post ID of the post which triggered the earning of the certificate
	 *
	 * This would be a lesson, course, section, track, etc...
	 *
	 * @since 3.8.0
	 * @since 4.5.0 Force return to an integer.
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return int
	 */
	public function get_related_post_id() {
		$meta = $this->get_user_postmeta();
		return isset( $meta->post_id ) ? absint( $meta->post_id ) : $this->get( 'related' );
	}

	/**
	 * Retrieve the user id of the user who earned the certificate
	 *
	 * @since 3.8.0
	 * @since 3.9.0 Unknown.
	 * @since 4.5.0 Force return to an integer.
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return int
	 */
	public function get_user_id() {
		$meta = $this->get_user_postmeta();
		return isset( $meta->user_id ) ? absint( $meta->user_id ) : $this->get( 'author' );
	}

	/**
	 * Retrieve user postmeta data for the certificate
	 *
	 * @since 3.8.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return obj
	 */
	public function get_user_postmeta() {
		global $wpdb;
		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT user_id, post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_value = %d AND meta_key = %s",
				$this->get( 'id' ),
				$this->get_user_post_meta_key()
			)
		);
	}

	/**
	 * Retrieve the user postmeta key recorded when the engagement is earned.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_user_post_meta_key() {
		return sprintf( '_%s_earned', $this->model_post_type );
	}

	/**
	 * Determines if the certificate has been awarded.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_awarded() {

		if ( 'publish' !== $this->get( 'status' ) ) {
			return false;
		}

		return $this->get( 'awarded' ) ? true : false;

	}

}
