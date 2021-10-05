<?php
/**
 * Tests for the LLMS_Admin_Tool_Wipe_Legacy_Account_Options class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 * @group legacy_opts
 *
 * @since 5.0.0
 * @since 5.3.0 Use `LLMS_Admin_Tool_Test_Case` and remove redundant methods/tests.
 */
class LLMS_Test_Admin_Tool_Wipe_Legacy_Account_Options extends LLMS_Admin_Tool_Test_Case {

	/**
	 * Name of the class being tested.
	 *
	 * @var sting
	 */
	const CLASS_NAME = 'LLMS_Admin_Tool_Wipe_Legacy_Account_Options';

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
	 * Tear Down
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		$this->delete_legacy_options();

	}

	/**
	 * Test handle()
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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

		// Check that the tool doesn't load after it has been handled.
		LLMS_Unit_Test_Util::call_method( $this->main, 'handle' );

		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' )
		);

	}

	/**
	 * Add legacy options to the WP options table
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	private function add_legacy_options() {

		array_map( 'add_option', self::LEGACY_OPTIONS, array_fill( 0, count( self::LEGACY_OPTIONS ), 'yes' ) );

	}


	/**
	 * Remove legacy options to the WP options table
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	private function delete_legacy_options() {

		array_map( 'delete_option', self::LEGACY_OPTIONS, array_fill( 0, count( self::LEGACY_OPTIONS ), 'yes' ) );

	}

}
