<?php
/**
 * LLMS_Payment_Gateways class file.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 6.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage and access LifterLMS payment gateways.
 *
 * @since 1.0.0
 * @since 3.0.0 Unknown.
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since 6.0.0 Removed the deprecated `LLMS_Payment_Gateways::$_instance` property.
 */
class LLMS_Payment_Gateways {

	use LLMS_Trait_Singleton;

	/**
	 * Payment Gateways
	 *
	 * @var LLMS_Payment_Gateway[]
	 */
	public $payment_gateways = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'lifterlms_payment_gateways', array( $this, 'add_core_gateways' ) );

		/**
		 * Filters the list of registered LifterLMS Payment Gateway classes.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $gateways Array of payment gateway class names.
		 */
		$gateways = apply_filters( 'lifterlms_payment_gateways', $this->payment_gateways );

		foreach ( $gateways as $gateway ) {

			$load_gateway = new $gateway();

			$order = absint( $load_gateway->get_display_order() );

			// If the order already exists create a new order for it.
			if ( isset( $this->payment_gateways[ $order ] ) ) {
				$order = max( array_keys( $this->payment_gateways ) ) + 1;
			}

			$this->payment_gateways[ $order ] = $load_gateway;
		}

		ksort( $this->payment_gateways );

	}

	/**
	 * Register core gateways.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $gateways Array of gateway class names.
	 * @return string[]
	 */
	public function add_core_gateways( $gateways ) {
		$gateways[] = 'LLMS_Payment_Gateway_Manual';
		return $gateways;
	}

	/**
	 * Get only enabled payment gateways.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_enabled_payment_gateways() {

		$gateways = array();
		foreach ( $this->get_payment_gateways() as $gateway ) {
			if ( $gateway->is_enabled() ) {
				$gateways[ $gateway->get_id() ] = $gateway;
			}
		}

		/**
		 * Filters the registered LifterLMS Payment Gateways which are explicitly enabled.
		 *
		 * @since 3.0.0
		 *
		 * @param LLMS_Payment_Gateway[] $gateways List of enabled gateways.
		 */
		return apply_filters( 'lifterlms_enabled_payment_gateways', $gateways );

	}

	/**
	 * Retrieves the default payment gateway ID.
	 *
	 * The default gateway is the first gateway in the list of enabled gateways.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_default_gateway() {

		$gateways = $this->get_enabled_payment_gateways();
		$ids      = array_keys( $gateways );
		return array_shift( $ids );

	}

	/**
	 * Retrieves a payment gateway object by the gateway ID.
	 *
	 * @since 2.5.0
	 *
	 * @param string $id  id of the gateway (paypal, stripe, etc...)
	 * @return LLMS_Payment_Gateway|boolean Returns the gateway if it's registered, otherwise `false`.
	 */
	public function get_gateway_by_id( $id ) {

		$gateways = $this->get_payment_gateways();

		if ( array_key_exists( $id, $gateways ) ) {
			return $gateways[ $id ];
		}

		return false;

	}

	/**
	 * Retrieves all registered payment gateways.
	 *
	 * @since 3.0.0
	 *
	 * @return LLMS_Payment_Gateway[]
	 */
	public function get_payment_gateways() {

		$gateways = array();
		foreach ( $this->payment_gateways as $gateway ) {
			$gateways[ $gateway->id ] = $gateway;
		}
		return $gateways;

	}

	/**
	 * Retrieves all enabled gateways which support the specified gateway feature.
	 *
	 * @since 3.10.0
	 *
	 * @see LLMS_Payment_Gateway::get_supported_features()
	 *
	 * @param string $feature A gateway feature string.
	 * @return array
	 */
	public function get_supporting_gateways( $feature ) {

		$gateways = array();
		foreach ( $this->get_enabled_payment_gateways() as $id => $gateway ) {
			if ( $gateway->supports( $feature ) ) {
				$gateways[ $id ] = $gateway;
			}
		}

		/**
		 * Filters the list of gateways supporting the specified feature.
		 *
		 * Hook description.
		 *
		 * @since 3.10.0
		 *
		 * @param LLMS_Payment_Gateway[] $gateways Array of supporting gateways.
		 * @param string                 $feature  The requested gateway feature string.
		 */
		return apply_filters( 'lifterlms_supporting_payment_gateways', $gateways, $feature );

	}

	/**
	 * Determines if any payment gateways are registered.
	 *
	 * @since 3.0.0
	 * @since 6.5.0 Refactor for code simplicity.
	 *
	 * @param boolean $enabled Whether or not to check against only enabled gateways.
	 * @return boolean
	 */
	public function has_gateways( $enabled = false ) {
		$method = $enabled ? 'get_enabled_payment_gateways' : 'get_payment_gateways';
		return count( $this->{$method}() ) >= 1;
	}

}
