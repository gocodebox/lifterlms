<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Achievement Class
*
* Handles generating Achievement
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Achievement {

	// is the email enabled
	var $enabled;

	var $heading;

	function __construct() {

			// Default template base if not declared in child constructor
			if ( is_null( $this->template_base ) ) {
				$this->template_base = LLMS()->plugin_path() . '/templates/';
			}

			// Settings TODO Refoactor: theses can come from the email post now
			$this->email_type     	= 'html';
			$this->enabled   		= get_option( 'enabled' );

			$this->find = array( '{blogname}', '{site_title}' );
			$this->replace = array( $this->get_blogname(), $this->get_blogname() );
	}

	/**
	 * Checks if achievement is enabled
	 * REFACTOR: returning true always. Need to build setting to disable / enable 
	 * @return bool [is achievement enabled]
	 */
	function is_enabled() {
		$enabled = $this->enabled == "yes" ? true : false;
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

//REFACTOR: REMOVE THESE METHODS AFTER TESTING
	// function get_content_type() {
	// 	return 'text/html';
	// }

	// function get_from_name() {
	// 	return wp_specialchars_decode( esc_html( get_option( 'lifterlms_email_from_name' ) ), ENT_QUOTES );
	// }

	// function get_from_address() {
	// 	return sanitize_email( get_option( 'lifterlms_email_from_address' ) );
	// }

	// function get_recipient() {
	// 	return apply_filters( 'lifterlms_email_recipient_' . $this->id, $this->recipient, $this->object );
	// }

	// function get_subject() {
	// 	return apply_filters( 'lifterlms_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
	// }

	// function get_headers() {
	// 	return apply_filters( 'lifterlms_email_headers', "Content-Type: " . $this->get_content_type() . "\r\n", $this->id, $this->object );
	// }
	// function format_string( $string ) {
	// 	return str_replace( $this->find, $this->replace, $string );
	// }

	// function get_heading() {
	// 	return apply_filters( 'lifterlms_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
	// }


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

	$this->sending = true;

	$achievement_content = $this->get_content_html();

		return $achievement_content;
	}

	/**
	 * Generate HTML output of achievement
	 * Converts merge fields to raw data sources and wraps content in HTML
	 * then saves new achivement post and updates user_postmeta table. 
	 * 
	 * @return [type] [description]
	 */
	function get_content_html() {}

	public function create($content) {
		global $wpdb;

		$new_user_achievement = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'llms_my_achievement',
			'post_title'    => $this->title,
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_author'   => 1,
		) );

		$new_user_achievement_id = wp_insert_post( $new_user_achievement, true );

		update_post_meta( $new_user_achievement_id,'_llms_achievement_title', $this->achievement_title );
		update_post_meta( $new_user_achievement_id,'_llms_achievement_image', $this->image );

		$user_metadatas = array(
			'_achievement_earned' => $new_user_achievement_id,
		);

		foreach ($user_metadatas as $key => $value) {
			$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta', 
				array( 
					'user_id' 			=> $this->userid,
					'post_id' 			=> $this->lesson_id,
					'meta_key'			=> $key,
					'meta_value'		=> $value,
					'updated_date'		=> current_time('mysql'),
				)
			);
		}
		
	}

}

