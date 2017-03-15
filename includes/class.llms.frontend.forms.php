<?php
if ( ! defined( 'ABSPATH' )) {
	exit;
}

/**
 * Front End Forms Class
 *
 * Class used managing front end facing forms.
 *
 * @version 1.0
 * @author  codeBOX
 * @project lifterLMS
 */
class LLMS_Frontend_Forms {


	/**
	 * Constructor
	 * initializes the forms methods
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'login' ) );

		add_action( 'template_redirect', array( $this, 'save_account_details' ) );
		add_action( 'init', array( $this, 'voucher_check' ) );
		add_action( 'init', array( $this, 'reset_password' ) );
		add_action( 'init', array( $this, 'mark_complete' ) );
		add_action( 'init', array( $this, 'mark_incomplete' ) );
		add_action( 'init', array( $this, 'take_quiz' ) );

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

		if ( ! empty( $url )) {

			$redirect = esc_url( $url );

		} elseif (wp_get_referer()) {

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

		if ( ! isset( $_POST['llms_reset_password'] )) {

			return;
		}

		// process lost password form
		if (isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] )) {

			wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms-lost_password' );

			LLMS_Shortcode_My_Account::retrieve_password();

		}

		// process reset password form
		if (isset( $_POST['password_1'] )
			&& isset( $_POST['password_2'] )
			&& isset( $_POST['reset_key'] )
			&& isset( $_POST['reset_login'] )
			&& isset( $_POST['_wpnonce'] )
		) {

			// verify reset key again
			$user = LLMS_Shortcode_My_Account::check_password_reset_key( $_POST['reset_key'], $_POST['reset_login'] );

			if (is_object( $user )) {

				// save these values into the form again in case of errors
				$args['key'] = llms_clean( $_POST['reset_key'] );
				$args['login'] = llms_clean( $_POST['reset_login'] );

				wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms-reset_password' );

				if (empty( $_POST['password_1'] ) || empty( $_POST['password_2'] )) {

					llms_add_notice( __( 'Please enter your password.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				if ($_POST['password_1'] !== $_POST['password_2']) {

					llms_add_notice( __( 'Passwords do not match.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				$errors = new WP_Error();
				do_action( 'validate_password_reset', $errors, $user );

				if ($errors->get_error_messages()) {

					foreach ($errors->get_error_messages() as $error) {

						llms_add_notice( $error, 'error' );
					}

				}

				if (0 == llms_notice_count( 'error' )) {

					LLMS_Shortcode_My_Account::reset_password( $user, $_POST['password_1'] );

					do_action( 'lifterlms_person_reset_password', $user );

					wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );

					exit;
				}
			}

		}

	}

	/**
	 * Account details form
	 *
	 * @return void
	 */
	public function save_account_details() {

		if ('POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] )) {
			return;
		}

		if (empty( $_POST['action'] ) || ('save_account_details' !== $_POST['action']) || empty( $_POST['_wpnonce'] )) {
			return;
		}

		wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' );

		$update = true;
		$errors = new WP_Error();
		$user = new stdClass();

		$user->ID = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ($user->ID <= 0) {
			return;
		}

		$account_first_name = ! empty( $_POST['account_first_name'] ) ? llms_clean( $_POST['account_first_name'] ) : '';
		$account_last_name = ! empty( $_POST['account_last_name'] ) ? llms_clean( $_POST['account_last_name'] ) : '';
		$account_email = ! empty( $_POST['account_email'] ) ? sanitize_email( $_POST['account_email'] ) : '';
		$pass1 = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
		$pass2 = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';

		$user->first_name = $account_first_name;
		$user->last_name = $account_last_name;
		$user->user_email = $account_email;
		$user->display_name = $user->first_name;

		if ('yes' === get_option( 'lifterlms_registration_require_address' )) {
			$billing_address_1 = ! empty( $_POST['billing_address_1'] ) ? llms_clean( $_POST['billing_address_1'] ) : '';
			$billing_address_2 = ! empty( $_POST['billing_address_2'] ) ? llms_clean( $_POST['billing_address_2'] ) : '';
			$billing_city = ! empty( $_POST['billing_city'] ) ? llms_clean( $_POST['billing_city'] ) : '';
			$billing_state = ! empty( $_POST['billing_state'] ) ? llms_clean( $_POST['billing_state'] ) : '';
			$billing_zip = ! empty( $_POST['billing_zip'] ) ? llms_clean( $_POST['billing_zip'] ) : '';
			$billing_country = ! empty( $_POST['billing_country'] ) ? llms_clean( $_POST['billing_country'] ) : '';
		}

		if ('yes' == get_option( 'lifterlms_registration_add_phone' )) {
			$phone = ( ! empty( $_POST['phone'] ) ) ? llms_clean( $_POST['phone'] ) : '';
		}

		if ($pass1) {
			$user->user_pass = $pass1;
		}

		if (empty( $account_first_name ) || empty( $account_last_name )) {

			llms_add_notice( __( 'Please enter your name.', 'lifterlms' ), 'error' );

		}

		if (empty( $account_email ) || ! is_email( $account_email )) {

			llms_add_notice( __( 'Please provide a valid email address.', 'lifterlms' ), 'error' );

		} elseif (email_exists( $account_email ) && $account_email !== $current_user->user_email) {

			llms_add_notice( __( 'The email entered is associated with another account.', 'lifterlms' ), 'error' );

		}

		if ( ! empty( $pass1 ) && empty( $pass2 )) {

			llms_add_notice( __( 'Please re-enter your password.', 'lifterlms' ), 'error' );

		} elseif ( ! empty( $pass1 ) && $pass1 !== $pass2) {

			llms_add_notice( __( 'Passwords do not match.', 'lifterlms' ), 'error' );

		} elseif ('yes' === get_option( 'lifterlms_registration_require_address' )) {
			if (empty( $billing_address_1 )) {
				llms_add_notice( __( 'Please enter your billing address.', 'lifterlms' ), 'error' );
			}
			if (empty( $billing_city )) {
				llms_add_notice( __( 'Please enter your billing city.', 'lifterlms' ), 'error' );
			}
			if (empty( $billing_state )) {
				llms_add_notice( __( 'Please enter your billing state.', 'lifterlms' ), 'error' );
			}
			if (empty( $billing_zip )) {
				llms_add_notice( __( 'Please enter your billing zip code.', 'lifterlms' ), 'error' );
			}
			if (empty( $billing_country )) {
				llms_add_notice( __( 'Please enter your billing country.', 'lifterlms' ), 'error' );
			}
		}

		do_action_ref_array( 'user_profile_update_errors', array( &$errors, $update, &$user ) );

		if ($errors->get_error_messages()) {

			foreach ($errors->get_error_messages() as $error) {

				llms_add_notice( $error, 'error' );

			}

		}

		// if no errors were returned save the data
		if (llms_notice_count( 'error' ) == 0) {

			wp_update_user( $user );

			//if address option is set then update address fields
			if ('yes' === get_option( 'lifterlms_registration_require_address' )) {

				$person_address = apply_filters('lifterlms_new_person_address', array(
					'llms_billing_address_1' => $billing_address_1,
					'llms_billing_address_2' => $billing_address_2,
					'llms_billing_city' => $billing_city,
					'llms_billing_state' => $billing_state,
					'llms_billing_zip' => $billing_zip,
					'llms_billing_country' => $billing_country,
				));

				foreach ($person_address as $key => $value) {
					update_user_meta( $user->ID, $key, $value );
				}
			}

			if ('yes' == get_option( 'lifterlms_registration_add_phone' )) {
				update_user_meta( $user->ID, 'llms_phone', $phone );
			}

			llms_add_notice( __( 'Account details were changed successfully.', 'lifterlms' ) );

			do_action( 'lifterlms_save_account_details', $user->ID, $_POST );

			wp_safe_redirect( get_permalink( llms_get_page_id( 'myaccount' ) ) );

			exit;
		}

	}

	/**
	 * Take quiz submit handler from lesson
	 * Redirect user to quiz if quiz is available for lesson.
	 * Creates session object llms_quiz
	 *
	 * @return void
	 */
	public function take_quiz() {

		$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
		if ('POST' !== $request_method) {
			return;
		}

		if ( ! isset( $_POST['take_quiz'] ) || empty( $_POST['_wpnonce'] )) {
			return;
		}

		if (isset( $_POST['take_quiz'] )) {

			//create quiz session object
			$quiz = new stdClass();
			$quiz->id = $_POST['quiz_id'];
			$quiz->assoc_lesson = $_POST['associated_lesson'];
			$quiz->user_id = (int) get_current_user_id();

			LLMS()->session->set( 'llms_quiz', $quiz );

			//redirect user to quiz page
			$redirect = get_permalink( $_POST['quiz_id'] );
			wp_redirect( apply_filters( 'lifterlms_lesson_start_quiz_redirect', $redirect ) );
			exit;
		}
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
