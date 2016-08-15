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
class LLMS_Frontend_Forms
{

	/**
	 * Constructor
	 * initializes the forms methods
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'login' ) );





		add_action( 'template_redirect', array( $this, 'save_account_details' ) );
		add_action( 'init', array( $this, 'apply_coupon' ) );
		add_action( 'init', array( $this, 'remove_coupon' ) );
		add_action( 'init', array( $this, 'coupon_check' ) );
		add_action( 'init', array( $this, 'voucher_check' ) );
		add_action( 'init', array( $this, 'user_registration' ) );
		add_action( 'init', array( $this, 'reset_password' ) );
		add_action( 'init', array( $this, 'mark_complete' ) );
		add_action( 'init', array( $this, 'take_quiz' ) );



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
	 * Mark Lesson as complete
	 * Complete Lesson form post
	 *
	 * Marks lesson as complete and returns completion message to user
	 *
	 * @return void
	 */
	public function mark_complete() {

		global $wpdb;

		$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
		if ('POST' !== $request_method) {
			return;
		}

		if ( ! isset( $_POST['mark_complete'] ) || empty( $_POST['_wpnonce'] )) {
			return;
		}

		if (isset( $_POST['mark-complete'] )) {
			$lesson = new LLMS_Lesson( $_POST['mark-complete'] );
			$lesson->mark_complete( get_current_user_id() );
		}

	}

