<?php
/**
* Email Abstract
*
* @since    1.0.0
* @version  [version]
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Abstract_Email {

	protected $id = '';

	protected $enabled = true;
	protected $content_type = 'text/html';
	protected $headers = array();
	protected $heading = '';
	protected $recipient = array();
	protected $subject = '';

	protected $find = array();
	protected $replace = array();

	/**
	 * Get the html content of the email
	 * @return   string
	 * @since    1.0.0
	 * @version  [version]
	 */
	abstract public function get_content_html();

	/**
	 * Initializer
	 * Children can configure the email in this function called by the __construct() functino
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function init();

	/**
	 * Constructor
	 * Sets up data needed to generate email content
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function __construct() {

		$this->headers = array(
			'Content-Type: ' . $this->get_content_type(),
		);

		$this->find = array(
			'{blogname}',
			'{site_title}',
		);
		$this->replace = array(
			get_bloginfo( 'name', 'display' ),
			get_bloginfo( 'name', 'display' ),
		);

		$this->init();

	}

	protected function add_merge_data( $data = array() ) {

		foreach ( $data as $find => $replace ) {

			$this->find[ $find ];
			$this->replace[ $replace ];

		}

	}

	/**
	 * Get the blogname option
	 * @return   string
	 * @since    1.0.0
	 * @version  [version] - use core functions rather than get_option()
	 */
	protected function get_blogname() {
		llms_deprecated_function( 'LLMS_Email::get_blogname', '[version]', 'get_bloginfo( \'name\', \'display\' )' );
		return get_bloginfo( 'name', 'display' );
	}

	/**
	 * Get email content
	 * @return string
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function get_content() {

		$content = apply_filters( 'llms_email_content_get_content', $this->get_content_html(), $this );
		return wordwrap( $content, 70 );

	}

	/**
	 * Get the content type
	 * @return string
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function get_content_type() {
		return apply_filters( 'llms_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get from email option data
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_from_address() {
		return sanitize_email( get_option( 'lifterlms_email_from_address' ) );
	}

	/**
	 * Get from name option data
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_from_name() {
		return wp_specialchars_decode( esc_html( get_option( 'lifterlms_email_from_name' ) ), ENT_QUOTES );
	}

	/**
	 * Get email headers
	 * @return   string|array
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function get_headers() {
		return apply_filters( 'lifterlms_email_headers', $this->headers, $this->id );
	}

	/**
	 * Get the text of the email "heading"
	 * @return string
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function get_heading() {
		return apply_filters( 'lifterlms_email_heading', $this->format_string( $this->heading ), $this );
	}

	/**
	 * Get recipient email address
	 * @return   string|array
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function get_recipient() {
		return apply_filters( 'lifterlms_email_recipient', $this->recipient, $this );
	}

	/**
	 * Get email subject
	 * @return   string
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function get_subject() {
		return apply_filters( 'lifterlms_email_subject', $this->format_string( $this->subject ), $this );
	}

	/**
	 * Checks if email is enabled.
	 * @return   boolean
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function is_enabled() {
		return apply_filters( 'lifterlms_email_enabled', $this->enabled, $this );
	}
















	/**
	 *  Format string method
	 *  Finds and replaces merge fields with appropriate data
	 *
	 * @param  string $string [string to be formatted]
	 * @return string         [Formatted string with raw data in replace of merge fields]
	 */
	public function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}







	/**
	 * Send email
	 *
	 * @param  string $to      [to email address]
	 * @param  string $subject [the email subject]
	 * @param  string $message [the html formatted message]
	 * @param  string $headers [the email headers]
	 *
	 * @return bool $return [Whether or not the email was sent successfully]
	 */
	public function send( $to, $subject, $message, $headers ) {

		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$return = wp_mail( $to, $subject, $message, $headers );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		return $return;

	}

}
