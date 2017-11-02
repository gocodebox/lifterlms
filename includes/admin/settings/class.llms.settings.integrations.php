<?php
/**
* Admin Settings Page, Integrations Tab
* @since    1.0.0
* @version  3.12.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Settings_Integrations extends LLMS_Settings_Page {

	/**
	 * Constructor
	 * executes settings tab actions
	 * @since    1.0.0
	 * @version  1.0.0
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
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_settings() {
		return apply_filters( 'lifterlms_integrations_settings', array() );
	}

	/**
	 * save settings to the database
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function save() {

		$settings = $this->get_settings();
		LLMS_Admin_Settings::save_fields( $settings );

	}

}

return new LLMS_Settings_Integrations();
