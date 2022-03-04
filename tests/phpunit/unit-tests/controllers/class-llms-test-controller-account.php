<?php
/**
 * Tests for the LLMS_Controller_Account class
 *
 * @group controllers
 * @group controller_account
 *
 * @since 3.19.0
 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
 * @since 3.37.17 Added tests for the `lost_password()` and `reset_password()` methods.
 * @since 4.12.0 Added tests for `redeem_voucher()` method.
 */
class LLMS_Test_Controller_Account extends LLMS_UnitTestCase {

	// Consider dates equal within 60 seconds.
	private $date_delta = 60;

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.17
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Controller_Account();

	}

	/**
	 * Teardown the test case.
	 *
	 * Clears LifterLMS Notices.
	 *
	 * @since 3.37.17
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		llms_clear_notices();

	}

	/**
	 * Mock wp_mail() arguments to ensure we fail when we want to test a wp_mail() failure.
	 *
	 * @since 3.37.17
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
	 * @since 3.37.17 Use `$this->main->cancel_subscription()` instead of `do_action( 'init' )`.
	 * @since [version] Replaced use of deprecated items.
	 *              - `LLMS_UnitTestCase::setup_post()` method with `LLMS_Unit_Test_Mock_Requests::mockPostRequest()`
	 *
	 * @return void
	 */
	public function test_cancel_subscription() {

		// form not submitted
		$this->mockPostRequest( array() );
		$this->main->cancel_subscription();
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );

		// form submitted but missing required fields
		$this->mockPostRequest( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
		) );
		$this->main->cancel_subscription();
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// form submitted but invalid order id or the order id is invalid
		$this->mockPostRequest( array(
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
		$this->mockPostRequest( array(
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

			$this->mockPostRequest( array(
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
	 * @since 3.37.17
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
	 * @since 3.37.17
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
	 * Test the lost password form returns an error if missing a required field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_lost_password_missing_required() {

		$controller = new LLMS_Controller_Account();

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
		) );
		$res = $controller->lost_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_pass_reset_missing_login', $res );

		$this->assertEquals( 1, did_action( 'llms_before_lost_password_form_submit' ) );

		$this->assertStringContains( 'Enter a username or e-mail address.', llms_get_notices() );

	}

	/**
	 * Test lost_password() error: login not submitted.
	 *
	 * @since 3.37.17
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
	 * @since 3.37.17
	 *
	 * @return void
	 */
	public function test_lost_password_user_not_found_email() {

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
	 * Test lost_password() returns errors for an invalid username.
	 *
	 * @since 5.0.0
	 *
	 * @return vod
	 */
	public function test_lost_password_user_not_found_email_username() {

		$controller = new LLMS_Controller_Account();

		$this->mockPostRequest( array(
			'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
			'llms_login'           => 'thisisafakeusername',
		) );

		$res = $controller->lost_password();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_pass_reset_invalid_login', $res );

		$this->assertStringContains( 'Invalid username or e-mail address.', llms_get_notices() );

	}

	/**
	 * Test lost_password() when WP core get_password_reset_key() returns an error or password reset is disabled via filters.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_lost_password_key_error() {

		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();
		$this->mockPostRequest( array(
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
	 * Test lost_password() success.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_lost_password_email_success() {

		// Something prior to this test triggers a password changed email to be sent and causes this test to fail as a result.
		// Adding a reset here is faster than tracking down the test that causes that email to be sent.
		reset_phpmailer_instance();

		$controller = new LLMS_Controller_Account();

		$user = $this->factory->user->create_and_get();

		// Test with user-submitted email & username.
		foreach ( array( 'user_email', 'user_login' ) as $field ) {

			$this->mockPostRequest( array(
				'_lost_password_nonce' => wp_create_nonce( 'llms_lost_password' ),
				'llms_login'           => $user->$field,
			) );

			$this->assertTrue( $controller->lost_password() );

			$this->assertStringContains( 'Check your e-mail for the confirmation link.', llms_get_notices() );

			// Test the email sent.
			$sent = tests_retrieve_phpmailer_instance()->get_sent();
			$this->assertEquals( $user->user_email, $sent->to[0][0] );
			$this->assertEquals( 'Password Reset for Test Blog', $sent->subject );

		}

		reset_phpmailer_instance();

	}

	/**
	 * Test lost_password() when password reset is disabled by the `allow_password_reset` WP core filter.
	 *
	 * @since 3.37.17
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
	 * @since 3.37.17
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
	 * @since 3.37.17
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
	 * @since 3.37.17
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
	 * Test redeem_voucher() when the form isn't submitted
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_redeem_voucher_not_submitted() {
		$this->assertNull( $this->main->redeem_voucher() );
	}

	/**
	 * Test redeem_voucher() when there's an invalid nonce
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_redeem_voucher_invalid_nonce() {

		$this->mockPostRequest( array(
			'lifterlms_voucher_nonce' => 'fake',
		) );

		$this->assertNull( $this->main->redeem_voucher() );

	}

	/**
	 * Test redeem_voucher() when no voucher code is submitted
	 *
	 * Note: the error message doesn't really make sense but in real world scenarios
	 * and end user will never encounter this error as HTML5 validation prevents
	 * the form from being submitted without a voucher.
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_redeem_voucher_missing_voucher() {

		wp_set_current_user( $this->factory->user->create() );
		$this->mockPostRequest( array(
			'lifterlms_voucher_nonce' => wp_create_nonce( 'lifterlms_voucher_check' ),
		) );

		$res = $this->main->redeem_voucher();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'not-found', $res );
		$this->assertHasNotice( 'Voucher code "" could not be found.', 'error' );

	}

	/**
	 * Test redeem_voucher() when there's no user
	 *
	 * This shouldn't ever really happen but we'll test it just in case.
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_redeem_voucher_missing_user() {

		$this->mockPostRequest( array(
			'lifterlms_voucher_nonce' => wp_create_nonce( 'lifterlms_voucher_check' ),
		) );

		$this->assertNull( $this->main->redeem_voucher() );

	}

	/**
	 * Test redeem_voucher() when an error is encountered during the voucher redemption
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_redeem_voucher_error() {

		wp_set_current_user( $this->factory->user->create() );
		$this->mockPostRequest( array(
			'lifterlms_voucher_nonce' => wp_create_nonce( 'lifterlms_voucher_check' ),
			'llms_voucher_code'       => 'fakevouchercode1',
		) );

		$res = $this->main->redeem_voucher();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'not-found', $res );
		$this->assertHasNotice( 'Voucher code "fakevouchercode1" could not be found.', 'error' );

	}

	/**
	 * Test redeem_voucher() success
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_redeem_voucher_success() {

		$voucher = $this->create_voucher( 1, 1 );

		wp_set_current_user( $this->factory->user->create() );
		$this->mockPostRequest( array(
			'lifterlms_voucher_nonce' => wp_create_nonce( 'lifterlms_voucher_check' ),
			'llms_voucher_code'       => $voucher->get_voucher_codes()[0]->code,
		) );

		$this->assertTrue( $this->main->redeem_voucher() );
		$this->assertHasNotice( 'Voucher redeemed successfully!', 'success' );

	}

	/**
	 * Test reset_password(): form not submitted.
	 *
	 * @since 3.37.17
	 *
	 * @return void
	 */
	public function test_reset_password_not_submitted() {

		$this->assertNull( $this->main->reset_password() );

	}

	/**
	 * Test reset_password(): invalid nonce
	 *
	 * @since 3.37.17
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
	 * @since 3.37.17
	 *
	 * @return void
	 */
	public function test_reset_password_form_validation_error() {

		$this->mockPostRequest( array(
			'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
		) );

		$res = $this->main->reset_password();

		$this->assertIsWPError( $res );
		$this->assertEquals( 1, count( $res->errors ) );

		$errors = array(
			'Password is a required field',
			'Confirm Password is a required field',
		);

		$notices = llms_get_notices();

		foreach ( $errors as $error ) {
			$this->assertStringContains( $error, $notices );
		}

	}

	/**
	 * Test reset_password(): password reset key errors
	 *
	 * @since 3.37.17
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
		$this->assertWPErrorCodeEquals( 'llms_password_reset_invalid_key', $res );
		$this->assertStringContains( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', llms_get_notices() );

		// Real user fake key.
		$user = $this->factory->user->create_and_get();
		$data['llms_reset_login'] = $user->user_login;
		$this->mockPostRequest( $post );

		$res = $this->main->reset_password();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_invalid_key', $res );
		$this->assertStringContains( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', llms_get_notices() );

	}

	/**
	 * Test reset_password() submitted passwords don't match.
	 *
	 * @since 5.0.0
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
	 * Test reset_password() with an expired password reset key.
	 *
	 * @since 5.0.0
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
	 * @since 3.37.17
	 * @since 4.21.0 Added more assertions for testing with special character passwords.
	 *
	 * @return void
	 */
	public function test_reset_password_success() {

		LLMS_Install::create_pages();
		$controller = new LLMS_Controller_Account();

		$passwords = array(
			// See notes on spaces below.
			' with leading space',
			'with trailing space ',

			// Some simple characters.
			'123456arst!',
			'\slashy \ passwordy',
			'<such>()-!=***. special!y320',

			// These passwords were failing before we improved the tests.
			' AxwVr=@D`z5SXh&Cj/z{#8xta>rvx Nr!5Ur48rtI[ykmc8k~Uj&HO>)/$4:z98',
			'G!*EODpN[!rw] Z|tW|L4.2]@Iok1b1ws(kF3~BP0B%_}./{?)5y$Y`ODn|#-!x ',
			' w,~5E3`=RiPZq&.q0P>-R]1t|t7Qxev',
			'_rsBES.Icg~T)c( -UPh?;Dhu>Up{|b!woR{_hynn7$0(*e1mI1Q3t9(h.V1h]v ',

			// Generate some random passwords to test.
			wp_generate_password( 12 ),
			wp_generate_password( 32, true, true ),
			wp_generate_password( 64, true, true ),
		);

		foreach ( $passwords as $pass ) {

			wp_set_current_user( null );

			$user = $this->factory->user->create_and_get();

			// Fake user and key.
			$post = array(
				'_reset_password_nonce' => wp_create_nonce( 'llms_reset_password' ),
				'password'         => $pass,
				'password_confirm' => $pass,
				'llms_reset_key'   => get_password_reset_key( $user ),
				'llms_reset_login' => $user->user_login,
			);

			$this->mockPostRequest( $post );

			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'reset_password_handler' ) );

			$user = get_user_by( 'id', $user->ID );

			/**
			 * Because of `wp_magic_quotes()`, slashes will be automatically added when a user actually tries to login.
			 *
			 * We also will trim the password because WP runs `trim()` on passwords when logging in / creating accounts
			 * but it doesn't run it when using `wp_check_password()` itself: https://core.trac.wordpress.org/ticket/34889.
			 *
			 * We'll add these to `wp_check_password()` here to make sure that a user can actually login with their newly updated
			 * password.
			 */
			$this->assertTrue( wp_check_password( addslashes( trim( $pass ) ), $user->user_pass ), $pass );

			// $this->assertHasNotices( 'success' );
			// $this->assertStringContains( 'Your password has been updated.', llms_get_notices() );

			// User should be able to login using our login functionality / forms.
			$login = LLMS_Person_Handler::login( array(
				'llms_login'    => $user->user_email,
				/**
				 * Here we add slashes to simulate a physical $_POST with `wp_magic_quotes()` but we don't need to `trim()` because
				 * that's handled in `wp_authenticate()` (called by `wp_signon()` used by our handler).
				 */
				'llms_password' => addslashes( $pass ),
			) );

			$this->assertEquals( $user->ID, $login, $pass );

			wp_set_current_user( null );

			// Authenticate via the WP core method directly (redundant but...).
			$auth = wp_authenticate( $user->user_login, addslashes( $pass ) );
			$this->assertEquals( $auth, $user, $pass );

		}

	}

	/**
	 * Test reset_password_link_redirect(): no redirect when not on the account page.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_update_not_submitted() {

		$this->mockPostRequest( array() );
		$this->main->update();
		$this->assertEquals( 0, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );

	}

	/**
	 * Test account update form submission handler when user is not logged in
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_update_no_user() {

		// form submitted but user isn't logged in
		$this->mockPostRequest( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		$this->main->update();
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

	}

	/**
	 * Test account update form submission handler when missing required fields.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_update_missing_fields() {

		LLMS_Install::create_pages();
		LLMS_Forms::instance()->install( true );

		// create a user
		$uid = $this->factory->user->create();
		// sign the user in
		wp_set_current_user( $uid );

		// form submitted but missing fields
		$this->mockPostRequest( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		$this->main->update();
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

	}

	/**
	 * Test account update form submission handler
	 *
	 * @since 5.0.0
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
		$this->mockPostRequest( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
			'email_address' => 'help+23568@lifterlms.com',
			'email_address_confirm' => 'help+23568@lifterlms.com',
			'display_name' => 'Marshall P.',
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

		$this->main->update();

	}

}
