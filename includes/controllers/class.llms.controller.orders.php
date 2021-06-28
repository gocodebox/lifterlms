<?php
/**
 * Order processing and related actions controller
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.0.0
 * @version 5.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Orders class
 *
 * @since 3.0.0
 * @since 3.33.0 Added logic to delete any enrollment records linked to an LLMS_Order on its permanent deletion.
 * @since 3.34.4 Added filter `llms_order_can_be_confirmed`.
 * @since 3.34.5 Fixed logic error in `llms_order_can_be_confirmed` conditional.
 * @since 3.36.1 In `recurring_charge()`, made sure to process only proper LLMS_Orders of existing users.
 * @since 4.2.0 Added logic to set the order status to 'cancelled' when an enrollment linked to an order is deleted.
 *              Also `llms_unenroll_on_error_order` fiter hook added.
 * @since 5.0.0 Build customer data using LLMS_Forms fields information.
 */
class LLMS_Controller_Orders {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Updated.
	 * @since 3.33.0 Added `before_delete_post` action to handle order deletion.
	 * @since 4.2.0 Added `llms_user_enrollment_deleted` action to handle order status change on enrollment deletion.
	 *
	 * @return void
	 */
	public function __construct() {

		// Form actions.
		add_action( 'init', array( $this, 'create_pending_order' ) );
		add_action( 'init', array( $this, 'confirm_pending_order' ) );
		add_action( 'init', array( $this, 'switch_payment_source' ) );

		// This action adds our lifterlms specific actions when order & transaction statuses change.
		add_action( 'transition_post_status', array( $this, 'transition_status' ), 10, 3 );

		// This action adds lifterlms specific action when an order is deleted, just before the WP post postmetas are removed..
		add_action( 'before_delete_post', array( $this, 'on_delete_order' ) );

		// This action is meant to do specific actions on orders when an enrollment, with an order as trigger, is deleted..
		add_action( 'llms_user_enrollment_deleted', array( $this, 'on_user_enrollment_deleted' ), 10, 3 );

		/**
		 * Status Change Actions for Orders and Transactions
		 */

		// Transaction status changes cascade up to the order to change the order status.
		add_action( 'lifterlms_transaction_status_failed', array( $this, 'transaction_failed' ), 10, 1 );
		add_action( 'lifterlms_transaction_status_refunded', array( $this, 'transaction_refunded' ), 10, 1 );
		add_action( 'lifterlms_transaction_status_succeeded', array( $this, 'transaction_succeeded' ), 10, 1 );

		// Status changes for orders to enroll students and trigger completion actions.
		add_action( 'lifterlms_order_status_completed', array( $this, 'complete_order' ), 10, 2 );
		add_action( 'lifterlms_order_status_active', array( $this, 'complete_order' ), 10, 2 );

		// Status changes to pending cancel.
		add_action( 'lifterlms_order_status_pending-cancel', array( $this, 'pending_cancel_order' ), 10, 1 );

		// Status changes for orders to unenroll students upon purchase.
		add_action( 'lifterlms_order_status_refunded', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_cancelled', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_expired', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_failed', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_on-hold', array( $this, 'error_order' ), 10, 1 );
		add_action( 'lifterlms_order_status_trash', array( $this, 'error_order' ), 10, 1 );

		/**
		 * Scheduler Actions
		 */

		// Charge recurring payments.
		add_action( 'llms_charge_recurring_payment', array( $this, 'recurring_charge' ), 10, 1 );

		// Expire access plans.
		add_action( 'llms_access_plan_expiration', array( $this, 'expire_access' ), 10, 1 );

	}

