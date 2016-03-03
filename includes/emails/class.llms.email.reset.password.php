<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Reset Password Email Class
* Custom email class to send email reset link to users email
*
* Generates and sends password reset email
*/
class LLMS_Email_Reset_Password extends LLMS_Email {

	/** @var string */
	var $user_login;

	/** @var string */
	var $user_email;

	/** @var string */
	var $reset_key;

	/**
	 * Constructor
	 *
	 * Inherits from parent constructor
	 * @return void
	 */
	function __construct() {

		$this->id 				= 'customer_reset_password';
		$this->title 			= __( 'Reset password', 'lifterlms' );
		$this->description		= __( 'Customer reset password emails are sent when a customer resets their password.', 'lifterlms' );
		$this->template_html 	= 'emails/reset-password.php';
		$this->subject 			= __( 'Password Reset for {site_title}', 'lifterlms' );
		$this->heading      	= __( 'Password Reset Instructions', 'lifterlms' );

		// Trigger
		add_action( 'lifterlms_reset_password_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Sets class variables and sends email
	 *
	 * @param  id $user_login [ID of user requesting password reset email]
	 * @param  string $reset_key  [string passed in http request to validate user]
	 *
	 * @return void
	 */
	function trigger( $user_login = '', $reset_key = '' ) {

		if ( $user_login && $reset_key ) {

			$this->object     = get_user_by( 'login', $user_login );
			$this->user_login = $user_login;
			$this->reset_key  = $reset_key;
			$this->user_email = stripslashes( $this->object->user_email );
			$this->recipient  = $this->user_email;

		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers() );
	}

	/**
	 * get_content_html function.
	 *
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		llms_get_template( $this->template_html, array(
			'email_heading' => $this->get_heading(),
			'user_login' 	=> $this->user_login,
			'reset_key'		=> $this->reset_key,
			'blogname'		=> $this->get_blogname(),
			'sent_to_admin' => false,
			'plain_text'    => false,
		) );
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		llms_get_template( $this->template_plain, array(
			'email_heading' => $this->get_heading(),
			'user_login' 	=> $this->user_login,
			'reset_key'		=> $this->reset_key,
			'blogname'		=> $this->get_blogname(),
			'sent_to_admin' => false,
			'plain_text'    => true,
		) );
		return ob_get_clean();
	}
}

return new LLMS_Email_Reset_Password();
