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


	// public function test_get_available_fields( $screen = 'registration', $data = array() ) {}


	// public function test_get_login_fields( $layout = 'columns' ) {}


	// public function test_get_lost_password_fields() {}


	// public function test_get_password_reset_fields( $key = '', $login = '' ) {}


	// public function test_login( $data ) {}


	// public function test_register( $data = array(), $screen = 'registration', $signon = true ) {}


	// public function test_update( $data = array(), $screen = 'update' ) {}


	// public function test_validate_fields( $data, $screen = 'registration' ) {}



}
