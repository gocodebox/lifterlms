<?php
/**
 * Mock payment gateway for testing.
 *
 * @since [version]
 */
class LLMS_Payment_Gateway_Mock extends LLMS_Payment_Gateway {

	/**
	 * Constructor
	 *
	 * @return  void
	 * @since   3.0.0
	 * @version 3.10.0
	 */
	public function __construct() {

		$this->id                   = 'mock';
		$this->admin_description    = __( 'Mock payment gateway used for unit testing', 'lifterlms' );
		$this->admin_title          = __( 'Mock', 'lifterlms' );
		$this->title                = __( 'Mock', 'lifterlms' );
		$this->description          = __( 'Make mock payments', 'lifterlms' );

		$this->supports = array(
			'checkout_fields'    => false,
			'refunds'            => true,
			'single_payments'    => true,
			'recurring_payments' => true,
			'test_mode'          => false,
		);

	}

	/**
	 * Handle a Pending Order
	 * Called by LLMS_Controller_Orders->create_pending_order() on checkout form submission
	 * All data will be validated before it's passed to this function
	 *
	 * @param   obj       $order   Instance LLMS_Order for the order being processed
	 * @param   obj       $plan    Instance LLMS_Access_Plan for the order being processed
	 * @param   obj       $person  Instance of LLMS_Student for the purchasing customer
	 * @param   obj|false $coupon  Instance of LLMS_Coupon applied to the order being processed, or false when none is being used
	 * @return  void
	 * @since   3.0.0
	 * @version 3.10.0
	 */
	public function handle_pending_order( $order, $plan, $person, $coupon = false ) {

		$payment_type = 'single';

		if ( $order->is_recurring() ) {
			$payment_type = $order->has_trial() ? 'trial' : 'recurring';
		}

		$order->record_transaction(
			array(
				'amount'             => $order->get_initial_price( array(), 'float' ),
				'source_description' => 'Mock Payment',
				'transaction_id'     => uniqid( 'mock-' ),
				'status'             => 'llms-txn-succeeded',
				'payment_gateway'    => $this->id,
				'payment_type'       => $payment_type,
			)
		);

	}

	/**
	 * Called by scheduled actions to charge an order for a scheduled recurring transaction
	 *
	 * This function must be defined by gateways which support recurring transactions
	 *
	 * @since [version]
	 *
	 * @param obj $order Instance LLMS_Order for the order being processed
	 * @return mixed
	 */
	public function handle_recurring_transaction( $order ) {

		$order->record_transaction(
			array(
				'amount'             => $order->get_price( 'total', array(), 'float' ),
				'source_description' => 'Mock Payment',
				'transaction_id'     => uniqid( 'mock-' ),
				'status'             => 'llms-txn-succeeded',
				'payment_gateway'    => $this->id,
				'payment_type'       => 'recurring',
			)
		);

	}

	/**
	 * Determine if the gateway is enabled according to admin settings checkbox.
	 *
	 * The mock gateway is always enabled.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return true;
	}

}
