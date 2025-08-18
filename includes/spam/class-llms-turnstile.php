<?php

/**
 * LifterLMS Turnstile integration.
 *
 * This class integrates Cloudflare's Turnstile captcha into LifterLMS checkout and registration forms.
 *
 * @package LifterLMS/Includes/Spam
 * @since 9.0.0
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Turnstile extends LLMS_Captcha {

	use LLMS_Trait_Singleton;

	public function __construct() {

		parent::__construct();

		add_action( 'wp_head', array( $this, 'add_turnstile_script' ) );
	}

	public function get_slug() {
		return 'turnstile';
	}

	/**
	 * Enqueue the Cloudflare Turnstile script.
	 *
	 * @since 9.0.0
	 *
	 * @return void
	 */
	public function add_turnstile_script() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js' );
	}

	/**
	 * Add the Turnstile widget to the checkout and registration forms.
	 *
	 * @since 9.0.0
	 *
	 * @return void
	 */
	public function render() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		?>
		<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $this->site_key ); ?>"></div>
		<?php
	}

	/**
	 * Validate the Turnstile captcha response.
	 *
	 * @since 9.0.0
	 *
	 * @param mixed $valid The current validation status.
	 * @return mixed True if validation fails, otherwise the original $valid value.
	 */
	public function validate( $valid ) {
		if ( ! $this->is_enabled() ) {
			return $valid;
		}

		// If $valid is already a truthy, return early since something else already encountered a validation issue.
		if ( $valid ) {
			return $valid;
		}

		// If we don't have a response to test, return an error and stop registration.
		$captcha = llms_filter_input_sanitize_string( INPUT_POST, 'cf-turnstile-response' );
		if ( ! $captcha ) {
			if ( apply_filters( 'llms_enable_recaptcha_logs', false ) ) {
				error_log( 'LifterLMS form blocked due to missing captcha' );
			}
			// Customize the error message displayed when a registration is blocked.
			llms_add_notice( __( 'Blocked.', 'lifterlms' ), 'error' );
			return true;
		}

		// Ok, try to validate the captcha.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && filter_var( $_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP ) ) {
			// Use the CloudFlare IP if it exists.
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$url_path      = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
		$data          = array(
			'secret'   => $this->secret_key,
			'response' => $captcha,
			'remoteip' => $ip,
		);
		$options       = array(
			'http' => array(
				'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" .
							"User-Agent: PHP Script\r\n",
				'method'  => 'POST',
				'content' => http_build_query( $data ),
			),
		);
		$stream        = stream_context_create( $options );
		$result        = file_get_contents( $url_path, false, $stream );
		$response      = $result;
		$response_keys = json_decode( $response, true );

		if ( intval( $response_keys['success'] ) !== 1 ) {
			if ( apply_filters( 'llms_enable_spam_logs', false ) ) {
				error_log( 'LLMS_Turnstile verification failed: ' . print_r( $response, true ) );
			}

			llms_add_notice( __( 'Verification failed. Please try again.', 'lifterlms' ), 'error' );
			return true;
		}

		// We're okay to proceed.
		return $valid;
	}
}

return LLMS_Turnstile::instance();
