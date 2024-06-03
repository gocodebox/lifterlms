<?php
/**
 * Test the User Info shortcode
 *
 * @package LifterLMS/Tests
 *
 * @group shortcodes
 * @group userinfo_shortcode
 *
 * @since 5.0.0
 * @version 5.0.0
 */
class LLMS_Test_Shortcode_User_Info extends LLMS_ShortcodeTestCase {

	/**
	 * Class name of the Shortcode Class
	 * @var string
	 */
	public $class_name = 'LLMS_Shortcode_User_Info';

	/**
	 * Test setting attributes with no key for the first attribute (field name).
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_set_attributes_field_no_key() {

		$obj = $this->get_class();

		$atts = array(
			0 => 'first_name',
			'or' => 'mock',
		);

		$this->assertArrayHasKey( 'key', LLMS_Unit_Test_Util::call_method( $obj, 'set_attributes', array( $atts ) ) );
		$this->assertArrayHasKey( 'or', LLMS_Unit_Test_Util::call_method( $obj, 'set_attributes', array( $atts ) ) );

	}

	/**
	 * Test setting attributes when the field name is passed.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_set_attributes_field_regular() {

		$obj = $this->get_class();

		$atts = array(
			'key' => 'first_name',
		);
		$this->assertArrayHasKey( 'key', LLMS_Unit_Test_Util::call_method( $obj, 'set_attributes', array( $atts ) ) );

	}

	/**
	 * Test get_output() with logged out user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_output_no_user() {

		$this->assertShortcodeOutputEquals( '', '[llms-user first_name]' );
		$this->assertShortcodeOutputEquals( 'Pal', '[llms-user first_name or="Pal"]' );

	}

	/**
	 * Test get_output() with logged in user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_output_with_user() {

		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );

		// No value set.
		$this->assertShortcodeOutputEquals( 'Bucko', '[llms-user first_name or="Bucko"]' );

		update_user_meta( $user->ID, 'first_name', 'mock' );
		$this->assertShortcodeOutputEquals( 'mock', '[llms-user first_name]' );

		// Works.
		$this->assertShortcodeOutputEquals( $user->ID, '[llms-user ID]' );
		$this->assertShortcodeOutputEquals( $user->display_name, '[llms-user display_name]' );
		$this->assertShortcodeOutputEquals( $user->user_email, '[llms-user user_email]' );

		// Blocked.
		$this->assertShortcodeOutputEquals( '', '[llms-user user_pass]' );

		update_user_meta( $user->ID, 'llms_phone', '123456789' );
		$this->assertShortcodeOutputEquals( '123456789', '[llms-user llms_phone]' );

	}

	/**
	 * Test output when filtering the user to display another user's information
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_output_for_another() {

		$user = $this->factory->user->create_and_get();

		$handler = function( $uid ) use( $user ) {
			return $user->ID;
		};
		add_filter( 'llms_user_info_shortcode_user_id', $handler );

		// Works.
		$this->assertShortcodeOutputEquals( $user->ID, '[llms-user ID]' );
		$this->assertShortcodeOutputEquals( $user->display_name, '[llms-user display_name]' );
		$this->assertShortcodeOutputEquals( $user->user_email, '[llms-user user_email]' );

		remove_filter( 'llms_user_info_shortcode_user_id', $handler );

	}

}
