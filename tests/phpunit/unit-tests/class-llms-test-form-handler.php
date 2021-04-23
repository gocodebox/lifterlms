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

		// Actions aren't firing on unit tests without explicitly calling the constructor to add them. Not sure why.
		LLMS_Unit_Test_Util::call_method( $this->handler, '__construct' );

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

	protected function get_data_for_form_submit( $args ) {

		$email = uniqid( 'fake-' ) . '@mock.tld';

		return wp_parse_args( $args, array(
			'email_address'          => $email,
			'email_address_confirm'  => $email,
			'password'               => '123456',
			'password_confirm'       => '123456',
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
	 * @since [version]
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
	 * @since [version]
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
	public function test_submit_registration_voucher_err_not_found() {

		$ret = $this->handler->submit( $this->get_data_for_form_submit( array( 'llms_voucher' => 'invalid-code' ) ), 'registration' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-field-invalid', $ret );
		$this->assertWPErrorMessageEquals( 'Voucher code "invalid-code" could not be found.', $ret );

	}

	/**
	 * Test registration form submissions with a deleted voucher code.
	 *
	 * @since [version]
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
	 * @since [version]
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
		$this->assertWPErrorMessageEquals( sprintf( 'Voucher code "%s" is no longer valid.', $code->code ), $ret );

	}

	/**
	 * Test registration form submissions when a voucher code has been redeemed the maximum number of times allowed
	 *
	 * @since [version]
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
	 * Test successful submission for a new users.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_success() {

		$args = array(
			'email_address'          => 'fake@mock.com',
			'email_address_confirm'  => 'fake@mock.com',

			'password'               => '123456',
			'password_confirm'       => '123456',
			'first_name'             => 'Jeffrey',
			'last_name'              => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city'      => 'Reseda',
			'llms_billing_state'     => 'CA',
			'llms_billing_zip'       => '91234',
			'llms_billing_country'   => 'US',
		);

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

		$this->assertTrue( wp_check_password( '123456', $user->user_pass, $user->ID ) );

		// Update.
		wp_set_current_user( $ret );
		$args['first_name'] = 'Maude';
		$args['display_name'] = $user->display_name;
		$this->assertSame( $ret, $this->handler->submit( $args, 'account' ) );
		$this->assertEquals( $args['first_name'], $user->first_name );

	}

	/**
	 * Test submit() with a country that doesn't require states.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_address_no_zip() {

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'fake@mock.com',
			'password' => '123456',
			'password_confirm' => '123456',
			'first_name' => 'Jeffrey',
			'last_name' => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city' => 'Reseda',
			'llms_billing_state' => 'C',
			'llms_billing_country' => 'UG', // Uganda.
		);

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertTrue( is_numeric( $ret ) );

	}

	/**
	 * Test submit() with a country that doesn't require zip codes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_address_no_states() {

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'fake@mock.com',
			'password' => '123456',
			'password_confirm' => '123456',
			'first_name' => 'Jeffrey',
			'last_name' => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_city' => 'Reseda',
			'llms_billing_zip' => '23424',
			'llms_billing_country' => 'AS', // America Samoa.
		);

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertTrue( is_numeric( $ret ) );

	}

	/**
	 * Test submit() with a country that doesn't require states or zip codes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_submit_address_no_city_or_zip() {

		$args = array(
			'email_address' => 'fake@mock.com',
			'email_address_confirm' => 'fake@mock.com',
			'password' => '123456',
			'password_confirm' => '123456',
			'first_name' => 'Jeffrey',
			'last_name' => 'Lebowski',
			'llms_billing_address_1' => '123 Any Street',
			'llms_billing_country' => 'NR',
			'llms_billing_state' => '08',
		);

		$ret = $this->handler->submit( $args, 'checkout' );
		$this->assertTrue( is_numeric( $ret ) );

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

}
