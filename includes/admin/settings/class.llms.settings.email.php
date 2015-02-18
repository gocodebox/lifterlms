<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Settings_Email' ) ) :

/**
* Admin Settings Page, Email Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_Email extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		apply_filters('debug', 'This is a checkpoint');
		
		$this->id    = 'email';
		$this->label = __( 'Email', 'lifterlms' );

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
		// Get email page
			$email_page_id = llms_get_page_id('email');

			$base_slug = ($email_page_id > 0 && get_page( $email_page_id )) ? get_page_uri( $email_page_id ) : 'email';

			return apply_filters( 'lifterlms_course_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'email_options', 'class' =>'top' ),

				array(	'title' => __( 'Email Settings', 'lifterlms' ), 'type' => 'title','desc' => 'Manage email settings.', 'id' => 'email_options' ),

				array(
					'title' => __( 'Senders Email Address', 'lifterlms' ),
					'desc' 		=> __( 'Email Address displayed in the From field', 'lifterlms' ),
					'id' 		=> 'lifterlms_email_from_address',
					'type' 		=> 'email',
					'default'	=> get_option('admin_email'),
					'desc_tip'	=> true,
				),
				array(
					'title' => __( 'Name of Sender', 'lifterlms' ),
					'desc' 		=> __( 'Name to be displayed in From field', 'lifterlms' ),
					'id' 		=> 'lifterlms_email_from_name',
					'type' 		=> 'text',
					'default'	=> esc_attr(get_bloginfo('title')),
					'desc_tip'	=> true,
				),
				array(
					'title' => __( 'Email Footer Text', 'lifterlms' ),
					'desc' 		=> __( 'Text you would like displayed in the footer of all emails.', 'lifterlms' ),
					'id' 		=> 'lifterlms_email_footer_text',
					'type' 		=> 'text',
					'default'	=> '',
					'desc_tip'	=> true,
				),
				array(
					'title' => __( 'Header Image', 'lifterlms' ),
					'desc' 		=> sprintf(__( 'Enter the url for the email header (logo).<a href="%s">Upload an image</a>.', 'lifterlms' ), admin_url('media-new.php')),
					'id' 		=> 'lifterlms_email_header_image',
					'type' 		=> 'text',
					'default'	=> '',
					'autoload'  => false
			),

				array( 'type' => 'sectionend', 'id' => 'email_options'),
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

endif;

return new LLMS_Settings_Email();
