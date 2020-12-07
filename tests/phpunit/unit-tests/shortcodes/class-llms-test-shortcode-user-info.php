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
			'if' => 'mock',
		);

		$this->assertArrayHasKey( 'field', LLMS_Unit_Test_Util::call_method( $obj, 'set_attributes', array( $atts ) ) );
		$this->assertArrayHasKey( 'if', LLMS_Unit_Test_Util::call_method( $obj, 'set_attributes', array( $atts ) ) );

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
			'field' => 'first_name',
		);
		$this->assertArrayHasKey( 'field', LLMS_Unit_Test_Util::call_method( $obj, 'set_attributes', array( $atts ) ) );

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

	}

	/**
	 * Test get_output() with logged in user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_output_current_user() {

		$uid = $this->factory->user->create();
		update_user_meta( $uid, 'first_name', 'mock' );
		wp_set_current_user( $uid );

		$this->assertShortcodeOutputEquals( 'mock', '[user first_name]' );

	}

}
