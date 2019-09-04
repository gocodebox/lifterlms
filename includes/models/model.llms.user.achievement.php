<?php
/**
 * LifterLMS User Achievement
 *
 * @package  LifterLMS/Models
 * @since    3.8.0
 * @version  3.18.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_User_Achievement model.
 */
class LLMS_User_Achievement extends LLMS_Post_Model {

	protected $db_post_type    = 'llms_my_achievement';
	protected $model_post_type = 'achievement';

	protected $properties = array(
		// 'achievement_title' => 'string', // use get( 'title' )
		'achievement_image'    => 'absint',
		// 'achievement_content' => 'html', // use get( 'content' )
		'achievement_template' => 'absint',
	);

	/**
	 * Delete the certificate
	 *
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function delete() {

		do_action( 'llms_before_delete_achievement', $this );

		global $wpdb;
		$id = $this->get( 'id' );
		$wpdb->delete(
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'user_id'    => $this->get_user_id(),
				'meta_key'   => '_achievement_earned',
				'meta_value' => $id,
			),
			array( '%d', '%s', '%d' )
		);
		wp_delete_post( $id, true );

		do_action( 'llms_delete_achievement', $this );

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
	 * Retrieve the HTML <img> for the achievement
	 *
	 * @param    array $size  dimensions of the image to return (width x height)
	 * @return   string
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function get_image_html( $size = array() ) {

		return apply_filters(
			'llms_achievement_get_image_html',
			sprintf( '<img alt="%1$s" class="llms-achievement-img" src="%2$s">', esc_attr( $this->get( 'title' ) ), $this->get_image( $size ) ),
			$this
		);

	}

	/**
	 * Retrieve the image source for the achievement
	 *
	 * @param    array $size  dimensions of the image to return (width x height)
	 * @return   string
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function get_image( $size = array(), $key = 'achievement_image' ) {

		if ( ! $size ) {
			$size = apply_filters( 'llms_achievement_image_default_size', array( 300, 300 ) );
		}

		if ( ! $this->get( 'achievement_image' ) ) {
			$src = LLMS()->plugin_url() . '/assets/images/optional_achievement.png';
		} else {
			$src = parent::get_image( $size, $key );
		}

		return apply_filters( 'llms_achievement_get_image', $src, $this );

	}

	/**
	 * Get the WP Post ID of the post which triggered the earning of the achievement
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
	 * Retrieve the user id of the user who earned the achievement
	 *
	 * @return   int
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_user_id() {
		$meta = $this->get_user_postmeta();
		return $meta->user_id;
	}

	/**
	 * Retrieve user postmeta data for the achievement
	 *
	 * @return   obj
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_user_postmeta() {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT user_id, post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_value = %d AND meta_key = '_achievement_earned'",
				$this->get( 'id' )
			)
		);
	}

}
