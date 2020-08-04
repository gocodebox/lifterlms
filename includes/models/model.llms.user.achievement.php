<?php
/**
 * LifterLMS User Achievement
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_User_Achievement model.
 *
 * @since 3.8.0
 * @since 3.18.0 Unknown.
 */
class LLMS_User_Achievement extends LLMS_Post_Model {

	/**
	 * Database post type.
	 *
	 * @var string
	 */
	protected $db_post_type    = 'llms_my_achievement';

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	protected $model_post_type = 'achievement';

	/**
	 * Object meta properties.
	 *
	 * @var array
	 */
	protected $properties = array(
		'achievement_image'    => 'absint',
		'achievement_template' => 'absint',
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
		 * Triggered prior to the deletion of a user's achievement.
		 *
		 * @since 3.18.0
		 *
		 * @param LLMS_User_Achievement $this Instance of the user achievement class.
		 */
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

		/**
		 * Triggered after the deletion of a user's achievement.
		 *
		 * @since 3.18.0
		 *
		 * @param LLMS_User_Achievement $this Instance of the user achievement class.
		 */
		do_action( 'llms_delete_achievement', $this );

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
	 * Retrieve the HTML <img> for the achievement
	 *
	 * @since 3.14.0
	 *
	 * @param array $size Dimensions of the image to return (width x height).
	 * @return string
	 */
	public function get_image_html( $size = array() ) {

		/**
		 * Filter the HTML of an achievement image.
		 *
		 * @since 3.14.0
		 *
		 * @param string                $html HTML `<img>` element for the achievement.
		 * @param LLMS_User_Achievement $this Instance of the user achievement class.
		 */
		return apply_filters(
			'llms_achievement_get_image_html',
			sprintf( '<img alt="%1$s" class="llms-achievement-img" src="%2$s">', esc_attr( $this->get( 'title' ) ), $this->get_image( $size ) ),
			$this
		);

	}

	/**
	 * Retrieve the image source for the achievement
	 *
	 * @since 3.14.0
	 * @since [version] Use `LLMS_Achievements::get_default_image()` in favor of hard-coded image path.
	 *
	 * @param array $size Dimensions of the image to return (width x height).
	 * @return string
	 */
	public function get_image( $size = array(), $key = 'achievement_image' ) {

		if ( ! $size ) {
			$size = apply_filters( 'llms_achievement_image_default_size', array( 300, 300 ) );
		}

		if ( ! $this->get( 'achievement_image' ) ) {
			$src = llms()->achievements()->get_default_image();
		} else {
			$src = parent::get_image( $size, $key );
		}

		/**
		 * Filter the source URL to the achievement image
		 *
		 * @since Unknown
		 *
		 * @param string                $src  URL to the achievement image.
		 * @param LLMS_User_Achievement $this Instance of the user achievement class.
		 */
		return apply_filters( 'llms_achievement_get_image', $src, $this );

	}

	/**
	 * Get the WP Post ID of the post which triggered the earning of the achievement
	 *
	 * This would be a lesson, course, section, track, etc...
	 *
	 * @since 3.8.0
	 * @return int
	 */
	public function get_related_post_id() {
		$meta = $this->get_user_postmeta();
		return $meta->post_id;
	}

	/**
	 * Retrieve the user id of the user who earned the achievement
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	public function get_user_id() {
		$meta = $this->get_user_postmeta();
		return $meta->user_id;
	}

	/**
	 * Retrieve user postmeta data for the achievement
	 *
	 * @since 3.8.0
	 *
	 * @return obj
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
