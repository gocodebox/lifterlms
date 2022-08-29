<?php
/**
 * Tests for the LLMS_Controller_Registration class
 *
 * @group controllers
 * @group registration
 *
 * @since 3.19.4
 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
 */
class LLMS_Test_Controller_Login extends LLMS_UnitTestCase {

	/**
	 * Test order completion actions
	 *
	 * @since 3.19.4
	 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
	 * @since 6.0.0 Replaced use of deprecated items.
	 *              - `LLMS_UnitTestCase::setup_get()` method with `LLMS_Unit_Test_Mock_Requests::mockGetRequest()`
	 *              - `LLMS_UnitTestCase::setup_post()` method with `LLMS_Unit_Test_Mock_Requests::mockPostRequest()`
	 * @since 6.10.0 Call the tested method directly instead of indirectly via `do_action( 'init' )`.
	 * 
	 * @return void
	 */
	public function test_login() {

		$main = new LLMS_Controller_Login();

		LLMS_Install::create_pages();

		// form not submitted
		$this->mockPostRequest( array() );
		$main->login();
		$this->assertEquals( 0, did_action( 'lifterlms_before_user_login' ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );

		// not submitted
		$this->mockGetRequest( array() );
		$main->login();
		$this->assertEquals( 0, did_action( 'lifterlms_before_user_login' ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );

		// form submitted but missing things
		$this->mockPostRequest( array(
			'_llms_login_user_nonce' => wp_create_nonce( 'llms_login_user' ),
		) );
		$main->login();
		$this->assertEquals( 1, did_action( 'lifterlms_before_user_login' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );
		llms_clear_notices();

		// incomplete form
		$this->mockPostRequest( array(
			'_llms_login_user_nonce' => wp_create_nonce( 'llms_login_user' ),
			'email_address' => 'fake@mock.org',
		) );
		$main->login();
		$this->assertEquals( 2, did_action( 'lifterlms_before_user_login' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );
		llms_clear_notices();

		$uid = $this->factory->user->create( array(
			'user_email' => 'test@arstarst.com',
			'user_pass' => '123456789',
		) );

		// this should login a user
		$this->mockPostRequest( array(
			'_llms_login_user_nonce' => wp_create_nonce( 'llms_login_user' ),
			'llms_login' => 'test@arstarst.com',
			'llms_password' => '123456789',
		) );

		// exceptions thrown in testing env instead of exit()
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', llms_get_page_url( 'myaccount' ) ) );

		// run these assertions within actions because the exit() at the end of the redirect will halt program execution
		// and then we'll never get to these assertions!
		add_action( 'lifterlms_before_user_login', function() {
			$this->assertEquals( 3, did_action( 'lifterlms_before_user_login' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'wp_login', function( $login, $user ) use ( $uid ) {
			$this->assertEquals( $uid, $user->ID );
			$this->assertEquals( 1, did_action( 'wp_login' ) );
			wp_logout();
		}, 10, 2 );

		$main->login();

	}

}
