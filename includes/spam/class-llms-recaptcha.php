<?php
/**
 * LifterLMS Google reCAPTCHA integration.
 *
 * This class integrates Google's reCAPTCHA into LifterLMS checkout and registration forms.
 *
 * @package LifterLMS/Includes/Spam
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Google_Recaptcha {

	use LLMS_Trait_Singleton;

	protected $site_key;

	protected $secret_key;

	protected $min_score;

	protected $action;

	public function __construct() {

		$this->site_key   = defined( 'LLMS_RECAPTCHA_SITE_KEY' ) ? LLMS_RECAPTCHA_SITE_KEY : get_option( 'lifterlms_recaptcha_site_key' );
		$this->secret_key = defined( 'LLMS_RECAPTCHA_SECRET_KEY' ) ? LLMS_RECAPTCHA_SECRET_KEY : get_option( 'lifterlms_recaptcha_secret_key' );

		/**
		 * Minimum score for reCAPTCHA validation.
		 *
		 * @since [version]
		 */
		$this->min_score = apply_filters( 'lifterlms_recaptcha_min_score', 0.5 );

		/**
		 * Action name for reCAPTCHA validation.
		 *
		 * @since [version]
		 */
		$this->action = apply_filters( 'lifterlms_recaptcha_action', 'submit' );

		/* Front‑end assets */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		/* Show widget on both forms */
		add_action( 'llms_checkout_footer_before', array( $this, 'render' ) );
		add_action( 'lifterlms_after_registration_fields', array( $this, 'render' ) );
		add_action( 'lifterlms_after_free_enroll_fields', array( $this, 'render' ) );

		add_action( 'llms_before_checkout_validation', array( $this, 'validate_recaptcha' ) );
		add_filter( 'llms_before_registration_validation', array( $this, 'validate_recaptcha' ) );
	}

	public function is_enabled() {
		return 'recaptcha' === get_option( 'lifterlms_captcha' );
	}

	public function enqueue() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		// Adding to all pages as recommended by the Google reCAPTCHA documentation.
		wp_enqueue_script(
			'llms-google-recaptcha',
			'https://www.google.com/recaptcha/api.js?render=' . $this->site_key,
			array(),
			null,
			true
		);
	}

	public function render() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		echo '<input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />';

		// TODO: This only lasts for two minutes, so we need to re-execute it.

		// Add class and data to the submit button?
		/**
		 * <button class="g-recaptcha"
		 * data-sitekey="reCAPTCHA_site_key"
		 * data-callback='onSubmit'
		 * data-action='submit'>Submit</button>
		 */

		wp_add_inline_script(
			'google-recaptcha-v3',
			sprintf(
				"document.addEventListener('DOMContentLoaded',function(){
					grecaptcha.ready(function(){
						grecaptcha.execute('%s',{action:'%s'}).then(function(token){
							document.querySelectorAll('.g-recaptcha-response').forEach(function(el){
								el.value = token;
							});
						});
					});
				});",
				esc_js( $this->site_key ),
				esc_js( $this->action )
			),
			'after'
		);
	}

	public function validate_recaptcha( $valid ) {
		if ( ! $this->is_enabled() ) {
			return $valid;
		}

		// If $valid is already a truthy, return early since something else already encountered a validation issue.
		if ( $valid ) {
			return $valid;
		}
		$token = isset( $_POST['g-recaptcha-response'] )
			? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
			: '';

		if ( ! $token ) {
			llms_add_notice( __( 'CAPTCHA token missing, please refresh and try again.', 'lifterlms' ), 'error' );
			return true;
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body'    => array(
					'secret'   => $this->secret_key,
					'response' => $token,
					'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
				),
				'timeout' => 15,
			)
		);

		$body = ! is_wp_error( $response ) ? json_decode( wp_remote_retrieve_body( $response ), true ) : null;

		$passed = $body
					&& ! empty( $body['success'] )
					&& $body['score'] >= $this->min_score
					&& ( empty( $body['action'] ) || $body['action'] === $this->action ); // action check is optional but recommended

		if ( ! $passed ) {
			llms_add_notice( __( 'CAPTCHA validation failed — please try again.', 'lifterlms' ), 'error' );
			return true;
		}

		// We're okay to proceed.
		return $valid;
	}
}

return LLMS_Google_Recaptcha::instance();
