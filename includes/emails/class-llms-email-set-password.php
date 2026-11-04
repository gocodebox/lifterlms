<?php
/**
 * LLMS_Email_Set_Password class
 *
 * @package LifterLMS/Emails/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Email sent to a student registered during passwordless checkout with a link to set their password.
 *
 * Uses the same reset key link as the password reset email, with copy for a password that was never chosen.
 *
 * @since [version]
 */
class LLMS_Email_Set_Password extends LLMS_Email_Reset_Password {

	/**
	 * Email ID.
	 *
	 * @var string
	 */
	protected $id = 'set_password';

	/**
	 * Initializer.
	 *
	 * @since [version]
	 *
	 * @param array $args Associative array of user related data for the email to be sent.
	 * @return void
	 */
	public function init( $args = array() ) {

		parent::init( $args );

		$original_locale = get_locale();
		$locale          = get_user_locale( $args['user']->ID );
		if ( $locale && $locale !== $original_locale ) {
			switch_to_locale( $locale );
		}

		$this->subject = __( 'Set your password for {site_title}', 'lifterlms' );
		$this->heading = __( 'Set Your Password', 'lifterlms' );

		if ( $locale && $locale !== $original_locale ) {
			restore_previous_locale();
		}
	}

	/**
	 * Retrieves the email body.
	 *
	 * @since [version]
	 *
	 * @param array $data Associative array of user related data for the email to be sent.
	 * @return string
	 */
	public function get_body_content( $data ) {

		ob_start();
		llms_get_template(
			'emails/set-password.php',
			array(
				'url'            => $this->get_set_password_url( $data ),
				'dashboard_url'  => llms_get_page_url( 'myaccount' ),
				/** This filter is documented in wp-includes/user.php */
				'expiration_hrs' => (int) round( apply_filters( 'password_reset_expiration', DAY_IN_SECONDS ) / HOUR_IN_SECONDS ),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Retrieves the URL of the password form for the user's reset key.
	 *
	 * @since [version]
	 *
	 * @param array $data Associative array of user related data for the email to be sent.
	 * @return string
	 */
	private function get_set_password_url( $data ) {
		return add_query_arg(
			array(
				'key'   => $data['key'],
				'login' => rawurlencode( $data['user']->user_login ),
			),
			wp_lostpassword_url()
		);
	}
}
