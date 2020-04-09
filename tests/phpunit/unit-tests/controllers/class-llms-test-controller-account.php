<?php
/**
 * Tests for the LLMS_Controller_Account class
 *
 * @group controllers
 *
 * @since 3.19.0
 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
 * @since [version] Added tests for the `lost_password()` and `reset_password()` methods.
 */
class LLMS_Test_Controller_Account extends LLMS_UnitTestCase {

	// Consider dates equal within 60 seconds.
	private $date_delta = 60;

	/**
	 * setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Controller_Account();

	}

	/**
	 * Teardown the test case.
	 *
	 * Clears LifterLMS Notices.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		llms_clear_notices();

	}

	/**
	 * Mock wp_mail() arguments to ensure we fail when we want to test a wp_mail() failure.
	 *
	 * @since [version]
	 *
	 * @param  array $args Associative array of arguments passed to wp_mail()
	 * @return array
	 */
	public function fail_wp_mail( $args ) {

		$args['to'] = 'fail';
		return $args;

	}

	/**
	 * Test order completion actions
	 *
	 * @since 3.19.0
	 * @since [version] Use `$this->main->cancel_subscription()` instead of `do_action( 'init' )`.
	 *
	 * @return void
	 */
	public function test_cancel_subscription() {

		// form not submitted
		$this->setup_post( array() );
		$this->main->cancel_subscription();
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );

