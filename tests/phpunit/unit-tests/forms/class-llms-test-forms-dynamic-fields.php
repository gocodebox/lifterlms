<?php
/**
 * Test LLMS_Forms Singleton
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group forms_dynamic_fields
 *
 * @since 5.0.0
 * @version [version]
 */
class LLMS_Test_Forms_Dynamic_fields extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Forms_Dynamic_fields();
		$this->forms = LLMS_Forms::instance();
	}

	/**
	 * Test add_password_strength_meter() when no password field found
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_no_password() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-text {"id":"block-one"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks, 'checkout' ) );
		$this->assertEquals( $blocks, $res );
	}

	/**
	 * Test add_password_strength_meter() when the password meter attr is not present
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_meter_attr_not_present() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-user-password {"id":"password"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks, 'checkout' ) );
		$this->assertEquals( $blocks, $res );

	}

	/**
	 * Test add_password_strength_meter() when the meter is explicitly disabled
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_meter_disabled() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-user-password {"id":"password","meter":"no"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks, 'checkout' ) );
		$this->assertEquals( $blocks, $res );

	}

	/**
	 * Test add_password_strength_meter() when meter is enabled
	 *
	 * @since 5.0.0
	 * @since 5.0.1 Add aria attribute to expected response.
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_meter_enabled() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-user-password {"id":"password","meter":"yes","meter_description":"test"} /--><!-- wp:llms/form-field-text {"id":"block-one"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks, 'checkout' ) );

		// Password block is unaffected.
		$this->assertEquals( $blocks[0], $res[0] );

		$this->assertEquals( '<div class="llms-form-field type-html llms-cols-12 llms-cols-last"><div aria-live="polite" class="llms-field-html llms-password-strength-meter" id="llms-password-strength-meter"></div><span class="llms-description">test</span></div><div class="clear"></div>', trim( $res[1]['innerHTML'] ) );

		// Block after password is in the new last position, unaffected.
		$this->assertEquals( $blocks[1], $res[2] );

	}


	/**
	 * Test add_password_strength_meter() when meter is enabled on the account edit screen
	 *
	 * @since 5.0.0
	 * @since 5.0.1 Add aria attribute to expected response.
	 *
	 * @return void
	 */
	public function test_add_password_strength_meter_meter_enabled_account() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-user-password {"id":"password","meter":"yes","meter_description":"test"} /--><!-- wp:llms/form-field-text {"id":"block-one"} /-->' );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'add_password_strength_meter', array( $blocks, 'account' ) );

		// Password block is unaffected.
		$this->assertEquals( $blocks[0], $res[0] );

		// Differs from above test because of the `llms-visually-hidden-field` class.
		$this->assertEquals( '<div class="llms-form-field type-html llms-cols-12 llms-cols-last llms-visually-hidden-field"><div aria-live="polite" class="llms-field-html llms-password-strength-meter" id="llms-password-strength-meter"></div><span class="llms-description">test</span></div><div class="clear"></div>', trim( $res[1]['innerHTML'] ) );

		// Block after password is in the new last position, unaffected.
		$this->assertEquals( $blocks[1], $res[2] );

	}

	/**
	 * Test find_block() when no password confirmation is present
	 *
	 * This also tests that the password field isn't the first field in the form to ensure the index returns properly.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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

	public function test_get_toggle_button_html() {

		$expect = '<a class="llms-toggle-fields" data-fields="#mock" data-change-text="Change Label" data-cancel-text="Cancel" href="#">Change Label</a>';
		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_toggle_button_html', array( '#mock', 'Label' ) );

		$this->assertEquals( $expect, $res );

	}

	public function test_modify_account_form_wrong_form() {

		$input = 'fake';
		$this->assertEquals( $input, $this->main->modify_account_form( $input, 'checkout' ) );

	}

	public function test_modify_account_form() {

		$fields = LLMS_Unit_Test_Util::call_method( LLMS_Forms::instance(), 'load_reusable_blocks', array( parse_blocks( LLMS_Form_Templates::get_template( 'account' ) ) ) );


		$res = $this->main->modify_account_form( $fields, 'account' );

		// @todo.

	}

	/**
	 * Test required fields block added to form blocks
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_add_required_block_fields() {

		// Make sure no user is logged in.
		wp_set_current_user( null );

		// Email and pw fields not added to forms which are not checkout or registration.
		$this->assertEmpty( $this->main->maybe_add_required_block_fields( array(), 'what', array() ) );
		$this->assertEmpty( $this->main->maybe_add_required_block_fields( array(), 'account', array() ) );

		// Email and pw fields added to checkout form.
		$checkout_blocks = $this->main->maybe_add_required_block_fields( array(), 'checkout', array() );
		foreach ( array( 'email_address', 'password' ) as $id ) {
			$this->assertNotEmpty(
				LLMS_Unit_Test_Util::call_method(
					$this->main,
					'find_block',
					array(
						$id,
						$checkout_blocks
					)
				),
				$id
			);
		}

		// Email and pw fields added to registration form.
		$registration_blocks = $this->main->maybe_add_required_block_fields( array(), 'registration', array() );
		foreach ( array( 'email_address', 'password' ) as $id ) {
			$this->assertNotEmpty(
				LLMS_Unit_Test_Util::call_method(
					$this->main,
					'find_block',
					array(
						$id,
						$registration_blocks
					)
				),
				$id
			);
		}

		// Log in.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Email and pw field not added to any forms except account for logged in users.
		$this->assertEmpty( $this->main->maybe_add_required_block_fields( array(), 'what', array() ) );
		$this->assertEmpty( $this->main->maybe_add_required_block_fields( array(), 'checkout', array() ) );
		$this->assertEmpty( $this->main->maybe_add_required_block_fields( array(), 'registration', array() ) );

		$account_blocks = $this->main->maybe_add_required_block_fields( array(), 'account', array() );
		foreach ( array( 'email_address', 'password' ) as $id ) {
			$this->assertNotEmpty(
				LLMS_Unit_Test_Util::call_method(
					$this->main,
					'find_block',
					array(
						$id,
						$account_blocks
					)
				),
				$id
			);
		}

		// Make sure no user is logged in.
		wp_set_current_user( null );

	}

	/**
	 * Test required fields blocks not added to form blocks if they already have them.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_add_required_block_fields_check_not_dupes() {

		foreach ( array( 'email_address', 'password' ) as $id ) {
			foreach ( array( 'checkout', 'registration', 'account' ) as $location ) {
				$this->forms->create( $location, false );
				$blocks = $this->forms->get_form_blocks( $location );
				$block  = LLMS_Unit_Test_Util::call_method(
					$this->main,
					'find_block',
					array(
						$id,
						$blocks
					)
				);
				$this->assertNotEmpty(
					$block,
					"{$location}:{$id}"
				);

				// Check again for dupes.
				array_splice( $blocks, $block[0], 1 ); // Remove just found block.

				$this->assertEmpty(
					LLMS_Unit_Test_Util::call_method(
						$this->main,
						'find_block',
						array(
							$id,
							$blocks
						),
					),
					"{$location}:{$id}"
				);

			}
		}
	}
}
