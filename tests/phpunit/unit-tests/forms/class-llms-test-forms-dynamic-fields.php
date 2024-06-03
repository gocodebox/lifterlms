<?php
/**
 * Test LLMS_Forms_Dynamic_Fields Singleton
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group forms_dynamic_fields
 *
 * @since 5.0.0
 * @version 5.4.1
 */
class LLMS_Test_Forms_Dynamic_fields extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
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

		$fields = LLMS_Unit_Test_Util::call_method( $this->forms, 'load_reusable_blocks', array( parse_blocks( LLMS_Form_Templates::get_template( 'account' ) ) ) );


		$res = $this->main->modify_account_form( $fields, 'account' );

		// @todo.
		$this->markTestIncomplete();

	}

	/**
	 * Test required fields block added to form blocks
	 *
	 * @since 5.1.0
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
	 * Test required fields made visible if they were not
	 *
	 * This additionally covers `LLMS_Forms_Dynamic_Fields::make_block_visible()` and `LLMS_Forms_Dynamic_Fields::get_confirm_group()`.
	 *
	 * @since 5.1.1
	 *
	 * @return void
	 */
	public function test_maybe_add_required_block_fields_not_visible_fields() {

		// Make sure no user is logged in.
		wp_set_current_user( null );

		// Only visible to logged in users, for testing purposes.
		$email_confirm_original_block = array( // Use a confirm block to cover `LLMS_Forms_Dynamic_Fields::get_confirm_group()`.
			'blockName'    => 'llms/form-field-confirm-group',
			'attrs'        => array(
				'fieldLayout'     => 'columns',
				'llms_visibility' => 'logged_in',
			),
			'innerBlocks'  => array(
				array(
					'blockName'    => 'llms/form-field-user-email',
					'attrs'        => array(
						'required'                  => true,
						'llms_visibility'           => 'logged_in',
						'id'                        => 'email_address',
						'name'                      => 'email_address',
						'label'                     => 'Email Address',
						'data_store'                => 'users',
						'data_store_key'            => 'user_email',
						'field'                     => 'email',
						'isConfimationControlField' => true,
						'match'                     => 'email_address_confirm',
						'isOriginal'                => true, // For testing purposes.
					),
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				),
				array(
					'blockName'    => 'llms/form-field-text',
					'attrs'        => array(
						'required'                  => true,
						'id'                        => 'email_address_confirm',
						'name'                      => 'email_address_confirm',
						'label'                     => 'Confirm Email Address',
						'data_store'                => '',
						'data_store_key'            => '',
						'field'                     => 'email',
						'isConfimationControlField' => true,
						'match'                     => 'email_address_confirm',
					),
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				),
			),
			'innerHTML'    => '',
			'innerContent' => array(
				null,
				null,
			),
		);

		// Only visible to logged in users, for testing purposes.
		$password_original_block = array(
			'blockName'    => 'llms/form-field-user-password',
			'attrs'        => array(
				'required'                  => true,
				'llms_visibility'           => 'logged_in',
				'id'                        => 'password',
				'name'                      => 'password',
				'label'                     => 'Password',
				'data_store'                => 'users',
				'data_store_key'            => 'user_pass',
				'field'                     => 'password',
				'isOriginal'                => true, // For testing purposes.
			),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);

		$blocks = $this->main->maybe_add_required_block_fields(
			array(
				$email_confirm_original_block,
				$password_original_block
			),
			'checkout',
			array()
		);


		// Check the email block is visible.
		$email_block = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'find_block',
			array(
				'email_address',
				$blocks
			)
		);
		$this->assertNotEmpty( $email_block );
		$this->assertTrue( $this->forms->is_block_visible_in_list( $email_block[1], $blocks ) );

		// Check the password is visible.
		$password_block = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'find_block',
			array(
				'password',
				$blocks
			)
		);
		$this->assertNotEmpty( $password_block );
		$this->assertTrue( $this->forms->is_block_visible_in_list( $password_block[1], $blocks ) );

		// Check both email and password block are the original ones (not replaced, only made visibile).
		$this->assertTrue( $email_block[1]['attrs']['isOriginal'] );
		$this->assertTrue( $password_block[1]['attrs']['isOriginal'] );

		// Move the password block into a group block, so to test it's correctly extrapolated from its parent.
		$blocks = $this->main->maybe_add_required_block_fields(
			array(
				$email_confirm_original_block,
				array(
					'blockName'    => 'core/group',
					'attrs'        => array(
						'isPasswordParent' => true,
					),
					'innerBlocks'  => array(
						$password_original_block
					),
					'innerHTML'    => '',
					'innerContent' => array(
						null
					),
				)
			),
			'checkout',
			array()
		);

		// Check the password is visible.
		$password_block = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'find_block',
			array(
				'password',
				$blocks
			)
		);
		$this->assertNotEmpty( $password_block );
		$this->assertTrue( $this->forms->is_block_visible_in_list( $password_block[1], $blocks ) );
		// It's the original one.
		$this->assertTrue( $password_block[1]['attrs']['isOriginal'] );
		// Check it has no parents anymore.
		$this->assertTrue( $this->forms->is_block_visible_in_list( $password_block[1], $blocks ) );
		// Check its former parent is now empty.
		foreach ( $blocks as $block ) {
			if ( 'core/group' === $block['blockName'] && ! empty( $block['attrs']['isPasswordParent'] ) ) {
				$this->assertEmpty( $block['innerBlocks'] );
			}
		}

	}

	/**
	 * Test required fields blocks not added to form blocks if they already have them.
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_maybe_add_required_block_fields_check_no_dupes() {

		foreach ( array( 'checkout', 'registration', 'account' ) as $location ) {
			if ( 'account' === $location ) {
				wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
			}

			$this->forms->create( $location, true );
			$blocks = $this->forms->get_form_blocks( $location );

			foreach ( array( 'email_address', 'password' ) as $id ) {

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
				array_splice( $blocks, $block[0], 1); // Remove just found block.

				$this->assertEmpty(
					LLMS_Unit_Test_Util::call_method(
						$this->main,
						'find_block',
						array(
							$id,
							$blocks
						)
					),
					"{$location}:{$id}"
				);
			}

			if ( 'account' === $location ) {
				wp_set_current_user( null );
			}

		}

	}

	/**
	 * Test remove_block
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_remove_block() {

		$this->forms->create( 'checkout', true );
		$blocks = $this->forms->get_form_blocks( 'checkout' );

		// Remove a field block, e.g. the email one.
		$email_field_block = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'find_block',
			array(
				'email_address',
				$blocks
			)
		)[1];

		$removed = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'remove_block',
			array(
				$email_field_block,
				&$blocks
			)
		);

		$this->assertTrue( $removed );

		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'find_block',
				array(
					'email_address',
					$blocks
				)
			)
		);

		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'remove_block',
				array(
					$email_field_block,
					&$blocks
				)
			)
		);

	}

	/**
	 * Test required fields are still added if their reusable blocks exist but do not contain them.
	 *
	 * @since 5.4.1
	 *
	 * @return void
	 */
	public function test_required_fields_added_when_reusable_empty() {

		foreach ( array( 'checkout', 'registration' ) as $location ) {
			if ( 'account' === $location ) {
				wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
			}

			// Get reusable blocks.
			foreach ( array( 'email', 'password' ) as $block_name ) {
				$reusable_block = LLMS_Form_Templates::get_block( $block_name, $location, true );

				// Turn reusable block contents into text (we remove the fields from them).
				if ( ! empty( $reusable_block['attrs']['ref'] ) ) {
					wp_update_post(
						array(
							'ID'      => $reusable_block['attrs']['ref'],
							'post_content' => '<p>Nothing special</p>',
						)
					);
				}
			}

			$this->forms->create( $location, true );
			// Here's where the required fields are added back.
			$blocks = $this->forms->get_form_blocks( $location );

			foreach ( array( 'email_address', 'password' ) as $id ) {

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

			}

			if ( 'account' === $location ) {
				wp_set_current_user( null );
			}

		}

	}
}