		// form submitted but missing required fields
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
		) );
		$this->main->cancel_subscription();
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// form submitted but invalid order id or the order id is invalid
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
			'order_id' => 123,
		) );
		$this->main->cancel_subscription();
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
		$this->main->cancel_subscription();
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
			$this->main->cancel_subscription();

			$expected = 'llms-active' === $status ? 'llms-pending-cancel' : 'llms-cancelled';
			$this->assertEquals( $expected, get_post_status( $order->get( 'id' ) ) );

		}

	}

	/**
	 * Test lost_password() when form not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_not_submitted() {

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->assertNull( $this->main->lost_password() );
		$this->assertEquals( $actions, did_action( 'llms_before_lost_password_form_submit' ) );

	}

	/**
	 * Test lost_password() when an invalid nonce is submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_invalid_nonce() {

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->mockPostRequest( array(
			'_lost_password_nonce' => 'fake',
		) );

		$this->assertNull( $this->main->lost_password() );
		$this->assertEquals( $actions, did_action( 'llms_before_lost_password_form_submit' ) );

	}


	/**
	 * Test lost_password() error: login not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_missing_login() {

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
		) );

		$res = $this->main->lost_password();
		$this->assertEquals( ++$actions, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_pass_reset_missing_login', $res );

		$this->assertHasNotice( 'Enter a username or e-mail address.', 'error' );

	}

	/**
	 * Test lost_password() error: user not found.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_user_not_found() {

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => 'fake',
		) );

		$res = $this->main->lost_password();

		$this->assertEquals( ++$actions, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_pass_reset_invalid_login', $res );

		$this->assertHasNotice( 'Invalid username or e-mail address.', 'error' );

	}

	/**
	 * Test lost_password() when password reset is disabled by the `allow_password_reset` WP core filter.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_reset_disabled() {

		$user = $this->factory->user->create_and_get();

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => $user->user_email,
		) );

		add_filter( 'allow_password_reset', '__return_false' );

		$res = $this->main->lost_password();

		$this->assertEquals( ++$actions, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'no_password_reset', $res );

		$this->assertHasNotice( 'Password reset is not allowed for this user', 'error' );

		remove_filter( 'allow_password_reset', '__return_false' );

	}

	/**
	 * Test lost_password() when a wp_mail() error is encountered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_email_error() {

		$user = $this->factory->user->create_and_get();

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => $user->user_email,
		) );

		add_filter( 'wp_mail', array( $this, 'fail_wp_mail' ) );

		$res = $this->main->lost_password();

		$this->assertEquals( ++$actions, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_pass_reset_email_failure', $res );

		$this->assertHasNotice( 'Unable to reset password due to an unknown error. Please try again.', 'error' );

		remove_filter( 'wp_mail', array( $this, 'fail_wp_mail' ) );

	}

	/**
	 * Test lost_password() success with an email address.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_with_email_success() {

		$user = $this->factory->user->create_and_get();

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => $user->user_email,
		) );

		$res = $this->main->lost_password();

		$this->assertEquals( ++$actions, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertTrue( $res );

		$this->assertHasNotice( 'Check your e-mail for the confirmation link.', 'success' );

	}

	/**
	 * Test lost_password() success with username.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lost_password_with_login_success() {

		$user = $this->factory->user->create_and_get();

		// Baseline actions count.
		$actions = did_action( 'llms_before_lost_password_form_submit' );

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => $user->user_login,
		) );

		$res = $this->main->lost_password();

		$this->assertEquals( ++$actions, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertTrue( $res );

		$this->assertHasNotice( 'Check your e-mail for the confirmation link.', 'success' );

	}

	/**
	 * Test reset_password(): form not submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_not_submitted() {

		$this->assertNull( $this->main->reset_password() );

	}

	/**
	 * Test reset_password(): invalid nonce
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_invalid_nonce() {

		$this->mockPostRequest( array(
			'_reset_password_nonce' => 'fake',
		) );

		$this->assertNull( $this->main->reset_password() );

	}

	/**
	 * Test reset_password(): form validation errors (missing required fields)
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_form_validation_error() {

		$this->mockPostRequest( array(
			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
		) );

		$res = $this->main->reset_password();

		$this->assertIsWPError( $res );
		$this->assertEquals( 4, count( $res->errors ) );

		$errors = array(
			'Password is a required field',
			'Confirm Password is a required field',
			'llms_reset_key is a required field',
			'llms_reset_login is a required field',
		);

		$notices = llms_get_notices();

		foreach ( $errors as $error ) {
			$this->assertStringContains( $error, $notices );
		}

	}

	/**
	 * Test reset_password(): password reset key errors
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_reset_key_errors() {

		$pass = wp_generate_password( 12 );

		// Fake user and key.
		$post = array(
			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
			'password'         => $pass,
			'password_confirm' => $pass,
			'llms_reset_key'   => 'fake',
			'llms_reset_login' => 'fake',
		);
		$this->mockPostRequest( $post );

		$res = $this->main->reset_password();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'invalid_key', $res );
		$this->assertStringContains( 'Invalid key', llms_get_notices() );

		// Real user fake key.
		$user = $this->factory->user->create_and_get();
		$data['llms_reset_login'] = $user->user_login;
		$this->mockPostRequest( $post );

		$res = $this->main->reset_password();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'invalid_key', $res );
		$this->assertStringContains( 'Invalid key', llms_get_notices() );

	}

	/**
	 * Test reset_password(): success
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reset_password_success() {

		$user = $this->factory->user->create_and_get();
		$pass = wp_generate_password( 12 );

		// Fake user and key.
		$post = array(
			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
			'password'         => $pass,
			'password_confirm' => $pass,
			'llms_reset_key'   => get_password_reset_key( $user ),
			'llms_reset_login' => $user->user_login,
		);

		$this->mockPostRequest( $post );

		$this->assertTrue( $this->main->reset_password() );

		$user = get_user_by( 'id', $user->ID );
		$this->assertTrue( wp_check_password( $pass, $user->user_pass ) );

		$this->assertHasNotices( 'success' );
		$this->assertStringContains( 'Your password has been updated.', llms_get_notices() );

	}

	/**
	 * Test account update form submission handler
	 *
	 * @since 3.19.4
	 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from test lib.
 	 * @since [version] Use `$this->main->cancel_subscription()` instead of `do_action( 'init' )`.
	 *
	 * @return void
	 */
	public function test_update() {

		LLMS_Install::create_pages();

		// form not submitted
		$this->setup_post( array() );
		$this->main->update();
		$this->assertEquals( 0, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );

		// also not submitted
		$this->setup_get( array() );
		$this->main->update();
		$this->assertEquals( 0, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );

		// form submitted but user isn't logged in
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		$this->main->update();
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

		// create a user
		$uid = $this->factory->user->create();
		// sign the user in
		wp_set_current_user( $uid );

		// form submitted but missing fields
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		$this->main->update();
		$this->assertEquals( 2, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

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
			$this->assertEquals( 3, did_action( 'llms_before_user_account_update_submit' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'lifterlms_user_updated', function() {
			$this->assertEquals( 1, did_action( 'lifterlms_user_updated' ) );
		} );

		$this->main->update();

	}

}
