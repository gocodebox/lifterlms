<?php
/**
 * Tests for the LLMS_Controller_Account class
 *
 * @group controllers
 * @group controller_account
 *
 * @since 3.19.0
 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
 * @since [version] Create forms before executing tests account update tests.
 *               Split account update assertions into multiple tests.
 */
class LLMS_Test_Controller_Account extends LLMS_UnitTestCase {

	/**
	 * Force an error during wp_mail()
	 *
	 * Hooked to `wp_mail` filter.
	 *
	 * @since [version]
	 *
	 * @param array $args Compacted wp_mail() arguments.
	 * @return array
	 */
	public function fail_email_send( $args ) {
		$args['to'] = 'invalid.address';
		return $args;
	}


	/**
	 * Test order completion actions
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_cancel_subscription() {

		// form not submitted
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );

		// form submitted but missing required fields
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// form submitted but invalid order id or the order id is invalid
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
			'order_id' => 123,
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// create a real order
		$order = $this->get_mock_order();

		// form submitted but invalid order id or the order doesn't belong to the current user
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
			'order_id' => $order->get( 'id' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();
		wp_set_current_user( $order->get( 'user_id' ) );

		foreach ( array_keys( llms_get_order_statuses( 'recurring' ) ) as $status ) {

			// active order moves to pending cancel
			$order->set_status( $status );

			$this->setup_post( array(
				'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
				'order_id' => $order->get( 'id' ),
			) );
			do_action( 'init' );

			$expected = 'llms-active' === $status ? 'llms-pending-cancel' : 'llms-cancelled';
			$this->assertEquals( $expected, get_post_status( $order->get( 'id' ) ) );

		}

	}

	/**
	 * Ensure the lost password form is not processed when it's not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_not_submitted() {

		$controller = new LLMS_Controller_Account();

		$this->setup_post( array() );
		$this->assertNull( $controller->lost_password() );
		$this->assertEquals( 0, did_action( 'llms_before_lost_password_form_submit' ) );

	}

	/**
	 * Test the lost password form returns an error if missing a required field.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_missing_required() {

		$controller = new LLMS_Controller_Account();

		$this->setup_post( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
		) );
		$res = $controller->lost_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_lost_password_missing_login', $res );

		$this->assertEquals( 1, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertStringContains( 'Enter a username or email address.', llms_get_notices() );

	}

	/**
	 * Test lost_password() returns errors for an invalid email address.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_invalid_email() {

		$controller = new LLMS_Controller_Account();

		$this->setup_post( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => 'thisisafakeemail@fake.tld',
		) );

		$res = $controller->lost_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_lost_password_invalid_login', $res );

		$this->assertStringContains( 'Invalid username or email address.', llms_get_notices() );

	}

	/**
	 * Test lost_password() returns errors for an invalid username.
	 *
	 * @since [version]
	 *
	 * @return vod
	 */
	public function test_lost_password_invalid_username() {

		$controller = new LLMS_Controller_Account();

		$this->setup_post( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => 'thisisafakeusername',
		) );

		$res = $controller->lost_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_lost_password_invalid_login', $res );

