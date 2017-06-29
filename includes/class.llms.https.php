<?php
/**
 * Handle HTTPS related redirects
 *
 * @since    3.0.0
 * @version  3.10.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_HTTPS {

	/**
	 * Constructor
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct() {

		if ( 'yes' === get_option( 'lifterlms_checkout_force_ssl' ) ) {

			add_action( 'template_redirect', array( $this, 'force_https_redirect' ) );
			add_action( 'template_redirect', array( $this, 'unforce_https_redirect' ) );

		}

	}

	/**
	 * Redircet to https checkout page is force is enabled
	 * @return   void
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function force_https_redirect() {

		if ( ! is_ssl() && ( is_llms_checkout() || is_llms_account_page() || apply_filters( 'llms_force_ssl_checkout', false ) ) ) {

			if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {

				wp_safe_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
				exit;

			} else {

				wp_safe_redirect( 'https://' . ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI'] );
				exit;

			}
		}

	}

	/**
	 * Redirect back to http when not on checkout if force ssl is enabled and the site isn't fully ssl'd
	 * @return   void
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function unforce_https_redirect() {

		if ( ! llms_is_site_https() && is_ssl() && $_SERVER['REQUEST_URI'] && ! is_llms_checkout() & ! is_llms_account_page() && ! llms_is_ajax() && apply_filters( 'llms_unforce_ssl_checkout', true ) ) {

			if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {

				wp_safe_redirect( preg_replace( '|^https://|', 'http://', $_SERVER['REQUEST_URI'] ) );
				exit;

			} else {

				wp_safe_redirect( 'http://' . ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI'] );
				exit;

			}
		}

	}


}

return new LLMS_HTTPS();
