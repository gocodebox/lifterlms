<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Integrations
*
* @author codeBOX
*/
class LLMS_Integrations {
	protected static $_instance = null;

	var $integrations;

	/**
	 * Instance Generator
	 *
	 * @return object
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
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
	 * @return null
	 */
	public function init() {
		$load_integrations = apply_filters( 'lifterlms_integrations', array(
			'LLMS_Integration_BBPress',
			'LLMS_Integration_Buddypress',
		) );

		$order_end = 999;

		foreach ( $load_integrations as $integration ) :

			$load_integration = new $integration();

			$this->integrations[ $order_end ] = $load_integration;
			$order_end++;

		endforeach;

		ksort( $this->integrations );
	}

	/**
	 * Get available integrations
	 *
	 * @access public
	 * @return array
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
	 *
	 * @return array [array of all integrations]
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
