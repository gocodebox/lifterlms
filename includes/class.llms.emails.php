<?php
/**
* LifterLMS Emails Class
*
* Manages finding the appropriate email
*
* @since    1.0.0
* @version  [version]
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Emails {

	/**
	 * Object of all emails
	 * @var object
	 */
	public $emails;

	/**
	 * protected private instance of email
	 * @var string
	 */
	protected static $_instance = null;

	/**
	 * Create instance of class
	 * @var object self
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 * Initializes class
	 * Adds actions to trigger emails off of events
	 * @since    1.0.0
	 * @version  [version]
	 */
	private function __construct() {

		// Include email child classes
		$this->emails['LLMS_Email_Engagement'] = include_once( 'emails/class.llms.email.engagement.php' );
		$this->emails['LLMS_Email_Reset_Password']= include_once( 'emails/class.llms.email.reset.password.php' );

		$this->emails = apply_filters( 'lifterlms_email_classes', $this->emails );

		add_action( 'lifterlms_email_header', array( $this, 'email_header' ) );
		add_action( 'lifterlms_email_footer', array( $this, 'email_footer' ) );

	}

	/**
	 * get email footer string
	 * @return string [Email footer option as string]
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function email_footer() {
		llms_get_template( 'emails/footer.php' );
	}

	/**
	 * Get email header option
	 * @param  string $email_heading [text email heading option]
	 * @return string [email heading]
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function email_header( $email_heading ) {
		llms_get_template( 'emails/header.php', array( 'email_heading' => $email_heading ) );
	}

	/**
	 * Get css rules specific to the the email templates
	 * @param    string     $rule  name of the css rule
	 * @param    boolean    $echo  if true, echo the definition
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_css( $rule = '', $echo = true ) {

		$css = apply_filters( 'llms_email_css', array(
			'background-color' => '#f6f6f6',
			'border-radius' => '3px',
			'font-color' => '#222222',
			'font-family' => 'sans-serif',
			'font-size' => '15px',
			'font-size-small' => '13px',
			'heading-background-color' => '#2295ff',
			'heading-font-color' => '#ffffff',
			'main-color' => '#2295ff',
			'max-width' => '580px',
		) );

		if ( isset( $css[ $rule ] ) ) {

			if ( $echo ) {
				echo $css[ $rule ];
			}

			return $css[ $rule ];

		}

		return '';

	}

	/**
	 * Get all email objects
	 * @return array [Array of all email objects]
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_emails() {
		return $this->emails;
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
	 * @return bool
	 *
	 */
	public function send( $to = array(), $subject = array(), $message = '', $headers = array(), $attachments = '' ) {

		// Filters for the email
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Send
		$send = wp_mail( $to, $subject, $message, $headers, $attachments );

		// Unhook filters
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		return $send;

	}

	/**
	 * Send an email related to an engagement
	 * Calls trigger method passing arguments
	 * @param  int $person_id        WP User ID
	 * @param  int $email            WP Post ID of the Email Post to send
	 * @param  int $related_post_id  WP Post ID of the triggering post
	 * @return void
	 * @since    ??
	 * @version  ??
	 */
	public function trigger_engagement( $person_id, $email_id, $related_post_id ) {

		$email = $this->emails['LLMS_Email_Engagement'];
		$email->trigger( $person_id, $email_id, $related_post_id );

	}

}
