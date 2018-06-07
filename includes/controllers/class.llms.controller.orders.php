<?php
defined( 'ABSPATH' ) || exit;

/**
 * Order processing and related actions controller
 *
 * @since   3.0.0
 * @version 3.19.0
 */
class LLMS_Controller_Orders {

	/**
	 * Constructor
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function __construct() {

		// form actions
		add_action( 'init', array( $this, 'create_pending_order' ) );
		add_action( 'init', array( $this, 'confirm_pending_order' ) );
		add_action( 'init', array( $this, 'switch_payment_source' ) );

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
		add_action( 'lifterlms_order_status_completed', array( $this, 'complete_order' ), 10, 2 );
		add_action( 'lifterlms_order_status_active', array( $this, 'complete_order' ), 10, 2 );

		// status changes to pending cancel
		add_action( 'lifterlms_order_status_pending-cancel', array( $this, 'pending_cancel_order' ), 10, 1 );

		// status changes for orders to unenroll students upon purchase
		add_action( 'lifterlms_order_status_refunded', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_cancelled', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_expired', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_failed', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_on-hold', array( $this, 'error_order' ), 10, 1 );
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
	 * @param    obj    $order       Instance of an LLMS_Order
	 * @param    string $old_status  Previous order status (eg: 'pending')
	 * @return   void
	 * @since    1.0.0
	 * @version  3.19.0
	 */
	public function complete_order( $order, $old_status ) {

		// clear expiration date when moving from a pending-cancel order
		if ( 'pending-cancel' === $old_status ) {
			$order->set( 'date_access_expires', '' );
		}

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
		do_action( 'lifterlms_access_plan_purchased', $user_id, $order->get( 'plan_id' ) );

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
	 * @version  3.16.1
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
		} // End if().
		else {
			$coupon_id = null;
			$coupon = false;
		}

		// if payment is required, verify we have a gateway & it's valid & enabled
		if ( $plan->requires_payment( $coupon_id ) && empty( $_POST['llms_payment_gateway'] ) ) {
			return llms_add_notice( __( 'No payment method selected.', 'lifterlms' ), 'error' );
		} else {
			$gid = empty( $_POST['llms_payment_gateway'] ) ? 'manual' : $_POST['llms_payment_gateway'];
			$gateway = $this->validate_selected_gateway( $gid, $plan );
			if ( is_wp_error( $gateway ) ) {
				return llms_add_notice( $gateway->get_error_message(), 'error' );
			}
		}

