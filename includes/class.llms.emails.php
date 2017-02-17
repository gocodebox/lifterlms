<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Emails Class
*
* Manages finding the appropriate email
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Emails {

	/**
	 * Object of all emails
	 * @var object
	 */
	public $emails;

	/**
	 * Content of email
	 * @var string
	 */
	public $email_content;

	/**
	 * private from address
	 * @var string
	 */
	private $_from_address;

	/**
	 * private from name
	 * @var string
	 */
	private $_from_name;

	/**
	 * private content type
	 * @var string
	 */
	private $_content_type;

	/**
	 * protected private instance of email
	 * @var string
	 */
	protected static $_instance = null;

	/**
	 * Create instance of class
	 * @var object self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(); }
		return self::$_instance;
	}

	/**
	 * Constructor
	 * Initializes class
	 * Adds actions to trigger emails off of events
	 */
	private function __construct() {

		$this->init();

		add_action( 'lifterlms_email_header', array( $this, 'email_header' ) );
		add_action( 'lifterlms_email_footer', array( $this, 'email_footer' ) );

		/**
		 * @todo  figure out why this is here and what it does...
		 *        there's probably no good reason, if that's the case remove it
		 */
		do_action( 'lifterlms_email', $this );

	}

	/**
	 * Include all email child classes
	 * @return void
	 */
	public function init() {

		// Include email base class
		include_once( 'class.llms.email.php' );

		// Include email child classes
		$this->emails['LLMS_Email_Engagement']      		= include_once( 'emails/class.llms.email.engagement.php' );
		$this->emails['LLMS_Email_Reset_Password']   		= include_once( 'emails/class.llms.email.reset.password.php' );

		$this->emails = apply_filters( 'lifterlms_email_classes', $this->emails );

	}


	/**
	 * Get all email objects
	 * @return array [Array of all email objects]
	 */
	public function get_emails() {
		return $this->emails;
	}


	/**
	 * [get_from_name description]
	 * @return [type] [description]
	 */
	public function get_from_name() {
		if ( ! $this->_from_name ) {
			$this->_from_name = get_option( 'lifterlms_email_from_name' ); }

		return wp_specialchars_decode( $this->_from_name );
	}


	/**
	 * Get from email option data
	 * @return string [From email option in settings->email]
	 */
	public function get_from_address() {
		if ( ! $this->_from_address ) {
			$this->_from_address = get_option( 'lifterlms_email_from_address' ); }

		return $this->_from_address;
	}


	/**
	 * Get the content type
	 * @return string [always returns text/html]
	 */
	public function get_content_type() {
		return $this->_content_type;
	}


	/**
	 * Get email header option
	 * @param  string $email_heading [text email heading option]
	 * @return string [email heading]
	 */
	public function email_header( $email_heading ) {
		llms_get_template( 'emails/header.php', array( 'email_heading' => $email_heading ) );
	}


	/**
	 * get email footer string
	 * @return string [Email footer option as string]
	 */
	public function email_footer() {
		llms_get_template( 'emails/footer.php' );
	}


	/**
	 * Wrap email content
	 * Adds wpautop and wptexturize to content
	 *
	 * @param  string  $email_heading [email heading string]
	 * @param  string  $message       [message string (email content)]
	 * @param  bool  $plain_text      [If plain text then just return content unwrapped]
	 *
	 * @return [type]                 [description]
	 */
	public function wrap_message( $email_heading, $message, $plain_text = false ) {
		// Buffer
		ob_start();

		do_action( 'lifterlms_email_header', $email_heading );

		echo wpautop( wptexturize( $message ) );

		do_action( 'lifterlms_email_footer' );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}


	/**
	 * Send email
	 * Sends email using wp_mail
	 *
	 * @param  string $to           [email address of recipient]
	 * @param  string $subject      [email subject]
	 * @param  string $message      [email message]
	 * @param  string $headers      [email headers]
	 * @param  string $attachments  [Email Attachements]
	 * @param  string $content_type [Email content type: html or text]
	 *
	 * @return void
	 *
	 */
	public function send( $to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = '', $content_type = 'text/html' ) {

		// Set content type
		$this->_content_type = $content_type;

		// Filters for the email
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Send
		wp_mail( $to, $subject, $message, $headers, $attachments );

		// Unhook filters
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}


	/**
	 * Send an email related to an engagement
	 * Calls trigger method passing arguments
	 *
	 * @param  int $person_id        WP User ID
	 * @param  int $email            WP Post ID of the Email Post to send
	 * @param  int $related_post_id  WP Post ID of the triggering post
	 *
	 * @return void
	 */
	public function trigger_engagement( $person_id, $email_id, $related_post_id ) {

		$email = $this->emails['LLMS_Email_Engagement'];
		$email->trigger( $person_id, $email_id, $related_post_id );

	}

}
