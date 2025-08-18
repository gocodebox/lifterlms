<?php
/**
 * LifterLMS Google reCAPTCHA integration.
 *
 * This class integrates Google's reCAPTCHA into LifterLMS checkout and registration forms.
 *
 * @package LifterLMS/Includes/Spam
 * @since 9.0.0
 */
abstract class LLMS_Captcha {

	protected $site_key;

	protected $secret_key;

	public function __construct() {
		$slug             = sanitize_title( $this->get_slug() );
		$constant_prefix  = 'LLMS_' . strtoupper( $slug );
		$this->site_key   = defined( $constant_prefix . '_SITE_KEY' ) ? constant( $constant_prefix . '_SITE_KEY' ) : get_option( 'lifterlms_' . $slug . '_site_key' );
		$this->secret_key = defined( $constant_prefix . '_SECRET_KEY' ) ? constant( $constant_prefix . '_SECRET_KEY' ) : get_option( 'lifterlms_' . $slug . '_secret_key' );

		add_action( 'llms_checkout_footer_before', array( $this, 'render' ) );
		add_action( 'lifterlms_after_registration_fields', array( $this, 'render' ) );
		add_action( 'lifterlms_after_free_enroll_fields', array( $this, 'render' ) );

		add_action( 'llms_before_checkout_validation', array( $this, 'validate' ) );
		add_filter( 'llms_before_registration_validation', array( $this, 'validate' ) );

		add_action( 'lifterlms_after_free_enroll_fields', array( $this, 'show_notices' ) );
	}

	/**
	 * Get the name of the captcha service.
	 *
	 * @return string
	 */
	abstract public function get_slug();

	/**
	 * Render the captcha in the footer.
	 *
	 * @since 9.0.0
	 *
	 * @return void
	 */
	abstract public function render();

	/**
	 * Validate the captcha response.
	 *
	 * @since 9.0.0
	 *
	 * @param array $data Form data.
	 *
	 * @return array
	 */
	abstract public function validate( $data );

	/**
	 * Check if the captcha is enabled.
	 *
	 * @since 9.0.0
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return $this->get_slug() === get_option( 'lifterlms_captcha' );
	}

	/**
	 * Show notices if enabled.
	 *
	 * @since 9.0.0
	 *
	 * @return void
	 */
	function show_notices() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		llms_print_notices();
	}
}
