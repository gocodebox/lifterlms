<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page, Integrations Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_Integrations extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'integrations';
		$this->label = __( 'Integrations', 'lifterlms' );

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
		$currency_code_options = get_lifterlms_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

		return apply_filters( 'lifterlms_integrations_settings', array(

			array( 'type' => 'sectionstart', 'id' => 'integration_options', 'class' => 'top' ),

			array( 'title' => __( 'Integration Settings', 'lifterlms' ), 'type' => 'title', 'desc' => '', 'id' => 'integrations_options' ),

			array(
				'title' => __( 'Integrations', 'lifterlms' ),
				'desc' 		=> __( 'Enable BuddyPress', 'lifterlms' ),
				'id' 		=> 'lifterlms_buddypress_enabled',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
				'desc_tip'	=> true,
			),
			array(
				// 'title' => __( 'Enable BuddyPress', 'lifterlms' ),
				'desc' 		=> __( 'Enable WooCommerce', 'lifterlms' ),
				'id' 		=> 'lifterlms_woocommerce_enabled',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
				'desc_tip'	=> true,
			),

			array( 'type' => 'sectionend', 'id' => 'integration_options' ),

		) );
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

}

return new LLMS_Settings_Integrations();
