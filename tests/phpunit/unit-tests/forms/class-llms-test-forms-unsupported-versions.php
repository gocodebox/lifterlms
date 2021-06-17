<?php
/**
 * Test LLMS_Forms_Unsupported_Versions
 *
 * @package LifterLMS/Tests/Forms
 *
 * @group forms
 * @group forms_unsupported_versions
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Forms_Unsupported_Versions extends LLMS_UnitTestCase {

	/**
	 * Set up before class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms-unsupported-versions.php';
	}

	public function setUp() {

		parent::setUp();
		$this->init_main();

	}

	/**
	 * Construct a new main class for testing.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function init_main() {
		$this->main = new LLMS_Forms_Unsupported_Versions();
	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor() {

		$this->assertFalse( has_action( 'current_screen', array( $this->main, 'init' ) ) );

		global $wp_version;
		$temp = $wp_version;
		$wp_version = '5.6.2';

		$this->init_main();
		$this->assertEquals( 10, has_action( 'current_screen', array( $this->main, 'init' ) ) );

		$wp_version = $temp;

	}

	/**
	 * Test init() when nothing should happen
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init_for_other() {

		set_current_screen( 'admin.php' );

		$this->main->init();

		$this->assertFalse( has_action( 'admin_print_styles', array( $this->main, 'print_styles' ) ) );
		$this->assertFalse( has_action( 'admin_notices', array( $this->main, 'output_notice' ) ) );

		set_current_screen( 'front' );

	}

	/**
	 * Test init() for the forms post table list
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init_for_post_table() {

		set_current_screen( 'edit-llms_form' );

		$this->main->init();

		$this->assertEquals( 10, has_action( 'admin_print_styles', array( $this->main, 'print_styles' ) ) );
		$this->assertEquals( 10, has_action( 'admin_notices', array( $this->main, 'output_notice' ) ) );

		set_current_screen( 'front' );

	}

	/**
	 * Test init() when accessing a form block editor directly
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init_for_form_post() {

		set_current_screen( 'llms_form' );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'http://example.org/wp-admin/edit.php?post_type=llms_form [302] YES' );

		try {

			$this->main->init();

		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {

			set_current_screen( 'front' );
			throw $exception;

		}

	}
}
