<?php
/**
 * Order processing and related actions controller
 *
 * @since   3.0.0
 * @version 3.5.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Controller_Orders {

	public function __construct() {

		// form actions
		add_action( 'init', array( $this, 'create_pending_order' ) );
		add_action( 'init', array( $this, 'confirm_pending_order' ) );

		// this action adds our lifterlms specific actions when order & transaction statuses change
		add_action( 'transition_post_status', array( $this, 'transition_status' ), 10, 3 );

		/**
		 * Status Change Actions for Orders and Transactions
		 */

		// transaction status changes cascade up to the order to change the order status
		add_action( 'lifterlms_transaction_status_failed', array( $this, 'transaction_failed' ), 10, 1 );
		add_action( 'lifterlms_transaction_status_refunded', array( $this, 'transaction_refunded' ), 10, 1 );
		add_action( 'lifterlms_transaction_status_succeeded', array( $this, 'transaction_succeeded' ), 10, 1 );

		// status changes for orders to enroll students and trigger completion actions
		add_action( 'lifterlms_order_status_completed', array( $this, 'complete_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_active', array( $this, 'complete_order' ), 10, 1 );

		// status changes for orders to unenroll students upon purchase
		add_action( 'lifterlms_order_status_refunded', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_cancelled', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_expired', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_failed', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_trash', array( $this, 'error_order' ), 10, 1 );

		/**
		 * Scheduler Actiions
		 */

		// charge recurring payments
		add_action( 'llms_charge_recurring_payment', array( $this, 'recurring_charge' ), 10, 1 );

		// expire access plans
		add_action( 'llms_access_plan_expiration', array( $this, 'expire_access' ), 10, 1 );

	}

	/**
	 * Confirm order form post
	 * User clicks confirm order or gateway determines the order is confirmed
	 *
	 * Executes payment gateway confirm order method and completes order.
	 * Redirects user to appropriate page / post
	 *
	 * @return void
	 *
	 * @since   3.0.0
	 * @version 3.4.0
	 */
	public function confirm_pending_order() {

		if ( 'POST' !== strtoupper( getenv( 'REQUEST_METHOD' ) ) || empty( $_POST['action'] ) || 'confirm_pending_order' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) ) { return; }

		// noonnce the post
		wp_verify_nonce( $_POST['_wpnonce'], 'confirm_pending_order' );

		// ensure we have an order key we can locate the order with
		$key = isset( $_POST['llms_order_key'] ) ? $_POST['llms_order_key'] : false;
		if ( ! $key ) {
			return llms_add_notice( __( 'Could not locate an order to confirm.', 'lifterlms' ), 'error' );
		}

		// lookup the order & return error if not found
		$order = llms_get_order_by_key( $key );
		if ( ! $order || ! $order instanceof LLMS_Order ) {
			return llms_add_notice( __( 'Could not locate an order to confirm.', 'lifterlms' ), 'error' );
		}

		// ensure the order is pending
		if ( 'llms-pending' !== $order->get( 'status' ) ) {
			return llms_add_notice( __( 'Only pending orders can be confirmed.', 'lifterlms' ), 'error' );
		}

		// get the gateway
		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $order->get( 'payment_gateway' ) );

		// pass the order to the gateway
		$gateway->confirm_pending_order( $order );

	}

	/**
	 * Perform actions on a succesful order completion
	 * @param  obj    $order  Instance of an LLMS_Order
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public function complete_order( $order ) {

		// record access start time & maybe schedule expiration
		$order->start_access();

		$order_id = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );
		$user_id = $order->get( 'user_id' );

		unset( LLMS()->session->llms_coupon );

		// trigger order complete action
		do_action( 'lifterlms_order_complete', $order_id ); // @todo used by AffiliateWP only, can remove after updating AffiliateWP

		// enroll student
		llms_enroll_student( $user_id, $product_id, 'order_' . $order_id );

		// trigger purchase action, used by engagements
		do_action( 'lifterlms_product_purchased', $user_id, $product_id );

		// maybe schedule a payment
		$order->maybe_schedule_payment();

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
	 * 			triggering the "lifterlms_process_payment_redirect" // todo check this last statement
	 *
	 * @return void
	 * @since    3.0.0
	 * @version  3.5.0
	 */
	public function create_pending_order() {

		// only run this if the correct action has been posted
		if ( 'POST' !== strtoupper( getenv( 'REQUEST_METHOD' ) ) || empty( $_POST['action'] ) || ( 'create_pending_order' !== $_POST['action'] ) || empty( $_POST['_wpnonce'] ) ) {
			return;
		}

		// nonce the post
		wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms_create_pending_order' );

		// prevent timeout
		@set_time_limit( 0 );

		/**
		 * Allow gateways, extensions, etc to do their own validation prior to standard validation
		 * If this returns a truthy, we'll stop processing
		 * The extension should add a notice in addition to returning the truthy
		 */
		if ( apply_filters( 'llms_before_checkout_validation', false ) ) {
			return;
		}

		// check t & c if configured
		if ( llms_are_terms_and_conditions_required() ) {
			if ( ! isset( $_POST['llms_agree_to_terms'] ) || 'yes' !== $_POST['llms_agree_to_terms'] ) {
				return llms_add_notice( sprintf( __( 'You must agree to the %s to continue.', 'lifterlms' ), get_the_title( get_option( 'lifterlms_terms_page_id' ) ) ), 'error' );
			}
		}

		// we must have a plan_id to proceed
		if ( empty( $_POST['llms_plan_id'] ) ) {
			return llms_add_notice( __( 'Missing an Access Plan ID.', 'lifterlms' ), 'error' );
		} else {
			$plan = new LLMS_Access_Plan( $_POST['llms_plan_id'] );
			if ( ! $plan->get( 'id' ) ) {
				return llms_add_notice( __( 'Invalid Access Plan ID.', 'lifterlms' ), 'error' );
			} else {
				$product = $plan->get_product();
			}
		}

		// if coupon code submitted, validate it
		if ( ! empty( $_POST['llms_coupon_code'] ) ) {

			$coupon_id = llms_find_coupon( $_POST['llms_coupon_code'] );

			if ( ! $coupon_id ) {
				return llms_add_notice( sprintf( __( 'Coupon code "%s" not found.', 'lifterlms' ), $_POST['llms_coupon_code'] ), 'error' );
			}

			$coupon = new LLMS_Coupon( $coupon_id );
			$valid = $coupon->is_valid( $_POST['llms_plan_id'] );

			// if the coupon has a validation error, return an error message
			if ( is_wp_error( $valid ) ) {

				return llms_add_notice( $valid->get_error_message(), 'error' );

			}

		} // no coupon, proceed
		else {
			$coupon_id = null;
			$coupon = false;
		}

		// if payment is required, verify we have a gateway & it's valid & enabled
		if ( $plan->requires_payment( $coupon_id ) && empty( $_POST['llms_payment_gateway'] ) ) {
			return llms_add_notice( __( 'No payment method selected.', 'lifterlms' ), 'error' );
		} else {
			$gid = empty( $_POST['llms_payment_gateway'] ) ? 'manual' : $_POST['llms_payment_gateway'];
			$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $gid );
			if ( is_subclass_of( $gateway, 'LLMS_Payment_Gateway' ) ) {
				// gateway must be enabled
				if ( 'manual' !== $gateway->get_id() && ! $gateway->is_enabled() ) {
					return llms_add_notice( __( 'The selected payment gateway is not currently enabled.', 'lifterlms' ), 'error' );
				} // if it's a recurring, ensure gateway supports recurring
				elseif ( $plan->is_recurring() && ! $gateway->supports( 'recurring_payments' ) ) {
					return llms_add_notice( sprintf( __( '%s does not support recurring payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ), 'error' );
					// if it's single, ensure gateway supports singles
				} elseif ( ! $plan->is_recurring() && ! $gateway->supports( 'single_payments' ) ) {
					return llms_add_notice( sprintf( __( '%s does not support single payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ), 'error' );
				}
			} else {
				return llms_add_notice( __( 'An invalid payment method was selected.', 'lifterlms' ), 'error' );
			}
		}

		// attempt to update the user (performs validations)
		if ( get_current_user_id() ) {
			$person_id = LLMS_Person_Handler::update( $_POST, 'checkout' );
		} // attempt to register new user (performs validations)
		else {
			$person_id = llms_register_user( $_POST, 'checkout', true );
		}

		// validation or registration issues
		if ( is_wp_error( $person_id ) ) {
			// existing user fails validation from the free checkout form
			if ( isset( $_POST['form'] ) && 'free_enroll' === $_POST['form'] ) {
				wp_redirect( $plan->get_checkout_url() );
				exit;
			}
			foreach ( $person_id->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;
		} // register should be a user_id at this point, if we're not numeric we have a problem...
		elseif ( ! is_numeric( $person_id ) ) {
			return llms_add_notice( __( 'An unknown error occurred when attempting to create an account, please try again.', 'lifterlms' ), 'error' );
		} // make sure the user isn't already enrolled in the course or membership
		elseif ( llms_is_user_enrolled( $person_id, $product->get( 'id' ) ) ) {

			return llms_add_notice( __( 'You already have access to this product!', 'lifterlms' ), 'error' );

		} else {
			$person = new LLMS_Student( $person_id );
		}

		/**
		 * Allow gateways, extensions, etc to do their own validation
		 * after all standard validations are succesfuly
		 * If this returns a truthy, we'll stop processing
		 * The extension should add a notice in addition to returning the truthy
		 */
		if ( apply_filters( 'llms_after_checkout_validation', false ) ) {
			return;
		}

		$order_id = 'new';

		// get order ID by Key if it exists
		if ( ! empty( $_POST['llms_order_key'] ) ) {
			$locate = llms_get_order_by_key( $_POST['llms_order_key'], 'id' );
			if ( $locate ) {
				$order_id = $locate;
			}
		}

		// instantiate the order
		$order = new LLMS_Order( $order_id );

		// if there's no id we can't proceed, return an error
		if ( ! $order->get( 'id' ) ) {
			return llms_add_notice( __( 'There was an error creating your order, please try again.', 'lifterlms' ), 'error' );
		}

		// add order key to globals so the order can be retried if processing errors occur
		$_POST['llms_order_key'] = $order->get( 'order_key' );

		// user related information
		$order->set( 'user_id', $person_id );
		$order->set( 'user_ip_address', llms_get_ip_address() );
		$order->set( 'billing_address_1', $person->get( 'billing_address_1' ) );
		$order->set( 'billing_address_2', $person->get( 'billing_address_2' ) );
		$order->set( 'billing_city', $person->get( 'billing_city' ) );
		$order->set( 'billing_country', $person->get( 'billing_country' ) );
		$order->set( 'billing_email', $person->get( 'user_email' ) );
		$order->set( 'billing_first_name', $person->get( 'first_name' ) );
		$order->set( 'billing_last_name', $person->get( 'last_name' ) );
		$order->set( 'billing_state', $person->get( 'billing_state' ) );
		$order->set( 'billing_zip', $person->get( 'billing_zip' ) );

		// access plan data
		$order->set( 'plan_id', $plan->get( 'id' ) );
		$order->set( 'plan_title', $plan->get( 'title' ) );
		$order->set( 'plan_sku', $plan->get( 'sku' ) );

		// product data
		$order->set( 'product_id', $product->get( 'id' ) );
		$order->set( 'product_title', $product->get( 'title' ) );
		$order->set( 'product_sku', $product->get( 'sku' ) );
		$order->set( 'product_type', $plan->get_product_type() );

		$order->set( 'payment_gateway', $gateway->get_id() );
		$order->set( 'gateway_api_mode', $gateway->get_api_mode() );

		// trial data
		if ( $plan->has_trial() ) {
			$order->set( 'trial_offer', 'yes' );
			$order->set( 'trial_length', $plan->get( 'trial_length' ) );
			$order->set( 'trial_period', $plan->get( 'trial_period' ) );
			$trial_price = $plan->get_price( 'trial_price', array(), 'float' );
			$order->set( 'trial_original_total', $trial_price );
			$trial_total = $coupon ? $plan->get_price_with_coupon( 'trial_price', $coupon, array(), 'float' ) : $trial_price;
			$order->set( 'trial_total', $trial_total );
		} else {
			$order->set( 'trial_offer', 'no' );
		}

		$price = $plan->get_price( 'price', array(), 'float' );
		$order->set( 'currency', get_lifterlms_currency() );

		// price data
		if ( $plan->is_on_sale() ) {
			$price_key = 'sale_price';
			$order->set( 'on_sale', 'yes' );
			$sale_price = $plan->get( 'sale_price', array(), 'float' );
			$order->set( 'sale_price', $sale_price );
			$order->set( 'sale_value', $price - $sale_price );
		} else {
			$price_key = 'price';
			$order->set( 'on_sale', 'no' );
		}

		// store original total before any discounts
		$order->set( 'original_total', $price );

		// get the actual total due after discounts if any are applicable
		$total = $coupon ? $plan->get_price_with_coupon( $price_key, $coupon, array(), 'float' ) : $$price_key;
		$order->set( 'total', $total );

		// coupon data
		if ( $coupon ) {
			$order->set( 'coupon_id', $coupon->get( 'id' ) );
			$order->set( 'coupon_amount', $coupon->get( 'coupon_amount' ) );
			$order->set( 'coupon_code', $coupon->get( 'title' ) );
			$order->set( 'coupon_type', $coupon->get( 'discount_type' ) );
			$order->set( 'coupon_used', 'yes' );
			$order->set( 'coupon_value', $$price_key - $total );
			if ( $plan->has_trial() && $coupon->has_trial_discount() ) {
				$order->set( 'coupon_amount_trial', $coupon->get( 'trial_amount' ) );
				$order->set( 'coupon_value_trial', $trial_price - $trial_total );
			}
		} else {
			$order->set( 'coupon_used', 'no' );
		}

		// get all billing schedule related information
		$order->set( 'billing_frequency', $plan->get( 'frequency' ) );
		if ( $plan->is_recurring() ) {
			$order->set( 'billing_length', $plan->get( 'length' ) );
			$order->set( 'billing_period', $plan->get( 'period' ) );
			$order->set( 'order_type', 'recurring' );
		} else {
			$order->set( 'order_type', 'single' );
		}

		$order->set( 'access_expiration', $plan->get( 'access_expiration' ) );

		// get access related data so when payment is complete we can calculate the actual expiration date
		if ( $plan->can_expire() ) {
			$order->set( 'access_expires', $plan->get( 'access_expires' ) );
			$order->set( 'access_length', $plan->get( 'access_length' ) );
			$order->set( 'access_period', $plan->get( 'access_period' ) );
		}

		do_action( 'lifterlms_new_pending_order', $order, $person );

		// pass to the gateway to start processing
		$gateway->handle_pending_order( $order, $plan, $person, $coupon );

	}

	/**
	 * Called when an order's status changes to refunded, cancelled, expired, or failed
	 *
	 * @param  obj    $order  instance of an LLMS_Order
	 * @return void
	 *
	 * @since  3.0.0
	 */
	public function error_order( $order ) {

		switch ( current_filter() ) {

			case 'lifterlms_order_status_trash':
			case 'lifterlms_order_status_cancelled':
			case 'lifterlms_order_status_refunded':
				$status = 'cancelled';
			break;

			case 'lifterlms_order_status_expired':
			case 'lifterlms_order_status_failed':
			default:
				$status = 'expired';
			break;

		}

		$order->unschedule_recurring_payment();

		llms_unenroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), $status, 'order_' . $order->get( 'id' ) );

	}

	/**
	 * Expires the enrollment associated with an order that has a limited access plan
	 * @param    int  $order_id  WP Post ID of the LLMS Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function expire_access( $order_id ) {

		$order = new LLMS_Order( $order_id );
		llms_unenroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), 'expired', 'order_' . $order->get( 'id' ) );
		$order->add_note( sprintf( __( 'Student unenrolled due to automatic access plan expiration', 'lifterlms' ) ) );
		$order->unschedule_recurring_payment();
		// @todo allow engagements to hook into expiration

	}

	/**
	 * Trigger a recrurring payment
	 * Called by action scheduler
	 * @param    int     $order_id  WP Post ID of the order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function recurring_charge( $order_id ) {

		$order = new LLMS_Order( $order_id );
		$gateway = $order->get_gateway();

		// ensure the gateway is still installed & available
		if ( ! is_wp_error( $gateway ) ) {

			// ensure that recurring payments feature is enabled
			// & that the gateway still supports recurring payments
			if ( LLMS_Site::get_feature( 'recurring_payments' ) && $gateway->supports( 'recurring_payments' ) ) {

				$gateway->handle_recurring_transaction( $order );

			} // log an error and do notifications
			else {
				llms_log( 'Recurring charge for order # ' . $order_id . ' could not be processed because the gateway no longer supports recurring payments', 'recurring-payments' );
				/**
				 * @todo  notifications....
				 */
			}
		} // record and error and do notifications
		else {

			llms_log( 'Recurring charge for order # ' . $order_id . ' could not be processed', 'recurring-payments' );
			llms_log( $gateway->get_error_message(), 'recurring-payments' );

			/**
			 * @todo  notifications....
			 */

		}

	}

	/**
	 * When a transaction fails, update the parent order's status
	 * @param    obj     $txn  Instance of the LLMS_Transaction
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function transaction_failed( $txn ) {

		$order = $txn->get_order();

		// halt if legacy
		if ( $order->is_legacy() ) { return; }

		$order->set( 'status', 'llms-failed' );

	}

	/**
	 * When a transaction is refunded, update the parent order's status
	 * @param    obj     $txn  Instance of the LLMS_Transaction
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function transaction_refunded( $txn ) {

		$order = $txn->get_order();

		// halt if legacy
		if ( $order->is_legacy() ) { return; }

		$order->set( 'status', 'llms-refunded' );

	}

	/**
	 * When a transaction succeeds, update the parent order's status
	 * @param    obj     $txn  Instance of the LLMS_Transaction
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function transaction_succeeded( $txn ) {

		// get the order
		$order = $txn->get_order();

		// halt if legacy
		if ( $order->is_legacy() ) { return; }

		// update the status based on the order type
		$status = $order->is_recurring() ? 'llms-active' : 'llms-completed';
		$order->set( 'status', $status );

		// maybe schedule a payment
		$order->maybe_schedule_payment();

	}

	/**
	 * Trigger actions when the status of LifterLMS Orders and LifterLMS Transactions change status
	 * @param    string     $new_status  new status
	 * @param    string     $old_status  old status
	 * @param    ojb        $post        WP_Post isntance
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function transition_status( $new_status, $old_status, $post ) {

		// don't do anything if the status hasn't changed
		if ( $new_status === $old_status ) {
			return;
		}

		// we're only concerned with order post statuses here
		if ( 'llms_order' !== $post->post_type && 'llms_transaction' !== $post->post_type ) {
			return;
		}

		$post_type = str_replace( 'llms_', '', $post->post_type );
		$obj = 'order' === $post_type ? new LLMS_Order( $post ) : new LLMS_Transaction( $post );

		// record order status changes as notes
		if ( 'order' === $post_type ) {
			$obj->add_note( sprintf( __( 'Order status changed from %1$s to %2$s', 'lifterlms' ), llms_get_order_status_name( $old_status ), llms_get_order_status_name( $new_status ) ) );
		}

		// remove prefixes from all the things
		$new_status = str_replace( array( 'llms-', 'txn-' ), '', $new_status );
		$old_status = str_replace( array( 'llms-', 'txn-' ), '', $old_status );

		do_action( 'lifterlms_' . $post_type . '_status_' . $old_status . '_to_' . $new_status, $obj );
		do_action( 'lifterlms_' . $post_type . '_status_' . $new_status, $obj );

	}

}
return new LLMS_Controller_Orders();
