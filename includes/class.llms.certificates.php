<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Frontend scripts class
*
* Initializes front end scripts
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Certificates {

	public $emails;

	public $email_content;

	private $_from_address;

	private $_from_name;

	private $_content_type;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	function __construct() {
		
		$this->init();

		add_action( 'lifterlms_lesson_completed_certificate', array( $this, 'lesson_completed' ), 10, 3 );

	}

	function init() {

		include_once( 'class.llms.certificate.php' );

		$this->emails['LLMS_Certificate_User']      = include_once( 'certificates/class.llms.certificate.user.php' );

	}

	function get_emails() {
		return $this->emails;
	}

	function get_from_name() {
		if ( ! $this->_from_name )
			$this->_from_name = get_option( 'lifterlms_email_from_name' );

		return wp_specialchars_decode( $this->_from_name );
	}

	function get_from_address() {
		if ( ! $this->_from_address )
			$this->_from_address = get_option( 'lifterlms_email_from_address' );

		return $this->_from_address;
	}

	function get_content_type() {
		return $this->_content_type;
	}

	function email_header( $email_heading ) {
		llms_get_template( 'emails/header.php', array( 'email_heading' => $email_heading ) );
	}

	function email_footer() {
		llms_get_template( 'emails/footer.php' );
	}

	function wrap_message( $email_heading, $message, $plain_text = false ) {
		// Buffer
		ob_start();

		do_action( 'lifterlms_email_header', $email_heading );

		echo wpautop( wptexturize( $message ) );

		do_action( 'lifterlms_email_footer' );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	function person_new_account( $person_id, $new_person_data = array(), $password_generated = false ) {
		if ( ! $person_id )
			return;

		$user_pass = ! empty( $new_person_data['user_pass'] ) ? $new_person_data['user_pass'] : '';
		$email = $this->emails['LLMS_Email_Person_New'];
		$email->trigger( $person_id, $user_pass, $password_generated );
	}

	function lesson_completed( $person_id, $email_id, $lesson_id ) {

		if ( ! $person_id )
			return;

		$certificate = $this->emails['LLMS_Certificate_User'];
		$certificate->trigger( $person_id, $email_id, $lesson_id );
	}

}



