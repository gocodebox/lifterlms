<?php
defined( 'ABSPATH' ) || exit;

/**
 * Base Achievement Class
 * Handles generating Achievement
 * @since    ??
 * @version  3.24.0
 */
class LLMS_Achievement {

	// is the achievement enabled
	var $enabled;

	var $heading;

	function __construct() {

		// Settings TODO Refoactor: theses can come from the achievement post now
		$this->enabled   		= get_option( 'enabled' );

		$this->find = array( '{blogname}', '{site_title}' );
		$this->replace = array( $this->get_blogname(), $this->get_blogname() );
	}

	/**
	 * Checks if achievement is enabled
	 * @return   bool
	 * @since    ??
	 * @version  3.24.0
	 * @todo     returning true always. Need to build setting to disable / enable
	 */
	function is_enabled() {
		$enabled = 'yes' == $this->enabled ? true : false;
		return true;
	}

	/**
	 * Get Blog name
	 * Used by achievement merge fields
	 *
	 * @return string [Blogname from options]
	 */
	function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Format String
	 * @param  string $string [un-formatted string]
	 * @return string         [formatted string]
	 */
	function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}


	/**
	 * Queries Achievement title postmeta
	 * @return string [display title of the achievement]
	 */
	function get_title() {
		return apply_filters( '_llms_achievement_title' . $this->id, $this->title, $this->object );
	}

	/**
	 * Get the content of the Achievement
	 *
	 * @return array $achievement_content [data needed to generate achievement]
	 */
	function get_content() {

		$achievement_content = $this->content;

		return $achievement_content;
	}

	/**
	 * Generate HTML output of achievement
	 * Converts merge fields to raw data sources and wraps content in HTML
	 * then saves new achivement post and updates user_postmeta table.
	 * @return   void
	 * @since    1.0.0
	 */
	function get_content_html() {}

	/**
	 * Create the achivement
	 * @param    string    $content  achievement body content
	 * @return   void
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function create( $content ) {
		global $wpdb;

		$new_user_achievement = apply_filters( 'lifterlms_new_achievement', array(
			'post_type' 	=> 'llms_my_achievement',
			'post_title'    => $this->title,
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_author'   => 1,
		) );

		$new_user_achievement_id = wp_insert_post( $new_user_achievement, true );

		update_post_meta( $new_user_achievement_id,'_llms_achievement_title', $this->achievement_title );
		update_post_meta( $new_user_achievement_id,'_llms_achievement_image', $this->image );
		update_post_meta( $new_user_achievement_id,'_llms_achievement_content', $this->content );
		update_post_meta( $new_user_achievement_id,'_llms_achievement_template', $this->achievement_template_id );

		$user_metadatas = array(
			'_achievement_earned' => $new_user_achievement_id,
		);

		foreach ( $user_metadatas as $key => $value ) {
			$update_user_postmeta = $wpdb->insert( $wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id' 			=> $this->userid,
					'post_id' 			=> $this->lesson_id,
					'meta_key'			=> $key,
					'meta_value'		=> $value,
					'updated_date'		=> current_time( 'mysql' ),
				)
			);
		}

		/**
		 * Allow 3rd parties to hook into the generation of an achievement
		 * Notifications uses this
		 * note 3rd param $this->lesson_id is actually the related post id (but misnamed)
		 */
		do_action( 'llms_user_earned_achievement', $this->userid, $new_user_achievement_id, $this->lesson_id );

	}

}

