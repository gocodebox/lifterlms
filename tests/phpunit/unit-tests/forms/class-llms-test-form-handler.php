<?php
/**
 * Test Form Handler class
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group form_handler
 *
 * @since 5.0.0
 */
class LLMS_Test_Form_Handler extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->handler = LLMS_Form_Handler::instance();

		// Actions aren't firing on unit tests without explicitly calling the constructor to add them. Not sure why.
		LLMS_Unit_Test_Util::call_method( $this->handler, '__construct' );

		LLMS_Forms::instance()->install( true );

	}

	/**
	 * Teardown the test.
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

	}

	public function make_address_required( $settings ) {

		if ( 0 === strpos( $settings['name'], 'llms_billing_' ) && 'llms_billing_address_2' !== $settings['name'] ) {
			$settings['required'] = true;
		}

		return $settings;
	}

	protected function get_data_for_form_submit( $args = array() ) {

		$email = uniqid( 'fake-' ) . '@mock.tld';

		return wp_parse_args( $args, array(
			'email_address'          => $email,
			'email_address_confirm'  => $email,
			'password'               => '12345678',
			'password_confirm'       => '12345678',
			'first_name'             => 'Jeffrey',
			'last_name'              => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city'      => 'Reseda',
			'llms_billing_state'     => 'CA',
			'llms_billing_zip'       => '91234',
			'llms_billing_country'   => 'US',
		) );

	}

	/**
	 * Test submit() for the account form when there's no logged in user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_account_no_user() {

		$ret = $this->handler->submit( array(), 'account' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-no-user', $ret );

	}

	/**
	 * Test submit() for the account for when there is a logged in user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_account_with_user() {

		wp_set_current_user( $this->factory->student->create() );
		$ret = $this->handler->submit( array(), 'account' );

		// We're still going to get an error but it won't be the "llms-form-no-user" error.
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );

	}

	/**
	 * Test submit on an invalid form location.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_invalid() {

		$ret = $this->handler->submit( array(), 'fake' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-invalid-location', $ret );

	}

	/**
	 * Test submit with missing required fields.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_missing_required() {

		$ret = $this->handler->submit( array(), 'checkout' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );

	}

	/**
	 * Test custom fields added the legacy way are correctly parsed
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_custom_field_legacy() {

		$custom_fields = array(
			array(
				'columns'      => 12,
				'id'           => 'llms_company_name',
				'label'        => 'Company name',
				'last_column'  => false,
				'required'     => true,
				'type'         => 'text',
			),
		);

		add_filter(
			'lifterlms_get_person_fields',
			function( $fields, $screen ) use ( $custom_fields ) {
				array_push( $fields, ...$custom_fields );
				return $fields;
			},
			10,
			2
		);

		$args = $this->get_data_for_form_submit();

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );

		$args[ 'llms_company_name' ] = 'something';

		$ret = $this->handler->submit( $args, 'checkout' );

		$this->assertTrue( is_int( $ret ) );
		$this->assertEquals( 'something', get_user_meta( $ret, 'llms_company_name', true ) );

		remove_all_filters( 'lifterlms_get_person_fields' );

	}

	/**
	 * Test submission matching errors.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_matching_errors() {

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'mismatch@mock.com',
			'password' => '12345678',
			'password_confirm' => 'mistmatch',
			'first_name' => 'Jeffrey',
			'last_name' => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city' => 'Reseda',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '91234',
			'llms_billing_country' => 'US',
		);

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-not-matched', $ret );

	}

	/**
	 * Test registration form submissions with an invalid voucher code.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_registration_voucher_err_not_found() {

		$ret = $this->handler->submit( $this->get_data_for_form_submit( array( 'llms_voucher' => 'invalid-code' ) ), 'registration' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( 'Voucher code "invalid-code" could not be found.', $ret );

	}

	/**
	 * Test registration form submissions with a deleted voucher code.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_registration_voucher_err_deleted() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];
		$voucher->delete_voucher_code( $code->id );

		$ret = $this->handler->submit( $this->get_data_for_form_submit( array( 'llms_voucher' => $code->code ) ), 'registration' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( sprintf( 'Voucher code "%s" could not be found.', $code->code ), $ret );

	}

	/**
	 * Test registration form submissions when a voucher code's parent post is deleted (or not published).
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_registration_voucher_err_post_deleted() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];
		wp_delete_post( $code->voucher_id, true );

		$ret = $this->handler->submit( $this->get_data_for_form_submit( array( 'llms_voucher' => $code->code ) ) , 'registration' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( sprintf( 'Voucher code "%s" could not be found.', $code->code ), $ret );

	}

	/**
	 * Test registration form submissions when a voucher code has been redeemed the maximum number of times allowed
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_registration_voucher_err_max() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];
		$voucher->use_voucher( $code->code, $this->factory->user->create() );

		$ret = $this->handler->submit( $this->get_data_for_form_submit( array( 'llms_voucher' => $code->code ) ), 'registration' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( sprintf( 'Voucher code "%s" has already been redeemed the maximum number of times.', $code->code ), $ret );

	}

	/**
	 * Test submit() with the validate_only flag and validation errors.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_submit_validate_only_error() {

		$ret = $this->handler->submit( array(), 'checkout', array( 'validate_only' => true ) );

		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );

	}

	/**
	 * Test submit() with the validate_only flag and no validation errors.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_submit_validate_only_success() {

		$this->assertTrue(
			$this->handler->submit(
				$this->get_data_for_form_submit(),
				'checkout',
				array( 'validate_only' => true )
			)
		);

	}

	/**
	 * Test successful submission for a new users.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Provide `password_current` when updating the `password`.
	 *
	 * @return void
	 */
	public function test_submit_success() {

		$args = $this->get_data_for_form_submit();

		// Register.
		$ret = $this->handler->submit( $args, 'checkout' );

		$this->assertTrue( is_int( $ret ) );
		$user = new WP_User( $ret );

		$this->assertEquals( $args['email_address'], $user->user_email );
		$this->assertEquals( $args['first_name'], $user->first_name );
		$this->assertEquals( $args['last_name'], $user->last_name );

		$this->assertEquals( $args['llms_billing_address_1'], $user->llms_billing_address_1 );
		$this->assertEquals( $args['llms_billing_city'], $user->llms_billing_city );
		$this->assertEquals( $args['llms_billing_state'], $user->llms_billing_state );
		$this->assertEquals( $args['llms_billing_zip'], $user->llms_billing_zip );
		$this->assertEquals( $args['llms_billing_country'], $user->llms_billing_country );

		$this->assertTrue( wp_check_password( $args['password'], $user->user_pass, $user->ID ) );

		// Update.
		wp_set_current_user( $ret );
		$args['first_name'] = 'Maude';
		$args['display_name'] = $user->display_name;
		// Current password is required when updating the password.
		$args['password_current'] = $args['password'];
		$this->assertSame( $ret, $this->handler->submit( $args, 'account' ) );
		$this->assertEquals( $args['first_name'], $user->first_name );

	}


	/**
	 * Test submitting account update without password update
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_submit_password_update_wrong_current_password() {

		$args = $this->get_data_for_form_submit(
			array(
				'display_name' => 'Disp', // Required on update.
			)
		);

		// Register.
		$ret = $this->handler->submit( $args, 'checkout' );

		$this->assertTrue( is_int( $ret ) );
		$user = new WP_User( $ret );

		// Update.
		wp_set_current_user( $ret );
		$args['first_name'] = 'Maude';
		unset($args['password']);
		unset($args['password_confirm']);

		$this->assertSame( $ret, $this->handler->submit( $args, 'account' ) );
		$this->assertEquals( $args['first_name'], $user->first_name );

	}

	/**
	 * Test submit password change without providing, or with wrong current password
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_submit_account_update_no_password() {

		$args = $this->get_data_for_form_submit(
			array(
				'display_name' => 'Disp', // Required on update.
			)
		);

		// Register.
		$ret = $this->handler->submit( $args, 'checkout' );

		$this->assertTrue( is_int( $ret ) );
		$user = new WP_User( $ret );

		// Update.
		wp_set_current_user( $ret );

		// No current password provided.
		$ret = $this->handler->submit( $args, 'account' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );
		$this->assertWPErrorMessageEquals( 'Current Password is a required field.', $ret );

		// Provide a wrong current password.
		$args['password_current'] = $args['password'] . "-wrong";
		$ret = $this->handler->submit( $args, 'account' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( 'The submitted password was not correct.', $ret );

	}

	/**
	 * Test submit() with a country that doesn't require states.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_address_no_zip() {

		add_filter( 'llms_field_settings', array( $this, 'make_address_required' ) );

		$args = $this->get_data_for_form_submit( array(
			'llms_billing_state' => 'C',
			'llms_billing_country' => 'UG', // Uganda.
		) );
		unset( $args['llms_billing_zip'] );

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertTrue( is_numeric( $ret ) );

		remove_filter( 'llms_field_settings', array( $this, 'make_address_required' ), 10 );

	}

	/**
	 * Test submit() with a country that doesn't require zip codes.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_address_no_states() {

		add_filter( 'llms_field_settings', array( $this, 'make_address_required' ) );

		$args = $this->get_data_for_form_submit( array(
			'llms_billing_zip' => '23424',
			'llms_billing_country' => 'AS', // America Samoa.
		) );
		unset( $args['llms_billing_state'] );

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertTrue( is_numeric( $ret ) );

		remove_filter( 'llms_field_settings', array( $this, 'make_address_required' ), 10 );

	}

	/**
	 * Test submit() with a country that doesn't require states or zip codes.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_address_no_city_or_zip() {

		add_filter( 'llms_field_settings', array( $this, 'make_address_required' ) );

		$args = $this->get_data_for_form_submit( array(
			'llms_billing_country' => 'NR',
			'llms_billing_state' => '08',
		) );
		unset( $args['llms_billing_city'] );
		unset( $args['llms_billing_zip'] );

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertTrue( is_numeric( $ret ) );

		remove_filter( 'llms_field_settings', array( $this, 'make_address_required' ), 10 );

	}

	/**
	 * Test successful submission for a new users.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_submit_success_with_voucher() {

		$voucher  = $this->get_mock_voucher( 1 );
		$products = $voucher->get_products();
		$code     = $voucher->get_voucher_codes()[0]->code;

		$args = $this->get_data_for_form_submit( array( 'llms_voucher' => $code ) );

		$ret = $this->handler->submit( $args, 'registration' );

		$this->assertTrue( is_int( $ret ) );
		$user = new WP_User( $ret );

		// Ensure voucher was redeemed successfully.
		foreach ( $products as $product_id ) {
			llms_is_user_enrolled( $user->ID, $product_id, 'all', false );
		}

	}

	/**
	 * Tests that submitting a invalid email address produces an error.
	 *
	 * @since 5.4.1
	 *
	 * @return void
	 */
	public function test_submit_registration_with_invalid_email_error() {

		// Disable user generation.
		add_filter( 'pre_option_lifterlms_registration_generate_username', '__return_empty_string' );
		LLMS_Forms::instance()->create( 'registration', true );

		$args = $this->get_data_for_form_submit(
			array(
				'user_login'             => 'the_dude',
				'email_address'          => 'fake@wrong',
				'email_address_confirm'  => 'fake@wrong',
			)
		);


		$ret = $this->handler->submit( $args, 'registration' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( 'The email address "fake@wrong" is not valid.', $ret );

		// Re-enable user generation.
		remove_filter( 'pre_option_lifterlms_registration_generate_username', '__return_empty_string' );
	}

}
