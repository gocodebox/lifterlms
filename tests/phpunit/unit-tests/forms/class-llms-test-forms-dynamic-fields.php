<?php
/**
 * Test LLMS_Forms Singleton
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group forms_dynamic_fields
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Forms_Dynamic_fields extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Forms_Dynamic_fields();

	}

	/**
	 * Test add_password_strength_meter() when no password field found
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_no_password() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-text {"id":"block-one"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks ) );
		$this->assertEquals( $blocks, $res );
	}

	/**
	 * Test add_password_strength_meter() when the password meter attr is not present
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_meter_attr_not_present() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-user-password {"id":"password"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks ) );
		$this->assertEquals( $blocks, $res );

	}

	/**
	 * Test add_password_strength_meter() when the meter is explicitly disabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_meter_disabled() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-user-password {"id":"password","meter":"no"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks ) );
		$this->assertEquals( $blocks, $res );

	}

	/**
	 * Test add_password_strength_meter() when meter is enabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_meter_enabled() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-user-password {"id":"password","meter":"yes","meter_description":"test"} /--><!-- wp:llms/form-field-text {"id":"block-one"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks ) );

		// Password block is unaffected.
		$this->assertEquals( $blocks[0], $res[0] );

		$this->assertEquals( '<div class="llms-form-field type-html llms-cols-12 llms-cols-last"><div class="llms-field-html llms-password-strength-meter" id="llms-password-strength-meter"></div><span class="llms-description">test</span></div><div class="clear"></div>', trim( $res[1]['innerHTML'] ) );

		// Block after password is in the new last position, unaffected.
		$this->assertEquals( $blocks[1], $res[2] );

	}

	/**
	 * Test find_block() when no password confirmation is present
	 *
	 * This also tests that the password field isn't the first field in the form to ensure the index returns properly.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_find_block_not_nested() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-text {"id":"block-one"} /--><!-- wp:llms/form-field-user-password {"id":"password"} /--><!-- wp:llms/form-field-text {"id":"block-two"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'find_block', array( 'password', $blocks ) );

		$this->assertEquals( array( 1, $blocks[1] ), $res );

	}

	/**
	 * Test find_block() when no password block is present
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_find_block_no_field() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-text {"id":"block-one"} /-->' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'find_block', array( 'password', $blocks ) ) );

	}

	/**
	 * Test find_block() when a password confirm field is used
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_find_block_with_confirm() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-confirm-group -->
<!-- wp:llms/form-field-user-password {"id":"password"} /-->
<!-- wp:llms/form-field-text {"id":"password-confirm"} /-->
<!-- /wp:llms/form-field-confirm-group -->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'find_block', array( 'password', $blocks ) );

		$this->assertEquals( array( 0, $blocks[0]['innerBlocks'][0] ), $res );

	}

	/**
	 * Test find_block() when a password confirm field is used and the block is nested inside another block (in this case a wp core group block)
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_find_block_nested() {

		$blocks = parse_blocks( '<!-- wp:group -->
<div class="wp-block-group"><div class="wp-block-group__inner-container"><!-- wp:llms/form-field-confirm-group -->
<!-- wp:llms/form-field-user-password {"id":"password"} /-->
<!-- wp:llms/form-field-text {"id":"password-confirm"} /-->
<!-- /wp:llms/form-field-confirm-group --></div></div>
<!-- /wp:group -->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'find_block', array( 'password', $blocks ) );

		$this->assertEquals( array( 0, $blocks[0]['innerBlocks'][0]['innerBlocks'][0] ), $res );

	}

}
