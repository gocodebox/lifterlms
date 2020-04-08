<?php
/**
 * My Account Shortcode
 *
 * [lifterlms_my_account]
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since 1.0.0
 * @version 3.25.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_My_Account class.
 *
 * @since 1.0.0
 * @since 3.25.1
 */
class LLMS_Shortcode_My_Account {

	/**
	 * Get shortcode content
	 *
	 * @param array $atts Shortcode attributes array.
	 * @return array $messages
	 */
	public static function get( $atts ) {
		return LLMS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Lost password template
	 *
	 * @return     void
	 * @since      1.0.0
	 * @version    3.25.1
	 * @deprecated 3.25.1
	 */
	public static function lost_password() {

		llms_deprecated_function( 'LLMS_Shortcode_My_Account::lost_password()', '3.25.1' );

		$args = array();

		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			$args['form']   = 'reset_password';
			$args['fields'] = LLMS_Person_Handler::get_password_reset_fields( trim( sanitize_text_field( wp_unslash( $_GET['key'] ) ) ), trim( sanitize_text_field( wp_unslash( $_GET['login'] ) ) ) );
		} else {
			$args['form']   = 'lost_password';
			$args['fields'] = LLMS_Person_Handler::get_lost_password_fields();
		}

		llms_get_template( 'myaccount/form-lost-password.php', $args );

	}

	/**
	 * Determines what content to output to user based on status
	 *
	 * @since 1.0.0
	 * @since 3.25.1
	 *
	 * @param array $atts Array of user submitted shortcode attributes.
	 * @return void
	 */
	public static function output( $atts ) {

		$atts = shortcode_atts(
			array(
				'login_redirect' => null,
			),
			$atts,
			'lifterlms_my_account'
		);

		lifterlms_student_dashboard( $atts );

	}

}
