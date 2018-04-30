<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Integrations
 * @since    1.0.0
 * @version  [version]
 */
class LLMS_Integrations {

	protected static $_instance = null;

	var $integrations;

	/**
	 * Instance Singleton Generator
	 * @return   obj
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 * @since    1.0.0
	 * @version  [version]
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Get an integration instance by id
	 * @param    string     $id  id of the integration
	 * @return   obj|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_integration( $id ) {
		$available = $this->get_available_integrations();
		return isset( $available[ $id ] ) ? $available[ $id ] : false;
	}

	/**
	 * Initalize Integration Classes
	 * @return   null
	 * @since    1.0.0
	 * @version  [version]
	 */
	public function init() {

		$integrations = apply_filters( 'lifterlms_integrations', array(
			'LLMS_Integration_BBPress',
			'LLMS_Integration_Buddypress',
		) );

		$order_end = 999;

		if ( ! empty( $integrations ) ) {

			foreach ( $integrations as $integration ) {

					$load_integration = new $integration();

					$this->integrations[ $order_end ] = $load_integration;
					$order_end++;

				ksort( $this->integrations );
			}

		}

	}

	/**
	 * Get available integrations
	 * @return array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_available_integrations() {

		$_available_integrations = array();

		foreach ( $this->integrations as $integration ) :

			if ( $integration->is_available() ) {

					$_available_integrations[ $integration->id ] = $integration;
			}

		endforeach;

		return apply_filters( 'lifterlms_available_integrations', $_available_integrations );
	}

	/**
	 * Get all available integrations
	 * @return array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	function integrations() {

		$_available_integrations = array();

		if ( sizeof( $this->integrations ) > 0 ) {
			foreach ( $this->integrations as $integration ) {
				$_available_integrations[ $integration->id ] = $integration; }
		}

		return $_available_integrations;
	}

}
