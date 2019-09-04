<?php
/**
 * LifterLMS Emails Class
 *
 * Manages finding the appropriate email
 *
 * @since    1.0.0
 * @version  3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Emails {

	/**
	 * Class names of all emails
	 *
	 * @var string[]
	 */
	public $emails;

	/**
	 * protected private instance of email
	 *
	 * @var LLMS_Emails
	 */
	protected static $_instance = null;

	/**
	 * Create instance of class
	 *
	 * @return LLMS_Emails
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
	 *
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	private function __construct() {

		// template functions
		LLMS()->include_template_functions();

		// email base class
		require_once 'emails/class.llms.email.php';
		$this->emails['generic'] = 'LLMS_Email';

		// Include email child classes
		require_once 'emails/class.llms.email.engagement.php';
		$this->emails['engagement'] = 'LLMS_Email_Engagement';

		require_once 'emails/class.llms.email.reset.password.php';
		$this->emails['reset_password'] = 'LLMS_Email_Reset_Password';

		$this->emails = apply_filters( 'lifterlms_email_classes', $this->emails );

	}

	/**
	 * Get a string of inline CSS to add to an email button
	 * Use {button_style} merge code to output in HTML emails
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_button_style() {
		$rules  = apply_filters(
			'llms_email_button_css',
			array(
				'background-color' => $this->get_css( 'button-background-color', false ),
				'color'            => $this->get_css( 'button-font-color', false ),
				'display'          => 'inline-block',
				'padding'          => '10px 15px',
				'text-decoration'  => 'none',
			)
		);
		$styles = '';
		foreach ( $rules as $rule => $style ) {
			$styles .= sprintf( '%1$s:%2$s !important;', $rule, $style );
		}
		return $styles;
	}

	/**
	 * Get css rules specific to the the email templates
	 *
	 * @param    string  $rule  name of the css rule
	 * @param    boolean $echo  if true, echo the definition
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_css( $rule = '', $echo = true ) {

		$css = apply_filters(
			'llms_email_css',
			array(
				'background-color'         => '#f6f6f6',
				'border-radius'            => '3px',
				'button-background-color'  => '#2295ff',
				'button-font-color'        => '#ffffff',
				'divider-color'            => '#cecece',
				'font-color'               => '#222222',
				'font-family'              => 'sans-serif',
				'font-size'                => '15px',
				'font-size-small'          => '13px',
				'heading-background-color' => '#2295ff',
				'heading-font-color'       => '#ffffff',
				'main-color'               => '#2295ff',
				'max-width'                => '580px',
			)
		);

		if ( isset( $css[ $rule ] ) ) {

			if ( $echo ) {
				echo $css[ $rule ];
			}

			return $css[ $rule ];

		}

	}

	/**
	 * Get an HTML divider for use in HTML emails
	 * Can use shortcode {divider} to output in any email
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_divider_html() {
		return '<div style="height:1px;width:100%;margin:15px auto;background-color:' . $this->get_css( 'divider-color', false ) . '"></div>';
	}

	/**
	 * Retrieve a new instance of an email
	 *
	 * @param    string $id    email id
	 * @param    array  $args  optional arguments to pass to the email
	 * @return   LLMS_Email
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_email( $id, $args = array() ) {

		$emails = $this->get_emails();

		// if we have an email matching the ID, return an instance of that email class
		if ( isset( $emails[ $id ] ) ) {
			return new $emails[ $id ]( $args );
		}

		// otherwise return a generic email and set the ID to be the requested ID
		/** @var LLMS_Email $generic */
		$generic = new $emails['generic']( $args );
		$generic->set_id( $id );
		return $generic;

	}

	/**
	 * Get all email objects
	 *
	 * @return string[] [Array of all email class names]
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_emails() {
		return $this->emails;
	}

	/**
	 * Retrieve the source url of the header image as defined in LifterLMS settings
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_header_image_src() {
		$src = get_option( 'lifterlms_email_header_image', '' );
		if ( is_numeric( $src ) ) {
			$attachment = wp_get_attachment_image_src( $src, 'full' );
			$src        = $attachment ? $attachment[0] : '';
		}
		return apply_filters( 'llms_email_header_image_src', $src );
	}

}
