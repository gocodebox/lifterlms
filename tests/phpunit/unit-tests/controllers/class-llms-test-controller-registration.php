<?php
/**
 * Tests for the LLMS_Controller_Registration class
 *
 * @group controllers
 * @group registration
 * @group controller_registration
 *
 * @since 3.19.4
 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
 * @since 5.0.0 Install forms during setup.
 */
class LLMS_Test_Controller_Registration extends LLMS_UnitTestCase {

	/**
	 * Test registration form submission.
	 *
	 * @since 3.19.4
	 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
	 * @since 5.0.0 Install forms during setup.
	 * @since 6.0.0 Replaced use of deprecated items.
	 *              - `LLMS_UnitTestCase::setup_get()` method with `LLMS_Unit_Test_Mock_Requests::mockGetRequest()`
	 *              - `LLMS_UnitTestCase::setup_post()` method with `LLMS_Unit_Test_Mock_Requests::mockPostRequest()`
	 * @since [version] Call the tested method directly instead of indirectly via `do_action( 'init' )`.
	 * 
	 * @return void
	 */
	public function test_register() {

		$main = new LLMS_Controller_Registration();

		LLMS_Install::create_pages();
		LLMS_Forms::instance()->install( true );

		// form not submitted
		$this->mockPostRequest( array() );
		$main->register();
		$this->assertEquals( 0, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );

		// not submitted
		$this->mockGetRequest( array() );
		$main->register();
		$this->assertEquals( 0, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );

		// form submitted but missing things
		$this->mockPostRequest( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
		) );
		$main->register();
		$this->assertEquals( 1, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );
		llms_clear_notices();

		// user already logged in
		$uid = $this->factory->user->create();
		wp_set_current_user( $uid );
		// form submitted but missing things
		$this->mockPostRequest( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
		) );
		$main->register();
		$this->assertEquals( 2, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );
		llms_clear_notices();

		// log that user out
		wp_set_current_user( null );

		// incomplete form
		$this->mockPostRequest( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
			'user_login' => '',
			'email_address' => 'fake@mock.org',
			'password' => 'owb2g1pICH82',
		) );
		$main->register();
		$this->assertEquals( 3, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );
		llms_clear_notices();

		// this should register a user
		$this->mockPostRequest( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
			'user_login' => '',
			'email_address' => 'fake@mock.org',
			'email_address_confirm' => 'fake@mock.org',
			'password' => 'owb2g1pICH82',
			'password_confirm' => 'owb2g1pICH82',
			'first_name' => 'David',
			'last_name' => 'Stevens',
			'llms_billing_address_1' => 'Voluptatem',
			'llms_billing_address_2' => '#12345',
			'llms_billing_city' => 'Harum est dolorum sed vel perspiciatis consequatur dignissimos possimus delectus quos optio omnis error quas rem dicta et consectetur odio',
			'llms_billing_state' => 'Esse ea est dolore sed sunt ipsum a ut nemo dolorem aut aliquam cillum asperiores minim culpa',
			'llms_billing_zip' => '72995',
			'llms_billing_country' => 'US',
			'llms_voucher' => '',
			'llms_mc_consent' => 'yes',
			'llms_agree_to_terms' => 'yes',
		) );

		// exceptions thrown in testing env instead of exit()
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', llms_get_page_url( 'myaccount' ) ) );

		// run these assertions within actions because the exit() at the end of the redirect will halt program execution
		// and then we'll never get to these assertions!
		add_action( 'lifterlms_before_new_user_registration', function() {
			$this->assertEquals( 4, did_action( 'lifterlms_before_new_user_registration' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'lifterlms_user_registered', function() {
			$this->assertEquals( 1, did_action( 'lifterlms_user_registered' ) );
		} );

		$main->register();

	}

}
