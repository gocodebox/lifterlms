<?php
/**
 * Order processing and related actions controller.
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Orders class.
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
	 * Constructor.
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Updated.
	 * @since 3.33.0 Added `before_delete_post` action to handle order deletion.
	 * @since 4.2.0 Added `llms_user_enrollment_deleted` action to handle order status change on enrollment deletion.
	 * @since 5.4.0 Perform `error_order()` when Detect a product deletion while processing a recurring charge.
	 * @since 7.0.0 Added callback for `wp_untrash_post_status` filter.
	 *              Remove action callbacks for order confirm, create, and payment source switch in favor of hooks in `LLMS_Controller_Checkout`.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'wp_untrash_post_status', array( $this, 'set_untrash_status' ), 10, 3 );

		// This action adds our lifterlms specific actions when order & transaction statuses change.
		add_action( 'transition_post_status', array( $this, 'transition_status' ), 10, 3 );

		// This action adds lifterlms specific action when an order is deleted, just before the WP post postmetas are removed.
		add_action( 'before_delete_post', array( $this, 'on_delete_order' ) );

		// This action is meant to do specific actions on orders when an enrollment, with an order as trigger, is deleted.
		add_action( 'llms_user_enrollment_deleted', array( $this, 'on_user_enrollment_deleted' ), 10, 3 );

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

		// Detect a product deletion while processing a recurring charge.
		add_action( 'llms_order_recurring_charge_aborted_product_deleted', array( $this, 'error_order' ), 10, 1 );

		/**
		 * Scheduler Actions
		 */

		// Charge recurring payments.
		add_action( 'llms_charge_recurring_payment', array( $this, 'recurring_charge' ), 10, 1 );

		// Expire access plans.
		add_action( 'llms_access_plan_expiration', array( $this, 'expire_access' ), 10, 1 );

	}

	/**
	 * Perform actions on a successful order completion.
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

		unset( llms()->session->llms_coupon );

		/**
		 * Action fired on order complete.
		 *
		 * Prior to the students being enrolled.
		 *
		 * @since 1.0.0
		 *
		 * @param integer $order_id The WP_Post ID of the order.
		 */
		do_action( 'lifterlms_order_complete', $order_id ); // @todo used by AffiliateWP only, can remove after updating AffiliateWP.

		// Enroll student.
		llms_enroll_student( $user_id, $product_id, 'order_' . $order_id );

		// Trigger purchase action, used by engagements.

		/**
		 * Action fired on product purchased.
		 *
		 * After the student has been enrolled.
		 *
		 * @since Unknown
		 *
		 * @param integer $user_id    The WP_User ID of the buyer.
		 * @param integer $product_id The WP_Post ID of the purchased product (course/membership).
		 */
		do_action( 'lifterlms_product_purchased', $user_id, $product_id );

		/**
		 * Action fired on access plan purchased.
		 *
		 * After the student has been enrolled.
		 *
		 * @since Unknown
		 *
		 * @param integer $user_id    The WP_User ID of the buyer.
		 * @param integer $product_id The WP_Post ID of the purchased access plan.
		 */
		do_action( 'lifterlms_access_plan_purchased', $user_id, $order->get( 'plan_id' ) );

		// Maybe schedule a payment.
		$order->maybe_schedule_payment();

	}

	/**
	 * Called when an order's status changes to refunded, cancelled, expired, or failed.
	 *
	 * Also called on product deletion detected while processing a recurring charge.

	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 * @since 4.2.0 Added `llms_unenroll_on_error_order` filter hook.
	 * @since 5.4.0 Unenroll with 'cancelled' status on 'llms_order_recurring_charge_aborted_product_deleted'.
	 *              The `$order` param can be also a WP_Post or its `ID`.
	 *
	 * @param int|WP_Post|LLMS_Order $order Instance of an LLMS_Order, WP_Post or WP_Post ID of the order.
	 * @return void
	 */
	public function error_order( $order ) {

		$order = is_a( $order, 'LLMS_Order' ) ? $order : llms_get_post( $order );
		if ( ! ( $order && is_a( $order, 'LLMS_Order' ) ) ) {
			return;
		}

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
			case 'llms_order_recurring_charge_aborted_product_deleted':
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
	 * Called when a post is permanently deleted.
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
	 * Called when an user enrollment is deleted.
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

			// No need to run an unenrollment as we're reacting to an enrollment deletion, user enrollments data already removed.
			add_filter( 'llms_unenroll_on_error_order', '__return_false', 100 );
			$order->set_status( 'cancelled' );
			// Reset unenrollment's suspension..
			remove_filter( 'llms_unenroll_on_error_order', '__return_false', 100 );

		}

	}

	/**
	 * Handle expiration & cancellation from a course / membership.
	 *
	 * Called via scheduled action set during order completion for plans with a limited access plan.
	 * Additionally called when an order is marked as "pending-cancel" to revoke access at the end of a pre-paid period.
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 * @since 7.5.0 Potentially allow recurring payment to go ahead even if access plans expired.
	 *
	 * @param int $order_id WP_Post ID of the LLMS Order.
	 * @return void
	 */
	public function expire_access( $order_id ) {

		$order            = new LLMS_Order( $order_id );
		$new_order_status = false;

		// Pending cancel order moves to cancelled.
		if ( 'llms-pending-cancel' === $order->get( 'status' ) ) {

			$status           = 'cancelled'; // Enrollment status.
			$note             = __( 'Student unenrolled at the end of access period due to subscription cancellation.', 'lifterlms' );
			$new_order_status = 'cancelled';

			// All others move to expired.
		} else {

			$status = 'expired'; // Enrollment status.
			$note   = __( 'Student unenrolled due to automatic access plan expiration', 'lifterlms' );

		}

		/**
		 * Filters whether or not recurring payments should be stopped on access plan expiration.
		 *
		 * By default when an access plan expires, recurring payments are stopped.
		 *
		 * @since 7.5.0
		 *
		 * @param bool
		 * @param LLMS_Order $order             Instance of the order.
		 * @param mixed      $new_order_status  New order status. If `false` it means that the new order status is not
		 *                                      going to change. At this stage the orders status is not changed yet.
		 * @param string     $enrollment_status The new enrollment status. At this stage the enrollment status is not changed yet.
		 */
		$unschedule_recurring_payment = apply_filters(
			'llms_unschedule_recurring_payment_on_access_plan_expiration',
			true,
			$order,
			$new_order_status,
			$status
		);

		llms_unenroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), $status, 'order_' . $order->get( 'id' ) );
		$order->add_note( $note );

		if ( $unschedule_recurring_payment ) {
			$order->unschedule_recurring_payment();
		}

		if ( $new_order_status ) {
			$order->set_status( $new_order_status );
		}

	}

	/**
	 * Unschedule recurring payments and schedule access expiration.
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
	 * Trigger a recurring payment.
	 *
	 * Called by action scheduler.
	 *
	 * @since 3.0.0
	 * @since 3.32.0 Record order notes and trigger actions during errors.
	 * @since 3.36.1 Made sure to process only proper LLMS_Orders of existing users.
	 * @since 5.2.0 Fixed buggy logging on gateway error because it doesn't support recurring payments.
	 * @since 5.4.0 Handle case when the order's related product has been removed.
	 *
	 * @param int $order_id WP Post ID of the order.
	 * @return bool `false` if the recurring charge cannot be processed, `true` when the charge is successfully handed off to the gateway.
	 */
	public function recurring_charge( $order_id ) {

		// Make sure the order still exists.
		$order = llms_get_post( $order_id );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {

			/**
			 * Fired when a LifterLMS order's recurring charge errors because the order doesn't exist anymore
			 *
			 * @since Unknown
			 *
			 * @param int                    $order_id   WP Post ID of the order.
			 * @param LLMS_Controller_Orders $controller This controller's instance.
			 */
			do_action( 'llms_order_recurring_charge_order_error', $order_id, $this );
			llms_log( sprintf( 'Recurring charge for Order #%d could not be processed because the order no longer exists.', $order_id ), 'recurring-payments' );
			return false;

		}

		// Check the user still exists.
		$user_id = $order->get( 'user_id' );
		if ( ! get_user_by( 'id', $user_id ) ) {

			/**
			 * Fired when a LifterLMS order's recurring charge errors because the user who placed the order doesn't exist anymore
			 *
			 * @since Unknown
			 *
			 * @param int                    $order_id   WP Post ID of the order.
			 * @param int                    $user_id    WP User ID of the user who placed the order.
			 * @param LLMS_Controller_Orders $controller This controller's instance.
			 */
			do_action( 'llms_order_recurring_charge_user_error', $order_id, $user_id, $this );
			llms_log( sprintf( 'Recurring charge for Order #%1$d could not be processed because the user (#%2$d) no longer exists.', $order_id, $user_id ), 'recurring-payments' );

			// Translators: %d = The deleted user's ID.
			$order->add_note( sprintf( __( 'Recurring charge skipped. The user (#%d) no longer exists.', 'lifterlms' ), $user_id ) );
			return false;

		}

		// Ensure Gateway is still available.
		$gateway = $order->get_gateway();

		if ( is_wp_error( $gateway ) ) {
			/**
			 * Fired when a LifterLMS order's recurring charge errors because of a gateway error. E.g. it's not available anymore.
			 *
			 * @since Unknown
			 *
			 * @param int                    $order_id   WP Post ID of the order.
			 * @param WP_Error               $error      WP_Error instance.
			 * @param LLMS_Controller_Orders $controller This controller's instance.
			 */
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
					// Translators: %s = error message encountered while loading the gateway.
					__( 'Recurring charge was not processed due to an error encountered while loading the payment gateway: %s.', 'lifterlms' ),
					$gateway->get_error_message()
				)
			);
			return false;

		}

		// Gateway doesn't support recurring payments.
		if ( ! $gateway->supports( 'recurring_payments' ) ) {

			/**
			 * Fired when a LifterLMS order's recurring charge errors because the selected gateway doesn't support recurring payments.
			 *
			 * @since Unknown
			 *
			 * @param int                    $order_id   WP Post ID of the order.
			 * @param LLMS_Payment_Gateway   $gateway    LLMS_Payment_Gateway extending class instance.
			 * @param LLMS_Controller_Orders $controller This controller's instance.
			 */
			do_action( 'llms_order_recurring_charge_gateway_payments_disabled', $order_id, $gateway, $this );
			llms_log(
				sprintf(
					'Recurring charge for order #%d could not be processed because the gateway no longer supports recurring payments.',
					$order_id
				),
				'recurring-payments'
			);

			$order->add_note( __( 'Recurring charge skipped because recurring payments are disabled for the payment gateway.', 'lifterlms' ) );
			return false;

		}

		// Recurring payments disabled as a site feature when in staging mode.
		if ( ! LLMS_Site::get_feature( 'recurring_payments' ) ) {

			/**
			 * Fired when a LifterLMS order's recurring charge errors because the recurring payments site feature is disabled.
			 *
			 * @since Unknown
			 *
			 * @param int                    $order_id   WP Post ID of the order.
			 * @param LLMS_Payment_Gateway   $gateway    LLMS_Payment_Gateway extending class instance.
			 * @param LLMS_Controller_Orders $controller This controller's instance.
			 */
			do_action( 'llms_order_recurring_charge_skipped', $order_id, $gateway, $this );
			$order->add_note( __( 'Recurring charge skipped because recurring payments are disabled in staging mode.', 'lifterlms' ) );
			return false;

		}

		// Related product removed.
		if ( empty( $order->get_product() ) ) {

			/**
			 * Fired when a LifterLMS order's recurring charge errors because the purchased product (Course/Membership) doesn't exist anymore.
			 *
			 * @since Unknown
			 *
			 * @param int                    $order_id   WP Post ID of the order.
			 * @param LLMS_Controller_Orders $controller This controller's instance.
			 */
			do_action( 'llms_order_recurring_charge_aborted_product_deleted', $order_id, $this );
			llms_log(
				sprintf(
					'Recurring charge for order #%d could not be processed because the product #%d does not exist anymore.',
					$order_id,
					$order->get( 'product_id' )
				),
				'recurring-payments'
			);

			$order->add_note( __( 'Recurring charge aborted because the purchased product does not exist anymore.', 'lifterlms' ) );
			return false;

		}

		// Passed validation, hand off to the gateway.
		$gateway->handle_recurring_transaction( $order );
		return true;

	}

	/**
	 * Sets an order's post status to `llms-pending` when untrashing an order.
	 *
	 * This is a filter hook callback for the WP core filter `wp_untrash_post_status`.
	 *
	 * @since 7.0.0
	 *
	 * @param string $new_status      The new status of the post after untrashing.
	 * @param int    $post_id         The WP_Post ID of the order.
	 * @param string $previous_status The status of the post at the point where it was trashed.
	 * @return string
	 */
	public function set_untrash_status( $new_status, $post_id, $previous_status ) {

		if ( 'llms_order' === get_post_type( $post_id ) ) {
			/**
			 * Filters the status that an order post gets assigned when it is restored from the trash.
			 *
			 * This is a filter nearly identical to `wp_untrash_post_status` applied specifically to `llms_order` posts.
			 *
			 * @since 7.0.0
			 *
			 * @link https://developer.wordpress.org/reference/hooks/wp_untrash_post_status/
			 *
			 * @param string $new_status      The new status of the post being restored.
			 * @param int    $post_id         The ID of the post being restored.
			 * @param string $previous_status The status of the post at the point where it was trashed.
			 */
			$new_status = apply_filters( 'llms_untrash_order_status', 'llms-pending', $post_id, $previous_status );
		}

		return $new_status;

	}

	/**
	 * When a transaction fails, update the parent order's status.
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
			return;
		}

		if ( $order->can_be_retried() ) {

			$order->maybe_schedule_retry();

		} else {

			$order->set( 'status', 'llms-failed' );

		}

	}

	/**
	 * When a transaction is refunded, update the parent order's status.
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
	 * When a transaction succeeds, update the parent order's status.
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
			return;
		}

		// Update the status based on the order type.
		$status = $order->is_recurring() ? 'llms-active' : 'llms-completed';
		$order->set( 'status', $status );
		$order->set( 'last_retry_rule', '' ); // Retries should always start with tne first rule for new transactions.

		// Maybe schedule a payment.
		$order->maybe_schedule_payment();

	}

	/**
	 * Trigger actions when the status of LifterLMS Orders and LifterLMS Transactions change status.
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $post       WP_Post instance of the transaction.
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

		/**
		 * Fired when a LifterLMS order or transaction changes status.
		 *
		 * The first dynamic portion of this hook, `$post_type`, refers to the unprefixed object post type ('order|transaction').
		 * The second dynamic portion of this hook, `$old_status`, refers to the previous object status.
		 * The third dynamic portion of this hook, `$new_status`, refers to the new object status.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order|LLMS_Transaction $object     The LifterLMS order or transaction instance.
		 * @param string                      $old_status The previous order or transaction status.
		 * @param string                      $new_status The new order or transaction status.
		 */
		do_action( "lifterlms_{$post_type}_status_{$old_status}_to_{$new_status}", $obj, $old_status, $new_status );

		/**
		 * Fired when a LifterLMS order or transaction changes status.
		 *
		 * The first dynamic portion of this hook, `$post_type`, refers to the unprefixed object post type ('order|transaction').
		 * The second dynamic portion of this hook, `$new_status`, refers to the new object status.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order|LLMS_Transaction $object     The LifterLMS order or transaction instance.
		 * @param string                      $old_status The previous order or transaction status.
		 * @param string                      $new_status The new order or transaction status.
		 */
		do_action( "lifterlms_{$post_type}_status_{$new_status}", $obj, $old_status, $new_status );

	}

	/**
	 * Validate a gateway can be used to process the current action / transaction.
	 *
	 * @since 3.10.0
	 *
	 * @param string           $gateway_id Gateway's id.
	 * @param LLMS_Access_Plan $plan       Instance of the LLMS_Access_Plan related to the action/transaction.
	 * @return WP_Error|LLMS_Payment_Gateway WP_Error or LLMS_Payment_Gateway subclass.
	 */
	private function validate_selected_gateway( $gateway_id, $plan ) {

		$gateway = llms()->payment_gateways()->get_gateway_by_id( $gateway_id );
		$err     = new WP_Error();

		// Valid gateway.
		if ( is_subclass_of( $gateway, 'LLMS_Payment_Gateway' ) ) {

			// Gateway not enabled.
			if ( 'manual' !== $gateway->get_id() && ! $gateway->is_enabled() ) {

				return $err->add( 'gateway-error', __( 'The selected payment gateway is not currently enabled.', 'lifterlms' ) );

				// It's a recurring plan and the gateway doesn't support recurring.
			} elseif ( $plan->is_recurring() && ! $gateway->supports( 'recurring_payments' ) ) {
				// Translators: %s = The gateway display name.
				return $err->add( 'gateway-error', sprintf( __( '%s does not support recurring payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ) );

				// Not recurring and the gateway doesn't support single payments.
			} elseif ( ! $plan->is_recurring() && ! $gateway->supports( 'single_payments' ) ) {
				// Translators: %s = The gateway display name.
				return $err->add( 'gateway-error', sprintf( __( '%s does not support single payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ) );

			}
		} else {

			return $err->add( 'invalid-gateway', __( 'An invalid payment method was selected.', 'lifterlms' ) );

		}

		return $gateway;

	}

	/**
	 * Confirm order form post.
	 *
	 * User clicks confirm order or gateway determines the order is confirmed.
	 *
	 * Executes payment gateway confirm order method and completes order.
	 * Redirects user to appropriate page / post
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Unknown.
	 * @since 3.34.4 Added filter `llms_order_can_be_confirmed`.
	 * @since 3.34.5 Fixed logic error in `llms_order_can_be_confirmed` conditional.
	 * @since 3.35.0 Return early if nonce doesn't pass verification and sanitize `$_POST` data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @deprecated 7.0.0 Deprecated in favor of {@see LLMS_Controller_Checkout::confirm_pending_order()}.
	 *
	 * @return void
	 */
	public function confirm_pending_order() {
		_deprecated_function( __METHOD__, '7.0.0', 'LLMS_Controller_Checkout::confirm_pending_order' );
		LLMS_Controller_Checkout::instance()->confirm_pending_order();
	}

	/**
	 * Handle form submission of the checkout / payment form.
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
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @deprecated 7.0.0 Deprecated in favor of {@see LLMS_Controller_Checkout::create_pending_order()}.
	 *
	 * @return void
	 */
	public function create_pending_order() {
		_deprecated_function( __METHOD__, '7.0.0', 'LLMS_Controller_Checkout::create_pending_order' );
		LLMS_Controller_Checkout::instance()->create_pending_order();
	}


	/**
	 * Handle form submission of the "Update Payment Method" form on the student dashboard when viewing a single order.
	 *
	 * @since 3.10.0
	 * @since 3.19.0 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @deprecated 7.0.0 Deprecated in favor of {@see LLMS_Controller_Checkout::switch_payment_source()}.
	 *
	 * @return void
	 */
	public function switch_payment_source() {
		_deprecated_function( __METHOD__, '7.0.0', 'LLMS_Controller_Checkout::switch_payment_source' );
		LLMS_Controller_Checkout::instance()->switch_payment_source();
	}

}

return new LLMS_Controller_Orders();
