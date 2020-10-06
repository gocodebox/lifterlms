<?php
/**
 * LifterLMS User Certificate
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version 4.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_User_Certificate model class
 *
 * @since 3.8.0
 */
class LLMS_User_Certificate extends LLMS_Post_Model {

	/**
	 * Database (WP) post type name
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_my_certificate';

	/**
	 * Post type model name
	 *
	 * @var string
	 */
	protected $model_post_type = 'certificate';

	/**
	 * Object properties
	 *
	 * @var array
	 */
	protected $properties = array(
		'certificate_title'    => 'string',
		'certificate_image'    => 'absint',
		'certificate_template' => 'absint',
		'allow_sharing'        => 'yesno',
	);

	/**
	 * Delete the certificate
	 *
	 * @since 3.18.0
	 *
	 * @return void
	 */
	public function delete() {

		/**
		 * Action fired immediately prior to the deletion of a user's earned certificate
		 *
		 * @since 3.18.0
		 *
		 * @param LLMS_User_Certificate $certificate Certificate class object.
		 */
		do_action( 'llms_before_delete_certificate', $this );

		global $wpdb;
		$id = $this->get( 'id' );
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'user_id'    => $this->get_user_id(),
				'meta_key'   => '_certificate_earned', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			),
			array( '%d', '%s', '%d' )
		);
		wp_delete_post( $id, true );

		/**
		 * Action fired immediately after the deletion of a user's earned certificate
		 *
		 * @since 3.18.0
		 *
		 * @param LLMS_User_Certificate $certificate Certificate class object.
		 */
		do_action( 'llms_delete_certificate', $this );

	}

	/**
	 * Retrieve the date the achievement was earned (created)
	 *
	 * @since 3.14.0
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
	 *
	 * @return int
	 */
	public function get_related_post_id() {
		$meta = $this->get_user_postmeta();
		return absint( $meta->post_id );
	}

	/**
	 * Retrieve the user id of the user who earned the certificate
	 *
	 * @since 3.8.0
	 * @since 3.9.0 Unknown.
	 * @since 4.5.0 Force return to an integer.
	 *
	 * @return int
	 */
	public function get_user_id() {
		$meta = $this->get_user_postmeta();
		return isset( $meta->user_id ) ? absint( $meta->user_id ) : null;
	}

	/**
	 * Retrieve user postmeta data for the certificate
	 *
	 * @since 3.8.0
	 *
	 * @return obj
	 */
	public function get_user_postmeta() {
		global $wpdb;
		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT user_id, post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_value = %d AND meta_key = '_certificate_earned'",
				$this->get( 'id' )
			)
		);
	}

	/**
	 * Can user manage and make some actions on the certificate
	 *
	 * @since 4.5.0
	 *
	 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
	 * @return bool
	 */
	public function can_user_manage( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();
		$result  = ( $user_id === $this->get_user_id() || llms_can_user_bypass_restrictions( $user_id ) );

		/**
		 * Filter whether or not a user can manage a given certificate.
		 *
		 * @since 4.5.0
		 *
		 * @param boolean               $result      Whether or not the user can manage certificate.
		 * @param int                   $user_id     WP_User ID of the user viewing the certificate.
		 * @param LLMS_User_Certificate $certificate Certificate class instance.
		 */
		return apply_filters( 'llms_certificate_can_user_manage', $result, $user_id, $this );

	}

	/**
	 * Can user view the certificate
	 *
	 * @since 4.5.0
	 *
	 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
	 * @return bool
	 */
	public function can_user_view( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();
		$result  = $this->can_user_manage( $user_id ) || $this->is_sharing_enabled();

		/**
		 * Filter whether or not a user can view a user's certificate.
		 *
		 * @since 4.5.0
		 *
		 * @param boolean               $result      Whether or not the user can view the certificate.
		 * @param int                   $user_id     WP_User ID of the user viewing the certificate.
		 * @param LLMS_User_Certificate $certificate Certificate class instance.
		 */
		return apply_filters( 'llms_certificate_can_user_view', $result, $user_id, $this );

	}

	/**
	 * Is sharing enabled
	 *
	 * @since 4.5.0
	 *
	 * @return bool
	 */
	public function is_sharing_enabled() {

		/**
		 * Filter whether or not sharing is enabled for a certificate.
		 *
		 * @since 4.5.0
		 *
		 * @param boolean               $enabled     Whether or not sharing is enabled.
		 * @param LLMS_User_Certificate $certificate Certificate class instance.
		 */
		return apply_filters( 'llms_certificate_is_sharing_enabled', llms_parse_bool( $this->get( 'allow_sharing' ) ), $this );

	}

}
