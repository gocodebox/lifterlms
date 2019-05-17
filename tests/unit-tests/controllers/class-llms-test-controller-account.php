<?php
/**
 * Tests for the LLMS_Controller_Account class.
 *
 * @group controllers
 *
 * @since 3.19.0
 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
 * @since [version] Added test methods on email address confirm field requirement.
 */
class LLMS_Test_Controller_Account extends LLMS_UnitTestCase {

	/**
	 * Consider dates equal within 60 seconds.
	 *
	 * @since ??
	 *
	 * @var int
	 */
	private $date_delta = 60;

	/**
	 * A default array of user information to feed a request with
	 *
	 * @since [version]
	 *
	 * @var array
	 */
	private $user_info = array(
		'email_address'             => 'help+23568@lifterlms.com',
		'email_address_confirm'     => 'help+23568@lifterlms.com',
		'first_name'                => 'Marshall',
		'last_name'                 => 'Pate',
		'llms_billing_address_1'    => 'Voluptatem',
		'llms_billing_address_2'    => '#12345',
		'llms_billing_city'         => 'Harum est dolorum sed vel perspiciatis consequatur dignissimos possimus delectus quos optio omnis error quas rem dicta et consectetur odio',
		'llms_billing_state'        => 'Esse ea est dolore sed sunt ipsum a ut nemo dolorem aut aliquam cillum asperiores minim culpa',
		'llms_billing_zip'          => '72995',
		'llms_billing_country'      => 'US',
	);

	/**
	 * Test order completion actions.
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_cancel_subscription() {

		// form not submitted.
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );

		// form submitted but missing required fields.
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// form submitted but invalid order id or the order id is invalid.
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
			'order_id' => 123,
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// create a real order.
		$order = $this->get_mock_order();

		// form submitted but invalid order id or the order doesn't belong to the current user.
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

			// active order moves to pending cancel.
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
	 * Test account update form submission handler catching email confirm validation errors.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_email_confirm_error() {

		LLMS_Install::create_pages();

		// create a user.
		$uid = $this->factory->user->create( array(
			'user_email' => $this->user_info['email_address'],
		) );

		// sign the user in.
		wp_set_current_user( $uid );

		// create nonce.
		$nonce = array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		);
		$post  = array_merge( $this->user_info, $nonce );

		// update something: email changes - no email confirm supplied.
		$post['email_address'] = 'test-' . $this->user_info['email_address'];
		unset( $post['email_address_confirm'] );
		$this->setup_post( $post );
		// expect submission but validation error.
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

		// update something: email changes - email confirm supplied but empty.
		$post['email_address_confirm'] = '';
		$this->setup_post( $post );
		// expect submission but validation error.
		do_action( 'init' );
		$this->assertEquals( 2, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

		// update something: email changes - email confirm supplied but not matching.
		$post['email_address']         = $this->user_info['email_address'];
		$post['email_address_confirm'] = 'wrong-' . $post['email_address'];
		$this->setup_post( $post );
		//expect submission but validation error.
		do_action( 'init' );
		$this->assertEquals( 3, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

	}

	/**
	 * Test account update form submission handler when no email and email confirm match.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_email_confirm_matching() {

		LLMS_Install::create_pages();

		// create a user.
		$uid = $this->factory->user->create( array(
			'user_email' => $this->user_info['email_address'],
		) );

		// sign the user in.
		wp_set_current_user( $uid );

		// create nonce.
		$nonce = array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		);
		$post  = array_merge( $this->user_info, $nonce );

		// email changes - email confirm matches.
		$post['email_address']         = 'test-' . $this->user_info['email_address'];
		$post['email_address_confirm'] = $post['email_address'];

		$this->setup_post( $post );

		// exceptions thrown in testing env instead of exit().
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );

		// run these assertions within actions because the exit() at the end of the redirect will halt program execution and then we'll never get to these assertions!
		add_action( 'llms_before_user_account_update_submit', function() {
			$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'lifterlms_user_updated', function() {
			$this->assertEquals( 1, did_action( 'lifterlms_user_updated' ) );
		} );

		do_action( 'init' );

	}

	/**
	 * Test account update form submission handler when no email changed => no email confirm is needed.
	 *
	 * @since [version]
	 * @return void
	 */
	public function test_update_email_confirm_unneded() {

		LLMS_Install::create_pages();

		// create a user.
		$uid = $this->factory->user->create( array(
			'user_email' => $this->user_info['email_address'],
		) );

		// sign the user in.
		wp_set_current_user( $uid );

		// create nonce.
		$nonce = array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		);
		$post  = array_merge( $this->user_info, $nonce );

		// no email changing - no need to have an email address confirm.
		$post['first_name'] = 'test-' . $this->user_info['first_name'];
		unset( $post['email_address_confirm'] );

		$this->setup_post( $post );

		// exceptions thrown in testing env instead of exit().
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );

		// run these assertions within actions because the exit() at the end of the redirect will halt program execution and then we'll never get to these assertions!
		add_action( 'llms_before_user_account_update_submit', function() {
			$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'lifterlms_user_updated', function() {
			$this->assertEquals( 1, did_action( 'lifterlms_user_updated' ) );
		} );

		do_action( 'init' );

	}


	/**
	 * Test account update form submission handler.
	 *
	 * @since 3.19.4
	 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from test lib.
	 *
	 * @return void
	 */
	public function test_update() {

		LLMS_Install::create_pages();

		// form not submitted.
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );

		// also not submitted.
		$this->setup_get( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );

		// form submitted but user isn't logged in.
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

		// create a user.
		$uid = $this->factory->user->create();
		// sign the user in.
		wp_set_current_user( $uid );

		// form submitted but missing fields.
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 2, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

		// update something.
		$this->setup_post( array_merge( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		), $this->user_info ) );

		// exceptions thrown in testing env instead of exit().
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );

		// run these assertions within actions because the exit() at the end of the redirect will halt program execution and then we'll never get to these assertions!
		add_action( 'llms_before_user_account_update_submit', function() {
			$this->assertEquals( 3, did_action( 'llms_before_user_account_update_submit' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'lifterlms_user_updated', function() {
			$this->assertEquals( 1, did_action( 'lifterlms_user_updated' ) );
		} );

		do_action( 'init' );

	}

}