		$this->assertStringContains( 'Invalid username or email address.', llms_get_notices() );

	}

	/**
	 * Test lost_password() when WP core get_password_reset_key() returns an error or password reset is disabled via filters.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_key_error() {

		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();
		$this->setup_post( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => $user->user_login,
		) );

		// Mock an error.
		add_filter( 'allow_password_reset', '__return_false' );

		$res = $controller->lost_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'no_password_reset', $res );

		$this->assertStringContains( 'Password reset is not allowed for this user', llms_get_notices() );

		remove_filter( 'allow_password_reset', '__return_false' );

	}

	/**
	 * Test lost_password() when an error is encountered by wp_mail().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_email_send_error() {

		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();
		$this->setup_post( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => $user->user_login,
		) );


		add_filter( 'wp_mail', array( $this, 'fail_email_send' ) );

		$res = $controller->lost_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_lost_password_email_send', $res );

		$this->assertStringContains( 'The password reset email could not be sent. An error was encountered while attempting to send mail.', llms_get_notices() );

		remove_filter( 'wp_mail', array( $this, 'fail_email_send' ) );

	}

	/**
	 * Test lost_password() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_email_success() {

		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();

		// Test with user-submitted email & username.
		foreach ( array( 'user_email', 'user_login' ) as $field ) {

			$this->setup_post( array(
				'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
				'llms_login'           => $user->$field,
			) );

			$this->assertTrue( $controller->lost_password() );

			$this->assertStringContains( 'Check your inbox for an email with instructions on how to reset your password.', llms_get_notices() );

			// Test the email sent.
			$sent = tests_retrieve_phpmailer_instance()->get_sent();
			$this->assertEquals( $user->user_email, $sent->to[0][0] );
			$this->assertEquals( 'Password Reset for Test Blog', $sent->subject );

		}

	}

	/**
	 * Test account reset password form submission handler when form is not submitted
	 *
	 * @since [version]
	 *
	 * @return void
	 */

	public function test_reset_password_not_submitted() {

		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_before_user_reset_password_submit' ) );
		$this->assertEquals( 0, did_action( 'password_reset' ) );

	}

	/**
	 * Test reset_password() missing required fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_missing_fields() {

		$controller = new LLMS_Controller_Account();

		$this->mockPostRequest( array(
 			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
		) );

		$res = $controller->reset_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms-password-reset-missing-field', $res );

		$notices = llms_get_notices();
		$this->assertStringContains( 'Password is a required field.', $notices );
		$this->assertStringContains( 'Confirm Password is a required field.', $notices );

	}

	/**
	 * Test reset_password() submitted passwords don't match.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_no_match() {

		$controller = new LLMS_Controller_Account();

		$this->mockPostRequest( array(
 			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
 			'password' => 'fake',
 			'password_confirm' => 'fake2',
		) );

		$res = $controller->reset_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms-passwords-must-match', $res );

		$notices = llms_get_notices();
		$this->assertStringContains( 'The submitted passwords do must match.', $notices );

	}

	/**
	 * Test reset_password() with an invalid user login
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_invalid_login() {

		$controller = new LLMS_Controller_Account();

		$this->mockPostRequest( array(
 			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
 			'password' => 'fake',
 			'password_confirm' => 'fake',
 			'llms_reset_login' => 'thisisafakelogin',
		) );

		$res = $controller->reset_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_invalid_key', $res );
		$this->assertStringContains( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', llms_get_notices() );

	}

	/**
	 * Test reset_password() with an invalid password reset key
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_invalid_key() {

		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();

		$this->mockPostRequest( array(
 			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
 			'password' => 'fake',
 			'password_confirm' => 'fake',
 			'llms_reset_login' => $user->user_login,
 			'llms_reset_key' => 'fake-key',
		) );

		$res = $controller->reset_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_invalid_key', $res );
		$this->assertStringContains( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', llms_get_notices() );

	}

	/**
	 * Test reset_password() with an expired password reset key.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_expired_key() {

		add_filter( 'password_reset_expiration', '__return_zero' );

		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();
		$key  = get_password_reset_key( $user );

		llms_set_password_reset_cookie( sprintf( '%1$d:%2$s', $user->ID, $key ) );

		$this->mockPostRequest( array(
 			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
 			'password' => 'fake',
 			'password_confirm' => 'fake',
 			'llms_reset_login' => $user->user_login,
 			'llms_reset_key' => $key,
		) );

		$res = $controller->reset_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_expired_key', $res );
		$this->assertStringContains( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', llms_get_notices() );

		remove_filter( 'password_reset_expiration', '__return_zero' );

	}

	/**
	 * Test reset_password() success
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_success() {

		LLMS_Install::create_pages();
		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();
		$key  = get_password_reset_key( $user );

		llms_set_password_reset_cookie( sprintf( '%1$d:%2$s', $user->ID, $key ) );

		$this->mockPostRequest( array(
 			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
 			'password' => 'fake',
 			'password_confirm' => 'fake',
 			'llms_reset_login' => $user->user_login,
 			'llms_reset_key' => $key,
		) );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( add_query_arg( 'password-reset', 1, llms_get_page_url( 'myaccount' ) ) . ' [302] YES' );

		try {

			$controller->reset_password();

		} catch( LLMS_Unit_Test_Exception_Redirect $exception ) {

			// Verify the password has been successfully changed.
			$user = get_user_by( 'id', $user->ID );
			wp_check_password( 'fake', $user->user_pass );

			$this->assertStringContains( 'Your password has been updated.', llms_get_notices() );

			throw $exception;

		}

	}

	/**
	 * Test reset_password_link_redirect(): no redirect when not on the account page.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_link_redirect_not_account_page() {

		$controller = new LLMS_Controller_Account();
		$this->go_to( home_url() );

		$controller->reset_password_link_redirect();
		$this->assertNull( $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) ) );

	}

	/**
	 * Test reset_password_link_redirect(): no redirect when missing key and/or login params.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_link_redirect_no_vars() {

		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'myaccount' ) );

		$controller = new LLMS_Controller_Account();

		// No vars.
		$controller->reset_password_link_redirect();
		$this->assertNull( $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) ) );

		// No login.
		$this->mockGetRequest( array(
			'key' => 'fake-key',
		) );
		$controller->reset_password_link_redirect();
		$this->assertNull( $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) ) );

		// No key.
		$this->mockGetRequest( array(
			'login' => 'fake-login',
		) );
		$controller->reset_password_link_redirect();
		$this->assertNull( $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) ) );

	}

	/**
	 * Test reset_password_link_redirect(): redirect & set the cookie (even if it's an invalid user.)
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_link_redirect_success_fake_user() {

		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'myaccount' ) );

		$controller = new LLMS_Controller_Account();
		$this->mockGetRequest( array(
			'key'   => 'fake-key',
			'login' => 'fake-login',
		) );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( add_query_arg( 'reset-pass', 1, llms_lostpassword_url() ) . ' [302] YES' );

		try {

			$controller->reset_password_link_redirect();

		} catch( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$cookie = $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) );
			$this->assertEquals( '0:fake-key', $cookie['value'] );
			throw $exception;

		}

	}

	/**
	 * Test reset_password_link_redirect(): redirect & set the cookie with a valid user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_link_redirect_success_real_user() {

		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'myaccount' ) );
		$user = $this->factory->user->create_and_get();

		$controller = new LLMS_Controller_Account();
		$this->mockGetRequest( array(
			'key'   => 'fake-key',
			'login' => $user->user_login,
		) );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( add_query_arg( 'reset-pass', 1, llms_lostpassword_url() ) . ' [302] YES' );

		try {

			$controller->reset_password_link_redirect();

		} catch( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$cookie = $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) );
			$this->assertEquals( sprintf( '%d:fake-key', $user->ID ), $cookie['value'] );
			throw $exception;

		}

	}

	/**
	 * Test account update form submission handler when form is not submitted
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_not_submitted() {

		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );

	}

	/**
	 * Test account update form submission handler when user is not logged in
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_no_user() {

		// form submitted but user isn't logged in
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

	}

	/**
	 * Test account update form submission handler when missing required fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_missing_fields() {

		// create a user
		$uid = $this->factory->user->create();
		// sign the user in
		wp_set_current_user( $uid );

		// form submitted but missing fields
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

	}

	/**
	 * Test account update form submission handler
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_success() {

		LLMS_Install::create_pages();
		LLMS_Forms::instance()->install();

		// I can't figure out why the action in the constructor isn't added when this test is run.
		LLMS_Unit_Test_Util::call_method( LLMS_Form_Handler::instance(), '__construct' );

		// create a user
		$uid = $this->factory->user->create();
		// sign the user in
		wp_set_current_user( $uid );

		// update something
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
			'email_address' => 'help+23568@lifterlms.com',
			'email_address_confirm' => 'help+23568@lifterlms.com',
			'first_name' => 'Marshall',
			'last_name' => 'Pate',
			'llms_billing_address_1' => 'Voluptatem',
			'llms_billing_address_2' => '#12345',
			'llms_billing_city' => 'Harum est dolorum sed vel perspiciatis consequatur dignissimos possimus delectus quos optio omnis error quas rem dicta et consectetur odio',
			'llms_billing_state' => 'Esse ea est dolore sed sunt ipsum a ut nemo dolorem aut aliquam cillum asperiores minim culpa',
			'llms_billing_zip' => '72995',
			'llms_billing_country' => 'US',
		) );

		// exceptions thrown in testing env instead of exit()
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );

		// run these assertions within actions because the exit() at the end of the redirect will halt program execution
		// and then we'll never get to these assertions!
		add_action( 'llms_before_user_account_update_submit', function() {
			$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'lifterlms_user_updated', function() {
			$this->assertEquals( 1, did_action( 'lifterlms_user_updated' ) );
		} );

		do_action( 'init' );

	}

}
