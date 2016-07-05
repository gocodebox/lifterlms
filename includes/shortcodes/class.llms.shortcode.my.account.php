<?php

/**
* My Account Shortcode
*
* Sets functionality associated with shortcode [llms_my_account]
*
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
	* Determines what content to output to user based on status
	*
	* @param array $atts
	* @return array $messages
	*/
	public static function output( $atts ) {
		global $lifterlms, $wp;

		do_action( 'lifterlms_before_my_account_shortcode' );

		// If user is not logged in
		if ( ! is_user_logged_in() ) {

			$message = apply_filters( 'lifterlms_my_account_message', '' );

			if ( ! empty( $message ) ) {

				llms_add_notice( $message );
			}

			if ( isset( $wp->query_vars['lost-password'] ) ) {

				self::lost_password();
			} else {

				llms_get_template( 'myaccount/form-login.php' );

				//can be enabled / disabled on options page.
				if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' || get_query_var( 'product-id' ) ) {
					llms_get_template( 'myaccount/form-registration.php' );
				}

			}

		} // If user is logged in, display the correct page
		else {

			// edit account page
			if ( isset( $wp->query_vars['edit-account'] ) ) {

				self::edit_account();
			} // vouchers redemption
			elseif ( isset( $wp->query_vars['redeem-voucher'] ) ) {

				self::redeem_voucher();

			} // default
			else {

				self::my_account( $atts );

			}
		}

		do_action( 'lifterlms_after_my_account_shortcode' );
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
			'user' => get_user_by( 'id', get_current_user_id() ),
		) );
	}

	/**
	 * Redeem Voucher template
	 * @return void
	 */
	private static function redeem_voucher() {

		llms_get_template( 'myaccount/form-redeem-voucher.php', array(
			'user' => get_user_by( 'id', get_current_user_id() ),
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
			if ( is_object( $user ) ) {
				$args['form'] = 'reset_password';
				$args['key'] = esc_attr( $_GET['key'] );
				$args['login'] = esc_attr( $_GET['login'] );
			}

		} elseif ( isset( $_GET['reset'] ) ) {

			llms_add_notice( apply_filters( 'lifterlms_password_reset_login_message', __( 'Your password has been reset.', 'lifterlms' )
			. ' <a href="' . get_permalink( llms_get_page_id( 'myaccount' ) ) . '">' . __( 'Log in', 'lifterlms' ) . '</a>' ) );

		}

		llms_get_template( 'myaccount/form-lost-password.php', $args );
	}

	/**
	 * Handles sending password retrieval email to customer.
	 *
	 * Based on retrieve_password() in core wp-login.php
	 *
	 * @access public
	 * @uses $wpdb WordPress Database object
	 * @return bool True: when finish. False: on error
	 */
	public static function retrieve_password() {
		global $wpdb, $wp_hasher;

		if ( empty( $_POST['user_login'] ) ) {

			llms_add_notice( __( 'Enter a username or e-mail address.', 'lifterlms' ), 'error' );

		} else {
			// Check on username first, as customers can use emails as usernames.
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is emaill and lookup user based on email.
		if ( ! $user_data && is_email( $_POST['user_login'] ) && apply_filters( 'lifterlms_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
		}

		do_action( 'lostpassword_post' );

		if ( ! $user_data ) {
			llms_add_notice( __( 'Invalid username or e-mail.', 'lifterlms' ), 'error' );
			return false;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			llms_add_notice( __( 'Password reset is not allowed for this user', 'lifterlms' ), 'error' );

			return false;

		} elseif ( is_wp_error( $allow ) ) {

			llms_add_notice( $allow->get_error_message(), 'error' );

			return false;
		}

		$key = wp_generate_password( 20, false );

		do_action( 'retrieve_password_key', $user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}

		$hashed = $wp_hasher->HashPassword( $key );

		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
		$mailer = LLMS()->mailer();
		do_action( 'lifterlms_reset_password_notification', $user_login, $key );

		llms_add_notice( __( 'Check your e-mail for the confirmation link.', 'lifterlms' ) );
		return true;
	}

	/**
	 * Check Password Reset Key
	 * @param  string $key   	[password reset key]
	 * @param  string $login 	[wp_user query return]
	 * @return array        	[wp_user query return]
	 */
	public static function check_password_reset_key( $key, $login ) {
		global $wpdb, $wp_hasher;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) ) {
			llms_add_notice( __( 'Invalid key', 'lifterlms' ), 'error' );
			return false;
		}

		if ( empty( $login ) || ! is_string( $login ) ) {
			llms_add_notice( __( 'Invalid key', 'lifterlms' ), 'error' );
			return false;
		}

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = %s", $login ) );

		if ( ! empty( $user ) ) {
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}

			$valid = $wp_hasher->CheckPassword( $key, $user->user_activation_key );
		}

		if ( empty( $user ) || empty( $valid ) ) {
			llms_add_notice( __( 'Invalid key', 'lifterlms' ), 'error' );
			return false;
		}

		return $user;
	}

	/**
	 * Reset Password
	 * @param  object $user     [user object]
	 * @param  string $new_pass [new password]
	 * @return void
	 */
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		wp_password_change_notification( $user );
	}

}
