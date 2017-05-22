<?php
/**
 * Admin Settings Page: Engagements
 * @since    1.0.0
 * @version  3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Settings_Engagements extends LLMS_Settings_Page {

	/**
	 * Constructor
	 * executes settings tab actions
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function __construct() {
		$this->id    = 'engagements';
		$this->label = __( 'Engagements', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 * @return array
	 * @since    1.0.0
	 * @version  3.8.0
	 */
	public function get_settings() {

		return apply_filters( 'lifterlms_engagements_settings', array(

			array(
				'type' => 'sectionstart',
				'id' => 'email_options',
				'class' => 'top',
			),

			array(
				'title' => __( 'Email Settings', 'lifterlms' ),
				'type' => 'title',
				'desc' => __( 'Settings for all emails sent by LifterLMS. Notification and engagement emails will adhere to these settings.', 'lifterlms' ),
				'id' => 'email_options',
			),
			array(
				'title' => __( 'Sender Name', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Name to be displayed in From field', 'lifterlms' ),
				'id' 		=> 'lifterlms_email_from_name',
				'type' 		=> 'text',
				'default'	=> esc_attr( get_bloginfo( 'title' ) ),
				'desc_tip'	=> true,
			),
			array(
				'title' => __( 'Sender Email', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Email Address displayed in the From field', 'lifterlms' ),
				'id' 		=> 'lifterlms_email_from_address',
				'type' 		=> 'email',
				'default'	=> get_option( 'admin_email' ),
				'desc_tip'	=> true,
			),
			array(
				'title' => __( 'Header Image', 'lifterlms' ),
				'id' 		=> 'lifterlms_email_header_image',
				'type' 		=> 'image',
				'default'	=> '',
				'autoload'  => false,
			),
			array(
				'title' => __( 'Email Footer Text', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Text you would like displayed in the footer of all emails.', 'lifterlms' ),
				'id' 		=> 'lifterlms_email_footer_text',
				'type' 		=> 'textarea',
				'default'	=> '',
				'desc_tip'	=> true,
			),

			array(
				'type' => 'sectionend',
				'id' => 'email_options',
			),

			array(
				'type' => 'sectionstart',
				'id' => 'certificates_options',
				'class' => 'top',
			),

			array(
				'title' => __( 'Certificates Settings', 'lifterlms' ),
				'type' => 'title',
				'desc' => '',
				'id' => 'certificates_options',
			),

			array(
				'type' => 'desc',
				'desc' => '<strong>' . __( 'Background Image Settings' ,'lifterlms' ) . '</strong><br>' .
						  __( 'Use these sizes to determine the dimensions of certificate background images. After changing these settings, you may need to <a href="http://wordpress.org/extend/plugins/regenerate-thumbnails/" target="_blank">regenerate your thumbnails</a>.' ,'lifterlms' ),
				'id' => 'cert_bg_image_settings',
			),

			array(
				'title'         => __( 'Image Width', 'lifterlms' ),
				'desc'          => __( 'in pixels', 'lifterlms' ),
				'id'            => 'lifterlms_certificate_bg_img_width',
				'default'       => '800',
				'type'          => 'number',
				'autoload'      => false,
			),

			array(
				'title'         => __( 'Image Height', 'lifterlms' ),
				'id'            => 'lifterlms_certificate_bg_img_height',
				'desc'          => __( 'in pixels', 'lifterlms' ),
				'default'       => '616',
				'type'          => 'number',
				'autoload'      => false,
			),

			array(
				'title'         => __( 'Legacy compatibility', 'lifterlms' ),
				'desc'          => __( 'Use legacy certificate image sizes.', 'lifterlms' ) .
								   '<br><em>' . __( 'Enabling this will override the above dimension settings and set the image dimensions to match the dimensions of the uploaded image.', 'lifterlms' ) . '</em>',
				'id'            => 'lifterlms_certificate_legacy_image_size',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
			),

			array(
				'type' => 'sectionend',
				'id' => 'certificates_options',
			),

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

return new LLMS_Settings_Engagements();
