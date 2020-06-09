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
 * @since 3.37.3
 * @since 3.40.0 Add tests for `get_settings_group_email_delivery()`.
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
	 * @since 3.37.3
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

	/**
	 * Retrieve mock email provider settings used to test the get_settings_group_email_delivery() method.
	 *
	 * @since 3.40.0
	 *
	 * @return array[]
	 */
	public function get_mock_email_provider_settings() {

		return array(
			array(
				'id' => 'mock_email_provider_title',
				'type' => 'subtitle',
				'title' => 'Email sender',
			),
		);

	}

	/**
	 * Return an array of mock settings and possible values.
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_get_settings_group_email_delivery_no_providers() {

		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->page, 'get_settings_group_email_delivery' ) );

	}

	/**
	 * Return an array of mock settings and possible values.
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_get_settings_group_email_delivery_with_providers() {

		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->page, 'get_settings_group_email_delivery' ) );

		add_filter( 'llms_email_delivery_services', array( $this, 'get_mock_email_provider_settings' ) );

		$res = LLMS_Unit_Test_Util::call_method( $this->page, 'get_settings_group_email_delivery' );

		$this->assertEquals( array( 'email_delivery', 'email_delivery_title', 'mock_email_provider_title', 'email_delivery_end' ), wp_list_pluck( $res, 'id' ) );

		remove_filter( 'llms_email_delivery_services', array( $this, 'get_mock_email_provider' ) );

	}

}
