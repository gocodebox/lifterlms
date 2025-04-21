<?php
/**
 * Test Admin Notices Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_notices
 *
 * @since 4.10.0
 */
class LLMS_Test_Admin_Notices extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class
	 *
	 * @since 4.10.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
	}

	/**
	 * Test add_output_actions().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_add_output_actions() {

		remove_action( 'admin_notices', array( 'LLMS_Admin_Notices', 'output_notices' ) );

		// Any screen.
		LLMS_Admin_Notices::add_output_actions();
		$this->assertEquals( 10, has_action( 'admin_notices', array( 'LLMS_Admin_Notices', 'output_notices' ) ) );

		remove_action( 'admin_notices', array( 'LLMS_Admin_Notices', 'output_notices' ) );

		// LLMS settings screen.
		set_current_screen( 'lifterlms_page_llms-settings' );

		LLMS_Admin_Notices::add_output_actions();
		$this->assertEquals( 10, has_action( 'lifterlms_settings_notices', array( 'LLMS_Admin_Notices', 'output_notices' ) ) );

		set_current_screen( 'front' );

	}

	/**
	 * Test init() properly initializes the `$notices` class variable
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_init_notices_var() {

		$expect = array( 'fake' );
		update_option( 'llms_admin_notices', $expect );

		LLMS_Admin_Notices::init();

		$this->assertEquals( $expect, LLMS_Admin_Notices::get_notices() );

	}

	/**
	 * Test init() properly adds action hooks
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_init_add_actions() {

		remove_action( 'wp_loaded', array( 'LLMS_Admin_Notices', 'hide_notices' ) );
		remove_action( 'current_screen', array( 'LLMS_Admin_Notices', 'add_output_actions' ) );
		remove_action( 'shutdown', array( 'LLMS_Admin_Notices', 'save_notices' ) );

		LLMS_Admin_Notices::init();

		$this->assertEquals( 10, has_action( 'wp_loaded', array( 'LLMS_Admin_Notices', 'hide_notices' ) ) );
		$this->assertEquals( 10, has_action( 'current_screen', array( 'LLMS_Admin_Notices', 'add_output_actions' ) ) );
		$this->assertEquals( 10, has_action( 'shutdown', array( 'LLMS_Admin_Notices', 'save_notices' ) ) );

	}

	/**
	 * Test add_notice() for a notice that has been previously dismissed
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_add_notice_already_dismissed() {

		set_transient( 'llms_admin_notice_test-dismissal_delay', 'yes', 60 );

		LLMS_Admin_Notices::add_notice( 'test-dismissal' );

		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'test-dismissal' ) );

	}

	/**
	 * Test add_notice() with HTML and defaults
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_add_notice_with_defaults() {

		LLMS_Admin_Notices::add_notice( 'test-add-notice', '<p>HTML CONTENT</p>' );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'test-add-notice' ) );

		$this->assertEquals( array(
			'dismissible'      => true,
			'dismiss_for_days' => 7,
			'flash'            => false,
			'html'             => '<p>HTML CONTENT</p>',
			'remind_in_days'   => 7,
			'remindable'       => false,
			'type'             => 'info',
			'template'         => false,
			'template_path'    => '',
			'default_path'     => '',
		), LLMS_Admin_Notices::get_notice( 'test-add-notice' ) );

	}

	/**
	 * Test add_notice() with HTML and defaults
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_add_notice_with_options() {

		LLMS_Admin_Notices::add_notice( 'test-add-notice-2', array( 'template' => 'path/to/template.php' ) );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'test-add-notice-2' ) );

		$this->assertEquals( array(
			'dismissible'      => true,
			'dismiss_for_days' => 7,
			'flash'            => false,
			'html'             => '',
			'remind_in_days'   => 7,
			'remindable'       => false,
			'type'             => 'info',
			'template'         => 'path/to/template.php',
			'template_path'    => '',
			'default_path'     => '',
		), LLMS_Admin_Notices::get_notice( 'test-add-notice-2' ) );

	}

	/**
	 * Test delete_notice()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_delete_notice() {

		LLMS_Admin_Notices::add_notice( 'test-delete' );
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'test-delete' ) );

		LLMS_Admin_Notices::delete_notice( 'test-delete' );
		$this->assertEquals( array(), LLMS_Admin_Notices::get_notice( 'test-delete' ) );

		$this->assertSame( 1, did_action( 'lifterlms_delete_test-delete_notice' ) );
		$this->assertFalse( get_transient( 'llms_admin_notice_test-delete_delay' ) );

	}

	/**
	 * Test delete_notice() when "reminding" for a notice that is not remindable
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_delete_notice_remind_not_remindable() {

		LLMS_Admin_Notices::add_notice( 'test-delete-not-remindable' );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'test-delete-not-remindable' ) );

		LLMS_Admin_Notices::delete_notice( 'test-delete-not-remindable', 'remind' );

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notice( 'test-delete-not-remindable' ) );
		$this->assertFalse( get_transient( 'llms_admin_notice_test-delete-not-remindable_delay' ) );
		$this->assertSame( 1, did_action( 'lifterlms_remind_test-delete-not-remindable_notice' ) );

	}

	/**
	 * Test delete_notice() for a remindable notice
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_delete_notice_remind() {

		LLMS_Admin_Notices::add_notice( 'test-remind', array( 'remindable' => true ) );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'test-remind' ) );

		LLMS_Admin_Notices::delete_notice( 'test-remind', 'remind' );

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notice( 'test-remind' ) );
		$this->assertTrue( is_numeric( get_option( 'llms_admin_notice_test-remind_delay' ) ) && time() < get_option( 'llms_admin_notice_test-remind_delay' ) );
		$this->assertSame( 1, did_action( 'lifterlms_remind_test-remind_notice' ) );


	}

	/**
	 * Test delete_notice() for dismissing a not dismissible notice
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_delete_notice_remind_not_dismissable() {

		LLMS_Admin_Notices::add_notice( 'test-delete-not-dismissible', array( 'dismissible' => false ) );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'test-delete-not-dismissible' ) );

		LLMS_Admin_Notices::delete_notice( 'test-delete-not-dismissible', 'hide' );

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notice( 'test-delete-not-dismissible' ) );
		$this->assertFalse( get_transient( 'llms_admin_notice_test-delete-not-dismissible_delay' ) );
		$this->assertSame( 1, did_action( 'lifterlms_hide_test-delete-not-dismissible_notice' ) );

	}

	/**
	 * Test delete_notice() for a dismissible notice
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_delete_notice_dismiss() {

		LLMS_Admin_Notices::add_notice( 'test-dismiss' );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'test-dismiss' ) );

		LLMS_Admin_Notices::delete_notice( 'test-dismiss', 'hide' );

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notice( 'test-dismiss' ) );
		$this->assertTrue( is_numeric( get_option( 'llms_admin_notice_test-dismiss_delay' ) ) && time() < get_option( 'llms_admin_notice_test-dismiss_delay' ) );
		$this->assertSame( 1, did_action( 'lifterlms_hide_test-dismiss_notice' ) );

	}

	/**
	 * Test flash_notice()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_flash_notice() {

		LLMS_Admin_Notices::flash_notice( '<p>FLASH NOTICE</p>', 'error' );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'llms-flash-notice-0' ) );
		$this->assertEquals( array(
			'dismissible'      => false,
			'dismiss_for_days' => 7,
			'flash'            => true,
			'html'             => '<p>FLASH NOTICE</p>',
			'remind_in_days'   => 7,
			'remindable'       => false,
			'type'             => 'error',
			'template'         => '',
			'template_path'    => '',
			'default_path'     => '',
		), LLMS_Admin_Notices::get_notice( 'llms-flash-notice-0' ) );

		// Test incrementor.
		LLMS_Admin_Notices::flash_notice( '<p>FLASH NOTICE 2</p>', 'success' );

		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'llms-flash-notice-1' ) );
		$this->assertEquals( array(
			'dismissible'      => false,
			'dismiss_for_days' => 7,
			'flash'            => true,
			'html'             => '<p>FLASH NOTICE 2</p>',
			'remind_in_days'   => 7,
			'remindable'       => false,
			'type'             => 'success',
			'template'         => '',
			'template_path'    => '',
			'default_path'     => '',
		), LLMS_Admin_Notices::get_notice( 'llms-flash-notice-1' ) );


	}

	/**
	 * Test get_notice()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_get_notice() {

		LLMS_Admin_Notices::add_notice( 'test-get' );

		$this->assertEquals( array(
			'dismissible'      => true,
			'dismiss_for_days' => 7,
			'flash'            => false,
			'html'             => '',
			'remind_in_days'   => 7,
			'remindable'       => false,
			'type'             => 'info',
			'template'         => false,
			'template_path'    => '',
			'default_path'     => '',
		), LLMS_Admin_Notices::get_notice( 'test-get' ) );

	}

	public function test_get_notice_not_found() {

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notice( 'test-get-not-found' ) );

	}

	/**
	 * Test get_notices()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_get_notices() {

		// Reset the array from previous tests.
		LLMS_Admin_Notices::init();

		LLMS_Admin_Notices::add_notice( 'test-get-all' );
		LLMS_Admin_Notices::add_notice( 'test-get-all-2' );
		$this->assertEquals( array( 'test-get-all', 'test-get-all-2' ), LLMS_Admin_Notices::get_notices() );

	}

	/**
	 * Test get_notices() when no notices record exists in the DB
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_get_notices_no_db_option() {

		delete_option( 'llms_admin_notices' );

		// Reset the array from previous tests.
		LLMS_Admin_Notices::init();

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notices() );

	}

	/**
	 * Test get_notices() when an empty string is stored in the DB option
	 *
	 * @since 4.13.0
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1443
	 *
	 * @return void
	 */
	public function test_get_notices_empty_string_db_option() {

		update_option( 'llms_admin_notices', '' );

		// Reset the array from previous tests.
		LLMS_Admin_Notices::init();

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notices() );

	}

	/**
	 * Test get_notices() when malformed or invalid data is stored in the DB.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_get_notices_invalid_db_option() {

		update_option( 'llms_admin_notices', array( array(), 1, null, new stdClass() ) );

		// Reset the array from previous tests.
		LLMS_Admin_Notices::init();

		$this->assertEquals( array(), LLMS_Admin_Notices::get_notices() );

	}

	/**
	 * Test has_notice()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_has_notice() {

		$id = 'test-has';
		$this->assertFalse( LLMS_Admin_Notices::has_notice( $id ) );

		LLMS_Admin_Notices::add_notice( $id );
		$this->assertTrue( LLMS_Admin_Notices::has_notice( $id ) );

	}

	/**
	 * Test output_notice().
	 *
	 * @since 5.3.1
	 *
	 * @return void
	 */
	public function test_output_notice() {

		LLMS_Admin_Notices::init();

		# Create a normal notice.
		$notice_html = 'Have you heard of the band 999 MB? They haven\'t got a gig yet.';
		$notice_id   = 'test-output-notice-normal';
		LLMS_Admin_Notices::add_notice( $notice_id, $notice_html );
		LLMS_Admin_Notices::save_notices();

		# Test where current user does not have the 'manage_options' capability.
		$this->assertOutputEmpty( array( 'LLMS_Admin_Notices', 'output_notice' ), array( $notice_id ) );

		# Test where current user does have the 'manage_options' capability.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertOutputContains( $notice_html, array( 'LLMS_Admin_Notices', 'output_notice' ), array( $notice_id ) );

		# Test where the notice does not exist.
		$this->assertOutputEmpty( array( 'LLMS_Admin_Notices', 'output_notice' ), array( 'notice-does-not-exist' ) );

		# Test where the notice html is empty.
		$notice_id = 'test-output-notice-empty-html-empty-template';
		LLMS_Admin_Notices::add_notice( $notice_id, '' );
		LLMS_Admin_Notices::save_notices();
		$this->assertOutputEmpty( array( 'LLMS_Admin_Notices', 'output_notice' ), array( $notice_id ) );

	}

	/**
	 * Test save_notices()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_save_notices() {

		// Reset the array from previous tests.
		LLMS_Admin_Notices::init();

		LLMS_Admin_Notices::add_notice( 'test-save-1' );
		LLMS_Admin_Notices::add_notice( 'test-save-2' );

		LLMS_Admin_Notices::save_notices();

		$this->assertEquals( array( 'test-save-1', 'test-save-2' ), get_option( 'llms_admin_notices' ) );

	}

}
