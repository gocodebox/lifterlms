<?php
/**
 * Privacy related template functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.18.0
 * @version 3.18.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the HTML for the Terms field displayed on reg forms
 *
 * @since 3.0.0
 * @since 3.18.1
 *
 * @param boolean $echo Echo the data if true, return otherwise.
 * @return void|string
 */
if ( ! function_exists( 'llms_agree_to_terms_form_field' ) ) {

	function llms_agree_to_terms_form_field( $echo = true ) {

		// Because `do_action()` passes empty string.
		if ( '' === $echo ) {
			$echo = true;
		}

		$ret = '';

		if ( llms_are_terms_and_conditions_required() ) {

			$ret = llms_form_field(
				array(
					'columns'     => 12,
					'description' => '',
					'default'     => 'no',
					'id'          => 'llms_agree_to_terms',
					'label'       => llms_get_terms_notice( true ),
					'last_column' => true,
					'required'    => true,
					'type'        => 'checkbox',
					'value'       => 'yes',
				),
				false
			);

		}

		$ret = apply_filters( 'llms_agree_to_terms_form_field', $ret, $echo );

		if ( $echo ) {

			echo $ret;
			return;

		}

		return $ret;

	}
}



/**
 * Get the HTML for the Privacy Policy section on checkout / registration forms
 *
 * @since 3.0.0
 * @since 3.18.1
 *
 * @param boolean $echo Echo the data if true, return otherwise.
 * @return void|string
 */
if ( ! function_exists( 'llms_privacy_policy_form_field' ) ) {

	function llms_privacy_policy_form_field( $echo = true ) {

		// Because `do_action()` passes empty string.
		if ( '' === $echo ) {
			$echo = true;
		}

		$ret = '';

		$notice = llms_get_privacy_notice( true );
		if ( $notice ) {
			$ret = llms_form_field(
				array(
					'columns'     => 12,
					'label'       => $notice,
					'last_column' => true,
					'type'        => 'html',
				),
				false
			);
		}

		$ret = apply_filters( 'llms_privacy_policy_form_field', $ret, $echo );

		if ( $echo ) {

			echo $ret;
			return;

		}

		return $ret;

	}
}
