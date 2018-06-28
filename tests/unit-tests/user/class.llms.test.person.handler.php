<?php
/**
 * Tests for LifterLMS Core Functions
 * @group    LLMS_Student
 * @group    LLMS_Person_Handler
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Person_Handler extends LLMS_UnitTestCase {

	/**
	 * Teste username generation
	 * @return   void
	 * @since    [version]
	 * @version  [version]
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
			'mockmock' => "mock'mock",
			'mockmock' => "mock+mock",
			'mock.mock' => "mock.mock",
			'mock-mock' => "mock-mock",
			'mock mock' => "mock mock",
			'mockmock' => "mock!mock",
		);

		foreach ( $tests as $expect => $email ) {
			$this->assertEquals( $expect, LLMS_Person_Handler::generate_username( $email . '@whatever.com' ) );
		}

	}


	// public function test_get_available_fields() {}


	// public function test_get_login_fields() {}


	// public function test_get_lost_password_fields() {}


	// public function test_get_password_reset_fields() {}


	// public function test_login() {

		// LLMS_Person_Handler::login( array(
		// 	'llms_login' => 'arstarst',
		// 	'llms_password' => 'arstarst',
		// ) );

	// }


	// public function test_register() {}


	// public function test_update() {}

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
