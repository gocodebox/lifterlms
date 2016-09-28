<?php
/**
 * Localization Functions
 * Currently only used to translate strings output by Javascript functions
 * More robust features will be added in the future
 *
 * @since  2.7.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_HTTPS {

	public function __construct() {

		if ( 'yes' === get_option( 'lifterlms_checkout_force_ssl' ) ) {

			add_action( 'template_redirect', array( $this, 'force_https_redirect' ) );
			add_action( 'template_redirect', array( $this, 'unforce_https_redirect' ) );

		}

		// var_dump(llms_is_site_https());

	}

	public function force_https_redirect() {

		if ( ! is_ssl() && ( is_llms_checkout() || apply_filters( 'llms_force_ssl_checkout', false ) ) ) {

			if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {

				wp_safe_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
				exit;

			} else {

				wp_safe_redirect( 'https://' . ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI'] );
				exit;

			}

		}

	}

	public function unforce_https_redirect() {

		if ( ! llms_is_site_https() && is_ssl() && $_SERVER['REQUEST_URI'] && ! is_llms_checkout() && ! is_ajax() && apply_filters( 'llms_unforce_ssl_checkout', true ) ) {

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
