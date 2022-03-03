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
	 * Determines whether or not legacy setting should be added to the mock settings array.
	 *
	 * @var boolean
	 */
	private $expect_legacy_opts = false;

	/**
	 * Return an array of mock settings and possible values.
	 *
	 * @since 3.37.3
	 * @since [version] Update settings.
	 *
	 * @return void
	 */
	protected function get_mock_settings() {

		$settings = array(
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
			'lifterlms_achievement_default_img' => array(
				0,
				25,
			),
			'lifterlms_certificate_default_img' => array(
				0,
				32,
			),
			'lifterlms_certificate_default_size' => array(
				'LETTER',
				'A4'
			),
		);

		if ( $this->expect_legacy_opts ) {
			$settings = array_merge(
				$settings,
				array(
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
				)
			);
		}

		return $settings;

	}

	/**
	 * Ensure all editable settings exist in the settings array when the legacy option is set.
	 *
	 * Calls the parent test method `test_get_setting()` after setting up data to enable legacy opts.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_settings_with_legacy() {

		update_option( 'llms_has_certificates_with_legacy_default_image', 'yes' );
		$this->expect_legacy_opts = true;
		parent::test_get_settings();
		$this->expect_legacy_opts = false;

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

	/**
	 * Test the save() method with legacy options enabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_with_legacy_opts() {

		update_option( 'llms_has_certificates_with_legacy_default_image', 'yes' );
		$this->expect_legacy_opts = true;
		parent::test_save();
		$this->expect_legacy_opts = false;

	}


}
