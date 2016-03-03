<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page, Gateways Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_Gateways extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'gateways';
		$this->label = __( 'Gateways', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		// Get gateways page
			$gateways_page_id = llms_get_page_id( 'gateways' );

			$base_slug = ($gateways_page_id > 0 && get_page( $gateways_page_id )) ? get_page_uri( $gateways_page_id ) : 'gateways';

			return apply_filters( 'lifterlms_gateway_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'gateways_options', 'class' => 'top' ),

				array( 'title' => __( 'Paypal Settings', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'Enable Paypal payment gateway', 'lifterlms' ), 'id' => 'gateway_paypal_options' ),

				array(
				'desc'          => __( 'Enable Paypal', 'lifterlms' ),
				'id'            => 'lifterlms_gateway_enable_paypal',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
				),

				array(
				'desc'          => __( 'Enable Sandbox Mode', 'lifterlms' ),
				'id'            => 'lifterlms_gateways_paypal_enable_sandbox',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'autoload'      => false,
				),

				array(
					'desc'          => __( 'Enable Debug Mode. <i>Display Response Messages on Checkout Screen</i>', 'lifterlms' ),
					'id'            => 'lifterlms_gateways_paypal_enable_debug',
					'default'       => false,
					'type'          => 'checkbox',
					'checkboxgroup' => 'end',
					'autoload'      => false,
				),

				array(
				'title' => __( 'API Username', 'lifterlms' ),
				'desc' 		=> __( 'API Username', 'lifterlms' ),
				'id' 		=> 'lifterlms_gateways_paypal_email',
				'type' 		=> 'text',
				'default'	=> '',
				'desc_tip'	=> true,
				),

				array(
				'title' => __( 'API Password', 'lifterlms' ),
				'desc' 		=> __( 'API Password', 'lifterlms' ),
				'id' 		=> 'lifterlms_gateways_paypal_password',
				'type' 		=> 'text',
				'default'	=> '',
				'desc_tip'	=> true,
				),

				array(
				'title' => __( 'API Signature', 'lifterlms' ),
				'desc' 		=> __( 'API Signature', 'lifterlms' ),
				'id' 		=> 'lifterlms_gateways_paypal_signature',
				'type' 		=> 'text',
				'default'	=> '',
				'desc_tip'	=> true,
				),

				array( 'type' => 'sectionend', 'id' => 'gateways_options' ),
				)
			);
	}

	/**
	 * save settings to the database
	 *
	 * @return LLMS_Admin_Settings::save_fields
	 */
	public function save() {
		$settings = $this->get_settings();

		LLMS_Admin_Settings::save_fields( $settings );

	}

	/**
	 * get settings from the database
	 *
	 * @return array
	 */
	public function output() {
		$settings = $this->get_settings( );

			LLMS_Admin_Settings::output_fields( $settings );
	}

}

return new LLMS_Settings_Gateways();
