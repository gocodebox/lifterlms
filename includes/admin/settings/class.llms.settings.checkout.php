<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page, Checkout Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_Checkout extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'checkout';
		$this->label = __( 'Checkout', 'lifterlms' );

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
		// Get checkout page
			$checkout_page_id = llms_get_page_id( 'checkout' );

			$base_slug = ($checkout_page_id > 0 && get_page( $checkout_page_id )) ? get_page_uri( $checkout_page_id ) : 'checkout';

			$currency_code_options = get_lifterlms_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

			return apply_filters( 'lifterlms_course_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'course_archive_options', 'class' => 'top' ),

				array( 'title' => __( 'Checkout Settings', 'lifterlms' ), 'type' => 'title','desc' => 'Manage checkout settings.', 'id' => 'course_options' ),

				array(
					'title' => __( 'Checkout Page', 'lifterlms' ),
					'desc' 		=> '<br/>' . sprintf( __( 'Page used for displaying the checkout form.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
					'id' 		=> 'lifterlms_checkout_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'desc_tip'	=> __( 'This sets the base page of the checkout page', 'lifterlms' ),
				),
				array(
					'title' => __( 'Confirm Payment', 'lifterlms' ),
					'desc' 		=> __( 'Payment Confirmation Page', 'lifterlms' ),
					'id' 		=> 'lifterlms_myaccount_confirm_payment_endpoint',
					'type' 		=> 'text',
					'default'	=> 'confirm-payment',
					'desc_tip'	=> true,
				),

				array( 'type' => 'sectionend', 'id' => 'course_archive_options' ),

				array( 'type' => 'sectionstart', 'id' => 'general_options' ),

				array( 'title' => __( 'Currency Options', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'The following options affect how prices are displayed on the frontend.', 'lifterlms' ), 'id' => 'pricing_options' ),

				array(
						'title' 	=> __( 'Default Currency', 'lifterlms' ),
						'desc' 		=> __( 'Default currency type.', 'lifterlms' ),
						'id' 		=> 'lifterlms_currency',
						'default'	=> 'USD',
						'type' 		=> 'select',
						'class'		=> 'chosen_select',
						'desc_tip'	=> true,
						'options'   => $currency_code_options,
				),

				array( 'type' => 'sectionend', 'id' => 'general_options' ),

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

return new LLMS_Settings_Checkout();
