<?php
/**
 * Privacy related template functions
 *
 * @since    3.18.0
 * @version  3.18.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the HTML for the Terms field displayed on reg forms
 *
 * @param    boolean    $echo   echo the data if true, return otherwise
 * @return   void|string
 * @since    3.0.0
 * @version  3.18.1
 */
if ( ! function_exists( 'llms_agree_to_terms_form_field' ) ) {

	function llms_agree_to_terms_form_field( $echo = true ) {

		// do_action passes empty string
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
}// End if().



/**
 * Get the HTML for the Privacy Policy section on checkout / registration forms
 *
 * @param    boolean    $echo   echo the data if true, return otherwise
 * @return   void|string
 * @since    3.0.0
 * @version  3.18.1
 */
if ( ! function_exists( 'llms_privacy_policy_form_field' ) ) {

	function llms_privacy_policy_form_field( $echo = true ) {

		// do_action passes empty string
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
