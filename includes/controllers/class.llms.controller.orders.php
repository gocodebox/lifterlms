<?php
/**
 * Order processing and related actions controller
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
class LLMS_Controller_Orders {

	public function __construct() {

		// form actions
		add_action( 'init', array( $this, 'create_pending_order' ) );
		add_action( 'init', array( $this, 'confirm_order' ) );

		// called by a gateway's "process_payment" method and should redirect user to a confirmation page
		add_action( 'lifterlms_payment_processing_redirect', array( $this, 'processing_redirect' ), 10, 1 );
		add_action( 'lifterlms_order_process_begin', array( $this, 'processing_redirect' ), 10, 1 ); // @todo deprecate

		// called by a gateway's "complete_payment" method and redirects user to the course, membership, etc...
		// this action results in a redirect and should not be used to watch for "completion" of an order
		// if you're looking to watch for order completion, use "status" actions found in the "LLMS_Order::update_status()" method instead
		add_action( 'lifterlms_payment_process_success', array( $this, 'processing_success_redirect' ), 10, 1 );
		add_action( 'lifterlms_order_process_success', array( $this, 'processing_success_redirect' ), 10, 1 ); // @todo deprecate

		// status changes for orders to enroll students and trigger completion actions
		add_action( 'lifterlms_order_status_completed', array( $this, 'order_complete' ), 10, 1 );
		add_action( 'lifterlms_order_status_active', array( $this, 'order_complete' ), 10, 1 );

		// status changes for orders to unenroll students upon purchase
		add_action( 'lifterlms_order_status_refunded', array( $this, 'unenroll' ), 10, 1 );
		add_action( 'lifterlms_order_status_cancelled', array( $this, 'unenroll' ), 10, 1 );
		add_action( 'lifterlms_order_status_expired', array( $this, 'unenroll' ), 10, 1 );

	}


	/**
	 * Format a price with default settings for this class
	 * @param  float $price price to format
	 * @return string
	 * @since  3.0.0
	 */
	public function format_price( $price ) {

		return llms_price( $price, array(
			'with_currency' => false,
			'decimal_places' => 2,
			'trim_zeros' => false,
		) );

	}


	/**
	 * Confirm order form post
	 * User clicks confirm order
	 *
	 * Executes payment gateway confirm order method and completes order.
	 * Redirects user to appropriate page / post
	 *
	 * @return void
	 *
	 * @version 3.0.0
	 */
	public function confirm_order() {

		if ( 'POST' !== strtoupper( getenv( 'REQUEST_METHOD' ) ) || empty( $_POST['action'] ) || 'llms_confirm_order' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) ) { return; }

		// noonnce the post
		wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms_confirm_order' );

		$session = LLMS()->session->get( 'llms_order' );
		if ( empty( $session ) ) {
			return;
		}

		$order = llms_get_order_by_key( $session );

		$available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways();
		$result = $available_gateways[ $order->get_payment_gateway() ]->confirm_payment( $_REQUEST );
		$complete = $available_gateways[ $order->get_payment_gateway() ]->complete_payment( $result, $order );

	}



	/**
	 * Handle form submission of the checkout / payment form
	 *
	 * 		1. Logs in or Registers a user
	 *   	2. Validates all fields
	 *    	3. Handles coupon pricing adjustments
	 *    	4. Creates a PENDING llms_order
	 *
	 * 		If errors, returns error on screen to user
	 * 		If success, passes to the selected gateways "process_payment" method
	 * 			the process_payment method should complete by returning an error or
	 * 			triggering the "lifterlms_process_payment_redirect"
	 *
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public function create_pending_order() {

		// only run this if the correct action has been posted
		if ( 'POST' !== strtoupper( getenv( 'REQUEST_METHOD' ) ) || empty( $_POST['action'] ) || ( 'create_pending_order' !== $_POST['action'] ) || empty( $_POST['_wpnonce'] ) ) {
			return;
		}

		// nonce the post
		wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms_create_pending_order' );

		/**
		 * Do a bunch of validation
		 */

		if ( empty( $_POST['product_id'] ) ) {
			llms_add_notice( __( 'Missing a product id.', 'lifterlms' ), 'error' );
		}

		if ( empty( $_POST['payment_option'] ) ) {
			llms_add_notice( __( 'No payment option selected.', 'lifterlms' ), 'error' );
		}

		if ( empty( $_POST['payment_method'] ) ) {
			llms_add_notice( __( 'No payment method selected.', 'lifterlms' ), 'error' );
		}

		// check out & validate the payment method
		$payment_method_data = explode( '_', $_POST['payment_method'] );
		$payment_type = $payment_method_data[0];
		if ( count( $payment_method_data ) > 1 ) {
			$payment_gateway = $payment_method_data[1];
			$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $payment_gateway );
			if ( ! $gateway || ! $gateway->is_available() ) {
				llms_add_notice( __( 'Invalid payment gateway selected.', 'lifterlms' ), 'error' );
			}

		} else {

			$payment_gateway = __( 'Unknown', 'lifterlms' );

		}

		// if coupon code submitted, validate it
		if ( isset( $_POST['coupon_code'] ) ) {
			$coupon = new LLMS_Coupon( $_POST['coupon_code'] );
			$valid = $coupon->is_valid( $_POST['product_id'] );
			if ( is_wp_error( $valid ) ) {

				llms_add_notice( $valid->get_error_message(), 'error' );
				return;

			}
		} else {
			$coupon = false;
		}

		// credit card validations for creditcard gateways
		if ( 'creditcard' === $payment_type && empty( $_POST['use_existing_card'] ) ) {

			if (empty( $_POST['cc_type'] )) {
				llms_add_notice( __( 'Please select a credit card type.', 'lifterlms' ), 'error' );
			}
			if (empty( $_POST['cc_number'] )) {
				llms_add_notice( __( 'Please enter a credit card number.', 'lifterlms' ), 'error' );
			}
			if (empty( $_POST['cc_exp_month'] )) {
				llms_add_notice( __( 'Please select an expiration month.', 'lifterlms' ), 'error' );
			}
			if (empty( $_POST['cc_exp_year'] )) {
				llms_add_notice( __( 'Please select an expiration year.', 'lifterlms' ), 'error' );
			}
			if (empty( $_POST['cc_cvv'] )) {
				llms_add_notice( __( 'Please enter the credit card CVV2 number', 'lifterlms' ), 'error' );
			}

		}

		// return if there were any noticies
		if ( llms_notice_count( 'error' ) ) {
			return;
		}

		// if there's no user AND alternate checkout is enabled
		// attempt to either login or register the user
		if ( ! is_user_logged_in() && llms_is_alternative_checkout_enabled() ) {

			if ( isset( $_POST['llms-login'] ) ) {

				try {

					$user = LLMS_Person::login_user();

					if ( is_wp_error( $user ) ) {

						llms_add_notice( $user->get_error_message(), 'error' );

						return;

					}

					wp_set_current_user( $user->ID );
					$user_id = get_current_user_id();

				} catch ( Exception $e ) {

					llms_add_notice( apply_filters( 'login_errors', $e->getMessage() ), 'error' );

					return;
				}

			} elseif ( $_POST['llms-registration'] ) {

				$user_id = LLMS_Person::create_new_person();

				if ( is_wp_error( $user_id ) ) {

					llms_add_notice( $user_id->get_error_message(), 'error' );

					return;

				}

				llms_set_person_auth_cookie( $user_id );

			}

		} else {

			$user_id = get_current_user_id();

		}

		// can't proceed without a user id
		if ( empty( $user_id ) ) {

			llms_add_notice( __( 'You must login or register to purchase this product', 'lifterlms' ), 'error' );
			return;

		}

		// make sure the user isn't already enrolled in the course or membership
		if ( llms_is_user_enrolled( $user_id, $_POST['product_id'] ) ) {

			llms_add_notice( __( 'You already have access to this product!' ), 'error' );
			return;

		}

		// var_dump( $_POST );

		// create a new order & fill it up with all the data
		$order = new LLMS_Order();

		$product = new LLMS_Product( $_POST['product_id'] );

		// product data
		$order->product_id = $_POST['product_id']; // already validated
		$order->product_title = $product->get_title();
		$order->product_sku = $product->get_sku();
		$order->product_type = $product->get_type();

	 	// payment options
		$payment_option_data = explode( '_', $_POST['payment_option'] );
		$order->type = $payment_option_data[0];
		$payment_option_id = $payment_option_data[1];

		// subscription data
		if ( 'recurring' === $order->get_type() ) {

			$subscriptions = $product->get_subscriptions();

			// validate the subscription plan
			if ( ! isset( $subscriptions[ $payment_option_id ] ) ) {

				llms_add_notice( __( 'The selected subscription is invalid.' ), 'error' ); // this should never happen
				return;

			} else {

				$subscription_data = $subscriptions[ $payment_option_id ];
				$order->billing_start_date = $product->get_recurring_next_payment_date( $subscription_data );
				$order->billing_period = $subscription_data['billing_period'];
				$order->billing_frequency = $subscription_data['billing_freq'];
				$order->billing_cycle = $subscription_data['billing_cycle'];
				$order->subscription_last_sync = current_time( 'timestamp' );

			}

		}

		/**
		 * Set totals depending on discounts
		 * Set coupon and sale fields where applicable
		 */

		// if a valid coupon is used
		if ( $coupon ) {

			// set all coupon data
			$order->coupon_id = $coupon->get_id();
			$order->coupon_code = $coupon->get_code();
			$order->coupon_type = $coupon->get_discount_type();
			$order->discount_type = 'coupon';

			if ( 'single' === $order->get_type() ) {

				$order->original_total = $this->format_price( $product->get_regular_price() );
				$order->total = $this->format_price( $product->get_coupon_adjusted_price( $product->get_single_price(), $coupon, 'single' ) );
				$order->coupon_amount = $coupon->get_formatted_single_amount();
				$order->coupon_value = $this->format_price( floatval( $order->get_original_total() ) - floatval( $order->get_total() ) );

			} elseif ( 'recurring' === $order->get_type() ) {

				$order->first_payment_orignal_total = $this->format_price( $subscription_data['first_payment'] );
				$order->first_payment_total = $this->format_price( $product->get_coupon_adjusted_price( $subscription_data['first_payment'], $coupon, 'first' ) );
				$order->coupon_first_payment_amount = $coupon->get_formatted_recurring_first_payment_amount();
				$order->coupon_first_payment_value = floatval( $order->get_first_payment_original_total() ) - floatval( $order->get_first_payment_total() );

				$order->recurring_payment_original_total = $this->format_price( $subscription_data['sub_price'] );
				$order->recurring_payment_total = $this->format_price( $product->get_coupon_adjusted_price( $subscription_data['sub_price'], $coupon, 'recurring' ) );
				$order->coupon_recurring_payment_amount = $coupon->get_formatted_recurring_payments_amount();
				$order->coupon_recurring_payment_value = floatval( $order->get_recurring_payment_original_total() ) - floatval( $order->get_recurring_payment_total() );

			}

		} // if it's a single and it's on sale (recurring payment sales don't exist)
		elseif ( 'single' === $order->get_type() && $product->is_on_sale() ) {

			$order->original_total = $this->format_price( $product->get_regular_price() );
			$order->total = $this->format_price( $product->get_sale_price() );
			$order->sale_value = $this->format_price( floatval( $order->get_original_total() ) - floatval( $order->get_total() ) );
			$order->discount_type = 'sale';

		} // otherwise do default stuff
		else {

			if ( 'single' === $order->get_type() ) {

				$order->total = $this->format_price( $product->get_regular_price() );

			} elseif ( 'recurring' === $order->get_type() ) {

				$order->first_payment_total = $this->format_price( $subscription_data['first_payment'] );
				$order->recurring_payment_total = $this->format_price( $subscription_data['sub_price'] );

			}

		}

		// paymet meta data
		$order->payment_gateway = $payment_gateway;
		$order->payment_type = $payment_type;
		$order->currency = get_lifterlms_currency();

		// user data
		$order->user_id = $user_id;
		$user = $order->get_user();
		$order->billing_first_name = $user->first_name;
		$order->billing_last_name = $user->last_name;
		$order->billing_email = $user->user_email;
		$order->billing_address_1 = $user->llms_billing_address_1;
		$order->billing_address_2 = $user->llms_billing_address_2;
		$order->billing_city = $user->llms_billing_city;
		$order->billing_state = $user->llms_billing_state;
		$order->billing_zip = $user->llms_billing_zip;
		$order->billing_country = $user->llms_billing_country;
		$order->user_ip_address = llms_get_ip_address();

		$order = apply_filters( 'lifterlms_before_order_creation', $order );

		$order->create();

		// order sucessfully created, pass to the gateway
		if ( $order->get_id() ) {

			// set the order to the session so gateway can retrieve order details
			LLMS()->session->set( 'llms_order', $order->get_order_key() );

			// pass to the gateway to start processing
			$available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways();
			$available_gateways[ $order->get_payment_gateway() ]->process_payment( $order );

		} // order creation failed
		else {

			llms_add_notice( sprintf( 'There was an error creating your order, please try again.' ), 'error' );
			return;

		}

	}


	/**
	 * Perform actions on a succesful order completion
	 * @param  obj    $order  Instance of an LLMS_Order
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public function order_complete( $order ) {

		// trigger order complete action
		do_action( 'lifterlms_order_complete', $order->get_id() ); // @todo used by AffiliateWP only, can remove after updating AffiliateWP

		// enroll student
		llms_enroll_student( $order->user_id, $order->get_product_id(), 'order_' . $order->get_id() );

		// trigger purchase action, used by engagements
		do_action( 'lifterlms_product_purchased', $order->user_id, $order->get_product_id() );

	}


	/**
	 * Redirect user to payment confirmation page
	 *
	 * Triggered by action: lifterlms_order_process_payment_redirect
	 *
	 * @param  string $url  URL to redirect user to
	 *
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public function processing_redirect( $url ) {

		// deprecated action hook
		if ( 'lifterlms_order_process_begin' === current_filter() ) {
			llms_deprecated_function( 'lifterlms_order_process_begin', '3.0.0', 'lifterlms_process_payment_redirect' );
		}

		$redirect = esc_url( $url );

		llms_add_notice( __( 'Please confirm your payment.', 'lifterlms' ) );

		wp_redirect( html_entity_decode( apply_filters( 'lifterlms_order_process_payment_redirect', $redirect ) ) );

		exit();

	}


	/**
	 * Redirect user on order success
	 * If order is returned successful from payment gateway
	 * Chooses the appropriate url based on order data.
	 *
	 * @note DO NOT USE THIS METHOD to watch for completion of an order, instead use STATUS actions
	 *       which can be found in LLMS_Order::update_status()
	 *
	 * @param  object $order instance of an LLMS_Order
	 *
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public function processing_success_redirect( $order ) {

		// deprecated action hook
		if ( 'lifterlms_order_process_success' === current_filter() ) {
			llms_deprecated_function( 'lifterlms_order_process_success', '3.0.0', 'lifterlms_payment_process_success' );
		}

		$product_title = $order->product_title;
		$post_obj = get_post( $order->product_id );

		if ($post_obj->post_type == 'course') {
			$redirect = esc_url( get_permalink( $order->product_id ) );
			llms_add_notice( sprintf( __( 'Congratulations! You have enrolled in <strong>%s</strong>', 'lifterlms' ), $product_title ) );
		} elseif ($post_obj->post_type == 'llms_membership') {
			$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );
			llms_add_notice( sprintf( __( 'Congratulations! Your new membership level is <strong>%s</strong>', 'lifterlms' ), $product_title ) );
		} else {
			$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );
			llms_add_notice( sprintf( __( 'You have successfully purchased <strong>%s</strong>', 'lifterlms' ), $product_title ) );
		}

		wp_redirect( apply_filters( 'lifterlms_order_process_success_redirect', $redirect ) );

		exit;

	}


	/**
	 * Unenroll students during various order status changes
	 * @param  obj    $order  instance of an LLMS_Order
	 * @return void
	 *
	 * @since  3.0.0
	 */
	public function unenroll( $order ) {

		switch ( current_filter() ) {

			case 'lifterlms_order_status_refunded':
				$status = 'Refunded';
			break;

			case 'lifterlms_order_status_cancelled':
				$status = 'Cancelled';
			break;

			case 'lifterlms_order_status_expired':
			default:
				$status = 'Expired';

		}

		llms_unenroll_student( $order->get_user_id(), $order->get_product_id(), $status, 'order_' . $order->get_id() );

	}

}
return new LLMS_Controller_Orders();
