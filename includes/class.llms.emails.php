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

		// email base class
		require_once 'emails/class.llms.email.php';
		$this->emails['generic'] = 'LLMS_Email';

		// Include email child classes
		require_once 'emails/class.llms.email.engagement.php';
		$this->emails['engagement'] = 'LLMS_Email_Engagement';

		// $this->emails['LLMS_Email_Reset_Password']= include_once( 'emails/class.llms.email.reset.password.php' );

		$this->emails = apply_filters( 'lifterlms_email_classes', $this->emails );

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
	 * Retrieve a new instance of an email
	 * @param    string     $id    email id
	 * @param    array      $args  optional arguments to pass to the email
	 * @return   obj
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_email( $id, $args = array() ) {

		$emails = $this->get_emails();

		// if we have an email matching the ID, return an instance of that email class
		if ( isset( $emails[ $id ] ) ) {
			return new $emails[ $id ]( $args );
		}

		// otherwise return a generic email and set the ID to be the requested ID
		$generic = new $emails['generic']( $args );
		$generic->set_id( $id );
		return $generic;

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

}
