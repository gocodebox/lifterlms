<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Payment Gateway class
*
* Class for managing payment gateways
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Payment_Gateways {

	var $payment_gateways;


	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->init();
	}

    function payment_gateways() {

		$_available_gateways = array();

		if ( sizeof( $this->payment_gateways ) > 0 )
			foreach ( $this->payment_gateways as $gateway )
				$_available_gateways[ $gateway->id ] = $gateway;

		return $_available_gateways;
	}

	function init() {

    	$load_gateways = apply_filters( 'lifterlms_payment_gateways', array(
			'LLMS_Payment_Gateway_Paypal'
    	) );

		$order_end 	= 999;

		foreach ($load_gateways as $gateway) :

			$load_gateway = new $gateway();

				$this->payment_gateways[$order_end] = $load_gateway;
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
				
					$_available_gateways[$gateway->id] = $gateway;
				}

		endforeach;

		return apply_filters( 'lifterlms_available_payment_gateways', $_available_gateways );
	}

	public function can_process_recurring() {
		$is_available = true;
		return $is_available;
	}

}