	/**
	 * Mark Course complete form post
	 * Called by lesson complete.
	 *
	 * If all lessons are complete in course mark course as complete
	 *
	 * @param  int $user_id [ID of the current user]
	 * @param  int $lesson_id [ID of the current lesson]
	 *
	 * @return void
	 */
	function mark_course_complete( $user_id, $lesson_id ) {

		global $wpdb;

		$lesson = new LLMS_Lesson( $lesson_id );
		$course_id = $lesson->get_parent_course();

		$course = new LLMS_Course( $course_id );
		$course_completion = $course->get_percent_complete();

		$user = new LLMS_Person( $user_id );

		if ($course_completion == '100') {

			$key = '_is_complete';
			$value = 'yes';

			$user_postmetas = $user->get_user_postmeta_data( $user_id, $course->id );
			if ( ! empty( $user_postmetas['_is_complete'] )) {
				if ($user_postmetas['_is_complete']->meta_value === 'yes') {
					return;
				}
			}

			$update_user_postmeta = $wpdb->insert($wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id' => $user_id,
					'post_id' => $course->id,
					'meta_key' => $key,
					'meta_value' => $value,
					'updated_date' => current_time( 'mysql' ),
				)
			);

			do_action( 'llms_course_completed', $user_id, $course->id );

		}
	}

	/**
	 * mark section complete
	 * Called by mark_lesson_complte
	 *
	 * If all lessons in section complete mark section as complete.
	 *
	 * @param  int $user_id [ID of the current user]
	 * @param  int $lesson_id [ID of the current lesson]
	 *
	 * @return void
	 */
	public function mark_section_complete( $user_id, $lesson_id ) {

		global $wpdb;

		$lesson = new LLMS_Lesson( $lesson_id );
		$course_id = $lesson->get_parent_course();
		$course = new LLMS_Course( $course_id );
		$course_syllabus = $course->get_syllabus();
	}

	/**
	 * Processes orders that, calculated with a coupon, result in a free amount
	 * and bypass the payment gateway.
	 */
	public function process_free_order() {

		/** Coupon data */
		$coupon = LLMS()->session->get( 'llms_coupon', array() );
		/** Order data */
		$order = LLMS()->session->get( 'llms_order', array() );
		/** Don't do anything if no coupon has been applied */
		if ( ! isset( $coupon->id ) || ! $coupon->id) {
			return false;
		}

		//don't do anything if coupon amount does not = 100% off.
		if (($coupon->amount != '100' && $coupon->type === 'percent')
			|| ($coupon->type === 'dollar' && (($order->total - $coupon->amount) > 0))
		) {
			return false;
		}

		$coupon_type = get_post_meta( $coupon->id, '_llms_discount_type', true );
		$coupon_amount = get_post_meta( $coupon->id, '_llms_coupon_amount', true );
		$coupon_is_valid = true;

		/** Check if coupon is valid and actually results in 0 total */
		if ($coupon_amount !== $coupon->amount) {

			$coupon_is_valid = false;
		}
		if ($coupon_type == 'percent' && $coupon_amount != 100) {

			$coupon_is_valid = false;
		} elseif ($coupon_type == 'dollar' && (($order->total - $coupon_amount) > 0)) {

			$coupon_is_valid = false;
		}
		if ( ! $coupon_is_valid) {
			/** Clear session */
			LLMS()->session->set( 'llms_coupon', '' );
			return $coupon->coupon_code;
		}

		/** Insert order into database */
		$lifterlms_checkout = LLMS()->checkout();
		$handle->process_order( $order );
		$handle->update_order( $order );

		/** Clear session */
		unset( LLMS()->session->llms_coupon );
		unset( LLMS()->session->llms_order );

		/** Redirect to success page */
		do_action( 'lifterlms_order_process_success', $order );

	}

	/**
	 * Apply Coupon to order form post
	 * Applies coupon value to session
	 * Sets price html output to discounted value
	 *
	 * @return void
	 */
	public function apply_coupon() {
		if ( ! isset( $_POST['llms_apply_coupon'] )) {
			return;
		}

		$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
		if ('POST' !== $request_method) {
			return;
		}

		if (empty( $_POST['_wpnonce'] )) {

			return;
		}

		wp_verify_nonce( $_POST['_wpnonce'], 'llms-checkout-coupon' );

		$coupon = $this->check_coupon();

		if ( $coupon ) {

			LLMS()->session->set( 'llms_coupon', $coupon );
			return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> has been applied to your order.', 'lifterlms' ), $coupon->coupon_code ), 'success' );

		}

	}

	public function remove_coupon() {

		if ( ! isset( $_POST['llms_remove_coupon'] )) {
			return;
		}

		LLMS()->session->set( 'llms_coupon', null );

		return llms_add_notice( __( 'The coupon has been removed from your order.', 'lifterlms' ), 'success' );
	}

	private function check_coupon() {
		$coupon = new stdClass();
		$errors = new WP_Error();

		$coupon->user_id = (int) get_current_user_id();
		$coupon->product_id = $_POST['product_id'];

		if ( empty( $coupon->user_id ) ) {
			//return;
		}

		$coupon->coupon_code = llms_clean( $_POST['coupon_code'] );

		$args = array(
				'posts_per_page' => 1,
				'post_type' => 'llms_coupon',
				'nopaging' => true,
				'post_status' => 'publish',
				'meta_query' => array(
						array(
								'key' => '_llms_coupon_title',
								'value' => $coupon->coupon_code,
						),
				),
		);
		$coupon_post = get_posts( $args );

		if ( empty( $coupon_post ) ) {
			return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> was not found.', 'lifterlms' ), $coupon->coupon_code ), 'error' );
		} else {
			$products = get_post_meta( $coupon_post[0]->ID, '_llms_coupon_products', true );
			if ( ! empty( $products ) && ! in_array( $coupon->product_id, $products )) {
				return llms_add_notice( sprintf( __( "Coupon code <strong>%s</strong> can't be applied to this product.", 'lifterlms' ), $coupon->coupon_code ), 'error' );
			}
		}

		foreach ($coupon_post as $cp) {
			$coupon->id = $cp->ID;
		}

		//get coupon metadata
		$coupon_meta = get_post_meta( $coupon->id );

		$coupon->type = ! empty( $coupon_meta['_llms_discount_type'][0] ) ? $coupon_meta['_llms_discount_type'][0] : '';
		$coupon->amount = ! empty( $coupon_meta['_llms_coupon_amount'][0] ) ? $coupon_meta['_llms_coupon_amount'][0] : '';

		$coupon->limit = $coupon_meta['_llms_usage_limit'][0] !== '' ? $coupon_meta['_llms_usage_limit'][0] : 'unlimited';

		$coupon->title = ! empty( $coupon_meta['_llms_coupon_title'][0] ) ? $coupon_meta['_llms_coupon_title'][0] : '';

		if ($coupon->type == 'percent') {
			$coupon->name = ($coupon->title . ': ' . $coupon->amount . '% coupon');
		} elseif ($coupon->type == 'dollar') {
			$coupon->name = ($coupon->title . ': ' . get_lifterlms_currency_symbol() . $coupon->amount . ' coupon');
		}

		//if coupon limit is not unlimited deduct 1 from limit
		if (isset( $coupon->limit )) {
			if ($coupon->limit !== 'unlimited') {

				if ($coupon->limit <= 0) {
					return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> cannot be applied to this order.', 'lifterlms' ), $coupon->coupon_code ), 'error' );
				}

				//remove coupon limit
				$coupon->limit = ($coupon->limit - 1);

			}
		}

		return $coupon;
	}

	/**
	 * Compares coupon code to product
	 * If coupon was applied to a different product it unsets the coupon.
	 * @return [type] [description]
	 */
	public function coupon_check() {

		if (empty( $_GET ) || empty( $_GET['product-id'] )) {
			return;
		}

		//if product_id associated with coupon does not match the current product then unset the coupon
		if (LLMS()->session->llms_coupon && $_GET['product-id'] != LLMS()->session->llms_coupon->product_id) {

			unset( LLMS()->session->llms_coupon );

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

			$code = llms_clean( $_POST['llms_voucher_code'] );
			$voucher = new LLMS_Voucher();
			$voucher->use_voucher( $code, get_current_user_id() );

		}
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
				foreach( $login->get_error_messages() as $msg ) {
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
	 * User Registration form
	 *
	 * @return void
	 */
	public function user_registration() {

		if ( ! empty( $_POST['register'] )) {

			wp_verify_nonce( $_POST['register'], 'lifterlms-register' );

			do_action( 'lifterlms_new_user_registration', array( $this, $_POST ) );

			$new_person = LLMS_Person::create_new_person();

			if (is_wp_error( $new_person )) {

				llms_add_notice( $new_person->get_error_message(), 'error' );
				return;

			}

			llms_set_person_auth_cookie( $new_person );

			// Redirect
			if (wp_get_referer()) {

				$redirect = esc_url( wp_get_referer() );
			} else {

				$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );

			}

			// Check if voucher exists and if valid and use it
			if (isset( $_POST['llms_voucher_code'] ) && ! empty( $_POST['llms_voucher_code'] )) {
				$code = llms_clean( $_POST['llms_voucher_code'] );

				$voucher = new LLMS_Voucher();
				$voucher->use_voucher( $code, $new_person, false );

				if ( ! empty( $_POST['product_id'] )) {
					$product_id = $_POST['product_id'];
					$valid = $voucher->is_product_to_voucher_link_valid( $code, $product_id );

					if ($valid) {
						wp_redirect( apply_filters( 'lifterlms_registration_redirect', $redirect, $new_person ) );
						exit;
					}
				}
			}

			if ( ! empty( $_POST['product_id'] )) {

				$product_id = $_POST['product_id'];

				$product = new LLMS_Product( $product_id );
				$single_price = $product->get_single_price();
				$rec_price = $product->get_recurring_price();

				if ($single_price > 0 || $rec_price > 0) {

					$checkout_url = get_permalink( llms_get_page_id( 'checkout' ) );
					$checkout_redirect = add_query_arg( 'product-id', $product_id, $checkout_url );

					wp_redirect( apply_filters( 'lifterlms_checkout_redirect', $checkout_redirect ) );
					exit;
				} else {
					$checkout_url = get_permalink( $product_id );

					wp_redirect( apply_filters( 'lifterlms_checkout_redirect', $checkout_url ) );
					exit;
				}
			} else {
				wp_redirect( apply_filters( 'lifterlms_registration_redirect', $redirect, $new_person ) );
				exit;
			}

		}

	}

}

new LLMS_Frontend_Forms();
