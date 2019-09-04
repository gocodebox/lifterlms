<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions related to privacy policy and terms & conditions
 *
 * @since    3.18.0
 * @version  3.18.0
 */


/**
 * Determine if Terms & Conditions agreement is required during registration
 * according to global settings
 *
 * @return   boolean
 * @since    3.0.0
 * @version  3.3.1
 */
function llms_are_terms_and_conditions_required() {

	$enabled = get_option( 'lifterlms_registration_require_agree_to_terms' );
	$page_id = absint( get_option( 'lifterlms_terms_page_id', false ) );

	return ( 'yes' === $enabled && $page_id );

}

/**
 * Retrieve the text/html for the custom privacy policy notice
 *
 * @param    bool $merge  if true, will merge {{policy}} to an HTML anchor
 *                        uses `wp_page_for_privacy_policy` for page ID & title
 * @return   string
 * @since    3.18.0
 * @version  3.18.0
 */
function llms_get_privacy_notice( $merge = false ) {

	$text = get_option( 'llms_privacy_notice', esc_html__( 'Your personal data will be used to process your enrollment, support your experience on this website, and for other purposes described in our {{policy}}.', 'lifterlms' ) );

	$ret = $text;

	// merge the {{policy}} code
	if ( $merge ) {

		// only merge if we some text saved & a page set
		if ( $text && get_option( 'wp_page_for_privacy_policy', false ) ) {
			$ret = str_replace( '{{policy}}', llms_get_option_page_anchor( 'wp_page_for_privacy_policy' ), $ret );
			// otherwise return empty string
		} else {
			$ret = '';
		}

		// kisses
		$ret = wp_kses(
			$ret,
			array(
				'a'      => array(
					'href'   => array(),
					'target' => array(),
				),
				'b'      => array(),
				'em'     => array(),
				'i'      => array(),
				'strong' => array(),
			)
		);
	}

	return apply_filters( 'llms_get_privacy_notice', $ret, $text );

}

/**
 * Retrieve the text/html for the custom t&c notice
 *
 * @param    bool $merge  if true, will merge {{terms}} to an HTML anchor
 *                        uses `lifterlms_terms_page_id` for page ID & title
 * @return   string
 * @since    3.18.0
 * @version  3.18.0
 */
function llms_get_terms_notice( $merge = false ) {

	// get the option
	$text = get_option( 'llms_terms_notice' );

	// fallback to default if no option set
	if ( ! $text ) {
		$text = esc_html__( 'I have read and agree to the {{terms}}.', 'lifterlms' );
	}

	$ret = $text;

	// merge the {{terms}} code
	if ( $merge ) {

		// only merge if we have a page set
		if ( get_option( 'lifterlms_terms_page_id', false ) ) {
			$ret = str_replace( '{{terms}}', llms_get_option_page_anchor( 'lifterlms_terms_page_id' ), $ret );
			// otherwise return empty string
		} else {
			$ret = '';
		}

		// kisses
		$ret = wp_kses(
			$ret,
			array(
				'a'      => array(
					'href'   => array(),
					'target' => array(),
				),
				'b'      => array(),
				'em'     => array(),
				'i'      => array(),
				'strong' => array(),
			)
		);
	}

	return apply_filters( 'llms_get_terms_notice', $ret, $text );

}
