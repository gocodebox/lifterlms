<?php
/**
 * LifterLMS Integrations
 *
 * @since 1.0.0
 * @version 3.33.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Integrations
 *
 * @since 1.0.0
 * @since 3.18.2 Updated.
 * @since 3.33.1 Integrations are now loaded based on their defined priority.
 * @since 3.33.2 Integration priority checks are backwards compatible to handle deprecated legacy integrations.
 */
class LLMS_Integrations {

	protected static $_instance = null;

	/**
	 * Array of integrations, regardless of availability
	 *
	 * @var  LLMS_Abstract_Integration[]
	 */
	private $integrations = array();

	/**
	 * Instance Singleton Generator
	 *
	 * @return   LLMS_Integrations
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
	 *
	 * @since    1.0.0
	 * @version  3.17.8
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Get an integration instance by id
	 *
	 * @param    string $id  id of the integration
	 * @return   LLMS_Abstract_Integration|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_integration( $id ) {
		$available = $this->get_available_integrations();
		return isset( $available[ $id ] ) ? $available[ $id ] : false;
	}

	/**
	 * Initialize Integration Classes
	 *
	 * @since 1.0.0
	 * @since 3.18.0 Updated.
	 * @since 3.33.1 Updated sort order to be based off the priority defined for the integration.
	 * @since 3.33.2 Made sort order check backwards compatible with deprecated legacy integrations.
	 *
	 * @return void
	 */
	public function init() {

		$integrations = apply_filters(
			'lifterlms_integrations',
			array(
				'LLMS_Integration_BBPress',
				'LLMS_Integration_Buddypress',
			)
		);

		if ( ! empty( $integrations ) ) {

			foreach ( $integrations as $integration ) {

				$load_integration = new $integration();

				$priority = method_exists( $load_integration, 'get_priority' ) ? $load_integration->get_priority() : 50;
				while ( array_key_exists( (string) $priority, $this->integrations ) ) {
					$priority += .01;
				}

				$this->integrations[ (string) $priority ] = $load_integration;

				ksort( $this->integrations );

			}
		}

		do_action( 'llms_integrations_init', $this );

	}

	/**
	 * Get available integrations
	 *
	 * @return   LLMS_Abstract_Integration[]
	 * @since    1.0.0
	 * @version  3.17.8
	 */
	public function get_available_integrations() {

		$_available_integrations = array();

		foreach ( $this->integrations as $integration ) {

			if ( $integration->is_available() ) {

				$_available_integrations[ $integration->id ] = $integration;
			}
		}

		return apply_filters( 'lifterlms_available_integrations', $_available_integrations );
	}

	/**
	 * Get all integrations regardless of availability
	 *
	 * @return   LLMS_Abstract_Integration[]
	 * @since    3.18.2
	 * @version  3.18.2
	 */
	public function get_integrations() {
		return $this->integrations;
	}

	/**
	 * Get all integrations regardless of availability
	 *
	 * @return   LLMS_Abstract_Integration[]
	 * @since    1.0.0
	 * @version  3.17.8
	 * @todo     deprecate
	 */
	public function integrations() {
		return $this->get_integrations();

	}

}
