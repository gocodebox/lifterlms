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

	protected $content_type = 'text/html';

	protected $body = '';
	protected $heading = '';
	protected $subject = '';

	private $headers = array();
	private $find = array();
	private $recipient = array();
	private $replace = array();

	protected $template_html = 'emails/template.php';

	/**
	 * Initializer
	 * Children can configure the email in this function called by the __construct() function
	 * @param    array     $args  optional arguments passed in from the constructor
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function init( $args );

	/**
	 * Constructor
	 * Sets up data needed to generate email content
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function __construct( $args = array() ) {

		$this->add_header( 'Content-Type', $this->get_content_type() );

		$this->add_merge_data( array(
			'{blogname}' => get_bloginfo( 'name', 'display' ),
			'{site_title}' => get_bloginfo( 'name', 'display' ),
		) );

		$this->init( $args );

	}


	public function add_header( $key, $val ) {

		array_push( $this->headers, sprintf( '%1$s: %2$s', $key, $val ) );

	}

	public function add_merge_data( $data = array() ) {

		foreach ( $data as $find => $replace ) {

			array_push( $this->find, $find );
			array_push( $this->replace, $replace );

		}

	}

	/**
	 * Add a single recipient for sending to, cc, or bcc
	 * @param    int|string  $address  if string, must be a valid email address
	 *                                 if int, must be the WP User ID of a user
	 * @param    string      $type     recipient type [to,cc,bcc]
	 * @param    string      $name     recipent name (optional)
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_recipient( $address, $type = 'to', $name = '' ) {

		// if an ID was supplied, get the information from the student object
		if ( is_numeric( $address ) ) {
			$student = new LLMS_Student( $address );
			$address = $student->get( 'user_email' );
			$name = $student->get_name();
		}

		// ensure address is a valid email
		if ( ! filter_var( $address, FILTER_VALIDATE_EMAIL ) ) {
			return false;
		}

		// if a name is supplied format the name & address
		if ( $name ) {
			$address = sprintf( '%1$s <%2$s>', $name, $address );
		}

		if ( 'to' === $type ) {

			array_push( $this->recipient, $address );
			return true;

		} elseif ( 'cc' === $type || 'bcc' === $type ) {

			$this->add_header( ucfirst( $type ), $address );
			return true;

		}

		return false;

	}

	/**
	 * Add multiple recipents
	 * @param    array      $recipients  array of recipient information
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_recipients( $recipients = array() ) {

		foreach ( $recipients as $data ) {

			$data = wp_parse_args( $data, array(
				'address' => '',
				'type' => 'to',
				'name' => '',
			) );

			if ( $data['address'] ) {
				$this->add_recipient( $data['address'], $data['type'], $data['name'] );
			}

		}

	}

	/**
	 *  Format string method
	 *  Finds and replaces merge fields with appropriate data
	 * @param    string  $string  string to be formatted
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}

	/**
	 * Get the body content of the email
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_body() {
		return apply_filters( 'llms_email_body', $this->format_string( $this->body ), $this );
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
	 * Get the HTML email content
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_content_html() {

		ob_start();
		llms_get_template( $this->template_html, array(
			'email_heading' => $this->get_heading(),
			'email_message' => $this->get_body(),
		) );
		return apply_filters( 'llms_email_content_get_content_html', ob_get_clean(), $this );

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
	 * Send email
	 * @return bool
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function send() {

		do_action( 'lifterlms_email_' . $this->id . '_before_send', $this );

		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$return = wp_mail( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers() );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		do_action( 'lifterlms_email_' . $this->id . '_after_send', $this, $return );

		return $return;

	}

}
