<?php
/**
 * Tests for the LLMS_Admin_Tool_Wipe_Legacy_Account_Options class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 *
 * @since [version]
 */
class LLMS_Test_Admin_Tool_Wipe_Legacy_Account_Options extends LLMS_UnitTestCase {

	const LEGACY_OPTIONS = array(
		'lifterlms_registration_generate_username',
		'lifterlms_registration_password_strength',
		'lifterlms_registration_password_min_strength',
		'lifterlms_user_info_field_names_checkout_visibility',
		'lifterlms_user_info_field_address_checkout_visibility',
		'lifterlms_user_info_field_phone_checkout_visibility',
		'lifterlms_user_info_field_email_confirmation_checkout_visibility',
		'lifterlms_user_info_field_names_registration_visibility',
		'lifterlms_user_info_field_address_registration_visibility',
		'lifterlms_user_info_field_phone_registration_visibility',
		'lifterlms_user_info_field_email_confirmation_registration_visibility',
		'lifterlms_voucher_field_registration_visibility',
		'lifterlms_user_info_field_names_account_visibility',
		'lifterlms_user_info_field_address_account_visibility',
		'lifterlms_user_info_field_phone_account_visibility',
		'lifterlms_user_info_field_email_confirmation_account_visibility',
	);

	/**
	 * Setup before class
	 *
	 * Include abstract class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-admin-tool.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/tools/class-llms-admin-tool-wipe-legacy-account-options.php';

	}

	/**
	 * Setup the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Admin_Tool_Wipe_Legacy_Account_Options();

	}

	/**
	 * Tear Down
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		$this->delete_legacy_options();

	}


	/**
	 * Test get_description()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_description() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_description' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_label()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_label() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_label' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_text()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_text() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_text' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test handle()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle() {

		global $wpdb;

		$sql = "
		SELECT COUNT(*) FROM {$wpdb->options}
		WHERE option_name IN (" . implode( ', ', array_fill( 0, count( self::LEGACY_OPTIONS ), '%s' ) ) . ')';

		$query = $wpdb->prepare(
			$sql,
			self::LEGACY_OPTIONS
		);

		$this->assertEquals( 0, $wpdb->get_var( $query ) );

		$this->add_legacy_options();

		$this->assertEquals( count( self::LEGACY_OPTIONS ), $wpdb->get_var( $query ) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'handle' ) );

		$this->assertEquals( 0, $wpdb->get_var( $query ) );

	}

	/**
	 * Test can_load()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_should_load() {
		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' )
		);

		$this->add_legacy_options();

		$this->assertTrue(
			LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' )
		);
	}

	/**
	 * Add legacy options to the WP options table
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function add_legacy_options() {

		array_map( 'add_option', self::LEGACY_OPTIONS, array_fill( 0, count( self::LEGACY_OPTIONS ), 'yes' ) );

	}


	/**
	 * Remove legacy options to the WP options table
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function delete_legacy_options() {

		array_map( 'delete_option', self::LEGACY_OPTIONS, array_fill( 0, count( self::LEGACY_OPTIONS ), 'yes' ) );

	}

}