	/**
	 * Confirm order form post
	 * User clicks confirm order or gateway determines the order is confirmed
	 *
	 * Executes payment gateway confirm order method and completes order.
	 * Redirects user to appropriate page / post
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Unknown.
	 * @since 3.34.4 Added filter `llms_order_can_be_confirmed`.
	 * @since 3.34.5 Fixed logic error in `llms_order_can_be_confirmed` conditional.
	 * @since 3.35.0 Return early if nonce doesn't pass verification and sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function confirm_pending_order() {

		// Nonce the post.
		if ( ! llms_verify_nonce( '_wpnonce', 'confirm_pending_order' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'confirm_pending_order' !== $_POST['action'] ) {
			return;
		}

		// Ensure we have an order key we can locate the order with.
		$key = llms_filter_input( INPUT_POST, 'llms_order_key', FILTER_SANITIZE_STRING );
		if ( ! $key ) {
			return llms_add_notice( __( 'Could not locate an order to confirm.', 'lifterlms' ), 'error' );
		}

		// Lookup the order & return error if not found.
		$order = llms_get_order_by_key( $key );
		if ( ! $order || ! $order instanceof LLMS_Order ) {
			return llms_add_notice( __( 'Could not locate an order to confirm.', 'lifterlms' ), 'error' );
		}

		/**
		 * Determine if the order can be confirmed.
		 *
		 * @since 3.34.4
		 *
		 * @param bool       $can_be_confirmed   True if the order can be confirmed, false otherwise.
		 * @param LLMS_Order $order              Order object.
		 * @param string     $gateway_id Payment gateway ID.
		 */
		if ( ! apply_filters( 'llms_order_can_be_confirmed', ( 'llms-pending' === $order->get( 'status' ) ), $order, $order->get( 'payment_gateway' ) ) ) {
			return llms_add_notice( __( 'Only pending orders can be confirmed.', 'lifterlms' ), 'error' );
		}

