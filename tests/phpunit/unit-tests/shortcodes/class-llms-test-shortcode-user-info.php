<?php
/**
 * Test the User Info shortcode
 *
 * @package LifterLMS/Tests
 *
 * @group shortcodes
 * @group userinfo_shortcode
 *
 * @since [version]
 * @version [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_output_no_user() {

		$this->assertShortcodeOutputEquals( '', '[user first_name]' );
		$this->assertShortcodeOutputEquals( 'Pal', '[user first_name or="Pal"]' );

	}

	/**
	 * Test get_output() with logged in user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_output_current_user() {

		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );

		// No value set.
		$this->assertShortcodeOutputEquals( 'Bucko', '[user first_name or="Bucko"]' );

		update_user_meta( $user->ID, 'first_name', 'mock' );
		$this->assertShortcodeOutputEquals( 'mock', '[user first_name]' );

		// Works.
		$this->assertShortcodeOutputEquals( $user->display_name, '[user display_name]' );
		$this->assertShortcodeOutputEquals( $user->user_email, '[user user_email]' );

		// Blocked.
		$this->assertShortcodeOutputEquals( '', '[user user_pass]' );

		update_user_meta( $user->ID, 'llms_phone', '123456789' );
		$this->assertShortcodeOutputEquals( '123456789', '[user llms_phone]' );

	}

}
