<?php
/**
 * Tests for the LLMS_Controller_Registration class
 * @group    controllers
 * @group    registration
 * @since    3.19.4
 * @version  3.19.4
 */
class LLMS_Test_Controller_Registration extends LLMS_UnitTestCase {

	/**
	 * Test order completion actions
	 * @return   void
	 * @since    3.19.4
	 * @version  3.19.4
	 */
	public function test_register() {

		LLMS_Install::create_pages();

		// form not submitted
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );

		// not submitted
		$this->setup_get( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );

		// form submitted but missing things
		$this->setup_post( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );
		llms_clear_notices();

		// user already logged in
		$uid = $this->factory->user->create();
		wp_set_current_user( $uid );
		// form submitted but missing things
		$this->setup_post( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 2, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );
		llms_clear_notices();

		// log that user out
		wp_set_current_user( null );

		// incomplete form
		$this->setup_post( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
			'user_login' => '',
			'email_address' => 'fake@mock.org',
			'password' => 'owb2g1pICH82',
		) );
		do_action( 'init' );
		$this->assertEquals( 3, did_action( 'lifterlms_before_new_user_registration' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_registered' ) );
		llms_clear_notices();

		// this should register a user
		$this->setup_post( array(
			'_llms_register_person_nonce' => wp_create_nonce( 'llms_register_person' ),
			'user_login' => '',
			'email_address' => 'fake@mock.org',
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
		$this->expectException( LLMS_Testing_Exception_Exit::class );
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

		do_action( 'init' );

	}

}
