<?php
/**
 * Base Achievement Class
 *
 * Handles generating Achievement
 *
 * @package LifterLMS/Classes/Achievements
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base Achievement Class
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 * @deprecated 6.0.0 Class `LLMS_Achievement` is deprecated with no direct replacement.
 */
class LLMS_Achievement {

	/**
	 * @var int
	 * @since 1.0.0
	 */
	public $achievement_template_id;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $achievement_title;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $content;

	/**
	 * is the achievement enabled
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	public $enabled;

	/**
	 * @var array
	 * @since 1.0.0
	 */
	public $find = array();

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $id;

	/**
	 * image id
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $image;

	/**
	 * @var int
	 * @since 1.0.0
	 */
	public $lesson_id;

	/**
	 * @var WP_User
	 * @since 1.0.0
	 */
	public $object;

	/**
	 * @var array
	 * @since 1.0.0
	 */
	public $replace = array();

	/**
	 * post title
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $title;

	/**
	 * @var int
	 * @since 1.0.0
	 */
	public $userid;

	/**
	 * Alert when deprecated methods are used.
	 *
	 * This class as well as core classes extending it have been deprecated. All public and protected methods
	 * have been changed to private and will be made accessible through this magic method which also emits a
	 * deprecation warning.
	 *
	 * This public method has been intentionally marked as private to denote it's temporary lifespan. It will be
	 * removed alongside this class in the next major release.
	 *
	 * @since 6.0.0
	 *
	 * @access private
	 *
	 * @param string $name Name of the method being called.
	 * @param array  $args Arguments provided to the method.
	 * @return void
	 */
	public function __call( $name, $args ) {
		_deprecated_function( __CLASS__ . '::' . esc_html( $name ), '6.0.0' );
		if ( method_exists( $this, $name ) ) {
			$this->$name( ...$args );
		}
	}

	/**
	 * Constructor.
	 *
	 * @since Unknown.
	 * @deprecated 6.0.0 `LLMS_Achievement::__construct()` is deprecated with no replacement.
	 */
	public function __construct() {

		// Settings TODO Refactor: theses can come from the achievement post now.
		$this->enabled = get_option( 'enabled' );

		$this->find    = array( '{blogname}', '{site_title}' );
		$this->replace = array( $this->get_blogname(), $this->get_blogname() );
	}

	/**
	 * Checks if achievement is enabled
	 *
	 * @since Unknown
	 * @since 3.24.0 Unknown.
	 * @deprecated 6.0.0 `LLMS_Achievement::is_enabled()` is deprecated with no replacement.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$enabled = 'yes' == $this->enabled ? true : false;
		return true;
	}

	/**
	 * Get Blog name
	 *
	 * Used by achievement merge fields.
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Achievement::get_blogname()` is deprecated with no replacement.
	 *
	 * @return string
	 */
	private function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Format String
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Achievement::format_string()` is deprecated with no replacement.
	 *
	 * @param string $string Un-formatted string.
	 * @return string Formatted string.
	 */
	private function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}

	/**
	 * Queries Achievement title postmeta
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Achievement::get_title()` is deprecated with no replacement.
	 *
	 * @return string
	 */
	private function get_title() {
		return apply_filters( '_llms_achievement_title' . $this->id, $this->title, $this->object );
	}

	/**
	 * Get the content of the Achievement
	 *
	 * @since   1.0.0
	 * @version 1.4.1
	 * @deprecated 6.0.0 `LLMS_Achievement::get_content()` is deprecated with no replacement.
	 *
	 * @return string Data needed to generate achievement.
	 */
	private function get_content() {
		$achievement_content = $this->content;
		return $achievement_content;
	}

	/**
	 * Generate HTML output of achievement.
	 *
	 * Converts merge fields to raw data sources and wraps content in HTML
	 * then saves new achievement post and updates user_postmeta table.
	 *
	 * @since 1.0.0
	 * @deprecated 6.0.0 `LLMS_Achievement::get_content_html()` is deprecated with no replacement.
	 *
	 * @return void
	 */
	private function get_content_html() {}

	/**
	 * Create the achievement
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown
	 * @deprecated 6.0.0 `LLMS_Achievement::create()` is deprecated with no replacement.
	 *
	 * @param string $content Achievement body content.
	 * @return void
	 */
	private function create( $content ) {
		global $wpdb;

		$new_user_achievement = apply_filters(
			'lifterlms_new_achievement',
			array(
				'post_type'    => 'llms_my_achievement',
				'post_title'   => $this->title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => 1,
			)
		);

		$new_user_achievement_id = wp_insert_post( $new_user_achievement, true );

		update_post_meta( $new_user_achievement_id, '_llms_achievement_title', $this->achievement_title );
		update_post_meta( $new_user_achievement_id, '_llms_achievement_image', $this->image );
		update_post_meta( $new_user_achievement_id, '_llms_achievement_content', $this->content );
		update_post_meta( $new_user_achievement_id, '_llms_achievement_template', $this->achievement_template_id );

		$user_metadatas = array(
			'_achievement_earned' => $new_user_achievement_id,
		);

		foreach ( $user_metadatas as $key => $value ) {
			$update_user_postmeta = $wpdb->insert(
				$wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id'      => $this->userid,
					'post_id'      => $this->lesson_id,
					'meta_key'     => $key,
					'meta_value'   => $value,
					'updated_date' => current_time( 'mysql' ),
				)
			);
		}

		// This hook is documented in includes/class-llms-engagement-handler.php.
		do_action( 'llms_user_earned_achievement', $this->userid, $new_user_achievement_id, $this->lesson_id );
	}
}
