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
		$activation_response = get_option( 'lifterlms_activation_message', '' );
		if($is_activated == 'yes') {
			$activation_message = 'Activated';
			$deactivate_checkbox = 'checkbox';
			$deactivate_message = __( 'Deactivate LifterLMS.', 'lifterlms' );
		}
		else {
			$activation_message = 'Not Activated (' . $activation_response . ')';
			$deactivate_checkbox = '';
			$deactivate_message = '';
		}

		$currency_code_options = get_lifterlms_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

		if ( ! get_option( 'lifterlms_first_time_setup' ) ) {
			return apply_filters( 'lifterlms_general_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'general_information', 'class' =>'top' ),

				array(	'title' => __( 'Welcome to LifterLMS', 
					'lifterlms' ), 
					'type' => 'title', 
					'desc' => '<h2>Getting Started with LifterLMS</h2>
					<p>Before you start creating courses, making lots of money and building the best (insert your business here) online there are a few setup items we need to address.</p>
					<p>First things first. We need to activate your plugin. Enter the activation key you were given when you purchased LifterLMS. If you don\'t have a key that\'s ok. Go ahead and continue the setup. You can activate the plugin later.</p>

	', 
					'id' => 'welcome_options_activate' ),

							array(
					'title' => __( 'Activation Key', 'lifterlms' ),
					'desc' 		=> __( $activation_message, 'lifterlms' ),
					'id' 		=> 'lifterlms_activation_key',
					'type' 		=> 'text',
					'default'	=> '',
					'desc_tip'	=> true,
				),



				array(	
					'type' => 'desc', 
					'desc' => '
					<p>Next we need to set up your pages. Ya, we know, more pages… That’s just the way Wordpress works. We’ve already installed them. You just need to set them. 
	When you installed LifterLMS we created a few pages for you. You can select those pages or use different ones. Your choice.</p>

	<p>The first page you need is the Student Account page. This is the page users will go to register, login and access their accounts. We installed a page called My Courses. You can use that or select a different page. If you happen to select a different page you will need to add this shortcode to the page: [lifterlms_my_account]</p>

	', 
					'id' => 'welcome_options_setup' ),

				array(
					'title' => __( 'Account Access Page', 'lifterlms' ),
					'desc' 		=> __( 'We suggest you choose "My Courses"', 'lifterlms' ),
					'id' 		=> 'lifterlms_myaccount_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'desc_tip'	=> true,
				),

				array(	
					'type' => 'desc', 
					'desc' => '
					<p>Next we need a checkout page so people can buy your courses and memberships. If you are a true philanthropist and don’t plan on selling anything you can skip setting up this page. 
	We created a page called “Purchase” you can use that or select a different page.</p>
	', 
					'id' => 'welcome_options_setup' ), 


					

				array(
						'title' => __( 'Checkout Page', 'lifterlms' ),
						'desc' 		=> __( 'We suggest you choose "Purchase"', 'lifterlms' ),
						'id' 		=> 'lifterlms_checkout_page_id',
						'type' 		=> 'single_select_page',
						'default'	=> '',
						'class'		=> 'chosen_select_nostd',
						'desc_tip'	=> __( 'This sets the base page of the checkout page', 'lifterlms' ),
					),



				array(	
								'type' => 'desc', 
								'desc' => '
								<p>If you are going to sell your courses you should probably pick a currency.</p>
				', 
								'id' => 'welcome_options_setup' ),

				array(
								'title' 	=> __( 'Default Currency', 'lifterlms' ),
								'desc' 		=> __( 'Default currency type.', 'lifterlms' ),
								'id' 		=> 'lifterlms_currency',
								'default'	=> 'USD',
								'type' 		=> 'select',
								'class'		=> 'chosen_select',
								'desc_tip'	=>  true,
								'options'   => $currency_code_options
							),

				array(	
								'type' => 'desc', 
								'desc' => '

								<p>There are a lot of other settings but those were the important ones to get you started. You can access all of the other settings from the big blue menu at the top of the page.</p>  

								<p>If you have any questions or want to request a feature head on over to our <a href="https://lifterlms.com/forums/">Support Forums.</a></p>

								<p>That’s all there is to it. Your ready to start building courses and changing the world!</p> 
								<p>Click "Save Changes" below to save your settings and get started.</p>
				', 
								'id' => 'welcome_options_setup' ),

				array(	
								'type' => 'hidden', 
								'value' => 'yes',
								
								'id' => 'lifterlms_first_time_setup' ),

				array( 'type' => 'sectionend', 'id' => 'welcome_options_activate' ),

				) 
			);

		} else {

			return apply_filters( 'lifterlms_general_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'general_information', 'class' =>'top' ),
			
				array(	'title' => __( 'Welcome to LifterLMS', 
					'lifterlms' ), 
					'type' => 'title', 
					'desc' => '
						
					<div class="llms-list">
					<ul>
					<li><p>' . __( 'Thank you for choosing <a href="http://lifterlms.com">LifterLMS</a> as your Learning Management Solution.', 'lifterlms' ) .' </p></li>
					<li><p>Version: ' . LLMS()->version . '</p></li>
					<li><p>Support: <a href="https://lifterlms.com/forums/">' . __( 'https://lifterlms.com/forums/' ) . '</a></p></li>
					<li><p>Blog: <a href="http://blog.lifterlms.com/">' . __( 'http://blog.lifterlms.com/' ) . '</a></p></li>
					<li><p>Tutorials: <a href="http://demo.lifterlms.com/">' . __( 'http://demo.lifterlms.com/' ) . '</a></p></li>
					</ul>
					</div>', 
					'id' => 'actsdfion_options' ),
					
				array( 'type' => 'sectionend', 'id' => 'general_information' ),

				array( 'type' => 'sectionstart', 'id' => 'activation' ),

				array(	'title' => __( 'Plugin Activation', 'lifterlms' ), 'type' => 'title', 
					'desc' => __( 'Enter your activation key to recieve important updates and new features when they are available.
						Need an activation key? <a href="http://lifterlms.com">Get one here</a>', 'lifterlms' ), 
					'id' => 'activation_options' ),

				array(
					'title' => __( 'Activation Key', 'lifterlms' ),
					'desc' 		=> __( $activation_message, 'lifterlms' ),
					'id' 		=> 'lifterlms_activation_key',
					'type' 		=> 'text',
					'default'	=> '',
					'desc_tip'	=> true,
				),

				array(
					'desc'          => $deactivate_message,
					'id'            => 'lifterlms_activation_deactivate',
					'default'       => 'no',
					'type'          => $deactivate_checkbox,
					'checkboxgroup' => 'start',
				),

				array(
					'title' => __( '', 'lifterlms' ),
					'value' => __( 'Update Activation', 'lifterlms' ),
					'type' 		=> 'button',
				),

				array( 'type' => 'sectionend', 'id' => 'activation' ),

				array( 'type' => 'sectionstart', 'id' => 'general_options'),

				array(	'title' => __( 'Currency Options', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'The following options affect how prices are displayed on the frontend.', 'lifterlms' ), 'id' => 'pricing_options' ),

				array(
					'title' 	=> __( 'Default Currency', 'lifterlms' ),
					'desc' 		=> __( 'Default currency type.', 'lifterlms' ),
					'id' 		=> 'lifterlms_currency',
					'default'	=> 'USD',
					'type' 		=> 'select',
					'class'		=> 'chosen_select',
					'desc_tip'	=>  true,
					'options'   => $currency_code_options
				),

				array( 'type' => 'sectionend', 'id' => 'general_options' ),


			 	) 
			);
		}

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
