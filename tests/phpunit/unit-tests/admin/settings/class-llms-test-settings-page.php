<?php
/**
 * Test LLMS_Settings_Page
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group settings_page
 *
 * @since 3.37.3
 */
class LLMS_Test_Settings_Page extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.3
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/settings/class.llms.settings.page.php';

		// Setup a mock settings page.
		$this->page = new class() extends LLMS_Settings_Page {
			public $id = 'mock';
			protected function set_label() {
				return 'Mock';
			}
		};

	}

	/**
	 * Test constructor
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_constructor() {

		$this->assertEquals( 'Mock', $this->page->label );

		// Tab should be registered.
		$this->assertEquals( $this->page->tab_priority, has_action( 'lifterlms_settings_tabs_array', array( $this->page, 'add_settings_page' ) ) );

		// Output action.
		$this->assertEquals( 10, has_action( 'lifterlms_settings_mock', array( $this->page, 'output' ) ) );

		// Save action.
		$this->assertEquals( 10, has_action( 'lifterlms_settings_save_mock', array( $this->page, 'save' ) ) );

	}

	/**
	 * Test add_settings_page() method.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_add_settings_page() {

		// No other pages exist.
		$this->assertEquals( array(
			'mock' => 'Mock',
		), $this->page->add_settings_page( array() ) );

		// Another page exists, add it to the end.
		$this->assertEquals( array(
			'fake' => 'Fake',
			'mock' => 'Mock',
		), $this->page->add_settings_page( array(
			'fake' => 'Fake',
		) ) );

	}

	/**
	 * Test generation of a settings group with no settings added.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_generate_settings() {

		// No description.
		$args = array(
			'mock_options',
			'Mock Settings',
		);
		$settings = LLMS_Unit_Test_Util::call_method( $this->page, 'generate_settings_group', $args );

		$this->assertEquals( 'mock_options', $settings[0]['id'] );
		$this->assertEquals( 'sectionstart', $settings[0]['type'] );

		$this->assertEquals( 'mock_options_title', $settings[1]['id'] );
		$this->assertEquals( 'title', $settings[1]['type'] );
		$this->assertEquals( 'Mock Settings', $settings[1]['title'] );
		$this->assertEquals( '', $settings[1]['desc'] );

		$this->assertEquals( 'mock_options_end', $settings[2]['id'] );
		$this->assertEquals( 'sectionend', $settings[2]['type'] );

		// Has a description.
		$args = array(
			'mock_options',
			'Mock Settings',
			'Mock Settings Description',
		);
		$settings = LLMS_Unit_Test_Util::call_method( $this->page, 'generate_settings_group', $args );
		$this->assertEquals( 'Mock Settings Description', $settings[1]['desc'] );

		// Has a description.
		$args = array(
			'mock_options',
			'Mock Settings',
			'Mock Settings Description',
			array(
				array(
					'id'   => 'mock_setting',
					'type' => 'text',
				),
			)
		);
		$settings = LLMS_Unit_Test_Util::call_method( $this->page, 'generate_settings_group', $args );
		$this->assertEquals( 'mock_setting', $settings[2]['id'] );
		$this->assertEquals( 'text', $settings[2]['type'] );

	}


	/**
	 * Test set_label() stub when no ID exists for the class.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_set_label_stub_no_id() {

		// Empty string because no ID defined.
		$page = new LLMS_Settings_Page();
		$this->assertEquals( '', LLMS_Unit_Test_Util::call_method( $page, 'set_label' ) );

	}

	/**
	 * Test set_label() stub when an ID is set.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_set_label_stub_with_id() {

		// Return ID because the method isn't overriden.
		$page = new class() extends LLMS_Settings_Page {
			public $id = 'mock';
		};
		$this->assertEquals( 'mock', LLMS_Unit_Test_Util::call_method( $page, 'set_label' ) );

	}

	/**
	 * Test the get_sections() stub.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_get_sections() {
		$this->assertEquals( array(), $this->page->get_sections() );
	}

	/**
	 * Test the get_settings() stub.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_get_settings_stub() {
		$this->assertEquals( array(), $this->page->get_settings() );
	}

	/**
	 * Test the output() stub.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_output() {
		$this->assertOutputEmpty( array( $this->page, 'output' ) );
	}

	/**
	 * Test the output_sections_nav() stub when no sections exist.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_output_sections_nav_empty() {
		$this->assertOutputEmpty( array( $this->page, 'output_sections_nav' ) );
	}

	/**
	 * Test the output_sections_nav() stub when sections do exist.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_output_sections_nav() {

		$page = new class() extends LLMS_Settings_Page {
			public $id = 'mock';
			public function get_sections() {
				return array(
					'section_1' => 'Section 1',
					'section_2' => 'Section 2',
				);
			}
		};

		$method = array( $page, 'output_sections_nav' );

		$this->assertOutputContains( '<nav class="llms-nav-tab-wrapper llms-nav-text">', $method );
		$this->assertOutputContains( '<ul class="llms-nav-items">', $method );

		$this->assertOutputContains( 'section=section_1">Section 1</a>', $method );
		$this->assertOutputContains( 'section=section_2">Section 2</a>', $method );

		$this->assertOutputContains( '</ul>', $method );
		$this->assertOutputContains( '</nav>', $method );

	}

	/**
	 * Test the save() method.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_save() {

		$page = new class() extends LLMS_Settings_Page {
			public $id = 'mock';
			public function get_settings() {
				return array(
					array(
						'id'   => 'mock_setting_id',
						'type' => 'text',
					),
					array(
						'id'   => 'mock_setting_id_2',
						'type' => 'text',
					),
				);
			}
		};

		// No data posted.
		$page->save();
		$this->assertEmpty( get_option( 'mock_setting_id' ) );
		$this->assertEmpty( get_option( 'mock_setting_id_2' ) );

		// Some Data posted.
		$this->mockPostRequest( array( 'mock_setting_id' => 'mock_setting_val' ) );
		$page->save();
		$this->assertEquals( 'mock_setting_val', get_option( 'mock_setting_id' ) );
		$this->assertEmpty( get_option( 'mock_setting_id_2' ) );

		// All Data posted.
		$this->mockPostRequest( array(
			'mock_setting_id'   => 'mock_setting_val',
			'mock_setting_id_2' => 'mock_setting_val',
		) );
		$page->save();
		$this->assertEquals( 'mock_setting_val', get_option( 'mock_setting_id' ) );
		$this->assertEquals( 'mock_setting_val', get_option( 'mock_setting_id_2' ) );

	}

	/**
	 * Ensure unregistered (fake) options aren't stored during save events.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_save_fake_option() {

		// Fake option.
		$this->mockPostRequest( array(
			'mock_setting_id_3' => 'mock_setting_val',
		) );
		$this->page->save();
		$this->assertEmpty( get_option( 'mock_setting_id_3' ) );

	}

	/**
	 * Test the save() method when the $flush prop is true.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_save_flush_disabled() {

		$page = new class() extends LLMS_Settings_Page {
			public $id = 'mock';
		};

		$this->assertFalse( has_action( 'shutdown', array( $page, 'flush_rewrite_rules' ) ) );
		$page->save();
		$this->assertFalse( has_action( 'shutdown', array( $page, 'flush_rewrite_rules' ) ) );

	}

	/**
	 * Test the save() method when the $flush prop is true.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_save_flush_enabled() {

		$page = new class() extends LLMS_Settings_Page {
			public $id = 'mock';
			protected $flush = true;
		};

		$this->assertFalse( has_action( 'shutdown', array( $page, 'flush_rewrite_rules' ) ) );
		$page->save();
		$this->assertEquals( 10, has_action( 'shutdown', array( $page, 'flush_rewrite_rules' ) ) );

	}

}
