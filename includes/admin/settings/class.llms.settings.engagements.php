<?php
/**
 * Admin Settings Page: Engagements
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @version 3.40.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Settings_Engagements class
 *
 * @since 1.0.0
 * @since 3.8.0 Unknown.
 * @since 3.37.3 Renamed setting field IDs to be unique.
 *              Removed redundant functions defined in the `LLMS_Settings_Page` class.
 *              Removed constructor and added `get_label()` method to be compatible with changes in `LLMS_Settings_Page`.
 * @since 3.40.0 Add a section that displays conditionally for email delivery provider connections.
 */
class LLMS_Settings_Engagements extends LLMS_Settings_Page {

	/**
	 * Settings identifier
	 *
	 * @var string
	 */
	public $id = 'engagements';

	/**
	 * Retrieve the page label.
	 *
	 * @since 3.37.3
	 *
	 * @return string
	 */
	protected function set_label() {
		return __( 'Engagements', 'lifterlms' );
	}

	/**
	 * Get settings array
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown.
	 * @since 3.37.3 Refactor to pull each settings group from its own method.
	 * @since 3.40.0 Include an email delivery section.
	 *
	 * @return array
	 */
	public function get_settings() {

		/**
		 * Modify LifterLMS Admin Settings on the "Engagements" tab,
		 *
		 * @since 1.0.0
		 *
		 * @param array[] $settings Array of settings fields arrays.
		 */
		return apply_filters(
			'lifterlms_engagements_settings',
			array_merge(
				$this->get_settings_group_email(),
				$this->get_settings_group_email_delivery(),
				$this->get_settings_group_certs()
			)
		);

	}

	/**
	 * Retrieve fields for the certificates settings group.
	 *
	 * @since 3.37.3
	 *
	 * @return array[]
	 */
	protected function get_settings_group_certs() {

		return $this->generate_settings_group(
			'certificates_options',
			__( 'Certificate Settings', 'lifterlms' ),
			'',
			array(
				array(
					'title' => __( 'Background Image Settings', 'lifterlms' ),
					'type'  => 'subtitle',
					'desc'  => __( 'Use these sizes to determine the dimensions of certificate background images. After changing these settings, you may need to <a href="http://wordpress.org/extend/plugins/regenerate-thumbnails/" target="_blank">regenerate your thumbnails</a>.', 'lifterlms' ),
					'id'    => 'cert_bg_image_settings',
				),
				array(
					'title'    => __( 'Image Width', 'lifterlms' ),
					'desc'     => __( 'in pixels', 'lifterlms' ),
					'id'       => 'lifterlms_certificate_bg_img_width',
					'default'  => '800',
					'type'     => 'number',
					'autoload' => false,
				),
				array(
					'title'    => __( 'Image Height', 'lifterlms' ),
					'id'       => 'lifterlms_certificate_bg_img_height',
					'desc'     => __( 'in pixels', 'lifterlms' ),
					'default'  => '616',
					'type'     => 'number',
					'autoload' => false,
				),
				array(
					'title'    => __( 'Legacy compatibility', 'lifterlms' ),
					'desc'     => __( 'Use legacy certificate image sizes.', 'lifterlms' ) .
									'<br><em>' . __( 'Enabling this will override the above dimension settings and set the image dimensions to match the dimensions of the uploaded image.', 'lifterlms' ) . '</em>',
					'id'       => 'lifterlms_certificate_legacy_image_size',
					'default'  => 'no',
					'type'     => 'checkbox',
					'autoload' => false,
				),
			)
		);

	}

	/**
	 * Retrieve fields for the email settings group.
	 *
	 * @since 3.37.3
	 *
	 * @return array[]
	 */
	protected function get_settings_group_email() {

		return $this->generate_settings_group(
			'email_options',
			__( 'Email Settings', 'lifterlms' ),
			__( 'Settings for all emails sent by LifterLMS. Notification and engagement emails will adhere to these settings.', 'lifterlms' ),
			array(
				array(
					'title'   => __( 'Sender Name', 'lifterlms' ),
					'desc'    => '<br>' . __( 'Name to be displayed in From field', 'lifterlms' ),
					'id'      => 'lifterlms_email_from_name',
					'type'    => 'text',
					'default' => esc_attr( get_bloginfo( 'title' ) ),
				),
				array(
					'title'   => __( 'Sender Email', 'lifterlms' ),
					'desc'    => '<br>' . __( 'Email Address displayed in the From field', 'lifterlms' ),
					'id'      => 'lifterlms_email_from_address',
					'type'    => 'email',
					'default' => get_option( 'admin_email' ),
				),
				array(
					'title'    => __( 'Header Image', 'lifterlms' ),
					'id'       => 'lifterlms_email_header_image',
					'type'     => 'image',
					'default'  => '',
					'autoload' => false,
				),
				array(
					'title'   => __( 'Email Footer Text', 'lifterlms' ),
					'desc'    => '<br>' . __( 'Text you would like displayed in the footer of all emails.', 'lifterlms' ),
					'id'      => 'lifterlms_email_footer_text',
					'type'    => 'textarea',
					'default' => '',
				),
			)
		);

	}

	/**
	 * Retrieve email delivery partner settings groups.
	 *
	 * @since 3.40.0
	 *
	 * @return array
	 */
	protected function get_settings_group_email_delivery() {

		/**
		 * Filter settings for available email delivery services.
		 *
		 * @since 3.40.0
		 *
		 * @param array[] $settings Array of settings arrays.
		 */
		$services = apply_filters( 'llms_email_delivery_services', array() );

		// If there's no services respond with an empty array so we don't output the whole section.
		if ( ! $services ) {
			return array();
		}

		// Output the a section.
		return $this->generate_settings_group(
			'email_delivery',
			__( 'Email Delivery', 'lifterlms' ),
			'',
			$services
		);

	}

}

return new LLMS_Settings_Engagements();
