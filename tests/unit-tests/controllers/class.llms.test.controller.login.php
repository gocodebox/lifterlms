<?php
/**
 * Tests for the LLMS_Controller_Registration class
 * @group    controllers
 * @group    registration
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Controller_Login extends LLMS_UnitTestCase {

	/**
	 * Test order completion actions
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_login() {

		LLMS_Install::create_pages();

		// form not submitted
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'lifterlms_before_user_login' ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );

		// not submitted
		$this->setup_get( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'lifterlms_before_user_login' ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );

		// form submitted but missing things
		$this->setup_post( array(
			'_llms_login_user_nonce' => wp_create_nonce( 'llms_login_user' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'lifterlms_before_user_login' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );
		llms_clear_notices();

		// incomplete form
		$this->setup_post( array(
			'_llms_login_user_nonce' => wp_create_nonce( 'llms_login_user' ),
			'email_address' => 'fake@mock.org',
		) );
		do_action( 'init' );
		$this->assertEquals( 2, did_action( 'lifterlms_before_user_login' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'wp_login' ) );
		llms_clear_notices();

		$uid = $this->factory->user->create( array(
			'user_email' => 'test@arstarst.com',
			'user_pass' => '123456789',
		) );

		// this should login a user
		$this->setup_post( array(
			'_llms_login_user_nonce' => wp_create_nonce( 'llms_login_user' ),
			'llms_login' => 'test@arstarst.com',
			'llms_password' => '123456789',
		) );

		// exceptions thrown in testing env instead of exit()
		$this->expectException( LLMS_Testing_Exception_Exit::class );
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
		}, 10, 2 );

		do_action( 'init' );

	}

}
