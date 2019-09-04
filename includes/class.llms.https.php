<?php
/**
 * Handle HTTPS related redirects
 *
 * @since 3.0.0
 * @version 3.35.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_HTTPS
 *
 * @since 3.0.0
 * @since 3.35.1 Sanitize `$_SERVER` input.
 */
class LLMS_HTTPS {

	/**
	 * Constructor
	 *
	 * @since    3.0.0
	 */
	public function __construct() {

		if ( 'yes' === get_option( 'lifterlms_checkout_force_ssl' ) ) {

			add_action( 'template_redirect', array( $this, 'force_https_redirect' ) );
			add_action( 'template_redirect', array( $this, 'unforce_https_redirect' ) );

		}

	}

	/**
	 * Retrieve the http/s version of the current url.
	 *
	 * @since 3.35.1
	 *
	 * @param bool $https If true, gets the HTTPS url, otherwise gets url without HTTPS
	 * @return string
	 */
	protected function get_force_redirect_url( $https = true ) {

		$uri = ! empty( $_SERVER['REQUEST_URI'] ) ? filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL ) : '';

		// URI is http, switch it to https.
		if ( $uri && 0 === strpos( $uri, 'http' ) ) {
			return $https ? preg_replace( '|^http://|', 'https://', $uri ) : preg_replace( '|^https://|', 'http://', $uri );
		}

		// URI doesn't have a protocol, build a new uri.
		$redirect = $https ? 'https://' : 'http://';
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
			$redirect .= sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_HOST'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$redirect .= sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		}

		$redirect .= $uri;
		return $redirect;

	}

	/**
	 * Redirect to https checkout page is force is enabled
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown
	 * @since 3.35.1 Sanitize `$_SERVER` input.
	 *
	 * @return void
	 */
	public function force_https_redirect() {

		if ( ! is_ssl() && ( is_llms_checkout() || is_llms_account_page() || apply_filters( 'llms_force_ssl_checkout', false ) ) ) {
			llms_redirect_and_exit(
				$this->get_force_redirect_url( true ),
				array(
					'status' => 301,
				)
			);
		}

	}

	/**
	 * Redirect back to http when not on checkout if force ssl is enabled and the site isn't fully ssl'd
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown
	 * @since 3.35.1 Sanitize `$_SERVER` input.
	 *
	 * @return void
	 */
	public function unforce_https_redirect() {

		if ( ! llms_is_site_https() && is_ssl() && ! is_llms_checkout() & ! is_llms_account_page() && ! llms_is_ajax() && apply_filters( 'llms_unforce_ssl_checkout', true ) ) {
			llms_redirect_and_exit(
				$this->get_force_redirect_url( false ),
				array(
					'status' => 301,
				)
			);
		}

	}


}

return new LLMS_HTTPS();
