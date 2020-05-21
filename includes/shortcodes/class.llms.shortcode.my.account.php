<?php
/**
 * My Account Shortcode
 *
 * Shortcode: [lifterlms_my_account].
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_My_Account class.
 *
 * @since 1.0.0
 * @since 3.25.1 Deprecated method `LLMS_Shortcode_My_Account::lost_password()`.
 * @since [version] Removed previously deprecated method `LLMS_Shortcode_My_Account::lost_password()`.
 */
class LLMS_Shortcode_My_Account {

	/**
	 * Get shortcode content
	 *
	 * @since Unknown
	 *
	 * @param array $atts Shortcode attributes array.
	 * @return array $messages
	 */
	public static function get( $atts ) {
		return LLMS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
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
