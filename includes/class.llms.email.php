<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Base Email Class
*
* Handles generating and sending the email
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Email {

	/**
	 * Returns if email template is enabled.
	 * @var bool
	 */
	var $enabled = true;

	/**
	 * Email heading
	 * @var string
	 */
	var $heading;

	/**
	 * Constructor
	 * Sets up data needed to generate email content
	 */
	function __construct() {

		// Settings TODO Refoactor: theses can come from the email post now
		$this->email_type     	= 'html';
		$this->find 			= array( '{blogname}', '{site_title}' );
		$this->replace 			= array( $this->get_blogname(), $this->get_blogname() );

	}

	/**
	 * Checks if email is enabled.
	 * REFACTOR: currently always returns true.
	 *
	 * @return boolean [Is email enabled]
	 */
	function is_enabled() {
		return $this->enabled;
	}

	/**
	 * Get the blogname option
	 * @return string [blog name option data]
	 */
	function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Get the content type
	 * REFACTOR: currently always returns text/html.
	 *
	 * @return string [always returns text/html]
	 */
	function get_content_type() {
		return 'text/html';
	}

	/**
	 * Get from name option data
	 * @return string [From name option in settings->Email]
	 */
	function get_from_name() {
		return wp_specialchars_decode( esc_html( get_option( 'lifterlms_email_from_name' ) ), ENT_QUOTES );
	}

	/**
	 * Get from email option data
	 * @return string [From email option in settings->email]
	 */
	function get_from_address() {
		return sanitize_email( get_option( 'lifterlms_email_from_address' ) );
	}

	/**
	 * Get recipient email address
	 * @return string [Email of the user to recieve the email]
	 */
	function get_recipient() {
		return apply_filters( 'lifterlms_email_recipient_' . $this->id, $this->recipient, $this->object );
	}

	/**
	 * Get email subject
	 * @return string [Returns the email subject fromt the email post]
	 */
	function get_subject() {
		return apply_filters( 'lifterlms_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
	}

	/**
	 * Get email headers
	 * @return string [Returns email headers as formatted string]
	 */
	function get_headers() {
		return apply_filters( 'lifterlms_email_headers', 'Content-Type: ' . $this->get_content_type() . "\r\n", $this->id, $this->object );
	}

	/**
	 *  Format string method
	 *  Finds and replaces merge fields with appropriate data
	 *
	 * @param  string $string [string to be formatted]
	 * @return string         [Formatted string with raw data in replace of merge fields]
	 */
	function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}

	/**
	 * Get heading post option
	 * @return string [Returns heading textbox option]
	 */
	function get_heading() {
		return apply_filters( 'lifterlms_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
	}

	/**
	 * Get email content
	 * Cals get_html_content to format then returns it wordwrapped
	 *
	 * @return string [html formatted string. This is the final output for the email content before sending.]
	 */
	function get_content() {

		$this->sending = true;

		$email_content = $this->get_content_html();

		return wordwrap( $email_content, 70 );
	}

	/**
	 * Format the content
	 * Wrap content in appropriate html for sending email.
	 * Child class overrides this method.
	 *
	 * @return string $return [HTML formatted string]
	 */
	function get_content_html() {}

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
	function send( $to, $subject, $message, $headers ) {

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
