<?php
/**
 * Test LLMS_Form_Templates class
 *
 * @package LifterLMS/Tests
 *
 * @group form_templates
 *
 * @since 5.0.0
 */
class LLMS_Test_Form_Templates extends LLMS_Unit_Test_Case {

	/**
	 * Ensures the generated block content of a reusable block matches the stored "snapshot"
	 *
	 * @since 5.0.0
	 * @since 5.3.1 Remove duplicate references to last-name block.
	 *
	 * @param string $id     Field ID.
	 * @param string $actual Actual generated content to compare to the expected "snapshot"
	 * @return void
	 */
	public function assertReusableBlockContentMatchesSnapshot( $id, $actual ) {

		$snapshots = array(
			'username'     => '<!-- wp:llms/form-field-user-login {"field":"text","required":true,"label":"Username","name":"user_login","id":"user_login","data_store":"users","data_store_key":"user_login","llms_visibility":"logged_out"} /-->',
			'email'        => '<!-- wp:llms/form-field-confirm-group {"fieldLayout":"columns","llms_visibility":"logged_out"} --><!-- wp:llms/form-field-user-email {"field":"email","required":true,"label":"Email Address","name":"email_address","id":"email_address","data_store":"users","data_store_key":"user_email","llms_visibility":"off","columns":6,"last_column":false,"isConfirmationControlField":true,"match":"email_address_confirm"} /--><!-- wp:llms/form-field-text {"field":"email","required":true,"label":"Confirm Email Address","name":"email_address_confirm","id":"email_address_confirm","data_store":false,"data_store_key":false,"llms_visibility":"off","columns":6,"last_column":true,"isConfirmationField":true,"match":"email_address"} /--><!-- /wp:llms/form-field-confirm-group -->',
			'password'     => '<!-- wp:llms/form-field-confirm-group {"fieldLayout":"columns","llms_visibility":"logged_out"} --><!-- wp:llms/form-field-user-password {"field":"password","required":true,"label":"Password","name":"password","id":"password","data_store":"users","data_store_key":"user_pass","llms_visibility":"off","meter":true,"min_strength":"strong","html_attrs":{"minlength":8},"meter_description":"A strong password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.","columns":6,"last_column":false,"isConfirmationControlField":true,"match":"password_confirm"} /--><!-- wp:llms/form-field-text {"field":"password","required":true,"label":"Confirm Password","name":"password_confirm","id":"password_confirm","data_store":false,"data_store_key":false,"llms_visibility":"off","meter":true,"min_strength":"strong","html_attrs":{"minlength":8},"meter_description":"A strong password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.","columns":6,"last_column":true,"isConfirmationField":true,"match":"password"} /--><!-- /wp:llms/form-field-confirm-group -->',
			'name'         => '<!-- wp:llms/form-field-user-name --><!-- wp:llms/form-field-user-first-name {"field":"text","label":"First Name","name":"first_name","id":"first_name","data_store":"usermeta","data_store_key":"first_name","columns":6,"last_column":false,"required":true} /--><!-- wp:llms/form-field-user-last-name {"field":"text","label":"Last Name","name":"last_name","id":"last_name","data_store":"usermeta","data_store_key":"last_name","columns":6,"last_column":true,"required":true} /--><!-- /wp:llms/form-field-user-name -->',
			'display_name' => '<!-- wp:llms/form-field-user-display-name {"field":"text","required":true,"label":"Display Name","name":"display_name","id":"display_name","data_store":"users","data_store_key":"display_name"} /-->',
			'address'      => '<!-- wp:llms/form-field-user-address --><!-- wp:llms/form-field-user-address-street --><!-- wp:llms/form-field-user-address-street-primary {"field":"text","label":"Address","name":"llms_billing_address_1","id":"llms_billing_address_1","data_store":"usermeta","data_store_key":"llms_billing_address_1","columns":8,"last_column":false,"required":true} /--><!-- wp:llms/form-field-user-address-street-secondary {"field":"text","label":"","label_show_empty":true,"placeholder":"Apartment, suite, etc...","name":"llms_billing_address_2","id":"llms_billing_address_2","data_store":"usermeta","data_store_key":"llms_billing_address_2","columns":4,"last_column":true,"required":false} /--><!-- /wp:llms/form-field-user-address-street --><!-- wp:llms/form-field-user-address-city {"field":"text","label":"City","name":"llms_billing_city","id":"llms_billing_city","data_store":"usermeta","data_store_key":"llms_billing_city","required":true} /--><!-- wp:llms/form-field-user-address-country {"field":"select","label":"Country","name":"llms_billing_country","id":"llms_billing_country","data_store":"usermeta","data_store_key":"llms_billing_country","required":true,"options_preset":"countries","placeholder":"Select a Country","className":"llms-select2"} /--><!-- wp:llms/form-field-user-address-region --><!-- wp:llms/form-field-user-address-state {"field":"select","label":"State \/ Region","options_preset":"states","placeholder":"Select a State \/ Region","name":"llms_billing_state","id":"llms_billing_state","data_store":"usermeta","data_store_key":"llms_billing_state","columns":6,"last_column":false,"required":true,"className":"llms-select2"} /--><!-- wp:llms/form-field-user-address-postal-code {"field":"text","label":"Postal \/ Zip Code","name":"llms_billing_zip","id":"llms_billing_zip","data_store":"usermeta","data_store_key":"llms_billing_zip","columns":6,"last_column":true,"required":true} /--><!-- /wp:llms/form-field-user-address-region --><!-- /wp:llms/form-field-user-address -->',
			'phone'        => '<!-- wp:llms/form-field-user-phone {"field":"tel","label":"Phone Number","name":"llms_phone","id":"llms_phone","data_store":"usermeta","data_store_key":"llms_phone","required":false} /-->',
		);

		// Parse blocks for comparison, mostly because we don't care about the order of attributes.
		$expected = parse_blocks( $snapshots[ $id ] );
		$actual   = parse_blocks( $actual );

		$this->assertEquals( $expected, $actual, $id );

	}

