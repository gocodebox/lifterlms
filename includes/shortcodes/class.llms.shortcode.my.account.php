<?php
/**
* My Account Shortcode [lifterlms_my_account]
* @since    1.0.0
* @version  3.2.2
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Shortcode_My_Account {

	/**
	* Get shortcode content
	*
	* @param array $atts
	* @return array $messages
	*/
	public static function get( $atts ) {

		return LLMS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );

	}

	/**
	* Lost password template
	* @return   void
	* @since    1.0.0
	* @version  [version]
	*/
	public static function lost_password() {

		$args = array();

		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			$args['form'] = 'reset_password';
			$args['fields'] = LLMS_Person_Handler::get_password_reset_fields( trim( sanitize_text_field( $_GET['key'] ) ), trim( sanitize_text_field( $_GET['login'] ) ) );
		} else {
			$args['form'] = 'lost_password';
			$args['fields'] = LLMS_Person_Handler::get_lost_password_fields();
		}

		llms_get_template( 'myaccount/form-lost-password.php', $args );

	}

	/**
	* Determines what content to output to user based on status
	* @param    array  $atts  array of user submitted shortcode attributes
	* @return   void
	* @since    1.0.0
	* @version  3.2.2
	*/
	public static function output( $atts ) {

		$atts = shortcode_atts( array(

			'login_redirect' => null,

		), $atts, 'lifterlms_my_account' );

		global $wp;

		do_action( 'lifterlms_before_student_dashboard' );

		// If user is not logged in
		if ( ! is_user_logged_in() ) {

			$message = apply_filters( 'lifterlms_my_account_message', '' );

			if ( ! empty( $message ) ) {

				llms_add_notice( $message );
			}

			if ( isset( $wp->query_vars['lost-password'] ) ) {

				self::lost_password();

			} else {

				llms_print_notices();

				llms_get_login_form(
					null,
					apply_filters( 'llms_student_dashboard_login_redirect', $atts['login_redirect'] )
				);

				// can be enabled / disabled on options page.
				if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) {

					llms_get_template( 'global/form-registration.php' );

				}

			}

		} // If user is logged in, display the correct page
		else {

			$tabs = LLMS_Student_Dashboard::get_tabs();

			$current_tab = LLMS_Student_Dashboard::get_current_tab( 'slug' );

			/**
			 * @hooked lifterlms_template_my_account_navigation - 10
			 * @hooked lifterlms_template_student_dashboard_title - 20
			 */
			do_action( 'lifterlms_before_student_dashboard_content' );

			if ( isset( $tabs[ $current_tab ] ) && isset( $tabs[ $current_tab ]['content'] ) && is_callable( $tabs[ $current_tab ]['content'] ) ) {

				call_user_func( $tabs[ $current_tab ]['content'] );

			}

		}

		do_action( 'lifterlms_after_student_dashboard' );

	}

}
