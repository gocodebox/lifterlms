<?php
/**
 * Email Base Class
 *
 * @package  LifterLMS/Email
 *
 * @since 1.0.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Email Base Class
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Email {

	/**
	 * @var array
	 * @since 3.15.0
	 */
	private $attachments = array();

	/**
	 * @var string
	 * @since 3.8.0
	 */
	protected $body = '';

	/**
	 * @var string
	 * @since 3.8.0
	 */
	protected $content_type = 'text/html';

	/**
	 * @var WP_Post
	 * @since 3.26.1
	 */
	public $email_post;

	/**
	 * @var array
	 * @since 1.0.0
	 */
	private $find = array();

	/**
	 * @var array
	 * @since 3.8.0
	 */
	private $headers = array();

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $heading = '';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $id = 'generic';

	/**
	 * @var array
	 * @since 1.0.0
	 */
	private $recipient = array();

	/**
	 * @var array
	 * @since 1.0.0
	 */
	private $replace = array();

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $subject = '';

	/**
	 * @var string
	 * @since 3.8.0
	 */
	protected $template_html = 'emails/template.php';

	/**
	 * Initializer
	 * Children can configure the email in this function called by the __construct() function
	 *
	 * @param    array $args  optional arguments passed in from the constructor
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function init( $args ) {}

	/**
	 * Constructor
	 * Sets up data needed to generate email content
	 *
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function __construct( $args = array() ) {

		$this->add_header( 'Content-Type', $this->get_content_type() );

		$this->add_merge_data(
			array(
				'{blogname}'     => get_bloginfo( 'name', 'display' ),
				'{site_title}'   => get_bloginfo( 'name', 'display' ),
				'{divider}'      => LLMS()->mailer()->get_divider_html(),
				'{button_style}' => LLMS()->mailer()->get_button_style(),
			)
		);

		$this->init( $args );

	}

	/**
	 * Add an attachment to the email
	 *
	 * @param    string $attachment  full system path to a file to attach
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function add_attachment( $attachment ) {

		array_push( $this->attachments, $attachment );

	}

	/**
	 * Add a single header to the email headers array
	 *
	 * @param    string $key   header key eg: 'Cc'
	 * @param    string $val   header value eg: 'noreply@website.tld'
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function add_header( $key, $val ) {

		array_push( $this->headers, sprintf( '%1$s: %2$s', $key, $val ) );

	}

	/**
	 * Add merge data that will be used in the email
	 *
	 * @param    array $data    associative array where
	 *                             $key = merge field
	 *                             $val = merge value
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function add_merge_data( $data = array() ) {

		foreach ( $data as $find => $replace ) {

			array_push( $this->find, $find );
			array_push( $this->replace, $replace );

		}

	}

	/**
	 * Add a single recipient for sending to, cc, or bcc
	 *
	 * @param    int|string $address  if string, must be a valid email address
	 *                                if int, must be the WP User ID of a user
	 * @param    string     $type     recipient type [to,cc,bcc]
	 * @param    string     $name     recipient name (optional)
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.10.1
	 */
	public function add_recipient( $address, $type = 'to', $name = '' ) {

		// if an ID was supplied, get the information from the student object
		if ( is_numeric( $address ) ) {
			$student = llms_get_student( $address );
			if ( ! $student ) {
				return false;
			}
			$address = $student->get( 'user_email' );
			$name    = $student->get_name();
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
	 * Add multiple recipients
	 *
	 * @param    array $recipients  array of recipient information
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function add_recipients( $recipients = array() ) {

		foreach ( $recipients as $data ) {

			$data = wp_parse_args(
				$data,
				array(
					'address' => '',
					'type'    => 'to',
					'name'    => '',
				)
			);

			if ( $data['address'] ) {
				$this->add_recipient( $data['address'], $data['type'], $data['name'] );
			}
		}

	}

	/**
	 *  Format string method
	 *  Finds and replaces merge fields with appropriate data
	 *
	 * @param    string $string  string to be formatted
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}

	/**
	 * Get attachments
	 *
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_attachments() {
		return apply_filters( 'llms_email_get_attachments', $this->attachments, $this );
	}

	/**
	 * Get the body content of the email
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_body() {
		return apply_filters( 'llms_email_body', $this->format_string( $this->body ), $this );
	}

	/**
	 * Get email content
	 *
	 * @return string
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function get_content() {

		$content = apply_filters( 'llms_email_content_get_content', $this->get_content_html(), $this );
		return wordwrap( $content, 70 );

	}

	/**
	 * Get the HTML email content
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.26.1
	 */
	public function get_content_html() {

		global $post;
		$temp = null;

		// Override the $post global with the email post content (if it exists).
		// This fixes Elementor / WC conflict outlined at https://github.com/gocodebox/lifterlms/issues/730.
		if ( isset( $this->email_post ) ) {
			$temp = $post;
			$post = $this->email_post;
		}

		ob_start();
		llms_get_template(
			$this->template_html,
			array(
				'email_heading' => $this->get_heading(),
				'email_message' => $this->get_body(),
			)
		);

		$html = apply_filters( 'llms_email_content_get_content_html', ob_get_clean(), $this );

		// Restore the default $post global.
		if ( $temp ) {
			$post = $temp;
		}

		return $html;

	}

	/**
	 * Get the content type
	 *
	 * @return string
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function get_content_type() {
		return apply_filters( 'llms_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get from email option data
	 *
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_from_address() {
		return sanitize_email( get_option( 'lifterlms_email_from_address' ) );
	}

	/**
	 * Get from name option data
	 *
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_from_name() {
		return wp_specialchars_decode( esc_html( get_option( 'lifterlms_email_from_name' ) ), ENT_QUOTES );
	}

	/**
	 * Get email headers
	 *
	 * @return   string|array
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function get_headers() {
		return apply_filters( 'lifterlms_email_headers', $this->headers, $this->id );
	}

	/**
	 * Get the text of the email "heading"
	 *
	 * @return string
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function get_heading() {
		return apply_filters( 'lifterlms_email_heading', $this->format_string( $this->heading ), $this );
	}

	/**
	 * Get recipient email address
	 *
	 * @return   string|array
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function get_recipient() {
		return apply_filters( 'lifterlms_email_recipient', $this->recipient, $this );
	}

	/**
	 * Get email subject
	 *
	 * @return   string
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function get_subject() {
		return apply_filters( 'lifterlms_email_subject', $this->format_string( $this->subject ), $this );
	}

	/**
	 * Set the body for the email
	 *
	 * @param    string $body   text or html body content for the email
	 * @return   $this
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_body( $body = '' ) {
		$this->body = $body;
		return $this;
	}

	/**
	 * set the content_type for the email
	 *
	 * @param    string $content_type   content type (for the header)
	 * @return   $this
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_content_type( $content_type = 'text/html' ) {
		$this->content_type = $content_type;
		return $this;
	}

	/**
	 * set the heading for the email
	 *
	 * @param    string $heading    text string to use for the email heading
	 * @return   $this
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_heading( $heading = '' ) {
		$this->heading = $heading;
		return $this;
	}

	/**
	 * Set the ID of the email
	 *
	 * @param    string $id   id string
	 * @return   $this
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_id( $id = '' ) {
		$this->id = $id;
		return $this;
	}

	/**
	 * set the subject for the email
	 *
	 * @param    string $content_type text string to use for the email subject.
	 * @return   $this
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	public function set_subject( $subject = '' ) {
		$this->subject = html_entity_decode( $subject, ENT_QUOTES );
		return $this;
	}

	/**
	 * Send email
	 *
	 * @return bool
	 * @since    1.0.0
	 * @version  3.15.0
	 */
	public function send() {

		do_action( 'lifterlms_email_' . $this->id . '_before_send', $this );

		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$return = wp_mail( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		do_action( 'lifterlms_email_' . $this->id . '_after_send', $this, $return );

		return $return;

	}

}
