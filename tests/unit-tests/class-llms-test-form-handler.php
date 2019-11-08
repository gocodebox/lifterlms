<?php
/**
 * Test Form Handler class
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group form_handler
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Form_Handler extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->handler = LLMS_Form_Handler::instance();
		LLMS_Forms::instance()->install();

	}

	/**
	 * Teardown the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

	}

	/**
	 * Test checking matching fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_check_matching_fields() {

		$posted = array(
			'email' => 'l@l.ms',
			'email_confirm' => 'l@l.ms',
		);

		$fields = array(
			array(
				'id'    => 'email',
				'name'  => 'email',
				'match' => 'email_confirm',
			),
			array(
				'id'    => 'email_confirm',
				'name'  => 'email_confirm',
				'match' => 'email',
			),
		);

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->handler, 'check_matching_fields', array( $posted, $fields ) ) );

	}

	/**
	 * Test checking matching fields when the matching field doesn't exist in the form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_check_matching_fields_err_missing_match_definition() {

		$posted = array(
			'email' => 'l@l.ms',
		);

		$fields = array(
			array(
				'id'    => 'email',
				'name'  => 'email',
				'match' => 'email_confirm',
			),
		);

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->handler, 'check_matching_fields', array( $posted, $fields ) ) );

	}

	/**
	 * Test checking matchind fields when user data is mismatched.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_check_matching_fields_err_missing_match() {

		$posted = array(
			'email' => 'l@l.ms',
		);

		$fields = array(
			array(
				'id'    => 'email',
				'name'  => 'email',
				'match' => 'email_confirm',
			),
			array(
				'id'    => 'email_confirm',
				'name'  => 'email_confirm',
				'match' => 'email',
			),
		);

		$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'check_matching_fields', array( $posted, $fields ) );
		$this->assertIsWPError( $valid );
		$this->assertWPErrorCodeEquals( 'llms-form-field-not-matched', $valid );

		// Should only have a single error message, not one error for each message.
		$this->assertEquals( 1, count( $valid->get_error_messages( 'llms-form-field-not-matched' ) ) );

	}

	/**
	 * Test reducing a fields array down to only the required fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_required_fields() {

		$required = array(
			array(
				'name'     => 'good',
				'required' => true,
			),
			array(
				'name'     => 'good2',
				'required' => true,
			),
		);

		$optional = array(
			array(
				'name'     => 'bad',
				'required' => false,
			),
		);

		$fields = LLMS_Unit_Test_Util::call_method( $this->handler, 'get_required_fields', array( $required ) );
		$this->assertEquals( $required, $fields );

		$fields = LLMS_Unit_Test_Util::call_method( $this->handler, 'get_required_fields', array( $optional ) );
		$this->assertEquals( array(), $fields );

		$fields = LLMS_Unit_Test_Util::call_method( $this->handler, 'get_required_fields', array( array_merge( $optional, $required ) ) );
		$this->assertEquals( $required, $fields );

	}

	/**
	 * Sanitize email fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sanitize_field_email() {

		$emails = array(
			"hello'hi@hello.com" => "hello'hi@hello.com",
			'hello@hello.com' => 'hello@hello.com',
			'admin@hello.net' => '    admin@hello.net',
			'admin+hi@hello.edu' => 'admin+hi@hello.edu',
			'hello@so.many.subdomains.org'=> 'hello@so.many.subdomains.org',
			'ip@204.32.111.32' => 'ip@204.32.111.32',
			'l@l.ms' => 'l@l.ms',
			'' => 'fake',
		);

		foreach ( $emails as $clean => $dirty ) {
			$this->assertEquals( $clean, LLMS_Unit_Test_Util::call_method( $this->handler, 'sanitize_field', array( $dirty, array( 'type' => 'email' ) ) ) );
		}

	}

	/**
	 * Test email validation.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_field_email() {

		$emails = array(
			array( true, "hello'hi@hello.com" ),
			array( true, 'hello@hello.com' ),
			array( true, 'admin@hello.net' ),
			array( true, 'admin+hi@hello.edu' ),
			array( true, 'hello@so.many.subdomains.org' ),
			array( true, 'ip@204.32.111.32' ),
			array( true, 'l@l.ms' ),
			array( false, 'fake' ),
			array( false, 'f@k.e' ),
			array( false, ' f' ),
			array( false, 'fake.mock.com' ),
		);

		foreach ( $emails as $test ) {

			$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $test[1], array( 'type' => 'email' ) ) );

			if ( $test[0] ) {
				$this->assertTrue( $valid );
			} else {
				$this->assertIsWPError( $valid );
			}

		}

	}

	/**
	 * Test special validation for user emails. They must be unique.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_field_user_email() {

		$email = sprintf( 'mock+%s@mock.tld', uniqid() );

		// Valid.
		$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $email, array( 'id' => 'user_email' ) ) );
		$this->assertTrue( $valid );

		// Not unique.
		$this->factory->user->create( array( 'user_email' => $email ) );
		$exists = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $email, array( 'id' => 'user_email' ) ) );
		$this->assertIsWPError( $exists );
		$this->assertWPErrorCodeEquals( 'llms-form-field-not-unique', $exists );

	}

	/**
	 * Test special validation for the username field.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_field_user_login() {

		// Banned.
		$banned = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( 'admin', array( 'id' => 'user_login' ) ) );
		$this->assertIsWPError( $banned );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $banned );

		// Not valid.
		$invalid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '   +-!', array( 'id' => 'user_login' ) ) );
		$this->assertIsWPError( $invalid );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $invalid );

		$login = sprintf( 'mock-%s', uniqid() );

		// Valid.
		$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $login, array( 'id' => 'user_login' ) ) );
		$this->assertTrue( $valid );

		// Not unique.
		$this->factory->user->create( array( 'user_login' => $login ) );
		$exists = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $login, array( 'id' => 'user_login' ) ) );
		$this->assertIsWPError( $exists );
		$this->assertWPErrorCodeEquals( 'llms-form-field-not-unique', $exists );

	}

	/**
	 * Sanitize telephone fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sanitize_field_tel() {

		$tels = array(
			'+00 000 000 0000'   => '+00 000 000 0000',
			'+00-000-000-0000'   => '+00-000-000-0000',
			'(000) 000 0000'     => '(000) 000 0000',
			'+00.000.000.0000'   => '+00.000.000.0000',
			'+00 (000) 000 0000' => '+00 (000) 000 0000',
			'000 000 0000 #000'  => '000 000 0000 #000',
			'+00'                => '+00 aaa bbb cccc',
		);

		foreach ( $tels as $clean => $dirty ) {
			$this->assertEquals( $clean, LLMS_Unit_Test_Util::call_method( $this->handler, 'sanitize_field', array( $dirty, array( 'type' => 'tel' ) ) ) );
		}

	}

	/**
	 * Test telephone validation.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_field_tel() {

		$emails = array(
			array( true, '+00 000 000 0000' ),
			array( true, '+00-000-000-0000' ),
			array( true, '(000) 000 0000' ),
			array( true, '+00.000.000.0000' ),
			array( true, '+00 (000) 000 0000' ),
			array( true, '000 000 0000 #000' ),
			array( false, '+00 aaa bbb cccc' ),
			array( false, 'fake' ),
		);

		foreach ( $emails as $test ) {

			$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $test[1], array( 'type' => 'tel' ) ) );

			if ( $test[0] ) {
				$this->assertTrue( $valid );
			} else {
				$this->assertIsWPError( $valid );
			}

		}

	}

	/**
	 * Sanitize number fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sanitize_field_number() {

		$numbers = array(
			'1'        => '1',
			'100'      => '100',
			'1.00'     => '1.00',
			'1,00'     => '1,00',
			'1,000'    => '1,000',
			'1.000'    => '1.000',
			'1,000.00' => '1,000.00',
			'1.000,00' => '1.000,00',
			'2'        => ' fake 2 mock',
		);

		foreach ( $numbers as $clean => $dirty ) {
			$this->assertEquals( $clean, LLMS_Unit_Test_Util::call_method( $this->handler, 'sanitize_field', array( $dirty, array( 'type' => 'number' ) ) ) );
		}

	}

	/**
	 * Test number field validation.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_field_number() {

		$field = array(
			'type' => 'number',
			'name' => 'number',
		);

		$emails = array(
			array( true, '1' ),
			array( true, '-1' ),
			array( true, '+1' ),
			array( true, '100' ),
			array( true, '1.00' ),
			array( true, '1,00' ),
			array( true, '1,000' ),
			array( true, '1.000' ),
			array( true, '1,000.00' ),
			array( true, '1.000,00' ),
			array( false, ' fake 2 mock' ),
		);

		foreach ( $emails as $test ) {

			$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $test[1], $field ) );

			if ( $test[0] ) {
				$this->assertTrue( $valid );
			} else {
				$this->assertIsWPError( $valid );
			}

		}

		$field['attributes']['min'] = '25';
		$field['attributes']['max'] = '500';

		// Number too small.
		$this->assertIsWPError( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '10', $field ) ) );
		$this->assertIsWPError( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '10.50', $field ) ) );
		$this->assertIsWPError( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '24.99', $field ) ) );
		$this->assertIsWPError( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '-500', $field ) ) );

		// Number too large.
		$this->assertIsWPError( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '500.01', $field ) ) );
		$this->assertIsWPError( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '1,000', $field ) ) );
		$this->assertIsWPError( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( '+99999', $field ) ) );

	}

	/**
	 * Test special voucher field validation.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_field_voucher() {

		$field = array( 'id' => 'llms_voucher' );

		// Invalid code.
		$res = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( 'invalid-code', $field ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorMessageEquals( 'Voucher code "invalid-code" could not be found.', $res );

		// Valid code.
		$voucher = $this->get_mock_voucher( 1 );
		$code    = $voucher->get_voucher_codes()[0]->code;
		$res = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $code, $field ) );
		$this->assertTrue( $res );

		// Use the voucher.
		$voucher->use_voucher( $code, 123 );

		// Valid code without any remaining redemptions.
		$res = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_field', array( $code, $field ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorMessageEquals( sprintf( 'Voucher code "%s" has already been redeemed the maximum number of times.', $code ), $res );

	}

	/**
	 * Test submit on an invalid form location.
	 *
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_missing_required() {

		$ret = $this->handler->submit( array(), 'checkout' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );

	}

	/**
	 * test submission validation errors
	 *
	 * @todo sanitizing first so invalid fields show up as missing (since sanitization leaves the fields blank).
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_validation_errors() {}

	/**
	 * Test submission matching errors.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_matching_errors() {

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'mismatch@mock.com',
			'password' => '123456',
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_registration_voucher_errors() {

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'fake@mock.com',
			'password' => '123456',
			'password_confirm' => '123456',
			'first_name' => 'Jeffrey',
			'last_name' => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city' => 'Reseda',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '91234',
			'llms_billing_country' => 'US',
			'llms_voucher' => 'invalid-code',
		);

		$ret = $this->handler->submit( $args, 'registration' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( 'Voucher code "invalid-code" could not be found.', $ret );

	}

	/**
	 * Test successful submission for a new users.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_success() {

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'fake@mock.com',
			'password' => '123456',
			'password_confirm' => '123456',
			'first_name' => 'Jeffrey',
			'last_name' => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city' => 'Reseda',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '91234',
			'llms_billing_country' => 'US',
		);

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

		$this->assertTrue( wp_check_password( '123456', $user->user_pass, $user->ID ) );

	}

	/**
	 * Test successful submission for a new users.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_success_with_voucher() {

		$voucher  = $this->get_mock_voucher( 1 );
		$products = $voucher->get_products();
		$code     = $voucher->get_voucher_codes()[0]->code;

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'fake@mock.com',
			'password' => '123456',
			'password_confirm' => '123456',
			'first_name' => 'Jeffrey',
			'last_name' => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city' => 'Reseda',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '91234',
			'llms_billing_country' => 'US',
			'llms_voucher' => $code,
		);

		$ret = $this->handler->submit( $args, 'registration' );

		$this->assertTrue( is_int( $ret ) );
		$user = new WP_User( $ret );

		// Ensure voucher was redeemed successfully.
		foreach ( $products as $product_id ) {
			llms_is_user_enrolled( $user->ID, $product_id, 'all', false );
		}

	}

	/**
	 * Test submission success as a logged in user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_update() {}

	/**
	 * Test validity of form based on presence of all required fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_required_fields_missing_fields() {

		$posted = array();

		$fields = array(
			array(
				'label'    => __( 'Email', 'lifterlms' ),
				'name'     => 'email',
				'required' => true,
			),
			array(
				'label'    => __( 'Password', 'lifterlms' ),
				'name'     => 'password',
				'required' => true,
			),
			array(
				'label'    => __( 'Name', 'lifterlms' ),
				'name'     => 'name',
				'required' => false,
			),
		);

		// Missing both required fields,
		$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_required_fields', array( $posted, $fields ) );
		$this->assertIsWPError( $valid );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $valid );
		$this->assertEquals( 2, count( $valid->errors['llms-form-missing-required'] ) );
		$this->assertEquals( 'Email is a required field.', $valid->errors['llms-form-missing-required'][0] );
		$this->assertEquals( 'Password is a required field.', $valid->errors['llms-form-missing-required'][1] );

		// Only missing password.
		$posted['email'] = 'fake@mock.com';
		$valid = LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_required_fields', array( $posted, $fields ) );
		$this->assertIsWPError( $valid );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $valid );
		$this->assertEquals( 1, count( $valid->errors['llms-form-missing-required'] ) );
		$this->assertEquals( 'Password is a required field.', $valid->errors['llms-form-missing-required'][0] );

	}

	/**
	 * Test form required field validation when all required fields are posted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_validate_required_fields_success() {


		$posted = array(
			'email'    => 'fake@mock.com',
			'password' => '1234',
		);

		$fields = array(
			array(
				'label'    => __( 'Email', 'lifterlms' ),
				'name'     => 'email',
				'required' => true,
			),
			array(
				'label'    => __( 'Password', 'lifterlms' ),
				'name'     => 'password',
				'required' => true,
			),
			array(
				'label'    => __( 'Name', 'lifterlms' ),
				'name'     => 'name',
				'required' => false,
			),
		);

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->handler, 'validate_required_fields', array( $posted, $fields ) ) );

	}



}