	/**
	 * Retrieve a list of field ids as they are to be stored on a template at a given location
	 *
	 * @since 5.0.0
	 *
	 * @param string $location A form location ID.
	 * @return string[]
	 */
	private function get_template_field_id_list( $location ) {

		$blocks = $this->get_template( $location );
		$list   = array();

		foreach ( $blocks as $block ) {

			if ( 'core/block' === $block['blockName'] ) {
				$list[] = get_post_meta( $block['attrs']['ref'], '_llms_field_id', true );
			} elseif ( 'llms/form-field-redeem-voucher' === $block['blockName'] ) {
				$list[] = 'voucher';
			} else {
				$list[] = $block['blockName'];
			}

		}

		return $list;

	}

	private function get_template( $location ) {

		$res = LLMS_Form_Templates::get_template( $location );
		return parse_blocks( $res );

	}

	private function get_block_from_template( $location, $name ) {
		$blocks = $this->get_template( $location );

		foreach ( $blocks as $block ) {
			if ( $name === $block['blockName'] ) {
				return $block;
			}
		}

		return false;

	}

	/**
	 * Test create_reusable_block()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_create_and_get_reusable_block() {

		$list = require LLMS_PLUGIN_DIR . 'includes/schemas/llms-reusable-blocks.php';

		foreach ( $list as $field_id => $def ) {

			$post_id = LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'create_reusable_block', array( $field_id ) );
			$this->assertTrue( ! empty( $post_id ) && is_int( $post_id ) );

			$post = get_post( $post_id );

			// Title stored.
			$this->assertEquals( $def['title'], $post->post_title );

			// Block(s) inserted correctly.
			$this->assertReusableBlockContentMatchesSnapshot( $field_id, $post->post_content );

			// Meta data is stored.
			$this->assertEquals( 'yes', get_post_meta( $post_id, '_is_llms_field', true ) );
			$this->assertEquals( $field_id, get_post_meta( $post_id, '_llms_field_id', true ) );

			// If we try to create it again the existing post will be used (in favor of creating a new one).
			$this->assertEquals( $post_id, LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'create_reusable_block', array( $field_id ) ) );

			// Retrieve the core/block array.
			$expected = array(
				'blockName'    => 'core/block',
				'attrs'        => array(
					'ref' => $post_id,
				),
				'innerContent' => array(),
			);

			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'get_reusable_block', array( $field_id ) ) );

		}

	}

	/**
	 * Test get_template() for the account location during a clean installation.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_template_account_clean() {

		add_filter( 'llms_blocks_template_use_reusable_blocks', '__return_true' );

		$expected = array(
			'name',
			'display_name',
			'address',
			'phone',
			'email',
			'password',
		);

		$this->assertEquals( $expected, $this->get_template_field_id_list( 'account' ) );

		remove_filter( 'llms_blocks_template_use_reusable_blocks', '__return_true' );

	}

	/**
	 * Test get_template() for the account location during an upgrade.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_template_account_update() {

		add_filter( 'llms_blocks_template_use_reusable_blocks', '__return_false' );

		$opts = array(
			'lifterlms_user_info_field_email_confirmation_account_visibility' => 'no',
			'lifterlms_user_info_field_address_account_visibility'            => 'hidden',
			'lifterlms_user_info_field_names_account_visibility'              => 'optional',
			'lifterlms_user_info_field_phone_account_visibility'              => 'required',
		);

		foreach ( $opts as $key => $val ) {
			update_option( $key, $val );
		}

		// Expected List.
		$expected = array(
			'llms/form-field-user-name',
			'llms/form-field-user-display-name',
			'llms/form-field-user-phone',
			'llms/form-field-user-email',
			'llms/form-field-confirm-group',
		);
		$this->assertEquals( $expected, $this->get_template_field_id_list( 'account' ) );

		// Password has confirm group.
		$pass = $this->get_block_from_template( 'account', 'llms/form-field-confirm-group' );
		$this->assertEquals( 'llms/form-field-user-password', $pass['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'llms/form-field-text', $pass['innerBlocks'][1]['blockName'] );

		// No confirm field on email.
		$email = $this->get_block_from_template( 'account', 'llms/form-field-user-email' );
		$this->assertEquals( array(), $email['innerBlocks'] );

		// Names are optional.
		$name = $this->get_block_from_template( 'account', 'llms/form-field-user-name' );
		$this->assertFalse( $name['innerBlocks'][0]['attrs']['required'] );
		$this->assertFalse( $name['innerBlocks'][1]['attrs']['required'] );

		// Phone is required.
		$phone = $this->get_block_from_template( 'account', 'llms/form-field-user-phone' );
		$this->assertTrue( $phone['attrs']['required'] );

		remove_filter( 'llms_blocks_template_use_reusable_blocks', '__return_false' );

	}

	/**
	 * Test get_template() for the checkout location during a clean installation.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_template_checkout_clean() {

		add_filter( 'llms_blocks_template_use_reusable_blocks', '__return_true' );

		$expected = array(
			'email',
			'password',
			'name',
			'address',
			'phone',
		);
		$this->assertEquals( $expected, $this->get_template_field_id_list( 'checkout' ) );

		// With username.
		update_option( 'lifterlms_registration_generate_username', 'no' );
		array_unshift( $expected, 'username' );

		$this->assertEquals( $expected, $this->get_template_field_id_list( 'checkout' ) );

		delete_option( 'lifterlms_registration_generate_username' );

		remove_filter( 'llms_blocks_template_use_reusable_blocks', '__return_true' );

	}

	/**
	 * Test get_template() for the checkout location during an upgrade.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_template_checkout_update() {

		add_filter( 'llms_blocks_template_use_reusable_blocks', '__return_false' );

		$opts = array(
			'lifterlms_user_info_field_email_confirmation_checkout_visibility' => 'no',
			'lifterlms_user_info_field_address_checkout_visibility'            => 'hidden',
			'lifterlms_user_info_field_names_checkout_visibility'              => 'optional',
			'lifterlms_user_info_field_phone_checkout_visibility'              => 'required',
		);

		foreach ( $opts as $key => $val ) {
			update_option( $key, $val );
		}

		// Expected List.
		$expected = array(
			'llms/form-field-user-email',
			'llms/form-field-confirm-group',
			'llms/form-field-user-name',
			'llms/form-field-user-phone',
		);
		$this->assertEquals( $expected, $this->get_template_field_id_list( 'checkout' ) );

		// Password has confirm group.
		$pass = $this->get_block_from_template( 'account', 'llms/form-field-confirm-group' );
		$this->assertEquals( 'llms/form-field-user-password', $pass['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'llms/form-field-text', $pass['innerBlocks'][1]['blockName'] );

		// No confirm field on email.
		$email = $this->get_block_from_template( 'checkout', 'llms/form-field-user-email' );
		$this->assertEquals( array(), $email['innerBlocks'] );

		// Names are optional.
		$name = $this->get_block_from_template( 'checkout', 'llms/form-field-user-name' );
		$this->assertFalse( $name['innerBlocks'][0]['attrs']['required'] );
		$this->assertFalse( $name['innerBlocks'][1]['attrs']['required'] );

		// Phone is required.
		$phone = $this->get_block_from_template( 'checkout', 'llms/form-field-user-phone' );
		$this->assertTrue( $phone['attrs']['required'] );

		remove_filter( 'llms_blocks_template_use_reusable_blocks', '__return_false' );

	}

	/**
	 * Test get_template() for the registration location during a clean installation.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_template_registration_clean() {

		add_filter( 'llms_blocks_template_use_reusable_blocks', '__return_true' );

		$expected = array(
			'email',
			'password',
			'name',
			'address',
			'phone',
		);

		// No voucher.
		update_option( 'lifterlms_voucher_field_registration_visibility', 'hidden' );
		$this->assertEquals( $expected, $this->get_template_field_id_list( 'registration' ) );

		// With username.
		update_option( 'lifterlms_registration_generate_username', 'no' );
		array_unshift( $expected, 'username' );
		$this->assertEquals( $expected, $this->get_template_field_id_list( 'registration' ) );

		// With voucher.
		delete_option( 'lifterlms_voucher_field_registration_visibility' );
		$expected[] = 'voucher';
		$this->assertEquals( $expected, $this->get_template_field_id_list( 'registration' ) );

		delete_option( 'lifterlms_registration_generate_username' );

		remove_filter( 'llms_blocks_template_use_reusable_blocks', '__return_true' );

	}

	/**
	 * Test get_template() for the registration location during an upgrade.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_template_registration_update() {

		add_filter( 'llms_blocks_template_use_reusable_blocks', '__return_false' );

		$opts = array(
			'lifterlms_user_info_field_email_confirmation_registration_visibility' => 'yes',
			'lifterlms_user_info_field_address_registration_visibility'            => 'required',
			'lifterlms_user_info_field_names_registration_visibility'              => 'hidden',
			'lifterlms_user_info_field_phone_registration_visibility'              => 'hidden',
		);

		foreach ( $opts as $key => $val ) {
			update_option( $key, $val );
		}

		// Expected List.
		$expected = array(
			'llms/form-field-confirm-group', // Email
			'llms/form-field-confirm-group', // Password
			'llms/form-field-user-address',
			'voucher',
		);
		$this->assertEquals( $expected, $this->get_template_field_id_list( 'registration' ) );

		$blocks = $this->get_template( 'registration' );

		// Confirm field on email.
		$email = $blocks[0];
		$this->assertEquals( 'llms/form-field-user-email', $email['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'llms/form-field-text', $email['innerBlocks'][1]['blockName'] );

		// Password has confirm group.
		$pass = $blocks[1];
		$this->assertEquals( 'llms/form-field-user-password', $pass['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'llms/form-field-text', $pass['innerBlocks'][1]['blockName'] );

		// Address is required are optional.
		$address = $this->get_block_from_template( 'registration', 'llms/form-field-user-address' );
		$this->assertTrue( $address['innerBlocks'][0]['innerBlocks'][0]['attrs']['required'] ); // Line 1.
		$this->assertFalse( $address['innerBlocks'][0]['innerBlocks'][1]['attrs']['required'] ); // Line 2.
		$this->assertTrue( $address['innerBlocks'][1]['attrs']['required'] ); // City.
		$this->assertTrue( $address['innerBlocks'][2]['attrs']['required'] ); // Country.
		$this->assertTrue( $address['innerBlocks'][3]['innerBlocks'][0]['attrs']['required'] ); // State.
		$this->assertTrue( $address['innerBlocks'][3]['innerBlocks'][1]['attrs']['required'] ); // Postal code.

		remove_filter( 'llms_blocks_template_use_reusable_blocks', '__return_false' );

	}


	/**
	 * Test get_voucher_block() when the voucher field is disabled
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_voucher_block_disabled() {

		update_option( 'lifterlms_voucher_field_registration_visibility', 'hidden' );
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'get_voucher_block' ) );

		delete_option( 'lifterlms_voucher_field_registration_visibility' );

	}

	/**
	 * Test get_voucher_block() when voucher submission is optional.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_voucher_block_optional() {

		update_option( 'lifterlms_voucher_field_registration_visibility', 'optional' );

		$expected = array(
			'blockName'    => 'llms/form-field-redeem-voucher',
			'attrs'        => array(
				'id'             => 'llms_voucher',
				'label'          => __( 'Have a voucher?', 'lifterlms' ),
				'placeholder'    => __( 'Voucher Code', 'lifterlms' ),
				'required'       => false,
				'toggleable'     => true,
				'data_store'     => false,
				'data_store_key' => false,
			),
			'innerContent' => array(),
		);

		$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'get_voucher_block' ) );

		delete_option( 'lifterlms_voucher_field_registration_visibility' );

	}

	/**
	 * Test get_voucher_block() when voucher submission is required.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_voucher_block_required() {

		update_option( 'lifterlms_voucher_field_registration_visibility', 'required' );

		$expected = array(
			'blockName'    => 'llms/form-field-redeem-voucher',
			'attrs'        => array(
				'id'             => 'llms_voucher',
				'label'          => __( 'Have a voucher?', 'lifterlms' ),
				'placeholder'    => __( 'Voucher Code', 'lifterlms' ),
				'required'       => true,
				'toggleable'     => true,
				'data_store'     => false,
				'data_store_key' => false,
			),
			'innerContent' => array(),
		);

		$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'get_voucher_block' ) );

		delete_option( 'lifterlms_voucher_field_registration_visibility' );

	}

	/**
	 * Test prepare_blocks(): Missing properties automatically added.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_blocks_without_props() {

		$input = array(
			array(),
		);
		$expected = array(
			array(
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerContent' => array(),
			),
		);

		$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'prepare_blocks', array( $input ) ) );

	}

	/**
	 * Test prepare_blocks(): Existing props not overwritten.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_blocks_with_props() {

		$input = array(
			array(
				'attrs'       => 'fake',
				'innerBlocks' => array(),
			),
		);
		$expected = array(
			array(
				'attrs'        => 'fake',
				'innerBlocks'  => array(),
				'innerContent' => array(),
			),
		);

		$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'prepare_blocks', array( $input ) ) );

	}

	/**
	 * Test prepare_blocks(): Works recursively on inner blocks and fills innerContent.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_blocks_recursive() {

		$input = array(
			array(
				'attrs'       => array(),
				'innerBlocks' => array( array(), array() ),
			),
		);
		$expected = array(
			array(
				'attrs'        => array(),
				'innerBlocks'  => array_fill( 0, 2, array(
					'attrs'        => array(),
					'innerBlocks'  => array(),
					'innerContent' => array(),
				) ),
				'innerContent' => array( null, null ),
			),
		);

		$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'prepare_blocks', array( $input ) ) );

	}


}