		// Get the gateway.
		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $order->get( 'payment_gateway' ) );

		// Pass the order to the gateway.
		$gateway->confirm_pending_order( $order );

	}

	/**
	 * Perform actions on a successful order completion
	 *
	 * @since 1.0.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param LLMS_Order $order      Instance of an LLMS_Order.
	 * @param string     $old_status Previous order status (eg: 'pending').
	 * @return void
	 */
	public function complete_order( $order, $old_status ) {

		// Clear expiration date when moving from a pending-cancel order.
		if ( 'pending-cancel' === $old_status ) {
			$order->set( 'date_access_expires', '' );
		}

		// Record access start time & maybe schedule expiration.
		$order->start_access();

		$order_id   = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );
		$user_id    = $order->get( 'user_id' );

		unset( LLMS()->session->llms_coupon );

		// Trigger order complete action.
		do_action( 'lifterlms_order_complete', $order_id ); // @todo used by AffiliateWP only, can remove after updating AffiliateWP.

		// Enroll student.
		llms_enroll_student( $user_id, $product_id, 'order_' . $order_id );

		// Trigger purchase action, used by engagements.
		do_action( 'lifterlms_product_purchased', $user_id, $product_id );
		do_action( 'lifterlms_access_plan_purchased', $user_id, $order->get( 'plan_id' ) );

		// Maybe schedule a payment.
		$order->maybe_schedule_payment();

	}


	/**
	 * Handle form submission of the checkout / payment form
	 *
	 *      1. Logs in or Registers a user
	 *      2. Validates all fields
	 *      3. Handles coupon pricing adjustments
	 *      4. Creates a PENDING llms_order
	 *
	 *      If errors, returns error on screen to user
	 *      If success, passes to the selected gateways "process_payment" method
	 *          the process_payment method should complete by returning an error or
	 *          triggering the "lifterlms_process_payment_redirect" // Todo check this last statement.
	 *
	 * @since 3.0.0
	 * @since 3.27.0 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since 5.0.0 Build customer data using LLMS_Forms fields information.
	 * @since 5.0.1 Delegate sanitization of user information fields of the `$_POST` to LLMS_Form_Handler::submit().
	 *
	 * @return void
	 */
	public function create_pending_order() {

		if ( ! llms_verify_nonce( '_llms_checkout_nonce', 'create_pending_order', 'POST' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'create_pending_order' !== $_POST['action'] ) {
			return;
		}

		// Prevent timeout.
		@set_time_limit( 0 );

		/**
		 * Allow 3rd parties to perform their own validation prior to standard validation.
		 *
		 * If this returns a truthy, we'll stop processing
		 *
		 * The extension should add a notice in addition to returning the truthy.
		 *
		 * @since Unknown
		 *
		 * @param boolean $valid Validation status. If `true` ceases checkout execution. If `false` checkout proceeds.
		 */
		if ( apply_filters( 'llms_before_checkout_validation', false ) ) {
			return;
		}

		// Setup data to pass to the pending order creation function.
		$data = array();
		$keys = array(
			'llms_plan_id',
			'llms_agree_to_terms',
			'llms_payment_gateway',
			'llms_coupon_code',
		);

		$plan = llms_get_post( llms_filter_input( INPUT_POST, 'llms_plan_id', FILTER_SANITIZE_NUMBER_INT ) );

		foreach ( $keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$data[ str_replace( 'llms_', '', $key ) ] = llms_filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
			}
		}

		$data['customer'] = array();
		if ( get_current_user_id() ) {
			$data['customer']['user_id'] = get_current_user_id();
		}

		foreach ( LLMS_Forms::instance()->get_form_fields( 'checkout', compact( 'plan' ) ) as $cust_field ) {
			if ( isset( $_POST[ $cust_field['name'] ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization happens on submission when setting up the pending order.
				$data['customer'][ $cust_field['name'] ] = $_POST[ $cust_field['name'] ];
			}
		}

		$setup = llms_setup_pending_order( $data );

		if ( is_wp_error( $setup ) ) {

			foreach ( $setup->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}

			// Existing user fails validation from the free checkout form.
			if ( get_current_user_id() && isset( $_POST['form'] ) && 'free_enroll' === $_POST['form'] && isset( $_POST['llms_plan_id'] ) ) {
				wp_redirect( $plan->get_checkout_url() );
				exit;
			}

			return;

		}

		/**
		 * Allow gateways, extensions, etc to do their own validation
		 * after all standard validations are successfully
		 * If this returns a truthy, we'll stop processing
		 * The extension should add a notice in addition to returning the truthy
		 */
		if ( apply_filters( 'llms_after_checkout_validation', false ) ) {
			return;
		}

		$order_id = 'new';

		// Get order ID by Key if it exists.
		if ( ! empty( $_POST['llms_order_key'] ) ) {
			$locate = llms_get_order_by_key( llms_filter_input( INPUT_POST, 'llms_order_key', FILTER_SANITIZE_STRING ), 'id' );
			if ( $locate ) {
				$order_id = $locate;
			}
		}

		// Instantiate the order.
		$order = new LLMS_Order( $order_id );

		// If there's no id we can't proceed, return an error.
		if ( ! $order->get( 'id' ) ) {
			return llms_add_notice( __( 'There was an error creating your order, please try again.', 'lifterlms' ), 'error' );
		}

		// Add order key to globals so the order can be retried if processing errors occur.
		$_POST['llms_order_key'] = $order->get( 'order_key' );

		$order->init( $setup['person'], $setup['plan'], $setup['gateway'], $setup['coupon'] );

		// Pass to the gateway to start processing.
		$setup['gateway']->handle_pending_order( $order, $setup['plan'], $setup['person'], $setup['coupon'] );

	}

	/**
	 * Called when an order's status changes to refunded, cancelled, expired, or failed.
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 * @since 4.2.0 Added `llms_unenroll_on_error_order` filter hook.
	 *
	 * @param LLMS_Order $order Instance of an LLMS_Order.
	 * @return void
	 */
	public function error_order( $order ) {

		$order->unschedule_recurring_payment();

		/**
		 * Determine if student should be unenrolled on order error.
		 *
		 * @since 4.2.0
		 *
		 * @param bool       $unenroll_on_error_order True if the student should be unenrolled, false otherwise. Default true.
		 * @param LLMS_Order $order                   Order object.
		 */
		if ( ! apply_filters( 'llms_unenroll_on_error_order', true, $order ) ) {
			return;
		}

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

		llms_unenroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), $status, 'order_' . $order->get( 'id' ) );

	}

	/**
	 * Called when a post is permanently deleted
	 *
	 * Will delete any enrollment records linked to the LLMS_Order with the ID of the deleted post.
	 *
	 * @since 3.33.0
	 *
	 * @param int $post_id WP_Post ID.
	 * @return void
	 */
	public function on_delete_order( $post_id ) {

		$order = llms_get_post( $post_id );
		if ( $order && is_a( $order, 'LLMS_Order' ) ) {
			llms_delete_student_enrollment( $order->get( 'user_id' ), $order->get( 'product_id' ), 'order_' . $order->get( 'id' ) );
		}

	}

	/**
	 * Called when an user enrollment is deleted
	 *
	 * Will set the related order status to 'cancelled'.
	 *
	 * @since 4.2.0
	 *
	 * @param int    $user_id    WP User ID.
	 * @param int    $product_id WP Post ID of the course or membership.
	 * @param string $trigger    The deleted enrollment trigger, or 'any' if no specific trigger.
	 * @return void
	 */
	public function on_user_enrollment_deleted( $user_id, $product_id, $trigger ) {

		$order_id = 'order_' === substr( $trigger, 0, 6 ) ? absint( substr( $trigger, 6 ) ) : false;
		$order    = $order_id ? llms_get_post( $order_id ) : false;

		if ( $order && is_a( $order, 'LLMS_Order' ) ) {

			// No need to run an unenrollment as we're reacting to an enrollment deletion, user enrollments data already removed..
			add_filter( 'llms_unenroll_on_error_order', '__return_false', 100 );
			$order->set_status( 'cancelled' );
			// Reset unenrollment's suspension..
			remove_filter( 'llms_unenroll_on_error_order', '__return_false', 100 );

		}

	}

	/**
	 * Handle expiration & cancellation from a course / membership
	 *
	 * Called via scheduled action set during order completion for plans with a limited access plan.
	 * Additionally called when an order is marked as "pending-cancel" to revoke access at the end of a pre-paid period.
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param int $order_id WP Post ID of the LLMS Order.
	 * @return void
	 */
	public function expire_access( $order_id ) {

		$order            = new LLMS_Order( $order_id );
		$new_order_status = false;

		// Pending cancel moves to cancelled.
		if ( 'llms-pending-cancel' === $order->get( 'status' ) ) {

			$status           = 'cancelled';
			$note             = __( 'Student unenrolled at the end of access period due to subscription cancellation.', 'lifterlms' );
			$new_order_status = 'cancelled';

			// All others move to expired.
		} else {

			$status = 'expired';
			$note   = __( 'Student unenrolled due to automatic access plan expiration', 'lifterlms' );

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
	 *
	 * @since 3.19.0
	 *
	 * @param LLMS_Order $order LLMS_Order object.
	 * @return void
	 */
	public function pending_cancel_order( $order ) {

		$date = $order->get_next_payment_due_date( 'Y-m-d H:i:s' );
		$order->set( 'date_access_expires', $date );

		$order->unschedule_recurring_payment();
		$order->maybe_schedule_expiration();

	}

	/**
	 * Trigger a recurring payment
	 *
	 * Called by action scheduler.
	 *
	 * @since 3.0.0
	 * @since 3.32.0 Record order notes and trigger actions during errors.
	 * @since 3.36.1 Made sure to process only proper LLMS_Orders of existing users.
	 *
	 * @param int $order_id WP Post ID of the order.
	 * @return bool `false` if the recurring charge cannot be processed, `true` when the charge is successfully handed off to the gateway.
	 */
	public function recurring_charge( $order_id ) {

		// Make sure the order still exists.
		$order = llms_get_post( $order_id );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {

			do_action( 'llms_order_recurring_charge_order_error', $order_id, $this );
			llms_log( sprintf( 'Recurring charge for Order #%d could not be processed because the order no longer exists.', $order_id ), 'recurring-payments' );
			return false;

		}

		// Check the user still exists.
		$user_id = $order->get( 'user_id' );
		if ( ! get_user_by( 'id', $user_id ) ) {

			do_action( 'llms_order_recurring_charge_user_error', $order_id, $user_id, $this );
			llms_log( sprintf( 'Recurring charge for Order #%1$d could not be processed because the user (#%2$d) no longer exists.', $order_id, $user_id ), 'recurring-payments' );

			// Translators: %d = The deleted user's ID..
			$order->add_note( sprintf( __( 'Recurring charge skipped. The user (#%d) no longer exists.', 'lifterlms' ), $user_id ) );
			return false;

		}

		// Ensure Gateway is still available..
		$gateway = $order->get_gateway();
		if ( is_wp_error( $gateway ) ) {

			do_action( 'llms_order_recurring_charge_gateway_error', $order_id, $gateway, $this );

			llms_log(
				sprintf(
					'Recurring charge for Order #%1$d could not be processed because the "%2$s" gateway is no longer available. Gateway Error: %3$s',
					$order_id,
					$order->get( 'payment_gateway' ),
					$gateway->get_error_message()
				),
				'recurring-payments'
			);

			$order->add_note(
				sprintf(
					// Translators: %s = error message encountered while loading the gateway..
					__( 'Recurring charge was not processed due to an error encountered while loading the payment gateway: %s.', 'lifterlms' ),
					$gateway->get_error_message()
				)
			);
			return false;

		}

		// Gateway doesn't support recurring payments..
		if ( ! $gateway->supports( 'recurring_payments' ) ) {

			do_action( 'llms_order_recurring_charge_gateway_payments_disabled', $order_id, $gateway, $this );
			llms_log( sprintf( 'Recurring charge for order #%d could not be processed because the gateway no longer supports recurring payments.', 'recurring-payments' ), $order_id );
			$order->add_note( __( 'Recurring charge skipped because recurring payments are disabled for the payment gateway.', 'lifterlms' ) );
			return false;

		}

		// Recurring payments disabled as a site feature when in staging mode..
		if ( ! LLMS_Site::get_feature( 'recurring_payments' ) ) {

			do_action( 'llms_order_recurring_charge_skipped', $order_id, $gateway, $this );
			$order->add_note( __( 'Recurring charge skipped because recurring payments are disabled in staging mode.', 'lifterlms' ) );
			return false;

		}

		// Passed validation, hand off to the gateway..
		$gateway->handle_recurring_transaction( $order );
		return true;

	}


	/**
	 * Handle form submission of the "Update Payment Method" form on the student dashboard when viewing a single order
	 *
	 * @since 3.10.0
	 * @since 3.19.0 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function switch_payment_source() {

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( '_switch_source_nonce', 'llms_switch_order_source', 'POST' ) ) {
			return;
		} elseif ( ! isset( $_POST['order_id'] ) && ! is_numeric( $_POST['order_id'] ) && 0 == $_POST['order_id'] ) {
			return llms_add_notice( __( 'Missing order information.', 'lifterlms' ), 'error' );
		}

		$order = llms_get_post( llms_filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT ) );
		if ( ! $order || get_current_user_id() != $order->get( 'user_id' ) ) {
			return llms_add_notice( __( 'Invalid Order.', 'lifterlms' ), 'error' );
		} elseif ( empty( $_POST['llms_payment_gateway'] ) ) {
			return llms_add_notice( __( 'Missing gateway information.', 'lifterlms' ), 'error' );
		}

		$plan       = llms_get_post( $order->get( 'plan_id' ) );
		$gateway_id = llms_filter_input( INPUT_POST, 'llms_payment_gateway', FILTER_SANITIZE_STRING );
		$gateway    = $this->validate_selected_gateway( $gateway_id, $plan );

		if ( is_wp_error( $gateway ) ) {
			return llms_add_notice( $gateway->get_error_message(), 'error' );
		}

		// Handoff to the gateway.
		$gateway->handle_payment_source_switch( $order, $_POST );

		// If the order is pending cancel and there were no errors returned activate it.
		if ( 'llms-pending-cancel' === $order->get( 'status' ) && ! llms_notice_count( 'error' ) ) {
			$order->set_status( 'active' );
		}

	}

	/**
	 * When a transaction fails, update the parent order's status
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 *
	 * @param LLMS_Transaction $txn Instance of the LLMS_Transaction.
	 * @return void
	 */
	public function transaction_failed( $txn ) {

		$order = $txn->get_order();

		// Halt if legacy.
		if ( $order->is_legacy() ) {
			return; }

		if ( $order->can_be_retried() ) {

			$order->maybe_schedule_retry();

		} else {

			$order->set( 'status', 'llms-failed' );

		}

	}

	/**
	 * When a transaction is refunded, update the parent order's status
	 *
	 * @since 3.0.0
	 *
	 * @param LLMS_Transaction $txn Instance of the LLMS_Transaction.
	 * @return void
	 */
	public function transaction_refunded( $txn ) {

		$order = $txn->get_order();

		// Halt if legacy.
		if ( $order->is_legacy() ) {
			return; }

		$order->set( 'status', 'llms-refunded' );

	}

	/**
	 * When a transaction succeeds, update the parent order's status
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 *
	 * @param LLMS_Transaction $txn Instance of the LLMS_Transaction.
	 * @return void
	 */
	public function transaction_succeeded( $txn ) {

		// Get the order.
		$order = $txn->get_order();

		// Halt if legacy.
		if ( $order->is_legacy() ) {
			return; }

		// Update the status based on the order type.
		$status = $order->is_recurring() ? 'llms-active' : 'llms-completed';
		$order->set( 'status', $status );
		$order->set( 'last_retry_rule', '' ); // Retries should always start with tne first rule for new transactions.

		// Maybe schedule a payment.
		$order->maybe_schedule_payment();

	}

	/**
	 * Trigger actions when the status of LifterLMS Orders and LifterLMS Transactions change status
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $post       WP_Post instance.
	 * @return void
	 */
	public function transition_status( $new_status, $old_status, $post ) {

		// Don't do anything if the status hasn't changed.
		if ( $new_status === $old_status ) {
			return;
		}

		// We're only concerned with order post statuses here.
		if ( 'llms_order' !== $post->post_type && 'llms_transaction' !== $post->post_type ) {
			return;
		}

		$post_type = str_replace( 'llms_', '', $post->post_type );
		$obj       = 'order' === $post_type ? new LLMS_Order( $post ) : new LLMS_Transaction( $post );

		// Record order status changes as notes.
		if ( 'order' === $post_type ) {
			$obj->add_note( sprintf( __( 'Order status changed from %1$s to %2$s', 'lifterlms' ), llms_get_order_status_name( $old_status ), llms_get_order_status_name( $new_status ) ) );
		}

		// Remove prefixes from all the things.
		$new_status = str_replace( array( 'llms-', 'txn-' ), '', $new_status );
		$old_status = str_replace( array( 'llms-', 'txn-' ), '', $old_status );

		do_action( 'lifterlms_' . $post_type . '_status_' . $old_status . '_to_' . $new_status, $obj, $old_status, $new_status );
		do_action( 'lifterlms_' . $post_type . '_status_' . $new_status, $obj, $old_status, $new_status );

	}

	/**
	 * Validate a gateway can be used to process the current action / transaction
	 *
	 * @since 3.10.0
	 *
	 * @param string           $gateway_id Gateway's id.
	 * @param LLMS_Access_Plan $plan       Instance of the LLMS_Access_Plan related to the action/transaction.
	 * @return WP_Error|LLMS_Payment_Gateway WP_Error or LLMS_Payment_Gateway subclass.
	 */
	private function validate_selected_gateway( $gateway_id, $plan ) {

		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $gateway_id );
		$err     = new WP_Error();

		// Valid gateway.
		if ( is_subclass_of( $gateway, 'LLMS_Payment_Gateway' ) ) {

			// Gateway not enabled.
			if ( 'manual' !== $gateway->get_id() && ! $gateway->is_enabled() ) {

				return $err->add( 'gateway-error', __( 'The selected payment gateway is not currently enabled.', 'lifterlms' ) );

				// It's a recurring plan and the gateway doesn't support recurring.
			} elseif ( $plan->is_recurring() && ! $gateway->supports( 'recurring_payments' ) ) {

				return $err->add( 'gateway-error', sprintf( __( '%s does not support recurring payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ) );

				// Not recurring and the gateway doesn't support single payments.
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
