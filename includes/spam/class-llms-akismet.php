<?php
/**
 * LifterLMS Google reCAPTCHA integration.
 *
 * This class integrates Google's reCAPTCHA into LifterLMS checkout and registration forms.
 *
 * @package LifterLMS/Includes/Spam
 * @since 9.0.0
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Akismet extends LLMS_Captcha {

	use LLMS_Trait_Singleton;

	public function get_slug() {
		return 'akismet';
	}

	public function render() {
		// Add in a honey pot field to help prevent spam bots.
		if ( ! $this->is_enabled() || is_admin() ) {
			return;
		}
		echo '<input type="text" aria-hidden="true" class="sr-only" name="llms_hp_fullname" style="display:none;" autocomplete="off" />';
	}

	public function is_available() {
		// Check if the Akismet plugin is active and the API key is set.
		return defined( 'AKISMET_VERSION' ) && class_exists( 'Akismet' ) && method_exists( 'Akismet', 'get_api_key' ) && ! empty( Akismet::get_api_key() );
	}

	public function is_enabled() {

		return $this->is_available() && llms_parse_bool( get_option( 'lifterlms_akismet_enabled', false ) );
	}

	public function validate( $valid ) {
		if ( ! $this->is_enabled() ) {
			return $valid;
		}

		// If $valid is already a truthy, return early since something else already encountered a validation issue.
		if ( $valid ) {
			return $valid;
		}

		$data_to_check = array(
			'user_ip'              => sanitize_text_field( llms_get_ip_address() ),
			'user_agent'           => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ),
			'referrer'             => sanitize_text_field( $_SERVER['HTTP_REFERER'] ),
			'blog'                 => get_option( 'home' ),
			'blog_lang'            => get_locale(),
			'blog_charset'         => get_option( 'blog_charset' ),
			'permalink'            => get_permalink(),
			'comment_type'         => 'signup',
			'comment_author'       => sanitize_text_field( $_REQUEST['email_address'] ?? wp_get_current_user()->user_email ?? '' ),
			'comment_author_email' => sanitize_email( $_REQUEST['email_address'] ?? wp_get_current_user()->user_email ?? '' ),
			'honeypot_field_name'  => 'llms_hp_fullname',
		);

		$response = Akismet::http_post( build_query( $data_to_check ), 'comment-check' );

		// If the response is empty, we can't determine if it's spam or not.
		if ( empty( $response ) ) {
			return $valid;
		}

		$passed = true;

		// If the X-akismet-pro-tip is set to 'discard' return 2 as blatant spam.
		if ( ! empty( $response[0] ) ) {
			$headers = $response[0]->getAll();
			if ( isset( $headers['x-akismet-pro-tip'] ) && $headers['x-akismet-pro-tip'] === 'discard' ) {
				$passed = false;
			}
		}

		// If the response is true, return 1 as likely spam.
		if ( ! empty( $response[1] ) && $response[1] == 'true' ) {
			// Check if this is a free plan or paid. If it's a free plan or not a checkout, we treat it as spam.
			$plan_id = absint( $_POST['llms_plan_id'] ?? 0 );

			if ( ! $plan_id ) {
				// If no plan ID is provided, we assume it's open registration.
				$passed = false;
			}

			if ( $plan_id && ( $plan = new LLMS_Access_Plan( $plan_id ) ) && $plan->is_free() ) {
				$passed = false;
			}
		}

		if ( ! $passed ) {
			if ( apply_filters( 'llms_enable_spam_logs', false ) ) {
				error_log( 'LLMS_Akismet verification failed: ' . print_r( $response, true ) );
			}

			llms_add_notice( __( 'Suspicious activity detected. Try again in a few minutes.', 'lifterlms' ), 'error' );
			return true;
		}

		// We're okay to proceed.
		return $valid;
	}
}

return LLMS_Akismet::instance();
