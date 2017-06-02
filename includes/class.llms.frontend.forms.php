<?php
/**
 * Front End Forms Class
 *
 * Class used managing front end facing forms.
 *
 * @since   1.0.0
 * @version 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Frontend_Forms {


	/**
	 * Constructor
	 * initializes the forms methods
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'login' ) );
		add_action( 'init', array( $this, 'voucher_check' ) );
		add_action( 'init', array( $this, 'mark_complete' ) );
		add_action( 'init', array( $this, 'mark_incomplete' ) );

	}

	/**
	 * Get redirect url method
	 * Safe redirect: If there is no referer then redirect user to myaccount
	 *
	 * @param  string $url [sting of url to redirect user to]
	 *
	 * @return string  $redirec [url to redirect user to]
	 */
	public static function llms_get_redirect( $url ) {

		if ( ! empty( $url ) ) {

			$redirect = esc_url( $url );

		} elseif ( wp_get_referer() ) {

			$redirect = esc_url( wp_get_referer() );

		} else {

			$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );

		}

		return $redirect;
	}

	/**
	 * Alert message when course / lesson is restricted by start date.
	 *
	 * @param  string $date [Formatted date for display]
	 *
	 * @return void
	 */
	public function llms_restricted_by_start_date( $date ) {

		llms_add_notice(sprintf(__( 'This content is not available until %s', 'lifterlms' ),
		$date));
	}

	/**
	 * Handle Login Form Submissipn
	 *
	 * @return void
	 * @version  3.0.0
	 */
	public function login() {

		if ( ! empty( $_POST['action'] ) && 'llms_login_user' === $_POST['action'] && ! empty( $_POST['_wpnonce'] ) ) {

			wp_verify_nonce( $_POST['_wpnonce'], 'llms_login_user' );

			$login = LLMS_Person_Handler::login( $_POST );

			// validation or registration issues
			if ( is_wp_error( $login ) ) {
				foreach ( $login->get_error_messages() as $msg ) {
					llms_add_notice( $msg, 'error' );
				}
				return;
			}

			$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : get_permalink( llms_get_page_id( 'myaccount' ) );

			wp_redirect( apply_filters( 'lifterlms_login_redirect', $redirect, $login ) );
			exit;

		}

	}

	/**
	 * Mark Lesson as complete
	 * Complete Lesson form post
	 * Marks lesson as complete and returns completion message to user
	 * Autoadvances to next lesson if completion is succesful
	 * @return void
	 * @since   1.0.0
	 * @version 3.5.1
	 */
	public function mark_complete() {

		$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
		if ( 'POST' !== $request_method ) {
			return;
		}

		// verify nonce
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'mark_complete' ) ) {
			return;
		}

		// required fields
		if ( ! isset( $_POST['mark_complete'] ) || ! isset( $_POST['mark-complete'] ) ) {
			return;
		}

		$lesson_id = absint( $_POST['mark-complete'] );
		if ( ! $lesson_id || ! is_numeric( $lesson_id ) ) {
			llms_add_notice( __( 'An error occurred, please try again.', 'lifterlms' ), 'error' );
		} else {

			if ( llms_mark_complete( get_current_user_id(), $lesson_id, 'lesson', 'lesson_' . $lesson_id ) ) {

				llms_add_notice( sprintf( __( 'Congratulations! You have completed %s', 'lifterlms' ), get_the_title( $lesson_id ) ) );

				if ( apply_filters( 'lifterlms_autoadvance', true ) ) {
					$lesson = new LLMS_Lesson( $lesson_id );
					$next_lesson_id = $lesson->get_next_lesson();
					if ( $next_lesson_id ) {
						wp_redirect( apply_filters( 'llms_lesson_complete_redirect', get_permalink( $next_lesson_id ) ) );
						exit;
					}
				}
			}
		}

	}

	/**
	 * Mark Lesson as incomplete
	 * Incomplete Lesson form post
	 * Marks lesson as incomplete and returns incompletion message to user
	 * @return void
	 * @since   3.5.0
	 * @version 3.5.0
	 */
	public function mark_incomplete() {

		$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
		if ( 'POST' !== $request_method ) {
			return;
		}

		// verify nonce
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'mark_incomplete' ) ) {
			return;
		}

		// required fields
		if ( ! isset( $_POST['mark_incomplete'] ) || ! isset( $_POST['mark-incomplete'] ) ) {
			return;
		}

		$lesson_id = absint( $_POST['mark-incomplete'] );
		if ( ! $lesson_id || ! is_numeric( $lesson_id ) ) {
			llms_add_notice( __( 'An error occurred, please try again.', 'lifterlms' ), 'error' );
		} else {

			// mark incomplete
			$incompleted = llms_mark_incomplete( get_current_user_id(), $lesson_id, 'lesson', 'lesson_' . $lesson_id );

			// if $incompleted is 'yes'
			if ( strcmp( $incompleted, 'yes' ) === 0 ) {

				llms_add_notice( sprintf( __( '%s is now incomplete.', 'lifterlms' ), get_the_title( $lesson_id ) ) );

			}
		}

	}

	/**
	 * Reset password form
	 *
	 * @return void
	 */
	public function reset_password() {

		if ( ! isset( $_POST['llms_reset_password'] ) ) {

			return;
		}

		// process lost password form
		if ( isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) ) {

			wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms-lost_password' );

			LLMS_Shortcode_My_Account::retrieve_password();

		}

		// process reset password form
		if ( isset( $_POST['password_1'] )
			&& isset( $_POST['password_2'] )
			&& isset( $_POST['reset_key'] )
			&& isset( $_POST['reset_login'] )
			&& isset( $_POST['_wpnonce'] )
		) {

			// verify reset key again
			$user = LLMS_Shortcode_My_Account::check_password_reset_key( $_POST['reset_key'], $_POST['reset_login'] );

			if ( is_object( $user ) ) {

				// save these values into the form again in case of errors
				$args['key'] = llms_clean( $_POST['reset_key'] );
				$args['login'] = llms_clean( $_POST['reset_login'] );

				wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms-reset_password' );

				if ( empty( $_POST['password_1'] ) || empty( $_POST['password_2'] ) ) {

					llms_add_notice( __( 'Please enter your password.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				if ( $_POST['password_1'] !== $_POST['password_2'] ) {

					llms_add_notice( __( 'Passwords do not match.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				$errors = new WP_Error();
				do_action( 'validate_password_reset', $errors, $user );

				if ( $errors->get_error_messages() ) {

					foreach ( $errors->get_error_messages() as $error ) {

						llms_add_notice( $error, 'error' );
					}
				}

				if ( 0 == llms_notice_count( 'error' ) ) {

					LLMS_Shortcode_My_Account::reset_password( $user, $_POST['password_1'] );

					do_action( 'lifterlms_person_reset_password', $user );

					wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );

					exit;
				}
			}// End if().
		}// End if().

	}

	/**
	 *
	 * Check voucher and use it if valid
	 *
	 * @return bool
	 */
	public function voucher_check() {

		if ( empty( $_POST['lifterlms_voucher_nonce'] ) || ! wp_verify_nonce( $_POST['lifterlms_voucher_nonce'], 'lifterlms_voucher_check' ) ) {
			return false;
		}

		if ( isset( $_POST['llms_voucher_code'] ) && ! empty( $_POST['llms_voucher_code'] ) ) {

			$voucher = new LLMS_Voucher();
			$redeemed = $voucher->use_voucher( $_POST['llms_voucher_code'], get_current_user_id() );

			if ( is_wp_error( $redeemed ) ) {

				llms_add_notice( $redeemed->get_error_message(), 'error' );

			} else {

				llms_add_notice( __( 'Voucher redeemed sucessfully!', 'lifterlms' ), 'success' );

			}
		}
	}

}

new LLMS_Frontend_Forms();
