<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Settings_General' ) ) :

/**
* Admin Settings Page, General Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_General extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
		LLMS()->activate();
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		$is_activated = get_option( 'lifterlms_is_activated', '' );
		$activation_response = get_option( 'lifterlms_activation_message', '' );;
		if($is_activated == 'yes') {
			$activation_message = 'Activated';
		}
		else {
			$activation_message = 'Not Activated (' . $activation_response . ')';
		}

		$currency_code_options = get_lifterlms_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

		return apply_filters( 'lifterlms_general_settings', array(
			array( 'title' => __( 'General Options', 'lifterlms' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),
		
			

			array(	'title' => __( 'Plugin Activation', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'Enter your activation key to recieve important updates and new features when they are available.', 'lifterlms' ), 'id' => 'activation_options' ),

			array(
				'title' => __( 'Activation Key', 'lifterlms' ),
				'desc' 		=> __( $activation_message, 'lifterlms' ),
				'id' 		=> 'lifterlms_activation_key',
				'type' 		=> 'text',
				'default'	=> '',
				'desc_tip'	=> true,
			),

			array( 'type' => 'sectionend', 'id' => 'general_options'),

			array(	'title' => __( 'Currency Options', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'The following options affect how prices are displayed on the frontend.', 'lifterlms' ), 'id' => 'pricing_options' ),

			array(
				'title' 	=> __( 'Default Currency', 'lifterlms' ),
				'desc' 		=> __( 'Default currency type.', 'lifterlms' ),
				'id' 		=> 'lifterlms_currency',
				'css' 		=> 'min-width:350px;',
				'default'	=> 'USD',
				'type' 		=> 'select',
				'class'		=> 'chosen_select',
				'desc_tip'	=>  true,
				'options'   => $currency_code_options
			),

			array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),


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

endif;

return new LLMS_Settings_General();