		// attempt to update the user (performs validations)
		if ( get_current_user_id() ) {
			$person_id = LLMS_Person_Handler::update( $_POST, 'checkout' );
		} // End if().
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
		} // End if().
		elseif ( ! is_numeric( $person_id ) ) {

			return llms_add_notice( __( 'An unknown error occurred when attempting to create an account, please try again.', 'lifterlms' ), 'error' );

		} // make sure the user isn't already enrolled in the course or membership
		elseif ( llms_is_user_enrolled( $person_id, $product->get( 'id' ) ) ) {

			return llms_add_notice( sprintf( __( 'You already have access to this %2$s! Visit your dashboard <a href="%s">here.</a>', 'lifterlms' ), llms_get_page_url( 'myaccount' ), $product->get_post_type_label() ) , 'error' );

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

		$order->init( $person, $plan, $gateway, $coupon );

		// pass to the gateway to start processing
		$gateway->handle_pending_order( $order, $plan, $person, $coupon );

	}

	/**
	 * Called when an order's status changes to refunded, cancelled, expired, or failed
	 *
	 * @param    obj    $order  instance of an LLMS_Order
	 * @return   void
	 *
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function error_order( $order ) {

		switch ( current_filter() ) {

			case 'lifterlms_order_status_trash':
			case 'lifterlms_order_status_cancelled':
			case 'lifterlms_order_status_on-hold':
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
	 * Handle expiration & cancellation from a course / membership
	 * Called via scheduled action set during order completion for plans with a limited access plan
	 * Additionally called when an order is marked as "pending-cancel" to revoke access at the end of a pre-paid period
	 * @param    int  $order_id  WP Post ID of the LLMS Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function expire_access( $order_id ) {

		$order = new LLMS_Order( $order_id );
		$new_order_status = false;

		// pending cancel moves to cancelled
		if ( 'llms-pending-cancel' === $order->get( 'status' ) ) {

			$status = 'cancelled';
			$note = __( 'Student unenrolled at the end of access period due to subscription cancellation.', 'lifterlms' );
			$new_order_status = 'cancelled';

			// all others move to expired
		} else {

			$status = 'expired';
			$note = __( 'Student unenrolled due to automatic access plan expiration', 'lifterlms' );

		}

		llms_unenroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), $status, 'order_' . $order->get( 'id' ) );
		$order->add_note( $note );
		$order->unschedule_recurring_payment();

		if ( $new_order_status ) {
			$order->set_status( $new_order_status );
		}

	}

	/**
	 * Unschedule recurring payments and schedule access expiration
	 * @param    obj        $order  LLMS_Order object
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function pending_cancel_order( $order ) {

		$date = $order->get_next_payment_due_date( 'Y-m-d H:i:s' );
		$order->set( 'date_access_expires', $date );

		$order->unschedule_recurring_payment();
		$order->maybe_schedule_expiration();

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

			} // End if().
			else {
				llms_log( 'Recurring charge for order # ' . $order_id . ' could not be processed because the gateway no longer supports recurring payments', 'recurring-payments' );
				/**
				 * @todo  notifications....
				 */
			}
		} // End if().
		else {

			llms_log( 'Recurring charge for order # ' . $order_id . ' could not be processed', 'recurring-payments' );
			llms_log( $gateway->get_error_message(), 'recurring-payments' );

			/**
			 * @todo  notifications....
			 */

		}

	}


	/**
	 * Handle form submission of the "Update Payment Method" form on the student dashboard when viewing a single order
	 * @return   void
	 * @since    3.10.0
	 * @version  3.19.0
	 */
	public function switch_payment_source() {

		// invalid nonce or the form wasn't submitted
		if ( ! llms_verify_nonce( '_switch_source_nonce', 'llms_switch_order_source', 'POST' ) ) {
			return;
		} elseif ( ! isset( $_POST['order_id'] ) && ! is_numeric( $_POST['order_id'] ) && 0 == $_POST['order_id'] ) {
			return llms_add_notice( __( 'Missing order information.', 'lifterlms' ), 'error' );
		}

		$order = llms_get_post( $_POST['order_id'] );
		if ( ! $order || get_current_user_id() != $order->get( 'user_id' ) ) {
			return llms_add_notice( __( 'Invalid Order.', 'lifterlms' ), 'error' );
		} elseif ( empty( $_POST['llms_payment_gateway'] ) ) {
			return llms_add_notice( __( 'Missing gateway information.', 'lifterlms' ), 'error' );
		}

		$plan = llms_get_post( $order->get( 'plan_id' ) );
		$gateway_id = sanitize_text_field( $_POST['llms_payment_gateway'] );
		$gateway = $this->validate_selected_gateway( $gateway_id, $plan );

		if ( is_wp_error( $gateway ) ) {
			return llms_add_notice( $gateway->get_error_message(), 'error' );
		}

		// handoff to the gateway
		$gateway->handle_payment_source_switch( $order, $_POST );

		// if the order is pending cancel and there were no errors returned activate it
		if ( 'llms-pending-cancel' === $order->get( 'status' ) && ! llms_notice_count( 'error' ) ) {
			$order->set_status( 'active' );
		}

	}

	/**
	 * When a transaction fails, update the parent order's status
	 * @param    obj     $txn  Instance of the LLMS_Transaction
	 * @return   void
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function transaction_failed( $txn ) {

		$order = $txn->get_order();

		// halt if legacy
		if ( $order->is_legacy() ) { return; }

		if ( $order->can_be_retried() ) {

			$order->maybe_schedule_retry();

		} else {

			$order->set( 'status', 'llms-failed' );

		}

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
	 * @version  3.10.0
	 */
	public function transaction_succeeded( $txn ) {

		// get the order
		$order = $txn->get_order();

		// halt if legacy
		if ( $order->is_legacy() ) { return; }

		// update the status based on the order type
		$status = $order->is_recurring() ? 'llms-active' : 'llms-completed';
		$order->set( 'status', $status );
		$order->set( 'last_retry_rule', '' ); // retries should always start with tne first rule for new transactions

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
	 * @version  3.19.0
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

		do_action( 'lifterlms_' . $post_type . '_status_' . $old_status . '_to_' . $new_status, $obj, $old_status, $new_status );
		do_action( 'lifterlms_' . $post_type . '_status_' . $new_status, $obj, $old_status, $new_status );

	}

	/**
	 * Validate a gateway can be used to process the current action / transaction
	 * @param    string     $gateway_id  gateway's id
	 * @param    obj        $plan        instance of the LLMS_Access_Plan related to the action/transaction
	 * @return   mixed                   WP_Error or LLMS_Payment_Gateway subclass
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	private function validate_selected_gateway( $gateway_id, $plan ) {

		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $gateway_id );
		$err = new WP_Error();

		// valid gateway
		if ( is_subclass_of( $gateway, 'LLMS_Payment_Gateway' ) ) {

			// gateway not enabled
			if ( 'manual' !== $gateway->get_id() && ! $gateway->is_enabled() ) {

				return $err->add( 'gateway-error', __( 'The selected payment gateway is not currently enabled.', 'lifterlms' ) );

				// it's a recurring plan and the gateway doesn't support recurring
			} elseif ( $plan->is_recurring() && ! $gateway->supports( 'recurring_payments' ) ) {

				return $err->add( 'gateway-error', sprintf( __( '%s does not support recurring payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ) );

				// not recurring and the gateway doesn't support single payments
			} elseif ( ! $plan->is_recurring() && ! $gateway->supports( 'single_payments' ) ) {

				return $err->add( 'gateway-error', sprintf( __( '%s does not support single payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ) );

			}
		} else {

			return $err->add( 'invalid-gateway', __( 'An invalid payment method was selected.', 'lifterlms' ) );

		}

		return $gateway;

	}

}
return new LLMS_Controller_Orders();
