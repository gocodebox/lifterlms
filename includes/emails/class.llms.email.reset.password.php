<?php
/**
 * LifterLMS Password Reset Email
 * @since    1.0.0
 * @version  3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Email_Reset_Password extends LLMS_Email {

	protected $id = 'reset_password';

	/**
	 * Initiailizer
	 * @param    array      $args  associative array of user related data for the email to be sent
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function init( $args = array() ) {

		$this->add_recipient( $args['user']->ID );

		$this->body = $this->get_body_content( $args );
		$this->subject = __( 'Password Reset for {site_title}', 'lifterlms' );
		$this->heading = __( 'Reset Your Password', 'lifterlms' );

		$this->add_merge_data( array(
			'{user_login}' => $args['login_display'],
		) );

	}

	/**
	 * Custom content for the password reset email
	 * @param    array     $data  associative array of user related data for the email to be sent
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_body_content( $data ) {

		$url = esc_url( add_query_arg( array(
				'key' => $data['key'],
				'login' => rawurlencode( $data['user']->user_login ),
			),
			llms_lostpassword_url()
		) );

		ob_start();
		llms_get_template( 'emails/reset-password.php', array(
			'url' => $url,
		) );
		return ob_get_clean();

	}

}
