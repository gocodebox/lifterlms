<?php
/**
 * Payment Gateway class
 *
 * Class for managing payment gateways
 *
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Payment_Gateways {

	/**
	 * Payment Gateways
	 *
	 * @var array
	 */
	public $payment_gateways = array();

	/**
	 * private instance of class
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Create instance of class
	 *
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
	 *
	 * @version  3.0.0
	 */
	public function __construct() {

		add_filter( 'lifterlms_payment_gateways', array( $this, 'add_core_gateways' ) );

		$gateways = apply_filters( 'lifterlms_payment_gateways', $this->payment_gateways );

		foreach ( $gateways as $gateway ) {

			$load_gateway = new $gateway();

			$order = absint( $load_gateway->get_display_order() );

			// if the order already exists create a new order for it
			if ( isset( $this->payment_gateways[ $order ] ) ) {

				$order = max( array_keys( $this->payment_gateways ) ) + 1;

			}

			$this->payment_gateways[ $order ] = $load_gateway;
		}

		ksort( $this->payment_gateways );

	}

	/**
	 * Register core gateways
	 *
	 * @param    array $gateways  array of gateways
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function add_core_gateways( $gateways ) {
		$gateways[] = 'LLMS_Payment_Gateway_Manual';
		return $gateways;
	}

	/**
	 * Get only enabled payment gateways
	 *
	 * @access public
	 * @return array
	 * @version  3.0.0
	 */
	public function get_enabled_payment_gateways() {

		$gateways = array();
		foreach ( $this->get_payment_gateways() as $gateway ) {
			if ( $gateway->is_enabled() ) {
				$gateways[ $gateway->get_id() ] = $gateway;
			}
		}

		return apply_filters( 'lifterlms_enabled_payment_gateways', $gateways );

	}

	/**
	 * Get the ID of the default payment gateway
	 * This will always be the FIRST gateway from the list of all enabled gateways
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function get_default_gateway() {

		$gateways = $this->get_enabled_payment_gateways();
		$ids      = array_keys( $gateways );

		return array_shift( $ids );

	}

	/**
	 * Retrieve a payment gateway object by the payment gateway ID
	 *
	 * @param  string $id  id of the gateway (paypal, stripe, etc...)
	 * @return mixed       instance of the gateway object OR false
	 *
	 * @since  2.5.0
	 */
	public function get_gateway_by_id( $id ) {

		$gateways = $this->get_payment_gateways();

		if ( array_key_exists( $id, $gateways ) ) {

			return $gateways[ $id ];

		}

		return false;

	}

	/**
	 * Get all registered payment gateways
	 *
	 * @return array
	 * @version  3.0.0
	 */
	public function get_payment_gateways() {

		$gateways = array();

		foreach ( $this->payment_gateways as $gateway ) {

			$gateways[ $gateway->id ] = $gateway;

		}

		return $gateways;

	}

	/**
	 * Get an array of payment gateways which support a specific gateway feature
	 *
	 * @param    string $feature  a gateway feature string
	 * @return   array
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function get_supporting_gateways( $feature ) {

		$gateways = array();
		foreach ( $this->get_enabled_payment_gateways() as $id => $gateway ) {
			if ( $gateway->supports( $feature ) ) {
				$gateways[ $id ] = $gateway;
			}
		}

		return apply_filters( 'lifterlms_supporting_payment_gateways', $gateways, $feature );

	}

	/**
	 * Determine if any payment gateways are registered
	 *
	 * @param boolean $enabled   if true, will check only enabled gateways
	 * @return boolean
	 * @since  3.0.0
	 */
	public function has_gateways( $enabled = false ) {

		if ( $enabled ) {
			return ( count( $this->get_enabled_payment_gateways() ) ) ? true : false;
		} else {
			return ( count( $this->get_payment_gateways() ) ) ? true : false;
		}

	}

}
