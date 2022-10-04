<?php
/**
 * LLMS_Meta_Box_Order_Details class
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 3.0.0
 * @version 7.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Order Details meta box
 *
 * @since 3.0.0
 */
class LLMS_Meta_Box_Order_Details extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-order-details';
		$this->title    = __( 'Order Details', 'lifterlms' );
		$this->screens  = array(
			'llms_order',
		);
		$this->context  = 'normal';
		$this->priority = 'high';

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Output metabox content
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 * @since 5.3.0 Use llms() in favor of deprecated LLMS().
	 *
	 * @return void
	 */
	public function output() {

		$order = llms_get_post( $this->post );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}

		$gateway = $order->get_gateway();

		// Setup a list of gateways that this order can be switched to.
		$gateway_feature           = $order->is_recurring() ? 'recurring_payments' : 'single_payments';
		$switchable_gateways       = array();
		$switchable_gateway_fields = array();
		foreach ( llms()->payment_gateways()->get_supporting_gateways( $gateway_feature ) as $id => $gateway_obj ) {
			$switchable_gateways[ $id ]       = $gateway_obj->get_admin_title();
			$switchable_gateway_fields[ $id ] = $gateway_obj->get_admin_order_fields();
		}

		include LLMS_PLUGIN_DIR . 'includes/admin/views/metaboxes/view-order-details.php';

	}

	/**
	 * Save method
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 * @since 5.3.0 Update return value from void to int (for testing conditions) and include update remaining payment data when necessary.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param int $post_id Post ID of the Order.
	 * @return int Returns `-1` on invalid or missing nonce, `0` when an order cannot be found, and
	 *             `1` otherwise.
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return -1;
		}

		$order = llms_get_post( $post_id );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return 0;
		}

		$fields = array(
			'payment_gateway',
			'gateway_customer_id',
			'gateway_subscription_id',
			'gateway_source_id',
		);

		foreach ( $fields as $key ) {

			if ( isset( $_POST[ $key ] ) ) {
				$order->set( $key, llms_filter_input_sanitize_string( INPUT_POST, $key ) );
			}
		}

		$this->save_remaining_payments( $order );

		return 1;

	}

	/**
	 * Save remaining payment date for expiring recurring orders
	 *
	 * @since 5.3.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 7.0.0 Return `-1` if the recurring payment cannot be modified (the gateway doesn't support this feature).
	 *
	 * @param LLMS_Order $order Order object
	 * @return int Returns `-1` when there's for invalid order types, `0` when there's no changes to save, and
	 *             `1` when remaining payment data is updated.
	 */
	protected function save_remaining_payments( $order ) {

		// If it's not a payment plan or cannot modify recurring payment, don't proceed.
		if ( ! $order->has_plan_expiration() || ! $order->supports_modify_recurring_payments() ) {
			return -1;
		}

		$payments  = (int) llms_filter_input( INPUT_POST, '_llms_remaining_payments', FILTER_SANITIZE_NUMBER_INT );
		$remaining = $order->get_remaining_payments();

		// Nothing to save.
		if ( $payments < 1 || $payments === $remaining ) {
			return 0;
		}

		// Determine how to adjust the billing length.
		$adjustment = $payments - $remaining;
		$original   = $order->get( 'billing_length' );
		$new_length = max( $order->get( 'billing_length' ) + $adjustment, 1 );
		$period     = $order->get( 'billing_period' );

		// Update the payment plan.
		$order->set( 'billing_length', $new_length );

		// Record that the payment plan has been modified.
		$order->add_note(
			sprintf(
				// Translators: %1$d is the original billing length; %2$s is the billing period (adjusted for pluralization against the original billing length); %3$d is the new billing length; %4$s is the billing period (adjusted for pluralization against the new billing length).
				__( 'The billing length of the order has been modified from %1$d %2$s to %3$d %4$s.', 'lifterlms' ),
				$original,
				llms_get_time_period_l10n( $period, $original ),
				$new_length,
				llms_get_time_period_l10n( $period, $new_length )
			)
		);

		// Store a use note if one was entered.
		$note = llms_filter_input_sanitize_string( INPUT_POST, '_llms_remaining_note' );
		if ( $note ) {
			$order->add_note( wp_strip_all_tags( $note ), true );
		}

		// Restart scheduled payments.
		if ( ! $order->has_scheduled_payment() ) {
			$order->maybe_schedule_payment();
		}

		return 1;

	}

}
