<?php
/**
 * Test admin settings pages for api keys and webhooks
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group admin
 * @group admin_settings_page
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Admin_Settings_Page extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Setup the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();

		// Ensure required classes are loaded.
		set_current_screen( 'index.php' );
		LLMS_REST_API()->includes();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/admin/class-llms-rest-admin-settings-page.php';
		$this->page = new LLMS_REST_Admin_Settings_Page();

		$this->user = $this->factory->user->create( array( 'role' => 'administrator' ) );

	}

	/**
	 * Tear down the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * test hooks are added from the constructor
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_hooks() {

		$this->assertEquals( 20, has_filter( 'lifterlms_settings_tabs_array', array( $this->page, 'add_settings_page' ) ) );
		$this->assertEquals( 10, has_action( 'lifterlms_sections_rest-api', array( $this->page, 'output_sections_nav' ) ) );
		$this->assertEquals( 10, has_action( 'lifterlms_settings_rest-api', array( $this->page, 'output' ) ) );

		$this->assertEquals( 10, has_action( 'lifterlms_settings_save_rest-api', array( 'LLMS_Rest_Admin_Settings_API_Keys', 'save' ) ) );

		$this->assertEquals( 10, has_filter( 'llms_settings_rest-api_has_save_button', '__return_false' ) );
		$this->assertEquals( 10, has_filter( 'llms_table_get_table_classes', array( $this->page, 'get_table_classes' ) ) );

		$this->assertEquals( 10, has_action( 'lifterlms_admin_field_title-with-html', array( $this->page, 'output_title_field' ) ) );

	}

	/**
	 * Test get_current_section() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_current_section() {

		// Default with no permissions.
		$this->assertEquals( 'main', LLMS_Unit_Test_Util::call_method( $this->page, 'get_current_section' ) );

		// Default with permissions.
		wp_set_current_user( $this->user );
		$this->assertEquals( 'keys', LLMS_Unit_Test_Util::call_method( $this->page, 'get_current_section' ) );

	}

	/**
	 * Test get_sections() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_sections() {

		// None without permissions.
		$this->assertEquals( array(), $this->page->get_sections() );

		// With permissions.
		wp_set_current_user( $this->user );
		$this->assertEquals( array( 'keys', 'webhooks' ), array_keys( $this->page->get_sections() ) );

	}

}
