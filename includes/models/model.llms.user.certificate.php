<?php
/**
 * LifterLMS User Certificate
 *
 * @package  LifterLMS/Models
 * @since    3.8.0
 * @version  3.18.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_User_Certificate model.
 */
class LLMS_User_Certificate extends LLMS_Post_Model {

	protected $db_post_type    = 'llms_my_certificate';
	protected $model_post_type = 'certificate';

	protected $properties = array(
		'certificate_title'    => 'string',
		'certificate_image'    => 'absint',
		// 'certificate_content' => 'html', // use get( 'content' )
		'certificate_template' => 'absint',
	);

	/**
	 * Delete the certificate
	 *
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function delete() {

		do_action( 'llms_before_delete_certificate', $this );

		global $wpdb;
		$id = $this->get( 'id' );
		$wpdb->delete(
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'user_id'    => $this->get_user_id(),
				'meta_key'   => '_certificate_earned',
				'meta_value' => $id,
			),
			array( '%d', '%s', '%d' )
		);
		wp_delete_post( $id, true );

		do_action( 'llms_delete_certificate', $this );

	}

	/**
	 * Retrieve the date the achievement was earned (created)
	 *
	 * @param    string $format  date format string
	 * @return   string
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function get_earned_date( $format = null ) {
		$format = $format ? $format : get_option( 'date_format' );
		return $this->get_date( 'date', $format );
	}

	/**
	 * Get the WP Post ID of the post which triggered the earning of the certificate
	 * This would be a lesson, course, section, track, etc...
	 *
	 * @return   int
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_related_post_id() {
		$meta = $this->get_user_postmeta();
		return $meta->post_id;
	}

	/**
	 * Retrieve the user id of the user who earned the certificate
	 *
	 * @return   int
	 * @since    3.8.0
	 * @version  3.9.0
	 */
	public function get_user_id() {
		$meta = $this->get_user_postmeta();
		return isset( $meta->user_id ) ? $meta->user_id : null;
	}

	/**
	 * Retrieve user postmeta data for the certificate
	 *
	 * @return   obj
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_user_postmeta() {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT user_id, post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_value = %d AND meta_key = '_certificate_earned'",
				$this->get( 'id' )
			)
		);
	}

}
