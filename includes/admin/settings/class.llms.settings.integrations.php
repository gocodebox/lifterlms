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

			array(
				'class' => 'top',
				'id' => 'integration_options',
				'type' => 'sectionstart',
			),

			array(
				'title' => __( 'Integration Settings', 'lifterlms' ),
				'type' => 'title',
				'id' => 'integrations_options',
			),

			array(
				'desc' => __( 'Extend LifterLMS functionality to any of the following popular plugins', 'lifterlms' ),
				'title' => __( 'Enable Integrations', 'lifterlms' ),
				'type' => 'subtitle',
			),

			array(
				'title' => __( 'bbPress', 'lifterlms' ),
				'desc' 		=> __( 'Enable', 'lifterlms' ) . '<br>' .
							   sprintf( __( 'Restrict forums and topics within forums to LifterLMS memberships. %1$sLearn More%2$s', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-and-bbpress/" target="_blank">', '</a>' ),
				'id' 		=> 'lifterlms_bbpress_enabled',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'BuddyPress', 'lifterlms' ),
				'desc' 		=> __( 'Enable', 'lifterlms' ) . '<br>' .
							   sprintf( __( 'Add LifterLMS information to BuddyPress profiles. %1$sLearn More%2$s', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-and-buddypress/" target="_blank">', '</a>' ),
				'id' 		=> 'lifterlms_buddypress_enabled',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
				'desc_tip'	=> true,
			),

			array(
				'id' => 'integration_options',
				'type' => 'sectionend',
			),

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
