<?php

/**
* My Account Shortcode
*
* TODO: description
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
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
	* Determines what content to output to user absed on status
	*
	* @param array $atts
	* @return array $messages
	*/
	public static function output( $atts ) {
		global $lifterlms, $wp;

		// If user is not logged in
		if ( ! is_user_logged_in() ) {

			$message = apply_filters( 'lifterlms_my_account_message', '' );

			if ( ! empty( $message ) ) {

				llms_add_notice( $message );
			}

			if ( isset( $wp->query_vars['lost-password'] ) ) {

				self::lost_password();
			}

			else {

				llms_get_template( 'myaccount/form-login.php' );
				llms_get_template( 'myaccount/form-registration.php' );

			}

		}

		// If user is logged in, display the correct page
		else {

			if ( isset( $wp->query_vars['edit-account'] ) ) {

				self::edit_account();
			}

			else {

				self::my_account( $atts );

			}
		}
	}

	/**
	* My Account page template
	*
	* @param array $atts
	* @return void
	*/
	private static function my_account( $atts ) {

		llms_get_template( 'myaccount/my-account.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
		) );
	}

	/**
	* Edit Account template
	*
	* @return void
	*/
	private static function edit_account() {
		llms_get_template( 'myaccount/form-edit-account.php', array(
			'user' => get_user_by( 'id', get_current_user_id() )
		) );
	}

	/**
	* Lost password template
	*
	* @return void
	*/
	public static function lost_password() {
		global $post;

		// arguments to pass to template
		$args = array( 'form' => 'lost_password' );

		// process reset key / login from email confirmation link
		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

			$user = self::check_password_reset_key( $_GET['key'], $_GET['login'] );

			// reset key / login is correct, display reset password form with hidden key / login values
			if( is_object( $user ) ) {
				$args['form'] = 'reset_password';
				$args['key'] = esc_attr( $_GET['key'] );
				$args['login'] = esc_attr( $_GET['login'] );
			}

		}

		elseif ( isset( $_GET['reset'] ) ) {

			llms_add_notice( __( 'Your password has been reset.', 'lifterlms' )
				. ' <a href="' . get_permalink( llms_get_page_id( 'myaccount' ) ) . '">' . __( 'Log in', 'lifterlms' ) . '</a>' );

		}

		llms_get_template( 'myaccount/form-lost-password.php', $args );
	}

}
