<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Payment Gateway class
*
* Class for managing payment gateways
*/
class LLMS_Payment_Gateways {

	/**
	 * Payment Gateways
	 * @var array
	 */
	var $payment_gateways;

	/**
	 * private instance of class
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * Create instance of class
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 * initializes class
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Get all payment gateways
	 *
	 * @return array $_available_gateways [private array of all payment gateways]
	 */
	function payment_gateways() {
		$_available_gateways = array();
		if ( sizeof( $this->payment_gateways ) > 0 ) {
			foreach ( $this->payment_gateways as $gateway ) {
				$_available_gateways[ $gateway->id ] = $gateway; } }

		return $_available_gateways;
	}

	/**
	 * Initialize payment gateways
	 *
	 * @return void
	 */
	function init() {
		$load_gateways = apply_filters( 'lifterlms_payment_gateways', array(
			'LLMS_Payment_Gateway_Paypal'
		) );

		$order_end 	= 999;
		foreach ($load_gateways as $gateway) :

			$load_gateway = new $gateway();

				$this->payment_gateways[ $order_end ] = $load_gateway;
				$order_end++;

		endforeach;

		ksort( $this->payment_gateways );
	}

	/**
	 * Get available gateways.
	 *
	 * @access public
	 * @return array
	 */
	function get_available_payment_gateways() {
		$_available_gateways = array();

		foreach ( $this->payment_gateways as $gateway ) :
			if ( $gateway->is_available() ) {
					$_available_gateways[ $gateway->id ] = $gateway;
			}

		endforeach;

		return apply_filters( 'lifterlms_available_payment_gateways', $_available_gateways );
	}


	/**
	 * Retrive a payment gateway object by the payment gateway ID
	 *
	 * @param  string $id  id of the gateway (paypal, stripe, etc...)
	 * @return mixed       instance of the gateway object OR false
	 *
	 * @since  2.5.0
	 */
	function get_gateway_by_id( $id ) {

		$gateways = $this->payment_gateways();

		if ( array_key_exists( $id, $gateways ) ) {

			return $gateways[ $id ];

		}

		return false;

	}


	/**
	 * Check if payment gateway can process recurring payments
	 *
	 * @return bool [can gateway handle recurring payments]
	 */
	public function can_process_recurring() {
		$is_available = true;
		return $is_available;
	}

}
