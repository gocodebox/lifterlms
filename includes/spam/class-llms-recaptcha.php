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

class LLMS_Google_Recaptcha extends LLMS_Captcha {

	use LLMS_Trait_Singleton;

	protected $min_score;

	protected $action;

	public function __construct() {

		parent::__construct();

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
	}

	public function get_slug() {
		return 'recaptcha';
	}

	public function render() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		echo '<input type="hidden" name="g-recaptcha-response" class="llms-google-recaptcha g-recaptcha-response" />';

		wp_enqueue_script(
			'llms-google-recaptcha',
			'https://www.google.com/recaptcha/api.js?render=' . $this->site_key,
			array(),
			null,
			true
		);
		wp_add_inline_script(
			'llms-google-recaptcha',
			"
		document.querySelectorAll( 'form' ).forEach( function( form ) {
			if ( form.querySelector( '.llms-google-recaptcha' ) === null ) {
				return;
			}
			form.addEventListener( 'submit', function( event ) {
				event.preventDefault();
				grecaptcha.ready(() => {
					grecaptcha.execute( '" . esc_js( $this->site_key ) . "', { action: '" . esc_js( $this->action ) . "' } ).then( token => {
						form.querySelector('[name=g-recaptcha-response]').value = token;
						form.submit();
					} );
				} );
			} );
		} );
		"
		);
	}

	public function validate( $valid ) {
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
			if ( apply_filters( 'llms_enable_recaptcha_logs', false ) ) {
				error_log( 'LifterLMS form blocked due to missing captcha' );
			}
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
			if ( apply_filters( 'llms_enable_recaptcha_logs', false ) ) {
				error_log( 'LLMS_Google_Recaptcha verification failed: ' . ( $body ? print_r( $body, true ) : '' ) );
			}

			llms_add_notice( __( 'CAPTCHA validation failed — please try again.', 'lifterlms' ), 'error' );
			return true;
		}

		// We're okay to proceed.
		return $valid;
	}
}

return LLMS_Google_Recaptcha::instance();
