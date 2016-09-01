<?php
/**
* Manual Payment Gateway Class
*
* @version 3.0.0
*/
if ( ! defined( 'ABSPATH' ) ) exit;
class LLMS_Payment_Gateway_Manual extends LLMS_Payment_Gateway {

	/**
	 * Constructor
	 * @return  void
	 * @since  3.0.0
	 * @version 3.0.0
	 */
	public function __construct() {

		$this->id = 'manual';
		$this->admin_description = __( 'Collect manual or offline payments. Also handles any free orders during checkout.', 'lifterlms' );
		$this->admin_title = __( 'Manual', 'lifterlms' );
		$this->title = __( 'Manual', 'lifterlms' );
		$this->description = __( 'Pay for manually', 'lifterlms' );

		$this->supports = array(
			'checkout_fields' => false,
			'refunds' => false,
			'single_payments' => true,
			'recurring_payments' => false,
			'test_mode' => false,
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
	 * @version 3.0.0
	 */
	public function handle_pending_order( $order, $plan, $person, $coupon = false ) {

		// @todo: validate to allow only free orders if this is being called from the frontend

		$order->set( 'status', 'llms-completed' );
		$this->complete_transaction( $order );

	}

		/**
	 * Determine if the gateway is enabled according to admin settings checkbox
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_enabled() {

		if ( ! defined( 'DOING_AJAX' ) && is_admin() ) {
			return true;
		}

		return ( 'yes' === $this->get_enabled() ) ? true : false;
	}

}
