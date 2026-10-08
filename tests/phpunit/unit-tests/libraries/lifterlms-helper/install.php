<?php
/**
 * Test LLMS_Helper_Install class
 *
 * @package LifterLMS_Helper/Tests
 *
 * @group install
 *
 * @since 3.4.0
 */
class LLMS_Helper_Test_Install extends LLMS_Helper_Unit_Test_Case {

	public static function set_up_before_class() {

		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

	}

	/**
	 * Test check_version()
	 *
	 * @since 3.4.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_check_version() {

		llms_maybe_define_constant( 'LLMS_HELPER_LIB', true );

		delete_option( 'llms_helper_version' );

		$action = did_action( 'llms_helper_updated' );

		// Update runs.
		LLMS_Helper_Install::check_version();
		$this->assertEquals( ++$action, did_action( 'llms_helper_updated' ) );

		// Does not run.
		LLMS_Helper_Install::check_version();
		$this->assertEquals( $action, did_action( 'llms_helper_updated' ) );

	}

	/**
	 * Test installation when running as a lib
	 *
	 * @since 3.4.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_install_as_lib() {

		llms_maybe_define_constant( 'LLMS_HELPER_LIB', true );

		$action_before = did_action( 'llms_helper_before_install' );
		$action_after = did_action( 'llms_helper_after_install' );

		LLMS_Helper_Install::install();

		$this->assertEquals( ++$action_before, did_action( 'llms_helper_before_install' ) );
		$this->assertEquals( ++$action_after, did_action( 'llms_helper_after_install' ) );

		$this->assertEquals( llms_helper()->version, get_option( 'llms_helper_version' ) );

		$notices = LLMS_Admin_Notices::get_notices();
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'llms-flash-notice-0' ) );

	}

	/**
	 * Test installation
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function test_install() {

		// The standalone (non-library) install path cannot be exercised when the helper
		// is loaded as a library within the LifterLMS core because the `LLMS_HELPER_LIB`
		// constant is always defined before tests run. See `test_install_as_lib()`.
		$this->markTestSkipped( 'The helper always runs in library mode within the LifterLMS core test suite.' );

	}

	/**
	 * Test update_version()
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function test_update_version() {

		// Arbitrary version passed in.
		LLMS_Helper_Install::update_version( '1.2.3' );
		$this->assertEquals( '1.2.3', get_option( 'llms_helper_version' ) );

		// Current version.
		LLMS_Helper_Install::update_version();
		$this->assertEquals( llms_helper()->version, get_option( 'llms_helper_version' ) );

	}

}
