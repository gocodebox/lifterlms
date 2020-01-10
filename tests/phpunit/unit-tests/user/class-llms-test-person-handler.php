<?php
/**
 * Tests for LifterLMS Core Functions
 * @group    LLMS_Student
 * @group    LLMS_Person_Handler
 * @since    3.19.4
 * @version  3.29.4
 */
class LLMS_Test_Person_Handler extends LLMS_UnitTestCase {

	/**
	 * Test username generation
	 * @return   void
	 * @since    3.19.4
	 * @version  3.19.4
	 */
	public function test_generate_username() {

		// username is first part of email
		$this->assertEquals( 'mock', LLMS_Person_Handler::generate_username( 'mock@whatever.com' ) );

		// create a user with the mock username
		$this->factory->user->create( array(
			'user_login' => 'mock',
		) );

		// test that usernames are unique
		$i = 1;
		while ( $i <= 5 ) {
			$this->factory->user->create( array(
				'user_login' => sprintf( 'mock%d', $i ),
			) );
			$this->assertEquals( sprintf( 'mock%d', $i+1 ), LLMS_Person_Handler::generate_username( 'mock@whatever.com' ) );
			$i++;
		}

		// test character sanitization
		$tests = array(
			'mock_mock' => 'mock_mock',
			"mock'mock" => "mockmock",
			'mock+mock' => "mockmock",
			'mock.mock' => "mock.mock",
			'mock-mock' => "mock-mock",
			'mock mock' => "mock mock",
			'mock!mock' => "mockmock",
		);

		foreach ( $tests as $email => $expect) {
			$this->assertEquals( $expect, LLMS_Person_Handler::generate_username( $email . '@whatever.com' ) );
		}

	}


	// public function test_get_available_fields() {}


	// public function test_get_login_fields() {}


	// public function test_get_lost_password_fields() {}


	// public function test_get_password_reset_fields() {}

	/**
	 * Test logging in with a username.
	 *
	 * @return  void
	 * @since   3.29.4
	 * @version 3.29.4
	 */
	public function test_login_with_username() {

		// Test with username.
		update_option( 'lifterlms_registration_generate_username', 'no' );

		// Missing login.
		$login = LLMS_Person_Handler::login( array(
			'llms_password' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_login', $login );

		// Missing Password
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_password', $login );

		// Totally Invalid creds.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => '3OGgpZZ146cH3vw775aMg1R7qQIrF4ph',
			'llms_password' => 'Ip439RKmf0am5MWRjD38ov6M45OEYs79',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Test against a real user with bad creds.
		$uid = $this->factory->user->create( array( 'user_login' => 'test_user_login', 'user_pass' => '1234' ) );

		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'test_user_login',
			'llms_password' => '1',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Success.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'test_user_login',
			'llms_password' => '1234',
		) );

		$this->assertEquals( $uid, $login );
		wp_logout();

	}

	/**
	 * Test logging in with a username.
	 *
	 * @return  void
	 * @since   3.29.4
	 * @version 3.29.4
	 */
	public function test_login_with_email() {

		// Set autousername option.
		update_option( 'lifterlms_registration_generate_username', 'yes' );

		// Missing login.
		$login = LLMS_Person_Handler::login( array(
			'llms_password' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_login', $login );

		// Invalid email address.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_login', $login );

		// Missing password.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'faker@fake.tld',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_password', $login );

		// Totally Invalid creds.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => '3OGgpZZ146cH3vw775aMg1R7qQIrF4ph@fake.tld',
			'llms_password' => 'Ip439RKmf0am5MWRjD38ov6M45OEYs79',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Test against a real user with bad creds.
		$user = $this->factory->user->create_and_get( array( 'user_pass' => '1234' ) );

		$login = LLMS_Person_Handler::login( array(
			'llms_login' => $user->user_email,
			'llms_password' => '1',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Success.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => $user->user_email,
			'llms_password' => '1234',
		) );

		$this->assertEquals( $user->ID, $login );
		wp_logout();

		// Make sure that email addresses with an apostrophe in them can login without issue.
		$user = $this->factory->user->create_and_get( array( 'user_email' => "mock'mock@what.org", 'user_pass' => '1234' ) );
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => wp_slash( $user->user_email ), // add slashes like the $_POST data.
			'llms_password' => '1234',
		) );

		$this->assertEquals( $user->ID, $login );
		wp_logout();

	}

	// public function test_register() {}


	/**
	 * @todo    this is an incomplete test
	 * @return  [type]
	 * @since   3.26.1
	 * @version 3.26.1
	 */
	public function test_update() {

		$data = array();

		// No user Id supplied.
		$update = LLMS_Person_Handler::update( $data, 'account' );
		$this->assertTrue( is_wp_error( $update ) );
		$this->assertEquals( 'user_id', $update->get_error_code() );

		$uid = $this->factory->user->create( array( 'role' => 'student' ) );
		$user = new WP_User( $uid );

		// user Id Interpreted from current logged in user.
		wp_set_current_user( $uid );
		$update = LLMS_Person_Handler::update( $data, 'account' );
		$this->assertTrue( is_wp_error( $update ) );
		$this->assertFalse( in_array( 'user_id', $update->get_error_codes(), true ) );
		wp_set_current_user( null );

		// Used ID explicitly passed.
		$data['user_id'] = $uid;
		$update = LLMS_Person_Handler::update( $data, 'account' );
		$this->assertTrue( is_wp_error( $update ) );
		$this->assertFalse( in_array( 'user_id', $update->get_error_codes(), true ) );

	}

	private function get_mock_registration_data( $data = array() ) {

		$password = wp_generate_password();

		return wp_parse_args( $data, array(
			'user_login' => 'mocker',
			'email_address' => 'mocker@mock.com',
			'first_name' => 'Bird',
			'last_name' => 'Person',
			'llms_billing_address_1' => '1234 Street Ave.',
			'llms_billing_address_2' => '#567',
			'llms_billing_city' => 'Anywhere,',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '12345',
			'llms_billing_country' => 'US',
			'llms_agree_to_terms' => 'yes',
			'password' => $password,
			'password_confirm' => $password,
		) );

	}

	public function test_validate_fields() {

		/**
		 * Registration
		 */

		// no data
		$this->assertTrue( is_wp_error( LLMS_Person_Handler::validate_fields( array(), 'registration' ) ) );

		$data = $this->get_mock_registration_data();
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'registration' ) );

		// check emails with quotes
		$data['email_address'] = "mock\'mock@what.org";
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'registration' ) );


		/**
		 * Login
		 */

		// no data
		$this->assertTrue( is_wp_error( LLMS_Person_Handler::validate_fields( array(), 'login' ) ) );

		$data = array(
			'llms_login' => 'mocker@mock.com',
			'llms_password' => '4bKyvI41Xxnf',
		);
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'login' ) );

		// check emails with quotes
		$data = array(
			'llms_login' => "moc\'ker@mock.com",
			'llms_password' => '4bKyvI41Xxnf',
		);
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'login' ) );

		/**
		 * Update
		 */

		// no data
		$this->assertTrue( is_wp_error( LLMS_Person_Handler::validate_fields( array(), 'account' ) ) );

		$data = $this->get_mock_registration_data();
		$data['email_address_confirm'] = $data['email_address'];
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'account' ) );


		$uid = $this->factory->user->create( array(
			'user_email' =>"mock\'mock@what.org",
		) );
		wp_set_current_user( $uid );

		$data = $this->get_mock_registration_data();
		$data['email_address'] = "mock\'mock@what.org";
		$data['email_address_confirm'] = $data['email_address'];
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'account' ) );

	}



}
