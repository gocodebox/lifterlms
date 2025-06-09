<?php

/**
 * LifterLMS Turnstile integration.
 *
 * This class integrates Cloudflare's Turnstile captcha into LifterLMS checkout and registration forms.
 *
 * @package LifterLMS/Includes/Spam
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Turnstile {
	use LLMS_Trait_Singleton;

	protected $site_key;

	protected $secret_key;

	public function __construct() {

		$this->site_key   = defined( 'LLMS_TURNSTILE_SITE_KEY' ) ? LLMS_TURNSTILE_SITE_KEY : get_option( 'lifterlms_turnstile_site_key' );
		$this->secret_key = defined( 'LLMS_TURNSTILE_SECRET_KEY' ) ? LLMS_TURNSTILE_SECRET_KEY : get_option( 'lifterlms_turnstile_secret_key' );

		add_action( 'wp_head', array( $this, 'add_turnstile_script' ) );
		add_action( 'llms_checkout_footer_before', array( $this, 'add_turnstile_check' ) );
		add_action( 'lifterlms_after_registration_fields', array( $this, 'add_turnstile_check' ) );
		add_action( 'lifterlms_after_free_enroll_fields', array( $this, 'add_turnstile_check' ) );

		add_filter( 'llms_before_checkout_validation', array( $this, 'validate_turnstile' ) );
		add_filter( 'llms_before_registration_validation', array( $this, 'validate_turnstile' ) );
	}

	/**
	 * Check if Turnstile is enabled.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	function is_enabled() {
		return 'turnstile' === get_option( 'lifterlms_captcha' );
	}

	/**
	 * Enqueue the Cloudflare Turnstile script.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function add_turnstile_script() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js' );
	}

	/**
	 * Add the Turnstile widget to the checkout and registration forms.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function add_turnstile_check() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		?>
		<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $this->site_key ); ?>"></div>
		<?php
	}

	/**
	 * Validate the Turnstile captcha response.
	 *
	 * @since [version]
	 *
	 * @param mixed $valid The current validation status.
	 * @return mixed True if validation fails, otherwise the original $valid value.
	 */
	function validate_turnstile( $valid ) {
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
			error_log( 'checkout blocked due to missing captcha' );
			// Customize the error message displayed when a registration is blocked.
			llms_add_notice( __( 'Blocked.', 'my-text-domain' ), 'error' );
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
			// Not valid. Block them.
			// Customize the error message displayed when a registration is blocked.
			llms_add_notice( __( 'Blocked.', 'my-text-domain' ), 'error' );
			return true;
		}

		// We're okay to proceed.
		return $valid;
	}
}

return LLMS_Turnstile::instance();
