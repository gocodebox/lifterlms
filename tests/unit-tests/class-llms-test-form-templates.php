<?php
/**
 * Test LLMS_Form_Templates class
 *
 * @package LifterLMS/Tests
 *
 * @group form_templates
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Form_Templates extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->obj = LLMS_Form_Templates::instance();

		delete_option( 'lifterlms_registration_generate_username' );
		delete_option( 'lifterlms_user_info_field_names_checkout_visibility' );
		delete_option( 'lifterlms_user_info_field_address_checkout_visibility' );
		delete_option( 'lifterlms_user_info_field_phone_checkout_visibility' );

	}

	/**
	 * Retrieve a flattened list of all LifterLMS field blocks in a given list of parsed blocks.
	 *
	 * Recursively checks innerBlocks lists to find blocks nested inside columns blocks.
	 *
	 * @since [version]
	 *
	 * @param array $blocks Blocks list from `parse_blocks()`.
	 * @return string[]
	 */
	protected function get_flat_block_list( $blocks ) {

		$flat = array();

		foreach ( $blocks as $block ) {

			if ( false !== strpos( $block['blockName'], 'llms/' ) ) {
				$flat[] = $block['blockName'];
			} elseif ( $block['innerBlocks'] ) {
				$flat = array_merge( $flat, $this->get_flat_block_list( $block['innerBlocks'] ) );
			}

		}

		return $flat;

	}

	/**
	 * Test retrieving an undefined template
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_not_exists() {

		$this->assertEquals( '', $this->obj->get_template( 'fake' ) );

	}

	/**
	 * Test checkout template with usernames enabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_username() {

		update_option( 'lifterlms_registration_generate_username', 'no' );
		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$this->assertEquals( 'llms/form-field-user-username', $blocks[0]['blockName'] );

	}

	/**
	 * Test checkout template with username disabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_without_username() {

		update_option( 'lifterlms_registration_generate_username', 'yes' );
		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$this->assertTrue( 'llms/form-field-user-username' !== $blocks[0]['blockName'] );

	}

	/**
	 * Test checkout template without email confirmation.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_without_email_confirm() {

		update_option( 'lifterlms_user_info_field_email_confirmation_checkout_visibility', 'no' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$list   = $this->get_flat_block_list( $blocks );
		$this->assertFalse( in_array( 'llms/form-field-user-email-confirm', $list, true ) );

	}

	/**
	 * Test checkout template with email confirmation
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_email_confirm() {

		update_option( 'lifterlms_user_info_field_email_confirmation_checkout_visibility', 'yes' );
		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );

		// First block is a column
		$this->assertEquals( 'core/columns', $blocks[0]['blockName'] );

		// First inner block is column with email field.
		$this->assertEquals( 'core/column', $blocks[0]['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'llms/form-field-user-email', $blocks[0]['innerBlocks'][0]['innerBlocks'][0]['blockName'] );

		// Second inner block is column with email field.
		$this->assertEquals( 'core/column', $blocks[0]['innerBlocks'][1]['blockName'] );
		$this->assertEquals( 'llms/form-field-user-email-confirm', $blocks[0]['innerBlocks'][1]['innerBlocks'][0]['blockName'] );

	}

	/**
	 * Test checkout template with a password strength meter
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_password_strength_meter() {

		update_option( 'lifterlms_registration_password_strength', 'yes' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$list   = $this->get_flat_block_list( $blocks );

		$this->assertTrue( in_array( 'llms/form-field-password-strength-meter', $list, true ) );

	}

	/**
	 * Test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_without_password_strength_meter() {

		update_option( 'lifterlms_registration_password_strength', 'no' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$list   = $this->get_flat_block_list( $blocks );

		$this->assertFalse( in_array( 'llms/form-field-password-strength-meter', $list, true ) );

	}

	/**
	 * Test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_names_required() {

		update_option( 'lifterlms_user_info_field_names_checkout_visibility', 'required' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );

		// First block is a column
		$this->assertEquals( 'core/columns', $blocks[2]['blockName'] );

		// First inner block is column with email field.
		$this->assertEquals( 'core/column', $blocks[2]['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'llms/form-field-user-first-name', $blocks[2]['innerBlocks'][0]['innerBlocks'][0]['blockName'] );

		// Required.
		$this->assertTrue( $blocks[2]['innerBlocks'][0]['innerBlocks'][0]['attrs']['required'] );

		// Second inner block is column with email field.
		$this->assertEquals( 'core/column', $blocks[2]['innerBlocks'][1]['blockName'] );
		$this->assertEquals( 'llms/form-field-user-last-name', $blocks[2]['innerBlocks'][1]['innerBlocks'][0]['blockName'] );

		// Required.
		$this->assertTrue( $blocks[2]['innerBlocks'][1]['innerBlocks'][0]['attrs']['required'] );

	}

	/**
	 * Test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_names_optional() {

		update_option( 'lifterlms_user_info_field_names_checkout_visibility', 'optional' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );

		// Not Required.
		$this->assertFalse( $blocks[2]['innerBlocks'][0]['innerBlocks'][0]['attrs']['required'] );
		$this->assertFalse( $blocks[2]['innerBlocks'][1]['innerBlocks'][0]['attrs']['required'] );

	}

	/**
	 * Test that name fields aren't returned when names are hidden.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_names_disabled() {

		update_option( 'lifterlms_user_info_field_names_checkout_visibility', 'hidden' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$list   = $this->get_flat_block_list( $blocks );
		$this->assertFalse( in_array( 'llms/form-field-user-first-name', $list, true ) );
		$this->assertFalse( in_array( 'llms/form-field-user-last-name', $list, true ) );

	}

	/**
	 * Test get template checkout with addresses required
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_addresses_required() {

		update_option( 'lifterlms_user_info_field_address_checkout_visibility', 'required' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );

		$street = $blocks[3];
		$this->assertEquals( 'core/columns', $street['blockName'] );
		$this->assertEquals( 'llms/form-field-user-address', $street['innerBlocks'][0]['innerBlocks'][0]['blockName'] );
		$this->assertTrue( $street['innerBlocks'][0]['innerBlocks'][0]['attrs']['required'] );

		$this->assertEquals( 'llms/form-field-user-address-additional', $street['innerBlocks'][1]['innerBlocks'][0]['blockName'] );
		$this->assertFalse( $street['innerBlocks'][1]['innerBlocks'][0]['attrs']['required'] );

		$city = $blocks[4];
		$this->assertEquals( 'llms/form-field-user-address-city', $city['blockName'] );
		$this->assertTrue( $city['attrs']['required'] );

		$final = $blocks[5];
		$this->assertEquals( 'core/columns', $final['blockName'] );
		$this->assertEquals( 'llms/form-field-user-address-country', $final['innerBlocks'][0]['innerBlocks'][0]['blockName'] );
		$this->assertTrue( $final['innerBlocks'][0]['innerBlocks'][0]['attrs']['required'] );

		$this->assertEquals( 'llms/form-field-user-address-state', $final['innerBlocks'][1]['innerBlocks'][0]['blockName'] );
		$this->assertTrue( $final['innerBlocks'][1]['innerBlocks'][0]['attrs']['required'] );

		$this->assertEquals( 'llms/form-field-user-address-zip', $final['innerBlocks'][2]['innerBlocks'][0]['blockName'] );
		$this->assertTrue( $final['innerBlocks'][2]['innerBlocks'][0]['attrs']['required'] );

	}

	/**
	 * Checkout with addresses optional.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_addresses_optional() {

		update_option( 'lifterlms_user_info_field_address_checkout_visibility', 'optional' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );

		$street = $blocks[3];
		$this->assertFalse( $street['innerBlocks'][0]['innerBlocks'][0]['attrs']['required'] );
		$this->assertFalse( $street['innerBlocks'][1]['innerBlocks'][0]['attrs']['required'] );

		$city = $blocks[4];
		$this->assertFalse( $city['attrs']['required'] );

		$final = $blocks[5];
		$this->assertFalse( $final['innerBlocks'][0]['innerBlocks'][0]['attrs']['required'] );
		$this->assertFalse( $final['innerBlocks'][1]['innerBlocks'][0]['attrs']['required'] );
		$this->assertFalse( $final['innerBlocks'][2]['innerBlocks'][0]['attrs']['required'] );

	}

	/**
	 * Checkout with hidden addresses.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_addresses_hidden() {

		update_option( 'lifterlms_user_info_field_address_checkout_visibility', 'hidden' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$list   = $this->get_flat_block_list( $blocks );

		$addresses = array(
			'llms/form-field-user-address',
			'llms/form-field-user-address-additional',
			'llms/form-field-user-address-city',
			'llms/form-field-user-address-country',
			'llms/form-field-user-address-state',
			'llms/form-field-user-address-zip',
		);
		foreach ( $addresses as $field ) {
			$this->assertFalse( in_array( $field, $list, true ), $field );
		}

	}

	/**
	 * Phone required.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_phone_required() {

		update_option( 'lifterlms_user_info_field_phone_checkout_visibility', 'required' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$phone  = $blocks[ count( $blocks ) - 1 ];

		$this->assertEquals( 'llms/form-field-user-phone', $phone['blockName'] );
		$this->assertTrue( $phone['attrs']['required'] );

	}

	/**
	 * Phone optional.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_phone_optional() {

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$phone  = $blocks[ count( $blocks ) - 1 ];

		$this->assertEquals( 'llms/form-field-user-phone', $phone['blockName'] );
		$this->assertFalse( $phone['attrs']['required'] );

	}

	/**
	 * Phone hidden.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_checkout_with_phone_hidden() {

		update_option( 'lifterlms_user_info_field_phone_checkout_visibility', 'hidden' );

		$blocks = parse_blocks( $this->obj->get_template( 'checkout' ) );
		$list   = $this->get_flat_block_list( $blocks );
		$this->assertFalse( in_array( 'llms/form-field-user-phone', $list, true ) );

	}

}
