<?php
/**
 * Base Certificate Class
 *
 * Handles generating certificates.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base Certificate Class
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 4.0.0 Remove previously deprecated class property `$enabled`.
 * @deprecated 6.0.0 Class `LLMS_Certificate` is deprecated with no direct replacement.
 */
class LLMS_Certificate {

	/**
	 * @var int
	 * @since 1.0.0
	 */
	public $certificate_template_id;

	/**
	 * post title
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $certificate_title;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $content;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $email_type;

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
	 * @var bool
	 * @since 1.0.0
	 */
	public $sending;

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
	 * @deprecated 6.0.0 `LLMS_Certificate::__construct()` is deprecated with no replacement.
	 */
	public function __construct() {

		// Settings TODO Refactor: theses can come from the email post now.
		$this->email_type = 'html';

		$this->find    = array( '{blogname}', '{site_title}' );
		$this->replace = array( $this->get_blogname(), $this->get_blogname() );
	}

	/**
	 * Is Enabled
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Certificate::is_enabled()` is deprecated with no replacement.
	 *
	 * @return boolean
	 */
	private function is_enabled() {
		return true;
	}

	/**
	 * Get Blog Name
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Certificate::get_blogname()` is deprecated with no replacement.
	 *
	 * @return string [blog name]
	 */
	private function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Format String
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Certificate::format_string()` is deprecated with no replacement.
	 *
	 * @param  string $string [Find and replace merge fields]
	 * @return string [formatted string]
	 */
	private function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}

	/**
	 * Get Blog Title
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Certificate::get_title()` is deprecated with no replacement.
	 *
	 * @return string [Blog title]
	 */
	private function get_title() {
		return apply_filters( '_llms_certificate_title' . $this->id, $this->title, $this->object );
	}

	/**
	 * Get Content
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Certificate::get_content()` is deprecated with no replacement.
	 *
	 * @return string [Post Content]
	 */
	private function get_content() {

		$this->sending = true;

		$email_content = $this->get_content_html();

		return $email_content;
	}

	/**
	 * Get Content HTML
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Certificate::get_content_html()` is deprecated with no replacement.
	 *
	 * @return void
	 */
	private function get_content_html() {}

	/**
	 * Create Certificate
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown.
	 * @deprecated 6.0.0 `LLMS_Certificate::get_title()` is deprecated with no replacement.
	 *
	 * @param string $content HTML formatted post content.
	 * @return void
	 */
	private function create( $content ) {
		global $wpdb;

		$new_user_certificate = apply_filters(
			'lifterlms_new_page',
			array(
				'post_type'    => 'llms_my_certificate',
				'post_title'   => $this->title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => 1,
			)
		);

		$new_user_certificate_id = wp_insert_post( $new_user_certificate, true );

		update_post_meta( $new_user_certificate_id, '_llms_certificate_title', $this->certificate_title );
		update_post_meta( $new_user_certificate_id, '_llms_certificate_image', $this->image );
		update_post_meta( $new_user_certificate_id, '_llms_certificate_template', $this->certificate_template_id );

		$user_metadatas = array(
			'_certificate_earned' => $new_user_certificate_id,
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
		do_action( 'llms_user_earned_certificate', $this->userid, $new_user_certificate_id, $this->lesson_id );
	}
}
