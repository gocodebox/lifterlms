<?php
/**
 * Test LLMS_Settings_Engagements
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group settings_page
 * @group settings_page_engagements
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Settings_Engagements extends LLMS_Settings_Page_Test_Case {

	/**
	 * Classname.
	 *
	 * @var string
	 */
	protected $classname = 'LLMS_Settings_Engagements';

	/**
	 * Expected class $id property.
	 *
	 * @var string
	 */
	protected $class_id = 'engagements';

	/**
	 * Expected class $label property.
	 *
	 * @var string
	 */
	protected $class_label = 'Engagements';

	/**
	 * Return an array of mock settings and possible values.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function get_mock_settings() {

		return array(
			'lifterlms_email_from_name' => array(
				esc_attr( get_bloginfo( 'title' ) ),
				'mock from name',
			),
			'lifterlms_email_from_address' => array(
				get_option( 'admin_email' ),
				'mock@mock.com',
			),
			'lifterlms_email_header_image' => array(
				'fake.png',
			),
			'lifterlms_email_footer_text' => array(
				'footer text content',
			),
			'lifterlms_certificate_bg_img_width' => array(
				800,
				1024,
			),
			'lifterlms_certificate_bg_img_height' => array(
				616,
				1200,
			),
			'lifterlms_certificate_legacy_image_size' => array(
				'yes',
			),
		);

	}

}
