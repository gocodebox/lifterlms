<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Base Certificate Class
*
* Handles generating certificates
*/
class LLMS_Certificate {

	/**
	 * Certificate Enabled
	 * @var bool
	 */
	var $enabled;

	/**
	 * Constructor
	 */
	function __construct() {

			// Settings TODO Refoactor: theses can come from the email post now
			$this->email_type     	= 'html';
			//$this->enabled   		= get_option( 'enabled' );

			$this->find = array( '{blogname}', '{site_title}' );
			$this->replace = array( $this->get_blogname(), $this->get_blogname() );
	}

	/**
	 * Is Enabled
	 * @return boolean [certificate enabled]
	 */
	function is_enabled() {
		//$enabled = $this->enabled == "yes" ? true : false;
		return true;
	}

	/**
	 * Get Blog Name
	 * @return string [blog name]
	 */
	function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Format String
	 * @param  string $string [Find and replace merge fields]
	 * @return string [formatted string]
	 */
	function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}

	/**
	 * Get Blog Title
	 * @return string [Blog title]
	 */
	function get_title() {
		return apply_filters( '_llms_certificate_title' . $this->id, $this->title, $this->object );
	}

	/**
	 * Get Content
	 * @return string [Post Content]
	 */
	function get_content() {

		$this->sending = true;

		$email_content = $this->get_content_html();

		return $email_content;
	}

	/**
	 * Get Content HTML
	 * @return string [html formatted string]
	 */
	function get_content_html() {}

	/**
	 * Create Certificate
	 * @param    string $content [html formatted post content]
	 * @return   void
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function create( $content ) {
		global $wpdb;

		$new_user_certificate = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'llms_my_certificate',
			'post_title'    => $this->title,
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_author'   => 1,
		) );

		$new_user_certificate_id = wp_insert_post( $new_user_certificate, true );

		update_post_meta( $new_user_certificate_id,'_llms_certificate_title', $this->certificate_title );
		update_post_meta( $new_user_certificate_id,'_llms_certificate_image', $this->image );
		update_post_meta( $new_user_certificate_id,'_llms_certificate_template', $this->certificate_template_id );

		$user_metadatas = array(
			'_certificate_earned' => $new_user_certificate_id,
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
		do_action( 'llms_user_earned_certificate', $this->userid, $new_user_certificate_id, $this->lesson_id );

	}

}

