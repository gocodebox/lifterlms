<?php
/**
 * LifterLMS User Achievement
 * @since    3.8.0
 * @version  3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_User_Achievement extends LLMS_Post_Model {

	protected $db_post_type = 'llms_my_achievement';
	protected $model_post_type = 'achievement';

	protected $properties = array(
		// 'achievement_title' => 'string', // use get( 'title' )
		'achievement_image' => 'absint',
		// 'achievement_content' => 'html', // use get( 'content' )
		'achievement_template' => 'absint',
	);

	/**
	 * Get the WP Post ID of the post which triggered the earning of the achievement
	 * This would be a lesson, course, section, track, etc...
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
	 * @return   obj
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_user_postmeta() {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT user_id, post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_value = %d AND meta_key = '_achievement_earned'",
			$this->get( 'id' )
		) );
	}

}
